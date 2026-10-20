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

<modal ref="modalAjudaVinculoCargo" id="modalAjudaVinculoCargo" titulo="Como vincular treinamento ao cargo" size="g">
    <template #conteudo>
        <p class="mb-3">
            Quando aparece o aviso de que o treinamento <strong>não está vinculado ao cargo</strong>, significa que o cadastro do treinamento (vencimento) não está associado ao <strong>cargo (vaga)</strong> do colaborador nesta empresa. Você pode corrigir o cadastro e, em seguida, os dados na carteira continuam podendo ser atualizados normalmente.
        </p>
        <h6 class="font-weight-bold">Opção 1 — Pelo cadastro de Cargos (recomendado)</h6>
        <ol class="pl-3 mb-3">
            <li>Acesse o menu de cadastros e abra <strong>Cargos</strong> (lista de cargos / vagas).</li>
            <li>Localize o <strong>mesmo cargo</strong> vinculado ao colaborador e clique para <strong>editar</strong>.</li>
            <li>Na seção <strong>Treinamentos</strong>, escolha o <strong>padrão de treinamento</strong>, se aplicável.</li>
            <li>Use <strong>Adicionar treinamento</strong> para incluir o treinamento desejado na lista do cargo.</li>
            <li>Salve o cargo com <strong>Alterar</strong>.</li>
        </ol>
        <h6 class="font-weight-bold">Opção 2 — Pelo cadastro de Treinamentos da indústria</h6>
        <ol class="pl-3 mb-3">
            <li>Abra o cadastro de <strong>Treinamentos da indústria</strong> (treinamentos / vencimentos configurados para a empresa).</li>
            <li>Edite o treinamento em questão e marque os <strong>cargos</strong> aos quais ele se aplica, ou use a opção de vínculo a <strong>todos os cargos</strong> quando o treinamento for obrigatório para qualquer função.</li>
            <li>Salve as alterações.</li>
        </ol>
        <div class="alert alert-light border mb-0">
            <strong>Dica:</strong> depois de vincular o treinamento ao cargo, volte à carteira/etiquetas e atualize a tela ou reabra o colaborador para conferir se o aviso deixou de aparecer.
        </div>
    </template>
</modal>

<modal ref="janelaTreinamento" id="janelaTreinamento" :titulo="tituloJanela" :size="95">
    <template #conteudo>
        <p class=" mt-2 text-center" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>

        <div v-if="!preload && (!cadastrado && !atualizado)" class="mybp-modal-form mybp-filtros-compactos">

            <aside
                class="treino-modal-perfil"
                :class="'treino-modal-perfil--' + chaveSituacaoColaboradorModal(form.dadosFuncionario)"
                aria-label="Dados do colaborador"
            >
                <div class="treino-modal-perfil__foto-wrap">
                    <img
                        v-if="form.dadosFuncionario && form.dadosFuncionario.foto_3x4_thumb"
                        :src="form.dadosFuncionario.foto_3x4_thumb"
                        :alt="(form.dadosFuncionario && form.dadosFuncionario.nome) || 'Foto 3x4'"
                        width="48"
                        height="64"
                        loading="lazy"
                        decoding="async"
                        class="treino-modal-perfil__foto"
                        :class="{ 'is-ready': form._modalFoto3x4Ready }"
                        @load="onModalFoto3x4Load"
                        @error="onModalFoto3x4Error"
                    />
                    <div
                        v-if="form.dadosFuncionario && form.dadosFuncionario.foto_3x4_thumb && !form._modalFoto3x4Ready"
                        class="treino-modal-perfil__foto treino-modal-perfil__foto--loading"
                        aria-hidden="true"
                    >
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                    <div
                        v-else-if="!(form.dadosFuncionario && form.dadosFuncionario.foto_3x4_thumb)"
                        class="treino-modal-perfil__foto treino-modal-perfil__foto--vazia"
                        aria-hidden="true"
                    >
                        <i class="fa fa-user"></i>
                    </div>
                </div>

                <div class="treino-modal-perfil__corpo">
                    <div class="treino-modal-perfil__titulo">
                        <h3 class="treino-modal-perfil__nome">{{ nomeIdadeModal(form.dadosFuncionario) }}</h3>
                        <p class="treino-modal-perfil__meta-id">
                            <span class="treino-modal-perfil__id">#{{ form.dadosFuncionario.feedback_id || form.feedback_id || '—' }}</span>
                            <span class="treino-modal-perfil__sep" aria-hidden="true">·</span>
                            <span
                                class="treino-modal-perfil__status"
                                :class="'treino-modal-perfil__status--' + chaveSituacaoColaboradorModal(form.dadosFuncionario)"
                            >
                                {{ textoSituacaoColaboradorModal(form.dadosFuncionario) }}
                            </span>
                            <template v-if="mostrarStatusAdmissaoModal(form.dadosFuncionario)">
                                <span class="treino-modal-perfil__sep" aria-hidden="true">·</span>
                                <span class="treino-modal-perfil__status treino-modal-perfil__status--aberto">
                                    {{ textoCampoModal(form.dadosFuncionario.status_admissao) }}
                                </span>
                            </template>
                        </p>
                    </div>

                    <div class="treino-modal-perfil__linhas">
                        <p
                            v-for="(linha, linhaIdx) in modalPerfilLinhasTreinamento"
                            :key="'treino-linha-' + linhaIdx"
                            class="treino-modal-perfil__linha"
                        >
                            <span
                                v-for="(campo, idx) in linha"
                                :key="'treino-campo-' + linhaIdx + '-' + idx"
                                class="treino-modal-perfil__item"
                            >
                                <template v-if="idx > 0">
                                    <span class="treino-modal-perfil__sep" aria-hidden="true">·</span>
                                </template>
                                <span class="treino-modal-perfil__k">{{ campo.label }}:</span>
                                {{ campo.valor }}
                            </span>
                        </p>
                    </div>
                </div>
            </aside>

            <div class="alert alert-warning" v-if="form.alertaSemCargoVinculo">
                <i class="fa fa-exclamation-triangle"></i>
                Não foi possível validar vínculo de treinamentos por cargo porque o colaborador está sem cargo definido.
            </div>

            <div class="alert alert-danger" v-if="form.listaVencimentosNaoVinculadosCargo && form.listaVencimentosNaoVinculadosCargo.length > 0">
                <strong>Treinamentos realizados sem vínculo com o cargo:</strong>
                <ul class="mb-0 mt-2 pl-3">
                    <li v-for="treinamentoAlerta in form.listaVencimentosNaoVinculadosCargo" :key="`alerta-${treinamentoAlerta.id}`">
                        {{ treinamentoAlerta.label_reduzida || treinamentoAlerta.label }}
                    </li>
                </ul>
            </div>

            <fieldset>
                <legend>Padrão de treinamento</legend>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>Padrão</label>
                            <select
                                class="form-control"
                                v-model="form.padrao_treinamento_id"
                                @change="trocarPadraoTreinamento"
                                :disabled="preload"
                            >
                                <option :value="null">Selecione</option>
                                <option v-for="p in padroesTreinamento" :key="p.id" :value="p.id">
                                    {{ p.nome }}
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
        <div class="row">
            <div class="col-md-8 mb-2 mb-md-0">
                <div class="input-group input-group">
                    <input type="text" class="form-control" placeholder="Buscar treinamento..."
                        v-model="trainingSearchQuery">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-control" v-model="trainingVinculoFilter">
                    <option value="all">Todos</option>
                    <option value="com_vinculo">Com vínculo ao cargo</option>
                    <option value="sem_vinculo">Sem vínculo ao cargo</option>
                </select>
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
                        <span v-if="treinamento.nao_vinculado_ao_cargo" class="text-danger">
                            (treinamento sem vínculo com o cargo)
                        </span>
                        <span v-if="isDeOutroPadrao(treinamento)" class="text-warning font-weight-bold">
                            ({{ textoOutroPadrao(treinamento) }})
                        </span>
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
                            v-if="treinamento.nao_vinculado_ao_cargo">
                            <strong>Treinamento sem vínculo com o cargo atual.</strong>
                            Este treinamento não está vinculado ao cargo do colaborador; você ainda pode atualizar os dados abaixo.
                            <button type="button"
                                class="btn btn-link btn-sm p-0 ml-1 align-baseline text-info"
                                title="Como vincular treinamento ao cargo"
                                aria-label="Como vincular treinamento ao cargo"
                                @click.prevent="abrirModal(refsModal.MODAL_AJUDA_VINCULO_CARGO)">
                                <i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>
                            </button>
                        </div>
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
                                        :disabled="(treinamento.fez_treinamento && treinamento._fez_treinamento_ja_salvo && !((privilegio_gestao_rh || treinamento_retirar_treinamento_realizado) && treinamento_permitir_desmarcar_realizado)) || salvandoVencimentoId === treinamento.id"
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
                                    :max="dataHojeBr"
                                    obrigatorio
                                    @input="calculoDataExpiracao(treinamento)"></datepicker>
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
                                    :max="dataHojeBr"
                                    obrigatorio
                                    @input="marcarAlterado(treinamento)"></datepicker>
                            </div>
                            <div class="col-md-6 mt-2">
                                <datepicker v-model="treinamento.data_vencimento"
                                    label="Data Vencimento"
                                    :disabled="salvandoVencimentoId === treinamento.id"
                                    :min="dataHojeBr"
                                    obrigatorio
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
                            <div class="col-md-12" :class="{ 'is-invalid': anexoFatInvalido(treinamento) }" :style="salvandoVencimentoId === treinamento.id ? { pointerEvents: 'none', opacity: 0.7 } : {}">
                                <upload :model="treinamento.arquivo"
                                    :model-delete="treinamento.arquivoDel"
                                    :url="url_anexo"
                                    :quantidade="5"
                                    :multi="true"
                                    :apenas-doc-pdf-img="true"
                                    :label="treinamento_fat_obrigatorio && treinamento.fez_treinamento ? 'ANEXAR FAT *' : 'ANEXAR FAT'"
                                    @onProgresso="anexoUploadAndamento=true"
                                    @onFinalizado="onUploadFinalizado(treinamento)"></upload>
                                <small class="form-text text-muted">Word, PDF ou imagens — até 5 arquivos.</small>
                                <div class="invalid-feedback" v-if="anexoFatInvalido(treinamento)">Anexo da FAT é obrigatório para este cliente em treinamentos realizados.</div>
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
    <div class="alert alert-warning"
        v-if="!preload && form.padrao_treinamento_id && (!form.listaVencimentos || form.listaVencimentos.length === 0)">
        <i class="fa fa-exclamation-triangle"></i>
        Este padrão não possui treinamentos vinculados. Cadastre ou vincule treinamentos no Padrão de treinamento.
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
                                            :disabled="treinamento.fez_treinamento && treinamento._fez_treinamento_ja_salvo && !((privilegio_gestao_rh || treinamento_retirar_treinamento_realizado) && treinamento_permitir_desmarcar_realizado)">
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
                                                :max="dataHojeBr"
                                                obrigatorio
                                                @input="calculoDataExpiracao(treinamento)"></datepicker>
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

<FiltroListagem
    class="mt-2 trn-carteira-filtros mybp-filtros-compactos"
    :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
    :desabilitado="controle.carregando"
    @submit="atualizar"
    @limpar="limparFiltros"
