<?php

namespace App\Console\Commands;

use App\Authorization\HabilidadeRegistry;
use App\Http\Middleware\CarregaHabilidades;
use App\Models\Habilidade;
use Illuminate\Console\Command;

class HabilidadesSyncCatalogCommand extends Command
{
    protected $signature = 'habilidades:sync-catalog
                            {--update-descricoes : Atualiza descricao das habilidades presentes no registry}
                            {--dry-run : Mostra o que seria alterado sem gravar}';

    protected $description = 'Sincroniza metadados (modulo/recurso/acao) das habilidades a partir do registry tipado';

    public function handle(HabilidadeRegistry $registry): int
    {
        $updateDescricoes = (bool) $this->option('update-descricoes');
        $dryRun = (bool) $this->option('dry-run');

        $criadas = 0;
        $atualizadas = 0;
        $heuristicas = 0;

        foreach ($registry->all() as $definition) {
            $existente = Habilidade::query()->where('nome', $definition->nome)->first();
            $payload = [
                'modulo' => $definition->modulo,
                'recurso' => $definition->recurso,
                'acao' => $definition->acao,
            ];

            if ($existente === null) {
                $payload['nome'] = $definition->nome;
                $payload['descricao'] = mb_substr($definition->descricao, 0, 150);
                if ($dryRun) {
                    $this->line("[dry-run] criar {$definition->nome}");
                } else {
                    Habilidade::query()->create($payload);
                }
                $criadas++;
                continue;
            }

            $mudou = false;
            foreach (['modulo', 'recurso', 'acao'] as $campo) {
                if ((string) $existente->{$campo} !== (string) $payload[$campo]) {
                    $mudou = true;
                    break;
                }
            }

            if ($updateDescricoes && (string) $existente->descricao !== (string) $definition->descricao) {
                $payload['descricao'] = mb_substr($definition->descricao, 0, 150);
                $mudou = true;
            }

            if ($mudou) {
                if ($dryRun) {
                    $this->line("[dry-run] atualizar {$definition->nome}");
                } else {
                    $existente->fill($payload)->save();
                }
                $atualizadas++;
            }
        }

        // Heurística para habilidades fora do registry piloto (não cria, só preenche metadados vazios).
        $foraDoCatalogo = Habilidade::query()
            ->where(function ($q) {
                $q->whereNull('modulo')->orWhere('modulo', '');
            })
            ->orderBy('id')
            ->get();

        foreach ($foraDoCatalogo as $habilidade) {
            $meta = $registry->enrich($habilidade->nome, $habilidade->descricao);
            $payload = [
                'modulo' => $meta['modulo'],
                'recurso' => $meta['recurso'],
                'acao' => $meta['acao'],
            ];

            if ($dryRun) {
                $this->line("[dry-run] heurística {$habilidade->nome} → {$meta['modulo']}/{$meta['recurso']}/{$meta['acao']}");
            } else {
                $habilidade->fill($payload)->save();
            }
            $heuristicas++;
        }

        if (!$dryRun) {
            CarregaHabilidades::forgetNomesCache();
        }

        $this->info("Sync concluído. Criadas: {$criadas}, atualizadas (catalog): {$atualizadas}, heurística: {$heuristicas}" . ($dryRun ? ' [dry-run]' : ''));

        return self::SUCCESS;
    }
}
