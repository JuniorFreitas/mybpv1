<?php

namespace Tests\Unit\Jobs;

use App\Jobs\JobExportaAdmissoes;
use App\Models\Curriculo;
use ReflectionMethod;
use Tests\TestCase;

class JobExportaAdmissoesEnderecoTest extends TestCase
{
    public function test_endereco_completo_entra_na_planilha(): void
    {
        $curriculo = new Curriculo();
        $curriculo->setRawAttributes([
            'logradouro' => 'Rua das Flores',
            'end_numero' => '120',
            'complemento' => 'Apto 2',
            'cep' => '69000-000',
            'bairro' => 'Centro',
            'municipio' => 'Manaus',
            'uf' => 'AM',
        ]);

        $this->assertSame(
            'Rua das Flores, Apto 2, 120, 69000-000, Centro, Manaus / AM.',
            $this->endereco($curriculo)
        );
    }

    public function test_endereco_parcial_nao_fica_em_branco(): void
    {
        $curriculo = new Curriculo();
        $curriculo->setRawAttributes([
            'logradouro' => 'Avenida Brasil',
            'end_numero' => '50',
            'bairro' => 'Aleixo',
            'municipio' => 'Manaus',
            'uf' => 'AM',
        ]);

        $this->assertSame(
            'Avenida Brasil, 50, Aleixo, Manaus / AM',
            $this->endereco($curriculo)
        );
    }

    public function test_sem_curriculo_informa_ausencia(): void
    {
        $this->assertSame('NÃO INFORMADO', $this->endereco(null));
    }

    private function endereco($curriculo): string
    {
        $job = new JobExportaAdmissoes(1, [], 'admissoes.xlsx');
        $metodo = new ReflectionMethod(JobExportaAdmissoes::class, 'enderecoFuncionario');
        $metodo->setAccessible(true);

        return $metodo->invoke($job, $curriculo);
    }
}
