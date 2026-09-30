<?php

namespace Tests\Unit\Services\Cloud;

use App\Authorization\Cloud\CloudCapabilityCatalog;
use App\Models\GrupoCloud;
use App\Models\ItensCloud;
use App\Models\User;
use App\Services\Cloud\CloudAuthorizationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CloudAuthorizationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->criarSchemaMinimo();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('permissoes_itens_clouds');
        Schema::dropIfExists('itens_cloud');
        Schema::dropIfExists('user_grupo_cloud');
        Schema::dropIfExists('grupo_habilidade_cloud');
        Schema::dropIfExists('habilidade_clouds');
        Schema::dropIfExists('grupo_clouds');
        parent::tearDown();
    }

    public function test_user_can_cloud_action_false_sem_capacidade(): void
    {
        $user = $this->usuarioComGrupos([], []);

        $service = new CloudAuthorizationService();

        $this->assertFalse($service->userCanCloudAction($user, CloudCapabilityCatalog::APROVAR));
    }

    public function test_user_can_cloud_action_true_com_capacidade(): void
    {
        $aprovId = $this->criarHabilidadeCloud(CloudCapabilityCatalog::APROVAR);
        $grupoId = $this->criarGrupo('Principal');
        $this->vincularHabilidade($grupoId, $aprovId);
        $user = $this->usuarioComGrupos([$grupoId], []);

        $service = new CloudAuthorizationService();

        $this->assertTrue($service->userCanCloudAction($user, CloudCapabilityCatalog::APROVAR));
        $this->assertFalse($service->userCanCloudAction($user, CloudCapabilityCatalog::DELETAR));
    }

    public function test_capacidades_uniao_de_multiplos_grupos(): void
    {
        $aprovar = $this->criarHabilidadeCloud(CloudCapabilityCatalog::APROVAR);
        $deletar = $this->criarHabilidadeCloud(CloudCapabilityCatalog::DELETAR);
        $g1 = $this->criarGrupo('G1');
        $g2 = $this->criarGrupo('G2');
        $this->vincularHabilidade($g1, $aprovar);
        $this->vincularHabilidade($g2, $deletar);

        $user = $this->usuarioComGrupos([$g1], [$g2]);
        $service = new CloudAuthorizationService();

        $this->assertTrue($service->userCanCloudAction($user, CloudCapabilityCatalog::APROVAR));
        $this->assertTrue($service->userCanCloudAction($user, CloudCapabilityCatalog::DELETAR));
        $this->assertContains($g1, $service->userGrupoCloudIds($user));
        $this->assertContains($g2, $service->userGrupoCloudIds($user));
    }

    public function test_user_can_access_item_via_grupo_pivot(): void
    {
        $gPrincipal = $this->criarGrupo('Principal');
        $gExtra = $this->criarGrupo('Extra');
        $user = $this->usuarioComGrupos([$gPrincipal], [$gExtra]);

        $itemId = DB::table('itens_cloud')->insertGetId([
            'label' => 'pasta',
            'tipo' => 'pasta',
            'cloud_id' => 1,
        ]);
        DB::table('permissoes_itens_clouds')->insert([
            'item_id' => $itemId,
            'grupo_cloud_id' => $gExtra,
        ]);

        $item = ItensCloud::query()->find($itemId);
        $service = new CloudAuthorizationService();

        $this->assertTrue($service->userCanAccessItem($user, $item));
    }

    public function test_authorize_cloud_action_abort_403(): void
    {
        $user = $this->usuarioComGrupos([], []);
        $this->actingAs($user);

        $service = new CloudAuthorizationService();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Sem permissão Cloud para esta ação.');
        $service->authorizeCloudAction($user, CloudCapabilityCatalog::APROVAR);
    }

    private function criarSchemaMinimo(): void
    {
        Schema::create('grupo_clouds', function ($table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('empresa_id')->nullable();
        });

        Schema::create('habilidade_clouds', function ($table) {
            $table->id();
            $table->string('nome');
        });

        Schema::create('grupo_habilidade_cloud', function ($table) {
            $table->unsignedBigInteger('grupo_cloud_id');
            $table->unsignedBigInteger('habilidade_cloud_id');
        });

        Schema::create('user_grupo_cloud', function ($table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('grupo_cloud_id');
        });

        Schema::create('itens_cloud', function ($table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('cloud_id')->nullable();
            $table->softDeletes();
        });

        Schema::create('permissoes_itens_clouds', function ($table) {
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('grupo_cloud_id');
        });
    }

    /**
     * @param list<int> $principais  IDs usados em grupo_cloud_id (primeiro = principal)
     * @param list<int> $extras      IDs só no pivot user_grupo_cloud
     */
    private function usuarioComGrupos(array $principais, array $extras): User
    {
        $principal = $principais[0] ?? null;
        $user = new User();
        $user->id = 77;
        $user->grupo_cloud_id = $principal;

        if ($principal) {
            $user->setRelation('GrupoCloud', GrupoCloud::query()->find($principal));
            DB::table('user_grupo_cloud')->insertOrIgnore([
                'user_id' => $user->id,
                'grupo_cloud_id' => $principal,
            ]);
        }

        foreach ($extras as $gid) {
            DB::table('user_grupo_cloud')->insert([
                'user_id' => $user->id,
                'grupo_cloud_id' => $gid,
            ]);
        }

        // Garante que idsGruposCloud enxerga o user id nas queries pivot
        $user->exists = true;

        return $user;
    }

    private function criarGrupo(string $nome): int
    {
        return (int) DB::table('grupo_clouds')->insertGetId([
            'nome' => $nome,
            'descricao' => 'test',
            'ativo' => true,
            'empresa_id' => 1,
        ]);
    }

    private function criarHabilidadeCloud(string $nome): int
    {
        return (int) DB::table('habilidade_clouds')->insertGetId(['nome' => $nome]);
    }

    private function vincularHabilidade(int $grupoId, int $habilidadeId): void
    {
        DB::table('grupo_habilidade_cloud')->insert([
            'grupo_cloud_id' => $grupoId,
            'habilidade_cloud_id' => $habilidadeId,
        ]);
    }
}
