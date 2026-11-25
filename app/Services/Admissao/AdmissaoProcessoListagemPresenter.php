<?php

namespace App\Services\Admissao;

use App\Models\Admissao;
use App\Models\CentroCusto;
use App\Models\Curriculo;
use App\Models\TelefoneCurriculo;
use App\Services\Curriculo\Foto3x4ListagemCache;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Orquestra e transforma a listagem do processo de Admissão exibida no card/tabela.
 * Extraído de AdmissaoController::atualizar (TD-002 fatia 3).
 */
class AdmissaoProcessoListagemPresenter
{
    /**
     * Orquestra paginate + transformação dos itens + selects de apoio para a resposta
     * JSON de AdmissaoController::atualizar.
     *
     * @return array<string, mixed>
     */
    public function montarResposta(Request $request): array
    {
        $pg = app(AdmissaoProcessoFiltroQuery::class)->aplicar($request)->paginate($request->pages ?: 20);
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);

        $itens = $this->transformarItens(collect($pg->items()), $cc);

        $selects = app(AdmissaoListSelectsService::class)->listSelects();

        $dados = [
            'itens' => $itens,
            'admissao_processo_dados_editar' => auth()->user()->can('privilegio_admissao_processo_dados_editar'),
            'status_admissao' => array_merge(['EM PROCESSO'], Admissao::TODOS_STATUS_ADMISSAO),
            'tipos_admissao' => Admissao::TODOS_TIPOS_ADMISSAO,
            'status_carteira_treinamento' => Admissao::TODOS_STATUS_CARTEIRA_TREINAMETO,
            'padroes_treinamento' => $selects['padroes_treinamento'],
            'lista_sexos' => Curriculo::TIPOS_SEXOS,
            'lista_estados_civis' => Curriculo::ESTADOS_CIVIS,
            'permissoes' => [
                'filtrar_demitido' => auth()->user()->can('admissao_historico_filtrar_demitido'),
                'privilegio_processo_demitir' => auth()->user()->can('privilegio_processo_demitir'),
            ],
            'cc' => $cc,
        ];

        Foto3x4ListagemCache::agendarAquecimentoAposResposta();

