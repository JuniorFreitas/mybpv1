<?php

namespace Tests\Unit\Services\Planejamento\Movimentacao;

use App\Models\Cliente;
use App\Models\ClienteFilial;
use App\Services\Planejamento\Movimentacao\LotacaoLabelResolver;
use Tests\TestCase;

class LotacaoLabelResolverTest extends TestCase
{
    public function test_format_com_nome_e_cnpj(): void
    {
        $this->assertSame('Filial Sul - 12.345.678/0001-99', LotacaoLabelResolver::format(
            'Filial Sul',
            'Razão LTDA',
            '12.345.678/0001-99'
        ));
    }

    public function test_from_filial_lê_campos_do_json_dados(): void
    {
        $filial = new ClienteFilial();
        $filial->id = 1;
        $filial->setRawAttributes([
            'id' => 1,
            'dados' => json_encode([
                'nome_fantasia' => 'Unidade SP',
                'razao_social' => 'Unidade SP LTDA',
                'cnpj' => '11.222.333/0001-44',
            ]),
        ]);

        $this->assertSame('Unidade SP - 11.222.333/0001-44', LotacaoLabelResolver::fromFilial($filial));
    }

    public function test_resolve_prioriza_filial_quando_flag_filial(): void
    {
        $filial = new ClienteFilial();
        $filial->setRawAttributes([
            'id' => 2,
            'dados' => json_encode(['nome_fantasia' => 'Filial A', 'cnpj' => '99.999.999/0001-11']),
        ]);

        $empresa = new Cliente([
            'nome_fantasia' => 'Matriz',
            'cnpj' => '00.000.000/0001-00',
        ]);

        $this->assertSame(
            'Filial A - 99.999.999/0001-11',
            LotacaoLabelResolver::resolve(true, $filial, $empresa)
        );
    }

    public function test_resolve_usa_empresa_quando_nao_e_filial(): void
    {
        $empresa = new Cliente([
            'nome_fantasia' => 'Matriz Central',
            'razao_social' => 'Matriz Central SA',
            'cnpj' => '00.111.222/0001-33',
        ]);

        $this->assertSame(
            'Matriz Central - 00.111.222/0001-33',
            LotacaoLabelResolver::resolve(false, null, $empresa)
        );
    }
}
