<?php

namespace App\Services\ControleExames;

use App\Jobs\ControleExames\JobRecalcularVencimentoAso;
use App\Models\EmpresaExameTipoPeriodicidade;
use App\Models\EmpresaExameTipoPeriodicidadeHistorico;
use App\Models\ExameTipo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MasterTag\DataHora;

class AsoPeriodicidadeService
{
    public const UNIDADE_ANOS = EmpresaExameTipoPeriodicidade::UNIDADE_ANOS;

    public const UNIDADE_DIAS = EmpresaExameTipoPeriodicidade::UNIDADE_DIAS;

    public const MODO_RETROATIVO = 'retroativo';

    public const MODO_FUTURO = 'futuro';

    public const MODO_A_PARTIR_DE = 'a_partir_de';

    public const DEFAULT_UNIDADE = self::UNIDADE_ANOS;

    public const DEFAULT_VALOR = 1;

    public const LABEL_ADMISSIONAL = 'Admissional';

    public const LABEL_DEMISSIONAL = ExameTipo::LABEL_DEMISSIONAL;

    /** @var list<int>|null */
    private ?array $idsSemVencimento = null;

    public function ehSemVencimento(?int $exameTipoId): bool
    {
        if (!$exameTipoId) {
            return false;
        }

        $this->idsSemVencimento ??= ExameTipo::query()
            ->where('label', self::LABEL_DEMISSIONAL)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        return in_array((int) $exameTipoId, $this->idsSemVencimento, true);
    }

    /**
     * @return array{unidade: string, valor: int}|null null = tipo sem vencimento (ex.: Demissional)
     */
    public function resolverConfig(int $empresaId, ?int $exameTipoId): ?array
    {
        if ($this->ehSemVencimento($exameTipoId)) {
            return null;
        }

        if (!$exameTipoId) {
            return [
                'unidade' => self::DEFAULT_UNIDADE,
                'valor' => self::DEFAULT_VALOR,
            ];
        }

        $row = EmpresaExameTipoPeriodicidade::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('exame_tipo_id', $exameTipoId)
            ->first(['unidade', 'valor']);

        if (!$row) {
            return [
                'unidade' => self::DEFAULT_UNIDADE,
                'valor' => self::DEFAULT_VALOR,
            ];
        }

        return [
            'unidade' => $row->unidade,
            'valor' => (int) $row->valor,
        ];
    }

    /**
     * @param  array{unidade: string, valor: int}|null  $config
     * @return string|null null quando o tipo não possui vencimento (Demissional)
     */
    public function calcularDataVencimento(
        $dataRealizacao,
        int $empresaId,
        ?int $exameTipoId,
        ?array $config = null
    ): ?string {
        if ($this->ehSemVencimento($exameTipoId)) {
            return null;
        }

        $config ??= $this->resolverConfig($empresaId, $exameTipoId);
        if ($config === null) {
            return null;
        }

        $data = new DataHora($dataRealizacao);

        if (($config['unidade'] ?? self::DEFAULT_UNIDADE) === self::UNIDADE_DIAS) {
            $data->addDia(max(1, (int) $config['valor']));

            return $data->dataCompleta();
        }

        $data->addAno(max(1, (int) ($config['valor'] ?? self::DEFAULT_VALOR)));
        $data->subtrairDia(1);

        return $data->dataCompleta();
    }

