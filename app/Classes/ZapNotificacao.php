<?php

namespace App\Classes;

use App\Models\NotificacaoWhats;
use App\Models\NotificacaoWhatsapp;

class ZapNotificacao
{

    public $Zap;

    public function __construct()
    {
        $this->Zap = (new \ZapMeTeam\Api\ZapMeApi)
            ->setApi(env('API_ZAPME'))
            ->setSecret(env('SECRET_ZAPME'))
            ->setEndpoint('https://api.zapme.com.br/messages/create');
    }

    public function requestQRCode()
    {
      dd($this->Zap->requestQRCode());
    }

    public function enviar(array $dados)
    {
        if (env('APP_ENV') == 'local') {
            $zapTelAtivo = \DB::table("zap_numeros")->where("ativo", true)->first();
            $dados['telefone'] = $zapTelAtivo ? $zapTelAtivo->telefone : '559899023762';

        }

        $send = $this->Zap->sendMessage($dados['telefone'], $dados['mensagem'])->getResult();
        if ($send['result'] == 'created') {
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
