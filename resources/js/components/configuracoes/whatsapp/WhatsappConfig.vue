<template>
    <div class="container-fluid">
        <div class="alert alert-warning" v-if="!whatsappLiberado">
            <i class="fa fa-info-circle"></i>
            O envio de WhatsApp não está habilitado para esta empresa. Entre em contato com o administrador MyBP.
            Os campos abaixo estão em modo somente leitura.
        </div>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link" :class="{ active: aba === 'contato' }" href="#" @click.prevent="aba = 'contato'">Dados de contato</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{ active: aba === 'templates' }" href="#" @click.prevent="aba = 'templates'">Templates</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{ active: aba === 'notificacoes' }" href="#" @click.prevent="aba = 'notificacoes'">Módulos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{ active: aba === 'usuarios' }" href="#" @click.prevent="aba = 'usuarios'">Usuários</a>
            </li>
        </ul>

        <preload v-if="loading" class="text-center" />

        <div v-show="!loading && aba === 'contato'">
            <fieldset>
                <legend>Dados de contato para mensagens</legend>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nome de exibição</label>
                            <input v-model="form.nome_exibicao" class="form-control form-control-sm" :disabled="readonly" />
                            <small class="text-muted">Fallback: {{ resolved.nome_exibicao }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Telefone de contato</label>
                            <input v-model="form.telefone_contato" class="form-control form-control-sm" :disabled="readonly" />
                            <small class="text-muted">Fallback: {{ resolved.telefone_contato }}</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Endereço completo</label>
                            <textarea v-model="form.endereco_completo" class="form-control form-control-sm" rows="2" :disabled="readonly"></textarea>
                            <small class="text-muted">Fallback: {{ resolved.endereco_completo }}</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Texto de assinatura</label>
                            <whatsapp-template-editor
                                v-model="form.texto_assinatura"
                                :disabled="readonly"
                                :maxlength="2000"
                                :rows="3"
                            />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info py-2 mb-0">
                            <small>Todas as mensagens incluem automaticamente o rodapé padrão MyBP.</small>
                        </div>
                    </div>
                </div>
                <button v-if="!readonly" class="btn btn-success btn-sm mt-2" :disabled="salvando" @click="salvarConfig">
                    <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i> Salvar
                </button>
            </fieldset>
        </div>

        <div v-show="!loading && aba === 'notificacoes'">
            <fieldset>
                <legend>Locais de envio habilitados</legend>
                <p class="text-muted small">
                    Defina em quais módulos do sistema a empresa pode enviar notificações por WhatsApp.
                    Usuários com a habilidade de preferências podem escolher individualmente o que receber.
                </p>
                <div
                    v-for="item in modulosHabilitados"
                    :key="item.modulo"
                    class="custom-control custom-switch mb-2"
                >
                    <input
                        :id="`mod-${slug(item.modulo)}`"
                        type="checkbox"
                        class="custom-control-input"
                        v-model="item.habilitado"
                        :disabled="readonly"
                    >
                    <label class="custom-control-label" :for="`mod-${slug(item.modulo)}`">
                        {{ item.modulo }}
                        <small class="text-muted d-block">{{ tiposModulo(item.tipos) }}</small>
                    </label>
                </div>
                <button v-if="!readonly" class="btn btn-success btn-sm mt-2" :disabled="salvando" @click="salvarModulos">
                    <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i> Salvar notificações
                </button>
            </fieldset>
        </div>

        <div v-show="!loading && aba === 'usuarios'">
            <whatsapp-notificacoes-usuarios
                :readonly="readonly"
                :whatsapp-liberado-prop="whatsappLiberado"
                :empresa-id="empresaId"
            />
        </div>

        <div v-show="!loading && aba === 'templates'" class="row">
            <div class="col-md-4">
                <div class="list-group">
                    <template v-for="(items, modulo) in templatesPorModulo" :key="modulo">
                        <div class="list-group-item list-group-item-secondary py-1"><small><strong>{{ modulo }}</strong></small></div>
                        <button
                            v-for="tpl in items"
                            :key="tpl.tipo_mensagem"
                            type="button"
                            class="list-group-item list-group-item-action py-2"
                            :class="{ active: tipoSelecionado === tpl.tipo_mensagem }"
                            @click="selecionarTemplate(tpl)"
                        >
                            {{ tpl.label }}
                            <span v-if="tpl.customizado" class="badge badge-info float-right">Custom</span>
                            <span v-else class="badge badge-secondary float-right">Padrão</span>
                        </button>
                    </template>
                </div>
            </div>
            <div class="col-md-8" v-if="templateAtual">
                <fieldset>
                    <legend>{{ templateAtual.label }}</legend>
                    <div class="mb-2">
                        <small class="text-muted">Placeholders disponíveis (clique para inserir):</small>
                        <div class="mt-1">
                            <button
                                v-for="ph in templateAtual.placeholders"
                                :key="ph"
                                type="button"
                                class="btn btn-outline-secondary btn-xs mr-1 mb-1"
                                style="font-size: 0.75rem"
                                :disabled="readonly"
                                @click="inserirPlaceholder(ph)"
                            >
                                {{ formatPlaceholder(ph) }}
                            </button>
                        </div>
                    </div>
                    <whatsapp-template-editor
                        v-model="corpoEdicao"
                        :disabled="readonly"
                        :maxlength="maxCorpo"
                        :rows="14"
                    />
                    <div class="mt-2">
                        <button v-if="!readonly" class="btn btn-success btn-sm mr-1" :disabled="salvando" @click="salvarTemplate">
                            <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i> Salvar template
                        </button>
                        <button v-if="!readonly && templateAtual.customizado" class="btn btn-warning btn-sm mr-1" :disabled="salvando" @click="restaurarPadrao">
                            Restaurar padrão MyBP
                        </button>
                        <button class="btn btn-info btn-sm" @click="previewTemplateAtual">Preview</button>
                    </div>
                </fieldset>
            </div>
        </div>

        <whatsapp-preview-modal
            v-model="modalPreview"
            :tipo-mensagem="previewTipoModal"
            :corpo-edicao="previewCorpoModal"
            :contexto="{}"
            :empresa-id="empresaId"
        />
    </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted, watch, toRef } from 'vue'
