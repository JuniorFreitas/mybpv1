<?php

namespace Tests\Unit\Listeners;

use App\Listeners\RedirectLocalMailCcAndBcc;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class RedirectLocalMailCcAndBccTest extends TestCase
{
    public function test_should_apply_only_for_local_and_ses(): void
    {
        $this->assertTrue(RedirectLocalMailCcAndBcc::shouldApply('local', 'ses'));
        $this->assertTrue(RedirectLocalMailCcAndBcc::shouldApply('local', 'SES'));
        $this->assertFalse(RedirectLocalMailCcAndBcc::shouldApply('local', 'smtp'));
        $this->assertFalse(RedirectLocalMailCcAndBcc::shouldApply('production', 'ses'));
    }

    public function test_apply_replaces_cc_and_bcc_with_single_bcc(): void
    {
        $email = (new Email)
            ->from('from@example.com')
            ->to('to@example.com')
            ->cc('cc1@example.com', 'cc2@example.com')
            ->bcc('bcc1@example.com')
            ->text('corpo');

        RedirectLocalMailCcAndBcc::applyToEmail($email, 'juniorfreitas@dynamusti.com.br');

        $this->assertSame([], $email->getCc());
        $this->assertCount(1, $email->getBcc());
        $this->assertSame('juniorfreitas@dynamusti.com.br', $email->getBcc()[0]->getAddress());
    }

    public function test_apply_does_nothing_when_no_cc_or_bcc(): void
    {
        $email = (new Email)
            ->from('from@example.com')
            ->to('to@example.com')
            ->text('corpo');

        RedirectLocalMailCcAndBcc::applyToEmail($email, 'juniorfreitas@dynamusti.com.br');

        $this->assertSame([], $email->getCc());
        $this->assertSame([], $email->getBcc());
    }
}
