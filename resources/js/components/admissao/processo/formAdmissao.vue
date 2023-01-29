<template>
    <div class="row" v-if="!preload">
        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Área</label>
                <select class="form-control" v-model="form.area_etiqueta_id"
                        :disabled="visualizar || disabled">
                    <option value="">Selecione</option>
                    <option :value="item.id"
                            v-for="item in areasetiquetas">
                        {{ item.label }}
                    </option>
                </select>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Centro de Custo</label>
                <select
                    :disabled="visualizar || disabled"
                    v-model="form.centro_custo_id"
                    class="form-control"
                >
                    <option value="">Selecione</option>
                    <option v-for="item in centro_custos" :value="item.id" :key="item.id">{{ item.label }}</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Função</label>
                <input type="text" class="form-control" onblur="valida_campo_vazio(this,2)"
                       :disabled="visualizar || disabled"
                       v-model="form.funcao">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Salário R$</label>
                <input type="text" class="form-control" v-mascara:dinheiro :disabled="visualizar || disabled"
                       v-model="form.salario">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Documento</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar || disabled"
                        v-model="form.documento">
                    <option value="">Selecione</option>
                    <option v-for="item in listSelects.todos_status_documentos" :value="item">{{ item }}</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Documento Portaria</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar || disabled"
                        v-model="form.documento_portaria">
                    <option value="">Selecione</option>
                    <option v-for="item in listSelects.todos_status_documentos_portaria" :value="item">
                        {{ item }}
                    </option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Tipo de admissão</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar || disabled"
                        v-model="form.tipo_admissao">
                    <option value="">Selecione</option>
                    <option v-for="item in listSelects.tipos_admissao" :value="item">{{ item }}</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6" v-if="form.tipo_admissao === 'FIXO'">
            <div class="form-group">
                <label>Prazo de experiência</label>
                <select class="form-control" onchange="valida_campo_vazio(this,1)"
                        onblur="valida_campo_vazio(this,1)" :disabled="visualizar || disabled"
                        v-model="form.prazo_experiencia">
                    <option value="">Selecione</option>
                    <option v-for="item in listSelects.todos_prazos" :value="item">{{ item }}</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6"
             v-if="['TEMPORARIO','DETERMINADO','INTERMITENTE'].includes(form.tipo_admissao)">
            <div class="form-group">
                <datepicker label="Data de encerramento" v-model="form.data_encerramento"
                            :disabled="visualizar || disabled"></datepicker>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Treinamento</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar || disabled"
                        v-model="form.treinamento">
                    <option value="">Selecione</option>
                    <option v-for="item in listSelects.todos_status_treinamentos" :value="item">{{ item }}</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6" v-if="form.treinamento === 'REALIZADO'">
            <div class="form-group">
                <label>Tipo de Treinamento</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar || disabled"
                        v-model="form.tipo_treinamento">
                    <option value="">Selecione</option>
                    <option value="COMPLETO">COMPLETO</option>
                    <option value="PARADA">PARADA</option>
                    <option value="LARGO">LARGO</option>
                </select>
            </div>
        </div>

        <!--        <div class="col-12 col-sm-6">-->
        <!--            <div class="form-group">-->
        <!--                <label>NR 33</label>-->
        <!--                <select class="form-control" onchange="valida_campo(this,1)"-->
        <!--                        onblur="valida_campo(this,1)" :disabled="visualizar || disabled"-->
        <!--                        v-model="form.nr_trinta_tres">-->
        <!--                    <option value="">Selecione</option>-->
        <!--                    <option v-for="item in listSelects.todos_status_treinamentos" :value="item">{{ item }}</option>-->
        <!--                </select>-->
        <!--            </div>-->
        <!--        </div>-->

        <!--        <div class="col-12 col-sm-6">-->
        <!--            <div class="form-group">-->
        <!--                <label>NR 35</label>-->
        <!--                <select class="form-control" onchange="valida_campo(this,1)"-->
        <!--                        onblur="valida_campo(this,1)" :disabled="visualizar || disabled"-->
        <!--                        v-model="form.nr_trinta_cinco">-->
        <!--                    <option value="">Selecione</option>-->
        <!--                    <option v-for="item in listSelects.todos_status_treinamentos" :value="item">{{ item }}</option>-->
        <!--                </select>-->
        <!--            </div>-->
        <!--        </div>-->

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Número Crachá</label>
                <input type="text" class="form-control" onblur="valida_campo(this,2)"
                       :disabled="visualizar || disabled"
                       v-model="form.numero_cracha">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Status Carteira de Treinamento e Etiqueta</label>
                <select class="form-control" onchange="valida_campo(this,1)" :disabled="visualizar || disabled"
                        onblur="valida_campo(this,1)"
                        v-model="form.status_carteira_treinamento">
                    <option value="">Selecione</option>
                    <option v-for="item in listSelects.status_carteira_treinamento" :key="item" :value="item">
                        {{ item }}
                    </option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Status</label>
                <select class="form-control" onchange="valida_campo_vazio(this,1)"
                        :disabled="visualizar || disabled"
                        onblur="valida_campo_vazio(this,1)"
                        v-model="form.status">
                    <option value="">Selecione</option>
                    <option v-for="item in listSelects.status_admissao" :key="item" :value="item">{{ item }}</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Data do ASO</label>
                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                       :disabled="visualizar || disabled"
                       v-model="form.ultimo_aso_ativo.data_aso" v-mascara:data
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_data_vazio($event.target) :valida_data($event.target)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_data_vazio($event.target) :valida_data($event.target)">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Data da Admissão</label>
                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                       :disabled="visualizar || disabled"
                       v-model="form.data_admissao" v-mascara:data
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_data_vazio($event.target) :valida_data($event.target)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_data_vazio($event.target) :valida_data($event.target)">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Data da Entrega na área</label>
                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                       :disabled="visualizar || disabled"
                       v-model="form.data_entrega_area" v-mascara:data
                       @keyup.prevent="valida_data($event.target)"
                       @blur.prevent="valida_data($event.target)">
            </div>
        </div>


        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Biometria</label>
                <select class="form-control" :disabled="visualizar || disabled"
                        v-model="form.biometria">
                    <option value="">Selecione</option>
                    <option :value="true">SIM</option>
                    <option :value="false">NÃO</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6" v-if="form.biometria">
            <div class="form-group">
                <label>Data Biometria</label>
                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                       :disabled="visualizar || disabled"
                       v-model="form.data_biometria" v-mascara:data
                       @keyup.prevent="valida_data($event.target)"
                       @blur.prevent="valida_data($event.target)">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>PIS</label>
                <input type="text" class="form-control validacampo"
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_campo($event.target,11) : valida_campo($event.target,11)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_campo($event.target,11) : valida_campo($event.target,11)"
                       :disabled="visualizar || disabled"
                       v-model="form.pis">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Número CTPS</label>
                <input type="text" class="form-control validacampo"
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_campo($event.target,6) : valida_campo($event.target,6)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_campo($event.target,6) : valida_campo($event.target,6)"
                       :disabled="visualizar || disabled"
                       v-model="form.dados_admissoes.ctps_numero">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Série CTPS</label>
                <input type="text" class="form-control validacampo"
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.ctps_numero.length ? valida_campo_vazio($event.target,3) : valida_campo($event.target,3)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.ctps_numero.length ? valida_campo_vazio($event.target,3) : valida_campo($event.target,3)"
                       :disabled="visualizar || disabled"
                       v-model="form.dados_admissoes.ctps_serie">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>CTPS UF</label>
                <input type="text" class="form-control validacampo" maxlength="2"
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.ctps_numero.length ? valida_campo($event.target,2) : valida_campo($event.target,2)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.ctps_numero.length ? valida_campo($event.target,2) : valida_campo($event.target,2)"
                       :disabled="visualizar || disabled"
                       v-model="form.dados_admissoes.ctps_uf">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Data Emissão CTPS</label>
                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                       :disabled="visualizar || disabled"
                       v-model="form.dados_admissoes.ctps_data_emissao" v-mascara:data
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.ctps_numero.length ? valida_data($event.target) :valida_data($event.target)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.ctps_numero.length ? valida_data($event.target) :valida_data($event.target)"
                >
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Certificado Reservista Numero</label>
                <input type="text" class="form-control"
                       :disabled="visualizar || disabled"
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao && verificaSexoMasculino ? valida_campo($event.target,2) : valida_campo($event.target,2)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao && verificaSexoMasculino ? valida_campo($event.target,2) : valida_campo($event.target,2)"
                       v-model="form.dados_admissoes.cert_reservista_num"
                >
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Certificado Reservista Categoria</label>
                <input type="text" class="form-control validacampo"
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao && verificaSexoMasculino ? valida_campo($event.target,2) : valida_campo($event.target,2)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao && verificaSexoMasculino ? valida_campo($event.target,2) : valida_campo($event.target,2)"
                       :disabled="visualizar || disabled"
                       v-model="form.dados_admissoes.cert_reservista_categoria">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Titulo de Eleitor</label>
                <input type="text" class="form-control validacampo"
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_campo($event.target,12) : valida_campo($event.target,12)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_campo($event.target,12) : valida_campo($event.target,12)"
                       :disabled="visualizar || disabled"
                       v-model="form.dados_admissoes.titulo_eleitor_numero">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Titulo de Eleitor Sessão</label>
                <input type="text" class="form-control validacampo"
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.titulo_eleitor_numero.length ? valida_campo($event.target,2) : valida_campo($event.target,2)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.titulo_eleitor_numero.length ? valida_campo($event.target,2) : valida_campo($event.target,2)"
                       :disabled="visualizar || disabled"
                       v-model="form.dados_admissoes.titulo_eleitor_sessao">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Titulo de Eleitor Zona</label>
                <input type="text" class="form-control validacampo"
                       @keyup.prevent="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.titulo_eleitor_numero.length ? valida_campo($event.target,2) : valida_campo($event.target,2)"
                       @blur.prevent="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.titulo_eleitor_numero.length ? valida_campo($event.target,2) : valida_campo($event.target,2)"
                       :disabled="visualizar || disabled"
                       v-model="form.dados_admissoes.titulo_eleitor_zona">
            </div>
        </div>
        <div class="col-12">
            <ferias-adquiridas :model="form.ferias_adquiridas"
                               :model-delete="form.ferias_adquiridasDelete"
                               :visualizar="visualizar"
            ></ferias-adquiridas>
        </div>
    </div>
