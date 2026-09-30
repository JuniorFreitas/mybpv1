<?php

namespace App\Console\Commands;

use App\Authorization\Catalog\AdmissaoCihCatalog;
use App\Authorization\Catalog\ConfiguracaoCatalog;
use App\Authorization\Catalog\PrivilegioCatalog;
use Illuminate\Console\Command;

class HabilidadesRegenerarCompletoCatalogCommand extends Command
{
    protected $signature = 'habilidades:regenerar-completo-catalog
                            {--dry-run : Mostra quantas entradas seriam geradas sem gravar o arquivo}';

    protected $description = 'Regenera CompletoCatalog.php a partir do HabilidadesTableSeeder (exceto piloto)';

    public function handle(): int
    {
        $seederPath = database_path('seeders/HabilidadesTableSeeder.php');
        $targetPath = app_path('Authorization/Catalog/CompletoCatalog.php');

        if (!is_readable($seederPath)) {
            $this->error("Seeder não encontrado: {$seederPath}");

            return self::FAILURE;
        }

        $src = file_get_contents($seederPath);
        if ($src === false) {
            $this->error('Falha ao ler o seeder.');

            return self::FAILURE;
        }

        preg_match_all(
            "/\\\$lista\\[\\]\\s*=\\s*\\[\\s*'nome'\\s*=>\\s*'([^']+)'\\s*,\\s*'descricao'\\s*=>\\s*'([^']*)'/",
            $src,
            $matches,
            PREG_SET_ORDER
        );

        $pilot = array_flip($this->pilotNomes());
        $entries = [];
        foreach ($matches as $row) {
            $nome = $row[1];
            if (isset($pilot[$nome])) {
                continue;
            }
            $entries[] = [$nome, $row[2]];
        }

        if ($this->option('dry-run')) {
            $this->info('Dry-run: ' . count($entries) . ' entradas (piloto excluído: ' . count($pilot) . ').');

            return self::SUCCESS;
        }

        $php = $this->buildPhp($entries);
        if (file_put_contents($targetPath, $php) === false) {
            $this->error("Falha ao gravar {$targetPath}");

            return self::FAILURE;
        }

        $this->info('CompletoCatalog regenerado com ' . count($entries) . ' entradas em ' . $targetPath);

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function pilotNomes(): array
    {
        $nomes = [];
        foreach ([
            ConfiguracaoCatalog::definitions(),
            AdmissaoCihCatalog::definitions(),
            PrivilegioCatalog::definitions(),
        ] as $defs) {
            foreach ($defs as $def) {
                $nomes[] = $def->nome;
            }
        }

        return array_values(array_unique($nomes));
    }

    /**
     * @param list<array{0: string, 1: string}> $entries
     */
    private function buildPhp(array $entries): string
    {
        $out = <<<'PHP'
<?php

namespace App\Authorization\Catalog;

use App\Authorization\HabilidadeDefinition;
use App\Authorization\HabilidadeMetaParser;

/**
 * Catálogo tipado gerado a partir do HabilidadesTableSeeder (exceto piloto).
 * Labels via HabilidadeMetaParser; piloto permanece nos catalogs dedicados.
 *
 * Regenerar: php artisan habilidades:regenerar-completo-catalog
 */
final class CompletoCatalog
{
    /**
     * @return list<HabilidadeDefinition>
     */
    public static function definitions(): array
    {
        $defs = [];
        foreach (self::entries() as [$nome, $descricao]) {
            $defs[] = HabilidadeMetaParser::toDefinition($nome, $descricao);
        }

        return $defs;
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private static function entries(): array
    {
        return [

PHP;

        foreach ($entries as [$nome, $desc]) {
            $out .= '            [' . var_export($nome, true) . ', ' . var_export($desc, true) . "],\n";
        }

        $out .= <<<'PHP'
        ];
    }
}

PHP;

        return $out;
    }
}
