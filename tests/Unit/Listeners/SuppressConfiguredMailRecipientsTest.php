<?php

namespace Tests\Unit\Listeners;

use App\Listeners\SuppressConfiguredMailRecipients;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class SuppressConfiguredMailRecipientsTest extends TestCase
{
    public function test_remove_suppressed_address_from_recipients(): void
    {
        config(['mail.suppress_recipients' => ['sistema@mybp.com.br']]);

        $email = (new Email)
            ->from('from@example.com')
            ->to('outro@example.com', 'sistema@mybp.com.br')
            ->text('corpo');

        $listener = new SuppressConfiguredMailRecipients;
        $result = $listener->handle(new MessageSending($email));

        $this->assertNull($result);
        $this->assertCount(1, $email->getTo());
        $this->assertSame('outro@example.com', $email->getTo()[0]->getAddress());
    }

    public function test_returns_false_when_only_suppressed_recipients_remain(): void
    {
        config(['mail.suppress_recipients' => ['sistema@mybp.com.br']]);

        $email = (new Email)
            ->from('from@example.com')
            ->to('sistema@mybp.com.br')
            ->text('corpo');

        $listener = new SuppressConfiguredMailRecipients;
        $result = $listener->handle(new MessageSending($email));

        $this->assertFalse($result);
    }

    public function test_suppression_is_case_insensitive(): void
    {
        config(['mail.suppress_recipients' => ['sistema@mybp.com.br']]);

        $email = (new Email)
            ->from('from@example.com')
            ->to('SISTEMA@MYBP.COM.BR')
            ->text('corpo');

        $listener = new SuppressConfiguredMailRecipients;
        $result = $listener->handle(new MessageSending($email));

        $this->assertFalse($result);
    }
}