>
    <template #filtros>
        <!-- Linha 1 -->
        <date-range-filter
            v-model:enabled="controle.dados.campoVencimento"
            v-model:start-date="controle.dados.dataInicioVencimento"
            v-model:end-date="controle.dados.dataFimVencimento"
            :disabled="controle.carregando"
            :id-suffix="'vencimento-' + hash"
            label="Período de vencimento"
            wrapper-class="col-12 col-md-4 trn-carteira-periodo mybp-filtro-periodo"
            @change="atualizarVencimentoString"
        />
        <date-range-filter
            v-model:enabled="controle.dados.campoPeriodoTreinado"
            v-model:start-date="controle.dados.dataInicioPeriodoTreinado"
            v-model:end-date="controle.dados.dataFimPeriodoTreinado"
            :disabled="controle.carregando"
            :id-suffix="'periodo-treinado-' + hash"
            label="Período treinado"
            wrapper-class="col-12 col-md-4 trn-carteira-periodo mybp-filtro-periodo"
            @change="atualizarPeriodoTreinadoString"
        />
        <div class="col-12 col-md-4">
            <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                <label class="mybp-label" for="trn-carteira-treinados">Treinados</label>
                <div class="mybp-combobox-wrap">
                    <combobox-auto-complete
                        ref="comboFiltroTreinados"
                        input-id="trn-carteira-treinados"
                        instance-id="trn-carteira-treinados"
                        :disabled="controle.carregando"
                        :options="opcoesTreinados"
                        placeholder-blur="Todos"
                        empty-message="Nenhuma opção encontrada."
                        :max-results="10"
                        v-model="controle.dados.campo_treinados"
                        @opening="fecharOutrosComboboxes('trn-carteira-treinados')"
                        @select="onSelectTreinados"
                    />
                </div>
            </div>
        </div>

        <!-- Linha 2 -->
        <div class="col-12 col-md-4">
            <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                <label class="mybp-label" for="trn-carteira-busca">
                    Colaborador / CPF
                    <span v-if="buscaUnificadaEhCpf" class="trn-carteira-filtro-hint">CPF</span>
                </label>
                <input
                    id="trn-carteira-busca"
                    type="text"
                    placeholder="Nome ou CPF"
                    autocomplete="off"
                    inputmode="search"
                    class="form-control form-control-sm"
                    :disabled="controle.carregando"
                    :value="campoBuscaUnificada"
                    @input="onInputBuscaUnificada"
                />
            </div>
        </div>
        <div class="col-12 col-md-4" v-if="lista_ccs && AUTENTICADO.temFilial">
            <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                <label class="mybp-label" for="trn-carteira-cnpj">CNPJ</label>
                <div class="mybp-combobox-wrap">
                    <combobox-auto-complete
                        ref="comboFiltroCnpj"
                        input-id="trn-carteira-cnpj"
                        instance-id="trn-carteira-cnpj"
                        :disabled="controle.carregando"
                        :options="opcoesCnpj"
                        placeholder-blur="Todos"
                        empty-message="Nenhum CNPJ encontrado."
                        :max-results="50"
                        v-model="controle.dados.campoCnpj"
                        @opening="fecharOutrosComboboxes('trn-carteira-cnpj')"
                        @select="onSelectCnpj"
                    />
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4" v-if="lista_ccs">
            <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                <label class="mybp-label" for="trn-carteira-cc">Centro de custo</label>
                <div class="mybp-combobox-wrap">
                    <combobox-auto-complete
                        ref="comboFiltroCc"
                        input-id="trn-carteira-cc"
                        instance-id="trn-carteira-cc"
                        :disabled="controle.carregando || !opcoesCentroCusto.length"
                        :options="opcoesCentroCusto"
                        placeholder-blur="Todos"
                        empty-message="Nenhum centro encontrado."
                        :max-results="80"
                        v-model="controle.dados.campoCentroCusto"
                        @opening="fecharOutrosComboboxes('trn-carteira-cc')"
                        @select="onSelectFiltro"
                    />
                </div>
            </div>
        </div>

        <!-- Linha 3 -->
        <div class="col-12 col-md-4">
            <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                <label class="mybp-label">Vaga</label>
                <autocomplete
                    :disabled="controle.carregando"
                    :caminho="controle.dados.caminho_autocomplete"
                    :valido="controle.dados.campoVaga !== ''"
                    v-model="controle.dados.autocomplete_label"
                    placeholder="Buscar vaga"
                    @onblur="resetaCampo"
                    @onselect="selecionaVaga"
                />
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                <label class="mybp-label" for="trn-carteira-cargo">Cargo</label>
                <input
                    id="trn-carteira-cargo"
                    type="text"
                    placeholder="Cargo"
                    autocomplete="off"
                    class="form-control form-control-sm"
                    :disabled="controle.carregando"
                    v-model="controle.dados.campoCargo"
                />
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                <label class="mybp-label" for="trn-carteira-situacao">Situação</label>
                <div class="mybp-combobox-wrap">
                    <combobox-auto-complete
                        ref="comboFiltroSituacao"
                        input-id="trn-carteira-situacao"
                        instance-id="trn-carteira-situacao"
                        :disabled="controle.carregando"
                        :options="opcoesSituacaoColaborador"
                        placeholder-blur="Ativos"
                        empty-message="Nenhuma opção encontrada."
                        :max-results="10"
                        v-model="campoSituacaoCombo"
                        @opening="fecharOutrosComboboxes('trn-carteira-situacao')"
                        @select="onSelectFiltro"
                    />
                </div>
            </div>
        </div>

        <!-- Linha 4 -->
        <div class="col-12">
            <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                <label class="mybp-label" for="trn-carteira-treino">
                    Treinamentos específicos
                    <span v-if="controle.dados.treinamentos_selecionados.length" class="trn-carteira-filtro-hint">
                        {{ controle.dados.treinamentos_selecionados.length }}
                    </span>
                </label>
                <div class="mybp-combobox-wrap">
                    <combobox-auto-complete
                        ref="comboFiltroTreinamento"
                        input-id="trn-carteira-treino"
                        instance-id="trn-carteira-treino"
                        :disabled="controle.carregando || !opcoesTreinamentoDisponiveis.length"
                        :options="opcoesTreinamentoDisponiveis"
                        placeholder-blur="Buscar treinamento"
                        placeholder-focus="Digite para filtrar…"
                        empty-message="Nenhum treinamento disponível."
                        :max-results="80"
                        v-model="comboTreinamento"
                        @opening="fecharOutrosComboboxes('trn-carteira-treino')"
                        @select="onSelectTreinamentoEspecifico"
                    />
                </div>
            </div>
            <div
                class="trn-carteira-treino-badges"
                v-if="controle.dados.treinamentos_selecionados.length"
                role="list"
                aria-label="Treinamentos selecionados"
            >
                <span
                    v-for="(item, ind) in controle.dados.treinamentos_selecionados"
                    :key="`${item}-${ind}`"
                    class="trn-carteira-treino-badge"
                    role="listitem"
                >
                    <span class="trn-carteira-treino-badge__text">{{ item }}</span>
                    <button
                        type="button"
                        class="trn-carteira-treino-badge__remove"
                        :aria-label="`Remover ${item}`"
                        @click="removeTreinamento(ind)"
                    >
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                </span>
                <button
                    type="button"
                    class="btn btn-sm btn-link px-1 py-0"
                    :disabled="controle.carregando"
                    @click="limparTreinamentosSelecionados"
                >
                    Limpar
                </button>
            </div>
        </div>

        <div class="col-12" v-if="advancedFilterChips.length && !filtrosAvancadosAbertos">
            <div class="trn-carteira-chips" role="list" aria-label="Filtros avançados ativos">
                <span
                    v-for="chip in advancedFilterChips"
                    :key="chip.key"
                    class="trn-carteira-chip"
                    role="listitem"
                >
                    <span class="trn-carteira-chip__text">{{ chip.label }}</span>
                    <button
                        type="button"
                        class="trn-carteira-chip__remove"
                        :aria-label="`Remover filtro ${chip.label}`"
                        @click="limparFiltroAtivo(chip.key)"
                    >
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                </span>
            </div>
        </div>

        <div
            class="col-12 mybp-filtros-avancados-shell"
            :class="{ 'is-open': filtrosAvancadosAbertos }"
            :aria-hidden="filtrosAvancadosAbertos ? 'false' : 'true'"
        >
            <div class="mybp-filtros-avancados-shell__inner">
            <div class="trn-carteira-avancados mybp-filtros-avancados">
                <div class="trn-carteira-avancados__grid">
                    <div class="trn-carteira-avancados__block mybp-filtros-avancados__block">
                        <p class="trn-carteira-avancados__title mybp-filtros-avancados__title">Outros filtros</p>
                        <div class="row">
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                                    <label class="mybp-label" for="trn-carteira-padrao">Padrão</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFiltroPadrao"
                                            input-id="trn-carteira-padrao"
                                            instance-id="trn-carteira-padrao"
                                            :disabled="controle.carregando"
                                            :options="opcoesPadrao"
                                            placeholder-blur="Todos"
                                            empty-message="Nenhum padrão encontrado."
                                            :max-results="30"
                                            v-model="controle.dados.padrao_treinamento_id"
                                            @opening="fecharOutrosComboboxes('trn-carteira-padrao')"
                                            @select="onSelectFiltro"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                                    <label class="mybp-label" for="trn-carteira-foto">Foto 3x4</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFiltroFoto"
                                            input-id="trn-carteira-foto"
                                            instance-id="trn-carteira-foto"
                                            :disabled="controle.carregando"
                                            :options="opcoesFoto"
                                            placeholder-blur="Geral"
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            v-model="campoFotoCombo"
                                            @opening="fecharOutrosComboboxes('trn-carteira-foto')"
                                            @select="onSelectFiltro"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                                    <label class="mybp-label" for="trn-carteira-cracha">Crachá</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFiltroCracha"
                                            input-id="trn-carteira-cracha"
                                            instance-id="trn-carteira-cracha"
                                            :disabled="controle.carregando"
                                            :options="opcoesSimNaoGeral"
                                            placeholder-blur="Geral"
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            v-model="controle.dados.campoCracha"
                                            @opening="fecharOutrosComboboxes('trn-carteira-cracha')"
                                            @select="onSelectFiltro"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                                    <label class="mybp-label" for="trn-carteira-pcd">PCD</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFiltroPcd"
                                            input-id="trn-carteira-pcd"
                                            instance-id="trn-carteira-pcd"
                                            :disabled="controle.carregando"
                                            :options="opcoesFoto"
                                            placeholder-blur="Geral"
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            v-model="campoPcdCombo"
                                            @opening="fecharOutrosComboboxes('trn-carteira-pcd')"
                                            @select="onSelectFiltro"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                                    <label class="mybp-label" for="trn-carteira-uf">UF</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFiltroUf"
                                            input-id="trn-carteira-uf"
                                            instance-id="trn-carteira-uf"
                                            :disabled="controle.carregando"
                                            :options="opcoesUf"
                                            placeholder-blur="Todos"
                                            empty-message="Nenhum estado encontrado."
                                            :max-results="30"
                                            v-model="controle.dados.campoUf"
                                            @opening="fecharOutrosComboboxes('trn-carteira-uf')"
                                            @select="onSelectFiltro"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="form-group trn-carteira-filtro-campo mybp-filtro-campo">
                                    <label class="mybp-label" for="trn-carteira-pages">Por página</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFiltroPages"
                                            input-id="trn-carteira-pages"
                                            instance-id="trn-carteira-pages"
                                            :disabled="controle.carregando"
                                            :options="opcoesPages"
                                            placeholder-blur="50"
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            v-model="campoPagesCombo"
                                            @opening="fecharOutrosComboboxes('trn-carteira-pages')"
                                            @select="onSelectFiltro"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </template>

    <template #acoes>
        <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
            <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
            Buscar
        </button>
        <button
            type="button"
            class="btn btn-sm mybp-btn-mais-filtros"
            :class="filtrosAvancadosAbertos ? 'btn-primary is-open' : 'btn-outline-primary'"
            :disabled="controle.carregando"
            :aria-expanded="filtrosAvancadosAbertos ? 'true' : 'false'"
            @click="filtrosAvancadosAbertos = !filtrosAvancadosAbertos"
        >
            <i class="fa" :class="filtrosAvancadosAbertos ? 'fa-chevron-up' : 'fa-sliders-h'"></i>
            {{ filtrosAvancadosAbertos ? 'Menos filtros' : 'Mais filtros' }}
            <span class="badge badge-light ml-1" v-if="totalFiltrosAtivosAvancados">{{ totalFiltrosAtivosAvancados }}</span>
        </button>
        <div class="dropdown dropdown-carteira-etiquetas" :class="{ show: isDropdownOpen('carteira') }">
            <button
                class="btn btn-sm btn-primary dropdown-toggle"
                type="button"
                id="dropdownCarteira"
                aria-haspopup="true"
                :aria-expanded="isDropdownOpen('carteira') ? 'true' : 'false'"
                :disabled="!selecionados.length || preloadCarteira"
                @click.prevent.stop="toggleDropdown('carteira')"
            >
                <i :class="preloadCarteira ? 'fa fa-sync fa-spin' : 'fa fa-id-card'" aria-hidden="true"></i>
                {{ preloadCarteira ? 'Gerando…' : 'Gerar carteira' }}
                <span class="badge badge-light ml-1" v-if="selecionados.length && !preloadCarteira">{{ selecionados.length }}</span>
            </button>
            <div
                class="dropdown-menu"
                :class="{ show: isDropdownOpen('carteira') }"
                aria-labelledby="dropdownCarteira"
                @click.stop="fecharDropdown"
            >
                <button
                    type="button"
                    class="dropdown-item"
                    :disabled="!selecionados.length || preloadCarteira"
                    @click.prevent="gerarCarteiras('treinamento')"
                >
                    <i class="fas fa-graduation-cap mr-2"></i>Treinamento
                </button>
                <button
                    type="button"
                    class="dropdown-item"
                    :disabled="!selecionados.length || preloadCarteira"
                    @click.prevent="gerarCarteiras('bloqueio')"
                >
                    <i class="fas fa-ban mr-2"></i>Bloqueio
                </button>
                <button
                    type="button"
                    class="dropdown-item"
                    :disabled="!selecionados.length || preloadCarteira"
                    @click.prevent="gerarCarteiras('treinamento_bloqueio')"
                >
                    <i class="fas fa-list mr-2"></i>Treinamento/Bloqueio
                </button>
            </div>
        </div>
        <button
            type="button"
            class="btn btn-sm btn-success"
            :disabled="controle.carregando || preloadExportacao || lista.length === 0"
            @click.prevent="exportaExcel()"
        >
            <i :class="preloadExportacao ? 'fa fa-sync fa-spin' : 'fas fa-file-excel'"></i>
            Exportar Excel
            <span class="badge badge-light ml-1" v-if="selecionados.length > 0">{{ selecionados.length }}</span>
        </button>
        <button
            class="btn btn-sm btn-primary"
            v-if="false"
            :disabled="!selecionadosMassa.length"
            @click.pre.prevent="abrirFormMassa()"
        >
            <i class="fa fa-plus"></i>
            Atualizar em massa
            <span class="badge badge-light ml-1">{{ selecionadosMassa.length }}</span>
        </button>
        <button
            class="btn btn-sm btn-outline-primary"
            v-if="false"
            :disabled="!selecionadosMassa.length"
            @click="selecionadosMassa = []"
        >
            <i class="fa fa-times"></i>
            Limpar seleção em massa
        </button>
    </template>
