<template>
    <div :model="model">
        <fieldset>
            <legend class="text-uppercase">Dados Bancários</legend>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label>Banco</label>
                    <input class="form-control" v-model="model.banco"
                           onblur="valida_campo_vazio(this,1)" type="text" :disabled="visualizar">
                </div>
                <div class="col-md-4 mb-2">
                    <label>Agência</label>
                    <input class="form-control" v-model="model.agencia"
                           onblur="valida_campo_vazio(this,1)" type="text" :disabled="visualizar">
                </div>
                <div class="col-md-4 mb-2">
                    <label>Conta</label>
                    <input class="form-control" v-model="model.conta"
                           onblur="valida_campo_vazio(this,1)" type="text" :disabled="visualizar">
                </div>
                <div class="col-md-4 mb-2">
                    <label>Tem PIX?</label>
                    <select class="form-control" v-model="model.pix" onblur="valida_campo_vazio(this,1)"
                            onchange="valida_campo_vazio(this,1)" :disabled="visualizar">
                        <option :value="''">Selecione</option>
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2" v-show="model.pix === true">
                    <label>Tipo de Chave</label>
                    <input class="form-control" v-model="model.tipochavepix"
                           onblur="valida_campo_vazio(this,1)" type="text" :disabled="visualizar">
                </div>
                <div class="col-md-4 mb-2" v-show="model.pix === true">
                    <label>Chave do Pix</label>
                    <input class="form-control" v-model="model.chavepix"
                           onblur="valida_campo_vazio(this,1)" type="text" :disabled="visualizar">
                </div>
            </div>
        </fieldset>
    </div>
</template>

<script>
export default {
    props: {
        model: {
            type: Object,
            required: true,
            default: () => {
                return {
                    banco: '',
                    agencia: '',
                    conta: '',
                    pix: '',
                    tipochavepix: '',
                    chavepix: '',
                }
            }
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


}
</script>

<style scoped>

</style>
