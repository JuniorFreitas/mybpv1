<template>
    <div class="container-fluid requisicao-vaga-campos-custom">
        <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90" :mostrar-botao-fechar-no-rodape="false" ref="modal_janelaCadastrar">
            <template #conteudo>
                <preload v-show="salvando" class="text-center"></preload>
                <form v-if="!salvando" id="form-campo-custom" @submit.prevent>
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Nome do campo (label) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" v-model="form.label" placeholder="Ex: E-mail institucional" />
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Tipo <span class="text-danger">*</span></label>
                                    <select class="form-control" v-model="form.tipo">
                                        <option value="">Selecione</option>
                                        <option value="sim_nao">Sim / Não</option>
                                        <option value="texto">Texto simples</option>
                                        <option value="textarea">Caixa de texto (textarea)</option>
                                        <option value="select">Select (opções)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12" v-if="form.tipo === 'select'">
                                <div class="form-group">
                                    <label>Opções (uma por linha) <span class="text-danger">*</span></label>
                                    <textarea
                                        class="form-control"
                                        v-model="opcoesTexto"
                                        rows="5"
                                        placeholder="Opção 1&#10;Opção 2&#10;Opção 3"
                                        style="white-space: pre-wrap; word-wrap: break-word"
                                    ></textarea>
                                    <small class="text-muted">Digite cada opção em uma linha.</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="d-block">&nbsp;</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="obrigatorio" v-model="form.obrigatorio" />
                                        <label class="custom-control-label" for="obrigatorio">Campo obrigatório</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" @click="salvar" :disabled="salvando">
                    <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i> Salvar
                </button>
            </template>
        </modal>

        <fieldset class="mt-0">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="listar">
                <div class="col-12">
                    <p class="text-muted mb-2">
                        Configure os campos extras que aparecem no formulário de Requisição de Vaga para sua empresa. Ex.: E-mail institucional (Sim/Não),
                        Notebooks (Sim/Não), Acesso a sistemas (texto).
                    </p>
                </div>
                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="carregando" @click="listar">
                        <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm mr-1 btn-primary"
                        :disabled="carregando"
                        @click.prevent="abrirModal(); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                    >
                        <i class="fa fa-plus"></i> Novo campo
                    </button>
                </div>
            </form>
        </fieldset>

        <preload class="text-center" v-if="carregando"></preload>

        <div class="conteudo-cards">
            <div class="alert alert-warning" v-show="!carregando && campos.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <draggable
                v-show="!carregando && campos.length > 0"
                :model-value="campos"
                item-key="id"
                class="cards-lista"
                handle=".drag-handle"
                :animation="200"
                :disabled="salvandoOrdem"
                @update:model-value="campos = $event"
                @end="aoReordenar"
            >
                <template #item="{ element: c, index }">
                    <div class="solicitacao-card">
                        <div class="card-header-row">
                            <div class="card-left">
                                <span class="drag-handle" title="Arrastar para reordenar">
                                    <i class="fas fa-grip-vertical text-muted"></i>
                                </span>
                                <span class="badge-id">#{{ c.id }}</span>
                                <div class="colaborador-principal">
                                    <span class="badge-tipo">{{ tipoLabel(c.tipo) }}</span>
                                    <strong>{{ c.label }}</strong>
                                </div>
                                <div class="data-info ml-3">
                                    <i class="fas fa-sort-numeric-down text-muted" style="font-size: 0.75rem"></i>
                                    <small class="text-muted">Ordem {{ index + 1 }}</small>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="dropdown show">
                                    <a class="btn-actions-compact" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right">
                                        <a
                                            class="dropdown-item"
                                            href="javascript://"
                                            title="Editar"
                                            @click.prevent="abrirModal(c); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                        >
                                            <i class="fa fa-edit mr-1"></i> Editar
                                        </a>
                                        <a class="dropdown-item text-danger" href="javascript://" title="Excluir" @click.prevent="excluir(c)">
                                            <i class="fa fa-trash mr-1"></i> Excluir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-details-row">
                            <div class="detail-item">
                                <i class="fas fa-tag text-muted"></i>
                                <span class="detail-label">Tipo:</span>
                                <span class="detail-value">{{ tipoLabel(c.tipo) }}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-asterisk text-muted"></i>
                                <span class="detail-label">Obrigatório:</span>
                                <span class="detail-value">{{ c.obrigatorio ? 'Sim' : 'Não' }}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-sort-numeric-down text-muted"></i>
                                <span class="detail-label">Ordem exibição:</span>
                                <span class="detail-value">{{ index + 1 }}</span>
                            </div>
                        </div>
                        <div class="card-details-row" v-if="c.tipo === 'select' && c.opcoes && c.opcoes.length">
                            <div class="detail-item detail-item-full">
                                <i class="fas fa-list text-muted"></i>
                                <span class="detail-label">Opções (select):</span>
                                <span class="detail-value">{{ (c.opcoes || []).join(', ') }}</span>
                            </div>
                        </div>
                    </div>
                </template>
            </draggable>
        </div>
    </div>
