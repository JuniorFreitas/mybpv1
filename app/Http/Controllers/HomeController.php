<?php

namespace App\Http\Controllers;

use App\Models\Exportacao;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function dashboard()
    {
        $empresa = auth()->user()->empresa_id;
        return view('g.dashboard.index', compact('empresa'));
    }

    public function concordarTermos()
    {
        auth()->user()->update(['termos' => true]);
        return response()->json([], 201);
    }

    public function saveToken(Request $request)
    {
        auth()->user()->update(['device_token' => $request->token]);

        return response()->json(['token saved successfully.']);
    }

    public function downloads()
    {
        $downloads = auth()->user()->Exportacoes()->get();
        return response()->json($downloads);
    }

    public function downloadArquivo($arquivo)
    {
        $dono = Exportacao::whereArquivo($arquivo)->whereUserId(auth()->user()->id)->first();
        if ($dono) {
            $disco = 'disco-exportacao';
            if (Storage::disk($disco)->exists($arquivo)) {
                return \Storage::disk($disco)->response($arquivo);
            }
        }
        abort(404);
    }

}