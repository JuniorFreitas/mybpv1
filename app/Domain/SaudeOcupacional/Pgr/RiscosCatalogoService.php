<?php

namespace App\Domain\SaudeOcupacional\Pgr;

use App\Models\ExameRisco;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Catálogo de riscos da empresa (exame_riscos), usado no PGR e nas regras.
 */
class RiscosCatalogoService
{
    /**
     * @return list<array{id: int, label: string, risco_tipo: int, tipo: string|null, ativo: bool}>
     */
    public function listar(int $empresaId, bool $somenteAtivos = false): array
    {
        return DB::table('exame_riscos as r')
            ->leftJoin('exame_risco_tipos as t', 't.id', '=', 'r.risco_tipo')
            ->where('r.empresa_id', $empresaId)
            ->when($somenteAtivos, fn ($q) => $q->where('r.ativo', true))
            ->orderBy('t.label')
            ->orderBy('r.label')
            ->get(['r.id', 'r.label', 'r.risco_tipo', 't.label as tipo', 'r.ativo'])
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'label' => (string) $r->label,
                'risco_tipo' => (int) $r->risco_tipo,
                'tipo' => $r->tipo,
                'ativo' => (bool) $r->ativo,
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, label: string}>
     */
    public function tipos(): array
    {
        return DB::table('exame_risco_tipos')
            ->where('ativo', true)
            ->orderBy('id')
            ->get(['id', 'label'])
            ->map(fn ($t) => ['id' => (int) $t->id, 'label' => (string) $t->label])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function nomes(int $empresaId, array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        return DB::table('exame_riscos')
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $ids)
            ->pluck('label', 'id')
            ->mapWithKeys(fn ($label, $id) => [(int) $id => (string) $label])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function salvar(int $empresaId, ?int $riscoId, array $dados): ExameRisco
    {
        $label = trim((string) ($dados['label'] ?? ''));
        if (mb_strlen($label) < 2 || mb_strlen($label) > 120) {
            throw ValidationException::withMessages(['label' => 'Informe o nome do risco (2 a 120 caracteres).']);
        }

        $tipo = (int) ($dados['risco_tipo'] ?? 0);
        if (!DB::table('exame_risco_tipos')->where('id', $tipo)->exists()) {
            throw ValidationException::withMessages(['risco_tipo' => 'Escolha o tipo do risco.']);
        }

        $duplicado = DB::table('exame_riscos')
            ->where('empresa_id', $empresaId)
            ->whereRaw('LOWER(label) = ?', [mb_strtolower($label)])
            ->when($riscoId, fn ($q) => $q->where('id', '!=', $riscoId))
            ->exists();
        if ($duplicado) {
            throw ValidationException::withMessages(['label' => 'Já existe um risco com esse nome.']);
        }

        $risco = $riscoId
            ? ExameRisco::query()->where('empresa_id', $empresaId)->findOrFail($riscoId)
            : new ExameRisco(['empresa_id' => $empresaId, 'ativo' => true]);

        $risco->label = $label;
        $risco->risco_tipo = $tipo;
        $risco->empresa_id = $empresaId;
        if (array_key_exists('ativo', $dados)) {
            $risco->ativo = (bool) $dados['ativo'];
        }
        $risco->save();

        return $risco;
    }

    /**
     * Garante que os ids são riscos ativos da empresa (multi-tenant).
     *
     * @param  list<int>  $ids
     */
    public function validarDaEmpresa(int $empresaId, array $ids, string $campo = 'riscos'): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return;
        }

        $encontrados = DB::table('exame_riscos')->where('empresa_id', $empresaId)->whereIn('id', $ids)->count();
        if ($encontrados !== count($ids)) {
            throw ValidationException::withMessages([$campo => 'Há riscos que não pertencem a esta empresa.']);
        }
    }
}
