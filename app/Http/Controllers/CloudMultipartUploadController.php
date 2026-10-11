<?php

namespace App\Http\Controllers;

use App\Services\Cloud\CloudMultipartUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CloudMultipartUploadController extends Controller
{
    public function __construct(private CloudMultipartUploadService $service)
    {
    }

    public function iniciar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'cloud_id' => 'required|integer',
            'pertence_id' => 'nullable|integer',
            'anterior_id' => 'nullable|integer',
            'nome_original' => 'required|string|max:255',
            'mime' => 'nullable|string|max:255',
            'bytes' => 'required|integer|min:1',
        ]);

        try {
            $resultado = $this->service->iniciar($dados);

            return response()->json($resultado, 201);
        } catch (HttpException $e) {
            return response()->json(['msg' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function assinarParte(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'upload_session_id' => 'required|string',
            'part_number' => 'required|integer|min:1|max:10000',
        ]);

        try {
            $resultado = $this->service->assinarParte(
                $dados['upload_session_id'],
                (int) $dados['part_number']
            );

            return response()->json($resultado);
        } catch (HttpException $e) {
            return response()->json(['msg' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function receberParte(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'upload_session_id' => 'required|string',
            'part_number' => 'required|integer|min:1|max:10000',
            'parte' => 'required|file',
        ]);

        try {
            $resultado = $this->service->receberParteLocal(
                $dados['upload_session_id'],
                (int) $dados['part_number'],
                $request->file('parte')
            );

            return response()->json($resultado);
        } catch (HttpException $e) {
            return response()->json(['msg' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function completar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'upload_session_id' => 'required|string',
            'partes' => 'required|array|min:1',
            'partes.*.part_number' => 'required|integer|min:1',
            'partes.*.etag' => 'required|string',
        ]);

        try {
            $arquivo = $this->service->completar(
                $dados['upload_session_id'],
                $dados['partes']
            );

            return response()->json($arquivo, 201);
        } catch (HttpException $e) {
            return response()->json(['msg' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function abortar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'upload_session_id' => 'required|string',
        ]);

        try {
            $this->service->abortar($dados['upload_session_id']);

            return response()->json(['ok' => true]);
        } catch (HttpException $e) {
            return response()->json(['msg' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
