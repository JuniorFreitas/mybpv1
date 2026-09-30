<?php

namespace App\Services\Cloud;

use App\Authorization\Cloud\CloudCapabilityCatalog;
use App\Models\Arquivo;
use App\Models\Cloud;
use App\Models\HabilidadeCloud;
use App\Models\ItensCloud;
use App\Models\User;

/**
 * Camada única de autorização Cloud (módulo MyBP + capacidades de grupo + ACL de item).
 * Não unifica tabelas habilidades ↔ habilidade_clouds — une a lógica de decisão.
 */
class CloudAuthorizationService
{
    public function userHasCloudModule(User $user, string $gate = 'cloud'): bool
    {
        return $user->can($gate);
    }

    /**
     * @return list<int>
     */
    public function userGrupoCloudIds(User $user): array
    {
        if (method_exists($user, 'idsGruposCloud')) {
            return $user->idsGruposCloud();
        }

        return array_values(array_filter([(int) ($user->grupo_cloud_id ?? 0)]));
    }

    /**
     * União das capacidades de todos os grupos Cloud do usuário.
     *
     * @return list<string>
     */
    public function userCloudCapabilityNames(User $user): array
    {
        $grupoIds = $this->userGrupoCloudIds($user);
        if ($grupoIds === []) {
            return [];
        }

        return HabilidadeCloud::query()
            ->whereIn('id', function ($q) use ($grupoIds) {
                $q->select('habilidade_cloud_id')
                    ->from('grupo_habilidade_cloud')
                    ->whereIn('grupo_cloud_id', $grupoIds);
            })
            ->pluck('nome')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function userCanCloudAction(User $user, string $capabilityNome): bool
    {
        if (!CloudCapabilityCatalog::isValidNome($capabilityNome)) {
            return false;
        }

        return in_array($capabilityNome, $this->userCloudCapabilityNames($user), true);
    }

    public function authorizeCloudAction(User $user, string $capabilityNome): void
    {
        if (!$this->userCanCloudAction($user, $capabilityNome)) {
            abort(403, 'Sem permissão Cloud para esta ação.');
        }
    }

    public function userCanAccessItem(User $user, ItensCloud $item): bool
    {
        $grupoIds = $this->userGrupoCloudIds($user);
        if ($grupoIds === []) {
            return false;
        }

        return $item->Permissoes()->whereIn('grupo_cloud_id', $grupoIds)->exists();
    }

    public function authorizeItemAccess(User $user, ItensCloud $item): void
    {
        if (!$this->userCanAccessItem($user, $item)) {
            abort(403, 'Sem permissão para acessar este item do Cloud.');
        }
    }

    /**
     * Exige autenticação, mesma empresa, membership no Cloud e permissão no item.
     */
    public function authorizeCloudFile(string $arquivo): Arquivo
    {
        if (!auth()->check()) {
            abort(401, 'Não autenticado');
        }

        /** @var User $user */
        $user = auth()->user();

        $model = Arquivo::query()
            ->where('disco', Arquivo::DISCO_CLOUD)
            ->where(function ($query) use ($arquivo) {
                $query->where('file', $arquivo)->orWhere('thumb', $arquivo);

                // URL de preview costuma pedir nome_p.ext; o registro pode ter só file=nome.ext
                if (preg_match('/^(.+)_p(\.[^.]+)$/', $arquivo, $m)) {
                    $query->orWhere('file', $m[1] . $m[2]);
                }
            })
            ->first();

        if (!$model) {
            abort(404);
        }

        $item = ItensCloud::query()->where('arquivo_id', $model->id)->first();
        if (!$item) {
            abort(404);
        }

        $cloud = Cloud::encontrarAutorizadoOuAbortar($item->cloud_id);

        if ((int) $cloud->empresa_id !== (int) $user->empresa_id) {
            abort(403, 'Sem permissão para acessar este arquivo');
        }

        $this->authorizeItemAccess($user, $item);

        return $model;
    }

    public function authorizeCloudFileWithCapability(string $arquivo, string $capabilityNome): Arquivo
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            abort(401, 'Não autenticado');
        }

        $this->authorizeCloudAction($user, $capabilityNome);

        return $this->authorizeCloudFile($arquivo);
    }
}
