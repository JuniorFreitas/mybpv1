import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Endereco from '../../../components/Endereco.vue'
import ContaCorrente from '../../../components/ContaCorrente.vue'
import Upload from '../../../components/Upload.vue'
import Telefones from '../../../components/Telefones.vue'
const app = createApp({
    data() {
        return {
        tituloJanela: 'Cadastrando Funcionário',
        preloadAjax: false,
        editando: false,
        apagado: false,


        urlAnexoUpload: `${URL_ADMIN}/funcionarios/uploadAnexos`,
        anexoUploadAndamento: false,

        urlFotoUpload: `${URL_ADMIN}/funcionarios/uploadFotos`,
        fotoUploadAndamento: false,

        id: 0,

        opened: [],

        form: {
            anexos: [],
            anexosDel: [],

            fotos: [],
            fotosDel: [],

            _method: null,
            id: 0,
            nome: '',
            cpf: '',
            nis: '',
            nascimento: '',
            sexo: 'Masculino',
            racaecor: 'Pardo',
            estadocivil: 'Solteiro',
            conjuge: '',
            conjuge_cpf: '',
            conjuge_rg: '',
            conjuge_nascimento: '',
            pai: '',
            mae: '',
            nacionalidade: 'Brasileira',
            naturalidade: '',
            escolaridade_id: 7,
            sapato: '',
            camisa: 'M',
            calca: '',
            altura: '',
            peso: '',
            cabelos: '',
            olhos: '',
            sinais: '',
            deficiente: false,
            deficiencia: [],
            deficiencia_obs: '',
            ctps_numero: '',
            ctps_serie: '',
            ctps_uf: '',
            ctps_emissao: '',
            tituloeleitoral: '',
            tituloeleitoral_zona: '',
            tituloeleitoral_secao: '',
            reservista: '',
            reservista_categoria: '',
            doc_tipo: 'RG - Registro Geral',
            doc_numero: '',
            doc_emissor: '',
            doc_emissao: '',
            doc_validade: '',
            residencia: true,
            comprada_fgts: false,
            reside_exterior: false,
            email: '',
            cep: '',
            logradouro: '',
            end_numero: '',
            complemento: '',
            bairro: '',
            municipio: '',
            uf: '',
            telefones: [],
            telefonesDel: [],
            chegada_brasil: '',
            naturalizacao_brasil: '',
            casado_brasil: false,
            filho_brasil: false,
            banco_id: 1,
            tipo_conta: 'corrente',
            agencia: '',
            numero: '',
            nome_conta: '',
            dependentes: [],
            dependentesDelete: [],
            vale_transporte: true,
            vale_transporte_linhaum: '0,00',
            vale_transporte_linhadois: '0,00',
            primeiro_emprego: false,
            desconto_plano_saude: false,
            desconto_plano_saude_valor: '0,00',
            outra_empresa: false,
            outra_empresa_razaosocial: '',
            outra_empresa_cnpj: '',
            outra_empresa_remuneracao: '0,00',
            outra_empresa_obs: '',
            admissao: '',
            admissional: '',
            admissional_crm: '',
            cargo_id: 1,
            cargo_descricao: '',
            departamento: '',
            cliente_id: '',
            empresa_id: 1,
            tipo_contrato: 'Contrato de trabalho por prazo INDETERMINADO',
            dias_experiencia: '',
            dias_prorrogacao: '',
            tipo_remuneracao: 'Mensal',
            forma_pagamento: 'Mensal',
            remuneracao: 'Fixa',
            remuneracao_valor: '0,00',
            descontar_vt: false,
            descontar_vr_va: false,
            valor_desconto_vr: '0,00',
            fgts: true,
            fgts_admissao: '',
            fgts_retratacao: '',
            fgts_banco: '',
            horarios: [],
            horariosDelete: [],
            demitido: false,
            demissao: '',
            demissional: '',
            demissional_crm: '',
            ativo: true,


        },

        formDefault: null,
        campoNome: null,

        cadastrado: false,
        atualizado: false,

        lista: [],

        controle: {
            carregando: false,
            dados: {
                empresa_id: "",
                cliente_id: "",
                cargo_id: "",
            },
        }
    },
    mounted: function () {
        this.formDefault = _.cloneDeep(this.form)//copia
        var _this = this;
        $("#admissao").on('apply.daterangepicker', function (ev, picker) {
            _this.form.admissao = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#nascimento").on('apply.daterangepicker', function (ev, picker) {
            _this.form.nascimento = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#doc_emissao").on('apply.daterangepicker', function (ev, picker) {
            _this.form.doc_emissao = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#doc_validade").on('apply.daterangepicker', function (ev, picker) {
            _this.form.doc_validade = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#ctps_emissao").on('apply.daterangepicker', function (ev, picker) {
            _this.form.ctps_emissao = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#admissional").on('apply.daterangepicker', function (ev, picker) {
            _this.form.admissional = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#pis_cadastro").on('apply.daterangepicker', function (ev, picker) {
            _this.form.pis_cadastro = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#fgts_admissao").on('apply.daterangepicker', function (ev, picker) {
            _this.form.fgts_admissao = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#fgts_retratacao").on('apply.daterangepicker', function (ev, picker) {
            _this.form.fgts_retratacao = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#datademissao").on('apply.daterangepicker', function (ev, picker) {
            _this.form.datademissao = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#chegada_brasil").on('apply.daterangepicker', function (ev, picker) {
            _this.form.chegada_brasil = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
        $("#naturalizacao_brasil").on('apply.daterangepicker', function (ev, picker) {
            _this.form.naturalizacao_brasil = $(this).val(picker.startDate.format('DD/MM/YYYY'))[0].value;
        });
    },

    methods: {
        toggle(id) {
            const index = this.opened.indexOf(id);
            if (index > -1) {
                this.opened.splice(index, 1)
            } else {
                this.opened.push(id)
            }
        },
        filtroEmpresa: function () {
            // this.controle.dados.cliente_id = ""
            // this.controle.dados.cargo_id = ""
            setTimeout(atualizar, 300);
        },
        filtroCliente: function () {
            // this.controle.dados.empresa_id = ""
            // this.controle.dados.cargo_id = ""
            setTimeout(atualizar, 300);
        },
        filtroCargo: function () {
            // this.controle.dados.empresa_id = ""
            // this.controle.dados.cliente_id = ""
            setTimeout(atualizar, 300);
        },
        addLIDependente: function () {
            let obj = {};
            obj.nova = true;
            obj.nome = '';
            obj.cpf = '';
            obj.nascimento = '';
            this.form.dependentes.push(obj);
        },
        removerLIDependente: function (index) {
            if (this.editando) {
                this.form.dependentesDelete.push(this.form.dependentes[index].id);
            }
            this.form.dependentes.splice(index, 1);
        },
        addLIHorario: function () {
            let obj = {};
            obj.nova = true;
            obj.horario = '';
            obj.entrada_turnoum = '';
            obj.entrada_turnodois = '';
            obj.saida_turnodois = '';
            obj.saida_turnoum = '';
            obj.diasSemana = [];
            obj.jornada = '';
            this.form.horarios.push(obj);
        },
        removerLIHorario: function (index) {
            if (this.editando) {
                this.form.horariosDelete.push(this.form.horarios[index].id);
            }
            this.form.horarios.splice(index, 1);
        },
        formNovo: function () {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.tituloJanela = "Cadastrando Funcionário";

            formReset();
            setupCampo();
            this.form = _.cloneDeep(this.formDefault) //copia
        },
        cadastrar: function () {
            $('#janelaCadastrar :input:visible').trigger('blur');
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }
            this.preloadAjax = true;
            $.post(`${URL_ADMIN}/funcionarios`, this.form)
                .done((data) => {
                    // console.log(data);
                    app.preloadAjax = false;
                    app.cadastrado = true;
                    $('#controle button:eq(0)').click();
                })
                .fail((data) => {
                    app.preloadAjax = false;
                });
        },
        pdf: function (id) {
            window.open(`${URL_ADMIN}/funcionarios/pdf/${id}`)
        },
        exportExcel: function () {
            let empresa_id = this.controle.dados.empresa_id;
            let cliente_id = this.controle.dados.cliente_id;
            let cargo_id = this.controle.dados.cargo_id;

            window.location = `${URL_ADMIN}/funcionarios/export?empresa_id=${empresa_id}&cliente_id=${cliente_id}&cargo_id=${cargo_id}`
        },
        formAlterar: function (id) {
            this.id = id;

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Alterando Funcionário";

            this.preloadAjax = true;
            formReset();
            $.get(`${URL_ADMIN}/funcionarios/${id}/editar`)
                .done((data) => {
                    Object.assign(app.form, data);
                    app.form.fotosDel = [];
                    app.form.anexosDel = [];
                    app.form.telefonesDel = [];
                    app.form.horariosDelete = [];
                    app.form.dependentesDelete = [];
                    setupCampo();
                    app.editando = true;
                    app.preloadAjax = false;
                })
                .fail((data) => {
                    app.preloadAjax = false;
                });
        },

        abrirJanelaAnexo: function (id) {
            this.id = id;

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Anexo Funcionarios #" + id;

            this.preloadAjax = true;
            formReset();
            $.get(`${URL_ADMIN}/funcionarios/${id}/editar`)
                .done((data) => {
                    Object.assign(app.form, data);
                    app.form.fotosDel = [];
                    app.form.anexosDel = [];
                    setupCampo();
                    app.editando = false;
                    app.preloadAjax = false;
                })
                .fail((data) => {
                    app.preloadAjax = false;
                });
        },

        alterar: function () {
            $('#janelaCadastrar :input:visible').trigger('blur');
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }
            this.form._method = 'PUT';
            this.preloadAjax = true;

            $.post(`${URL_ADMIN}/funcionarios/${this.form.id}`, this.form)
                .done((data) => {
                    // console.log(data)
                    app.preloadAjax = false;
                    app.atualizado = true;
                    $('#controle button:eq(0)').click();
                })
                .fail((data) => {
                    app.preloadAjax = false;
                });

        },
        apagar: function () {
            this.erros = [];
            this.form._method = 'DELETE';
            this.preloadAjax = true;

            $.post(`${URL_ADMIN}/funcionarios/${this.form.id}`, this.form)
                .done((data) => {
                    app.preloadAjax = false;
                    app.apagado = true;
                    $('#controle button:eq(0)').click();
                })
                .fail((data) => {
                    app.preloadAjax = false;
                    app.erros = data.erros;
                    mostraErro(data.responseJSON);
                });
        },
        janelaConfirmar: function (id) {
            app.form.id = id;
            this.apagado = false;

            this.preloadAjax = false;
        },
        carregou: function (dados) {

            this.lista = dados;
            this.controle.carregando = false;

        },
        carregando: function () {
            this.controle.carregando = true;
        },
    }
});

app.component('endereco', Endereco)
app.component('banco', ContaCorrente)
app.component('upload', Upload)
app.component('telefone', Telefones)

registerGlobals(app)
app.mount('#app');

$().ready(function () {

    $('#janelaCadastrar').on('shown.bs.modal', function () {
        $('#cnpj').focus(); // ja foca no descricao quando a janela abrir
    });
    $('#btnAtualizar').on('click', atualizar);
    atualizar();

    $('#formBusca').on('submit', function (e) {
        e.preventDefault();
        app.controle.dados.campoBusca = $('#campoBusca').val();
        atualizar();
    });

});

function atualizar() {
    app.$refs.componente.atual = 1;
    app.this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null;
}
