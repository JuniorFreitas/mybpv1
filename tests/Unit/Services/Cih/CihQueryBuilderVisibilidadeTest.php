<?php

namespace Tests\Unit\Services\Cih;

use App\Models\User;
use App\Services\Cih\CihQueryBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CihQueryBuilderVisibilidadeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);

        Schema::dropIfExists('cihs');
        Schema::create('cihs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('gestor_id')->nullable();
            $table->unsignedInteger('user_lancamento_id')->nullable();
            $table->unsignedInteger('user_aprovacao_id')->nullable();
            $table->unsignedInteger('empresa_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_com_ver_todas_nao_aplica_filtro_vinculados(): void
    {
        $user = $this->usuarioComHabilidades(['admissao_cih_ver_todas'], 7);
        $this->actingAs($user);

        $sql = CihQueryBuilder::forListing($user)->toSql();

        $this->assertStringNotContainsString('gestor_id', $sql);
        $this->assertStringNotContainsString('user_lancamento_id', $sql);
        $this->assertStringNotContainsString('user_aprovacao_id', $sql);
    }

    public function test_com_privilegio_adm_nao_aplica_filtro_vinculados(): void
    {
        $user = $this->usuarioComHabilidades(['admissao_cih_privilegio_adm'], 7);
        $this->actingAs($user);

        $sql = CihQueryBuilder::forListing($user)->toSql();

        $this->assertStringNotContainsString('gestor_id', $sql);
        $this->assertStringNotContainsString('user_lancamento_id', $sql);
        $this->assertStringNotContainsString('user_aprovacao_id', $sql);
    }

    public function test_sem_ver_todas_aplica_filtro_vinculados(): void
    {
        $user = $this->usuarioComHabilidades([], 7);
        $this->actingAs($user);

        $sql = CihQueryBuilder::forListing($user)->toSql();

        $this->assertStringContainsString('gestor_id', $sql);
        $this->assertStringContainsString('user_lancamento_id', $sql);
        $this->assertStringContainsString('user_aprovacao_id', $sql);
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
        $user->empresa_id = 10;
        $user->grupo_id = 1;
        $user->habilidadesPermitidas = $habilidades;

        return $user;
    }
}