    public function calcularDataVencimentoAdmissional($dataRealizacao, int $empresaId): string
    {
        $tipoId = ExameTipo::query()
            ->where('label', self::LABEL_ADMISSIONAL)
            ->value('id');

        return $this->calcularDataVencimento($dataRealizacao, $empresaId, $tipoId ? (int) $tipoId : null);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listarParaEmpresa(int $empresaId): array
    {
        $configs = EmpresaExameTipoPeriodicidade::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->get(['exame_tipo_id', 'unidade', 'valor'])
            ->keyBy('exame_tipo_id');

        return ExameTipo::query()
            ->where('ativo', true)
            ->orderBy('id')
            ->get(['id', 'label', 'ativo'])
            ->map(function (ExameTipo $tipo) use ($configs) {
                $semVencimento = $tipo->label === self::LABEL_DEMISSIONAL;
                $cfg = $configs->get($tipo->id);

                return [
                    'exame_tipo_id' => $tipo->id,
                    'label' => $tipo->label,
                    'sem_vencimento' => $semVencimento,
                    'unidade' => $semVencimento ? null : ($cfg->unidade ?? self::DEFAULT_UNIDADE),
                    'valor' => $semVencimento ? null : ($cfg ? (int) $cfg->valor : self::DEFAULT_VALOR),
                    'configurado' => $semVencimento ? false : (bool) $cfg,
                ];
            })
            ->values()
            ->all();
    }

    public function listarHistorico(int $empresaId, int $porPagina = 15): LengthAwarePaginator
    {
        return EmpresaExameTipoPeriodicidadeHistorico::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->with(['usuario:id,nome'])
            ->orderByDesc('id')
            ->paginate($porPagina);
    }

    /**
     * @param  list<array{exame_tipo_id:int,unidade:string,valor:int}>  $itens
     * @return array{
     *   tipos_alterados: list<array<string,mixed>>,
     *   job_despachado: bool,
     *   modo_aplicacao: string|null,
     *   mensagem: string
     * }
     */
    public function salvar(
        int $empresaId,
        array $itens,
        ?string $modoAplicacao,
        ?string $dataAplicacao,
        User $autor
    ): array {
        $itensNormalizados = $this->normalizarItens($itens);
        $labels = ExameTipo::query()
            ->whereIn('id', collect($itensNormalizados)->pluck('exame_tipo_id'))
            ->pluck('label', 'id');

        // Demissional não possui vencimento — ignora no save e remove config residual.
        $itensNormalizados = array_values(array_filter(
            $itensNormalizados,
            function (array $item) use ($labels) {
                $label = $labels[$item['exame_tipo_id']] ?? null;

                return $label !== self::LABEL_DEMISSIONAL;
            }
        ));

        $idsDemissional = ExameTipo::query()
            ->where('label', self::LABEL_DEMISSIONAL)
            ->pluck('id')
            ->all();
        if ($idsDemissional !== []) {
            EmpresaExameTipoPeriodicidade::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->whereIn('exame_tipo_id', $idsDemissional)
                ->delete();

            DB::table('examesesmts as e')
                ->join('exame_funcionarios as ef', 'ef.id', '=', 'e.exame_funcionario_id')
                ->where('e.empresa_id', $empresaId)
                ->where('e.atual', 1)
                ->whereIn('ef.exame_tipo_id', $idsDemissional)
                ->whereNotNull('e.data_vencimento')
                ->update([
                    'e.data_vencimento' => null,
                    'e.vencido' => 0,
                    'e.updated_at' => now(),
                ]);
        }

        $atuais = EmpresaExameTipoPeriodicidade::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->get()
            ->keyBy('exame_tipo_id');

        $alteracoes = [];
        foreach ($itensNormalizados as $item) {
            $antes = $atuais->get($item['exame_tipo_id']);
            $unidadeAntes = $antes->unidade ?? self::DEFAULT_UNIDADE;
            $valorAntes = $antes ? (int) $antes->valor : self::DEFAULT_VALOR;

            // Sem registro prévio também conta (permite gravar default explícito e recalcular −1 dia).
            if ($antes && $unidadeAntes === $item['unidade'] && $valorAntes === $item['valor']) {
                continue;
            }

            $alteracoes[] = [
                'exame_tipo_id' => $item['exame_tipo_id'],
                'label' => $labels[$item['exame_tipo_id']] ?? ('Tipo #' . $item['exame_tipo_id']),
                'unidade_antes' => $antes ? $unidadeAntes : null,
                'valor_antes' => $antes ? $valorAntes : null,
                'unidade_depois' => $item['unidade'],
                'valor_depois' => $item['valor'],
            ];
        }

        if ($alteracoes === []) {
            return [
                'tipos_alterados' => [],
                'job_despachado' => false,
                'modo_aplicacao' => null,
                'mensagem' => 'Nenhuma periodicidade foi alterada.',
            ];
        }

        $modo = $this->validarModo($modoAplicacao, $dataAplicacao);
        $dataAplicacaoInsert = null;
        if ($modo === self::MODO_A_PARTIR_DE) {
            $dataAplicacaoInsert = (new DataHora($dataAplicacao))->dataInsert();
        }

        $jobDespachado = false;

        DB::transaction(function () use (
            $empresaId,
            $itensNormalizados,
            $alteracoes,
            $modo,
            $dataAplicacaoInsert,
            $autor,
            &$jobDespachado
        ) {
            foreach ($itensNormalizados as $item) {
                EmpresaExameTipoPeriodicidade::withoutGlobalScopes()->updateOrCreate(
                    [
                        'empresa_id' => $empresaId,
                        'exame_tipo_id' => $item['exame_tipo_id'],
                    ],
                    [
                        'unidade' => $item['unidade'],
                        'valor' => $item['valor'],
                    ]
                );
            }

            $deveDespachar = in_array($modo, [self::MODO_RETROATIVO, self::MODO_A_PARTIR_DE], true);
            $descricao = $this->montarDescricao($alteracoes, $modo, $dataAplicacaoInsert);

            EmpresaExameTipoPeriodicidadeHistorico::withoutGlobalScopes()->create([
                'empresa_id' => $empresaId,
                'user_id' => $autor->id,
                'modo_aplicacao' => $modo,
                'data_aplicacao' => $dataAplicacaoInsert,
                'job_despachado' => $deveDespachar,
                'alteracoes' => $alteracoes,
                'descricao' => $descricao,
            ]);

            // activity_log do MyBP exige coluna custom `descricao` (NOT NULL).
            activity('aso_periodicidade')
                ->causedBy($autor)
                ->withProperties([
                    'empresa_id' => $empresaId,
                    'modo_aplicacao' => $modo,
                    'data_aplicacao' => $dataAplicacaoInsert,
                    'job_despachado' => $deveDespachar,
                    'alteracoes' => $alteracoes,
                ])
                ->tap(function ($activity) use ($descricao) {
                    $activity->descricao = $descricao;
                })
                ->log($descricao);

            if ($deveDespachar) {
                $tiposJob = array_map(static function (array $alt) {
                    return [
                        'exame_tipo_id' => $alt['exame_tipo_id'],
                        'unidade' => $alt['unidade_depois'],
                        'valor' => $alt['valor_depois'],
                    ];
                }, $alteracoes);

                JobRecalcularVencimentoAso::dispatch(
                    $empresaId,
                    $tiposJob,
                    $modo === self::MODO_A_PARTIR_DE ? $dataAplicacaoInsert : null
                );
                $jobDespachado = true;
            }
        });

        return [
            'tipos_alterados' => $alteracoes,
            'job_despachado' => $jobDespachado,
            'modo_aplicacao' => $modo,
            'mensagem' => $this->mensagemFeedback($modo, $jobDespachado, $dataAplicacaoInsert),
        ];
    }

    /**
     * @param  list<array{exame_tipo_id:int,unidade:string,valor:int}>  $itens
     * @return list<array{exame_tipo_id:int,unidade:string,valor:int}>
     */
    private function normalizarItens(array $itens): array
    {
        $normalizados = [];
        foreach ($itens as $item) {
            $tipoId = (int) ($item['exame_tipo_id'] ?? 0);
            $unidade = (string) ($item['unidade'] ?? '');
            $valor = (int) ($item['valor'] ?? 0);

            if ($tipoId <= 0) {
                throw ValidationException::withMessages([
                    'itens' => 'Tipo de exame inválido.',
                ]);
            }

            if (!in_array($unidade, [self::UNIDADE_ANOS, self::UNIDADE_DIAS], true)) {
                throw ValidationException::withMessages([
                    'itens' => 'Unidade de periodicidade inválida.',
                ]);
            }

            if ($valor < 1) {
                throw ValidationException::withMessages([
                    'itens' => 'Valor de periodicidade deve ser maior que zero.',
                ]);
            }

            if ($unidade === self::UNIDADE_ANOS && $valor > 10) {
                throw ValidationException::withMessages([
                    'itens' => 'Periodicidade em anos deve ser no máximo 10.',
                ]);
            }

            if ($unidade === self::UNIDADE_DIAS && $valor > 3650) {
                throw ValidationException::withMessages([
                    'itens' => 'Periodicidade em dias deve ser no máximo 3650.',
                ]);
            }

            $normalizados[] = [
                'exame_tipo_id' => $tipoId,
                'unidade' => $unidade,
                'valor' => $valor,
            ];
        }

        return $normalizados;
    }

    private function validarModo(?string $modoAplicacao, ?string $dataAplicacao): string
    {
        $modo = (string) $modoAplicacao;
        if (!in_array($modo, [self::MODO_RETROATIVO, self::MODO_FUTURO, self::MODO_A_PARTIR_DE], true)) {
            throw ValidationException::withMessages([
                'modo_aplicacao' => 'Selecione como aplicar a alteração (retroativo, futuros ou a partir de uma data).',
            ]);
        }

        if ($modo === self::MODO_A_PARTIR_DE) {
            if (!$dataAplicacao) {
                throw ValidationException::withMessages([
                    'data_aplicacao' => 'Informe a data a partir da qual o recálculo deve valer.',
                ]);
            }

            try {
                (new DataHora($dataAplicacao))->dataInsert();
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'data_aplicacao' => 'Data de aplicação inválida.',
                ]);
            }
        }

