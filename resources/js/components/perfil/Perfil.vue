<template>
    <div>
        <span v-show="preload"><preload></preload></span>
        <form id="form">
            <div class="form-group">
                <label>Nome do usuário</label>
                <input
                    type="text"
                    class="form-control form-control-sm"
                    v-model="form.nome"
                    placeholder="Nome do usuário"
                    autocomplete="off"
                    onblur="valida_campo_vazio(this, 3)"
                />
            </div>
            <div class="form-group">
                <label>Login</label>
                <input
                    type="text"
                    class="form-control form-control-sm"
                    v-model="form.login"
                    placeholder="Login"
                    autocomplete="off"
                    onblur="valida_campo_vazio(this, 3)"
                />
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-primary" v-show="!preload" @click="alterar()">Salvar</button>
        </div>
    </div>
</template>
<script>
import modal from '../Modal'

export default {
    components: {
        modal
    },
    props: {
        usuario_id: {
            type: Number,
            required: true,
            default: ''
        }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),

            preload: false,

            form: {
                nome: '',
                login: ''
            },

            formDefault: null,

            lista: []
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form)
    },
    methods: {
        alterarPerfil(perfil) {
            this.editando = true
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia

            axios
                .get(`${URL_ADMIN}/perfil/${perfil}`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarFormPerfil() {
            formReset()
            $('#janelaPerfil :input:enabled').trigger('blur')

            if ($('#janelaPerfil :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/perfil/${this.form.id}`, this.form)
                .then((response) => {
                    $('#janelaFormPerfil').modal('hide')
                    mostraSucesso('', 'Perfil Atualizado com sucesso!')
                    this.preloadAjax = false
                    this.controle.carregando = true
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAjax = false))
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
</style>
