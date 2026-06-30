<?php

namespace App\Classes;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Jobs\JobSendNotificacaoWhatsApp;
use App\Services\Dynamus\ZapDynamusService;
use Illuminate\Support\Facades\Log;

class ZapNotificacao
{

    public $Zap;

    public const DELAY_MIN_SEGUNDOS = 5;
    public const DELAY_MAX_SEGUNDOS = 10;
    public const DELAY_INTERVALO_LOTE_SEGUNDOS = 3;

    const TIPO_PDF = 'pdf';
    const TIPO_IMAGEM = 'imagem';

    const EXTENSAO_IMG = 'jpg';
    const EXTENSAO_PDF = 'pdf';

    const TIPOS = [
        self::TIPO_IMAGEM,
        self::TIPO_PDF,
    ];

    public function __construct()
    {
        $this->Zap = (new ZapDynamusService());
    }

//    public function status()
//    {
//        return $this->Zap->accountStatus();
//    }
//
//    public function requestQRCode()
//    {
//        dd($this->Zap->requestQRCode());
//    }

    public static function meta(
        TipoMensagemWhatsapp $tipo,
        int $empresaId,
        ?int $destinatarioUserId = null,
    ): array {
        $meta = [
            'tipo' => $tipo->value,
            'empresa_id' => $empresaId,
        ];

        if ($destinatarioUserId !== null) {
            $meta['destinatario_user_id'] = $destinatarioUserId;
        }

        return $meta;
    }

    public function enviar(array $dados, ?int $delaySegundos = null): void
    {
        if (!$this->deveEnviarWhatsapp($dados)) {
            return;
        }

        $delay = $delaySegundos ?? ($dados['delay_segundos'] ?? self::calcularDelayFila());

        JobSendNotificacaoWhatsApp::dispatch($dados)
            ->delay(now()->addSeconds((int) $delay));
    }

    public function deveEnviarWhatsapp(array $dados): bool
    {
        $meta = $dados['_whatsapp_meta'] ?? null;

        if (!is_array($meta)) {
            Log::info('WhatsApp bloqueado: metadados de envio ausentes');

            return false;
        }

        $tipo = TipoMensagemWhatsapp::tryFromString($meta['tipo'] ?? null);
        $empresaId = (int) ($meta['empresa_id'] ?? 0);

        if (!$tipo || $empresaId <= 0) {
            Log::info('WhatsApp bloqueado: metadados inválidos', [
                'tipo' => $meta['tipo'] ?? null,
                'empresa_id' => $meta['empresa_id'] ?? null,
            ]);

            return false;
        }

        return app(WhatsappNotificationGateService::class)->podeEnviar(
            $tipo,
            $empresaId,
            isset($meta['destinatario_user_id']) ? (int) $meta['destinatario_user_id'] : null,
        );
    }

    public static function calcularDelayFila(int $indiceLote = 0): int
    {
        $base = random_int(self::DELAY_MIN_SEGUNDOS, self::DELAY_MAX_SEGUNDOS);

        return $base + ($indiceLote * self::DELAY_INTERVALO_LOTE_SEGUNDOS);
    }

    public function normalizarDadosEnvio(array $dados): array
    {
        unset($dados['delay_segundos'], $dados['_whatsapp_meta']);

        $ambiente = env('AMBIENTE', 'local') == 'prod' ?: 'local';

        if ($ambiente != 'prod') {
            $zapTelAtivo = \DB::table('zap_numeros')->where('ativo', true)->first();
            $dados['telefone'] = $zapTelAtivo ? $zapTelAtivo->telefone : '5598999023762';
        }

        return $dados;
    }

    public function SgiEnvia(array $dados)
    {
        if (!$this->deveEnviarWhatsapp($dados)) {
            return ['status' => false, 'msg' => 'Envio bloqueado pelas configurações de WhatsApp.'];
        }

        $dados = $this->normalizarDadosEnvio($dados);

        $upload = isset($dados['anexo']) ? [
            'file_content' => $dados['anexo']['arquivo'],
            'file_extension' => $dados['anexo']['tipo'] == self::TIPO_IMAGEM ? self::EXTENSAO_IMG : self::EXTENSAO_PDF
        ] : [];

        return $this->send($dados, $upload);
    }

    public function send($dados)
    {
        if (isset($dados['anexo']) && $dados['anexo']['tipo'] == self::TIPO_IMAGEM) {
            return $this->Zap->sendImagem($dados['telefone'], $dados['mensagem'], $dados['anexo']['arquivo']);
        }
        if (isset($dados['anexo']) && $dados['anexo']['tipo'] == self::TIPO_PDF) {
            return $this->Zap->sendPdf($dados['telefone'], $dados['mensagem'], $dados['anexo']['arquivo']);
        }
        return $this->Zap->sendMessage($dados['telefone'], $dados['mensagem']);
    }

    private function convertPngToJpg($path)
    {

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);

        print_r($data);
    }
}
