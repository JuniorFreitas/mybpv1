import Documento from "../components/documento/Documento";

const app = new Vue({
    el: '#app',
    components: {
        Documento,
    },
    data: {
        autenticado: false,
        preload: true,
        curriculo: null,

        formUser: {
            cpf: '',
            nascimento: '',
            apelido: '',
        }
    },
    mounted() {
        this.formVinculoDefault = _.cloneDeep(this.formVinculo) //copia
        const pathArray = window.location.pathname.split("/");
        this.formUser.apelido = pathArray[1];
    },
    methods: {
        autenticar() {
            this.autenticado = false;
            this.preloadAutenticacao = true;
            axios.post(`${URL_SITE}/${this.formUser.apelido}/documentos-pre-admissao/autenticar`, this.formUser)
                .then(response => {
                    let data = response.data;
                    this.curriculo = data.curriculo;
                    this.autenticado = data.autenticado;
                    setTimeout(()=>{
                        $("#componenteDocumentos").modal('show');
                    },100)

                    this.preloadAutenticacao = false;
                })
                .catch(error => {
                    this.autenticado = false;
                    this.preloadAutenticacao = false;
                })
        }
    }
});
