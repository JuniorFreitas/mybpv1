const configuracoes = {
    data() {
        return {
            authconfiguracao: null
        };
    },
    mounted() {
        axios.get(`${URL_ADMIN}/usuario/autenticado/`)
            .then(({data}) => {
                this.authconfiguracao = data;
            })
            .catch(error => {
            });
    },
    computed: {
        whatsappLiberado() {
            return this.authconfiguracao?.whatsappLiberado
        },
        temFilial() {
            return this.authconfiguracao?.temFilial
        },
    }
};

export default configuracoes;
