<template>
    <fieldset class="ma-modal-fieldset mt-4">
        <legend class="ma-modal-legend">{{ titulo }}</legend>

        <p v-if="descricao" class="ma-modal-lead">
            {{ descricao }}
        </p>

        <button class="btn btn-sm mr-1 btn-primary mb-2" @click="$emit('adicionar')" v-show="podeEditar"><i class="fa fa-plus"></i> Adicionar Plano</button>

        <fieldset v-for="(item, index) in planos" :key="index" class="ma-modal-plano-item">
            <legend class="ma-modal-legend ma-modal-legend--sub">Plano {{ index + 1 }}</legend>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Competência ou critério relacionado</label>
                        <select
                            class="form-control form-control-sm validacampo"
                            v-model="item.topico_id"
                            :disabled="!podeEditar"
                            @blur.prevent="validarCampo($event.target)"
                            @change.prevent="validarCampo($event.target)"
                        >
                            <option value="">Selecione a competência</option>
                            <option v-for="(topico, topico_id) in resultTopico" :key="topico_id" :value="topico_id">
                                {{ topico.topico_pai }} - {{ topico.subtopico }}
                            </option>
                        </select>
                        <h5 class="my-3 text-danger" v-if="item.topico_id && resultTopico[item.topico_id]">
                            Média neste critério: {{ getMediaTopico(item.topico_id, resultTopico) }}
                        </h5>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Descrição do plano de ação</label>
                        <textarea
                            rows="5"
                            class="form-control form-control-sm validacampo"
                            v-model="item.plano_de_acao"
                            @blur.prevent="validarCampo($event.target)"
                            :disabled="!podeEditar"
                            placeholder="O que será feito, como será acompanhado e o resultado esperado."
                        ></textarea>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <date-picker formsm label="Início" v-model="item.inicio" :disabled="!podeEditar"></date-picker>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <date-picker formsm label="Término" v-model="item.termino" :disabled="!podeEditar"></date-picker>
                    </div>
                </div>

                <div class="col-lg-12" v-show="podeEditar">
                    <button type="button" class="btn btn-sm mr-1 btn-outline-danger" @click="$emit('remover', index)">
                        <i class="fa fa-trash"></i> Remover este plano
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
        },
        titulo: {
            type: String,
            default: 'Plano de ação e oportunidades de melhoria'
        },
        descricao: {
            type: String,
            default: 'Registre ações objetivas, prazos e responsáveis para apoiar o desenvolvimento nos pontos que precisam evoluir.'
        }
    },
    computed: {
        podeEditar() {
            return !this.visualizando
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
