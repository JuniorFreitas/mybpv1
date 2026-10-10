<?php

/**
 * One-shot: funil Montisol 63122 (90d) — admitidos, candidaturas, treinamento, exames.
 * Uso: docker compose exec -T mybpdp php storage/app/relatorios/_gerar_funil_63122.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

$eid = 63122;
$dias = 90;
$agora = Carbon::now();
$desde = $agora->copy()->subDays($dias)->startOfDay();
$desdeD = $desde->toDateString();
$hoje = $agora->toDateString();

$cliente = DB::table('clientes')->where('id', $eid)->first();
$dir = storage_path('app/relatorios');
File::ensureDirectoryExists($dir);
$stamp = $agora->format('Ymd_His');
$prefix = "montisol_63122_funil_{$dias}d_{$stamp}";

$admBase = function () use ($eid) {
    return DB::table('admissoes as a')
        ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
        ->leftJoin('curriculos as c', 'c.id', '=', 'f.curriculo_id')
        ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
        ->leftJoin('vagas as v', 'v.id', '=', 'f.vaga_id')
        ->where('f.empresa_id', $eid)
        ->whereNull('a.deleted_at')
        ->whereNull('f.deleted_at');
};

$admitidos = $admBase()
    ->where('a.status', 'ADMITIDO')
    ->whereBetween('a.data_admissao', [$desdeD, $hoje])
    ->orderBy('a.data_admissao')
    ->select([
        'a.id as admissao_id',
        'a.feedback_id',
        'c.nome as colaborador',
        'c.cpf',
        'a.status',
        'a.tipo_admissao',
        'a.data_admissao',
        'a.funcao',
        'a.cargo',
        DB::raw('coalesce(cc.label, "") as centro_custo'),
        DB::raw('coalesce(v.nome, "") as vaga'),
        'a.created_at',
        'a.updated_at',
    ])
    ->get();

$candidaturasPorDia = DB::table('activity_log')
    ->where('log_name', 'Feedback')
    ->where('description', 'created')
    ->where('created_at', '>=', $desde)
    ->whereIn('subject_id', function ($q) use ($eid) {
        $q->select('id')->from('feedback_curriculos')->where('empresa_id', $eid)->whereNull('deleted_at');
    })
    ->selectRaw('date(created_at) d, count(*) q')
    ->groupBy('d')
    ->orderBy('d')
    ->get();
$candidaturasTotal = (int) $candidaturasPorDia->sum('q');

$curriculosNovos = (int) DB::table('activity_log as al')
    ->where('al.log_name', 'curriculo')
    ->where('al.description', 'created')
    ->where('al.created_at', '>=', $desde)
    ->whereIn('al.subject_id', function ($q) use ($eid) {
        $q->select('f.curriculo_id')->from('feedback_curriculos as f')->where('f.empresa_id', $eid)->whereNull('f.deleted_at');
    })
    ->count();

$treinamentos = DB::table('treinamentos as t')
    ->join('feedback_curriculos as f', 'f.id', '=', 't.feedback_id')
    ->leftJoin('curriculos as c', 'c.id', '=', 'f.curriculo_id')
    ->where('f.empresa_id', $eid)
    ->whereNull('f.deleted_at')
    ->where('t.created_at', '>=', $desde)
    ->orderBy('t.created_at')
    ->select(['t.id', 't.feedback_id', 't.tipo', 't.created_at', 'c.nome as colaborador', 't.data_envio', 't.enviado_email'])
    ->get();

$emTreinamentoSnap = [
    'PENDENTE TREINAMENTO' => (int) $admBase()->where('a.status', 'PENDENTE TREINAMENTO')->count(),
    'carteira_AGUARDANDO TREINAMENTO' => (int) $admBase()->where('a.status_carteira_treinamento', 'AGUARDANDO TREINAMENTO')->count(),
    'carteira_PENDENTE' => (int) $admBase()->where('a.status_carteira_treinamento', 'PENDENTE')->count(),
];

$examesEncaminhadosQtd = (int) DB::table('exame_funcionarios')
    ->where('empresa_id', $eid)
    ->where('created_at', '>=', $desde)
    ->count();

$exameSnap = (int) $admBase()->where('a.status', 'ENCAMINHADO EXAME')->count();
$examesRealizados = (int) DB::table('examesesmts')
    ->where('empresa_id', $eid)
    ->where('data_realizacao', '>=', $desdeD)
    ->where('exame_realizado', 1)
    ->count();

$cargos = $admitidos->groupBy(fn ($a) => $a->cargo ?: $a->funcao ?: 'Nao informado')->map->count()->sortDesc();
$meses = $admitidos->groupBy(fn ($a) => substr((string) $a->data_admissao, 0, 7))->map->count()->sortKeys();
$maxCargo = max(1, (int) ($cargos->first() ?: 1));

$writeCsv = function (string $path, array $rows): void {
    $fh = fopen($path, 'wb');
    fwrite($fh, "\xEF\xBB\xBF");
    foreach ($rows as $row) {
        $line = collect($row)->map(function ($v) {
            $v = $v === null ? '' : (string) $v;
            if (str_contains($v, ';') || str_contains($v, '"') || str_contains($v, "\n")) {
                return '"'.str_replace('"', '""', $v).'"';
            }

            return $v;
        })->implode(';');
        fwrite($fh, $line."\n");
    }
    fclose($fh);
};

$csvAdm = "{$dir}/{$prefix}_admitidos.csv";
$rows = [['admissao_id', 'feedback_id', 'colaborador', 'cpf', 'status', 'tipo_admissao', 'data_admissao', 'funcao', 'cargo', 'centro_custo', 'vaga', 'created_at']];
foreach ($admitidos as $a) {
    $rows[] = [
        $a->admissao_id, $a->feedback_id, $a->colaborador ?: 'Nao informado', $a->cpf ?: 'Nao informado',
        $a->status, $a->tipo_admissao, $a->data_admissao, $a->funcao ?: 'Nao informado',
        $a->cargo ?: 'Nao informado', $a->centro_custo ?: 'Nao informado', $a->vaga ?: 'Nao informado', $a->created_at,
    ];
}
$writeCsv($csvAdm, $rows);

$csvCand = "{$dir}/{$prefix}_candidaturas_por_dia.csv";
$rows = [['data', 'qtd_candidaturas_feedback']];
foreach ($candidaturasPorDia as $r) {
    $rows[] = [$r->d, $r->q];
}
$writeCsv($csvCand, $rows);

$csvTreino = "{$dir}/{$prefix}_treinamentos.csv";
$rows = [['id', 'feedback_id', 'colaborador', 'tipo', 'created_at', 'data_envio', 'enviado_email']];
foreach ($treinamentos as $t) {
    $rows[] = [
        $t->id, $t->feedback_id, $t->colaborador ?: 'Nao informado', $t->tipo,
        $t->created_at, $t->data_envio, $t->enviado_email ? 'Sim' : 'Nao',
    ];
}
$writeCsv($csvTreino, $rows);

$esc = fn ($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$conv = $candidaturasTotal > 0
    ? number_format(($admitidos->count() / $candidaturasTotal) * 100, 1, ',', '.')
    : '0';

ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Funil 90 dias — <?= $esc($cliente->razao_social) ?> (63122)</title>
  <style>
    :root { --bg:#f3f5f7; --surface:#fff; --ink:#1a2332; --muted:#5c6b7a; --line:#d8e0e8; --brand:#0d4f6c; --ok:#1b6b3a; --warn:#9a5b00; --info:#0b5f8a; --info-bg:#e6f3fa; --warn-bg:#fff4e0; --shadow:0 1px 2px rgba(26,35,50,.06),0 8px 24px rgba(26,35,50,.06); }
    * { box-sizing: border-box; }
    body { margin:0; font-family:"Segoe UI",Arial,sans-serif; color:var(--ink); background:radial-gradient(1100px 480px at 8% -10%,#d7e8ef,transparent 55%),var(--bg); line-height:1.45; }
    .wrap { max-width:1100px; margin:0 auto; padding:32px 20px 64px; }
    .hero { background:linear-gradient(135deg,#0d4f6c,#16627f 55%,#1f7a6e); color:#fff; border-radius:16px; padding:28px 32px; box-shadow:var(--shadow); margin-bottom:24px; }
    .hero h1 { margin:0 0 8px; font-size:28px; } .hero p { margin:0; opacity:.92; }
    .meta { display:flex; flex-wrap:wrap; gap:8px; margin-top:14px; }
    .chip { padding:6px 10px; border-radius:999px; font-size:12px; font-weight:600; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.22); }
    .callout { background:var(--info-bg); border:1px solid #b9d9ea; color:#0a3d58; border-radius:12px; padding:14px 16px; margin-bottom:20px; }
    .callout strong { display:block; margin-bottom:6px; color:var(--info); }
    .grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
    @media(max-width:900px){ .grid { grid-template-columns:repeat(2,1fr); } }
    .stat { background:var(--surface); border:1px solid var(--line); border-radius:12px; padding:16px; box-shadow:var(--shadow); }
    .stat .label { font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; }
    .stat .value { font-size:28px; font-weight:700; color:var(--brand); }
    .stat.ok .value { color:var(--ok); } .stat.warn .value { color:var(--warn); } .stat.info .value { color:var(--info); }
    section { background:var(--surface); border:1px solid var(--line); border-radius:16px; padding:22px 24px; box-shadow:var(--shadow); margin-bottom:18px; }
    section h2 { margin:0 0 4px; font-size:18px; color:var(--brand); }
    .sub { margin:0 0 14px; color:var(--muted); font-size:13px; }
    table { width:100%; border-collapse:collapse; font-size:13px; }
    th, td { padding:9px 10px; border-bottom:1px solid var(--line); text-align:left; vertical-align:top; }
    th { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:var(--muted); background:#f7fafc; }
    .two { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    @media(max-width:800px){ .two { grid-template-columns:1fr; } }
    .bar { display:grid; grid-template-columns:1fr 40px; gap:6px; align-items:center; margin-bottom:8px; font-size:12px; }
    .track { grid-column:1/-1; height:8px; background:#e8eef2; border-radius:999px; overflow:hidden; }
    .fill { height:100%; background:linear-gradient(90deg,#0d4f6c,#1f7a6e); }
    .val { font-weight:700; color:var(--brand); text-align:right; }
    .note { font-size:12px; color:var(--muted); }
    footer { text-align:center; color:var(--muted); font-size:12px; margin-top:8px; }
  </style>
</head>
<body>
<div class="wrap">
  <header class="hero">
    <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;opacity:.85;margin-bottom:8px">MyBPIN · Funil de vagas / admissão</div>
    <h1>Vagas preenchidas e pipeline — 90 dias</h1>
    <p><strong><?= $esc($cliente->razao_social) ?></strong> · cliente_id <strong>63122</strong> · apelido <strong>montisol</strong></p>
    <div class="meta">
      <span class="chip">Período: <?= $esc($desde->format('d/m/Y')) ?> → <?= $esc($agora->format('d/m/Y')) ?></span>
      <span class="chip">Gerado: <?= $esc($agora->format('d/m/Y H:i')) ?></span>
    </div>
  </header>

  <div class="callout">
    <strong>Critérios</strong>
    <div><b>Vagas preenchidas</b>: status ADMITIDO + data_admissao no período (até hoje).</div>
    <div><b>Candidaturas</b>: Feedback created no activity_log (timestamps de feedback_curriculos nulos neste cliente).</div>
    <div><b>Treinamento</b>: registros em treinamentos criados no período + snapshot de status/carteira.</div>
    <div><b>Exames</b>: snapshot ENCAMINHADO EXAME + exame_funcionarios criados no período.</div>
  </div>

  <div class="grid">
    <div class="stat ok"><div class="label">Vagas preenchidas (ADMITIDO)</div><div class="value"><?= number_format($admitidos->count(), 0, ',', '.') ?></div></div>
    <div class="stat info"><div class="label">Candidaturas entraram</div><div class="value"><?= number_format($candidaturasTotal, 0, ',', '.') ?></div></div>
    <div class="stat"><div class="label">Treinamentos no período</div><div class="value"><?= number_format($treinamentos->count(), 0, ',', '.') ?></div></div>
    <div class="stat warn"><div class="label">Em exame agora</div><div class="value"><?= number_format($exameSnap, 0, ',', '.') ?></div></div>
  </div>

  <section>
    <h2>1. Vagas preenchidas (ADMITIDO)</h2>
    <p class="sub"><?= $admitidos->count() ?> pessoas com data_admissao entre <?= $esc($desde->format('d/m/Y')) ?> e <?= $esc(Carbon::parse($hoje)->format('d/m/Y')) ?>. Lista no CSV (CPF omitido neste HTML).</p>
    <div class="two">
      <div>
        <h3 style="margin:0 0 10px;font-size:13px;color:var(--muted);text-transform:uppercase">Por mês</h3>
        <table><thead><tr><th>Mês</th><th>Qtd</th></tr></thead><tbody>
        <?php foreach ($meses as $m => $q): ?>
          <tr><td><?= $esc($m) ?></td><td><?= $q ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
      </div>
      <div>
        <h3 style="margin:0 0 10px;font-size:13px;color:var(--muted);text-transform:uppercase">Top cargos</h3>
        <?php foreach ($cargos->take(12) as $cargo => $q): ?>
          <?php $pct = max(4, (int) round(($q / $maxCargo) * 100)); ?>
          <div class="bar">
            <div><?= $esc($cargo) ?></div><div class="val"><?= $q ?></div>
            <div class="track"><div class="fill" style="width:<?= $pct ?>%"></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section>
    <h2>2. Candidaturas</h2>
    <p class="sub">Entraram <strong><?= number_format($candidaturasTotal, 0, ',', '.') ?></strong> candidaturas em <?= $candidaturasPorDia->count() ?> dias com movimento. Currículos novos: <strong><?= number_format($curriculosNovos, 0, ',', '.') ?></strong>.</p>
    <table>
      <thead><tr><th>Indicador</th><th>Qtd</th></tr></thead>
      <tbody>
        <tr><td>Candidaturas (Feedback · activity_log)</td><td><?= $candidaturasTotal ?></td></tr>
        <tr><td>Currículos criados (activity_log)</td><td><?= $curriculosNovos ?></td></tr>
        <tr><td>Dias com nova candidatura</td><td><?= $candidaturasPorDia->count() ?></td></tr>
        <tr><td>Conversão candidatura → ADMITIDO</td><td><?= $conv ?>%</td></tr>
      </tbody>
    </table>
    <p class="note" style="margin-top:10px">Neste cliente, feedback_curriculos.created_at/updated_at estão nulos (9687 registros). Contagem via activity_log.</p>
  </section>

  <section>
    <h2>3. Treinamento (últimos 90 dias)</h2>
    <div class="grid" style="margin-bottom:12px">
      <div class="stat"><div class="label">Treinamentos criados</div><div class="value" style="font-size:24px"><?= $treinamentos->count() ?></div></div>
      <div class="stat warn"><div class="label">Status PENDENTE TREINAMENTO</div><div class="value" style="font-size:24px"><?= $emTreinamentoSnap['PENDENTE TREINAMENTO'] ?></div></div>
      <div class="stat"><div class="label">Carteira AGUARDANDO</div><div class="value" style="font-size:24px"><?= $emTreinamentoSnap['carteira_AGUARDANDO TREINAMENTO'] ?></div></div>
      <div class="stat"><div class="label">Carteira PENDENTE</div><div class="value" style="font-size:24px"><?= $emTreinamentoSnap['carteira_PENDENTE'] ?></div></div>
    </div>
    <p class="sub">Snapshot “em treinamento” quase zerado. Volume do período: <?= $treinamentos->count() ?> registros na tabela treinamentos (tipo Fixo).</p>
  </section>

  <section>
    <h2>4. Exames</h2>
    <div class="grid" style="margin-bottom:12px">
      <div class="stat warn"><div class="label">ENCAMINHADO EXAME (agora)</div><div class="value" style="font-size:24px"><?= $exameSnap ?></div></div>
      <div class="stat info"><div class="label">Encaminhamentos criados 90d</div><div class="value" style="font-size:24px"><?= $examesEncaminhadosQtd ?></div></div>
      <div class="stat ok"><div class="label">Exames realizados 90d</div><div class="value" style="font-size:24px"><?= $examesRealizados ?></div></div>
      <div class="stat"><div class="label">Encaminhados − realizados*</div><div class="value" style="font-size:24px"><?= max(0, $examesEncaminhadosQtd - $examesRealizados) ?></div></div>
    </div>
    <p class="note">*aproximação entre exame_funcionarios (criados) e examesesmts (realizados no período).</p>
  </section>

  <section>
    <h2>Resumo</h2>
    <ul>
      <li><strong><?= $admitidos->count() ?></strong> vagas preenchidas (ADMITIDO) no período.</li>
      <li><strong><?= $candidaturasTotal ?></strong> candidaturas; conversão ≈ <strong><?= $conv ?>%</strong>.</li>
      <li><strong><?= $treinamentos->count() ?></strong> treinamentos registrados; snapshot em treinamento quase zerado.</li>
      <li><strong><?= $exameSnap ?></strong> em ENCAMINHADO EXAME agora; <strong><?= $examesEncaminhadosQtd ?></strong> encaminhamentos nos 90 dias.</li>
    </ul>
  </section>

  <section>
    <h2>Arquivos</h2>
    <p class="sub" style="font-family:ui-monospace,monospace;font-size:12px">
      <?= $esc(basename($csvAdm)) ?><br/>
      <?= $esc(basename($csvCand)) ?><br/>
      <?= $esc(basename($csvTreino)) ?>
    </p>
  </section>
  <footer>MyBPIN · Funil Montisol 63122 · CPF omitido no HTML</footer>
</div>
</body>
</html>
<?php
$html = ob_get_clean();
$htmlPath = "{$dir}/{$prefix}_relatorio.html";
File::put($htmlPath, $html);

echo "ADMITIDOS=".$admitidos->count()."\n";
echo "CANDIDATURAS={$candidaturasTotal}\n";
echo "CURRICULOS={$curriculosNovos}\n";
echo "TREINAMENTOS=".$treinamentos->count()."\n";
echo "EXAME_SNAP={$exameSnap}\n";
echo "EXAME_ENC={$examesEncaminhadosQtd}\n";
echo "EXAME_OK={$examesRealizados}\n";
echo "HTML={$htmlPath}\n";
echo "CSV_ADM={$csvAdm}\n";
echo "CSV_CAND={$csvCand}\n";
echo "CSV_TREINO={$csvTreino}\n";
