<?php

namespace App\Domain\SaudeOcupacional\Documentos;

use App\Domain\SaudeOcupacional\ModuloSaudeOcupacional;
use App\Domain\SaudeOcupacional\Painel\PainelCache;
use App\Domain\SaudeOcupacional\Pcmso\PcmsoPdfService;
use App\Models\DocumentoParaAssinatura;
use App\Models\Examesesmt;
use App\Models\ExamesesmtAso;
use App\Models\PcmsoVersao;
use App\Models\SaudeDocumento;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Leva para Documentos SST o que o próprio sistema gera:
 * - PDF do PCMSO ao publicar a versão (vigência = a da versão);
 * - ASO assinado digitalmente (sem vigência: o vencimento do ASO é controlado na Conformidade).
 *
 * Idempotente por origem (origem_tipo + origem_id): reprocessar não duplica.
 */
class ArquivamentoAutomaticoSst
{
    public const ORIGEM_PCMSO_VERSAO = 'pcmso_versao';

    public const ORIGEM_ASO = 'examesesmt_aso';

    public function __construct(
        private readonly DocumentosSstService $documentos,
        private readonly ArmazenamentoDocumentoSst $armazenamento
    ) {
    }

    public function arquivarPcmso(int $empresaId, int $versaoId, ?int $userId): ?SaudeDocumento
    {
        if (!$this->disponivel($empresaId)) {
            return null;
        }

        $versao = PcmsoVersao::query()->where('empresa_id', $empresaId)->find($versaoId);
        if (!$versao || $versao->status === PcmsoVersao::STATUS_RASCUNHO) {
            return null;
        }
        // A versão anterior foi arquivada com fim = início desta − 1 dia: leva o fim para o documento dela.
        $this->sincronizarVigenciasPcmso($empresaId, (int) $versao->pcmso_id);
        if ($this->jaArquivado(self::ORIGEM_PCMSO_VERSAO, $versao->id)) {
            return null;
        }

        $programa = (string) DB::table('pcmsos')->where('id', $versao->pcmso_id)->value('label');
        $conteudo = app(PcmsoPdfService::class)->gerar($empresaId, $versao->id, 'Sistema')->output();
        $titulo = trim('PCMSO ' . $programa . ' - versão ' . $versao->versao);

        return $this->registrar($empresaId, [
            'tipo' => 'pcmso',
            'titulo' => $titulo,
            'origem_tipo' => self::ORIGEM_PCMSO_VERSAO,
            'origem_id' => $versao->id,
            'feedback_id' => null,
            'responsavel' => optional($versao->Coordenador)->nome,
        ], $conteudo, $titulo . '.pdf', [
            'emissao' => CarbonImmutable::parse($versao->publicado_em ?? now())->toDateString(),
            'vigencia_inicio' => $versao->vigencia_inicio?->toDateString(),
            'vigencia_fim' => $versao->vigencia_fim?->toDateString(),
        ], $userId, null);
    }

