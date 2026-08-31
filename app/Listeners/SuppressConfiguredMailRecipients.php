<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SuppressConfiguredMailRecipients
{
    /**
     * Login sintético gerado pelo dedup de users.login (ver migrations
     * 2026_08_29_100002/100003/100006): "joao+dup12345@gmail.com" nunca é um
     * endereço real, mesmo quando a conta continua ativo=1. Filtrado aqui
     * (e não em cada callsite) porque não há ativo-check consistente nos
     * ~25 pontos que leem User->login para montar destinatários de e-mail.
     */
    private const PADRAO_LOGIN_DEDUP = '/\+dup\d+@/i';

    /**
     * Placeholder de conta sistema (migration 100001): "sistema+123@mybp.com.br".
     */
    private const PADRAO_LOGIN_SISTEMA = '/^sistema\+\d+@mybp\.com\.br$/i';

    public function handle(MessageSending $event): ?bool
    {
        $suppressed = config('mail.suppress_recipients', []);
        $blocked = array_map('strtolower', $suppressed);
        $message = $event->message;

        $to = $this->filterAddresses($message->getTo(), $blocked);
        $cc = $this->filterAddresses($message->getCc(), $blocked);
        $bcc = $this->filterAddresses($message->getBcc(), $blocked);

        $this->applyRecipients($message, 'To', $to);
        $this->applyRecipients($message, 'Cc', $cc);
        $this->applyRecipients($message, 'Bcc', $bcc);

        if ($to === [] && $cc === [] && $bcc === []) {
            return false;
        }

        return null;
    }

    /**
     * @param  array<int, Address>  $addresses
     * @param  array<int, string>  $blocked
     * @return array<int, Address>
     */
    private function filterAddresses(array $addresses, array $blocked): array
    {
        return array_values(array_filter($addresses, function (Address $address) use ($blocked) {
            $endereco = strtolower($address->getAddress());

            if (in_array($endereco, $blocked, true)) {
                return false;
            }

            return ! preg_match(self::PADRAO_LOGIN_DEDUP, $endereco)
                && ! preg_match(self::PADRAO_LOGIN_SISTEMA, $endereco);
        }));
    }

    /**
     * @param  array<int, Address>  $addresses
     */
    private function applyRecipients(Email $message, string $header, array $addresses): void
    {
        if ($addresses === []) {
            $message->getHeaders()->remove($header);

            return;
        }

        match ($header) {
            'To' => $message->to(...$addresses),
            'Cc' => $message->cc(...$addresses),
            'Bcc' => $message->bcc(...$addresses),
        };
    }
}
