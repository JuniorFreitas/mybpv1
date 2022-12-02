<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\AvaliacaoTipo;
use App\Models\User;
use App\Rules\TenantUniqueRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use MasterTag\DataHora;

class AvaliacaoController extends Controller
{
    public function index(Request $request)
    {
        return view('g.cadastros.avaliacoes.avaliacao.index');
    }

    public function store(Request $request)
    {
//        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
//        dd($dados);
        $titulo = $dados['titulo'];

        $arrayValidacao = [
            'titulo' => [
                function ($attribute, $value, $fail) use ($dados) {
                    if (strlen($value) <= 3) {
                        $fail('Informe uma título maior que 3 caracteres.');
                    }
                },
                'required',
                new TenantUniqueRules('avaliacoes', $request->segment(5))
            ],
            'avaliacao_tipo_id' => [function ($attribute, $value, $fail) use ($dados) {
                $avaliacao_tipo_id = $dados['avaliacao_tipo_id'];
                $avaliacaotipo = AvaliacaoTipo::whereId($avaliacao_tipo_id)->first();
                if(!$avaliacaotipo){
                    $fail('Verificar o tipo de avaliação');
                }
            }],
            'data_inicio_prazo' => [function ($attribute, $value, $fail) use ($dados) {
                $datainicio = $dados['data_inicio_prazo'];
                $dataencerramento = $dados['data_fim_prazo'];

                $diff_dias = DataHora::diferencaDias($datainicio, $dataencerramento);

                if ($diff_dias < 0) {
                    $fail('Data Fim precisa ser maior que a Data início');
                }
            }],
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar a avaliação',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            Avaliacao::create($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE AVALIAÇÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit(Avaliacao $avaliacao)
    {
        return $avaliacao;
    }

    public function show(AvaliacaoTipo $avaliacaotipo)
    {
//        $this->authorize('administracao_documentos_legais_insert');
        return $avaliacaotipo;
    }

    public function update(Request $request, Avaliacao $avaliacao)
    {
        //        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
//        dd($dados);
        $titulo = $dados['titulo'];

        $arrayValidacao = [
            'titulo' => [
                function ($attribute, $value, $fail) use ($dados) {
                    if (strlen($value) <= 3) {
                        $fail('Informe uma título maior que 3 caracteres.');
                    }
                },
                'required',
                new TenantUniqueRules('avaliacoes', $request->segment(5))
            ],
            'avaliacao_tipo_id' => [function ($attribute, $value, $fail) use ($dados) {
                $avaliacao_tipo_id = $dados['avaliacao_tipo_id'];
                $avaliacaotipo = AvaliacaoTipo::whereId($avaliacao_tipo_id)->first();
                if(!$avaliacaotipo){
                    $fail('Verificar o tipo de avaliação');
                }
            }],
            'data_inicio_prazo' => [function ($attribute, $value, $fail) use ($dados) {
                $datainicio = $dados['data_inicio_prazo'];
                $dataencerramento = $dados['data_fim_prazo'];

                $diff_dias = DataHora::diferencaDias($datainicio, $dataencerramento);

                if ($diff_dias < 0) {
                    $fail('Data Fim precisa ser maior que a Data início');
                }
            }],
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao editar a avaliação',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $avaliacao->update($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error UPDATE AVALIAÇÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function filtro(Request $request)
    {
        $resultado = Avaliacao::with('AvaliacaoTipo')->orderBy('data_inicio_prazo');
        if ($request->filled('campoBusca')) {
            $resultado->where("titulo", "like", "%$request->campoBusca%")
                ->orWhere('id', $request->campoBusca);
        }
        return $resultado;
    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);
        $avaliacoes_tipos = AvaliacaoTipo::whereAtivo(true)->get();

        $permissoes = [
//            'insert' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_insert'),
//            'update' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_update'),
//            'delete' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_delete')
        ];

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'avaliacoes_tipos' => $avaliacoes_tipos,
                'lista_status' => Avaliacao::LISTA_STATUS,
//                'permissoes' => [
//                    'admissao_cih_lancar' => auth()->user()->can('admissao_cih_lancar'),
//                    'admissao_cih_aprovar' => auth()->user()->can('admissao_cih_aprovar'),
//                    'admissao_cih_privilegio_adm' => auth()->user()->can('admissao_cih_privilegio_adm'),
//                ]
            ]
        ]);
    }

    public function ativaDesativa(Request $request)
    {
//        $this->authorize('administracao_documentos_legais_insert');

        $avaliacao = Avaliacao::find($request->id);
        $avaliacao->ativo = !$avaliacao->ativo;
        $avaliacao->save();
        $avaliacao->refresh();
        return response()->json(['ativo' => $avaliacao->ativo], 201);
    }

}
