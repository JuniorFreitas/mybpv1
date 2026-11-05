<?php

namespace App\Domain\SaudeOcupacional\Aso;

use App\Domain\SaudeOcupacional\Medicos\MedicosResponsaveisService;
use App\Domain\SaudeOcupacional\Pcmso\ExamesPrevistosPcmso;
use App\Domain\SaudeOcupacional\Pgr\RiscosCatalogoService;
use App\Domain\SaudeOcupacional\Regras\PerfisColaboradoresRepositorio;
use App\Models\DocumentoParaAssinatura;
use App\Models\Examesesmt;
use App\Models\ExamesesmtAso;
use App\Models\FeedbackCurriculo;
use App\Models\SaudeMedicoVinculo;
use App\Models\Sistema;
use App\Models\User;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use App\Services\Demissao\ComunicadoDispensaSemJustaCausaPdfService;
use Barryvdh\DomPDF\PDF;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * ASO (Atestado de Saúde Ocupacional) em PDF no formato da NR-7 7.5.19.1:
 * empresa (razão social/CNPJ), empregado (nome, CPF, função), riscos do PGR ou sua inexistência,
 * exames clínico e complementares com datas, apto/inapto, médico responsável pelo PCMSO e médico examinador
 * (nome, CRM, data e assinatura).
 *
 * Emitir grava a "foto" do documento (examesesmts_aso). Observações clínicas não entram no ASO.
 */
class AsoDocumentoService
{
    public const TIPO_DOCUMENTO_ASSINATURA = 'aso';

    public const MAX_EXAMES = 30;

    public function __construct(
        private readonly PerfisColaboradoresRepositorio $perfis,
        private readonly RiscosCatalogoService $riscos,
        private readonly ExamesPrevistosPcmso $examesPrevistos,
        private readonly MedicosResponsaveisService $medicos
    ) {
    }

    /**
     * Sugestões para o modal "Emitir ASO": médicos examinadores, coordenador, riscos e exames do PCMSO.
     *
     * @return array<string, mixed>
     */
    public function preparar(int $empresaId, int $resultadoId): array
    {
        $r = $this->resultadoRealizado($empresaId, $resultadoId);
        $data = CarbonImmutable::parse($r->data_realizacao);
        $perfil = $this->perfis->carregar((int) $r->feedback_id);
        $emitido = ExamesesmtAso::query()->where('examesesmt_id', $resultadoId)->first();

        $examinadores = DB::table('saude_medicos as m')
            ->join('saude_medicos_vinculos as v', 'v.saude_medico_id', '=', 'm.id')
            ->where('m.empresa_id', $empresaId)
            ->where('m.ativo', true)
            ->whereNull('m.deleted_at')
            ->whereNull('v.deleted_at')
            ->whereIn('v.papel', SaudeMedicoVinculo::PAPEIS)
            ->get(['m.id', 'm.nome', 'm.crm', 'm.crm_uf', 'm.email', 'v.empresa_exame_id', 'v.papel'])
            ->groupBy('id')
            ->map(function ($vinculos) use ($r) {
                $m = $vinculos->first();

                return [
                    'value' => (int) $m->id,
                    'label' => $m->nome . ' (CRM ' . $m->crm . '/' . $m->crm_uf . ')',
                    'da_clinica' => $vinculos->contains(fn ($v) => $v->empresa_exame_id && (int) $v->empresa_exame_id === (int) $r->empresa_exame_id),
                    'tem_email' => !empty($m->email),
                ];
            })
            ->sortByDesc('da_clinica')
            ->values()
            ->all();

        $previstos = $this->examesPrevistos->para($empresaId, $r->pcmso_id ? (int) $r->pcmso_id : null, (int) $r->feedback_id, $r->exame_tipo_id ? (int) $r->exame_tipo_id : null, $data);

        return [
            'examinadores' => $examinadores,
            'saude_medico_id' => $emitido?->saude_medico_id ?? ($examinadores[0]['value'] ?? null),
            'coordenador' => $this->medicos->coordenadorVigente($empresaId, $data),
            'riscos' => array_values($this->riscos->nomes($empresaId, $perfil?->riscoIds ?? [])),
            'exames' => $emitido?->exames ?? collect($previstos['exames'] ?? [])
                ->map(fn ($e) => ['label' => $e['label'], 'data' => $data->format('d/m/Y')])
                ->values()
                ->all(),
            'emitido' => $emitido ? ['emitido_em_br' => $emitido->emitido_em_br, 'assinatura' => $this->statusAssinatura($emitido)] : null,
        ];
    }

