<template>
    <div>
        <!--Janela de filtrar-->
        <modal id="janelaFiltrarLancamentos" ref="janelaFiltrarLancamentos" :modal-pai="modalPai" size="g" titulo="Filtrar lançamentos">
            <template #conteudo>
                <p>Filtre os lançamentos por planos de conta dentro do período</p>
                <div v-if="abaMovimentacoes.lista.length > 0" class="table-responsive">
                    <table class="tabela">
                        <thead class="bg-default">
                            <tr>
                                <th class="text-center">
                                    <div class="form-group form-check">
                                        <input
                                            v-model="abaMovimentacoes.tudoMarcado"
                                            class="form-check-input"
                                            type="checkbox"
                                            @change="selecionarTodosPlanosFiltro"
                                        />
                                    </div>
                                </th>
                                <th>Plano de conta</th>
                                <th>Operação</th>
                                <th>Categoria</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(obj, index) in abaMovimentacoes.listaPlanosFiltro" :key="obj.id">
                                <td class="text-center">
                                    <div class="form-group form-check">
                                        <input
                                            v-model="abaMovimentacoes.filtrarPor"
                                            :value="obj.id"
                                            class="form-check-input"
                                            type="checkbox"
                                            @change="verificarCheckFiltrarTudo"
                                        />
                                    </div>
                                </td>
                                <td>
                                    {{ obj.descricao }}
                                </td>
                                <td>
                                    {{ obj.operacaoText }}
                                </td>
                                <td>
                                    {{ obj.categoria.descricao }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
            <template #rodape>
                <button
                    :disabled="abaMovimentacoes.listaPlanosFiltro.length === 0"
                    class="btn btn-sm btn-success"
                    type="button"
                    @click="atualizaContaCorrente(); $refs.janelaFiltrarLancamentos.fecharModal()"
                >
                    <i class="fas fa-filter"></i> Filtrar lançamentos
                </button>
            </template>
        </modal>

        <!--Janela de Apagar lançamento-->
        <modal id="janelaApagarLancamento" :fechar="!formApagar.preload" :modal-pai="modalPai" titulo="Excluir lançamento">
            <template #conteudo>
                <span v-show="formApagar.preload"> <i class="fa fa-spinner fa-pulse"></i> Apagando lançamento... </span>

                <div v-show="!formApagar.preload && !formApagar.delete && !formApagar.erro" class="alert alert-warning" role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-exclamation-triangle"></i><br />
                        Atenção! Deseja excluir esse lançamento?
                    </h4>
                </div>

                <div v-show="!formApagar.preload && formApagar.delete && !formApagar.erro" class="alert alert-success" role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-check"></i><br />
                        O lançamento foi apagado
                    </h4>
                </div>

                <div v-show="!formApagar.preload && !formApagar.delete && formApagar.erro" class="alert alert-danger" role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-times-circle"></i><br />
                        {{ formApagar.msg }}
                    </h4>
                </div>
            </template>
            <template #rodape>
                <button
                    v-show="!formApagar.preload && !formApagar.delete && !formApagar.erro"
                    class="btn btn-sm btn-danger"
                    type="button"
                    @click="apagarLancamento"
                >
                    <i class="fas fa-trash-alt"></i> Apagar lançamento
                </button>
            </template>
        </modal>

        <!--Janela de Inserir/Alterar lançamento-->
        <modal id="janelaLancamento" :fechar="!formLancamento.preload" :modal-pai="modalPai" :size="90" :titulo="formLancamento.titulo">
            <template #conteudo>
                <span v-show="formLancamento.preload">
                    <span v-show="formLancamento.editando"> <i class="fa fa-spinner fa-pulse"></i> Alterando lançamento... </span>
                    <span v-show="!formLancamento.editando"> <i class="fa fa-spinner fa-pulse"></i> Cadastrando lançamento... </span>
                </span>
                <form v-if="!formLancamento.preload && !formLancamento.erro && !formLancamento.cadastrado && !formLancamento.atualizado && cliente">
                    <div v-if="formLancamento.updated_at" class="form-group row">
                        <label v-show="formLancamento.created_at" class="col-sm-2 col-form-label">Cadastrado:</label>
                        <div v-if="formLancamento.quem_cadastrou" class="col-sm-10">
                            <input
                                :value="`Em ${formLancamento.created_at} por ${formLancamento.quem_cadastrou.nome}`"
                                class="form-control-plaintext"
                                readonly
                                type="text"
                            />
                        </div>

                        <label v-show="formLancamento.created_at !== formLancamento.updated_at" class="col-sm-2 col-form-label">Última alteração:</label>
                        <div v-if="formLancamento.quem_alterou && formLancamento.created_at !== formLancamento.updated_at" class="col-sm-10">
                            <input
                                :value="`Em ${formLancamento.updated_at} por ${formLancamento.quem_alterou.nome}`"
                                class="form-control-plaintext"
                                readonly
                                type="text"
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            Nome do Cliente: <strong>{{ cliente.razao_social }}</strong>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>Informações do Lançamento</legend>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Data e hora</label>
                                            <datepicker v-model="formLancamento.data_hora" label="" style="margin-top: -19px" :hora="true"></datepicker>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Plano de conta</label>
                                            <autocomplete
                                                :formsm="false"
                                                v-model="formLancamento.plano_conta.descricao"
                                                :rows="20"
                                                :valido="formLancamento.plano_id > 0"
                                                caminho="fluxo-caixa/buscaNomePlanoConta"
                                                placeholder="Busque um plano de conta"
                                                @input="resetNomePlano"
                                                @onselect="selecionaPlano"
                                            ></autocomplete>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tipo</label>
                                            <select
                                                v-model="formLancamento.operacao"
                                                :disabled="formLancamento.plano_conta.operacao !== 'T'"
                                                class="form-control"
                                            >
                                                <option value="">Selecione...</option>
                                                <option value="C">Crédito</option>
                                                <option value="D">Débito</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Descrição</label>
                                            <textarea v-model="formLancamento.descricao" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Valor</label>
                        <h3>R$ {{ formatDinheiro(valorTotalLancamento) }}</h3>
                    </div>
                    <!-- formas de pagamento-->
                    <fieldset>
                        <legend>Formas de pagamento</legend>

                        <button class="btn btn-secondary" type="button" @click="addFormaPagamento">Adicionar</button>
                        <div v-if="formLancamento.formas.length > 0" class="table-responsive">
                            <table class="tabela">
                                <thead class="bg-default">
                                    <tr>
                                        <th scope="col">Forma</th>
                                        <th scope="col">Valor</th>
                                        <th scope="col">Observações</th>
                                        <th scope="col">Remover</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(obj, index) in formLancamento.formas">
                                        <td>
                                            <select v-model="obj.forma_pagamento_id" class="form-control">
                                                <option :value="0">Selecione...</option>
                                                <option v-for="forma in cliente.formas_pagamento" :disabled="!forma.ativo" :value="forma.id">
                                                    {{ forma.descricao }}
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                            <input v-model="obj.valorFormat" v-mascara:dinheiroPN class="form-control" type="text" />
                                        </td>
                                        <td>
                                            <textarea v-model="obj.observacoes" class="form-control" rows="3"></textarea>
                                        </td>

                                        <td class="text-center">
                                            <button class="btn btn-danger" type="button" @click="apagarForma(index)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                    <!-- Lançamentos futuros-->
                    <fieldset>
                        <legend>Agendamentos</legend>
                        <div class="form-check mb-2">
                            <input id="agendarLancamento" v-model="formLancamento.agendar" class="form-check-input" type="checkbox" />
                            <label class="form-check-label" for="agendarLancamento">Agendar recebimento/pagamento</label>
                        </div>

                        <div v-show="formLancamento.agendar" class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <datepicker v-model="formLancamento.data_pendete"></datepicker>
                                </div>
                            </div>
                        </div>
                        <div v-show="formLancamento.agendar && pode_realizar" class="row">
                            <div class="col-md-2">
                                <div class="form-check form-switch form-switch-lg mb-3" dir="ltr">
                                    <input id="checkboxConcluido" v-model="formLancamento.concluido" class="form-check-input" type="checkbox" />
                                    <label class="form-check-label" for="checkboxConcluido">Realizado</label>
                                </div>
                            </div>
                        </div>

                        <div v-show="formLancamento.agendar && formLancamento.concluido && pode_realizar" class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <datepicker v-model="formLancamento.data_hora_concluido" :hora="true" posicao="up"></datepicker>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div
                    v-show="!formLancamento.preload && !formLancamento.erro && (formLancamento.cadastrado || formLancamento.atualizado)"
                    class="alert alert-success"
                    role="alert"
                >
                    <h4 class="text-center">
                        <i class="fas fa-check"></i><br />
                        <span v-show="formLancamento.cadastrado">Lançamento cadastrado</span>
                        <span v-show="formLancamento.atualizado">Lançamento alterado</span>
                    </h4>
                </div>
                <div v-show="!formLancamento.preload && formLancamento.erro" class="alert alert-danger" role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-times-circle"></i><br />
                        {{ formLancamento.msg }}
                    </h4>
                </div>
            </template>
            <template #rodape>
                <button
                    v-show="
                        !formLancamento.preload && !formLancamento.erro && !formLancamento.cadastrado && !formLancamento.atualizado && !formLancamento.editando
                    "
                    class="btn btn-sm btn-primary"
                    type="button"
                    @click="salvarLancamento"
                >
                    <i class="fas fa-plus"></i> Inserir lançamento
                </button>
                <button
                    v-if="
                        !formLancamento.preload && !formLancamento.erro && !formLancamento.cadastrado && !formLancamento.atualizado && formLancamento.editando
                    "
                    class="btn btn-sm btn-primary"
                    type="button"
                    @click="salvarLancamento"
                >
                    <i class="far fa-edit"></i> Alterar lançamento
                </button>
            </template>
        </modal>

        <!-- ------------------------------------------------------------------------------------------------------- -->

        <p v-show="preload && !cliente" class="text-center"><i class="fa fa-spinner fa-pulse"></i> Buscando dados do cliente...</p>

        <div v-show="!preload && !cliente" class="alert alert-danger" role="alert">
            <h4 class="text-center">
                <i class="fas fa-times-circle"></i><br />
                Cliente não encontrado
            </h4>
        </div>

        <div v-if="!preload && cliente">
            <h4 style="display: inline">{{ cliente.razao_social }}</h4>
            <small> ({{ cliente.tipo }})</small>
            <ul class="nav nav-tabs bg-light mt-3" role="tablist" style="border-bottom: 1px solid #653232">
                <li class="nav-item">
                    <a
                        id="aba-movimentacoes-tab"
                        aria-controls="aba-movimentacoes"
                        aria-selected="true"
                        class="nav-link active"
                        data-toggle="pill"
                        href="#aba-movimentacoes"
                        role="tab"
                        >Lançamentos</a
                    >
                </li>
            </ul>

            <div class="tab-content mb-3">
                <!-- Aba movimentacoes -->
                <div id="aba-movimentacoes" aria-labelledby="aba-movimentacoes-tab" class="tab-pane fade show active" role="tabpanel">
                    <div class="col mt-2 mb-2">
                        <div class="form-row">
                            <div class="col-12 col-md-4">
                                <div class="form-check mb-2">
                                    <input
                                        id="por_periodo_lancamento"
                                        v-model="controle.dados.por_periodo"
                                        class="form-check-input"
                                        type="checkbox"
                                        @change="atualizaContaCorrente"
                                    />
                                    <label class="form-check-label" for="por_periodo_lancamento">Por período</label>
                                </div>
                                <datepicker
                                    v-model="abaMovimentacoes.intervalo"
                                    :disabled="abaMovimentacoes.preload || !controle.dados.por_periodo"
                                    placeholder="Informe um intervalo de data"
                                    label=""
                                    range
                                    style="margin-top: -19px"
                                    separador=" até "
                                    @onselect="atualizaContaCorrente"
                                >
                                </datepicker>
                            </div>
                            <div class="col-12 col-md-8">
                                <form action="" @submit.prevent="$refs.paginacao.buscar()">
                                    <label>Busca: </label>
                                    <div class="input-group">
                                        <input v-model="controle.dados.campoBusca" aria-describedby="" aria-label="" class="form-control" type="text" />
                                        <button
                                            v-show="controle.dados.campoBusca !== ''"
                                            class="btn btn-danger"
                                            type="button"
                                            @click="controle.dados.campoBusca = ''; atualizaContaCorrente()"
                                        >
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col mt-2 mb-2">
                        <button :disabled="abaMovimentacoes.preload" class="btn btn-sm btn-success" type="button" @click="atualizaContaCorrente">
                            <i class="fas fa-sync"></i> Atualizar
                        </button>
                        <button
                            v-if="pode_insert"
                            :disabled="abaMovimentacoes.preload"
                            class="btn btn-sm btn-primary"
                            data-target="#janelaLancamento"
                            data-toggle="modal"
                            type="button"
                            @click="formNovoLancamento"
                        >
                            <i class="fas fa-plus"></i> Novo lançamento
                        </button>
                        <button
                            :class="{
                                'btn-outline-primary': abaMovimentacoes.filtrarPor.length === 0,
                                'btn-primary': abaMovimentacoes.filtrarPor.length > 0
                            }"
                            :disabled="abaMovimentacoes.preload"
                            class="btn btn-sm"
                            data-target="#janelaFiltrarLancamentos"
                            data-toggle="modal"
                            type="button"
                        >
                            <i class="fas fa-filter"></i>
                            Filtrar <span v-show="abaMovimentacoes.filtrarPor.length > 0" class="badge badge-success">ativo</span>
                        </button>
                    </div>

                    <preload v-show="abaMovimentacoes.preload" class="text-center" label="Buscando lançamentos..."></preload>

                    <!-- lista lançamentos -->
                    <div v-if="!abaMovimentacoes.preload" class="col-12">
                        <h4 v-if="abaMovimentacoes.lista.length === 0" class="text-center">
                            Nenhum lançamento encontrato no período
                            <span v-show="controle.dados.campoBusca !== ''"> com o termo"{{ controle.dados.campoBusca }}" </span>
                        </h4>

                        <table v-if="abaMovimentacoes.lista.length > 0 && !abaMovimentacoes.preload" class="tabela">
                            <thead class="bg-default">
                                <tr>
                                    <th scope="col">Data/Hora</th>
                                    <th scope="col" width="30%">Plano de conta</th>
                                    <th class="text-right" scope="col">Valor</th>
                                    <th class="text-right" scope="col">Saldo</th>
                                    <th class="text-right" scope="col">Projetado</th>
                                    <th scope="col">Agendado</th>
                                    <th scope="col">Realizado</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td><strong class="text-primary">Saldo anterior</strong></td>
                                    <td
                                        :class="{ 'text-primary': lancamentosSaldoTotalAntetior >= 0, 'text-danger': lancamentosSaldoTotalAntetior < 0 }"
                                        class="text-right"
                                    >
                                        R$ {{ formatDinheiro(lancamentosSaldoTotalAntetior) }}
                                    </td>
                                    <td
                                        :class="{ 'text-primary': lancamentosSaldoTotalAntetior >= 0, 'text-danger': lancamentosSaldoTotalAntetior < 0 }"
                                        class="text-right"
                                    >
                                        R$ {{ formatDinheiro(lancamentosSaldoTotalAntetior) }}
                                    </td>
                                    <td colspan="4"></td>
                                </tr>
                                <tr v-for="(lan, index) in abaMovimentacoes.lista" :key="lan.id">
                                    <td>{{ lan.data_hora }}</td>
                                    <td>
                                        {{ lan.plano_conta.descricao }}<br />
                                        <small v-if="lan.descricao != ''">{{ lan.descricao }}</small>
                                    </td>
                                    <td :class="{ 'text-primary': lan.valor >= 0, 'text-danger': lan.valor < 0 }" class="text-right">
                                        R$ {{ formatDinheiro(lan.valor) }} {{ lan.operacao }}
                                    </td>
                                    <td
                                        :class="{
                                            'text-primary': lan.saldoAtual >= 0,
                                            'text-danger': lan.saldoAtual < 0
                                        }"
                                        class="text-right"
                                    >
                                        <strong v-show="lan.concluido">R$ {{ formatDinheiro(lan.saldoAtual) }}</strong>
                                    </td>
                                    <td
                                        :class="{
                                            'text-primary': lan.saldo >= 0,
                                            'text-danger': lan.saldo < 0
                                        }"
                                        class="text-right"
                                    >
                                        <strong v-show="!lan.concluido">R$ {{ formatDinheiro(lan.saldo) }}</strong>
                                    </td>
                                    <td>
                                        <template v-if="lan.data_pendente !== null">
                                            <span v-if="lan.diasAtraso > 0" class="text-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </span>
                                            {{ lan.data_pendente }}
                                            <template v-if="!lan.concluido">
                                                <br />
                                                <small v-if="lan.diasAtraso < 0">Faltam {{ lan.diasAtraso * -1 }} dias</small>
                                                <small v-if="lan.diasAtraso > 0">{{ lan.diasAtraso }} dias atrasado</small>
                                            </template>
                                        </template>
                                    </td>
                                    <td>
                                        <template v-if="lan.data_hora_concluido !== null && lan.concluido">
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i>
                                            </span>
                                            {{ lan.data_hora_concluido }}
                                            <template v-if="lan.concluido">
                                                <br />
                                                <small v-if="lan.diasAtrasoConcluido < 0">{{ lan.diasAtrasoConcluido * -1 }} dias antes</small>
                                                <small v-if="lan.diasAtrasoConcluido > 0">{{ lan.diasAtrasoConcluido }} dias depois</small>
                                            </template>
                                        </template>
                                    </td>
                                    <td class="text-center">
                                        <div v-if="pode_update || pode_delete" class="dropdown">
                                            <button
                                                aria-expanded="false"
                                                aria-haspopup="true"
                                                class="btn dropdown-toggle btn-outline-secondary"
                                                data-toggle="dropdown"
                                                type="button"
                                            >
                                                Opções
                                            </button>
                                            <div aria-labelledby="dropdownMenuButton" class="dropdown-menu dropdown-menu-custom">
                                                <template v-if="formRealizar.preload && pode_realizar">
                                                    <a v-if="!formRealizar.preload && pode_realizar" class="dropdown-item disabled">
                                                        <i class="fa fa-spinner fa-pulse"></i> Agaurde ...
                                                    </a>
                                                </template>
                                                <a
                                                    v-if="!formRealizar.preload && pode_realizar && !lan.concluido && lan.data_pendente != null"
                                                    class="dropdown-item text-success"
                                                    href="#"
                                                    @click.prevent="mudarStatus(lan, true)"
                                                >
                                                    <i class="fas fa-check"></i> Realizar
                                                </a>
                                                <a
                                                    v-if="!formRealizar.preload && pode_realizar && lan.concluido && lan.data_pendente != null"
                                                    class="dropdown-item text-danger"
                                                    href="#"
                                                    @click.prevent="mudarStatus(lan, false)"
                                                >
                                                    <i class="fas fa-undo"></i> Desfazer
                                                </a>
                                                <a
                                                    v-if="pode_update"
                                                    class="dropdown-item text-primary"
                                                    data-target="#janelaLancamento"
                                                    data-toggle="modal"
                                                    href="#"
                                                    @click="formEditarLancamento(lan)"
                                                >
                                                    <i class="far fa-edit"></i> Editar
                                                </a>
                                                <a
                                                    v-if="pode_delete"
                                                    class="dropdown-item text-danger"
                                                    data-target="#janelaApagarLancamento"
                                                    data-toggle="modal"
                                                    href="#"
                                                    @click="formApagarLancamento(lan)"
                                                >
                                                    <i class="far fa-trash-alt"></i> Apagar
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-right"><strong class="text-primary">Resultado anterior</strong></td>
                                    <td class="text-right">
                                        <strong>
                                            <span
                                                :class="{
                                                    'text-primary': lancamentosSaldoTotalAntetior >= 0,
                                                    'text-danger': lancamentosSaldoTotalAntetior < 0
                                                }"
                                            >
                                                R$ {{ formatDinheiro(lancamentosSaldoTotalAntetior) }}</span
                                            >
                                        </strong>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-right"><strong class="text-primary">Total de receitas</strong></td>
                                    <td class="text-right">
                                        <strong>
                                            <span class="text-primary">R$ {{ formatDinheiro(lancamentosTotalReceitas) }}</span>
                                        </strong>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-right"><strong class="text-danger">Total de despesas</strong></td>
                                    <td class="text-right">
                                        <strong>
                                            <span class="text-danger"> R$ {{ formatDinheiro(lancamentosTotalDespesas) }} </span>
                                        </strong>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-right"><strong class="text-success">Resultado</strong></td>
                                    <td class="text-right">
                                        <strong>
                                            <span :class="{ 'text-primary': lancamentosSaldoTotal >= 0, 'text-danger': lancamentosSaldoTotal < 0 }">
                                                R$ {{ formatDinheiro(lancamentosSaldoTotal + lancamentosSaldoTotalAntetior) }}
                                            </span>
                                        </strong>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <controle-paginacao
                        ref="paginacao"
                        :dados="controle.dados"
                        :url="`fluxo-caixa/${id}/atualizaFluxoCaixa`"
                        class="d-flex justify-content-center"
                        por-pagina="30"
                        @carregando="carregando"
                        @carregou="carregou"
                    ></controle-paginacao>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import datepicker from '../DatePicker'
