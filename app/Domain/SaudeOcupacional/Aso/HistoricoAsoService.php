<?php

namespace App\Domain\SaudeOcupacional\Aso;

use App\Domain\SaudeOcupacional\Seguranca\AuditoriaAcessoSaude;
use App\Domain\SaudeOcupacional\Seguranca\PerfilAcessoSaude;
use App\Models\DocumentoParaAssinatura;
use App\Models\ExamesesmtAso;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Histórico de ASO de um colaborador: todos os encaminhamentos e resultados (inclusive os antigos),
 * do mais recente para o mais antigo. Lê o que já existe — nada é reprocessado.
 *
 * RH vê a conclusão (apto/inapto), aptidões NR-35/NR-33, datas, clínica e o ASO emitido.
 * Observações, "pendências — quais" e anexos são do perfil Saúde, e a leitura fica auditada.
 */
class HistoricoAsoService
{
    public const CAMPOS_SENSIVEIS = ['observacoes', 'pendencias_quais', 'anexos'];

    public function __construct(
        private readonly PerfilAcessoSaude $perfil,
        private readonly AuditoriaAcessoSaude $auditoria
    ) {
    }

    /**
     * Colaboradores da empresa (ativos e desligados) por nome, para abrir o histórico.
     *
     * @return list<array{feedback_id: int, nome: string, cargo: string|null, desligado: bool}>
     */
    public function buscar(int $empresaId, string $termo): array
    {
        $termo = trim($termo);
        if (mb_strlen($termo) < 3) {
            return [];
        }

        $linhas = DB::table('feedback_curriculos as fc')
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->join('admissoes as a', 'a.feedback_id', '=', 'fc.id')
            ->where('fc.empresa_id', $empresaId)
            ->whereNull('fc.deleted_at')
            ->whereNull('a.deleted_at')
            ->where('c.nome', 'like', '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $termo) . '%')
            ->orderBy('c.nome')
            ->limit(15)
            ->get(['fc.id', 'c.nome', 'a.cargo'])
            ->unique('id');

        $desligados = DB::table('demissaos')->whereIn('feedback_id', $linhas->pluck('id')->all() ?: [0])->pluck('feedback_id')->map(fn ($id) => (int) $id)->all();

        return $linhas
            ->map(fn ($l) => [
                'feedback_id' => (int) $l->id,
                'nome' => (string) $l->nome,
                'cargo' => $l->cargo ? mb_strtoupper((string) $l->cargo) : null,
                'desligado' => in_array((int) $l->id, $desligados, true),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{colaborador: array<string, mixed>, itens: list<array<string, mixed>>, ve_dados_sensiveis: bool}
     */
    public function historico(int $empresaId, int $feedbackId, User $usuario): array
    {
        $colaborador = DB::table('feedback_curriculos as fc')
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->leftJoin('admissoes as a', fn ($j) => $j->on('a.feedback_id', '=', 'fc.id')->whereNull('a.deleted_at'))
            ->where('fc.empresa_id', $empresaId)
            ->where('fc.id', $feedbackId)
            ->first(['fc.id', 'c.nome', 'c.cpf', 'a.cargo', 'a.data_admissao']);
        if (!$colaborador) {
            throw (new ModelNotFoundException())->setModel('Colaborador', [$feedbackId]);
        }

        $sensiveis = $this->perfil->podeVerDadosSensiveis($usuario);
        $temAso = Schema::hasTable(ExamesesmtAso::TABELA);

        $linhas = DB::table('exame_funcionarios as ef')
            ->leftJoin('examesesmts as e', 'e.exame_funcionario_id', '=', 'ef.id')
            ->leftJoin('exame_tipos as t', 't.id', '=', 'ef.exame_tipo_id')
            ->leftJoin('empresa_exames as cl', 'cl.id', '=', 'ef.empresa_exame_id')
            ->leftJoin('pcmsos as p', 'p.id', '=', 'ef.pcmso_id')
            ->when($temAso, fn ($q) => $q->leftJoin('examesesmts_aso as aso', 'aso.examesesmt_id', '=', 'e.id'))
            ->where('ef.feedback_id', $feedbackId)
            ->where('ef.empresa_id', $empresaId)
            ->orderByDesc(DB::raw('COALESCE(e.data_realizacao, ef.encaminhamento_data)'))
            ->orderByDesc('ef.id')
            ->get(array_merge([
                'ef.id as encaminhamento_id', 'ef.encaminhamento_data', 'ef.pcmso',
                't.label as tipo', 'cl.nome as clinica', 'p.label as pcmso_label',
                'e.id as resultado_id', 'e.exame_realizado', 'e.resultado', 'e.data_realizacao', 'e.data_vencimento', 'e.atual',
            ], $temAso ? ['aso.id as aso_id', 'aso.emitido_em', 'aso.documento_assinatura_id'] : []));

        $anexos = DB::table('controle_exame_sesmt_anexos')
            ->whereIn('examesesmt_id', $linhas->pluck('resultado_id')->filter()->all() ?: [0])
            ->groupBy('examesesmt_id')
            ->selectRaw('examesesmt_id, COUNT(*) as total')
            ->pluck('total', 'examesesmt_id');

        $assinaturas = DB::table((new DocumentoParaAssinatura())->getTable())
            ->whereIn('id', $temAso ? ($linhas->pluck('documento_assinatura_id')->filter()->all() ?: [0]) : [0])
            ->pluck('status', 'id');

        $itens = $linhas->map(function ($l) use ($sensiveis, $anexos, $assinaturas, $temAso) {
            $resultado = $l->resultado ? (array) json_decode((string) $l->resultado, true) : [];
            $realizado = (bool) $l->exame_realizado;

            $item = [
                'encaminhamento_id' => (int) $l->encaminhamento_id,
                'resultado_id' => $l->resultado_id ? (int) $l->resultado_id : null,
                'tipo' => $l->tipo,
                'encaminhado_em_br' => $this->data($l->encaminhamento_data),
                'clinica' => $l->clinica,
                'pcmso' => $l->pcmso ? $l->pcmso_label : null,
                'situacao' => !$l->resultado_id ? 'aguardando_resultado' : ($realizado ? 'realizado' : 'nao_realizado'),
                'realizado_em_br' => $realizado ? $this->data($l->data_realizacao) : null,
                'conclusao' => $realizado ? ($resultado['result'] ?? null) : null,
                'trabalho_altura' => $realizado ? ($resultado['trabalho_altura'] ?? null) : null,
                'espaco_confinado' => $realizado ? ($resultado['espacao_confinado'] ?? null) : null,
                'vencimento_br' => $this->data($l->data_vencimento),
                'atual' => (bool) $l->atual,
                'aso' => $temAso && $l->aso_id ? [
                    'emitido_em_br' => $l->emitido_em ? CarbonImmutable::parse($l->emitido_em)->format('d/m/Y H:i') : null,
                    'assinatura' => $l->documento_assinatura_id ? ($assinaturas[$l->documento_assinatura_id] ?? null) : null,
                ] : null,
                'anexos' => (int) ($anexos[$l->resultado_id] ?? 0),
            ];

            if ($sensiveis) {
                $item['observacoes'] = $resultado['observacoes'] ?? null;
                $item['pendencias_quais'] = $resultado['pendencias_quais'] ?? null;
            } else {
                // RH: sabe que há anexos, mas não abre (laudos e resultados são sigilo médico).
                $item['anexos'] = $item['anexos'] > 0 ? 'restrito' : 0;
            }

            return $item;
        })->values()->all();

        if ($sensiveis && $itens !== []) {
            $this->auditoria->registrar($usuario, AuditoriaAcessoSaude::ACAO_VISUALIZAR, 'historico_aso', $feedbackId, 'visualizou histórico de ASO com dados sensíveis');
        }

        return [
            'colaborador' => [
                'feedback_id' => (int) $colaborador->id,
                'nome' => (string) $colaborador->nome,
                'cpf' => $this->mascararCpf($colaborador->cpf),
                'cargo' => $colaborador->cargo ? mb_strtoupper((string) $colaborador->cargo) : null,
                'admissao_br' => $this->data($colaborador->data_admissao),
                'desligado' => DB::table('demissaos')->where('feedback_id', $feedbackId)->exists(),
            ],
            'itens' => $itens,
            've_dados_sensiveis' => $sensiveis,
        ];
    }

    public function mascararCpf(?string $cpf): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $cpf);
        if (strlen($digitos) !== 11) {
            return null;
        }

        return substr($digitos, 0, 3) . '.***.***-' . substr($digitos, 9, 2);
    }

    private function data(mixed $valor): ?string
    {
        if (!$valor || str_starts_with((string) $valor, '0000')) {
            return null;
        }

        try {
            return CarbonImmutable::parse($valor)->format('d/m/Y');
        } catch (\Throwable) {
            return null;
        }
    }
}
