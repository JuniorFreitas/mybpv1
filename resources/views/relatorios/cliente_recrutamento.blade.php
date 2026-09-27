<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Recrutamento {{ $r['dias'] }}d — {{ $r['razao_social'] }} ({{ $r['empresa_id'] }})</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    :root {
      --bg:#f3f5f7; --surface:#fff; --ink:#1a2332; --muted:#5c6b7a; --line:#d8e0e8;
      --brand:#0d4f6c; --ok:#1b6b3a; --warn:#9a5b00; --info:#0b5f8a; --warn-bg:#fff4e0;
      --shadow:0 1px 2px rgba(26,35,50,.06),0 8px 24px rgba(26,35,50,.06);
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:"Segoe UI",Arial,sans-serif;color:var(--ink);line-height:1.45;
      background:radial-gradient(1100px 480px at 8% -10%,#d7e8ef,transparent 55%),var(--bg)}
    .wrap{max-width:1100px;margin:0 auto;padding:32px 20px 64px}
    .hero{background:linear-gradient(135deg,#0d4f6c,#16627f 55%,#1f7a6e);color:#fff;border-radius:16px;padding:28px 32px;box-shadow:var(--shadow);margin-bottom:24px}
    .hero h1{margin:0 0 8px;font-size:28px}.hero p{margin:0;opacity:.92}
    .meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
    .chip{padding:6px 10px;border-radius:999px;font-size:12px;font-weight:600;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22)}
    .callout{background:var(--warn-bg);border:1px solid #f0d7a8;color:#5c3a00;border-radius:12px;padding:14px 16px;margin-bottom:20px}
    .callout strong{display:block;margin-bottom:4px;color:var(--warn)}
    .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
    @media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}}
    .stat{background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:16px;box-shadow:var(--shadow)}
    .stat .label{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px}
    .stat .value{font-size:28px;font-weight:700;color:var(--brand)}
    .stat.ok .value{color:var(--ok)}.stat.warn .value{color:var(--warn)}.stat.info .value{color:var(--info)}
    section{background:var(--surface);border:1px solid var(--line);border-radius:16px;padding:22px 24px;box-shadow:var(--shadow);margin-bottom:18px}
    section h2{margin:0 0 4px;font-size:18px;color:var(--brand)}
    .sub{margin:0 0 14px;color:var(--muted);font-size:13px}
    .two{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    @media(max-width:800px){.two{grid-template-columns:1fr}}
    .chart-box{background:#f8fafb;border:1px solid var(--line);border-radius:12px;padding:14px 16px 8px;min-height:280px}
    .chart-box h3{margin:0 0 8px;font-size:13px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}
    .chart-wrap{position:relative;height:240px}.chart-wrap.tall{height:320px}.chart-box.tall{min-height:360px}
    table{width:100%;border-collapse:collapse;font-size:13px}
    th,td{padding:9px 10px;border-bottom:1px solid var(--line);text-align:left;vertical-align:top}
    th{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);background:#f7fafc}
    footer{text-align:center;color:var(--muted);font-size:12px;margin-top:8px}
  </style>
</head>
<body>
@php
  $palette = ['#0d4f6c','#1f7a6e','#0b5f8a','#9a5b00','#1b6b3a','#5c6b7a','#c45c26','#3d6b9a','#6b4f8a','#2a8f7a'];
  $temFilial = (bool) ($r['tem_filial'] ?? false);
  $statusLabels = ($r['status_snapshot'] ?? collect())->pluck('status')->values()->all();
  $statusData = ($r['status_snapshot'] ?? collect())->pluck('total')->values()->all();
  $statusMatriz = ($r['status_snapshot'] ?? collect())->pluck('matriz')->values()->all();
  $statusFilial = ($r['status_snapshot'] ?? collect())->pluck('filial')->values()->all();
  $outros = $r['outros_status'] ?? collect();
  $funilLabels = ['Currículos','Candidaturas','Vagas criadas','Vagas preenchidas'];
  $funilData = [
    (int) $r['curriculos_periodo'],
    (int) $r['candidaturas_periodo'],
    (int) $r['vagas_abertas_criadas'],
    (int) $r['vagas_preenchidas_periodo'],
  ];
@endphp
<div class="wrap">
  <header class="hero">
    <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;opacity:.85;margin-bottom:8px">MyBP · Recrutamento e Admissão</div>
    <h1>Relatório de recrutamento — {{ $r['dias'] }} dias</h1>
    <p>
      <strong>{{ $r['razao_social'] }}</strong> · cliente_id <strong>{{ $r['empresa_id'] }}</strong>
      @if(!empty($r['apelido'])) · apelido <strong>{{ $r['apelido'] }}</strong>@endif
    </p>
    <div class="meta">
      <span class="chip">Período: {{ $r['periodo_de'] }} → {{ $r['periodo_ate'] }}</span>
      <span class="chip">Gerado: {{ $r['gerado_em'] }}</span>
      <span class="chip">{{ $temFilial ? 'Com quebra Matriz / Filial' : 'Somente Matriz (sem filial)' }}</span>
    </div>
  </header>

  <div class="callout">
    <strong>Critérios</strong>
    Currículos/candidaturas via activity_log · Vagas abertas = registros criados em <code>vagas_abertas</code> ·
    Preenchidas = ADMITIDO com <code>data_admissao</code> no período · Demitidos no período = DEMITIDO com data de desmobilização ou <code>updated_at</code>.
  </div>

  <div class="grid">
    <div class="stat info"><div class="label">Currículos cadastrados</div><div class="value">{{ number_format($r['curriculos_periodo'], 0, ',', '.') }}</div></div>
    <div class="stat"><div class="label">Vagas abertas criadas</div><div class="value">{{ number_format($r['vagas_abertas_criadas'], 0, ',', '.') }}</div></div>
    <div class="stat ok"><div class="label">Vagas preenchidas (ADMITIDO)</div><div class="value">{{ number_format($r['vagas_preenchidas_periodo'], 0, ',', '.') }}</div></div>
    <div class="stat warn"><div class="label">DEMITIDO no período</div><div class="value">{{ number_format($r['demitidos_periodo']['total'], 0, ',', '.') }}</div></div>
  </div>

  <section>
    <h2>1. Funil do período</h2>
    <p class="sub">
      Candidaturas (Feedback): {{ number_format($r['candidaturas_periodo'], 0, ',', '.') }} ·
      Vagas ativas agora: {{ number_format($r['vagas_abertas_ativas'], 0, ',', '.') }} ·
      Funções distintas preenchidas: {{ number_format($r['vagas_preenchidas_distintas'], 0, ',', '.') }}.
    </p>
    <div class="chart-box" style="margin-bottom:12px">
      <h3>Volume no período</h3>
      <div class="chart-wrap"><canvas id="chartFunil"></canvas></div>
    </div>
    @if(($r['vagas_criadas_lista'] ?? collect())->isNotEmpty())
      <h3 style="margin:12px 0 8px;font-size:13px;color:var(--muted);text-transform:uppercase">Vagas abertas criadas no período</h3>
      <table>
        <thead><tr><th>Título</th><th>Vaga</th><th>Ativa</th><th>Criada em</th></tr></thead>
        <tbody>
          @foreach($r['vagas_criadas_lista'] as $v)
            <tr>
              <td>{{ $v->titulo ?: '—' }}</td>
              <td>{{ $v->vaga_nome ?: '—' }}</td>
              <td>{{ $v->ativo ? 'Sim' : 'Não' }}</td>
              <td>{{ $v->created_at ? \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') : '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </section>

  <section>
    <h2>2. ADMITIDO e DEMITIDO</h2>
    <div class="grid" style="margin-bottom:16px">
      <div class="stat ok">
        <div class="label">ADMITIDO snapshot</div>
        <div class="value">{{ number_format($r['admitidos_snapshot']->total, 0, ',', '.') }}</div>
      </div>
      <div class="stat warn">
        <div class="label">DEMITIDO snapshot</div>
        <div class="value">{{ number_format($r['demitidos_snapshot']->total, 0, ',', '.') }}</div>
      </div>
      <div class="stat info">
        <div class="label">ADMITIDO no período</div>
        <div class="value">{{ number_format($r['admitidos_periodo']['total'], 0, ',', '.') }}</div>
      </div>
      <div class="stat">
        <div class="label">DEMITIDO no período</div>
        <div class="value">{{ number_format($r['demitidos_periodo']['total'], 0, ',', '.') }}</div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Indicador</th>
          <th>Matriz</th>
          @if($temFilial)<th>Filial</th>@endif
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>ADMITIDO (snapshot)</td>
          <td>{{ number_format($r['admitidos_snapshot']->matriz, 0, ',', '.') }}</td>
          @if($temFilial)<td>{{ number_format($r['admitidos_snapshot']->filial, 0, ',', '.') }}</td>@endif
          <td>{{ number_format($r['admitidos_snapshot']->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
          <td>ADMITIDO (período · data_admissao)</td>
          <td>{{ number_format($r['admitidos_periodo']['matriz'], 0, ',', '.') }}</td>
          @if($temFilial)<td>{{ number_format($r['admitidos_periodo']['filial'], 0, ',', '.') }}</td>@endif
          <td>{{ number_format($r['admitidos_periodo']['total'], 0, ',', '.') }}</td>
        </tr>
        <tr>
          <td>DEMITIDO (snapshot)</td>
          <td>{{ number_format($r['demitidos_snapshot']->matriz, 0, ',', '.') }}</td>
          @if($temFilial)<td>{{ number_format($r['demitidos_snapshot']->filial, 0, ',', '.') }}</td>@endif
          <td>{{ number_format($r['demitidos_snapshot']->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
          <td>DEMITIDO (período)</td>
          <td>{{ number_format($r['demitidos_periodo']['matriz'], 0, ',', '.') }}</td>
          @if($temFilial)<td>{{ number_format($r['demitidos_periodo']['filial'], 0, ',', '.') }}</td>@endif
          <td>{{ number_format($r['demitidos_periodo']['total'], 0, ',', '.') }}</td>
        </tr>
      </tbody>
    </table>

    @if($temFilial)
      <div class="two" style="margin-top:16px">
        <div class="chart-box">
          <h3>ADMITIDO snapshot · Matriz × Filial</h3>
          <div class="chart-wrap"><canvas id="chartAdmMf"></canvas></div>
        </div>
        <div class="chart-box">
          <h3>DEMITIDO snapshot · Matriz × Filial</h3>
          <div class="chart-wrap"><canvas id="chartDemMf"></canvas></div>
        </div>
      </div>
    @endif
  </section>

  <section>
    <h2>3. Demais status (snapshot)</h2>
    <p class="sub">Todos os status de admissão atuais, com quebra Matriz/Filial quando houver.</p>
    <div class="two" style="margin-bottom:16px">
      <div class="chart-box tall">
        <h3>Todos os status</h3>
        <div class="chart-wrap tall"><canvas id="chartStatus"></canvas></div>
      </div>
      <div class="chart-box tall">
        <h3>Outros status (exceto ADMITIDO/DEMITIDO)</h3>
        <div class="chart-wrap tall"><canvas id="chartOutros"></canvas></div>
      </div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Status</th>
          <th>Matriz</th>
          @if($temFilial)<th>Filial</th>@endif
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($r['status_snapshot'] as $row)
          <tr>
            <td>{{ $row->status }}</td>
            <td>{{ number_format($row->matriz, 0, ',', '.') }}</td>
            @if($temFilial)<td>{{ number_format($row->filial, 0, ',', '.') }}</td>@endif
            <td>{{ number_format($row->total, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </section>

  <footer>MyBP · Relatório de recrutamento · cliente_id {{ $r['empresa_id'] }}</footer>
</div>

<script>
const PALETTE = @json($palette);
const temFilial = @json($temFilial);
const chartData = {
  funil: { labels: @json($funilLabels), data: @json($funilData) },
  status: { labels: @json($statusLabels), data: @json($statusData), matriz: @json($statusMatriz), filial: @json($statusFilial) },
  outros: {
    labels: @json($outros->pluck('status')->values()->all()),
    data: @json($outros->pluck('total')->values()->all())
  },
  admMf: {
    labels: ['Matriz','Filial'],
    data: [{{ (int)$r['admitidos_snapshot']->matriz }}, {{ (int)$r['admitidos_snapshot']->filial }}]
  },
  demMf: {
    labels: ['Matriz','Filial'],
    data: [{{ (int)$r['demitidos_snapshot']->matriz }}, {{ (int)$r['demitidos_snapshot']->filial }}]
  }
};

function colors(n){ return Array.from({length:n},(_,i)=>PALETTE[i%PALETTE.length]); }

function doughnut(id, labels, data) {
  const el = document.getElementById(id);
  if (!el || !labels.length) return;
  new Chart(el, {
    type: 'doughnut',
    data: { labels, datasets: [{ data, backgroundColor: colors(labels.length), borderWidth: 0 }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'right', labels:{ boxWidth:12, font:{ size:11 } } } } }
  });
}

function vBar(id, labels, data, color) {
  const el = document.getElementById(id);
  if (!el || !labels.length) return;
  new Chart(el, {
    type: 'bar',
    data: { labels, datasets: [{ data, backgroundColor: color || '#0d4f6c', borderRadius: 6, maxBarThickness: 48 }] },
    options: {
      responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } },
      scales:{ y:{ beginAtZero:true, ticks:{ precision:0 }, grid:{ color:'#e8eef2' } }, x:{ grid:{ display:false } } }
    }
  });
}

function hBar(id, labels, data, color) {
  const el = document.getElementById(id);
  if (!el || !labels.length) return;
  new Chart(el, {
    type: 'bar',
    data: { labels, datasets: [{ data, backgroundColor: color || '#0d4f6c', borderRadius: 6, barThickness: 16 }] },
    options: {
      indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } },
      scales:{ x:{ ticks:{ precision:0 }, grid:{ color:'#e8eef2' } }, y:{ grid:{ display:false }, ticks:{ font:{ size:10 } } } }
    }
  });
}

function stackedStatus(id) {
  const el = document.getElementById(id);
  if (!el || !chartData.status.labels.length) return;
  const datasets = temFilial
    ? [
        { label:'Matriz', data: chartData.status.matriz, backgroundColor:'#0d4f6c', borderRadius:4 },
        { label:'Filial', data: chartData.status.filial, backgroundColor:'#1f7a6e', borderRadius:4 }
      ]
    : [{ label:'Total', data: chartData.status.data, backgroundColor:'#0d4f6c', borderRadius:4 }];
  new Chart(el, {
    type: 'bar',
    data: { labels: chartData.status.labels, datasets },
    options: {
      indexAxis:'y', responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ position:'bottom', labels:{ boxWidth:12, font:{ size:11 } } } },
      scales:{
        x:{ stacked: temFilial, ticks:{ precision:0 }, grid:{ color:'#e8eef2' } },
        y:{ stacked: temFilial, grid:{ display:false }, ticks:{ font:{ size:10 } } }
      }
    }
  });
}

vBar('chartFunil', chartData.funil.labels, chartData.funil.data, '#0b5f8a');
stackedStatus('chartStatus');
hBar('chartOutros', chartData.outros.labels, chartData.outros.data, '#9a5b00');
if (temFilial) {
  doughnut('chartAdmMf', chartData.admMf.labels, chartData.admMf.data);
  doughnut('chartDemMf', chartData.demMf.labels, chartData.demMf.data);
}
</script>
</body>
</html>
