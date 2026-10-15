<?php

namespace App\Support\ApiExterna;

/**
 * public_id no formato UUID (xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).
 * Determinístico e reversível no servidor (HMAC + ID), sem coluna no banco.
 */
final class ApiExternaId
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    public static function encode(int|string|null $id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }

        // 8 bytes HMAC + 8 bytes ID mascarado (XOR com máscara fixa) = UUID.
        $mac = substr(hash_hmac('sha256', 'api-v3|' . $id, self::secret(), true), 0, 8);
        $maskedId = pack('J', $id) ^ self::idMask();
        $payload = $mac . $maskedId;

        return self::binToUuid($payload);
    }

    public static function decode(string $publicId): ?int
    {
        $publicId = strtolower(trim($publicId));
        if ($publicId === '' || ! preg_match(self::UUID_PATTERN, $publicId)) {
            return null;
        }

        $bin = self::uuidToBin($publicId);
        if ($bin === null || strlen($bin) !== 16) {
            return null;
        }

        $mac = substr($bin, 0, 8);
        $id = unpack('J', substr($bin, 8, 8) ^ self::idMask())[1];
        if ($id <= 0) {
            return null;
        }

        $expectedMac = substr(hash_hmac('sha256', 'api-v3|' . $id, self::secret(), true), 0, 8);
        if (! hash_equals($expectedMac, $mac)) {
            return null;
        }

        return $id;
    }

    /**
     * @param  array<string, mixed>|object  $row
     * @param  list<string>|array<string, string>  $idKeys
     * @param  list<string>  $dropKeys
     * @return array<string, mixed>
     */
    public static function mapRow(array|object $row, array $idKeys = ['id'], array $dropKeys = ['empresa_id']): array
    {
        $data = is_array($row) ? $row : get_object_vars($row);

        foreach ($dropKeys as $key) {
            unset($data[$key]);
        }

        $map = self::normalizeIdMap($idKeys);

        foreach ($map as $from => $to) {
            if (! array_key_exists($from, $data)) {
                continue;
            }

            $value = $data[$from];
            unset($data[$from]);
            $data[$to] = self::encode($value !== null && $value !== '' ? (int) $value : null);
        }

        return $data;
    }

    public static function routePattern(): string
    {
        return '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';
    }

    private static function secret(): string
    {
        return (string) (config('services.api_externa.public_id_salt') ?: config('app.key'));
    }

    private static function idMask(): string
    {
        return substr(hash_hmac('sha256', 'api-v3-id-mask', self::secret(), true), 0, 8);
    }

    private static function binToUuid(string $bin): string
    {
        $hex = bin2hex($bin);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    private static function uuidToBin(string $uuid): ?string
    {
        $hex = str_replace('-', '', $uuid);
        if (strlen($hex) !== 32 || ! ctype_xdigit($hex)) {
            return null;
        }

        $bin = hex2bin($hex);

        return $bin === false ? null : $bin;
    }

    /**
     * @param  list<string>|array<string, string>  $idKeys
     * @return array<string, string>
     */
    private static function normalizeIdMap(array $idKeys): array
    {
        if ($idKeys === []) {
            return [];
        }

        if (array_is_list($idKeys)) {
            $map = [];
            foreach ($idKeys as $from) {
                $map[$from] = $from === 'id'
                    ? 'public_id'
                    : (str_ends_with($from, '_id')
                        ? substr($from, 0, -3) . '_public_id'
                        : $from . '_public_id');
            }

            return $map;
        }

        return $idKeys;
    }
}
