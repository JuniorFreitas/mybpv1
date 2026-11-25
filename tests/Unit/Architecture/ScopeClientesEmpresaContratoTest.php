<?php

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Contratos estáticos SEC-008: o scope não pode voltar a confiar só no pivot
 * cliente_funcionarios sem intersectar ClientesEmpresa.
 */
class ScopeClientesEmpresaContratoTest extends TestCase
{
    #[Test]
    public function scope_canonico_exige_intersecao_com_clientes_empresa(): void
    {
        $path = base_path('app/Scopes/ScopeClientesEmpresa.php');
        $this->assertFileExists($path);

        $src = file_get_contents($path);

        $this->assertStringContainsString('ClientesEmpresa', $src);
        $this->assertMatchesRegularExpression('/contains\s*\(|idsPermitidos/', $src);
        $this->assertStringContainsString('0 = 1', $src);
    }

    #[Test]
    public function espelho_tenant_delega_ao_scope_canonico(): void
    {
        $path = base_path('app/Tenant/Scopes/ScopeClientesEmpresa.php');
        $this->assertFileExists($path);

        $src = file_get_contents($path);

        $this->assertStringContainsString('App\\Scopes\\ScopeClientesEmpresa', $src);
    }

    #[Test]
    public function get_usuario_nao_usa_without_global_scopes_no_pivot(): void
    {
        $path = base_path('app/Http/Controllers/UserController.php');
        $src = file_get_contents($path);

        preg_match('/function getUsuario\(\)\s*\{(.*?)\n    public function /s', $src, $m);
        $corpo = $m[1] ?? '';

        $this->assertNotSame('', $corpo, 'Não foi possível extrair getUsuario');
        $this->assertStringNotContainsString(
            'withoutGlobalScopes',
            $corpo,
            'getUsuario não deve bypassar scope Cliente para ler area_id do pivot'
        );
        $this->assertStringContainsString('?->Cliente?->area_id', $corpo);
    }
}
