<?php

namespace App\Support\Mail;

/**
 * Em APP_ENV=local, o alwaysTo redireciona To/Cc/Bcc para um endereço de teste.
 * Com Mailpit (MAIL_HOST=host.docker.internal etc.), preservamos os destinatários reais
 * para inspecionar To/Cc/Bcc em http://localhost:8025.
 */
class LocalMailRedirect
{
    public static function smtpHost(): string
    {
        return strtolower(trim((string) (
            config('mail.mailers.smtp.host')
            ?? config('mail.host')
            ?? ''
        )));
    }

    /**
     * @param  list<string>|null  $mailpitHosts
     */
    public static function isMailpitHost(?string $smtpHost = null, ?array $mailpitHosts = null): bool
    {
        $host = strtolower(trim((string) ($smtpHost ?? self::smtpHost())));
        if ($host === '') {
            return false;
        }

        $hosts = $mailpitHosts ?? config('mail.local_mailpit_hosts', [
            'host.docker.internal',
            '127.0.0.1',
            'localhost',
            'mailpit',
        ]);

        $hosts = array_map(
            static fn ($h) => strtolower(trim((string) $h)),
            is_array($hosts) ? $hosts : []
        );

        return in_array($host, $hosts, true);
    }

    public static function shouldForceAlwaysTo(string $appEnv, ?string $smtpHost = null): bool
    {
        if ($appEnv !== 'local') {
            return false;
        }

        return ! self::isMailpitHost($smtpHost);
    }
}
