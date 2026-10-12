<template>
    <div class="carta-oferta-template">
        <div class="row">
            <div class="col-12 col-xl-7">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <span class="badge badge-light border text-uppercase mr-2" v-if="meta.versao">
                                    Versão {{ meta.versao }}
                                </span>
                                <span
                                    class="badge"
                                    :class="form.status === 'publicado' ? 'badge-success' : 'badge-secondary'"
                                >
                                    {{ form.status === 'publicado' ? 'Publicado' : 'Rascunho' }}
                                </span>
                                <span v-if="meta.publicado_versao" class="text-muted small ml-2">
                                    Publicado vigente: v{{ meta.publicado_versao }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="origemHistorico"
                            class="alert alert-warning py-2 small mb-3"
                        >
                            Editando a partir da <strong>v{{ origemHistorico.versao }}</strong>
                            ({{ origemHistorico.created_at || '—' }}).
                            Salve para criar uma <strong>nova versão</strong>.
                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-8">
                                <div class="form-group">
                                    <label class="mybp-label">Título <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" v-model="form.titulo" />
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label class="mybp-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm" v-model="form.status">
                                        <option value="publicado">Publicado</option>
                                        <option value="rascunho">Rascunho</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="mybp-label">Conteúdo da Carta Oferta <span class="text-danger">*</span></label>
                            <div v-if="carregando" class="text-muted small py-4 text-center">
                                <i class="fa fa-spinner fa-spin"></i> Carregando editor…
                            </div>
                            <!-- Monta TinyMCE só depois do conteúdo carregado (evita editor vazio) -->
                            <tiny-mce-editor
                                v-else-if="editorPronto"
                                ref="editor"
                                v-model="form.conteudo_html"
                                preset=""
                                :init="configEditor"
                            />
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-primary" :disabled="salvando || carregando" @click.prevent="salvar">
                                <i class="fa" :class="salvando ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                                Salvar
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" :disabled="salvando || carregando" @click.prevent="preview">
                                <i class="fa fa-eye"></i>
                                Preview
                            </button>
                            <button class="btn btn-sm btn-outline-primary" :disabled="salvando || carregando" @click.prevent="restaurarPadrao">
                                <i class="fa fa-undo"></i>
                                Restaurar padrão
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="card mb-3 sticky-top" style="top: 80px;">
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-tabs-sm mb-3">
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    href="#"
                                    :class="{ active: painelAtivo === 'ferramentas' }"
                                    @click.prevent="painelAtivo = 'ferramentas'"
                                >Ferramentas</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    href="#"
                                    :class="{ active: painelAtivo === 'historico' }"
                                    @click.prevent="painelAtivo = 'historico'"
                                >Histórico</a>
                            </li>
                        </ul>

                        <div v-show="painelAtivo === 'ferramentas'">
                            <h6 class="text-uppercase mb-2">Placeholders</h6>
                            <p class="text-muted small mb-3">Clique para copiar e colar no editor.</p>
                            <div class="d-flex flex-wrap mb-3" style="gap: 8px;">
                                <button
                                    v-for="item in placeholders"
                                    :key="item"
                                    type="button"
                                    class="btn btn-sm btn-light border"
                                    @click.prevent="copiarPlaceholder(item)"
                                    :title="'Copiar ' + item"
                                >
                                    {{ item }}
                                </button>
                            </div>

                            <hr>

                            <h6 class="text-uppercase mb-2">Preview</h6>
                            <div class="custom-control custom-switch mb-2">
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="previewDadosReais"
                                    v-model="previewForm.usar_dados_reais"
                                >
                                <label class="custom-control-label" for="previewDadosReais">
                                    Usar dados reais de um currículo
                                </label>
                            </div>

                            <div v-if="previewForm.usar_dados_reais" class="mb-2">
                                <label class="mybp-label">Currículo</label>
                                <autocomplete
                                    :caminho="caminhoCurriculos"
                                    v-model="previewForm.autocomplete_curriculo_label"
                                    placeholder="Buscar por nome ou CPF"
                                    @onselect="selecionaCurriculoPreview"
                                ></autocomplete>
                                <label class="mybp-label mt-2">Vaga (opcional)</label>
                                <autocomplete
                                    caminho="autocomplete/todas-vagas-ativas"
                                    v-model="previewForm.autocomplete_vaga_label"
                                    placeholder="Buscar vaga"
                                    @onselect="selecionaVagaPreview"
                                ></autocomplete>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <label class="mybp-label">Setor</label>
                                        <input type="text" class="form-control form-control-sm" v-model="previewForm.setor">
                                    </div>
                                    <div class="col-6">
                                        <label class="mybp-label">Salário</label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm text-right"
                                            v-model="previewForm.salario"
                                            v-mascara:dinheiro
                                            placeholder="0,00"
                                        >
                                    </div>
                                    <div class="col-6 mt-2">
                                        <div class="form-group mybp-filtro-campo mybp-modal-campo-data mb-0">
                                            <label class="mybp-label">Data início</label>
                                            <datepicker
                                                formsm
                                                label=""
                                                class="corrigiDatepicker"
                                                v-model="previewForm.data_inicio"
                                                placeholder="dd/mm/aaaa"
                                            ></datepicker>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="previewHtml" class="mt-3">
                                <div class="alert alert-info py-2 small mb-2">
                                    Preview com dados {{ previewModo === 'reais' ? 'reais' : 'simulados' }}.
                                </div>
                                <div class="carta-oferta-preview" v-html="previewHtml"></div>
                            </div>
                            <p v-else class="text-muted small mb-0">Clique em Preview para visualizar o HTML renderizado.</p>
                        </div>

                        <div v-show="painelAtivo === 'historico'">
                            <h6 class="text-uppercase mb-2">Versões anteriores</h6>
                            <p class="text-muted small mb-3">
                                Selecione uma versão para carregar no editor e salvar como nova.
                            </p>
                            <div v-if="!historico.length" class="text-muted small">Nenhuma versão salva ainda.</div>
                            <div class="list-group list-group-flush carta-historico">
                                <div
                                    v-for="item in historico"
                                    :key="item.id"
                                    class="list-group-item px-0"
                                    :class="{ 'carta-historico__ativa': origemHistorico && origemHistorico.id === item.id }"
                                >
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>v{{ item.versao }}</strong>
                                            <span
                                                class="badge ml-1"
                                                :class="item.status === 'publicado' ? 'badge-success' : 'badge-secondary'"
                                            >{{ item.status }}</span>
                                            <div class="text-muted small">{{ item.titulo }}</div>
                                            <div class="text-muted small">
                                                {{ item.created_at || '—' }} · {{ item.criado_por_nome }}
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            :disabled="carregandoVersao === item.id || salvando"
                                            @click.prevent="usarVersao(item)"
                                        >
                                            <i class="fa" :class="carregandoVersao === item.id ? 'fa-spinner fa-spin' : 'fa-history'"></i>
                                            Usar
                                        </button>
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

<script>
import tinymceSelfhost from '../../../utils/tinymceSelfhost'

export default {
    name: 'CartaOfertaTemplate',
    data() {
        return {
            carregando: true,
            editorPronto: false,
            salvando: false,
            carregandoVersao: null,
            painelAtivo: 'ferramentas',
            previewHtml: '',
            previewModo: 'simulados',
            historico: [],
            origemHistorico: null,
            configEditor: tinymceSelfhost.applySelfHost({
                toolbar: [
                    'undo redo | fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | removeformat'
                ],
                menubar: false,
                statusbar: true,
                schema: 'html5',
                height: 420,
                width: '100%',
                resize: true,
                branding: false,
                font_formats: 'Arial=arial,helvetica,sans-serif;Times New Roman=times new roman,times;',
                fontsize_formats: '10pt 11pt 12pt 14pt 16pt 18pt 24pt',
                plugins: 'paste lists',
                paste_auto_cleanup_on_paste: true,
                paste_remove_styles: true,
                paste_remove_styles_if_webkit: true,
                paste_strip_class_attributes: true,
                content_style: 'body { font-size: 12pt; font-family: Arial; line-height: 1.5; }'
            }),
            meta: {
                versao: null,
                publicado_versao: null
            },
            form: {
                titulo: 'Carta Oferta',
                conteudo_html: '',
                status: 'publicado'
            },
            previewForm: {
                usar_dados_reais: false,
                curriculo_id: '',
                vagas_abertas_id: '',
                setor: '',
                salario: '',
                data_inicio: '',
                autocomplete_curriculo_label: '',
                autocomplete_vaga_label: ''
            },
            placeholders: [
                '{{colaborador.nome}}',
                '{{colaborador.cpf}}',
                '{{colaborador.email}}',
                '{{cargo}}',
                '{{setor}}',
                '{{projeto}}',
                '{{salario}}',
                '{{bloco_salario}}',
                '{{data_inicio}}',
                '{{empresa.nome_fantasia}}',
                '{{empresa.razao_social}}',
                '{{empresa.cnpj}}',
                '{{data_emissao}}'
            ]
        }
    },
    computed: {
        urlBase() {
            return `${URL_ADMIN}/administracao/carta-oferta-template`
        },
        caminhoCurriculos() {
            return 'admissao/documentos/carta-oferta/buscar-curriculos'
        }
    },
    mounted() {
        this.carregar()
    },
    methods: {
        aplicarConteudoNoEditor(html) {
            this.form.conteudo_html = html || ''
            this.$nextTick(() => {
                if (this.$refs.editor && typeof this.$refs.editor.setContentHtml === 'function') {
                    this.$refs.editor.setContentHtml(this.form.conteudo_html)
                }
            })
        },
        async carregar() {
            this.carregando = true
            this.editorPronto = false
            try {
                const { data } = await axios.get(`${this.urlBase}/dados`)
                if (data && data.template) {
                    this.form.titulo = data.template.titulo || this.form.titulo
                    this.form.conteudo_html = data.template.conteudo_html || ''
                    this.form.status = data.template.status || 'publicado'
                    this.meta.versao = data.template.versao || null
                } else if (data && data.html_padrao) {
                    this.form.conteudo_html = data.html_padrao
                }
                if (data && data.publicado) {
                    this.meta.publicado_versao = data.publicado.versao || null
                }
                this.historico = Array.isArray(data.historico) ? data.historico : []
                this.origemHistorico = null
            } catch (err) {
                mostraErro('', 'Não foi possível carregar o template.')
            } finally {
                this.carregando = false
                // Só monta o TinyMCE com o HTML já preenchido
                this.$nextTick(() => {
                    this.editorPronto = true
                })
            }
        },
        async salvar() {
            if (!this.form.conteudo_html || !String(this.form.conteudo_html).trim()) {
                mostraErro('', 'Informe o conteúdo da carta oferta.')
                return
            }
            this.salvando = true
            try {
                const { data } = await axios.post(`${this.urlBase}/salvar`, this.form)
                mostraSucesso('', 'Template salvo com sucesso.')
                if (data && data.template) {
                    this.meta.versao = data.template.versao
                    if (data.template.status === 'publicado') {
                        this.meta.publicado_versao = data.template.versao
                    }
                }
                this.origemHistorico = null
                await this.recarregarHistorico()
            } catch (err) {
                mostraErro('', 'Não foi possível salvar o template.')
            } finally {
                this.salvando = false
            }
        },
        async recarregarHistorico() {
            try {
                const { data } = await axios.get(`${this.urlBase}/historico`)
                this.historico = Array.isArray(data.historico) ? data.historico : []
            } catch (e) {
                // silencioso
            }
        },
        async usarVersao(item) {
            if (!item || !item.id) return
            this.carregandoVersao = item.id
            try {
                const { data } = await axios.get(`${this.urlBase}/versao/${item.id}`)
                const t = data && data.template
                if (!t) {
                    mostraErro('', 'Versão não encontrada.')
                    return
                }
                this.form.titulo = t.titulo || this.form.titulo
                this.form.status = 'rascunho'
                this.aplicarConteudoNoEditor(t.conteudo_html || '')
                this.origemHistorico = {
                    id: item.id,
                    versao: item.versao,
                    created_at: item.created_at
                }
                this.painelAtivo = 'ferramentas'
                mostraSucesso('', `Versão ${item.versao} carregada. Salve para criar uma nova versão.`)
            } catch (err) {
                mostraErro('', 'Não foi possível carregar a versão.')
            } finally {
                this.carregandoVersao = null
            }
        },
        selecionaCurriculoPreview(obj) {
            this.previewForm.curriculo_id = obj?.curriculo_id || obj?.id || ''
            this.previewForm.autocomplete_curriculo_label = obj?.label || ''
        },
        selecionaVagaPreview(obj) {
            this.previewForm.vagas_abertas_id = obj?.id || ''
            this.previewForm.autocomplete_vaga_label = obj?.label || ''
        },
        salarioParaEnvio(valor) {
            if (valor === null || valor === undefined) {
                return null
            }
            const texto = String(valor).trim()
            if (!texto) {
                return null
            }
            const digitos = texto.replace(/\D/g, '')
            if (!digitos || Number(digitos) === 0) {
                return null
            }
            return texto
        },
        async preview() {
            if (this.previewForm.usar_dados_reais && !this.previewForm.curriculo_id) {
                mostraErro('', 'Selecione um currículo para o preview com dados reais.')
                return
            }
            this.salvando = true
            try {
                const payload = {
                    conteudo_html: this.form.conteudo_html,
                    usar_dados_reais: !!this.previewForm.usar_dados_reais,
                    curriculo_id: this.previewForm.curriculo_id || null,
                    vagas_abertas_id: this.previewForm.vagas_abertas_id || null,
                    setor: this.previewForm.setor || null,
                    salario: this.salarioParaEnvio(this.previewForm.salario),
                    data_inicio: this.previewForm.data_inicio || null
                }
                const { data } = await axios.post(`${this.urlBase}/preview`, payload)
                this.previewHtml = data && data.html ? data.html : ''
                this.previewModo = data && data.modo ? data.modo : 'simulados'
                this.painelAtivo = 'ferramentas'
            } catch (err) {
                const msg = err?.response?.data?.message || 'Não foi possível gerar o preview.'
                mostraErro('', msg)
            } finally {
                this.salvando = false
            }
        },
        async restaurarPadrao() {
            this.salvando = true
            try {
                const { data } = await axios.get(`${this.urlBase}/padrao`)
                this.form.titulo = (data && data.titulo) || 'Carta Oferta'
                this.aplicarConteudoNoEditor((data && data.conteudo_html) || '')
                this.origemHistorico = null
                mostraSucesso('', 'Conteúdo padrão carregado. Salve para publicar uma nova versão.')
            } catch (err) {
                mostraErro('', 'Não foi possível carregar o padrão.')
            } finally {
                this.salvando = false
            }
        },
        async copiarPlaceholder(texto) {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(texto)
                } else {
                    const input = document.createElement('textarea')
                    input.value = texto
                    document.body.appendChild(input)
                    input.select()
                    document.execCommand('copy')
                    document.body.removeChild(input)
                }
                mostraSucesso('', `${texto} copiado. Cole no editor.`)
            } catch (e) {
                mostraErro('', 'Não foi possível copiar o placeholder.')
            }
        }
    }
}
</script>

<style scoped>
.carta-oferta-preview {
    background: #fff;
    border: 1px solid #e4e7ee;
    border-radius: 8px;
    padding: 20px 22px;
    font-size: 12pt;
    line-height: 1.7;
    max-height: 40vh;
    overflow: auto;
}
.carta-oferta-preview :deep(p) {
    margin: 0 0 0.55em 0;
}
.carta-oferta-preview :deep(hr) {
    width: 60%;
    margin: 1.2em auto 0.6em;
    border: none;
    border-top: 1px solid #333;
}
.carta-historico {
    max-height: 55vh;
    overflow: auto;
}
.carta-historico__ativa {
    background: #f4f8fb;
}
.gap-2 {
    gap: 0.5rem;
}
.nav-tabs-sm .nav-link {
    padding: 0.35rem 0.75rem;
    font-size: 0.875rem;
}
</style>
