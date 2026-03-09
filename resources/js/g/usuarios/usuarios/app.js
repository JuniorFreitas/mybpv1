import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'

const app = createApp({
    data() {
        return {
            tituloJanela: 'Cadastrando usuário',
            preloadAjax: false,
            editando: false,
            cadastrado: false,
            atualizado: false,
            urlAjax: '',
            apagado: false,
            grupoempresa: false,
            user_recebe_emailDefault: null,

            form: {
                id: '',
                nome: '',
                login: '',
                grupo_id: '',
                tipo: '',
                grupo_cloud_id: '',
                empresa_id: '',
                ativo: true,
                gestor: false,
                user_recebe_email: [],
                privilegio_gestor_area: false,
                privilegio_gestor_centro_custo: false
            },
            empresa_id: '',
            formDefault: null,
            listaPapeis: [],
            listaCloud: [],
            listaTipoEmail: [],
            lista_tipos: [],
            tipos_usuarios_gerenciais: [],
            lista: [],
            dados: {},
            controle: {
                carregando: false,
                showCampoGrupo: false,
                dados: {
                    campoBusca: '',
                    campoLogin: '',
                    por_pagina: 50,
                    campoEmpresa: '',
                    campoGrupo: '',
                    campoTipo: '',
                    listaPapeis: [],
                    campoStatus: ''
                }
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar()
        // Object.assign(this.form, this.formDefault);
    },
    methods: {
        formNovo() {
            this.selecionaEmpresa(this.empresa_id)

            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Cadastrando usuário'
            formReset()
            this.form = _.cloneDeep(this.formDefault) //copia
            if (this.empresa_id !== 100) {
                this.form.empresa_id = this.empresa_id
            }
        },

        cadastrar() {
            $('#janelaCadastrar :input:visible:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.preloadAjax = true

            if (!this.form.gestor) {
                this.form.privilegio_gestor_centro_custo = false
                this.form.privilegio_gestor_area = false
            }

            axios
                .post(`${URL_ADMIN}/usuarios`, this.form)
                .then((response) => {
                    this.preloadAjax = false
                    this.cadastrado = true
                    this.atualizar()
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        formAlterar(id) {
            formReset()
            this.form = _.cloneDeep(this.formDefault) //copia

            this.selecionaEmpresa(this.empresa_id)
            if (this.empresa_id !== 100) {
                this.form.empresa_id = this.empresa_id
            }

            this.form.id = id

            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando usuário'

            this.preloadAjax = true
            formReset()
            this.form = _.cloneDeep(this.formDefault) //copia

            axios
                .get(`${URL_ADMIN}/usuarios/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data.usuario)

                    if (response.data.usuario.grupo_id == null) {
                        this.form.grupo_id = ''
                    }
                    if (response.data.usuario.gestor == null) {
                        this.form.gestor = false
                    }

                    this.listaPapeis = response.data.papeis
                    this.listaCloud = response.data.cloud
                    this.form.user_recebe_email = response.data.formulario_vazio
                    this.editando = true
                    this.preloadAjax = false
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        alterar() {
            $('#janelaCadastrar :input:visible:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.preloadAjax = true

            if (!this.form.gestor) {
                this.form.privilegio_gestor_centro_custo = false
                this.form.privilegio_gestor_area = false
            }

            axios
                .put(`${URL_ADMIN}/usuarios/${this.form.id}`, this.form)
                .then((response) => {
                    if (response.status === 201) {
                        this.preloadAjax = false
                        this.atualizado = true
                        this.atualizar()
                    }
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        simularUsuario(user_id) {
            this.preloadAjax = true
            axios
                .put(`${URL_ADMIN}/usuarios/simularUsuario`, { user_id: user_id })
                .then((response) => {
                    if (response.data.simulacao) {
                        window.location.href = `${URL_ADMIN}/dashboard`
                    }
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        async selecionaEmpresa(id) {
            this.grupoempresa = false
            this.listaPapeis = []
            this.form.grupo_id = ''
            if (id != '' && id != 100) {
                try {
                    const response = await axios.get(`${URL_ADMIN}/usuario/busca-grupo-empresa/${id}`)
                    if (response.status === 200) {
                        const data = response.data
                        this.listaPapeis = data.papeis
                        this.listaCloud = data.cloud
                        this.grupoempresa = true
                    }
                } catch (err) {}
            }
        },

        async buscarGruposEmpresa(id) {
            this.controle.showCampoGrupo = false
            this.controle.dados.listaPapeis = []
            this.controle.dados.campoGrupo = ''
            if (id != '') {
                try {
                    const response = await axios.get(`${URL_ADMIN}/usuario/busca-grupo-empresa/${id}`)
                    if (response.status === 200) {
                        this.controle.dados.listaPapeis = response.data.papeis
                        this.controle.showCampoGrupo = true
                    }
                } catch (err) {}
            }
        },

        carregou(dados) {
            this.lista = dados.resultado
            this.empresa_id = dados.empresa
            this.listaTipoEmail = dados.tipo_email
            this.tipos_usuarios_gerenciais = dados.tipos_usuarios_gerenciais
            this.user_recebe_emailDefault = dados.formulario_vazio
            this.lista_tipos = dados.lista_tipos
            this.empresa_id != 100 ? (this.controle.dados.listaPapeis = dados.lista_grupos) : []
            this.form.user_recebe_email = _.cloneDeep(this.user_recebe_emailDefault)
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
})

registerGlobals(app)
app.mount('#app')
