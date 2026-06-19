<?php

namespace App\Services\Entrevistas;

use App\Models\FeedbackCurriculo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class ParecerRotaFilter
{
    private Builder $query;
    private array $filters;

    public function __construct()
    {
        // Query exatamente igual ao seu método filtro() original
        $this->query = FeedbackCurriculo::with(
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
            'Cliente:id,razao_social',
            'VagaAberta.VagaSelecionada',
            'TelPrincipal',
            'parecerRh',
            'parecerTecnica',
            'parecerRota.QuemEnviouWhatsapp:id,nome',
            'parecerTeste'
        )
            ->join('curriculos', 'feedback_curriculos.curriculo_id', '=', 'curriculos.id')
            ->has('parecerRh')
            ->whereIn('feedback_curriculos.selecionado', ['sim', 'standby'])
            ->where('feedback_curriculos.interesse', true)
            ->select('feedback_curriculos.*');

        $this->filters = [];
    }

    public static function make(): self
    {
        return new static();
    }

    /**
     * Cria instância para usuário específico (para jobs)
     */
    public static function forUser($idUser): self
    {
        // Para simplificar, apenas chama make()
        // Em jobs, o usuário já é autenticado via Auth::loginUsingId()
        return new static();
    }

    public function apply($filters): self
    {
        if ($filters instanceof Request) {
            $this->filters = $filters->all();
        } elseif (is_array($filters)) {
            $this->filters = $filters;
        }

        $this->applyAllFilters();
        return $this;
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }

    public function get()
    {
        return $this->query->orderByDesc('created_at')->get();
    }

    public function paginate($perPage = 15)
    {
        return $this->query->orderBy('curriculos.nome')->paginate($perPage);
    }

    public function count(): int
    {
        return $this->query->count();
    }

    public function whereIds(array $ids): self
    {
        if (!empty($ids)) {
            $this->query->whereIn('feedback_curriculos.id', $ids);
        }
        return $this;
    }

    private function applyAllFilters(): void
    {
        // Filtro de período - EXATAMENTE igual ao seu método original
        if (isset($this->filters['filtroPeriodo']) && $this->filters['filtroPeriodo'] == 'true') {
            if (isset($this->filters['periodo'])) {
                $periodo = explode(' até ', $this->filters['periodo']);
                if (count($periodo) === 2) {
                    $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
                    $dataFim = new DataHora($periodo[1] . ' 23:59:59');
                    $this->query->whereHas('parecerRota', function ($q) use ($dataInicio, $dataFim) {
                        $q->where('created_at', '>=', $dataInicio->dataHoraInsert())
                            ->where('created_at', '<=', $dataFim->dataHoraInsert());
                    });
                }
            }
        }

        // Filtro de busca
        if (isset($this->filters['campoBusca']) && !empty($this->filters['campoBusca'])) {
            $this->query->whereHas('Curriculo', function ($query) {
                $query->where('nome', 'like', '%' . $this->filters['campoBusca'] . '%')
                    ->orWhere('cpf', 'like', '%' . $this->filters['campoBusca'] . '%')
                    ->orWhere('id', $this->filters['campoBusca']);
            });
        }

        // Filtro por cliente
        if (isset($this->filters['campoCliente']) && !empty($this->filters['campoCliente'])) {
            $this->query->where('feedback_curriculos.cliente_id', $this->filters['campoCliente']);
        }

        // Filtro por vaga
        if (isset($this->filters['campoVaga']) && !empty($this->filters['campoVaga'])) {
            $this->query->whereHas('VagaAberta', function ($query) {
                $query->where('id', $this->filters['campoVaga']);
            });
        }

        // Filtro por UF
        if (isset($this->filters['campoUf']) && !empty($this->filters['campoUf'])) {
            $this->query->whereHas('Curriculo', function ($q) {
                $q->where('uf_vaga', $this->filters['campoUf']);
            });
        }

        // Filtro por CPF
        if (isset($this->filters['campoCPF']) && !empty($this->filters['campoCPF'])) {
            $this->query->whereHas('Curriculo', function ($query) {
                $query->where('cpf', $this->filters['campoCPF']);
            });
        }

        // Filtro PCD
        if (isset($this->filters['campoPcd']) && !empty($this->filters['campoPcd'])) {
            $campoPcd = $this->filters['campoPcd'] == 'true';
            $this->query->whereHas('Curriculo', function ($query) use ($campoPcd) {
                $query->where('pcd', $campoPcd);
            });
        }

        // Filtro Rota
        if (isset($this->filters['campoRota']) && !empty($this->filters['campoRota'])) {
            if ($this->filters['campoRota'] == 'sem_parecer') {
                $this->query->whereDoesntHave('parecerRota');
            }
            if ($this->filters['campoRota'] == 'sim' || $this->filters['campoRota'] == 'nao') {
                $campoRota = $this->filters['campoRota'] == 'sim';
                $this->query->whereHas('parecerRota', function ($q) use ($campoRota) {
                    $q->where('tem_rota', $campoRota);
                });
            }
        }
    }

    // Métodos adicionais para compatibilidade
    public function limit(int $limit): self
    {
        $this->query->limit($limit);
        return $this;
    }

    public function with(array $relations): self
    {
        $this->query->with($relations);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }

    public function whereRaw(string $sql, array $bindings = []): self
    {
        $this->query->whereRaw($sql, $bindings);
        return $this;
    }
}
