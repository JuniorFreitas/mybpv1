<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SuppressConfiguredMailRecipients
{
    public function handle(MessageSending $event): ?bool
    {
        $suppressed = config('mail.suppress_recipients', []);

        if ($suppressed === []) {
            return null;
        }

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
            return ! in_array(strtolower($address->getAddress()), $blocked, true);
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
