<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\EmpresaTreinamento;
use App\Models\Instrutor;
use App\Models\PessoaEmpresa;
use App\Models\TreinamentoEvento;
use App\Models\TreinamentoSgi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;

class TreinamentoEventoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.treinamentos.sgi.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'data_inicio' => 'required',
            'data_fim' => 'required',
            'empresa_treinamento_id' => 'required',
            'treinamento_sgi_id' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                //todo Verifica se ja existe a pessoa se existir so da um increment
                DB::beginTransaction();
                $dados['cliente_id'] = 1;
                $evento = TreinamentoEvento::create($dados);
                if (isset($dados['anexosDel'])) {
                    foreach ($dados['anexosDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de anexo
                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $evento->Anexos()->attach($arquivo->id);
                        }
                    }
                }
                foreach ($dados['instrutores_evento'] as $instrutor) {
                    $evento->InstrutoresEvento()->attach($instrutor['instrutor_id']);
                }
                foreach ($dados['pessoas_evento'] as $pessoa) {
                    $nota = isset($pessoa['nota']) ? $pessoa['nota'] : null;
                    $pessoaEmpresa = PessoasEmpresa::create($pessoa);
                    $evento->PessoasEvento()->attach($pessoaEmpresa->id, ['nota' => $nota]);
                }
                DB::commit();
                return response()->json([$evento], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE FREQUÊNCIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\TreinamentoEvento $treinamento
     * @return \Illuminate\Http\Response
     */
    public function show(TreinamentoEvento $treinamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\TreinamentoEvento $treinamento
     * @return \Illuminate\Http\Response
     */
    public function edit(Clientes $cliente_id, TreinamentoEvento $treinamento)
    {
        $treinamento->load('Cliente', 'TreinamentoSgi', 'PessoasEvento', 'Anexos');

        if ($treinamento->InstrutoresEvento) {
            $treinamento->InstrutoresEvento->transform(function ($item) {
                $item->instrutor_id = $item->id;
                return $item;
            });
        } else {
            $treinamento->load('InstrutoresEvento');
        }

        if ($treinamento->PessoasEvento) {
            $treinamento->PessoasEvento->transform(function ($item) {
                $item->nota = $item->pivot->nota;
                return $item;
            });
        } else {
            $treinamento->load('PessoasEvento');
        }

        return $treinamento;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\TreinamentoEvento $treinamento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Clientes $cliente_id, TreinamentoEvento $treinamento)
    {
        $evento = $treinamento;
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'data_inicio' => 'required',
            'data_fim' => 'required',
            'empresa_treinamento_id' => 'required',
            'treinamento_sgi_id' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                //todo Verifica se ja existe a pessoa se existir so da um increment
                DB::beginTransaction();
                $dados['cliente_id'] = 1;
                $evento->update($dados);

                //apagando os pivot para poder criar novo
                $evento->InstrutoresEvento()->detach();
                $evento->PessoasEvento()->detach();

                if (isset($dados['anexosDel'])) {
                    foreach ($dados['anexosDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de anexo
                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $evento->Anexos()->attach($arquivo->id);
                        }
                    }
                }

                foreach ($dados['instrutores_evento'] as $instrutor) {
                    if (isset($instrutor['novo'])) {
                        $evento->InstrutoresEvento()->attach($instrutor['instrutor_id']);
                    } else {
                        $evento->InstrutoresEvento()->attach($instrutor['instrutor_id']);
                    }
                }
                foreach ($dados['pessoas_evento'] as $pessoa) {
                    $nota = isset($pessoa['nota']) ? $pessoa['nota'] : null;
                    if (isset($pessoa['novo'])) {
                        $pessoaEmpresa = PessoasEmpresa::create($pessoa);
                        $evento->PessoasEvento()->attach($pessoaEmpresa->id, ['nota' => $nota]);
                    } else {
                        $evento->PessoasEvento()->attach($pessoa['id'], ['nota' => $nota]);
                    }
                }
                DB::commit();
                return response()->json([$evento], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE FREQUÊNCIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\TreinamentoEvento $treinamento
     * @return \Illuminate\Http\Response
     */
    public function destroy(TreinamentoEvento $treinamento)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $resultado = TreinamentoEvento::Empresa()->with('EmpresaTreinamento', 'TreinamentoSgi','Anexos')->withCount('PessoasEvento as qnt_pessoas', 'InstrutoresEvento as qnt_instrutores');
        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        $listaTreinamentosSgi = TreinamentoSgi::orderBy('nome')->get();
        $empresasTreinamentos = EmpresaTreinamento::orderBy('nome')->get();
        $listaInstrutores = Instrutor::whereAtivo(true)->orderBy('nome')->get(['id', 'nome']);
        $listaClientes = Cliente::whereAtivo(true)->orderBy('nome')->orderBy('razao_social')->get(['id', 'cpf', 'cnpj', 'nome', 'razao_social', 'nome_fantasia']);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $resultado->items(),
                'cliente_id' => auth()->user()->cliente_id,
                'listaTreinamentos' => $listaTreinamentosSgi,
                'listaEmpresasTreinamentos' => $empresasTreinamentos,
                'listaInstrutores' => $listaInstrutores,
                'listaClientes' => $listaClientes,
            ]
        ]);
    }

    public function buscaCPF(Request $request)
    {
        $curriculo = Curriculo::whereCpf($request->cpf);
        $pessoaEvento = PessoaEmpresa::whereCpf($request->cpf);
        if ($curriculo->count() > 0) {
            $curriculo = $curriculo->first();
            $dados = [
                'nome' => $curriculo->nome,
                'email' => $curriculo->email,
                'telefone' => $curriculo->FeedBack ? $curriculo->FeedBack->TelPrincipal ? $curriculo->FeedBack->TelPrincipal->numero : null : null,
            ];
            return response()->json($dados, 200);
        }

        if ($pessoaEvento->count() > 0) {
            $pessoaEvento = $pessoaEvento->first();
            $dados = [
                'nome' => $pessoaEvento->nome,
                'email' => $pessoaEvento->email,
                'telefone' => $pessoaEvento->telefone,
            ];
            return response()->json($dados, 200);
        }

        if ($pessoaEvento->count() == 0 && $curriculo->count()) {
            return response()->json('zero', 200);
        }
    }

    //PDF
    public function listaPresencaPdf($cliente_id, TreinamentoEvento $treinamento)
    {
        $treinamento->load('Cliente', 'TreinamentoSgi', 'PessoasEvento');

        if ($treinamento->InstrutoresEvento) {
            $treinamento->InstrutoresEvento->transform(function ($item) {
                $item->instrutor_id = $item->id;
                return $item;
            });
        } else {
            $treinamento->load('InstrutoresEvento');
        }

        if ($treinamento->PessoasEvento) {
            $treinamento->PessoasEvento->transform(function ($item) {
                $item->nota = $item->pivot->nota;
                return $item;
            });
        } else {
            $treinamento->load('PessoasEvento');
        }

        $dados = $treinamento;
//        return $dados;
        $pdf = PDF::loadView('pdf.treinamento.listaPresenca', compact('dados'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream(Str::slug($treinamento->TreinamentoSgi->nome) . ".pdf");
    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_TREINAMENTO_LISTA_PRESENCA);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow([Arquivo::DISCO_TREINAMENTO_LISTA_PRESENCA], $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete([Arquivo::DISCO_TREINAMENTO_LISTA_PRESENCA], $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload([Arquivo::DISCO_TREINAMENTO_LISTA_PRESENCA], $arquivo);
    }
}
