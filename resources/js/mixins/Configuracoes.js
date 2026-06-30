const configuracoes = {
    data() {
        return {
            authconfiguracao: null,
            whatsappStatus: null,
        };
    },
    async mounted() {
        try {
            const [authRes, statusRes] = await Promise.all([
                axios.get(`${URL_ADMIN}/usuario/autenticado/`),
                axios.get('/g/configuracoes/whatsapp/status').catch(() => ({ data: null })),
            ]);
            this.authconfiguracao = authRes.data;
            this.whatsappStatus = statusRes.data;
        } catch (error) {
            // silencioso
        }
    },
    computed: {
        whatsappLiberado() {
            if (this.whatsappStatus != null) {
                return !!this.whatsappStatus.whatsapp_liberado;
            }

            return !!this.authconfiguracao?.whatsappLiberado;
        },
        temFilial() {
            return this.authconfiguracao?.temFilial
        },
        urlVaga() {
            let ambiente = process.env.MIX_AMBIENTE;
            let urlPadrao = ambiente === 'dev' ? 'https://hvagas.mybp.com.br' : 'https://vagas.mybp.com.br';
            return `${urlPadrao}/${this.authconfiguracao?.apelido}`;
        }
    },
    methods: {
        whatsappModuloHabilitado(modulo) {
            if (!this.whatsappLiberado) {
                return false;
            }

            if (!this.whatsappStatus?.modulos) {
                return false;
            }

            return !!this.whatsappStatus.modulos[modulo];
        },
        /** Exige empresa com WhatsApp liberado E módulo do tipo habilitado. */
        whatsappTipoHabilitado(tipo) {
            if (!this.whatsappLiberado) {
                return false;
            }

            if (!this.whatsappStatus?.tipos) {
                return false;
            }

            return !!this.whatsappStatus.tipos[tipo];
        },
        telefonePrincipalEhWhatsapp(telPrincipal) {
            return telPrincipal?.tipo === 'whatsapp' && !!(telPrincipal?.numero || telPrincipal?.sonumero);
        },
        /** Empresa liberada + módulo habilitado + telefone principal tipo WhatsApp. */
        whatsappPodeNotificar(tipo, telPrincipal) {
            return this.whatsappTipoHabilitado(tipo) && this.telefonePrincipalEhWhatsapp(telPrincipal);
        },
    }
};

export default configuracoes;
