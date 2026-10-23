<?php

namespace Tests\Unit\Models;

use App\Models\CentroCusto;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CentroCustoCentrosDoCnpjTest extends TestCase
{
    public function test_encontra_centros_quando_a_chave_numerica_do_cnpj_foi_agrupada(): void
    {
        $cnpj = '82984378000131';
        $lista = $this->listaComCentros($cnpj, [
            ['id' => 10, 'filial_id' => null, 'matriz' => true, 'label' => 'ADM'],
        ]);

        $cc = (new CentroCusto())->centrosDoCnpj($lista, $cnpj);

        $this->assertInstanceOf(Collection::class, $cc);
        $this->assertSame(10, $cc[0]['id']);
        $this->assertTrue($cc[0]['matriz']);
    }

    public function test_aceita_cnpj_formatado_no_filtro(): void
    {
        $lista = $this->listaComCentros('82984378000131', [
            ['id' => 11, 'filial_id' => 4, 'matriz' => false, 'label' => 'OBRA'],
        ]);

        $cc = (new CentroCusto())->centrosDoCnpj($lista, '82.984.378/0001-31');

        $this->assertSame(11, $cc[0]['id']);
        $this->assertFalse($cc[0]['matriz']);
    }

    public function test_retorna_null_quando_a_lotacao_nao_tem_centro_de_custo(): void
    {
        $comCentro = '15110791000180';
        $semCentro = '82984378000131';
        $lista = $this->listaComCentros($comCentro, [
            ['id' => 1, 'filial_id' => null, 'matriz' => true, 'label' => 'MATRIZ'],
        ]);
        $lista->get('cnpjs')[$semCentro] = [
            'cnpj' => '82.984.378/0001-31',
            'matriz' => false,
        ];

        $this->assertNull((new CentroCusto())->centrosDoCnpj($lista, $semCentro));
    }

    private function listaComCentros(string $cnpj, array $centros): Collection
    {
        $itens = array_map(function (array $centro) use ($cnpj) {
            return array_merge($centro, ['cnpj' => $cnpj]);
        }, $centros);

        return collect([
            'cnpjs' => collect([
                $cnpj => [
                    'cnpj' => $cnpj,
                    'matriz' => (bool) ($centros[0]['matriz'] ?? false),
                ],
            ]),
            'centros_custos' => collect($itens)->groupBy('cnpj'),
        ]);
    }
}