    /**
     * Chamado na conclusão da assinatura digital (job em fila). Nunca lança: não pode travar o fluxo de assinatura.
     */
    public function arquivarAsoAssinado(DocumentoParaAssinatura $doc): void
    {
        try {
            if ($doc->status !== DocumentoParaAssinatura::STATUS_CONCLUIDO || $doc->documentable_type !== Examesesmt::class) {
                return;
            }
            $empresaId = (int) $doc->empresa_id;
            if (!$this->disponivel($empresaId)) {
                return;
            }

            $aso = ExamesesmtAso::query()->where('empresa_id', $empresaId)->where('documento_assinatura_id', $doc->id)->first();
            if (!$aso || $this->jaArquivado(self::ORIGEM_ASO, $aso->id)) {
                return;
            }

            $arquivo = $doc->arquivoAssinado ?? $doc->arquivo;
            if (!$arquivo) {
                return;
            }

            $r = DB::table('examesesmts as e')
                ->join('exame_funcionarios as ef', 'ef.id', '=', 'e.exame_funcionario_id')
                ->leftJoin('exame_tipos as t', 't.id', '=', 'ef.exame_tipo_id')
                ->where('e.id', $aso->examesesmt_id)
                ->where('e.empresa_id', $empresaId)
                ->first(['e.feedback_id', 'e.data_realizacao', 't.label as tipo']);
            if (!$r) {
                return;
            }

            $data = $r->data_realizacao ? CarbonImmutable::parse($r->data_realizacao) : null;
            $documento = SaudeDocumento::create([
                'empresa_id' => $empresaId,
                'tipo' => 'aso',
                'titulo' => trim('ASO ' . ($r->tipo ?? '') . ($data ? ' de ' . $data->format('d/m/Y') : '')),
                'sigilo' => SaudeDocumento::SIGILO_COMUM,
                'feedback_id' => $r->feedback_id ? (int) $r->feedback_id : null,
                'origem_tipo' => self::ORIGEM_ASO,
                'origem_id' => $aso->id,
                'responsavel' => is_array($aso->examinador) ? ($aso->examinador['nome'] ?? null) : null,
                'user_id' => null,
            ]);

            // Reaproveita o PDF assinado (com marca e página de verificação), sem copiar o arquivo.
            $this->documentos->gravarVersao($documento, 1, [
                'arquivo' => $arquivo,
                'hash' => (string) ($doc->hash_sha256 ?? ''),
            ], [
                'emissao' => $data?->toDateString(),
                'vigencia_inicio' => null,
                'vigencia_fim' => null,
            ], null, $doc->id);

            PainelCache::invalidar($empresaId);
        } catch (\Throwable $e) {
            Log::warning('ArquivamentoAutomaticoSst: ASO assinado não arquivado', ['documento_assinatura_id' => $doc->id, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * @param  array<string, mixed>  $dados
     * @param  array{emissao: ?string, vigencia_inicio: ?string, vigencia_fim: ?string}  $datas
     */
    private function registrar(int $empresaId, array $dados, string $conteudo, string $nomeArquivo, array $datas, ?int $userId, ?int $documentoAssinaturaId): SaudeDocumento
    {
        $gravado = $this->armazenamento->gravarConteudo($empresaId, $conteudo, $nomeArquivo, 'pdf', $userId);

        $documento = DB::transaction(function () use ($empresaId, $dados, $gravado, $datas, $userId, $documentoAssinaturaId) {
            $documento = SaudeDocumento::create($dados + [
                'empresa_id' => $empresaId,
                'sigilo' => SaudeDocumento::SIGILO_COMUM,
                'user_id' => $userId,
            ]);
            $this->documentos->gravarVersao($documento, 1, $gravado, $datas, $userId, $documentoAssinaturaId);

            return $documento;
        });

        PainelCache::invalidar($empresaId);

        return $documento;
    }

    private function sincronizarVigenciasPcmso(int $empresaId, int $pcmsoId): void
    {
        $versoes = DB::table('pcmsos_versoes')
            ->where('empresa_id', $empresaId)
            ->where('pcmso_id', $pcmsoId)
            ->where('status', PcmsoVersao::STATUS_ARQUIVADO)
            ->whereNotNull('vigencia_fim')
            ->whereNull('deleted_at')
            ->pluck('vigencia_fim', 'id');

        if ($versoes->isEmpty()) {
            return;
        }

        $documentos = DB::table('saude_documentos')
            ->where('empresa_id', $empresaId)
            ->where('origem_tipo', self::ORIGEM_PCMSO_VERSAO)
            ->whereIn('origem_id', $versoes->keys()->all())
            ->pluck('origem_id', 'id');

        foreach ($documentos as $documentoId => $origemId) {
            DB::table('saude_documentos_versoes')
                ->where('saude_documento_id', (int) $documentoId)
                ->update(['vigencia_fim' => substr((string) $versoes[$origemId], 0, 10), 'updated_at' => now()]);
        }
    }

    private function jaArquivado(string $origemTipo, int $origemId): bool
    {
        return SaudeDocumento::withTrashed()->where('origem_tipo', $origemTipo)->where('origem_id', $origemId)->exists();
    }

    private function disponivel(int $empresaId): bool
    {
        return Schema::hasTable(SaudeDocumento::TABELA) && ModuloSaudeOcupacional::habilitado($empresaId);
    }
}
