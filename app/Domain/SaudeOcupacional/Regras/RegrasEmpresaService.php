<?php

namespace App\Domain\SaudeOcupacional\Regras;

use App\Domain\SaudeOcupacional\Painel\PainelCache;
use App\Domain\SaudeOcupacional\Regras\Condicoes\CatalogoCondicoes;
use App\Domain\SaudeOcupacional\Regras\Condicoes\CondicaoCargo;
use App\Domain\SaudeOcupacional\Regras\Condicoes\CondicaoIdadeMaxima;
use App\Domain\SaudeOcupacional\Regras\Condicoes\CondicaoIdadeMinima;
use App\Domain\SaudeOcupacional\Regras\Condicoes\CondicaoRisco;
use App\Domain\SaudeOcupacional\Pgr\PgrVigenteRepositorio;
use App\Domain\SaudeOcupacional\Pgr\RiscosCatalogoService;
use App\Jobs\ControleExames\JobRecalcularVencimentoAso;
use App\Models\ExameTipo;
use App\Models\SaudeRegra;
use App\Models\SaudeRegraHistorico;
use App\Models\User;
use App\Services\ControleExames\AsoPeriodicidadeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MasterTag\DataHora;

/**
 * Cadastro das Regras da empresa: validação, histórico, auditoria e recálculo dos vencimentos.
 */
class RegrasEmpresaService
{
    public const MODO_FUTURO = AsoPeriodicidadeService::MODO_FUTURO;

    public const MODO_RETROATIVO = AsoPeriodicidadeService::MODO_RETROATIVO;

    public const MODO_A_PARTIR_DE = AsoPeriodicidadeService::MODO_A_PARTIR_DE;

    public const MODELOS = ['idade_40', 'idade_45', 'menores_18', 'cargos_anual'];

