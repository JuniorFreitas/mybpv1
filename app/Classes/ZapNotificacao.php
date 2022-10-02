<?php

namespace App\Classes;

use App\Jobs\JobSendNotificacaoWhatsApp;
use ZapMeSdk\Base as ZapMeSdk;

class ZapNotificacao
{

    public $Zap;

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
        $this->Zap = (new ZapMeSdk())
            ->withApi(env('API_ZAPME'))
            ->withSecret(env('SECRET_ZAPME'));
    }

    public function status()
    {
        return $this->Zap->accountStatus();
    }

    public function requestQRCode()
    {
        dd($this->Zap->requestQRCode());
    }

    public function enviar(array $dados)
    {
        $ambiente = env('AMBIENTE', 'local') == 'prod' ?: 'local';

        if ($ambiente != 'prod') {
            $zapTelAtivo = \DB::table("zap_numeros")->where("ativo", true)->first();
            $dados['telefone'] = $zapTelAtivo ? $zapTelAtivo->telefone : '559899023762';
        }

        $upload = isset($dados['anexo']) ? [
            'file_content' => $dados['anexo']['arquivo'],
            'file_extension' => $dados['anexo']['tipo'] == self::TIPO_IMAGEM ? self::EXTENSAO_IMG : self::EXTENSAO_PDF
        ] : [];

        JobSendNotificacaoWhatsApp::dispatch($dados, $upload);
    }

    public function send($dados, $upload)
    {
        return $this->Zap->sendMessage($dados['telefone'], $dados['mensagem'], $upload);
    }

    private function convertPngToJpg($path)
    {

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);

        print_r($data);
    }
}
