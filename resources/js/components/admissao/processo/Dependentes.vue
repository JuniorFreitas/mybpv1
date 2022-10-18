<template>
    <div :model="model" :modelDelete="modelDelete">
        <fieldset>
            <legend class="text-uppercase">DEPENDENTES</legend>
            <div class="row">
                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                    <button v-if="!visualizar" class="btn btn-sm btn-secondary mb-2"
                            @click="add()">
                        <span class="fas fa-plus" aria-hidden="true"></span>
                        ADICIONAR DEPENDENTE(S)
                    </button>
                </div>
            </div>
            <div class="row" v-for="(dependente, index) in lista" :key="index">
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select class="form-control validacampo" v-model="dependente.tipo"
                                        :disabled="visualizar"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"  onblur="valida_campo_vazio(this, 1)">
                                    <option value="">Selecione ...</option>
                                    <option v-for="(item, key) in tipos" :value="key" :key="key" v-text="item"></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4" v-if="dependente.tipo === 'outro'">
                            <div class="form-group">
                                <label>Especifique</label>
                                <input type="text" class="form-control validacampo" onblur="valida_campo(this,2)"
                                       :disabled="visualizar"
                                       v-model="dependente.outro_tipo">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-6">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" class="form-control validacampo" onblur="valida_campo_vazio(this,2)"
                                       :disabled="visualizar"
                                       v-model="dependente.nome" placeholder="Ex: Ana">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label>CPF</label>
                                <input type="text" class="form-control validacampo" onblur="valida_cpf(this)"
                                       :disabled="visualizar"
                                       v-model="dependente.cpf" v-mascara:cpf
                                       placeholder="Informe o CPF">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
<!--                            <div class="form-group">-->
<!--                                <datepicker label="Data de Nascimento" posicao="up"-->
<!--                                            v-model="dependente.nascimento"></datepicker>-->
<!--                            </div>-->
                            <div class="form-group">
                                <label>Data de Nascimento</label>
                                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                       v-model="dependente.nascimento" v-mascara:data
                                       :disabled="visualizar"
                                       @keyup.prevent="valida_data($event.target, true)"
                                       @blur.prevent="valida_data($event.target, true)">
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-lg-12">
                            <div class="form-group">
                                <label>Observação</label>
                                <textarea class="form-control validacampo" :disabled="visualizar" v-model="dependente.observacao" rows="3" cols="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row" v-show="model.length > 0">
                        <div class="col-12 mb-3">
                            <button v-if="!visualizar" class="btn btn-sm btn-danger mb-2 mr-1" type="button" @click.prevent="remove(index)">
                                <span class="fas fa-times" aria-hidden="true"></span>
                                REMOVER
                            </button>
                            <button v-if="!visualizar" class="btn btn-sm btn-secondary mb-2"
                                    @click="add()">
                                <span class="fas fa-plus" aria-hidden="true"></span>
                                ADICIONAR DEPENDENTE(S)
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
            tipos: [],
        }
    },
    computed: {
        lista() {
            return this.model;
        },
        listaDelete() {
            return this.modelDelete;
        }
    },
    mounted() {
        axios.get(`${URL_ADMIN}/admissao/tipos_dependentes`)
            .then(response => {
                this.tipos = response.data;
            }).catch(e => console.log(e));
    },
    methods: {
        add() {
            let op = {};
            op.nova = true;
            op.tipo = '';
            op.outro_tipo = '';
            op.nome = '';
            op.cpf = '';
            op.nascimento = '';
            op.observacao = '';

            this.lista.push(op);
        },
        remove(index) {
            this.$emit("ondelete", this.lista[index]);
            if (!this.lista.nova) {
                this.listaDelete.push(this.lista[index].id);
            }
            this.lista.splice(index, 1);
        },
    }

}
</script>

<!--<style scoped>-->

<!--</style>-->
