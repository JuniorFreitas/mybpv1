<?php

namespace App\Authorization;

use App\Models\Habilidade;
use Illuminate\Support\Facades\DB;

/**
 * Audita e consolida linhas duplicadas de habilidades (alias → canônico).
 */
final class HabilidadeAliasConsolidator
{
    public const STATUS_NENHUM = 'nenhum';
    public const STATUS_SO_ALIAS = 'so_alias';
    public const STATUS_SO_CANONICO = 'so_canonico';
    public const STATUS_AMBOS = 'ambos';

    /**
     * @return array{
     *     alias: string,
     *     canonico: string,
     *     status: string,
     *     alias_id: int|null,
     *     canonico_id: int|null,
     *     pivots_alias: int,
     *     pivots_canonico: int
     * }
     */
    public function auditPair(string $alias, string $canonico): array
    {
        $aliasRow = Habilidade::query()->where('nome', $alias)->first();
        $canonRow = Habilidade::query()->where('nome', $canonico)->first();

        $status = self::STATUS_NENHUM;
        if ($aliasRow !== null && $canonRow !== null) {
            $status = self::STATUS_AMBOS;
        } elseif ($aliasRow !== null) {
            $status = self::STATUS_SO_ALIAS;
        } elseif ($canonRow !== null) {
            $status = self::STATUS_SO_CANONICO;
        }

        return [
            'alias' => $alias,
            'canonico' => $canonico,
            'status' => $status,
            'alias_id' => $aliasRow?->id,
            'canonico_id' => $canonRow?->id,
            'pivots_alias' => $aliasRow ? $this->countPivots((int) $aliasRow->id) : 0,
            'pivots_canonico' => $canonRow ? $this->countPivots((int) $canonRow->id) : 0,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function auditAll(): array
    {
        $rows = [];
        foreach (HabilidadeAliasMap::map() as $alias => $canonico) {
            $rows[] = $this->auditPair($alias, $canonico);
        }

        return $rows;
    }

    /**
     * @return array{action: string, ok: bool, message: string}
     */
    public function consolidatePair(string $alias, string $canonico, bool $apply): array
    {
        $audit = $this->auditPair($alias, $canonico);

        return match ($audit['status']) {
            self::STATUS_NENHUM => [
                'action' => 'skip',
                'ok' => true,
                'message' => 'Nenhuma linha alias ou canônico no banco.',
            ],
            self::STATUS_SO_CANONICO => [
                'action' => 'skip',
                'ok' => true,
                'message' => 'Já consolidado (somente canônico).',
            ],
            self::STATUS_SO_ALIAS => $this->renameAliasToCanonical($alias, $canonico, $apply),
            self::STATUS_AMBOS => $this->mergeAliasIntoCanonical(
                (int) $audit['alias_id'],
                (int) $audit['canonico_id'],
                $alias,
                $canonico,
                $apply
            ),
            default => [
                'action' => 'error',
                'ok' => false,
                'message' => 'Status desconhecido.',
            ],
        };
    }

    /**
     * @return list<array{alias: string, canonico: string, action: string, ok: bool, message: string}>
     */
    public function consolidateAll(bool $apply): array
    {
        $results = [];
        foreach (HabilidadeAliasMap::map() as $alias => $canonico) {
            $r = $this->consolidatePair($alias, $canonico, $apply);
            $results[] = array_merge(['alias' => $alias, 'canonico' => $canonico], $r);
        }

        return $results;
    }

    private function countPivots(int $habilidadeId): int
    {
        return (int) DB::table('papeis_habilidades')->where('habilidade_id', $habilidadeId)->count();
    }

    /**
     * @return array{action: string, ok: bool, message: string}
     */
    private function renameAliasToCanonical(string $alias, string $canonico, bool $apply): array
    {
        if (Habilidade::query()->where('nome', $canonico)->exists()) {
            return [
                'action' => 'error',
                'ok' => false,
                'message' => "Canônico {$canonico} já existe; esperado merge, não rename.",
            ];
        }

        if (!$apply) {
            return [
                'action' => 'rename',
                'ok' => true,
                'message' => "Renomear {$alias} → {$canonico}.",
            ];
        }

        $updated = Habilidade::query()->where('nome', $alias)->update(['nome' => $canonico]);
        if ($updated === 0) {
            return [
                'action' => 'error',
                'ok' => false,
                'message' => "Falha ao renomear {$alias}.",
            ];
        }

        return [
            'action' => 'rename',
            'ok' => true,
            'message' => "Renomeado {$alias} → {$canonico}.",
        ];
    }

    /**
     * @return array{action: string, ok: bool, message: string}
     */
    private function mergeAliasIntoCanonical(
        int $aliasId,
        int $canonicoId,
        string $alias,
        string $canonico,
        bool $apply
    ): array {
        $pivotCount = $this->countPivots($aliasId);

        if (!$apply) {
            return [
                'action' => 'merge',
                'ok' => true,
                'message' => "Mesclar {$alias} (#{$aliasId}, {$pivotCount} pivots) em {$canonico} (#{$canonicoId}) e remover alias.",
            ];
        }

        DB::transaction(function () use ($aliasId, $canonicoId) {
            $papelIds = DB::table('papeis_habilidades')
                ->where('habilidade_id', $aliasId)
                ->pluck('papel_id');

            foreach ($papelIds as $papelId) {
                $exists = DB::table('papeis_habilidades')
                    ->where('papel_id', $papelId)
                    ->where('habilidade_id', $canonicoId)
                    ->exists();

                if (!$exists) {
                    DB::table('papeis_habilidades')->insert([
                        'papel_id' => $papelId,
                        'habilidade_id' => $canonicoId,
                    ]);
                }
            }

            DB::table('papeis_habilidades')->where('habilidade_id', $aliasId)->delete();
            Habilidade::query()->where('id', $aliasId)->delete();
        });

        return [
            'action' => 'merge',
            'ok' => true,
            'message' => "Mesclado {$alias} em {$canonico}.",
        ];
    }
}
