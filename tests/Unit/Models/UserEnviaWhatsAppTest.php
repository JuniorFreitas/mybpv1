<?php

namespace Tests\Unit\Models;

use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Models\User;
use Mockery;
use Tests\TestCase;

class UserEnviaWhatsAppTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_envia_whatsapp_delega_ao_gate(): void
    {
        $gate = Mockery::mock(WhatsappNotificationGateService::class);
        $gate->shouldReceive('empresaPermiteWhatsapp')->once()->with(77)->andReturn(true);
        $this->app->instance(WhatsappNotificationGateService::class, $gate);

        $user = new User(['empresa_id' => 77]);

        $this->assertTrue($user->enviaWhatsApp());
    }

    public function test_envia_whatsapp_false_sem_empresa(): void
    {
        $user = new User(['empresa_id' => null]);

        $this->assertFalse($user->enviaWhatsApp());
    }
}
