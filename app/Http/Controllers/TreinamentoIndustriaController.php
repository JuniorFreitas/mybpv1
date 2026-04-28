<?php

namespace App\Http\Controllers;

use App\Models\SegmentoTreinamento;
use App\Models\User;
use App\Models\Vencimento;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TreinamentoIndustriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.treinamentoindustria.index');
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('cadastro_treinamento_sgi_insert');
        $dados = $request->input();
        $dados['vinculo_todos_cargos'] = $this->normalizeBoolean($dados['vinculo_todos_cargos'] ?? false);
        $cargoIds = $this->normalizeIds($dados['cargo_ids'] ?? []);
        unset($dados['cargo_ids']);
        if ($dados['vinculo_todos_cargos']) {
            $cargoIds = [];
        }
        $dadosValidados = \Validator::make($dados,
            [
                'label' => 'required',
                'label_reduzida' => Rule::requiredIf($request->input('exibir_na_carteira')),
                'descricao' => 'required',
                'ativo' => 'required',
                'vinculo_todos_cargos' => 'required|boolean',
            ]
        );
        if (!$dados['vinculo_todos_cargos']) {
            $cargosValidados = \Validator::make(
                ['cargo_ids' => $cargoIds],
                [
                    'cargo_ids' => 'nullable|array',
                    'cargo_ids.*' => 'integer',
                    'cargo_ids.*' => Rule::exists('vagas', 'id')->where(function ($query) {
                        $query->whereAtivo(true);
                    }),
                ]
            );

            if ($cargosValidados->fails()) {
                return response()->json([
                    'msg' => 'Erro ao Cadastrar Treinamento Indústria',
                    'erros' => $cargosValidados->errors()
                ], 400);
            }
        }

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar Treinamento Indústria',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                if(!$dados['exibir_na_carteira']){
                    $dados['label_reduzida'] = null;
                }
                $dados['segmento_treinamento_id'] = $dados['segmento_treinamento_id'] ?? SegmentoTreinamento::getIdAlumar();

                $vencimento = Vencimento::create($dados);
                $vencimento->Vagas()->sync($dados['vinculo_todos_cargos'] ? [] : $cargoIds);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE TREINAMENTO INDUSTRIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Vencimento $vencimento
     * @return \Illuminate\Http\Response
     */
    public function show(Vencimento $vencimento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Vencimento $vencimento
     * @return Vencimento|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        $vencimento = Vencimento::with('SegmentoTreinamento:id,nome,slug', 'Vagas:id,nome')->whereId($id)->first();
        if (!$vencimento) {
            return $vencimento;
        }

        $vencimento->cargo_ids = $vencimento->Vagas->pluck('id')->values();
        return $vencimento;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Vencimento $vencimento
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
//        dd($request->input());
        $this->authorize('cadastro_treinamento_sgi_update');
        $dados = $request->input();
        $dados['vinculo_todos_cargos'] = $this->normalizeBoolean($dados['vinculo_todos_cargos'] ?? false);
        $cargoIds = $this->normalizeIds($dados['cargo_ids'] ?? []);
        unset($dados['cargo_ids']);
        if ($dados['vinculo_todos_cargos']) {
            $cargoIds = [];
        }
        $dadosValidados = \Validator::make($dados,
            [
                'label' => 'required',
                'label_reduzida' => Rule::requiredIf($request->input('exibir_na_carteira')),
                'descricao' => 'required',
                'ativo' => 'required',
                'vinculo_todos_cargos' => 'required|boolean',
            ]
        );
        if (!$dados['vinculo_todos_cargos']) {
            $cargosValidados = \Validator::make(
                ['cargo_ids' => $cargoIds],
                [
                    'cargo_ids' => 'nullable|array',
                    'cargo_ids.*' => 'integer',
                    'cargo_ids.*' => Rule::exists('vagas', 'id')->where(function ($query) {
                        $query->whereAtivo(true);
                    }),
                ]
            );

            if ($cargosValidados->fails()) {
                return response()->json([
                    'msg' => 'Erro ao Editar Treinamento Indústria',
                    'erros' => $cargosValidados->errors()
                ], 400);
            }
        }

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Editar Treinamento Indústria',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $vencimento = Vencimento::whereId($id)->first();

                if(!$dados['exibir_na_carteira']){
                    $dados['label_reduzida'] = null;
                }
                if (array_key_exists('segmento_treinamento_id', $dados)) {
                    $dados['segmento_treinamento_id'] = $dados['segmento_treinamento_id'] ?: SegmentoTreinamento::getIdAlumar();
                }

                $vencimento->update($dados);
                $vencimento->Vagas()->sync($dados['vinculo_todos_cargos'] ? [] : $cargoIds);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE TREINAMENTO INDUSTRIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Vencimento $vencimento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vencimento $vencimento)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $resultado = Vencimento::with('SegmentoTreinamento:id,nome,slug')->whereNotNull('label');

        if ($request->filled('campoBusca')) {
            $resultado->where('label', 'like', '%' . $request->campoBusca . '%');
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true';
            $resultado->whereAtivo($status);
        }

        if ($request->filled('segmento_treinamento_id')) {
            $resultado->where('segmento_treinamento_id', $request->segmento_treinamento_id);
        }

        $resultado = $resultado->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' =>
                ['items' => $resultado->items()
                ]
        ]);
    }

    public function ativaDesativa(Request $request)
    {
        $this->authorize('cadastro_treinamento_industria');

        $treinamento = Vencimento::find($request->id);

        $treinamento->ativo = !$treinamento->ativo;
        $treinamento->save();
        $treinamento->refresh();
        return response()->json(['ativo' => $treinamento->ativo], 201);
    }

    private function normalizeIds($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->filter(function ($id) {
                return is_numeric($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === 1 || $value === '1' || $value === 'true' || $value === true) {
            return true;
        }

        return false;
    }
}
