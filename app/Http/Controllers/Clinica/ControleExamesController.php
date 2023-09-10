<?php

namespace App\Http\Controllers\Clinica;

use App\Http\Controllers\Controller;
use App\Models\Admissao;
use App\Models\AlternativaFormulario;
use App\Models\ExameFuncionario;
use App\Models\Examesesmt;
use App\Models\FeedbackCurriculo;
use App\Models\RespostaAlternativas;
use App\Models\Sistema;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class ControleExamesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

//        return $this->atualizar($request);
        return view('g.acesso-clinica.colaboradores.index');
    }

    /**
     * @param Request $request
     * @return FeedbackCurriculo|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function filtro(Request $request)
    {
        $resultado = ExameFuncionario::whereEmpresaId(auth()->user()->empresa_id)
            ->with(['Feedback.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
                'Feedback.VagaSelecionada',
                'QuemEncaminhou:id,nome'])
            ->whereEmpresaExameId(auth()->user()->EmpresaExame->id)->orderByDesc('created_at')
        ;
//        $EmpresaExameId = auth()->user()->EmpresaExame->id;
//        $resultado = FeedbackCurriculo::select(['curriculo_id', 'id', 'vaga_id', 'vagas_abertas_id', 'telefone_id', 'empresa_id'])
//            ->whereHas('ResultadoIntegrado', function ($q) use ($EmpresaExameId) {
//                $q->whereEmpresaExameId($EmpresaExameId);
//            })
//            ->with(
//                'ResultadoIntegrado.Pcmso',
//                'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga,filiacao_pai,filiacao_mae',
//                'Curriculo.FotoTres:id',
//                'VagaAberta:id,vaga_id,titulo,municipio_id,empresa_id',
//                'VagaAberta.VagaSelecionada',
//                'VagaAberta.Municipio',
//                'Empresa:id,razao_social,cnpj,cep,logradouro,numero,complemento,bairro,municipio,uf'
//            );
//
//        $filtroPeriodo = $request->filtroPeriodo == 'true';
//
//        if ($filtroPeriodo) {
//            $periodo = explode(' até ', $request->periodo);
//            $dataInicio = new DataHora($periodo[0]. ' 00:00:00');
//            $dataFim = new DataHora($periodo[1]. ' 23:59:59');
//            $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
//                $q->where('created_at', '>=', $dataInicio->dataHoraInsert())->where('created_at', '<=', $dataFim->dataHoraInsert());
//            });
//        }
//
//        if ($request->filled('campoBusca')) {
//            $resultado->whereHas('Curriculo', function ($query) use ($request) {
//                $query->where('nome', 'like', '%' . $request->campoBusca . '%');
//            });
//        }
//
//        if ($request->filled('campoCPF')) {
//            $resultado->whereHas('Curriculo', function ($query) use ($request) {
//                $query->whereCpf($request->campoBusca);
//            });
//        }
//
//        if ($request->filled('campoVaga')) {
//            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
//                $query->whereId($request->campoVaga);
//            });
//        }
//
//        if ($request->filled('campoStatusAdmissao')) {
//            $resultado->whereHas('Admissao', function ($query) use ($request) {
//                $query->whereStatus($request->campoStatusAdmissao);
//            });
//        }
//
//        if ($request->filled('campoUf')) {
//            $resultado->whereHas('VagaAberta.Municipio', function ($q) use ($request) {
//                $q->whereUf($request->campoUf);
//            });
//        }
//
//        $resultado = $resultado->orderByDesc('created_at');

//        return $resultado;
    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);

        $t = collect($resultado->items());
        $itens = $t->map(function ($item) {
            $tipoOrdem = AlternativaFormulario::whereNome('Tipo de ordem')->whereEmpresaId(auth()->user()->empresa_id)->first()->id;
            $item->tipo_exame = RespostaAlternativas::find($item->respostas['alternativa_id_' . $tipoOrdem]['valor'])->label;
            $item->resultado = Examesesmt::whereExameFuncionarioId($item->id)->with('Anexos')->first();
            return $item;
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
            ],
        ]);
    }
}
