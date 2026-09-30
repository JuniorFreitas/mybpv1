<?php

namespace App\Authorization;

use App\Authorization\Catalog\AdmissaoCihCatalog;
use App\Authorization\Catalog\CompletoCatalog;
use App\Authorization\Catalog\ConfiguracaoCatalog;
use App\Authorization\Catalog\PrivilegioCatalog;

/**
 * Catálogo tipado de habilidades (fonte de verdade para metadados e aliases).
 *
 * Ordem de carga: CompletoCatalog (base) → catalogs piloto (sobrescrevem labels).
 */
class HabilidadeRegistry
{
    /** @var array<string, HabilidadeDefinition>|null */
    private ?array $byNome = null;

    /** @var array<string, string>|null alias => nome canônico */
    private ?array $aliasMap = null;

    /**
     * @return list<HabilidadeDefinition>
     */
    public function all(): array
    {
        return array_values($this->indexed());
    }

    public function find(string $nome): ?HabilidadeDefinition
    {
        $canonico = $this->resolveAlias($nome);

        return $this->indexed()[$canonico] ?? null;
    }

    /**
     * Resolve alias para o nome canônico; se não houver alias, devolve o próprio nome.
     */
    public function resolveAlias(string $nomeOuAlias): string
    {
        $this->ensureLoaded();

        return $this->aliasMap[$nomeOuAlias] ?? $nomeOuAlias;
    }

    /**
     * @return array<string, string>
     */
    public function aliasMap(): array
    {
        $this->ensureLoaded();

        return $this->aliasMap;
    }

    /**
     * @return list<HabilidadeDefinition>
     */
    public function byModulo(string $modulo): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (HabilidadeDefinition $d) => $d->modulo === $modulo
        ));
    }

    /**
     * Metadados para enriquecimento de UI/API (catalog tipado ou heurística pelo nome).
     *
     * @return array{
     *     nome: string,
     *     modulo: string,
     *     recurso: string,
     *     acao: string,
     *     descricao: string|null,
     *     aliases: list<string>,
     *     modulo_label: string,
     *     recurso_label: string,
     *     acao_label: string
     * }
     */
    public function enrich(string $nome, ?string $descricao = null): array
    {
        $def = $this->find($nome);
        if ($def !== null) {
            $meta = $def->toMetaArray();
            if ($descricao !== null && $descricao !== '') {
                $meta['descricao'] = $descricao;
            }

            return $meta;
        }

        return HabilidadeMetaParser::parse($nome, $descricao);
    }

    /**
     * Invalida cache interno (útil em testes).
     */
    public function flush(): void
    {
        $this->byNome = null;
        $this->aliasMap = null;
    }

    /**
     * @return array<string, HabilidadeDefinition>
     */
    private function indexed(): array
    {
        $this->ensureLoaded();

        return $this->byNome;
    }

    private function ensureLoaded(): void
    {
        if ($this->byNome !== null) {
            return;
        }

        $this->byNome = [];
        $this->aliasMap = [];

        foreach ($this->catalogDefinitions() as $definition) {
            $this->byNome[$definition->nome] = $definition;
            foreach ($definition->aliases as $alias) {
                if ($alias === '' || $alias === $definition->nome) {
                    continue;
                }
                $this->aliasMap[$alias] = $definition->nome;
            }
        }

        foreach (HabilidadeAliasMap::map() as $alias => $canonico) {
            if ($alias === '' || $alias === $canonico) {
                continue;
            }
            $this->aliasMap[$alias] = $canonico;
        }
    }

    /**
     * @return list<HabilidadeDefinition>
     */
    private function catalogDefinitions(): array
    {
        // Base completa primeiro; piloto sobrescreve metadados/labels.
        return array_merge(
            CompletoCatalog::definitions(),
            ConfiguracaoCatalog::definitions(),
            AdmissaoCihCatalog::definitions(),
            PrivilegioCatalog::definitions(),
        );
    }
}
