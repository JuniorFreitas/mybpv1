<?php

namespace Tests\Unit\Rules;

use App\Rules\Recaptcha;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecaptchaFailClosedTest extends TestCase
{
    #[Test]
    public function falha_sem_secret_configurado(): void
    {
        config(['services.recaptcha.secret_key' => '']);

        $this->assertFalse((new Recaptcha())->passes('g-recaptcha-response', 'token'));
    }

    #[Test]
    public function falha_quando_google_retorna_success_false(): void
    {
        config(['services.recaptcha.secret_key' => 'test-secret']);

        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false], 200),
        ]);

        $this->assertFalse((new Recaptcha())->passes('g-recaptcha-response', 'token'));
    }

    #[Test]
    public function falha_em_erro_http(): void
    {
        config(['services.recaptcha.secret_key' => 'test-secret']);

        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response('erro', 500),
        ]);

        $this->assertFalse((new Recaptcha())->passes('g-recaptcha-response', 'token'));
    }

    #[Test]
    public function passa_quando_google_confirma_success(): void
    {
        config(['services.recaptcha.secret_key' => 'test-secret']);

        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);

        $this->assertTrue((new Recaptcha())->passes('g-recaptcha-response', 'token'));
    }
}
