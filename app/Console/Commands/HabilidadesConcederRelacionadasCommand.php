<?php

namespace App\Console\Commands;

use App\Http\Middleware\CarregaHabilidades;
use App\Models\Habilidade;
use App\Models\Papel;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Concede habilidades novas a papéis que já possuem uma habilidade "âncora" relacionada.
 */
class HabilidadesConcederRelacionadasCommand extends Command
{
    protected $signature = 'habilidades:conceder-relacionadas
                            {--dry-run : Mostra o que seria concedido sem gravar}';

    protected $description = 'Concede skills novas (ex. admissao_controle_exames, relatorio_nps) a papéis com habilidade âncora';

    /**
     * skill a conceder => [ancora, ?empresa_id restrita]
     *
     * @var array<string, array{0: string, 1: int|null}>
     */
    private const REGRAS = [
        // Controle de exames: quem já opera admissão
        'admissao_controle_exames' => ['admissao_processo', null],
        // NPS: só papéis da empresa MyBP que já têm relatório
        'relatorio_nps' => ['relatorio_relatorios', User::MYBP_EMPRESA_ID],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $concedidas = 0;
        $puladas = 0;

        foreach (self::REGRAS as $skillAlvo => [$skillAncora, $empresaId]) {
            $alvo = Habilidade::query()->where('nome', $skillAlvo)->first();
            $ancora = Habilidade::query()->where('nome', $skillAncora)->first();

            if ($alvo === null || $ancora === null) {
                $this->warn("Pular {$skillAlvo}: skill alvo ou âncora ausente no banco.");
                $puladas++;
                continue;
            }

            $papelIds = DB::table('papeis_habilidades')
                ->where('habilidade_id', $ancora->id)
                ->pluck('papel_id');

            $query = Papel::withoutGlobalScopes()->whereIn('id', $papelIds);
            if ($empresaId !== null) {
                $query->where('empresa_id', $empresaId);
            }
            $papeis = $query->get();

            foreach ($papeis as $papel) {
                $jaTem = $papel->habilidades()->where('habilidades.id', $alvo->id)->exists();
                if ($jaTem) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("[dry-run] papel #{$papel->id} ({$papel->nome}) ← {$skillAlvo} (via {$skillAncora})");
                } else {
                    $papel->habilidades()->attach($alvo->id);
                }
                $concedidas++;
            }
        }

        if (!$dryRun && $concedidas > 0) {
            CarregaHabilidades::forgetNomesCache();
        }

        $this->info("Concessões: {$concedidas}, regras puladas: {$puladas}" . ($dryRun ? ' [dry-run]' : ''));

        return self::SUCCESS;
    }
}