    public function __construct(
        private readonly CatalogoCondicoes $catalogo,
        private readonly RegrasAtivasRepositorio $regrasAtivas,
        private readonly AsoPeriodicidadeService $periodicidade,
        private readonly TravaLegalNr7 $trava,
        private readonly RiscosCatalogoService $riscos,
        private readonly PgrVigenteRepositorio $pgr
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listar(int $empresaId): array
    {
        $regras = SaudeRegra::query()
            ->where('empresa_id', $empresaId)
            ->with('Usuario:id,nome')
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get();

        $tipos = $this->tiposComVencimento()->pluck('label', 'id');
        $rotulos = [
            'cargos' => $this->nomesCargos($empresaId, $regras->flatMap(fn (SaudeRegra $r) => $this->idsDaCondicao($r->condicoes, CondicaoCargo::TIPO))->all()),
            'riscos' => $this->riscos->nomes($empresaId, $regras->flatMap(fn (SaudeRegra $r) => $this->idsDaCondicao($r->condicoes, CondicaoRisco::TIPO))->all()),
        ];

        return $regras->map(fn (SaudeRegra $r) => [
            'id' => $r->id,
            'nome' => $r->nome,
            'modelo' => $r->modelo,
            'ativo' => $r->ativo,
            'exame_tipo_ids' => $r->exame_tipo_ids,
            'tipos' => collect($r->exame_tipo_ids)->map(fn ($id) => $tipos[$id] ?? ('Tipo #' . $id))->values()->all(),
            'condicoes' => $r->condicoes,
            'condicoes_texto' => $this->descreverCondicoes($r->condicoes, $rotulos),
            'unidade' => $r->unidade,
            'valor' => $r->valor,
            'periodicidade_texto' => (new Periodicidade($r->unidade, $r->valor))->descricao(),
            'updated_at_br' => $r->updated_at_br,
            'usuario' => $r->Usuario?->nome,
        ])->all();
    }

    /**
     * Listas para o formulário (tipos, cargos, condições, modelos, regras de mercado).
     *
     * @return array<string, mixed>
     */
    public function opcoes(int $empresaId): array
    {
        $tipos = $this->tiposComVencimento();
        $todosTipos = $tipos->pluck('id')->all();

        return [
            'tipos_exame' => $tipos->map(fn ($t) => ['id' => $t->id, 'label' => $t->label])->values()->all(),
            'cargos' => DB::table('vagas')
                ->where('empresa_id', $empresaId)
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome'])
                ->map(fn ($v) => ['value' => (int) $v->id, 'label' => $v->nome])
                ->all(),
            'condicoes' => $this->catalogo->listar(),
            'riscos' => collect($this->riscos->listar($empresaId, true))
                ->map(fn ($r) => ['value' => $r['id'], 'label' => $r['label'] . ($r['tipo'] ? ' (' . $r['tipo'] . ')' : '')])
                ->all(),
            'pgr_vigente' => $this->pgr->vigente($empresaId),
            'regras_mercado' => $this->trava->regrasMercado($this->pgr->vigente($empresaId) !== null),
            'limite_legal' => ['anos' => TravaLegalNr7::LIMITE_ANOS, 'dias' => TravaLegalNr7::LIMITE_DIAS],
            'modelos' => [
                ['modelo' => 'idade_40', 'nome' => 'Idade 40+ anual', 'exame_tipo_ids' => $todosTipos, 'condicoes' => [['tipo' => CondicaoIdadeMinima::TIPO, 'valor' => 40]], 'unidade' => 'anos', 'valor' => 1],
                ['modelo' => 'idade_45', 'nome' => 'Idade 45+ anual', 'exame_tipo_ids' => $todosTipos, 'condicoes' => [['tipo' => CondicaoIdadeMinima::TIPO, 'valor' => 45]], 'unidade' => 'anos', 'valor' => 1],
                ['modelo' => 'menores_18', 'nome' => 'Menores de 18 anual', 'exame_tipo_ids' => $todosTipos, 'condicoes' => [['tipo' => CondicaoIdadeMaxima::TIPO, 'valor' => 17]], 'unidade' => 'anos', 'valor' => 1],
                ['modelo' => 'cargos_anual', 'nome' => 'Cargos com exposição a risco (anual)', 'exame_tipo_ids' => $todosTipos, 'condicoes' => [['tipo' => CondicaoCargo::TIPO, 'valor' => []]], 'unidade' => 'anos', 'valor' => 1],
            ],
        ];
    }

    /**
     * Valida e normaliza os dados da regra vindos da tela.
     *
     * @param  array<string, mixed>  $dados
     * @return array{nome: string, modelo: string|null, exame_tipo_ids: list<int>, condicoes: list<array{tipo: string, valor: mixed}>, unidade: string, valor: int}
     */
    public function normalizar(int $empresaId, array $dados): array
    {
        $nome = trim((string) ($dados['nome'] ?? ''));
        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 120) {
            throw ValidationException::withMessages(['nome' => 'Dê um nome à regra (3 a 120 caracteres).']);
        }

        $tiposValidos = $this->tiposComVencimento()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $tipos = collect((array) ($dados['exame_tipo_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        if ($tipos === [] || array_diff($tipos, $tiposValidos) !== []) {
            throw ValidationException::withMessages(['exame_tipo_ids' => 'Escolha os tipos de exame (o Demissional não tem vencimento).']);
        }

        $condicoes = $this->catalogo->normalizar($dados['condicoes'] ?? []);
        $this->validarCargosDaEmpresa($empresaId, $this->idsDaCondicao($condicoes, CondicaoCargo::TIPO));
        $this->riscos->validarDaEmpresa($empresaId, $this->idsDaCondicao($condicoes, CondicaoRisco::TIPO), 'condicoes');

        try {
            $periodicidade = new Periodicidade((string) ($dados['unidade'] ?? ''), (int) ($dados['valor'] ?? 0));
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['valor' => 'Informe uma periodicidade válida.']);
        }
        $this->trava->validar($periodicidade);

        $modelo = $dados['modelo'] ?? null;

        return [
            'nome' => $nome,
            'modelo' => in_array($modelo, self::MODELOS, true) ? $modelo : null,
            'exame_tipo_ids' => $tipos,
            'condicoes' => $condicoes,
            'unidade' => $periodicidade->unidade,
            'valor' => $periodicidade->valor,
        ];
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array{regra_id: int, job_despachado: bool, mensagem: string}
     */
    public function salvar(int $empresaId, ?int $regraId, array $dados, ?string $modo, ?string $dataAplicacao, User $autor): array
    {
        $normalizados = $this->normalizar($empresaId, $dados);
        [$modo, $dataInsert] = $this->validarModo($modo, $dataAplicacao, $empresaId);

        $regra = $regraId ? $this->buscar($empresaId, $regraId) : null;
        $antes = $regra ? $this->foto($regra) : null;

        $regra = DB::transaction(function () use ($regra, $empresaId, $normalizados, $autor, $antes, $modo, $dataInsert) {
            $regra ??= new SaudeRegra(['empresa_id' => $empresaId, 'ativo' => true]);
            $regra->fill($normalizados + ['user_id' => $autor->id]);
            $regra->empresa_id = $empresaId;
            $regra->save();

            $this->registrarHistorico(
                $regra,
                $antes ? SaudeRegraHistorico::ACAO_EDITAR : SaudeRegraHistorico::ACAO_CRIAR,
                $antes,
                $this->foto($regra),
                $modo,
                $dataInsert,
                $autor
            );

            return $regra;
        });

        $tipos = array_unique(array_merge($antes['exame_tipo_ids'] ?? [], $regra->exame_tipo_ids));

        return $this->depoisDeAlterar($regra, $tipos, $modo, $dataInsert, $antes ? 'Regra atualizada.' : 'Regra criada.');
    }

    /**
     * @return array{regra_id: int, job_despachado: bool, mensagem: string}
     */
    public function alterarAtivo(int $empresaId, int $regraId, bool $ativo, ?string $modo, ?string $dataAplicacao, User $autor): array
    {
        [$modo, $dataInsert] = $this->validarModo($modo, $dataAplicacao, $empresaId);
        $regra = $this->buscar($empresaId, $regraId);
        $antes = $this->foto($regra);

        DB::transaction(function () use ($regra, $ativo, $antes, $modo, $dataInsert, $autor) {
            $regra->ativo = $ativo;
            $regra->user_id = $autor->id;
            $regra->save();

            $this->registrarHistorico(
                $regra,
                $ativo ? SaudeRegraHistorico::ACAO_ATIVAR : SaudeRegraHistorico::ACAO_DESATIVAR,
                $antes,
                $this->foto($regra),
                $modo,
                $dataInsert,
                $autor
            );
        });

        return $this->depoisDeAlterar($regra, $regra->exame_tipo_ids, $modo, $dataInsert, $ativo ? 'Regra ativada.' : 'Regra desativada.');
    }

    /**
     * @return array{regra_id: int, job_despachado: bool, mensagem: string}
     */
    public function excluir(int $empresaId, int $regraId, ?string $modo, ?string $dataAplicacao, User $autor): array
    {
        [$modo, $dataInsert] = $this->validarModo($modo, $dataAplicacao, $empresaId);
        $regra = $this->buscar($empresaId, $regraId);
        $antes = $this->foto($regra);

        DB::transaction(function () use ($regra, $antes, $modo, $dataInsert, $autor) {
            $regra->delete();
            $this->registrarHistorico($regra, SaudeRegraHistorico::ACAO_EXCLUIR, $antes, null, $modo, $dataInsert, $autor);
        });

        return $this->depoisDeAlterar($regra, $regra->exame_tipo_ids, $modo, $dataInsert, 'Regra excluída.');
    }

    public function historico(int $empresaId, int $porPagina = 15): LengthAwarePaginator
    {
        return SaudeRegraHistorico::query()
            ->where('empresa_id', $empresaId)
            ->with('Usuario:id,nome')
            ->orderByDesc('id')
            ->paginate(min(max($porPagina, 5), 50));
    }

    /**
     * Regra ativa da empresa ainda não salva (para prévia).
     *
     * @param  array{nome: string, exame_tipo_ids: list<int>, condicoes: list<array>, unidade: string, valor: int}  $normalizados
     */
    public function comoRegraPeriodicidade(array $normalizados, ?int $regraId): RegraPeriodicidade
    {
        return new RegraPeriodicidade(
            $regraId ?? 0,
            $normalizados['nome'],
            $normalizados['exame_tipo_ids'],
            $normalizados['condicoes'],
            new Periodicidade($normalizados['unidade'], $normalizados['valor'])
        );
    }

    /**
     * @param  list<array{tipo: string, valor: mixed}>  $condicoes
     * @param  array{cargos?: array<int, string>, riscos?: array<int, string>}  $rotulos
     */
    public function descreverCondicoes(array $condicoes, array $rotulos): string
    {
        $partes = array_map(
            fn (array $c) => $this->catalogo->obter((string) $c['tipo'])->descrever($c['valor'], $rotulos),
            $condicoes
        );

        return $partes === [] ? '—' : ucfirst(implode(' e ', $partes));
    }

    /**
     * @param  list<int>  $ids
     * @return array<int, string>
     */
    public function nomesCargos(int $empresaId, array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        return DB::table('vagas')
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $ids)
            ->pluck('nome', 'id')
            ->mapWithKeys(fn ($nome, $id) => [(int) $id => (string) $nome])
            ->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, ExameTipo>
     */
    public function tiposComVencimento()
    {
        return ExameTipo::query()
            ->where('ativo', true)
            ->where('label', '!=', ExameTipo::LABEL_DEMISSIONAL)
            ->orderBy('id')
            ->get(['id', 'label']);
    }

    /**
     * Ids da condição de lista (cargo ou risco) da regra.
     *
     * @param  list<array{tipo: string, valor: mixed}>  $condicoes
     * @return list<int>
     */
    private function idsDaCondicao(array $condicoes, string $tipo): array
    {
        foreach ($condicoes as $condicao) {
            if (($condicao['tipo'] ?? null) === $tipo) {
                return array_map('intval', (array) $condicao['valor']);
            }
        }

        return [];
    }

    /**
     * Multi-tenant: só cargos da própria empresa.
     *
     * @param  list<int>  $ids
     */
    private function validarCargosDaEmpresa(int $empresaId, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $encontrados = DB::table('vagas')->where('empresa_id', $empresaId)->whereIn('id', $ids)->count();
        if ($encontrados !== count($ids)) {
            throw ValidationException::withMessages(['condicoes' => 'Há cargos que não pertencem a esta empresa.']);
        }
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function validarModo(?string $modo, ?string $dataAplicacao, int $empresaId): array
    {
        $modo = (string) $modo;
        if (!in_array($modo, [self::MODO_FUTURO, self::MODO_RETROATIVO, self::MODO_A_PARTIR_DE], true)) {
            throw ValidationException::withMessages(['modo_aplicacao' => 'Escolha como aplicar: só futuros, retroativo ou a partir de uma data.']);
        }

        $dataInsert = null;
        if ($modo === self::MODO_A_PARTIR_DE) {
            try {
                $dataInsert = $dataAplicacao ? (new DataHora($dataAplicacao))->dataInsert() : null;
            } catch (\Throwable) {
                $dataInsert = null;
            }
            if (!$dataInsert) {
                throw ValidationException::withMessages(['data_aplicacao' => 'Informe uma data válida para aplicar a regra.']);
            }
        }

        if ($modo !== self::MODO_FUTURO && Cache::has(JobRecalcularVencimentoAso::cacheLockKey($empresaId))) {
            throw ValidationException::withMessages(['modo_aplicacao' => 'Já existe um recálculo de vencimentos em andamento. Aguarde alguns minutos e tente de novo.']);
        }

        return [$modo, $dataInsert];
    }

    private function buscar(int $empresaId, int $regraId): SaudeRegra
    {
        return SaudeRegra::query()->where('empresa_id', $empresaId)->findOrFail($regraId);
    }

    /**
     * @return array<string, mixed>
     */
    private function foto(SaudeRegra $regra): array
    {
        return [
            'nome' => $regra->nome,
            'ativo' => (bool) $regra->ativo,
            'exame_tipo_ids' => array_map('intval', (array) $regra->exame_tipo_ids),
            'condicoes' => $regra->condicoes,
            'unidade' => $regra->unidade,
            'valor' => (int) $regra->valor,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $antes
     * @param  array<string, mixed>|null  $depois
     */
    private function registrarHistorico(
        SaudeRegra $regra,
        string $acao,
        ?array $antes,
        ?array $depois,
        string $modo,
        ?string $dataInsert,
        User $autor
    ): void {
        $despacha = $modo !== self::MODO_FUTURO;
        $descricao = mb_substr(sprintf(
            '%s regra "%s"%s (modo: %s%s)',
            ['criar' => 'criou', 'editar' => 'alterou', 'ativar' => 'ativou', 'desativar' => 'desativou', 'excluir' => 'excluiu'][$acao] ?? $acao,
            $regra->nome,
            $depois ? ': ' . (new Periodicidade($depois['unidade'], $depois['valor']))->descricao() : '',
            $modo,
            $dataInsert ? ' ' . (new DataHora($dataInsert))->dataCompleta() : ''
        ), 0, 500);

        SaudeRegraHistorico::query()->create([
            'empresa_id' => $regra->empresa_id,
            'saude_regra_id' => $regra->id,
            'user_id' => $autor->id,
            'acao' => $acao,
            'modo_aplicacao' => $modo,
            'data_aplicacao' => $dataInsert,
            'job_despachado' => $despacha,
            'antes' => $antes,
            'depois' => $depois,
            'descricao' => $descricao,
        ]);

        // activity_log do MyBP exige a coluna custom `descricao` (NOT NULL).
        activity('saude_regras')
            ->causedBy($autor)
            ->withProperties([
                'empresa_id' => $regra->empresa_id,
                'saude_regra_id' => $regra->id,
                'acao' => $acao,
                'modo_aplicacao' => $modo,
                'data_aplicacao' => $dataInsert,
                'antes' => $antes,
                'depois' => $depois,
            ])
            ->tap(function ($activity) use ($descricao) {
                $activity->descricao = $descricao;
            })
            ->log($descricao);
    }

    /**
     * Invalida caches e, nos modos retroativo/a partir de, recalcula os ASOs atuais dos tipos afetados.
     *
     * @param  list<int>  $tiposAfetados
     * @return array{regra_id: int, job_despachado: bool, mensagem: string}
     */
    private function depoisDeAlterar(SaudeRegra $regra, array $tiposAfetados, string $modo, ?string $dataInsert, string $mensagem): array
    {
        $this->regrasAtivas->invalidar($regra->empresa_id);
        PainelCache::invalidar($regra->empresa_id);

        if ($modo === self::MODO_FUTURO) {
            return ['regra_id' => $regra->id, 'job_despachado' => false, 'mensagem' => $mensagem . ' Vale para os próximos ASOs.'];
        }

        $tipos = [];
        foreach (array_unique(array_map('intval', $tiposAfetados)) as $tipoId) {
            $config = $this->periodicidade->resolverConfig($regra->empresa_id, $tipoId);
            if ($config !== null) {
                $tipos[] = ['exame_tipo_id' => $tipoId] + $config;
            }
        }

        if ($tipos === []) {
            return ['regra_id' => $regra->id, 'job_despachado' => false, 'mensagem' => $mensagem];
        }

        JobRecalcularVencimentoAso::dispatch($regra->empresa_id, $tipos, $modo === self::MODO_A_PARTIR_DE ? $dataInsert : null);

        $quando = $modo === self::MODO_A_PARTIR_DE
            ? ' a partir de ' . (new DataHora($dataInsert))->dataCompleta()
            : '';

        return [
            'regra_id' => $regra->id,
            'job_despachado' => true,
            'mensagem' => $mensagem . ' Recálculo dos ASOs atuais' . $quando . ' enfileirado.',
        ];
    }
}
