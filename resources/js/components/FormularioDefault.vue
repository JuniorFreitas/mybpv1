<template>
    <div class='row' id='formdinamico' v-if='model.formulario'>
        <div class='col-12'>
            <h4 v-show='mostra_titulo'>{{ model.formulario.titulo }}</h4>
            <fieldset v-for='(setor, index) in model.formulario.setores'>
            :key="setor.id || index"
                <legend>{{ setor.nome }}</legend>

                <div class='col-12 col-sm-6' v-for='(alternativa, index) in setor.alternativas'>
                :key="alternativa.id || index"

                    <div class='form-group' v-if='alternativa.tipo === "checkbox"'>
                        <div class='custom-control custom-switch'>
                            <input type='checkbox' class='custom-control-input'
                                   v-model='getResposta(alternativa.id).valor'
                                   :value='alternativa.id'
                                   :id='`alternativa_${alternativa.id}`'>
                            <label class='custom-control-label' style='cursor: pointer'
                                   :for='`alternativa_${alternativa.id}`'>
                                {{ alternativa.nome }}
                            </label>
                        </div>
                    </div>

                    <div class='form-group' v-if='alternativa.tipo === "select"'>
                        <label>{{ alternativa.nome }}</label>
                        <div v-if='alternativa.pivot.obrigatorio'>
                            <select class='form-control' v-model='getResposta(alternativa.id).valor'
                                    :onblur='`valida_campo_vazio(this, ${alternativa.pivot.min})`'
                                    :onchange='`valida_campo_vazio(this, ${alternativa.pivot.min})`'
                            >
                                <option v-for='(opcao,index) in alternativa.opcoes'
                                :key="opcao.id || index"
                                        :value='opcao.value'>
                                    {{ opcao.label }}
                                </option>
                            </select>
                        </div>

                        <div v-if='!alternativa.pivot.obrigatorio'>
                            <select class='form-control' v-model='getResposta(alternativa.id).valor'>
                                <option v-for='(opcao, index) in alternativa.opcoes' :value='opcao.id'>
                                :key="opcao.id || index"
                                    {{ opcao.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class='form-group' v-if='alternativa.tipo === "text"'>
                        <label>{{ alternativa.nome }}</label>
                        <template v-if='alternativa.pivot.obrigatorio'>
                            <input type='text' class='form-control' v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot.max'
                                   :onblur='`valida_campo_vazio(this, ${alternativa.pivot.min})`'>
                        </template>

                        <template v-if='!alternativa.pivot.obrigatorio'>
                            <input type='text' class='form-control' v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot.max'
                            >
                        </template>
                    </div>

                    <div class='form-group' v-if='alternativa.tipo === "textarea"'>
                        <label>{{ alternativa.nome }}</label>
                        <template v-if='alternativa.pivot.obrigatorio'>
                        <textarea class='form-control' cols='3' rows='3' :maxlength='alternativa.pivot.max'
                                  v-model='getResposta(alternativa.id).valor'
                                  :onblur='`valida_campo_vazio(this, ${alternativa.pivot.min})`'>
                        </textarea>
                        </template>

                        <template v-if='!alternativa.pivot.obrigatorio'>
                            <textarea class='form-control' cols='3' rows='3' v-model='getResposta(alternativa.id).valor'
                                      :maxlength='alternativa.pivot.max'></textarea>
                        </template>
                    </div>

                    <div class='form-group' v-if='alternativa.tipo === "number"'>
                        <label>{{ alternativa.nome }}</label>
                        <template v-if='alternativa.pivot.obrigatorio'>
                            <input type='number' class='form-control' v-mascara:numero
                                   v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot.max'
                                   :onblur='`valida_campo_vazio(this, ${alternativa.pivot.min})`'>
                        </template>

                        <template v-if='!alternativa.pivot.obrigatorio'>
                            <input type='number' class='form-control' v-mascara:numero
                                   v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot.max'>
                        </template>
                    </div>

                    <div class='form-group' v-if='alternativa.tipo === "float"'>
                        <label>{{ alternativa.nome }}</label>
                        <template v-if='alternativa.pivot.obrigatorio'>
                            <input type='float' class='form-control' v-mascara:dinheiro
                                   v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot.max'
                                   :onblur='`valida_campo_vazio(this, ${alternativa.pivot.min})`'>
                        </template>

                        <template v-if='!alternativa.pivot.obrigatorio'>
                            <input type='float' class='form-control' v-mascara:dinheiro
                                   v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot.max'>
                        </template>
                    </div>

                </div>
                <!--          <div class='form-group'>
                              <label>{{ formulario[0].nome }}</label>
                              <select
                                  class='form-control'
                                  onchange='valida_campo_vazio(this,1)'
                                  onblur='valida_campo_vazio(this,1)'
                                  :disabled='visualizar'
                              >
                                  <option value=''>Selecione</option>
                                  <option v-for='(tipoexame, index) in listaTiposExames' :value='tipoexame.id'>{{ tipoexame.label }}</option>
                                  :key="tipoexame.id || index"
                              </select>
                </div>-->
            </fieldset>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        visualizar: {
            type: Boolean,
            default: false
        },
        formulario_id: {
            type: Number,
            default: null
        },
        model: {
            type: Object,
            default: () => ({})
        },
        mostra_titulo: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            preload: false
        }
    },
    mounted() {
        let alternativas = []
        this.model.formulario.setores.forEach((item) => {
            alternativas = _.concat(alternativas, item.alternativas)
        })

        alternativas.forEach((item) => {
            let encontrou = _.find(this.model.respostas, resposta => resposta.alternativa_id === item.id)
            if (!encontrou) {
                let valor = ''
                if (item.tipo === 'checkbox') {
                    valor = false
                }
                if (item.tipo === 'select') {
                    valor = item.opcoes[0].value
                }

                this.model.respostas[`alternativa_id_${item.id}`] = {
                    tipo: item.tipo,
                    valor: valor,
                    alternativa_id: item.id
                }
            }
        })


    },
    methods: {
        getResposta(id) {
            if (!this.model.respostas) {
                return false
            }
            if (this.model.respostas['alternativa_id_' + id]) {
                return this.model.respostas['alternativa_id_' + id]
            }
            return false
        },

        getLink(id) {
            if (!this.model.respostas) {
                return false
            }
            if (this.model.respostas['alternativa_id_' + id]) {
                if (this.model.respostas['alternativa_id_' + id].link) {
                    return this.model.respostas['alternativa_id_' + id].link
                }
            }


            return false
        },
    }
}
</script>

<style scoped>

</style>
