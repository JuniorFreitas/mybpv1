<template>
    <div id="componenteAtaReuniao">
        <modal ref="modalAtaReuniao" :modal-pai="modal" :titulo="titulo_janela_form_atareuniao" id="janelaFormAtaReuniao" :size="65">
            <template #conteudo>
                <p class="mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h6 class="text-center"><i class="icon fa fa-check"></i> Cadastrado com sucesso!</h6>
                </div>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label>Área</label>
                                    <select class="form-control form-control-sm" v-model="form.area_etiqueta_id">
                                        <option value="">Selecione</option>
                                        <option :value="item.id" v-for="(item, index) in areasetiquetas" :key="index">
                                            {{ item.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label>Centro de Custo</label>
                                    <select v-model="form.centro_custo_id" class="form-control form-control-sm">
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :value="item.id" :key="item.id">{{ item.label }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-sm-8">
                                <label>Título</label>
                                <input class="form-control form-control-sm" v-model="form.titulo" type="text" placeholder="Ex.: Reunião gerencial semanal" />
                            </div>
                            <div class="col-12 col-sm-4" v-if="editando">
                                <label>Status</label>
                                <div>
                                    <span class="badge" :class="statusClass(form.status)">{{ statusLabel(form.status) }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label>Objetivo</label>
                                <textarea class="form-control form-control-sm" v-model="form.objetivo" rows="2" placeholder="Descreva o objetivo principal da reunião"></textarea>
                            </div>

                            <div class="col-12">
                                <label>Local</label>
                                <input class="form-control form-control-sm" v-model="form.local" onblur="valida_campo_vazio(this, 1)" type="text" />
                            </div>
                            <div class="col-6">
                                <date-picker formsm label="Data Iníco" v-model="form.data_inicio" :disabled="editando" :hora="true"></date-picker>
                            </div>
                            <div class="col-6">
                                <date-picker formsm label="Data Fim" v-model="form.data_fim" :disabled="editando" :hora="true"></date-picker>
                            </div>
                        </div>
                    </fieldset>

                    <button class="btn btn-sm mr-1 btn-primary mb-3" :disabled="editando" @click="addLIAssuntos">
                        <i class="fa fa-plus"></i> Adicionar Assuntos
                    </button>

                    <template v-if="form.assuntos.length > 0">
                    <fieldset class="mb-2" v-for="(obj, index) in form.assuntos" :key="index + 1">
                        <legend>#Assuntos {{ index + 1 }}</legend>
                        <div class="row">
                            <div class="col-12">
                                <label>Assunto</label>
                                <textarea
                                    v-model="obj.assunto"
                                    :disabled="!obj.novo"
                                    onblur="valida_campo_vazio(this, 1)"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>
                            <div class="col-12 mt-3" v-show="obj.novo">
                                <button class="btn btn-sm mr-1 btn-danger" @click="removerLIAssuntos(index)"><i class="fa fa-times"></i> Remover</button>

                                <button class="btn btn-sm mr-1 btn-primary mt" @click="addLIAssuntos" v-show="index >= 1">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </fieldset>
                    </template>

                    <button class="btn btn-sm mr-1 btn-primary mb-3" :disabled="editando" @click="addLITipos">
                        <i class="fa fa-plus"></i> Adicionar Comentários / Assuntos Pendentes / Próxima Reunião
                    </button>

                    <template v-if="form.tipos.length > 0">
                    <fieldset class="mb-2" v-for="(obj, index) in form.tipos" :key="index + 100">
                        <legend>#Tipos {{ index + 1 }}</legend>
                        <div class="row">
                            <div class="col-12">
                                <label>Local</label>
                                <select class="form-control" :disabled="!obj.novo" v-model="obj.tipo" onblur="valida_campo_vazio(this, 1)">
                                    <option value="">Selecione</option>
                                    <option value="comentarios">Comentários</option>
                                    <option value="assuntos_pendentes">Assuntos Pendentes</option>
                                    <option value="proxima_reuniao">Próxima Reunião</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label>Comentários / Assuntos Pendentes / Próxima Reunião</label>
                                <textarea
                                    v-model="obj.observacao"
                                    :disabled="!obj.novo"
                                    onblur="valida_campo_vazio(this, 1)"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>
                            <div class="col-12 mt-3" v-show="obj.novo">
                                <button class="btn btn-sm mr-1 btn-danger" @click="removerLITipos(index)"><i class="fa fa-times"></i> Remover</button>

                                <button class="btn btn-sm mr-1 btn-primary mt" @click="addLITipos" v-show="index >= 1"><i class="fa fa-plus"></i> Adicionar</button>
                            </div>
                        </div>
                    </fieldset>
                    </template>

                    <button class="btn btn-sm mr-1 btn-primary mb-3" :disabled="editando" @click="addLIAcoes">
                        <i class="fa fa-plus"></i> Ações - Próximos passos
                    </button>

                    <template v-if="form.acoes.length > 0">
                        <fieldset class="mb-2" v-for="(obj, index) in form.acoes" :key="index + 1000">
                            <legend>#Ações {{ index + 1 }}</legend>
                            <div class="row">
                                <div class="col-12">
                                    <label>Responsável</label>
                                    <input class="form-control" :disabled="!obj.novo" onblur="valida_campo_vazio(this, 1)" v-model="obj.responsavel" />
                                </div>
                                <div class="col-12">
                                    <label>Email</label>
                                    <input class="form-control" type="email" :disabled="!obj.novo" onblur="valida_campo_vazio(this, 1)" v-model="obj.email" />
                                </div>

                                <div class="col-12">
                                    <label>Contínuo</label>
                                    <select class="form-control" :disabled="!obj.novo" onblur="valida_campo_vazio(this, 1)" v-model="obj.continuo">
                                        <option value="">Selecione</option>
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>

                                <div class="col-12" v-show="obj.continuo == false">
                                    <date-picker formsm label="Prazo" v-model="obj.prazo" :disabled="!obj.novo"></date-picker>
                                </div>

                                <div class="col-12" v-show="!obj.novo">
                                    <label>Status</label>
                                    <select class="form-control" onblur="valida_campo_vazio(this, 1)" v-model="obj.status">
                                        <option value="nao_iniciada">Não iniciada</option>
                                        <option value="em_andamento">Em andamento</option>
                                        <option value="aguardando_terceiro">Aguardando terceiro</option>
                                        <option value="aguardando_validacao">Aguardando validação</option>
                                        <option value="concluida">Concluída</option>
                                        <option value="reprogramada">Reprogramada</option>
                                        <option value="cancelada">Cancelada</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label>Ações</label>
                                    <textarea
                                        v-model="obj.acao"
                                        :disabled="!obj.novo"
                                        onblur="valida_campo_vazio(this, 1)"
                                        class="form-control"
                                        rows="3"
                                    ></textarea>
                                </div>

                                <div class="col-12 mt-3" v-show="obj.novo">
                                    <button class="btn btn-sm mr-1 btn-danger" @click="removerLIAcoes(index)"><i class="fa fa-times"></i> Remover</button>

                                    <button class="btn btn-sm mr-1 btn-primary mt" @click="addLIAcoes" v-show="index >= 1"><i class="fa fa-plus"></i> Adicionar</button>
                                </div>
                            </div>
                        </fieldset>
                    </template>

                    <button class="btn btn-sm mr-1 btn-primary mb-3" :disabled="editando" @click="addLIParticipantes">
                        <i class="fa fa-plus"></i> Participantes
                    </button>

                    <fieldset class="mb-2" v-show="form.participantes.length > 0" v-for="(obj, index) in form.participantes" :key="index + 1500">
                        <legend>#Participantes {{ index + 1 }}</legend>
                        <div class="row">
                            <div class="col-12">
                                <label>Nome</label>
                                <input class="form-control" onblur="valida_campo_vazio(this, 1)" v-model="obj.nome" :disabled="!obj.novo" />
                            </div>
                            <div class="col-12">
                                <label>Função</label>
                                <input class="form-control" onblur="valida_campo_vazio(this, 1)" v-model="obj.funcao" :disabled="!obj.novo" />
                            </div>

                            <div class="col-12 mt-3" v-show="obj.novo">
                                <button class="btn btn-sm mr-1 btn-danger" @click="removerLIParticipantes(index)"><i class="fa fa-times"></i> Remover</button>

                                <button class="btn btn-sm mr-1 btn-primary mt" @click="addLIParticipantes" v-show="index >= 1">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando" @click="cadastrar()">Cadastrar</button>
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando" @click="alterarformAtaReuniao()">Alterar</button>
                    <button type="button" class="btn btn-sm mr-1 btn-info" v-show="editando" @click="confirmarCiencia()">Confirmar ciência</button>
                </template>
        </modal>

        <div class="ata-dashboard row mb-3">
            <div class="col-6 col-lg-3 mb-2">
                <div class="card ata-card">
                    <span>Total de atas</span>
                    <strong>{{ dashboardResumo.atas.total || 0 }}</strong>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-2">
                <div class="card ata-card">
                    <span>Aguardando aprovação</span>
                    <strong>{{ dashboardResumo.atas.aguardando_aprovacao || 0 }}</strong>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-2">
                <div class="card ata-card ata-card-warning">
                    <span>Pendências abertas</span>
                    <strong>{{ dashboardResumo.pendencias.abertas || 0 }}</strong>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-2">
                <div class="card ata-card ata-card-danger">
                    <span>Pendências atrasadas</span>
                    <strong>{{ dashboardResumo.pendencias.atrasadas || 0 }}</strong>
                </div>
            </div>
        </div>

        <fieldset class="mb-3">
            <legend>Aprovação, notificações e relatórios</legend>
            <div class="row">
                <div class="col-12 col-lg-5">
                    <label>Buscar aprovador</label>
                    <div class="input-group input-group-sm">
                        <input class="form-control" v-model="aprovadorBusca" type="text" placeholder="Digite o nome do aprovador" @keyup.enter="buscarAprovadores" />
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" @click="buscarAprovadores">Buscar</button>
                        </div>
                    </div>
                    <div class="ata-sugestoes" v-if="aprovadoresEncontrados.length">
                        <button class="btn btn-sm btn-light mr-1 mb-1" type="button" v-for="usuario in aprovadoresEncontrados" :key="usuario.id" @click="selecionarAprovador(usuario)">
                            {{ usuario.label || usuario.nome }}
                        </button>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <label>Aprovadores selecionados</label>
                    <div class="ata-aprovadores-selecionados">
                        <span class="badge badge-info mr-1 mb-1" v-for="usuario in aprovadoresSelecionados" :key="usuario.id">
                            {{ usuario.nome }}
                            <button type="button" class="ata-badge-remove" @click="removerAprovador(usuario.id)">×</button>
                        </span>
                        <span class="text-muted" v-if="!aprovadoresSelecionados.length">Nenhum aprovador selecionado</span>
                    </div>
                </div>
                <div class="col-12 col-lg-3">
                    <label>Configuração de cobrança</label>
                    <button class="btn btn-sm btn-outline-primary btn-block" type="button" @click="carregarConfigNotificacao">Carregar configuração</button>
                    <button class="btn btn-sm btn-outline-success btn-block mt-1" type="button" @click="salvarConfigNotificacao">Salvar configuração</button>
                </div>
                <div class="col-12 mt-2" v-if="configNotificacao.carregada">
                    <div class="row">
                        <div class="col-6 col-lg-2">
                            <label>Dias antes</label>
                            <input class="form-control form-control-sm" type="number" min="0" max="30" v-model.number="configNotificacao.dias_antecedencia" />
                        </div>
                        <div class="col-6 col-lg-2">
                            <label>Horário</label>
                            <input class="form-control form-control-sm" type="time" v-model="configNotificacao.horario_envio" />
                        </div>
                        <div class="col-12 col-lg-3">
                            <label>Fuso horário</label>
                            <input class="form-control form-control-sm" type="text" v-model="configNotificacao.timezone" />
                        </div>
                        <div class="col-12 col-lg-5 d-flex align-items-end flex-wrap">
                            <label class="mr-3"><input type="checkbox" v-model="configNotificacao.usar_dias_uteis" /> Dias úteis</label>
                            <label class="mr-3"><input type="checkbox" v-model="configNotificacao.reenviar_no_vencimento" /> Cobrar no vencimento</label>
                            <label class="mr-3"><input type="checkbox" v-model="configNotificacao.cobrar_apos_atraso" /> Cobrar atrasos</label>
                            <label><input type="checkbox" v-model="configNotificacao.incluir_gestor_copia" /> Gestor em cópia</label>
                        </div>
                        <div class="col-12 col-lg-4 mt-2">
                            <label>Escalonamento D+</label>
                            <input class="form-control form-control-sm" type="text" v-model="configNotificacao.dias_escalonamento_texto" placeholder="1,3,5,10" />
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <button class="btn btn-sm btn-outline-secondary" type="button" @click="carregarRelatorios">Atualizar relatórios</button>
                    <button class="btn btn-sm btn-outline-primary ml-1" type="button" @click="exportarRelatorio('csv')">Exportar CSV</button>
                    <button class="btn btn-sm btn-outline-primary ml-1" type="button" @click="exportarRelatorio('xlsx')">Exportar XLSX</button>
                    <button class="btn btn-sm btn-outline-primary ml-1" type="button" @click="exportarRelatorio('pdf')">Exportar PDF</button>
                    <div class="row mt-2" v-if="relatorios.carregado">
                        <div class="col-12 col-lg-4">
                            <strong>Atas por status</strong>
                            <div v-for="item in relatorios.atas_por_status" :key="`ata-${item.status}`">{{ statusLabel(item.status) }}: {{ item.total }}</div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <strong>Pendências por status</strong>
                            <div v-for="item in relatorios.pendencias_por_status" :key="`pend-${item.status}`">{{ statusLabel(item.status) }}: {{ item.total }}</div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <strong>Notificações</strong>
                            <div v-for="(item, index) in relatorios.notificacoes" :key="index">{{ item.tipo }} / {{ item.status }}: {{ item.total }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="mb-3" v-if="ataSelecionadaId">
            <legend>Comentários, anexos e ciência da ata selecionada</legend>
            <div class="row">
                <div class="col-12 col-lg-6">
                    <label>Novo comentário</label>
                    <textarea class="form-control form-control-sm" rows="3" v-model="novoComentario" placeholder="Registre um comentário rastreável sobre a ata ou pendência"></textarea>
                    <button class="btn btn-sm btn-primary mt-2" type="button" @click="salvarComentario">Adicionar comentário</button>
                    <div class="ata-lista-colaboracao mt-2">
                        <div class="ata-item-colaboracao" v-for="comentario in comentarios" :key="comentario.id">
                            <strong>{{ autorComentario(comentario) }}</strong>
                            <small>{{ comentario.created_at }}</small>
                            <p>{{ comentario.texto }}</p>
                        </div>
                        <span class="text-muted" v-if="!comentarios.length">Nenhum comentário registrado.</span>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <label>Registrar anexo ou link</label>
                    <input class="form-control form-control-sm mb-1" v-model="novoAnexo.nome" placeholder="Nome do anexo" />
                    <input class="form-control form-control-sm mb-1" v-model="novoAnexo.link" placeholder="Link seguro ou referência do arquivo" />
                    <button class="btn btn-sm btn-primary" type="button" @click="salvarAnexo">Registrar anexo</button>
                    <div class="ata-lista-colaboracao mt-2">
                        <div class="ata-item-colaboracao" v-for="anexo in anexos" :key="anexo.id">
                            <strong>{{ anexo.nome }}</strong>
                            <a v-if="anexo.link" :href="anexo.link" target="_blank" rel="noopener noreferrer">Abrir</a>
                            <small>{{ anexo.created_at }}</small>
                        </div>
                        <span class="text-muted" v-if="!anexos.length">Nenhum anexo registrado.</span>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
                <div class="col-12 col-md-5">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input
                            type="text"
                            placeholder="Buscar por nome"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button type="button" class="btn btn-sm mr-1 btn-primary" :disabled="controle.carregando" @click="formNovo">
                        <i class="fa fa-plus"></i> Cadastrar Ata de Reunião
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Nº</td>
                            <td class="text-center">Código</td>
                            <td class="text-center">Título</td>
                            <td class="text-center">Status</td>
                            <td class="text-center">Quem Cadastrou</td>
                            <td class="text-center">Local</td>
                            <td class="text-center">Data Início</td>
                            <td class="text-center">Data Fim</td>
                            <td class="text-center">Opções</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(atareuniao, ind) in lista" :key="ind">
                            <td class="text-center">{{ ind + 1 }}</td>
                            <td class="text-center">{{ atareuniao.codigo || '-' }}</td>
                            <td class="text-center">{{ atareuniao.titulo || 'Ata de Reunião' }}</td>
                            <td class="text-center"><span class="badge" :class="statusClass(atareuniao.status)">{{ statusLabel(atareuniao.status) }}</span></td>
                            <td class="text-center">{{ atareuniao.quem_cadastrou.nome }}</td>
                            <td class="text-center">{{ atareuniao.local }}</td>
                            <td class="text-center">{{ atareuniao.data_inicio }}</td>
                            <td class="text-center">{{ atareuniao.data_fim }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm mr-1 btn-primary" @click="alterarAtaReuniao(atareuniao.id)" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm mr-1 btn-primary" title="Gerar PDF" @click="gerarPdf(atareuniao.id)">
                                    <i class="fa fa-file-pdf"></i>
                                </button>
                                <button type="button" class="btn btn-sm mr-1 btn-info" title="Solicitar aprovação" @click="solicitarAprovacao(atareuniao)">
                                    <i class="fa fa-user-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm mr-1 btn-success" title="Aprovar ata" @click="decidirAprovacao(atareuniao, 'aprovado')">
                                    <i class="fa fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm mr-1 btn-secondary" title="Compartilhar link externo" @click="compartilharExterno(atareuniao)">
                                    <i class="fa fa-link"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlPaginacao"
                :por-pagina="qntPag"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            ></controle-paginacao>
        </div>

        <fieldset class="mt-3">
            <legend>Minhas Pendências</legend>
            <div class="alert alert-warning text-center" v-show="!minhasPendenciasCarregando && minhasPendencias.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhuma pendência atribuída a você
            </div>
            <p class="mt-2 text-center" v-if="minhasPendenciasCarregando"><i class="fa fa-spinner fa-pulse"></i> Carregando pendências...</p>
            <div class="table-responsive" v-show="!minhasPendenciasCarregando && minhasPendencias.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <td>Ata</td>
                            <td>Pendência</td>
                            <td>Prazo</td>
                            <td>Status</td>
                            <td>Prioridade</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="pendencia in minhasPendencias" :key="pendencia.id">
                            <td>{{ pendencia.ata_codigo || '-' }} - {{ pendencia.ata_titulo || 'Ata' }}</td>
                            <td>{{ pendencia.titulo || pendencia.descricao || pendencia.acao }}</td>
                            <td>{{ pendencia.prazo || 'Não informado' }}</td>
                            <td><span class="badge" :class="statusClass(pendencia.status)">{{ statusLabel(pendencia.status) }}</span></td>
                            <td>{{ pendencia.prioridade || 'media' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>
    </div>
</template>

<script>
import controlePaginacao from '../../ControlePaginacao'
import modal from '../../Modal'
import editor from '@tinymce/tinymce-vue'
import DatePicker from '../../DatePicker'

export default {
    components: {
        DatePicker,
        modal,
        controlePaginacao,
        editor
    },
    props: {
        qntPag: {
            type: Number,
            required: false,
            default: 20
        },

        status: {
            type: Boolean,
            required: false,
            default: true
        },

        filtro: {
            type: Boolean,
            required: false,
            default: true
        },
        modal: {
            // modal Pai
            type: String,
            required: false,
            default: ''
        }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela_form_atareuniao: 'Ata Reunião',

            preload: false,
            editando: false,
            cadastrado: false,
            atualizado: false,

            form: {
                area_etiqueta_id: '',
                centro_custo_id: '',
                titulo: '',
                objetivo: '',
                status: 'rascunho',
                local: '',
                data_inicio: '',
                data_fim: '',
                assuntos: [],
                assuntosDelete: [],
                tipos: [],
                tiposDelete: [],
                acoes: [],
                acoesDelete: [],
                participantes: [],
                participantesDelete: []
            },
            formDefault: null,

            lista: [],
                dashboardResumo: {
                atas: {},
                pendencias: {}
            },
            aprovadorBusca: '',
            aprovadoresEncontrados: [],
            aprovadoresSelecionados: [],
            ataSelecionadaId: null,
            novoComentario: '',
            comentarios: [],
            novoAnexo: {
                nome: '',
                link: ''
            },
            anexos: [],
            configNotificacao: {
                carregada: false,
                usar_dias_uteis: false,
                dias_antecedencia: 2,
                horario_envio: '07:00',
                timezone: 'America/Sao_Paulo',
                incluir_gestor_copia: false,
                reenviar_no_vencimento: true,
                cobrar_apos_atraso: true,
                dias_escalonamento_texto: '1,3,5,10'
            },
            relatorios: {
                carregado: false,
                atas_por_status: [],
                pendencias_por_status: [],
                notificacoes: []
            },
            minhasPendencias: [],
            minhasPendenciasCarregando: false,
            empresa_id: null,

            areasetiquetas: [],
            centro_custos: [],

            urlPaginacao: `${URL_ADMIN}/administracao/atareuniao/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoFiltro: ''
                }
            }
        }
    },
    mounted() {
        this.atualizar()
        this.carregarDashboardResumo()
        this.carregarMinhasPendencias()
        this.formDefault = _.cloneDeep(this.form)
    },
    methods: {
        statusLabel(status) {
            const labels = {
                rascunho: 'Rascunho',
                em_elaboracao: 'Em elaboração',
                aguardando_revisao: 'Aguardando revisão',
                aguardando_aprovacao: 'Aguardando aprovação',
                ajustes_solicitados: 'Ajustes solicitados',
                aprovada: 'Aprovada',
                publicada: 'Publicada',
                encerrada: 'Encerrada',
                cancelada: 'Cancelada',
                andamento: 'Andamento',
                pendente: 'Pendente',
                concluido: 'Concluída',
                concluida: 'Concluída',
                atrasada: 'Atrasada',
                nao_iniciada: 'Não iniciada',
                em_andamento: 'Em andamento',
                aguardando_validacao: 'Aguardando validação'
            }
            return labels[status] || status || 'Rascunho'
        },
        statusClass(status) {
            if (['aprovada', 'publicada', 'concluido', 'concluida'].includes(status)) return 'badge-success'
            if (['aguardando_aprovacao', 'aguardando_revisao', 'aguardando_validacao'].includes(status)) return 'badge-warning'
            if (['atrasada', 'cancelada', 'rejeitado'].includes(status)) return 'badge-danger'
            return 'badge-secondary'
        },
        carregarDashboardResumo() {
            axios
                .get(`${URL_ADMIN}/administracao/atareuniao/dashboard-resumo`)
                .then((response) => {
                    this.dashboardResumo = response.data || { atas: {}, pendencias: {} }
                })
                .catch(() => {})
        },
        carregarMinhasPendencias() {
            this.minhasPendenciasCarregando = true
            axios
                .get(`${URL_ADMIN}/administracao/atareuniao/minhas-pendencias`, { params: { porPagina: 10 } })
                .then((response) => {
                    this.minhasPendencias = response.data.data || []
                })
                .catch(() => {})
                .finally(() => {
                    this.minhasPendenciasCarregando = false
                })
        },
        buscarAprovadores() {
            if (!this.aprovadorBusca || this.aprovadorBusca.trim().length < 2) {
                mostraErro('', 'Digite ao menos 2 caracteres para buscar aprovadores')
                return
            }

            axios
                .get(`${URL_ADMIN}/autocomplete/todos-usuarios-ativos`, { params: { busca: this.aprovadorBusca, rows: 10 } })
                .then((response) => {
                    this.aprovadoresEncontrados = response.data || []
                })
                .catch(() => mostraErro('', 'Não foi possível buscar aprovadores'))
        },
        selecionarAprovador(usuario) {
            if (this.aprovadoresSelecionados.some((item) => item.id === usuario.id)) return
            this.aprovadoresSelecionados.push({ id: usuario.id, nome: usuario.nome || usuario.label })
        },
        removerAprovador(id) {
            this.aprovadoresSelecionados = this.aprovadoresSelecionados.filter((item) => item.id !== id)
        },
        carregarConfigNotificacao() {
            axios
                .get(`${URL_ADMIN}/administracao/atareuniao/notificacao-config`)
                .then((response) => {
                    const dados = response.data || {}
                    this.configNotificacao = {
                        carregada: true,
                        usar_dias_uteis: !!dados.usar_dias_uteis,
                        dias_antecedencia: dados.dias_antecedencia ?? 2,
                        horario_envio: (dados.horario_envio || '07:00').substring(0, 5),
                        timezone: dados.timezone || 'America/Sao_Paulo',
                        incluir_gestor_copia: !!dados.incluir_gestor_copia,
                        reenviar_no_vencimento: !!dados.reenviar_no_vencimento,
                        cobrar_apos_atraso: !!dados.cobrar_apos_atraso,
                        dias_escalonamento_texto: (dados.dias_escalonamento || [1, 3, 5, 10]).join(',')
                    }
                })
                .catch(() => mostraErro('', 'Sem permissão ou erro ao carregar configuração'))
        },
        salvarConfigNotificacao() {
            const diasEscalonamento = String(this.configNotificacao.dias_escalonamento_texto || '')
                .split(',')
                .map((item) => parseInt(item.trim()))
                .filter((item) => Number.isInteger(item) && item > 0)

            axios
                .put(`${URL_ADMIN}/administracao/atareuniao/notificacao-config`, {
                    usar_dias_uteis: this.configNotificacao.usar_dias_uteis,
                    dias_antecedencia: this.configNotificacao.dias_antecedencia,
                    horario_envio: this.configNotificacao.horario_envio,
                    timezone: this.configNotificacao.timezone,
                    incluir_gestor_copia: this.configNotificacao.incluir_gestor_copia,
                    reenviar_no_vencimento: this.configNotificacao.reenviar_no_vencimento,
                    cobrar_apos_atraso: this.configNotificacao.cobrar_apos_atraso,
                    dias_escalonamento: diasEscalonamento
                })
                .then(() => mostraSucesso('', 'Configuração salva'))
                .catch(() => mostraErro('', 'Não foi possível salvar configuração'))
        },
        carregarRelatorios() {
            axios
                .get(`${URL_ADMIN}/administracao/atareuniao/relatorios`)
                .then((response) => {
                    this.relatorios = {
                        carregado: true,
                        atas_por_status: response.data.atas_por_status || [],
                        pendencias_por_status: response.data.pendencias_por_status || [],
                        notificacoes: response.data.notificacoes || []
                    }
                })
                .catch(() => mostraErro('', 'Não foi possível carregar relatórios'))
        },
        exportarRelatorio(formato) {
            axios
                .post(`${URL_ADMIN}/administracao/atareuniao/relatorios/exportar`, { formato })
                .then(() => mostraSucesso('', 'Exportação enfileirada. Você será notificado ao concluir.'))
                .catch(() => mostraErro('', 'Não foi possível enfileirar exportação'))
        },
        carregarColaboracaoAta(id) {
            this.ataSelecionadaId = id
            axios
                .get(`${URL_ADMIN}/administracao/atareuniao/${id}/comentarios`)
                .then((response) => {
                    this.comentarios = response.data || []
                })
                .catch(() => {
                    this.comentarios = []
                })

            axios
                .get(`${URL_ADMIN}/administracao/atareuniao/${id}/anexos`)
                .then((response) => {
                    this.anexos = response.data || []
                })
                .catch(() => {
                    this.anexos = []
                })
        },
        salvarComentario() {
            if (!this.ataSelecionadaId || !this.novoComentario.trim()) return

            axios
                .post(`${URL_ADMIN}/administracao/atareuniao/${this.ataSelecionadaId}/comentarios`, { texto: this.novoComentario })
                .then((response) => {
                    this.comentarios.push(response.data)
                    this.novoComentario = ''
                    mostraSucesso('', 'Comentário registrado')
                })
                .catch(() => mostraErro('', 'Não foi possível registrar comentário'))
        },
        autorComentario(comentario) {
            const autor = comentario.autor || comentario.Autor

            return autor?.nome || 'Usuário'
        },
        salvarAnexo() {
            if (!this.ataSelecionadaId || !this.novoAnexo.nome.trim()) return

            axios
                .post(`${URL_ADMIN}/administracao/atareuniao/${this.ataSelecionadaId}/anexos`, {
                    nome: this.novoAnexo.nome,
                    link: this.novoAnexo.link || null,
                    secao: 'ata'
                })
                .then((response) => {
                    this.anexos.unshift(response.data)
                    this.novoAnexo = { nome: '', link: '' }
                    mostraSucesso('', 'Anexo registrado')
                })
                .catch(() => mostraErro('', 'Não foi possível registrar anexo'))
        },
        confirmarCiencia() {
            if (!this.ataSelecionadaId) return

            axios
                .post(`${URL_ADMIN}/administracao/atareuniao/${this.ataSelecionadaId}/ciencia`, { tipo: 'ciencia' })
                .then(() => mostraSucesso('', 'Ciência confirmada'))
                .catch(() => mostraErro('', 'Não foi possível confirmar ciência'))
        },
        abrirModalAtaReuniao() {
            if (this.$refs && this.$refs.modalAtaReuniao && typeof this.$refs.modalAtaReuniao.abrirModal === 'function') {
                this.$refs.modalAtaReuniao.abrirModal()
            }
        },
        fecharModalAtaReuniao() {
            if (this.$refs && this.$refs.modalAtaReuniao && typeof this.$refs.modalAtaReuniao.fecharModal === 'function') {
                this.$refs.modalAtaReuniao.fecharModal()
            }
        },
        addLIAssuntos() {
            const obj = {}
            obj.novo = true
            obj.assunto = ''

            this.form.assuntos.push(obj)
        },
        removerLIAssuntos(index) {
            if (this.editando) {
                this.form.assuntosDelete.push(this.form.assuntos[index].id)
            }
            this.form.assuntos.splice(index, 1)
        },
        addLITipos() {
            const obj = {}
            obj.novo = true
            obj.tipo = ''
            obj.observacao = ''

            this.form.tipos.push(obj)
        },
        removerLITipos(index) {
            if (this.editando) {
                this.form.tiposDelete.push(this.form.tipos[index].id)
            }
            this.form.tipos.splice(index, 1)
        },
        addLIAcoes() {
            const obj = {}
            obj.novo = true
            obj.responsavel = ''
            obj.email = ''
            obj.prazo = ''
            obj.continuo = ''
            obj.acao = ''
            obj.observacao = ''

            this.form.acoes.push(obj)
        },
        removerLIAcoes(index) {
            if (this.editando) {
                this.form.acoesDelete.push(this.form.acoes[index].id)
            }
            this.form.acoes.splice(index, 1)
        },
        addLIParticipantes() {
            const obj = {}
            obj.novo = true
            obj.nome = ''
            obj.funcao = ''

            this.form.participantes.push(obj)
        },
        removerLIParticipantes(index) {
            if (this.editando) {
                this.form.participantesDelete.push(this.form.participantes[index].id)
            }
            this.form.participantes.splice(index, 1)
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela_form_atareuniao = 'Cadastro de Ata Reunião'
            this.cadastrado = false
            this.finalizado = false
            this.atualizado = false
            this.editando = false
            this.ataSelecionadaId = null
            this.comentarios = []
            this.anexos = []

            formReset()
            setupCampo()
            this.abrirModalAtaReuniao()
        },
        cadastrar() {
            $('#janelaFormAtaReuniao :input:visible').trigger('blur')
            if ($('#janelaFormAtaReuniao :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            axios
                .post(`${URL_ADMIN}/administracao/atareuniao`, this.form)
                .then((res) => {
                    if (res.status === 201) {
                        this.fecharModalAtaReuniao()
                        mostraSucesso('', 'Ata Reunião Cadastrado com sucesso')
                        this.preload = false
                        this.cadastrado = true
                        this.atualizar()
                        this.carregarDashboardResumo()
                    } else {
                        this.cadastrado = false
                        this.preload = false
                    }
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preload = false
                })
        },
        alterarAtaReuniao(atareuniao) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela_form_atareuniao = 'Alterando Ata Reunião'
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.abrirModalAtaReuniao()

            axios
                .get(`${URL_ADMIN}/administracao/atareuniao/${atareuniao}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.carregarColaboracaoAta(response.data.id)
                    this.form.area_etiqueta_id = response.data.area_etiqueta_id ? response.data.area_etiqueta_id : ''
                    this.form.centro_custo_id = response.data.centro_custo_id ? response.data.centro_custo_id : ''
                    this.editando = true
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarformAtaReuniao() {
            formReset()
            $('#janelaFormAtaReuniao :input:enabled').trigger('blur')

            if ($('#janelaFormAtaReuniao :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/administracao/atareuniao/${this.form.id}`, this.form)
                .then((response) => {
                    this.fecharModalAtaReuniao()
                    mostraSucesso('', 'Ata Reunião Editado com sucesso')
                    this.preloadAjax = false
                    this.controle.carregando = true
                    this.atualizado = true
                    this.atualizar()
                    this.carregarDashboardResumo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        gerarPdf(item) {
            let link = `${URL_ADMIN}/administracao/atareuniao/pdf/${item}`
            open(link, 'blank')
        },
        solicitarAprovacao(ata) {
            const aprovador_ids = this.aprovadoresSelecionados.map((usuario) => usuario.id)

            if (!aprovador_ids.length) {
                mostraErro('', 'Selecione ao menos um aprovador antes de solicitar aprovação')
                return
            }

            axios
                .post(`${URL_ADMIN}/administracao/atareuniao/${ata.id}/solicitar-aprovacao`, { aprovador_ids })
                .then(() => {
                    mostraSucesso('', 'Ata enviada para aprovação')
                    this.aprovadoresSelecionados = []
                    this.aprovadoresEncontrados = []
                    this.aprovadorBusca = ''
                    this.atualizar()
                    this.carregarDashboardResumo()
                })
                .catch(() => mostraErro('', 'Não foi possível solicitar aprovação'))
        },
        decidirAprovacao(ata, decisao) {
            const comentario = window.prompt('Comentário da aprovação (opcional)')
            axios
                .post(`${URL_ADMIN}/administracao/atareuniao/${ata.id}/decidir-aprovacao`, { decisao, comentario })
                .then(() => {
                    mostraSucesso('', 'Decisão registrada')
                    this.atualizar()
                    this.carregarDashboardResumo()
                })
                .catch(() => mostraErro('', 'Não foi possível registrar a decisão'))
        },
        compartilharExterno(ata) {
            const email = window.prompt('E-mail do convidado externo (opcional)')
            if (email === null) return
            const nome = window.prompt('Nome do convidado externo (opcional)')

            axios
                .post(`${URL_ADMIN}/administracao/atareuniao/${ata.id}/compartilhar-externo`, { email, nome, escopo: 'leitura' })
                .then((response) => {
                    window.prompt('Link externo válido por 24 horas', response.data.url)
                    mostraSucesso('', 'Link externo gerado')
                })
                .catch(() => mostraErro('', 'Não foi possível gerar o link externo'))
        },
        carregou(dados) {
            this.lista = dados.items

            axios
                .get(`${URL_PUBLICO}/lista-areas`)
                .then((response) => {
                    this.areasetiquetas = response.data.areas ?? ''
                })
                .catch((e) => console.log(e))

            axios
                .post(`${URL_PUBLICO}/centro-custos/`, { empresa_id: dados.empresa_id })
                .then((response) => {
                    this.centro_custos = response.data.centro_custos
                })
                .catch((error) => {
                    this.preload = false
                })

            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            this.carregarDashboardResumo()
            this.carregarMinhasPendencias()
        }
    }
}
</script>

<style scoped>
.card {
    border: none;
    background: transparent;
}

ul.timeline {
    list-style-type: none;
    position: relative;
}

ul.timeline:before {
    content: ' ';
    background: #d4d9df;
    display: inline-block;
    position: absolute;
    left: 29px;
    width: 2px;
    height: 100%;
    z-index: 400;
}

ul.timeline > li {
    margin: 20px 0;
    padding-left: 20px;
}

ul.timeline > li:before {
    content: ' ';
    background: white;
    display: inline-block;
    position: absolute;
    border-radius: 50%;
    border: 3px solid #184056;
    left: 20px;
    width: 20px;
    height: 20px;
    z-index: 400;
}

.trackind {
    padding: 0.5rem 0.8rem;
    background-color: #f4f4f4;
    border-radius: 0.5rem;
}

.ata-card {
    background: #ffffff;
    border-left: 4px solid #184056;
    border-radius: 0.5rem;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    padding: 0.85rem 1rem;
}

.ata-card span {
    color: #617080;
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
}

.ata-card strong {
    color: #184056;
    display: block;
    font-size: 1.45rem;
    line-height: 1.2;
    margin-top: 0.2rem;
}

.ata-card-warning {
    border-left-color: #d97706;
}

.ata-card-danger {
    border-left-color: #dc2626;
}

.ata-sugestoes {
    background: #f8fafc;
    border: 1px solid #d9e2ec;
    border-radius: 0.35rem;
    margin-top: 0.35rem;
    padding: 0.45rem;
}

.ata-aprovadores-selecionados {
    min-height: 32px;
    padding-top: 0.25rem;
}

.ata-badge-remove {
    background: transparent;
    border: 0;
    color: inherit;
    cursor: pointer;
    font-weight: 700;
    margin-left: 0.25rem;
    padding: 0;
}

.ata-lista-colaboracao {
    border: 1px solid #e5e7eb;
    border-radius: 0.35rem;
    max-height: 260px;
    overflow: auto;
    padding: 0.5rem;
}

.ata-item-colaboracao {
    border-bottom: 1px solid #eef2f7;
    padding: 0.45rem 0;
}

.ata-item-colaboracao:last-child {
    border-bottom: 0;
}

.ata-item-colaboracao small {
    color: #7b8794;
    display: block;
}

.ata-item-colaboracao p {
    margin-bottom: 0;
}
</style>
