<?php

namespace App\Services\Relatorios;

use App\Models\Admissao;
use App\Models\CentroCusto;
use App\Models\ClienteConfig;
use App\Models\FeedbackCurriculo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MasterTag\DataHora;

/**
 * Regra única do relatório de vencimento de ASO (tela + Excel + e-mail).
 *
 * Fonte: examesesmts via UltimoAso (atual, realizado, aprovado).
 * População: sem demissão + Admissao ADMITIDO | PRONTO PARA ADMISSÃO.
 * Alerta: dias_vencer <= dias da config ClienteConfig.vencimento_aso.
 */
class AsoVencimentoRelatorioService
{
    public const CATEGORIA_VENCIDO = 'VENCIDO';

    public const CATEGORIA_PROXIMO = 'PROXIMO';

    public const CATEGORIA_REGULAR = 'REGULAR';

    public const DIAS_CONFIG_PADRAO = 90;

    /**
     * @return array{dias: int, extenso: string, chave: int|null}
     */
    public function resolverPeriodoConfig(?int $vencimentoAsoKey): array
    {
        if ($vencimentoAsoKey !== null && isset(ClienteConfig::LISTA_VENCIMENTOS[$vencimentoAsoKey])) {
            $extenso = ClienteConfig::LISTA_VENCIMENTOS[$vencimentoAsoKey];
            $dias = (int) preg_replace('/[^0-9]/', '', $extenso);

            return [
                'dias' => $dias > 0 ? $dias : self::DIAS_CONFIG_PADRAO,
                'extenso' => $extenso,
                'chave' => $vencimentoAsoKey,
            ];
        }

        return [
            'dias' => self::DIAS_CONFIG_PADRAO,
            'extenso' => self::DIAS_CONFIG_PADRAO . ' dias',
            'chave' => null,
        ];
    }

    /**
     * @param  array<string, mixed>|Request  $filtros
     * @return array{
     *   dados: list<array<string, mixed>>,
     *   cc: mixed,
     *   periodo_vencimento_numero: int,
     *   periodo_vencimento_extenso: string,
     *   config_ok: bool
     * }
     */
    public function listarParaTela(User $user, array|Request $filtros): array
    {
        $request = $filtros instanceof Request ? $filtros : Request::create('/', 'POST', $filtros);
        $dadosFiltro = $filtros instanceof Request ? $filtros->all() : $filtros;

        $config = $user->EmpresaConfiguracoes;
        $periodo = $this->resolverPeriodoConfig($config->vencimento_aso ?? null);
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj($user->empresa_id);

        if (!$config || $periodo['chave'] === null) {
            return [
                'dados' => [],
                'cc' => $cc,
                'periodo_vencimento_numero' => $periodo['dias'],
                'periodo_vencimento_extenso' => $periodo['extenso'],
                'config_ok' => false,
            ];
        }

        $itens = $this->montarQuery($user, $dadosFiltro, $request)
            ->get()
            ->map(fn ($item) => $this->mapearItem($item, $cc, $periodo['dias']))
            ->filter()
            ->sortBy('dias_vencer')
            ->values()
            ->all();

        return [
            'dados' => $itens,
            'cc' => $cc,
            'periodo_vencimento_numero' => $periodo['dias'],
            'periodo_vencimento_extenso' => $periodo['extenso'],
            'config_ok' => true,
        ];
    }

