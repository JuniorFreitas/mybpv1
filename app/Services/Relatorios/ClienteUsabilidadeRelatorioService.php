<?php

namespace App\Services\Relatorios;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Relatório de usabilidade + admissão por cliente (empresa_id).
 *
 * Entrega CSVs (UTF-8 BOM, ";") e HTML consolidado em storage/app/relatorios.
 */
class ClienteUsabilidadeRelatorioService
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
        $prefix = sprintf('%s_%d', $slug !== '' ? $slug : 'cliente', $empresaId);

        $usuarios = $this->coletarUsuarios($empresaId, $desde);
        $admissoes = $this->coletarAdmissoes($empresaId, $desde);
        $activity = $this->coletarActivityResumo($usuarios->pluck('id'), $desde);
        $extras = $this->coletarExtras($empresaId, $desde);
        $distAdmitidos = $this->coletarDistribuicaoPorStatus($empresaId, 'ADMITIDO');
        $distDemitidos = $this->coletarDistribuicaoPorStatus($empresaId, 'DEMITIDO');
        $demitidosPeriodo = $this->coletarDemitidosPeriodo($empresaId, $desde);

        $csvUsuarios = "{$dir}/{$prefix}_usuarios_uso_{$dias}d_{$stamp}.csv";
        $csvAdmissoes = "{$dir}/{$prefix}_admissoes_{$dias}d_{$stamp}.csv";
        $csvActivity = "{$dir}/{$prefix}_activity_resumo_{$dias}d_{$stamp}.csv";
        $csvCcFilial = "{$dir}/{$prefix}_admissoes_cc_filial_{$dias}d_{$stamp}.csv";
        $csvDemitidos = "{$dir}/{$prefix}_demitidos_{$dias}d_{$stamp}.csv";
        $htmlPath = "{$dir}/{$prefix}_relatorio_{$dias}d_{$stamp}.html";

        $this->escreverCsv($csvUsuarios, $this->linhasUsuariosCsv($usuarios));
        $this->escreverCsv($csvAdmissoes, $this->linhasAdmissoesCsv($admissoes));
        $this->escreverCsv($csvActivity, $this->linhasActivityCsv($activity));
        $this->escreverCsv($csvCcFilial, $this->linhasCcFilialCsv($admissoes, $distAdmitidos, $distDemitidos, $demitidosPeriodo));
        $this->escreverCsv($csvDemitidos, $this->linhasAdmissoesCsv($demitidosPeriodo));

        $resumo = $this->montarResumo(
            $cliente,
            $empresaId,
            $dias,
            $desde,
            $agora,
            $usuarios,
            $admissoes,
            $activity,
            $extras,
            $distAdmitidos,
            $distDemitidos,
            $demitidosPeriodo
        );
        File::put($htmlPath, view('relatorios.cliente_usabilidade', ['r' => $resumo])->render());

        return [
            'empresa_id' => $empresaId,
            'dias' => $dias,
            'desde' => $desde->toDateTimeString(),
            'ate' => $agora->toDateTimeString(),
            'resumo' => $resumo,
            'arquivos' => [
                'usuarios' => $csvUsuarios,
                'admissoes' => $csvAdmissoes,
                'activity' => $csvActivity,
                'cc_filial' => $csvCcFilial,
                'demitidos' => $csvDemitidos,
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

    private function coletarUsuarios(int $empresaId, Carbon $desde): Collection
    {
        // Apenas usuários ativos e não soft-deleted (deleted_at null).
        $userIds = DB::table('users')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->where('ativo', 1)
            ->pluck('id');

        $acts = collect();
        if ($userIds->isNotEmpty()) {
            $acts = DB::table('activity_log')
                ->whereIn('causer_id', $userIds)
                ->where('causer_type', 'like', '%User%')
                ->where('created_at', '>=', $desde)
                ->selectRaw('causer_id, count(*) as qtd, min(created_at) as primeira, max(created_at) as ultima')
                ->groupBy('causer_id')
                ->get()
                ->keyBy('causer_id');
        }

        return DB::table('users as u')
            ->leftJoin('papeis as p', 'p.id', '=', 'u.grupo_id')
            ->where('u.empresa_id', $empresaId)
            ->whereNull('u.deleted_at')
            ->where('u.ativo', 1)
            ->orderBy('u.nome')
            ->select([
                'u.id',
                'u.nome',
                'u.login',
                'u.tipo',
                'u.ativo',
                'u.gestor',
                'u.ultimo_acesso',
                'u.created_at',
                DB::raw('coalesce(p.nome, "") as papel'),
            ])
            ->get()
            ->map(function ($u) use ($acts, $desde) {
                $a = $acts->get($u->id);
                $u->acoes_activity_log = (int) ($a->qtd ?? 0);
                $u->primeira_acao = $a->primeira ?? null;
                $u->ultima_acao = $a->ultima ?? null;
                $u->acessou_periodo = $u->ultimo_acesso && Carbon::parse($u->ultimo_acesso)->gte($desde);

                return $u;
            });
    }

    private function coletarAdmissoes(int $empresaId, Carbon $desde): Collection
    {
        return DB::table('admissoes as a')
            ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
            ->leftJoin('curriculos as c', 'c.id', '=', 'f.curriculo_id')
            ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
            ->leftJoin('centro_custo_filials as ccf', 'ccf.id', '=', 'a.centro_custo_filial_id')
            ->leftJoin('cliente_filials as cf', 'cf.id', '=', 'ccf.cliente_filial_id')
            ->leftJoin('users as criador', 'criador.id', '=', 'a.usuario_id')
            ->leftJoin('users as editor', 'editor.id', '=', 'a.editado_usuario_id')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('a.deleted_at')
            ->whereNull('f.deleted_at')
            ->where('a.created_at', '>=', $desde)
            ->orderBy('c.nome')
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
                'a.filial',
                'a.centro_custo_filial_id',
                'cf.dados as filial_dados',
                DB::raw('coalesce(cc.label, "") as centro_custo'),
                DB::raw('coalesce(criador.nome, "") as criado_por'),
                DB::raw('coalesce(editor.nome, "") as editado_por'),
                'a.created_at',
                'a.updated_at',
                'a.data_desmobilizacao',
                'a.data_desmob',
            ])
            ->get()
            ->map(function ($a) {
                $a->filial_nome = $this->resolverNomeFilial($a);

                return $a;
            });
    }

    /**
     * Snapshot por status (ADMITIDO / DEMITIDO) · centro de custo × filial.
     */
    private function coletarDistribuicaoPorStatus(int $empresaId, string $status): array
    {
        $rows = DB::table('admissoes as a')
            ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
            ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
            ->leftJoin('centro_custo_filials as ccf', 'ccf.id', '=', 'a.centro_custo_filial_id')
            ->leftJoin('cliente_filials as cf', 'cf.id', '=', 'ccf.cliente_filial_id')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('a.deleted_at')
            ->whereNull('f.deleted_at')
            ->where('a.status', $status)
            ->select([
                'a.filial',
                'a.centro_custo_filial_id',
                'cf.dados as filial_dados',
                DB::raw('coalesce(cc.label, "(sem CC)") as centro_custo'),
                DB::raw('count(*) as qtd'),
            ])
            ->groupBy('centro_custo', 'a.filial', 'a.centro_custo_filial_id', 'cf.dados')
            ->orderByDesc('qtd')
            ->get()
            ->map(function ($r) {
                $r->filial_nome = $this->resolverNomeFilial($r);

                return $r;
            });

        $porCc = $rows->groupBy('centro_custo')->map->sum('qtd')->sortDesc();
        $porFilial = $rows->groupBy('filial_nome')->map->sum('qtd')->sortDesc();
        $porCcFilial = $rows->map(fn ($r) => (object) [
            'centro_custo' => $r->centro_custo,
            'filial' => $r->filial_nome,
            'qtd' => (int) $r->qtd,
        ])->sortByDesc('qtd')->values();

        return [
            'total' => (int) $rows->sum('qtd'),
            'por_cc' => $porCc,
            'por_filial' => $porFilial,
            'por_cc_filial' => $porCcFilial,
        ];
    }

    /**
     * Demissões no período: status DEMITIDO com data_desmobilizacao/data_desmob na janela;
     * se ambas vazias, usa updated_at no período (proxy quando o cliente não preenche a data).
     */
    private function coletarDemitidosPeriodo(int $empresaId, Carbon $desde): Collection
    {
        $desdeData = $desde->toDateString();

        return DB::table('admissoes as a')
            ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
            ->leftJoin('curriculos as c', 'c.id', '=', 'f.curriculo_id')
            ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
            ->leftJoin('centro_custo_filials as ccf', 'ccf.id', '=', 'a.centro_custo_filial_id')
            ->leftJoin('cliente_filials as cf', 'cf.id', '=', 'ccf.cliente_filial_id')
            ->leftJoin('users as criador', 'criador.id', '=', 'a.usuario_id')
            ->leftJoin('users as editor', 'editor.id', '=', 'a.editado_usuario_id')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('a.deleted_at')
            ->whereNull('f.deleted_at')
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
            ->orderByDesc('a.updated_at')
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
                'a.filial',
                'a.centro_custo_filial_id',
                'cf.dados as filial_dados',
                'a.data_desmobilizacao',
                'a.data_desmob',
                DB::raw('coalesce(cc.label, "") as centro_custo'),
                DB::raw('coalesce(criador.nome, "") as criado_por'),
                DB::raw('coalesce(editor.nome, "") as editado_por'),
                'a.created_at',
                'a.updated_at',
            ])
            ->get()
            ->map(function ($a) {
                $a->filial_nome = $this->resolverNomeFilial($a);

                return $a;
            });
    }

    private function resolverNomeFilial(object $row): string
    {
        $ehFilial = (int) ($row->filial ?? 0) === 1;
        if (!$ehFilial) {
            return 'Matriz';
        }

        $dados = $row->filial_dados ?? null;
        if (is_string($dados) && $dados !== '') {
            $dados = json_decode($dados, true);
        } elseif (is_object($dados)) {
            $dados = (array) $dados;
        }

        if (is_array($dados)) {
            $nome = $dados['nome_fantasia'] ?? $dados['razao_social'] ?? $dados['cnpj'] ?? null;
            if ($nome) {
                return (string) $nome;
            }
        }

        $id = $row->centro_custo_filial_id ?? null;

        return $id ? "Filial #{$id}" : 'Filial';
    }

    private function coletarActivityResumo(Collection $userIds, Carbon $desde): Collection
    {
        if ($userIds->isEmpty()) {
            return collect();
        }

        return DB::table('activity_log as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.causer_id')
            ->whereIn('al.causer_id', $userIds)
            ->where('al.causer_type', 'like', '%User%')
            ->where('al.created_at', '>=', $desde)
            ->groupBy('al.causer_id', 'u.nome', 'u.login', 'al.log_name', 'al.description')
            ->orderByDesc(DB::raw('count(*)'))
            ->select([
                'al.causer_id as usuario_id',
                DB::raw('coalesce(u.nome, "") as usuario_nome'),
                DB::raw('coalesce(u.login, "") as usuario_login'),
                DB::raw('coalesce(al.log_name, "default") as log_name'),
                DB::raw('coalesce(al.description, "") as description'),
                DB::raw('count(*) as qtd'),
            ])
            ->get();
    }

    private function coletarExtras(int $empresaId, Carbon $desde): array
    {
        $totalAdmissoes = DB::table('admissoes as a')
            ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('a.deleted_at')
            ->whereNull('f.deleted_at')
            ->count();

        $demitidosTotal = DB::table('admissoes as a')
            ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('a.deleted_at')
            ->whereNull('f.deleted_at')
            ->where('a.status', 'DEMITIDO')
            ->count();

        $desdeData = $desde->toDateString();
        $desmobilizacoes = DB::table('admissoes as a')
            ->join('feedback_curriculos as f', 'f.id', '=', 'a.feedback_id')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('a.deleted_at')
            ->whereNull('f.deleted_at')
            ->where(function ($q) use ($desde, $desdeData) {
                $q->where(function ($q1) use ($desdeData) {
                    $q1->whereNotNull('a.data_desmobilizacao')
                        ->where('a.data_desmobilizacao', '>=', $desdeData);
                })->orWhere(function ($q1) use ($desdeData) {
                    $q1->whereNotNull('a.data_desmob')
                        ->where('a.data_desmob', '>=', $desdeData);
                })->orWhere(function ($q2) use ($desde) {
                    $q2->where('a.status', 'DEMITIDO')
                        ->whereNull('a.data_desmobilizacao')
                        ->whereNull('a.data_desmob')
                        ->where('a.updated_at', '>=', $desde);
                });
            })
            ->count();

        $treinamentos = 0;
        if (DB::getSchemaBuilder()->hasTable('treinamentos')) {
            $treinamentos = DB::table('treinamentos as t')
                ->join('feedback_curriculos as f', 'f.id', '=', 't.feedback_id')
                ->where('f.empresa_id', $empresaId)
                ->whereNull('f.deleted_at')
                ->where('t.created_at', '>=', $desde)
                ->count();
        }

        return [
            'admissoes_total_empresa' => $totalAdmissoes,
            'demitidos_total' => $demitidosTotal,
            'desmobilizacoes_periodo' => $desmobilizacoes,
            'treinamentos_criados_periodo' => $treinamentos,
        ];
    }

    private function linhasUsuariosCsv(Collection $usuarios): array
    {
        $rows = [[
            'id', 'nome', 'login', 'tipo', 'papel', 'ativo', 'gestor',
            'ultimo_acesso', 'acessou_periodo', 'acoes_activity_log_periodo',
            'primeira_acao_periodo', 'ultima_acao_periodo', 'created_at',
        ]];

        foreach ($usuarios as $u) {
            $rows[] = [
                $u->id,
                $u->nome,
                $u->login,
                $u->tipo,
                $u->papel,
                $u->ativo ? 'Sim' : 'Nao',
                $u->gestor ? 'Sim' : 'Nao',
                $u->ultimo_acesso,
                $u->acessou_periodo ? 'Sim' : 'Nao',
                $u->acoes_activity_log,
                $u->primeira_acao,
                $u->ultima_acao,
                $u->created_at,
            ];
        }

        return $rows;
    }

    private function linhasAdmissoesCsv(Collection $admissoes): array
    {
        $rows = [[
            'admissao_id', 'feedback_id', 'colaborador', 'cpf', 'status', 'tipo_admissao',
            'data_admissao', 'funcao', 'cargo', 'centro_custo', 'filial', 'criado_por', 'editado_por',
            'created_at', 'updated_at',
        ]];

        foreach ($admissoes as $a) {
            $rows[] = [
                $a->admissao_id,
                $a->feedback_id,
                $a->colaborador ?: 'Nao informado',
                $a->cpf ?: 'Nao informado',
                $a->status ?: 'Nao informado',
                $a->tipo_admissao ?: 'Nao informado',
                $a->data_admissao,
                $a->funcao ?: 'Nao informado',
                $a->cargo ?: 'Nao informado',
                $a->centro_custo ?: 'Nao informado',
                $a->filial_nome ?: 'Nao informado',
                $a->criado_por ?: 'Nao informado',
                $a->editado_por ?: '',
                $a->created_at,
                $a->updated_at,
            ];
        }

        return $rows;
    }

    private function linhasCcFilialCsv(
        Collection $admissoesPeriodo,
        array $distAdmitidos,
        array $distDemitidos,
        Collection $demitidosPeriodo
    ): array {
        $rows = [[
            'escopo', 'centro_custo', 'filial', 'qtd',
        ]];

        $periodo = $admissoesPeriodo
            ->groupBy(fn ($a) => ($a->centro_custo ?: 'Nao informado').'||'.($a->filial_nome ?: 'Nao informado'))
            ->map(function ($g, $key) {
                [$cc, $filial] = explode('||', $key, 2);

                return (object) ['centro_custo' => $cc, 'filial' => $filial, 'qtd' => $g->count()];
            })
            ->sortByDesc('qtd')
            ->values();

        foreach ($periodo as $r) {
            $rows[] = ['admissoes_criadas_periodo', $r->centro_custo, $r->filial, $r->qtd];
        }
        foreach ($distAdmitidos['por_cc_filial'] as $r) {
            $rows[] = ['admitidos_snapshot', $r->centro_custo, $r->filial, $r->qtd];
        }
        foreach ($distDemitidos['por_cc_filial'] as $r) {
            $rows[] = ['demitidos_snapshot', $r->centro_custo, $r->filial, $r->qtd];
        }

        $demPeriodo = $demitidosPeriodo
            ->groupBy(fn ($a) => ($a->centro_custo ?: 'Nao informado').'||'.($a->filial_nome ?: 'Nao informado'))
            ->map(function ($g, $key) {
                [$cc, $filial] = explode('||', $key, 2);

                return (object) ['centro_custo' => $cc, 'filial' => $filial, 'qtd' => $g->count()];
            })
            ->sortByDesc('qtd')
            ->values();
        foreach ($demPeriodo as $r) {
            $rows[] = ['demitidos_periodo', $r->centro_custo, $r->filial, $r->qtd];
        }

        return $rows;
    }

    private function linhasActivityCsv(Collection $activity): array
    {
        $rows = [['usuario_id', 'usuario_nome', 'usuario_login', 'log_name', 'description', 'qtd']];
        foreach ($activity as $a) {
            $rows[] = [
                $a->usuario_id,
                $a->usuario_nome,
                $a->usuario_login,
                $a->log_name,
                $a->description,
                $a->qtd,
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

    private function montarResumo(
        object $cliente,
        int $empresaId,
        int $dias,
        Carbon $desde,
        Carbon $agora,
        Collection $usuarios,
        Collection $admissoes,
        Collection $activity,
        array $extras,
        array $distAdmitidos,
        array $distDemitidos,
        Collection $demitidosPeriodo
    ): array {
        $porTipo = $usuarios->groupBy('tipo')->map->count()->sortDesc();
        $comLogin = $usuarios->where('acessou_periodo', true)->values();
        $comAcao = $usuarios->filter(fn ($u) => $u->acoes_activity_log > 0)
            ->sortByDesc('acoes_activity_log')
            ->values();

        $statusAdm = $admissoes->groupBy(fn ($a) => $a->status ?: 'Nao informado')->map->count()->sortDesc();
        $tipoAdm = $admissoes->groupBy(fn ($a) => $a->tipo_admissao ?: 'Nao informado')->map->count()->sortDesc();
        $ccAdmPeriodo = $admissoes->groupBy(fn ($a) => $a->centro_custo ?: 'Nao informado')->map->count()->sortDesc();
        $filialAdmPeriodo = $admissoes->groupBy(fn ($a) => $a->filial_nome ?: 'Nao informado')->map->count()->sortDesc();
        $ccFilialAdmPeriodo = $admissoes
            ->groupBy(fn ($a) => ($a->centro_custo ?: 'Nao informado').'||'.($a->filial_nome ?: 'Nao informado'))
            ->map(function ($g, $key) {
                [$cc, $filial] = explode('||', $key, 2);

                return (object) [
                    'centro_custo' => $cc,
                    'filial' => $filial,
                    'qtd' => $g->count(),
                ];
            })
            ->sortByDesc('qtd')
            ->values();
        $cargos = $admissoes->groupBy(fn ($a) => $a->cargo ?: $a->funcao ?: 'Nao informado')->map->count()->sortDesc()->take(12);
        $mesesAdm = $admissoes
            ->groupBy(fn ($a) => $a->data_admissao ? substr((string) $a->data_admissao, 0, 7) : 'Nao informado')
            ->map->count()
            ->sortKeys();
        $diasCriacao = $admissoes
            ->groupBy(fn ($a) => $a->created_at ? substr((string) $a->created_at, 0, 10) : 'Nao informado')
            ->map->count()
            ->sortKeys();

        $ccDemPeriodo = $demitidosPeriodo->groupBy(fn ($a) => $a->centro_custo ?: 'Nao informado')->map->count()->sortDesc();
        $filialDemPeriodo = $demitidosPeriodo->groupBy(fn ($a) => $a->filial_nome ?: 'Nao informado')->map->count()->sortDesc();

        $maxCargo = max(1, (int) ($cargos->first() ?: 1));
        $maxCcPeriodo = max(1, (int) ($ccAdmPeriodo->first() ?: 1));
        $maxCcSnap = max(1, (int) ($distAdmitidos['por_cc']->first() ?: 1));
        $maxCcDem = max(1, (int) ($distDemitidos['por_cc']->first() ?: 1));
        $maxCcDemPeriodo = max(1, (int) ($ccDemPeriodo->first() ?: 1));

        $activityEmpresa = $activity->groupBy('usuario_id');
        $topModulos = $activity
            ->groupBy(fn ($a) => ($a->log_name ?: 'default').'|'.($a->description ?: ''))
            ->map(function ($g) {
                $first = $g->first();

                return (object) [
                    'log_name' => $first->log_name,
                    'description' => $first->description,
                    'qtd' => $g->sum('qtd'),
                    'usuarios' => $g->pluck('usuario_nome')->unique()->implode(', '),
                ];
            })
            ->sortByDesc('qtd')
            ->take(25)
            ->values();

        $leitura = $this->montarLeitura($comLogin->count(), $comAcao, $admissoes->count(), $activity->sum('qtd'));

        return [
            'empresa_id' => $empresaId,
            'apelido' => $cliente->apelido,
            'razao_social' => $cliente->razao_social ?: $cliente->nome_fantasia ?: $cliente->nome,
            'dias' => $dias,
            'periodo_de' => $desde->format('d/m/Y'),
            'periodo_ate' => $agora->format('d/m/Y'),
            'gerado_em' => $agora->format('d/m/Y H:i'),
            'usuarios_total' => $usuarios->count(),
            'usuarios_ativos' => $usuarios->where('ativo', 1)->count(),
            'logins_periodo' => $comLogin->count(),
            'com_acao_periodo' => $comAcao->count(),
            'acoes_totais' => (int) $activity->sum('qtd'),
            'por_tipo' => $porTipo,
            'usuarios_destaque' => $comAcao->take(20)->merge(
                $comLogin->reject(fn ($u) => $comAcao->contains('id', $u->id))
            )->unique('id')->take(25)->values(),
            'admissoes_periodo' => $admissoes->count(),
            'admissoes_total_empresa' => $extras['admissoes_total_empresa'],
            'desmobilizacoes_periodo' => $extras['desmobilizacoes_periodo'],
            'demitidos_total' => $extras['demitidos_total'],
            'demitidos_periodo' => $demitidosPeriodo->count(),
            'treinamentos_criados_periodo' => $extras['treinamentos_criados_periodo'],
            'status_adm' => $statusAdm,
            'tipo_adm' => $tipoAdm,
            'cc_adm' => $ccAdmPeriodo,
            'filial_adm' => $filialAdmPeriodo,
            'cc_filial_adm' => $ccFilialAdmPeriodo,
            'max_cc_periodo' => $maxCcPeriodo,
            'admitidos_ativos_total' => $distAdmitidos['total'],
            'cc_admitidos_ativos' => $distAdmitidos['por_cc']->take(20),
            'filial_admitidos_ativos' => $distAdmitidos['por_filial'],
            'cc_filial_admitidos_ativos' => $distAdmitidos['por_cc_filial']->take(30),
            'max_cc_snap' => $maxCcSnap,
            'demitidos_snapshot_total' => $distDemitidos['total'],
            'cc_demitidos' => $distDemitidos['por_cc']->take(20),
            'filial_demitidos' => $distDemitidos['por_filial'],
            'cc_filial_demitidos' => $distDemitidos['por_cc_filial']->take(30),
            'max_cc_dem' => $maxCcDem,
            'cc_demitidos_periodo' => $ccDemPeriodo->take(20),
            'filial_demitidos_periodo' => $filialDemPeriodo,
            'max_cc_dem_periodo' => $maxCcDemPeriodo,
            'cargos' => $cargos,
            'max_cargo' => $maxCargo,
            'meses_adm' => $mesesAdm,
            'dias_criacao_adm' => $diasCriacao,
            'top_modulos' => $topModulos,
            'activity_por_usuario' => $activityEmpresa,
            'leitura' => $leitura,
        ];
    }

    private function montarLeitura(int $logins, Collection $comAcao, int $admissoesPeriodo, int $acoesTotais): string
    {
        if ($logins <= 1 && $acoesTotais > 500 && $admissoesPeriodo > 50) {
            return 'Baixa adoção operacional no período: volume alto no activity_log provavelmente vem de importação/carga em lote, não de uso diário. Verifique o causer Empresa vs usuários humanos.';
        }
        if ($logins === 0) {
            return 'Nenhum login registrado (ultimo_acesso) no período. Avaliar se o cliente está usando o sistema ou apenas possui base cadastrada.';
        }
        if ($comAcao->isEmpty()) {
            return 'Há login(s) no período, porém sem ações relevantes no activity_log.';
        }

        return 'Há uso operacional no período. Priorize os usuários com ações no activity_log e o volume de admissões criadas.';
    }
}
