<?php

namespace App\Http\Controllers;

use App\Events\WeeklyReport\QuadroEvent;
use App\Http\Controllers\Concerns\GuardsWeeklyReportTenant;
use App\Models\Quadro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class QuadroController extends Controller
{
    use GuardsWeeklyReportTenant;

    public function index()
    {
        return view('g.weekly-report.index');
    }

    public function store(Request $request, int $empresa)
    {
        $this->assertWeeklyEmpresaId($empresa);

        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao criar o quadro',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();

            $quadro = Quadro::create([
                'titulo' => $request->input('titulo'),
            ]);

            \DB::commit();

            Event::dispatch(new QuadroEvent($quadro, QuadroEvent::INSERT));

            return response()->json(['quadro' => $quadro], 201);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, int $empresa)
    {
        $this->assertWeeklyEmpresaId($empresa);

        return response()->json([
            'lista' => Quadro::query()->orderBy('titulo')->get(),
            'quadro_insert' => auth()->user()->can('weekly_report_quadro_insert'),
            'quadro_update' => auth()->user()->can('weekly_report_quadro_update'),
            'quadro_delete' => auth()->user()->can('weekly_report_quadro_delete'),
            'lista_insert' => auth()->user()->can('weekly_report_quadro_lista_insert'),
            'lista_update' => auth()->user()->can('weekly_report_quadro_lista_update'),
            'lista_delete' => auth()->user()->can('weekly_report_quadro_lista_delete'),
            'tarefa_insert' => auth()->user()->can('weekly_report_quadro_tarefa_insert'),
            'tarefa_update' => auth()->user()->can('weekly_report_quadro_tarefa_update'),
            'tarefa_delete' => auth()->user()->can('weekly_report_quadro_tarefa_delete'),
        ], 200);
    }

    public function update(Request $request, int $empresa, Quadro $quadro)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro);

        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar o quadro',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $quadro->update($request->only(['titulo']));
            \DB::commit();

            Event::dispatch(new QuadroEvent($quadro, QuadroEvent::UPDATE));

            return response()->json(['quadro' => $quadro], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request, int $empresa, Quadro $quadro)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro);

        try {
            \DB::beginTransaction();
            $idDelete = $quadro->id;
            $quadro->delete();
            \DB::commit();

            Event::dispatch(new QuadroEvent($idDelete, QuadroEvent::DELETE));

            return response()->json([], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'msg' => $e->getMessage(),
                'lista' => Quadro::query()->orderBy('titulo')->get(),
            ], 400);
        }
    }
}
