<?php

namespace App\Domain\SaudeOcupacional\Regras;

use App\Domain\SaudeOcupacional\Colaboradores\ColaboradoresAtivosQuery;
use App\Domain\SaudeOcupacional\Pgr\PgrVigenteRepositorio;
use App\Domain\SaudeOcupacional\Pgr\RiscosCatalogoService;
use App\Services\ControleExames\AsoPeriodicidadeService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Simulador: para um colaborador, mostra quais regras se aplicam e por que o ASO vence em tal data.
 * Mostra só idade, cargo, datas e regras. Nenhum dado de saúde.
 */
class SimuladorVencimento
{
    public const MIN_BUSCA = 3;

    public const MAX_RESULTADOS = 15;

    public function __construct(
        private readonly MotorPeriodicidadeAso $motor,
        private readonly RegrasAtivasRepositorio $regrasAtivas,
        private readonly PerfisColaboradoresRepositorio $perfis,
        private readonly AsoPeriodicidadeService $periodicidade,
        private readonly RegrasEmpresaService $regrasEmpresa,
        private readonly RiscosCatalogoService $riscos,
        private readonly PgrVigenteRepositorio $pgr
    ) {
    }

    /**
     * @return list<array{feedback_id: int, nome: string, cargo: string|null}>
     */
    public function buscar(int $empresaId, string $termo): array
    {
        $termo = trim($termo);
        if (mb_strlen($termo) < self::MIN_BUSCA) {
            return [];
        }

        $termoLike = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $termo) . '%';

        return (new ColaboradoresAtivosQuery())->daEmpresa($empresaId)
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->whereNull('c.deleted_at')
            ->where('c.nome', 'like', $termoLike)
            ->orderBy('c.nome')
            ->limit(self::MAX_RESULTADOS)
            ->distinct()
            ->get(['fc.id', 'c.nome', 'a.cargo'])
            ->map(fn ($l) => ['feedback_id' => (int) $l->id, 'nome' => (string) $l->nome, 'cargo' => $l->cargo ? mb_strtoupper($l->cargo) : null])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function simular(int $empresaId, int $feedbackId, ?CarbonImmutable $hoje = null): array
    {
        $hoje ??= CarbonImmutable::today();

        $ativo = (new ColaboradoresAtivosQuery())->daEmpresa($empresaId)->where('fc.id', $feedbackId)->exists();
        if (!$ativo) {
            throw (new ModelNotFoundException())->setModel('Colaborador');
        }

        $perfil = $this->perfis->carregar($feedbackId);
        $nome = DB::table('feedback_curriculos as fc')
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->where('fc.id', $feedbackId)
            ->value('c.nome');
        $rotulos = $this->rotulos($empresaId, $perfil);

        $ultimos = DB::table('examesesmts as e')
            ->join('exame_funcionarios as ef', 'ef.id', '=', 'e.exame_funcionario_id')
            ->where('e.empresa_id', $empresaId)
            ->where('e.feedback_id', $feedbackId)
            ->where('e.atual', true)
            ->where('e.exame_realizado', true)
            ->whereNotNull('e.data_realizacao')
            ->orderByDesc('e.data_realizacao')
            ->get(['ef.exame_tipo_id', 'e.data_realizacao', 'e.data_vencimento'])
            ->unique('exame_tipo_id')
            ->keyBy('exame_tipo_id');

        $tipos = $this->regrasEmpresa->tiposComVencimento()->map(function ($tipo) use ($empresaId, $perfil, $hoje, $ultimos, $rotulos) {
            $tipoId = (int) $tipo->id;
            $padrao = Periodicidade::deArray($this->periodicidade->resolverConfig($empresaId, $tipoId) ?? ['unidade' => 'anos', 'valor' => 1]);
            $regras = $this->regrasAtivas->doTipo($empresaId, $tipoId);

            $seHoje = $this->motor->calcular($hoje, $padrao, $perfil, $regras);
            $ultimo = $ultimos->get($tipoId);
            $ultimoAso = null;
            if ($ultimo) {
                $realizacao = CarbonImmutable::parse($ultimo->data_realizacao);
                $pelasRegras = $this->motor->calcular($realizacao, $padrao, $perfil, $regras);
                $gravado = $ultimo->data_vencimento ? CarbonImmutable::parse($ultimo->data_vencimento) : null;
                $ultimoAso = [
                    'data_realizacao_br' => $realizacao->format('d/m/Y'),
                    'vencimento_gravado_br' => $gravado?->format('d/m/Y'),
                    'vencimento_pelas_regras_br' => $pelasRegras->vencimento->format('d/m/Y'),
                    'motivo' => $pelasRegras->motivo(),
                    'divergente' => !$gravado || !$gravado->equalTo($pelasRegras->vencimento),
                ];
            }

            return [
                'exame_tipo_id' => $tipoId,
                'tipo' => $tipo->label,
                'padrao' => $padrao->descricao(),
                'se_realizado_hoje' => [
                    'vencimento_br' => $seHoje->vencimento->format('d/m/Y'),
                    'motivo' => $seHoje->motivo(),
                ],
                'ultimo_aso' => $ultimoAso,
                'regras' => $this->motor->explicar($hoje, $padrao, $perfil, $regras, $rotulos),
            ];
        })->values()->all();

        return [
            'colaborador' => [
                'feedback_id' => $feedbackId,
                'nome' => (string) $nome,
                'idade' => $perfil?->idadeEm($hoje),
                'sem_nascimento' => !$perfil?->nascimento,
                'cargo' => $perfil?->vagaId ? ($rotulos['cargos'][$perfil->vagaId] ?? null) : null,
                'riscos' => collect($perfil?->riscoIds ?? [])->map(fn ($id) => $rotulos['riscos'][$id] ?? ('#' . $id))->values()->all(),
            ],
            'pgr_vigente' => $this->pgr->vigente($empresaId),
            'data_referencia_br' => $hoje->format('d/m/Y'),
            'tipos' => $tipos,
        ];
    }

    /**
     * Nomes de cargos e riscos citados pelo colaborador e pelas regras ativas.
     *
     * @return array{cargos: array<int, string>, riscos: array<int, string>}
     */
    private function rotulos(int $empresaId, ?PerfilColaborador $perfil): array
    {
        $cargos = $perfil?->vagaId ? [$perfil->vagaId] : [];
        $riscos = $perfil?->riscoIds ?? [];

        foreach ($this->regrasAtivas->daEmpresa($empresaId) as $regra) {
            foreach ($regra->condicoes as $condicao) {
                if ($condicao['tipo'] === Condicoes\CondicaoCargo::TIPO) {
                    $cargos = array_merge($cargos, (array) $condicao['valor']);
                }
                if ($condicao['tipo'] === Condicoes\CondicaoRisco::TIPO) {
                    $riscos = array_merge($riscos, (array) $condicao['valor']);
                }
            }
        }

        return [
            'cargos' => $this->regrasEmpresa->nomesCargos($empresaId, $cargos),
            'riscos' => $this->riscos->nomes($empresaId, $riscos),
        ];
    }
}