        return $modo;
    }

    /**
     * @param  list<array<string,mixed>>  $alteracoes
     */
    private function montarDescricao(array $alteracoes, string $modo, ?string $dataAplicacaoInsert): string
    {
        $partes = array_map(static function (array $alt) {
            $antes = ($alt['unidade_antes'] === null)
                ? 'padrão'
                : sprintf('%s %d', $alt['unidade_antes'], $alt['valor_antes']);

            return sprintf(
                '%s: %s→%s %d',
                $alt['label'],
                $antes,
                $alt['unidade_depois'],
                $alt['valor_depois']
            );
        }, $alteracoes);

        $sufixo = $modo;
        if ($modo === self::MODO_A_PARTIR_DE && $dataAplicacaoInsert) {
            $sufixo .= ' ' . (new DataHora($dataAplicacaoInsert))->dataCompleta();
        }

        return 'alterou periodicidade ASO (' . implode('; ', $partes) . '; modo: ' . $sufixo . ')';
    }

    private function mensagemFeedback(string $modo, bool $jobDespachado, ?string $dataAplicacaoInsert): string
    {
        if ($modo === self::MODO_FUTURO) {
            return 'Periodicidade salva. Vale para novos ASOs.';
        }

        if ($modo === self::MODO_A_PARTIR_DE) {
            $dataBr = $dataAplicacaoInsert
                ? (new DataHora($dataAplicacaoInsert))->dataCompleta()
                : '';

            return $jobDespachado
                ? "Periodicidade salva. Recálculo a partir de {$dataBr} enfileirado."
                : 'Periodicidade salva.';
        }

        return $jobDespachado
            ? 'Periodicidade salva. Recálculo de todos os ASOs atuais enfileirado.'
            : 'Periodicidade salva.';
    }
}
