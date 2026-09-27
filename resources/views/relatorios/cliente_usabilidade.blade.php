<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Relatório {{ $r['dias'] }} dias — {{ $r['razao_social'] }} ({{ $r['empresa_id'] }})</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    :root {
      --bg: #f3f5f7; --surface: #fff; --ink: #1a2332; --muted: #5c6b7a; --line: #d8e0e8;
      --brand: #0d4f6c; --ok: #1b6b3a; --ok-bg: #e8f6ee; --warn: #9a5b00; --warn-bg: #fff4e0;
      --info: #0b5f8a; --info-bg: #e6f3fa;
      --shadow: 0 1px 2px rgba(26,35,50,.06), 0 8px 24px rgba(26,35,50,.06);
      --radius: 12px; --font: "Segoe UI","Helvetica Neue",Arial,sans-serif;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0; font-family: var(--font); color: var(--ink); line-height: 1.45;
      background: radial-gradient(1200px 500px at 10% -10%, #d7e8ef 0%, transparent 55%),
                  radial-gradient(900px 400px at 100% 0%, #e8efe6 0%, transparent 50%), var(--bg);
    }
    .wrap { max-width: 1100px; margin: 0 auto; padding: 32px 20px 64px; }
    header.hero {
      background: linear-gradient(135deg, #0d4f6c 0%, #16627f 55%, #1f7a6e 100%);
      color: #fff; border-radius: 16px; padding: 28px 32px; box-shadow: var(--shadow); margin-bottom: 24px;
    }
    .eyebrow { font-size: 12px; letter-spacing: .08em; text-transform: uppercase; opacity: .85; margin-bottom: 8px; }
    h1 { margin: 0 0 8px; font-size: 28px; }
    header.hero p { margin: 0; opacity: .92; max-width: 720px; }
    .meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    .chip {
      display: inline-flex; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;
      background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22);
    }
    .callout {
      background: var(--warn-bg); border: 1px solid #f0d7a8; color: #5c3a00;
      border-radius: var(--radius); padding: 14px 16px; margin-bottom: 24px;
    }
    .callout strong { display: block; margin-bottom: 4px; color: var(--warn); }
    .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
    @media (max-width: 900px) { .grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 520px) { .grid { grid-template-columns: 1fr; } }
    .stat {
      background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius);
      padding: 16px; box-shadow: var(--shadow);
    }
    .stat .label { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
    .stat .value { font-size: 28px; font-weight: 700; color: var(--brand); line-height: 1.1; }
    .stat.warn .value { color: var(--warn); }
    .stat.info .value { color: var(--info); }
    .stat.ok .value { color: var(--ok); }
    section {
      background: var(--surface); border: 1px solid var(--line); border-radius: 16px;
      padding: 22px 24px; box-shadow: var(--shadow); margin-bottom: 18px;
    }
    section h2 { margin: 0 0 4px; font-size: 18px; color: var(--brand); }
    section .sub { margin: 0 0 16px; color: var(--muted); font-size: 13px; }
    .two { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 800px) { .two { grid-template-columns: 1fr; } }
    .chart-box {
      background: #f8fafb; border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px 8px;
      min-height: 280px;
    }
    .chart-box h3 {
      margin: 0 0 8px; font-size: 13px; color: var(--muted); text-transform: uppercase; letter-spacing: .04em;
    }
    .chart-box.tall { min-height: 380px; }
    .chart-wrap { position: relative; height: 240px; }
    .chart-wrap.tall { height: 340px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th, td { text-align: left; padding: 10px; border-bottom: 1px solid var(--line); vertical-align: top; }
    th { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); background: #f7fafc; }
    tr:last-child td { border-bottom: none; }
    .pill {
      display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 700;
    }
    .pill.ok { background: var(--ok-bg); color: var(--ok); }
    .pill.warn { background: var(--warn-bg); color: var(--warn); }
    .pill.muted { background: #eef2f5; color: var(--muted); }
    footer { margin-top: 8px; color: var(--muted); font-size: 12px; text-align: center; }
    @media print {
      body { background: #fff; }
      .wrap { padding: 0; max-width: none; }
      header.hero, section, .stat { box-shadow: none; }
      .chart-box { break-inside: avoid; }
    }
  </style>
</head>
<body>
@php
  $palette = ['#0d4f6c','#1f7a6e','#0b5f8a','#9a5b00','#1b6b3a','#5c6b7a','#c45c26','#3d6b9a','#6b4f8a','#2a8f7a','#b35a5a','#4a7c59'];
  $short = function ($label, $max = 42) {
      $label = (string) $label;
      return mb_strlen($label) > $max ? mb_substr($label, 0, $max - 1).'…' : $label;
  };
  $isFilialReal = function ($nome) {
      $nome = trim((string) $nome);
      return $nome !== '' && !in_array($nome, ['Matriz', 'Nao informado', 'Não informado'], true);
  };
  $filiaisSnap = collect($r['filial_admitidos_ativos'] ?? [])->union(collect($r['filial_demitidos'] ?? []));
  $filiaisPeriodo = collect($r['filial_adm'] ?? [])->union(collect($r['filial_demitidos_periodo'] ?? []));
  $temFilial = $filiaisSnap->keys()->contains(fn ($n) => $isFilialReal($n))
      || $filiaisPeriodo->keys()->contains(fn ($n) => $isFilialReal($n));
  $chartTipoLabels = ($r['por_tipo'] ?? collect())->keys()->values()->all();
  $chartTipoData = ($r['por_tipo'] ?? collect())->values()->all();
  $chartFilialLabels = $temFilial ? ($r['filial_admitidos_ativos'] ?? collect())->keys()->values()->all() : [];
  $chartFilialData = $temFilial ? ($r['filial_admitidos_ativos'] ?? collect())->values()->all() : [];
  $ccSnap = ($r['cc_admitidos_ativos'] ?? collect())->take(12);
  $chartCcLabels = $ccSnap->keys()->map(fn ($l) => $short($l, 36))->values()->all();
  $chartCcData = $ccSnap->values()->all();
  $ccDem = ($r['cc_demitidos'] ?? collect())->take(12);
  $chartCcDemLabels = $ccDem->keys()->map(fn ($l) => $short($l, 36))->values()->all();
  $chartCcDemData = $ccDem->values()->all();
  $chartFilialDemLabels = $temFilial ? ($r['filial_demitidos'] ?? collect())->keys()->values()->all() : [];
  $chartFilialDemData = $temFilial ? ($r['filial_demitidos'] ?? collect())->values()->all() : [];
  $ccDemPeriodo = ($r['cc_demitidos_periodo'] ?? collect())->take(12);
  $chartCcDemPeriodoLabels = $ccDemPeriodo->keys()->map(fn ($l) => $short($l, 36))->values()->all();
  $chartCcDemPeriodoData = $ccDemPeriodo->values()->all();
  $cargos = ($r['cargos'] ?? collect())->take(10);
  $chartCargoLabels = $cargos->keys()->map(fn ($l) => $short($l, 28))->values()->all();
  $chartCargoData = $cargos->values()->all();
  $meses = $r['meses_adm'] ?? collect();
  $chartMesLabels = $meses->keys()->values()->all();
  $chartMesData = $meses->values()->all();
  $mods = ($r['top_modulos'] ?? collect())->take(10);
  $chartModLabels = $mods->map(fn ($m) => $short(($m->log_name ?: 'default').' · '.($m->description ?: ''), 32))->values()->all();
  $chartModData = $mods->map(fn ($m) => (int) $m->qtd)->values()->all();
@endphp
<div class="wrap">
  <header class="hero">
    <div class="eyebrow">MyBP · Usabilidade e Admissão</div>
    <h1>Relatório consolidado — {{ $r['dias'] }} dias</h1>
    <p>
      Cliente <strong>{{ $r['razao_social'] }}</strong>
      (empresa_id / cliente_id <strong>{{ $r['empresa_id'] }}</strong>@if(!empty($r['apelido'])), apelido <strong>{{ $r['apelido'] }}</strong>@endif).
      Escopo: acessos, uso (activity_log) e admissão.
    </p>
    <div class="meta">
      <span class="chip">Período: {{ $r['periodo_de'] }} → {{ $r['periodo_ate'] }}</span>
      <span class="chip">Gerado: {{ $r['gerado_em'] }}</span>
      <span class="chip">Fonte: users · activity_log · admissoes</span>
    </div>
  </header>

  <div class="callout">
    <strong>Leitura principal</strong>
    {{ $r['leitura'] }}
  </div>

  <div class="grid">
    <div class="stat">
      <div class="label">Usuários ativos</div>
      <div class="value">{{ number_format($r['usuarios_total'], 0, ',', '.') }}</div>
    </div>
    <div class="stat ok">
      <div class="label">ADMITIDO (snapshot)</div>
      <div class="value">{{ number_format($r['admitidos_ativos_total'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="stat warn">
      <div class="label">DEMITIDO (snapshot)</div>
      <div class="value">{{ number_format($r['demitidos_snapshot_total'] ?? $r['demitidos_total'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="stat info">
      <div class="label">DEMITIDO no período</div>
      <div class="value">{{ number_format($r['demitidos_periodo'] ?? 0, 0, ',', '.') }}</div>
    </div>
  </div>

  <div class="grid">
    <div class="stat warn">
      <div class="label">Logins no período</div>
      <div class="value" style="font-size:22px">{{ number_format($r['logins_periodo'], 0, ',', '.') }}</div>
    </div>
    <div class="stat info">
      <div class="label">Admissões criadas</div>
      <div class="value" style="font-size:22px">{{ number_format($r['admissoes_periodo'], 0, ',', '.') }}</div>
    </div>
    <div class="stat">
      <div class="label">Ações no activity_log</div>
      <div class="value" style="font-size:22px">{{ number_format($r['acoes_totais'], 0, ',', '.') }}</div>
    </div>
    <div class="stat">
      <div class="label">Treinamentos criados</div>
      <div class="value" style="font-size:22px">{{ number_format($r['treinamentos_criados_periodo'], 0, ',', '.') }}</div>
    </div>
  </div>

  <section>
    <h2>1. Acessos e perfil de usuários</h2>
    <p class="sub">Somente usuários <strong>ativos</strong> e sem soft delete (<code>ativo=1</code>, <code>deleted_at</code> nulo).</p>

    <div class="two" style="margin-bottom:16px">
      <div class="chart-box">
        <h3>Usuários ativos por tipo</h3>
        <div class="chart-wrap"><canvas id="chartTipo"></canvas></div>
      </div>
      <div class="chart-box">
        <h3>Resumo numérico</h3>
        <div class="grid" style="grid-template-columns:repeat(2,1fr);margin:0;gap:10px">
          @foreach($r['por_tipo'] as $tipo => $qtd)
            <div class="stat" style="box-shadow:none;padding:12px">
              <div class="label">{{ $tipo ?: 'Sem tipo' }}</div>
              <div class="value" style="font-size:20px">{{ number_format($qtd, 0, ',', '.') }}</div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Usuário</th>
          <th>Login</th>
          <th>Tipo</th>
          <th>Último acesso</th>
          <th>Acessou período</th>
          <th>Ações</th>
          <th>Janela de ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($r['usuarios_destaque'] as $u)
          <tr>
            <td>{{ $u->nome }}</td>
            <td>{{ $u->login }}</td>
            <td><span class="pill {{ $u->acoes_activity_log > 0 ? 'ok' : 'muted' }}">{{ $u->tipo ?: '—' }}</span></td>
            <td>{{ $u->ultimo_acesso ? \Carbon\Carbon::parse($u->ultimo_acesso)->format('d/m/Y H:i') : '—' }}</td>
            <td>
              @if($u->acessou_periodo)
                <span class="pill ok">Sim</span>
              @else
                <span class="pill muted">Não</span>
              @endif
            </td>
            <td>{{ number_format($u->acoes_activity_log, 0, ',', '.') }}</td>
            <td>
              @if($u->primeira_acao)
                {{ \Carbon\Carbon::parse($u->primeira_acao)->format('d/m H:i') }}
                → {{ \Carbon\Carbon::parse($u->ultima_acao)->format('d/m H:i') }}
              @else
                —
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" style="color:var(--muted)">Nenhum usuário com login ou ação no período.</td></tr>
        @endforelse
        @if($r['usuarios_total'] > count($r['usuarios_destaque']))
          <tr>
            <td colspan="7" style="color:var(--muted)">
              Demais {{ number_format($r['usuarios_total'] - count($r['usuarios_destaque']), 0, ',', '.') }} usuários
              sem destaque de uso no período (detalhe no CSV de usuários).
            </td>
          </tr>
        @endif
      </tbody>
    </table>
  </section>

  <section>
    <h2>2. Admissão (período)</h2>
    <p class="sub">
      Criadas no período: {{ number_format($r['admissoes_periodo'], 0, ',', '.') }} ·
      Totais da empresa: {{ number_format($r['admissoes_total_empresa'], 0, ',', '.') }} ·
      Desmobilizações: {{ number_format($r['desmobilizacoes_periodo'], 0, ',', '.') }} ·
      Treinamentos criados: {{ number_format($r['treinamentos_criados_periodo'], 0, ',', '.') }}.
      CPF omitido neste HTML.
    </p>

    <div class="grid" style="margin-bottom:16px">
      <div class="stat info">
        <div class="label">Criadas no período</div>
        <div class="value">{{ number_format($r['admissoes_periodo'], 0, ',', '.') }}</div>
      </div>
      @php $topStatus = $r['status_adm']->keys()->first(); @endphp
      <div class="stat ok">
        <div class="label">Status predominante</div>
        <div class="value" style="font-size:18px">{{ $topStatus ?: '—' }}</div>
      </div>
      @php $topTipo = $r['tipo_adm']->keys()->first(); @endphp
      <div class="stat">
        <div class="label">Tipo predominante</div>
        <div class="value" style="font-size:18px">{{ $topTipo ?: '—' }}</div>
      </div>
      <div class="stat warn">
        <div class="label">Desmobilizações</div>
        <div class="value">{{ number_format($r['desmobilizacoes_periodo'], 0, ',', '.') }}</div>
      </div>
    </div>

    <div class="two">
      <div class="chart-box">
        <h3>Top cargos (admissões do período)</h3>
        <div class="chart-wrap"><canvas id="chartCargos"></canvas></div>
      </div>
      <div class="chart-box">
        <h3>data_admissao por mês</h3>
        <div class="chart-wrap"><canvas id="chartMeses"></canvas></div>
      </div>
    </div>
  </section>

  <section>
    <h2>2.1 Centro de custo{{ $temFilial ? ' e filial' : '' }}</h2>
    <p class="sub">
      Quebra pela <strong>admissão</strong> (usuários não têm CC no cadastro).
      Snapshot = status ADMITIDO atual{{ $temFilial ? ' · Filial via cliente_filials.' : '.' }}
    </p>

    <div class="grid" style="margin-bottom:16px;{{ $temFilial ? '' : 'grid-template-columns:repeat(3,1fr);' }}">
      <div class="stat ok">
        <div class="label">ADMITIDO ativos (snapshot)</div>
        <div class="value">{{ number_format($r['admitidos_ativos_total'] ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="stat info">
        <div class="label">CCs no snapshot</div>
        <div class="value">{{ number_format(($r['cc_admitidos_ativos'] ?? collect())->count(), 0, ',', '.') }}</div>
      </div>
      @if($temFilial)
        <div class="stat">
          <div class="label">Filiais no snapshot</div>
          <div class="value">{{ number_format($filiaisSnap->keys()->filter(fn ($n) => $isFilialReal($n))->count(), 0, ',', '.') }}</div>
        </div>
      @endif
      <div class="stat warn">
        <div class="label">Admissões criadas no período</div>
        <div class="value">{{ number_format($r['admissoes_periodo'], 0, ',', '.') }}</div>
      </div>
    </div>

    <div class="{{ $temFilial ? 'two' : '' }}" style="margin-bottom:16px">
      <div class="chart-box tall">
        <h3>ADMITIDO ativos por centro de custo (top 12)</h3>
        <div class="chart-wrap tall"><canvas id="chartCc"></canvas></div>
      </div>
      @if($temFilial)
        <div class="chart-box">
          <h3>ADMITIDO ativos por filial</h3>
          <div class="chart-wrap"><canvas id="chartFilial"></canvas></div>
        </div>
      @endif
    </div>

    @if($temFilial)
      <h3 style="margin:8px 0 10px;font-size:14px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em">ADMITIDO ativos · CC × filial (top 30)</h3>
      <table>
        <thead><tr><th>Centro de custo</th><th>Filial</th><th>Qtd</th></tr></thead>
        <tbody>
          @forelse(($r['cc_filial_admitidos_ativos'] ?? collect()) as $row)
            <tr>
              <td>{{ $row->centro_custo }}</td>
              <td>{{ $row->filial }}</td>
              <td>{{ number_format($row->qtd, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr><td colspan="3" style="color:var(--muted)">—</td></tr>
          @endforelse
        </tbody>
      </table>
    @else
      <h3 style="margin:8px 0 10px;font-size:14px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em">ADMITIDO ativos por centro de custo (lista)</h3>
      <table>
        <thead><tr><th>Centro de custo</th><th>Qtd</th></tr></thead>
        <tbody>
          @forelse(($r['cc_admitidos_ativos'] ?? collect())->take(30) as $cc => $qtd)
            <tr>
              <td>{{ $cc }}</td>
              <td>{{ number_format($qtd, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr><td colspan="2" style="color:var(--muted)">—</td></tr>
          @endforelse
        </tbody>
      </table>
    @endif
  </section>

  <section>
    <h2>2.2 Demitidos (status DEMITIDO)</h2>
    <p class="sub">
      Snapshot = todos com status <strong>DEMITIDO</strong> (sem soft delete).
      Período = data_desmobilizacao/data_desmob na janela; se vazias, usa <code>updated_at</code> no período.
    </p>

    <div class="grid" style="margin-bottom:16px">
      <div class="stat warn">
        <div class="label">DEMITIDO total (snapshot)</div>
        <div class="value">{{ number_format($r['demitidos_snapshot_total'] ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="stat info">
        <div class="label">DEMITIDO no período</div>
        <div class="value">{{ number_format($r['demitidos_periodo'] ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="stat">
        <div class="label">CCs no snapshot</div>
        <div class="value">{{ number_format(($r['cc_demitidos'] ?? collect())->count(), 0, ',', '.') }}</div>
      </div>
      @if($temFilial)
        <div class="stat">
          <div class="label">Filiais (snapshot demitidos)</div>
          <div class="value">{{ number_format(($r['filial_demitidos'] ?? collect())->keys()->filter(fn ($n) => $isFilialReal($n))->count(), 0, ',', '.') }}</div>
        </div>
      @else
        <div class="stat">
          <div class="label">Desmobilizações (métrica)</div>
          <div class="value">{{ number_format($r['desmobilizacoes_periodo'] ?? 0, 0, ',', '.') }}</div>
        </div>
      @endif
    </div>

    <div class="{{ $temFilial ? 'two' : '' }}" style="margin-bottom:16px">
      <div class="chart-box tall">
        <h3>DEMITIDO snapshot por centro de custo (top 12)</h3>
        <div class="chart-wrap tall"><canvas id="chartCcDem"></canvas></div>
      </div>
      @if($temFilial)
        <div class="chart-box">
          <h3>DEMITIDO snapshot por filial</h3>
          <div class="chart-wrap"><canvas id="chartFilialDem"></canvas></div>
        </div>
      @endif
    </div>

    <div class="chart-box tall" style="margin-bottom:16px">
      <h3>DEMITIDO no período por centro de custo (top 12)</h3>
      <div class="chart-wrap tall"><canvas id="chartCcDemPeriodo"></canvas></div>
    </div>

    @if($temFilial)
      <h3 style="margin:8px 0 10px;font-size:14px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em">DEMITIDO snapshot · CC × filial (top 30)</h3>
      <table>
        <thead><tr><th>Centro de custo</th><th>Filial</th><th>Qtd</th></tr></thead>
        <tbody>
          @forelse(($r['cc_filial_demitidos'] ?? collect()) as $row)
            <tr>
              <td>{{ $row->centro_custo }}</td>
              <td>{{ $row->filial }}</td>
              <td>{{ number_format($row->qtd, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr><td colspan="3" style="color:var(--muted)">—</td></tr>
          @endforelse
        </tbody>
      </table>
    @endif
  </section>

  <section>
    <h2>3. Uso no sistema (activity_log)</h2>
    <p class="sub">Agrupado por usuário · módulo (log_name) · evento (description).</p>

    <div class="chart-box tall" style="margin-bottom:16px">
      <h3>Top módulos / eventos (90 dias)</h3>
      <div class="chart-wrap tall"><canvas id="chartModulos"></canvas></div>
    </div>

    <table>
      <thead>
        <tr><th>Usuário(s)</th><th>Módulo</th><th>Evento</th><th>Qtd</th></tr>
      </thead>
      <tbody>
        @forelse($r['top_modulos'] as $m)
          <tr>
            <td>{{ $m->usuarios }}</td>
            <td>{{ $m->log_name }}</td>
            <td>{{ $m->description }}</td>
            <td>{{ number_format($m->qtd, 0, ',', '.') }}</td>
          </tr>
        @empty
          <tr><td colspan="4" style="color:var(--muted)">Sem ações no período.</td></tr>
        @endforelse
      </tbody>
    </table>
  </section>

  <footer>
    MyBP · Relatório interno · cliente_id {{ $r['empresa_id'] }} · CPF omitido neste HTML (detalhe nos CSVs).
  </footer>
</div>

<script>
const PALETTE = @json($palette);
const chartData = {
  tipo: { labels: @json($chartTipoLabels), data: @json($chartTipoData) },
  filial: { labels: @json($chartFilialLabels), data: @json($chartFilialData) },
  cc: { labels: @json($chartCcLabels), data: @json($chartCcData) },
  ccDem: { labels: @json($chartCcDemLabels), data: @json($chartCcDemData) },
  filialDem: { labels: @json($chartFilialDemLabels), data: @json($chartFilialDemData) },
  ccDemPeriodo: { labels: @json($chartCcDemPeriodoLabels), data: @json($chartCcDemPeriodoData) },
  cargos: { labels: @json($chartCargoLabels), data: @json($chartCargoData) },
  meses: { labels: @json($chartMesLabels), data: @json($chartMesData) },
  modulos: { labels: @json($chartModLabels), data: @json($chartModData) },
};

function colors(n) {
  return Array.from({ length: n }, (_, i) => PALETTE[i % PALETTE.length]);
}

function doughnut(id, labels, data) {
  const el = document.getElementById(id);
  if (!el || !labels.length) return;
  new Chart(el, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{ data, backgroundColor: colors(labels.length), borderWidth: 0 }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
      }
    }
  });
}

function hBar(id, labels, data, color) {
  const el = document.getElementById(id);
  if (!el || !labels.length) return;
  new Chart(el, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: color || '#0d4f6c',
        borderRadius: 6,
        barThickness: 16
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: '#e8eef2' }, ticks: { precision: 0 } },
        y: { grid: { display: false }, ticks: { font: { size: 10 } } }
      }
    }
  });
}

function vBar(id, labels, data, color) {
  const el = document.getElementById(id);
  if (!el || !labels.length) return;
  new Chart(el, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: color || '#1f7a6e',
        borderRadius: 6,
        maxBarThickness: 42
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#e8eef2' }, ticks: { precision: 0 } },
        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
      }
    }
  });
}

doughnut('chartTipo', chartData.tipo.labels, chartData.tipo.data);
if (chartData.filial.labels.length) {
  doughnut('chartFilial', chartData.filial.labels, chartData.filial.data);
}
if (chartData.filialDem.labels.length) {
  doughnut('chartFilialDem', chartData.filialDem.labels, chartData.filialDem.data);
}
hBar('chartCc', chartData.cc.labels, chartData.cc.data, '#0d4f6c');
hBar('chartCcDem', chartData.ccDem.labels, chartData.ccDem.data, '#9a5b00');
hBar('chartCcDemPeriodo', chartData.ccDemPeriodo.labels, chartData.ccDemPeriodo.data, '#c45c26');
hBar('chartCargos', chartData.cargos.labels, chartData.cargos.data, '#0b5f8a');
vBar('chartMeses', chartData.meses.labels, chartData.meses.data, '#1f7a6e');
hBar('chartModulos', chartData.modulos.labels, chartData.modulos.data, '#5c6b7a');
</script>
</body>
</html>
