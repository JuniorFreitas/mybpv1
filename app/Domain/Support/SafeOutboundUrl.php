<?php

namespace App\Domain\Support;

/**
 * Valida uma URL de saída antes de uma chamada HTTP para um serviço externo,
 * para reduzir o risco de SSRF quando o destino depende de configuração.
 */
final class SafeOutboundUrl
{
    private const BLOCKED_HOSTS = [
        'localhost',
        '127.0.0.1',
        '0.0.0.0',
        '::1',
        'metadata.google.internal',
        '169.254.169.254',
    ];

    /**
     * @param  string[]  $allowedHosts
     */
    public function isAllowed(string $url, array $allowedHosts, bool $requireHttps = true, bool $resolveDns = false): bool
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        if ($requireHttps && $scheme !== 'https') {
            return false;
        }

        $host = strtolower($parts['host']);

        if (in_array($host, self::BLOCKED_HOSTS, true)) {
            return false;
        }

        $allowedHosts = array_map('strtolower', array_filter(array_map('trim', $allowedHosts)));
        if ($allowedHosts === [] || ! in_array($host, $allowedHosts, true)) {
            return false;
        }

        if ($this->isIpAddress($host) && $this->isPrivateOrReservedIp($host)) {
            return false;
        }

        if ($resolveDns && ! $this->isIpAddress($host)) {
            $resolved = @gethostbynamel($host);
            if ($resolved === false || $resolved === []) {
                return false;
            }
            foreach ($resolved as $ip) {
                if ($this->isPrivateOrReservedIp($ip)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function isIpAddress(string $host): bool
    {
        return filter_var($host, FILTER_VALIDATE_IP) !== false;
    }

    private function isPrivateOrReservedIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
