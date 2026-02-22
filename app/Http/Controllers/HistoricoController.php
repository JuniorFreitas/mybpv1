<?php

namespace App\Http\Controllers;

use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\AvaliacaoNoventaDias;
use App\Models\AvaliacaoNoventaFeedback;
use App\Models\AvaliacaoNoventaFeedbackQuantidade;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\FeedbackCurriculo;
use App\Models\LogHistorico;
use App\Models\MedidaAdministrativa;
use App\Models\User;
use App\Models\Vaga;
use App\Services\Historico\MedidaAdministrativaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;

class HistoricoController extends Controller
{
    public function index()
    {
        return view('g.admissao.historico.index');
    }

    public function show(Request $request, $feedback)
    {
        $feedback_id = $feedback;

        $feedback = FeedbackCurriculo::whereId($feedback_id)
            ->with([
                'MedidasAdministrativas.Anexos',
                'Curriculo:id,nome,cpf',
                'Empresa:id,nome,nome_fantasia',
                'VagaAberta.Vaga:id,nome'
            ])
            ->first();
        $perguntas = AvaliacaoNoventaDias::get()->transform(function ($item) {
            $item->nota = '';
            return $item;
        });
        $tabelaNoventa = AvaliacaoNoventaFeedbackQuantidade::with('Feedback')->whereFeedbackId($feedback_id)->get();

        $avNoventaVencimento = Admissao::whereFeedbackId($feedback_id)->with('Feedback.AvaliacaoNoventaVencimento')->first();
        $restricao = new DataHora();
        $restricao->addDia(2);

        return response()->json([
            'feedback' => $feedback,
            'causas' => MedidaAdministrativa::CAUSAS,
            'definicao' => MedidaAdministrativa::DEFINICAO,
            'tipos' => MedidaAdministrativa::TIPOS,
            'perguntas' => $perguntas,
            'tabelaNoventa' => $tabelaNoventa,
            'avNoventaVencimento' => $avNoventaVencimento,
            'hoje' => (new DataHora())->dataCompleta(),
            'restricao' => $restricao->dataCompleta(),
            'privilegio_gestao_rh' => auth()->user()->can('privilegio_gestao_rh')
        ], 200);
    }

