<?php

namespace App\Domain\Exames\Services;

use App\Models\Exame;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExameCatalogoService
{
    public function listar(int $empresaId, array $filtros, int $porPagina = 20, int $page = 1): LengthAwarePaginator
    {
        $query = Exame::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
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

        if (!empty($filtros['exame_tipo_id'])) {
            $query->where('exame_tipo_id', (int) $filtros['exame_tipo_id']);
        }

        return $query->paginate($porPagina, ['*'], 'page', $page);
    }

    public function listarAtivos(int $empresaId, ?int $exameTipoId = null)
    {
        $query = Exame::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('label');

        if ($exameTipoId) {
            $query->where(function ($q) use ($exameTipoId) {
                $q->whereNull('exame_tipo_id')->orWhere('exame_tipo_id', $exameTipoId);
            });
        }

        return $query->get();
    }

    /**
     * Snapshot {id, label} dos exames escolhidos no encaminhamento.
     * Preserva o nome histórico mesmo se o catálogo mudar depois.
     *
     * @param  array<int|string>  $ids
     * @return array<int, array{id:int, label:string}>
     */
    public function montarSnapshot(int $empresaId, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }

        return Exame::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $ids)
            ->orderBy('label')
            ->get(['id', 'label'])
            ->map(static fn (Exame $exame) => [
                'id' => (int) $exame->id,
                'label' => (string) $exame->label,
            ])
            ->values()
            ->all();
    }

    public function criar(array $dados, int $empresaId): Exame
    {
        return DB::transaction(function () use ($dados, $empresaId) {
            return Exame::create([
                'empresa_id' => $empresaId,
                'exame_tipo_id' => $dados['exame_tipo_id'] ?? null,
                'label' => trim($dados['label']),
                'ativo' => (bool) ($dados['ativo'] ?? true),
            ]);
        });
    }

    public function atualizar(int $id, array $dados, int $empresaId): Exame
    {
        $exame = Exame::query()->where('id', $id)->where('empresa_id', $empresaId)->first();
        if (!$exame) {
            throw new DomainException('Exame não encontrado.');
        }

        $exame->update([
            'exame_tipo_id' => array_key_exists('exame_tipo_id', $dados) ? $dados['exame_tipo_id'] : $exame->exame_tipo_id,
            'label' => trim($dados['label'] ?? $exame->label),
            'ativo' => array_key_exists('ativo', $dados) ? (bool) $dados['ativo'] : $exame->ativo,
        ]);

        return $exame->fresh();
    }

    public function desativar(int $id, int $empresaId): Exame
    {
        $exame = Exame::query()->where('id', $id)->where('empresa_id', $empresaId)->first();
        if (!$exame) {
            throw new DomainException('Exame não encontrado.');
        }
        $exame->update(['ativo' => false]);

        return $exame;
    }
}
