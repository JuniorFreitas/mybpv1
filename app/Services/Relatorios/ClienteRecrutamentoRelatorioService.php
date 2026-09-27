<?php

namespace App\Services\Relatorios;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Relatório de recrutamento: currículos, vagas, admitidos, demais status e demitidos
 * com quebra Matriz / Filial.
 */
class ClienteRecrutamentoRelatorioService
{
    public function gerar(int $empresaId, int $dias = 90, ?string $outputDir = null): array
    {
        $dias = max(1, min(3650, $dias));
        $agora = Carbon::now();
        $desde = $agora->copy()->subDays($dias)->startOfDay();
        $cliente = $this->resolverCliente($empresaId);

        $dir = $outputDir ?: storage_path('app/relatorios');
        File::ensureDirectoryExists($dir);

        $slug = Str::slug((string) ($cliente->apelido ?: $cliente->razao_social ?: 'cliente'), '_');
        $stamp = $agora->format('Ymd_His');
        $prefix = sprintf('%s_%d_recrutamento', $slug !== '' ? $slug : 'cliente', $empresaId);

        $dados = $this->coletar($empresaId, $desde, $agora);
        $resumo = $this->montarResumo($cliente, $empresaId, $dias, $desde, $agora, $dados);

        $csvStatus = "{$dir}/{$prefix}_{$dias}d_{$stamp}_status_matriz_filial.csv";
        $csvVagas = "{$dir}/{$prefix}_{$dias}d_{$stamp}_vagas.csv";
        $htmlPath = "{$dir}/{$prefix}_{$dias}d_{$stamp}.html";

        $this->escreverCsv($csvStatus, $this->linhasStatusCsv($dados));
        $this->escreverCsv($csvVagas, $this->linhasVagasCsv($dados));
        File::put($htmlPath, view('relatorios.cliente_recrutamento', ['r' => $resumo])->render());

        return [
            'empresa_id' => $empresaId,
            'dias' => $dias,
            'resumo' => $resumo,
            'arquivos' => [
                'status' => $csvStatus,
                'vagas' => $csvVagas,
                'html' => $htmlPath,
            ],
        ];
    }

    private function resolverCliente(int $empresaId): object
    {
        $cliente = DB::table('clientes')->where('id', $empresaId)->first();
        if (!$cliente) {
            throw new \InvalidArgumentException("Cliente/empresa_id {$empresaId} não encontrado em clientes.");
        }

        return $cliente;
    }

    private function coletar(int $empresaId, Carbon $desde, Carbon $agora): array
    {
        $desdeData = $desde->toDateString();
        $hoje = $agora->toDateString();

        $curriculos = (int) DB::table('activity_log as al')
            ->where('al.log_name', 'curriculo')
            ->where('al.description', 'created')
            ->where('al.created_at', '>=', $desde)
            ->whereIn('al.subject_id', function ($q) use ($empresaId) {
                $q->select('f.curriculo_id')
                    ->from('feedback_curriculos as f')
                    ->where('f.empresa_id', $empresaId)
                    ->whereNull('f.deleted_at');
            })
            ->count();

        $candidaturas = (int) DB::table('activity_log')
            ->where('log_name', 'Feedback')
            ->where('description', 'created')
            ->where('created_at', '>=', $desde)
            ->whereIn('subject_id', function ($q) use ($empresaId) {
                $q->select('id')
                    ->from('feedback_curriculos')
                    ->where('empresa_id', $empresaId)
                    ->whereNull('deleted_at');
            })
            ->count();

        $vagasAbertasCriadas = (int) DB::table('vagas_abertas')
            ->where('empresa_id', $empresaId)
            ->where('created_at', '>=', $desde)
            ->count();

        $vagasAbertasAtivas = (int) DB::table('vagas_abertas')
            ->where('empresa_id', $empresaId)
            ->where('ativo', 1)
            ->count();

        $vagasAbertasTotal = (int) DB::table('vagas_abertas')
            ->where('empresa_id', $empresaId)
            ->count();

        $vagasCriadasLista = DB::table('vagas_abertas as va')
            ->leftJoin('vagas as v', 'v.id', '=', 'va.vaga_id')
            ->where('va.empresa_id', $empresaId)
            ->where('va.created_at', '>=', $desde)
            ->orderByDesc('va.created_at')
            ->select([
                'va.id',
                'va.titulo',
                'va.ativo',
                'va.created_at',
                DB::raw('coalesce(v.nome, "") as vaga_nome'),
            ])
            ->get();

        // Preenchidas no período = ADMITIDO com data_admissao na janela
        $vagasPreenchidas = (int) $this->baseAdmissao($empresaId)
            ->where('a.status', 'ADMITIDO')
            ->whereBetween('a.data_admissao', [$desdeData, $hoje])
            ->count();

        $vagasPreenchidasDistintas = (int) $this->baseAdmissao($empresaId)
            ->where('a.status', 'ADMITIDO')
            ->whereBetween('a.data_admissao', [$desdeData, $hoje])
            ->whereNotNull('f.vaga_id')
            ->selectRaw('count(distinct f.vaga_id) as q')
            ->value('q');

        $statusSnapshot = $this->agregarStatusMatrizFilial($empresaId);
        $admitidosPeriodoMf = $this->agregarMatrizFilial(
            $this->baseAdmissao($empresaId)
                ->where('a.status', 'ADMITIDO')
                ->whereBetween('a.data_admissao', [$desdeData, $hoje])
        );
        $demitidosPeriodoMf = $this->agregarMatrizFilial(
            $this->baseAdmissao($empresaId)
                ->where('a.status', 'DEMITIDO')
                ->where(function ($q) use ($desde, $desdeData) {
                    $q->where(function ($q1) use ($desdeData) {
                        $q1->whereNotNull('a.data_desmobilizacao')
                            ->where('a.data_desmobilizacao', '>=', $desdeData);
                    })->orWhere(function ($q1) use ($desdeData) {
                        $q1->whereNotNull('a.data_desmob')
                            ->where('a.data_desmob', '>=', $desdeData);
                    })->orWhere(function ($q2) use ($desde) {
                        $q2->whereNull('a.data_desmobilizacao')
                            ->whereNull('a.data_desmob')
                            ->where('a.updated_at', '>=', $desde);
                    });
                })
        );

        $admitidosSnap = $statusSnapshot->firstWhere('status', 'ADMITIDO')
            ?: (object) ['status' => 'ADMITIDO', 'matriz' => 0, 'filial' => 0, 'total' => 0];
        $demitidosSnap = $statusSnapshot->firstWhere('status', 'DEMITIDO')
            ?: (object) ['status' => 'DEMITIDO', 'matriz' => 0, 'filial' => 0, 'total' => 0];
        $outrosStatus = $statusSnapshot->reject(fn ($r) => in_array($r->status, ['ADMITIDO', 'DEMITIDO'], true))->values();

        $temFilial = $statusSnapshot->sum('filial') > 0
            || $admitidosPeriodoMf['filial'] > 0
            || $demitidosPeriodoMf['filial'] > 0;

        return [
            'curriculos_periodo' => $curriculos,
            'candidaturas_periodo' => $candidaturas,
            'vagas_abertas_criadas' => $vagasAbertasCriadas,
            'vagas_abertas_ativas' => $vagasAbertasAtivas,
            'vagas_abertas_total' => $vagasAbertasTotal,
            'vagas_criadas_lista' => $vagasCriadasLista,
            'vagas_preenchidas_periodo' => $vagasPreenchidas,
            'vagas_preenchidas_distintas' => $vagasPreenchidasDistintas,
            'status_snapshot' => $statusSnapshot,
            'admitidos_snapshot' => $admitidosSnap,
            'demitidos_snapshot' => $demitidosSnap,
            'outros_status' => $outrosStatus,
            'admitidos_periodo' => $admitidosPeriodoMf,
            'demitidos_periodo' => $demitidosPeriodoMf,
            'tem_filial' => $temFilial,
        ];
    }

