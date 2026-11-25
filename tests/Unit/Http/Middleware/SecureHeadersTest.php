<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\SecureHeaders;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecureHeadersTest extends TestCase
{
    #[Test]
    public function adiciona_headers_basicos(): void
    {
        $middleware = new SecureHeaders();
        $request = Request::create('/g/login', 'GET');

        $response = $middleware->handle($request, function () {
            return response('ok');
        });

        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
    }
}
