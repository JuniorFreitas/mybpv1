<?php

namespace Tests\Unit\Support;

use App\Support\DocumentoPreadmissaoDescricaoSanitizer;
use Tests\TestCase;

class DocumentoPreadmissaoDescricaoSanitizerTest extends TestCase
{
    public function test_mantem_link_http_e_abre_em_nova_aba(): void
    {
        $html = '<p>Veja <a href="https://exemplo.com/doc" target="_blank">o documento</a>.</p>';

        $resultado = DocumentoPreadmissaoDescricaoSanitizer::sanitize($html);

        $this->assertStringContainsString('href="https://exemplo.com/doc"', $resultado);
        $this->assertStringContainsString('target="_blank"', $resultado);
        $this->assertStringContainsString('rel="noopener noreferrer"', $resultado);
        $this->assertStringContainsString('o documento', $resultado);
    }

    public function test_forca_nova_janela_mesmo_sem_target(): void
    {
        $html = '<p><a href="https://exemplo.com/doc">o documento</a></p>';

        $resultado = DocumentoPreadmissaoDescricaoSanitizer::sanitize($html);

        $this->assertStringContainsString('target="_blank"', (string) $resultado);
        $this->assertStringContainsString('rel="noopener noreferrer"', (string) $resultado);
    }

    public function test_remove_link_javascript(): void
    {
        $html = '<p><a href="javascript:alert(1)">clique</a></p>';

        $resultado = DocumentoPreadmissaoDescricaoSanitizer::sanitize($html);

        $this->assertStringNotContainsString('javascript:', (string) $resultado);
        $this->assertStringNotContainsString('href=', (string) $resultado);
        $this->assertStringContainsString('clique', (string) $resultado);
    }
}
