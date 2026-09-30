<?php

namespace App\Policies;

use App\Authorization\Cloud\CloudCapabilityCatalog;
use App\Models\ItensCloud;
use App\Models\User;
use App\Services\Cloud\CloudAuthorizationService;

/**
 * Policy Cloud: módulo MyBP + capacidades de grupo + ACL de item.
 */
class ItensCloudPolicy
{
    public function __construct(
        private readonly CloudAuthorizationService $cloudAuth,
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->cloudAuth->userHasCloudModule($user, 'cloud');
    }

    public function view(User $user, ItensCloud $item): bool
    {
        return $this->cloudAuth->userHasCloudModule($user, 'cloud')
            && $this->cloudAuth->userCanAccessItem($user, $item);
    }

    public function create(User $user): bool
    {
        return $user->can('cloud_insert')
            || $this->cloudAuth->userCanCloudAction($user, CloudCapabilityCatalog::EDITAR);
    }

    public function update(User $user, ItensCloud $item): bool
    {
        if (!$this->cloudAuth->userHasCloudModule($user, 'cloud')) {
            return false;
        }
        if (!$this->cloudAuth->userCanAccessItem($user, $item)) {
            return false;
        }

        return $user->can('cloud_update')
            || $this->cloudAuth->userCanCloudAction($user, CloudCapabilityCatalog::EDITAR);
    }

    public function delete(User $user, ItensCloud $item): bool
    {
        return $this->cloudAuth->userHasCloudModule($user, 'cloud')
            && $this->cloudAuth->userCanAccessItem($user, $item)
            && $this->cloudAuth->userCanCloudAction($user, CloudCapabilityCatalog::DELETAR);
    }

    public function move(User $user, ItensCloud $item): bool
    {
        return $this->cloudAuth->userHasCloudModule($user, 'cloud')
            && $this->cloudAuth->userCanAccessItem($user, $item)
            && $this->cloudAuth->userCanCloudAction($user, CloudCapabilityCatalog::MOVER);
    }

    public function review(User $user, ItensCloud $item): bool
    {
        return $this->cloudAuth->userHasCloudModule($user, 'cloud')
            && $this->cloudAuth->userCanAccessItem($user, $item)
            && $this->cloudAuth->userCanCloudAction($user, CloudCapabilityCatalog::REVISAR);
    }

    public function approve(User $user, ItensCloud $item): bool
    {
        return $this->cloudAuth->userHasCloudModule($user, 'cloud')
            && $this->cloudAuth->userCanAccessItem($user, $item)
            && $this->cloudAuth->userCanCloudAction($user, CloudCapabilityCatalog::APROVAR);
    }
}
