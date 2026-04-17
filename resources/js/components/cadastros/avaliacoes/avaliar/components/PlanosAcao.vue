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
                        <label class="d-block" :for="'ma-pdi-topico-' + index">Competência ou critério relacionado</label>
                        <combobox-auto-complete
                            :instance-id="'pdi-topico-' + index"
                            :input-id="'ma-pdi-topico-' + index"
                            v-model="item.topico_id"
                            :options="topicosComboboxOpcoes"
                            :disabled="!podeEditar"
                            placeholder-blur="Selecione a competência"
                            placeholder-focus="Digite para filtrar…"
                            empty-message="Nenhuma competência encontrada."
                            :max-results="200"
                            wrapper-class="ma-pdi-topico-combo"
                            @select="() => validarTopicoAposSelecao(index)"
                        />
                        <h5 class="my-3 text-danger" v-if="item.topico_id && resultTopico[item.topico_id]">
                            Média neste critério: {{ getMediaTopico(item.topico_id, resultTopico) }}
                        </h5>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Descrição do plano de ação</label>
                        <div v-if="podeEditar" :key="'editor-pdi-' + index + '-' + (item.id || 'novo')" class="ma-pdi-editor-wrap">
                            <editor :api-key="editorPlanoInit.key" v-model="item.plano_de_acao" :init="editorPlanoInit" />
                        </div>
                        <div
                            v-else
                            class="form-control form-control-sm ma-plano-acao-html border rounded p-2 bg-light"
                            style="min-height: 120px"
                        >
                            <div v-if="planoAcaoTemConteudo(item)" v-html="item.plano_de_acao"></div>
                            <span v-else class="text-muted">—</span>
                        </div>
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
import ComboboxAutoComplete from '../../../../ComboboxAutoComplete'
import Editor from '@tinymce/tinymce-vue'
import { tinyPadrao } from '../../../../../utils'
import avaliacaoMixin from '../mixins/avaliacaoMixin'

export default {
    name: 'PlanosAcao',
    components: {
        DatePicker,
        ComboboxAutoComplete,
        Editor
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
    data() {
        return {
            /** Mesmo preset de vagas abertas (`tinyPadrao`), altura menor para o modal do PDI. */
            editorPlanoInit: {
                ...tinyPadrao,
                height: 280,
                resize: true
            }
        }
    },
    computed: {
        podeEditar() {
            return !this.visualizando
        },
        topicosComboboxOpcoes() {
            const raw = this.resultTopico && typeof this.resultTopico === 'object' ? this.resultTopico : {}
            const opts = [{ value: '', label: 'Selecione a competência' }]
            for (const id of Object.keys(raw)) {
                const t = raw[id]
                if (!t) {
                    continue
                }
                opts.push({
                    value: id,
                    label: `${t.topico_pai} - ${t.subtopico}`,
                    raw: { id, ...t }
                })
            }
            return opts
        }
    },
    methods: {
        planoAcaoTemConteudo(item) {
            const h = item?.plano_de_acao
            if (h == null || String(h).trim() === '') {
                return false
            }
            const texto = String(h).replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').trim()
            return texto !== ''
        },
        validarTopicoAposSelecao(index) {
            this.$nextTick(() => {
                const el = document.getElementById(`ma-pdi-topico-${index}`)
                if (el && typeof valida_campo_vazio === 'function') {
                    valida_campo_vazio(el, 1)
                }
            })
        }
    }
}
</script>

<style scoped>
.ma-pdi-topico-combo {
    max-width: 100%;
}
.ma-pdi-editor-wrap {
    max-width: 100%;
}
.ma-plano-acao-html :deep(p) {
    margin-bottom: 0.35rem;
}
.ma-plano-acao-html :deep(p:last-child) {
    margin-bottom: 0;
}
</style>
