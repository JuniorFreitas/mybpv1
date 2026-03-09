<template>
    <div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <div class="form-group">
                            <label>Titulo</label>
                            <input type="text" class="form-control form-control-sm" v-model="form.titulo" />
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control form-control-sm" v-model="form.status">
                                <option value="publicado">Publicado</option>
                                <option value="rascunho">Rascunho</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Conteudo da Carta Oferta</label>
                    <editor v-model="form.conteudo_html" :init="configTinyMCE" />
                </div>

                <div>
                    <button class="btn btn-sm mr-1 btn-primary" :disabled="salvando" @click.prevent="salvar">
                        <i class="fa" :class="salvando ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        Salvar
                    </button>
                    <button class="btn btn-sm mr-1 btn-outline-secondary ml-2" :disabled="salvando" @click.prevent="preview">
                        <i class="fa fa-eye"></i>
                        Preview
                    </button>
                </div>
            </div>
        </div>

        <div v-if="previewHtml" class="card mt-3">
            <div class="card-body">
                <div class="alert alert-info">Preview com dados simulados.</div>
                <div v-html="previewHtml"></div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h6 class="text-uppercase">Placeholders disponiveis</h6>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <ul class="mb-0">
                            <li v-for="item in placeholders" :key="item">{{ item }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Editor from '@tinymce/tinymce-vue'
import configTinyMCE from '../../configTinyMCE'

export default {
    name: 'CartaOfertaTemplate',
    components: {
        Editor
    },
    data() {
        return {
            salvando: false,
            previewHtml: '',
            configTinyMCE,
            form: {
                titulo: 'Carta Oferta',
                conteudo_html: '',
                status: 'publicado'
            },
            placeholders: [
                '{{colaborador.nome}}',
                '{{colaborador.cpf}}',
                '{{colaborador.email}}',
                '{{cargo}}',
                '{{setor}}',
                '{{salario}}',
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
        }
    },
    mounted() {
        this.carregar()
    },
    methods: {
        async carregar() {
            try {
                const { data } = await axios.get(`${this.urlBase}/dados`)
                if (data && data.template) {
                    this.form.titulo = data.template.titulo || this.form.titulo
                    this.form.conteudo_html = data.template.conteudo_html || ''
                    this.form.status = data.template.status || 'publicado'
                }
            } catch (err) {
                // silencioso
            }
        },
        async salvar() {
            this.salvando = true
            this.previewHtml = ''
            try {
                await axios.post(`${this.urlBase}/salvar`, this.form)
                mostraSucesso('', 'Template salvo com sucesso.')
            } catch (err) {
                mostraErro('', 'Nao foi possivel salvar o template.')
            } finally {
                this.salvando = false
            }
        },
        async preview() {
            this.salvando = true
            try {
                const { data } = await axios.post(`${this.urlBase}/preview`, { conteudo_html: this.form.conteudo_html })
                this.previewHtml = data && data.html ? data.html : ''
            } catch (err) {
                mostraErro('', 'Nao foi possivel gerar o preview.')
            } finally {
                this.salvando = false
            }
        }
    }
}
</script>
