<?php

namespace Tests\Feature\Cloud;

use App\Models\Arquivo;
use App\Models\Cloud;
use App\Models\ItensCloud;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CloudMultipartUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['filesystems.disks.disco-cloud.driver' => 'local']);
        Storage::fake('disco-cloud');
        Storage::fake('local');
        Cache::flush();
        $this->criarSchema();
    }

    public function testIniciarAssinarCompletarCriaArquivoEItem(): void
    {
        [$user, $cloud, $pasta] = $this->cenarioBasico();
        $this->actingAs($user);

        $bytes = 12 * 1024;
        $iniciar = $this->postJson(route('g.itenscloud.multipart.iniciar'), [
            'cloud_id' => $cloud->id,
            'pertence_id' => $pasta->id,
            'nome_original' => 'video-grande.mp4',
            'mime' => 'video/mp4',
            'bytes' => $bytes,
        ]);

        $iniciar->assertStatus(201);
        $sessionId = $iniciar->json('upload_session_id');
        $this->assertNotEmpty($sessionId);
        $this->assertSame('local', $iniciar->json('modo'));

        $parte1 = UploadedFile::fake()->createWithContent('p1.bin', str_repeat('a', 5 * 1024));
        $parte2 = UploadedFile::fake()->createWithContent('p2.bin', str_repeat('b', 7 * 1024));

        $r1 = $this->post(route('g.itenscloud.multipart.parte'), [
            'upload_session_id' => $sessionId,
            'part_number' => 1,
            'parte' => $parte1,
        ]);
        $r1->assertOk();

        $r2 = $this->post(route('g.itenscloud.multipart.parte'), [
            'upload_session_id' => $sessionId,
            'part_number' => 2,
            'parte' => $parte2,
        ]);
        $r2->assertOk();

        $completar = $this->postJson(route('g.itenscloud.multipart.completar'), [
            'upload_session_id' => $sessionId,
            'partes' => [
                ['part_number' => 1, 'etag' => $r1->json('etag')],
                ['part_number' => 2, 'etag' => $r2->json('etag')],
            ],
        ]);

        $completar->assertStatus(201);
        $this->assertDatabaseHas('arquivos', [
            'nome' => 'video-grande',
            'extensao' => '.mp4',
            'disco' => Arquivo::DISCO_CLOUD,
            'bytes' => $bytes,
            'temporario' => 0,
        ]);
        $this->assertDatabaseHas('itens_cloud', [
            'cloud_id' => $cloud->id,
            'label' => 'video-grande',
            'tipo' => 'arquivo',
            'pertence' => $pasta->id,
        ]);

        $file = $completar->json('file');
        Storage::disk(Arquivo::DISCO_CLOUD)->assertExists($file);
    }

    public function testIniciarRejeitaDuplicata(): void
    {
        [$user, $cloud, $pasta] = $this->cenarioBasico();
        $this->actingAs($user);

        $arquivo = Arquivo::create([
            'quem_enviou' => $user->id,
            'nome' => 'relatorio',
            'imagem' => false,
            'extensao' => '.pdf',
            'file' => 'abc.pdf',
            'bytes' => 100,
            'temporario' => false,
            'chave' => '',
            'disco' => Arquivo::DISCO_CLOUD,
        ]);

        ItensCloud::create([
            'cloud_id' => $cloud->id,
            'arquivo_id' => $arquivo->id,
            'label' => 'relatorio',
            'tipo' => 'arquivo',
            'pertence' => $pasta->id,
            'quem_criou' => $user->id,
        ]);

        $response = $this->postJson(route('g.itenscloud.multipart.iniciar'), [
            'cloud_id' => $cloud->id,
            'pertence_id' => $pasta->id,
            'nome_original' => 'relatorio.pdf',
            'mime' => 'application/pdf',
            'bytes' => 2048,
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['msg' => 'Arquivo ja existe!']);
    }

    public function testIniciarRejeitaExecutavel(): void
    {
        [$user, $cloud, $pasta] = $this->cenarioBasico();
        $this->actingAs($user);

        $response = $this->postJson(route('g.itenscloud.multipart.iniciar'), [
            'cloud_id' => $cloud->id,
            'pertence_id' => $pasta->id,
            'nome_original' => 'malware.exe',
            'mime' => 'application/octet-stream',
            'bytes' => 2048,
        ]);

        $response->assertStatus(400);
        $this->assertStringContainsString('executáveis', (string) $response->json('msg'));
    }

    public function testAbortarLimpaSessao(): void
    {
        [$user, $cloud, $pasta] = $this->cenarioBasico();
        $this->actingAs($user);

        $iniciar = $this->postJson(route('g.itenscloud.multipart.iniciar'), [
            'cloud_id' => $cloud->id,
            'pertence_id' => $pasta->id,
            'nome_original' => 'abortar.bin',
            'mime' => 'application/octet-stream',
            'bytes' => 4096,
        ]);
        $iniciar->assertStatus(201);
        $sessionId = $iniciar->json('upload_session_id');

        $abortar = $this->postJson(route('g.itenscloud.multipart.abortar'), [
            'upload_session_id' => $sessionId,
        ]);
        $abortar->assertOk();

        $assinar = $this->postJson(route('g.itenscloud.multipart.assinar-parte'), [
            'upload_session_id' => $sessionId,
            'part_number' => 1,
        ]);
        $assinar->assertStatus(404);
    }

    public function testCloudSemAcessoRetorna403(): void
    {
        [$user, $cloud] = $this->cenarioBasico();
        $outro = $this->criarUsuario(10); // mesma empresa, sem vínculo no cloud
        $this->actingAs($outro);

        $response = $this->postJson(route('g.itenscloud.multipart.iniciar'), [
            'cloud_id' => $cloud->id,
            'pertence_id' => null,
            'nome_original' => 'x.pdf',
            'mime' => 'application/pdf',
            'bytes' => 1000,
        ]);

        $response->assertStatus(403);
    }

    /**
     * @return array{0: User, 1: Cloud, 2: ItensCloud}
     */
    private function cenarioBasico(): array
    {
        $user = $this->criarUsuario(10);
        $cloudId = DB::table('clouds')->insertGetId([
            'empresa_id' => 10,
            'nome' => 'Cloud Teste',
            'slug' => 'cloud-teste',
            'ativo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_clouds')->insert([
            'user_id' => $user->id,
            'cloud_id' => $cloudId,
        ]);

        $cloud = Cloud::withoutGlobalScopes()->findOrFail($cloudId);

        $pasta = ItensCloud::create([
            'cloud_id' => $cloud->id,
            'arquivo_id' => null,
            'label' => 'Pasta',
            'tipo' => 'pasta',
            'pertence' => null,
            'quem_criou' => $user->id,
        ]);

        return [$user, $cloud, $pasta];
    }

    private function criarUsuario(int $empresaId): User
    {
        $id = DB::table('users')->insertGetId([
            'nome' => 'User ' . $empresaId,
            'login' => 'user' . $empresaId . '@test.com',
            'password' => bcrypt('secret'),
            'tipo' => 'admin',
            'ativo' => true,
            'temp' => false,
            'empresa_id' => $empresaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::withoutGlobalScopes()->findOrFail($id);
    }

    private function criarSchema(): void
    {
        Schema::dropIfExists('permissoes_itens_clouds');
        Schema::dropIfExists('itens_cloud');
        Schema::dropIfExists('user_clouds');
        Schema::dropIfExists('clouds');
        Schema::dropIfExists('arquivos');
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('treinamento_vencimento');
        Schema::dropIfExists('users');
        Schema::dropIfExists('grupo_clouds');

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

        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quem_enviou')->nullable();
            $table->text('nome');
            $table->boolean('imagem')->default(false);
            $table->string('layout', 25)->nullable();
            $table->string('extensao', 10);
            $table->text('file');
            $table->string('thumb', 100)->nullable();
            $table->bigInteger('bytes')->default(0);
            $table->boolean('temporario')->default(false);
            $table->string('chave', 90)->nullable();
            $table->string('disco')->nullable();
            $table->timestamps();
        });

        Schema::create('itens_cloud', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cloud_id');
            $table->unsignedBigInteger('arquivo_id')->nullable();
            $table->string('label');
            $table->string('tipo');
            $table->unsignedBigInteger('pertence')->nullable();
            $table->unsignedBigInteger('quem_criou');
            $table->boolean('aprovado')->default(0);
            $table->unsignedBigInteger('quem_aprovou')->nullable();
            $table->dateTime('data_aprovacao')->nullable();
            $table->boolean('revisado')->default(0);
            $table->unsignedBigInteger('quem_revisou')->nullable();
            $table->dateTime('data_revisao')->nullable();
            $table->unsignedBigInteger('quem_editou')->nullable();
            $table->unsignedBigInteger('quem_excluiu')->nullable();
            $table->boolean('movido')->default(0);
            $table->unsignedBigInteger('quem_moveu')->nullable();
            $table->dateTime('data_movido')->nullable();
            $table->unsignedBigInteger('pertence_anterior')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('permissoes_itens_clouds', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('grupo_cloud_id');
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
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });

        Schema::create('treinamento_vencimento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('arquivo_id')->nullable();
        });
    }
}
