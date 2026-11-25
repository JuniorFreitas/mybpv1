<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Documentos\StoreCarteiraAssinaturaRequest;
use App\Http\Requests\Documentos\StoreDossieTipoRequest;
use App\Http\Requests\Documentos\StoreDocumentoPreadmissaoRequest;
use App\Http\Requests\Documentos\UpdateDocumentosPreAdmissaoCandidatoRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DocumentosWaveCFormRequestsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(new User(['empresa_id' => 10, 'id' => 1]));
    }

    public function test_documento_preadmissao_exige_label_e_ativo(): void
    {
        $request = new StoreDocumentoPreadmissaoRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $erros = $validator->errors()->toArray();
        $this->assertArrayHasKey('label', $erros);
        $this->assertArrayHasKey('ativo', $erros);
    }

    public function test_dossie_tipo_exige_label_permite_assinatura_e_ativo(): void
    {
        $request = new StoreDossieTipoRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $erros = $validator->errors()->toArray();
        $this->assertArrayHasKey('label', $erros);
        $this->assertArrayHasKey('permite_assinatura', $erros);
        $this->assertArrayHasKey('ativo', $erros);
    }

    public function test_carteira_assinatura_exige_nome_e_tipo(): void
    {
        $request = StoreCarteiraAssinaturaRequest::create('/fake', 'POST', []);
        $request->setContainer($this->app)->setRedirector($this->app['redirect']);
        $request->setUserResolver(fn () => auth()->user());

        $validator = Validator::make([], $request->rules());
        $this->assertTrue($validator->fails());
        $erros = $validator->errors()->toArray();
        $this->assertArrayHasKey('nome', $erros);
        $this->assertArrayHasKey('tipo', $erros);
    }

    public function test_candidato_pre_admissao_exige_nome_e_filiacao_mae(): void
    {
        $request = new UpdateDocumentosPreAdmissaoCandidatoRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $erros = $validator->errors()->toArray();
        $this->assertArrayHasKey('nome', $erros);
        $this->assertArrayHasKey('filiacao_mae', $erros);
    }
}
