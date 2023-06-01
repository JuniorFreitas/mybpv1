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
        urlVaga() {
            let ambiente = process.env.MIX_AMBIENTE;
            let urlPadrao = ambiente === 'dev' ? 'https://hvagas.mybp.com.br/#' : 'https://vagas.mybp.com.br/#';
            return `${urlPadrao}/${this.authconfiguracao?.apelido}`;
        }
    }
};

export default configuracoes;
