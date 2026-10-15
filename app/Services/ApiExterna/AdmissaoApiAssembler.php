<?php

namespace App\Services\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Monta o payload de processo/admissão alinhado ao que a tela de Admissão
 * retorna em filtro (lista) e edit (detalhe), com public_id e sanitização LGPD.
 */
class AdmissaoApiAssembler
{
    /**
     * @param  list<int>  $feedbackIds
     * @return array<int, array<string, mixed>>
     */
    public function montarVarios(int $empresaId, array $feedbackIds, bool $completo = false): array
    {
        $feedbackIds = array_values(array_unique(array_filter(array_map('intval', $feedbackIds))));
        if ($feedbackIds === []) {
            return [];
        }

        $feedbacks = DB::table('feedback_curriculos as fc')
            ->where('fc.empresa_id', $empresaId)
            ->whereIn('fc.id', $feedbackIds)
            ->whereNull('fc.deleted_at')
            ->select([
                'fc.id',
                'fc.curriculo_id',
                'fc.cliente_id',
                'fc.empresa_id',
                'fc.vaga_id',
                'fc.vagas_abertas_id',
                'fc.vaga_projeto_id',
                'fc.telefone_id',
                'fc.status',
                'fc.selecionado',
                'fc.interesse',
                'fc.contato_realizado',
                'fc.data_entrevista',
                'fc.local_entrevista',
                'fc.obs',
                'fc.created_at',
                'fc.updated_at',
            ])
            ->get()
            ->keyBy('id');

        if ($feedbacks->isEmpty()) {
            return [];
        }

        $curriculoIds = $feedbacks->pluck('curriculo_id')->filter()->unique()->values()->all();
        $vagaAbertaIds = $feedbacks->pluck('vagas_abertas_id')->filter()->unique()->values()->all();
        $clienteIds = $feedbacks->pluck('cliente_id')->merge($feedbacks->pluck('empresa_id'))->filter()->unique()->values()->all();
        $telefoneIds = $feedbacks->pluck('telefone_id')->filter()->unique()->values()->all();

        $curriculos = $this->carregarCurriculos($curriculoIds);
        $telefonesFeedback = $this->carregarTelefonesPorId($telefoneIds);
        $telefonesCurriculo = $completo
            ? $this->carregarTelefonesPorCurriculo($curriculoIds)
            : collect();
        $telsPrincipaisCurriculo = $this->carregarTelPrincipalPorCurriculo($curriculoIds);
        $contas = $this->carregarContas($curriculoIds);
        $parecerRh = $this->carregarParecerRh($feedbackIds, $completo);
        $parecerTecnica = $this->carregarParecerTecnica($feedbackIds, $completo);
        $parecerRota = $this->carregarParecerRota($feedbackIds, $completo);
        $parecerTeste = $this->carregarParecerTeste($feedbackIds, $completo);
        $resultados = $this->carregarResultadoIntegrado($feedbackIds, $completo);
        $admissoes = $this->carregarAdmissoes($feedbackIds, $completo);
        $ultimosAso = $this->carregarUltimosAso($feedbackIds);
        $demissoes = $this->carregarDemissoes($feedbackIds);
        $vagasAbertas = $this->carregarVagasAbertas($vagaAbertaIds);
        $clientes = $this->carregarClientes($clienteIds);
        $escolaridades = $completo ? $this->carregarEscolaridades($curriculos) : collect();
        $dependentes = $completo ? $this->carregarDependentes($curriculoIds) : collect();
        $dadosAdmissao = $completo ? $this->carregarDadosAdmissao($admissoes->pluck('id')->all()) : collect();
        $ferias = $completo ? $this->carregarFerias($admissoes->pluck('id')->all()) : collect();
        $preAdmissao = $completo ? $this->carregarPreAdmissao($feedbackIds) : collect();

        $out = [];
        foreach ($feedbackIds as $feedbackId) {
            $fb = $feedbacks->get($feedbackId);
            if (! $fb) {
                continue;
            }

            $curriculoId = (int) ($fb->curriculo_id ?? 0);
            $curriculo = $curriculos->get($curriculoId);
            $admissao = $admissoes->get($feedbackId);
            $vagaAberta = $vagasAbertas->get((int) ($fb->vagas_abertas_id ?? 0));
            // empresa_id e cliente_id apontam para a mesma base (clientes); expor só empresa.
            $empresa = $clientes->get((int) ($fb->empresa_id ?? 0))
                ?: $clientes->get((int) ($fb->cliente_id ?? 0));

            $telPrincipal = null;
            if (! empty($fb->telefone_id)) {
                $telPrincipal = $telefonesFeedback->get((int) $fb->telefone_id);
            }
            if (! $telPrincipal && $curriculoId) {
                $telPrincipal = $telsPrincipaisCurriculo->get($curriculoId);
            }

            $payload = [
                'public_id' => ApiExternaId::encode($feedbackId),
                'curriculo_public_id' => ApiExternaId::encode($fb->curriculo_id),
                'vaga_public_id' => ApiExternaId::encode($fb->vaga_id),
                'vaga_aberta_public_id' => ApiExternaId::encode($fb->vagas_abertas_id),
                'vaga_projeto_public_id' => ApiExternaId::encode($fb->vaga_projeto_id),
                'status' => $fb->status,
                'selecionado' => $fb->selecionado,
                'interesse' => $fb->interesse,
                'contato_realizado' => $fb->contato_realizado,
                'data_entrevista' => $fb->data_entrevista,
                'local_entrevista' => $fb->local_entrevista,
                'obs' => $fb->obs,
                'curriculo' => $this->mapCurriculo($curriculo, $completo, $escolaridades, $dependentes, $telefonesCurriculo),
                'telefone_principal' => $this->mapTelefone($telPrincipal),
                'conta_banco' => $this->mapConta($contas->get($curriculoId)),
                'parecer_rh' => $this->mapModelRow($parecerRh->get($feedbackId), ['id', 'feedback_id', 'formulario_id']),
                'parecer_tecnica' => $this->mapModelRow($parecerTecnica->get($feedbackId), ['id', 'feedback_id', 'formulario_id']),
                'parecer_rota' => $this->mapModelRow($parecerRota->get($feedbackId), ['id', 'feedback_id', 'formulario_id']),
                'parecer_teste' => $this->mapModelRow($parecerTeste->get($feedbackId), ['id', 'feedback_id', 'formulario_id']),
                'resultado_integrado' => $this->mapResultadoIntegrado($resultados->get($feedbackId)),
                'vaga_aberta' => $vagaAberta,
                'empresa' => $this->mapEmpresa($empresa),
                'admissao' => $this->mapAdmissao(
                    $admissao,
                    $completo,
                    $dadosAdmissao,
                    $ferias,
                    $ultimosAso->get($feedbackId)
                ),
                'ultimo_aso' => $this->mapAso($ultimosAso->get($feedbackId)),
                'demissao' => $this->mapDemissao($demissoes->get($feedbackId)),
                'created_at' => $fb->created_at,
                'updated_at' => $fb->updated_at,
            ];

            if ($completo) {
                $payload['pre_admissao'] = $this->mapModelRow(
                    $preAdmissao->get($feedbackId),
                    ['id', 'feedback_id', 'user_finalizou_id']
                );
            }

            $payload['links'] = $this->montarLinks($feedbackId, $completo);

            $out[$feedbackId] = $payload;
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    private function montarLinks(int $feedbackId, bool $completo): array
    {
        $pid = ApiExternaId::encode($feedbackId);
        $base = url('/api/v3/processos/' . $pid);

        $links = [
            'self' => $base,
            'etapas' => $base . '/etapas',
        ];

        if ($completo) {
            $links['exames'] = $base . '/exames';
            $links['documentos'] = $base . '/documentos';
            $links['ferias'] = $base . '/ferias';
            $links['pre_admissao'] = $base . '/pre-admissao';
            $links['fluxo'] = url('/api/v3/fluxo/' . $pid);
        }

        return $links;
    }

    public function montarUm(int $empresaId, int $feedbackId, bool $completo = true): ?array
    {
        $map = $this->montarVarios($empresaId, [$feedbackId], $completo);

        return $map[$feedbackId] ?? null;
    }

    /**
     * @param  list<int>  $ids
     */
    private function carregarCurriculos(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return DB::table('curriculos')
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'nome',
                'estado_civil',
                'naturalidade',
                'nacionalidade',
                'carteira_trabalho',
                'cnh',
                'cnh_vencimento',
                'sexo',
                'cpf',
                'rg',
                'rg_data_emissao',
                'orgao_expeditor',
                'logradouro',
                'end_numero',
                'complemento',
                'bairro',
                'municipio',
                'uf',
                'cep',
                'filiacao_pai',
                'filiacao_mae',
                'pcd',
                'nascimento',
                'email',
                'disponibilidade_sabado',
                'disponibilidade_domingo',
                'formacao',
                'formacao_curso',
                'formacao_status',
                'vaga_pretendida',
                'created_at',
                'updated_at',
            ])
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  list<int>  $ids
     */
    private function carregarTelefonesPorId(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return DB::table('curriculo_telefone')
            ->whereIn('id', $ids)
            ->select(['id', 'curriculo_id', 'numero', 'pais', 'tipo', 'principal'])
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  list<int>  $curriculoIds
     */
    private function carregarTelefonesPorCurriculo(array $curriculoIds): Collection
    {
        if ($curriculoIds === []) {
            return collect();
        }

        return DB::table('curriculo_telefone')
            ->whereIn('curriculo_id', $curriculoIds)
            ->select(['id', 'curriculo_id', 'numero', 'pais', 'tipo', 'principal'])
            ->orderBy('id')
            ->get()
            ->groupBy('curriculo_id');
    }

    /**
     * @param  list<int>  $curriculoIds
     */
    private function carregarTelPrincipalPorCurriculo(array $curriculoIds): Collection
    {
        if ($curriculoIds === []) {
            return collect();
        }

        return DB::table('curriculo_telefone')
            ->whereIn('curriculo_id', $curriculoIds)
            ->where('principal', true)
            ->select(['id', 'curriculo_id', 'numero', 'pais', 'tipo', 'principal'])
            ->orderBy('id')
            ->get()
            ->keyBy('curriculo_id');
    }

    /**
     * @param  list<int>  $curriculoIds
     */
    private function carregarContas(array $curriculoIds): Collection
    {
        if ($curriculoIds === []) {
            return collect();
        }

        return DB::table('usuario_contas')
            ->whereIn('user_id', $curriculoIds)
            ->select(['id', 'user_id', 'banco', 'agencia', 'conta', 'pix', 'tipochavepix', 'chavepix'])
            ->get()
            ->keyBy('user_id');
    }

    /**
     * @param  list<int>  $feedbackIds
     */
    private function carregarParecerRh(array $feedbackIds, bool $completo): Collection
    {
        $cols = $completo
            ? ['*']
            : [
                'id', 'feedback_id', 'destro', 'cnh_tipo', 'calca', 'bota', 'camisa_meia',
                'camisa_protecao', 'ex_funcionario', 'turnos_seis_por_dois', 'indicado_por',
                'created_at', 'updated_at',
            ];

        return DB::table('parecer_rh')
            ->whereIn('feedback_id', $feedbackIds)
            ->select($cols)
            ->get()
            ->keyBy('feedback_id');
    }

    /**
     * @param  list<int>  $feedbackIds
     */
    private function carregarParecerTecnica(array $feedbackIds, bool $completo): Collection
    {
        $cols = $completo
            ? ['*']
            : [
                'id', 'feedback_id', 'indicado_area', 'experiencia_cargas_rigger',
                'opera_plat_movel', 'opera_plat_ponte', 'created_at', 'updated_at',
            ];

        return DB::table('parecer_entrevista_tecnica')
            ->whereIn('feedback_id', $feedbackIds)
            ->select($cols)
            ->get()
            ->keyBy('feedback_id');
    }

    /**
     * @param  list<int>  $feedbackIds
     */
    private function carregarParecerRota(array $feedbackIds, bool $completo): Collection
    {
        $cols = $completo
            ? ['*']
            : [
                'id', 'feedback_id', 'tem_rota', 'qual', 'bairro_rota', 'ponto_referencia_rota',
                'pega_onibus', 'pega_onibus_qual_ponto', 'bairro_residencia',
                'ponto_referencia_residencia', 'created_at', 'updated_at',
            ];

        return DB::table('parecer_rotas')
            ->whereIn('feedback_id', $feedbackIds)
            ->select($cols)
            ->get()
            ->keyBy('feedback_id');
    }

    /**
     * @param  list<int>  $feedbackIds
     */
    private function carregarParecerTeste(array $feedbackIds, bool $completo): Collection
    {
        $cols = $completo
            ? ['*']
            : ['id', 'feedback_id', 'qual_teste', 'nota_teste', 'created_at', 'updated_at'];

        return DB::table('parecer_teste_pratico')
            ->whereIn('feedback_id', $feedbackIds)
            ->select($cols)
            ->get()
            ->keyBy('feedback_id');
    }

    /**
     * @param  list<int>  $feedbackIds
     */
    private function carregarResultadoIntegrado(array $feedbackIds, bool $completo): Collection
    {
        $cols = $completo
            ? [
                'id', 'feedback_id', 'formulario_id',
                'documentos_entregue', 'documentos_entregue_data',
                'encaminhado_exame', 'encaminhado_exame_data',
                'pcmso_id', 'empresa_exame_id',
                'encaminhado_treinamento', 'encaminhado_treinamento_data',
                'excessao', 'autorizado_por', 'responsavel_envio', 'obs',
                'created_at', 'updated_at',
            ]
            : [
                'id', 'feedback_id', 'documentos_entregue', 'encaminhado_exame',
                'encaminhado_treinamento', 'responsavel_envio', 'created_at', 'updated_at',
            ];

        return DB::table('resultado_integrados')
            ->whereIn('feedback_id', $feedbackIds)
            ->select($cols)
            ->get()
            ->keyBy('feedback_id');
    }

    /**
     * @param  list<int>  $feedbackIds
     */
    private function carregarAdmissoes(array $feedbackIds, bool $completo): Collection
    {
        $cols = $completo
            ? [
                'id', 'feedback_id', 'centro_custo_filial_id', 'filial', 'formulario_id',
                'matricula', 'usa_lentes_corretivas', 'centro_custo_id', 'funcao', 'cargo',
                'salario', 'contrato', 'status', 'documento',
                'documento_portaria', 'tipo_admissao', 'tipo_treinamento', 'treinamento',
                'data_treinamento', 'carteira_treinamento', 'nr_trinta_tres',
                'data_nr_trinta_tres', 'nr_trinta_cinco', 'data_nr_trinta_cinco',
                'trinta_dois_sessenta', 'data_trinta_dois_sessenta', 'numero_cracha',
                'data_aso', 'foto_escaneada', 'status_carteira_treinamento',
                'segmento_treinamento_id', 'data_admissao', 'data_adm_prevista',
                'data_desmobilizacao', 'avaliacao', 'obs_avaliacao', 'responsavel_feedback',
                'data_avaliacao', 'area_etiqueta_id', 'cipa', 'deu_baixa_epi', 'alternativas',
                'data_desmob', 'pendencia', 'pendencias_quais', 'outros',
                'preenchido_por_rh', 'preenchido_por_adm', 'preenchido_por_ssma',
                'data_entrega_area', 'biometria', 'data_biometria', 'pis',
                'prazo_experiencia', 'data_encerramento', 'acessar_area_porto',
                'avaliacao_psicologica', 'created_at', 'updated_at',
            ]
            : [
                'id', 'feedback_id', 'status', 'data_admissao', 'data_adm_prevista',
                'cargo', 'funcao', 'salario', 'contrato', 'tipo_admissao', 'centro_custo_id',
                'area_etiqueta_id', 'matricula', 'numero_cracha', 'prazo_experiencia',
                'created_at', 'updated_at',
            ];

        return DB::table('admissoes')
            ->whereIn('feedback_id', $feedbackIds)
            ->whereNull('deleted_at')
            ->select($cols)
            ->get()
            ->keyBy('feedback_id');
    }

    /**
     * @param  list<int>  $feedbackIds
     */
    private function carregarUltimosAso(array $feedbackIds): Collection
    {
        if ($feedbackIds === []) {
            return collect();
        }

        // Espelha UltimoAso: aprovado, realizado, atual — pega o de maior data_vencimento por feedback.
        $rows = DB::table('examesesmts')
            ->whereIn('feedback_id', $feedbackIds)
            ->where('exame_realizado', true)
            ->where('atual', true)
            ->whereJsonContains('resultado->aprovado', 'Sim')
            ->orderByDesc('data_vencimento')
            ->select(['id', 'feedback_id', 'data_realizacao', 'data_vencimento', 'created_at', 'updated_at'])
            ->get();

        $byFeedback = collect();
        foreach ($rows as $row) {
            if (! $byFeedback->has($row->feedback_id)) {
                $byFeedback->put($row->feedback_id, $row);
            }
        }

        return $byFeedback;
    }

    /**
     * @param  list<int>  $feedbackIds
     */
    private function carregarDemissoes(array $feedbackIds): Collection
    {
        return DB::table('demissaos')
            ->whereIn('feedback_id', $feedbackIds)
            ->select(['id', 'feedback_id', 'data_desmobilizacao', 'created_at', 'updated_at'])
            ->get()
            ->keyBy('feedback_id');
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, array<string, mixed>|null>
     */
    private function carregarVagasAbertas(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        $vagas = DB::table('vagas_abertas as va')
            ->leftJoin('vagas as v', 'v.id', '=', 'va.vaga_id')
            ->leftJoin('municipios as m', 'm.id', '=', 'va.municipio_id')
            ->whereIn('va.id', $ids)
            ->select([
                'va.id',
                'va.vaga_id',
                'va.titulo',
                'va.municipio_id',
                'va.ativo',
                'v.nome as vaga_nome',
                'v.ativo as vaga_ativo',
                'm.nome as municipio_nome',
                'm.uf as municipio_uf',
            ])
            ->get();

        return $vagas->mapWithKeys(function ($row) {
            return [
                (int) $row->id => [
                    'public_id' => ApiExternaId::encode((int) $row->id),
                    'vaga_public_id' => ApiExternaId::encode($row->vaga_id),
                    'titulo' => $row->titulo,
                    'ativo' => $row->ativo,
                    'vaga' => $row->vaga_id ? [
                        'public_id' => ApiExternaId::encode($row->vaga_id),
                        'nome' => $row->vaga_nome,
                        'ativo' => $row->vaga_ativo,
                    ] : null,
                    'municipio' => $row->municipio_id ? [
                        'public_id' => ApiExternaId::encode($row->municipio_id),
                        'nome' => $row->municipio_nome,
                        'uf' => $row->municipio_uf,
                    ] : null,
                    'label' => trim(implode(' - ', array_filter([
                        $row->vaga_nome,
                        $row->municipio_nome,
                        $row->municipio_uf,
                    ]))),
                ],
            ];
        });
    }

    /**
     * @param  list<int>  $ids
     */
    private function carregarClientes(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return DB::table('clientes as c')
            ->leftJoin('areas as a', 'a.id', '=', 'c.area_id')
            ->whereIn('c.id', $ids)
            ->select([
                'c.id',
                'c.razao_social',
                'c.nome',
                'c.cnpj',
                'c.cpf',
                'c.area_id',
                'a.label as area_nome',
            ])
            ->get()
            ->keyBy('id');
    }

    private function carregarEscolaridades(Collection $curriculos): Collection
    {
        $ids = $curriculos->pluck('formacao')->filter()->unique()->values()->all();
        if ($ids === []) {
            return collect();
        }

        return DB::table('escolaridades')
            ->whereIn('id', $ids)
            ->select(['id', 'tipo'])
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  list<int>  $curriculoIds
     */
    private function carregarDependentes(array $curriculoIds): Collection
    {
        if ($curriculoIds === []) {
            return collect();
        }

        if (! Schema::hasTable('usuario_dependentes')) {
            return collect();
        }

        return DB::table('usuario_dependentes')
            ->whereIn('user_id', $curriculoIds)
            ->select(['id', 'user_id', 'nome', 'nascimento', 'tipo', 'outro_tipo', 'cpf'])
            ->orderBy('id')
            ->get()
            ->groupBy('user_id');
    }

    /**
     * @param  list<int>  $admissaoIds
     */
    private function carregarDadosAdmissao(array $admissaoIds): Collection
    {
        $admissaoIds = array_values(array_filter(array_map('intval', $admissaoIds)));
        if ($admissaoIds === [] || ! Schema::hasTable('dados_admissaos')) {
            return collect();
        }

        return DB::table('dados_admissaos')
            ->whereIn('admissao_id', $admissaoIds)
            ->get()
            ->keyBy('admissao_id');
    }

    /**
     * @param  list<int>  $admissaoIds
     */
    private function carregarFerias(array $admissaoIds): Collection
    {
        $admissaoIds = array_values(array_filter(array_map('intval', $admissaoIds)));
        if ($admissaoIds === [] || ! Schema::hasTable('ferias_adquiridas')) {
            return collect();
        }

        return DB::table('ferias_adquiridas')
            ->whereIn('admissao_id', $admissaoIds)
            ->orderBy('id')
            ->get()
            ->groupBy('admissao_id');
    }

    /**
     * @param  list<int>  $feedbackIds
     */
    private function carregarPreAdmissao(array $feedbackIds): Collection
    {
        if (! Schema::hasTable('feedback_preadmissao')) {
            return collect();
        }

        return DB::table('feedback_preadmissao')
            ->whereIn('feedback_id', $feedbackIds)
            ->select(['id', 'feedback_id', 'user_finalizou_id', 'created_at', 'updated_at'])
            ->get()
            ->keyBy('feedback_id');
    }

    private function mapCurriculo(
        ?object $curriculo,
        bool $completo,
        Collection $escolaridades,
        Collection $dependentes,
        Collection $telefonesPorCurriculo
    ): ?array {
        if (! $curriculo) {
            return null;
        }

        $data = [
            'public_id' => ApiExternaId::encode((int) $curriculo->id),
            'nome' => $curriculo->nome,
            'estado_civil' => $curriculo->estado_civil,
            'naturalidade' => $curriculo->naturalidade,
            'nacionalidade' => $curriculo->nacionalidade,
            'carteira_trabalho' => $curriculo->carteira_trabalho,
            'cnh' => $curriculo->cnh,
            'cnh_vencimento' => $curriculo->cnh_vencimento,
            'sexo' => $curriculo->sexo,
            'cpf' => ApiExternaResponse::mascararCpf($curriculo->cpf),
            'rg' => $curriculo->rg,
            'rg_data_emissao' => $curriculo->rg_data_emissao,
            'orgao_expeditor' => $curriculo->orgao_expeditor,
            'logradouro' => $curriculo->logradouro,
            'end_numero' => $curriculo->end_numero,
            'complemento' => $curriculo->complemento,
            'bairro' => $curriculo->bairro,
            'municipio' => $curriculo->municipio,
            'uf' => $curriculo->uf,
            'cep' => $curriculo->cep,
            'filiacao_pai' => $curriculo->filiacao_pai,
            'filiacao_mae' => $curriculo->filiacao_mae,
            'pcd' => $curriculo->pcd,
            'nascimento' => $curriculo->nascimento,
            'email' => $curriculo->email,
            'disponibilidade_sabado' => $curriculo->disponibilidade_sabado,
            'disponibilidade_domingo' => $curriculo->disponibilidade_domingo,
            'formacao' => $curriculo->formacao,
            'formacao_curso' => $curriculo->formacao_curso,
            'formacao_status' => $curriculo->formacao_status,
            'vaga_pretendida' => $curriculo->vaga_pretendida ?? null,
        ];

        if ($completo) {
            $esc = $escolaridades->get($curriculo->formacao);
            $data['escolaridade'] = $esc ? [
                'public_id' => ApiExternaId::encode((int) $esc->id),
                'tipo' => $esc->tipo,
            ] : null;

            $deps = $dependentes->get($curriculo->id) ?? collect();
            $data['dependentes'] = $deps->map(function ($d) {
                return [
                    'public_id' => ApiExternaId::encode((int) $d->id),
                    'nome' => $d->nome,
                    'nascimento' => $d->nascimento ?? null,
                    'tipo' => $d->tipo ?? null,
                    'outro_tipo' => $d->outro_tipo ?? null,
                    'cpf' => ApiExternaResponse::mascararCpf($d->cpf ?? null),
                ];
            })->values()->all();

            $tels = $telefonesPorCurriculo->get($curriculo->id) ?? collect();
            $data['telefones'] = $tels->map(fn ($t) => $this->mapTelefone($t))->values()->all();
        }

        return $data;
    }

    private function mapTelefone(?object $tel): ?array
    {
        if (! $tel) {
            return null;
        }

        return [
            'public_id' => ApiExternaId::encode((int) $tel->id),
            'numero' => $tel->numero,
            'pais' => $tel->pais ?? null,
            'tipo' => $tel->tipo ?? null,
            'principal' => (bool) ($tel->principal ?? false),
        ];
    }

    private function mapConta(?object $conta): ?array
    {
        if (! $conta) {
            return null;
        }

        return [
            'public_id' => ApiExternaId::encode((int) $conta->id),
            'banco' => $conta->banco ?: 'Banco do Brasil',
            'agencia' => $conta->agencia ?: '',
            'conta' => $conta->conta ?: '',
            'pix' => (bool) $conta->pix,
            'tipochavepix' => $conta->tipochavepix ?: '',
            'chavepix' => $this->mascararChavePix($conta->chavepix),
        ];
    }

    private function mapResultadoIntegrado(?object $row): ?array
    {
        if (! $row) {
            return null;
        }

        return ApiExternaId::mapRow($row, [
            'id',
            'feedback_id',
            'formulario_id',
            'pcmso_id',
            'empresa_exame_id',
        ], ['usuario_id', 'acesso_codigo_hash', 'acesso_codigo_expira_em', 'acesso_codigo_usado_em', 'public_id']);
    }

    private function mapAdmissao(
        ?object $admissao,
        bool $completo,
        Collection $dadosAdmissao,
        Collection $ferias,
        ?object $ultimoAso
    ): ?array {
        if (! $admissao) {
            return null;
        }

        $data = ApiExternaId::mapRow($admissao, [
            'id',
            'feedback_id',
            'centro_custo_filial_id',
            'formulario_id',
            'centro_custo_id',
            'segmento_treinamento_id',
            'area_etiqueta_id',
        ], ['usuario_id', 'editado_usuario_id', 'user_avaliacao', 'usuario_desmob']);

        if (array_key_exists('pis', $data) && $data['pis']) {
            $data['pis'] = $this->mascararFinal((string) $data['pis'], 4);
        }

        $data['ultimo_aso'] = $this->mapAso($ultimoAso);

        if ($completo) {
            $dados = $dadosAdmissao->get($admissao->id);
            $data['dados_admissao'] = $dados
                ? ApiExternaId::mapRow($dados, ['id', 'admissao_id'])
                : null;

            $listaFerias = $ferias->get($admissao->id) ?? collect();
            $data['ferias_adquiridas'] = $listaFerias->map(
                fn ($f) => ApiExternaId::mapRow($f, ['id', 'admissao_id'])
            )->values()->all();
        }

        return $data;
    }

    private function mapAso(?object $aso): ?array
    {
        if (! $aso) {
            return null;
        }

        return ApiExternaId::mapRow($aso, ['id', 'feedback_id']);
    }

    private function mapDemissao(?object $demissao): ?array
    {
        if (! $demissao) {
            return null;
        }

        return ApiExternaId::mapRow($demissao, ['id', 'feedback_id']);
    }

    private function mapEmpresa(?object $empresa): ?array
    {
        if (! $empresa) {
            return null;
        }

        return [
            'public_id' => ApiExternaId::encode((int) $empresa->id),
            'razao_social' => $empresa->razao_social,
            'nome' => $empresa->nome,
            'cnpj' => $empresa->cnpj,
            'cpf' => ApiExternaResponse::mascararCpf($empresa->cpf),
            'area' => $empresa->area_id ? [
                'public_id' => ApiExternaId::encode((int) $empresa->area_id),
                'nome' => $empresa->area_nome,
            ] : null,
        ];
    }

    /**
     * @param  list<string>  $idKeys
     */
    private function mapModelRow(?object $row, array $idKeys = ['id']): ?array
    {
        if (! $row) {
            return null;
        }

        return ApiExternaId::mapRow($row, $idKeys);
    }

    private function mascararChavePix(?string $chave): string
    {
        if ($chave === null || $chave === '') {
            return '';
        }

        return $this->mascararFinal($chave, 4);
    }

    private function mascararFinal(string $value, int $visible): string
    {
        $len = mb_strlen($value);
        if ($len <= $visible) {
            return str_repeat('*', $len);
        }

        return str_repeat('*', $len - $visible) . mb_substr($value, -$visible);
    }
}