        return [
            'atual' => $pg->currentPage(),
            'ultima' => $pg->lastPage(),
            'total' => $pg->total(),
            'dados' => $dados,
        ];
    }

    /**
     * Transforma os itens (FeedbackCurriculo) paginados da listagem do processo,
     * aplicando setVisible/setAppends e materializando os campos usados nos cards.
     *
     * @param  iterable  $itens
     * @param  array<string, mixed>  $cc  Centros de custo por CNPJ (CentroCusto::listaCentroCustoPorCnpj).
     */
    public function transformarItens(iterable $itens, array $cc): Collection
    {
        return collect($itens)->transform(function ($item) use ($cc) {
            // Mantém só appends da listagem; materializa cargo antes de esconder VagaAberta
            $item->setAppends(['vaga_aberta_municipio', 'fc_token']);
            $item->setAttribute('vaga_aberta_municipio', $item->vaga_aberta_municipio);
            $item->unsetRelation('VagaAberta');
            $item->makeHidden(['empresa_id', 'curriculo_id', 'vagas_abertas_id', 'created_at']);

            if ($item->Curriculo) {
                $foto = $item->Curriculo->relationLoaded('FotoTres')
                    ? $item->Curriculo->FotoTres->first()
                    : null;
                $item->Curriculo->setAppends([]);
                $item->Curriculo->setAttribute(
                    'foto_3x4_thumb',
                    Foto3x4ListagemCache::paraListagem($foto)
                );
                $item->Curriculo->unsetRelation('FotoTres');

                // Materializa telefone principal (evita sumir no setVisible / appends do model)
                $telPrincipal = $item->Curriculo->relationLoaded('TelPrincipal')
                    ? $item->Curriculo->TelPrincipal
                    : null;
                $item->Curriculo->unsetRelation('TelPrincipal');
                $item->Curriculo->setAttribute(
                    'tel_principal',
                    $this->montarTelefonePrincipal($telPrincipal?->numero, $telPrincipal?->tipo)
                );

                $item->Curriculo->setVisible(['id', 'nome', 'cpf', 'pcd', 'foto_3x4_thumb', 'tel_principal']);
            }

            if ($item->ResultadoIntegrado) {
                $item->ResultadoIntegrado->setVisible([
                    'documentos_entregue',
                    'documentos_entregue_data',
                    'encaminhado_exame',
                    'encaminhado_exame_data',
                    'encaminhado_treinamento',
                    'encaminhado_treinamento_data',
                    'responsavel_envio',
                ]);
            }

            if ($item->UltimoAso) {
                $item->UltimoAso->setVisible(['data_realizacao']);
            }

            // Eager-load usa a chave 'Admissao' (case-sensitive). Acessar $item->admissao
            // dispara lazy-load sem UltimoAso/Demissao e sobrescreve a chave snake no JSON.
            $admissao = $item->relationLoaded('Admissao') ? $item->getRelation('Admissao') : null;
            if ($admissao) {
                $dadosCentroCusto = $this->resolverDadosCentroCusto($cc, $admissao->centro_custo_id);
                $admissao->emp_cnpj = $dadosCentroCusto['emp_cnpj'];
                $admissao->emp_nome_fantasia = $dadosCentroCusto['emp_nome_fantasia'];
                $admissao->emp_centro_custo = $dadosCentroCusto['emp_centro_custo'];
                $admissao->emp_tipo = $dadosCentroCusto['emp_tipo'];

                if ($admissao->relationLoaded('UltimoAso') && $admissao->getRelation('UltimoAso')) {
                    $admissao->getRelation('UltimoAso')->setVisible(['data_realizacao']);
                }
                if ($admissao->relationLoaded('Demissao') && $admissao->getRelation('Demissao')) {
                    $admissao->getRelation('Demissao')->setVisible(['data_desmobilizacao']);
                }

                // setVisible filtra relações pela chave em $relations (antes do snake_case no JSON).
                $admissao->setVisible([
                    'id',
                    'feedback_id',
                    'status',
                    'tipo_admissao',
                    'data_admissao',
                    'numero_cracha',
                    'centro_custo_id',
                    'cargo',
                    'funcao',
                    'emp_cnpj',
                    'emp_nome_fantasia',
                    'emp_centro_custo',
                    'emp_tipo',
                    'UltimoAso',
                    'Demissao',
                ]);
            }

            return $item;
        });
    }

    /**
     * Monta o bloco de telefone principal exposto na listagem (numero/tipo/whatsapp).
     * Método puro (sem Eloquent/DB) extraído para facilitar testes isolados.
     */
    public function montarTelefonePrincipal(?string $numero, ?string $tipo): ?array
    {
        if ($numero === null || $numero === '') {
            return null;
        }

        return [
            'numero' => (string) $numero,
            'tipo' => $tipo ? (string) $tipo : null,
            'whatsapp' => $tipo === TelefoneCurriculo::TIPO_WHATS,
        ];
    }

    /**
     * Resolve os dados do centro de custo (cnpj/nome fantasia/label/tipo) do colaborador
     * a partir da listagem combinada por CNPJ (CentroCusto::listaCentroCustoPorCnpj).
     * Método puro (sem Eloquent/DB) extraído para facilitar testes isolados.
     *
     * @param  array<string, mixed>  $cc
     * @return array{emp_cnpj: ?string, emp_nome_fantasia: ?string, emp_centro_custo: ?string, emp_tipo: ?string}
     */
    public function resolverDadosCentroCusto(array $cc, $centroCustoId): array
    {
        $vazio = [
            'emp_cnpj' => null,
            'emp_nome_fantasia' => null,
            'emp_centro_custo' => null,
            'emp_tipo' => null,
        ];

        $ccColaborador = collect($cc['centros_custos'] ?? [])->collapse()->where('id', $centroCustoId)->first();

        if (!$ccColaborador) {
            return $vazio;
        }

        return [
            'emp_cnpj' => $ccColaborador['cnpj_format'],
            'emp_nome_fantasia' => $ccColaborador['nome_fantasia'],
            'emp_centro_custo' => $ccColaborador['label'],
            'emp_tipo' => $ccColaborador['matriz'] ? 'Matriz' : 'Filial',
        ];
    }
}
