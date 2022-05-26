<?php

namespace App\Classes;

use App\Models\NotificacaoWhats;
use App\Models\NotificacaoWhatsapp;

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

    public function enviar(array $dados)
    {
//        $dados['telefone'] = "5598999023762";
        $send = $this->Zap->sendMessage($dados['telefone'], $dados['mensagem'])->getResult();
        if ($send['result'] == 'success') {
            $notificacao = new NotificacaoWhatsapp();
            $notificacao->enviado_id = $dados['enviado_id'];
            $notificacao->user_id = auth()->id();
            $notificacao->messageid = intval($send['messageid']);
            $notificacao->telefone = $dados['telefone'];
            $notificacao->mensagem = $dados['mensagem'];
            $notificacao->save();
        }

        return $send;
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
