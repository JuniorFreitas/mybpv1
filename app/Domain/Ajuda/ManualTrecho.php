<?php

namespace App\Domain\Ajuda;

/**
 * Um trecho da base de conhecimento do Manual Completo (uma linha de chunks.jsonl).
 */
final class ManualTrecho
{
    public function __construct(
        public readonly string $id,
        public readonly string $capitulo,
        public readonly string $secao,
        public readonly string $subsecao,
        public readonly string $caminhoMenu,
        public readonly string $ancora,
        public readonly ?int $paginaPdf,
        public readonly string $texto,
    ) {
    }

    /**
     * @param  array<string, mixed>  $linha
     */
    public static function fromArray(array $linha): self
    {
        return new self(
            id: (string) ($linha['id'] ?? ''),
            capitulo: (string) ($linha['capitulo'] ?? ''),
            secao: (string) ($linha['secao'] ?? ''),
            subsecao: (string) ($linha['subsecao'] ?? ''),
            caminhoMenu: (string) ($linha['caminho_menu'] ?? ''),
            ancora: (string) ($linha['ancora'] ?? ''),
            paginaPdf: isset($linha['pagina_pdf']) ? (int) $linha['pagina_pdf'] : null,
            texto: (string) ($linha['texto'] ?? ''),
        );
    }

    /**
     * Títulos e caminho de menu: termos exatos que pesam mais na busca.
     */
    public function cabecalho(): string
    {
        return trim(implode(' ', [$this->capitulo, $this->secao, $this->subsecao, $this->caminhoMenu]));
    }

    public function titulo(): string
    {
        $partes = array_filter([$this->capitulo, $this->secao, $this->subsecao], static fn ($parte) => $parte !== '');

        return implode(' › ', $partes);
    }
}
