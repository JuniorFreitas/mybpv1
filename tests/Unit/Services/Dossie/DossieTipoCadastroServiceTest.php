<?php

namespace Tests\Unit\Services\Dossie;

use App\Models\DossieTipo;
use App\Services\Dossie\DossieTipoCadastroService;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DossieTipoCadastroServiceTest extends TestCase
{
    private DossieTipoCadastroService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirTabela();
        $this->service = new DossieTipoCadastroService();
    }

    public function test_catalogo_mescla_global_com_override_da_empresa(): void
    {
        $global = $this->criarTipo(['tipo' => 'DocSelecao', 'chave' => 'doc_selecao', 'label' => 'GLOBAL', 'empresa_id' => null]);
        $this->criarTipo([
            'tipo' => 'DocSelecao',
            'chave' => 'doc_selecao',
            'label' => 'EMPRESA',
            'empresa_id' => 10,
            'ativo' => false,
        ]);
        $this->criarTipo(['tipo' => 'OutroDoc', 'chave' => 'outro_doc', 'label' => 'OUTRO', 'empresa_id' => null]);

        $catalogo = $this->service->catalogoEfetivo(10)->keyBy('tipo');

        $this->assertSame('EMPRESA', $catalogo['DocSelecao']['label']);
        $this->assertFalse($catalogo['DocSelecao']['ativo']);
        $this->assertSame('empresa', $catalogo['DocSelecao']['escopo']);
        $this->assertSame('OUTRO', $catalogo['OutroDoc']['label']);
        $this->assertSame('global', $catalogo['OutroDoc']['escopo']);
        $this->assertNotSame($global->id, $catalogo['DocSelecao']['id']);
    }

    public function test_criar_gera_identificadores_pelo_nome_e_recusa_duplicado(): void
    {
        $this->criarTipo(['tipo' => 'AtestadoMedico', 'chave' => 'atestado_medico', 'label' => 'ATESTADO MÉDICO', 'empresa_id' => null]);

        $novo = $this->service->criar([
            'label' => 'Termo de Integração',
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ordem' => 99,
            'ativo' => true,
        ], 10);

        $this->assertSame(10, $novo->empresa_id);
        $this->assertSame('TermoDeIntegracao', $novo->tipo);
        $this->assertSame('termo_de_integracao', $novo->chave);
        $this->assertSame('termodeintegracao', $novo->tipo_modelo);
        $this->assertTrue($novo->ativo);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Já existe um tipo de dossiê com este nome.');
        $this->service->criar([
            'label' => 'Atestado Medico',
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ativo' => true,
        ], 10);
    }

    public function test_criar_ignora_tipo_e_chave_enviados_pelo_cliente(): void
    {
        $tipo = $this->service->criar([
            'label' => 'ATESTADO MÉDICO',
            'tipo' => 'Hacked',
            'chave' => 'hacked',
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ativo' => true,
        ], 10);

        $this->assertSame('AtestadoMedico', $tipo->tipo);
        $this->assertSame('atestado_medico', $tipo->chave);
    }

    public function test_atualizar_tipo_global_cria_override_sem_alterar_original(): void
    {
        $global = $this->criarTipo([
            'tipo' => 'DocSelecao',
            'chave' => 'doc_selecao',
            'label' => 'DOCUMENTO DE SELEÇÃO',
            'empresa_id' => null,
            'ativo' => true,
            'ordem' => 1,
        ]);

        $override = $this->service->atualizar($global->id, [
            'label' => 'DOC SELEÇÃO EMPRESA',
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ordem' => 5,
            'ativo' => true,
        ], 10);

        $this->assertNotSame($global->id, $override->id);
        $this->assertSame(10, $override->empresa_id);
        $this->assertSame('DOC SELEÇÃO EMPRESA', $override->label);
        $this->assertSame('DocSelecao', $override->tipo);

        $global->refresh();
        $this->assertSame('DOCUMENTO DE SELEÇÃO', $global->label);
        $this->assertNull($global->empresa_id);
    }

    public function test_alternar_ativo_de_global_cria_override_inativo(): void
    {
        $global = $this->criarTipo([
            'tipo' => 'DocSelecao',
            'chave' => 'doc_selecao',
            'label' => 'DOCUMENTO DE SELEÇÃO',
            'empresa_id' => null,
            'ativo' => true,
        ]);

        $override = $this->service->alternarAtivo($global->id, 10);

        $this->assertSame(10, $override->empresa_id);
        $this->assertFalse($override->ativo);
        $this->assertTrue($global->fresh()->ativo);
    }

    public function test_nao_acessa_tipo_de_outra_empresa(): void
    {
        $outro = $this->criarTipo([
            'tipo' => 'DocOutra',
            'chave' => 'doc_outra',
            'label' => 'OUTRA',
            'empresa_id' => 99,
        ]);

        $this->expectException(DomainException::class);
        $this->service->buscarParaEdicao($outro->id, 10);
    }

    public function test_assinatura_sem_config_da_empresa_grava_false(): void
    {
        $tipo = $this->service->criar([
            'tipo' => 'TermoNovo',
            'chave' => 'termo_novo',
            'label' => 'TERMO NOVO',
            'permite_assinatura' => true,
            'ativo' => true,
        ], 10);

        $this->assertFalse($tipo->permite_assinatura);
    }

    public function test_assinatura_sem_modelo_e_recusada_quando_flag_ativa(): void
    {
        $tipo = $this->criarTipo([
            'tipo' => 'TermoSemModelo',
            'chave' => 'termo_sem_modelo',
            'label' => 'TERMO SEM MODELO',
            'empresa_id' => 10,
            'permite_assinatura' => true,
            'tem_modelo' => false,
            'tipo_modelo' => 'termosemmmodelo',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Para enviar à assinatura digital, cadastre um PDF de modelo.');
        $this->service->assertAssinaturaTemModelo($tipo);
    }

    public function test_listar_filtra_por_busca_status_e_escopo(): void
    {
        $this->criarTipo(['tipo' => 'DocSelecao', 'chave' => 'doc_selecao', 'label' => 'DOCUMENTO DE SELEÇÃO', 'empresa_id' => null, 'ativo' => true]);
        $this->criarTipo(['tipo' => 'AtestadoMedico', 'chave' => 'atestado_medico', 'label' => 'ATESTADO', 'empresa_id' => 10, 'ativo' => false]);

        $ativos = $this->service->listar(10, ['campoStatus' => 'true'], 20, 1);
        $this->assertSame(1, $ativos->total());
        $this->assertSame('DOCUMENTO DE SELEÇÃO', $ativos->items()[0]['label']);

        $busca = $this->service->listar(10, ['campoBusca' => 'atestado'], 20, 1);
        $this->assertSame(1, $busca->total());

        $empresa = $this->service->listar(10, ['campoEscopo' => 'empresa'], 20, 1);
        $this->assertSame(1, $empresa->total());
        $this->assertSame('empresa', $empresa->items()[0]['escopo']);
    }

    private function garantirTabela(): void
    {
        if (Schema::hasTable('dossie_tipos')) {
            if (!Schema::hasColumn('dossie_tipos', 'modelo_arquivo_id')) {
                Schema::table('dossie_tipos', function (Blueprint $table) {
                    $table->unsignedBigInteger('modelo_arquivo_id')->nullable();
                });
            }
            DossieTipo::query()->delete();

            return;
        }

        Schema::create('dossie_tipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('tipo', 100);
            $table->string('chave', 100);
            $table->string('label', 255);
            $table->string('tipo_modelo', 100)->nullable();
            $table->string('tipo_documento', 100)->nullable();
            $table->boolean('tem_modelo')->default(false);
            $table->boolean('permite_assinatura')->default(false);
            $table->unsignedBigInteger('modelo_arquivo_id')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function criarTipo(array $dados): DossieTipo
    {
        return DossieTipo::create(array_merge([
            'tipo_modelo' => null,
            'tipo_documento' => null,
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ordem' => 1,
            'ativo' => true,
        ], $dados));
    }
}