import autocomplete from '../AutoComplete'
import dinheiro from '../../filters/Dinheiro'
import controlePaginacao from '../ControlePaginacao'

export default {
    components: {
        datepicker,
        autocomplete,
        controlePaginacao
    },
    props: {
        id: {
            type: Number,
            required: true,
            default: 0
        },
        leitura: {
            type: Boolean,
            required: false,
            default: false
        },
        modalPai: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            URL_ADMIN: URL_ADMIN,
            preload: false,
            cliente: null,
            lancamento_id: null,

            //permissoes
            pode_insert: false,
            pode_update: false,
            pode_delete: false,
            pode_realizar: false,

            url_imprimir: `${URL_ADMIN}/fluxo-caixa/${this.id}/`,
            CSRF_token: CSRF_token, // verificar /application/resources/bootstrap.js linha 47

            controle: {
                preload: false,
                dados: {
                    campoBusca: '',
                    por_periodo: true
                }
            },
            abaMovimentacoes: {
                preload: false,
                intervalo: '',
                lista: [],
                listaPlanosFiltro: [],

                filtrarPor: [],
                tudoMarcado: false
            },

            formLancamento: {
                id: null,
                preload: false,
                editando: false,
                titulo: 'Novo lançamento',

                created_at: null,
                quem_cadastrou: null,

                updated_at: null,
                quem_alterou: null,

                data_hora: '',
                plano_conta: {
                    descricao: '',
                    operacao: ''
                },
                plano_id: null,
                operacao: '',
                descricao: '',

                formas: [],
                formasDelete: [],

                agendar: false,
                data_pendete: '',
                data_hora_concluido: '',
                concluido: false,

                cadastrado: false,
                atualizado: false,
                erro: false,
                msg: ''
            },
            formLancamentoDefault: null,

            lancamentosTotalReceitas: 0.0,
            lancamentosTotalDespesas: 0.0,
            lancamentosSaldoTotal: 0.0,
            lancamentosSaldoTotalAntetior: 0.0,

            formApagar: {
                preload: false,
                delete: false,
                erro: false,
                msg: ''
            },

            formRealizar: {
                preload: false
            },

            formImprimir: {
                resumo: false
            }
        }
    },
    mounted() {
        this.formLancamentoDefault = _.cloneDeep(this.formLancamento)
        this.$emit('carregando', {})

        this.preload = true
        axios
            .get(`${URL_ADMIN}/fluxo-caixa/${this.id}`)
            .then((response) => {
                let data = response.data
                this.cliente = data.cliente
                this.abaMovimentacoes.intervalo = `${data.dataInicial} até ${data.dataFinal}`
                this.pode_insert = data.pode_insert
                this.pode_update = data.pode_update
                this.pode_delete = data.pode_delete
                this.pode_realizar = data.pode_realizar
                this.preload = false

                setTimeout(() => {
                    this.atualizaContaCorrente()
                }, 300) // para aguardar o componente aparecer dentro de this.$refs (paginacao)

                this.$emit('carregou', {})
            })
            .catch((data) => {
                this.preload = false
            })
    },

    computed: {
        lancamentoAtual() {
            return this.lancamento_id !== null ? this.abaMovimentacoes.lista[this.lancamento_id] : false
        },
        valorTotalLancamento() {
            if (this.formLancamento.formas.length > 0) {
                return _.sumBy(this.formLancamento.formas, (formas) => {
                    return convertFloat(formas.valorFormat)
                })
            }
            return 0
        }
    },
    methods: {
        formatDinheiro(valor) {
            return dinheiro(valor)
        },
        //Aba movimentações ------------------------------------------------------------
        carregou(data) {
            this.abaMovimentacoes.lista = data.lista
            this.abaMovimentacoes.listaPlanosFiltro = data.planos_filtro
            this.lancamentosTotalReceitas = data.total_receitas
            this.lancamentosTotalDespesas = data.total_despesas
            this.lancamentosSaldoTotal = data.saldo_total
            this.lancamentosSaldoTotalAntetior = data.saldoAntetior
            this.ajustarSaldoRealiados()

            this.abaMovimentacoes.preload = false

            if (this.abaMovimentacoes.lista.length === 0) {
                this.abaMovimentacoes.filtrarPor = []
            }
        },
        carregando() {
            this.abaMovimentacoes.preload = true
            this.abaMovimentacoes.lista = []
        },

        atualizaContaCorrente() {
            this.controle.dados.intervalo = this.abaMovimentacoes.intervalo
            this.controle.dados.imovel_id = this.abaMovimentacoes.imovel_id
            this.controle.dados.filtrar = this.abaMovimentacoes.filtrarPor

            //this.abaMovimentacoes.filtrarPor = [];
            //this.abaMovimentacoes.tudoMarcado = false;

            this.$refs.paginacao.atual = 1
            this.$refs.paginacao.buscar()
        },
        ajustarSaldoRealiados() {
            let saldo = 0
            this.abaMovimentacoes.lista.forEach((lan, index) => {
                if (lan.concluido) {
                    saldo += lan.valor
                    //app.set(lan,'saldoAtual',saldo);
                    lan.saldoAtual = saldo
                } else {
                    //app.set(lan,'saldoAtual',saldo);
                    lan.saldoAtual = saldo
                }
            })
        },

        selecionarTodosPlanosFiltro() {
            this.abaMovimentacoes.filtrarPor = []

            if (this.abaMovimentacoes.tudoMarcado) {
                //abaMovimentacoes
                this.abaMovimentacoes.listaPlanosFiltro.forEach((obj) => {
                    this.abaMovimentacoes.filtrarPor.push(obj.id)
                })
            }
        },
        verificarCheckFiltrarTudo() {
            this.abaMovimentacoes.tudoMarcado = this.abaMovimentacoes.listaPlanosFiltro.length === this.abaMovimentacoes.filtrarPor.length
        },

        saldoParcial(index) {
            let lista = _.take(this.abaMovimentacoes.lista, index + 1)
            return _.sumBy(lista, (lancamento) => {
                return lancamento.valor
            })
        },

        //add lançamento
        formNovoLancamento() {
            this.formLancamento = _.cloneDeep(this.formLancamentoDefault)
            this.formLancamento.data_hora = moment().format('L [às] HH:mm')
            this.formLancamento.data_pendete = moment().format('L')
        },

        //edit lançamento
        formEditarLancamento(obj) {
            this.lancamento_id = obj.id
            this.formLancamento = _.cloneDeep(this.formLancamentoDefault)
            this.formLancamento.titulo = 'Alterando lançamento'
            this.formLancamento.editando = true

            this.formLancamento.preload = true

            axios
                .get(`${URL_ADMIN}/fluxo-caixa/${this.id}/lancamento/${this.lancamento_id}`)
                .then((response) => {
                    let data = response.data
                    this.formLancamento.preload = false
                    Object.assign(this.formLancamento, data)
                    if (this.formLancamento.data_pendente !== null) {
                        this.formLancamento.agendar = true
                    }
                    if (this.formLancamento.data_hora_concluido == null) {
                        this.formLancamento.data_hora_concluido = moment().format('L [às] HH:mm')
                    }
                })
                .catch((response) => {
                    this.formLancamento.editando = false
                    //this.formLancamento.msg = response.data.msg;
                    this.formLancamento.preload = false
                    //this.formLancamento.erro = true;
                })
        },

        //selecionar um plano de conta
        selecionaPlano(obj) {
            this.formLancamento.plano_conta = obj
            this.formLancamento.plano_id = obj.id
            this.formLancamento.descricao = obj.descricao
            this.validaOperacoes()
        },
        resetNomePlano() {
            if (this.formLancamento.plano_id > 0 && this.formLancamento.plano_conta.descricao.length > 0) {
                this.formLancamento.plano_id = null
                this.formLancamento.plano_conta.operacao = ''
            }
        },

        //formas de pagamento
        addFormaPagamento() {
            this.formLancamento.formas.push({
                id: 0,
                forma_pagamento_id: 0,
                valorFormat: this.formLancamento.plano_conta.operacaoDebito ? '-0,00' : '0,00',
                observacoes: ''
            })
        },
        apagarForma(index) {
            if (this.formLancamento.formas[index].id > 0) {
                this.formLancamento.formasDelete.push(this.formLancamento.formas[index].id)
            }
            this.formLancamento.formas.splice(index, 1)
        },

        validaOperacoes() {
            let operacao = this.formLancamento.plano_conta.operacao
            if (operacao !== 'T') {
                this.formLancamento.operacao = operacao
            } else {
                this.formLancamento.operacao = ''
            }
        },

        salvarLancamento() {
            if (this.formLancamento.plano_id == null) {
                mostraErro('', 'Nenuma plano de conta foi escolhido para o lançamento')
                return false
            }

            if (this.formLancamento.operacao === '') {
                mostraErro('', 'Selecione CRÉDITO ou DÉBITO para a operação do plano de conta')
                return false
            }

            if (this.formLancamento.formas.length === 0) {
                mostraErro('', 'Coloque as formas de pagamento')
                return false
            }

            if (this.valorTotalLancamento === 0) {
                mostraErro('', 'O valor do lançamento não pode ser igual a zero')
                return false
            }

            let semFormaPagamento = this.formLancamento.formas.filter((forma) => {
                return forma.forma === 'nulo'
            })
            if (semFormaPagamento.length > 0) {
                mostraErro('', 'Alguma forma de pagamento não está selecionada')
                return false
            }
            let grupos = _.groupBy(this.formLancamento.formas, (obj) => obj.forma_pagamento_id)

            for (let forma_pagamento_id in grupos) {
                let forma = _.find(this.cliente.formas_pagamento, { id: parseInt(forma_pagamento_id) })
                let quantidade = this.formLancamento.formas.filter((obj) => obj.forma_pagamento_id === parseInt(forma_pagamento_id))
                if (quantidade.length >= 2) {
                    mostraErro('', `A forma de pagamento ${forma.descricao} está duplicada`)
                    return false
                }
            }

            let algumaFormaComZero = this.formLancamento.formas.filter((forma) => {
                let valor = convertFloat(forma.valorFormat)
                return valor === 0
            })
            if (algumaFormaComZero.length) {
                mostraErro('', 'O valor das formas de pagamentos não devem ficar com valor zero!')
                return false
            }

            let erro_pagamento_credito = false
            let erro_pagamento_debito = false
            this.formLancamento.formas.forEach((forma) => {
                let valor = parseFloat(forma.valorFormat)
                if (this.formLancamento.operacao === 'C' && valor < 0.0) {
                    erro_pagamento_credito = true
                }
                if (this.formLancamento.operacao === 'D' && valor > 0.0) {
                    erro_pagamento_debito = true
                }
            })

            if (erro_pagamento_credito) {
                mostraErro('', 'Operação de crédito não aceita formas de pagamento negativas')
                return false
            }

            if (erro_pagamento_debito) {
                mostraErro('', 'Operação de débito não aceita formas de pagamento positivas')
                return false
            }

            this.formLancamento.preload = true
            if (this.formLancamento.editando) {
                // se estiver editando
                axios
                    .put(`${URL_ADMIN}/fluxo-caixa/${this.id}/lancamento/${this.lancamento_id}`, this.formLancamento)
                    .then((data) => {
                        this.formLancamento.preload = false
                        if (this.formLancamento.editando) {
                            this.formLancamento.atualizado = true
                        } else {
                            this.formLancamento.cadastrado = true
                        }
                        this.atualizaContaCorrente()
                    })
                    .catch((response) => {
                        //this.formLancamento.msg = response.data.msg;
                        this.formLancamento.preload = false
                        if (this.formLancamento.editando) {
                            this.formLancamento.atualizado = false
                        } else {
                            this.formLancamento.cadastrado = false
                        }
                    })
            } else {
                // ou cadastrando
                axios
                    .post(`${URL_ADMIN}/fluxo-caixa/${this.id}/lancamento/`, this.formLancamento)
                    .then((data) => {
                        this.formLancamento.preload = false
                        if (this.formLancamento.editando) {
                            this.formLancamento.atualizado = true
                        } else {
                            this.formLancamento.cadastrado = true
                        }
                        this.atualizaContaCorrente()
                    })
                    .catch((response) => {
                        //this.formLancamento.msg = response.data.msg;
                        this.formLancamento.preload = false
                        if (this.formLancamento.editando) {
                            this.formLancamento.atualizado = false
                        } else {
                            this.formLancamento.cadastrado = false
                        }
                    })
            }
        },

        //apagar lançamentos
        formApagarLancamento(obj) {
            this.lancamento_id = obj.id
            this.formApagar.preload = false
            this.formApagar.delete = false
            this.formApagar.erro = false
            this.formApagar.msg = ''
        },
        apagarLancamento() {
            this.formApagar.preload = true
            axios
                .delete(`${URL_ADMIN}/fluxo-caixa/${this.id}/lancamento/${this.lancamento_id}`, this.formApagar)
                .then((response) => {
                    this.formApagar.preload = false
                    this.formApagar.delete = true
                    this.atualizaContaCorrente()
                })
                .catch((response) => {
                    this.formApagar.msg = response.data.msg
                    this.formApagar.preload = false
                    this.formApagar.erro = true
                })
        },

        //Mudar status de realizado/nao realizado
        mudarStatus(obj, status) {
            this.formRealizar.preload = true
            axios
                .put(`${URL_ADMIN}/fluxo-caixa/${this.id}/lancamento/${obj.id}/mudarStatus`, { status: status })
                .then((response) => {
                    this.formRealizar.preload = false
                    Object.assign(obj, response.data)
                    this.$refs.paginacao.buscar()
                })
                .catch((response) => {
                    this.formRealizar.preload = false
                    Object.assign(obj, response.data.lancamento)
                    this.$refs.paginacao.buscar()
                })
        }
    }
}
</script>
<style scoped>
small {
    color: #666;
}

.linha {
    cursor: pointer;
}
</style>
