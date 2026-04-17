<?php

namespace Tests\Unit\Support;

use App\Support\AvaliacaoPlanoAcaoImpressaoHtmlSanitizer;
use PHPUnit\Framework\TestCase;

class AvaliacaoPlanoAcaoImpressaoHtmlSanitizerTest extends TestCase
{
    public function test_remove_script_and_keeps_bold(): void
    {
        $html = '<p>Texto <b onclick="evil()">ok</b><script>alert(1)</script></p>';
        $out = AvaliacaoPlanoAcaoImpressaoHtmlSanitizer::sanitize($html);

        $this->assertStringNotContainsString('<script', $out);
        $this->assertStringNotContainsString('onclick', $out);
        $this->assertStringContainsString('<b>', $out);
        $this->assertStringContainsString('ok', $out);
    }

    public function test_plain_text_becomes_paragraph_with_entities(): void
    {
        $out = AvaliacaoPlanoAcaoImpressaoHtmlSanitizer::sanitize("Linha1\nFoo & bar");

        $this->assertStringContainsString('<p>', $out);
        $this->assertStringContainsString('Linha1', $out);
        $this->assertStringContainsString('&amp;', $out);
    }

    public function test_span_allows_font_size_and_arial_only(): void
    {
        $html = '<p><span style="font-size: 18pt; color: red; font-family: Arial, sans-serif">X</span></p>';
        $out = AvaliacaoPlanoAcaoImpressaoHtmlSanitizer::sanitize($html);

        $this->assertStringContainsString('font-size: 18pt', $out);
        $this->assertStringContainsString('font-family:', $out);
        $this->assertStringContainsString('Arial', $out);
        $this->assertStringNotContainsString('color', $out);
    }

    public function test_rejects_non_whitelisted_font_size(): void
    {
        $html = '<p><span style="font-size: 99pt">X</span></p>';
        $out = AvaliacaoPlanoAcaoImpressaoHtmlSanitizer::sanitize($html);

        $this->assertStringNotContainsString('99pt', $out);
        $this->assertStringContainsString('<span>', $out);
    }

    public function test_rejects_non_arial_font_family(): void
    {
        $html = '<p><span style="font-family: Comic Sans MS">X</span></p>';
        $out = AvaliacaoPlanoAcaoImpressaoHtmlSanitizer::sanitize($html);

        $this->assertStringNotContainsString('Comic', $out);
    }
}
