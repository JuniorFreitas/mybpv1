<?php

namespace Tests\Unit\Services\Entrevistas;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Jobs\JobSendNotificacaoWhatsApp;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\ParecerRota;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Services\Entrevistas\ParecerRotaWhatsappService;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class ParecerRotaWhatsappServiceTest extends TestCase
{
    private ParecerRotaWhatsappService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ParecerRotaWhatsappService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testMontarMensagemIncluiDadosDaRotaComFallback(): void
    {
        $parecer = new ParecerRota([
            'qual' => 'Linha 10',
            'bairro_rota' => 'Centro',
            'ponto_referencia_rota' => 'Praça Central',
            'observacao' => null,
        ]);

        $feedback = new FeedbackCurriculo();
        $curriculo = new Curriculo(['nome' => 'Maria Silva']);
        $feedback->setRelation('Curriculo', $curriculo);
        $parecer->setRelation('FeedbackCurriculo', $feedback);

        $empresa = new Cliente(['razao_social' => 'Empresa Teste LTDA']);
        $user = new User(['empresa_id' => 1]);
        $user->setRelation('Empresa', $empresa);

        $factory = Mockery::mock(WhatsappMessageFactory::class);
        $factory->shouldReceive('render')
            ->once()
            ->with(
                TipoMensagemWhatsapp::ParecerRotaTransporte,
                1,
                Mockery::on(function (array $contexto) {
                    return $contexto['nome_destinatario'] === 'MARIA SILVA'
                        && $contexto['rota'] === 'Linha 10';
                })
            )
            ->andReturn("Prezado(a) sr(a) *Maria Silva*, Tudo bem?\n\nSeguem as informações da rota de transporte:\n\n🚌 Rota: *Linha 10*\n📍 Bairro: *Centro*\n📌 Ponto de referência: *Praça Central*\nAtenciosamente,\n\nEquipe de Transporte\n*Empresa Teste LTDA*");

        $this->app->instance(WhatsappMessageFactory::class, $factory);

        $mensagem = $this->service->montarMensagem($parecer, $user);

        $this->assertStringContainsString('Maria Silva', $mensagem);
        $this->assertStringContainsString('Linha 10', $mensagem);
        $this->assertStringContainsString('Centro', $mensagem);
        $this->assertStringContainsString('Praça Central', $mensagem);
        $this->assertStringContainsString('Equipe de Transporte', $mensagem);
        $this->assertStringContainsString('Empresa Teste LTDA', $mensagem);
    }

    public function testEnviarLancaExcecaoQuandoNaoTemRota(): void
    {
        $parecer = new ParecerRota(['tem_rota' => false]);
        $user = Mockery::mock(User::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Envio permitido somente quando há rota que atende.');

        $this->service->enviar($parecer, '98999023762', TelefoneCurriculo::TIPO_WHATS, $user);
    }

    public function testEnviarLancaExcecaoQuandoTipoNaoEhWhatsapp(): void
    {
        $parecer = new ParecerRota(['tem_rota' => true]);
        $user = Mockery::mock(User::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('O telefone deve ser do tipo WhatsApp.');

        $this->service->enviar($parecer, '98999023762', TelefoneCurriculo::TIPO_CELULAR, $user);
    }

    public function testEnviarLancaExcecaoQuandoEmpresaSemWhatsappOuModuloDesativado(): void
    {
        $parecer = new ParecerRota(['tem_rota' => true]);
        $user = Mockery::mock(User::class)->makePartial();
        $user->empresa_id = 10;

        $gate = Mockery::mock(WhatsappNotificationGateService::class);
        $gate->shouldReceive('podeEnviar')
            ->once()
            ->with(TipoMensagemWhatsapp::ParecerRotaTransporte, 10)
            ->andReturn(false);
        $this->app->instance(WhatsappNotificationGateService::class, $gate);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('empresa sem WhatsApp liberado ou módulo de Transporte desativado');

        $this->service->enviar($parecer, '98999023762', TelefoneCurriculo::TIPO_WHATS, $user);
    }

    public function testMontarAcaoLogIncluiDadosDaRota(): void
    {
        $parecer = new ParecerRota([
            'qual' => 'Linha 10',
            'bairro_rota' => 'Centro',
            'ponto_referencia_rota' => 'Praça Central',
        ]);

        $acao = $this->service->montarAcaoLog($parecer, '****3762');

        $this->assertStringContainsString('Parecer Rota Transporte', $acao);
        $this->assertStringContainsString('Linha 10', $acao);
        $this->assertStringContainsString('Centro', $acao);
        $this->assertStringContainsString('****3762', $acao);
    }

    public function testMascararTelefoneOcultaDigitosSensiveis(): void
    {
        $this->assertSame('****3762', $this->service->mascararTelefone('5598999023762'));
        $this->assertSame('****', $this->service->mascararTelefone('1234'));
    }

    public function testZapNotificacaoForaDeProducaoUsaTelefoneDeTeste(): void
    {
        Queue::fake();

        putenv('AMBIENTE=local');
        $_ENV['AMBIENTE'] = 'local';
        $_SERVER['AMBIENTE'] = 'local';

        $this->app->forgetInstance(WhatsappNotificationGateService::class);

        $gate = Mockery::mock(WhatsappNotificationGateService::class);
        $gate->shouldReceive('podeEnviar')->once()->andReturn(true);
        $this->app->instance(WhatsappNotificationGateService::class, $gate);

        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => '5511999999999',
            'mensagem' => 'teste parecer rota',
            '_whatsapp_meta' => ZapNotificacao::meta(
                TipoMensagemWhatsapp::ParecerRotaTransporte,
                1,
            ),
        ]);

        Queue::assertPushed(JobSendNotificacaoWhatsApp::class, function (JobSendNotificacaoWhatsApp $job) {
            return $job->dados['telefone'] === '5511999999999'
                && ($job->dados['_whatsapp_meta']['tipo'] ?? null) === TipoMensagemWhatsapp::ParecerRotaTransporte->value;
        });
    }
}
