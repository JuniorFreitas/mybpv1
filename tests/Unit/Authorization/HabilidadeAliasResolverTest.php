<?php

namespace Tests\Unit\Authorization;

use App\Authorization\HabilidadeAliasMap;
use App\Authorization\HabilidadeImplication;
use App\Authorization\HabilidadeRegistry;
use App\Authorization\HabilidadeResolver;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HabilidadeAliasResolverTest extends TestCase
{
    public function test_resolver_aceita_alias_quando_usuario_tem_canonico(): void
    {
        $registry = new HabilidadeRegistry();
        $registry->flush();
        $resolver = new HabilidadeResolver($registry);

        $user = new class extends User {
            /** @var list<string> */
            public array $habilidadesPermitidas = [];

            public function can($ability, $arguments = []): bool
            {
                return in_array($ability, $this->habilidadesPermitidas, true);
            }
        };
        $user->habilidadesPermitidas = ['admissao_pos_admissao'];

        $this->assertTrue($resolver->can($user, 'posadmissao'));
        $this->assertTrue($resolver->can($user, 'pos_admissao'));
        $this->assertTrue($resolver->can($user, 'admissao_pos_admissao'));
        $this->assertFalse($resolver->can($user, 'admissao_cih'));
    }

    public function test_alias_map_nao_tem_ciclos(): void
    {
        foreach (HabilidadeAliasMap::map() as $alias => $canonico) {
            $this->assertNotSame($alias, $canonico);
            $this->assertArrayNotHasKey($canonico, HabilidadeAliasMap::map());
        }
    }

    public function test_insert_implica_access(): void
    {
        $registry = new HabilidadeRegistry();
        $registry->flush();
        $resolver = new HabilidadeResolver($registry);

        $user = new class extends User {
            /** @var list<string> */
            public array $habilidadesPermitidas = [];

            public function can($ability, $arguments = []): bool
            {
                return in_array($ability, $this->habilidadesPermitidas, true);
            }

            public function listaDeHabilidades()
            {
                return $this->habilidadesPermitidas;
            }
        };
        $user->habilidadesPermitidas = ['cadastro_provas_insert'];

        $this->assertTrue($resolver->can($user, 'cadastro_provas'));
        $this->assertTrue($resolver->can($user, 'cadastro_provas_insert'));
        $this->assertFalse($resolver->can($user, 'cadastro_instrutor'));

        $expandidas = HabilidadeImplication::expandWithImpliedAccess(
            ['cadastro_provas_insert'],
            ['cadastro_provas' => true, 'cadastro_provas_insert' => true]
        );
        $this->assertContains('cadastro_provas', $expandidas);
        $this->assertContains('cadastro_provas_insert', $expandidas);
    }
}

class CanAnyHabilidadeMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'can.any:planejamento_movimentacao_exibir_aba_demissao,planejamento_movimentacao_exibir_aba_ferias'])
            ->get('/__test/can-any-mov', static fn () => response('ok', 200));
    }

    public function test_can_any_bloqueia_sem_habilidades(): void
    {
        Gate::define('planejamento_movimentacao_exibir_aba_ferias', static fn () => false);
        Gate::define('planejamento_movimentacao_exibir_aba_demissao', static fn () => false);

        $user = new User();
        $user->id = 1;
        $this->actingAs($user);

        $this->get('/__test/can-any-mov')->assertForbidden();
    }

    public function test_can_any_permite_com_uma_das_habilidades(): void
    {
        Gate::define('planejamento_movimentacao_exibir_aba_ferias', static fn () => true);
        Gate::define('planejamento_movimentacao_exibir_aba_demissao', static fn () => false);

        $user = new User();
        $user->id = 1;
        $this->actingAs($user);

        $this->get('/__test/can-any-mov')->assertOk();
    }
}
