<?php

/**
 * Script de Teste - Aprovação Extra (Requisição de Vaga e Valor Extra)
 *
 * Execute: php artisan tinker
 * Depois: include 'docs/TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.php'
 *
 * Ou execute diretamente:
 * docker compose exec mybpdp php artisan tinker < docs/TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.php
 */

use App\Models\AprovacaoExtraConfig;
use App\Models\RequisicaoVaga;
use App\Models\User;
use App\Models\ValorExtraPrevista;

echo "\n";
echo "========================================\n";
echo "TESTE DE APROVAÇÃO EXTRA\n";
echo "========================================\n\n";

// Configurações
$empresaId = 1; // Altere para o ID da sua empresa
$usuarioGestorId = 2; // Altere para ID de um gestor
$usuarioExtraId = 3; // Altere para ID de usuário autorizado

echo "📋 Configuração:\n";
echo "   Empresa ID: {$empresaId}\n";
echo "   Gestor ID: {$usuarioGestorId}\n";
echo "   Usuário Extra ID: {$usuarioExtraId}\n\n";

// ============================================================================
// TESTE 1: Verificar Configurações
// ============================================================================

echo "🧪 TESTE 1: Verificar Configurações\n";
echo "-------------------------------------------\n";

$configRequisicao = AprovacaoExtraConfig::where('empresa_id', $empresaId)
    ->where('tipo_processo', 'requisicao_vaga')
    ->where('ativo', true)
    ->first();

if ($configRequisicao) {
    echo "✅ Configuração Requisição de Vaga encontrada\n";
    echo "   Nome: {$configRequisicao->nome_aprovacao}\n";
    echo "   Usuários autorizados: " . json_encode($configRequisicao->usuarios_autorizados) . "\n";
} else {
    echo "❌ Configuração Requisição de Vaga NÃO encontrada\n";
    echo "   Execute o SQL script primeiro!\n";
}

$configValorExtra = AprovacaoExtraConfig::where('empresa_id', $empresaId)
    ->where('tipo_processo', 'valor_extra')
    ->where('ativo', true)
    ->first();

if ($configValorExtra) {
    echo "✅ Configuração Valor Extra encontrada\n";
    echo "   Nome: {$configValorExtra->nome_aprovacao}\n";
    echo "   Usuários autorizados: " . json_encode($configValorExtra->usuarios_autorizados) . "\n";
} else {
    echo "❌ Configuração Valor Extra NÃO encontrada\n";
}

echo "\n";

// ============================================================================
// TESTE 2: Verificar Permissões
// ============================================================================

echo "🧪 TESTE 2: Verificar Permissões\n";
echo "-------------------------------------------\n";

if ($configRequisicao) {
    $usuarioExtra = User::find($usuarioExtraId);

    if ($usuarioExtra) {
        $podeAprovar = $configRequisicao->podeAprovar($usuarioExtraId);

        echo "Usuário: {$usuarioExtra->nome} ({$usuarioExtra->login})\n";
        echo "Pode aprovar requisição: " . ($podeAprovar ? "✅ SIM" : "❌ NÃO") . "\n";

        // Verificar habilidades
        $habilidades = $usuarioExtra->listaDeHabilidades();
        $temGestaoRh = in_array('privilegio_gestao_rh', $habilidades);
        $temAprovarRh = in_array('privilegio_aprovar_por_rh', $habilidades);

        echo "Tem privilegio_gestao_rh: " . ($temGestaoRh ? "✅ SIM" : "❌ NÃO") . "\n";
        echo "Tem privilegio_aprovar_por_rh: " . ($temAprovarRh ? "✅ SIM" : "❌ NÃO") . "\n";
    } else {
        echo "❌ Usuário Extra ID {$usuarioExtraId} não encontrado\n";
    }
} else {
    echo "⚠️  Pulando teste (sem configuração)\n";
}

echo "\n";

// ============================================================================
// TESTE 3: Verificar Colunas no Banco
// ============================================================================

echo "🧪 TESTE 3: Verificar Colunas no Banco\n";
echo "-------------------------------------------\n";

