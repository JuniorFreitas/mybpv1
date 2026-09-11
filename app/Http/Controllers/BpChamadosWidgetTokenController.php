<?php

namespace App\Http\Controllers;

use App\Services\BpChamados\BpChamadosWidgetTokenService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class BpChamadosWidgetTokenController extends Controller
{
    public function __invoke(BpChamadosWidgetTokenService $service): JsonResponse
    {
        if (! $service->isEnabled()) {
            return response()->json(['msg' => 'Widget BP Chamados desabilitado.'], 503);
        }

        try {
            $minted = $service->mintFor(auth()->user());
        } catch (RuntimeException $exception) {
            return response()->json(['msg' => $exception->getMessage()], 503);
        }

        return response()->json([
            'token' => $minted['token'],
            'expires_in' => $minted['expires_in'],
        ]);
    }
}
