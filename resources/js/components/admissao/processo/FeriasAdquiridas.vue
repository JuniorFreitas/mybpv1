<template>
    <div>
        <fieldset>
            <legend class="text-uppercase">Férias</legend>
            <div class="row">
                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                    <button v-if="!visualizar" class="btn btn-sm btn-secondary mb-2"
                            @click="add()">
                        <span class="fas fa-plus" aria-hidden="true"></span>
                        ADICIONAR PERÍODO(S)
                    </button>
                </div>
            </div>
            <div class="row" v-for="(item, index) in lista" :key="index" v-if="lista.length">
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 col-sm-3 col-lg-2">
                            <div class="form-group">
                                <label>Período aquisitivo gozado</label>
                                <input type="text" class="form-control validacampo"
                                       v-mascara:per_aquisitivo
                                       :disabled="visualizar || item.status === 'gozada' || item.status === 'gozando'"
                                       placeholder="Ex: 2020/2021"
                                       @keyup.prevent="valida_campo_vazio($event.target,9)"
                                       @blur.prevent="valida_campo_vazio($event.target,9)"
                                       v-model="item.periodo_gozado">
                            </div>
                        </div>

                        <div class="col-12 col-md-2 col-lg-2">
                            <label>Quant de dias</label>
                            <select class="form-control validacampo" v-model="item.qnt_dias" :disabled="visualizar || item.status === 'gozada' || item.status === 'gozando'"
                                    @change.prevent="valida_campo_vazio($event.target,1);dataRetorno(item,index)"
                                    @blur.prevent="valida_campo_vazio($event.target,1);dataRetorno(item,index)"
                            >
                                <option v-for="cont in 30" :value="cont" v-show="cont >= 5">
                                    {{ cont }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-3 col-lg-2">
                            <div class="form-group">
                                <label>Data saída</label>
                                <datepicker label="" @onselect="dataRetorno(item,index)"
                                            class="corrigiDatepicker" v-model="item.data_saida"
                                            :disabled="visualizar || item.status === 'gozada' || item.status === 'gozando'"></datepicker>
                            </div>
                        </div>

                        <div class="col-12 col-sm-3 col-lg-2">
                            <div class="form-group">
                                <label>Data retorno</label>
                                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                       v-model="item.data_retorno" v-mascara:data
                                       readonly
                                       @keyup.prevent="valida_data($event.target, true)"
                                       @blur.prevent="valida_data($event.target, true)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-3 col-lg-2">
                            <div class="form-group">
                                <label>Próximo período</label>
                                <input type="text" class="form-control validacampo"
                                       v-mascara:per_aquisitivo
                                       :disabled="visualizar || item.status === 'gozada' || item.status === 'gozando'"
                                       placeholder="Ex: 2021/2022"
                                       @keyup.prevent="valida_campo_vazio($event.target,9)"
                                       @blur.prevent="valida_campo_vazio($event.target,9)"
                                       v-model="item.proximo_periodo">
                            </div>
                        </div>

                        <div class="col-12 col-sm-3 col-lg-2">
                            <div class="form-group">
                                <label>Data limite</label>
                                <datepicker label=""
                                            class="corrigiDatepicker" v-model="item.data_limite"
                                            :disabled="visualizar || item.status === 'gozada' || item.status === 'gozando'"></datepicker>
                            </div>
                        </div>

                    </div>
                    <div class="row" v-show="model.length > 0">
                        <div class="col-12 mb-3">
                            <button v-if="!visualizar && item.status === 'aguardando'" class="btn btn-sm btn-danger mb-2 mr-1" type="button"
                                    @click.prevent="remove(index)">
                                <span class="fas fa-times" aria-hidden="true"></span>
                                REMOVER
                            </button>
                            <button v-if="!visualizar" class="btn btn-sm btn-secondary mb-2"
                                    @click="add()">
                                <span class="fas fa-plus" aria-hidden="true"></span>
                                ADICIONAR PERIODO(S)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</template>

<script>
import Validacoes from "../../../mixins/Validacoes";

export default {
    mixins: [Validacoes],
    name: "FeriasAdquiridas",
    props: {
        model: {
            type: Array,
            required: true,
            default: () => []
        },
        modelDelete: {
            type: Array,
            required: true,
            default: () => []
        },
        visualizar: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            hash: parseInt((Math.random() * 999999)),
        }
    },
    computed: {
        lista() {
            return this.model;
        },
        listaDelete() {
            return this.modelDelete;
        },
    },
    methods: {
        add() {
            let dataAtual = new Date();
            let dia = dataAtual.getDate();
            let mes = dataAtual.getMonth();
            let ano = dataAtual.getFullYear();
            let dataHoje = `${this.padTo2Digits(dia)}/${this.padTo2Digits((mes + 1))}/${ano}`;
            let dataLimite = `${this.padTo2Digits(dia)}/${this.padTo2Digits((mes + 1))}/${ano + 1}`;

            let obj = {};
            obj.nova = true;
            obj.periodo_gozado = '';
            obj.qnt_dias = 5;
            obj.data_saida = dataHoje;
            obj.data_retorno = dataHoje;
            obj.proximo_periodo = '';
            obj.data_limite = dataLimite;
            obj.status = 'aguardando';
            this.lista.push(obj);

            this.dataRetorno(this.lista[this.lista.length - 1], this.lista.length - 1);
        },
        dataRetorno(obj, index) {
            let data_saida = obj.data_saida.split("/");
            let data_saida_convert = `${data_saida[2]}-${data_saida[1]}-${data_saida[0]}`;
            let data_retorno = new Date(data_saida_convert);
            data_retorno.setDate(data_retorno.getDate() + obj.qnt_dias);

            this.model[index].data_retorno = `${this.padTo2Digits(data_retorno.getDate())}/${this.padTo2Digits((data_retorno.getMonth() + 1))}/${data_retorno.getFullYear()}`;
        },
        padTo2Digits(num) {
            return num.toString().padStart(2, "0");
        },
        remove(index) {
            this.$emit("ondelete", this.lista[index]);
            if (!this.lista[index].nova) {
                this.listaDelete.push(this.lista[index].id);
            }
            this.lista.splice(index, 1);
        },
    }

}
</script>
