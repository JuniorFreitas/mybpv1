<?php

namespace Tests\Unit\Services\Admissao;

use App\Models\Admissao;
use App\Models\UsuarioDependente;
use App\Services\Admissao\AdmissaoListSelectsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdmissaoListSelectsServiceTest extends TestCase
{
    private AdmissaoListSelectsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdmissaoListSelectsService();

        Schema::dropIfExists('padroes_treinamento');
        Schema::create('padroes_treinamento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
            $table->string('slug', 50);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function test_list_selects_retorna_todas_as_chaves_esperadas(): void
    {
        $dados = $this->service->listSelects();

        $chavesEsperadas = [
            'tipos_admissao',
            'status_admissao',
            'status_carteira_treinamento',
            'padroes_treinamento',
            'todos_prazos',
            'todos_status_treinamentos',
            'todos_status_documentos',
            'todos_status_documentos_portaria',
        ];

        foreach ($chavesEsperadas as $chave) {
            $this->assertArrayHasKey($chave, $dados);
        }

        $this->assertSame(Admissao::TODOS_TIPOS_ADMISSAO, $dados['tipos_admissao']);
        $this->assertSame(Admissao::TODOS_STATUS_ADMISSAO, $dados['status_admissao']);
        $this->assertSame(Admissao::TODOS_STATUS_CARTEIRA_TREINAMETO, $dados['status_carteira_treinamento']);
        $this->assertSame(Admissao::TODOS_PRAZOS, $dados['todos_prazos']);
        $this->assertSame(Admissao::TODOS_STATUS_TREINAMENTOS, $dados['todos_status_treinamentos']);
        $this->assertSame(Admissao::TODOS_STATUS_DOCUMENTOS, $dados['todos_status_documentos']);
        $this->assertSame(Admissao::TODOS_STATUS_DOCUMENTOS_PORTARIA, $dados['todos_status_documentos_portaria']);
    }

    public function test_tipos_dependentes_retorna_constante_do_model(): void
    {
        $this->assertSame(UsuarioDependente::TIPOS_DEPENDENTES, $this->service->tiposDependentes());
    }
}