    public function atualizar(Request $request)
    {
//        $resultado = Admissao::with(['Feedback' => function ($q) {
//            $q->with('Curriculo', 'Cliente:id,nome,razao_social,cpf,cnpj,nome_fantasia', 'VagaSelecionada:id,nome');
//        }])->whereIn('status', ['ADMITIDO']);

        $resultado = FeedbackCurriculo::whereHas('Admissao', function ($q) {
            $q->whereIn('status', [Admissao::STATUS_ADMISSAO_ADMITIDO, Admissao::STATUS_DEMITIDO]);
        })->with('Admissao', 'Curriculo', 'Cliente:id,nome,razao_social,cpf,cnpj,nome_fantasia', 'VagaSelecionada:id,nome');

        $cliente = new Cliente();
        $filialOuMatriz = $cliente->findFiliarOuMatriz(auth()->user()->empresa_id);
        $temFilial = !isset($filialOuMatriz['matriz']);
        if (!$temFilial && isset($filialOuMatriz['cnpjkey'])) {
            $request->merge(['campoCnpj' => $filialOuMatriz['cnpjkey']]);
        }
        if ($request->filled('campoCnpj')) {
            $resultado->filtrarPorCnpjECentroCusto($request);
        }

        if ($request->campoDemitido) {
            $resultado->Demitidos();
        } else {
            $resultado->Admitidos();
        }
        if ($request->filled('campoBusca')) {
            $resultado->where(function ($query) use ($request) {
                $query->whereHas('Curriculo', function ($q) use ($request) {
                    $q->where('nome', 'like', '%' . $request->campoBusca . '%');
                })->orWhere('id', $request->campoBusca);
            });
        }
        if ($request->filled('campoCargo')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->where('cargo', 'like', '%' . $request->campoCargo . '%');
            });
        }
        if ($request->filled('campoCPF')) {
            $cpf = preg_replace('/[^0-9]/', '', $request->campoCPF);
            $resultado->whereHas('Curriculo', function ($q) use ($cpf) {
                $q->whereRaw('REPLACE(REPLACE(REPLACE(cpf, ".", ""), "-", ""), "/", "") like ?', ['%' . $cpf . '%']);
            });
        }
        if ($request->filled('campoMatricula')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->where('matricula', 'like', '%' . $request->campoMatricula . '%');
            });
        }
        if ($request->filled('campoTipoAdmissao')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->where('tipo_admissao', $request->campoTipoAdmissao);
            });
        }
        if ($request->filled('campoFuncao')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->where('funcao', 'like', '%' . $request->campoFuncao . '%');
            });
        }
        $cargos = Vaga::whereAtivo(true)->orderBy('nome')->get(['id', 'nome']);
        $tiposAdmissao = Admissao::TODOS_TIPOS_ADMISSAO;

        /*   $idsCargos = DB::table('feedback_curriculos')->distinct('vaga_id')->pluck('vaga_id');

           $cargos = [];
           foreach ($idsCargos as $id) {
               $cargos[]=[
                   'id' => $id,
                   'nome' => Vaga::find($id)->nome
                   ];
           }*/

        $ordenacao = $request->filled('ordenacao') ? $request->ordenacao : 'created_at_desc';
        $campo = 'feedback_curriculos.created_at';
        $direcao = 'desc';
        switch ($ordenacao) {
            case 'created_at_asc':
                $campo = 'feedback_curriculos.created_at';
                $direcao = 'asc';
                break;
            case 'updated_at_desc':
                $ultimoLogSub = LogHistorico::selectRaw('feedback_id, MAX(id) as ultimo_log_id')->groupBy('feedback_id');
                $resultado = $resultado->leftJoinSub($ultimoLogSub, 'ultimo_log', 'feedback_curriculos.id', '=', 'ultimo_log.feedback_id')
                    ->orderByRaw('COALESCE(ultimo_log.ultimo_log_id, 0) DESC')
                    ->select('feedback_curriculos.*');
                break;
            case 'updated_at_asc':
                $ultimoLogSub = LogHistorico::selectRaw('feedback_id, MAX(id) as ultimo_log_id')->groupBy('feedback_id');
                $resultado = $resultado->leftJoinSub($ultimoLogSub, 'ultimo_log', 'feedback_curriculos.id', '=', 'ultimo_log.feedback_id')
                    ->orderByRaw('COALESCE(ultimo_log.ultimo_log_id, 0) ASC')
                    ->select('feedback_curriculos.*');
                break;
            case 'nome_asc':
                $resultado = $resultado->join('curriculos', 'feedback_curriculos.curriculo_id', '=', 'curriculos.id')
                    ->orderBy('curriculos.nome', 'asc')
                    ->select('feedback_curriculos.*');
                break;
            case 'nome_desc':
                $resultado = $resultado->join('curriculos', 'feedback_curriculos.curriculo_id', '=', 'curriculos.id')
                    ->orderBy('curriculos.nome', 'desc')
                    ->select('feedback_curriculos.*');
                break;
            case 'data_admissao_desc':
                $resultado = $resultado->join('admissoes', 'feedback_curriculos.id', '=', 'admissoes.feedback_id')
                    ->orderBy('admissoes.data_admissao', 'desc')
                    ->select('feedback_curriculos.*');
                break;
            case 'data_admissao_asc':
                $resultado = $resultado->join('admissoes', 'feedback_curriculos.id', '=', 'admissoes.feedback_id')
                    ->orderBy('admissoes.data_admissao', 'asc')
                    ->select('feedback_curriculos.*');
                break;
            case 'cargo_asc':
                $resultado = $resultado->join('admissoes', 'feedback_curriculos.id', '=', 'admissoes.feedback_id')
                    ->orderBy('admissoes.cargo', 'asc')
                    ->select('feedback_curriculos.*');
                break;
            case 'cargo_desc':
                $resultado = $resultado->join('admissoes', 'feedback_curriculos.id', '=', 'admissoes.feedback_id')
                    ->orderBy('admissoes.cargo', 'desc')
                    ->select('feedback_curriculos.*');
                break;
            default:
                break;
        }
        if (! in_array($ordenacao, ['nome_asc', 'nome_desc', 'data_admissao_desc', 'data_admissao_asc', 'cargo_asc', 'cargo_desc', 'updated_at_desc', 'updated_at_asc'], true)) {
            $resultado = $resultado->orderBy($campo, $direcao);
        }
        $resultado = $resultado->paginate($request->pages);
        $permissoes = [
            'dossie' => auth()->user()->can('admissao_historico_aba_dossie'),
            'medida_administrativa' => auth()->user()->can('admissao_historico_aba_medidas_administrativas'),
            'feedback' => auth()->user()->can('admissao_historico_aba_feedback'),
            'avaliacao_noventa_dias' => auth()->user()->can('admissao_historico_aba_avaliacao_noventa_dias'),
            'avaliacao_anual' => auth()->user()->can('admissao_historico_aba_avaliacao_anual'),
            'ferias' => auth()->user()->can('admissao_historico_aba_ferias'),
            'promocao' => auth()->user()->can('admissao_historico_aba_promocao'),
            'metas' => auth()->user()->can('admissao_historico_aba_metas'),
            'beneficio' => auth()->user()->can('cadastro_beneficio'),
            'cih' => auth()->user()->can('admissao_cih'),
            'afastamento' => auth()->user()->can('admissao_historico_aba_afastamento'),
            'filtrar_demitido' => auth()->user()->can('admissao_historico_filtrar_demitido'),
            'logs' => auth()->user()->can('admissao_historico_aba_log'),
            'privilegio_gestao_rh' => auth()->user()->can('privilegio_gestao_rh'),
        ];

        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
        $itens = collect($resultado->items())->transform(function ($item) use ($cc) {
            $ultima_atualizacao = LogHistorico::whereFeedbackId($item->id)->orderByDesc('id')->first();
            $item->ultima_atualizacao = $ultima_atualizacao ? $ultima_atualizacao->data : '';

                if ($item->admissao) {
                    $item->admissao->emp_centro_custo = null;
                    $item->admissao->emp_nome_fantasia = null;
                    $item->admissao->emp_razao_social = null;
                    $item->admissao->emp_tipo = null;
                $centrosFlat = collect($cc['centros_custos'] ?? [])->collapse();
                if ($item->admissao->filial && $item->admissao->centro_custo_filial_id) {
                    $cc_colaborador = $centrosFlat->where('filial_id', $item->admissao->centro_custo_filial_id)->first();
                } else {
                    $cc_colaborador = $centrosFlat->where('id', $item->admissao->centro_custo_id)->where('matriz', true)->first();
                }
                if ($cc_colaborador) {
                    $item->admissao->emp_centro_custo = $cc_colaborador['label'];
                    $item->admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'] ?? null;
                    $item->admissao->emp_razao_social = $cc_colaborador['razao_social'] ?? null;
                    $item->admissao->emp_tipo = !empty($cc_colaborador['matriz']) ? 'Matriz' : 'Filial';
                }
            }
            return $item;
        });
        $listaCentrosCusto = [];
        if (!$temFilial && isset($filialOuMatriz['cnpjkey'], $cc['centros_custos'][$filialOuMatriz['cnpjkey']])) {
            $listaCentrosCusto = $cc['centros_custos'][$filialOuMatriz['cnpjkey']]->values()->toArray();
        }

        $listaCnpjs = isset($cc['cnpjs']) ? $cc['cnpjs']->toArray() : [];
        $listaCentrosPorCnpj = [];
        if (isset($cc['centros_custos'])) {
            foreach ($cc['centros_custos'] as $cnpjKey => $coll) {
                $listaCentrosPorCnpj[$cnpjKey] = $coll->values()->toArray();
            }
        }

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'cargos' => $cargos,
                'permissoes' => $permissoes,
                'lista_centros_custo' => $listaCentrosCusto,
                'lista_cnpjs' => $listaCnpjs,
                'lista_centros_por_cnpj' => $listaCentrosPorCnpj,
                'tem_filial' => $temFilial,
                'tipos_admissao' => $tiposAdmissao
            ]
        ]);
    }

    //************MEDIDAS ADMINISTRATIVAS**************//
    public function storeMedidas(Request $request, $feedback)
    {
        $service = new MedidaAdministrativaService();
        $dados = $request->input();

        $dadosValidados = $service->validarDados($dados);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            $service->storeMedidas($dados, $feedback);
            return response()->json([], 201);
        } catch (\Exception $e) {
            $usuario = User::find(auth()->id());
            $msg = "error STORE MEDIDAS ADMINISTRATIVAS: {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . ($usuario ? $usuario->nome : 'N/A');
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function updateMedidas(Request $request, $feedback)
    {
        $service = new MedidaAdministrativaService();
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
//            'data_inicio' => 'required',
//            'data_fim' => 'required',
//            'empresa_treinamento_id' => 'required',
//            'treinamento_sgi_id' => 'required',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            $service->updateMedidas($dados, $feedback);
            return response()->json([], 201);
        } catch (\Exception $e) {
            $usuario = User::find(auth()->id());
            $msg = "error UPDATE MEDIDAS ADMINISTRATIVAS: {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . ($usuario ? $usuario->nome : 'N/A');
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function medidasAdministrativasPDF($medida, $feedback_id)
    {
        $service = new MedidaAdministrativaService();
        $medida = $service->buscarParaPDF($medida);

        if (!$medida) {
            return abort(404);
        }

        $pdf = PDF::loadView('pdf.admissao.historico.medidasadministrativas.carta-advertencia', compact('medida'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("carta_" . Str::slug($medida->tipo) . (new DataHora())->nomeUnico() . ".pdf");
    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_MEDIDAS);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_MEDIDAS, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_MEDIDAS, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_MEDIDAS, $arquivo);
    }

    //**************************FORMULARIO NOVENTA DIAS**************************//

    public function storeFormularioNoventaDias(Request $request)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'gestor_imediato' => 'required'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar as Notas',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $total = AvaliacaoNoventaFeedbackQuantidade::where('feedback_id', $dados['feedback_id'])
                    ->sum('quantidade_avaliacao');
                $qntAvaliacao = $total > 0 ? intval($total) + 1 : 1;

                $info = [
                    'feedback_id' => $dados['feedback_id'],
                    'quantidade_avaliacao' => $qntAvaliacao,
                ];
                AvaliacaoNoventaFeedbackQuantidade::create($info);

                foreach ($dados['perguntas'] as $form) {
                    $formulario = [];
                    $formulario['feedback_id'] = $dados['feedback_id'];
                    $formulario['pergunta_id'] = $form['id'];
                    $formulario['gestor_id'] = auth()->user()->id;
                    $formulario['nota'] = $form['nota'];
                    $formulario['quantidade_avaliacao'] = $qntAvaliacao;
                    $formulario['gestor_imediato'] = $dados['gestor_imediato'];
                    $formulario['observacao'] = $dados['observacao'];
                    AvaliacaoNoventaFeedback::create($formulario);
                }
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE AVALIACAO NOVENTA FEEDBACK:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} " . User::find(auth()->id())->nome;
                \Log::debug($msg);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
                return response()->json(['msg' => $msg], 400);
            }
        }
    }

    public function formularioNoventaDiasPDF($quantidade_avaliacao, $feedback_id)
    {
        $avaliacaoPerguntas = AvaliacaoNoventaFeedback::whereFeedbackId($feedback_id)->whereQuantidadeAvaliacao($quantidade_avaliacao)->get();
        $avaliacao = AvaliacaoNoventaFeedbackQuantidade::whereFeedbackId($feedback_id)->whereQuantidadeAvaliacao($quantidade_avaliacao)->first();
        $pdf = PDF::loadView('pdf.admissao.historico.formularionoventadias.avaliacao', compact('avaliacao', 'avaliacaoPerguntas'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream((new DataHora())->nomeUnico() . ".pdf");

    }

    public function removerMedidaAdministrativa(Request $request)
    {
        $this->authorize('privilegio_gestao_rh');
        $dados = $request->input();
        
        try {
            $service = new MedidaAdministrativaService();
            $medidaId = $dados['medida_id'] ?? $dados['id'];
            $motivo = $dados['motivo'] ?? null;
            
            $service->removerMedidaAdministrativa($medidaId, $motivo);
            
            return response()->json([], 201);
        } catch (\Exception $e) {
            $msg = "error HISTÓRICO - REMOVER MEDIDA ADMINISTRATIVA: {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $e->getMessage() === 'Medida administrativa não encontrada!' ? $e->getMessage() : 'Houve um erro por favor tente novamente!'], 400);
        }
    }
}
