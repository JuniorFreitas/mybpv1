<?php

namespace Tests\Unit\Views;

use Tests\TestCase;

class LogoMybpinMailTest extends TestCase
{
    public function test_ignora_arquivo_array_herdado_do_e_mail_pai(): void
    {
        $html = view('layouts.mail.logo-mybpin', [
            'arquivo' => [
                ['documento' => 'RG', 'nome' => 'rg.pdf', 'sha256' => 'abc'],
            ],
            'altura' => ['40px'],
        ])->render();

        $this->assertStringContainsString('alt="MyBPIN"', $html);
        $this->assertStringContainsString('height: 40px', $html);
    }

    public function test_usa_logo_informado_quando_o_nome_e_png_seguro(): void
    {
        $html = view('layouts.mail.logo-mybpin', [
            'logoAltura' => '35px',
            'logoArquivo' => 'mybpin-logo-fundo-claro.png',
            'arquivo' => [['nome' => 'rg.pdf']],
        ])->render();

        $this->assertStringContainsString('height: 35px', $html);
        $this->assertStringContainsString('alt="MyBPIN"', $html);
    }

    public function test_recusa_caminho_fora_da_pasta_de_imagens(): void
    {
        $html = view('layouts.mail.logo-mybpin', [
            'logoArquivo' => '../.env',
        ])->render();

        $this->assertStringNotContainsString('.env', $html);
        $this->assertStringContainsString('alt="MyBPIN"', $html);
    }
}
