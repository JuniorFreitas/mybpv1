<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ExamesAdminController extends Controller
{
    public function index(): View
    {
        $this->authorize('cadastro_empresa_exame');

        return view('g.cadastros.exames.index');
    }
}
