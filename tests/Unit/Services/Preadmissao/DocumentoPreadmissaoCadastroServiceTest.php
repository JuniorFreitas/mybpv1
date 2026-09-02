<?php

namespace Tests\Unit\Services\Preadmissao;

use App\Models\DocumentosCurriculosAdmissaoEmpresa;
use App\Models\DocumentosCurriculosCatAdmissaoEmpresa;
use App\Services\Preadmissao\DocumentoPreadmissaoCadastroService;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DocumentoPreadmissaoCadastroServiceTest extends TestCase
{
    private DocumentoPreadmissaoCadastroService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
        $this->service = new DocumentoPreadmissaoCadastroService();
    }

    public function test_listar_nao_expoe_documento_de_outra_empresa(): void
    {
        $categoria = $this->criarCategoria(['empresa_id' => 10]);
        $this->criarDocumento([
            'empresa_id' => 10,
            'categoria_id' => $categoria->id,
            'label' => 'MEU DOC',
            'tipo' => 'meu_doc',
        ]);
        $this->criarDocumento([
            'empresa_id' => 99,
            'categoria_id' => $this->criarCategoria(['empresa_id' => 99, 'label' => 'OUTRA'])->id,
            'label' => 'SECRETO',
            'tipo' => 'secreto',
        ]);

        $resultado = $this->service->listar(10, [], 20, 1);
        $labels = collect($resultado->items())->pluck('label');
        $tipos = collect($resultado->items())->pluck('tipo');

        $this->assertSame(2, $resultado->total());
        $this->assertTrue($labels->contains('MEU DOC'));
        $this->assertTrue($tipos->contains('foto3x4'));
        $this->assertFalse($labels->contains('SECRETO'));
        $foto = collect($resultado->items())->firstWhere('tipo', 'foto3x4');
        $this->assertTrue($foto->padrao_sistema);
    }

    public function test_listar_limita_quantidade_de_registros_por_pagina(): void
    {
        $resultado = $this->service->listar(10, [], 10000, 1);

        $this->assertSame(100, $resultado->perPage());
    }

    public function test_listar_filtra_por_busca_status_e_categoria(): void
    {
        $pessoais = $this->criarCategoria(['label' => 'PESSOAIS']);
        $filhos = $this->criarCategoria(['label' => 'FILHOS']);
        $this->criarDocumento([
            'categoria_id' => $pessoais->id,
            'label' => 'RG/CPF',
            'tipo' => 'anexoscpfrg',
            'ativo' => true,
        ]);
        $this->criarDocumento([
            'categoria_id' => $filhos->id,
            'label' => 'CERTIDAO',
            'tipo' => 'certidao',
            'ativo' => false,
        ]);

        $ativos = $this->service->listar(10, ['campoStatus' => 'true'], 20, 1);
        $this->assertSame(2, $ativos->total());
        $this->assertTrue(collect($ativos->items())->pluck('tipo')->contains('anexoscpfrg'));
        $this->assertTrue(collect($ativos->items())->pluck('tipo')->contains('foto3x4'));

        $busca = $this->service->listar(10, ['campoBusca' => 'certidao'], 20, 1);
        $this->assertSame(1, $busca->total());

        $porCategoria = $this->service->listar(10, ['campoCategoria' => $pessoais->id], 20, 1);
        $tipos = collect($porCategoria->items())->pluck('tipo');
        $this->assertTrue($tipos->contains('anexoscpfrg'));
        $this->assertTrue($tipos->contains('foto3x4'));
    }

    public function test_listar_filtra_por_nome_e_id(): void
    {
        $categoria = $this->criarCategoria();
        $doc = $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'COMPROVANTE DE RESIDÊNCIA',
            'tipo' => 'comprovante_residencia',
        ]);

        $porNome = $this->service->listar(10, ['campoBusca' => 'COMPROVANTE'], 20, 1);
        $this->assertSame(1, $porNome->total());
        $this->assertSame('comprovante_residencia', collect($porNome->items())->first()->tipo);

        $porId = $this->service->listar(10, ['campoBusca' => (string) $doc->id], 20, 1);
        $this->assertSame(1, $porId->total());
        $this->assertSame($doc->id, collect($porId->items())->first()->id);
    }

    public function test_criar_gera_tipo_snake_e_metodo_studly_e_ignora_payload(): void
    {
        $categoria = $this->criarCategoria();

        $doc = $this->service->criar([
            'label' => 'ATESTADO MÉDICO',
            'tipo' => 'hacked',
            'metodo' => 'Hacked',
            'categoria_id' => $categoria->id,
            'ordem' => 5,
            'ativo' => true,
            'configuracoes' => [
                'obrigatorio' => true,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false,
            ],
        ], 10);

        $this->assertSame(10, $doc->empresa_id);
        $this->assertSame('atestado_medico', $doc->tipo);
        $this->assertSame('AtestadoMedico', $doc->metodo);
        $this->assertSame($categoria->id, $doc->categoria_id);
        $this->assertTrue($doc->configuracoes['obrigatorio']);
        $this->assertTrue($doc->configuracoes['apenas_pdf_img']);
    }

    public function test_criar_recusa_tipo_duplicado_na_empresa(): void
    {
        $categoria = $this->criarCategoria();
        $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'ATESTADO MÉDICO',
            'tipo' => 'atestado_medico',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Já existe um documento com este nome.');
        $this->service->criar([
            'label' => 'Atestado Medico',
            'categoria_id' => $categoria->id,
            'ativo' => true,
        ], 10);
    }

    public function test_atualizar_nao_altera_tipo_nem_metodo(): void
    {
        $categoria = $this->criarCategoria();
        $doc = $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'RG/CPF',
            'tipo' => 'anexoscpfrg',
            'metodo' => 'AnexosCpfRg',
        ]);

        $atualizado = $this->service->atualizar($doc->id, [
            'label' => 'RG E CPF',
            'tipo' => 'alterado',
            'metodo' => 'Alterado',
            'categoria_id' => $categoria->id,
            'ativo' => true,
            'ordem' => 3,
        ], 10);

        $this->assertSame('anexoscpfrg', $atualizado->tipo);
        $this->assertSame('AnexosCpfRg', $atualizado->metodo);
        $this->assertSame('RG E CPF', $atualizado->label);
        $this->assertSame(3, $atualizado->ordem);
    }

    public function test_alternar_ativo_e_nao_acessa_outra_empresa(): void
    {
        $categoria = $this->criarCategoria();
        $doc = $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'ativo' => true,
        ]);

        $inativo = $this->service->alternarAtivo($doc->id, 10);
        $this->assertFalse($inativo->ativo);

        $outro = $this->criarDocumento([
            'empresa_id' => 99,
            'categoria_id' => $this->criarCategoria(['empresa_id' => 99, 'label' => 'X'])->id,
            'tipo' => 'outro',
            'label' => 'OUTRO',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Documento não encontrado.');
        $this->service->alternarAtivo($outro->id, 10);
    }

    public function test_criar_categoria_nova_reusa_label_existente(): void
    {
        $existente = $this->criarCategoria(['label' => 'DOCUMENTOS PESSOAIS']);

        $doc = $this->service->criar([
            'label' => 'COMPROVANTE DE RESIDÊNCIA',
            'categoria_nova' => 'documentos pessoais',
            'ativo' => true,
        ], 10);

        $this->assertSame($existente->id, $doc->categoria_id);
        $this->assertSame(1, DocumentosCurriculosCatAdmissaoEmpresa::query()->where('empresa_id', 10)->count());
    }

    public function test_criar_categoria_nova_quando_nao_existe(): void
    {
        $doc = $this->service->criar([
            'label' => 'EXAME ADMISSIONAL',
            'categoria_nova' => 'EXAMES',
            'ativo' => true,
        ], 10);

        $this->assertSame('exame_admissional', $doc->tipo);
        $this->assertSame('EXAMES', $doc->categoria->label);
        $this->assertSame(10, $doc->categoria->empresa_id);
    }

    public function test_recusa_cadastro_sem_categoria(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Informe ou cadastre uma categoria.');
        $this->service->criar([
            'label' => 'SEM CATEGORIA',
            'ativo' => true,
        ], 10);
    }

    public function test_recusa_flags_de_arquivo_conflitantes_e_max_menor_que_min(): void
    {
        $categoria = $this->criarCategoria();

        try {
            $this->service->criar([
                'label' => 'DOC A',
                'categoria_id' => $categoria->id,
                'ativo' => true,
                'configuracoes' => [
                    'apenas_img' => true,
                    'apenas_pdf' => true,
                ],
            ], 10);
            $this->fail('Deveria recusar flags conflitantes');
        } catch (DomainException $e) {
            $this->assertSame('Escolha apenas um tipo de arquivo aceito.', $e->getMessage());
        }

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('A quantidade máxima não pode ser menor que a mínima.');
        $this->service->criar([
            'label' => 'DOC B',
            'categoria_id' => $categoria->id,
            'ativo' => true,
            'configuracoes' => [
                'multiple' => true,
                'min' => 2,
                'max' => 1,
            ],
        ], 10);
    }

    public function test_sanitiza_html_perigoso_na_descricao_e_mantem_link_seguro(): void
    {
        $categoria = $this->criarCategoria();
        $doc = $this->service->criar([
            'label' => 'ANTECEDENTE',
            'categoria_id' => $categoria->id,
            'descricao' => 'SITE: <a href="https://exemplo.test" onclick="alert(1)">CLIQUE</a>'
                . '<a id="_preadm_root" href="javascript:alert(2)" onclick="alert(2)">PERIGO</a>'
                . '<script>alert(3)</script>',
            'ativo' => true,
        ], 10);

        $this->assertStringContainsString('<a href="https://exemplo.test">CLIQUE</a>', $doc->descricao);
        $this->assertStringNotContainsString('<script>', $doc->descricao);
        $this->assertStringNotContainsString('alert(3)', $doc->descricao);
        $this->assertStringNotContainsString('onclick', $doc->descricao);
        $this->assertStringNotContainsString('javascript:', $doc->descricao);
    }

    public function test_mutacoes_invalidam_cache_e_getter_nao_zera_na_leitura(): void
    {
        $categoria = $this->criarCategoria();
        $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'RG',
            'tipo' => 'rg',
            'ativo' => true,
        ]);

        $primeira = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(10);
        $this->assertCount(2, $primeira);
        $this->assertTrue($primeira->pluck('tipo')->contains('foto3x4'));
        $this->assertNotNull(Cache::get(DocumentosCurriculosAdmissaoEmpresa::cacheKey(10)));

        Cache::put(DocumentosCurriculosAdmissaoEmpresa::cacheKey(10), collect(['sentinel']), now()->addHour());

        $this->service->criar([
            'label' => 'NOVO DOC',
            'categoria_id' => $categoria->id,
            'ativo' => true,
        ], 10);

        $this->assertNull(Cache::get(DocumentosCurriculosAdmissaoEmpresa::cacheKey(10)));

        $depois = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(10);
        $this->assertCount(3, $depois);
        $this->assertNotNull(Cache::get(DocumentosCurriculosAdmissaoEmpresa::cacheKey(10)));

        $doc = $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'RG',
            'tipo' => 'rg_cache',
            'ativo' => true,
        ]);

        Cache::put(DocumentosCurriculosAdmissaoEmpresa::cacheKey(10), collect(['sentinel']), now()->addHour());

        $this->service->atualizar($doc->id, [
            'label' => 'RG ATUALIZADO',
            'categoria_id' => $categoria->id,
            'ativo' => true,
        ], 10);

        $this->assertNull(Cache::get(DocumentosCurriculosAdmissaoEmpresa::cacheKey(10)));

        Cache::put(DocumentosCurriculosAdmissaoEmpresa::cacheKey(10), collect(['sentinel']), now()->addHour());

        $this->service->alternarAtivo($doc->id, 10);

        $this->assertNull(Cache::get(DocumentosCurriculosAdmissaoEmpresa::cacheKey(10)));

        $segundaLeitura = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(10);
        $this->assertCount(3, $segundaLeitura);
    }

    public function test_getter_omite_inativos_e_aceita_categoria_nula(): void
    {
        $categoria = $this->criarCategoria();
        $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'ATIVO',
            'tipo' => 'ativo',
            'ativo' => true,
        ]);
        $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'INATIVO',
            'tipo' => 'inativo',
            'ativo' => false,
        ]);
        $this->criarDocumento([
            'categoria_id' => null,
            'label' => 'SEM CAT',
            'tipo' => 'sem_cat',
            'ativo' => true,
        ]);

        $lista = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(10);
        $labels = $lista->pluck('label');
        $tipos = $lista->pluck('tipo');

        $this->assertTrue($labels->contains('ATIVO'));
        $this->assertTrue($labels->contains('SEM CAT'));
        $this->assertTrue($tipos->contains('foto3x4'));
        $this->assertFalse($labels->contains('INATIVO'));
        $semCat = $lista->firstWhere('tipo', 'sem_cat');
        $this->assertNull($semCat->categoria);
    }

    public function test_getter_sanitiza_descricao_legada_antes_de_exibir(): void
    {
        $categoria = $this->criarCategoria();
        $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'LEGADO',
            'tipo' => 'legado',
            'descricao' => '<a href="javascript:alert(1)" onclick="alert(2)">LINK</a><script>alert(3)</script>',
        ]);

        $documento = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(10)
            ->firstWhere('tipo', 'legado');

        $this->assertNotNull($documento);
        $this->assertStringContainsString('<a>LINK</a>', $documento->descricao);
        $this->assertStringNotContainsString('javascript:', $documento->descricao);
        $this->assertStringNotContainsString('onclick', $documento->descricao);
        $this->assertStringNotContainsString('alert(3)', $documento->descricao);
    }

    public function test_getter_normaliza_configuracoes_legadas_nulas(): void
    {
        $categoria = $this->criarCategoria();
        $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'SEM CONFIGURAÇÃO',
            'tipo' => 'sem_configuracao',
            'configuracoes' => null,
        ]);

        $documento = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(10)
            ->firstWhere('tipo', 'sem_configuracao');

        $this->assertNotNull($documento);
        $this->assertSame([
            'obrigatorio' => false,
            'apenas_img' => false,
            'apenas_pdf' => false,
            'apenas_pdf_img' => false,
            'multiple' => false,
            'min' => 1,
            'max' => 1,
            'sogestao' => false,
        ], $documento->configuracoes);
    }

    public function test_sogestao_permanece_no_cadastro_e_no_getter(): void
    {
        $categoria = $this->criarCategoria();
        $doc = $this->service->criar([
            'label' => 'DOC INTERNO',
            'categoria_id' => $categoria->id,
            'ativo' => true,
            'configuracoes' => [
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => false,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => true,
            ],
        ], 10);

        $listagem = $this->service->listar(10, ['campoBusca' => 'DOC INTERNO'], 20, 1);
        $item = collect($listagem->items())->firstWhere('tipo', $doc->tipo);
        $this->assertNotNull($item);
        $this->assertTrue($item->configuracoes['sogestao']);

        $catalogo = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(10);
        $noGetter = $catalogo->firstWhere('tipo', $doc->tipo);
        $this->assertNotNull($noGetter);
        $this->assertTrue($noGetter->configuracoes['sogestao']);
    }

    public function test_foto3x4_nao_pode_ser_alterado_nem_desativado(): void
    {
        $this->service->listar(10, [], 20, 1);
        $foto = DocumentosCurriculosAdmissaoEmpresa::query()
            ->where('empresa_id', 10)
            ->where('tipo', 'foto3x4')
            ->first();

        $this->assertNotNull($foto);

        try {
            $this->service->atualizar($foto->id, [
                'label' => 'FOTO ALTERADA',
                'categoria_id' => $foto->categoria_id,
                'ativo' => true,
            ], 10);
            $this->fail('Deveria recusar alteração da foto 3x4');
        } catch (DomainException $e) {
            $this->assertSame('A foto 3x4 é um documento padrão do sistema e não pode ser alterada.', $e->getMessage());
        }

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('A foto 3x4 é um documento padrão do sistema e não pode ser alterada.');
        $this->service->alternarAtivo($foto->id, 10);
    }

    public function test_foto3x4_inativa_e_reativada_e_cadastro_manual_e_recusado(): void
    {
        $categoria = $this->criarCategoria();
        $foto = $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'FOTO 3X4',
            'tipo' => 'foto3x4',
            'metodo' => 'FotoTres',
            'ativo' => false,
        ]);

        $this->service->garantirPadraoSistema(10);
        $this->assertTrue($foto->fresh()->ativo);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('A foto 3x4 é um documento padrão do sistema e não pode ser cadastrada manualmente.');
        $this->service->criar([
            'label' => 'FOTO 3X4',
            'categoria_id' => $categoria->id,
            'ativo' => true,
        ], 99);
    }

    public function test_variante_legada_da_foto3x4_nao_gera_documento_duplicado(): void
    {
        $categoria = $this->criarCategoria();
        $variante = $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'FOTO 3X4 LEGADA',
            'tipo' => 'foto_3x4',
            'ativo' => false,
        ]);

        $this->service->garantirPadraoSistema(10);

        $this->assertTrue($variante->fresh()->ativo);
        $this->assertSame(1, DocumentosCurriculosAdmissaoEmpresa::query()
            ->where('empresa_id', 10)
            ->whereIn('tipo', ['foto3x4', 'foto_3x4'])
            ->count());
    }

    public function test_banco_impede_tipo_duplicado_na_mesma_empresa(): void
    {
        $categoria = $this->criarCategoria();
        $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'tipo' => 'documento_unico',
        ]);

        $this->expectException(QueryException::class);
        $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'OUTRO DOCUMENTO',
            'tipo' => 'documento_unico',
        ]);
    }

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('documentos_curriculos_adm_empresa');
        Schema::dropIfExists('documentos_curriculos_cat_adm_empresa');

        Schema::create('documentos_curriculos_cat_adm_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('label');
            $table->boolean('ativo')->default(true);
        });

        Schema::create('documentos_curriculos_adm_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('label');
            $table->string('metodo')->nullable();
            $table->text('descricao')->nullable();
            $table->string('tipo');
            $table->string('url_arquivo')->nullable();
            $table->json('configuracoes')->nullable();
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->unique(['empresa_id', 'tipo'], 'doc_adm_empresa_tipo_unique');
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function criarCategoria(array $dados = []): DocumentosCurriculosCatAdmissaoEmpresa
    {
        return DocumentosCurriculosCatAdmissaoEmpresa::create(array_merge([
            'empresa_id' => 10,
            'label' => 'DOCUMENTOS PESSOAIS',
            'ativo' => true,
        ], $dados));
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function criarDocumento(array $dados = []): DocumentosCurriculosAdmissaoEmpresa
    {
        return DocumentosCurriculosAdmissaoEmpresa::create(array_merge([
            'empresa_id' => 10,
            'label' => 'DOC',
            'metodo' => 'Doc',
            'tipo' => 'doc',
            'ordem' => 1,
            'ativo' => true,
            'configuracoes' => [
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => false,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false,
            ],
        ], $dados));
    }
}
