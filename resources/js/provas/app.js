import { createApp } from 'vue'
import { registerGlobals } from '../registerGlobals'
import Simulado from '../components/Simulado'

const app = createApp({
    components: {
        Simulado
    },
    data() {
        return {
            autenticado: false,
            preloadAutenticacao: false,

            formVinculo: {
                curriculo_id: '',
                vaga_id: '',
                parente: '',
                funcao: '',
                grau_parentesco: '',
                foi_empregado: '',
                local_empregado: '',
                outra_empresa_parceira: ''
            },

            formVinculoDefault: null,

            formUser: {
                cpf: '',
                nascimento: '',
                vaga_id: '',
                simulado_vaga_id: '',
                simulado_id: '',
                curriculo_id: '',
                empresa_id: '',
                vagas_abertas_id: ''
            }
        }
    },
    mounted() {
        this.formUser.vaga_id = parseInt($('#vaga_id').val())
        this.formUser.simulado_vaga_id = parseInt($('#simulado_vaga_id').val())
        this.formUser.simulado_id = parseInt($('#simulado_id').val())
        this.formUser.empresa_id = parseInt($('#empresa_id').val())
        this.formUser.vagas_abertas_id = parseInt($('#vagas_abertas_id').val())
        this.formVinculoDefault = _.cloneDeep(this.formVinculo) //copia
    },
    methods: {
        salvarVinculo() {
            $('#vinculo :input:visible').trigger('blur')
            if ($('#vinculo :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }
            axios
                .post(`${URL_SITE}/prova/salvar-vinculo`, this.formVinculo)
                .then((response) => {
                    let data = response.data
                    $('#vinculo').modal('hide')
                    mostraSucesso('', 'Obrigado, uma ótima prova!')
                })
                .catch((error) => {})
        },
        autenticar() {
            this.autenticado = false
            this.preloadAutenticacao = true
            axios
                .post(`${URL_SITE}/provas/autenticar`, this.formUser)
                .then((response) => {
                    let data = response.data
                    this.formUser.curriculo_id = data.curriculo.id
                    this.formUser.feedback_id = data.curriculo.feed_back.id
                    this.autenticado = data.autenticado
                    // if (!data.curriculo.vinculo) {
                    //     this.formVinculo.curriculo_id = data.curriculo.id;
                    //     this.formVinculo.vaga_id = data.curriculo.feed_back.vaga_id;
                    //     $('#vinculo').modal('show');
                    // }
                    this.preloadAutenticacao = false
                })
                .catch((error) => {
                    this.autenticado = false
                    this.preloadAutenticacao = false
                })
        }
    }
})

registerGlobals(app)
app.mount('#app')