</template>

<script>
import Validacoes from "../../../mixins/Validacoes";
import FeriasAdquiridas from "./FeriasAdquiridas";

export default {
    mixins: [Validacoes],
    components: {
        FeriasAdquiridas
    },
    props: {
        form: {
            type: Object,
            required: true,
            default: {
                feedback_id: "",
                area_etiqueta_id: "",
                centro_custo_id: "",
                contrato: "",
                funcao: "",
                salario: "0,00",
                status: "",
                documento: "",
                documento_portaria: "",
                tipo_admissao: "",
                tipo_treinamento: "",
                treinamento: "",
                data_treinamento: "",
                carteira_treinamento: "",
                nr_trinta_tres: "",
                data_nr_trinta_tres: "",
                nr_trinta_cinco: "",
                data_nr_trinta_cinco: "",
                trinta_dois_sessenta: "",
                data_trinta_dois_sessenta: "",
                numero_cracha: "",
                foto_escaneada: "",
                status_carteira_treinamento: "",
                data_admissao: "",
                data_adm_prevista: "",

                data_entrega_area: "",
                biometria: "",
                data_biometria: "",

                indicado_por: "",
                indicado_area: "",

                filiacao_pai: "",
                filiacao_mae: "",
                nome: "",
                calca: "",
                bota: "",
                camisa_protecao: "",
                camisa_meia: "",
                pis: "",
                prazo_experiencia: "",
                prazo_encerramento: "",
                dados_admissoes: {
                    ctps_numero: "",
                    ctps_serie: "",
                    ctps_data_emissao: "",
                    titulo_eleitor_numero: "",
                    titulo_eleitor_sessao: "",
                    titulo_eleitor_zona: "",
                    ctps_uf: "",
                    cert_reservista_num: "",
                    cert_reservista_categoria: "",
                },
                ultimo_aso_ativo: {
                    data_aso: ""
                },
                ferias_adquiridas: [],
                ferias_adquiridasDelete: [],
            }
        },
        visualizar: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    filters: {
        formataMaiuscula(value) {
            this.uppercase(value);
        }
    },
    computed: {
        verificaStatusAdmitidoProntoAdmissao() {
            return ['ADMITIDO', 'PRONTO PARA ADMISSÃO'].includes(this.form.status);
        },
        verificaSexoMasculino() {
            const sexo = this.$root.$data.formulario_open === 'Avulsa' ? this.$root.$data.formAvulsa.curriculo.sexo : this.$root.$data.form.curriculo.sexo
            return sexo === 'Masculino';
        }
    },
    data() {
        return {
            hash: `mastertag_${parseInt(Math.random() * 999999)}`,
            preload: true,
            areasetiquetas: [],
            listSelects: [],
            centro_custos: [],
        };
    },
    async created() {
        this.preload = true;
        await axios.get(`${URL_PUBLICO}/lista-areas`)
            .then(response => {
                this.areasetiquetas = response.data.areas ?? "";
            }).catch(e => console.log(e));

        await axios.get(`${URL_ADMIN}/admissao/listSelects`)
            .then(response => {
                this.listSelects = response.data;
            }).catch(e => console.log(e));

        await axios.post(`${URL_PUBLICO}/centro-custos/`, {'empresa_id': this.form.empresa_id})
            .then(response => {
                this.centro_custos = response.data.centro_custos;
            }).catch(error => {
                this.preload = false;
            });

        this.preload = false;
    },
    methods: {
        uppercase(value) {
            if (!value) return ''
            value = value.toString()
            return value.toUpperCase()
        },
        validaData() {
            if (this.form.ultimo_aso_ativo.data_aso.length >= 10) {
                let dataCorreta = moment(this.form.ultimo_aso_ativo.data_aso, "DD/MM/YYYY");
                if (!dataCorreta.isValid()) {
                    mostraErro("", "A data do ASO inserida é inválida");
                    this.form.ultimo_aso_ativo.data_aso = "";
                }
            }
        },

        validaDataAdmissao() {
            if (this.form.data_admissao.length >= 10) {
                let dataCorreta = moment(this.form.data_admissao, "DD/MM/YYYY");
                if (!dataCorreta.isValid()) {
                    mostraErro("", "A data da admissao inserida é inválida");
                    this.form.data_admissao = "";
                }
            }
        }
    }
};
</script>

<style scoped></style>
