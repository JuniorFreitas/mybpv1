<?php

namespace App\Http\Controllers;

use App\Application\UseCases\ResponderDuvidaManual;
use App\Http\Requests\AssistenteManualPerguntaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AssistenteManualController extends Controller
{
    public function perguntar(AssistenteManualPerguntaRequest $request, ResponderDuvidaManual $useCase): JsonResponse
    {
        if (! config('services.assistente_manual.habilitado')) {
            return response()->json(['msg' => 'O assistente não está habilitado.'], 403);
        }

        $dados = $request->validated();

        try {
            $resultado = $useCase->handle($dados['pergunta'], $dados['historico'] ?? []);
        } catch (RuntimeException $exception) {
            [$status, $mensagem] = match (true) {
                str_contains($exception->getMessage(), 'não configurada') => [503, 'O assistente não está configurado.'],
                str_contains($exception->getMessage(), 'limite de taxa') => [429, 'O assistente atingiu o limite de uso do momento. Aguarde cerca de 1 minuto e tente novamente.'],
                default => [502, 'Não foi possível responder agora. Tente novamente em instantes.'],
            };

            return response()->json(['msg' => $mensagem], $status);
        }

        // Sem o texto da pergunta: o usuário pode ter digitado dados pessoais.
        Log::info('ajuda.assistente.respondido', [
            'user_id' => optional($request->user())->id,
            'encontrou' => $resultado['encontrou'],
            'fontes' => count($resultado['fontes']),
        ]);

        return response()->json(['success' => true, 'data' => $resultado]);
    }
}
