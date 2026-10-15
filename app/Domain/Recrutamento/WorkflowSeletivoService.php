<?php

namespace App\Domain\Recrutamento;

use App\Models\EtapaTipo;
use App\Models\Papel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WorkflowSeletivoService
{
    /**
     * Garante que a empresa tenha o workflow padrão (só cria faltantes).
     */
    public function garantirPadraoEmpresa(int $clienteId): void
    {
        $existentes = EtapaTipo::query()
            ->where('cliente_id', $clienteId)
            ->whereNull('vaga_id')
            ->pluck('chave')
            ->filter()
            ->all();

        $criadosPorChave = [];

        DB::transaction(function () use ($clienteId, $existentes, &$criadosPorChave) {
            foreach (WorkflowSeletivoPadrao::definicoes() as $def) {
                if (in_array($def['chave'], $existentes, true)) {
                    continue;
                }
                $etapa = EtapaTipo::create([
                    'cliente_id' => $clienteId,
                    'vaga_id' => null,
                    'nome' => $def['nome'],
                    'chave' => $def['chave'],
                    'tipo' => $def['tipo'],
                    'ordem' => $def['ordem'],
                    'obrigatoria' => $def['obrigatoria'],
                    'is_apto_admissao' => $def['is_apto_admissao'],
                    'permite_email' => $def['permite_email'],
                    'habilidade' => $def['habilidade'],
                    'ativo' => $def['ativo'],
                    'depende_de_id' => null,
                ]);
                $criadosPorChave[$def['chave']] = $etapa->id;
            }

            $mapa = EtapaTipo::query()
                ->where('cliente_id', $clienteId)
                ->whereNull('vaga_id')
                ->pluck('id', 'chave');

            foreach (WorkflowSeletivoPadrao::definicoes() as $def) {
                $dep = $def['depende_de_chave'] ?? null;
                if (!$dep) {
                    continue;
                }
                $id = $mapa[$def['chave']] ?? null;
                $depId = $mapa[$dep] ?? null;
                if ($id && $depId) {
                    EtapaTipo::where('id', $id)->whereNull('depende_de_id')->update(['depende_de_id' => $depId]);
                }
            }
        });
    }

    /**
     * Clona o padrão da empresa para um cargo (override).
     */
    public function clonarPadraoParaCargo(int $clienteId, int $vagaId): Collection
    {
        $this->garantirPadraoEmpresa($clienteId);

        $jaTem = EtapaTipo::query()
            ->where('cliente_id', $clienteId)
            ->where('vaga_id', $vagaId)
            ->exists();

        if ($jaTem) {
            return $this->listarEscopo($clienteId, $vagaId, false);
        }

        DB::transaction(function () use ($clienteId, $vagaId) {
            $origem = EtapaTipo::query()
                ->where('cliente_id', $clienteId)
                ->whereNull('vaga_id')
                ->orderBy('ordem')
                ->get();

            $mapaIds = [];
            foreach ($origem as $item) {
                $novo = $item->replicate(['id']);
                $novo->vaga_id = $vagaId;
                $novo->depende_de_id = null;
                $novo->save();
                $mapaIds[$item->id] = $novo->id;
            }

            foreach ($origem as $item) {
                if ($item->depende_de_id && isset($mapaIds[$item->id], $mapaIds[$item->depende_de_id])) {
                    EtapaTipo::where('id', $mapaIds[$item->id])
                        ->update(['depende_de_id' => $mapaIds[$item->depende_de_id]]);
                }
            }
        });

        return $this->listarEscopo($clienteId, $vagaId, false);
    }

    /**
     * Remove override do cargo (volta ao padrão da empresa).
     */
    public function removerOverrideCargo(int $clienteId, int $vagaId): void
    {
        EtapaTipo::query()
            ->where('cliente_id', $clienteId)
            ->where('vaga_id', $vagaId)
            ->delete();
    }

    /**
     * Resolve o conjunto de etapas: override do cargo ou padrão da empresa.
     */
    public function resolverEtapas(int $clienteId, ?int $vagaId, bool $somenteAtivas = true): Collection
    {
        $this->garantirPadraoEmpresa($clienteId);

        if ($vagaId) {
            $override = $this->listarEscopo($clienteId, $vagaId, $somenteAtivas);
            if ($override->isNotEmpty()) {
                return $override;
            }
        }

        return $this->listarEscopo($clienteId, null, $somenteAtivas);
    }

    public function listarEscopo(int $clienteId, ?int $vagaId, bool $somenteAtivas = true): Collection
    {
        $q = EtapaTipo::query()
            ->where('cliente_id', $clienteId)
            ->when($vagaId === null, fn ($qq) => $qq->whereNull('vaga_id'))
            ->when($vagaId !== null, fn ($qq) => $qq->where('vaga_id', $vagaId))
            ->orderBy('ordem')
            ->orderBy('id');

        if ($somenteAtivas) {
            $q->where('ativo', true);
        }

        return $q->get();
    }

    /**
     * Filtra etapas que o usuário pode ver/usar (master da empresa + usuário).
     */
    public function filtrarPorPermissao(Collection $etapas, User $user): Collection
    {
        $habilidadesMaster = $this->habilidadesDoMaster((int) $user->empresa_id);

        return $etapas->filter(function (EtapaTipo $etapa) use ($user, $habilidadesMaster) {
            $hab = $etapa->habilidade;
            if (!$hab) {
                return true;
            }
            if (!in_array($hab, $habilidadesMaster, true)) {
                return false;
            }

            return $user->can($hab);
        })->values();
    }

    /**
     * @return list<string>
     */
    public function habilidadesDoMaster(int $empresaId): array
    {
        $papel = Papel::query()
            ->where('empresa_id', $empresaId)
            ->where('master', true)
            ->with('habilidades:id,nome')
            ->first();

        if (!$papel) {
            return [];
        }

        return $papel->habilidades->pluck('nome')->filter()->values()->all();
    }

    public function cargoTemOverride(int $clienteId, int $vagaId): bool
    {
        return EtapaTipo::query()
            ->where('cliente_id', $clienteId)
            ->where('vaga_id', $vagaId)
            ->exists();
    }
}
