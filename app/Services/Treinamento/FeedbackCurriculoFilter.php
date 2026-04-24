<?php

namespace App\Services\Treinamento;

use App\Models\FeedbackCurriculo;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FeedbackCurriculoFilter
{
    private $query;
    private $filters;

    private $user;
    private $authMethod;

    /**
     * @throws \Exception
     */
    public function __construct($idUser = null, $autoLogin = false)
    {
        $this->authenticateUser($idUser, $autoLogin);
        $this->query = FeedbackCurriculo::select([
            'id', 'curriculo_id', 'telefone_id', 'vaga_id', 'vagas_abertas_id', 'vaga_projeto_id'
        ])->with(
            'Curriculo:id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
            'Curriculo.FotoTres:id',
            'Admissao.AreaEtiqueta',
            'Admissao.SegmentoTreinamento:id,nome,slug',
            'VagaSelecionada:id,nome',
            'Treinamento:id,cadastrou,feedback_id,tipo,created_at,updated_at',
            'Treinamento.Vencimentos',
            'Treinamento.QuemCadastrou:id,nome'
        );

        $this->filters = [];
    }

    /**
     * Cria nova instância do filtro
     */
    public static function make($idUser = null, $autoLogin = false): self
    {
        return new static($idUser, $autoLogin);
    }

    /**
     * Aplica filtros baseado no Request ou array
     */
    public function apply($filters): self
    {
        if ($filters instanceof Request) {
            $this->filters = $filters->all();
        } elseif (is_array($filters)) {
            $this->filters = $filters;
        } else {
            throw new \InvalidArgumentException('Filtros devem ser Request ou array');
        }

        $this->applyAllFilters();

        return $this;
    }

    /**
     * Retorna a query construída
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Executa e retorna os resultados
     */
    public function get()
    {
        return $this->query
            ->orderByRaw("CASE WHEN EXISTS (SELECT 1 FROM admissoes a WHERE a.feedback_id = feedback_curriculos.id AND a.status = 'ADMITIDO') THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Executa e retorna os resultados paginados
     */
    public function paginate($perPage = 15)
    {
        return $this->query
            ->orderByRaw("CASE WHEN EXISTS (SELECT 1 FROM admissoes a WHERE a.feedback_id = feedback_curriculos.id AND a.status = 'ADMITIDO') THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retorna contagem dos resultados
     */
    public function count()
    {
        return $this->query->count();
    }

    /**
     * Aplica filtro de IDs selecionados
     */
    public function whereIds(array $ids): self
    {
        if (!empty($ids)) {
            $this->query->whereIn('id', $ids);
        }
        return $this;
    }

    /**
     * Aplica todos os filtros
     */
    private function applyAllFilters(): void
    {
        $this->applyBasicFilters()
            ->applyDateFilters()
            ->applyStatusFilters()
            ->applySearchFilters()
            ->applyTrainingFilters()
            ->applyNRFilters()
            ->applyAdmissionFilters();
    }

    /**
     * Filtros básicos
     */
    private function applyBasicFilters(): self
    {
        // Filtro CNPJ e Centro de Custo - usando a mesma abordagem do AdmissaoController
        $this->applyCnpjECentroCustoFilter();

        // Filtro demitido/admitido
        if (isset($this->filters['campoDemitido']) && $this->filters['campoDemitido']) {
            $this->query->Demitidos();
        } else {
            $this->query->Admitidos()->whereHas('ResultadoIntegrado', function ($q) {
                $q->whereEncaminhadoTreinamento(true);
            });
        }

        return $this;
    }

    /**
     * Aplica filtro de CNPJ e Centro de Custo seguindo o padrão do AdmissaoController
     */
    private function applyCnpjECentroCustoFilter(): void
    {
        try {
            // Se não há filtros de CNPJ nem Centro de Custo, não aplica filtro
            $temCnpj = isset($this->filters['campoCnpj']) && !empty($this->filters['campoCnpj']);
            $temCentroCusto = isset($this->filters['campoCentroCusto']) && !empty($this->filters['campoCentroCusto']);
            
            if (!$temCnpj && !$temCentroCusto) {
                return;
            }

            // Usar o mesmo método que o AdmissaoController
            if ($temCnpj) {
                $centroCusto = new \App\Models\CentroCusto();
                $centros_custos = $centroCusto->listaCentroCustoPorCnpj($this->user->empresa_id);
                
                if (!$temCentroCusto) {
                    // Apenas CNPJ - buscar todos os centros de custo deste CNPJ
                    $this->applyFilterByCnpjOnly($centros_custos);
                } else {
                    // CNPJ + Centro de Custo específico
                    $this->applyFilterByCnpjAndCentroCusto($centros_custos);
                }
            } elseif ($temCentroCusto) {
                // Apenas Centro de Custo (sem CNPJ específico)
                $this->applyFilterByCentroCustoOnly();
            }

        } catch (\Exception $e) {
            \Log::warning("Erro ao aplicar filtro CNPJ/Centro de Custo: " . $e->getMessage());
            // Não aplica filtro se houver erro - deixa passar todos os registros
        }
    }

    /**
     * Aplica filtro apenas por CNPJ (todos os centros de custo deste CNPJ)
     */
    private function applyFilterByCnpjOnly($centros_custos): void
    {
        $cnpj = $this->filters['campoCnpj'];
        $cnpjNumerico = preg_replace("/[^0-9]/", "", $cnpj);
        
        // Verificar se o CNPJ existe nos centros de custo
        if (!isset($centros_custos['centros_custos'][$cnpjNumerico])) {
            // CNPJ não encontrado, não retorna nenhum resultado
            $this->query->whereRaw('1 = 0');
            return;
        }

        $cc = $centros_custos['centros_custos'][$cnpjNumerico];
        
        $this->query->whereHas('Admissao', function ($query) use ($cc) {
            if ($cc[0]['matriz']) {
                // É matriz - buscar por centro_custo_id
                $query->where(function ($q) use ($cc) {
                    $q->whereIn('centro_custo_id', $cc->pluck('id')->toArray())
                      ->orWhere('centro_custo_id', null);
                })->where('filial', false);
            } else {
                // É filial - buscar por centro_custo_filial_id  
                $query->where(function ($q) use ($cc) {
                    $q->whereIn('centro_custo_filial_id', $cc->pluck('filial_id')->toArray())
                      ->orWhere('centro_custo_filial_id', null);
                })->where('filial', true);
            }
        });
    }

    /**
     * Aplica filtro por CNPJ + Centro de Custo específico
     */
    private function applyFilterByCnpjAndCentroCusto($centros_custos): void
    {
        $cnpj = $this->filters['campoCnpj'];
        $cnpjNumerico = preg_replace("/[^0-9]/", "", $cnpj);
        $centroCustoId = $this->filters['campoCentroCusto'];
        
        // Verificar se o CNPJ existe nos centros de custo
        if (!isset($centros_custos['centros_custos'][$cnpjNumerico])) {
            $this->query->whereRaw('1 = 0');
            return;
        }

        $cc = $centros_custos['centros_custos'][$cnpjNumerico];
        
        $this->query->whereHas('Admissao', function ($query) use ($cc, $centroCustoId) {
            if ($cc[0]['matriz']) {
                // É matriz - buscar por centro_custo_id
                $campoCentroCusto = $centroCustoId != '--naoinformado--' ? $centroCustoId : null;
                $query->where('centro_custo_id', $campoCentroCusto)
                      ->where('filial', false);
            } else {
                // É filial - buscar por centro_custo_filial_id
                $campoCentroCusto = $centroCustoId != '--naoinformado--' ? $centroCustoId : null;
                $query->where('centro_custo_filial_id', $campoCentroCusto)
                      ->where('filial', true);
            }
        });
    }

    /**
     * Aplica filtro apenas por Centro de Custo (sem CNPJ específico)
     */
    private function applyFilterByCentroCustoOnly(): void
    {
        $centroCustoId = $this->filters['campoCentroCusto'];
        
        $this->query->whereHas('Admissao', function ($query) use ($centroCustoId) {
            if ($centroCustoId === '--naoinformado--') {
                $query->where(function ($q) {
                    $q->whereNull('centro_custo_id')
                      ->whereNull('centro_custo_filial_id');
                });
            } else {
                $query->where(function ($q) use ($centroCustoId) {
                    $q->where('centro_custo_id', $centroCustoId)
                      ->orWhere('centro_custo_filial_id', $centroCustoId);
                });
            }
        });
    }

    /**
     * Filtros de data
     */
    private function applyDateFilters(): self
    {
        // Filtro de vencimento (aceita boolean do Vue ou string 'true')
        $campoVencimentoAtivo = isset($this->filters['campoVencimento']) && ($this->filters['campoVencimento'] === true || $this->filters['campoVencimento'] == 'true');
        if ($campoVencimentoAtivo && !empty($this->filters['vencimento'])) {
            $periodo = explode(' até ', $this->filters['vencimento']);
            if (count($periodo) === 2) {
                $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
                $dataFim = new DataHora($periodo[1] . ' 23:59:59');
                $this->query->whereHas('Treinamento', function ($query) use ($dataInicio, $dataFim) {
                    $query->whereHas('Vencimentos', function ($q) use ($dataInicio, $dataFim) {
                        $q->where('data_vencimento', '>=', $dataInicio->dataHoraInsert())
                            ->where('data_vencimento', '<=', $dataFim->dataHoraInsert());
                    });
                });
            }
        }

        // Filtro de período treinado (aceita boolean do Vue ou string 'true')
        $campoPeriodoTreinadoAtivo = isset($this->filters['campoPeriodoTreinado']) && ($this->filters['campoPeriodoTreinado'] === true || $this->filters['campoPeriodoTreinado'] == 'true');
        if ($campoPeriodoTreinadoAtivo && !empty($this->filters['periodoTreinado'])) {
            $periodo_treinado = explode(' até ', $this->filters['periodoTreinado']);
            if (count($periodo_treinado) === 2) {
                $dataInicio = new DataHora($periodo_treinado[0] . ' 00:00:00');
                $dataFim = new DataHora($periodo_treinado[1] . ' 23:59:59');
                $this->query->whereHas('Treinamento', function ($query) use ($dataInicio, $dataFim) {
                    $query->whereHas('Vencimentos', function ($q) use ($dataInicio, $dataFim) {
                        $q->where('data_treinamento', '>=', $dataInicio->dataHoraInsert())
                            ->where('data_treinamento', '<=', $dataFim->dataHoraInsert());
                    });
                });
            }
        }

        return $this;
    }

    /**
     * Filtros de busca
     */
    private function applySearchFilters(): self
    {
        // Filtro de busca geral
        if (isset($this->filters['campoBusca']) && !empty($this->filters['campoBusca'])) {
            $this->query->whereHas('Curriculo', function ($query) {
                $query->where(function ($q) {
                    $q->where('nome', 'like', '%' . $this->filters['campoBusca'] . '%')
                        ->orWhere('cpf', 'like', '%' . $this->filters['campoBusca'] . '%')
                        ->orWhere('id', $this->filters['campoBusca']);
                });
            });
        }

        // Filtro CPF específico
        if (isset($this->filters['campoCPF']) && !empty($this->filters['campoCPF'])) {
            $this->query->whereHas('Curriculo', function ($q) {
                $q->whereCpf($this->filters['campoCPF']);
            });
        }

        // Filtro Vaga
        if (isset($this->filters['campoVaga']) && !empty($this->filters['campoVaga'])) {
            $this->query->whereHas('VagaSelecionada', function ($query) {
                $query->whereId($this->filters['campoVaga']);
            });
        }

        // Filtro UF
        if (isset($this->filters['campoUf']) && !empty($this->filters['campoUf'])) {
            $this->query->whereHas('Curriculo', function ($q) {
                $q->whereUfVaga($this->filters['campoUf']);
            });
        }

        // Filtro Área
        if (isset($this->filters['campoArea']) && !empty($this->filters['campoArea'])) {
            $this->query->whereHas('Admissao', function ($q) {
                $q->whereAreaEtiquetaId($this->filters['campoArea']);
            });
        }

        // Filtro Cargo
        if (isset($this->filters['campoCargo']) && !empty($this->filters['campoCargo'])) {
            $this->query->whereHas('Admissao', function ($query) {
                $query->where('cargo', 'like', '%' . $this->filters['campoCargo'] . '%');
            });
        }

        // Filtro Padrão de treinamento (Segmento da admissão)
        if (isset($this->filters['segmento_treinamento_id']) && $this->filters['segmento_treinamento_id'] !== '') {
            $segmentoTreinamentoId = (int) $this->filters['segmento_treinamento_id'];
            $this->query->whereHas('Admissao', function ($query) use ($segmentoTreinamentoId) {
                $query->where('segmento_treinamento_id', $segmentoTreinamentoId);
            });
        }

        return $this;
    }

    /**
     * Filtros de status
     */
    private function applyStatusFilters(): self
    {
        // Filtro PCD
        if (isset($this->filters['campoPcd']) && $this->filters['campoPcd'] !== '') {
            $campoPcd = $this->filters['campoPcd'] == 'true';
            $this->query->whereHas('Curriculo', function ($query) use ($campoPcd) {
                $query->wherePcd($campoPcd);
            });
        }

        // Filtro Foto
        if (isset($this->filters['campoFoto']) && $this->filters['campoFoto'] !== '') {
            if ($this->filters['campoFoto'] == 'true') {
                $this->query->has('Curriculo.FotoTres');
            } else {
                $this->query->whereDoesntHave('Curriculo.FotoTres');
            }
        }

        return $this;
    }

    /**
     * Filtros de treinamento
     */
    private function applyTrainingFilters(): self
    {
        // Filtro Treinados
        if (isset($this->filters['campo_treinados']) && !empty($this->filters['campo_treinados'])) {
            if ($this->filters['campo_treinados'] == 'S') {
                $this->query->has('Treinamento');
            } elseif ($this->filters['campo_treinados'] == 'N') {
                $this->query->whereDoesntHave('Treinamento');
            }
        }

        // Filtro treinamentos selecionados
        if (isset($this->filters['treinamentos_selecionados']) && is_array($this->filters['treinamentos_selecionados']) && count($this->filters['treinamentos_selecionados']) > 0) {
            $this->query->whereHas('Treinamento.Vencimentos', function ($query) {
                $query->whereIn('label', $this->filters['treinamentos_selecionados']);
            });
        }

        // Filtro EBTV
        if (isset($this->filters['campoNr_ebtv'])) {
            if ($this->filters['campoNr_ebtv']) {
                $this->query->whereHas('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'EBTV');
                });
            } else {
                $this->query->whereDoesntHave('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'EBTV');
                });
            }
        }

        return $this;
    }

    /**
     * Filtros de NR (Normas Regulamentadoras)
     */
    private function applyNRFilters(): self
    {
        // Filtro NR33
        if (isset($this->filters['campoNr_trinta_tres']) && !empty($this->filters['campoNr_trinta_tres'])) {
            switch ($this->filters['campoNr_trinta_tres']) {
                case 'Realizado':
                    $this->query->whereHas('Treinamento.Vencimentos', function ($query) {
                        $query->where('label', 'like', '%NR33%');
                    });
                    break;
                case 'Não Realizado':
                    $this->query->whereDoesntHave('Treinamento.Vencimentos', function ($query) {
                        $query->where('label', 'like', '%NR33%');
                    });
                    break;
                case 'NÃO SE APLICA':
                    $this->query->whereHas('Admissao', function ($query) {
                        $query->where('nr_trinta_tres', $this->filters['campoNr_trinta_tres']);
                    });
                    break;
            }
        }

        // Filtro NR35
        if (isset($this->filters['campoNr_trinta_cinco']) && !empty($this->filters['campoNr_trinta_cinco'])) {
            switch ($this->filters['campoNr_trinta_cinco']) {
                case 'Realizado':
                    $this->query->whereHas('Treinamento.Vencimentos', function ($query) {
                        $query->where('label', 'like', '%NR35%');
                    });
                    break;
                case 'Não Realizado':
                    $this->query->whereDoesntHave('Treinamento.Vencimentos', function ($query) {
                        $query->where('label', 'like', '%NR35%');
                    });
                    break;
                case 'NÃO SE APLICA':
                    $this->query->whereHas('Admissao', function ($query) {
                        $query->where('nr_trinta_cinco', $this->filters['campoNr_trinta_cinco']);
                    });
                    break;
            }
        }

        return $this;
    }

    /**
     * Filtros de admissão
     */
    private function applyAdmissionFilters(): self
    {
        // Filtro Admitido
        if (isset($this->filters['campoAdmitido']) && !empty($this->filters['campoAdmitido'])) {
            if ($this->filters['campoAdmitido'] == 'S') {
                $this->query->whereHas('Admissao', function ($q) {
                    $q->whereStatus('ADMITIDO');
                });
            } elseif ($this->filters['campoAdmitido'] == 'N') {
                $this->query->whereDoesntHave('Admissao');
            }
        }

        // Filtro Crachá
        if (isset($this->filters['campoCracha']) && !empty($this->filters['campoCracha'])) {
            if ($this->filters['campoCracha'] == 'S') {
                $this->query->whereHas('Admissao', function ($q) {
                    $q->whereNotNull('numero_cracha');
                });
            } elseif ($this->filters['campoCracha'] == 'N') {
                $this->query->whereHas('Admissao', function ($q) {
                    $q->whereNull('numero_cracha');
                });
            }
        }

        return $this;
    }

    /**
     * Cria instância para usuário específico (sem login automático)
     */
    public static function forUser($idUser): self
    {
        return new static($idUser, false);
    }

    /**
     * Cria instância com login automático
     */
    public static function withLogin($idUser): self
    {
        return new static($idUser, true);
    }

    /**
     * Autentica usuário usando diferentes estratégias
     */
    private function authenticateUser($idUser = null, $autoLogin = false): void
    {
        // Estratégia 1: Usuário já autenticado
        $this->user = Auth::user();
        if ($this->user) {
            $this->authMethod = 'session';
            return;
        }

        // Estratégia 2: ID do usuário fornecido
        if ($idUser) {
            try {
                // Primeiro carrega o usuário sem relacionamentos
                $user = User::find($idUser);

                if (!$user) {
                    throw new \Exception("Usuário com ID {$idUser} não encontrado");
                }

                $this->validateUser($user);
                $this->user = $user;
                $this->authMethod = 'manual';

                // Se solicitado, faz login automático
                if ($autoLogin) {
                    Auth::login($user);
                    $this->authMethod = 'auto_login';
                }

                return;
            } catch (\Exception $e) {
                \Log::error("Erro ao autenticar usuário {$idUser}: " . $e->getMessage());
                throw new \Exception("Erro ao autenticar usuário: " . $e->getMessage());
            }
        }

        // Estratégia 3: Tentar autenticação por token (se aplicável)
        if (request()->bearerToken()) {
            $this->authenticateByToken();
            return;
        }

        throw new \Exception('Usuário não autenticado e ID não fornecido');
    }

    /**
     * Autentica por token Bearer (para APIs)
     */
    private function authenticateByToken(): void
    {
        $token = request()->bearerToken();

        // Implementar lógica de validação de token
        // Exemplo com Sanctum:
        // $user = User::whereHas('tokens', function($q) use ($token) {
        //     $q->where('token', hash('sha256', $token));
        // })->first();

        // Por enquanto, lança exceção se não implementado
        throw new \Exception('Autenticação por token não implementada');
    }

    /**
     * Valida se o usuário pode ser usado
     */
    private function validateUser($user): void
    {
        // Verifica se o usuário está ativo
        if (property_exists($user, 'ativo') && !$user->ativo) {
            throw new \Exception("Usuário {$user->id} está inativo");
        }

        // Verifica se tem empresa associada - usando verificação mais segura
        if (!isset($user->empresa_id) && !isset($user->cnpj)) {
            throw new \Exception("Usuário {$user->id} não possui empresa associada");
        }

        // Para jobs em background, não validamos a empresa diretamente
        // pois pode causar problemas com scopes globais
        if ($this->isRunningInBackground()) {
            \Log::info("Validação de empresa pulada para job em background - Usuário: {$user->id}");
            return;
        }

        // Tenta carregar e validar a empresa apenas se não estivermos em background
        try {
            if (!$user->relationLoaded('empresa') && isset($user->empresa_id)) {
                $user->load('empresa');
            }

            // Verifica se a empresa está ativa
            if ($user->empresa && property_exists($user->empresa, 'ativa') && !$user->empresa->ativa) {
                throw new \Exception("Empresa do usuário {$user->id} está inativa");
            }
        } catch (\Exception $e) {
            \Log::warning("Erro ao validar empresa para usuário {$user->id}: " . $e->getMessage());
            // Em jobs, não falha se não conseguir validar a empresa
            if (!$this->isRunningInBackground()) {
                throw $e;
            }
        }
    }

    /**
     * Verifica se o código está rodando em background (queue)
     */
    private function isRunningInBackground(): bool
    {
        return app()->runningInConsole() &&
            (request()->server('argv')[1] ?? '') === 'queue:work' ||
            (request()->server('argv')[1] ?? '') === 'horizon:work';
    }
}
