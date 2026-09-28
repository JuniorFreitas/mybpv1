<?php

namespace App\Domain\Exames\Services;

use App\Models\ExameFuncionario;
use App\Models\ExameTipo;
use App\Models\Formulario;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExameTipoAdminService
{
    public function __construct(private readonly ExameFormularioResolver $resolver)
    {
    }

    public function listar(?int $empresaId, array $filtros, int $porPagina = 20, int $page = 1): LengthAwarePaginator
    {
        $query = ExameTipo::query()
            ->where(function ($q) use ($empresaId) {
                $q->whereNull('empresa_id');
                if ($empresaId) {
                    $q->orWhere('empresa_id', $empresaId);
                }
            })
            ->orderBy('ordem')
            ->orderBy('label');

        if (!empty($filtros['campoBusca'])) {
            $busca = trim((string) $filtros['campoBusca']);
            $query->where(function ($q) use ($busca) {
                $q->where('label', 'like', "%{$busca}%");
                if (ctype_digit($busca)) {
                    $q->orWhere('id', (int) $busca);
                }
            });
        }

        if (isset($filtros['campoStatus']) && $filtros['campoStatus'] !== '' && $filtros['campoStatus'] !== null) {
            $ativo = filter_var($filtros['campoStatus'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($ativo !== null) {
                $query->where('ativo', $ativo);
            }
        }

        if (($filtros['campoEscopo'] ?? '') === 'global') {
            $query->whereNull('empresa_id');
        } elseif (($filtros['campoEscopo'] ?? '') === 'empresa') {
            $query->where('empresa_id', $empresaId);
        }

        return $query->paginate($porPagina, ['*'], 'page', $page);
    }

    public function listarAtivosParaOperacional(?int $empresaId = null)
    {
        $empresaId = $empresaId ?? (auth()->check() ? (int) auth()->user()->empresa_id : null);

        return ExameTipo::query()
            ->where('ativo', true)
            ->where(function ($q) use ($empresaId) {
                $q->whereNull('empresa_id');
                if ($empresaId) {
                    $q->orWhere('empresa_id', $empresaId);
                }
            })
            ->orderBy('ordem')
            ->orderBy('label')
            ->get();
    }

    public function criar(array $dados, int $empresaId): ExameTipo
    {
        return DB::transaction(function () use ($dados, $empresaId) {
            $tipo = ExameTipo::create([
                'empresa_id' => $empresaId,
                'label' => trim($dados['label']),
                'ativo' => (bool) ($dados['ativo'] ?? true),
                'ordem' => (int) ($dados['ordem'] ?? 0),
                'formulario_encaminhamento_id' => $dados['formulario_encaminhamento_id'] ?? null,
                'formulario_resultado_id' => $dados['formulario_resultado_id'] ?? null,
            ]);

            $this->resolver->invalidarCache($tipo->id, $empresaId);

            return $tipo;
        });
    }

    public function atualizar(int $id, array $dados, ?int $empresaId): ExameTipo
    {
        return DB::transaction(function () use ($id, $dados, $empresaId) {
            $tipo = $this->buscarEditavel($id, $empresaId);

            if ($tipo->empresa_id === null) {
                // Tenant não altera label de tipo global: clona para a empresa
                if ($empresaId && ($dados['clonar_se_global'] ?? true)) {
                    $clone = ExameTipo::create([
                        'empresa_id' => $empresaId,
                        'label' => trim($dados['label'] ?? $tipo->label),
                        'ativo' => (bool) ($dados['ativo'] ?? $tipo->ativo),
                        'ordem' => (int) ($dados['ordem'] ?? $tipo->ordem),
                        'formulario_encaminhamento_id' => $dados['formulario_encaminhamento_id'] ?? $tipo->formulario_encaminhamento_id,
                        'formulario_resultado_id' => $dados['formulario_resultado_id'] ?? $tipo->formulario_resultado_id,
                    ]);
                    $this->resolver->invalidarCache($clone->id, $empresaId);

                    return $clone;
                }

                throw new DomainException('Tipos globais não podem ser editados. Crie um tipo da empresa.');
            }

            if ($tipo->empresa_id !== $empresaId) {
                throw new DomainException('Tipo de exame não pertence à sua empresa.');
            }

            $tipo->update([
                'label' => trim($dados['label'] ?? $tipo->label),
                'ativo' => array_key_exists('ativo', $dados) ? (bool) $dados['ativo'] : $tipo->ativo,
                'ordem' => array_key_exists('ordem', $dados) ? (int) $dados['ordem'] : $tipo->ordem,
                'formulario_encaminhamento_id' => array_key_exists('formulario_encaminhamento_id', $dados)
                    ? $dados['formulario_encaminhamento_id']
                    : $tipo->formulario_encaminhamento_id,
                'formulario_resultado_id' => array_key_exists('formulario_resultado_id', $dados)
                    ? $dados['formulario_resultado_id']
                    : $tipo->formulario_resultado_id,
            ]);

            $this->resolver->invalidarCache($tipo->id, $empresaId);

            return $tipo->fresh();
        });
    }

    public function vincularFormularios(int $id, ?int $empresaId, ?int $encaminhamentoId, ?int $resultadoId): ExameTipo
    {
        $tipo = $this->buscarEditavel($id, $empresaId);

        if ($tipo->empresa_id === null && $empresaId) {
            return $this->atualizar($id, [
                'label' => $tipo->label,
                'ativo' => $tipo->ativo,
                'ordem' => $tipo->ordem,
                'formulario_encaminhamento_id' => $encaminhamentoId,
                'formulario_resultado_id' => $resultadoId,
                'clonar_se_global' => true,
            ], $empresaId);
        }

        if ($tipo->empresa_id !== $empresaId) {
            throw new DomainException('Tipo de exame não pertence à sua empresa.');
        }

        $this->assertFormularioEmpresa($encaminhamentoId, $empresaId);
        $this->assertFormularioEmpresa($resultadoId, $empresaId);

        $tipo->update([
            'formulario_encaminhamento_id' => $encaminhamentoId,
            'formulario_resultado_id' => $resultadoId,
        ]);

        $this->resolver->invalidarCache($tipo->id, $empresaId);

        return $tipo->fresh();
    }

    public function desativar(int $id, ?int $empresaId): ExameTipo
    {
        $tipo = $this->buscarEditavel($id, $empresaId);

        if ($tipo->empresa_id === null) {
            throw new DomainException('Tipos globais não podem ser excluídos. Desative um tipo da empresa.');
        }

        if ($tipo->empresa_id !== $empresaId) {
            throw new DomainException('Tipo de exame não pertence à sua empresa.');
        }

        $tipo->update(['ativo' => false]);
        $this->resolver->invalidarCache($tipo->id, $empresaId);

        return $tipo;
    }

    public function podeExcluirFisicamente(int $id): bool
    {
        return !ExameFuncionario::withoutGlobalScopes()
            ->where('exame_tipo_id', $id)
            ->exists();
    }

    private function buscarEditavel(int $id, ?int $empresaId): ExameTipo
    {
        $tipo = ExameTipo::query()
            ->where('id', $id)
            ->where(function ($q) use ($empresaId) {
                $q->whereNull('empresa_id');
                if ($empresaId) {
                    $q->orWhere('empresa_id', $empresaId);
                }
            })
            ->first();

        if (!$tipo) {
            throw new DomainException('Tipo de exame não encontrado.');
        }

        return $tipo;
    }

    private function assertFormularioEmpresa(?int $formularioId, ?int $empresaId): void
    {
        if (!$formularioId) {
            return;
        }

        $existe = Formulario::withoutGlobalScopes()
            ->where('id', $formularioId)
            ->where(function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
            })
            ->exists();

        if (!$existe) {
            throw new DomainException('Formulário inválido para a empresa.');
        }
    }
}
