<?php

namespace App\Domain\SaudeOcupacional\Documentos;

use App\Domain\SaudeOcupacional\Painel\PainelCache;
use App\Domain\SaudeOcupacional\Seguranca\AuditoriaAcessoSaude;
use App\Domain\SaudeOcupacional\Seguranca\PerfilAcessoSaude;
use App\Models\Arquivo;
use App\Models\SaudeDocumento;
use App\Models\SaudeDocumentoVersao;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Documentos SST: repositório versionado por empresa (PCMSO, PGR, LTCAT, laudos, ordens de serviço, ASOs…).
 *
 * - Cada envio de arquivo é uma nova versão; a versão de maior número é a atual.
 * - Documento "sensível" (laudo/exame médico) só aparece para quem tem o perfil Saúde.
 * - Todo download/visualização grava a trilha (saude_documentos_acessos); de sensível, também a auditoria LGPD.
 */
class DocumentosSstService
{
    public const POR_PAGINA = 20;

    public const ACESSO_VISUALIZAR = 'visualizar';

    public const ACESSO_BAIXAR = 'baixar';

    public const ACESSO_ENVIAR = 'enviar';

    public function __construct(
        private readonly ArmazenamentoDocumentoSst $armazenamento,
        private readonly PerfilAcessoSaude $perfil,
        private readonly AuditoriaAcessoSaude $auditoria
    ) {
    }

    /**
     * @param  array{tipo?: string|null, situacao?: string|null, busca?: string|null, feedback_id?: int|null, page?: int}  $filtros
     * @return array{itens: list<array<string, mixed>>, total: int, pagina: int, paginas: int, totais: array<string, int>}
     */
    public function listar(int $empresaId, array $filtros, User $usuario, ?CarbonImmutable $hoje = null): array
    {
        $hoje ??= CarbonImmutable::today();
        $base = $this->base($empresaId, $usuario);

        if (!empty($filtros['tipo']) && isset(SaudeDocumento::TIPOS[$filtros['tipo']])) {
            $base->where('d.tipo', $filtros['tipo']);
        }
        if (!empty($filtros['feedback_id'])) {
            $base->where('d.feedback_id', (int) $filtros['feedback_id']);
        }
        $busca = trim((string) ($filtros['busca'] ?? ''));
        if ($busca !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $busca) . '%';
            $base->where(fn (Builder $q) => $q->where('d.titulo', 'like', $like)->orWhere('c.nome', 'like', $like));
        }

        $totais = $this->totaisPorSituacao(clone $base, $hoje);
        $this->filtrarSituacao($base, (string) ($filtros['situacao'] ?? ''), $hoje);

        $total = (clone $base)->count();
        $paginas = max(1, (int) ceil($total / self::POR_PAGINA));
        $pagina = min(max(1, (int) ($filtros['page'] ?? 1)), $paginas);

        $linhas = $base
            ->orderByRaw('CASE WHEN v.vigencia_fim IS NULL THEN 1 ELSE 0 END')
            ->orderBy('v.vigencia_fim')
            ->orderByDesc('d.id')
            ->forPage($pagina, self::POR_PAGINA)
            ->get([
                'd.id', 'd.tipo', 'd.titulo', 'd.sigilo', 'd.feedback_id', 'd.origem_tipo', 'd.responsavel',
                'c.nome as colaborador', 'v.id as versao_id', 'v.versao', 'v.emissao', 'v.vigencia_inicio', 'v.vigencia_fim',
                'v.created_at as versao_em', 'arq.extensao', 'arq.bytes',
            ]);

