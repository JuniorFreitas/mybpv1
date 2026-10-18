<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vaga;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicoListaVagasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
    }

    public function testAutenticadoSemEmpresaIdUsaEmpresaDaSessao(): void
    {
        $this->seedCliente(10, true);
        $this->seedCliente(20, true);

        Vaga::withoutGlobalScopes()->create([
            'nome' => 'Analista RH',
            'ativo' => true,
            'empresa_id' => 10,
        ]);
        Vaga::withoutGlobalScopes()->create([
            'nome' => 'Auxiliar Outra Empresa',
            'ativo' => true,
            'empresa_id' => 20,
        ]);

        $user = new User([
            'nome' => 'Usuário Teste',
            'tipo' => 'ADMINISTRADOR',
            'ativo' => true,
            'temp' => false,
            'empresa_id' => 10,
        ]);
        $this->actingAs($user);

        $response = $this->getJson('/publico/lista-vagas');

        $response->assertOk();
        $nomes = collect($response->json('vagas'))->pluck('nome')->all();
        $this->assertContains('Analista RH', $nomes);
        $this->assertNotContains('Auxiliar Outra Empresa', $nomes);
    }

    public function testSemAuthESemEmpresaIdRetorna400(): void
    {
        $this->getJson('/publico/lista-vagas')
            ->assertStatus(400)
            ->assertJsonFragment(['message' => 'empresa_id é obrigatório']);
    }

    public function testComEmpresaIdNaQueryFiltraEmpresaInformada(): void
    {
        $this->seedCliente(10, true);
        $this->seedCliente(20, true);

        Vaga::withoutGlobalScopes()->create([
            'nome' => 'Vaga Empresa 20',
            'ativo' => true,
            'empresa_id' => 20,
        ]);

        $this->getJson('/publico/lista-vagas?empresa_id=20')
            ->assertOk()
            ->assertJsonFragment(['nome' => 'Vaga Empresa 20']);
    }

    private function seedCliente(int $id, bool $ativo): void
    {
        DB::table('clientes')->insert([
            'id' => $id,
            'ativo' => $ativo,
            'razao_social' => "Empresa {$id}",
            'nome_fantasia' => "Empresa {$id}",
        ]);
    }

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('vagas');
        Schema::dropIfExists('clientes');

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->boolean('ativo')->default(true);
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
        });

        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('nome');
            $table->boolean('ativo');
            $table->unsignedBigInteger('empresa_id')->nullable();
        });
    }
}