</FiltroListagem>

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
                        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                            <div class="custom-control custom-checkbox mb-0">
                                <input type="checkbox" class="custom-control-input" id="checkAllMain"
                                    :style="!emTreinamentos.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                                    :disabled="!emTreinamentos.length" :checked="tudoMarcado"
                                    @click="selecionaTodos">
                                <label class="custom-control-label" for="checkAllMain">Selecionar todos</label>
                            </div>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary trn-carteira-btn mybp-btn-acao-compact"
                                v-if="selecionados.length"
                                @click="selecionados = []"
                            >
                                <i class="fa fa-times"></i>
                                Limpar seleção
                                <span class="badge badge-light ml-1">{{ selecionados.length }}</span>
                            </button>
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
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary trn-carteira-btn mybp-btn-acao-compact"
                            content="Mostrar e Ocultar Treinamentos"
                            v-tippy
                            @click.prevent="abrirModal(refsModal.MODAL_FILTRO_COLUNAS)"
                        >
                            <i class="fa fa-filter" aria-hidden="true"></i>
                            Filtrar treinamentos
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

                            <div class="col-auto pl-0">
                                <div class="colab-foto-3x4-wrap">
                                    <img
                                        v-if="item.curriculo && item.curriculo.foto_3x4_thumb"
                                        :src="item.curriculo.foto_3x4_thumb"
                                        alt=""
                                        width="40"
                                        height="53"
                                        loading="lazy"
                                        decoding="async"
                                        class="colab-foto-3x4"
                                        :class="{ 'is-ready': item._foto3x4Ready }"
                                        @load="onFoto3x4ThumbLoad(item)"
                                        @error="onFoto3x4ThumbError(item)"
                                    >
                                    <div
                                        v-if="item.curriculo && item.curriculo.foto_3x4_thumb && !item._foto3x4Ready"
                                        class="colab-foto-3x4 colab-foto-3x4--loading"
                                        aria-hidden="true"
                                    >
                                        <i class="fa fa-spinner fa-spin"></i>
                                    </div>
                                    <div
                                        v-else-if="!(item.curriculo && item.curriculo.foto_3x4_thumb)"
                                        class="colab-foto-3x4 colab-foto-3x4--vazia"
                                        aria-hidden="true"
                                    >
                                        <i class="fa fa-user"></i>
                                    </div>
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
                            <div
                                v-if="AUTENTICADO.temFilial"
                                class="col-12 col-md-4"
                            >
                                <div class="meta-item">
                                    <div class="meta-icon">
                                        <i class="fa fa-building"></i>
                                    </div>
                                    <div class="meta-content">
                                        <div class="meta-label">Lotação</div>
                                        <div class="meta-value">
                                            <span v-if="item.admissao && (item.admissao.emp_nome_fantasia || item.admissao.emp_cnpj)">
                                                {{ item.admissao.emp_nome_fantasia || '—' }}<template v-if="item.admissao.emp_cnpj"> - {{ item.admissao.emp_cnpj }}</template>
                                            </span>
                                            <span v-else>---</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="col-12"
                                :class="AUTENTICADO.temFilial ? 'col-md-4' : 'col-md-6'"
                            >
                                <div class="meta-item">
                                    <div class="meta-icon">
                                        <i class="fa fa-sitemap"></i>
                                    </div>
                                    <div class="meta-content">
                                        <div class="meta-label">Centro de custo</div>
                                        <div class="meta-value">
                                            <span v-if="item.admissao && item.admissao.emp_centro_custo">
                                                {{item.admissao.emp_centro_custo}}
                                            </span>
                                            <span v-else>---</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="col-12"
                                :class="AUTENTICADO.temFilial ? 'col-md-4' : 'col-md-6'"
                            >
                                <div class="meta-item">
                                    <div class="meta-icon">
                                        <i class="fa fa-graduation-cap"></i>
                                    </div>
                                    <div class="meta-content">
                                        <div class="meta-label">Padrão de treinamento</div>
                                        <div class="meta-value">
                                            <template v-if="nomePadraoAdmissao(item)">
                                                <div>{{ nomePadraoAdmissao(item) }}</div>
                                                <small class="d-block text-muted">
                                                    Carteira: <strong>{{ simNaoPadrao(item.admissao && item.admissao.padrao_tem_carteira) }}</strong>
                                                    · Etiqueta: <strong>{{ simNaoPadrao(item.admissao && item.admissao.padrao_tem_etiqueta) }}</strong>
                                                </small>
                                            </template>
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
                                        <td>
                                            {{ v.label }}
                                            <small v-if="v.nao_vinculado_ao_cargo" class="text-danger d-block">
                                                (treinamento sem vínculo do cargo)
                                            </small>
                                            <small v-if="isDeOutroPadrao(v)" class="text-warning d-block font-weight-bold">
                                                {{ textoOutroPadrao(v) }}
                                            </small>
                                        </td>
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

<!-- Form nativo permanente: target=_blank + POST com selecionados[] (evita form dinâmico virar GET) -->
<form
    ref="formGerarCarteira"
    method="post"
    :action="urlGerarCarteira"
    target="_blank"
    accept-charset="UTF-8"
    style="display: none"
>
    <input type="hidden" name="_token" :value="csrfToken">
    <input type="hidden" name="tipo" :value="carteiraTipoGeracao">
    <input
        v-for="id in selecionadosParaCarteira"
        :key="`sel-cart-${id}`"
        type="hidden"
        name="selecionados[]"
        :value="id"
    >
</form>
</div>
</template>

<script>
import { defineComponent } from 'vue'
import { REFS_MODAL, API_PATHS } from './constants'
import datepicker from '../DateInputHtml5.vue'
import ExportacaoMixin from '../../mixins/Exportacoes'
import Upload from '../Upload.vue'
import DateRangeFilter from '../DateRangeFilter.vue'
import Modal from '../Modal.vue'
import ModalAuditoriaTermoResponsabilidade from '../ModalAuditoriaTermoResponsabilidade.vue'
import ControlePaginacao from '../ControlePaginacao.vue'
import ComboboxAutoComplete from '../ComboboxAutoComplete.vue'
import FiltroListagem from '../ui/FiltroListagem.vue'

const UF_OPTIONS = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG',
    'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
]

