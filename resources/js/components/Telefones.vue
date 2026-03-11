<template>
    <div :model="model" :modelDelete="modelDelete">
        <div class="mb-3">
            <!--<label>Telefone</label><br>-->
            <button class="btn btn-sm mr-1 btn-secondary" type="button" @click.prevent="add()"
                    v-show="model.length < qnt_max">
                <span class="fas fa-plus" aria-hidden="true"></span>
                Adicionar
            </button>
        </div>
        <div>
            <div class="row mb-2" v-for="(tel, index) in lista" :key="tel.id || index">
                <div class="col-12">
                    <div class="form-inline tels pb-2">
                        <button class="btn btn-sm mr-1 btn-danger mb-2 mr-1" type="button" @click.prevent="remove(index)"
                                v-show="model.length > qnt_min">
                            <span class="fas fa-times" aria-hidden="true"></span>

                        </button>

                        <select class="form-control mb-2 mr-sm-2" :disabled="disabled" v-model="tel.tipo">
                            <option value="whatsapp">WhatsApp</option>
                            <option value="celular">Celular</option>
                            <option value="residencial">Residencial</option>
                            <option value="comercial">Comercial</option>
                        </select>

                        <div class="input-group mb-2 mr-sm-2" v-if="pais">
                            <div class="input-group-prepend">
                                <div class="input-group-text">País +</div>
                            </div>
                            <input type="text" class="form-control pais" :disabled="disabled" v-model="tel.pais">
                        </div>

                        <div class="input-group mb-2 mr-sm-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-phone"></i></div>
                            </div>
                            <input type="text" class="form-control telefone" :disabled="disabled" v-mascara:telefone
                                   onblur="valida_telefone_vazio(this)" v-model="tel.numero">
                        </div>

                        <div class="input-group mb-2 mr-sm-2" v-if="ramal">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Ramal</div>
                            </div>
                            <input type="text" class="form-control ramal" :disabled="disabled" v-model="tel.ramal">
                        </div>

                        <div class="input-group mb-2 mr-sm-2" v-if="detalhe">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Obs</div>
                            </div>
                            <input type="text" class="form-control" :disabled="disabled" v-model="tel.detalhe">
                        </div>

                        <div class="custom-control custom-switch">
                            <input type="checkbox" @click="marcaPrincipal(tel)" :disabled="disabled" :value="tel.principal" v-model="tel.principal"
                                   class="custom-control-input" :id="index">
                            <label class="custom-control-label"
                                   :for="index">Principal</label>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </div>


</template>

<script>
export default {
    props: {
        model: {
            type: Array,
            required: true,
            default: () => []
        },
        modelDelete: {
            type: Array,
            required: false,
            default: () => []
        },
        ramal: {
            type: Boolean,
            default: () => true

        },
        pais: {
            type: Boolean,
            default: () => true

        },
        detalhe: {
            type: Boolean,
            default: () => true

        },

        qnt_min: {
            type: Number,
            default: () => 0,
            required: false
        },

        qnt_max: {
            type: Number,
            required: false,
            default: () => 99999
        },

        principal: {
            type: Boolean,
            required: false,
            default: () => false
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        }

    },
    data() {
        return {
            hash: parseInt((Math.random() * 999999)),
        }
    },
    computed: {
        lista: function () {
            return this.model;
        },
        listaDelete: function () {
            return this.modelDelete;
        },
    },
    methods: {
        marcaPrincipal(tel) {
            this.lista.forEach((obj) => {
                obj.principal = false;
            });
            tel.principal = !tel.principal;
        },
        add() {
            let op = {};
            op.id = 0;
            op.nova = true;
            op.tipo = 'residencial';
            op.pais = '55';
            op.numero = '';
            op.ramal = '';
            op.detalhe = '';
            op.principal = false;

            //this.lista.push(op);
            this.lista.push(op);
        },
        remove(index) {
            this.$emit("ondelete", this.lista[index]);
            if (this.lista[index].id) {
                this.listaDelete.push(this.lista[index].id);
            }
            this.lista.splice(index, 1);
            //Fazer um find this.lista se tem algum com principal = true
            //this.lista[0].principal = true;
        },
    }

}
</script>

<style scoped>
.bt {
    margin-bottom: 10px;
}

.tels {
    border-bottom: 1px dashed #999999;
    margin-bottom: 10px;
}

.pais {
    width: 46px;
}

.ramal {
    width: 75px;
}

.telefone {
    /*width: 134px;*/
}

</style>
