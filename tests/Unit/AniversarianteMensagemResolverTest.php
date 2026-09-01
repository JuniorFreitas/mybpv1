<?php

namespace Tests\Unit;

use App\Models\AniversarianteMensagemTemplate;
use App\Services\Aniversariante\AniversarianteMensagemResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AniversarianteMensagemResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        Schema::dropIfExists('aniversariante_mensagem_templates');
        Schema::create('aniversariante_mensagem_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->longText('conteudo_html');
            $table->unsignedBigInteger('criado_por')->nullable();
            $table->unsignedBigInteger('atualizado_por')->nullable();
            $table->timestamps();
        });
    }

    public function testSemEmpresaUsaMensagemPadrao(): void
    {
        $resolver = new AniversarianteMensagemResolver();

        $this->assertSame(
            AniversarianteMensagemResolver::MENSAGEM_PADRAO,
            $resolver->conteudoHtml(null)
        );
    }

    public function testSemRegistroUsaMensagemPadrao(): void
    {
        $resolver = new AniversarianteMensagemResolver();

        $this->assertSame(
            AniversarianteMensagemResolver::MENSAGEM_PADRAO,
            $resolver->conteudoHtml(40568)
        );
    }

    public function testHtmlVazioUsaMensagemPadrao(): void
    {
        AniversarianteMensagemTemplate::withoutGlobalScopes()->create([
            'empresa_id' => 40568,
            'conteudo_html' => '   ',
        ]);

        $resolver = new AniversarianteMensagemResolver();

        $this->assertSame(
            AniversarianteMensagemResolver::MENSAGEM_PADRAO,
            $resolver->conteudoHtml(40568)
        );
    }

    public function testHtmlSoComTagsUsaMensagemPadrao(): void
    {
        AniversarianteMensagemTemplate::withoutGlobalScopes()->create([
            'empresa_id' => 40568,
            'conteudo_html' => '<p></p><p><br></p>',
        ]);

        $resolver = new AniversarianteMensagemResolver();

        $this->assertSame(
            AniversarianteMensagemResolver::MENSAGEM_PADRAO,
            $resolver->conteudoHtml(40568)
        );
    }

    public function testRetornaHtmlDaEmpresa(): void
    {
        AniversarianteMensagemTemplate::withoutGlobalScopes()->create([
            'empresa_id' => 40568,
            'conteudo_html' => '<p>Feliz aniversário {{nome}}</p>',
        ]);

        $resolver = new AniversarianteMensagemResolver();

        $this->assertSame(
            '<p>Feliz aniversário {{nome}}</p>',
            $resolver->conteudoHtml(40568)
        );
    }

    public function testEmpresasDiferentesNaoCompartilhamMensagem(): void
    {
        AniversarianteMensagemTemplate::withoutGlobalScopes()->create([
            'empresa_id' => 1,
            'conteudo_html' => '<p>Empresa um</p>',
        ]);
        AniversarianteMensagemTemplate::withoutGlobalScopes()->create([
            'empresa_id' => 2,
            'conteudo_html' => '<p>Empresa dois</p>',
        ]);

        $resolver = new AniversarianteMensagemResolver();

        $this->assertSame('<p>Empresa um</p>', $resolver->conteudoHtml(1));
        $this->assertSame('<p>Empresa dois</p>', $resolver->conteudoHtml(2));
    }

    public function testPipelinePadraoSubstituiNomeNoEnvio(): void
    {
        $resolver = new AniversarianteMensagemResolver();
        $renderer = new \App\Services\Aniversariante\AniversarianteMensagemRenderer();
        $html = $resolver->conteudoHtml(null);

        $resultado = $renderer->render($html, [
            'nome' => 'Maria Silva',
            'empresa' => 'ACME',
        ]);

        $this->assertStringContainsString('Maria Silva', $resultado);
        $this->assertStringNotContainsString('{{nome}}', $resultado);
        $this->assertStringContainsString('Muitos parabéns!', $resultado);
    }

    public function testPipelineUsaHtmlCustomizadoDaEmpresa(): void
    {
        AniversarianteMensagemTemplate::withoutGlobalScopes()->create([
            'empresa_id' => 10,
            'conteudo_html' => '<p>Oi {{nome}} da {{empresa}}</p>',
        ]);

        $resolver = new AniversarianteMensagemResolver();
        $renderer = new \App\Services\Aniversariante\AniversarianteMensagemRenderer();
        $resultado = $renderer->render($resolver->conteudoHtml(10), [
            'nome' => 'Ana',
            'empresa' => 'Beta',
        ]);

        $this->assertSame('<p>Oi Ana da Beta</p>', $resultado);
    }
}