import axios from 'axios'
import WhatsappPreviewModal from './WhatsappPreviewModal.vue'
import WhatsappTemplateEditor from './WhatsappTemplateEditor.vue'
import WhatsappNotificacoesUsuarios from './WhatsappNotificacoesUsuarios.vue'

const BASE = '/g/configuracoes/whatsapp'

export default defineComponent({
    name: 'WhatsappConfig',
    components: { WhatsappPreviewModal, WhatsappTemplateEditor, WhatsappNotificacoesUsuarios },
    props: {
        empresaId: { type: [Number, String], default: null },
    },
    setup(props) {
        const empresaId = toRef(props, 'empresaId')
        const loading = ref(true)
        const salvando = ref(false)
        const aba = ref('contato')
        const readonly = ref(false)
        const whatsappLiberado = ref(false)
        const resolved = ref({})
        const form = ref({
            nome_exibicao: '',
            telefone_contato: '',
            endereco_completo: '',
            texto_assinatura: '',
        })
        const templates = ref([])
        const modulosHabilitados = ref([])
        const tipoSelecionado = ref('')
        const corpoEdicao = ref('')
        const maxCorpo = ref(4096)
        const modalPreview = ref(false)
        const previewTipoModal = ref('')
        const previewCorpoModal = ref(null)

        const templateAtual = computed(() => templates.value.find((t) => t.tipo_mensagem === tipoSelecionado.value) || null)

        const templatesPorModulo = computed(() => {
            const grupos = {}
            templates.value.forEach((tpl) => {
                if (!grupos[tpl.modulo]) grupos[tpl.modulo] = []
                grupos[tpl.modulo].push(tpl)
            })
            return grupos
        })

        const empresaParams = () => (empresaId.value ? { empresa_id: Number(empresaId.value) } : {})

        const carregar = async () => {
            loading.value = true
            try {
                const params = empresaParams()
                const [configRes, templatesRes] = await Promise.all([
                    axios.get(`${BASE}/config`, { params }),
                    axios.get(`${BASE}/templates`, { params }),
                ])
                whatsappLiberado.value = configRes.data.whatsapp_liberado
                readonly.value = configRes.data.readonly
                resolved.value = configRes.data.config.resolved || {}
                const cfg = configRes.data.config
                form.value = {
                    nome_exibicao: cfg.nome_exibicao || '',
                    telefone_contato: cfg.telefone_contato || '',
                    endereco_completo: cfg.endereco_completo || '',
                    texto_assinatura: cfg.texto_assinatura || '',
                }
                modulosHabilitados.value = (cfg.modulos_habilitados || []).map((item) => ({
                    ...item,
                    habilitado: !!item.habilitado,
                }))
                templates.value = templatesRes.data
                if (templates.value.length && !tipoSelecionado.value) {
                    selecionarTemplate(templates.value[0])
                }
            } finally {
                loading.value = false
            }
        }

        const selecionarTemplate = (tpl) => {
            tipoSelecionado.value = tpl.tipo_mensagem
            corpoEdicao.value = tpl.corpo || tpl.corpo_padrao || ''
        }

        const inserirPlaceholder = (ph) => {
            const token = `{{${ph}}}`
            corpoEdicao.value += (corpoEdicao.value ? '\n' : '') + token
        }

        const formatPlaceholder = (ph) => `{{${ph}}}`

        const slug = (texto) => String(texto).toLowerCase().replace(/[^a-z0-9]+/g, '-')

        const tiposModulo = (tipos) => (tipos || []).join(', ')

        const salvarModulos = async () => {
            salvando.value = true
            try {
                await axios.put(`${BASE}/modulos`, {
                    modulos_habilitados: modulosHabilitados.value.map((item) => ({
                        modulo: item.modulo,
                        habilitado: !!item.habilitado,
                    })),
                }, { params: empresaParams() })
                await carregar()
                alert('Notificações salvas com sucesso.')
            } catch (e) {
                alert(e.response?.data?.message || 'Erro ao salvar notificações.')
            } finally {
                salvando.value = false
            }
        }

        const salvarConfig = async () => {
            salvando.value = true
            try {
                await axios.put(`${BASE}/config`, form.value, { params: empresaParams() })
                await carregar()
                alert('Configuração salva com sucesso.')
            } catch (e) {
                alert(e.response?.data?.message || 'Erro ao salvar configuração.')
            } finally {
                salvando.value = false
            }
        }

        const salvarTemplate = async () => {
            if (!tipoSelecionado.value) return
            salvando.value = true
            try {
                await axios.put(`${BASE}/templates/${tipoSelecionado.value}`, { corpo: corpoEdicao.value, ativo: true }, { params: empresaParams() })
                await carregar()
                selecionarTemplate(templates.value.find((t) => t.tipo_mensagem === tipoSelecionado.value))
                alert('Template salvo com sucesso.')
            } catch (e) {
                alert(e.response?.data?.message || 'Erro ao salvar template.')
            } finally {
                salvando.value = false
            }
        }

        const restaurarPadrao = async () => {
            if (!tipoSelecionado.value || !confirm('Restaurar template padrão MyBP?')) return
            salvando.value = true
            try {
                await axios.delete(`${BASE}/templates/${tipoSelecionado.value}`, { params: empresaParams() })
                await carregar()
                selecionarTemplate(templates.value.find((t) => t.tipo_mensagem === tipoSelecionado.value))
            } catch (e) {
                alert(e.response?.data?.message || 'Erro ao restaurar template.')
            } finally {
                salvando.value = false
            }
        }

        const previewTemplateAtual = () => {
            if (!tipoSelecionado.value) return
            previewTipoModal.value = tipoSelecionado.value
            previewCorpoModal.value = corpoEdicao.value
            modalPreview.value = true
        }

        onMounted(carregar)

        watch(empresaId, () => {
            tipoSelecionado.value = ''
            carregar()
        })

        return {
            loading, salvando, aba, readonly, whatsappLiberado, resolved, form, empresaId,
            templates, modulosHabilitados, tipoSelecionado, corpoEdicao, maxCorpo,
            templateAtual, templatesPorModulo,
            modalPreview, previewTipoModal, previewCorpoModal,
            selecionarTemplate, inserirPlaceholder, formatPlaceholder, slug, tiposModulo,
            salvarConfig, salvarModulos, salvarTemplate,
            restaurarPadrao, previewTemplateAtual,
        }
    },
})
</script>
