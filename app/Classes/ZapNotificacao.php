<?php

namespace App\Classes;

class ZapNotificacao
{
    private $token = 'api-fc3501695a44cf7ca6c4';
    private $secret = '3540172869';

    public $Zap;

    public function __construct()
    {
        $this->Zap = (new \ZapMeTeam\Api\ZapMeApi)
            ->setApi($this->token)
            ->setSecret($this->secret);
    }


    public function enviar($numero, $mensagem)
    {
        $send = $this->Zap->sendMessage($numero, $mensagem)->getResult();
        if ($send['result'] == 'success') {
            return $send;
        } else {
            return $send;
        }
    }

    public function enviarArquivo($numero, $mensagem, $arquivo)
    {
        $extensao = substr($arquivo, -3);
        $base64 = base64_encode($arquivo);

        $send = $this->Zap->sendMessage($numero, $mensagem, [
            'document' => $base64,
            'filetype' => $extensao
        ])->getResult();
        if ($send['result'] == 'success') {
            return $send;
        } else {
            return $send;
        }
    }
}
