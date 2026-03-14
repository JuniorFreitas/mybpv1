<template>
<modal ref="modalFiltroColunas" id="filtroColunas" size="g" titulo="Mostrar e Ocultar Treinamentos">
    <template #conteudo>
        <div class="row">
            <div class="col-sm-6" v-for="item in listaColunasTreinamentos">
                <div class="custom-control custom-switch mb-2">
                    <input type="checkbox" @click="item.checked = !item.checked"
                        v-model="item.checked"
                        class="custom-control-input" :id="item.id">
                    <label class="custom-control-label"
                        :for="item.id">{{item.label}}</label>
                </div>
            </div>
        </div>
    </template>
    <template #rodape>
        <div v-if="listaColunasTreinamentos && listaColunasTreinamentos.length">
            <button class="btn btn-sm mr-1 btn-primary"
                :disabled="listaColunasTreinamentos.length === listaColunasTreinamentos.filter(item => item.checked).length"
                @click.prevent="marcarDesmarcarTodosTreinamentosColuna(true)">
                Selecionar todos
            </button>
            <button class="btn btn-sm mr-1 btn-primary"
                :disabled="listaColunasTreinamentos.filter(item => item.checked).length === 0"
                @click.prevent="marcarDesmarcarTodosTreinamentosColuna(false)">
                Desmarcar todos
            </button>
        </div>
    </template>
</modal>

<modal ref="janelaTreinamento" id="janelaTreinamento" titulo="Treinamentos" :size="95">
    <template #conteudo>
        <p class=" mt-2 text-center" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>

        <div v-if="!preload && (!cadastrado && !atualizado)">

            <fieldset>
                <legend>Dados do funcionário</legend>

                <div class="row">
                    <div class="col-12">
                        <p>
                            Nome: <strong>{{ form.dadosFuncionario.nome }}</strong> - {{
                                form.dadosFuncionario.idade }} anos <br>
                            Cargo: <strong>{{ form.dadosFuncionario.cargo }}</strong> <br>
                            E-mail: <strong>{{ form.dadosFuncionario.email }}</strong>
                            <br>
                        </p>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Padrão de treinamento</legend>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>Segmento</label>
                            <select class="form-control" v-model="form.segmento_treinamento_id"
                                @change="trocarSegmentoTreinamento" :disabled="preload">
                                <option :value="null">Selecione</option>
                                <option v-for="s in segmentosTreinamento" :key="s.id" :value="s.id">
                                    {{ s.nome }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Treinamentos</legend>
                <div class="row">

    <div class="col-12 mb-2"
        v-if="form.listaVencimentos && form.listaVencimentos.length > 0">
        <div class="row">

            <div class="col-md-2">
                <div class="card">
                    <div class="card-body text-center py-2">
                        <h4>{{ form.listaVencimentos.length }}</h4>
                        <p class="mb-0">Todos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center py-2">
                        <h4>{{ treinamentosNaoRealizados }}</h4>
                        <p class="mb-0">Não realizados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center py-2">
                        <h4>{{ treinamentosRealizados }}</h4>
                        <p class="mb-0">Realizados em dias</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center py-2">
                        <h4>{{ treinamentosVencendo }}</h4>
                        <p class="mb-0">A Vencer</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center py-2">
                        <h4>{{ treinamentosVencidos }}</h4>
                        <p class="mb-0">Vencidos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>

    <div class="mb-4" v-if="form.listaVencimentos && form.listaVencimentos.length > 0">
        <div class="input-group input-group">
            <input type="text" class="form-control" placeholder="Buscar treinamento..."
                v-model="trainingSearchQuery">
            <div class="input-group-append">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
            </div>
        </div>
    </div>

    <!-- Accordion de treinamentos -->
    <div class="accordion" id="accordionTreinamentos"
        v-if="treinamentosFiltrados.length > 0">
        <div v-for="(treinamento, index) in treinamentosFiltrados"
            :key="index"
            class="card mb-3"
            :class="{
                             'border-left-success': getTreinamentoStatus(treinamento) === 'ativo' && treinamento.fez_treinamento,
                             'border-left-warning': getTreinamentoStatus(treinamento) === 'avencer' && treinamento.fez_treinamento,
                             'border-left-danger': getTreinamentoStatus(treinamento) === 'vencido' && treinamento.fez_treinamento,
                             'border-left-secondary': !treinamento.fez_treinamento
                         }">
            <div class="card-header d-flex justify-content-between align-items-center"
                :class="{
                                 'bg-success-light': getTreinamentoStatus(treinamento) === 'ativo' && treinamento.fez_treinamento,
                                 'bg-warning-light': getTreinamentoStatus(treinamento) === 'avencer' && treinamento.fez_treinamento,
                                 'bg-danger-light': getTreinamentoStatus(treinamento) === 'vencido' && treinamento.fez_treinamento,
                                 'bg-light': !treinamento.fez_treinamento
                             }">
                <h5 class="mb-0">
                    <button class="btn btn-link text-dark"
                        type="button"
                        @click="togglePanel(index)"
                        style="text-decoration: none; text-align: left;">
                        <i class="fa"
                            :class="openPanels.includes(index) ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                        {{ treinamento.label }}
                    </button>
                </h5>
                <span class="badge"
                    :class="{
                                      'badge-success': getTreinamentoStatus(treinamento) === 'ativo' && treinamento.fez_treinamento,
                                      'badge-warning': getTreinamentoStatus(treinamento) === 'avencer' && treinamento.fez_treinamento,
                                      'badge-danger': getTreinamentoStatus(treinamento) === 'vencido' && treinamento.fez_treinamento,
                                      'badge-secondary': !treinamento.fez_treinamento
                                  }">
                    {{ getStatusText(treinamento) }}
                </span>
            </div>

            <div class="collapse" :class="{ show: openPanels.includes(index) }">
                <div class="card-body">
                    <fieldset :disabled="salvandoVencimentoId === treinamento.id">
                        <div class="alert alert-warning p-2" style="font-size: 0.85rem;"
                            v-show="treinamento.descricao">
                            <strong>A quem se destina:</strong> {{ treinamento.descricao }}
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Realizou este treinamento?</label>
                                    <select class="form-control"
                                        :key="'fez-'+treinamento.id+'-'+treinamento.fez_treinamento"
                                        :value="treinamento.fez_treinamento"
                                        :disabled="(treinamento.fez_treinamento && treinamento._fez_treinamento_ja_salvo && !(privilegio_gestao_rh && treinamento_permitir_desmarcar_realizado)) || salvandoVencimentoId === treinamento.id"
                                        @change="onFezTreinamentoChange(treinamento, $event.target.value === 'true' || $event.target.value === true)">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row" v-if="treinamento.fez_treinamento">
                        <!-- Datas de treinamento e vencimento (somente prazo fixo) -->
                        <template v-if="treinamento.prazo_fixo">
                            <div class="col-md-6 mt-2">
                                <datepicker v-model="treinamento.data_treinamento"
                                    label="Data do treinamento"
                                    :disabled="salvandoVencimentoId === treinamento.id"
                                    @input="calculoDataExpiracao(treinamento)"
                                    max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                    onblur="valida_data_vazio(this)"></datepicker>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label>Vencimento (prazo fixo)</label>
                                    <input class="form-control" readonly disabled
                                        :value="treinamento.data_vencimento">
                                </div>
                            </div>
                        </template>
                        <template v-if="!treinamento.prazo_fixo">
                            <div class="col-md-6 mt-2">
                                <datepicker v-model="treinamento.data_treinamento"
                                    label="Data do treinamento"
                                    :disabled="salvandoVencimentoId === treinamento.id"
                                    max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                    onblur="valida_data_vazio(this)"
                                    @input="marcarAlterado(treinamento)"></datepicker>
                            </div>
                            <div class="col-md-6 mt-2">
                                <datepicker v-model="treinamento.data_vencimento"
                                    label="Data Vencimento"
                                    :disabled="salvandoVencimentoId === treinamento.id"
                                    min="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                    onblur="valida_data_vazio(this)"
                                    @input="marcarAlterado(treinamento)"></datepicker>
                            </div>
                        </template>
                    </div>

                    <!-- Informações da FAT -->
                    <fieldset class="mt-3" v-if="treinamento.fez_treinamento">
                        <legend>FAT</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Número da FAT</label>
                                    <input type="text" class="form-control"
                                        v-model="treinamento.numero_fat"
                                        :disabled="salvandoVencimentoId === treinamento.id"
                                        @input="marcarAlterado(treinamento)">
                                </div>
                            </div>
                            <div class="col-md-12" :style="salvandoVencimentoId === treinamento.id ? { pointerEvents: 'none', opacity: 0.7 } : {}">
                                <upload :model="treinamento.arquivo"
                                    :model-delete="treinamento.arquivoDel"
                                    :url="url_anexo"
                                    :quantidade="1"
                                    :multi="false"
                                    label="ANEXAR FAT"
                                    @onProgresso="anexoUploadAndamento=true"
                                    @onFinalizado="onUploadFinalizado(treinamento)"></upload>
                            </div>
                        </div>
                    </fieldset>

                    </fieldset>

                    <div class="mt-3 pt-2 border-top" v-show="(treinamento._alterado === true) && !(treinamentoMotivoDesmarcar && treinamentoMotivoDesmarcar.id === treinamento.id)">
                        <p class="text-muted small mb-2" v-if="salvandoVencimentoId === treinamento.id">
                            <i class="fa fa-spinner fa-pulse mr-1"></i>
                            Atualizando...
                        </p>
                        <button type="button" class="btn btn-sm btn-primary" @click="salvar(treinamento)"
                            :disabled="salvandoVencimentoId === treinamento.id || anexoUploadAndamento || cadastrado || atualizado">
                            <i class="fa fa-spinner fa-pulse" v-if="salvandoVencimentoId === treinamento.id"></i>
                            <i class="fa fa-save" v-else></i>
                            Salvar este treinamento
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </fieldset>

    <!-- Mensagem de nenhum treinamento encontrado -->
    <div class="alert alert-info"
        v-if="form.listaVencimentos && form.listaVencimentos.length > 0 && (!treinamentosFiltrados || treinamentosFiltrados.length === 0)">
        <i class="fa fa-info-circle"></i> Nenhum treinamento encontrado com os filtros atuais.
    </div>
    </div>
    </template>
    <template #rodape>
        <!-- Botão de salvar foi movido para cada treinamento (salvar individual no card) -->
    </template>
