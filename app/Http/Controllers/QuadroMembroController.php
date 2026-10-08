<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardsWeeklyReportTenant;
use App\Models\Quadro;
use App\Models\QuadroMembro;
use App\Models\User;
use Illuminate\Http\Request;

class QuadroMembroController extends Controller
{
    use GuardsWeeklyReportTenant;

    public function index(Request $request, int $empresa, Quadro $quadro)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro);

        $membros = $quadro->Membros()
            ->orderByRaw("CASE WHEN quadros_membros.papel = 'dono' THEN 0 ELSE 1 END")
            ->orderBy('users.nome')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'nome' => $user->nome,
                'login' => $user->login,
                'papel' => $user->pivot->papel ?? QuadroMembro::PAPEL_MEMBRO,
                'sou_dono' => ($user->pivot->papel ?? '') === QuadroMembro::PAPEL_DONO,
            ]);

        return response()->json([
            'membros' => $membros,
            'sou_dono' => $quadro->isDono(auth()->user()),
        ]);
    }

    public function buscar(Request $request, int $empresa, Quadro $quadro)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro);
        $this->assertQuadroDono($quadro);

        $busca = trim((string) $request->query('busca', ''));
        if ($busca === '') {
            return response()->json([], 200);
        }

        $rows = (int) $request->query('rows', 10);
        $rows = max(1, min($rows, 30));

        $jaMembros = $quadro->QuadrosMembros()->pluck('user_id')->all();

        $usuarios = User::query()
            ->whereAtivo(true)
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresa)
            ->whereIn('tipo', User::TIPOS_USUARIOS_GERENCIAIS)
            ->where('nome', 'like', '%' . $busca . '%')
            ->when($jaMembros !== [], fn ($q) => $q->whereNotIn('id', $jaMembros))
            ->orderBy('nome')
            ->take(max($rows * 3, 15))
            ->get(['id', 'nome', 'login', 'tipo'])
            ->filter(fn (User $user) => $user->can('weekly_report'))
            ->take($rows)
            ->values()
            ->map(function (User $user) {
                $user->label = $user->nome;
                return $user;
            });

        return response()->json($usuarios, 200);
    }

    public function store(Request $request, int $empresa, Quadro $quadro)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro);
        $this->assertQuadroDono($quadro);

        $dados = \Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($dados->fails()) {
            return response()->json([
                'msg' => 'Informe um usuário válido',
                'erros' => $dados->errors(),
            ], 400);
        }

        $userId = (int) $request->input('user_id');
        $user = User::query()
            ->where('id', $userId)
            ->where('empresa_id', $empresa)
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json(['msg' => 'Usuário não encontrado nesta empresa'], 404);
        }

        if (!$user->can('weekly_report')) {
            return response()->json(['msg' => 'Usuário sem acesso ao Weekly Report'], 400);
        }

        if ($quadro->temMembro($user)) {
            return response()->json(['msg' => 'Usuário já é membro deste quadro'], 400);
        }

        QuadroMembro::query()->create([
            'quadro_id' => $quadro->id,
            'user_id' => $user->id,
            'papel' => QuadroMembro::PAPEL_MEMBRO,
        ]);

        return response()->json([
            'msg' => 'Membro adicionado',
            'membro' => [
                'id' => $user->id,
                'nome' => $user->nome,
                'login' => $user->login,
                'papel' => QuadroMembro::PAPEL_MEMBRO,
                'sou_dono' => false,
            ],
        ], 201);
    }

    public function destroy(Request $request, int $empresa, Quadro $quadro, int $user)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro);
        $this->assertQuadroDono($quadro);

        $membro = QuadroMembro::query()
            ->where('quadro_id', $quadro->id)
            ->where('user_id', $user)
            ->first();

        if (!$membro) {
            return response()->json(['msg' => 'Membro não encontrado neste quadro'], 404);
        }

        if ($membro->papel === QuadroMembro::PAPEL_DONO) {
            return response()->json(['msg' => 'Não é possível remover o dono do quadro'], 400);
        }

        $membro->delete();

        return response()->json(['msg' => 'Membro removido'], 200);
    }
}