</template>

<script>
import draggable from 'vuedraggable'

const API_BASE = '/g/planejamento/requisicao-vaga/campos-custom'

export default {
    name: 'RequisicaoVagaCamposCustom',

    components: {
        draggable
    },

    data() {
        return {
            tituloJanela: 'Campos personalizados - Requisição de Vaga',
            campos: [],
            carregando: true,
            editando: false,
            salvando: false,
            salvandoOrdem: false,
            opcoesTexto: '',
            form: {
                id: null,
                label: '',
                tipo: 'sim_nao',
                opcoes: null,
                obrigatorio: false,
                ordem: 0
            }
        }
    },

    mounted() {
        this.listar()
    },

    methods: {
        tipoLabel(tipo) {
            const map = {
                sim_nao: 'Sim/Não',
                texto: 'Texto simples',
                textarea: 'Caixa de texto',
                select: 'Select'
            }
            return map[tipo] || tipo
        },

        listar() {
            this.carregando = true
            axios
                .get(API_BASE)
                .then((r) => {
                    this.campos = r.data || []
                })
                .catch(() => {
                    this.campos = []
                })
                .finally(() => {
                    this.carregando = false
                })
        },

        abrirModal(campo = null) {
            this.editando = !!campo
            this.tituloJanela = campo ? 'Editar campo' : 'Novo campo'
            this.form = {
                id: campo ? campo.id : null,
                label: campo ? campo.label : '',
                tipo: campo ? campo.tipo : 'sim_nao',
                opcoes: campo && campo.opcoes ? [...(campo.opcoes || [])] : null,
                obrigatorio: campo ? !!campo.obrigatorio : false,
                ordem: campo != null && campo.ordem !== undefined ? campo.ordem : this.campos.length
            }
            this.opcoesTexto = this.form.opcoes && Array.isArray(this.form.opcoes) ? this.form.opcoes.join('\n') : ''
            if (!campo) {
                this.$nextTick(() => {
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.abrirModal()
                })
            }
        },

        opcoesParaArray() {
            const lines = (this.opcoesTexto || '')
                .split(/\r?\n/)
                .map((s) => s.trim())
                .filter(Boolean)
            return lines.length ? lines : null
        },

        salvar() {
            if (!this.form.label.trim()) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Informe o nome do campo.')
                } else {
                    alert('Informe o nome do campo.')
                }
                return
            }
            if (!this.form.tipo) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Selecione o tipo do campo.')
                } else {
                    alert('Selecione o tipo do campo.')
                }
                return
            }
            const opcoesArray = this.form.tipo === 'select' ? this.opcoesParaArray() : null
            if (this.form.tipo === 'select' && (!opcoesArray || !opcoesArray.length)) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Para tipo Select, informe ao menos uma opção.')
                } else {
                    alert('Para tipo Select, informe ao menos uma opção.')
                }
                return
            }

            this.salvando = true
            const payload = {
                label: this.form.label.trim(),
                tipo: this.form.tipo,
                opcoes: opcoesArray,
                obrigatorio: this.form.obrigatorio,
                ordem: parseInt(this.form.ordem, 10) || 0
            }

            const req = this.editando ? axios.put(`${API_BASE}/${this.form.id}`, payload) : axios.post(API_BASE, payload)

            req.then(() => {
                if (typeof toastr !== 'undefined') {
                    toastr.success('Salvo com sucesso.')
                } else {
                    alert('Salvo com sucesso.')
                }
                this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                this.listar()
            })
                .catch((err) => {
                    const msg = (err.response && err.response.data && err.response.data.msg) || 'Erro ao salvar.'
                    if (typeof toastr !== 'undefined') {
                        toastr.error(msg)
                    } else {
                        alert(msg)
                    }
                })
                .finally(() => {
                    this.salvando = false
                })
        },

        excluir(campo) {
            const self = this
            if (typeof $swal !== 'undefined') {
                $swal({
                    title: 'Excluir?',
                    text: `Excluir o campo "${campo.label}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, excluir'
                }).then((result) => {
                    if (result && result.value) self.executarExcluir(campo.id)
                })
            } else if (confirm(`Excluir o campo "${campo.label}"?`)) {
                this.executarExcluir(campo.id)
            }
        },

        executarExcluir(id) {
            axios
                .delete(`${API_BASE}/${id}`)
                .then(() => {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Campo excluído.')
                    } else {
                        alert('Campo excluído.')
                    }
                    this.listar()
                })
                .catch(() => {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Erro ao excluir.')
                    } else {
                        alert('Erro ao excluir.')
                    }
                })
        },

        aoReordenar() {
            const promises = []
            this.campos.forEach((c, index) => {
                if (c.ordem === index) return
                const payload = {
                    label: c.label,
                    tipo: c.tipo,
                    opcoes: c.opcoes || null,
                    obrigatorio: !!c.obrigatorio,
                    ordem: index
                }
                promises.push(
                    axios.put(`${API_BASE}/${c.id}`, payload).then(() => {
                        c.ordem = index
                    })
                )
            })
            if (promises.length === 0) return
            this.salvandoOrdem = true
            Promise.all(promises)
                .then(() => {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Ordem atualizada.')
                    }
                })
                .catch(() => {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Erro ao salvar ordem. Recarregue a página.')
                    }
                    this.listar()
                })
                .finally(() => {
                    this.salvandoOrdem = false
                })
        }
    }
}
</script>

<style scoped>
.requisicao-vaga-campos-custom .cards-lista {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.requisicao-vaga-campos-custom .drag-handle {
    cursor: grab;
    padding: 0.25rem 0.5rem;
    margin: -0.25rem 0.25rem -0.25rem -0.25rem;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    user-select: none;
}

.requisicao-vaga-campos-custom .drag-handle:active {
    cursor: grabbing;
}

.requisicao-vaga-campos-custom .drag-handle:hover {
    background: rgba(0, 123, 255, 0.1);
}

.requisicao-vaga-campos-custom .sortable-ghost {
    opacity: 0.5;
    background: #f8f9fa;
}

.requisicao-vaga-campos-custom .sortable-chosen {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.25);
}

.requisicao-vaga-campos-custom .solicitacao-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.requisicao-vaga-campos-custom .solicitacao-card:hover {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    border-color: #007bff;
    transform: translateY(-2px);
}

.requisicao-vaga-campos-custom .card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    margin-bottom: 0.75rem;
}

.requisicao-vaga-campos-custom .card-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    overflow: hidden;
}

.requisicao-vaga-campos-custom .card-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.requisicao-vaga-campos-custom .badge-id {
    background: #174257;
    color: white;
    padding: 0.25rem 0.625rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.75rem;
    white-space: nowrap;
    flex-shrink: 0;
}

.requisicao-vaga-campos-custom .badge-tipo {
    background: #6c757d;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-right: 0.5rem;
    flex-shrink: 0;
}

.requisicao-vaga-campos-custom .colaborador-principal {
    display: flex;
    align-items: center;
    font-size: 0.938rem;
    color: #212529;
    overflow: hidden;
}

.requisicao-vaga-campos-custom .colaborador-principal strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.requisicao-vaga-campos-custom .data-info {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.requisicao-vaga-campos-custom .card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    margin-bottom: 0.75rem;
}

.requisicao-vaga-campos-custom .card-details-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.requisicao-vaga-campos-custom .detail-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.813rem;
    min-width: 0;
}

.requisicao-vaga-campos-custom .detail-item i {
    flex-shrink: 0;
    font-size: 0.875rem;
}

.requisicao-vaga-campos-custom .detail-label {
    font-weight: 500;
    color: #6c757d;
    white-space: nowrap;
}

.requisicao-vaga-campos-custom .detail-value {
    color: #212529;
    font-weight: 400;
}

.requisicao-vaga-campos-custom .detail-item-full {
    flex: 1 1 100%;
}

.requisicao-vaga-campos-custom .btn-actions-compact {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #495057;
    transition: all 0.2s ease;
    text-decoration: none;
    flex-shrink: 0;
}

.requisicao-vaga-campos-custom .btn-actions-compact:hover {
    background: #007bff;
    border-color: #007bff;
    color: white;
    transform: rotate(90deg);
}

.requisicao-vaga-campos-custom .dropdown-menu-custom {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: none;
    padding: 0.5rem 0;
}

.requisicao-vaga-campos-custom .dropdown-menu-custom .dropdown-item {
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.requisicao-vaga-campos-custom .dropdown-menu-custom .dropdown-item:hover {
    background: #f8f9fa;
    color: #007bff;
    padding-left: 1.5rem;
}

@media (max-width: 768px) {
    .requisicao-vaga-campos-custom .card-header-row {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .requisicao-vaga-campos-custom .card-left {
        width: 100%;
    }
    .requisicao-vaga-campos-custom .card-right {
        width: 100%;
        justify-content: space-between;
    }
    .requisicao-vaga-campos-custom .card-details-row {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>