    /**
     * @param  array{saude_medico_id?: int, exames?: list<array{label?: string, data?: string}>}  $dados
     */
    public function emitir(int $empresaId, int $resultadoId, array $dados, User $autor): ExamesesmtAso
    {
        $r = $this->resultadoRealizado($empresaId, $resultadoId);
        $existente = ExamesesmtAso::query()->where('examesesmt_id', $resultadoId)->first();
        if ($existente && in_array($this->statusAssinatura($existente), [DocumentoParaAssinatura::STATUS_ENVIADO, DocumentoParaAssinatura::STATUS_EM_ASSINATURA, DocumentoParaAssinatura::STATUS_CONCLUIDO], true)) {
            throw ValidationException::withMessages(['aso' => 'Este ASO já foi enviado para assinatura e não pode ser reemitido.']);
        }

        $medico = DB::table('saude_medicos')
            ->where('empresa_id', $empresaId)
            ->where('id', (int) ($dados['saude_medico_id'] ?? 0))
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->first(['id', 'nome', 'crm', 'crm_uf', 'rqe']);
        if (!$medico) {
            throw ValidationException::withMessages(['saude_medico_id' => 'Escolha o médico examinador (cadastro em Médicos responsáveis).']);
        }

        $exames = collect((array) ($dados['exames'] ?? []))
            ->map(fn ($e) => [
                'label' => mb_substr(trim((string) ($e['label'] ?? '')), 0, 120),
                'data' => $this->dataBr($e['data'] ?? null),
            ])
            ->filter(fn ($e) => $e['label'] !== '')
            ->values();
        if ($exames->count() > self::MAX_EXAMES) {
            throw ValidationException::withMessages(['exames' => 'No máximo ' . self::MAX_EXAMES . ' exames complementares.']);
        }

        $data = CarbonImmutable::parse($r->data_realizacao);
        $coordenador = $this->medicos->coordenadorVigente($empresaId, $data);
        $perfil = $this->perfis->carregar((int) $r->feedback_id);
        // Função = cargo vigente (catálogo), ou o cargo gravado na admissão.
        $funcao = ($perfil?->vagaId ? DB::table('vagas')->where('id', $perfil->vagaId)->value('nome') : null)
            ?: DB::table('admissoes')->where('feedback_id', $r->feedback_id)->whereNull('deleted_at')->value('cargo');

        $aso = $existente ?? new ExamesesmtAso(['examesesmt_id' => $resultadoId]);
        $aso->fill([
            'empresa_id' => $empresaId,
            'saude_medico_id' => (int) $medico->id,
            'examinador' => ['nome' => $medico->nome, 'crm' => $medico->crm, 'crm_uf' => $medico->crm_uf, 'rqe' => $medico->rqe],
            'coordenador' => $coordenador ? ['nome' => $coordenador['nome'], 'crm' => $coordenador['crm_completo']] : null,
            'funcao' => $funcao ? mb_strtoupper(mb_substr((string) $funcao, 0, 150)) : null,
            'riscos' => array_values($this->riscos->nomes($empresaId, $perfil?->riscoIds ?? [])),
            'exames' => $exames->all(),
            'documento_assinatura_id' => null,
            'emitido_por' => $autor->id,
            'emitido_em' => now(),
        ]);
        $aso->save();

        $descricao = 'emitiu ASO (resultado #' . $resultadoId . ')';
        activity('saude_aso')
            ->causedBy($autor)
            ->withProperties(['empresa_id' => $empresaId, 'examesesmt_id' => $resultadoId, 'feedback_id' => (int) $r->feedback_id])
            ->tap(function ($activity) use ($descricao) {
                $activity->descricao = $descricao;
            })
            ->log($descricao);

        return $aso;
    }

    public function gerarPdf(int $empresaId, int $resultadoId, ?string $solicitante): PDF
    {
        $r = $this->resultadoRealizado($empresaId, $resultadoId);
        $aso = ExamesesmtAso::query()->where('examesesmt_id', $resultadoId)->first();
        if (!$aso) {
            throw ValidationException::withMessages(['aso' => 'Emita o ASO antes de gerar o PDF.']);
        }

        $feedback = FeedbackCurriculo::withoutGlobalScopes()->with(['Curriculo' => fn ($q) => $q->withoutGlobalScopes()])->find($r->feedback_id);
        $resultado = (array) json_decode((string) $r->resultado, true);

        $dados = [
            'dados_empresa' => Sistema::getEmpresaFilialMatriz($feedback->centro_custo_filial_id ?? null, $empresaId),
            'empregado' => [
                'nome' => (string) ($feedback?->Curriculo?->nome ?? ''),
                'cpf' => $this->formatarCpf($feedback?->Curriculo?->cpf),
                'funcao' => $aso->funcao,
            ],
            'tipo' => (string) $r->tipo,
            'data_exame_br' => CarbonImmutable::parse($r->data_realizacao)->format('d/m/Y'),
            'conclusao' => (string) ($resultado['result'] ?? ''),
            'trabalho_altura' => $resultado['trabalho_altura'] ?? null,
            'espaco_confinado' => $resultado['espacao_confinado'] ?? null,
            'riscos' => $aso->riscos,
            'exames' => $aso->exames,
            'examinador' => $aso->examinador,
            'coordenador' => $aso->coordenador,
            'emitido_em_br' => $aso->emitido_em_br,
        ];

        /** @var PDF $pdf */
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.saude-ocupacional.aso', ['dados' => $dados]);
        $pdf->setPaper('a4', 'portrait');

        return app(ComunicadoDispensaSemJustaCausaPdfService::class)->aplicarPadraoDocumento($pdf, $solicitante);
    }