</modal>

<modal-auditoria-termo-responsabilidade
    ref="modalAuditoriaDesmarcar"
    id="modalMotivoDesmarcar"
    ref-name="modalMotivoDesmarcar"
    titulo="Motivo da retirada do treinamento"
    :texto-termo="textoTermoResponsabilidadeDesmarcar"
    label-motivo="Motivo"
    placeholder-motivo="Ex.: Registro feito por engano; treinamento não aplicável."
    label-botao-confirmar="Confirmar e desmarcar"
    :loading="desmarcarPreload"
    @confirmar="onConfirmarAuditoriaDesmarcar"
    @fechou="onModalMotivoDesmarcarFechou">
    <template #conteudo-antecipado>
        <p class="mb-2" v-if="treinamentoMotivoDesmarcar && (form.dadosFuncionario.nome || form.dadosFuncionario.cargo)">
            <span v-if="form.dadosFuncionario.nome">Colaborador: <strong>{{ form.dadosFuncionario.nome }}</strong></span><span v-if="form.dadosFuncionario.nome && form.dadosFuncionario.cargo"> — </span><span v-if="form.dadosFuncionario.cargo">Cargo: <strong>{{ form.dadosFuncionario.cargo }}</strong></span>
        </p>
    </template>
    <template #intro>
        <span v-if="treinamentoMotivoDesmarcar">Informe o motivo para alterar <strong>{{ treinamentoMotivoDesmarcar.label }}</strong> de "Realizado" para "Não realizado".</span>
    </template>
    <template #conteudo-pos-termo>
        <p class="text-muted small mb-0 mt-2" v-if="treinamentoMotivoDesmarcar">
            O treinamento será desmarcado individualmente e a ação será registrada em auditoria.
        </p>
    </template>
</modal-auditoria-termo-responsabilidade>

<modal ref="janelaTreinamentoMassa" id="janelaTreinamentoMassa" titulo="Treinamentos" :size="95">
    <template #conteudo>
        <div class="alert alert-success text-center" v-show="cadastrado">
            <h4><i class="icon fa fa-check"></i> Treinamento atualizado com sucesso</h4>
        </div>
        <p class=" mt-2 text-center" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>

        <div v-if="!preload && (!cadastrado && !atualizado)">

            <fieldset>
                <legend>Treinamentos</legend>
                <div class="row">
                    <div class="col-12 col-md-6" v-for="(treinamento, index) in formMassa.listaVencimentos"
                        v-if="formMassa.listaVencimentos && formMassa.listaVencimentos.length > 0">
                        <fieldset>
                            <legend>{{ treinamento.label }}</legend>

                            <div class="alert alert-warning p-2" style="font-size: 0.85rem;"
                                v-show="treinamento.descricao">
                                A quem se destina: {{ treinamento.descricao }}
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="">Realizou este treinamento?</label>
                                        <select class="form-control" v-model="treinamento.fez_treinamento"
                                            :disabled="treinamento.fez_treinamento && treinamento._fez_treinamento_ja_salvo && !(privilegio_gestao_rh && treinamento_permitir_desmarcar_realizado)">
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6" v-if="treinamento.fez_treinamento">
                                    <div class="form-group">
                                        <template v-if="treinamento.prazo_fixo">
                                            <label for="">Data do treinamento:
                                                <span class="text-danger" style="font-size: 0.85rem;">
                                                    Vencimento: {{ treinamento.data_vencimento }}
                                                </span>
                                            </label>
                                            <datepicker v-model="treinamento.data_treinamento"
                                                max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                onblur="valida_data_vazio(this)"></datepicker>
                                        </template>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Numero da FAT</label>
                                        <input type="text" class="form-control" v-model="treinamento.numero_fat">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </fieldset>
        </div>

    </template>
    <template #rodape>
        <button type="button" class="btn btn-sm mr-1 btn-primary" @click="salvarMassa"
            v-if="!preload && formMassa.listaVencimentos && formMassa.listaVencimentos.length > 0 && (!cadastrado && !atualizado)">
            <i class="fa fa-save"></i> Salvar
        </button>
    </template>
</modal>

<modal ref="janelaEnviar" id="janelaEnviar" :fechar="!formEnviar.preload" :titulo="formEnviar.titulo">
    <template #conteudo>
        <span v-show="formEnviar.preload">
            <i class="fa fa-spinner fa-pulse"></i> Enviando...
        </span>
        <div class="alert alert-success alert-dismissible" v-show="formEnviar.enviado">
            <h4>
                <i class="icon fa fa-check"></i>
                Carteira enviada com sucesso!
            </h4>
        </div>
        <fieldset v-show="!formEnviar.enviado && !formEnviar.preload">
            <legend>Informações</legend>
            <div class="row">
                <div class="col-12">
                    <p>Nome: {{ formEnviar.nome }}</p>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="text" onblur="validaEmailVazio(this)" class="form-control"
                            v-model="formEnviar.email">
                    </div>
                </div>

            </div>
        </fieldset>
    </template>
    <template #rodape>
        <div v-show="!formEnviar.preload">
            <button type="button" class="btn btn-sm mr-1 btn-primary"
                @click="enviar"
                v-show="!formEnviar.enviado">
                <i class="fa fa-envelope"></i> Enviar
            </button>
        </div>
    </template>
</modal>

<modal ref="janelaEnviarAviso" id="janelaEnviarAviso" :fechar="!formEnviarAviso.preload"
    titulo="Notificação de treinamento próximo ao vencimento">
    <template #conteudo>
        <span v-show="formEnviarAviso.preload">
            <i class="fa fa-spinner fa-pulse"></i> Enviando...
        </span>
        <div class="alert alert-success alert-dismissible" v-show="formEnviarAviso.enviado">
            <h4>
                <i class="icon fa fa-check"></i>
                Aviso de treinamento enviado com sucesso!
            </h4>
        </div>
        <fieldset v-show="!formEnviarAviso.enviado && !formEnviarAviso.preload">
            <legend>Informações</legend>
            <div class="row">
                <div class="alert alert-secondary">Informe o e-mail para que seja enviado os treinamentos que estão
                    próximos ao vencimento.
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="text" onblur="validaEmailVazio(this)" class="form-control"
                            v-model="formEnviarAviso.email">
                    </div>
                </div>

            </div>
        </fieldset>
    </template>
    <template #rodape>
        <div v-show="!formEnviarAviso.preload">
            <button type="button" class="btn btn-sm mr-1 btn-primary"
                @click="enviarAviso"
                v-show="!formEnviarAviso.enviado">
                <i class="fa fa-envelope"></i> Enviar
            </button>
        </div>
    </template>
</modal>

