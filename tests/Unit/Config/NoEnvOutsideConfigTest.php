<?php

namespace Tests\Unit\Config;

use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Tests\TestCase;

/**
 * Garante que env() não seja usado fora de config/ — compatível com config:cache
 * e FPM clear_env=yes em produção.
 */
class NoEnvOutsideConfigTest extends TestCase
{
    private const ROOTS = ['app', 'resources', 'routes', 'database'];

    #[Test]
    public function nao_usa_env_fora_de_config(): void
    {
        $hits = [];
        $pattern = '/env\s*\(\s*[\'"][A-Z0-9_]+[\'"]/';

        foreach (self::ROOTS as $root) {
            $base = base_path($root);
            if (!is_dir($base)) {
                continue;
            }

            $iterator = new RegexIterator(
                new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base)),
                '/\.php$/i'
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                $path = $file->getPathname();
                $contents = file_get_contents($path);
                if ($contents === false) {
                    continue;
                }

                // Ignora comentários de linha simples com env( em exemplos
                if (!preg_match($pattern, $contents)) {
                    continue;
                }

                foreach (preg_split("/\r\n|\n|\r/", $contents) as $lineNum => $line) {
                    $trimmed = ltrim($line);
                    if ($trimmed === '' || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '#')) {
                        continue;
                    }
                    if (preg_match($pattern, $line)) {
                        $rel = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
                        $hits[] = $rel . ':' . ($lineNum + 1);
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $hits,
            "env() fora de config/ quebra com config:cache. Substitua por config()/app()->environment().\n"
            . implode("\n", $hits)
        );
    }
}
