<?php

namespace Tests\Unit\Services\Entrevistas;

use App\Classes\ZapNotificacao;
use App\Jobs\JobSendNotificacaoWhatsApp;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\ParecerRota;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Services\Entrevistas\ParecerRotaWhatsappService;
use Illuminate\Support\Facades\DB;
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
        $user = new User();
        $user->setRelation('Empresa', $empresa);

        $mensagem = $this->service->montarMensagem($parecer, $user);

        $this->assertStringContainsString('MARIA SILVA', $mensagem);
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

    public function testEnviarLancaExcecaoQuandoWhatsappDesabilitadoNaEmpresa(): void
    {
        $parecer = new ParecerRota(['tem_rota' => true]);
        $user = Mockery::mock(User::class);
        $user->shouldReceive('enviaWhatsApp')->once()->andReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Envio de WhatsApp não habilitado para esta empresa.');

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

        DB::shouldReceive('table')->once()->with('zap_numeros')->andReturnSelf();
        DB::shouldReceive('where')->once()->with('ativo', true)->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn(null);

        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => '5511999999999',
            'mensagem' => 'teste parecer rota',
        ]);

        Queue::assertPushed(JobSendNotificacaoWhatsApp::class, function (JobSendNotificacaoWhatsApp $job) {
            return $job->dados['telefone'] === '5598999023762';
        });
    }
}
