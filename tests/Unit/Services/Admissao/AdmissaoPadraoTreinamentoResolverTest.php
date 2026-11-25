<?php

namespace Tests\Unit\Services\Admissao;

use App\Services\Admissao\AdmissaoPadraoTreinamentoResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdmissaoPadraoTreinamentoResolverTest extends TestCase
{
    private AdmissaoPadraoTreinamentoResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new AdmissaoPadraoTreinamentoResolver();

        Schema::dropIfExists('padroes_treinamento');
        Schema::create('padroes_treinamento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
            $table->string('slug', 50);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        DB::table('padroes_treinamento')->insert([
            'id' => 1,
            'empresa_id' => 10,
            'nome' => 'Integração',
            'slug' => 'integracao',
            'ativo' => true,
        ]);
    }

    public function test_retorna_null_quando_padrao_id_e_null_ou_vazio(): void
    {
        $this->assertNull($this->resolver->resolver(null, 10));
        $this->assertNull($this->resolver->resolver('', 10));
        $this->assertNull($this->resolver->resolver(0, 10));
        $this->assertNull($this->resolver->resolver('0', 10));
    }

    public function test_retorna_null_quando_padrao_nao_existe_na_empresa(): void
    {
        $this->assertNull($this->resolver->resolver(999, 10));
    }

    public function test_retorna_null_quando_padrao_existe_mas_e_de_outra_empresa(): void
    {
        $this->assertNull($this->resolver->resolver(1, 20));
    }

    public function test_retorna_id_quando_padrao_existe_na_empresa_informada(): void
    {
        $this->assertSame(1, $this->resolver->resolver(1, 10));
        $this->assertSame(1, $this->resolver->resolver('1', 10), 'Deve aceitar id em string.');
    }
}
