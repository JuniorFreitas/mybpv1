<?php

namespace App\Http\Controllers;

use App\Application\UseCases\GenerateVagaAbertaDescription;
use App\Models\Municipio;
use App\Models\Sistema;
use App\Models\Vaga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AiController extends Controller
{
    public function gerarDescricaoVagaAberta(Request $request, GenerateVagaAbertaDescription $useCase): JsonResponse
    {
        $this->authorize('cadastro_vagas_abertas');

        $dados = $request->validate([
            'vaga_id' => 'required|integer',
            'municipio_id' => 'nullable|integer',
            'titulo' => 'nullable|string|max:255',
        ]);

        $vaga = Vaga::query()->find($dados['vaga_id']);
        if (! $vaga) {
            return response()->json(['msg' => 'Cargo não encontrado.'], 404);
        }

        if (! Sistema::descricaoVagaIaHabilitada($vaga->empresa_id)) {
            return response()->json(['msg' => 'A geração de descrição por IA não está habilitada para esta empresa.'], 403);
        }

        $municipio = filled($dados['municipio_id'] ?? null)
            ? Municipio::query()->find($dados['municipio_id'])
            : null;

        try {
            $resultado = $useCase->handle($vaga, $municipio, $dados['titulo'] ?? null);
        } catch (RuntimeException $exception) {
            [$status, $mensagem] = match (true) {
                str_contains($exception->getMessage(), 'não configurada') => [503, 'O gerador de descrição por IA não está configurado.'],
                str_contains($exception->getMessage(), 'limite de taxa') => [429, 'O gerador de descrição por IA atingiu o limite de uso do momento. Aguarde cerca de 1 minuto e tente novamente.'],
                default => [502, 'Não foi possível gerar a descrição agora. Tente novamente em instantes.'],
            };

            return response()->json(['msg' => $mensagem], $status);
        }

        return response()->json(['success' => true, 'data' => $resultado]);
    }
}
