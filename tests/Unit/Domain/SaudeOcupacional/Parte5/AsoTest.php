<?php

namespace Tests\Unit\Domain\SaudeOcupacional\Parte5;

use App\Domain\SaudeOcupacional\Aso\AsoDocumentoService;
use App\Domain\SaudeOcupacional\Aso\HistoricoAsoService;
use App\Domain\SaudeOcupacional\Medicos\MedicosResponsaveisService;
use App\Domain\SaudeOcupacional\Pgr\PgrInventarioService;
use App\Models\ExamesesmtAso;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Tests\Unit\Domain\SaudeOcupacional\CriaTabelasSaudeOcupacional;

class AsoTest extends TestCase
{
    use CriaTabelasSaudeOcupacional;

    private const EMPRESA = 10;

    private const OUTRA = 20;

    private int $periodico;

    private int $operador;

    private int $colaborador;

    private User $autor;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->criarTabelasSaude();

        DB::table('cliente_configs')->insert(['cliente_id' => self::EMPRESA, 'modulo_saude_ocupacional' => true]);
        $this->periodico = DB::table('exame_tipos')->insertGetId(['label' => 'Periódico']);
        $this->operador = DB::table('vagas')->insertGetId(['nome' => 'Operador de máquina', 'empresa_id' => self::EMPRESA]);

        $curriculo = DB::table('curriculos')->insertGetId(['nome' => 'Maria Teste', 'cpf' => '12345678909', 'email' => 'maria@example.test']);
        $this->colaborador = DB::table('feedback_curriculos')->insertGetId(['empresa_id' => self::EMPRESA, 'curriculo_id' => $curriculo, 'vaga_id' => $this->operador]);
        DB::table('admissoes')->insert(['feedback_id' => $this->colaborador, 'status' => 'ADMITIDO', 'cargo' => 'Operador', 'data_admissao' => '2024-02-01']);

