<?php

namespace App\Domain\SaudeOcupacional\Conformidade;

use App\Domain\SaudeOcupacional\Colaboradores\ColaboradoresAtivosQuery;
use App\Jobs\SaudeOcupacional\JobEncaminhamentoLote;
use App\Models\ExameTipo;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MasterTag\DataHora;

/**
 * Encaminhamento de exame em lote (Conformidade → "Encaminhar selecionados").
 * Mesmo caminho do encaminhamento individual com PCMSO; processado em fila.
 *
 * Proteções contra duplicidade:
 * - não encaminha quem já tem encaminhamento sem resultado (por padrão);
 * - trava em cache por colaborador + tipo (10 min) contra duplo clique / dois usuários ao mesmo tempo.
 */
class EncaminhamentoLoteService
{
    public const MAX_COLABORADORES = 200;

    public const TAMANHO_JOB = 25;

    public const TTL_TRAVA_SEGUNDOS = 600;

    /**
     * @param  array<string, mixed>  $dados
     * @return array{enfileirados: int, ignorados: array{com_encaminhamento_aberto: int, fora_da_lista_de_ativos: int, em_processamento: int}}
     */
    public function enfileirar(int $empresaId, array $dados, User $autor): array
    {
        $ids = collect((array) ($dados['feedback_ids'] ?? []))->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        if ($ids === [] || count($ids) > self::MAX_COLABORADORES) {
            throw ValidationException::withMessages(['feedback_ids' => 'Selecione de 1 a ' . self::MAX_COLABORADORES . ' colaboradores.']);
        }

        $parametros = $this->validarParametros($empresaId, $dados);

        $ativos = (new ColaboradoresAtivosQuery())->daEmpresa($empresaId)
            ->whereIn('fc.id', $ids)
            ->distinct()
            ->pluck('fc.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $comAberto = filter_var($dados['ignorar_com_encaminhamento_aberto'] ?? true, FILTER_VALIDATE_BOOLEAN)
            ? $this->comEncaminhamentoAberto($empresaId, $ativos)
            : [];

        $liberados = [];
        $emProcessamento = 0;
        foreach (array_diff($ativos, $comAberto) as $feedbackId) {
            if (Cache::add($this->chaveTrava($empresaId, $feedbackId, $parametros['exame_tipo_id']), 1, self::TTL_TRAVA_SEGUNDOS)) {
                $liberados[] = $feedbackId;
            } else {
                $emProcessamento++;
            }
        }

        foreach (array_chunk($liberados, self::TAMANHO_JOB) as $lote) {
            JobEncaminhamentoLote::dispatch($empresaId, (int) $autor->id, $lote, $parametros);
        }

        activity('saude_encaminhamento_lote')
            ->causedBy($autor)
            ->withProperties([
                'empresa_id' => $empresaId,
                'enfileirados' => count($liberados),
                'exame_tipo_id' => $parametros['exame_tipo_id'],
                'pcmso_id' => $parametros['pcmso_id'],
                'empresa_exame_id' => $parametros['empresa_exame_id'],
            ])
            ->tap(function ($activity) use ($liberados) {
                $activity->descricao = 'encaminhou exame em lote para ' . count($liberados) . ' colaborador(es)';
            })
            ->log('encaminhou exame em lote para ' . count($liberados) . ' colaborador(es)');

        return [
            'enfileirados' => count($liberados),
            'ignorados' => [
                'com_encaminhamento_aberto' => count($comAberto),
                'fora_da_lista_de_ativos' => count($ids) - count($ativos),
                'em_processamento' => $emProcessamento,
            ],
        ];
    }

    public function chaveTrava(int $empresaId, int $feedbackId, int $exameTipoId): string
    {
        return "saude_ocupacional:encaminhar:{$empresaId}:{$feedbackId}:{$exameTipoId}";
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array{exame_tipo_id: int, empresa_exame_id: int, pcmso_id: int, formulario_id: int, data_realizacao: string, envia_email: bool, envia_whatsapp: bool}
     */
    private function validarParametros(int $empresaId, array $dados): array
    {
        $tipo = ExameTipo::query()->where('ativo', true)->find((int) ($dados['exame_tipo_id'] ?? 0));
        if (!$tipo) {
            throw ValidationException::withMessages(['exame_tipo_id' => 'Escolha o tipo de exame.']);
        }
        if ($tipo->label === ExameTipo::LABEL_DEMISSIONAL) {
            throw ValidationException::withMessages(['exame_tipo_id' => 'O demissional é encaminhado individualmente, na demissão.']);
        }

        $clinica = (int) ($dados['empresa_exame_id'] ?? 0);
        if (!DB::table('empresa_exames')->where('empresa_id', $empresaId)->where('ativo', true)->where('id', $clinica)->exists()) {
            throw ValidationException::withMessages(['empresa_exame_id' => 'Escolha uma clínica ativa da empresa.']);
        }

        $pcmso = (int) ($dados['pcmso_id'] ?? 0);
        if (!DB::table('pcmsos')->where('empresa_id', $empresaId)->where('ativo', true)->where('id', $pcmso)->exists()) {
            throw ValidationException::withMessages(['pcmso_id' => 'Escolha o PCMSO.']);
        }

        $formulario = DB::table('formularios')->where('empresa_id', $empresaId)->where('titulo', 'Exames')->value('id');
        if (!$formulario) {
            throw ValidationException::withMessages(['formulario' => 'Formulário "Exames" da empresa não encontrado.']);
        }

        try {
            $data = CarbonImmutable::parse((new DataHora((string) ($dados['data_realizacao'] ?? '')))->dataInsert());
        } catch (\Throwable) {
            throw ValidationException::withMessages(['data_realizacao' => 'Informe a data de realização do exame.']);
        }
        if (empty($dados['data_realizacao']) || $data->lessThan(CarbonImmutable::today()->subDays(30))) {
            throw ValidationException::withMessages(['data_realizacao' => 'Informe uma data de realização válida (não muito antiga).']);
        }

        return [
            'exame_tipo_id' => (int) $tipo->id,
            'empresa_exame_id' => $clinica,
            'pcmso_id' => $pcmso,
            'formulario_id' => (int) $formulario,
            'data_realizacao' => $data->toDateString(),
            'envia_email' => filter_var($dados['envia_email'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'envia_whatsapp' => filter_var($dados['envia_whatsapp'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function comEncaminhamentoAberto(int $empresaId, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return DB::table('exame_funcionarios as ef')
            ->whereIn('ef.feedback_id', $ids)
            ->where('ef.empresa_id', $empresaId)
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')
                    ->from('examesesmts as e')
                    ->whereColumn('e.exame_funcionario_id', 'ef.id')
                    ->where('e.exame_realizado', true);
            })
            ->distinct()
            ->pluck('ef.feedback_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
