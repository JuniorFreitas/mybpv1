<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

/**
 * ID público gerado em runtime (sem coluna no banco).
 * Usa Crypt + Base64 URL-safe para não expor o ID sequencial na URL.
 */
final class RuntimePublicId
{
    public static function encode(int|string $id): string
    {
        $id = (int) $id;
        if ($id <= 0) {
            throw new InvalidArgumentException('ID inválido para public_id.');
        }

        return self::toUrlSafe(Crypt::encryptString((string) $id));
    }

    public static function decode(string $publicId): int
    {
        $publicId = trim($publicId);
        if ($publicId === '') {
            throw new InvalidArgumentException('public_id vazio.');
        }

        try {
            $id = (int) Crypt::decryptString(self::fromUrlSafe($publicId));
        } catch (DecryptException $e) {
            throw new InvalidArgumentException('public_id inválido.', 0, $e);
        }

        if ($id <= 0) {
            throw new InvalidArgumentException('public_id inválido.');
        }

        return $id;
    }

    private static function toUrlSafe(string $value): string
    {
        return rtrim(strtr($value, '+/', '-_'), '=');
    }

    private static function fromUrlSafe(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return strtr($value, '-_', '+/');
    }
}
