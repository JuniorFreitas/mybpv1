<?php

namespace App\Http\Controllers;

use App\Services\BpChamados\BpChamadosWidgetTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BpChamadosController extends Controller
{
    public function index(BpChamadosWidgetTokenService $service): View|RedirectResponse
    {
        if (! $service->isEnabled()) {
            return redirect()
                ->route('g.dashboard')
                ->with('error', 'O módulo de chamados não está disponível.');
        }

        return view('g.bp-chamados.index', [
            'apiBaseUrl' => rtrim((string) config('services.bp_chamados.api_base_url'), '/'),
            'applicationId' => (string) config('services.bp_chamados.application_id'),
            'tokenUrl' => route('bp-chamados.widget-token'),
        ]);
    }
}
