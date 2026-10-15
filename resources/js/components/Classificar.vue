<template>
    <div>
        <modal :id="`janelaProximaEtapa_${hash}`" titulo="Proxima etapa" :modal-pai="`${janelapai}`" :fechar="!formClassificar.preload" size="g" ref="modal_`janelaProximaEtapa_${hash}`">
            <template #conteudo>
                <preload
                    v-show="formClassificar.preload"
                    :label="formClassificar.enviarEmail ? 'Registrando e enviando a notificacao' : 'Registrando'"
                ></preload>
                <div v-show="!formClassificar.preload">
                    <fieldset>
                        <div class="form-group">
                            <label
                                >Para: {{ model.curriculo.nome }} - <small>({{ model.curriculo.email }})</small></label
                            >
                            <br />
                            <label>Mensagem: <small class="text-danger">que será enviada como corpo do e-mail</small></label>
                            <textarea class="form-control" cols="5" rows="5" v-model="formClassificar.mensagem"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Próxima etapa:</label>
                            <select
                                class="form-control"
                                v-model="formClassificar.passo_id"
                                onblur="valida_campo_vazio(this, 1)"
                                onchange="valida_campo_vazio(this, 1)"
                                @change="onChangeEtapaAvanco"
                            >
                                <option value="">Selecione</option>
                                <option
                                    v-for="etapa in etapasAvanco"
                                    :key="'av-' + etapa.id"
                                    :value="etapa.id"
                                    :disabled="etapaBloqueada(etapa)"
                                >
                                    {{ etapa.nome }}{{ etapaBloqueada(etapa) ? ' (bloqueada)' : '' }}
                                </option>
                            </select>
                            <small class="text-danger d-block" v-if="motivoBloqueioSelecionado">{{ motivoBloqueioSelecionado }}</small>
                        </div>

                        <div class="form-group">
                            <label>Preenchido por: </label>
                            <input type="text" class="form-control" v-model="formClassificar.preenchido_por" onblur="valida_campo_vazio(this, 1)" />
                        </div>

                        <div class="form-group" v-if="usuario_id === 46 || etapaSelecionadaPermiteEmail">
                            <label>Enviar e-mail:</label>
                            <select class="form-control" v-model="formClassificar.enviarEmail">
                                <option :value="true">Sim</option>
                                <option :value="false">Não</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Observação: </label>
                            <input type="text" class="form-control" v-model="formClassificar.observacao" />
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!formClassificar.preload" @click="classificar">
                    <i class="fa fa-save"></i> Salvar e enviar mensagem
                </button>
            </template>
        </modal>
        <modal
            :id="`janelaDesclassificar_${hash}`"
            :titulo="`Desclassificar ${model.curriculo.nome}`"
            :fechar="!formDesclassificar.preload"
            label-fechar="Não"
            :modal-pai="`${janelapai}`"
         ref="modal_`janelaDesclassificar_${hash}`">
            <template #conteudo>
                <span v-show="formDesclassificar.preload"
                    ><i class="fa fa-spinner fa-pulse"></i> Registrando<span v-show="formDesclassificar.enviarEmail"> e enviando o e-mail</span>...</span
                >
                <div v-show="!formDesclassificar.preload">
                    <h4 class="text-center">
                        Você tem certeza que deseja desclassificar <br />
                        <span class="text-danger">{{ model.curriculo.nome }}</span
                        >?
                    </h4>

                    <div class="form-group">
                        <label>Qual etapa?</label>
                        <select
                            class="form-control"
                            v-model="formDesclassificar.passo_id"
                            onblur="valida_campo_vazio(this, 1)"
                            onchange="valida_campo_vazio(this, 1)"
                            @change="onChangeEtapaDesclass"
                        >
                            <option value="">Selecione</option>
                            <option v-for="etapa in etapasDesclass" :key="'dc-' + etapa.id" :value="etapa.id">
                                {{ etapa.nome }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Preenchido por: </label>
                        <input type="text" class="form-control" v-model="formDesclassificar.preenchido_por" onblur="valida_campo_vazio(this, 1)" />
                    </div>

                    <div class="form-group" v-if="usuario_id === 46">
                        <label>Enviar e-mail:</label>
                        <select class="form-control" v-model="formDesclassificar.enviarEmail">
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Observação: </label>
                        <input type="text" class="form-control" v-model="formDesclassificar.observacao" />
                    </div>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!formDesclassificar.preload" @click="desclassificar">Sim</button>
            </template>
        </modal>

        <button class="btn btn-primary" @click="abrirClassificar">Classificar</button>
        <button class="btn btn-danger" @click="abrirDesclassificar">Desclassificar</button>
    </div>
</template>

<style scoped></style>

<script>
export default {
    props: {
        model: {
            type: Object,
            required: true,
            default: () => {}
        },
        simulado_id: {
            type: Number,
            required: false,
            default: 0
        },
        janelapai: {
            type: String,
            required: false,
            default: ''
        },
        usuario_id: {
            type: Number,
            required: false,
            default: 0
        }
    },

    data() {
        return {
            hash: `${parseInt(Math.random() * 999999)}`,
            carregandoEtapas: false,
            etapasAvanco: [],
            etapasDesclass: [],
            progressoMap: {},
            formClassificar: {
                feedback_id: '',
                user_id: '',
                vaga_id: '',
                etapa: '',
                passo_id: '',
                mensagem: '',
                enviado_email: '',
                text_email: '',
                observacao: '',
                preenchido_por: '',
                status: '',

                enviarEmail: true,
                simulado_id: 0,
                preload: false
            },

            formClassificarDefault: null,

            formDesclassificar: {
                feedback_id: '',
                vaga_id: '',
                etapa: '',
                passo_id: '',
                enviado_email: '',
                text_email: '',
                observacao: '',
                preenchido_por: '',
                status: '',
                enviarEmail: true,

                simulado_id: 0,
                preload: false
            },

            formDesclassificarDefault: null
        }
    },
    computed: {
        etapaSelecionada() {
            const id = Number(this.formClassificar.passo_id)
            return this.etapasAvanco.find((e) => Number(e.id) === id) || null
        },
        etapaSelecionadaPermiteEmail() {
            return this.etapaSelecionada ? this.etapaSelecionada.permite_email !== false : true
        },
        motivoBloqueioSelecionado() {
            if (!this.etapaSelecionada) return ''
            const p = this.progressoMap[this.etapaSelecionada.id]
            if (p && p.liberada === false) return p.motivo_bloqueio || 'Etapa bloqueada por dependência'
            return ''
        }
    },
    mounted() {
        this.formClassificarDefault = _.cloneDeep(this.formClassificar)
        this.formDesclassificarDefault = _.cloneDeep(this.formDesclassificar)
    },
    methods: {
        etapaBloqueada(etapa) {
            const p = this.progressoMap[etapa.id]
            return !!(p && p.liberada === false)
        },
        onChangeEtapaAvanco() {
            const etapa = this.etapaSelecionada
            this.formClassificar.etapa = etapa ? etapa.nome : ''
            if (etapa && etapa.permite_email === false) {
                this.formClassificar.enviarEmail = false
            }
        },
        onChangeEtapaDesclass() {
            const id = Number(this.formDesclassificar.passo_id)
            const etapa = this.etapasDesclass.find((e) => Number(e.id) === id)
            this.formDesclassificar.etapa = etapa ? etapa.nome : ''
        },
        abrirClassificar() {
            this.carregarEtapas().then(() => {
                this.$refs[`janelaProximaEtapa_${this.hash}`] && this.$refs[`janelaProximaEtapa_${this.hash}`].abrirModal()
            })
        },
        abrirDesclassificar() {
            this.carregarEtapas().then(() => {
                this.$refs[`janelaDesclassificar_${this.hash}`] && this.$refs[`janelaDesclassificar_${this.hash}`].abrirModal()
            })
        },
        carregarEtapas() {
            if (!this.model?.id) {
                return Promise.resolve()
            }
            this.carregandoEtapas = true
            return axios
                .get(`${URL_ADMIN}/etapa/workflow`, {
                    params: {
                        feedback_id: this.model.id,
                        vaga_id: this.model.vaga_id || null
                    }
                })
                .then((r) => {
                    const data = r.data || {}
                    // Avanços manuais + apto (sistema classificado via Classificar)
                    const avanco = (data.avanco || []).filter((e) => {
                        if (e.tipo === 'sistema') {
                            return e.chave === 'apto_admissao' || e.is_apto_admissao
                        }
                        return e.tipo === 'avanco'
                    })
                    this.etapasAvanco = avanco
                    this.etapasDesclass = data.desclassificacao || []
                    this.progressoMap = {}
                    ;(data.progresso?.etapas || []).forEach((p) => {
                        this.progressoMap[p.id] = p
                    })
                    this.carregandoEtapas = false
                })
                .catch(() => {
                    this.carregandoEtapas = false
                })
        },
        desclassificar() {
            this.formDesclassificar.feedback_id = this.model.id
            this.formDesclassificar.vaga_id = this.model.vaga_id
            this.onChangeEtapaDesclass()

            $(`#janelaDesclassificar_${this.hash} :input:visible`).trigger('blur')
            if ($(`#janelaDesclassificar_${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.formDesclassificar.preload = true

            axios
                .post(`${URL_ADMIN}/etapa/${this.model.id}/desclassificar`, this.formDesclassificar)
                .then((response) => {
                    this.$refs[`janelaDesclassificar_${this.hash}`] && this.$refs[`janelaDesclassificar_${this.hash}`].fecharModal()
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    let resposta = {
                        id: this.model.id,
                        simulado_id: this.simulado_id,
                        status: 'desclassificado'
                    }
                    this.$emit('salvou', resposta)
                    this.formDesclassificar.preload = false
                })
                .catch((error) => {
                    this.formDesclassificar.preload = false
                    const msg = error?.response?.data?.msg
                    if (msg) mostraErro('', msg)
                })
        },

        classificar() {
            this.formClassificar.feedback_id = this.model.id
            this.formClassificar.vaga_id = this.model.vaga_id
            this.onChangeEtapaAvanco()

            if (this.motivoBloqueioSelecionado) {
                mostraErro('', this.motivoBloqueioSelecionado)
                return false
            }

            $(`#janelaProximaEtapa_${this.hash} :input:visible`).trigger('blur')
            if ($(`#janelaProximaEtapa_${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.formClassificar.preload = true

            axios
                .post(`${URL_ADMIN}/etapa/${this.model.id}/classificar`, this.formClassificar)
                .then((response) => {
                    this.$refs[`janelaProximaEtapa_${this.hash}`] && this.$refs[`janelaProximaEtapa_${this.hash}`].fecharModal()
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    let resposta = {
                        id: this.model.id,
                        simulado_id: this.simulado_id,
                        status: 'classificado'
                    }
                    this.$emit('salvou', resposta)
                    this.formClassificar.preload = false
                })
                .catch((error) => {
                    this.formClassificar.preload = false
                    const msg = error?.response?.data?.msg
                    if (msg) mostraErro('', msg)
                })
        }
    }
}
</script>
