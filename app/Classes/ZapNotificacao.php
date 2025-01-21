<?php

namespace App\Classes;

use App\Jobs\JobSendNotificacaoWhatsApp;
use App\Services\Dynamus\ZapDynamusService;

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

    public function enviar(array $dados)
    {
        $ambiente = env('AMBIENTE', 'local') == 'prod' ?: 'local';

        if ($ambiente != 'prod') {
            $zapTelAtivo = \DB::table("zap_numeros")->where("ativo", true)->first();
            $dados['telefone'] = $zapTelAtivo ? $zapTelAtivo->telefone : '5598999023762';
//            $dados['telefone'] = $zapTelAtivo ? $zapTelAtivo->telefone : '5598991140405';
        }

//        $upload = isset($dados['anexo']) ? [
//            'file_content' => Sistema::convertBase2($dados['anexo']['arquivo'], true),
//            'file_extension' => $dados['anexo']['tipo'] == self::TIPO_IMAGEM ? self::EXTENSAO_IMG : self::EXTENSAO_PDF
//        ] : [];

        JobSendNotificacaoWhatsApp::dispatch($dados);
    }

    public function SgiEnvia(array $dados)
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

        $send = (new ZapNotificacao())->send($dados, $upload);

        return $send;
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
