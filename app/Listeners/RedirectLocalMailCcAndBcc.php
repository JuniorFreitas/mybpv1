<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class RedirectLocalMailCcAndBcc
{
    public function handle(MessageSending $event): ?bool
    {
        if (! self::shouldApply(app()->environment(), (string) config('mail.default'))) {
            return null;
        }

        $redirect = config('mail.local_redirect_cc_bcc');
        if (! is_string($redirect)) {
            return null;
        }
        $redirect = trim($redirect);
        if ($redirect === '') {
            return null;
        }

        $email = $event->message;
        if (! $email instanceof Email) {
            return null;
        }

        self::applyToEmail($email, $redirect);

        return null;
    }

    /** Só redireciona cópias em ambiente local com envio via API SES. */
    public static function shouldApply(string $appEnv, string $mailDefault): bool
    {
        return $appEnv === 'local' && strtolower($mailDefault) === 'ses';
    }

    /**
     * Remove CC/BCC reais e envia uma única cópia oculta para o endereço de teste (local).
     */
    public static function applyToEmail(Email $email, string $redirectTarget): void
    {
        if ($email->getCc() === [] && $email->getBcc() === []) {
            return;
        }

        $email->getHeaders()->remove('Cc');
        $email->getHeaders()->remove('Bcc');
        $email->bcc(new Address($redirectTarget));
    }
}
