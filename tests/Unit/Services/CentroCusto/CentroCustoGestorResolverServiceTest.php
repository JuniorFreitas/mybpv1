<?php

namespace Tests\Unit\Services\CentroCusto;

use App\Models\User;
use App\Services\CentroCusto\CentroCustoGestorResolverService;
use DomainException;
use Tests\TestCase;

class CentroCustoGestorResolverServiceTest extends TestCase
{
    public function test_usuario_ativo_retorna_false_para_inativo(): void
    {
        $service = new CentroCustoGestorResolverService();
        $user = new User(['id' => 1, 'ativo' => false]);

        $this->assertFalse($service->usuarioAtivo($user));
    }

    public function test_usuario_ativo_retorna_true_para_ativo(): void
    {
        $service = new CentroCustoGestorResolverService();
        $user = new User(['id' => 1, 'ativo' => true]);

        $this->assertTrue($service->usuarioAtivo($user));
    }

    public function test_resolver_aprovador_lanca_excecao_sem_gestor(): void
    {
        $this->expectException(DomainException::class);

        $service = $this->getMockBuilder(CentroCustoGestorResolverService::class)
            ->onlyMethods(['getGestorPrincipal', 'getGestorSubstituto'])
            ->getMock();

        $service->method('getGestorPrincipal')->willReturn(null);

        $service->resolverAprovador(1, 999);
    }

    public function test_resolver_aprovador_lanca_excecao_quando_autoaprovacao_sem_fallback(): void
    {
        $this->expectException(DomainException::class);

        $principal = new User(['ativo' => true]);
        $principal->id = 999;
        $principal->setRelation('GestorSuperior', null);

        $service = $this->getMockBuilder(CentroCustoGestorResolverService::class)
            ->onlyMethods(['getGestorPrincipal', 'getGestorSubstituto'])
            ->getMock();

        $service->method('getGestorPrincipal')->willReturn($principal);
        $service->method('getGestorSubstituto')->willReturn(null);

        $service->resolverAprovador(1, 999);
    }

    public function test_resolver_aprovador_retorna_substituto_quando_principal_e_o_solicitante(): void
    {
        $principal = new User(['ativo' => true]);
        $principal->id = 999;
        $principal->setRelation('GestorSuperior', null);

        $substituto = new User(['ativo' => true]);
        $substituto->id = 111;

        $service = $this->getMockBuilder(CentroCustoGestorResolverService::class)
            ->onlyMethods(['getGestorPrincipal', 'getGestorSubstituto'])
            ->getMock();

        $service->method('getGestorPrincipal')->willReturn($principal);
        $service->method('getGestorSubstituto')->willReturn($substituto);

        $aprovador = $service->resolverAprovador(1, 999);

        $this->assertNotNull($aprovador);
        $this->assertSame(111, $aprovador->id);
    }
}
