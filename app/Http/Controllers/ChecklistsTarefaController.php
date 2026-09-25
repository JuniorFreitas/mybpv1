<?php

namespace App\Http\Controllers;

use App\Events\WeeklyReport\CheckListTarefaEvent;
use App\Http\Controllers\Concerns\GuardsWeeklyReportTenant;
use App\Models\ChecklistsTarefa;
use App\Models\ChecklistsTarefaItem;
use App\Models\ListaTarefa;
use App\Models\LogWeekly;
use App\Models\Quadro;
use App\Models\Tarefa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use MasterTag\DataHora;

class ChecklistsTarefaController extends Controller
{
    use GuardsWeeklyReportTenant;

    public function store(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao criar a checklist',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $dados = [
                'titulo' => $request->input('titulo'),
                'tarefa_id' => $tarefa->id,
                'ordem' => 1,
            ];
            $ultimo = $tarefa->Checklists()->orderByDesc('ordem')->first();
            if ($ultimo) {
                $dados['ordem'] = $ultimo->ordem + 1;
            }
            $checkList = $tarefa->Checklists()->create($dados);
            \DB::commit();

            Event::dispatch(new CheckListTarefaEvent($checkList, CheckListTarefaEvent::INSERT));

            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'tarefa_id' => $tarefa->id,
                'descricao' => "adicionou a checklist {$checkList->titulo} nesta tarefa",
            ]);

            return response()->json($checkList, 201);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa, $checklist);

        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'nullable|min:1',
            'datahora_entrega' => 'nullable|string',
            'acao' => 'nullable|in:add,remove',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar a checklist',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $dados = [];

            if ($request->filled('titulo')) {
                $dados['titulo'] = $request->input('titulo');
            }

            if ($request->input('acao') === 'remove' || ($request->has('datahora_entrega') && $request->input('datahora_entrega') === null)) {
                $dados['datahora_entrega'] = null;
            } elseif ($request->filled('datahora_entrega') || $request->input('acao') === 'add') {
                $convertida = DataHora::converterDatePicker($request->input('datahora_entrega'));
                if (!$convertida) {
                    \DB::rollBack();

                    return response()->json(['msg' => 'Data/hora de entrega inválida'], 422);
                }
                $dados['datahora_entrega'] = (new DataHora($convertida))->dataHoraInsert();
            }

            if ($dados === []) {
                \DB::rollBack();

                return response()->json(['msg' => 'Nada para atualizar'], 400);
            }

            $antesEntrega = $checklist->datahora_entrega;
            $checklist->update($dados);
            \DB::commit();

            $checklist = $checklist->fresh(['Itens']);

            if (array_key_exists('datahora_entrega', $dados)) {
                if ($dados['datahora_entrega']) {
                    LogWeekly::create([
                        'quadro_id' => $quadro->id,
                        'tarefa_id' => $tarefa->id,
                        'descricao' => "definiu prazo na checklist {$checklist->titulo}",
                    ]);
                } elseif ($antesEntrega) {
                    LogWeekly::create([
                        'quadro_id' => $quadro->id,
                        'tarefa_id' => $tarefa->id,
                        'descricao' => "removeu o prazo da checklist {$checklist->titulo}",
                    ]);
                }
            }

            Event::dispatch(new CheckListTarefaEvent($checklist, CheckListTarefaEvent::UPDATE));

            return response()->json(['checklist' => $checklist], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa, $checklist);

        try {
            \DB::beginTransaction();

            $idDelete = $checklist->id;
            $tarefaId = $tarefa->id;
            $listaId = $lista->id;
            $tituloChecklist = $checklist->titulo;

            // Remove itens primeiro (evita falha por FK em ambientes sem cascade)
            $checklist->Itens()->delete();
            $checklist->delete();

            \DB::commit();

            Event::dispatch(new CheckListTarefaEvent(
                (object) [
                    'id' => $idDelete,
                    'tarefa_id' => $tarefaId,
                    'lista_id' => $listaId,
                ],
                CheckListTarefaEvent::DELETE,
                $idDelete
            ));

            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'tarefa_id' => $tarefaId,
                'descricao' => "removeu a checklist {$tituloChecklist} desta tarefa",
            ]);

            return response()->json(['checklist_id' => $idDelete], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        }
    }

    public function atualizarOrdemItens(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa, $checklist);

        if ($request->evento == 'moveu') {
            foreach ($request->novaLista ?? [] as $obj) {
                $id = $obj['id'] ?? null;
                if (!$id) {
                    continue;
                }
                $payload = array_intersect_key($obj, array_flip(['ordem', 'checklist_id', 'titulo', 'concluido']));
                ChecklistsTarefaItem::whereChecklistId($checklist->id)->whereId($id)->update($payload);
            }
            Event::dispatch(new CheckListTarefaEvent($checklist, CheckListTarefaEvent::ORDENAR_ITENS));
        }

        if ($request->evento == 'adicionar') {
            foreach ($request->novaChecklist ?? [] as $obj) {
                $id = $obj['id'] ?? null;
                if (!$id) {
                    continue;
                }
                $payload = array_intersect_key($obj, array_flip(['ordem', 'checklist_id', 'titulo', 'concluido']));
                if (!isset($payload['checklist_id'])) {
                    $payload['checklist_id'] = $checklist->id;
                }
                ChecklistsTarefaItem::whereId($id)->update($payload);
            }
            Event::dispatch(new CheckListTarefaEvent($checklist, CheckListTarefaEvent::ORDENAR_ITENS));
        }

        if ($request->evento == 'remover') {
            foreach ($request->antigaChecklist ?? [] as $obj) {
                $id = $obj['id'] ?? null;
                if (!$id) {
                    continue;
                }
                $payload = array_intersect_key($obj, array_flip(['ordem', 'checklist_id', 'titulo', 'concluido']));
                ChecklistsTarefaItem::whereId($id)->update($payload);
            }
            Event::dispatch(new CheckListTarefaEvent($checklist, CheckListTarefaEvent::ORDENAR_ITENS));
        }

        return response()->json([], 200);
    }
}
