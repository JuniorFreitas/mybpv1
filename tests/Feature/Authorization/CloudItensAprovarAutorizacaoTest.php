<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use App\Services\Cloud\CloudAuthorizationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CloudItensAprovarAutorizacaoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::put('/__test/itenscloud/aprovar', static function () {
            app(CloudAuthorizationService::class)->authorizeCloudAction(auth()->user(), 'Aprovar');

            return response('ok', 200);
        })->middleware(['web', 'can:cloud']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_aprovar_retorna_403_sem_capacidade_aprovar(): void
    {
        Gate::define('cloud', static fn () => true);

        $cloudAuth = Mockery::mock(CloudAuthorizationService::class);
        $cloudAuth->shouldReceive('authorizeCloudAction')
            ->once()
            ->andThrow(new HttpException(403, 'Sem permissão Cloud para esta ação.'));
        $this->app->instance(CloudAuthorizationService::class, $cloudAuth);

        $user = new User();
        $user->id = 1;
        $this->actingAs($user);

        $this->put('/__test/itenscloud/aprovar')->assertForbidden();
    }
}
