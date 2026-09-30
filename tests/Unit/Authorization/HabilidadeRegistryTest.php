<?php

namespace Tests\Unit\Authorization;

use App\Authorization\HabilidadeRegistry;
use App\Authorization\HabilidadeResolver;
use App\Models\User;
use Tests\TestCase;

class HabilidadeRegistryTest extends TestCase
{
    private HabilidadeRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new HabilidadeRegistry();
        $this->registry->flush();
    }

    public function test_catalog_piloto_contem_cih_e_configuracao(): void
    {
        $nomes = array_map(static fn ($d) => $d->nome, $this->registry->all());

        $this->assertContains('admissao_cih', $nomes);
        $this->assertContains('admissao_cih_ver_todas', $nomes);
        $this->assertContains('admissao_cih_privilegio_adm', $nomes);
        $this->assertContains('configuracao_papel', $nomes);
        $this->assertContains('privilegio_aprovar_por_gestor', $nomes);
        $this->assertContains('controle_ponto_folha-ponto', $nomes);
        $this->assertContains('planejamento_movimentacao_exibir_aba_ferias', $nomes);
        $this->assertGreaterThan(300, count($nomes));
    }

    public function test_find_retorna_metadados_cih(): void
    {
        $def = $this->registry->find('admissao_cih_aprovar');

        $this->assertNotNull($def);
        $this->assertSame('admissao', $def->modulo);
        $this->assertSame('cih', $def->recurso);
        $this->assertSame('aprovar', $def->acao);
    }

    public function test_by_modulo_filtra_configuracao(): void
    {
        $defs = $this->registry->byModulo('configuracao');

        $this->assertNotEmpty($defs);
        foreach ($defs as $def) {
            $this->assertSame('configuracao', $def->modulo);
        }
    }

    public function test_enrich_usa_catalog_quando_existe(): void
    {
        $meta = $this->registry->enrich('admissao_cih_ver_todas');

        $this->assertSame('admissao', $meta['modulo']);
        $this->assertSame('cih', $meta['recurso']);
        $this->assertSame('ver_todas', $meta['acao']);
        $this->assertSame('Ver todas', $meta['acao_label']);
    }

    public function test_enrich_heuristica_para_nome_desconhecido(): void
    {
        $meta = $this->registry->enrich('modulo_inexistente_xyz_insert', 'Teste');

        $this->assertSame('modulo', $meta['modulo']);
        $this->assertSame('inexistente_xyz', $meta['recurso']);
        $this->assertSame('insert', $meta['acao']);
        $this->assertSame('Teste', $meta['descricao']);
    }

    public function test_controle_ponto_usa_modulo_composto(): void
    {
        $def = $this->registry->find('controle_ponto_folha-ponto');

        $this->assertNotNull($def);
        $this->assertSame('controle_ponto', $def->modulo);
        $this->assertSame('Controle de Ponto', $def->moduloLabel);
    }

    public function test_movimentacao_aba_agrupa_em_recurso_movimentacao(): void
    {
        $def = $this->registry->find('planejamento_movimentacao_exibir_aba_demissao');

        $this->assertNotNull($def);
        $this->assertSame('planejamento', $def->modulo);
        $this->assertSame('movimentacao', $def->recurso);
        $this->assertSame('exibir_aba_demissao', $def->acao);
    }

    public function test_resolve_alias_sem_alias_devolve_mesmo_nome(): void
    {
        $this->assertSame('admissao_cih', $this->registry->resolveAlias('admissao_cih'));
    }

    public function test_alias_map_posadmissao_resolve_para_canonico(): void
    {
        $this->assertSame('admissao_pos_admissao', $this->registry->resolveAlias('posadmissao'));
        $this->assertSame('admissao_pos_admissao', $this->registry->resolveAlias('pos_admissao'));
        $this->assertSame('historico_dossie_insert', $this->registry->resolveAlias('admissao_historico_dossie_insert'));
        $this->assertSame('admissao_pos_form_rh', $this->registry->resolveAlias('posadmissao_form_rh'));
        $this->assertSame('admissao_pos_desmobilizar', $this->registry->resolveAlias('posadmissao_desmobilizar'));
    }

    public function test_posadmissao_skills_agrupam_no_modulo_admissao(): void
    {
        $def = $this->registry->find('admissao_pos_form_rh');

        $this->assertNotNull($def);
        $this->assertSame('admissao', $def->modulo);
        $this->assertSame('Admissão', $def->moduloLabel);
        $this->assertStringContainsString('pos_', $def->recurso);

        $viaAlias = $this->registry->find('posadmissao_form_rh');
        $this->assertNotNull($viaAlias);
        $this->assertSame('admissao_pos_form_rh', $viaAlias->nome);
    }
}

class HabilidadeResolverTest extends TestCase
{
    public function test_can_e_can_any_respeitam_habilidades_do_usuario(): void
    {
        $registry = new HabilidadeRegistry();
        $resolver = new HabilidadeResolver($registry);
        $user = $this->usuarioComHabilidades(['admissao_cih_ver_todas']);

        $this->assertTrue($resolver->can($user, 'admissao_cih_ver_todas'));
        $this->assertFalse($resolver->can($user, 'admissao_cih_privilegio_adm'));
        $this->assertTrue($resolver->canAny($user, [
            'admissao_cih_privilegio_adm',
            'admissao_cih_ver_todas',
        ]));
        $this->assertFalse($resolver->canAll($user, [
            'admissao_cih_ver_todas',
            'admissao_cih_privilegio_adm',
        ]));
    }

    public function test_habilidades_canonicas_deduplica(): void
    {
        $registry = new HabilidadeRegistry();
        $resolver = new HabilidadeResolver($registry);
        $user = $this->usuarioComHabilidades(['admissao_cih', 'admissao_cih_lancar']);

        $canonicas = $resolver->habilidadesCanonicas($user);

        $this->assertSame(['admissao_cih', 'admissao_cih_lancar'], $canonicas);
    }

    /**
     * @param list<string> $habilidades
     */
    private function usuarioComHabilidades(array $habilidades): User
    {
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
        $user->habilidadesPermitidas = $habilidades;

        return $user;
    }
}
