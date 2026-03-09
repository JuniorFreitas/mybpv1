<template>
    <fieldset>
        <legend>Oportunidades de Melhoria / Plano de Ação</legend>

        <button
            class="btn btn-sm mr-1 btn-primary mb-2"
            @click="$emit('adicionar')"
            v-show="!visualizando"
        >
            <i class="fa fa-plus"></i> Adicionar Plano
        </button>

        <fieldset v-for="(item, index) in planos" :key="item.id || index">
            <legend>Plano - {{ index + 1 }}</legend>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Competência/Desempenho</label>
                        <select
                            class="form-control form-control-sm validacampo"
                            v-model="item.topico_id"
                            :disabled="visualizando"
                            @blur.prevent="validarCampo($event.target)"
                            @change.prevent="validarCampo($event.target)"
                        >
                            <option value="">Selecione</option>
                            <option
                                v-for="(topico, topico_id) in resultTopico"
                                :key="topico_id"
                                :value="topico_id"
                            >
                                {{ topico.topico_pai }} - {{ topico.subtopico }}
                            </option>
                        </select>
                        <h5
                            class="my-3 text-danger"
                            v-if="item.topico_id && resultTopico[item.topico_id]"
                        >
                            Média: {{ getMediaTopico(item.topico_id, resultTopico) }}
                        </h5>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="">Plano de Ação</label>
                        <textarea
                            rows="5"
                            class="form-control form-control-sm validacampo"
                            v-model="item.plano_de_acao"
                            @blur.prevent="validarCampo($event.target)"
                            :disabled="visualizando"
                        ></textarea>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <date-picker
                            formsm
                            label="Início"
                            v-model="item.inicio"
                            :disabled="visualizando"
                        ></date-picker>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <date-picker
                            formsm
                            label="Término"
                            v-model="item.termino"
                            :disabled="visualizando"
                        ></date-picker>
                    </div>
                </div>

                <div class="col-lg-12" v-show="!visualizando">
                    <button
                        class="btn btn-sm mr-1 btn-danger"
                        @click="$emit('remover', index)"
                    >
                        <i class="fa fa-trash"></i> Apagar
                    </button>
                </div>
            </div>
        </fieldset>
    </fieldset>
</template>

<script>
import DatePicker from '../../../../DatePicker'
import avaliacaoMixin from '../mixins/avaliacaoMixin'

export default {
    name: 'PlanosAcao',
    components: {
        DatePicker
    },
    mixins: [avaliacaoMixin],
    props: {
        planos: {
            type: Array,
            default: () => []
        },
        resultTopico: {
            type: Object,
            default: () => ({})
        },
        visualizando: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        validarCampo(target) {
            if (typeof valida_campo_vazio === 'function') {
                valida_campo_vazio(target, 1)
            }
        }
    }
}
</script>
