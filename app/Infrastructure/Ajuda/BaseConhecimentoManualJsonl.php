<?php

namespace App\Infrastructure\Ajuda;

use App\Domain\Ajuda\BaseConhecimentoManual;
use App\Domain\Ajuda\ManualTrecho;
use Illuminate\Support\Facades\Cache;

/**
 * Lê a base gerada por docs/manual-completo/gerar-base-conhecimento.py.
 *
 * A chave de cache carrega a versão do arquivo (mtime + tamanho): regenerar o
 * manual troca a chave e invalida o cache sem intervenção manual.
 */
final class BaseConhecimentoManualJsonl implements BaseConhecimentoManual
{
    private const CACHE_TTL_SECONDS = 86400;

    /** @var array<int, ManualTrecho>|null */
    private ?array $trechos = null;

    public function disponivel(): bool
    {
        $path = $this->path();

        return is_file($path) && is_readable($path);
    }

    public function versao(): string
    {
        if (! $this->disponivel()) {
            return 'indisponivel';
        }

        $path = $this->path();

        return substr(sha1($path.'|'.filemtime($path).'|'.filesize($path)), 0, 12);
    }

    public function trechos(): array
    {
        if ($this->trechos !== null) {
            return $this->trechos;
        }

        if (! $this->disponivel()) {
            return $this->trechos = [];
        }

        $linhas = Cache::remember(
            'ajuda:manual:trechos:'.$this->versao(),
            self::CACHE_TTL_SECONDS,
            fn () => $this->lerArquivo(),
        );

        return $this->trechos = array_map(
            static fn (array $linha) => ManualTrecho::fromArray($linha),
            $linhas,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function lerArquivo(): array
    {
        $linhas = [];
        foreach (file($this->path(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $linha) {
            $dados = json_decode($linha, true);
            if (is_array($dados) && filled($dados['id'] ?? null) && filled($dados['texto'] ?? null)) {
                $linhas[] = $dados;
            }
        }

        return $linhas;
    }

    private function path(): string
    {
        return base_path((string) config('services.assistente_manual.base_path'));
    }
}
