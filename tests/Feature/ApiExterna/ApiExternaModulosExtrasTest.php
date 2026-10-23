<?php

namespace Tests\Feature\ApiExterna;

use App\Models\Admissao;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApiExternaSchema;
use Tests\TestCase;

class ApiExternaModulosExtrasTest extends TestCase
{
    use ApiExternaSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirSchemaModulosExtrasApiExterna();
    }

    public function testVagasAbertasECentrosCusto(): void
    {
        $empresaId = 99101;
        $cred = $this->criarCredencialApiExterna($empresaId, [
            ApiExternaScopes::VAGAS_READ,
            ApiExternaScopes::CENTROS_CUSTO_READ,
        ]);
        $seed = $this->seedBase($empresaId);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);
        $auth = ['Authorization' => 'Bearer ' . $token];

        $vaga = $this->withHeaders($auth)
            ->getJson('/api/v3/vagas-abertas')
            ->assertOk()
            ->json('data.0');
        $this->assertSame(ApiExternaId::encode($seed['vaga_aberta_id']), $vaga['public_id']);
        $this->assertSame('Operador', $vaga['vaga']['nome']);
        $this->assertArrayHasKey('simulados', $vaga['links']);

        $this->withHeaders($auth)
            ->getJson('/api/v3/vagas-abertas/buscar?q=Op')
            ->assertOk()
            ->assertJsonPath('data.0.titulo', 'Operador - São Luís');

        $detalheVa = $this->withHeaders($auth)
            ->getJson('/api/v3/vagas-abertas/' . $vaga['public_id'])
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $detalheVa['simulados']);
        $this->assertCount(1, $detalheVa['projetos']);

        $cc = $this->withHeaders($auth)
            ->getJson('/api/v3/centros-custo')
            ->assertOk()
            ->json('data.0');
        $this->assertSame('CC Operações', $cc['label']);

        $detalheCc = $this->withHeaders($auth)
            ->getJson('/api/v3/centros-custo/' . $cc['public_id'])
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $detalheCc['filiais']);
        $this->assertSame('Filial SLZ', $detalheCc['filiais'][0]['nome']);
        $this->assertSame('22.222.222/0001-22', $detalheCc['filiais'][0]['cnpj']);
        $this->assertCount(1, $detalheCc['gestores']);
        $this->assertSame('GESTOR_PRINCIPAL', $detalheCc['gestores'][0]['tipo']);
    }

    public function testCihMedidasHistoricoPosAdmissao(): void
    {
        $empresaId = 99102;
        $cred = $this->criarCredencialApiExterna($empresaId, [
            ApiExternaScopes::CIH_READ,
            ApiExternaScopes::MEDIDAS_READ,
            ApiExternaScopes::HISTORICO_READ,
            ApiExternaScopes::POS_ADMISSAO_READ,
        ]);
        $seed = $this->seedBase($empresaId);
        $token = $this->bearerToken($cred['client_id'], $cred['client_secret']);
        $auth = ['Authorization' => 'Bearer ' . $token];

        $cih = $this->withHeaders($auth)
            ->getJson('/api/v3/cih')
            ->assertOk()
            ->json('data.0');
        $this->assertSame('Aberto', $cih['status']);
        $this->assertSame('Segurança', $cih['tag']['label']);

        $this->withHeaders($auth)
            ->getJson('/api/v3/cih/tags')
            ->assertOk()
            ->assertJsonPath('data.0.label', 'Segurança');

        $cols = $this->withHeaders($auth)
            ->getJson('/api/v3/cih/' . $cih['public_id'] . '/colaboradores')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $cols['colaboradores']);
        $this->assertSame('***.***.***-99', $cols['colaboradores'][0]['cpf']);

        $medida = $this->withHeaders($auth)
            ->getJson('/api/v3/medidas-administrativas')
            ->assertOk()
            ->json('data.0');
        $this->assertSame('Advertência Verbal', $medida['tipo']);
        $this->assertArrayNotHasKey('motivo', $medida);

        $medidaDetalhe = $this->withHeaders($auth)
            ->getJson('/api/v3/medidas-administrativas/' . $medida['public_id'])
            ->assertOk()
            ->json('data');
        $this->assertSame('Conduta inadequada', $medidaDetalhe['motivo']);

        $hist = $this->withHeaders($auth)
            ->getJson('/api/v3/historico')
            ->assertOk()
            ->json('data.0');
        $this->assertSame(ApiExternaId::encode($seed['feedback_id']), $hist['public_id']);
        $this->assertFalse($hist['demitido']);

        $medidasHist = $this->withHeaders($auth)
            ->getJson('/api/v3/historico/' . $hist['public_id'] . '/medidas')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $medidasHist['medidas']);

        $afast = $this->withHeaders($auth)
            ->getJson('/api/v3/historico/' . $hist['public_id'] . '/afastamentos')
            ->assertOk()
            ->json('data');
        $this->assertCount(1, $afast['afastamentos']);

        $pos = $this->withHeaders($auth)
            ->getJson('/api/v3/pos-admissao')
            ->assertOk()
            ->json('data.0');
        $this->assertFalse($pos['tem_entrevista_desligamento']);

        DB::table('entrevista_desligamentos')->insert([
            'feedback_id' => $seed['feedback_id'],
            'motivo' => 'Pessoal',
            'trabalharia_novamente' => 'Sim',
            'pode_voltar' => true,
            'quem_entrevistou' => 'RH',
            'data_entrevista' => '2026-06-01',
            'parecer_entrevistador' => 'Ok',
            'comentarios' => 'Privado',
            'salario_beneficio' => '3000',
        ]);

        $ent = $this->withHeaders($auth)
            ->getJson('/api/v3/pos-admissao/' . $pos['public_id'] . '/entrevista-desligamento')
            ->assertOk()
            ->json('data');
        $this->assertSame('Pessoal', $ent['entrevista']['motivo']);
        $this->assertTrue($ent['entrevista']['pode_voltar']);
        $this->assertArrayNotHasKey('salario_beneficio', $ent['entrevista']);
        $this->assertArrayNotHasKey('comentarios', $ent['entrevista']);
        $this->assertTrue($ent['entrevista']['tem_parecer']);
    }

    /**
     * @return array{feedback_id:int,vaga_aberta_id:int,centro_custo_id:int}
     */
    private function seedBase(int $empresaId): array
    {
        $areaId = DB::table('areas')->insertGetId(['label' => 'Operações', 'ativo' => true]);
        $clienteId = DB::table('clientes')->insertGetId([
            'razao_social' => 'Cliente Extra',
            'nome' => 'Cliente Extra',
            'cnpj' => '11111111000111',
            'area_id' => $areaId,
        ]);
        $municipioId = DB::table('municipios')->insertGetId(['nome' => 'São Luís', 'uf' => 'MA']);
        $vagaId = DB::table('vagas')->insertGetId([
            'nome' => 'Operador',
            'ativo' => true,
            'empresa_id' => $empresaId,
        ]);
        $vagaAbertaId = DB::table('vagas_abertas')->insertGetId([
            'empresa_id' => $empresaId,
            'vaga_id' => $vagaId,
            'titulo' => 'Operador - São Luís',
            'descricao' => 'Vaga aberta teste',
            'municipio_id' => $municipioId,
            'ativo' => true,
            'ativo_sistema' => true,
        ]);
        DB::table('simulado_vagas')->insert([
            'simulado_id' => 1,
            'vaga_id' => $vagaId,
            'vagas_abertas_id' => $vagaAbertaId,
            'empresa_id' => $empresaId,
            'online' => true,
            'ativo' => true,
        ]);
        $projetoId = DB::table('projetos')->insertGetId([
            'nome' => 'Projeto Alpha',
            'empresa_id' => $empresaId,
        ]);
        DB::table('vaga_projetos')->insert([
            'projeto_id' => $projetoId,
            'vaga_aberta_id' => $vagaAbertaId,
            'empresa_id' => $empresaId,
            'qnt_total' => 5,
            'qnt_preenchida' => 1,
        ]);

        $gestorId = DB::table('users')->insertGetId([
            'nome' => 'Gestor CC',
            'login' => 'gestor_cc_' . $empresaId,
            'password' => 'x',
            'empresa_id' => $empresaId,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $centroId = DB::table('centro_custos')->insertGetId([
            'empresa_id' => $empresaId,
            'label' => 'CC Operações',
            'ativo' => true,
            'gestor_id' => $gestorId,
        ]);
        $filialId = DB::table('cliente_filials')->insertGetId([
            'empresa_id' => $empresaId,
            'dados' => json_encode([
                'nome_fantasia' => 'Filial SLZ',
                'razao_social' => 'Filial São Luís LTDA',
                'cnpj' => '22.222.222/0001-22',
            ], JSON_UNESCAPED_UNICODE),
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('centro_custo_filials')->insert([
            'empresa_id' => $empresaId,
            'centro_custo_id' => $centroId,
            'cliente_filial_id' => $filialId,
            'ativo' => true,
        ]);
        DB::table('centro_custo_gestores')->insert([
            'centro_custo_id' => $centroId,
            'usuario_id' => $gestorId,
            'tipo' => 'GESTOR_PRINCIPAL',
            'ativo' => true,
            'empresa_id' => $empresaId,
        ]);

        $curriculoId = DB::table('curriculos')->insertGetId([
            'nome' => 'João Histórico',
            'cpf' => '12345678999',
            'email' => 'joao@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'curriculo_id' => $curriculoId,
            'cliente_id' => $clienteId,
            'empresa_id' => $empresaId,
            'vaga_id' => $vagaId,
            'vagas_abertas_id' => $vagaAbertaId,
            'status' => 'Admitido',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('admissoes')->insert([
            'feedback_id' => $feedbackId,
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'data_admissao' => '2026-01-10',
            'cargo' => 'Operador',
            'matricula' => 'M001',
            'centro_custo_id' => $centroId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tagId = DB::table('cih_tags')->insertGetId([
            'empresa_id' => $empresaId,
            'label' => 'Segurança',
            'ativo' => true,
        ]);
        $cihId = DB::table('cihs')->insertGetId([
            'empresa_id' => $empresaId,
            'tag_id' => $tagId,
            'gestor_id' => $gestorId,
            'area_id' => $areaId,
            'centro_custo_id' => $centroId,
            'cliente_id' => $clienteId,
            'feedback_id' => $feedbackId,
            'status' => 'Aberto',
            'acao' => 'Orientar',
            'data_lancamento' => '2026-02-01',
            'varios_colaboradores' => false,
        ]);
        DB::table('cih_feedback')->insert([
            'cih_id' => $cihId,
            'feedback_id' => $feedbackId,
        ]);

        DB::table('medida_administrativas')->insert([
            'feedback_id' => $feedbackId,
            'tipo' => 'Advertência Verbal',
            'definicao' => 'Definição',
            'motivo' => 'Conduta inadequada',
            'causa' => 'Comportamento',
            'data_solicitacao' => '2026-03-01',
            'solicitante' => 'Gestor',
        ]);

        DB::table('afastamentos')->insert([
            'feedback_id' => $feedbackId,
            'motivo' => 'Médico',
            'data_inicio' => '2026-04-01',
            'data_fim' => '2026-04-10',
            'observacao' => 'Atestado',
        ]);

        return [
            'feedback_id' => $feedbackId,
            'vaga_aberta_id' => $vagaAbertaId,
            'centro_custo_id' => $centroId,
        ];
    }
}