    /**
     * Envia o ASO emitido para assinatura: primeiro o médico examinador, depois a ciência do empregado.
     *
     * @return array{documento_id: int, signatarios: int}
     */
    public function enviarParaAssinatura(int $empresaId, int $resultadoId, User $autor): array
    {
        if (!Sistema::assinaturaDigitalHabilitada($empresaId)) {
            throw ValidationException::withMessages(['aso' => 'Assinatura digital não está habilitada para esta empresa.']);
        }

        $aso = ExamesesmtAso::query()->where('examesesmt_id', $resultadoId)->where('empresa_id', $empresaId)->first();
        if (!$aso) {
            throw ValidationException::withMessages(['aso' => 'Emita o ASO antes de enviar para assinatura.']);
        }
        if (in_array($this->statusAssinatura($aso), [DocumentoParaAssinatura::STATUS_ENVIADO, DocumentoParaAssinatura::STATUS_EM_ASSINATURA, DocumentoParaAssinatura::STATUS_CONCLUIDO], true)) {
            throw ValidationException::withMessages(['aso' => 'Este ASO já foi enviado para assinatura.']);
        }

        $r = $this->resultadoRealizado($empresaId, $resultadoId);
        $medico = DB::table('saude_medicos')->where('empresa_id', $empresaId)->where('id', $aso->saude_medico_id)->first(['nome', 'email']);
        $curriculo = DB::table('feedback_curriculos as fc')->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')->where('fc.id', $r->feedback_id)->first(['c.nome', 'c.email', 'c.cpf']);

        if (empty($medico?->email)) {
            throw ValidationException::withMessages(['aso' => 'Cadastre o e-mail do médico examinador (Médicos responsáveis) para ele assinar.']);
        }
        if (empty($curriculo?->email)) {
            throw ValidationException::withMessages(['aso' => 'O colaborador não tem e-mail cadastrado para dar ciência.']);
        }

        $pdf = $this->gerarPdf($empresaId, $resultadoId, $autor->nome)->output();
        $documento = app(AssinaturaDigitalService::class)->criarEnvio(
            $empresaId,
            self::TIPO_DOCUMENTO_ASSINATURA,
            Examesesmt::class,
            $resultadoId,
            $autor->id,
            [
                ['user_id' => null, 'email' => mb_strtolower(trim($medico->email)), 'nome' => $medico->nome, 'cpf' => null],
                ['user_id' => null, 'email' => mb_strtolower(trim($curriculo->email)), 'nome' => $curriculo->nome, 'cpf' => $this->formatarCpf($curriculo->cpf)],
            ],
            'sequencial',
            $pdf,
            'aso_' . Str::slug($curriculo->nome) . '.pdf'
        );

        $aso->documento_assinatura_id = $documento->id;
        $aso->save();

        return ['documento_id' => (int) $documento->id, 'signatarios' => 2];
    }

    public function statusAssinatura(ExamesesmtAso $aso): ?string
    {
        if (!$aso->documento_assinatura_id) {
            return null;
        }

        return DB::table((new DocumentoParaAssinatura())->getTable())->where('id', $aso->documento_assinatura_id)->value('status');
    }

    /**
     * Resultado realizado da empresa, com tipo, PCMSO e clínica do encaminhamento.
     */
    private function resultadoRealizado(int $empresaId, int $resultadoId): object
    {
        $r = DB::table('examesesmts as e')
            ->join('exame_funcionarios as ef', 'ef.id', '=', 'e.exame_funcionario_id')
            ->leftJoin('exame_tipos as t', 't.id', '=', 'ef.exame_tipo_id')
            ->where('e.id', $resultadoId)
            ->where('e.empresa_id', $empresaId)
            ->first(['e.id', 'e.feedback_id', 'e.exame_realizado', 'e.resultado', 'e.data_realizacao', 'ef.exame_tipo_id', 'ef.pcmso_id', 'ef.empresa_exame_id', 't.label as tipo']);

        if (!$r) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel('Resultado de exame', [$resultadoId]);
        }
        if (!$r->exame_realizado || !$r->data_realizacao) {
            throw ValidationException::withMessages(['aso' => 'O ASO só pode ser emitido para exame realizado.']);
        }

        return $r;
    }

    private function formatarCpf(?string $cpf): ?string
    {
        $d = preg_replace('/\D/', '', (string) $cpf);

        return strlen($d) === 11 ? substr($d, 0, 3) . '.' . substr($d, 3, 3) . '.' . substr($d, 6, 3) . '-' . substr($d, 9, 2) : null;
    }

    private function dataBr(mixed $valor): ?string
    {
        if (!$valor) {
            return null;
        }

        try {
            return CarbonImmutable::parse((new \MasterTag\DataHora((string) $valor))->dataInsert())->format('d/m/Y');
        } catch (\Throwable) {
            return null;
        }
    }
}
