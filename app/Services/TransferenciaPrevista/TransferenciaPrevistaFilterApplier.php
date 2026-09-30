<?php

namespace App\Services\TransferenciaPrevista;

use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\TransferenciaPrevista;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use MasterTag\DataHora;

class TransferenciaPrevistaFilterApplier
{
    private array $filtros;
    private User $user;

    public function __construct(array $filtros, User $user)
    {
        $this->filtros = $filtros;
        $this->user = $user;
    }

    public function apply(Builder $query): void
    {
        $this->applyToken($query);
        $this->applyPeriodo($query);
        $this->applyCampoBusca($query);
        $this->applyCampoCpf($query);
        $this->applyCampoStatus($query);
        $this->applyCnpjCentroCusto($query);
        $this->applyPermissoes($query);
        $this->applyOrdenacao($query);
    }

    /**
     * Filtro por token (mascara o id na URL/request). Formato: hash . 'lpve' . id
     */
    private function applyToken(Builder $query): void
    {
        $token = $this->filtros['token'] ?? null;
        if ($token === null || $token === '') {
            return;
        }
        $token = (string) $token;
        if (strpos($token, 'lpve') === false) {
            return;
        }
        $parts = explode('lpve', $token, 2);
        $id = isset($parts[1]) ? (int) $parts[1] : 0;
        if ($id > 0) {
            $query->where('id', $id);
        }
    }

    private function applyPeriodo(Builder $query): void
    {
        $filtroPeriodo = ($this->filtros['filtroPeriodo'] ?? '') === 'true' || ($this->filtros['filtroPeriodo'] ?? false) === true;
        if (!$filtroPeriodo) {
            return;
        }
        $dataInicio = $this->filtros['dataInicio'] ?? null;
        $dataFim = $this->filtros['dataFim'] ?? null;
        if ($dataInicio && $dataFim) {
            $inicio = new DataHora($dataInicio . ' 00:00:00');
            $fim = new DataHora($dataFim . ' 23:59:59');
            $query->where('created_at', '>=', $inicio->dataHoraInsert())
                ->where('created_at', '<=', $fim->dataHoraInsert());
            return;
        }
        if (!empty($this->filtros['periodo'])) {
            $periodo = explode(' até ', $this->filtros['periodo']);
            if (count($periodo) === 2) {
                $inicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
                $fim = new DataHora(trim($periodo[1]) . ' 23:59:59');
                $query->where('created_at', '>=', $inicio->dataHoraInsert())
                    ->where('created_at', '<=', $fim->dataHoraInsert());
            }
        }
    }

    private function applyCampoBusca(Builder $query): void
    {
        if (empty($this->filtros['campoBusca'] ?? '')) {
            return;
        }
        $busca = $this->filtros['campoBusca'];
        $query->where(function ($q) use ($busca) {
            $q->whereHas('Colaborador', function ($c) use ($busca) {
                $c->where('nome', 'like', '%' . $busca . '%')->orWhere('id', $busca);
            })->orWhere('id', $busca);
        });
    }

