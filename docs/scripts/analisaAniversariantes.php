<?php

// Script para analisar se todos os clientes disparam notificações de aniversariante

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n========================================\n";
echo "ANÁLISE DE DISPARO DE ANIVERSARIANTES\n";
echo "========================================\n\n";

// 1. Listar todos os clientes
$clientes = DB::table('clientes')->select('id', 'nome_fantasia', 'razao_social', 'ativo')->get();

echo "TOTAL DE CLIENTES NO SISTEMA: " . $clientes->count() . "\n\n";

foreach ($clientes as $cliente) {
    echo "┌─────────────────────────────────────────────────────────────────\n";
    echo "│ ID: {$cliente->id} | Nome: {$cliente->nome_fantasia}\n";
    echo "│ Razão Social: {$cliente->razao_social}\n";
    echo "│ Ativo: " . ($cliente->ativo ? 'SIM ✓' : 'NÃO ✗') . "\n";
    
    // Verificar requisitos para disparo
    $motivos = [];
    
    // 1. Empresa deve estar ativa
    if (!$cliente->ativo) {
        $motivos[] = "❌ EMPRESA INATIVA";
    }
    
    // 2. Deve ter pelo menos 1 funcionário admitido
    $funcionariosAdmitidos = DB::table('curriculos')
        ->select('c.id')
        ->from('curriculos as c')
        ->join('feedback_curriculos as fc', 'c.id', '=', 'fc.curriculo_id')
        ->join('users as u', 'c.id', '=', 'u.id')
        ->join('admissoes as a', 'fc.id', '=', 'a.feedback_id')
        ->where('u.empresa_id', $cliente->id)
        ->where('a.status', 'Admitido')
        ->whereNull('c.deleted_at')
        ->whereNull('fc.deleted_at')
        ->distinct()
        ->count();
    
    echo "│ Funcionários admitidos: {$funcionariosAdmitidos}\n";
    
    if ($funcionariosAdmitidos === 0) {
        $motivos[] = "❌ NENHUM FUNCIONÁRIO ADMITIDO";
    }
    
    // 3. Deve ter pelo menos 1 funcionário com data de nascimento no mês atual
    $aniversariantesEsteMes = DB::table('curriculos')
        ->select('c.id')
        ->from('curriculos as c')
        ->join('feedback_curriculos as fc', 'c.id', '=', 'fc.curriculo_id')
        ->join('users as u', 'c.id', '=', 'u.id')
        ->join('admissoes as a', 'fc.id', '=', 'a.feedback_id')
        ->where('u.empresa_id', $cliente->id)
        ->where('a.status', 'Admitido')
        ->whereRaw('MONTH(c.nascimento) = MONTH(NOW())')
        ->whereNull('c.deleted_at')
        ->whereNull('fc.deleted_at')
        ->distinct()
        ->count();
    
    echo "│ Aniversariantes este mês: {$aniversariantesEsteMes}\n";
    
    if ($aniversariantesEsteMes === 0) {
        $motivos[] = "⚠️  NENHUM ANIVERSARIANTE ESTE MÊS";
    }
    
    // 4. Verificar se há aniversariante HOJE
    $aniversarianteHoje = DB::table('curriculos')
        ->select('c.id', 'c.nome', 'c.email')
        ->from('curriculos as c')
        ->join('feedback_curriculos as fc', 'c.id', '=', 'fc.curriculo_id')
        ->join('users as u', 'c.id', '=', 'u.id')
        ->join('admissoes as a', 'fc.id', '=', 'a.feedback_id')
        ->where('u.empresa_id', $cliente->id)
        ->where('a.status', 'Admitido')
        ->whereRaw('MONTH(c.nascimento) = MONTH(NOW())')
        ->whereRaw('DAY(c.nascimento) = DAY(NOW())')
        ->whereNull('c.deleted_at')
        ->whereNull('fc.deleted_at')
        ->get();
    
    if ($aniversarianteHoje->count() > 0) {
        echo "│ ✅ ANIVERSARIANTE HOJE:\n";
        foreach ($aniversarianteHoje as $aniv) {
            echo "│    - {$aniv->nome} ({$aniv->email})\n";
        }
    } else {
        echo "│ Nenhum aniversariante hoje\n";
    }
    
    // Verificar se foi removido da demissão
    $demitidos = DB::table('curriculos')
        ->select('c.id')
        ->from('curriculos as c')
        ->join('feedback_curriculos as fc', 'c.id', '=', 'fc.curriculo_id')
        ->join('demissaos as d', 'fc.id', '=', 'd.feedback_id')
        ->join('users as u', 'c.id', '=', 'u.id')
        ->where('u.empresa_id', $cliente->id)
        ->count();
    
    echo "│ Funcionários demitidos: {$demitidos}\n";
    
    // Exibir motivos
    if (!empty($motivos)) {
        echo "│\n";
        echo "│ ❌ MOTIVOS PELOS QUAIS NÃO DISPARA:\n";
        foreach ($motivos as $motivo) {
            echo "│    {$motivo}\n";
        }
    } else {
        echo "│\n";
        echo "│ ✅ CLIENTE HABILITADO PARA DISPARO\n";
    }
    
    echo "└─────────────────────────────────────────────────────────────────\n\n";
}

echo "\n========================================\n";
echo "FIM DA ANÁLISE\n";
echo "========================================\n\n";
