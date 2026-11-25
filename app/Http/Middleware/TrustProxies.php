<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Proxies confiáveis. Preferir CIDRs do LB/Cloudflare via TRUSTED_PROXIES.
     * Ex.: TRUSTED_PROXIES=173.245.48.0/20,103.21.244.0/22
     * Use * apenas em ambientes controlados (não recomendado em produção).
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_AWS_ELB;

    public function __construct()
    {
        // Default: redes privadas (LB/VPC). Em produção defina TRUSTED_PROXIES com CIDRs do proxy.
        // Evitar '*' — permite spoof de X-Forwarded-*.
        $configured = env('TRUSTED_PROXIES', '10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,127.0.0.1');
        if ($configured === null || $configured === '') {
            $this->proxies = ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.1'];
        } elseif ($configured === '*') {
            $this->proxies = '*';
        } else {
            $this->proxies = array_values(array_filter(array_map('trim', explode(',', $configured))));
        }
    }
}
