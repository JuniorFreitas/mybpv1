<?php

namespace Tests\Unit\Policies;

use App\Authorization\HabilidadeResolver;
use App\Models\User;
use App\Policies\HabilidadePolicy;
use Tests\TestCase;

class HabilidadePolicyTest extends TestCase
{
    public function test_access_respeita_resolver(): void
    {
        $user = new class extends User {
            /** @var list<string> */
            public array $habilidadesPermitidas = ['admissao_cih'];

            public function can($ability, $arguments = []): bool
            {
                return in_array($ability, $this->habilidadesPermitidas, true);
            }

            public function listaDeHabilidades()
            {
                return $this->habilidadesPermitidas;
            }
        };

        $policy = new HabilidadePolicy(app(HabilidadeResolver::class));

        $this->assertTrue($policy->access($user, 'admissao_cih'));
        $this->assertFalse($policy->access($user, 'cloud_cadastro'));
    }
}
