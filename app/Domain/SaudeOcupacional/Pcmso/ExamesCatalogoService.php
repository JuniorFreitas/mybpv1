<?php

namespace App\Domain\SaudeOcupacional\Pcmso;

use App\Models\Exame;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Catálogo de exames complementares da empresa (tabela `exames`), com código da Tabela 27 do eSocial.
 * Também cuida do mapa "risco → exames sugeridos" (exame_riscos_exames).
 */
class ExamesCatalogoService
{
    /**
     * @return list<array{id: int, label: string, codigo_esocial: string|null, ativo: bool}>
     */
    public function listar(int $empresaId, bool $somenteAtivos = false): array
    {
        return DB::table('exames')
            ->where('empresa_id', $empresaId)
            ->when($somenteAtivos, fn ($q) => $q->where('ativo', true))
            ->orderBy('label')
            ->get(['id', 'label', 'codigo_esocial', 'ativo'])
            ->map(fn ($e) => [
                'id' => (int) $e->id,
                'label' => (string) $e->label,
                'codigo_esocial' => $e->codigo_esocial,
                'ativo' => (bool) $e->ativo,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function salvar(int $empresaId, ?int $exameId, array $dados): Exame
    {
        $label = trim((string) ($dados['label'] ?? ''));
        if (mb_strlen($label) < 2 || mb_strlen($label) > 120) {
            throw ValidationException::withMessages(['label' => 'Informe o nome do exame (2 a 120 caracteres).']);
        }

        $codigo = trim((string) ($dados['codigo_esocial'] ?? ''));
        if ($codigo !== '' && !preg_match('/^\d{4}$/', $codigo)) {
            throw ValidationException::withMessages(['codigo_esocial' => 'O código da Tabela 27 do eSocial tem 4 dígitos.']);
        }

        $duplicado = DB::table('exames')
            ->where('empresa_id', $empresaId)
            ->whereRaw('LOWER(label) = ?', [mb_strtolower($label)])
            ->when($exameId, fn ($q) => $q->where('id', '!=', $exameId))
            ->exists();
        if ($duplicado) {
            throw ValidationException::withMessages(['label' => 'Já existe um exame com esse nome.']);
        }

        $exame = $exameId
            ? Exame::query()->where('empresa_id', $empresaId)->findOrFail($exameId)
            : new Exame(['empresa_id' => $empresaId, 'ativo' => true]);
        $exame->empresa_id = $empresaId;
        $exame->label = $label;
        $exame->codigo_esocial = $codigo !== '' ? $codigo : null;
        if (array_key_exists('ativo', $dados)) {
            $exame->ativo = (bool) $dados['ativo'];
        }
        $exame->save();

        return $exame;
    }

    /**
     * @param  list<int>  $ids
     */
    public function validarDaEmpresa(int $empresaId, array $ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return;
        }

        if (DB::table('exames')->where('empresa_id', $empresaId)->whereIn('id', $ids)->count() !== count($ids)) {
            throw ValidationException::withMessages(['exames' => 'Há exames que não pertencem a esta empresa.']);
        }
    }

    /**
     * Mapa risco → exames sugeridos da empresa.
     *
     * @return array<int, list<array{exame_id: int, exame_tipo_ids: list<int>}>>
     */
    public function examesPorRisco(int $empresaId): array
    {
        $mapa = [];
        DB::table('exame_riscos_exames as re')
            ->join('exame_riscos as r', 'r.id', '=', 're.exame_risco_id')
            ->join('exames as e', 'e.id', '=', 're.exame_id')
            ->where('r.empresa_id', $empresaId)
            ->where('e.empresa_id', $empresaId)
            ->where('e.ativo', true)
            ->get(['re.exame_risco_id', 're.exame_id', 're.exame_tipo_ids'])
            ->each(function ($l) use (&$mapa) {
                $mapa[(int) $l->exame_risco_id][] = [
                    'exame_id' => (int) $l->exame_id,
                    'exame_tipo_ids' => array_map('intval', (array) json_decode((string) $l->exame_tipo_ids, true)),
                ];
            });

        return $mapa;
    }

    /**
     * Substitui os exames sugeridos de um risco.
     *
     * @param  list<array{exame_id?: int, exame_tipo_ids?: list<int>}>  $itens
     * @param  list<int>  $tiposValidos
     */
    public function salvarExamesDoRisco(int $empresaId, int $riscoId, array $itens, array $tiposValidos): void
    {
        if (!DB::table('exame_riscos')->where('empresa_id', $empresaId)->where('id', $riscoId)->exists()) {
            throw ValidationException::withMessages(['exame_risco_id' => 'Risco não pertence a esta empresa.']);
        }

        $normalizados = collect($itens)
            ->map(fn ($i) => [
                'exame_id' => (int) ($i['exame_id'] ?? 0),
                'exame_tipo_ids' => array_values(array_intersect(array_map('intval', (array) ($i['exame_tipo_ids'] ?? [])), $tiposValidos)),
            ])
            ->filter(fn ($i) => $i['exame_id'] > 0)
            ->unique('exame_id')
            ->values();

        if ($normalizados->contains(fn ($i) => $i['exame_tipo_ids'] === [])) {
            throw ValidationException::withMessages(['exames' => 'Marque em quais tipos de ASO cada exame é feito.']);
        }
        $this->validarDaEmpresa($empresaId, $normalizados->pluck('exame_id')->all());

        DB::transaction(function () use ($riscoId, $normalizados) {
            DB::table('exame_riscos_exames')->where('exame_risco_id', $riscoId)->delete();
            foreach ($normalizados as $item) {
                DB::table('exame_riscos_exames')->insert([
                    'exame_risco_id' => $riscoId,
                    'exame_id' => $item['exame_id'],
                    'exame_tipo_ids' => json_encode($item['exame_tipo_ids']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
