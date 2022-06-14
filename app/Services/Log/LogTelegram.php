<?php

namespace App\Services\Log;

use MasterTag\TelegramBotHandler;

class LogTelegram extends TelegramBotHandler
{
    public function write(array $record): void
    {
        $userAutenticado = "USUÁRIO - Não autenticado";
        if (auth()) {
            $userAutenticado = 'USUÁRIO - ' . auth()->user()->nome . ' - Empresa ' . auth()->user()->Empresa->razao_social;
        }

        $mensagem = env('APP_NAME') . " | [{$record['level_name']}] - data: {$record['datetime']->format('d/m/Y H:i:s')} - mensagem: {$record['message']}";
        if (isset($record['context']['exception'])) {
            $mensagem .= " - Arquivo: {$record['context']['exception']->getFile()} - Linha: {$record['context']['exception']->getLine()}\n";
            $mensagem .= \Request::fullUrl() . "\n";
            $mensagem .= $userAutenticado;
        }
        $record['formatted'] = $mensagem;
        parent::write($record);
    }

}
