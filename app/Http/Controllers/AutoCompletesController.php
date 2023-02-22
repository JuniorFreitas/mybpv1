<?php

namespace App\Http\Controllers;

use App\Models\Admissao;
use App\Models\Cliente;
use App\Models\DocumentoContratos;
use App\Models\FeedbackCurriculo;
use App\Models\Municipio;
use App\Models\User;
use App\Models\Vaga;
use App\Models\VagasAbertas;
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
        return VagasAbertas::whereAtivoSistema(true)->with('Vaga', 'Projetos.Projeto')
            ->whereHas('VagaSelecionada', function ($query) use ($busca, $quantidade) {
                $query->where('nome', 'like', '%' . $busca . '%')->take($quantidade);
            })
//                ->with(['VagaSelecionada.SimuladoVaga.Simulado' => function ($q) {
//                $q->whereAtivo(true);
//            }, 'Municipio', 'VagaSelecionada' => function ($query) use ($busca, $quantidade) {
//                $query->where('nome', 'like', '%' . $busca . '%')->take($quantidade);
//            }])
            ->get()
            ->map(function ($item) {
                $item->label = $item->VagaSelecionada->nome . ' - ' . $item->Municipio->nome . ' - ' . $item->Municipio->uf;
                return $item;
            });
    }

    public function vagasAbertasAtivas(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');
        $busca = $request->query('busca');
        return VagasAbertas::whereAtivoSistema(true)->with('Vaga', 'Projetos.Projeto')
            ->where('titulo', 'like', '%' . $busca . '%')->take($quantidade)
            ->get()
            ->map(function ($item) {
                $item->label = $item->titulo . ' - ' . $item->Municipio->nome . ' - ' . $item->Municipio->uf;
                return $item;
            });
    }

    public function cargosAtivos(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');
        $busca = $request->query('busca');
        if ($busca === '*') {
            return Vaga::whereAtivo(true)
                ->get()
                ->map(function ($item) {
                    $item->label = $item->nome;
                    return $item;
                });
        } else {
            return Vaga::whereAtivo(true)->where('nome', 'like', '%' . $busca . '%')->take($quantidade)
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

        return User::whereEmpresaId(auth()->user()->empresa_id)
            ->whereNotIn('id', [auth()->user()->empresa_id])
//                ->orWhereIn('id', User::LISTA_SUPORTE)
            ->whereAtivo(true)
            ->where('nome', 'like', '%' . $busca . '%')
            ->take($quantidade)
            ->get()
            ->map(function ($item) {
                $item->label = $item->empresa_id == User::MYBP_EMPRESA_ID ? $item->nome . ' - MyBP' : $item->nome;
                return $item;
            });
    }

    public function usuariosAtivosAvaliador(Request $request)
    {
        $busca = $request->texto;
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = 10;

//        $usuariosSelecionados = FeedbackCurriculo::whereIn('id',$request->funcionariosSelecionados)->pluck('curriculo_id')->toArray();

        return User::select(['id', 'nome', 'login', 'tipo', 'ativo'])
            ->TiposGerenciais()
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->whereNotIn('id', $request->funcionariosSelecionados)
            ->whereAtivo(true)
            ->where('nome', 'like', '%' . $busca . '%')
            ->take($quantidade)
            ->get()
            ->map(function ($item) {
                $item->label = $item->nome;
                return $item;
            });

//        return User::whereEmpresaId(auth()->user()->empresa_id)
//            ->whereIn('tipo', User::TIPOS_USUARIOS_GERENCIAIS)
//            ->whereNotIn('id', $usuariosSelecionados)
////                ->whereNotIn('id', User::LISTA_SUPORTE)
//            ->whereAtivo(true)
//            ->where('nome', 'like', '%' . $busca . '%')
//            ->take($quantidade)
//            ->get()
//            ->map(function ($item) {
//                $item->label = $item->empresa_id == User::MYBP_EMPRESA_ID ? $item->nome . ' - MyBP' : $item->nome;
//                return $item;
//            });
    }


    public function colaboradores(Request $request)
    {
        $busca = $request->query('busca');

        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');

        return FeedbackCurriculo::Admitidos()->whereHas('Admissao', function ($q) {
            $q->whereIn('status', ['ADMITIDO']);
        })->whereHas('Curriculo', function ($q) use ($busca) {
            $q->where('nome', 'like', '%' . $busca . '%');
        })->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor', 'VagaAberta.Municipio', 'VagaSelecionada:id,nome')->take($quantidade)
            ->get()->map(function ($item) {
                $item->label = "{$item->Curriculo->nome} - {$item->VagaAberta->VagaSelecionada->nome} - {$item->VagaAberta->Municipio->nome} - {$item->VagaAberta->Municipio->uf}";
                return $item;
            });
    }

    public function colaboradoresFerias(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');
        $busca = $request->query('busca');

        return Admissao::whereNotNull('data_admissao')->admitidos()->whereHas('Feedback', function ($q) use ($busca) {
            $q->whereHas('Curriculo', function ($q) use ($busca) {
                $q->where('nome', 'like', '%' . $busca . '%');
            });
        })->with('Feedback.Curriculo:id,nome,nascimento,rg,orgao_expeditor', 'Feedback.VagaAberta.Municipio', 'Feedback.VagaSelecionada:id,nome')->take($quantidade)
            ->get()->map(function ($item) {
                $item->curriculo_id = $item->Feedback->Curriculo->id;
                $item->label = "{$item->Feedback->Curriculo->nome} - {$item->Feedback->VagaAberta->VagaSelecionada->nome} - {$item->Feedback->VagaAberta->Municipio->nome} - {$item->Feedback->VagaAberta->Municipio->uf}";
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
        return $feedback = FeedbackCurriculo::select(['id', 'vagas_abertas_id', 'curriculo_id'])->Admitidos()->whereHas('Admissao', function ($q) {
            $q->whereIn('status', ['ADMITIDO']);
        })->whereHas('Curriculo', function ($q) use ($busca) {
            $q->where('nome', 'like', '%' . $busca . '%');
        })->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor', 'VagaAberta.Municipio', 'VagaSelecionada:id,nome')->take($quantidade)
            ->get()->map(function ($item) {
                $item->label = "{$item->Curriculo->nome} - {$item->VagaAberta->Vaga->nome}";
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
        return FeedbackCurriculo::Admitidos()->whereHas('Admissao', function ($q) {
            $q->whereIn('status', ['ADMITIDO'])->whereTipoAdmissao('INTERMITENTE');
        })->whereHas('Curriculo', function ($q) use ($busca) {
            $q->where('nome', 'like', '%' . $busca . '%')->where('email', '<>', 'sistema@mybp.com.br');
        })->take($quantidade)
            ->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor,email', 'VagaAberta.Municipio', 'VagaSelecionada:id,nome')->take($quantidade)
            ->get()->map(function ($item) {
                $item->label = "{$item->Curriculo->nome} - {$item->VagaAberta->VagaSelecionada->nome} - {$item->VagaAberta->Municipio->nome} - {$item->VagaAberta->Municipio->uf}";
                return $item;
            });
    }

    public function cargosEmpresa(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');

        return Vaga::where('nome', 'like', '%' . $busca . '%')
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

    public function buscaUsuariosAtivos(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 200);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');
        return User::whereAtivo(true)
            ->where('nome', 'like', '%' . $busca . '%')
            ->whereIn('tipo', User::TIPOS_USUARIOS_GERENCIAIS)
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->with('GrupoCloud:id,nome')
            ->take($quantidade)
            ->get()
            ->map(function ($item) {
                $item->label = $item->nome . ' | ' . $item->tipo;
                return $item;
            });
    }

    //Ponto eletronico (ajustar jornadas)
    public function funcionarios(Request $request)
    {

        $quantidade = $request->query('rows');
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 200);
        }
        return auth()->user()->Empresa->EmpresaFuncionarios()->select(['id', 'nome'])->where('nome', 'like', '%' . $busca . '%')
            ->take($quantidade)
            ->get()->map(function ($item) {
                $item->label = $item->nome;
                return $item;
            });
    }


    public function gestoresAtivos(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');

        $user = \App\Models\User::whereAtivo(true)->whereGestor(true)->whereEmpresaId(auth()->user()->empresa_id);

        return $user->where('nome', 'like', '%' . $busca . '%')
            ->take($quantidade)
            ->get()
            ->map(function ($item) {
                $item->label = $item->nome;
                return $item;
            });
    }

    public function documentosLegaisContrato(Request $request)
    {
        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        $busca = $request->query('busca');

        $contratos = DocumentoContratos::select([
            'id',
            'dados_cadastrais->razao_social as razao_social',
            'dados_cadastrais->nome as nome',
            'dados_cadastrais->tipo as tipo'
        ]);

        return $contratos->where('dados_cadastrais->razao_social', 'like', '%' . $busca . '%')
            ->orWhere('dados_cadastrais->nome', 'like', '%' . $busca . '%')
            ->take($quantidade)
            ->get()
            ->map(function ($item) {
                $item->label = $item->tipo == DocumentoContratos::TIPO_PESSOA_FISICA ? $item->nome : $item->razao_social;
                return $item;
            });
    }
}
