const configuracoes = {
    data() {
        return {
            authconfiguracao: null
        };
    },
    mounted() {
        axios.get(`${URL_ADMIN}/usuario/autenticado/`)
            .then(response => {
                this.authconfiguracao = response.data;
            })
            .catch(error => {
            });
    },
    computed: {
        whatsappLiberado() {
            return this.authconfiguracao?.empresa_configuracoes?.envia_whatsapp
        }
    }
};

export default configuracoes;
