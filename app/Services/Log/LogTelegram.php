<?php

namespace App\Services\Log;

use MasterTag\TelegramBotHandler;
use Monolog\LogRecord;

class LogTelegram extends TelegramBotHandler
{
    protected function write(LogRecord $record): void
    {
        $userAutenticado = "USUÁRIO - Não autenticado";
        if (auth()->check()) {
            $userAutenticado = 'USUÁRIO - ' . auth()->user()->nome . ' - Empresa ' . auth()->user()->Empresa->razao_social;
        }

        $mensagem = env('APP_NAME') . " | [{$record->level->getName()}] - data: {$record->datetime->format('d/m/Y H:i:s')} - mensagem: {$record->message}";
        if (isset($record->context['exception'])) {
            $mensagem .= " - Arquivo: {$record->context['exception']->getFile()} - Linha: {$record->context['exception']->getLine()}\n";
            $mensagem .= \Request::fullUrl() . "\n";
            $mensagem .= $userAutenticado;
        }
        parent::write($record->with(formatted: $mensagem));
    }

}