        return [
            'itens' => $linhas->map(fn ($l) => $this->formatarLinha($l, $hoje))->all(),
            'total' => $total,
            'pagina' => $pagina,
            'paginas' => $paginas,
            'totais' => $totais,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detalhe(int $empresaId, int $documentoId, User $usuario, ?CarbonImmutable $hoje = null): array
    {
        $hoje ??= CarbonImmutable::today();
        $doc = $this->documento($empresaId, $documentoId, $usuario);

        $versoes = SaudeDocumentoVersao::query()
            ->with(['Arquivo:id,nome,extensao,bytes'])
            ->where('saude_documento_id', $doc->id)
            ->orderByDesc('versao')
            ->get();

        $nomes = User::query()
            ->whereIn('id', $versoes->pluck('enviado_por')->filter()->unique()->all() ?: [0])
            ->pluck('nome', 'id');

        $acessos = DB::table('saude_documentos_acessos as ac')
            ->join('saude_documentos_versoes as v', 'v.id', '=', 'ac.saude_documento_versao_id')
            ->leftJoin('users as u', 'u.id', '=', 'ac.user_id')
            ->where('v.saude_documento_id', $doc->id)
            ->where('ac.empresa_id', $empresaId)
            ->orderByDesc('ac.id')
            ->limit(30)
            ->get(['ac.acao', 'ac.created_at', 'v.versao', 'u.nome']);

        return [
            'documento' => [
                'id' => $doc->id,
                'tipo' => $doc->tipo,
                'tipo_label' => SaudeDocumento::TIPOS[$doc->tipo][0] ?? $doc->tipo,
                'titulo' => $doc->titulo,
                'sigilo' => $doc->sigilo,
                'responsavel' => $doc->responsavel,
                'observacao' => $doc->observacao,
                'feedback_id' => $doc->feedback_id,
                'colaborador' => $doc->feedback_id ? $this->nomeColaborador($empresaId, (int) $doc->feedback_id) : null,
                'gerado_pelo_sistema' => $doc->origem_tipo !== null,
            ],
            'versoes' => $versoes->map(fn (SaudeDocumentoVersao $v) => [
                'id' => $v->id,
                'versao' => $v->versao,
                'emissao_br' => $v->emissao_br,
                'vigencia_inicio_br' => $v->vigencia_inicio_br,
                'vigencia_fim_br' => $v->vigencia_fim_br,
                'situacao' => SituacaoDocumentoSst::de($v->vigencia_fim?->toDateString(), $hoje),
                'arquivo' => $v->Arquivo ? $v->Arquivo->nome . $v->Arquivo->extensao : null,
                'bytes' => $v->Arquivo?->bytes,
                'assinado_digitalmente' => $v->documento_assinatura_id !== null,
                'enviado_por' => $v->enviado_por ? ($nomes[$v->enviado_por] ?? null) : 'Sistema',
                'enviado_em_br' => $v->created_at?->format('d/m/Y H:i'),
            ])->values()->all(),
            'acessos' => $acessos->map(fn ($a) => [
                'acao' => $a->acao,
                'usuario' => $a->nome ?: 'Usuário removido',
                'versao' => (int) $a->versao,
                'em_br' => $a->created_at ? CarbonImmutable::parse($a->created_at)->format('d/m/Y H:i') : null,
            ])->all(),
        ];
    }

    /**
     * @param  array{tipo: string, titulo: string, sigilo?: string|null, feedback_id?: int|null, responsavel?: string|null, observacao?: string|null, emissao?: string|null, vigencia_inicio?: string|null, vigencia_fim?: string|null}  $dados
     */
    public function criar(int $empresaId, array $dados, UploadedFile $arquivo, User $autor): SaudeDocumento
    {
        $tipo = (string) $dados['tipo'];
        if (!isset(SaudeDocumento::TIPOS[$tipo])) {
            throw ValidationException::withMessages(['tipo' => 'Tipo de documento inválido.']);
        }
        $sigilo = $this->sigiloEfetivo($tipo, $dados['sigilo'] ?? null);
        if ($sigilo === SaudeDocumento::SIGILO_SENSIVEL && !$this->perfil->podeVerDadosSensiveis($autor)) {
            throw ValidationException::withMessages(['sigilo' => 'Só o perfil Saúde (dados sensíveis) pode enviar documento sigiloso.']);
        }
        $feedbackId = !empty($dados['feedback_id']) ? $this->validarColaborador($empresaId, (int) $dados['feedback_id']) : null;
        $datas = $this->datas($dados);

        $gravado = $this->armazenamento->gravarUpload($empresaId, $arquivo, $autor->id);

        try {
            $documento = DB::transaction(function () use ($empresaId, $tipo, $sigilo, $feedbackId, $dados, $datas, $gravado, $autor) {
                $documento = SaudeDocumento::create([
                    'empresa_id' => $empresaId,
                    'tipo' => $tipo,
                    'titulo' => mb_substr(trim((string) $dados['titulo']), 0, 200),
                    'sigilo' => $sigilo,
                    'feedback_id' => $feedbackId,
                    'responsavel' => $this->texto($dados['responsavel'] ?? null, 150),
                    'observacao' => $this->texto($dados['observacao'] ?? null, 1000),
                    'user_id' => $autor->id,
                ]);
                $versao = $this->gravarVersao($documento, 1, $gravado, $datas, $autor->id, null);
                $this->registrarAcesso($versao, $empresaId, $autor, self::ACESSO_ENVIAR);
                $this->auditar($autor, $documento, sprintf('enviou o documento SST "%s" (versão 1)', $documento->titulo));

                return $documento;
            });
        } catch (\Throwable $e) {
            $this->descartarArquivo($gravado['arquivo']);
            throw $e;
        }

        PainelCache::invalidar($empresaId);

        return $documento;
    }

    /**
     * @param  array{emissao?: string|null, vigencia_inicio?: string|null, vigencia_fim?: string|null}  $dados
     */
    public function novaVersao(int $empresaId, int $documentoId, array $dados, UploadedFile $arquivo, User $autor): SaudeDocumentoVersao
    {
        $documento = $this->documento($empresaId, $documentoId, $autor);
        if ($documento->origem_tipo !== null) {
            throw ValidationException::withMessages(['arquivo' => 'Documento gerado pelo sistema: a nova versão vem da origem (ex.: publicar o PCMSO).']);
        }
        $datas = $this->datas($dados);
        $gravado = $this->armazenamento->gravarUpload($empresaId, $arquivo, $autor->id);

        try {
            $versao = DB::transaction(function () use ($documento, $empresaId, $datas, $gravado, $autor) {
                $numero = $this->proximaVersao($documento->id);
                $versao = $this->gravarVersao($documento, $numero, $gravado, $datas, $autor->id, null);
                $documento->touch();
                $this->registrarAcesso($versao, $empresaId, $autor, self::ACESSO_ENVIAR);
                $this->auditar($autor, $documento, sprintf('enviou a versão %d do documento SST "%s"', $numero, $documento->titulo));

                return $versao;
            });
        } catch (\Throwable $e) {
            $this->descartarArquivo($gravado['arquivo']);
            throw $e;
        }

        PainelCache::invalidar($empresaId);

        return $versao;
    }

    /**
     * @param  array{titulo?: string, responsavel?: string|null, observacao?: string|null}  $dados
     */
    public function atualizar(int $empresaId, int $documentoId, array $dados, User $autor): void
    {
        $documento = $this->documento($empresaId, $documentoId, $autor);
        $documento->fill([
            'titulo' => mb_substr(trim((string) ($dados['titulo'] ?? $documento->titulo)), 0, 200),
            'responsavel' => $this->texto($dados['responsavel'] ?? null, 150),
            'observacao' => $this->texto($dados['observacao'] ?? null, 1000),
        ]);
        if ($documento->isDirty()) {
            $documento->save();
            $this->auditar($autor, $documento, sprintf('alterou os dados do documento SST "%s"', $documento->titulo));
        }
    }

    /**
     * Arquiva (soft delete): some das listas, mas o arquivo e a trilha ficam guardados (prazo legal de guarda).
     */
    public function arquivar(int $empresaId, int $documentoId, User $autor): void
    {
        $documento = $this->documento($empresaId, $documentoId, $autor);
        $documento->delete();
        $this->auditar($autor, $documento, sprintf('arquivou o documento SST "%s"', $documento->titulo));
        PainelCache::invalidar($empresaId);
    }

    /**
     * Libera o arquivo de uma versão para download/visualização e grava a trilha.
     *
     * @return array{arquivo: Arquivo, nome: string, mime: string}
     */
    public function abrir(int $empresaId, int $versaoId, User $usuario, string $acao): array
    {
        $versao = SaudeDocumentoVersao::query()->with('Arquivo')->findOrFail($versaoId);
        $documento = $this->documento($empresaId, $versao->saude_documento_id, $usuario);
        $arquivo = $versao->Arquivo;
        if (!$arquivo || !$arquivo->file) {
            throw (new ModelNotFoundException())->setModel('Arquivo do documento', [$versaoId]);
        }

        $acao = $acao === self::ACESSO_VISUALIZAR ? self::ACESSO_VISUALIZAR : self::ACESSO_BAIXAR;
        $this->registrarAcesso($versao, $empresaId, $usuario, $acao);
        if ($documento->sigilo === SaudeDocumento::SIGILO_SENSIVEL) {
            $this->auditoria->registrar(
                $usuario,
                AuditoriaAcessoSaude::ACAO_BAIXAR_ANEXO,
                'saude_documento',
                $documento->id,
                sprintf('abriu documento SST sigiloso #%d (versão %d)', $documento->id, $versao->versao)
            );
        }

        $extensao = ltrim((string) $arquivo->extensao, '.');

        return [
            'arquivo' => $arquivo,
            'nome' => sprintf('%s v%d.%s', $this->nomeSeguro($documento->titulo), $versao->versao, $extensao ?: 'pdf'),
            'mime' => $this->mime($extensao),
        ];
    }

    /**
     * Para o Painel: documentos com vigência vencida ou vencendo (versão atual). Conta todos, inclusive sigilosos
     * (só número, sem conteúdo).
     *
     * @return array{total: int, vencendo: int, vencidos: int}
     */
    public function alertas(int $empresaId, ?CarbonImmutable $hoje = null): array
    {
        $hoje ??= CarbonImmutable::today();
        $totais = $this->totaisPorSituacao($this->base($empresaId, null), $hoje);

        return [
            'total' => array_sum($totais),
            'vencendo' => $totais[SituacaoDocumentoSst::VENCENDO],
            'vencidos' => $totais[SituacaoDocumentoSst::VENCIDO],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function opcoes(User $usuario): array
    {
        $veSensivel = $this->perfil->podeVerDadosSensiveis($usuario);
        $tipos = [];
        foreach (SaudeDocumento::TIPOS as $valor => [$rotulo, $sigilo]) {
            if ($sigilo === SaudeDocumento::SIGILO_SENSIVEL && !$veSensivel) {
                continue;
            }
            $tipos[] = ['value' => $valor, 'label' => $rotulo, 'sigilo' => $sigilo];
        }

        return [
            'tipos' => $tipos,
            'situacoes' => collect(SituacaoDocumentoSst::ROTULOS)->map(fn ($l, $v) => ['value' => $v, 'label' => $l])->values()->all(),
            've_dados_sensiveis' => $veSensivel,
            'pode_configurar' => $this->perfil->podeConfigurar($usuario),
            'janela_vencendo_dias' => SituacaoDocumentoSst::JANELA_VENCENDO_DIAS,
            'extensoes' => array_keys(ArmazenamentoDocumentoSst::TIPOS_ACEITOS),
            'tamanho_maximo_mb' => (int) (ArmazenamentoDocumentoSst::TAMANHO_MAXIMO_KB / 1024),
        ];
    }

    /**
     * Grava uma versão (usado também pelo arquivamento automático).
     *
     * @param  array{arquivo: Arquivo, hash: string}  $gravado
     * @param  array{emissao: ?string, vigencia_inicio: ?string, vigencia_fim: ?string}  $datas
     */
    public function gravarVersao(SaudeDocumento $documento, int $numero, array $gravado, array $datas, ?int $userId, ?int $documentoAssinaturaId): SaudeDocumentoVersao
    {
        return SaudeDocumentoVersao::create([
            'saude_documento_id' => $documento->id,
            'versao' => $numero,
            'arquivo_id' => $gravado['arquivo']->id,
            'hash_sha256' => $gravado['hash'],
            'emissao' => $datas['emissao'],
            'vigencia_inicio' => $datas['vigencia_inicio'],
            'vigencia_fim' => $datas['vigencia_fim'],
            'documento_assinatura_id' => $documentoAssinaturaId,
            'enviado_por' => $userId,
        ]);
    }

    public function proximaVersao(int $documentoId): int
    {
        return (int) SaudeDocumentoVersao::query()->where('saude_documento_id', $documentoId)->lockForUpdate()->max('versao') + 1;
    }

    /**
     * Documentos ativos da empresa + versão atual. Sem usuário (Painel/alertas) inclui os sigilosos.
     */
    private function base(int $empresaId, ?User $usuario): Builder
    {
        $atual = DB::table('saude_documentos_versoes')
            ->selectRaw('saude_documento_id, MAX(versao) as versao')
            ->groupBy('saude_documento_id');

        $q = DB::table('saude_documentos as d')
            ->joinSub($atual, 'ult', 'ult.saude_documento_id', '=', 'd.id')
            ->join('saude_documentos_versoes as v', function ($j) {
                $j->on('v.saude_documento_id', '=', 'd.id')->on('v.versao', '=', 'ult.versao');
            })
            ->leftJoin('arquivos as arq', 'arq.id', '=', 'v.arquivo_id')
            ->leftJoin('feedback_curriculos as fc', 'fc.id', '=', 'd.feedback_id')
            ->leftJoin('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->where('d.empresa_id', $empresaId)
            ->whereNull('d.deleted_at');

        if ($usuario && !$this->perfil->podeVerDadosSensiveis($usuario)) {
            $q->where('d.sigilo', SaudeDocumento::SIGILO_COMUM);
        }

        return $q;
    }

    /**
     * @return array<string, int>
     */
    private function totaisPorSituacao(Builder $base, CarbonImmutable $hoje): array
    {
        // Fim do dia: compara igual em MySQL (date) e SQLite (texto 'Y-m-d H:i:s').
        $limite = $hoje->addDays(SituacaoDocumentoSst::JANELA_VENCENDO_DIAS)->toDateString() . ' 23:59:59';
        $hojeStr = $hoje->toDateString();

        $linha = $base->selectRaw(
            'SUM(CASE WHEN v.vigencia_fim IS NULL THEN 1 ELSE 0 END) as sem_validade,
             SUM(CASE WHEN v.vigencia_fim < ? THEN 1 ELSE 0 END) as vencido,
             SUM(CASE WHEN v.vigencia_fim >= ? AND v.vigencia_fim <= ? THEN 1 ELSE 0 END) as vencendo,
             SUM(CASE WHEN v.vigencia_fim > ? THEN 1 ELSE 0 END) as vigente',
            [$hojeStr, $hojeStr, $limite, $limite]
        )->first();

        return [
            SituacaoDocumentoSst::VIGENTE => (int) ($linha->vigente ?? 0),
            SituacaoDocumentoSst::VENCENDO => (int) ($linha->vencendo ?? 0),
            SituacaoDocumentoSst::VENCIDO => (int) ($linha->vencido ?? 0),
            SituacaoDocumentoSst::SEM_VALIDADE => (int) ($linha->sem_validade ?? 0),
        ];
    }

    private function filtrarSituacao(Builder $q, string $situacao, CarbonImmutable $hoje): void
    {
        $hojeStr = $hoje->toDateString();
        // Fim do dia: compara igual em MySQL (date) e SQLite (texto 'Y-m-d H:i:s').
        $limite = $hoje->addDays(SituacaoDocumentoSst::JANELA_VENCENDO_DIAS)->toDateString() . ' 23:59:59';

        match ($situacao) {
            SituacaoDocumentoSst::SEM_VALIDADE => $q->whereNull('v.vigencia_fim'),
            SituacaoDocumentoSst::VENCIDO => $q->where('v.vigencia_fim', '<', $hojeStr),
            SituacaoDocumentoSst::VENCENDO => $q->whereBetween('v.vigencia_fim', [$hojeStr, $limite]),
            SituacaoDocumentoSst::VIGENTE => $q->where('v.vigencia_fim', '>', $limite),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function formatarLinha(object $l, CarbonImmutable $hoje): array
    {
        $fim = $l->vigencia_fim ? CarbonImmutable::parse($l->vigencia_fim) : null;

        return [
            'id' => (int) $l->id,
            'tipo' => $l->tipo,
            'tipo_label' => SaudeDocumento::TIPOS[$l->tipo][0] ?? $l->tipo,
            'titulo' => $l->titulo,
            'sigilo' => $l->sigilo,
            'colaborador' => $l->colaborador,
            'feedback_id' => $l->feedback_id ? (int) $l->feedback_id : null,
            'responsavel' => $l->responsavel,
            'gerado_pelo_sistema' => $l->origem_tipo !== null,
            'versao_id' => (int) $l->versao_id,
            'versao' => (int) $l->versao,
            'emissao_br' => $l->emissao ? CarbonImmutable::parse($l->emissao)->format('d/m/Y') : null,
            'vigencia_inicio_br' => $l->vigencia_inicio ? CarbonImmutable::parse($l->vigencia_inicio)->format('d/m/Y') : null,
            'vigencia_fim_br' => $fim?->format('d/m/Y'),
            'dias_para_vencer' => $fim ? (int) $hoje->startOfDay()->diffInDays($fim->startOfDay(), false) : null,
            'situacao' => SituacaoDocumentoSst::de($l->vigencia_fim, $hoje),
            'extensao' => $l->extensao ? ltrim((string) $l->extensao, '.') : null,
            'bytes' => $l->bytes !== null ? (int) $l->bytes : null,
        ];
    }

    private function documento(int $empresaId, int $documentoId, User $usuario): SaudeDocumento
    {
        $documento = SaudeDocumento::query()->where('empresa_id', $empresaId)->findOrFail($documentoId);

        // Sigiloso para quem não tem o perfil Saúde: responde como inexistente (não revela que existe).
        if ($documento->sigilo === SaudeDocumento::SIGILO_SENSIVEL && !$this->perfil->podeVerDadosSensiveis($usuario)) {
            throw (new ModelNotFoundException())->setModel(SaudeDocumento::class, [$documentoId]);
        }

        return $documento;
    }

    private function sigiloEfetivo(string $tipo, ?string $pedido): string
    {
        $padrao = SaudeDocumento::TIPOS[$tipo][1];
        // Tipo sigiloso nunca vira comum; os demais podem ser marcados como sigilosos.
        if ($padrao === SaudeDocumento::SIGILO_SENSIVEL) {
            return $padrao;
        }

        return $pedido === SaudeDocumento::SIGILO_SENSIVEL ? SaudeDocumento::SIGILO_SENSIVEL : SaudeDocumento::SIGILO_COMUM;
    }

    private function validarColaborador(int $empresaId, int $feedbackId): int
    {
        $existe = DB::table('feedback_curriculos')->where('id', $feedbackId)->where('empresa_id', $empresaId)->whereNull('deleted_at')->exists();
        if (!$existe) {
            throw ValidationException::withMessages(['feedback_id' => 'Colaborador não encontrado nesta empresa.']);
        }

        return $feedbackId;
    }

    private function nomeColaborador(int $empresaId, int $feedbackId): ?string
    {
        return DB::table('feedback_curriculos as fc')
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->where('fc.id', $feedbackId)
            ->where('fc.empresa_id', $empresaId)
            ->value('c.nome');
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array{emissao: ?string, vigencia_inicio: ?string, vigencia_fim: ?string}
     */
    private function datas(array $dados): array
    {
        $emissao = $this->data($dados['emissao'] ?? null, 'emissao');
        $inicio = $this->data($dados['vigencia_inicio'] ?? null, 'vigencia_inicio');
        $fim = $this->data($dados['vigencia_fim'] ?? null, 'vigencia_fim');

        if ($inicio && $fim && $fim->lessThanOrEqualTo($inicio)) {
            throw ValidationException::withMessages(['vigencia_fim' => 'O fim da vigência precisa ser depois do início.']);
        }

        return [
            'emissao' => $emissao?->toDateString(),
            'vigencia_inicio' => $inicio?->toDateString(),
            'vigencia_fim' => $fim?->toDateString(),
        ];
    }

    private function data(mixed $valor, string $campo): ?CarbonImmutable
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        $valor = (string) $valor;
        foreach (['d/m/Y', 'Y-m-d'] as $formato) {
            try {
                $data = CarbonImmutable::createFromFormat('!' . $formato, $valor);
            } catch (\Throwable) {
                continue;
            }
            if ($data !== false && $data->format($formato) === $valor) {
                return $data;
            }
        }

        throw ValidationException::withMessages([$campo => 'Data inválida.']);
    }

    private function registrarAcesso(SaudeDocumentoVersao $versao, int $empresaId, User $usuario, string $acao): void
    {
        DB::table('saude_documentos_acessos')->insert([
            'saude_documento_versao_id' => $versao->id,
            'empresa_id' => $empresaId,
            'user_id' => $usuario->id,
            'acao' => $acao,
            'ip' => request()?->ip(),
            'created_at' => now(),
        ]);
    }

    private function auditar(User $autor, SaudeDocumento $documento, string $descricao): void
    {
        // Só referências: nunca o conteúdo do documento.
        activity('saude_documentos')
            ->causedBy($autor)
            ->withProperties(['empresa_id' => $documento->empresa_id, 'saude_documento_id' => $documento->id, 'tipo' => $documento->tipo])
            ->tap(function ($activity) use ($descricao) {
                $activity->descricao = $descricao;
            })
            ->log($descricao);
    }

    private function descartarArquivo(Arquivo $arquivo): void
    {
        try {
            \Illuminate\Support\Facades\Storage::disk($arquivo->disco)->delete($arquivo->file);
            $arquivo->delete();
        } catch (\Throwable) {
            // O arquivo órfão não expõe nada (disco privado); não mascara o erro original.
        }
    }

    private function texto(mixed $valor, int $max): ?string
    {
        $t = trim((string) $valor);

        return $t === '' ? null : mb_substr($t, 0, $max);
    }

    private function nomeSeguro(string $titulo): string
    {
        $nome = preg_replace('/[^\pL\pN \-_]+/u', '', $titulo) ?: 'documento';

        return mb_substr(trim($nome), 0, 80) ?: 'documento';
    }

    private function mime(string $extensao): string
    {
        return match (strtolower($extensao)) {
            'jpg', 'jpeg' => Arquivo::MIME_JPEG,
            'png' => Arquivo::MIME_PNG,
            'docx' => Arquivo::MIME_DOCX,
            'xlsx' => Arquivo::MIME_XLSX,
            default => Arquivo::MIME_PDF,
        };
    }
}