<fieldset>
    <legend class="text-uppercase">Filtro</legend>
    <div class="row">
        <date-range-filter
            v-model:enabled="controle.dados.campoVencimento"
            v-model:start-date="controle.dados.dataInicioVencimento"
            v-model:end-date="controle.dados.dataFimVencimento"
            :disabled="controle.carregando"
            :id-suffix="'vencimento-' + hash"
            label="Por período de vencimento"
            wrapper-class="col-12 col-lg-3"
            @update:startDate="atualizarVencimentoString"
            @update:endDate="atualizarVencimentoString"
            @update:enabled="atualizarVencimentoString">
        </date-range-filter>

        <date-range-filter
            v-model:enabled="controle.dados.campoPeriodoTreinado"
            v-model:start-date="controle.dados.dataInicioPeriodoTreinado"
            v-model:end-date="controle.dados.dataFimPeriodoTreinado"
            :disabled="controle.carregando"
            :id-suffix="'periodo-treinado-' + hash"
            label="Por período treinado"
            wrapper-class="col-12 col-lg-3"
            @update:startDate="atualizarPeriodoTreinadoString"
            @update:endDate="atualizarPeriodoTreinadoString"
            @update:enabled="atualizarPeriodoTreinadoString">
        </date-range-filter>

        <div class="col-12 col-lg-3">
            <label>Admitidos</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoAdmitido">
                <option value="">Geral</option>
                <option value="S">Sim</option>
                <option value="N">Não</option>
            </select>
        </div>

        <div class="col-12 col-sm-2">
            <div class="form-group">
                <label>Por Demitido</label>
                <select class="form-control form-control-sm"
                    @change="atualizar"
                    :disabled="controle.carregando"
                    v-model="controle.dados.campoDemitido">
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <label>Treinados</label>
            <select class="custom-select custom-select-sm" @change="selecionaTreinados($event.target.value)"
                :disabled="controle.carregando"
                v-model="controle.dados.campo_treinados">
                <option value="">Sem filtro</option>
                <option value="S">Sim</option>
                <option value="N">Não</option>
            </select>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <label>Buscar</label>
            <input type="text"
                placeholder="Buscar por nome"
                autocomplete="off"
                class="form-control form-control-sm" :disabled="controle.carregando"
                v-model="controle.dados.campoBusca">
        </div>

        <div class="col-12 col-lg-3 mb-3">
            <label>CPF</label>
            <input type="text"
                placeholder="Buscar por cpf"
                autocomplete="mastertag"
                v-mascara:cpf
                class="form-control form-control-sm" :disabled="controle.carregando"
                v-model="controle.dados.campoCPF">
        </div>

        <div class="col-12 col-lg-3 mb-3">
            <label>Foto 3x4</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoFoto">
                <option value="">Geral</option>
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
            </select>
        </div>

        <div class="col-12 col-lg-2 mb-3">
            <label>Nº Crachá</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoCracha">
                <option value="">Geral</option>
                <option value="S">Sim</option>
                <option value="N">Não</option>
            </select>
        </div>

        <div class="col-12 col-lg-4 mb-3"
            v-if="lista_ccs && AUTENTICADO.temFilial">
            <label for="">Por Cnpj</label>
            <select class="form-control form-control-sm" @change="changeCnpj"
                :disabled="controle.carregando"
                v-model="controle.dados.campoCnpj">
                <option value="">Todos</option>
                <option v-for="(item, key) in lista_ccs.cnpjs" :value="key" :keys="key">
                    {{item.nome_fantasia}} - {{item.cnpj}}
                </option>
            </select>
        </div>

        <div class="col-12 mb-3" :class="AUTENTICADO.temFilial ? 'col-lg-3' : 'col-lg-5'" v-if="lista_ccs">
            <label for="">Centro de Custo</label>
            <select class="form-control form-control-sm" @change="atualizar"
                :disabled="controle.carregando"
                v-model="controle.dados.campoCentroCusto">
                <option value="">Todos</option>
                <option :title="item.label" v-for="(item, key) in filtroListaCentroCustoCnpj"
                    :value="item.matriz ? item.id : item.filial_id"
                    :keys="key">
                    {{item.label}}
                </option>
                <option value="--naoinformado--">--- Não Informado ---</option>
            </select>
        </div>

        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>Por Vaga</label>
                <autocomplete :disabled="controle.carregando" :caminho="controle.dados.caminho_autocomplete"
                    :valido="controle.dados.campoVaga !== ''"
                    v-model="controle.dados.autocomplete_label"
                    placeholder="Por vaga"
                    @onblur="resetaCampo"
                    @onselect="selecionaVaga"></autocomplete>
            </div>
        </div>

        <div class="col-12 mb-3" :class="AUTENTICADO.temFilial ? 'col-lg-3' : 'col-lg-5'">
            <label>Cargo</label>
            <input type="text"
                placeholder="Buscar por cargo"
                autocomplete="off"
                class="form-control form-control-sm" :disabled="controle.carregando"
                v-model="controle.dados.campoCargo">
        </div>

        <div class="col-12 col-lg-2 mb-3">
            <label>Estados</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoUf">
                <option value="">Todos</option>
                <option value="AC">AC</option>
                <option value="AL">AL</option>
                <option value="AP">AP</option>
                <option value="AM">AM</option>
                <option value="BA">BA</option>
                <option value="CE">CE</option>
                <option value="DF">DF</option>
                <option value="ES">ES</option>
                <option value="GO">GO</option>
                <option value="MA">MA</option>
                <option value="MT">MT</option>
                <option value="MS">MS</option>
                <option value="MG">MG</option>
                <option value="PA">PA</option>
                <option value="PB">PB</option>
                <option value="PR">PR</option>
                <option value="PE">PE</option>
                <option value="PI">PI</option>
                <option value="RJ">RJ</option>
                <option value="RN">RN</option>
                <option value="RS">RS</option>
                <option value="RO">RO</option>
                <option value="RR">RR</option>
                <option value="SC">SC</option>
                <option value="SP">SP</option>
                <option value="SE">SE</option>
                <option value="TO">TO</option>
            </select>
        </div>

        <div class="col-12">
            <label>Treinamentos</label>
            <select class="custom-select custom-select-sm" @change="addTreinamento($event.target.value)"
                :disabled="controle.carregando"
                v-model="controle.dados.treinamentos">
                <option value="">Selecionar ...</option>
                <option value="todos">---- ADICIONAR TODOS ----</option>
                <option v-for="treinamento in listaTodosTreinamentos" :value="treinamento.label">
                    {{ treinamento.label }}
                </option>
                <option value="rm">---- REMOVER TODOS ----</option>
            </select>

        </div>

        <div class="col-12 mt-2">
            <div class="p-2" style="border: 1px dashed #cccbcb">
                <h6>TREINAMENTOS SELECIONADOS:</h6>
                <div class="row">
                    <small class="p-2 ml-2 mb-2 table-secondary text-dark rounded"
                        v-for="(item, ind) in controle.dados.treinamentos_selecionados">
                        {{ item }} <a href="javascript://" @click.prevent="removeTreinamento(ind)"><i
                                class="fa fa-times ml-1"></i></a>
                    </small>
                </div>
            </div>
        </div>

    </div>

    <div class="row" v-if="false">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
            <label>Buscar</label>
            <input type="text"
                placeholder="Buscar por nome"
                autocomplete="off"
                class="form-control form-control-sm" :disabled="controle.carregando"
                v-model="controle.dados.campoBusca">
        </div>

        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
            <label>CPF</label>
            <input type="text"
                placeholder="Buscar por cpf"
                autocomplete="mastertag"
                v-mascara:cpf
                class="form-control form-control-sm" :disabled="controle.carregando"
                v-model="controle.dados.campoCPF">
        </div>

        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
            <div class="form-group">
                <label>Por Vaga</label>
                <autocomplete :disabled="controle.carregando" :caminho="controle.dados.caminho_autocomplete"
                    :valido="controle.dados.campoVaga !== ''"
                    v-model="controle.dados.autocomplete_label"
                    placeholder="Por vaga"
                    @onblur="resetaCampo"
                    @onselect="selecionaVaga"></autocomplete>
            </div>
        </div>

        @if(!Request::has('cliente_id'))
        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
            <div class="form-group">
                <label>Por Cliente</label>
                <autocomplete :disabled="controle.carregando"
                    :caminho="controle.dados.caminho_cliente_autocomplete"
                    :valido="controle.dados.campoCliente !== ''"
                    v-model="controle.dados.autocomplete_label_cliente"
                    placeholder="Por cliente"
                    @onblur="resetaCampoCliente"
                    @onselect="selecionaCliente"></autocomplete>
            </div>
        </div>
        @endif

        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
            <label>Áreas</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoArea">
                <option value="">Todas</option>
                <option :value="item.id" v-for="item in listaAreas">{{ item.label }}</option>
            </select>
        </div>

        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
            <label>Cargo</label>
            <input type="text"
                placeholder="Buscar por cargo"
                autocomplete="off"
                class="form-control form-control-sm" :disabled="controle.carregando"
                v-model="controle.dados.campoCargo">
        </div>

        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
            <label>Estado</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoUf">
                <option value="">Todos</option>
                <option value="AC">AC</option>
                <option value="AL">AL</option>
                <option value="AP">AP</option>
                <option value="AM">AM</option>
                <option value="BA">BA</option>
                <option value="CE">CE</option>
                <option value="DF">DF</option>
                <option value="ES">ES</option>
                <option value="GO">GO</option>
                <option value="MA">MA</option>
                <option value="MT">MT</option>
                <option value="MS">MS</option>
                <option value="MG">MG</option>
                <option value="PA">PA</option>
                <option value="PB">PB</option>
                <option value="PR">PR</option>
                <option value="PE">PE</option>
                <option value="PI">PI</option>
                <option value="RJ">RJ</option>
                <option value="RN">RN</option>
                <option value="RS">RS</option>
                <option value="RO">RO</option>
                <option value="RR">RR</option>
                <option value="SC">SC</option>
                <option value="SP">SP</option>
                <option value="SE">SE</option>
                <option value="TO">TO</option>
            </select>
        </div>

        <div class="col-12 col-sm-4 col-md-3">
            <label>Treinados</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campo_treinados">
                <option value="">Sem filtro</option>
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
            </select>
        </div>

        <div class="col-12 col-md-3 col-sm-4 ">
            <div class="form-check" style="margin-bottom: -11px;">
                <input type="checkbox" class="form-check-input" @change="atualizar()"
                    :disabled="controle.carregando"
                    id="filtroVencimento"
                    v-model="controle.dados.campoVencimento">
                <label class="form-check-label cursor-pointer" for="filtroVencimento">
                    Por período de vencimento
                </label>
            </div>
            <div class="form-group">
                <datepicker range formsm label="" @onselect="atualizar()"
                    :disabled="controle.carregando"
                    v-model="controle.dados.vencimento"></datepicker>
            </div>
        </div>

        <div class="col-12 col-sm-4 col-md-3 col-lg-2">
            <label>NR33</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoNr_trinta_tres">
                <option value="">Sem filtro</option>
                <option value="Realizado">Realizado</option>
                <option value="Não Realizado">Não Realizado</option>
                <option value="NÃO SE APLICA">Não se aplica</option>
            </select>
        </div>

        <div class="col-12 col-sm-4 col-md-3 col-lg-2">

            <label>NR35</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoNr_trinta_cinco">
                <option value="">Sem filtro</option>
                <option value="Realizado">Realizado</option>
                <option value="Não Realizado">Não Realizado</option>
                <option value="NÃO SE APLICA">Não se aplica</option>
            </select>
        </div>

        <div class="col-12 col-sm-4 col-md-3 col-lg-2">
            <label>EBTV</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoNr_ebtv">
                <option value="">Sem filtro</option>
                <option :value="true">Realizado</option>
                <option :value="false">Não Realizado</option>
            </select>
        </div>

        <div class="col-12 col-sm-4 col-md-3 col-lg-2">
            <label>Admitidos</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoAdmitido">
                <option value="">Geral</option>
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
            </select>
        </div>

        <div class="col-12 col-sm-4 col-md-3 col-lg-2">
            <label>Nº Crachá</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoCracha">
                <option value="">Geral</option>
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
            </select>
        </div>

        <div class="col-12 col-sm-4 col-md-3 col-lg-2">
            <label>Foto 3x4</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoFoto">
                <option value="">Geral</option>
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
            </select>
        </div>

        <div class="col-12 col-sm-4 col-md-3 col-lg-2">
            <label>PCD</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.campoPcd">
                <option value="">Geral</option>
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label>Exibir</label>
            <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                v-model="controle.dados.pages">
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="500">500</option>
            </select>
        </div>
    </div>

    <div class="col-12">
        <div class="row mt-2">
            <button type="button" class="btn btn-sm mr-1 btn-success mb-1 mr-1" :disabled="controle.carregando"
                :style="controle.carregando ? 'cursor: not-allowed' : 'cursor: pointer'" @click="atualizar">
                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                Atualizar
            </button>

            <div class="dropdown dropdown-carteira-etiquetas" :class="{ show: isDropdownOpen('carteira') }">
                <button class="btn btn-sm mr-1 btn-primary dropdown-toggle mr-1"
                    type="button"
                    id="dropdownCarteira"
                    aria-haspopup="true"
                    :aria-expanded="isDropdownOpen('carteira') ? 'true' : 'false'"
                    :style="!selecionados.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                    :disabled="!selecionados.length"
                    @click.prevent.stop="toggleDropdown('carteira')">
                    Gerar Carteira <span class="badge badge-light">{{ selecionados.length }}</span>
                </button>

                <div class="dropdown-menu" :class="{ show: isDropdownOpen('carteira') }" aria-labelledby="dropdownCarteira" @click.stop="fecharDropdown">
                    <button type="button"
                        class="dropdown-item"
                        :disabled="!selecionados.length"
                        @click.prevent="gerarCarteiras('treinamento')">
                        <i class="fas fa-graduation-cap mr-2"></i>Treinamento
                    </button>
                    <button type="button"
                        class="dropdown-item"
                        :disabled="!selecionados.length"
                        @click.prevent="gerarCarteiras('bloqueio')">
                        <i class="fas fa-ban mr-2"></i>Bloqueio
                    </button>
                    <button type="button"
                        class="dropdown-item"
                        :disabled="!selecionados.length"
                        @click.prevent="gerarCarteiras('treinamento_bloqueio')">
                        <i class="fas fa-list mr-2"></i>Treinamento/Bloqueio
                    </button>
                </div>
            </div>

            <button class="btn btn-sm mr-1 btn-danger mb-1 mr-1"
                :style="!selecionados.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                :disabled="!selecionados.length" @click="selecionados = []">
                <i class="fa fa-times"></i> Limpar seleção
            </button>

            <button class="btn btn-sm mr-1 btn-primary mb-1 mr-1" v-if="false"
                :style="!selecionadosMassa.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                @click.pre.prevent="abrirFormMassa()"
                :disabled="!selecionadosMassa.length">
                <i class="fa fa-plus"></i> Atualizar em massa <span class="badge badge-light">{{ selecionadosMassa.length }}</span>
            </button>

            <button class="btn btn-sm mr-1 btn-danger mb-1 mr-1" v-if="false"
                :style="!selecionadosMassa.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                :disabled="!selecionadosMassa.length" @click="selecionadosMassa = []">
                <i class="fa fa-times"></i> Limpar seleção em massa
            </button>

            <button type="button" class="btn btn-sm mr-1 btn-primary mb-1 mr-1"
                @click.prevent="exportaExcel()"
                :disabled="controle.carregando || preloadExportacao || lista.length===0">
                <i class="fas fa-file-excel"></i> EXPORTAR EXCEL <span class="badge badge-light"
                    v-show="selecionados.length > 0">{{ selecionados.length }}</span>
            </button>

        </div>
    </div>

