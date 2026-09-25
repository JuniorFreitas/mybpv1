<?php

namespace App\Http\Controllers;

use App\Events\WeeklyReport\ListaEvent;
use App\Http\Controllers\Concerns\GuardsWeeklyReportTenant;
use App\Models\ListaTarefa;
use App\Models\LogWeekly;
use App\Models\Quadro;
use App\Support\WeeklyReport\WeeklyReportEagerLoads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class ListaTarefaController extends Controller
{
    use GuardsWeeklyReportTenant;

    public function index(Request $request, int $empresa, Quadro $quadro)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro);

        return response()->json([
            'lista' => WeeklyReportEagerLoads::finalizeBoardListas(
                $quadro->Listas()->orderBy('ordem')->with([
                    'Tarefas' => function ($q) {
                        WeeklyReportEagerLoads::applyBoardTarefas($q);
                    },
                ])->get(['id', 'quadro_id', 'titulo', 'ordem', 'user_id', 'created_at', 'updated_at'])
            ),
            'atividades' => $quadro->Logs()
                ->with(['Usuario:id,nome'])
                ->take(5)
                ->get(['id', 'quadro_id', 'tarefa_id', 'user_id', 'descricao', 'created_at']),
        ], 200);
    }

    public function store(Request $request, int $empresa, Quadro $quadro)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro);

        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao criar a lista',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $dados = [
                'titulo' => $request->input('titulo'),
                'quadro_id' => $quadro->id,
                'ordem' => 1,
                'user_id' => auth()->id(),
            ];
            $ultimo = $quadro->Listas()->orderByDesc('ordem')->first();
            if ($ultimo) {
                $dados['ordem'] = $ultimo->ordem + 1;
            }
            $lista = ListaTarefa::create($dados);

            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'descricao' => "Criou a lista {$lista->titulo} a este quadro",
            ]);

            \DB::commit();

            Event::dispatch(new ListaEvent($lista, ListaEvent::INSERT));

            return response()->json(['lista' => $lista], 201);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista);

        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar a lista',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $lista->update($request->only(['titulo']));
            \DB::commit();

            Event::dispatch(new ListaEvent($lista, ListaEvent::UPDATE));

            return response()->json(['lista' => $lista], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista);

        try {
            \DB::beginTransaction();

            $titulo = $lista->titulo;
            $idDelete = $lista->id;
            $lista->delete();

            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'descricao' => "Apagou a lista {$titulo}",
            ]);

            \DB::commit();

            Event::dispatch(new ListaEvent($quadro, ListaEvent::DELETE, $idDelete));

            return response()->json([], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'msg' => $e->getMessage(),
                'lista' => WeeklyReportEagerLoads::finalizeBoardListas(
                    $quadro->Listas()->orderBy('ordem')->with([
                        'Tarefas' => function ($q) {
                            WeeklyReportEagerLoads::applyBoardTarefas($q);
                        },
                    ])->get(['id', 'quadro_id', 'titulo', 'ordem', 'user_id', 'created_at', 'updated_at'])
                ),
            ], 400);
        }
    }

    public function atualizarOrdem(Request $request, int $empresa, Quadro $quadro)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro);

        foreach ($request->novaLista ?? [] as $obj) {
            $id = $obj['id'] ?? null;
            if (!$id) {
                continue;
            }
            $payload = array_intersect_key($obj, array_flip(['ordem', 'titulo']));
            if ($payload) {
                ListaTarefa::whereQuadroId($quadro->id)->whereId($id)->update($payload);
            }
        }

        Event::dispatch(new ListaEvent($quadro, ListaEvent::ORDENAR));

        return response()->json([], 200);
    }
}
