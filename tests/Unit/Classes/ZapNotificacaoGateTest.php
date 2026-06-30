<?php

namespace Tests\Unit\Classes;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Jobs\JobSendNotificacaoWhatsApp;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class ZapNotificacaoGateTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testBloqueiaEnvioSemMetadados(): void
    {
        Queue::fake();

        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => '5511999999999',
            'mensagem' => 'teste',
        ]);

        Queue::assertNothingPushed();
    }

    /** @return array<string, array{0: TipoMensagemWhatsapp}> */
    public static function tiposWhatsappProvider(): array
    {
        $casos = [];

        foreach (TipoMensagemWhatsapp::cases() as $tipo) {
            $casos[$tipo->value] = [$tipo];
        }

        return $casos;
    }

    /**
     * @dataProvider tiposWhatsappProvider
     */
    public function testBloqueiaEnvioDeQualquerTipoQuandoModuloDesabilitado(TipoMensagemWhatsapp $tipo): void
    {
        Queue::fake();

        $gate = Mockery::mock(WhatsappNotificationGateService::class);
        $gate->shouldReceive('podeEnviar')
            ->once()
            ->with($tipo, 104, null)
            ->andReturn(false);
        $this->app->instance(WhatsappNotificationGateService::class, $gate);

        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => '5511999999999',
            'mensagem' => 'teste',
            '_whatsapp_meta' => ZapNotificacao::meta($tipo, 104),
        ]);

        Queue::assertNothingPushed();
    }

    public function testBloqueiaEnvioQuandoModuloDesabilitado(): void
    {
        Queue::fake();

        $gate = Mockery::mock(WhatsappNotificationGateService::class);
        $gate->shouldReceive('podeEnviar')
            ->once()
            ->with(TipoMensagemWhatsapp::RecrutamentoSelecao, 104, null)
            ->andReturn(false);
        $this->app->instance(WhatsappNotificationGateService::class, $gate);

        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => '5511999999999',
            'mensagem' => 'teste',
            '_whatsapp_meta' => ZapNotificacao::meta(
                TipoMensagemWhatsapp::RecrutamentoSelecao,
                104,
            ),
        ]);

        Queue::assertNothingPushed();
    }

    public function testPermiteEnvioQuandoModuloHabilitado(): void
    {
        Queue::fake();

        $gate = Mockery::mock(WhatsappNotificationGateService::class);
        $gate->shouldReceive('podeEnviar')
            ->once()
            ->with(TipoMensagemWhatsapp::RecrutamentoSelecao, 104, null)
            ->andReturn(true);
        $this->app->instance(WhatsappNotificationGateService::class, $gate);

        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => '5511999999999',
            'mensagem' => 'teste',
            '_whatsapp_meta' => ZapNotificacao::meta(
                TipoMensagemWhatsapp::RecrutamentoSelecao,
                104,
            ),
        ], 12);

        Queue::assertPushed(JobSendNotificacaoWhatsApp::class, function (JobSendNotificacaoWhatsApp $job) {
            return isset($job->dados['_whatsapp_meta'])
                && $job->dados['_whatsapp_meta']['tipo'] === TipoMensagemWhatsapp::RecrutamentoSelecao->value;
        });
    }
}
