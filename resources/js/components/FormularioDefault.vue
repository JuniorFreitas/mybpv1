<template>
    <div class='row' id='formdinamico' v-if='model.formulario'>
        <div class='col-12'>
            <h4 v-show='mostra_titulo'>{{ model.formulario.titulo }}</h4>
            <fieldset v-for='(setor, index) in model.formulario.setores' :key="setor.id || index">
                <legend>{{ setor.nome }}</legend>

                <div class='col-12 col-sm-6' v-for='(alternativa, altIndex) in setor.alternativas' :key="alternativa.id || altIndex">

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
                        <div v-if='alternativa.pivot && alternativa.pivot.obrigatorio'>
                            <select class='form-control' v-model='getResposta(alternativa.id).valor'
                                    :onblur='`valida_campo_vazio(this, ${alternativa.pivot.min || 1})`'
                                    :onchange='`valida_campo_vazio(this, ${alternativa.pivot.min || 1})`'
                            >
                                <option v-for='(opcao, opIndex) in (alternativa.opcoes || [])'
                                        :key="opcao.id || opIndex"
                                        :value='opcao.value'>
                                    {{ opcao.label }}
                                </option>
                            </select>
                        </div>

                        <div v-else>
                            <select class='form-control' v-model='getResposta(alternativa.id).valor'>
                                <option v-for='(opcao, opIndex) in (alternativa.opcoes || [])'
                                        :key="opcao.id || opIndex"
                                        :value='opcao.value != null ? opcao.value : opcao.id'>
                                    {{ opcao.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class='form-group' v-if='alternativa.tipo === "text"'>
                        <label>{{ alternativa.nome }}</label>
                        <template v-if='alternativa.pivot && alternativa.pivot.obrigatorio'>
                            <input type='text' class='form-control' v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot.max'
                                   :onblur='`valida_campo_vazio(this, ${alternativa.pivot.min || 1})`'>
                        </template>

                        <template v-else>
                            <input type='text' class='form-control' v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot && alternativa.pivot.max'>
                        </template>
                    </div>

                    <div class='form-group' v-if='alternativa.tipo === "textarea"'>
                        <label>{{ alternativa.nome }}</label>
                        <template v-if='alternativa.pivot && alternativa.pivot.obrigatorio'>
                        <textarea class='form-control' cols='3' rows='3' :maxlength='alternativa.pivot.max'
                                  v-model='getResposta(alternativa.id).valor'
                                  :onblur='`valida_campo_vazio(this, ${alternativa.pivot.min || 1})`'>
                        </textarea>
                        </template>

                        <template v-else>
                            <textarea class='form-control' cols='3' rows='3' v-model='getResposta(alternativa.id).valor'
                                      :maxlength='alternativa.pivot && alternativa.pivot.max'></textarea>
                        </template>
                    </div>

                    <div class='form-group' v-if='alternativa.tipo === "number"'>
                        <label>{{ alternativa.nome }}</label>
                        <template v-if='alternativa.pivot && alternativa.pivot.obrigatorio'>
                            <input type='number' class='form-control' v-mascara:numero
                                   v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot.max'
                                   :onblur='`valida_campo_vazio(this, ${alternativa.pivot.min || 1})`'>
                        </template>

                        <template v-else>
                            <input type='number' class='form-control' v-mascara:numero
                                   v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot && alternativa.pivot.max'>
                        </template>
                    </div>

                    <div class='form-group' v-if='alternativa.tipo === "float"'>
                        <label>{{ alternativa.nome }}</label>
                        <template v-if='alternativa.pivot && alternativa.pivot.obrigatorio'>
                            <input type='float' class='form-control' v-mascara:dinheiro
                                   v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot.max'
                                   :onblur='`valida_campo_vazio(this, ${alternativa.pivot.min || 1})`'>
                        </template>

                        <template v-else>
                            <input type='float' class='form-control' v-mascara:dinheiro
                                   v-model='getResposta(alternativa.id).valor'
                                   :maxlength='alternativa.pivot && alternativa.pivot.max'>
                        </template>
                    </div>

                </div>
            </fieldset>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FormularioDefault',
    props: {
        model: {
            type: Object,
            required: true
        },
        mostra_titulo: {
            type: Boolean,
            default: true
        },
        formulario_id: {
            type: [Number, String],
            default: null
        }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            preload: false
        }
    },
    mounted() {
        this.inicializarRespostas()
    },
    watch: {
        'model.formulario': {
            deep: true,
            handler() {
                this.inicializarRespostas()
            }
        }
    },
    methods: {
        inicializarRespostas() {
            if (!this.model?.formulario?.setores) {
                return
            }
            if (!this.model.respostas) {
                this.model.respostas = {}
            }
            let alternativas = []
            this.model.formulario.setores.forEach((item) => {
                alternativas = _.concat(alternativas, item.alternativas || [])
            })

            alternativas.forEach((item) => {
                let encontrou = _.find(this.model.respostas, resposta => resposta && resposta.alternativa_id === item.id)
                if (!encontrou) {
                    let valor = ''
                    if (item.tipo === 'checkbox') {
                        valor = false
                    }
                    if (item.tipo === 'select' && item.opcoes && item.opcoes.length) {
                        valor = item.opcoes[0].value != null ? item.opcoes[0].value : item.opcoes[0].id
                    }

                    this.model.respostas[`alternativa_id_${item.id}`] = {
                        tipo: item.tipo,
                        valor: valor,
                        alternativa_id: item.id
                    }
                }
            })
        },
        getResposta(id) {
            if (!this.model.respostas) {
                this.model.respostas = {}
            }
            if (!this.model.respostas['alternativa_id_' + id]) {
                this.model.respostas['alternativa_id_' + id] = {
                    tipo: 'text',
                    valor: '',
                    alternativa_id: id
                }
            }
            return this.model.respostas['alternativa_id_' + id]
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
