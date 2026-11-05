<?php

namespace Tests\Unit\Domain\SaudeOcupacional\Parte2;

use App\Domain\SaudeOcupacional\Medicos\MedicosResponsaveisService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Tests\Unit\Domain\SaudeOcupacional\CriaTabelasSaudeOcupacional;

class MedicosResponsaveisTest extends TestCase
{
    use CriaTabelasSaudeOcupacional;

    private const EMPRESA = 10;

    private const OUTRA = 20;

    private MedicosResponsaveisService $service;

    private User $autor;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->criarTabelasSaude();
        $this->service = new MedicosResponsaveisService();
        $this->autor = new User();
        $this->autor->forceFill(['empresa_id' => self::EMPRESA, 'nome' => 'RH', 'email' => 'rh@example.test'])->save();
    }

    public function test_cadastra_coordenador_e_normaliza_crm(): void
    {
        $medico = $this->service->salvar(self::EMPRESA, null, $this->dados('12.345', 'sp', [
            ['papel' => 'coordenador', 'vigencia_inicio' => '01/01/2026'],
        ]), $this->autor);

        $this->assertSame('12345', $medico->crm);
        $this->assertSame('SP', $medico->crm_uf);

        $coordenador = $this->service->coordenadorVigente(self::EMPRESA, CarbonImmutable::parse('2026-11-05'));
        $this->assertSame('Dra. Ana', $coordenador['nome']);
        $this->assertSame('CRM 12345/SP', $coordenador['crm_completo']);
        $this->assertSame(1, DB::table('activity_log')->where('log_name', 'saude_medicos')->count());
    }

    public function test_crm_duplicado_na_mesma_empresa_e_recusado_mas_vale_em_outra(): void
    {
        $this->service->salvar(self::EMPRESA, null, $this->dados('12345', 'SP'), $this->autor);
        $this->service->salvar(self::OUTRA, null, $this->dados('12345', 'SP'), $this->autor);

        $this->expectException(ValidationException::class);
        $this->service->salvar(self::EMPRESA, null, $this->dados('12345', 'SP'), $this->autor);
    }

    public function test_nao_permite_dois_coordenadores_no_mesmo_periodo(): void
    {
        $this->service->salvar(self::EMPRESA, null, $this->dados('11111', 'SP', [
            ['papel' => 'coordenador', 'vigencia_inicio' => '01/01/2025', 'vigencia_fim' => '31/12/2025'],
        ]), $this->autor);

        // Depois do fim do anterior: ok.
        $this->service->salvar(self::EMPRESA, null, $this->dados('22222', 'SP', [
            ['papel' => 'coordenador', 'vigencia_inicio' => '01/01/2026'],
        ]), $this->autor);

        $this->expectException(ValidationException::class);
        $this->service->salvar(self::EMPRESA, null, $this->dados('33333', 'SP', [
            ['papel' => 'coordenador', 'vigencia_inicio' => '01/06/2026'],
        ]), $this->autor);
    }

    public function test_clinica_precisa_ser_da_empresa_e_so_para_examinador(): void
    {
        $clinicaOutra = DB::table('empresa_exames')->insertGetId(['empresa_id' => self::OUTRA, 'nome' => 'Clínica X']);

        $this->expectException(ValidationException::class);
        $this->service->salvar(self::EMPRESA, null, $this->dados('12345', 'SP', [
            ['papel' => 'examinador', 'empresa_exame_id' => $clinicaOutra, 'vigencia_inicio' => '01/01/2026'],
        ]), $this->autor);
    }

    public function test_alertas_contam_vinculos_vencendo_e_ignoram_medico_inativo(): void
    {
        $medico = $this->service->salvar(self::EMPRESA, null, $this->dados('12345', 'SP', [
            ['papel' => 'coordenador', 'vigencia_inicio' => '01/01/2026', 'vigencia_fim' => '20/11/2026'],
        ]), $this->autor);

        $alertas = $this->service->alertas(self::EMPRESA, CarbonImmutable::parse('2026-11-05'));
        $this->assertSame(1, $alertas['vinculos_vencendo']);
        $this->assertNotNull($alertas['coordenador']);

        $this->service->alterarAtivo(self::EMPRESA, $medico->id, false, $this->autor);
        $this->assertNull($this->service->coordenadorVigente(self::EMPRESA, CarbonImmutable::parse('2026-11-05')));
    }

    public function test_editar_troca_vinculos_e_nao_conflita_consigo_mesmo(): void
    {
        $medico = $this->service->salvar(self::EMPRESA, null, $this->dados('12345', 'SP', [
            ['papel' => 'coordenador', 'vigencia_inicio' => '01/01/2026'],
        ]), $this->autor);
        $vinculoId = DB::table('saude_medicos_vinculos')->value('id');

        $this->service->salvar(self::EMPRESA, $medico->id, $this->dados('12345', 'SP', [
            ['id' => $vinculoId, 'papel' => 'coordenador', 'vigencia_inicio' => '01/02/2026'],
            ['papel' => 'examinador', 'vigencia_inicio' => '01/02/2026'],
        ]), $this->autor);

        $this->assertSame(2, DB::table('saude_medicos_vinculos')->whereNull('deleted_at')->count());
        $this->assertSame('2026-02-01', substr((string) DB::table('saude_medicos_vinculos')->where('id', $vinculoId)->value('vigencia_inicio'), 0, 10));
    }

    public function test_nao_exclui_medico_de_outra_empresa(): void
    {
        $medico = $this->service->salvar(self::EMPRESA, null, $this->dados('12345', 'SP'), $this->autor);

        $this->expectException(ModelNotFoundException::class);
        $this->service->excluir(self::OUTRA, $medico->id, $this->autor);
    }

    private function dados(string $crm, string $uf, array $vinculos = []): array
    {
        return ['nome' => 'Dra. Ana', 'crm' => $crm, 'crm_uf' => $uf, 'especialidade' => 'Medicina do Trabalho', 'vinculos' => $vinculos];
    }
}