    private function applyCampoCpf(Builder $query): void
    {
        if (empty($this->filtros['campoCPF'] ?? '')) {
            return;
        }
        $cpfDigits = preg_replace('/\D/', '', (string) $this->filtros['campoCPF']);
        if ($cpfDigits === '') {
            return;
        }
        $query->whereHas('Colaborador', function ($c) use ($cpfDigits) {
            $c->where('cpf', 'like', '%' . $cpfDigits . '%')
                ->orWhereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(cpf,''), '.', ''), '-', ''), '/', ''), ' ', '') LIKE ?",
                    ['%' . $cpfDigits . '%']
                );
        });
    }

    private function applyCnpjCentroCusto(Builder $query): void
    {
        $campoCnpj = $this->filtros['campoCnpj'] ?? null;
        $campoCentroCusto = $this->filtros['campoCentroCusto'] ?? null;
        $temCnpj = $campoCnpj !== null && $campoCnpj !== '';
        $temCentro = $campoCentroCusto !== null && $campoCentroCusto !== '' && $campoCentroCusto !== 'todos';

        if (!$temCnpj && !$temCentro) {
            return;
        }

        if ($temCentro && !$temCnpj) {
            $id = (int) $campoCentroCusto;
            $query->where(function ($q) use ($id) {
                $q->where('centro_custo_origem_id', $id)
                    ->orWhere('centro_custo_destino_id', $id);
            });

            return;
        }

        $centrosCustos = (new CentroCusto())->listaCentroCustoPorCnpj($this->user->empresa_id);
        if ($centrosCustos instanceof JsonResponse) {
            $query->whereRaw('1 = 0');

            return;
        }

        $cnpjKey = preg_replace('/[^0-9]/', '', (string) $campoCnpj);
        $ccLista = $centrosCustos['centros_custos'][$campoCnpj]
            ?? $centrosCustos['centros_custos'][$cnpjKey]
            ?? null;

        if (!$ccLista || !isset($ccLista[0])) {
            $query->whereRaw('1 = 0');

            return;
        }

        $cc = collect($ccLista);

        if ($temCentro) {
            $id = (int) $campoCentroCusto;
            $query->where(function ($q) use ($id) {
                $q->where('centro_custo_origem_id', $id)
                    ->orWhere('centro_custo_destino_id', $id);
            });

            return;
        }

        $ids = $cc->map(function (array $item) {
            if (!empty($item['matriz'])) {
                return (int) $item['id'];
            }

            return (int) ($item['filial_id'] ?? $item['id'] ?? 0);
        })->filter()->unique()->values()->all();

        if ($ids === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($q) use ($ids) {
            $q->whereIn('centro_custo_origem_id', $ids)
                ->orWhereIn('centro_custo_destino_id', $ids);
        });
    }

    private function applyCampoStatus(Builder $query): void
    {
        $status = $this->filtros['campoStatus'] ?? $this->filtros['campoStatusAprovacao'] ?? null;
        if ($status === null || $status === '') {
            return;
        }

        // Em aberto: ainda em andamento (sem RH final) e sem reprovação em nenhuma etapa.
        // Não basta status_aprovacao null — origem dispensada/omitida deixa null e a
        // reprova pode estar em destino, gestor único, extra ou RH.
        if ($status === 'aberto') {
            $this->whereEmAndamento($query);
            return;
        }

        // Espelha TransferenciaPrevistaFluxoAprovacaoService::etapaAtual().
        if ($status === 'pendente_gestor_origem') {
            $this->whereEmAndamento($query);
            $this->whereModoPadrao($query);
            $this->whereCampoVazio($query, 'status_aprovacao');
            $this->whereExigeAprovacaoGestorOrigem($query);
            return;
        }

        if ($status === 'pendente_gestor_destino') {
            $this->whereEmAndamento($query);
            $this->whereModoPadrao($query);
            $query->where('exige_aprovacao_gestor_destino', true);
            $this->whereCampoVazio($query, 'status_aprovacao_gestor_destino');
            $this->whereOrigemConcluida($query);
            return;
        }

        if ($status === 'pendente_gestor_unico') {
            $this->whereEmAndamento($query);
            $query->where('modo_aprovacao', TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO);
            $this->whereCampoVazio($query, 'status_aprovacao_gestor_unico');
            // Solicitante = gestor único: etapa dispensada (não fica pendente).
            $query->where(function ($q) {
                $q->whereNull('gestor_aprovacao_id')
                    ->orWhere('gestor_aprovacao_id', 0)
                    ->orWhereColumn('gestor_aprovacao_id', '!=', 'user_id');
            });
            return;
        }

        if ($status === 'pendente_extra') {
            if (!$this->temAprovacaoExtraAtiva()) {
                $query->whereRaw('1 = 0');
                return;
            }
            $this->whereEmAndamento($query);
            $this->whereGestoresConcluidos($query);
            $this->whereCampoVazio($query, 'status_aprovacao_extra');
            return;
        }

        if ($status === 'pendente_rh') {
            $this->whereEmAndamento($query);
            $this->whereGestoresConcluidos($query);
            if ($this->temAprovacaoExtraAtiva()) {
                $query->where('status_aprovacao_extra', 'aprovado');
            }
            return;
        }

        if ($status === 'aprovado_gestor_origem') {
            $this->whereModoPadrao($query);
            $query->where('status_aprovacao', 'aprovado');
            return;
        }

        if ($status === 'reprovado_gestor_origem') {
            $this->whereModoPadrao($query);
            $query->where('status_aprovacao', 'reprovado');
            return;
        }

        if ($status === 'aprovado_gestor_destino') {
            $this->whereModoPadrao($query);
            $query->where('exige_aprovacao_gestor_destino', true)
                ->where('status_aprovacao_gestor_destino', 'aprovado');
            return;
        }

        if ($status === 'reprovado_gestor_destino') {
            $this->whereModoPadrao($query);
            $query->where('status_aprovacao_gestor_destino', 'reprovado');
            return;
        }

        if ($status === 'aprovado_gestor_unico') {
            $query->where('modo_aprovacao', TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO)
                ->where('status_aprovacao_gestor_unico', 'aprovado');
            return;
        }

        if ($status === 'reprovado_gestor_unico') {
            $query->where('modo_aprovacao', TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO)
                ->where('status_aprovacao_gestor_unico', 'reprovado');
            return;
        }

        if ($status === 'aprovado_extra') {
            if (!$this->temAprovacaoExtraAtiva()) {
                $query->whereRaw('1 = 0');
                return;
            }
            $query->where('status_aprovacao_extra', 'aprovado');
            return;
        }

        if ($status === 'reprovado_extra') {
            $query->where('status_aprovacao_extra', 'reprovado');
            return;
        }

        if ($status === 'reprovado_rh') {
            $query->where('resposta_rh', 'reprovado');
            return;
        }

        // Aprovado = efetivado pelo RH (badge final da timeline).
        if ($status === 'aprovado' || $status === 'aprovado_rh') {
            $query->where('resposta_rh', 'aprovado');
            return;
        }

        if ($status === 'reprovado') {
            $query->where(function ($q) {
                $q->where('status_aprovacao', 'reprovado')
                    ->orWhere('status_aprovacao_gestor_destino', 'reprovado')
                    ->orWhere('status_aprovacao_gestor_unico', 'reprovado')
                    ->orWhere('status_aprovacao_extra', 'reprovado')
                    ->orWhere('resposta_rh', 'reprovado');
            });
        }
    }

    private function whereEmAndamento(Builder $query): void
    {
        $query->where(function ($q) {
            $q->whereNull('resposta_rh')->orWhere('resposta_rh', '');
        });
        $this->whereNaoReprovado($query, 'status_aprovacao');
        $this->whereNaoReprovado($query, 'status_aprovacao_gestor_destino');
        $this->whereNaoReprovado($query, 'status_aprovacao_gestor_unico');
        $this->whereNaoReprovado($query, 'status_aprovacao_extra');
    }

    private function whereNaoReprovado(Builder $query, string $coluna): void
    {
        $query->where(function ($q) use ($coluna) {
            $q->whereNull($coluna)->orWhere($coluna, '!=', 'reprovado');
        });
    }

    private function whereCampoVazio(Builder $query, string $coluna): void
    {
        $query->where(function ($q) use ($coluna) {
            $q->whereNull($coluna)->orWhere($coluna, '');
        });
    }

    private function whereModoPadrao(Builder $query): void
    {
        $query->where(function ($q) {
            $q->whereNull('modo_aprovacao')
                ->orWhere('modo_aprovacao', '')
                ->orWhere('modo_aprovacao', TransferenciaPrevista::MODO_APROVACAO_PADRAO);
        });
    }

    /**
     * Espelha TransferenciaPrevistaFluxoAprovacaoService::exigeAprovacaoGestorOrigem().
     */
    private function whereExigeAprovacaoGestorOrigem(Builder $query): void
    {
        $empresaExige = $this->empresaExigeAprovacaoGestorOrigem();

        $query->where(function ($q) use ($empresaExige) {
            // Fluxo legado sempre exige origem.
            $q->where(function ($legado) {
                $legado->whereNull('fluxo_gestores_automatico')
                    ->orWhere('fluxo_gestores_automatico', false)
                    ->orWhere('fluxo_gestores_automatico', 0);
            });

            if ($empresaExige) {
                $q->orWhere(function ($auto) {
                    $auto->where('fluxo_gestores_automatico', true)
                        ->whereNotNull('gestor_id')
                        ->where('gestor_id', '>', 0)
                        ->where(function ($obs) {
                            $obs->whereNull('obs_aprovacao')
                                ->orWhere('obs_aprovacao', 'not like', '%não exigir aprovação do gestor de origem%');
                        });
                });
            }
        });
    }

    /**
     * Origem concluída: aprovada OU etapa não exigida (dispensada/omitida).
     */
    private function whereOrigemConcluida(Builder $query): void
    {
        $empresaExige = $this->empresaExigeAprovacaoGestorOrigem();

        $query->where(function ($q) use ($empresaExige) {
            $q->where('status_aprovacao', 'aprovado');

            // Automático sem gestor origem → dispensada.
            $q->orWhere(function ($disp) {
                $disp->where('fluxo_gestores_automatico', true)
                    ->where(function ($g) {
                        $g->whereNull('gestor_id')->orWhere('gestor_id', 0);
                    })
                    ->where(function ($s) {
                        $s->whereNull('status_aprovacao')->orWhere('status_aprovacao', '');
                    });
            });

            // Obs de empresa sem exigir origem.
            $q->orWhere('obs_aprovacao', 'like', '%não exigir aprovação do gestor de origem%');

            // Preferência atual da empresa: origem omitida no fluxo automático.
            if (!$empresaExige) {
                $q->orWhere(function ($omit) {
                    $omit->where('fluxo_gestores_automatico', true)
                        ->where(function ($s) {
                            $s->whereNull('status_aprovacao')->orWhere('status_aprovacao', '');
                        });
                });
            }
        });
    }

    /**
     * Espelha TransferenciaPrevistaFluxoAprovacaoService::gestoresEtapasConcluidas().
     */
    private function whereGestoresConcluidos(Builder $query): void
    {
        $query->where(function ($q) {
            $q->where(function ($unico) {
                $unico->where('modo_aprovacao', TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO)
                    ->where(function ($ok) {
                        $ok->where('status_aprovacao_gestor_unico', 'aprovado')
                            ->orWhere(function ($disp) {
                                $disp->whereNotNull('gestor_aprovacao_id')
                                    ->where('gestor_aprovacao_id', '>', 0)
                                    ->whereColumn('gestor_aprovacao_id', 'user_id');
                            });
                    });
            })->orWhere(function ($padrao) {
                $padrao->where(function ($modo) {
                    $modo->whereNull('modo_aprovacao')
                        ->orWhere('modo_aprovacao', '')
                        ->orWhere('modo_aprovacao', TransferenciaPrevista::MODO_APROVACAO_PADRAO);
                });
                $this->whereOrigemConcluida($padrao);
                $padrao->where(function ($destino) {
                    $destino->where(function ($sem) {
                        $sem->whereNull('exige_aprovacao_gestor_destino')
                            ->orWhere('exige_aprovacao_gestor_destino', false)
                            ->orWhere('exige_aprovacao_gestor_destino', 0);
                    })->orWhere('status_aprovacao_gestor_destino', 'aprovado');
                });
            });
        });
    }

    private function empresaExigeAprovacaoGestorOrigem(): bool
    {
        $empresaId = (int) ($this->user->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return true;
        }

        return app(TransferenciaPrevistaFluxoAprovacaoService::class)
            ->empresaExigeAprovacaoGestorOrigem($empresaId);
    }

    private function temAprovacaoExtraAtiva(): bool
    {
        $empresaId = (int) ($this->user->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return false;
        }

        try {
            return (bool) AprovacaoExtraConfig::getConfigAtiva($empresaId, 'transferencia');
        } catch (\Throwable) {
            return false;
        }
    }

    private function applyPermissoes(Builder $query): void
    {
        if (!empty($this->filtros['_full_export_access'])) {
            return;
        }
        if ($this->user->can('privilegio_gestao_rh') || $this->user->can('privilegio_aprovar_por_rh') || $this->user->can('privilegio_aprovar_rh')) {
            return;
        }
        $query->where(function ($q) {
            $q->where('user_id', $this->user->id)
                ->orWhere('gestor_id', $this->user->id)
                ->orWhere('gestor_destino_id', $this->user->id);
        });
    }

    private function applyOrdenacao(Builder $query): void
    {
        $ordenacao = $this->filtros['ordenacao'] ?? 'created_at_desc';
        switch ($ordenacao) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'updated_at_desc':
                $query->orderByDesc('updated_at');
                break;
            default:
                $query->orderByDesc('created_at');
        }
    }
}