try {
    // Tentar criar requisição vazia para validar schema
    $requisicao = new RequisicaoVaga();
    $fillable = $requisicao->getFillable();

    $colunasNecessarias = [
        'aprovacao_extra_id',
        'status_aprovacao_extra',
        'obs_aprovacao_extra',
        'data_aprovacao_extra'
    ];

    $todasPresentes = true;
    foreach ($colunasNecessarias as $coluna) {
        $presente = in_array($coluna, $fillable);
        echo ($presente ? "✅" : "❌") . " {$coluna}\n";
        if (!$presente) $todasPresentes = false;
    }

    if ($todasPresentes) {
        echo "\n✅ Todas as colunas estão no fillable do RequisicaoVaga\n";
    } else {
        echo "\n❌ Algumas colunas faltam no fillable\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar model: {$e->getMessage()}\n";
}

echo "\n";

// ============================================================================
// TESTE 4: Verificar Relacionamentos
// ============================================================================

echo "🧪 TESTE 4: Verificar Relacionamentos\n";
echo "-------------------------------------------\n";

try {
    $requisicao = new RequisicaoVaga();

    // Verificar se método existe
    if (method_exists($requisicao, 'AprovacaoExtra')) {
        echo "✅ Relacionamento AprovacaoExtra existe no RequisicaoVaga\n";
    } else {
        echo "❌ Relacionamento AprovacaoExtra NÃO existe\n";
    }

    $valorExtra = new ValorExtraPrevista();

    if (method_exists($valorExtra, 'AprovacaoExtra')) {
        echo "✅ Relacionamento AprovacaoExtra existe no ValorExtraPrevista\n";
    } else {
        echo "❌ Relacionamento AprovacaoExtra NÃO existe\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar relacionamentos: {$e->getMessage()}\n";
}

echo "\n";

// ============================================================================
// TESTE 5: Verificar Métodos dos Controllers
// ============================================================================

echo "🧪 TESTE 5: Verificar Métodos\n";
echo "-------------------------------------------\n";

try {
    $reqController = new \App\Http\Controllers\RequisicaoVagaController();

    if (method_exists($reqController, 'aprovarExtra')) {
        echo "✅ Método aprovarExtra existe no RequisicaoVagaController\n";
    } else {
        echo "❌ Método aprovarExtra NÃO existe\n";
    }

    $valorController = new \App\Http\Controllers\ValorExtraPrevistaController();

    if (method_exists($valorController, 'aprovarExtra')) {
        echo "✅ Método aprovarExtra existe no ValorExtraPrevistaController\n";
    } else {
        echo "❌ Método aprovarExtra NÃO existe\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar controllers: {$e->getMessage()}\n";
}

echo "\n";

// ============================================================================
// TESTE 6: Contar Registros
// ============================================================================

echo "🧪 TESTE 6: Estatísticas\n";
echo "-------------------------------------------\n";

$totalRequisicoes = RequisicaoVaga::where('empresa_id', $empresaId)->count();
$requisicoesComExtra = RequisicaoVaga::where('empresa_id', $empresaId)
    ->whereNotNull('aprovacao_extra_id')
    ->count();

echo "Total de Requisições de Vaga: {$totalRequisicoes}\n";
echo "Com Aprovação Extra: {$requisicoesComExtra}\n";

$totalValorExtra = ValorExtraPrevista::where('empresa_id', $empresaId)->count();
$valorExtraComExtra = ValorExtraPrevista::where('empresa_id', $empresaId)
    ->whereNotNull('aprovacao_extra_id')
    ->count();

echo "Total de Valor Extra: {$totalValorExtra}\n";
echo "Com Aprovação Extra: {$valorExtraComExtra}\n";

echo "\n";

// ============================================================================
// RESUMO
// ============================================================================

echo "========================================\n";
echo "RESUMO DOS TESTES\n";
echo "========================================\n\n";

$tudo_ok = $configRequisicao && $configValorExtra;

if ($tudo_ok) {
    echo "✅ Sistema de Aprovação Extra está configurado!\n\n";

    echo "📝 Próximos passos:\n";
    echo "   1. Testar criando uma requisição de vaga\n";
    echo "   2. Aprovar como gestor\n";
    echo "   3. Aprovar como aprovação extra\n";
    echo "   4. Verificar no banco de dados\n\n";

    echo "🔗 URLs de teste:\n";
    echo "   POST /planejamento/requisicao-vaga\n";
    echo "   PUT  /planejamento/requisicao-vaga/{id}/aprovar\n";
    echo "   PUT  /planejamento/requisicao-vaga/{id}/aprovarextra\n\n";
} else {
    echo "⚠️  Sistema de Aprovação Extra precisa de configuração\n\n";

    echo "📝 Execute o SQL script:\n";
    echo "   docs/SQL_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.sql\n\n";
}

echo "📚 Documentação disponível:\n";
echo "   - docs/IMPLEMENTACAO_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.md\n";
echo "   - docs/GUIA_TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.md\n";
echo "   - docs/EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA_REQUISICAO_VALOR.vue\n\n";

echo "========================================\n";
echo "FIM DOS TESTES\n";
echo "========================================\n\n";