        $this->autor = new User();
        $this->autor->forceFill(['empresa_id' => self::EMPRESA, 'nome' => 'RH', 'email' => 'rh@example.test'])->save();
    }

    public function test_historico_ordena_e_rh_nao_ve_dados_clinicos(): void
    {
        $this->definirPerfil(false);
        $antigo = $this->resultado('2024-02-01', '2025-01-31', ['result' => 'Apto', 'observacoes' => 'texto clínico']);
        DB::table('controle_exame_sesmt_anexos')->insert(['arquivo_id' => 1, 'examesesmt_id' => $antigo]);
        $this->resultado('2025-02-01', '2026-01-31', ['result' => 'Apto com Restrição', 'trabalho_altura' => 'Não']);
        DB::table('exame_funcionarios')->insert(['empresa_id' => self::EMPRESA, 'feedback_id' => $this->colaborador, 'exame_tipo_id' => $this->periodico, 'encaminhamento_data' => '2026-10-20']);

        $h = app(HistoricoAsoService::class)->historico(self::EMPRESA, $this->colaborador, $this->autor);

        $this->assertSame('123.***.***-09', $h['colaborador']['cpf']);
        $this->assertSame(['aguardando_resultado', 'realizado', 'realizado'], array_column($h['itens'], 'situacao'));
        $this->assertSame('Apto com Restrição', $h['itens'][1]['conclusao']);
        $this->assertSame('Não', $h['itens'][1]['trabalho_altura']);
        $this->assertArrayNotHasKey('observacoes', $h['itens'][2]);
        $this->assertSame('restrito', $h['itens'][2]['anexos']);
        $this->assertFalse($h['ve_dados_sensiveis']);
        $this->assertSame(0, DB::table('activity_log')->where('log_name', 'saude_ocupacional_acesso')->count());
    }

    public function test_perfil_saude_ve_observacoes_e_acesso_fica_auditado(): void
    {
        $this->definirPerfil(true);
        $this->resultado('2025-02-01', '2026-01-31', ['result' => 'Apto', 'observacoes' => 'texto clínico']);

        $h = app(HistoricoAsoService::class)->historico(self::EMPRESA, $this->colaborador, $this->autor);

        $this->assertSame('texto clínico', $h['itens'][0]['observacoes']);
        $this->assertSame(1, DB::table('activity_log')->where('log_name', 'saude_ocupacional_acesso')->count());
    }

    public function test_historico_isola_empresas(): void
    {
        $this->definirPerfil(false);

        $this->expectException(ModelNotFoundException::class);
        app(HistoricoAsoService::class)->historico(self::OUTRA, $this->colaborador, $this->autor);
    }

    public function test_emitir_aso_grava_foto_do_documento(): void
    {
        $medico = $this->medico('examinador', '01/01/2024');
        $this->medico('coordenador', '01/01/2024', 'Dr. Coordenador', '22222');
        $this->publicarPgrComRuido();
        $resultado = $this->resultado('2025-02-01', '2026-01-31', ['result' => 'Apto']);
        $aso = app(AsoDocumentoService::class);

        $prep = $aso->preparar(self::EMPRESA, $resultado);
        $this->assertSame(['Ruído'], $prep['riscos']);
        $this->assertSame('Dr. Coordenador', $prep['coordenador']['nome']);

        $emitido = $aso->emitir(self::EMPRESA, $resultado, [
            'saude_medico_id' => $medico,
            'exames' => [['label' => 'Audiometria', 'data' => '01/02/2025'], ['label' => '  ']],
        ], $this->autor);

        $this->assertSame('OPERADOR DE MÁQUINA', $emitido->funcao);
        $this->assertSame(['Ruído'], $emitido->riscos);
        $this->assertSame([['label' => 'Audiometria', 'data' => '01/02/2025']], $emitido->exames);
        $this->assertSame('Dra. Examinadora', $emitido->examinador['nome']);
        $this->assertSame('Dr. Coordenador', $emitido->coordenador['nome']);
        $this->assertSame(1, DB::table('activity_log')->where('log_name', 'saude_aso')->count());
    }

    public function test_emitir_exige_exame_realizado_e_medico_da_empresa(): void
    {
        $aso = app(AsoDocumentoService::class);
        $naoRealizado = $this->resultado('2025-02-01', null, ['result' => null], false);
        $realizado = $this->resultado('2025-03-01', '2026-02-28', ['result' => 'Apto']);
        $medicoOutra = DB::table('saude_medicos')->insertGetId(['empresa_id' => self::OUTRA, 'nome' => 'X', 'crm' => '9999', 'crm_uf' => 'SP', 'ativo' => true]);

        foreach ([[$naoRealizado, $this->medico('examinador', '01/01/2024')], [$realizado, $medicoOutra]] as [$id, $medico]) {
            try {
                $aso->emitir(self::EMPRESA, $id, ['saude_medico_id' => $medico, 'exames' => []], $this->autor);
                $this->fail('Deveria recusar.');
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_nao_reemite_aso_ja_enviado_para_assinatura(): void
    {
        $medico = $this->medico('examinador', '01/01/2024');
        $resultado = $this->resultado('2025-02-01', '2026-01-31', ['result' => 'Apto']);
        $aso = app(AsoDocumentoService::class);
        $emitido = $aso->emitir(self::EMPRESA, $resultado, ['saude_medico_id' => $medico, 'exames' => []], $this->autor);

        $emitido->documento_assinatura_id = DB::table('documento_para_assinatura')->insertGetId(['status' => 'em_assinatura']);
        $emitido->save();

        $this->expectException(ValidationException::class);
        $aso->emitir(self::EMPRESA, $resultado, ['saude_medico_id' => $medico, 'exames' => []], $this->autor);
    }

    public function test_pdf_do_aso_tem_campos_da_nr7(): void
    {
        $html = view('pdf.saude-ocupacional.aso', ['dados' => [
            'dados_empresa' => ['razao_social' => 'EMPRESA TESTE LTDA', 'cnpj' => '00.000.000/0001-00'],
            'empregado' => ['nome' => 'Maria Teste', 'cpf' => '123.456.789-09', 'funcao' => 'OPERADOR'],
            'tipo' => 'Periódico',
            'data_exame_br' => '01/02/2025',
            'conclusao' => 'Apto',
            'trabalho_altura' => 'Sim',
            'espaco_confinado' => 'Não se aplica',
            'riscos' => [],
            'exames' => [['label' => 'Audiometria', 'data' => '01/02/2025']],
            'examinador' => ['nome' => 'Dra. Examinadora', 'crm' => '11111', 'crm_uf' => 'SP'],
            'coordenador' => ['nome' => 'Dr. Coordenador', 'crm' => 'CRM 22222/SP'],
            'emitido_em_br' => '05/11/2026 10:00',
        ]])->render();

        foreach (['EMPRESA TESTE LTDA', '00.000.000/0001-00', 'Maria Teste', '123.456.789-09', 'OPERADOR',
            'Ausência de fatores de risco', 'Exame clínico', 'Audiometria', 'APTO PARA A FUNÇÃO',
            'Dr. Coordenador — CRM 22222/SP', 'Dra. Examinadora — CRM 11111/SP'] as $esperado) {
            $this->assertStringContainsString($esperado, $html);
        }
    }

    private function definirPerfil(bool $saude): void
    {
        Gate::define('saude_ocupacional_dados_sensiveis', fn () => $saude);
    }

    private function resultado(string $realizacao, ?string $vencimento, array $resultado, bool $realizado = true): int
    {
        $ef = DB::table('exame_funcionarios')->insertGetId([
            'empresa_id' => self::EMPRESA, 'exame_tipo_id' => $this->periodico, 'feedback_id' => $this->colaborador, 'encaminhamento_data' => $realizacao,
        ]);

        return DB::table('examesesmts')->insertGetId([
            'empresa_id' => self::EMPRESA, 'exame_funcionario_id' => $ef, 'feedback_id' => $this->colaborador,
            'exame_realizado' => $realizado, 'atual' => true, 'resultado' => json_encode($resultado + ['aprovado' => 'Sim']),
            'data_realizacao' => $realizado ? $realizacao : null, 'data_vencimento' => $vencimento,
        ]);
    }

    private function medico(string $papel, string $inicio, string $nome = 'Dra. Examinadora', string $crm = '11111'): int
    {
        return app(MedicosResponsaveisService::class)->salvar(self::EMPRESA, null, [
            'nome' => $nome, 'crm' => $crm, 'crm_uf' => 'SP', 'email' => 'medico' . $crm . '@example.test',
            'vinculos' => [['papel' => $papel, 'vigencia_inicio' => $inicio]],
        ], $this->autor)->id;
    }

    private function publicarPgrComRuido(): void
    {
        $tipo = DB::table('exame_risco_tipos')->insertGetId(['label' => 'Físico']);
        $ruido = DB::table('exame_riscos')->insertGetId(['empresa_id' => self::EMPRESA, 'risco_tipo' => $tipo, 'label' => 'Ruído']);
        $pgr = app(PgrInventarioService::class);
        $rascunho = $pgr->criarRascunho(self::EMPRESA, $this->autor);
        $pgr->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, ['ausencia_risco' => false, 'riscos' => [['exame_risco_id' => $ruido]]]);
        $pgr->publicar(self::EMPRESA, $rascunho->id, '01/01/2024', 'futuro', null, $this->autor);
    }
}