</fieldset>

<p class="text-center" v-if="controle.carregando">
    <i class="fa fa-spinner fa-pulse"></i> Carregando...
</p>

<div id="conteudo">
    <div class="alert alert-warning" v-show="!controle.carregando && lista.length==0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>

    <div v-show="!controle.carregando && lista.length > 0">
        <!-- Cabeçalho com checkboxes "selecionar todos" e filtro -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="custom-control custom-checkbox mr-3">
                            <input type="checkbox" class="custom-control-input" id="checkAllMain"
                                :style="!emTreinamentos.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                                :disabled="!emTreinamentos.length" :checked="tudoMarcado"
                                @click="selecionaTodos">
                            <label class="custom-control-label" for="checkAllMain">Selecionar todos</label>
                        </div>
                    </div>

                    <div class="col-auto">
                        <div class="card-body py-2">
                            <div class="d-flex flex-wrap">
                                <div class="mr-4 mb-1">
                                    <span class="badge badge-success mr-1">●</span> Em dia
                                </div>
                                <div class="mr-4 mb-1">
                                    <span class="badge badge-warning mr-1">●</span> Vencendo
                                </div>
                                <div class="mr-4 mb-1">
                                    <span class="badge badge-danger mr-1">●</span> Vencido
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col text-right">
                        <button class="btn btn-sm mr-1 btn-primary" content="Mostrar e Ocultar Treinamentos" v-tippy
                            @click.prevent="abrirModal(refsModal.MODAL_FILTRO_COLUNAS)">
                            <i class="bx bxs-filter-alt" aria-hidden="true"></i> Filtrar treinamentos
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards dos colaboradores -->
        <div class="row">
            <div class="col-12 mb-3" v-for="(item, key) in lista" :key="item.id">
                <!-- Card principal do colaborador -->
                <div class="card shadow-sm">
                    <div class="card-header py-2 border-0"
                        :class="{
                        'bg-danger text-white': item.admissao && ['Demitido','DEMITIDO'].includes(item.admissao.status),
                        'bg-white': item.admissao &&  !['Demitido','DEMITIDO'].includes(item.admissao.status),
                        }">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="custom-control custom-checkbox" style="padding-left: 0px">
                                    <input
                                        type="checkbox"
                                        v-model="selecionados"
                                        :value="item.id"
                                        :id="item.id"
                                        :style="item.treinamento ? 'cursor:pointer' : 'cursor: not-allowed'"
                                        :title="item.treinamento ? null : 'Não possui treinamento'"
                                        v-if="item.treinamento && item.treinamento.vencimentos.length">
                                </div>
                            </div>

                            <div class="col">
                                <div class="d-flex flex-wrap align-items-center">
                                    <h5 class="mb-0 mr-2">{{item.curriculo.nome}}</h5>
                                    <span class="badge badge-light border mr-2"
                                        v-if="item.treinamento && item.treinamento.vencimentos.length">
                                        {{ item.treinamento.vencimentos.length }} treinamentos
                                    </span>
                                    <span class="badge badge-warning text-dark"
                                        v-if="item.admissao && ['Demitido','DEMITIDO'].includes(item.admissao.status)">
                                        DEMITIDO
                                    </span>
                                </div>
                                <div class="small text-muted colab-header-meta">
                                    <span class="mr-3"><i class="fa fa-id-card mr-1"></i>CPF: {{item.curriculo.cpf}}</span>
                                    <span class="mr-3"><i class="fa fa-briefcase mr-1"></i>Cargo: {{item.vaga_aberta.vaga.nome}}</span>
                                    <span class="mr-3">
                                        <i class="fa fa-file-signature mr-1"></i>Tipo: {{ (item.admissao && item.admissao.tipo_admissao) ? item.admissao.tipo_admissao : '---' }}
                                    </span>
                                    <span class="mr-3">
                                        <i class="fa fa-flag mr-1"></i>Status: {{ (item.admissao && item.admissao.status) ? item.admissao.status : '---' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="dropdown dropleft dropdown-carteira-etiquetas" :class="{ show: isDropdownOpen(`treinamento:${item.id}`) }">
                                    <button class="btn btn-sm mr-1 btn-secondary dropdown-toggle"
                                        :id="`dropdownMenuLink_${item.id}`"
                                        aria-haspopup="true"
                                        :aria-expanded="isDropdownOpen(`treinamento:${item.id}`) ? 'true' : 'false'"
                                        @click.prevent.stop="toggleDropdown(`treinamento:${item.id}`)">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-custom"
                                        :class="{ show: isDropdownOpen(`treinamento:${item.id}`) }"
                                        :aria-labelledby="`dropdownMenuLink_${item.id}`"
                                        @click.stop="fecharDropdown">
                                        <a class="dropdown-item" href="javascript://" title="Atualizar treinamento"
                                            @click.prevent="formAlterar(item.id)">
                                            <i class="fas fa-edit fa-fw mr-1"></i> Atualizar treinamento
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body py-2">
                        <div class="row text-left colab-meta">
                            <div class="col-md-3 col-6 mb-2">
                                <div class="meta-item">
                                    <div class="meta-icon">
                                        <i class="fa fa-building"></i>
                                    </div>
                                    <div class="meta-content">
                                        <div class="meta-label">Empresa</div>
                                        <div class="meta-value" v-if="AUTENTICADO.temFilial">
                                            <span v-if="item.admissao && item.admissao.emp_cnpj">
                                                {{item.admissao.emp_nome_fantasia}} ({{item.admissao.emp_tipo}})
                                            </span>
                                            <span v-else>---</span>
                                        </div>
                                        <div class="meta-value" v-else>---</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <div class="meta-item">
                                    <div class="meta-icon">
                                        <i class="fa fa-sitemap"></i>
                                    </div>
                                    <div class="meta-content">
                                        <div class="meta-label">Centro de Custo</div>
                                        <div class="meta-value">
                                            <span v-if="item.admissao && item.admissao.emp_centro_custo">
                                                {{item.admissao.emp_centro_custo}}
                                            </span>
                                            <span v-else>---</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <div class="meta-item">
                                    <div class="meta-icon">
                                        <i class="fa fa-graduation-cap"></i>
                                    </div>
                                    <div class="meta-content">
                                        <div class="meta-label">Padrão de Treinamento</div>
                                        <div class="meta-value">
                                            <span v-if="item.admissao && (item.admissao.segmento_treinamento || item.admissao.segmento_treinamento_nome)">
                                                {{ (item.admissao.segmento_treinamento && item.admissao.segmento_treinamento.nome) ? item.admissao.segmento_treinamento.nome : item.admissao.segmento_treinamento_nome }}
                                            </span>
                                            <span v-else>---</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de treinamentos -->
                    <div v-if="item.treinamento && item.treinamento.vencimentos.length" class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr class="bg-light border-left-secondary">
                                    <th>Treinamento</th>
                                    <th>Data Treinamento</th>
                                    <th>Data Vencimento</th>
                                    <th>Anexo FAT</th>
                                    <th>Status</th>
                                    <th>Exibi na Carteira</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="v in item.treinamento.vencimentos">
                                    <tr v-if="isColunaTreinamentoSelecionada(v)" :key="v.id" :class="v.pivot.status.corBorder">
                                        <td>{{ v.label }}</td>
                                        <td>{{ v.pivot.data_treinamento }}</td>
                                        <td>{{ v.pivot.data_vencimento }}</td>
                                        <td>
                                            <i class="fa fa-paperclip" v-show="v.pivot.arquivo_id"></i>
                                            <i class="fa fa-minus" v-show="!v.pivot.arquivo_id"></i>
                                        </td>
                                        <td>
                                            <span class="badge" :class="v.pivot.status.badge">
                                                {{ v.pivot.status.label }}
                                            </span>
                                        </td>
                                        <td>{{ v.exibir_na_carteira ? 'Sim' : 'Não' }}</td>
                                    </tr>
                                </template>
                                <tr v-if="!item.treinamento.vencimentos.some(v => isColunaTreinamentoSelecionada(v))">
                                    <td colspan="5" class="text-center py-3">
                                        <em>Nenhum treinamento visível com os filtros atuais</em>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="card-footer text-muted">
                        <em>Não há treinamentos registrados para este colaborador</em>
                    </div>
                </div>
            </div>
        </div>
    </div>

<controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
    :url="urlPaginacao"
    :por-pagina="controle.dados.pages"
    :dados="controle.dados"
    v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
</div>
</template>

<script>
import { defineComponent } from 'vue'
import { REFS_MODAL, API_PATHS } from './constants'
import datepicker from '../DatePicker'
import ExportacaoMixin from '../../mixins/Exportacoes'
import Upload from '../Upload.vue'
import DateRangeFilter from '../DateRangeFilter.vue'
import Modal from '../Modal.vue'
import ModalAuditoriaTermoResponsabilidade from '../ModalAuditoriaTermoResponsabilidade.vue'
import ControlePaginacao from '../ControlePaginacao.vue'

export default defineComponent({
    name: 'TreinamentosCarteiraEtiquetas',
    mixins: [ExportacaoMixin],
    components: {
        datepicker,
        Upload,
        DateRangeFilter,
        Modal,
        ModalAuditoriaTermoResponsabilidade,
        ControlePaginacao
    },
    data() {
        return {
            tituloJanela: 'Treinamentos',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            disabled: true,
            preloadExportacao: false,

            URL_ADMIN,
            AUTENTICADO,
            refsModal: REFS_MODAL,
            urlPaginacao: `${URL_ADMIN}/${API_PATHS.atualizar}`,

            urlExportacao: `${URL_ADMIN}/${API_PATHS.export}`,
            hash: `mybp_${parseInt(Math.random() * 999999)}`,

            cliente_id: '',

            todos_municipios: `autocomplete/todos-municipios`,

            selecionados: [],
            selecionaTudo: false,

            selecionadosMassa: [],
            selecionaTudoMassa: false,

            openPanels: [],
            expandAll: false,

            trainingSearchQuery: '',
            trainingStatusFilter: 'all',

            form: {
                dadosFuncionario: {
                    nome: '',
                    idade: '',
                    cargo: '',
                    email: ''
                },

                feedback_id: '',
                curriculo_id: '',
                tipo: '',
                gerou_id: '',
                data_envio: '',
                enviado_email: '',
                enviou_id: '',
                email_envio: '',
                email_aberto: '',
                data_email_aberto: '',
                listaVencimentos: [],

                nr_trinta_tres: true,
                nr_trinta_cinco: true,
                segmento_treinamento_id: null,
                exame: {
                    feedback_id: '',
                    exame_realizado: '',
                    data_realizado: '',
                    tipo_exame: '',
                    trabalho_altura: '',
                    espaco_confinado: ''
                }
            },
            formDefault: null,

            formMassa: {
                tipo: '',
                gerou_id: '',
                data_envio: '',
                enviado_email: '',
                enviou_id: '',
                email_envio: '',
                email_aberto: '',
                data_email_aberto: '',
                listaVencimentos: [],
                nr_trinta_tres: true,
                nr_trinta_cinco: true,
                selecionadosMassa: '',
                exame: {
                    feedback_id: '',
                    exame_realizado: '',
                    data_realizado: '',
                    tipo_exame: '',
                    trabalho_altura: '',
                    espaco_confinado: ''
                }
            },
            formMassaDefault: null,

            vencimentos: [],
            listaTodosTreinamentos: [],

            listaColunasTreinamentos: null,

            lista_ccs: null,

            formEnviar: {
                enviado: false,
                preload: false,
                titulo: 'Enviar Carteira e Etiqueta',
                nome: '',
                email: '',
                token: ''
            },
            formEnviarDefault: null,

            formEnviarAviso: {
                enviado: false,
                preload: false,
                email: ''
            },

            formEnviarAvisoDefault: null,

            lista: [],
            vagas: [],
            listaAreas: [],
            segmentosTreinamento: [],

            dropdownAbertoKey: null,
            dropdownJustOpened: false,

            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                    autocomplete_label_anterior: '',
                    autocomplete_label: '',
                    autocomplete_label_cliente_anterior: '',
                    autocomplete_label_cliente: '',
                    pages: 50,
                    cliente_custom: '',
                    campoBusca: '',
                    campoVaga: '',
                    campoLido: '',
                    campoFiltro: '',
                    campoPcd: '',
                    campoUf: '',
                    campoArea: '',
                    campoCargo: '',
                    campo_treinados: '',
                    campoNr_trinta_tres: '',
                    campoNr_trinta_cinco: '',
                    campoNr_ebtv: '',
                    campoAdmitido: '',
                    campoDemitido: false,
                    campoCracha: '',
                    campoFoto: '',
                    campo_dataInicio: '',
                    campo_dataFim: '',
                    campoVencimento: false,
                    dataInicioVencimento: '',
                    dataFimVencimento: '',
                    vencimento: '',
                    treinamentos: '',
                    treinamentos_selecionados: [],
                    campoPeriodoTreinado: false,
                    dataInicioPeriodoTreinado: '',
                    dataFimPeriodoTreinado: '',
                    periodoTreinado: '',
                    campoCnpj: '',
                    campoCentroCusto: ''
                }
            },

            url_anexo: `${URL_ADMIN}/${API_PATHS.uploadAnexos}`,
            anexoUploadAndamento: false,

            privilegio_gestao_rh: false,
            treinamento_permitir_desmarcar_realizado: false,

            showModalMotivoDesmarcar: false,
            treinamentoMotivoDesmarcar: null,
            desmarcarPreload: false,

            salvandoVencimentoId: null
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form)
        this.formEnviarDefault = _.cloneDeep(this.formEnviar)
        this.formEnviarAvisoDefault = _.cloneDeep(this.formEnviarAviso)
        this.cliente_id = document.getElementById('cliente_id') ? document.getElementById('cliente_id').value : ''
        if (this.cliente_id) {
            this.controle.dados.campoCliente = parseInt(this.cliente_id)
            this.controle.dados.cliente_custom = parseInt(this.cliente_id)
        }
        this.listaVagas()
        this.listaAreasGeral()
        this.carregarSegmentosTreinamento()
        this.atualizar()

        document.addEventListener('click', this.onClickOutside)

        let intervalId = setInterval(() => {
            if (this.listaTodosTreinamentos.length > 0) {
                this.listaColunasTreinamentos = this.listaTodosTreinamentos.map((item) => {
                    return {
                        id: item.id,
                        label: item.label,
                        label_reduzida: item.label_reduzida,
                        checked: true
                    }
                })

                clearInterval(intervalId)
            }
        }, 200)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    computed: {
        isColunaTreinamentoSelecionada() {
            return (treinamento) => {
                if (!treinamento || !this.listaColunasTreinamentos) {
                    return false
                }
                return this.listaColunasTreinamentos.some((col) => col.id === treinamento.id && col.checked)
            }
        },
        textoTermoResponsabilidadeDesmarcar() {
            const nomeColaborador = this.form.dadosFuncionario && this.form.dadosFuncionario.nome ? this.form.dadosFuncionario.nome : ''
            const nomeUsuario = this.AUTENTICADO && this.AUTENTICADO.nome ? this.AUTENTICADO.nome : ''
            const labelTreinamento = this.treinamentoMotivoDesmarcar && this.treinamentoMotivoDesmarcar.label ? this.treinamentoMotivoDesmarcar.label : ''
            return `<p>
                Ao clicar em "Confirmar e desmarcar" e retirar o treinamento <strong>${labelTreinamento}</strong> marcado como realizado do colaborador <strong>${nomeColaborador}</strong>, eu, <strong>${nomeUsuario}</strong>, reconheço e aceito que estou assumindo a responsabilidade por esta ação.
                <br><br>
                Além disso, declaro que:
                <br><br>
                Estou ciente de que a retirada do treinamento realizado implica em uma alteração registrada em auditoria no sistema.
                <br><br>
                Confirmo que revisei cuidadosamente as informações e que o motivo informado é válido e justificável.
                <br><br>
                Aceito total responsabilidade por quaisquer consequências decorrentes da retirada do treinamento realizado.
                <br><br>
                Assumo que, ao clicar em "Confirmar e desmarcar" no sistema MyBP, estou ciente e concordo com as disposições deste termo de responsabilidade.
            </p>`
        },
        emTreinamentos() {
            return this.lista.filter((item) => item.treinamento)
        },
        tudoMarcado() {
            let totalTreinamento = this.emTreinamentos.length
            let totalEncontrado = 0

            if (totalTreinamento === 0) {
                return false
            }

            this.emTreinamentos.forEach((item) => {
                let id = item.id
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++
                } else {
                    return false
                }
            })
            let resultado = totalTreinamento === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        },
        emTreinamentosMassa() {
            return this.lista.filter((item) => item.treinamento)
        },

        tudoMarcadoMassa() {
            const totalTreinamento = this.emTreinamentosMassa.length

            if (totalTreinamento === 0) return false

            const todosSelecionados = this.emTreinamentosMassa.every((item) => {
                const id = item.id
                return this.selecionadosMassa.includes(id)
            })

            this.selecionaTudoMassa = todosSelecionados
            return todosSelecionados
        },

        paramsExport() {
            let dados = this.controle.dados
            dados.selecionados = this.selecionados
            return dados
        },
        filtroListaCentroCustoCnpj() {
            if (this.controle.dados.campoCnpj !== '' && this.AUTENTICADO.temFilial) {
                return this.lista_ccs.centros_custos[this.controle.dados.campoCnpj]
            }
            if (!this.AUTENTICADO.temFilial && this.lista_ccs) {
                return this.lista_ccs.centros_custos[Object.keys(this.lista_ccs.centros_custos)[0]]
            }
            return []
        },

        treinamentosFiltrados() {
            if (!this.form.listaVencimentos) return []

            return this.form.listaVencimentos.filter((training) => {
                const matchesSearch = this.trainingSearchQuery === '' || training.label.toLowerCase().includes(this.trainingSearchQuery.toLowerCase())
                let matchesStatus = true
                if (this.trainingStatusFilter !== 'all') {
                    const status = this.getTreinamentoStatus(training)
                    matchesStatus = status === this.trainingStatusFilter
                }

                return matchesSearch && matchesStatus
            })
        },

        treinamentosNaoRealizados() {
            return this.form.listaVencimentos
                ? this.form.listaVencimentos.length -
                      this.form.listaVencimentos.filter((t) => t.fez_treinamento && this.getTreinamentoStatus(t) === 'ativo').length
                : 0
        },

        treinamentosRealizados() {
            return this.form.listaVencimentos
                ? this.form.listaVencimentos.filter((t) => t.fez_treinamento && this.getTreinamentoStatus(t) === 'ativo').length
                : 0
        },

        treinamentosVencendo() {
            return this.form.listaVencimentos
                ? this.form.listaVencimentos.filter((t) => t.fez_treinamento && this.getTreinamentoStatus(t) === 'avencer').length
                : 0
        },
        treinamentosVencidos() {
            return this.form.listaVencimentos
                ? this.form.listaVencimentos.filter((t) => t.fez_treinamento && this.getTreinamentoStatus(t) === 'vencido').length
                : 0
        }
    },
    methods: {
        abrirModal(refName) {
            const ref = this.$refs[refName]
            const r = Array.isArray(ref) ? ref[0] : ref
            if (r && typeof r.abrirModal === 'function') {
                this.$nextTick(() => r.abrirModal())
            }
        },
        fecharModal(refName) {
            const r = this.$refs[refName]
            if (r && r.fecharModal) r.fecharModal()
        },
        toggleDropdown(key) {
            if (!key) {
                return
            }
            const abrindo = this.dropdownAbertoKey !== key
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
            if (abrindo && this.dropdownAbertoKey) {
                this.dropdownJustOpened = true
                this.$nextTick(() => {
                    setTimeout(() => { this.dropdownJustOpened = false }, 50)
                })
            }
        },
        isDropdownOpen(key) {
            return this.dropdownAbertoKey === key
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        onClickOutside(event) {
            if (this.dropdownJustOpened) return
            if (event && event.target && event.target.closest && event.target.closest('.dropdown')) {
                return
            }
            this.dropdownAbertoKey = null
        },
        async carregarSegmentosTreinamento() {
            try {
                const res = await axios.get(`${URL_ADMIN}/${API_PATHS.segmentosHabilitados}`)
                this.segmentosTreinamento = res.data || []
            } catch {
                this.segmentosTreinamento = []
            }
        },
        async trocarSegmentoTreinamento() {
            if (!this.form.feedback_id) {
                return
            }

            this.preload = true
            try {
                const response = await axios.post(`${URL_ADMIN}/${API_PATHS.vencimentosPorSegmento}`, {
                    feedback_id: this.form.feedback_id,
                    segmento_treinamento_id: this.form.segmento_treinamento_id
                })
                this.form.listaVencimentos = (response.data.listaVencimentos || []).map((t) => ({
                    ...t,
                    _fez_treinamento_ja_salvo: !!t.fez_treinamento,
                    _alterado: false
                }))
                if (response.data.privilegio_gestao_rh !== undefined) this.privilegio_gestao_rh = response.data.privilegio_gestao_rh
                if (response.data.treinamento_permitir_desmarcar_realizado !== undefined) this.treinamento_permitir_desmarcar_realizado = response.data.treinamento_permitir_desmarcar_realizado
                this.openPanels = []
                this.expandAll = false
            } catch {
                // mantém preload = false no finally
            } finally {
                this.preload = false
            }
        },
        marcarAlterado(treinamento) {
            if (treinamento) treinamento._alterado = true
        },
        onUploadFinalizado(treinamento) {
            this.anexoUploadAndamento = false
            this.marcarAlterado(treinamento)
        },
        onFezTreinamentoChange(treinamento, newValue) {
            const valorBoolean = newValue === true || newValue === 'true'
            const precisaModal = !valorBoolean && treinamento._fez_treinamento_ja_salvo && (this.privilegio_gestao_rh || this.treinamento_retirar_treinamento_realizado) && this.treinamento_permitir_desmarcar_realizado
            if (precisaModal) {
                treinamento.fez_treinamento = false
                treinamento._alterado = true
                this.treinamentoMotivoDesmarcar = treinamento
                this.showModalMotivoDesmarcar = true
                this.$nextTick(() => {
                    if (this.$refs.modalAuditoriaDesmarcar && typeof this.$refs.modalAuditoriaDesmarcar.abrir === 'function') {
                        this.$refs.modalAuditoriaDesmarcar.abrir()
                    }
                })
            } else {
                treinamento.fez_treinamento = valorBoolean
                treinamento._alterado = true
            }
        },
        /** Chamado pelo botão Cancelar: restaura estado e fecha a modal. */
        fecharModalMotivoDesmarcar() {
            this.limparEstadoModalMotivoDesmarcar()
            if (this.$refs.modalAuditoriaDesmarcar && typeof this.$refs.modalAuditoriaDesmarcar.fechar === 'function') {
                this.$refs.modalAuditoriaDesmarcar.fechar()
            }
        },
        /** Apenas restaura estado e limpa refs; não chama fecharModal (evita loop com @fechou). */
        limparEstadoModalMotivoDesmarcar() {
            const id = this.treinamentoMotivoDesmarcar && this.treinamentoMotivoDesmarcar.id
            this.showModalMotivoDesmarcar = false
            this.treinamentoMotivoDesmarcar = null
            this.desmarcarPreload = false
            if (id && this.form.listaVencimentos) {
                const item = this.form.listaVencimentos.find((t) => t.id === id)
                if (item) {
                    item.fez_treinamento = true
                    item._alterado = false
                }
            }
        },
        /** Handler do @fechou da modal (X ou backdrop): só limpa estado, não chama fecharModal. */
        onModalMotivoDesmarcarFechou() {
            this.limparEstadoModalMotivoDesmarcar()
        },
        /** Handler do @confirmar do componente de auditoria: desmarca o treinamento com o motivo informado. */
        async onConfirmarAuditoriaDesmarcar({ motivo }) {
            if (!this.treinamentoMotivoDesmarcar || !(motivo || '').trim()) return
            const feedbackId = this.form.feedback_id
            const vencimentoId = this.treinamentoMotivoDesmarcar.id
            this.desmarcarPreload = true
            try {
                const response = await axios.post(`${URL_ADMIN}/${API_PATHS.desmarcarTreinamentoRealizado}`, {
                    feedback_id: feedbackId,
                    vencimento_id: vencimentoId,
                    motivo: motivo.trim()
                })
                if (response.status === 200) {
                    const t = this.treinamentoMotivoDesmarcar
                    t.fez_treinamento = false
                    t.data_treinamento = null
                    t.data_vencimento = null
                    t.numero_fat = null
                    t.arquivo = []
                    t.arquivoDel = []
                    t._fez_treinamento_ja_salvo = false
                    this.showModalMotivoDesmarcar = false
                    this.treinamentoMotivoDesmarcar = null
                    this.desmarcarPreload = false
                    if (this.$refs.modalAuditoriaDesmarcar && typeof this.$refs.modalAuditoriaDesmarcar.fechar === 'function') {
                        this.$refs.modalAuditoriaDesmarcar.fechar()
                    }
                    mostraSucesso('', response.data.msg || 'Treinamento desmarcado com sucesso.')
                    await this.salvar(t)
                }
            } catch (err) {
                const msg = err.response && err.response.data && err.response.data.msg ? err.response.data.msg : 'Erro ao desmarcar treinamento.'
                mostraErro('', msg)
            } finally {
                this.desmarcarPreload = false
            }
        },
        marcarDesmarcarTodosTreinamentosColuna(valor) {
            this.listaColunasTreinamentos.map((item) => {
                item.checked = valor
            })
        },
        changeCnpj() {
            this.controle.dados.campoCentroCusto = ''
            this.atualizar()
        },
        selecionaTreinados(valor) {
            if (valor !== 'S') {
                this.controle.dados.treinamentos_selecionados = []
            }
            this.atualizar()
        },
        addTreinamento(valor) {
            if (valor !== '') {
                if (!this.controle.dados.treinamentos_selecionados.includes(valor)) {
                    this.controle.dados.treinamentos_selecionados.push(valor)
                } else {
                    mostraErro('', 'Treinamento já adicionado na lista')
                }
            }
            if (valor === 'rm') {
                this.controle.dados.treinamentos_selecionados = []
                this.controle.dados.treinamentos = ''
            }
            if (valor === 'todos') {
                this.controle.dados.treinamentos_selecionados = []
                this.listaTodosTreinamentos.forEach((item) => this.controle.dados.treinamentos_selecionados.push(item.label))
            }

            this.controle.dados.treinamentos = ''

            this.controle.dados.campo_treinados = this.controle.dados.treinamentos_selecionados.length > 0 ? 'S' : ''
            this.atualizar()
        },
        removeTreinamento(indice) {
            this.controle.dados.treinamentos_selecionados.splice(indice, 1)
            this.controle.dados.campo_treinados = this.controle.dados.treinamentos_selecionados.length > 0 ? 'S' : ''
            this.atualizar()
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo

            const selectedSet = new Set(this.selecionados)

            this.emTreinamentos.forEach((item) => {
                const id = item.id
                this.selecionaTudo ? selectedSet.add(id) : selectedSet.delete(id)
            })

            this.selecionados = Array.from(selectedSet)
        },

        selecionaTodosMassa() {
            this.selecionaTudoMassa = !this.selecionaTudoMassa

            const selectedSet = new Set(this.selecionadosMassa)

            this.lista.forEach((item) => {
                const id = item.id
                this.selecionaTudoMassa ? selectedSet.add(id) : selectedSet.delete(id)
            })

            this.selecionadosMassa = Array.from(selectedSet)
        },

        /**
         * Gera carteira/etiqueta em nova aba (comportamento igual ao Blade: form POST target _blank).
         * @param {string} tipo - 'treinamento' | 'bloqueio' | 'treinamento_bloqueio'
         */
        gerarCarteiras(tipo) {
            if (!this.selecionados.length) return

            const form = document.createElement('form')
            form.method = 'post'
            form.action = `${URL_ADMIN}/${API_PATHS.carteiras}`
            form.target = '_blank'
            form.style.display = 'none'

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            if (token) {
                const inputToken = document.createElement('input')
                inputToken.type = 'hidden'
                inputToken.name = '_token'
                inputToken.value = token
                form.appendChild(inputToken)
            }

            const inputTipo = document.createElement('input')
            inputTipo.type = 'hidden'
            inputTipo.name = 'tipo'
            inputTipo.value = tipo
            form.appendChild(inputTipo)

            this.selecionados.forEach((id) => {
                const input = document.createElement('input')
                input.type = 'hidden'
                input.name = 'selecionados[]'
                input.value = id
                form.appendChild(input)
            })

            document.body.appendChild(form)
            form.submit()
            document.body.removeChild(form)
        },

        formCadastra() {
            this.form = _.cloneDeep(this.formDefault)
            formReset()
            setupCampo()
        },

        async abrirFormMassa() {
            this.preload = true

            if (this.formMassa.listaVencimentos.length > 0 && !this.formMassaDefault) {
                this.formMassaDefault = await _.cloneDeep(this.formMassa)
            }

            _.assign(this.formMassa, this.formMassaDefault)

            this.atualizado = false
            this.cadastrando = false
            this.visualizar = false
            this.cadastrado = false

            this.preload = false
            this.$nextTick(() => this.abrirModal(REFS_MODAL.JANELA_TREINAMENTO_MASSA))
        },

        async formAlterar(curriculo_id) {
            this.preload = true
            this.atualizado = false
            this.cadastrando = false
            this.visualizar = false
            this.cadastrado = false
            this.form = _.cloneDeep(this.formDefault)
            this.form.curriculo_id = curriculo_id

            try {
                const response = await axios.get(`${URL_ADMIN}/${API_PATHS.editar}/${this.form.curriculo_id}/editar`)
                const data = response.data
                // API retorna relações com maiúscula (Treinamento, Curriculo, Feedback)
                const treinamento = data.Treinamento || data.treinamento
                const curriculo = data.Curriculo || data.curriculo
                const feedback = data.Feedback || data.feedback

                this.form.dadosFuncionario = data.dadosFuncionario || {}
                this.form.segmento_treinamento_id = data.segmento_treinamento_id ?? null
                this.privilegio_gestao_rh = data.privilegio_gestao_rh ?? false
                this.treinamento_permitir_desmarcar_realizado = data.treinamento_permitir_desmarcar_realizado ?? false

                if (treinamento) {
                    this.editando = true
                    Object.assign(this.form, treinamento)
                    this.form.listaVencimentos = (data.listaVencimentos || []).map((t) => ({
                        ...t,
                        _fez_treinamento_ja_salvo: !!t.fez_treinamento,
                        _alterado: false
                    }))
                    this.form.nr_trinta_tres = data.nr_trinta_tres
                    this.form.nr_trinta_cinco = data.nr_trinta_cinco
                    this.form.nome = curriculo?.nome ?? ''
                } else {
                    this.form.feedback_id = data.feedback_id
                    this.form.curriculo_id = curriculo_id
                    this.editando = false
                    this.form.nr_trinta_tres = data.nr_trinta_tres
                    this.form.nr_trinta_cinco = data.nr_trinta_cinco
                    this.form.vencimentos = []
                }

                if (feedback?.exame) {
                    Object.assign(this.form.exame, feedback.exame)
                } else if (feedback?.id) {
                    this.form.exame.feedback_id = feedback.id
                }

                this.form.listaVencimentos = (data.listaVencimentos || []).map((t) => ({
                    ...t,
                    _fez_treinamento_ja_salvo: !!t.fez_treinamento,
                    _alterado: false
                }))

                if (!this.form.nr_trinta_tres && this.form.listaVencimentos?.length) {
                    const index = _.findIndex(this.form.listaVencimentos, { id: 7 })
                    if (index >= 0) this.form.listaVencimentos.splice(index, 1)
                }
                if (!this.form.nr_trinta_cinco && this.form.listaVencimentos?.length) {
                    const index = _.findIndex(this.form.listaVencimentos, { id: 6 })
                    if (index >= 0) this.form.listaVencimentos.splice(index, 1)
                }
                this.$nextTick(() => this.abrirModal(REFS_MODAL.JANELA_TREINAMENTO))
            } catch (err) {
                console.error('formAlterar:', err)
            } finally {
                this.preload = false
            }
        },

        async salvar(treinamento) {
            formReset()
            const el = document.getElementById('janelaTreinamento')
            if (el) {
                const inputs = el.querySelectorAll('input, select, textarea, button')
                inputs.forEach((i) => i.blur && i.blur())
            }

            if (this.nr_trinta_tres) {
                let nr33 = _.find(this.form.listaVencimentos, { id: 7, fez_treinamento: false })
                if (nr33) {
                    nr33.fez_treinamento = false
                    mostraErro('', 'ATENÇÃO NR33 não pode ser vazio!')
                    return false
                }
            }

            if (this.nr_trinta_cinco) {
                let nr35 = _.find(this.form.listaVencimentos, { id: 6, fez_treinamento: false })

                if (nr35) {
                    nr35.fez_treinamento = false
                    mostraErro('', 'ATENÇÃO NR35 não pode ser vazio!')
                    return false
                }
            }

            const janela = document.getElementById('janelaTreinamento')
            if (janela && janela.querySelectorAll('input.is-invalid, select.is-invalid, textarea.is-invalid, button.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false
            }

            const vencimentoId = treinamento ? treinamento.id : null
            if (vencimentoId) {
                this.salvandoVencimentoId = vencimentoId
            } else {
                this.preload = true
            }
            try {
                const response = await axios.post(`${URL_ADMIN}/${API_PATHS.store}`, this.form)
                if (response.status === 201) {
                    if (vencimentoId && this.form.listaVencimentos) {
                        const item = this.form.listaVencimentos.find((t) => t.id === vencimentoId)
                        if (item) item._alterado = false
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Treinamento atualizado com sucesso.')
                    } else {
                        mostraSucesso('', 'Treinamento atualizado com sucesso.')
                    }
                    this.atualizar()
                    // Salvar individual: mantém o formulário visível; só seta cadastrado se não veio de um card
                    if (!vencimentoId) {
                        this.cadastrado = true
                    }
                }
            } catch (err) {
                if (err.response && err.response.data && err.response.data.msg && typeof toastr !== 'undefined') {
                    toastr.error(err.response.data.msg)
                }
            } finally {
                this.salvandoVencimentoId = null
                this.preload = false
            }
        },

        async salvarMassa() {
            formReset()
            const el = document.getElementById('janelaTreinamentoMassa')
            if (el) {
                el.querySelectorAll('input, select, textarea, button').forEach((i) => i.blur && i.blur())
            }

            const janelaMassa = document.getElementById('janelaTreinamentoMassa')
            if (janelaMassa && janelaMassa.querySelectorAll('input.is-invalid, select.is-invalid, textarea.is-invalid, button.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false
            }

            this.preload = true
            this.formMassa.selecionadosMassa = this.selecionadosMassa
            this.formMassa.tipo = 'Fixo'

            try {
                const response = await axios.post(`${URL_ADMIN}/${API_PATHS.salvarMassa}`, this.formMassa)
                if (response.status === 201) {
                    this.cadastrado = true
                    this.atualizar()
                }
            } catch {
                // mantém preload = false no finally
            } finally {
                this.preload = false
            }
        },

        abriJanelaEnviar(obj) {
            this.formEnviar = _.cloneDeep(this.formEnviarDefault)
            formReset()
            setupCampo()

            this.formEnviar.nome = obj.curriculo.nome
            this.formEnviar.titulo = `Enviar carteira etiqueta de ${this.formEnviar.nome}`
            this.formEnviar.email = obj.curriculo.email
            this.formEnviar.token = obj.treinamento.token
            this.$nextTick(() => this.abrirModal(REFS_MODAL.JANELA_ENVIAR))
        },

        async enviar() {
            const el = document.getElementById('janelaEnviar')
            if (el) el.querySelectorAll('input, select, textarea, button').forEach((i) => i.blur && i.blur())
            const janelaEnviar = document.getElementById('janelaEnviar')
            if (janelaEnviar && janelaEnviar.querySelectorAll('input.is-invalid, select.is-invalid, textarea.is-invalid, button.is-invalid').length) {
                mostraErro('', 'Verificar os campos marcados')
                return false
            }

            this.formEnviar.preload = true
            try {
                const response = await axios.post(`${URL_ADMIN}/${API_PATHS.enviarCarteira}`, this.formEnviar)
                const data = response.data
                this.formEnviar.enviado = data.enviado
            } catch {
                this.formEnviar.enviado = false
            } finally {
                this.formEnviar.preload = false
            }
        },

        abriJanelaEnviarAviso() {
            this.formEnviarAviso = _.cloneDeep(this.formEnviarAvisoDefault)
            formReset()
            setupCampo()
            this.$nextTick(() => this.abrirModal(REFS_MODAL.JANELA_ENVIAR_AVISO))
        },

        async enviarAviso() {
            const el = document.getElementById('janelaEnviarAviso')
            if (el) el.querySelectorAll('input, select, textarea, button').forEach((i) => i.blur && i.blur())
            const janelaAviso = document.getElementById('janelaEnviarAviso')
            if (janelaAviso && janelaAviso.querySelectorAll('input.is-invalid, select.is-invalid, textarea.is-invalid, button.is-invalid').length) {
                mostraErro('', 'Verificar os campos marcados')
                return false
            }

            this.formEnviarAviso.preload = true
            try {
                const response = await axios.post(`${URL_ADMIN}/${API_PATHS.proximovencimento}`, this.formEnviarAviso)
                const data = response.data
                this.formEnviarAviso.enviado = data.enviado
            } catch {
                this.formEnviarAviso.enviado = false
            } finally {
                this.formEnviarAviso.preload = false
            }
        },

        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior != this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
            }
        },

        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
        },

        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior != this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = ''
                this.controle.dados.autocomplete_label_cliente = ''
                this.controle.dados.campoCliente = ''
            }
        },

        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id
            this.controle.dados.autocomplete_label_cliente = obj.label
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label
        },

        validaData() {
            if (this.form.data_aso.length >= 10) {
                let dataCorreta = moment(this.form.data_aso, 'DD/MM/YYYY')
                if (!dataCorreta.isValid()) {
                    mostraErro('', 'A data do ASO inserida é inválida')
                    this.form.data_aso = ''
                }
            }
        },

        listaVagas() {
            this.preload = true
            $.get(`${URL_PUBLICO}/lista-vagas`)
                .done((data) => {
                    this.preload = false
                    this.vagas = data.vagas
                })
                .fail((data) => {
                    this.preload = false
                })
        },

        listaAreasGeral() {
            this.preload = true
            $.get(`${URL_PUBLICO}/lista-areas`)
                .done((data) => {
                    this.preload = false
                    this.listaAreas = data.areas
                })
                .fail((data) => {
                    this.preload = false
                })
        },

        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false

            this.preload = false
        },

        carregou(dados) {
            this.lista = dados.itens
            this.listaTodosTreinamentos = dados.vencimentos
            this.selecionaTudo = this.tudoMarcado
            this.formMassa.listaVencimentos = (dados.vencimentos || []).map((t) => ({
                ...t,
                _fez_treinamento_ja_salvo: !!t.fez_treinamento
            }))
            this.lista_ccs = dados.cc
            if (!this.AUTENTICADO.temFilial) {
                this.controle.dados.campoCnpj = Object.keys(dados.cc.cnpjs)[0]
            }
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            if (this.$refs && this.$refs.componente) {
                this.$refs.componente.atual = 1
                if (this.$refs.componente.buscar) this.$refs.componente.buscar()
            }
        },

        atualizarVencimentoString() {
            const d = this.controle.dados
            if (d.campoVencimento && d.dataInicioVencimento && d.dataFimVencimento) {
                d.vencimento = d.dataInicioVencimento + ' até ' + d.dataFimVencimento
            } else {
                d.vencimento = ''
            }
            this.atualizar()
        },

        atualizarPeriodoTreinadoString() {
            const d = this.controle.dados
            if (d.campoPeriodoTreinado && d.dataInicioPeriodoTreinado && d.dataFimPeriodoTreinado) {
                d.periodoTreinado = d.dataInicioPeriodoTreinado + ' até ' + d.dataFimPeriodoTreinado
            } else {
                d.periodoTreinado = ''
            }
            this.atualizar()
        },

        togglePanel(index) {
            const position = this.openPanels.indexOf(index)
            if (position !== -1) {
                this.openPanels.splice(position, 1)
            } else {
                this.openPanels.push(index)
            }
        },

        toggleAllPanels() {
            if (this.expandAll) {
                this.openPanels = []
            } else {
                this.openPanels = this.form.listaVencimentos.map((_, index) => index)
            }
            this.expandAll = !this.expandAll
        },

        getTreinamentoStatus(treinamento) {
            if (!treinamento.fez_treinamento) return 'inativo'
            if (!treinamento.data_vencimento) return 'ativo'

            const today = new Date()

            let expiryDate
            if (typeof treinamento.data_vencimento === 'string') {
                const parts = treinamento.data_vencimento.split('/')
                if (parts.length === 3) {
                    expiryDate = new Date(parts[2], parts[1] - 1, parts[0])
                } else {
                    return 'ativo'
                }
            } else {
                expiryDate = new Date(treinamento.data_vencimento)
            }

            if (expiryDate < today) {
                return 'vencido'
            }

            const thirtyDaysFromNow = new Date()
            thirtyDaysFromNow.setDate(today.getDate() + 30)

            if (expiryDate <= thirtyDaysFromNow) {
                return 'avencer'
            }

            return 'ativo'
        },

        getStatusText(treinamento) {
            if (!treinamento.fez_treinamento) {
                return 'Não Realizado'
            }

            const status = this.getTreinamentoStatus(treinamento)

            switch (status) {
                case 'ativo':
                    return 'Em dia'
                case 'avencer':
                    return 'A Vencer'
                case 'vencido':
                    return 'Vencido'
                default:
                    return 'Desconhecido'
            }
        },

        calculoDataExpiracao(treinamento) {
            if (treinamento) treinamento._alterado = true
            if (!treinamento.data_treinamento) return

            const dateParts = treinamento.data_treinamento.split('/')
            if (dateParts.length !== 3) return

            const trainingDate = new Date(parseInt(dateParts[2]), parseInt(dateParts[1]) - 1, parseInt(dateParts[0]))

            let expiryDate = new Date(trainingDate)

            if (treinamento.prazo_fixo) {
                expiryDate.setDate(expiryDate.getDate() + parseInt(treinamento.prazo_fixo))
            } else {
                return
            }

            const dd = String(expiryDate.getDate()).padStart(2, '0')
            const mm = String(expiryDate.getMonth() + 1).padStart(2, '0')
            const yyyy = expiryDate.getFullYear()

            treinamento.data_vencimento = `${dd}/${mm}/${yyyy}`
        }
    }
})
</script>
<style scoped>
/* Garante que o dropdown aberto fique acima do card e que o menu seja visível */
.dropdown-carteira-etiquetas.show {
    position: relative;
    z-index: 1040;
}
.dropdown-carteira-etiquetas.show .dropdown-menu {
    z-index: 1041;
}
</style>
