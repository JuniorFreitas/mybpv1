<?php

namespace App\Domain\SaudeOcupacional\Pgr;

use App\Domain\SaudeOcupacional\Colaboradores\ColaboradoresAtivosQuery;
use App\Domain\SaudeOcupacional\Painel\PainelCache;
use App\Domain\SaudeOcupacional\Regras\ComparadorCenariosVencimento;
use App\Domain\SaudeOcupacional\Regras\RegrasAtivasRepositorio;
use App\Domain\SaudeOcupacional\Regras\RegrasEmpresaService;
use App\Domain\SaudeOcupacional\Regras\TravaLegalNr7;
use App\Jobs\ControleExames\JobRecalcularVencimentoAso;
use App\Models\PgrInventario;
use App\Models\PgrInventarioCargo;
use App\Models\PgrInventarioRisco;
use App\Models\User;
use App\Services\ControleExames\AsoPeriodicidadeService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MasterTag\DataHora;

/**
 * PGR básico (NR-1): inventário de riscos por cargo em versões (rascunho → vigente → arquivado).
 * A versão vigente define quem está exposto a risco (regra NR-7 de periódico anual).
 */
class PgrInventarioService
{
    public function __construct(
        private readonly PgrVigenteRepositorio $vigente,
        private readonly RegrasAtivasRepositorio $regrasAtivas,
        private readonly RiscosCatalogoService $riscos,
        private readonly ComparadorCenariosVencimento $comparador,
        private readonly RegrasEmpresaService $regrasEmpresa,
        private readonly AsoPeriodicidadeService $periodicidade
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listarVersoes(int $empresaId): array
    {
        $totais = DB::table('pgr_inventarios_cargos as c')
            ->leftJoin('pgr_inventarios_riscos as r', 'r.pgr_inventario_cargo_id', '=', 'c.id')
            ->join('pgr_inventarios as i', 'i.id', '=', 'c.pgr_inventario_id')
            ->where('i.empresa_id', $empresaId)
            ->groupBy('c.pgr_inventario_id')
            ->selectRaw('c.pgr_inventario_id, COUNT(DISTINCT c.id) as cargos, COUNT(r.id) as riscos')
            ->get()
            ->keyBy('pgr_inventario_id');

        return PgrInventario::query()
            ->where('empresa_id', $empresaId)
            ->orderByDesc('versao')
            ->get()
            ->map(fn (PgrInventario $i) => [
                'id' => $i->id,
                'versao' => $i->versao,
                'status' => $i->status,
                'vigencia_inicio_br' => $i->vigencia_inicio_br,
                'vigencia_fim_br' => $i->vigencia_fim_br,
                'responsavel_tecnico' => $i->responsavel_tecnico,
                'responsavel_registro' => $i->responsavel_registro,
                'observacao' => $i->observacao,
                'publicado_em_br' => $i->publicado_em_br,
                'total_cargos' => (int) ($totais[$i->id]->cargos ?? 0),
                'total_riscos' => (int) ($totais[$i->id]->riscos ?? 0),
            ])
            ->all();
    }

    /**
     * Cargos ativos da empresa com os riscos desta versão e quantos colaboradores ativos têm o cargo.
     *
     * @return array<string, mixed>
     */
    public function detalhe(int $empresaId, int $inventarioId): array
    {
        $inventario = $this->buscar($empresaId, $inventarioId);
        $catalogo = collect($this->riscos->listar($empresaId))->keyBy('id');

        $entradas = PgrInventarioCargo::query()
            ->where('pgr_inventario_id', $inventario->id)
            ->with('Riscos')
            ->get()
            ->keyBy('vaga_id');

        $colaboradores = (new ColaboradoresAtivosQuery())->daEmpresa($empresaId)
            ->leftJoin('vagas_abertas as va', 'va.id', '=', 'fc.vagas_abertas_id')
            ->selectRaw('COALESCE(va.vaga_id, fc.vaga_id) as vaga, COUNT(DISTINCT fc.id) as total')
            ->groupByRaw('COALESCE(va.vaga_id, fc.vaga_id)')
            ->pluck('total', 'vaga');

        $cargos = DB::table('vagas')
            ->where('empresa_id', $empresaId)
            ->where(fn ($q) => $q->where('ativo', true)->orWhereIn('id', $entradas->keys()->all()))
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->map(function ($vaga) use ($entradas, $catalogo, $colaboradores) {
                $entrada = $entradas->get($vaga->id);

                return [
                    'vaga_id' => (int) $vaga->id,
                    'nome' => (string) $vaga->nome,
                    'colaboradores' => (int) ($colaboradores[$vaga->id] ?? 0),
                    'avaliado' => (bool) $entrada,
                    'ausencia_risco' => (bool) ($entrada?->ausencia_risco),
                    'observacao' => $entrada?->observacao,
                    'riscos' => $entrada
                        ? $entrada->Riscos->map(fn (PgrInventarioRisco $r) => [
                            'exame_risco_id' => $r->exame_risco_id,
                            'label' => $catalogo[$r->exame_risco_id]['label'] ?? ('#' . $r->exame_risco_id),
                            'tipo' => $catalogo[$r->exame_risco_id]['tipo'] ?? null,
                            'fonte_geradora' => $r->fonte_geradora,
                            'intensidade' => $r->intensidade,
                            'medidas_controle' => $r->medidas_controle,
                        ])->values()->all()
                        : [],
                ];
            })
            ->all();

        return [
            'inventario' => collect($this->listarVersoes($empresaId))->firstWhere('id', $inventario->id),
            'editavel' => $inventario->status === PgrInventario::STATUS_RASCUNHO,
            'cargos' => $cargos,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function opcoes(int $empresaId): array
    {
        return [
            'riscos' => $this->riscos->listar($empresaId),
            'tipos_risco' => $this->riscos->tipos(),
            'vigente' => $this->vigente->vigente($empresaId),
        ];
    }

    /**
     * Nova versão em rascunho, copiando a vigente (se houver). Só um rascunho por vez.
     */
    public function criarRascunho(int $empresaId, User $autor): PgrInventario
    {
        $existe = PgrInventario::query()
            ->where('empresa_id', $empresaId)
            ->where('status', PgrInventario::STATUS_RASCUNHO)
            ->exists();
        if ($existe) {
            throw ValidationException::withMessages(['pgr' => 'Já existe uma versão em rascunho. Publique ou exclua antes de criar outra.']);
        }

        return DB::transaction(function () use ($empresaId, $autor) {
            $base = PgrInventario::query()
                ->where('empresa_id', $empresaId)
                ->where('status', PgrInventario::STATUS_VIGENTE)
                ->with('Cargos.Riscos')
                ->first();

            $rascunho = PgrInventario::query()->create([
                'empresa_id' => $empresaId,
                'versao' => (int) PgrInventario::withTrashed()->where('empresa_id', $empresaId)->max('versao') + 1,
                'status' => PgrInventario::STATUS_RASCUNHO,
                'responsavel_tecnico' => $base?->responsavel_tecnico,
                'responsavel_registro' => $base?->responsavel_registro,
                'user_id' => $autor->id,
            ]);

            foreach ($base?->Cargos ?? [] as $cargo) {
                $copia = PgrInventarioCargo::query()->create([
                    'pgr_inventario_id' => $rascunho->id,
                    'vaga_id' => $cargo->vaga_id,
                    'ausencia_risco' => $cargo->ausencia_risco,
                    'observacao' => $cargo->observacao,
                ]);
                foreach ($cargo->Riscos as $risco) {
                    PgrInventarioRisco::query()->create([
                        'pgr_inventario_cargo_id' => $copia->id,
                        'exame_risco_id' => $risco->exame_risco_id,
                        'fonte_geradora' => $risco->fonte_geradora,
                        'intensidade' => $risco->intensidade,
                        'medidas_controle' => $risco->medidas_controle,
                    ]);
                }
            }

            $this->auditar($autor, $rascunho, sprintf('criou rascunho do PGR versão %d%s', $rascunho->versao, $base ? ' (cópia da versão ' . $base->versao . ')' : ''));

            return $rascunho;
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function salvarCabecalho(int $empresaId, int $inventarioId, array $dados): void
    {
        $inventario = $this->rascunho($empresaId, $inventarioId);
        $inventario->fill([
            'responsavel_tecnico' => $this->texto($dados['responsavel_tecnico'] ?? null, 150),
            'responsavel_registro' => $this->texto($dados['responsavel_registro'] ?? null, 60),
            'observacao' => $this->texto($dados['observacao'] ?? null, 1000),
        ])->save();
    }

    /**
     * Riscos de um cargo no rascunho. Sem riscos e sem "ausência de risco" = cargo volta a não avaliado.
     *
     * @param  array<string, mixed>  $dados
     */
    public function salvarCargo(int $empresaId, int $inventarioId, int $vagaId, array $dados): void
    {
        $inventario = $this->rascunho($empresaId, $inventarioId);

        if (!DB::table('vagas')->where('empresa_id', $empresaId)->where('id', $vagaId)->exists()) {
            throw ValidationException::withMessages(['vaga_id' => 'Cargo não pertence a esta empresa.']);
        }

        $ausencia = filter_var($dados['ausencia_risco'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $riscos = collect((array) ($dados['riscos'] ?? []))
            ->map(fn ($r) => [
                'exame_risco_id' => (int) ($r['exame_risco_id'] ?? 0),
                'fonte_geradora' => $this->texto($r['fonte_geradora'] ?? null, 200),
                'intensidade' => $this->texto($r['intensidade'] ?? null, 100),
                'medidas_controle' => $this->texto($r['medidas_controle'] ?? null, 1000),
            ])
            ->filter(fn ($r) => $r['exame_risco_id'] > 0)
            ->unique('exame_risco_id')
            ->values();

        if ($ausencia && $riscos->isNotEmpty()) {
            throw ValidationException::withMessages(['riscos' => 'Marque "ausência de risco" ou informe riscos, não os dois.']);
        }
        if ($riscos->count() > 50) {
            throw ValidationException::withMessages(['riscos' => 'No máximo 50 riscos por cargo.']);
        }
        $this->riscos->validarDaEmpresa($empresaId, $riscos->pluck('exame_risco_id')->all());

        DB::transaction(function () use ($inventario, $vagaId, $ausencia, $riscos, $dados) {
            $cargo = PgrInventarioCargo::query()
                ->where('pgr_inventario_id', $inventario->id)
                ->where('vaga_id', $vagaId)
                ->first();

            if (!$ausencia && $riscos->isEmpty()) {
                if ($cargo) {
                    PgrInventarioRisco::query()->where('pgr_inventario_cargo_id', $cargo->id)->delete();
                    $cargo->delete();
                }

                return;
            }

            $cargo ??= new PgrInventarioCargo(['pgr_inventario_id' => $inventario->id, 'vaga_id' => $vagaId]);
            $cargo->ausencia_risco = $ausencia;
            $cargo->observacao = $this->texto($dados['observacao'] ?? null, 500);
            $cargo->save();

            PgrInventarioRisco::query()->where('pgr_inventario_cargo_id', $cargo->id)->delete();
            foreach ($riscos as $risco) {
                PgrInventarioRisco::query()->create($risco + ['pgr_inventario_cargo_id' => $cargo->id]);
            }
        });
    }

    /**
     * Prévia da publicação: ASOs atuais que mudam de vencimento com os riscos deste rascunho.
     *
     * @return array<string, mixed>
     */
    public function previaPublicacao(int $empresaId, int $inventarioId): array
    {
        $rascunho = $this->rascunho($empresaId, $inventarioId);
        $regrasAntes = $this->regrasAtivas->daEmpresa($empresaId);
        $regrasDepois = array_merge(
            $this->regrasAtivas->daEmpresaSemMercado($empresaId),
            [(new TravaLegalNr7())->regraExpostoRisco()]
        );

        $resultado = $this->comparador->comparar(
            $empresaId,
            $this->regrasEmpresa->tiposComVencimento()->pluck('id')->map(fn ($id) => (int) $id)->all(),
            $regrasAntes,
            $regrasDepois,
            $this->vigente->riscosPorCargo($empresaId),
            $this->vigente->riscosPorCargoDoInventario($rascunho->id)
        );

        return $resultado + ['cargos_sem_avaliacao' => $this->cargosComColaboradoresSemAvaliacao($empresaId, $rascunho->id)];
    }

    /**
     * Publica o rascunho: arquiva a vigente (fim = início − 1 dia) e recalcula conforme o modo.
     *
     * @return array{job_despachado: bool, mensagem: string}
     */
    public function publicar(int $empresaId, int $inventarioId, ?string $vigenciaInicio, string $modo, ?string $dataAplicacao, User $autor): array
    {
        $rascunho = $this->rascunho($empresaId, $inventarioId);
        $inicio = $this->dataObrigatoria($vigenciaInicio, 'vigencia_inicio', 'Informe o início da vigência.');
        [$modo, $dataInsert] = $this->validarModo($modo, $dataAplicacao, $empresaId);

        if (!PgrInventarioCargo::query()->where('pgr_inventario_id', $rascunho->id)->exists()) {
            throw ValidationException::withMessages(['pgr' => 'Avalie ao menos um cargo antes de publicar.']);
        }

        DB::transaction(function () use ($empresaId, $rascunho, $inicio, $autor) {
            $anterior = PgrInventario::query()
                ->where('empresa_id', $empresaId)
                ->where('status', PgrInventario::STATUS_VIGENTE)
                ->lockForUpdate()
                ->first();

            if ($anterior) {
                if ($anterior->vigencia_inicio && $inicio->lessThanOrEqualTo(CarbonImmutable::parse($anterior->vigencia_inicio))) {
                    throw ValidationException::withMessages(['vigencia_inicio' => 'O início precisa ser depois do início da versão vigente (' . $anterior->vigencia_inicio_br . ').']);
                }
                $anterior->status = PgrInventario::STATUS_ARQUIVADO;
                $anterior->vigencia_fim = $inicio->subDay()->toDateString();
                $anterior->save();
            }

            $rascunho->status = PgrInventario::STATUS_VIGENTE;
            $rascunho->vigencia_inicio = $inicio->toDateString();
            $rascunho->vigencia_fim = null;
            $rascunho->publicado_em = now();
            $rascunho->user_id = $autor->id;
            $rascunho->save();

            $this->auditar($autor, $rascunho, sprintf(
                'publicou o PGR versão %d (vigência a partir de %s)%s',
                $rascunho->versao,
                $inicio->format('d/m/Y'),
                $anterior ? '; versão ' . $anterior->versao . ' arquivada' : ''
            ));
        });

        $this->vigente->invalidar($empresaId);
        $this->regrasAtivas->invalidar($empresaId);
        PainelCache::invalidar($empresaId);

        if ($modo === AsoPeriodicidadeService::MODO_FUTURO) {
            return ['job_despachado' => false, 'mensagem' => 'PGR publicado. Vale para os próximos ASOs.'];
        }

        $tipos = [];
        foreach ($this->regrasEmpresa->tiposComVencimento() as $tipo) {
            $config = $this->periodicidade->resolverConfig($empresaId, (int) $tipo->id);
            if ($config !== null) {
                $tipos[] = ['exame_tipo_id' => (int) $tipo->id] + $config;
            }
        }
        JobRecalcularVencimentoAso::dispatch($empresaId, $tipos, $modo === AsoPeriodicidadeService::MODO_A_PARTIR_DE ? $dataInsert : null);

        return ['job_despachado' => true, 'mensagem' => 'PGR publicado. Recálculo dos ASOs atuais enfileirado.'];
    }

    public function excluirRascunho(int $empresaId, int $inventarioId, User $autor): void
    {
        $rascunho = $this->rascunho($empresaId, $inventarioId);
        $rascunho->delete();
        $this->auditar($autor, $rascunho, sprintf('excluiu o rascunho do PGR versão %d', $rascunho->versao));
    }

    /**
     * Cargos com colaboradores ativos que não foram avaliados nesta versão.
     */
    public function cargosComColaboradoresSemAvaliacao(int $empresaId, ?int $inventarioId): int
    {
        if (!$inventarioId) {
            return 0;
        }

        $avaliados = PgrInventarioCargo::query()->where('pgr_inventario_id', $inventarioId)->pluck('vaga_id')->all();

        return (int) (new ColaboradoresAtivosQuery())->daEmpresa($empresaId)
            ->leftJoin('vagas_abertas as va', 'va.id', '=', 'fc.vagas_abertas_id')
            ->whereRaw('COALESCE(va.vaga_id, fc.vaga_id) IS NOT NULL')
            ->when($avaliados !== [], fn ($q) => $q->whereRaw(
                'COALESCE(va.vaga_id, fc.vaga_id) NOT IN (' . implode(',', array_fill(0, count($avaliados), '?')) . ')',
                $avaliados
            ))
            ->selectRaw('COUNT(DISTINCT COALESCE(va.vaga_id, fc.vaga_id)) as total')
            ->value('total');
    }

    private function buscar(int $empresaId, int $inventarioId): PgrInventario
    {
        return PgrInventario::query()->where('empresa_id', $empresaId)->findOrFail($inventarioId);
    }

    private function rascunho(int $empresaId, int $inventarioId): PgrInventario
    {
        $inventario = $this->buscar($empresaId, $inventarioId);
        if ($inventario->status !== PgrInventario::STATUS_RASCUNHO) {
            throw ValidationException::withMessages(['pgr' => 'Só a versão em rascunho pode ser alterada. Crie uma nova versão.']);
        }

        return $inventario;
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function validarModo(string $modo, ?string $dataAplicacao, int $empresaId): array
    {
        $modos = [AsoPeriodicidadeService::MODO_FUTURO, AsoPeriodicidadeService::MODO_RETROATIVO, AsoPeriodicidadeService::MODO_A_PARTIR_DE];
        if (!in_array($modo, $modos, true)) {
            throw ValidationException::withMessages(['modo_aplicacao' => 'Escolha como aplicar: só futuros, retroativo ou a partir de uma data.']);
        }

        $dataInsert = null;
        if ($modo === AsoPeriodicidadeService::MODO_A_PARTIR_DE) {
            $dataInsert = $this->dataObrigatoria($dataAplicacao, 'data_aplicacao', 'Informe a data de aplicação.')->toDateString();
        }

        if ($modo !== AsoPeriodicidadeService::MODO_FUTURO && Cache::has(JobRecalcularVencimentoAso::cacheLockKey($empresaId))) {
            throw ValidationException::withMessages(['modo_aplicacao' => 'Já existe um recálculo de vencimentos em andamento. Aguarde alguns minutos e tente de novo.']);
        }

        return [$modo, $dataInsert];
    }

    private function dataObrigatoria(?string $valor, string $campo, string $mensagem): CarbonImmutable
    {
        try {
            if ($valor) {
                return CarbonImmutable::parse((new DataHora($valor))->dataInsert())->startOfDay();
            }
        } catch (\Throwable) {
            // cai na mensagem abaixo
        }

        throw ValidationException::withMessages([$campo => $mensagem]);
    }

    private function texto(mixed $valor, int $max): ?string
    {
        $texto = trim((string) $valor);

        return $texto === '' ? null : mb_substr($texto, 0, $max);
    }

    private function auditar(User $autor, PgrInventario $inventario, string $descricao): void
    {
        // activity_log do MyBP exige a coluna custom `descricao` (NOT NULL).
        activity('saude_pgr')
            ->causedBy($autor)
            ->withProperties(['empresa_id' => $inventario->empresa_id, 'pgr_inventario_id' => $inventario->id, 'versao' => $inventario->versao])
            ->tap(function ($activity) use ($descricao) {
                $activity->descricao = $descricao;
            })
            ->log($descricao);
    }
}
