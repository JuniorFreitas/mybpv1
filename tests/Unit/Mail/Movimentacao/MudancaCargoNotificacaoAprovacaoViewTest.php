<?php

namespace Tests\Unit\Mail\Movimentacao;

use Tests\TestCase;

class MudancaCargoNotificacaoAprovacaoViewTest extends TestCase
{
    public function test_exibe_apenas_itens_efetivamente_alterados(): void
    {
        $mudanca = (object) [
            'id' => 42,
            'created_at' => now(),
            'status_aprovacao_gestor' => null,
            'status_aprovacao_extra' => null,
            'status_aprovacao_rh' => null,
            'aprovacao_extra_id' => null,
            'obs_solicitante' => null,
            'obs_gestor_aprovacao' => null,
            'obs_aprovacao_extra' => null,
            'obs_rh' => null,
            'data_solicitacao' => '26/11/2026',
            'data_aprovacao_gestor' => null,
            'data_aprovacao_extra' => null,
            'data_aprovacao_rh' => null,
            'anterior_salario' => '1.000,00',
            'novo_salario' => '1.200,00',
            'mantem_centro_custo' => true,
            'mantem_cargo' => true,
            'mantem_funcao' => true,
            'mantem_salario' => false,
        ];

        $html = view('emails.movimentacao.mudanca_cargo.notificacao-aprovacao', [
            'dados' => [
                'tipo' => 'criacao',
                'mudanca_cargo' => $mudanca,
                'centro_custo_anterior' => 'CC Antigo',
                'centro_custo_novo' => 'CC Novo',
                'cargo_anterior' => 'Analista',
                'cargo_novo' => 'Coordenador',
                'funcao_anterior' => 'Função A',
                'funcao_nova' => 'Função B',
                'solicitante' => 'Solicitante Teste',
                'gestor_aprovador' => '',
                'gestor_selecionado' => 'Gestor Teste',
                'aprovacao_extra' => '',
                'rh' => '',
                'nome_aprovacao_extra' => 'Aprovação Extra',
                'url' => 'https://example.test/movimentacao',
                'has_aprovacao_extra' => false,
                'alteracoes' => [
                    'centro_custo' => false,
                    'cargo' => false,
                    'funcao' => false,
                    'salario' => true,
                ],
                'assunto' => 'Teste',
            ],
        ])->render();

        $this->assertStringContainsString('Novo Salário', $html);
        $this->assertStringContainsString('1.200,00', $html);
        $this->assertStringNotContainsString('Centro de Custo Novo', $html);
        $this->assertStringNotContainsString('Novo Cargo', $html);
        $this->assertStringNotContainsString('Nova Função', $html);
    }
}
