<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\Sistema;
use App\Models\User;
use App\Models\VagaProjeto;
use App\Models\VagasAbertas;
use Illuminate\Http\Request;
use DB;
use Illuminate\Validation\Rule;

class ProjetoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('cadastro_projetos');
        return view('g.cadastros.projeto.index');
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('cadastro_projetos_insert');
        $dados = $request->input();

        $regra = Rule::unique('projetos')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresaAtivaId())
                ->whereNome($dados['nome']);
        });

        $dadosValidados = \Validator::make($dados, [
            'nome' => ['required', $regra],
            'qnt_total' => 'required|numeric',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Projeto',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();

                $projeto = Projeto::create($dados);

                if (isset($dados['vagas_projeto'])) {
                    foreach ($dados['vagas_projeto'] as $vaga_projeto) {
                        $vaga_projeto['projeto_id'] = $projeto->id;
                        if (isset($vaga_projeto['novo'])) {
                            VagaProjeto::create($vaga_projeto);
                        } else {
                            VagaProjeto::find($vaga_projeto['id'])->update($vaga_projeto);
                        }
                    }
                }

                $this->sincronizarRestanteProjeto($projeto);

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                $msg = "error PROJETO STORE: {$e->getFile()} , {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome . ' EMPRESA - ' . auth()->user()->Empresa->razao_social;
                \Log::debug($msg);
                \Log::debug($e->getTraceAsString());
                \Log::info("-------DADOS-------");
                Sistema::telegram(print_r($dados, true));
                \Log::info("-------FIM DE DADOS-------");
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->authorize('cadastro_projetos_update');

        $projeto = Projeto::with([
            'VagasProjeto.VagaAberta:id,titulo,vaga_id,empresa_id,municipio_id',
            'VagasProjeto.VagaAberta.Vaga:id,nome',
            'VagasProjeto.VagaAberta.Municipio:id,nome,uf',
        ])->findOrFail($id);

        return response()->json($this->payloadEdicaoProjeto($projeto));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->authorize('cadastro_projetos_update');
        $dados = $request->input();

        $projeto = Projeto::findOrFail($id);

        $regra = Rule::unique('projetos')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresaAtivaId())
                ->whereNome($dados['nome']);
        })->ignore($projeto->id);

        $dadosValidados = \Validator::make($dados, [
            'nome' => ['required', $regra],
            'qnt_total' => 'required|numeric|min:1',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Projeto',
                'erros' => $dadosValidados->errors()
            ], 400);

        }

        if ($erroDelete = $this->validarVagasProjetoDelete($dados['vagas_projetoDelete'] ?? [], $projeto)) {
            return $erroDelete;
        }

        if ($erroVagas = $this->validarVagasProjeto($dados['vagas_projeto'] ?? [], $projeto)) {
            return $erroVagas;
        }

        $totalAlocado = collect($dados['vagas_projeto'] ?? [])->sum(function ($item) {
            return (int) ($item['qnt_total'] ?? 0);
        });

        if ((int) $dados['qnt_total'] < $totalAlocado) {
            return response()->json([
                'msg' => 'A quantidade total do projeto não pode ser menor que a soma alocada nas vagas abertas.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            if (!empty($dados['vagas_projetoDelete'])) {
                VagaProjeto::query()
                    ->where('projeto_id', $projeto->id)
                    ->whereIn('id', $dados['vagas_projetoDelete'])
                    ->where('qnt_preenchida', 0)
                    ->delete();
            }

            if (isset($dados['vagas_projeto'])) {
                foreach ($dados['vagas_projeto'] as $vaga_projeto) {
                    $vaga_projeto['projeto_id'] = $projeto->id;
                    if (isset($vaga_projeto['novo'])) {
                        unset($vaga_projeto['novo'], $vaga_projeto['vaga_aberta']);
                        VagaProjeto::create($vaga_projeto);
                    } else {
                        unset($vaga_projeto['vaga_aberta']);
                        VagaProjeto::where('projeto_id', $projeto->id)
                            ->whereKey($vaga_projeto['id'])
                            ->update(collect($vaga_projeto)->only([
                                'vaga_aberta_id',
                                'qnt_total',
                                'qnt_preenchida',
                                'empresa_id',
                                'projeto_id',
                            ])->toArray());
                    }
                }
            }

            $projeto->update([
                'nome' => $dados['nome'],
                'qnt_total' => (int) $dados['qnt_total'],
            ]);

            $this->sincronizarRestanteProjeto($projeto);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error PROJETO UPDATE: {$e->getFile()} , {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome . ' EMPRESA - ' . auth()->user()->Empresa->razao_social;
            \Log::debug($msg);
            \Log::debug($e->getTraceAsString());
            \Log::info("-------DADOS-------");
            Sistema::telegram(print_r($dados, true));
            \Log::info("-------FIM DE DADOS-------");
            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.∂
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_projetos');

        $query = Projeto::query()
            ->withCount('VagasProjeto')
            ->orderByDesc('updated_at');

        if ($request->filled('campoBusca')) {
            $termo = trim((string) $request->campoBusca);
            $query->where(function ($q) use ($termo) {
                $q->where('nome', 'like', '%' . $termo . '%');

                if (is_numeric($termo)) {
                    $q->orWhere('id', (int) $termo);
                }
            });
        }

        if ($request->filled('campoDisponibilidade')) {
            if ($request->campoDisponibilidade === 'com_restante') {
                $query->where('qnt_total_restante', '>', 0);
            } elseif ($request->campoDisponibilidade === 'esgotado') {
                $query->where('qnt_total_restante', '<=', 0);
            }
        }

        if ($request->filled('campoVinculoVagas')) {
            if ($request->campoVinculoVagas === 'com_vinculo') {
                $query->whereHas('VagasProjeto');
            } elseif ($request->campoVinculoVagas === 'sem_vinculo') {
                $query->whereDoesntHave('VagasProjeto');
            }
        }

        $resultado = $query->paginate(50);

        $itens = collect($resultado->items())->map(function (Projeto $projeto) {
            $qntTotal = max((int) $projeto->qnt_total, 0);
            $preenchidas = max((int) $projeto->preenchidas, 0);

            $projeto->updated_at_br = $projeto->updated_at?->format('d/m/Y H:i');
            $projeto->tem_vagas_restantes = (int) $projeto->qnt_total_restante > 0;
            $projeto->percentual_preenchido = $qntTotal > 0
                ? (int) round(($preenchidas / $qntTotal) * 100)
                : 0;

            return $projeto;
        })->values();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
            ],
        ]);
    }

    private function payloadEdicaoProjeto(Projeto $projeto): array
    {
        $vagasProjeto = $projeto->VagasProjeto->map(function (VagaProjeto $vagaProjeto) use ($projeto) {
            $vagaAberta = $vagaProjeto->VagaAberta;

            return [
                'id' => $vagaProjeto->id,
                'projeto_id' => $projeto->id,
                'vaga_aberta_id' => $vagaProjeto->vaga_aberta_id,
                'empresa_id' => $vagaProjeto->empresa_id,
                'qnt_total' => (int) $vagaProjeto->qnt_total,
                'qnt_preenchida' => (int) $vagaProjeto->qnt_preenchida,
                'vaga_aberta' => $this->formatVagaAbertaPayload($vagaAberta),
            ];
        })->values();

        return [
            'id' => $projeto->id,
            'nome' => $projeto->nome,
            'qnt_total' => (int) $projeto->qnt_total,
            'qnt_total_restante' => (int) $projeto->qnt_total_restante,
            'preenchidas' => (int) $projeto->preenchidas,
            'empresa_id' => $projeto->empresa_id,
            'vagas_projeto' => $vagasProjeto,
        ];
    }

    private function formatVagaAbertaPayload(?VagasAbertas $vagaAberta): ?array
    {
        if (!$vagaAberta) {
            return null;
        }

        $municipio = $vagaAberta->Municipio;

        return [
            'id' => $vagaAberta->id,
            'titulo' => $vagaAberta->titulo,
            'empresa_id' => $vagaAberta->empresa_id,
            'municipio_id' => $vagaAberta->municipio_id,
            'municipio_label' => $municipio
                ? "{$municipio->nome} - {$municipio->uf}"
                : null,
            'municipio' => $municipio ? [
                'id' => $municipio->id,
                'nome' => $municipio->nome,
                'uf' => $municipio->uf,
            ] : null,
            'vaga' => $vagaAberta->Vaga ? [
                'id' => $vagaAberta->Vaga->id,
                'nome' => $vagaAberta->Vaga->nome,
            ] : null,
        ];
    }

    private function sincronizarRestanteProjeto(Projeto $projeto): void
    {
        $totalAlocado = (int) VagaProjeto::query()
            ->where('projeto_id', $projeto->id)
            ->sum('qnt_total');

        $projeto->update([
            'qnt_total_restante' => max((int) $projeto->qnt_total - $totalAlocado, 0),
        ]);
    }

    private function validarVagasProjetoDelete(array $ids, Projeto $projeto): ?\Illuminate\Http\JsonResponse
    {
        if (empty($ids)) {
            return null;
        }

        $vinculos = VagaProjeto::query()
            ->where('projeto_id', $projeto->id)
            ->whereIn('id', $ids)
            ->get(['id', 'qnt_preenchida']);

        if ($vinculos->count() !== count($ids)) {
            return response()->json([
                'msg' => 'Não foi possível remover um ou mais vínculos de vaga aberta.',
            ], 400);
        }

        foreach ($vinculos as $vinculo) {
            if ((int) $vinculo->qnt_preenchida > 0) {
                return response()->json([
                    'msg' => 'Não é possível remover vínculos com vagas preenchidas.',
                ], 400);
            }
        }

        return null;
    }

    private function validarVagasProjeto(array $vagas, Projeto $projeto): ?\Illuminate\Http\JsonResponse
    {
        $idsExistentes = VagaProjeto::query()
            ->where('projeto_id', $projeto->id)
            ->pluck('id')
            ->all();

        $totalAlocado = 0;

        foreach ($vagas as $index => $vaga) {
            $linha = $index + 1;
            $qntTotal = (int) ($vaga['qnt_total'] ?? 0);
            $qntPreenchida = (int) ($vaga['qnt_preenchida'] ?? 0);

            if ($qntTotal < 1) {
                return response()->json([
                    'msg' => "Informe a quantidade total na linha #{$linha}.",
                ], 400);
            }

            if ($qntTotal < $qntPreenchida) {
                return response()->json([
                    'msg' => "A quantidade total na linha #{$linha} não pode ser menor que as preenchidas ({$qntPreenchida}).",
                ], 400);
            }

            if (empty($vaga['novo'])) {
                $id = (int) ($vaga['id'] ?? 0);
                if (!$id || !in_array($id, $idsExistentes, true)) {
                    return response()->json([
                        'msg' => "Vínculo inválido na linha #{$linha}.",
                    ], 400);
                }
            } elseif (empty($vaga['vaga_aberta_id'])) {
                return response()->json([
                    'msg' => "Selecione uma vaga aberta na linha #{$linha}.",
                ], 400);
            }

            $totalAlocado += $qntTotal;
        }

        return null;
    }

    public function buscaProjeto($vaga_aberta_id)
    {
        $dados = VagaProjeto::whereVagaAbertaId($vaga_aberta_id)->with('Projeto')->get();
        return response()->json(['dados' => $dados, 'encontrou' => !empty($dados)], 201);
    }

    public function buscaTodosProjeto()
    {
        $dados = VagaProjeto::with('Projeto')->get();
        return response()->json(['dados' => $dados], 201);
    }
}
