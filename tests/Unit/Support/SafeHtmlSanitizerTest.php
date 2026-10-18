<?php

namespace Tests\Unit\Support;

use App\Support\SafeHtmlSanitizer;
use PHPUnit\Framework\TestCase;

class SafeHtmlSanitizerTest extends TestCase
{
    public function test_remove_event_handlers_e_javascript_href(): void
    {
        $html = '<p onmouseover="alert(1)">Oi</p><a href="javascript:alert(1)">x</a>';
        $clean = SafeHtmlSanitizer::sanitize($html);

        $this->assertStringNotContainsString('onmouseover', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('<p>', $clean);
    }

    public function test_preserva_strong_e_href_https(): void
    {
        $html = '<p>Prazo <strong>ok</strong></p><a href="https://example.com">link</a>';
        $clean = SafeHtmlSanitizer::sanitize($html);

        $this->assertStringContainsString('<strong>ok</strong>', $clean);
        $this->assertStringContainsString('href="https://example.com"', $clean);
    }
}
