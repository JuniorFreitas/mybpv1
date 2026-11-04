<?php

namespace Tests\Unit\Services\ControleExames;

use App\Services\ControleExames\ColaboradorExameIdentificacao;
use Tests\TestCase;

class ColaboradorExameIdentificacaoTest extends TestCase
{
    public function test_marca_centro_de_custo_e_cargo_ausentes(): void
    {
        $dados = (new ColaboradorExameIdentificacao())->apresentar('Matriz SA', '00.000.000/0001-00', '  ', null);

        $this->assertSame('Matriz SA - 00.000.000/0001-00', $dados['lotacao']);
        $this->assertSame('Matriz SA', $dados['lotacao_nome']);
        $this->assertSame('00.000.000/0001-00', $dados['lotacao_cnpj']);
        $this->assertSame('', $dados['centro_custo']);
        $this->assertSame('', $dados['cargo']);
        $this->assertFalse($dados['possui_centro_custo']);
        $this->assertFalse($dados['possui_cargo']);
    }

    public function test_considera_preenchido_quando_ha_texto(): void
    {
        $dados = (new ColaboradorExameIdentificacao())->apresentar('Filial SLZ', '22.222.222/0001-22', 'Operações', 'Analista');

        $this->assertTrue($dados['possui_centro_custo']);
        $this->assertTrue($dados['possui_cargo']);
        $this->assertSame('Operações', $dados['centro_custo']);
        $this->assertSame('Analista', $dados['cargo']);
        $this->assertSame('Filial SLZ', $dados['lotacao_nome']);
        $this->assertSame('22.222.222/0001-22', $dados['lotacao_cnpj']);
    }

    public function test_lotacao_nao_informada_fica_vazia(): void
    {
        $dados = (new ColaboradorExameIdentificacao())->apresentar('Não informado', null, 'CC 1', 'Cargo');

        $this->assertSame('', $dados['lotacao']);
        $this->assertSame('', $dados['lotacao_nome']);
        $this->assertSame('', $dados['lotacao_cnpj']);
        $this->assertTrue($dados['possui_centro_custo']);
        $this->assertTrue($dados['possui_cargo']);
    }
}
