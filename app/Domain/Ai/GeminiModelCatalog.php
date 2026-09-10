<?php

namespace App\Domain\Ai;

/**
 * Resolve a lista de modelos elegíveis para rotação a partir de configuração
 * do servidor (GEMINI_MODEL/GEMINI_MODELS), nunca do frontend — a rota de IA
 * não aceita um model_id vindo da requisição. Por isso não é preciso manter
 * aqui uma lista fixa de IDs conhecidos (que ficaria desatualizada a cada
 * novo modelo do provedor); só valida o formato, para não deixar um valor
 * absurdo virar segmento da URL chamada em GeminiHttpClient.
 */
final class GeminiModelCatalog
{
    private const ID_PATTERN = '/^[a-zA-Z0-9](?:[a-zA-Z0-9.\-]{1,62})[a-zA-Z0-9]$/';

    /**
     * @return array<int, array{alias: string, id: string}>
     */
    public function resolve(?string $configuredModels, ?string $preferredModel): array
    {
        $ids = collect(explode(',', (string) $configuredModels))
            ->map(fn ($id) => trim($id))
            ->filter(fn ($id) => $this->isValidId($id))
            ->unique()
            ->values();

        $preferredModel = trim((string) $preferredModel);
        if ($preferredModel !== '' && $this->isValidId($preferredModel)) {
            $ids = $ids
                ->reject(fn ($id) => $id === $preferredModel)
                ->prepend($preferredModel);
        }

        return $ids
            ->map(fn ($id) => ['alias' => $id, 'id' => $id])
            ->values()
            ->all();
    }

    private function isValidId(string $id): bool
    {
        return (bool) preg_match(self::ID_PATTERN, $id);
    }
}