export default defineComponent({
    name: 'TreinamentosCarteiraEtiquetas',
    mixins: [ExportacaoMixin],
    components: {
        datepicker,
        Upload,
        DateRangeFilter,
        Modal,
        ModalAuditoriaTermoResponsabilidade,
        ControlePaginacao,
        ComboboxAutoComplete,
        FiltroListagem,
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
            preloadCarteira: false,
            comboTreinamento: '',

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
            carteiraTipoGeracao: 'treinamento',

            selecionadosMassa: [],
            selecionaTudoMassa: false,

            openPanels: [],
            expandAll: false,

            trainingSearchQuery: '',
            trainingStatusFilter: 'all',
            trainingVinculoFilter: 'all',

            form: {
                dadosFuncionario: {
                    feedback_id: '',
                    nome: '',
                    idade: '',
                    cpf: '',
                    cargo: '',
                    email: '',
                    telefone: '',
                    telefone_tipo: '',
                    nome_vaga: '',
                    status_admissao: '',
                    tipo_admissao: '',
                    numero_cracha: '',
                    situacao_colaborador: '',
                    emp_cnpj: '',
                    emp_nome_fantasia: '',
                    emp_centro_custo: '',
                    emp_tipo: '',
                    padrao_treinamento_nome: '',
                    padrao_tem_carteira: null,
                    padrao_tem_etiqueta: null,
                    cliente_nome: '',
                    foto_3x4_thumb: null
                },
                _modalFoto3x4Ready: false,

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
                listaVencimentosNaoVinculadosCargo: [],
                alertaSemCargoVinculo: false,

                nr_trinta_tres: true,
                nr_trinta_cinco: true,
                padrao_treinamento_id: null,
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
            filtrosAvancadosAbertos: false,
            initialPage: null,

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
            padroesTreinamento: [],

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
                    campoCentroCusto: '',
                    padrao_treinamento_id: ''
                }
            },

            url_anexo: `${URL_ADMIN}/${API_PATHS.uploadAnexos}`,
            anexoUploadAndamento: false,

            privilegio_gestao_rh: false,
            treinamento_permitir_desmarcar_realizado: false,
            treinamento_fat_obrigatorio: false,

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
        this.urlParamGet()
        this.listaVagas()
        this.listaAreasGeral()
        this.carregarPadroesTreinamento()
        if (this.initialPage != null && this.initialPage >= 1) {
            this.$nextTick(() => {
                if (this.$refs.componente) {
                    this.$refs.componente.atual = this.initialPage
                    this.$refs.componente.buscar && this.$refs.componente.buscar()
                }
            })
        } else {
            this.atualizar()
        }

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
        if (this._syncUrlTimer) clearTimeout(this._syncUrlTimer)
    },
    computed: {
        modalPerfilLinhasTreinamento() {
            const d = this.form.dadosFuncionario || {}
            const lotacao = this.lotacaoModal(d)
            const linha1 = [
                { label: 'CPF', valor: this.textoCampoModal(d.cpf) },
                { label: 'Vaga', valor: this.textoCampoModal(d.nome_vaga || d.cargo) },
                { label: 'Tipo admissão', valor: this.textoCampoModal(d.tipo_admissao) }
            ]
            const linha2 = [
                { label: 'Lotação', valor: lotacao },
                { label: 'Centro de custo', valor: this.textoCampoModal(d.emp_centro_custo) },
                { label: 'Crachá', valor: this.textoCampoModal(d.numero_cracha) }
            ]
            const linha3 = [
                { label: 'E-mail', valor: this.textoCampoModal(d.email) },
                { label: 'Telefone', valor: this.textoCampoModal(d.telefone) }
            ]
            const linha4 = [{ label: 'Padrão', valor: this.padraoModal(d) }]
            if (Number(this.cliente_id) === 1) {
                linha4.push({ label: 'Cliente', valor: this.textoCampoModal(d.cliente_nome) })
            }
            return [linha1, linha2, linha3, linha4]
        },
        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        /** Hoje em dd/mm/aaaa para min/max do date HTML5 */
        dataHojeBr() {
            const d = new Date()
            const dd = String(d.getDate()).padStart(2, '0')
            const mm = String(d.getMonth() + 1).padStart(2, '0')
            return `${dd}/${mm}/${d.getFullYear()}`
        },
        urlGerarCarteira() {
            return `${URL_ADMIN}/${API_PATHS.carteiras}`
        },
        selecionadosParaCarteira() {
            return (this.selecionados || [])
                .map((id) => Number(id))
                .filter((id) => Number.isFinite(id) && id > 0)
        },
        isColunaTreinamentoSelecionada() {
            return (treinamento) => {
                if (!treinamento) {
                    return false
                }
                // Sem lista de colunas ainda: exibe (evita sumir treinamentos de outro padrão)
                if (!this.listaColunasTreinamentos || !this.listaColunasTreinamentos.length) {
                    return true
                }
                const col = this.listaColunasTreinamentos.find(
                    (c) => Number(c.id) === Number(treinamento.id)
                )
                // Fora do catálogo de colunas (ex.: outro padrão / inativo): sempre exibir
                if (!col) {
                    return true
                }
                return !!col.checked
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
                Assumo que, ao clicar em "Confirmar e desmarcar" no sistema MyBPIN, estou ciente e concordo com as disposições deste termo de responsabilidade.
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
            if (!this.lista_ccs) return []
            if (this.controle.dados.campoCnpj !== '' && this.AUTENTICADO.temFilial) {
                return this.lista_ccs.centros_custos[this.controle.dados.campoCnpj] || []
            }
            if (!this.AUTENTICADO.temFilial) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                return keys.length ? this.lista_ccs.centros_custos[keys[0]] || [] : []
            }
            const all = []
            Object.values(this.lista_ccs.centros_custos || {}).forEach((lista) => {
                ;(lista || []).forEach((item) => all.push(item))
            })
            return all
        },
        opcoesTreinados() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: 'S', label: 'Sim' },
                { value: 'N', label: 'Não' }
            ]
        },
        opcoesSimNaoGeral() {
            return [
                { value: '', label: 'Geral' },
                { value: 'S', label: 'Sim' },
                { value: 'N', label: 'Não' }
            ]
        },
        opcoesSituacaoColaborador() {
            return [
                { value: 'ativos', label: 'Ativos' },
                { value: 'admitidos', label: 'Admitidos' },
                { value: 'nao_admitidos', label: 'Não admitidos' },
                { value: 'demitidos', label: 'Demitidos' }
            ]
        },
        opcoesFoto() {
            return [
                { value: '', label: 'Geral' },
                { value: 'true', label: 'Sim' },
                { value: 'false', label: 'Não' }
            ]
        },
        opcoesPadrao() {
            const opts = [{ value: '', label: 'Todos os padrões' }]
            ;(this.padroesTreinamento || []).forEach((p) => {
                opts.push({ value: String(p.id), label: p.nome })
            })
            return opts
        },
        opcoesCnpj() {
            const opts = [{ value: '', label: 'Todos os CNPJs' }]
            if (!this.lista_ccs || !this.lista_ccs.cnpjs) return opts
            Object.keys(this.lista_ccs.cnpjs).forEach((key) => {
                const item = this.lista_ccs.cnpjs[key]
                opts.push({
                    value: key,
                    label: `${item.nome_fantasia} - ${item.cnpj}`,
                    meta: item.cnpj
                })
            })
            return opts
        },
        opcoesCentroCusto() {
            const opts = [
                { value: '', label: 'Todos os centros' },
                { value: '--naoinformado--', label: 'Não informado' }
            ]
            ;(this.filtroListaCentroCustoCnpj || []).forEach((item) => {
                const value = item.matriz ? item.id : item.filial_id
                opts.push({
                    value,
                    label: item.label,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        opcoesUf() {
            return [{ value: '', label: 'Todos os estados' }, ...UF_OPTIONS.map((uf) => ({ value: uf, label: uf }))]
        },
        opcoesPages() {
            return [
                { value: '20', label: '20' },
                { value: '50', label: '50' },
                { value: '100', label: '100' },
                { value: '500', label: '500' }
            ]
        },
        opcoesTreinamentoDisponiveis() {
            const selecionados = new Set(this.controle.dados.treinamentos_selecionados || [])
            const opts = [{ value: 'todos', label: 'Adicionar todos' }]
            ;(this.listaTodosTreinamentos || []).forEach((item) => {
                if (!selecionados.has(item.label)) {
                    opts.push({ value: item.label, label: item.label })
                }
            })
            return opts
        },
        campoSituacaoCombo: {
            get() {
                const d = this.controle.dados
                if (d.campoDemitido === true) return 'demitidos'
                if (d.campoAdmitido === 'S') return 'admitidos'
                if (d.campoAdmitido === 'N') return 'nao_admitidos'
                return 'ativos'
            },
            set(valor) {
                const d = this.controle.dados
                switch (valor) {
                    case 'demitidos':
                        d.campoDemitido = true
                        d.campoAdmitido = ''
                        break
                    case 'admitidos':
                        d.campoDemitido = false
                        d.campoAdmitido = 'S'
                        break
                    case 'nao_admitidos':
                        d.campoDemitido = false
                        d.campoAdmitido = 'N'
                        break
                    default:
                        d.campoDemitido = false
                        d.campoAdmitido = ''
                        break
                }
            }
        },
        campoFotoCombo: {
            get() {
                const v = this.controle.dados.campoFoto
                if (v === '' || v === null || v === undefined) return ''
                return v === true || v === 'true' ? 'true' : 'false'
            },
            set(valor) {
                if (valor === '') this.controle.dados.campoFoto = ''
                else this.controle.dados.campoFoto = valor === 'true'
            }
        },
        campoPcdCombo: {
            get() {
                const v = this.controle.dados.campoPcd
                if (v === '' || v === null || v === undefined) return ''
                return v === true || v === 'true' ? 'true' : 'false'
            },
            set(valor) {
                if (valor === '') this.controle.dados.campoPcd = ''
                else this.controle.dados.campoPcd = valor === 'true'
            }
        },
        campoPagesCombo: {
            get() {
                return String(this.controle.dados.pages || 50)
            },
            set(valor) {
                this.controle.dados.pages = parseInt(valor, 10) || 50
            }
        },

        treinamentosFiltrados() {
            if (!this.form.listaVencimentos) return []

            return this.form.listaVencimentos
                .filter((training) => {
                    const labelTreinamento = (training.label || '').toLowerCase()
                    const matchesSearch = this.trainingSearchQuery === '' || labelTreinamento.includes(this.trainingSearchQuery.toLowerCase())
                    let matchesVinculo = true
                    if (this.trainingVinculoFilter === 'com_vinculo') {
                        matchesVinculo = !training.nao_vinculado_ao_cargo
                    } else if (this.trainingVinculoFilter === 'sem_vinculo') {
                        matchesVinculo = !!training.nao_vinculado_ao_cargo
                    }

                let matchesStatus = true
                if (this.trainingStatusFilter !== 'all') {
                    const status = this.getTreinamentoStatus(training)
                    matchesStatus = status === this.trainingStatusFilter
                }

                    return matchesSearch && matchesStatus && matchesVinculo
                })
                .sort((a, b) => {
                    const aSemVinculo = !!a.nao_vinculado_ao_cargo
                    const bSemVinculo = !!b.nao_vinculado_ao_cargo
                    if (aSemVinculo === bSemVinculo) return 0
                    return aSemVinculo ? 1 : -1
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
        },
        totalFiltrosAtivos() {
            const d = this.controle.dados
            const lista = [
                d.campoBusca,
                d.campoCPF,
                d.campoVaga,
                d.campoUf,
                d.campoArea,
                d.campoCargo,
                d.padrao_treinamento_id,
                d.campo_treinados,
                d.campoNr_trinta_tres,
                d.campoNr_trinta_cinco,
                d.campoNr_ebtv,
                d.campoCracha,
                d.campoFoto,
                d.campoPcd,
                d.campoCnpj,
                d.campoCentroCusto
            ]
            let total = lista.filter((v) => v !== '' && v !== null && v !== undefined).length
            if (d.campoDemitido === true || d.campoAdmitido === 'S' || d.campoAdmitido === 'N') total++
            if (d.campoVencimento && d.dataInicioVencimento && d.dataFimVencimento) total++
            if (d.campoPeriodoTreinado && d.dataInicioPeriodoTreinado && d.dataFimPeriodoTreinado) total++
            if (Array.isArray(d.treinamentos_selecionados) && d.treinamentos_selecionados.length > 0) total++
            return total
        },
        totalFiltrosAtivosAvancados() {
            const d = this.controle.dados
            const lista = [
                d.padrao_treinamento_id,
                d.campoUf,
                d.campoArea,
                d.campoCracha,
                d.campoFoto,
                d.campoPcd
            ]
            let total = lista.filter((v) => v !== '' && v !== null && v !== undefined).length
            if (String(d.pages) !== '50') total++
            return total
        },
        campoBuscaUnificada() {
            const d = this.controle.dados
            if (d.campoCPF) return d.campoCPF
            return d.campoBusca || ''
        },
        buscaUnificadaEhCpf() {
            return !!this.controle.dados.campoCPF
        },
        activeFilterChips() {
            const d = this.controle.dados
            const padraoSelecionado = this.padroesTreinamento.find((p) => String(p.id) === String(d.padrao_treinamento_id))
            const chips = []
            if (d.campoCPF) chips.push({ key: 'campoCPF', label: `CPF: ${d.campoCPF}`, primario: true })
            else if (d.campoBusca) chips.push({ key: 'campoBusca', label: `Busca: ${d.campoBusca}`, primario: true })
            if (d.campo_treinados) chips.push({ key: 'campo_treinados', label: `Treinados: ${d.campo_treinados === 'S' ? 'Sim' : 'Não'}`, primario: true })
            if (d.campoDemitido === true) {
                chips.push({ key: 'campoSituacao', label: 'Situação: Demitidos', primario: true })
            } else if (d.campoAdmitido === 'S') {
                chips.push({ key: 'campoSituacao', label: 'Situação: Admitidos', primario: true })
            } else if (d.campoAdmitido === 'N') {
                chips.push({ key: 'campoSituacao', label: 'Situação: Não admitidos', primario: true })
            }
            if (d.campoCnpj) chips.push({ key: 'campoCnpj', label: 'CNPJ selecionado', primario: true })
            if (d.campoCargo) chips.push({ key: 'campoCargo', label: `Cargo: ${d.campoCargo}`, primario: true })
            if (d.campoVaga) chips.push({ key: 'campoVaga', label: 'Vaga selecionada', primario: true })
            if (d.campoCentroCusto) chips.push({ key: 'campoCentroCusto', label: 'Centro de custo selecionado', primario: true })
            if (d.campoVencimento && d.dataInicioVencimento && d.dataFimVencimento) {
                chips.push({
                    key: 'campoVencimento',
                    label: `Vencimento: ${this.formatarDataFiltro(d.dataInicioVencimento)} até ${this.formatarDataFiltro(d.dataFimVencimento)}`,
                    primario: true
                })
            }
            if (d.campoPeriodoTreinado && d.dataInicioPeriodoTreinado && d.dataFimPeriodoTreinado) {
                chips.push({
                    key: 'campoPeriodoTreinado',
                    label: `Período treinado: ${this.formatarDataFiltro(d.dataInicioPeriodoTreinado)} até ${this.formatarDataFiltro(d.dataFimPeriodoTreinado)}`,
                    primario: true
                })
            }
            if (Array.isArray(d.treinamentos_selecionados) && d.treinamentos_selecionados.length > 0) {
                chips.push({ key: 'treinamentos_selecionados', label: `Treinamentos: ${d.treinamentos_selecionados.length}`, primario: true })
            }
            if (d.padrao_treinamento_id) {
                chips.push({
                    key: 'padrao_treinamento_id',
                    label: `Padrão: ${padraoSelecionado ? padraoSelecionado.nome : d.padrao_treinamento_id}`
                })
            }
            if (d.campoUf) chips.push({ key: 'campoUf', label: `UF: ${d.campoUf}` })
            if (d.campoFoto !== '') chips.push({ key: 'campoFoto', label: `Foto 3x4: ${d.campoFoto ? 'Sim' : 'Não'}` })
            if (d.campoPcd !== '') chips.push({ key: 'campoPcd', label: `PCD: ${d.campoPcd ? 'Sim' : 'Não'}` })
            if (d.campoCracha) chips.push({ key: 'campoCracha', label: `Crachá: ${d.campoCracha === 'S' ? 'Sim' : 'Não'}` })
            if (String(d.pages) !== '50') chips.push({ key: 'pages', label: `Por página: ${d.pages}` })
            return chips
        },
        advancedFilterChips() {
            return this.activeFilterChips.filter((chip) => !chip.primario)
        },
    },
    watch: {
        'controle.dados': {
            handler() {
                if (this._syncUrlTimer) clearTimeout(this._syncUrlTimer)
                this._syncUrlTimer = setTimeout(() => this.syncUrlFiltros(), 350)
            },
            deep: true
        }
    },
    methods: {
        onFoto3x4ThumbLoad(item) {
            if (item) {
                item._foto3x4Ready = true
            }
        },
        onFoto3x4ThumbError(item) {
            if (item && item.curriculo) {
                item.curriculo.foto_3x4_thumb = null
                item._foto3x4Ready = false
            }
        },
        onModalFoto3x4Load() {
            this.form._modalFoto3x4Ready = true
        },
        onModalFoto3x4Error() {
            if (this.form.dadosFuncionario) {
                this.form.dadosFuncionario.foto_3x4_thumb = null
            }
            this.form._modalFoto3x4Ready = false
        },
        boolFromParam(value, fallback = '') {
            if (value === null || value === '') return fallback
            if (value === 'true' || value === '1' || value === 'S') return true
            if (value === 'false' || value === '0' || value === 'N') return false
            return fallback
        },
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            const d = this.controle.dados
            if (urlParams.get('pages')) d.pages = parseInt(urlParams.get('pages'), 10) || 50
            if (urlParams.get('campoBusca')) d.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoCPF')) d.campoCPF = urlParams.get('campoCPF')
            if (urlParams.get('campoVaga')) d.campoVaga = urlParams.get('campoVaga')
            if (urlParams.get('campoUf')) d.campoUf = urlParams.get('campoUf')
            if (urlParams.get('campoArea')) d.campoArea = urlParams.get('campoArea')
            if (urlParams.get('campoCargo')) d.campoCargo = urlParams.get('campoCargo')
            if (urlParams.get('padrao_treinamento_id')) d.padrao_treinamento_id = urlParams.get('padrao_treinamento_id')
            if (urlParams.get('campo_treinados')) d.campo_treinados = urlParams.get('campo_treinados')
            if (urlParams.get('campoAdmitido')) d.campoAdmitido = urlParams.get('campoAdmitido')
            d.campoDemitido = this.boolFromParam(urlParams.get('campoDemitido'), false)
            if (d.campoDemitido === true) d.campoAdmitido = ''
            const campoFoto = this.boolFromParam(urlParams.get('campoFoto'), '')
            if (campoFoto !== '') d.campoFoto = campoFoto
            if (urlParams.get('campoCracha')) d.campoCracha = urlParams.get('campoCracha')
            const campoPcd = this.boolFromParam(urlParams.get('campoPcd'), '')
            if (campoPcd !== '') d.campoPcd = campoPcd
            if (urlParams.get('campoCnpj')) d.campoCnpj = urlParams.get('campoCnpj')
            if (urlParams.get('campoCentroCusto')) d.campoCentroCusto = urlParams.get('campoCentroCusto')
            if (urlParams.get('treinamentos')) {
                d.treinamentos_selecionados = urlParams
                    .get('treinamentos')
                    .split('|')
                    .map((item) => item.trim())
                    .filter(Boolean)
                d.campo_treinados = d.treinamentos_selecionados.length ? 'S' : d.campo_treinados
            }
            d.campoVencimento = this.boolFromParam(urlParams.get('campoVencimento'), false)
            if (urlParams.get('dataInicioVencimento')) d.dataInicioVencimento = urlParams.get('dataInicioVencimento')
            if (urlParams.get('dataFimVencimento')) d.dataFimVencimento = urlParams.get('dataFimVencimento')
            d.campoPeriodoTreinado = this.boolFromParam(urlParams.get('campoPeriodoTreinado'), false)
            if (urlParams.get('dataInicioPeriodoTreinado')) d.dataInicioPeriodoTreinado = urlParams.get('dataInicioPeriodoTreinado')
            if (urlParams.get('dataFimPeriodoTreinado')) d.dataFimPeriodoTreinado = urlParams.get('dataFimPeriodoTreinado')
            if (d.campoVencimento && d.dataInicioVencimento && d.dataFimVencimento) {
                d.vencimento = `${this.formatarDataFiltro(d.dataInicioVencimento)} até ${this.formatarDataFiltro(d.dataFimVencimento)}`
            }
            if (d.campoPeriodoTreinado && d.dataInicioPeriodoTreinado && d.dataFimPeriodoTreinado) {
                d.periodoTreinado = `${this.formatarDataFiltro(d.dataInicioPeriodoTreinado)} até ${this.formatarDataFiltro(d.dataFimPeriodoTreinado)}`
            }
            if (urlParams.get('page')) {
                const p = parseInt(urlParams.get('page'), 10)
                if (p >= 1) this.initialPage = p
            }
            if (this.totalFiltrosAtivosAvancados > 0) this.filtrosAvancadosAbertos = true
        },
        syncUrlFiltros() {
            const d = this.controle.dados
            const atual = this.$refs.componente && this.$refs.componente.atual ? this.$refs.componente.atual : 1
            const params = {}
            if (d.pages && String(d.pages) !== '50') params.pages = d.pages
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoCPF) params.campoCPF = d.campoCPF
            if (d.campoVaga) params.campoVaga = d.campoVaga
            if (d.campoUf) params.campoUf = d.campoUf
            if (d.campoArea) params.campoArea = d.campoArea
            if (d.campoCargo) params.campoCargo = d.campoCargo
            if (d.padrao_treinamento_id) params.padrao_treinamento_id = d.padrao_treinamento_id
            if (d.campo_treinados) params.campo_treinados = d.campo_treinados
            if (d.campoAdmitido) params.campoAdmitido = d.campoAdmitido
            if (d.campoDemitido === true) params.campoDemitido = 'true'
            if (d.campoCracha) params.campoCracha = d.campoCracha
            if (d.campoFoto !== '') params.campoFoto = String(d.campoFoto)
            if (d.campoPcd !== '') params.campoPcd = String(d.campoPcd)
            if (d.campoCnpj) params.campoCnpj = d.campoCnpj
            if (d.campoCentroCusto) params.campoCentroCusto = d.campoCentroCusto
            if (Array.isArray(d.treinamentos_selecionados) && d.treinamentos_selecionados.length > 0) {
                params.treinamentos = d.treinamentos_selecionados.join('|')
            }
            if (d.campoVencimento && d.dataInicioVencimento && d.dataFimVencimento) {
                params.campoVencimento = 'true'
                params.dataInicioVencimento = d.dataInicioVencimento
                params.dataFimVencimento = d.dataFimVencimento
            }
            if (d.campoPeriodoTreinado && d.dataInicioPeriodoTreinado && d.dataFimPeriodoTreinado) {
                params.campoPeriodoTreinado = 'true'
                params.dataInicioPeriodoTreinado = d.dataInicioPeriodoTreinado
                params.dataFimPeriodoTreinado = d.dataFimPeriodoTreinado
            }
            if (atual > 1) params.page = atual
            const qs = new URLSearchParams(params).toString()
            const url = qs ? `${window.location.pathname}?${qs}` : window.location.pathname
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, '', url)
            }
        },
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
        async carregarPadroesTreinamento() {
            try {
                const resPadroes = await axios.get(`${URL_ADMIN}/${API_PATHS.padroesHabilitados}`)
                this.padroesTreinamento = resPadroes.data || []
            } catch {
                this.padroesTreinamento = []
            }
        },
        nomePadraoAdmissao(item) {
            const adm = item && item.admissao
            if (!adm) return ''
            return adm.padrao_treinamento_nome
                || (adm.padrao_treinamento && adm.padrao_treinamento.nome)
                || ''
        },
        simNaoPadrao(valor) {
            if (valor === true || valor === 1 || valor === '1') return 'SIM'
            if (valor === false || valor === 0 || valor === '0') return 'NÃO'
            return '—'
        },
        textoCampoModal(valor) {
            const texto = String(valor ?? '').trim()
            return texto !== '' ? texto : 'Não informado'
        },
        nomeIdadeModal(dados) {
            const nome = this.textoCampoModal(dados?.nome)
            const idade = dados?.idade
            if (idade !== null && idade !== undefined && String(idade).trim() !== '') {
                return `${nome} — ${idade} anos`
            }
            return nome
        },
        lotacaoModal(dados) {
            const nome = (dados?.emp_nome_fantasia || '').trim()
            const cnpj = (dados?.emp_cnpj || '').trim()
            const tipo = (dados?.emp_tipo || '').trim()
            if (!nome && !cnpj) return 'Não informado'
            let texto = nome || '—'
            if (cnpj) texto += ` - ${cnpj}`
            if (tipo) texto += ` (${tipo})`
            return texto
        },
        padraoModal(dados) {
            const nome = (dados?.padrao_treinamento_nome || '').trim()
            if (!nome) return 'Não informado'
            const carteira = this.simNaoPadrao(dados?.padrao_tem_carteira)
            const etiqueta = this.simNaoPadrao(dados?.padrao_tem_etiqueta)
            return `${nome} · Carteira: ${carteira} · Etiqueta: ${etiqueta}`
        },
        chaveSituacaoColaboradorModal(dados) {
            const situacao = dados?.situacao_colaborador || 'em_processo'
            if (situacao === 'admitido') return 'aprovado'
            if (situacao === 'demitido') return 'reprovado'
            return 'aberto'
        },
        textoSituacaoColaboradorModal(dados) {
            const situacao = dados?.situacao_colaborador || 'em_processo'
            if (situacao === 'admitido') return 'Admitido'
            if (situacao === 'demitido') return 'Demitido'
            return 'Em processo'
        },
        /** Evita "Admitido" + "ADMITIDO" duplicados no header da modal. */
        mostrarStatusAdmissaoModal(dados) {
            const status = String(dados?.status_admissao || '').trim()
            if (!status) return false
            const situacao = dados?.situacao_colaborador || 'em_processo'
            if (situacao === 'admitido' || situacao === 'demitido') return false
            return true
        },
        isDeOutroPadrao(treinamento) {
            if (!treinamento) return false
            const v = treinamento.de_outro_padrao
            return v === true || v === 1 || v === '1'
        },
        textoOutroPadrao(treinamento) {
            const nome = treinamento && (treinamento.padrao_origem_nome || '').trim()
            if (nome) {
                return `Este treinamento é do padrão ${nome}`
            }
            return 'Este treinamento é de outro padrão'
        },
        async trocarPadraoTreinamento() {
            if (!this.form.feedback_id) {
                return
            }

            this.preload = true
            try {
                const response = await axios.post(`${URL_ADMIN}/${API_PATHS.vencimentosPorPadrao}`, {
                    feedback_id: this.form.feedback_id,
                    padrao_treinamento_id: this.form.padrao_treinamento_id
                })
                this.form.listaVencimentos = (response.data.listaVencimentos || []).map((t) => ({
                    ...t,
                    _fez_treinamento_ja_salvo: !!t.fez_treinamento,
                    _alterado: false,
                }))
                this.form.listaVencimentosNaoVinculadosCargo = response.data.listaVencimentosNaoVinculadosCargo || []
                this.form.alertaSemCargoVinculo = !!response.data.alertaSemCargoVinculo
                if (response.data.privilegio_gestao_rh !== undefined) this.privilegio_gestao_rh = response.data.privilegio_gestao_rh
                if (response.data.treinamento_permitir_desmarcar_realizado !== undefined) this.treinamento_permitir_desmarcar_realizado = response.data.treinamento_permitir_desmarcar_realizado
                if (response.data.treinamento_retirar_treinamento_realizado !== undefined) this.treinamento_retirar_treinamento_realizado = response.data.treinamento_retirar_treinamento_realizado
                if (response.data.treinamento_fat_obrigatorio !== undefined) this.treinamento_fat_obrigatorio = response.data.treinamento_fat_obrigatorio
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
        /** Verifica se o item tem ao menos um anexo FAT válido (existente ou novo upload). */
        itemTemAnexoFat(treinamento) {
            if (!treinamento.arquivo || !Array.isArray(treinamento.arquivo) || treinamento.arquivo.length === 0) return false
            return treinamento.arquivo.some((arq) => {
                if (!arq) return false
                if (arq.id && !arq.temporario) return true
                return !!(arq.temporario && arq.chave && !arq.falhou)
            })
        },
        /** Exibe is-invalid no bloco de anexo FAT quando obrigatório, sem anexo e falhou na validação ao salvar. */
        anexoFatInvalido(treinamento) {
            if (!this.treinamento_fat_obrigatorio || !treinamento.fez_treinamento) return false
            return !this.itemTemAnexoFat(treinamento) && (treinamento._anexoTouched === true)
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
                    t._alterado = false
                    this.showModalMotivoDesmarcar = false
                    this.treinamentoMotivoDesmarcar = null
                    this.desmarcarPreload = false
                    if (this.$refs.modalAuditoriaDesmarcar && typeof this.$refs.modalAuditoriaDesmarcar.fechar === 'function') {
                        this.$refs.modalAuditoriaDesmarcar.fechar()
                    }
                    mostraSucesso('', response.data.msg || 'Treinamento desmarcado com sucesso.')
                    await this.atualizar()
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
        onSelectCnpj() {
            this.changeCnpj()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        onSelectTreinados(opt) {
            const valor = opt && opt.value != null ? String(opt.value) : ''
            this.selecionaTreinados(valor)
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'trn-carteira-treinados': 'comboFiltroTreinados',
                'trn-carteira-padrao': 'comboFiltroPadrao',
                'trn-carteira-situacao': 'comboFiltroSituacao',
                'trn-carteira-foto': 'comboFiltroFoto',
                'trn-carteira-pcd': 'comboFiltroPcd',
                'trn-carteira-cracha': 'comboFiltroCracha',
                'trn-carteira-cnpj': 'comboFiltroCnpj',
                'trn-carteira-cc': 'comboFiltroCc',
                'trn-carteira-uf': 'comboFiltroUf',
                'trn-carteira-pages': 'comboFiltroPages',
                'trn-carteira-treino': 'comboFiltroTreinamento'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.close === 'function') ref.close()
            })
        },
        formatarCpfDigitos(digitos) {
            const d = String(digitos || '').replace(/\D/g, '').slice(0, 11)
            if (d.length <= 3) return d
            if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`
            if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`
            return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`
        },
        parecePadraoCpf(valor) {
            const raw = String(valor || '').trim()
            if (!raw) return false
            if (!/^[\d.\-\s]+$/.test(raw)) return false
            const digitos = raw.replace(/\D/g, '')
            return digitos.length > 0 && digitos.length <= 11
        },
        onInputBuscaUnificada(event) {
            const valor = event && event.target ? event.target.value : ''
            const d = this.controle.dados
            if (!valor) {
                d.campoBusca = ''
                d.campoCPF = ''
                return
            }
            if (this.parecePadraoCpf(valor)) {
                const mascarado = this.formatarCpfDigitos(valor)
                d.campoCPF = mascarado
                d.campoBusca = ''
                if (event.target && event.target.value !== mascarado) {
                    event.target.value = mascarado
                }
                return
            }
            d.campoBusca = valor
            d.campoCPF = ''
        },
        limparBuscaUnificada() {
            this.controle.dados.campoBusca = ''
            this.controle.dados.campoCPF = ''
        },
        selecionaTreinados(valor) {
            if (valor !== 'S') {
                this.controle.dados.treinamentos_selecionados = []
            }
            this.atualizar()
        },
        onSelectTreinamentoEspecifico(opt) {
            const valor = opt && opt.value != null ? String(opt.value) : ''
            this.addTreinamento(valor)
            this.$nextTick(() => {
                this.comboTreinamento = ''
            })
        },
        limparTreinamentosSelecionados() {
            this.addTreinamento('rm')
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

        mensagemErroGeracaoDocumento() {
            return 'Houve um erro ao gerar. Tente novamente. Se o problema persistir, entre em contato com o suporte técnico e abra um chamado.'
        },
        htmlErroGeracaoDocumento() {
            const msg = this.mensagemErroGeracaoDocumento()
            return `<!doctype html><html><head><meta charset="utf-8"><title>Erro na geração</title></head>
<body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#fff5f5;color:#333">
<div style="text-align:center;max-width:420px;padding:24px">
<p style="font-size:18px;margin:0 0 12px;color:#c0392b">Não foi possível gerar</p>
<p style="font-size:14px;line-height:1.5;margin:0;color:#555">${msg}</p>
</div></body></html>`
        },
        exibirErroGeracaoDocumento(novaAba) {
            const msg = this.mensagemErroGeracaoDocumento()
            if (typeof toastr !== 'undefined') toastr.error(msg)
            else mostraErro('', msg)

            const blobUrl = URL.createObjectURL(new Blob([this.htmlErroGeracaoDocumento()], { type: 'text/html;charset=utf-8' }))
            if (novaAba && !novaAba.closed) {
                novaAba.location.href = blobUrl
            } else {
                window.open(blobUrl, '_blank')
            }
            setTimeout(() => URL.revokeObjectURL(blobUrl), 120000)
        },
        /**
         * Blob URL: o HTML vem do POST; viramos um arquivo temporário no browser
         * (blob:) e abrimos nessa URL — evita form.submit virar GET e document.write preso.
         * @param {string} tipo - 'treinamento' | 'bloqueio' | 'treinamento_bloqueio'
         */
        async gerarCarteiras(tipo) {
            if (this.preloadCarteira) return
            if (!this.selecionadosParaCarteira.length) {
                if (typeof toastr !== 'undefined') toastr.warning('Selecione ao menos um colaborador com treinamento.')
                else mostraErro('', 'Selecione ao menos um colaborador com treinamento.')
                return
            }

            this.carteiraTipoGeracao = tipo
            this.preloadCarteira = true
            this.fecharDropdown()

            const token = this.csrfToken
            const body = new URLSearchParams()
            if (token) body.append('_token', token)
            body.append('tipo', tipo)
            this.selecionadosParaCarteira.forEach((id) => body.append('selecionados[]', String(id)))

            const htmlAguarde = `<!doctype html><html><head><meta charset="utf-8"><title>Gerando…</title>
<style>
@keyframes spin{to{transform:rotate(360deg)}}
.spinner{width:36px;height:36px;border:3px solid #dee2e6;border-top-color:#0d6efd;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 14px}
</style></head>
<body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f8f9fa;color:#333">
<div style="text-align:center">
<div class="spinner" role="status" aria-label="Carregando"></div>
<p style="font-size:18px;margin:0 0 8px">Gerando…</p>
<p style="font-size:13px;color:#666;margin:0">Aguarde, isso pode levar alguns segundos.</p>
</div></body></html>`

            // Mesmo gesto do clique → evita bloqueio de popup; já mostra aviso na aba
            let novaAba = window.open('about:blank', '_blank')
            let urlAguarde = null
            try {
                urlAguarde = URL.createObjectURL(new Blob([htmlAguarde], { type: 'text/html;charset=utf-8' }))
                if (novaAba && !novaAba.closed) {
                    novaAba.location.href = urlAguarde
                } else {
                    novaAba = window.open(urlAguarde, '_blank')
                }
            } catch (_) { /* ignore */ }

            try {
                const response = await fetch(this.urlGerarCarteira, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        Accept: 'text/html,application/xhtml+xml',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                    },
                    body,
                    credentials: 'same-origin',
                })
                const html = await response.text()

                if (!response.ok) {
                    this.exibirErroGeracaoDocumento(novaAba)
                    return
                }

                const blobUrl = URL.createObjectURL(new Blob([html], { type: 'text/html;charset=utf-8' }))
                if (novaAba && !novaAba.closed) {
                    novaAba.location.href = blobUrl
                } else {
                    window.open(blobUrl, '_blank')
                }
                setTimeout(() => URL.revokeObjectURL(blobUrl), 120000)
            } catch (err) {
                console.error('gerarCarteiras:', err)
                this.exibirErroGeracaoDocumento(novaAba)
            } finally {
                this.preloadCarteira = false
                if (urlAguarde) {
                    setTimeout(() => URL.revokeObjectURL(urlAguarde), 5000)
                }
            }
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
            this.editando = false
            this.form = _.cloneDeep(this.formDefault)
            this.form.curriculo_id = curriculo_id
            this.tituloJanela = 'Treinamentos do colaborador'

            const itemLista = (this.lista || []).find((i) => Number(i.id) === Number(curriculo_id))
            if (itemLista && itemLista.curriculo) {
                this.form.dadosFuncionario.nome = itemLista.curriculo.nome || ''
                this.form.dadosFuncionario.cpf = itemLista.curriculo.cpf || ''
                this.form.dadosFuncionario.foto_3x4_thumb = itemLista.curriculo.foto_3x4_thumb || null
                this.form.dadosFuncionario.feedback_id = itemLista.id || ''
                this.form._modalFoto3x4Ready = !!(itemLista.curriculo.foto_3x4_thumb && itemLista._foto3x4Ready)
                if (itemLista.admissao) {
                    this.form.dadosFuncionario.tipo_admissao = itemLista.admissao.tipo_admissao || ''
                    this.form.dadosFuncionario.status_admissao = itemLista.admissao.status || ''
                    this.form.dadosFuncionario.emp_nome_fantasia = itemLista.admissao.emp_nome_fantasia || ''
                    this.form.dadosFuncionario.emp_cnpj = itemLista.admissao.emp_cnpj || ''
                    this.form.dadosFuncionario.emp_centro_custo = itemLista.admissao.emp_centro_custo || ''
                    this.form.dadosFuncionario.emp_tipo = itemLista.admissao.emp_tipo || ''
                }
                if (itemLista.vaga_aberta_municipio) {
                    this.form.dadosFuncionario.nome_vaga = itemLista.vaga_aberta_municipio
                } else if (itemLista.vaga_aberta) {
                    const nomeVaga = (itemLista.vaga_aberta.vaga && itemLista.vaga_aberta.vaga.nome) || ''
                    const mun = itemLista.vaga_aberta.municipio
                    if (nomeVaga && mun && mun.nome) {
                        this.form.dadosFuncionario.nome_vaga =
                            nomeVaga + ' - ' + mun.nome + (mun.uf ? ' - ' + mun.uf : '')
                    } else {
                        this.form.dadosFuncionario.nome_vaga = nomeVaga
                    }
                    this.form.dadosFuncionario.cargo = nomeVaga
                }
            }

            // Abre a modal já com preload — senão o "Aguarde" não aparece
            this.$nextTick(() => this.abrirModal(REFS_MODAL.JANELA_TREINAMENTO))

            try {
                const response = await axios.get(`${URL_ADMIN}/${API_PATHS.editar}/${this.form.curriculo_id}/editar`)
                const data = response.data
                // API enxuta: Treinamento/Curriculo/Feedback só com campos da modal
                const treinamento = data.Treinamento || data.treinamento
                const curriculo = data.Curriculo || data.curriculo

                const fotoLista = this.form.dadosFuncionario.foto_3x4_thumb
                this.form.dadosFuncionario = {
                    ...(data.dadosFuncionario || {}),
                    foto_3x4_thumb: (data.dadosFuncionario && data.dadosFuncionario.foto_3x4_thumb) || fotoLista || null
                }
                this.form._modalFoto3x4Ready = !!this.form.dadosFuncionario.foto_3x4_thumb && !!this.form._modalFoto3x4Ready
                this.tituloJanela = 'Treinamentos do colaborador'
                this.form.listaVencimentosNaoVinculadosCargo = data.listaVencimentosNaoVinculadosCargo || []
                this.form.alertaSemCargoVinculo = !!data.alertaSemCargoVinculo
                this.privilegio_gestao_rh = data.privilegio_gestao_rh ?? false
                this.treinamento_permitir_desmarcar_realizado = data.treinamento_permitir_desmarcar_realizado ?? false
                this.treinamento_retirar_treinamento_realizado = data.treinamento_retirar_treinamento_realizado ?? false
                this.treinamento_fat_obrigatorio = data.treinamento_fat_obrigatorio ?? false
                this.form.feedback_id = data.feedback_id ?? treinamento?.feedback_id ?? ''
                this.form.curriculo_id = curriculo_id
                this.form.nr_trinta_tres = data.nr_trinta_tres
                this.form.nr_trinta_cinco = data.nr_trinta_cinco
                this.form.padrao_treinamento_id = data.padrao_treinamento_id ?? null
                this.form.exame.feedback_id = this.form.feedback_id
                this.form.nome = curriculo?.nome ?? this.form.dadosFuncionario.nome ?? ''

                if (treinamento) {
                    this.editando = true
                    Object.assign(this.form, {
                        id: treinamento.id,
                        feedback_id: treinamento.feedback_id ?? this.form.feedback_id,
                        tipo: treinamento.tipo,
                        gerou_id: treinamento.gerou_id,
                        data_envio: treinamento.data_envio,
                        enviado_email: treinamento.enviado_email,
                        enviou_id: treinamento.enviou_id,
                        email_envio: treinamento.email_envio,
                        email_aberto: treinamento.email_aberto,
                        data_email_aberto: treinamento.data_email_aberto,
                    })
                } else {
                    this.editando = false
                    this.form.vencimentos = []
                }

                this.form.listaVencimentos = (data.listaVencimentos || []).map((t) => ({
                    ...t,
                    arquivo: Array.isArray(t.arquivo) ? t.arquivo : [],
                    arquivoDel: Array.isArray(t.arquivoDel) ? t.arquivoDel : [],
                    _fez_treinamento_ja_salvo: !!t.fez_treinamento,
                    _alterado: false,
                }))

                if (!this.form.nr_trinta_tres && this.form.listaVencimentos?.length) {
                    const index = _.findIndex(this.form.listaVencimentos, { id: 7 })
                    if (index >= 0) this.form.listaVencimentos.splice(index, 1)
                }
                if (!this.form.nr_trinta_cinco && this.form.listaVencimentos?.length) {
                    const index = _.findIndex(this.form.listaVencimentos, { id: 6 })
                    if (index >= 0) this.form.listaVencimentos.splice(index, 1)
                }
            } catch (err) {
                console.error('formAlterar:', err)
                mostraErro('', 'Não foi possível carregar os treinamentos.')
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

            const vencimentoId = treinamento ? treinamento.id : null

            // Datas obrigatórias / válidas (HTML5 date + regra de negócio)
            const itensParaValidarData = vencimentoId
                ? (this.form.listaVencimentos || []).filter((t) => t.id === vencimentoId && t.fez_treinamento)
                : (this.form.listaVencimentos || []).filter((t) => t.fez_treinamento)
            for (const item of itensParaValidarData) {
                const erroData = this.validarDatasTreinamento(item)
                if (erroData) {
                    if (typeof toastr !== 'undefined') toastr.error(erroData)
                    else mostraErro('', erroData)
                    return false
                }
            }

            // Validação FAT obrigatório (apenas anexo): bloqueio no frontend antes de enviar (config ativa)
            if (this.treinamento_fat_obrigatorio) {
                if (vencimentoId) {
                    const item = this.form.listaVencimentos?.find((t) => t.id === vencimentoId)
                    if (item && item.fez_treinamento && !this.itemTemAnexoFat(item)) {
                        item._anexoTouched = true
                        if (typeof toastr !== 'undefined') toastr.error('Anexo da FAT é obrigatório para este cliente em treinamentos realizados.')
                        else mostraErro('', 'Anexo da FAT é obrigatório para este cliente em treinamentos realizados.')
                        return false
                    }
                } else {
                    const semAnexoFat = (this.form.listaVencimentos || []).filter(
                        (t) => t.fez_treinamento && !this.itemTemAnexoFat(t)
                    )
                    if (semAnexoFat.length > 0) {
                        semAnexoFat.forEach((t) => { t._anexoTouched = true })
                        if (typeof toastr !== 'undefined') toastr.error('Anexo da FAT é obrigatório para este cliente em treinamentos realizados.')
                        else mostraErro('', 'Anexo da FAT é obrigatório para este cliente em treinamentos realizados.')
                        return false
                    }
                }
            }

            const janela = document.getElementById('janelaTreinamento')
            if (janela && janela.querySelectorAll('input.is-invalid, select.is-invalid, textarea.is-invalid, button.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false
            }
            if (vencimentoId) {
                this.salvandoVencimentoId = vencimentoId
            } else {
                this.preload = true
            }
            try {
                let response
                if (vencimentoId) {
                    const item = this.form.listaVencimentos?.find((t) => t.id === vencimentoId)
                    if (!item) {
                        if (typeof toastr !== 'undefined') toastr.error('Vencimento não encontrado na lista.')
                        return
                    }
                    response = await axios.post(`${URL_ADMIN}/${API_PATHS.atualizarVencimento}`, {
                        feedback_id: this.form.feedback_id,
                        padrao_treinamento_id: this.form.padrao_treinamento_id,
                        vencimento: item
                    })
                } else {
                    const payload = { ...this.form }
                    payload.padrao_treinamento_id = this.form.padrao_treinamento_id
                    response = await axios.post(`${URL_ADMIN}/${API_PATHS.store}`, payload)
                }
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
                // Erro já exibido pelo interceptor do axios (bootstrap.js)
                const msg = err.response?.data?.msg || ''
                if (msg.indexOf('Anexo da FAT') !== -1 && this.form.listaVencimentos) {
                    this.form.listaVencimentos.forEach((t) => {
                        if (t.fez_treinamento && !this.itemTemAnexoFat(t)) t._anexoTouched = true
                    })
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

            // Com FAT obrigatório, o salvamento em massa não permite anexo por item; exige edição individual.
            if (this.treinamento_fat_obrigatorio && (this.formMassa.listaVencimentos || []).some((t) => t.fez_treinamento)) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('O anexo da FAT é obrigatório. Salve cada treinamento individualmente pela carteira para anexar a FAT.')
                } else {
                    mostraErro('', 'O anexo da FAT é obrigatório. Salve cada treinamento individualmente pela carteira para anexar a FAT.')
                }
                return
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
            this.$nextTick(() => this.syncUrlFiltros())
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.syncUrlFiltros()
            if (this.$refs && this.$refs.componente) {
                this.$refs.componente.atual = 1
                if (this.$refs.componente.buscar) this.$refs.componente.buscar()
            }
        },
        limparFiltros() {
            const d = this.controle.dados
            d.campoBusca = ''
            d.campoCPF = ''
            d.campoVaga = ''
            d.campoUf = ''
            d.campoArea = ''
            d.campoCargo = ''
            d.padrao_treinamento_id = ''
            d.campo_treinados = ''
            d.campoNr_trinta_tres = ''
            d.campoNr_trinta_cinco = ''
            d.campoNr_ebtv = ''
            d.campoAdmitido = ''
            d.campoDemitido = false
            d.campoCracha = ''
            d.campoFoto = ''
            d.campoPcd = ''
            d.campoVencimento = false
            d.dataInicioVencimento = ''
            d.dataFimVencimento = ''
            d.vencimento = ''
            d.campoPeriodoTreinado = false
            d.dataInicioPeriodoTreinado = ''
            d.dataFimPeriodoTreinado = ''
            d.periodoTreinado = ''
            d.campoCnpj = ''
            d.campoCentroCusto = ''
            d.treinamentos = ''
            d.treinamentos_selecionados = []
            d.pages = 50
            this.filtrosAvancadosAbertos = false
            this.syncUrlFiltros()
            this.atualizar()
        },
        limparFiltroAtivo(key) {
            const d = this.controle.dados
            const resetMap = {
                campoBusca: () => this.limparBuscaUnificada(),
                campoCPF: () => this.limparBuscaUnificada(),
                padrao_treinamento_id: () => (d.padrao_treinamento_id = ''),
                campo_treinados: () => {
                    d.campo_treinados = ''
                    d.treinamentos = ''
                    d.treinamentos_selecionados = []
                },
                campoAdmitido: () => {
                    d.campoAdmitido = ''
                    d.campoDemitido = false
                },
                campoDemitido: () => {
                    d.campoAdmitido = ''
                    d.campoDemitido = false
                },
                campoSituacao: () => {
                    d.campoAdmitido = ''
                    d.campoDemitido = false
                },
                campoCnpj: () => {
                    d.campoCnpj = ''
                    d.campoCentroCusto = ''
                },
                campoCentroCusto: () => (d.campoCentroCusto = ''),
                campoVaga: () => {
                    d.campoVaga = ''
                    d.autocomplete_label = ''
                    d.autocomplete_label_anterior = ''
                },
                campoCargo: () => (d.campoCargo = ''),
                campoUf: () => (d.campoUf = ''),
                campoFoto: () => (d.campoFoto = ''),
                campoPcd: () => (d.campoPcd = ''),
                campoCracha: () => (d.campoCracha = ''),
                pages: () => (d.pages = 50),
                campoVencimento: () => {
                    d.campoVencimento = false
                    d.dataInicioVencimento = ''
                    d.dataFimVencimento = ''
                    d.vencimento = ''
                },
                campoPeriodoTreinado: () => {
                    d.campoPeriodoTreinado = false
                    d.dataInicioPeriodoTreinado = ''
                    d.dataFimPeriodoTreinado = ''
                    d.periodoTreinado = ''
                },
                treinamentos_selecionados: () => {
                    d.treinamentos = ''
                    d.treinamentos_selecionados = []
                    d.campo_treinados = ''
                }
            }
            if (resetMap[key]) resetMap[key]()
            this.atualizar()
        },

        atualizarVencimentoString() {
            const d = this.controle.dados
            if (d.campoVencimento && d.dataInicioVencimento && d.dataFimVencimento) {
                d.vencimento = `${this.formatarDataFiltro(d.dataInicioVencimento)} até ${this.formatarDataFiltro(d.dataFimVencimento)}`
            } else {
                d.vencimento = ''
            }
            this.atualizar()
        },

        atualizarPeriodoTreinadoString() {
            const d = this.controle.dados
            if (d.campoPeriodoTreinado && d.dataInicioPeriodoTreinado && d.dataFimPeriodoTreinado) {
                d.periodoTreinado = `${this.formatarDataFiltro(d.dataInicioPeriodoTreinado)} até ${this.formatarDataFiltro(d.dataFimPeriodoTreinado)}`
            } else {
                d.periodoTreinado = ''
            }
            this.atualizar()
        },

        formatarDataFiltro(valor) {
            const raw = (valor || '').trim()
            if (!raw) return ''
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(raw)) return raw
            if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
                const [y, m, d] = raw.split('-')
                return `${d}/${m}/${y}`
            }
            return raw
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

        dataBrValida(br) {
            const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(String(br || '').trim())
            if (!m) return false
            const d = Number(m[1])
            const mo = Number(m[2])
            const y = Number(m[3])
            const dt = new Date(y, mo - 1, d)
            return dt.getFullYear() === y && dt.getMonth() === mo - 1 && dt.getDate() === d
        },
        compararDataBr(a, b) {
            // retorna <0 se a<b, 0 se iguais, >0 se a>b (dd/mm/aaaa)
            const toKey = (br) => {
                const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(String(br || '').trim())
                return m ? `${m[3]}${m[2]}${m[1]}` : ''
            }
            const ka = toKey(a)
            const kb = toKey(b)
            if (!ka || !kb) return 0
            return ka < kb ? -1 : ka > kb ? 1 : 0
        },
        validarDatasTreinamento(treinamento) {
            if (!treinamento || !treinamento.fez_treinamento) return null
            if (!this.dataBrValida(treinamento.data_treinamento)) {
                return `Informe a data do treinamento${treinamento.label ? ` (${treinamento.label})` : ''}.`
            }
            if (this.compararDataBr(treinamento.data_treinamento, this.dataHojeBr) > 0) {
                return `A data do treinamento não pode ser futura${treinamento.label ? ` (${treinamento.label})` : ''}.`
            }
            if (!treinamento.prazo_fixo) {
                if (!this.dataBrValida(treinamento.data_vencimento)) {
                    return `Informe a data de vencimento${treinamento.label ? ` (${treinamento.label})` : ''}.`
                }
                if (this.compararDataBr(treinamento.data_vencimento, this.dataHojeBr) < 0) {
                    return `A data de vencimento não pode ser anterior a hoje${treinamento.label ? ` (${treinamento.label})` : ''}.`
                }
            }
            return null
        },
        calculoDataExpiracao(treinamento) {
            if (treinamento) treinamento._alterado = true
            if (!treinamento.data_treinamento) return
            if (!this.dataBrValida(treinamento.data_treinamento)) return

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
.trn-carteira-filtros {
    --trn-ctrl-h: 1.625rem;
    --trn-ctrl-fs: 0.6875rem;
    --trn-label-fs: 0.7rem;
    --trn-gap: 0.45rem;
}

.trn-carteira-filtros :deep(.mybp-filtros-form) {
    align-items: start;
    row-gap: 0;
}

.trn-carteira-filtros :deep(.mybp-filtros-form > [class*='col-']) {
    margin-bottom: var(--trn-gap);
}

.trn-carteira-filtros :deep(.mybp-filtros-acoes-row) {
    margin-top: 0.15rem;
}

.trn-carteira-filtros :deep(.mybp-filtros-botoes .btn) {
    padding: 0.2rem 0.55rem;
    font-size: 0.8125rem;
    line-height: 1.3;
    border-radius: 8px;
    font-weight: 600;
    white-space: nowrap;
}

.trn-carteira-filtros :deep(.mybp-filtros-botoes .btn .badge) {
    font-size: 0.68rem;
    font-weight: 700;
    vertical-align: middle;
}

.trn-carteira-filtros :deep(.mybp-filtros-botoes .dropdown-toggle::after) {
    margin-left: 0.35rem;
}

.trn-carteira-btn {
    padding: 0.2rem 0.55rem;
    font-size: 0.8125rem;
    line-height: 1.3;
    border-radius: 8px;
    font-weight: 600;
    white-space: nowrap;
}

.colab-foto-3x4-wrap {
    position: relative;
    width: 40px;
    height: 53px;
    flex-shrink: 0;
}

.colab-foto-3x4 {
    display: block;
    width: 40px;
    height: 53px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid rgba(0, 0, 0, 0.12);
    background: #eef1f4;
    flex-shrink: 0;
}

.colab-foto-3x4-wrap > img.colab-foto-3x4 {
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.colab-foto-3x4-wrap > img.colab-foto-3x4.is-ready {
    opacity: 1;
}

.colab-foto-3x4--vazia,
.colab-foto-3x4--loading {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #8a93a0;
    font-size: 0.85rem;
}

.colab-foto-3x4--loading {
    position: absolute;
    inset: 0;
    background: #eef1f4;
}

.card-header.bg-danger .colab-foto-3x4 {
    border-color: rgba(255, 255, 255, 0.35);
}

.card-header.bg-danger .colab-foto-3x4--vazia,
.card-header.bg-danger .colab-foto-3x4--loading {
    background: rgba(255, 255, 255, 0.15);
    color: rgba(255, 255, 255, 0.85);
}

.colab-meta {
    margin: 0 -0.35rem;
}

.colab-meta > [class*='col-'] {
    padding-left: 0.35rem;
    padding-right: 0.35rem;
    margin-bottom: 0.35rem;
}

.colab-meta .meta-item {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    height: 100%;
    padding: 0.45rem 0.55rem;
    background: #f8f9fb;
    border: 1px solid #eef1f4;
    border-radius: 6px;
}

.colab-meta .meta-icon {
    flex-shrink: 0;
    width: 1.5rem;
    height: 1.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(23, 66, 87, 0.1);
    color: var(--primary, #174257);
    font-size: 0.72rem;
}

.colab-meta .meta-content {
    min-width: 0;
    flex: 1;
}

.colab-meta .meta-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    line-height: 1.2;
    margin-bottom: 0.15rem;
}

.colab-meta .meta-value {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #212529;
    line-height: 1.3;
    word-break: break-word;
}

.trn-carteira-filtro-campo {
    width: 100%;
    margin-bottom: 0 !important;
    display: flex;
    flex-direction: column;
}

.trn-carteira-filtro-campo > .mybp-label {
    display: flex;
    align-items: center;
    min-height: 1rem;
    margin-bottom: 0.15rem;
    line-height: 1.15;
    font-size: var(--trn-label-fs);
    font-weight: 600;
}

.trn-carteira-filtro-hint {
    margin-left: 0.3rem;
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--primary, #174257);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.trn-carteira-filtro-campo > .form-control,
.trn-carteira-filtro-campo > .form-control-sm,
.trn-carteira-filtro-campo :deep(.form-control),
.trn-carteira-filtro-campo :deep(.form-control-sm),
.trn-carteira-filtro-campo :deep(.combobox-ac-wrap .form-control),
.trn-carteira-filtro-campo :deep(.ma-select),
.trn-carteira-filtro-campo :deep(.ma-filtro-combo-toggle),
.trn-carteira-filtro-campo :deep(input),
.trn-carteira-periodo :deep(.form-control),
.trn-carteira-periodo :deep(.input-group-text),
.trn-carteira-periodo :deep(input) {
    height: var(--trn-ctrl-h) !important;
    min-height: var(--trn-ctrl-h) !important;
    max-height: var(--trn-ctrl-h) !important;
    padding: 0.15rem 0.45rem !important;
    font-size: var(--trn-ctrl-fs) !important;
    line-height: 1.25 !important;
    border-radius: 6px !important;
    border-color: #dee2e6 !important;
    box-sizing: border-box !important;
}

.trn-carteira-filtro-campo :deep(.ma-filtro-combo > .form-control.ma-select),
.trn-carteira-filtro-campo :deep(.ma-filtro-combo > input.form-control) {
    border-radius: 6px 0 0 6px !important;
}

.trn-carteira-filtro-campo :deep(.ma-filtro-combo-toggle) {
    border-radius: 0 6px 6px 0 !important;
    border-left-width: 0 !important;
}

.trn-carteira-filtro-campo :deep(input::placeholder),
.trn-carteira-filtro-campo :deep(.form-control::placeholder),
.trn-carteira-filtro-campo :deep(.ma-select::placeholder),
.trn-carteira-periodo :deep(input::placeholder) {
    font-size: var(--trn-ctrl-fs) !important;
    opacity: 0.75;
}

.trn-carteira-filtro-campo > .form-control,
.trn-carteira-filtro-campo > .form-control-sm {
    width: 100%;
}

.trn-carteira-filtro-campo :deep(.ma-filtro-combo-toggle) {
    width: auto;
    padding-left: 0.4rem !important;
    padding-right: 0.4rem !important;
    font-size: 0.7rem !important;
}

.trn-carteira-filtro-campo :deep(.mybp-combobox-wrap),
.trn-carteira-filtro-campo :deep(.combobox-ac-wrap) {
    width: 100%;
    display: block;
}

.trn-carteira-filtro-campo :deep(.ma-filtro-combo),
.trn-carteira-filtro-campo :deep(.input-group) {
    width: 100%;
    height: var(--trn-ctrl-h);
}

.trn-carteira-periodo {
    display: flex;
    flex-direction: column;
}

.trn-carteira-periodo :deep(.form-check) {
    display: flex;
    align-items: center;
    min-height: 1rem;
    margin-bottom: 0.15rem;
    padding-left: 1.15rem;
}

.trn-carteira-periodo :deep(.form-check-input) {
    margin-top: 0;
    width: 0.85rem;
    height: 0.85rem;
}

.trn-carteira-periodo :deep(.form-check-label) {
    font-size: var(--trn-label-fs);
    font-weight: 600;
    line-height: 1.15;
    margin-bottom: 0;
}

.trn-carteira-periodo :deep(.input-group) {
    width: 100%;
    height: var(--trn-ctrl-h);
    flex-wrap: nowrap;
}

.trn-carteira-periodo :deep(.input-group-text) {
    width: auto;
    padding-left: 0.35rem !important;
    padding-right: 0.35rem !important;
    border-radius: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    font-size: 0.75rem !important;
}

.trn-carteira-periodo :deep(.input-group > .form-control:first-child) {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

.trn-carteira-periodo :deep(.input-group > .form-control:last-child) {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

.trn-carteira-avancados {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #f8f9fb;
    padding: 0.5rem 0.6rem 0.25rem;
    max-height: min(34vh, 240px);
    overflow: auto;
}

.trn-carteira-avancados__grid {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.trn-carteira-avancados__block {
    background: #fff;
    border: 1px solid #eef1f4;
    border-radius: 6px;
    padding: 0.4rem 0.55rem 0.1rem;
}

.trn-carteira-avancados__title {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6c757d;
    margin: 0 0 0.35rem;
}

.trn-carteira-avancados :deep(.mybp-label) {
    font-size: var(--trn-label-fs);
    margin-bottom: 0.15rem;
    min-height: 1rem;
}

.trn-carteira-avancados :deep([class*='col-']) {
    margin-bottom: var(--trn-gap);
}

.trn-carteira-chips,
.trn-carteira-treino-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    align-items: center;
}

.trn-carteira-chips {
    max-height: 3.6rem;
    overflow: auto;
    padding: 0.1rem 0;
}

.trn-carteira-treino-badges {
    margin-top: 0.3rem;
    min-height: var(--trn-ctrl-h);
    padding: 0.1rem 0;
}

.trn-carteira-chip,
.trn-carteira-treino-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    max-width: 100%;
    padding: 0.1rem 0.25rem 0.1rem 0.4rem;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(23, 66, 87, 0.18);
    color: var(--primary, #174257);
    font-size: 0.68rem;
    font-weight: 600;
    line-height: 1.2;
}

.trn-carteira-chip__text,
.trn-carteira-treino-badge__text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 200px;
}

.trn-carteira-chip__remove,
.trn-carteira-treino-badge__remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 0.9rem;
    height: 0.9rem;
    border: 0;
    border-radius: 50%;
    background: rgba(23, 66, 87, 0.12);
    color: var(--primary, #174257);
    cursor: pointer;
    padding: 0;
    line-height: 1;
    flex-shrink: 0;
    font-size: 0.6rem;
}

.trn-carteira-chip__remove:hover,
.trn-carteira-treino-badge__remove:hover {
    background: var(--primary, #174257);
    color: #fff;
}

.dropdown-carteira-etiquetas.show {
    position: relative;
    z-index: 1040;
}
.dropdown-carteira-etiquetas.show .dropdown-menu {
    z-index: 1041;
}

/* Cabeçalho compacto da modal — mesmo modelo do histórico */
.treino-modal-perfil {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin: 0 0 0.85rem;
    padding: 0.7rem 0.9rem;
    background: #fff;
    border: 1px solid #e4e8ec;
    border-radius: 8px;
    border-left: 4px solid #174257;
}
.treino-modal-perfil--aprovado {
    border-left-color: #1e7e34;
}
.treino-modal-perfil--reprovado {
    border-left-color: #dc3545;
}
.treino-modal-perfil--aberto {
    border-left-color: #174257;
}
.treino-modal-perfil__foto-wrap {
    position: relative;
    width: 48px;
    height: 64px;
    flex-shrink: 0;
}
.treino-modal-perfil__foto {
    display: block;
    width: 48px;
    height: 64px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    background: #eef1f4;
}
.treino-modal-perfil__foto-wrap > img.treino-modal-perfil__foto {
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.treino-modal-perfil__foto-wrap > img.treino-modal-perfil__foto.is-ready {
    opacity: 1;
}
.treino-modal-perfil__foto--vazia,
.treino-modal-perfil__foto--loading {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #8a93a0;
    font-size: 0.9rem;
}
.treino-modal-perfil__foto--loading {
    position: absolute;
    inset: 0;
    background: #eef1f4;
}
.treino-modal-perfil__corpo {
    min-width: 0;
    flex: 1;
}
.treino-modal-perfil__titulo {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.15rem;
    margin-bottom: 0.3rem;
}
.treino-modal-perfil__nome {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.3;
    color: #174257;
    word-break: break-word;
}
.treino-modal-perfil__meta-id {
    margin: 0;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    font-size: 0.8125rem;
    line-height: 1.35;
}
.treino-modal-perfil__id {
    font-weight: 600;
    color: #6c757d;
}
.treino-modal-perfil__status {
    font-weight: 600;
    color: #6c757d;
}
.treino-modal-perfil__status--aprovado {
    color: #1e7e34;
}
.treino-modal-perfil__status--reprovado {
    color: #dc3545;
}
.treino-modal-perfil__status--aberto {
    color: #174257;
}
.treino-modal-perfil__linhas {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}
.treino-modal-perfil__linha {
    margin: 0;
    font-size: 0.8125rem;
    line-height: 1.45;
    color: #495057;
}
.treino-modal-perfil__k {
    font-weight: 600;
    color: #6c757d;
    margin-right: 0.2rem;
}
.treino-modal-perfil__sep {
    margin: 0 0.4rem;
    color: #ced4da;
}
@media (max-width: 575.98px) {
    .treino-modal-perfil {
        gap: 0.65rem;
        padding: 0.65rem 0.75rem;
    }
}
</style>
