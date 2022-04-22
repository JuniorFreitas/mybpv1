<?php

namespace App\Http\Controllers;

use App\Jobs\Entrevista\JobEnvioDocumento;
use App\Mail\Entrevista\EnvioDocumentosMail;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class PreAdmissaoController extends Controller
{

    public function index()
    {
        return view('g.admissao.preadmissao.index');
    }

    public function show(FeedbackCurriculo $feedback)
    {
        return $feedback->load(
            'Curriculo:id,nome,cpf,email,nascimento,rg,orgao_expeditor,logradouro,complemento,bairro,municipio,uf',
            'Cliente:id,nome,razao_social,nome_fantasia',
            'VagaSelecionada:id,nome',
            'Admissao:feedback_id,data_admissao,funcao,cargo,status',
            'Curriculo.Telefones',
            'Curriculo.FotoTres',
            'Curriculo.AnexosCpfRg',
            'Curriculo.ComprovanteEnd',
            'Curriculo.CtpsFrente',
            'Curriculo.CtpsVerso',
            'Curriculo.Antecedentes',
            'Curriculo.TituloEleitor',
            'Curriculo.CertificadoReservista',
            'Curriculo.PisRescisao',
            'Curriculo.CertificadoEscolaridade',
            'Curriculo.ContaBanco',
            'Curriculo.CartaSindicato',
            'Curriculo.CarteiraVacina',
            'Curriculo.RgcpfFilho',
            'Curriculo.CartaoVacinaFilho',
            'Curriculo.DeclaracaoEscolarFilho'
        );
    }

    public function atualizar(Request $request)
    {
        $resultado = FeedbackCurriculo::whereHas('ResultadoIntegrado', function ($q) {
            $q->whereDocumentosEntregue(true);
        })->with(
            'Curriculo:id,nome,cpf,email,nascimento,rg,orgao_expeditor,logradouro,complemento,bairro,municipio,uf',
            'Cliente:id,nome,razao_social,nome_fantasia',
            'VagaSelecionada:id,nome',
            'Admissao:feedback_id,data_admissao,funcao,cargo,status',
        );

        $filtroPeriodo = $request->filtroPeriodo == 'true';

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
                $q->where('created_at', '>=', $dataInicio->dataInsert())->where('created_at', '<=', $dataFim->dataInsert());
            });
        }
        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->whereCpf($request->campoBusca);
            });
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('VagaAberta.Municipio', function ($q) use ($request) {
                $q->whereUf($request->campoUf);
            });
        }


        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['items' => $resultado->items(), 'usuario_cliente_id' => auth()->user()->cliente_id]
        ]);
    }

    public function edit(FeedbackCurriculo $feedback)
    {
        return $feedback->load('Curriculo.Pessoa');
    }

    public function enviarEmail(Request $request)
    {
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $feedback = FeedbackCurriculo::whereId($dados['id'])->first();
            $feedback['email'] = $dados['email'];
            $curriculo = Curriculo::whereId($dados['curriculo_id'])->with('Pessoa')->first();
            $curriculo->update(['email' => $dados['email']]);
            $curriculo->Pessoa->update(['login' => $dados['email']]);
            DB::commit();
            JobEnvioDocumento::dispatch([
                'nome' => $curriculo->nome,
                'email' => $feedback['email'],
                'empresa_id' => $feedback->empresa_id
            ]);
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => 'Erro ao enviar e-mail', 'erros' => $e->getTraceAsString()], 400);
        }
    }
}