    private function baseAdmissao(int $empresaId)
    {
        return DB::table('admissoes as a')
            ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('a.deleted_at')
            ->whereNull('f.deleted_at');
    }

    private function agregarStatusMatrizFilial(int $empresaId): Collection
    {
        return $this->baseAdmissao($empresaId)
            ->selectRaw('coalesce(a.status, "Nao informado") as status')
            ->selectRaw('sum(case when a.filial = 1 then 1 else 0 end) as filial')
            ->selectRaw('sum(case when a.filial = 1 then 0 else 1 end) as matriz')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(function ($r) {
                $r->filial = (int) $r->filial;
                $r->matriz = (int) $r->matriz;
                $r->total = (int) $r->total;

                return $r;
            });
    }

    private function agregarMatrizFilial($query): array
    {
        $row = $query
            ->selectRaw('sum(case when a.filial = 1 then 1 else 0 end) as filial')
            ->selectRaw('sum(case when a.filial = 1 then 0 else 1 end) as matriz')
            ->selectRaw('count(*) as total')
            ->first();

        return [
            'matriz' => (int) ($row->matriz ?? 0),
            'filial' => (int) ($row->filial ?? 0),
            'total' => (int) ($row->total ?? 0),
        ];
    }

    private function montarResumo(object $cliente, int $empresaId, int $dias, Carbon $desde, Carbon $agora, array $d): array
    {
        return array_merge($d, [
            'empresa_id' => $empresaId,
            'apelido' => $cliente->apelido,
            'razao_social' => $cliente->razao_social ?: $cliente->nome_fantasia ?: $cliente->nome,
            'dias' => $dias,
            'periodo_de' => $desde->format('d/m/Y'),
            'periodo_ate' => $agora->format('d/m/Y'),
            'gerado_em' => $agora->format('d/m/Y H:i'),
        ]);
    }

    private function linhasStatusCsv(array $d): array
    {
        $rows = [['escopo', 'status', 'matriz', 'filial', 'total']];
        foreach ($d['status_snapshot'] as $r) {
            $rows[] = ['snapshot', $r->status, $r->matriz, $r->filial, $r->total];
        }
        $rows[] = ['admitidos_periodo_data_admissao', 'ADMITIDO', $d['admitidos_periodo']['matriz'], $d['admitidos_periodo']['filial'], $d['admitidos_periodo']['total']];
        $rows[] = ['demitidos_periodo', 'DEMITIDO', $d['demitidos_periodo']['matriz'], $d['demitidos_periodo']['filial'], $d['demitidos_periodo']['total']];

        return $rows;
    }

    private function linhasVagasCsv(array $d): array
    {
        $rows = [['id', 'titulo', 'vaga_nome', 'ativo', 'created_at']];
        foreach ($d['vagas_criadas_lista'] as $v) {
            $rows[] = [
                $v->id,
                $v->titulo ?: 'Nao informado',
                $v->vaga_nome ?: 'Nao informado',
                $v->ativo ? 'Sim' : 'Nao',
                $v->created_at,
            ];
        }

        return $rows;
    }

    private function escreverCsv(string $path, array $rows): void
    {
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
    }
}
