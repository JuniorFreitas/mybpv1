import cadastro from '../../../components/cloud/cadastro';

const app = new Vue({
    el: '#app',
    components: {
        cadastro
    },
    // data: {
        // tituloJanela: 'Cadastrando Cloud',
        // preloadAjax: false,
        // editando: false,
        // cadastrado: false,
        // atualizado: false,
        // apagado: false,
        //
        // form: {
        //     nome: '',
        //     ativo: true,
        // },
        //
        // formDefault: null,
        //
        // lista: [],
        //
        // controle: {
        //     carregando: false,
        //     dados: {},
        // }
    // },
    // mounted() {
    // mounted() {
    //     this.formDefault = _.cloneDeep(this.form)//copia
    //     this.atualizar();
    // },
    // methods: {
    //
    //
    //     formNovo() {
    //         this.cadastrado = false;
    //         this.atualizado = false;
    //         this.editando = false;
    //
    //         this.tituloJanela = "Cadastrando Cloud";
    //
    //         formReset();
    //         this.form = _.cloneDeep(this.formDefault) //copia
    //         this.form.habilidades = _.cloneDeep(this.listaDeHabilidades);
    //     },
    //
    //     cadastrar() {
    //         $('#janelaCadastrar :input:visible').trigger('blur');
    //         if ($('#janelaCadastrar :input:visible.is-invalid').length) {
    //             alert('Verificar os erros');
    //             return false;
    //         }
    //
    //         this.preloadAjax = true;
    //         axios.post(`${URL_ADMIN}/clouds/cadastro`, this.form)
    //             .then(response => {
    //                 let data = response.data;
    //                 this.preloadAjax = false;
    //                 this.cadastrado = true;
    //                 this.atualizar();
    //             }).catch(error => {
    //             this.preloadAjax = false;
    //         });
    //     },
    //
    //     formAlterar(id) {
    //         this.form.id = id;
    //
    //         this.cadastrado = false;
    //         this.atualizado = false;
    //         this.editando = false;
    //         this.tituloJanela = "Alterando Cloud";
    //
    //         this.preloadAjax = true;
    //
    //         formReset();
    //         axios.get(`${URL_ADMIN}/clouds/cadastro/${id}/editar`)
    //             .then(({ data }) => {
    //                 this.editando = true;
    //                 Object.assign(this.form, data);
    //                 this.preloadAjax = false;
    //             }).catch(error => {
    //             this.preloadAjax = false;
    //         });
    //     },
    //
    //     alterar() {
    //         $('#janelaCadastrar :input:visible').trigger('blur');
    //         if ($('#janelaCadastrar :input:visible.is-invalid').length) {
    //             alert('Verificar os erros');
    //             return false;
    //         }
    //
    //         this.preloadAjax = true;
    //
    //         axios.put(`${URL_ADMIN}/clouds/cadastro/${this.form.id}`, this.form)
    //             .then(({ data }) => {
    //                 this.preloadAjax = false;
    //                 this.atualizado = true;
    //                 this.atualizar();
    //             }).catch(error => {
    //             this.preloadAjax = false;
    //         });
    //
    //     },
    //
    //     janelaConfirmar(id) {
    //         this.form.id = id;
    //         this.apagado = false;
    //         this.preloadAjax = false;
    //     },
    //
    //     apagar() {
    //         this.preloadAjax = true;
    //
    //         axios.delete(`${URL_ADMIN}/clouds/cadastro/${this.form.id}`, this.form)
    //             .then(({ data }) => {
    //                 this.preloadAjax = false;
    //                 this.apagado = true;
    //                 this.atualizar();
    //             }).catch(error => {
    //             this.preloadAjax = false;
    //         });
    //     },
    //
    //     carregou(dados) {
    //         this.lista = dados.lista;
    //         this.listaDeHabilidades = dados.listaHabilidades;
    //         this.controle.carregando = false;
    //     },
    //
    //     carregando() {
    //         this.controle.carregando = true;
    //     },
    //     atualizar() {
    //         this.$refs.componente.atual = 1;
    //         this.$refs.componente.buscar();
    //     }
    // }
});
