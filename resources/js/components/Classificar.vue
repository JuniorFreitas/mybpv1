<template>
    <div>
        <modal :id="`janelaProximaEtapa_${hash}`" titulo="Proxima etapa" :modal-pai="`${janelapai}`" :fechar="!formClassificar.preload" size="g">
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
                                v-model="formClassificar.etapa"
                                onblur="valida_campo_vazio(this, 1)"
                                onchange="valida_campo_vazio(this, 1)"
                            >
                                <option value="">Selecione</option>
                                <option value="Prova presencial">Prova presencial</option>
                                <option value="Teste de digitação">Teste de digitação</option>
                                <option value="Dinâmica de grupo">Dinâmica de grupo</option>
                                <option value="Entrevista Individual">Entrevista Individual</option>
                                <option value="Entrevista RH">Entrevista RH</option>
                                <option value="Entrevista Gestor">Entrevista Gestor</option>
                                <option value="Apto para Admissao">Apto para Admissao</option>
                                <option value="Aviso Recesso">Aviso Recesso</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Preenchido por: </label>
                            <input type="text" class="form-control" v-model="formClassificar.preenchido_por" onblur="valida_campo_vazio(this, 1)" />
                        </div>

                        <div class="form-group" v-if="usuario_id === 46">
                            <label>Enviar e-mail:</label>
                            <select class="form-control" v-model="formClassificar.enviarEmail">
                                <option :value="true">Sim</option>
                                <option :value="false">Não</option>
                            </select>
                        </div>

                        <!--                        <div class="form-group" v-if="formClassificar.etapa === 'Apto para Admissao'">-->
                        <!--                            <label>Enviar e-mail:</label>-->
                        <!--                            <select class="form-control" v-model="formClassificar.enviaWhats">-->
                        <!--                                <option :value="true">Sim</option>-->
                        <!--                                <option :value="false">Não</option>-->
                        <!--                            </select>-->
                        <!--                        </div>-->

                        <div class="form-group">
                            <label>Observação: </label>
                            <input type="text" class="form-control" v-model="formClassificar.observacao" />
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-primary" v-show="!formClassificar.preload" @click="classificar">
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
        >
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
                            v-model="formDesclassificar.etapa"
                            onblur="valida_campo_vazio(this, 1)"
                            onchange="valida_campo_vazio(this, 1)"
                        >
                            <option value="">Selecione</option>
                            <option value="Desclassificado em prova presencial">Desclassificado em prova online</option>
                            <option value="Desclassificado em prova presencial">Desclassificado em prova presencial</option>
                            <option value="Desclassificado em teste de digitação">Desclassificado em teste de digitação</option>
                            <option value="Desclassificado em dinâmica de grupo">Desclassificado em dinâmica de grupo</option>
                            <option value="Desclassificado em entrevista Individual">Desclassificado em entrevista Individual</option>
                            <option value="Desclassificado em entrevista RH">Desclassificado em entrevista RH</option>
                            <option value="Desclassificado em entrevista Gestor">Desclassificado em entrevista Gestor</option>
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
                <button type="button" class="btn btn-sm btn-primary" v-show="!formDesclassificar.preload" @click="desclassificar">Sim</button>
            </template>
        </modal>

        <button class="btn btn-primary" data-toggle="modal" :data-target="`#janelaProximaEtapa_${hash}`">Classificar</button>
        <button class="btn btn-danger" data-toggle="modal" :data-target="`#janelaDesclassificar_${hash}`">Desclassificar</button>
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
            formClassificar: {
                feedback_id: '',
                user_id: '',
                vaga_id: '',
                etapa: '',
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
    methods: {
        desclassificar() {
            this.formDesclassificar.feedback_id = this.model.id
            this.formDesclassificar.vaga_id = this.model.vaga_id

            $(`#janelaDesclassificar_${this.hash} :input:visible`).trigger('blur')
            if ($(`#janelaDesclassificar_${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.formDesclassificar.preload = true

            axios
                .post(`${URL_ADMIN}/etapa/${this.model.id}/desclassificar`, this.formDesclassificar)
                .then((response) => {
                    $(`#janelaDesclassificar_${this.hash}`).modal('hide')
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
                })
        },

        classificar() {
            this.formClassificar.feedback_id = this.model.id
            this.formClassificar.vaga_id = this.model.vaga_id

            $(`#janelaProximaEtapa_${this.hash} :input:visible`).trigger('blur')
            if ($(`#janelaProximaEtapa_${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.formClassificar.preload = true

            axios
                .post(`${URL_ADMIN}/etapa/${this.model.id}/classificar`, this.formClassificar)
                .then((response) => {
                    $(`#janelaProximaEtapa_${this.hash}`).modal('hide')
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
                })
        }
    }
}
</script>