    /**
     * Lista para e-mail: mesma população da tela, só quem precisa de alerta
     * (já vencido ou dias_vencer <= dias da config).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function listarParaAlertaEmail(User $userContexto): Collection
    {
        $config = $userContexto->EmpresaConfiguracoes
            ?? ClienteConfig::withoutGlobalScopes()
                ->where('cliente_id', $userContexto->empresa_id)
                ->first();

        $periodo = $this->resolverPeriodoConfig($config->vencimento_aso ?? null);
        if ($periodo['chave'] === null) {
            return collect();
        }

        $cc = (new CentroCusto())->listaCentroCustoPorCnpj($userContexto->empresa_id);
        $request = Request::create('/', 'POST', []);

        // Inclui passado (vencidos) até o limite da config à frente
        $dadosFiltro = [
            'filtroVencimento' => true,
            'campoVencimento' => '2000-01-01 até ' . date('Y-m-d', strtotime('+' . $periodo['dias'] . ' days')),
            'campoTipoExame' => null,
            'campoBusca' => null,
            'campoVencido' => '',
        ];

        return $this->montarQuery($userContexto, $dadosFiltro, $request)
            ->get()
            ->map(fn ($item) => $this->mapearItem($item, $cc, $periodo['dias']))
            ->filter()
            ->filter(fn (array $row) => $row['dias_vencer'] <= $periodo['dias'])
            ->sortBy('dias_vencer')
            ->values();
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function montarQuery(User $user, array $dados, Request $request): Builder
    {
        return FeedbackCurriculo::select([
            'id', 'curriculo_id', 'empresa_id', 'vaga_id', 'vagas_abertas_id',
        ])
            ->admitidos()
            ->whereHas('Admissao', function ($query) {
                $query->whereIn('status', [
                    Admissao::STATUS_ADMISSAO_ADMITIDO,
                    Admissao::STATUS_ADMISSAO_PRONTOPARAADMISSAO,
                ]);
            })
            ->filtrarPorCnpjECentroCusto($request)
            ->filtrarPorUltimoAso($dados)
            ->filtrarPorTipoExame($dados)
            ->filtrarPorNome($dados)
            ->with([
                'UltimoAso.ExameFuncionario:id,feedback_id,exame_tipo_id',
                'UltimoAso.ExameFuncionario.ExameTipo:id,label',
                'Admissao:id,feedback_id,data_admissao,matricula,funcao,numero_cracha,status,cargo,centro_custo_filial_id,centro_custo_id,filial',
                'Curriculo:id,nome,nascimento,rg,orgao_expeditor',
                'VagaAberta:id,vaga_id,titulo,municipio_id,empresa_id',
            ])
            ->groupBy('id');
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $cc
     * @return array<string, mixed>|null
     */
    public function mapearItem($feedback, $cc, int $diasAlerta): ?array
    {
        $ultimoAso = $feedback->UltimoAso;
        if (!$ultimoAso || empty($ultimoAso->data_vencimento)) {
            return null;
        }

        $ccInfo = $this->resolverCentroCusto($feedback->Admissao, $cc);
        $exameFuncionario = $ultimoAso->ExameFuncionario->first();
        $exameTipoLabel = $exameFuncionario?->ExameTipo?->label ?? '—';
        $cargo = $feedback->Admissao?->cargo
            ?? $feedback->VagaAberta?->VagaSelecionada?->nome
            ?? '—';

        $diasVencer = DataHora::diferencaDias(
            (new DataHora())->dataInsert() . ' 00:00:00',
            (new DataHora($ultimoAso->data_vencimento))->dataInsert() . ' 23:59:59'
        );

        $categoria = $this->determinarCategoria((int) $diasVencer, $diasAlerta);

        return [
            'emp_cnpj' => $ccInfo['cnpj_format'] ?? null,
            'emp_nome_fantasia' => $ccInfo['nome_fantasia'] ?? null,
            'emp_centro_custo' => $ccInfo['label'] ?? null,
            'emp_tipo' => isset($ccInfo['matriz']) ? (($ccInfo['matriz'] ?? false) ? 'Matriz' : 'Filial') : null,
            'feedback_id' => $feedback->id,
            'atual' => $ultimoAso->atual,
            'colaborador' => $feedback->Curriculo?->nome ?? '—',
            'cargo' => $cargo,
            'data_admissao' => $feedback->Admissao?->data_admissao ?? 'Não informada',
            'exame_tipo' => $exameTipoLabel,
            'data_aso' => $ultimoAso->data_realizacao,
            'data_vencimento' => $ultimoAso->data_vencimento,
            'dias_vencer' => $diasVencer,
            'pintar' => $diasVencer <= $diasAlerta,
            'categoria' => $categoria,
            'status' => $diasVencer < 0 ? self::CATEGORIA_VENCIDO : 'A VENCER',
        ];
    }

    public function determinarCategoria(int $diasVencer, int $diasAlerta): string
    {
        if ($diasVencer < 0) {
            return self::CATEGORIA_VENCIDO;
        }
        if ($diasVencer <= $diasAlerta) {
            return self::CATEGORIA_PROXIMO;
        }

        return self::CATEGORIA_REGULAR;
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $cc
     * @return array|mixed|null
     */
    private function resolverCentroCusto(?Admissao $admissao, $cc)
    {
        if (!$admissao) {
            return null;
        }

        $centros = is_array($cc) ? ($cc['centros_custos'] ?? []) : ($cc['centros_custos'] ?? []);
        $todos = collect($centros)->collapse();

        if ($admissao->filial && $admissao->centro_custo_filial_id) {
            $encontrado = $todos->first(function ($item) use ($admissao) {
                return (int) ($item['filial_id'] ?? 0) === (int) $admissao->centro_custo_filial_id
                    || (int) ($item['id'] ?? 0) === (int) $admissao->centro_custo_filial_id;
            });
            if ($encontrado) {
                return $encontrado;
            }
        }

        if ($admissao->centro_custo_id) {
            return $todos->where('id', $admissao->centro_custo_id)->first();
        }

        return null;
    }
}
