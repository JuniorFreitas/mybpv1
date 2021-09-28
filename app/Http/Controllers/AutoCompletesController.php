<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\FeedbackCurriculo;
use App\Models\Municipio;
use App\Models\User;
use App\Models\Vaga;
use Illuminate\Http\Request;

class AutoCompletesController extends Controller
{
    public function vagasAtivas(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');
        $busca = $request->query('busca');
        if ($busca === '*') {
            return Vaga::whereAtivo(true)->with(['SimuladoVaga.Simulado' => function ($q) {
                $q->whereAtivo(true);
            }])
                ->get()
                ->map(function ($item) {
                    $item->label = $item->nome;
                    return $item;
                });
        } else {
            return Vaga::whereAtivo(true)->with(['SimuladoVaga.Simulado' => function ($q) {
                $q->whereAtivo(true);
            }])
                ->where('nome', 'like', '%' . $busca . '%')
                ->take($quantidade)
                ->get()
                ->map(function ($item) {
                    $item->label = $item->nome;
                    return $item;
                });
        }
    }

    public function clientesAtivos(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');
        if ($busca === '*') {
            return Cliente::whereAtivo(true)
                ->get()
                ->map(function ($item) {
                    if ($item->tipo == Cliente::TIPO_PESSOA_JURIDICA) {
                        $label = $item->nome_fantasia . ' | ' . $item->cnpj;
                    } else {
                        $label = $item->nome . ' | ' . $item->cnpj;
                    }
                    $item->label = $label;
                    return $item;
                });
        } else {
            return Cliente::whereAtivo(true)
                ->where('nome_fantasia', 'like', '%' . $busca . '%')
                ->orWhere('nome', 'like', '%' . $busca . '%')
                ->take($quantidade)
                ->get()
                ->map(function ($item) {
                    if ($item->tipo == Cliente::TIPO_PESSOA_JURIDICA) {
                        $label = $item->nome_fantasia . ' | ' . $item->cnpj;
                    } else {
                        $label = $item->nome . ' | ' . $item->cnpj;
                    }
                    $item->label = $label;
                    return $item;
                });
        }

    }

    public function municipiosAll(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');
        if ($busca === '*') {
            return Municipio::get()
                ->map(function ($item) {
                    $item->label = $item->nome . ' - ' . $item->uf;
                    return $item;
                });
        } else {
            return Municipio::where('nome', 'like', '%' . $busca . '%')
                ->take($quantidade)
                ->orderByDesc('capital')
                ->get()
                ->map(function ($item) {
                    $item->label = $item->nome . ' - ' . $item->uf;
                    return $item;
                });
        }
    }

    public function usuariosAtivos(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');
        if ($busca === '*') {
            return User::whereAtivo(true)
                ->get()
                ->map(function ($item) {
                    $item->label = $item->nome;
                    return $item;
                });
        } else {
            return User::whereAtivo(true)
                ->where('nome', 'like', '%' . $busca . '%')
                ->take($quantidade)
                ->get()
                ->map(function ($item) {
                    $item->label = $item->nome;
                    return $item;
                });
        }

    }

    public function colaboradores(Request $request, $cliente_id)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');

        return $feedback = FeedbackCurriculo::whereClienteId($cliente_id)->whereHas('Admissao', function ($q) {
            $q->whereIn('status', ['ADMITIDO']);
        })->whereHas('Curriculo', function ($q) use ($busca) {
            $q->where('nome', 'like', '%' . $busca . '%');
        })->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor', 'VagaSelecionada:id,nome', 'Cliente:id,nome_fantasia')->take($quantidade)
            ->get()->map(function ($item) {
                $item->label = "{$item->Curriculo->nome} - {$item->VagaSelecionada->nome} - {$item->Cliente->nome_fantasia}";
                return $item;
            });
    }

    public function colaboradorCih(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');
        return $feedback = FeedbackCurriculo::whereHas('Admissao', function ($q) {
            $q->whereIn('status', ['ADMITIDO']);
        })->whereHas('Curriculo', function ($q) use ($busca) {
            $q->where('nome', 'like', '%' . $busca . '%');
        })->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor', 'VagaSelecionada:id,nome', 'Cliente:id,nome_fantasia')->take($quantidade)
            ->get()->map(function ($item) {
                $item->label = "{$item->Curriculo->nome} - {$item->VagaSelecionada->nome} - {$item->Cliente->nome_fantasia}";
                return $item;
            });
    }

    public function colaboradorIntermitente(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');
        return $feedback = FeedbackCurriculo::whereClienteId(auth()->user()->cliente_id)
            ->whereHas('Admissao', function ($q) {
                $q->whereIn('status', ['ADMITIDO']);
            })->whereHas('Curriculo', function ($q) use ($busca) {
                $q->where('nome', 'like', '%' . $busca . '%');
            })->take($quantidade)
            ->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor', 'VagaSelecionada:id,nome', 'Cliente:id,nome_fantasia', 'Exame', 'Curriculo.Treinamentos.Vencimentos')->take($quantidade)
            ->get()->map(function ($item) {
                $item->label = "{$item->Curriculo->nome} - {$item->VagaSelecionada->nome} - {$item->Cliente->nome_fantasia}";
                return $item;
            });
    }

    public function colaboradorIntermitenteCliente(Request $request,$cliente_id)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');
        return $feedback = FeedbackCurriculo::whereClienteId($cliente_id)
            ->whereHas('Admissao', function ($q) {
                $q->whereIn('status', ['ADMITIDO'])->whereIn('tipo_admissao', ['INTERMITENTE']);
            })->whereHas('Curriculo', function ($q) use ($busca) {
                $q->where('nome', 'like', '%' . $busca . '%');
            })->take($quantidade)
            ->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor', 'VagaSelecionada:id,nome', 'Cliente:id,nome_fantasia', 'Exame')->take($quantidade)
            ->get()->map(function ($item) {
                $item->label = "{$item->Curriculo->nome} - {$item->VagaSelecionada->nome} - {$item->Cliente->nome_fantasia}";
                return $item;
            });
    }

    public function cargosEmpresa(Request $request, Cliente $cliente)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');

        return $cliente->Cargos()
            ->where('nome', 'like', '%' . $busca . '%')
            ->whereAtivo(true)
            ->take($quantidade)
            ->get()->map(function ($item) {
                $item->label = $item->nome;
                return $item;
            });
    }

    //Weekly report ---------------------------------------
    public function buscarMembros(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 200);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');
        return User::whereAtivo(true)
            ->where('nome', 'like', '%' . $busca . '%')
            ->whereTipo(User::ADMINISTRADOR)
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->take($quantidade)
            ->get()
            ->map(function ($item) {
                $item->label = $item->nome;
                return $item;
            });

    }

    //Ponto eletronico (ajustar jornadas)
    public function funcionarios(Request $request){

        $quantidade = $request->query('rows');
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 200);
        }
        return auth()->user()->Empresa->EmpresaFuncionarios()->select(['id','nome'])->where('nome', 'like', '%' . $busca . '%')
            ->take($quantidade)
            ->get()->map(function ($item) {
                $item->label = $item->nome;
                return $item;
            });
    }
}
