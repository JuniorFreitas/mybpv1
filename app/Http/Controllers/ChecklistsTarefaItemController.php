<?php

namespace App\Http\Controllers;

use App\Events\WeeklyReport\ItemChecklistEvent;
use App\Http\Controllers\Concerns\GuardsWeeklyReportTenant;
use App\Models\ChecklistsTarefa;
use App\Models\ChecklistsTarefaItem;
use App\Models\ListaTarefa;
use App\Models\LogWeekly;
use App\Models\Quadro;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use MasterTag\DataHora;

class ChecklistsTarefaItemController extends Controller
{
    use GuardsWeeklyReportTenant;

    public function store(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa, $checklist);

        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao criar a item',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $dados = [
                'titulo' => $request->input('titulo'),
                'checklist_id' => $checklist->id,
                'ordem' => 1,
                'concluido' => false,
            ];
            $ultimo = $checklist->Itens()->orderByDesc('ordem')->first();
            if ($ultimo) {
                $dados['ordem'] = $ultimo->ordem + 1;
            }
            $item = $checklist->Itens()->create($dados);
            \DB::commit();

            $item->load('Membros');
            Event::dispatch(new ItemChecklistEvent($item, ItemChecklistEvent::INSERT));

            return response()->json(['item' => $item], 201);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist, ChecklistsTarefaItem $item)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa, $checklist, $item);

        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'nullable|min:1',
            'concluido' => 'boolean',
            'datahora_entrega' => 'nullable|string',
            'acao' => 'nullable|in:add,remove',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar o item',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $dados = $request->only(['titulo', 'concluido']);

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

            $antesEntrega = $item->datahora_entrega;
            $item->update($dados);
            \DB::commit();

            $item = $item->fresh();

            if ($request->has('concluido')) {
                if (!empty($dados['concluido'])) {
                    LogWeekly::create([
                        'quadro_id' => $quadro->id,
                        'tarefa_id' => $tarefa->id,
                        'descricao' => "concluiu {$item->titulo} nesta tarefa",
                    ]);
                } else {
                    LogWeekly::create([
                        'quadro_id' => $quadro->id,
                        'tarefa_id' => $tarefa->id,
                        'descricao' => " não concluiu {$item->titulo} nesta tarefa",
                    ]);
                }
            }

            if (array_key_exists('datahora_entrega', $dados)) {
                if ($dados['datahora_entrega']) {
                    LogWeekly::create([
                        'quadro_id' => $quadro->id,
                        'tarefa_id' => $tarefa->id,
                        'descricao' => "definiu prazo em {$item->titulo}",
                    ]);
                } elseif ($antesEntrega) {
                    LogWeekly::create([
                        'quadro_id' => $quadro->id,
                        'tarefa_id' => $tarefa->id,
                        'descricao' => "removeu o prazo de {$item->titulo}",
                    ]);
                }
            }

            $item->load('Membros');
            Event::dispatch(new ItemChecklistEvent($item, ItemChecklistEvent::UPDATE));

            return response()->json(['item' => $item], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function updateMembro(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist, ChecklistsTarefaItem $item)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa, $checklist, $item);

        $dadosValidados = \Validator::make($request->all(), [
            'acao' => 'required|in:add,remove',
            'user_id' => 'required|integer',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar membros do item',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        $membro = User::query()
            ->whereId($request->user_id)
            ->whereEmpresaId($empresa)
            ->whereAtivo(true)
            ->first();

        if (!$membro) {
            return response()->json(['msg' => 'Membro inválido para este tenant.'], 400);
        }

        if ($request->acao === 'add') {
            $item->Membros()->syncWithoutDetaching([$membro->id]);
            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'tarefa_id' => $tarefa->id,
                'descricao' => "adicionou {$membro->nome} em {$item->titulo}",
            ]);
        }

        if ($request->acao === 'remove') {
            $item->Membros()->detach($membro->id);
            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'tarefa_id' => $tarefa->id,
                'descricao' => "removeu {$membro->nome} de {$item->titulo}",
            ]);
        }

        $item = $item->fresh(['Membros']);
        Event::dispatch(new ItemChecklistEvent($item, ItemChecklistEvent::UPDATE));

        return response()->json(['item' => $item], 200);
    }

    public function destroy(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist, ChecklistsTarefaItem $item)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa, $checklist, $item);

        try {
            \DB::beginTransaction();

            $idDelete = $item->id;
            $payload = (object) [
                'id' => $idDelete,
                'checklist_id' => $checklist->id,
                'tarefa_id' => $tarefa->id,
                'lista_id' => $lista->id,
            ];

            $item->delete();
            \DB::commit();

            Event::dispatch(new ItemChecklistEvent($payload, ItemChecklistEvent::DELETE, $idDelete));

            return response()->json(['item_id' => $idDelete], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        }
    }
}
