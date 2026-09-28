<?php

namespace Tests\Unit\Services\Cih;

use App\Models\Cih;
use App\Models\User;
use App\Services\Cih\CihAcessoService;
use Tests\TestCase;


class CihAcessoServiceTest extends TestCase
{
    private CihAcessoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CihAcessoService();
    }

    public function test_pode_ver_todas_com_privilegio_adm(): void
    {
        $user = $this->usuarioComHabilidades(['admissao_cih_privilegio_adm']);

        $this->assertTrue($this->service->podeVerTodas($user));
    }

    public function test_pode_ver_todas_com_ver_todas(): void
    {
        $user = $this->usuarioComHabilidades(['admissao_cih_ver_todas']);

        $this->assertTrue($this->service->podeVerTodas($user));
    }

    public function test_nao_pode_ver_todas_sem_habilidades(): void
    {
        $user = $this->usuarioComHabilidades([]);

        $this->assertFalse($this->service->podeVerTodas($user));
    }

    public function test_gestor_pode_aprovar_cih_sob_sua_responsabilidade(): void
    {
        $user = $this->usuarioComHabilidades(['admissao_cih_aprovar'], 10);
        $cih = new Cih(['gestor_id' => 10]);

        $this->assertTrue($this->service->podeAprovarComoGestor($user, $cih));
    }

    public function test_ver_todas_nao_libera_aprovar_cih_de_outro_gestor(): void
    {
        $user = $this->usuarioComHabilidades([
            'admissao_cih_ver_todas',
            'admissao_cih_aprovar',
            'privilegio_aprovar_por_gestor',
        ], 10);
        $cih = new Cih(['gestor_id' => 99]);

        $this->assertFalse($this->service->podeAprovarComoGestor($user, $cih));
    }

    public function test_privilegio_adm_libera_aprovar_cih_de_outro_gestor(): void
    {
        $user = $this->usuarioComHabilidades([
            'admissao_cih_privilegio_adm',
            'admissao_cih_aprovar',
        ], 10);
        $cih = new Cih(['gestor_id' => 99]);

        $this->assertTrue($this->service->podeAprovarComoGestor($user, $cih));
    }

    public function test_papel_montisol_usa_escopo_centros_custo_matriz(): void
    {
        $user = $this->usuarioComHabilidades([], 10);
        $user->grupo_id = CihAcessoService::PAPEL_MONTISOL_CIH_MATRIZ;

        $this->assertTrue($this->service->usaEscopoCentrosCustoMatrizMontisol($user));
    }

    public function test_papel_comum_nao_usa_escopo_montisol(): void
    {
        $user = $this->usuarioComHabilidades([], 10);
        $user->grupo_id = 1;

        $this->assertFalse($this->service->usaEscopoCentrosCustoMatrizMontisol($user));
    }

    /**
     * @param list<string> $habilidades
     */
    private function usuarioComHabilidades(array $habilidades, int $id = 1): User
    {
        $user = new class extends User {
            /** @var list<string> */
            public array $habilidadesPermitidas = [];

            public function can($ability, $arguments = []): bool
            {
                return in_array($ability, $this->habilidadesPermitidas, true);
            }
        };
        $user->id = $id;
        $user->habilidadesPermitidas = $habilidades;

        return $user;
    }
}
