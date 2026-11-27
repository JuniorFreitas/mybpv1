<?php

namespace Tests\Unit\Support\Mail;

use App\Support\Mail\LocalMailRedirect;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocalMailRedirectTest extends TestCase
{
    #[Test]
    public function detecta_host_mailpit(): void
    {
        $hosts = ['host.docker.internal', '127.0.0.1', 'localhost', 'mailpit'];

        $this->assertTrue(LocalMailRedirect::isMailpitHost('host.docker.internal', $hosts));
        $this->assertTrue(LocalMailRedirect::isMailpitHost('HOST.DOCKER.INTERNAL', $hosts));
        $this->assertFalse(LocalMailRedirect::isMailpitHost('smtp.mailtrap.io', $hosts));
        $this->assertFalse(LocalMailRedirect::isMailpitHost('email-smtp.us-east-1.amazonaws.com', $hosts));
    }

    #[Test]
    public function always_to_nao_aplica_com_mailpit_em_local(): void
    {
        $this->assertFalse(LocalMailRedirect::shouldForceAlwaysTo('local', 'host.docker.internal'));
        $this->assertTrue(LocalMailRedirect::shouldForceAlwaysTo('local', 'email-smtp.us-east-1.amazonaws.com'));
        $this->assertFalse(LocalMailRedirect::shouldForceAlwaysTo('production', 'host.docker.internal'));
        $this->assertFalse(LocalMailRedirect::shouldForceAlwaysTo('production', 'email-smtp.us-east-1.amazonaws.com'));
    }
}
