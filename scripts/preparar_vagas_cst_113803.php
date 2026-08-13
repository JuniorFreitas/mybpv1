<?php

/**
 * Prepara cargos (vagas) e vagas_abertas para importação CST empresa 113803.
 * - Lê nomes únicos de cod_vaga da planilha
 * - Cria Vaga se não existir (mesmo nome + empresa); se existir, reutiliza
 * - Cria VagasAbertas em São Luís (municipio_id=2743) se não existir; se existir, reutiliza
 *
 * Uso:
 *   docker compose exec mybpdp php scripts/preparar_vagas_cst_113803.php
 */

use App\Models\Municipio;
use App\Models\Vaga;
use App\Models\VagasAbertas;
use App\Services\Admissao\Importacao\LeitorPlanilhaAdmissao;
use App\Services\Admissao\Importacao\ResolvedorVagaAreaCentroCusto;
use Illuminate\Support\Facades\Auth;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

ini_set('memory_limit', '-1');

$empresaId = 113803;
$planilha = __DIR__ . '/importacao_cst_2026.xlsx';

$municipio = Municipio::query()
    ->where('uf', 'MA')
    ->where(function ($q) {
        $q->where('nome', 'São Luís')->orWhere('nome', 'São Luis');
    })
    ->first(['id', 'nome', 'uf']);

if (!$municipio) {
    fwrite(STDERR, "Município São Luís-MA não encontrado.\n");
    exit(1);
}

$municipioId = (int) $municipio->id;
echo "Empresa: {$empresaId}\n";
echo "Município: {$municipio->id} | {$municipio->nome}-{$municipio->uf}\n";

Auth::loginUsingId($empresaId);

$leitor = new LeitorPlanilhaAdmissao();
$nomes = [];
foreach ($leitor->ler($planilha, 100) as $chunk) {
    foreach ($chunk as $linha) {
        if (trim((string) ($linha['cpf'] ?? '')) === '') {
            continue;
        }
        $nome = trim((string) ($linha['cod_vaga'] ?? ''));
        if ($nome !== '') {
            $nomes[$nome] = true;
        }
    }
}

$nomes = array_keys($nomes);
sort($nomes, SORT_STRING | SORT_FLAG_CASE);
echo 'Cargos únicos na planilha: ' . count($nomes) . PHP_EOL;

$criadosCargo = 0;
$reusadosCargo = 0;
$criadosVa = 0;
$reusadosVa = 0;

foreach ($nomes as $i => $nome) {
    $cargo = Vaga::withoutGlobalScopes()
        ->where('empresa_id', $empresaId)
        ->where('nome', $nome)
        ->first();

    if ($cargo) {
        $reusadosCargo++;
        $cargoCriado = false;
    } else {
        $cargo = Vaga::create([
            'nome' => $nome,
            'empresa_id' => $empresaId,
            'ativo' => true,
        ]);
        $criadosCargo++;
        $cargoCriado = true;
    }

    $va = VagasAbertas::withoutGlobalScopes()
        ->where('empresa_id', $empresaId)
        ->where('vaga_id', $cargo->id)
        ->where('municipio_id', $municipioId)
        ->first();

    if ($va) {
        $reusadosVa++;
        $vaCriado = false;
        if (!$va->ativo) {
            $va->ativo = true;
            $va->ativo_sistema = true;
            $va->save();
        }
    } else {
        $va = VagasAbertas::create([
            'vaga_id' => $cargo->id,
            'municipio_id' => $municipioId,
            'empresa_id' => $empresaId,
            'titulo' => $nome,
            'descricao' => '',
            'ativo' => true,
            'ativo_sistema' => true,
        ]);
        $criadosVa++;
        $vaCriado = true;
    }

    $n = $i + 1;
    $flagCargo = $cargoCriado ? 'NOVO' : 'EXISTE';
    $flagVa = $vaCriado ? 'NOVA' : 'EXISTE';
    echo "{$n}. [{$flagCargo}] vaga_id={$cargo->id} | [{$flagVa}] va_id={$va->id} | {$nome}\n";
}

echo PHP_EOL . '=== RESUMO ===' . PHP_EOL;
echo "Cargos criados={$criadosCargo} reutilizados={$reusadosCargo}\n";
echo "Vagas abertas criadas={$criadosVa} reutilizadas={$reusadosVa}\n";

$resolvedor = new ResolvedorVagaAreaCentroCusto();
$ok = 0;
$fail = 0;
foreach ($nomes as $nome) {
    $r = $resolvedor->resolverVaga($empresaId, $nome);
    if ($r['erro'] !== null) {
        $fail++;
        echo "FAIL resolve [{$nome}]: {$r['erro']}\n";
    } else {
        $ok++;
    }
}
echo "Resolução pós-prep: ok={$ok} fail={$fail}\n";
