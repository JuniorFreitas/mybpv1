<?php

namespace Tests\Unit\Services\Cloud;

use App\Models\User;
use App\Services\Cloud\CloudMultipartUploadService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CloudMultipartUploadServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['filesystems.disks.disco-cloud.driver' => 'local']);
        Storage::fake('disco-cloud');
        Storage::fake('local');
        Cache::flush();
        $this->criarSchemaMinimo();
    }

    public function testPartSizePadraoEhCincoMb(): void
    {
        $this->assertSame(5 * 1024 * 1024, CloudMultipartUploadService::PART_SIZE);
    }

    public function testIniciarLocalRetornaSessaoComModoLocal(): void
    {
        $user = $this->criarUsuario(10);
        $this->actingAs($user);

        $cloudId = DB::table('clouds')->insertGetId([
            'empresa_id' => 10,
            'nome' => 'C',
            'slug' => 'c',
            'ativo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_clouds')->insert(['user_id' => $user->id, 'cloud_id' => $cloudId]);

        $service = app(CloudMultipartUploadService::class);
        $resultado = $service->iniciar([
            'cloud_id' => $cloudId,
            'pertence_id' => null,
            'nome_original' => 'arquivo.dat',
            'mime' => 'application/octet-stream',
            'bytes' => 1024,
        ]);

        $this->assertSame('local', $resultado['modo']);
        $this->assertSame(CloudMultipartUploadService::PART_SIZE, $resultado['part_size']);
        $this->assertNotEmpty($resultado['upload_session_id']);
        $this->assertTrue(Cache::has('cloud_multipart:' . $resultado['upload_session_id']));
    }

    private function criarUsuario(int $empresaId): User
    {
        $id = DB::table('users')->insertGetId([
            'nome' => 'U',
            'login' => 'u' . $empresaId . '@t.com',
            'password' => 'x',
            'tipo' => 'admin',
            'ativo' => true,
            'temp' => false,
            'empresa_id' => $empresaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::withoutGlobalScopes()->findOrFail($id);
    }

    private function criarSchemaMinimo(): void
    {
        Schema::dropIfExists('user_clouds');
        Schema::dropIfExists('itens_cloud');
        Schema::dropIfExists('arquivos');
        Schema::dropIfExists('clouds');
        Schema::dropIfExists('users');
        Schema::dropIfExists('grupo_clouds');
        Schema::dropIfExists('activity_log');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('tipo')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('temp')->default(false);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('clouds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
            $table->string('slug')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
        Schema::create('user_clouds', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cloud_id');
        });
        Schema::create('itens_cloud', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cloud_id');
            $table->unsignedBigInteger('arquivo_id')->nullable();
            $table->string('label');
            $table->string('tipo');
            $table->unsignedBigInteger('pertence')->nullable();
            $table->unsignedBigInteger('quem_criou');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('extensao')->nullable();
            $table->string('file')->nullable();
            $table->string('disco')->nullable();
            $table->timestamps();
        });
        Schema::create('grupo_clouds', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
        });
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description')->nullable();
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->json('properties')->nullable();
            $table->string('event')->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }
}
