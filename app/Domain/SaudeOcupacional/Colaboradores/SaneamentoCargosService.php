<?php

namespace App\Domain\SaudeOcupacional\Colaboradores;

use Illuminate\Support\Facades\DB;

/**
 * Colaboradores ativos sem cargo no catálogo (feedback sem vaga_id e sem vaga aberta com vaga):
 * casa pelo nome do cargo gravado na admissão (admissoes.cargo) com vagas.nome da mesma empresa.
 * Sem o cargo vinculado, PGR e regras por cargo não alcançam o colaborador.
 */
class SaneamentoCargosService
{
    /**
     * @return array{sem_cargo: int, casaveis: int, nao_casados: array<string, int>, mapa: array<int, int>}
     */
    public function analisar(int $empresaId): array
    {
        $linhas = (new ColaboradoresAtivosQuery())->daEmpresa($empresaId)
            ->leftJoin('vagas_abertas as va', 'va.id', '=', 'fc.vagas_abertas_id')
            ->whereNull('fc.vaga_id')
            ->whereNull('va.vaga_id')
            ->distinct()
            ->get(['fc.id', 'a.cargo']);

        $vagas = DB::table('vagas')
            ->where('empresa_id', $empresaId)
            ->orderByDesc('ativo')
            ->orderBy('id')
            ->get(['id', 'nome'])
            ->groupBy(fn ($v) => $this->normalizar((string) $v->nome))
            ->map(fn ($grupo) => (int) $grupo->first()->id);

        $mapa = [];
        $naoCasados = [];
        foreach ($linhas as $linha) {
            $chave = $this->normalizar((string) $linha->cargo);
            if ($chave !== '' && isset($vagas[$chave])) {
                $mapa[(int) $linha->id] = $vagas[$chave];
                continue;
            }
            $rotulo = $chave === '' ? '(sem cargo na admissão)' : $chave;
            $naoCasados[$rotulo] = ($naoCasados[$rotulo] ?? 0) + 1;
        }

        arsort($naoCasados);

        return [
            'sem_cargo' => $linhas->count(),
            'casaveis' => count($mapa),
            'nao_casados' => $naoCasados,
            'mapa' => $mapa,
        ];
    }

    /**
     * Grava feedback_curriculos.vaga_id dos colaboradores casados. Só preenche quem está sem cargo.
     *
     * @param  array<int, int>  $mapa  feedback_id => vaga_id
     */
    public function aplicar(int $empresaId, array $mapa): int
    {
        $porVaga = [];
        foreach ($mapa as $feedbackId => $vagaId) {
            $porVaga[$vagaId][] = $feedbackId;
        }

        \App\Domain\SaudeOcupacional\Painel\PainelCache::invalidar($empresaId);

        return DB::transaction(function () use ($empresaId, $porVaga) {
            $total = 0;
            foreach ($porVaga as $vagaId => $feedbacks) {
                foreach (array_chunk($feedbacks, 500) as $lote) {
                    $total += DB::table('feedback_curriculos')
                        ->where('empresa_id', $empresaId)
                        ->whereNull('vaga_id')
                        ->whereIn('id', $lote)
                        ->update(['vaga_id' => $vagaId, 'updated_at' => now()]);
                }
            }

            return $total;
        });
    }

    private function normalizar(string $nome): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', mb_strtoupper($nome)));
    }
}
