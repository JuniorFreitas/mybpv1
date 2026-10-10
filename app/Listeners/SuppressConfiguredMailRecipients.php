<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SuppressConfiguredMailRecipients
{
    private const FALLBACK_FROM = 'naoresponda@mybp.com.br';

    public function handle(MessageSending $event): ?bool
    {
        $suppressed = config('mail.suppress_recipients', []);

        if ($suppressed === []) {
            return null;
        }

        $blocked = array_map(function ($email) {
            $email = strtolower(trim((string) $email));

            return preg_replace('/\s+/', '', $email) ?: '';
        }, $suppressed);
        $blocked = array_values(array_filter($blocked));
        $message = $event->message;

        $this->substituirRemetenteBloqueado($message, $blocked);

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
            $email = strtolower($address->getAddress());
            $email = preg_replace('/\s+/', '', $email) ?: $email;

            return ! in_array($email, $blocked, true);
        }));
    }

    /**
     * @param  array<int, string>  $blocked
     */
    private function substituirRemetenteBloqueado(Email $message, array $blocked): void
    {
        $from = $message->getFrom();
        if ($from === []) {
            return;
        }

        $endereco = strtolower($from[0]->getAddress());
        $endereco = preg_replace('/\s+/', '', $endereco) ?: $endereco;
        if (! in_array($endereco, $blocked, true)) {
            return;
        }

        $nome = $from[0]->getName() ?: 'MyBPIN';
        $message->from(new Address(self::FALLBACK_FROM, $nome));
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
