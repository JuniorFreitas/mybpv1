<?php

namespace Tests\Unit\Helpers;

use App\Helpers\RHHelper;
use App\Models\NotificacaoTipo;
use App\Services\Notificacao\NotificacaoEngine;
use Mockery;
use Tests\TestCase;

class RHHelperNotificacaoAdapterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_buscar_emails_rh_delega_ao_engine_com_contexto(): void
    {
        $engine = Mockery::mock(NotificacaoEngine::class);
        $engine->shouldReceive('emailsAprovacaoRh')
            ->once()
            ->with(7, ['centro_custo_id' => 99])
            ->andReturn(['a@example.com']);

        $this->app->instance(NotificacaoEngine::class, $engine);

        $emails = RHHelper::buscarEmailsRH(7, ['centro_custo_id' => 99]);

        $this->assertSame(['a@example.com'], $emails);
        $this->assertSame(NotificacaoTipo::APROVACAO_RH, NotificacaoTipo::APROVACAO_RH);
    }
}
