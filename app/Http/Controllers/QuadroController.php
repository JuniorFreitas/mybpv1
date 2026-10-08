<?php

namespace App\Http\Controllers;

use App\Events\WeeklyReport\QuadroEvent;
use App\Http\Controllers\Concerns\GuardsWeeklyReportTenant;
use App\Models\Quadro;
use App\Models\QuadroMembro;
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

        $titulo = trim((string) $request->input('titulo', ''));

        $dadosValidados = \Validator::make(
            ['titulo' => $titulo],
            ['titulo' => 'required|min:1|max:120']
        );

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao criar o quadro',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        if ($this->tituloQuadroEmUso($titulo)) {
            return response()->json([
                'msg' => 'Você já possui ou está vinculado a um quadro com este nome.',
            ], 400);
        }

        try {
            \DB::beginTransaction();

            $quadro = Quadro::create([
                'titulo' => $titulo,
                'empresa_id' => (int) auth()->user()->empresa_id,
                'user_id' => (int) auth()->id(),
            ]);

            // Garante dono mesmo se model events estiverem fakes/desligados
            QuadroMembro::query()->firstOrCreate(
                [
                    'quadro_id' => $quadro->id,
                    'user_id' => (int) auth()->id(),
                ],
                [
                    'papel' => QuadroMembro::PAPEL_DONO,
                ]
            );

            \DB::commit();

            $quadroFresh = $quadro->fresh()->load('Membros')->loadCount('Membros as membros_count');
            $payload = $this->serializeQuadro($quadroFresh);

            Event::dispatch(new QuadroEvent($payload, QuadroEvent::INSERT));

            return response()->json(['quadro' => $payload], 201);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, int $empresa)
    {
        $this->assertWeeklyEmpresaId($empresa);

        $userId = (int) auth()->id();

        $lista = Quadro::query()
            ->whereHas('QuadrosMembros', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with([
                'Membros' => function ($q) {
                    $q->orderByRaw("CASE WHEN quadros_membros.papel = 'dono' THEN 0 ELSE 1 END")
                        ->orderBy('users.nome')
                        ->limit(8);
                },
            ])
            ->withCount('Membros as membros_count')
            ->orderBy('titulo')
            ->get()
            ->map(fn (Quadro $quadro) => $this->serializeQuadro($quadro));

        return response()->json([
            'lista' => $lista,
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
        $this->assertQuadroDono($quadro);

        $titulo = trim((string) $request->input('titulo', ''));

        $dadosValidados = \Validator::make(
            ['titulo' => $titulo],
            ['titulo' => 'required|min:1|max:120']
        );

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar o quadro',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        if ($this->tituloQuadroEmUso($titulo, (int) $quadro->id)) {
            return response()->json([
                'msg' => 'Você já possui ou está vinculado a um quadro com este nome.',
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $quadro->update(['titulo' => $titulo]);
            \DB::commit();

            $quadroFresh = $quadro->fresh()->load('Membros')->loadCount('Membros as membros_count');
            $payload = $this->serializeQuadro($quadroFresh);

            Event::dispatch(new QuadroEvent($payload, QuadroEvent::UPDATE));

            return response()->json(['quadro' => $payload], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request, int $empresa, Quadro $quadro)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro);
        $this->assertQuadroDono($quadro);

        try {
            \DB::beginTransaction();
            $idDelete = $quadro->id;
            // Soft delete + quem_deletou_id (hook deleting no model)
            $quadro->delete();
            \DB::commit();

            Event::dispatch(new QuadroEvent($idDelete, QuadroEvent::DELETE));

            return response()->json(['msg' => 'Quadro excluído'], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'msg' => $e->getMessage(),
                'lista' => Quadro::query()
                    ->whereHas('QuadrosMembros', fn ($q) => $q->where('user_id', auth()->id()))
                    ->orderBy('titulo')
                    ->get()
                    ->map(fn (Quadro $q) => $this->serializeQuadro($q)),
            ], 400);
        }
    }

    /**
     * Título único entre os quadros do usuário (dono ou membro vinculado).
     * Outros usuários da mesma empresa podem ter o mesmo nome em quadros distintos.
     */
    private function tituloQuadroEmUso(string $titulo, ?int $excetoQuadroId = null): bool
    {
        $normalizado = mb_strtolower(trim($titulo));
        if ($normalizado === '') {
            return false;
        }

        $userId = (int) auth()->id();
        if (!$userId) {
            return false;
        }

        return Quadro::query()
            ->whereHas('QuadrosMembros', fn ($q) => $q->where('user_id', $userId))
            ->when($excetoQuadroId, fn ($q) => $q->where('id', '!=', $excetoQuadroId))
            ->whereRaw('LOWER(TRIM(titulo)) = ?', [$normalizado])
            ->exists();
    }

    private function serializeQuadro(Quadro $quadro): array
    {
        $userId = (int) auth()->id();
        $papel = $quadro->relationLoaded('Membros')
            ? optional($quadro->Membros->firstWhere('id', $userId))->pivot->papel ?? $quadro->papelDoUsuario()
            : $quadro->papelDoUsuario();

        $membros = [];
        if ($quadro->relationLoaded('Membros')) {
            $membros = $quadro->Membros->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nome' => $user->nome,
                    'papel' => $user->pivot->papel ?? QuadroMembro::PAPEL_MEMBRO,
                ];
            })->values()->all();
        }

        return [
            'id' => $quadro->id,
            'titulo' => $quadro->titulo,
            'empresa_id' => $quadro->empresa_id,
            'user_id' => $quadro->user_id,
            'created_at' => $quadro->created_at,
            'updated_at' => $quadro->updated_at,
            'meu_papel' => $papel,
            'sou_dono' => $papel === QuadroMembro::PAPEL_DONO,
            'membros_count' => (int) ($quadro->membros_count ?? count($membros)),
            'membros_preview' => $membros,
        ];
    }
}
