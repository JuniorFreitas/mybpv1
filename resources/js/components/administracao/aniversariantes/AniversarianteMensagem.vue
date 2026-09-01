<template>
    <div>
        <p class="text-muted mb-3">
            Texto enviado no e-mail de aniversário. O assunto continua
            <strong>EMPRESA - FELIZ ANIVERSÁRIO</strong>.
            Placeholders: <code v-for="item in placeholders" :key="item" class="mr-1">{{ item }}</code>
        </p>

        <div v-if="form.is_padrao" class="alert alert-info">
            Nenhuma mensagem customizada ainda. O editor está com o texto padrão do sistema.
        </div>

        <div class="form-group">
            <label class="mybp-label" for="mensagem-aniversariante">
                Mensagem <span class="text-danger">*</span>
            </label>
            <tiny-mce-editor v-model="form.conteudo_html" preset="padrao" :init="{ height: 280 }"></tiny-mce-editor>
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-sm mr-1 btn-primary" :disabled="salvando" @click.prevent="salvar">
                <i class="fa" :class="salvando ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                Salvar
            </button>
            <button type="button" class="btn btn-sm mr-1 btn-outline-secondary" :disabled="salvando" @click.prevent="preview">
                <i class="fa fa-eye"></i>
                Preview
            </button>
        </div>

        <div v-if="previewHtml" class="alert alert-light border">
            <strong class="d-block mb-2">Preview com dados simulados</strong>
            <div v-html="previewHtml"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AniversarianteMensagem',
    data() {
        return {
            salvando: false,
            previewHtml: '',
            form: {
                conteudo_html: '',
                is_padrao: true
            },
            placeholders: ['{{nome}}', '{{empresa}}']
        }
    },
    computed: {
        urlBase() {
            return `${URL_ADMIN}/administracao/aniversariante-mensagem`
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
                    this.form.conteudo_html = data.template.conteudo_html || ''
                    this.form.is_padrao = !!data.template.is_padrao
                }
                if (data && data.placeholders) {
                    this.placeholders = data.placeholders
                }
            } catch (err) {
                mostraErro('', 'Não foi possível carregar a mensagem.')
            }
        },
        async salvar() {
            const html = (this.form.conteudo_html || '').replace(/<[^>]*>/g, '').trim()
            if (!html) {
                mostraErro('', 'Informe o conteúdo da mensagem.')
                return
            }
            this.salvando = true
            this.previewHtml = ''
            try {
                await axios.post(`${this.urlBase}/salvar`, { conteudo_html: this.form.conteudo_html })
                this.form.is_padrao = false
                mostraSucesso('', 'Mensagem salva com sucesso.')
            } catch (err) {
                mostraErro('', 'Não foi possível salvar a mensagem.')
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
                mostraErro('', 'Não foi possível gerar o preview.')
            } finally {
                this.salvando = false
            }
        }
    }
}
</script>
