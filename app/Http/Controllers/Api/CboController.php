<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cbo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CboController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');

        $query = Cbo::query()
            ->leftJoin('cbo_familias', 'cbo_familias.codigo', '=', 'cbos.codigo_familia')
            ->where('cbos.ativo', true)
            ->select([
                'cbos.codigo',
                'cbos.titulo',
                'cbos.codigo_familia',
                'cbo_familias.titulo as familia',
                'cbo_familias.descricao_sumaria',
                'cbos.fonte',
                'cbos.data_importacao',
            ]);

        if (is_string($q) && trim($q) !== '') {
            $q = trim($q);
            $query->where(function ($subquery) use ($q) {
                $subquery->where('cbos.codigo', 'like', "%{$q}%")
                    ->orWhere('cbos.titulo', 'like', "%{$q}%")
                    ->orWhere('cbo_familias.titulo', 'like', "%{$q}%")
                    ->orWhere('cbo_familias.descricao_sumaria', 'like', "%{$q}%");
            });
        }

        $rows = $query
            ->orderBy('cbos.titulo')
            ->limit(50)
            ->get();

        return response()->json($rows->map(fn ($row) => $this->formatRow($row))->values()->all());
    }

    public function show(string $codigo): JsonResponse
    {
        $row = Cbo::query()
            ->leftJoin('cbo_familias', 'cbo_familias.codigo', '=', 'cbos.codigo_familia')
            ->where('cbos.ativo', true)
            ->where('cbos.codigo', $codigo)
            ->select([
                'cbos.codigo',
                'cbos.titulo',
                'cbos.codigo_familia',
                'cbo_familias.titulo as familia',
                'cbo_familias.descricao_sumaria',
                'cbos.fonte',
                'cbos.data_importacao',
            ])
            ->first();

        if ($row === null) {
            return response()->json(['msg' => 'CBO não encontrado', 'success' => false], 404);
        }

        return response()->json($this->formatRow($row));
    }

    private function formatRow(object $row): array
    {
        $ts = $row->data_importacao ?? null;

        return [
            'codigo' => $row->codigo,
            'titulo' => $row->titulo,
            'codigo_familia' => $row->codigo_familia,
            'familia' => $row->familia,
            'descricao_sumaria' => $row->descricao_sumaria,
            'fonte' => $row->fonte,
            'data_importacao' => $ts ? \Carbon\Carbon::parse($ts)->format('Y-m-d H:i:s') : null,
        ];
    }
}
