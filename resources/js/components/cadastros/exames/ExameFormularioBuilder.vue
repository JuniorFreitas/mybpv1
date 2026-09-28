<template>
    <div class="exame-form-builder">
        <div class="exame-form-builder__intro alert alert-light border mb-3">
            <div class="d-flex flex-wrap align-items-start justify-content-between">
                <div>
                    <strong>{{ tituloContexto }}</strong>
                    <p class="mb-0 text-muted small mt-1">{{ descricaoContexto }}</p>
                </div>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary mt-2 mt-md-0"
                    :disabled="!formulario"
                    @click="mostrarPreview = !mostrarPreview"
                >
                    <i class="fa fa-eye"></i>
                    {{ mostrarPreview ? 'Ocultar preview' : 'Ver preview' }}
                </button>
            </div>
        </div>

        <!-- Toolbar formulário -->
        <div class="exame-form-builder__toolbar card mb-3">
            <div class="card-body py-3">
                <div class="row align-items-end">
                    <div class="col-12 col-lg-5">
                        <label class="mybp-label" for="select-formulario-exame">Formulário</label>
                        <select
                            id="select-formulario-exame"
                            class="form-control form-control-sm"
                            v-model="formularioId"
                            :disabled="carregando"
                            @change="onTrocarFormulario"
                        >
                            <option :value="null">Selecione um formulário...</option>
                            <option v-for="f in formularios" :key="f.id" :value="f.id">
                                {{ f.titulo }} (#{{ f.id }})
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-7 mt-2 mt-lg-0">
                        <div class="d-flex flex-wrap">
                            <button type="button" class="btn btn-sm btn-secondary mr-2 mb-1" :disabled="carregando" @click="abrirModalFormulario()">
                                <i class="fa fa-plus"></i> Novo formulário
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary mr-2 mb-1"
                                :disabled="!formulario || carregando"
                                @click="abrirModalFormulario(formulario)"
                            >
                                <i class="fa fa-edit"></i> Renomear
                            </button>
                            <button type="button" class="btn btn-sm btn-success mb-1" :disabled="carregando" @click="recarregarTudo">
                                <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vínculo com tipos -->
        <div class="exame-form-builder__vinculo card mb-3" v-if="formularioId">
            <div class="card-body py-3">
                <label class="mybp-label mb-1">Usar este formulário em quais tipos de exame?</label>
                <p class="text-muted small mb-2">
                    Tipos já vinculados ficam destacados. Ao vincular um tipo global, o sistema cria uma cópia da empresa.
                </p>
                <div class="d-flex flex-wrap mb-2" v-if="tiposVinculadosAoForm.length">
                    <span
                        v-for="t in tiposVinculadosAoForm"
                        :key="'v-' + t.id"
                        class="badge badge-primary mr-1 mb-1 exame-form-builder__chip"
                    >
                        {{ t.label }}
                    </span>
                </div>
                <div class="row align-items-end">
                    <div class="col-12 col-md-8">
                        <select class="form-control form-control-sm" v-model="tipoVinculoId">
                            <option :value="null">Selecione um tipo para vincular...</option>
                            <option v-for="t in tipos" :key="t.id" :value="t.id">
                                {{ t.label }}{{ estaVinculado(t) ? ' (já vinculado)' : '' }}{{ t.empresa_id ? '' : ' · global' }}
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 mt-2 mt-md-0">
                        <button
                            type="button"
                            class="btn btn-sm btn-primary btn-block"
                            :disabled="!tipoVinculoId || vinculando"
                            @click="vincularTipo"
                        >
                            <i :class="vinculando ? 'fa fa-spinner fa-spin' : 'fa fa-link'"></i>
                            Vincular tipo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <preload v-if="carregando" class="text-center my-4"></preload>

        <div class="alert alert-warning" v-if="!carregando && !formularioId">
            <i class="fa fa-info-circle"></i>
            Selecione um formulário existente ou crie um novo para começar a montar as seções e campos.
        </div>

        <div class="row" v-if="formulario && !carregando">
            <div :class="mostrarPreview ? 'col-12 col-xl-7' : 'col-12'">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">{{ formulario.titulo }}</h5>
                        <small class="text-muted">{{ totalCampos }} campo(s) em {{ setores.length }} seção(ões)</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success" @click="abrirModalSetor()">
                        <i class="fa fa-plus"></i> Nova seção
                    </button>
                </div>

                <div class="alert alert-light border" v-if="!setores.length">
                    Nenhuma seção ainda.
                    <button type="button" class="btn btn-link btn-sm p-0 align-baseline" @click="abrirModalSetor()">
                        Criar a primeira seção
                    </button>
                </div>

                <div v-for="setor in setores" :key="setor.id" class="exame-form-builder__setor card mb-3">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center py-2">
                        <div class="d-flex align-items-center">
                            <strong class="mr-2">{{ setor.nome }}</strong>
                            <span class="badge badge-secondary">{{ (setor.alternativas || []).length }}</span>
                        </div>
                        <div class="btn-group btn-group-sm mt-1 mt-md-0">
                            <button type="button" class="btn btn-outline-secondary" title="Renomear seção" @click="abrirModalSetor(setor)">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-primary" @click="abrirCampo(setor)">
                                <i class="fa fa-plus"></i> Campo
                            </button>
                        </div>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item text-muted" v-if="!(setor.alternativas || []).length">
                            Nenhum campo nesta seção.
                            <a href="#" class="ml-1" @click.prevent="abrirCampo(setor)">Adicionar campo</a>
                        </div>

                        <draggable
                            v-if="(setor.alternativas || []).length"
                            :model-value="setor.alternativas"
                            item-key="id"
                            handle=".exame-form-builder__drag"
                            :animation="180"
                            @update:model-value="(val) => (setor.alternativas = val)"
                            @end="() => aoReordenarCampos(setor)"
                        >
                            <template #item="{ element: alt }">
                                <div class="list-group-item exame-form-builder__campo">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-start flex-grow-1 min-w-0">
                                            <span class="exame-form-builder__drag mr-2 text-muted" title="Arrastar para reordenar">
                                                <i class="fas fa-grip-vertical"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="d-flex flex-wrap align-items-center">
                                                    <span class="badge badge-light border mr-1">{{ tipoLabel(alt.tipo) }}</span>
                                                    <strong class="mr-2 text-truncate">{{ alt.nome }}</strong>
                                                    <span class="badge badge-warning mr-1" v-if="alt.pivot?.obrigatorio">obrigatório</span>
                                                    <span class="badge badge-info mr-1" v-if="alt.chave_canonica">{{ alt.chave_canonica }}</span>
                                                </div>
                                                <small class="text-muted" v-if="alt.tipo === 'select'">
                                                    {{ (alt.opcoes || []).length }} opção(ões)
                                                </small>
                                            </div>
                                        </div>
                                        <div class="dropdown" :class="{ show: isDropdownOpen(alt.id) }">
                                            <a
                                                class="mybp-btn-acoes-compact"
                                                href="#"
                                                role="button"
                                                @click.prevent.stop="toggleDropdown(alt.id)"
                                            >
                                                <i class="fas fa-ellipsis-v"></i>
                                            </a>
                                            <div
                                                class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                                :class="{ show: isDropdownOpen(alt.id) }"
                                                @click="fecharDropdown"
                                            >
                                                <a class="dropdown-item" href="javascript://" @click.prevent="editarCampo(setor, alt)">
                                                    <i class="fa fa-edit mr-1"></i> Editar
                                                </a>
                                                <a class="dropdown-item text-danger" href="javascript://" @click.prevent="removerCampo(setor, alt)">
                                                    <i class="fa fa-trash mr-1"></i> Remover
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5" v-if="mostrarPreview">
                <div class="exame-form-builder__preview card sticky-top">
                    <div class="card-header py-2">
                        <strong>Preview</strong>
                        <small class="text-muted d-block">Como o usuário verá no encaminhamento/resultado</small>
                    </div>
                    <div class="card-body">
                        <formulario-default :model="previewModel" :mostra_titulo="true" :key="'prev-' + formularioId + '-' + previewKey"></formulario-default>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal formulário -->
        <modal :id="'janelaFormMeta_' + contexto" :titulo="tituloModalForm" :size="50" :mostrar-botao-fechar-no-rodape="false" ref="modalForm">
            <template #conteudo>
                <p class="mybp-campo-obrigatorio-legenda mb-3">
                    Campos com <span class="text-danger">*</span> são obrigatórios.
                </p>
                <div class="form-group">
                    <label class="mybp-label">Título <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" v-model="formMeta.titulo" placeholder="Ex: Exames Admissional" />
                </div>
                <div class="form-group mb-0">
                    <label class="mybp-label">Descrição</label>
                    <textarea class="form-control form-control-sm" rows="2" v-model="formMeta.descricao" placeholder="Opcional"></textarea>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-secondary" @click="$refs.modalForm?.fecharModal?.()">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvarFormulario">
                    <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i> Salvar
                </button>
            </template>
        </modal>

        <!-- Modal seção -->
        <modal :id="'janelaSetorMeta_' + contexto" :titulo="tituloModalSetor" :size="50" :mostrar-botao-fechar-no-rodape="false" ref="modalSetor">
            <template #conteudo>
                <div class="form-group mb-0">
                    <label class="mybp-label">Nome da seção <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" v-model="setorMeta.nome" placeholder="Ex: Riscos / Exames / Resultado" />
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-secondary" @click="$refs.modalSetor?.fecharModal?.()">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvarSetor">
                    <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i> Salvar
                </button>
            </template>
        </modal>

        <!-- Modal campo -->
        <modal :id="'janelaCampoExame_' + contexto" :titulo="tituloCampo" :size="70" :mostrar-botao-fechar-no-rodape="false" ref="modalCampo">
            <template #conteudo>
                <p class="mybp-campo-obrigatorio-legenda mb-3">
                    Campos com <span class="text-danger">*</span> são obrigatórios.
                </p>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="mybp-label">Label <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" v-model="campo.nome" placeholder="Nome exibido ao usuário" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="mybp-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" v-model="campo.tipo">
                                <option value="text">Texto</option>
                                <option value="textarea">Caixa de texto</option>
                                <option value="number">Número</option>
                                <option value="float">Decimal</option>
                                <option value="checkbox">Sim / Não (checkbox)</option>
                                <option value="select">Lista (select)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12" v-if="campo.tipo === 'select'">
                        <div class="form-group">
                            <label class="mybp-label">Opções (uma por linha) <span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="4" v-model="opcoesTexto" placeholder="Opção 1&#10;Opção 2&#10;Opção 3"></textarea>
                            <small class="text-muted">Opções antigas removidas da lista não são apagadas do histórico.</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-6" v-if="contexto === 'resultado'">
                        <div class="form-group">
                            <label class="mybp-label">Chave canônica (relatórios / WhatsApp)</label>
                            <select class="form-control form-control-sm" v-model="campo.chave_canonica">
                                <option :value="null">Nenhuma</option>
                                <option value="result">result — Resultado ASO</option>
                                <option value="pendencias">pendencias</option>
                                <option value="aprovado">aprovado</option>
                                <option value="trabalho_altura">trabalho_altura</option>
                                <option value="espacao_confinado">espacao_confinado</option>
                                <option value="observacoes">observacoes</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" :id="'campo-obrigatorio-' + contexto" v-model="campo.obrigatorio" />
                            <label class="custom-control-label" :for="'campo-obrigatorio-' + contexto">Campo obrigatório</label>
                        </div>
                    </div>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-secondary" @click="$refs.modalCampo?.fecharModal?.()">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvarCampo">
                    <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i> Salvar campo
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import draggable from 'vuedraggable'
import FormularioDefault from '../../FormularioDefault.vue'

export default defineComponent({
    name: 'ExameFormularioBuilder',
    components: { FormularioDefault, draggable },
    props: {
        contexto: {
            type: String,
            default: 'encaminhamento'
        }
    },
    data() {
        return {
            formularios: [],
            formularioId: null,
            formulario: null,
            carregando: false,
            salvando: false,
            vinculando: false,
            mostrarPreview: true,
            previewKey: 0,
            tipos: [],
            tipoVinculoId: null,
            dropdownAbertoKey: null,
            tituloCampo: 'Campo',
            campo: {
                id: null,
                nome: '',
                tipo: 'text',
                obrigatorio: false,
                chave_canonica: null,
                setor_id: null
            },
            opcoesTexto: '',
            previewModel: { formulario: null, respostas: {} },
            formMeta: { id: null, titulo: '', descricao: '' },
            setorMeta: { id: null, nome: '' },
            tituloModalForm: 'Novo formulário',
            tituloModalSetor: 'Nova seção'
        }
    },
    computed: {
        tituloContexto() {
            return this.contexto === 'resultado'
                ? 'Formulário de resultado SESMT'
                : 'Formulário de encaminhamento'
        },
        descricaoContexto() {
            return this.contexto === 'resultado'
                ? 'Monte os campos do resultado do exame (apto, pendências, observações…). Use chave canônica para manter relatórios e WhatsApp.'
                : 'Monte as seções e campos exibidos no encaminhamento do colaborador à clínica (quando não usa só PCMSO).'
        },
        setores() {
            return this.formulario?.setores || this.formulario?.Setores || []
        },
        totalCampos() {
            return this.setores.reduce((acc, s) => acc + ((s.alternativas || []).length), 0)
        },
        tiposVinculadosAoForm() {
            if (!this.formularioId) return []
            const id = Number(this.formularioId)
            return this.tipos.filter((t) => {
                const fk =
                    this.contexto === 'resultado'
                        ? t.formulario_resultado_id
                        : t.formulario_encaminhamento_id
                return Number(fk) === id
            })
        }
    },
    mounted() {
        document.addEventListener('click', this.fecharDropdown)
        this.recarregarTudo()
    },
    beforeUnmount() {
        document.removeEventListener('click', this.fecharDropdown)
        document.body.classList.remove('modal-open')
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove())
    },
    methods: {
        tipoLabel(tipo) {
            const map = {
                text: 'Texto',
                textarea: 'Textarea',
                number: 'Número',
                float: 'Decimal',
                checkbox: 'Checkbox',
                select: 'Select'
            }
            return map[tipo] || tipo
        },
        campoVazio() {
            return {
                id: null,
                nome: '',
                tipo: 'text',
                obrigatorio: false,
                chave_canonica: null,
                setor_id: null
            }
        },
        estaVinculado(t) {
            const id = Number(this.formularioId)
            const fk =
                this.contexto === 'resultado' ? t.formulario_resultado_id : t.formulario_encaminhamento_id
            return Number(fk) === id
        },
        toggleDropdown(id) {
            const key = `campo:${id}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(id) {
            return this.dropdownAbertoKey === `campo:${id}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        async recarregarTudo() {
            await Promise.all([this.carregarLista(false), this.carregarTipos()])
            if (this.formularioId) {
                await this.carregarFormulario()
            }
        },
        async carregarLista(autoSelect = true) {
            try {
                const { data } = await axios.get(`${URL_ADMIN}/cadastro/formularios-exame`)
                this.formularios = data || []
                if (!autoSelect) return
                if (this.formularioId && this.formularios.some((f) => f.id === this.formularioId)) {
                    await this.carregarFormulario()
                    return
                }
                const preferido = this.contexto === 'resultado' ? 'Resultado SESMT' : 'Exames'
                const match = this.formularios.find((f) => f.titulo === preferido) || this.formularios[0]
                if (match) {
                    this.formularioId = match.id
                    await this.carregarFormulario()
                }
            } catch (e) {
                toastr.error('Não foi possível listar formulários.')
            }
        },
        async carregarTipos() {
            try {
                // lista completa via atualizar (ativos + formularios vinculados)
                const { data } = await axios.post(`${URL_ADMIN}/cadastro/exame-tipos/atualizar`, {
                    campoStatus: 'true',
                    pages: 200,
                    page: 1
                })
                this.tipos = data?.dados?.items || []
                if (!this.tipos.length) {
                    const ativos = await axios.get(`${URL_ADMIN}/cadastro/exame-tipos/ativos`)
                    this.tipos = ativos.data || []
                }
            } catch (e) {
                try {
                    const { data } = await axios.get(`${URL_ADMIN}/cadastro/exame-tipos/ativos`)
                    this.tipos = data || []
                } catch (err) {
                    this.tipos = []
                }
            }
        },
        async onTrocarFormulario() {
            this.tipoVinculoId = null
            await this.carregarFormulario()
        },
        async carregarFormulario() {
            if (!this.formularioId) {
                this.formulario = null
                return
            }
            this.carregando = true
            try {
                const { data } = await axios.get(`${URL_ADMIN}/cadastro/formularios-exame/${this.formularioId}`)
                this.formulario = this.normalizarFormPreview(data)
                this.previewModel = {
                    formulario: this.formulario,
                    respostas: {}
                }
                this.previewKey++
            } catch (e) {
                this.formulario = null
                toastr.error(e?.response?.data?.msg || 'Erro ao carregar formulário.')
            } finally {
                this.carregando = false
            }
        },
        normalizarFormPreview(data) {
            const setores = (data.setores || data.Setores || []).map((s) => ({
                ...s,
                alternativas: (s.alternativas || s.Alternativas || []).map((a) => ({
                    ...a,
                    opcoes: a.opcoes || a.Opcoes || [],
                    pivot: a.pivot || { obrigatorio: false, min: null, max: null }
                }))
            }))
            return { ...data, setores, titulo: data.titulo }
        },
        abrirModalFormulario(form = null) {
            this.tituloModalForm = form ? 'Renomear formulário' : 'Novo formulário'
            this.formMeta = {
                id: form?.id || null,
                titulo: form?.titulo || (this.contexto === 'resultado' ? 'Resultado SESMT' : 'Exames'),
                descricao: form?.descricao || ''
            }
            this.$nextTick(() => this.$refs.modalForm?.abrirModal?.())
        },
        async salvarFormulario() {
            if (!this.formMeta.titulo?.trim()) {
                return toastr.warning('Informe o título.')
            }
            this.salvando = true
            try {
                if (this.formMeta.id) {
                    await axios.put(`${URL_ADMIN}/cadastro/formularios-exame/${this.formMeta.id}`, {
                        titulo: this.formMeta.titulo.trim(),
                        descricao: this.formMeta.descricao
                    })
                    toastr.success('Formulário atualizado.')
                } else {
                    const { data } = await axios.post(`${URL_ADMIN}/cadastro/formularios-exame`, {
                        titulo: this.formMeta.titulo.trim(),
                        descricao: this.formMeta.descricao
                    })
                    this.formularioId = data.id
                    toastr.success('Formulário criado.')
                }
                this.$refs.modalForm?.fecharModal?.()
                await this.carregarLista(false)
                await this.carregarFormulario()
            } catch (e) {
                toastr.error(e?.response?.data?.msg || 'Erro ao salvar formulário.')
            } finally {
                this.salvando = false
            }
        },
        abrirModalSetor(setor = null) {
            this.tituloModalSetor = setor ? 'Renomear seção' : 'Nova seção'
            this.setorMeta = { id: setor?.id || null, nome: setor?.nome || '' }
            this.$nextTick(() => this.$refs.modalSetor?.abrirModal?.())
        },
        async salvarSetor() {
            if (!this.setorMeta.nome?.trim()) {
                return toastr.warning('Informe o nome da seção.')
            }
            this.salvando = true
            try {
                if (this.setorMeta.id) {
                    await axios.put(`${URL_ADMIN}/cadastro/formularios-exame/setores/${this.setorMeta.id}`, {
                        nome: this.setorMeta.nome.trim()
                    })
                    toastr.success('Seção atualizada.')
                } else {
                    await axios.post(`${URL_ADMIN}/cadastro/formularios-exame/${this.formularioId}/setores`, {
                        nome: this.setorMeta.nome.trim()
                    })
                    toastr.success('Seção criada.')
                }
                this.$refs.modalSetor?.fecharModal?.()
                await this.carregarFormulario()
            } catch (e) {
                toastr.error(e?.response?.data?.msg || 'Erro ao salvar seção.')
            } finally {
                this.salvando = false
            }
        },
        abrirCampo(setor) {
            this.fecharDropdown()
            this.campo = this.campoVazio()
            this.campo.setor_id = setor.id
            this.opcoesTexto = ''
            this.tituloCampo = 'Novo campo'
            this.$nextTick(() => this.$refs.modalCampo?.abrirModal?.())
        },
        editarCampo(setor, alt) {
            this.fecharDropdown()
            this.campo = {
                id: alt.id,
                nome: alt.nome,
                tipo: alt.tipo,
                obrigatorio: !!(alt.pivot && alt.pivot.obrigatorio),
                chave_canonica: alt.chave_canonica || null,
                setor_id: setor.id
            }
            const ops = alt.opcoes || alt.Opcoes || []
            this.opcoesTexto = ops.map((o) => o.label).join('\n')
            this.tituloCampo = 'Editar campo'
            this.$nextTick(() => this.$refs.modalCampo?.abrirModal?.())
        },
        async salvarCampo() {
            if (!this.campo.nome?.trim()) {
                return toastr.warning('Informe o label.')
            }
            if (this.campo.tipo === 'select') {
                const ops = this.opcoesTexto.split('\n').map((l) => l.trim()).filter(Boolean)
                if (!ops.length) {
                    return toastr.warning('Informe ao menos uma opção para o select.')
                }
            }
            const payload = {
                ...this.campo,
                opcoes:
                    this.campo.tipo === 'select'
                        ? this.opcoesTexto.split('\n').map((l) => l.trim()).filter(Boolean)
                        : []
            }
            this.salvando = true
            try {
                if (this.campo.id) {
                    await axios.put(`${URL_ADMIN}/cadastro/formularios-exame/campos/${this.campo.id}`, payload)
                } else {
                    await axios.post(
                        `${URL_ADMIN}/cadastro/formularios-exame/setores/${this.campo.setor_id}/campos`,
                        payload
                    )
                }
                this.$refs.modalCampo?.fecharModal?.()
                await this.carregarFormulario()
                toastr.success('Campo salvo.')
            } catch (e) {
                toastr.error(e?.response?.data?.msg || 'Erro ao salvar campo.')
            } finally {
                this.salvando = false
            }
        },
        async removerCampo(setor, alt) {
            this.fecharDropdown()
            if (!confirm('Remover este campo do formulário?\nRespostas antigas continuam salvas no histórico.')) {
                return
            }
            try {
                await axios.delete(`${URL_ADMIN}/cadastro/formularios-exame/setores/${setor.id}/campos/${alt.id}`)
                await this.carregarFormulario()
                toastr.success('Campo removido.')
            } catch (e) {
                toastr.error(e?.response?.data?.msg || 'Erro ao remover.')
            }
        },
        async aoReordenarCampos(setor) {
            const ids = (setor.alternativas || []).map((a) => a.id)
            try {
                await axios.post(`${URL_ADMIN}/cadastro/formularios-exame/setores/${setor.id}/campos/reorder`, {
                    alternativa_ids: ids
                })
                this.previewKey++
            } catch (e) {
                toastr.error('Não foi possível salvar a ordem.')
                await this.carregarFormulario()
            }
        },
        async vincularTipo() {
            if (!this.tipoVinculoId || !this.formularioId) return
            this.vinculando = true
            try {
                const tipo = this.tipos.find((t) => Number(t.id) === Number(this.tipoVinculoId))
                const body = {
                    formulario_encaminhamento_id:
                        this.contexto === 'encaminhamento'
                            ? this.formularioId
                            : tipo?.formulario_encaminhamento_id ?? null,
                    formulario_resultado_id:
                        this.contexto === 'resultado'
                            ? this.formularioId
                            : tipo?.formulario_resultado_id ?? null
                }
                await axios.put(`${URL_ADMIN}/cadastro/exame-tipos/${this.tipoVinculoId}/vincular-formularios`, body)
                toastr.success('Tipo vinculado a este formulário.')
                this.tipoVinculoId = null
                await this.carregarTipos()
            } catch (e) {
                toastr.error(e?.response?.data?.msg || 'Erro ao vincular.')
            } finally {
                this.vinculando = false
            }
        }
    }
})
</script>

<style scoped>
.exame-form-builder__chip {
    font-weight: 500;
    padding: 0.35em 0.65em;
}
.exame-form-builder__drag {
    cursor: grab;
    padding: 0.25rem 0.35rem;
}
.exame-form-builder__drag:active {
    cursor: grabbing;
}
.exame-form-builder__campo:hover {
    background: #f8f9fa;
}
.exame-form-builder__preview.sticky-top {
    top: 1rem;
    z-index: 2;
}
.min-w-0 {
    min-width: 0;
}
</style>
