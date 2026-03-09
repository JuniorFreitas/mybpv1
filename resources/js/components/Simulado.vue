<template>
    <div>
        <modal id="janelaMensagemFinalizar" :fechar="false" titulo="ATENÇÃO!" ref="modal_janelaMensagemFinalizar">
            <template #conteudo>
                <p v-if="finalizando"><i class="fa fa-spinner fa-pulse"></i> Finalizando Aguarde...</p>
                <div class="text-center" v-if="!finalizando">
                    <h5 class="text-danger">Você realmente deseja finalizar a prova?</h5>
                    <img :src="`${URL_SITE}/imagens/bepinhas/branca_2.png`" class="img-fluid mx-auto d-flex" alt="bepinha" />
                </div>
            </template>
            <template #rodape>
                <button type="button" v-show="!finalizando" class="btn btn-primary" @click="finalizarSimulado">Sim</button>
                <button type="button" v-show="!finalizando" class="btn btn-primary" data-dismiss="modal">Revisar</button>
            </template>
        </modal>

        <div class="row">
            <p class="col-12" v-show="carregando"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>
        </div>

        <div class="row" v-if="finalizado">
            <div class="col-12">
                <div class="py-3 alert alert-danger">
                    <h6>Prova Finalizada em {{ data_finalizacao }}</h6>
                </div>
            </div>
        </div>

        <div class="row" v-if="!carregando && !finalizado">
            <!-- Questionário  -->
            <div class="col-12 col-md-9 mb-3">
                <div class="text-center py-3" style="border: 1px solid #ccc">
                    <!-- Páginação  -->
                    <div class="paginacao">
                        <h6>Questão {{ atual + 1 }}</h6>

                        <button @click="voltar" class="btn btn-outline-primary btn-circle" :disabled="(atual === 0 && atual < 4) || respondendo">
                            <i class="fas fa-chevron-left"></i>
                        </button>

                        <button
                            v-for="(botao, index) in lista.simulado.perguntas"
                            :key="botao.id || index"
                            v-show="index + 3 > atual && index < atual + 3"
                            @click="abrir(index)"
                            :disabled="respondendo"
                            class="btn btn-circle"
                            :class="btnClass(index, atual)"
                        >
                            {{ index + 1 }}
                        </button>

                        <button @click="avancar" class="btn btn-outline-primary btn-circle" :disabled="atual + 1 > fim || respondendo">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <hr style="width: 98%" />

                    <!-- Enunciado  -->
                    <div class="text-justify col-12 py-3 defaultText">
                        <p class="text-justify defaultText" v-html="lista.simulado.perguntas[atual].enunciado"></p>
                    </div>

                    <!-- Alternativas  -->
                    <div class="col-12 text-left py-1">
                        <div
                            class="form-check mb-2"
                            v-for="(resp, index) in lista.simulado.perguntas[atual].respostas"
                            :key="resp.id || index"
                            :id="alts[index]"
                            style="padding-top: 14px"
                            :class="objClass(lista.simulado.perguntas[atual], resp)"
                        >
                            <label class="form-check-label" :style="finalizado ? 'no-drop' : 'cursor: pointer'">
                                <input
                                    type="radio"
                                    class="form-check-input"
                                    style="display: none"
                                    :disabled="finalizado"
                                    :value="resp.id"
                                    :letraescolhida="alts[index]"
                                    v-model="lista.simulado.perguntas[atual].simulado_resposta_id"
                                    name="resposta"
                                />

                                <div class="row">
                                    <div style="margin-top: -10px">
                                        <svg width="46" height="51">
                                            <circle></circle>
                                            <text fill="#000000" font-size="25" font-family="Arial" x="15" y="31">
                                                {{ alts[index] }}
                                            </text>
                                        </svg>
                                    </div>

                                    <input type="radio" style="display: none" name="letracorreta" :checked="resp.correto" :value="alts[index]" />

                                    <div class="col">
                                        <span v-html="resp.resposta" v-if="!finalizado"></span>
                                        <span v-html="resp.resposta" v-if="finalizado"></span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="py-2">
                            <hr style="width: 98%" />
                            <div v-if="finalizado">
                                <button @click="voltar" class="btn btn-outline-primary btn-circle" :disabled="atual === 0 && atual < 4">
                                    <i class="fas fa-chevron-left"></i>
                                </button>

                                <button @click="avancar" class="btn btn-outline-primary btn-circle" :disabled="atual + 1 > fim">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>

                            <div v-show="!finalizado">
                                <p v-show="respondendo"><i class="fa fa-spinner fa-pulse"></i> Salvando resposta...</p>

                                <button
                                    class="btn btn-primary"
                                    style="font-size: 1rem"
                                    v-show="!respondeu"
                                    :disabled="lista.simulado.perguntas[atual].simulado_resposta_id == null || respondendo"
                                    @click="Responder(lista.simulado.perguntas[atual])"
                                >
                                    Responder
                                </button>

                                <button
                                    class="btn btn-primary"
                                    style="font-size: 1rem"
                                    v-show="completa"
                                 @click="$refs.modal_janelaMensagemFinalizar && $refs.modal_janelaMensagemFinalizar.abrirModal()">
                                    Finalizar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tempo  -->
            <div class="col-md-3 py-5 rounded d-flex justify-content-center bg-primary col-12" style="max-height: 348px" v-if="!finalizado">
                <div class="circle bg-primary">
                    <div>
                        <div class="count">{{ tempo }}</div>
                        <p class="minuto">Minutos<br />Restantes</p>
                        <div class="l-half"></div>
                        <div class="r-half"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 py-5 rounded bg-primary col-12" v-if="finalizado" style="max-height: 372px">
                <h5 class="text-center text-white">PROVA FINALIZADA</h5>

                <!--<h5 class="text-center text-white">RESULTADO</h5>
                <div class="d-flex justify-content-center">
                    <div class="circulo">
                        <p style="line-height:22px">{{ contaAcertos }} <br><span style="font-size:17px">ACERTOS</span>
                        </p>

                    </div>
                </div>-->
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        simulado_id: {
            type: Number,
            required: true,
            default: () => ''
        },

        simulado_vaga_id: {
            type: Number,
            required: true,
            default: () => ''
        },

        curriculo_id: {
            type: Number,
            required: true,
            default: () => ''
        },

        empresa_id: {
            type: Number,
            required: true,
            default: () => ''
        },

        vagas_abertas_id: {
            type: Number,
            required: true,
            default: () => ''
        },

        feedback_id: {
            type: Number,
            required: true,
            default: () => ''
        }
    },
    data() {
        return {
            // tempo: 88,
            finalizado: false,
            finalizando: false,
            carregando: true,
            respondendo: false,
            completa: false,
            data_finalizacao: null,
            URL_SITE,

            alts: {
                0: 'A',
                1: 'B',
                2: 'C',
                3: 'D',
                4: 'E',
                5: 'F',
                6: 'G',
                7: 'H',
                8: 'I',
                9: 'J',
                10: 'K',
                11: 'L',
                12: 'M',
                13: 'N',
                14: 'O',
                15: 'P',
                16: 'Q',
                17: 'R',
                18: 'S',
                19: 'T',
                20: 'U',
                21: 'V',
                22: 'X',
                23: 'Y',
                24: 'W',
                25: 'Z'
            },

            // alternativa: '',
            // correta: '',
            // resposta_correta: '',
            // letra_correta: '',
            // letra_escolhida: '',
            // acertou: false,
            // errou: false,

            respondeu: false,
            disabled: false,

            played: false,

            atual: 0,
            inicio: 0,
            fim: 0,

            lista: []

            // resultado: [],
        }
    },
    mounted() {
        this.getSimulado()
        this.gravaTempo()
        // setTimeout(() => {
        //     let respostasBranco = this.lista.simulado.perguntas.filter((pergunta) => {
        //         console.log(pergunta)
        //         return pergunta.simulado_resposta_id == null;
        //     });
        //     if (respostasBranco.length === 0) {
        //         this.completa = true;
        //     }
        // }, 600)
    },
    computed: {
        contaAcertos() {
            let qntAcerto = this.lista.simulado.perguntas.filter((pergunta) => {
                let correta = _.find(pergunta.respostas, {
                    correto: true
                })

                if (pergunta.simulado_resposta_id === correta.id) {
                    return pergunta
                }
            })

            return qntAcerto.length
        }
    },
    methods: {
        avancar() {
            this.atual++
        },
        voltar() {
            this.atual--
        },
        abrir(index) {
            this.atual = index
        },
        btnClass(index, atual) {
            let classe = {}
            if (!this.finalizado) {
                if (index === atual) {
                    classe['btn btn-default text-white'] = true
                }
                if (this.lista.simulado.perguntas[index].simulado_resposta_id != null) {
                    classe['btn bg-primary text-white round-1 text-white'] = true
                }
            } else {
                let correta = _.find(this.lista.simulado.perguntas[index].respostas, {
                    correto: true
                })
                if (this.lista.simulado.perguntas[index].simulado_resposta_id === correta.id) {
                    classe['btn btn-success text-white'] = true
                } else {
                    classe['btn btn-danger text-white'] = true
                }
            }
            return classe
        },

        objClass(pergunta, alternativa) {
            let classe = {}
            if (!this.finalizado) {
                if (pergunta.simulado_resposta_id === alternativa.id) {
                    classe['bg-primary text-white round-1'] = true
                }
            } else {
                let correta = _.find(pergunta.respostas, {
                    correto: true
                })
                if (pergunta.simulado_resposta_id === alternativa.id) {
                    if (pergunta.simulado_resposta_id === correta.id) {
                        classe['bg-success text-white'] = true
                        return classe
                    } else {
                        classe['bg-danger text-white'] = true
                    }
                } else {
                    if (correta.id === alternativa.id) {
                        classe['bg-success text-white'] = true
                    }
                }
            }
            return classe
        },
        Responder(obj) {
            this.respondendo = true
            axios
                .post(`${URL_SITE}/prova/responder`, {
                    simulado_vaga_id: this.simulado_vaga_id,
                    curriculo_id: this.curriculo_id,
                    feedback_id: this.feedback_id,
                    vagas_abertas_id: this.vagas_abertas_id,
                    empresa_id: this.empresa_id,
                    simulado_pergunta_id: obj.id,
                    simulado_resposta_id: obj.simulado_resposta_id
                })
                .then((response) => {
                    let data = response.data
                    let respostasBranco = this.lista.simulado.perguntas.filter((pergunta) => {
                        return pergunta.simulado_resposta_id == null
                    })
                    if (this.atual < this.fim) {
                        setTimeout(() => {
                            this.avancar()
                            this.respondendo = false
                        }, 300)
                        if (respostasBranco.length === 0) {
                            this.completa = true
                        }
                    } else {
                        if (respostasBranco.length === 0) {
                            this.respondendo = false
                            this.completa = true
                        } else {
                            if (respostasBranco.length === 1) {
                                mostraErro('', 'Ops! Existe ' + respostasBranco.length + ' questão em branco.')
                            } else {
                                mostraErro('', 'Ops! Existem ' + respostasBranco.length + ' questões em branco.')
                            }
                            this.respondendo = false
                        }
                    }
                })
                .catch((error) => {
                    this.respondendo = false
                })
        },

        gravaTempo() {
            if (!this.finalizado && this.tempo > 0) {
                axios.post(`${URL_SITE}/prova/grava-tempo`, {
                    simulado_id: this.simulado_id,
                    simulado_vaga_id: this.simulado_vaga_id,
                    curriculo_id: this.curriculo_id,
                    vagas_abertas_id: this.vagas_abertas_id,
                    empresa_id: this.empresa_id,
                    feedback_id: this.feedback_id,
                    tempo: this.tempo
                })
            }
            if (this.tempo <= 0) {
                this.finalizarSimulado()
            }
        },

        finalizarSimulado() {
            this.finalizando = true
            axios
                .post(`${URL_SITE}/prova/finalizar`, {
                    simulado_vaga_id: this.simulado_vaga_id,
                    simulado_id: this.simulado_id,
                    curriculo_id: this.curriculo_id,
                    feedback_id: this.feedback_id,
                    vagas_abertas_id: this.vagas_abertas_id,
                    empresa_id: this.empresa_id,
                    tempo: this.tempo
                })
                .then((response) => {
                    this.finalizando = false
                    this.finalizado = true
                    this.data_finalizacao = response.data.data_finalizacao
                    mostraSucesso('', 'Sua prova foi finalizada!')
                    this.$refs.modal_janelaMensagemFinalizar && this.$refs.modal_janelaMensagemFinalizar.fecharModal()
                })
                .catch((error) => {
                    this.finalizando = false
                })
        },

        getSimulado() {
            axios
                .post(`${URL_SITE}/prova/get-simulado`, {
                    simulado_vaga_id: this.simulado_vaga_id,
                    simulado_id: this.simulado_id,
                    curriculo_id: this.curriculo_id,
                    vagas_abertas_id: this.vagas_abertas_id,
                    empresa_id: this.empresa_id,
                    feedback_id: this.feedback_id
                })
                .then((response) => {
                    let data = response.data
                    this.lista = data.simulado_vaga
                    this.fim = data.simulado_vaga.simulado.perguntas.length - 1
                    this.finalizado = data.finalizado
                    this.data_finalizacao = data.data_finalizacao
                    this.tempo = data.duracao_segundos

                    this.carregando = false

                    if (!this.finalizado) {
                        setInterval(() => {
                            if (this.tempo >= 1) {
                                this.tempo--
                            }
                            if (this.tempo <= 0) {
                                this.tempo = 0
                                this.gravaTempo()
                                setTimeout(() => {
                                    // document.location.reload();
                                    this.finalizado = true
                                }, 100)
                            }
                            this.gravaTempo()
                        }, 60000)
                    }
                })
                .catch((error) => {})
        }
    }
}
</script>

<style scoped>
.circulo {
    color: #fff;
    font-size: 5em;
    border: 6px solid orange;
    border-radius: 50%;
    height: 176px;
    width: 176px;
    padding-top: 60px;
    text-align: center;
}

.minuto {
    color: orange;
    position: absolute;
    left: 89px;
    bottom: 25px;
    line-height: 20px;
    text-align: center;
}

.defaultText {
    font-family: 'Arial', serif !important;
    font-size: 10.5pt !important;
}

.defaultText p {
    font-family: 'Arial', serif !important;
    font-size: 10.5pt !important;
}

.defaultText p span p {
    font-family: 'Arial', serif !important;
    font-size: 10.5pt !important;
}

.defaultText p p span {
    font-family: 'Arial', serif !important;
    font-size: 10.5pt !important;
}

.defaultText span {
    font-family: 'Arial', serif !important;
    font-size: 10.5pt !important;
}

.defaultText p img {
    max-width: 100%;
    height: auto;
}

/*Paginacao Circle Botao*/

.btn-circle {
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    padding: 0;
    border-radius: 50%;
    border: 1px solid #ccc;
    margin-left: 3px;
}

.btn-circle i {
    position: relative;
    top: -1px;
}

.btn-circle-sm {
    width: 35px;
    height: 35px;
    line-height: 35px;
    font-size: 0.9rem;
}

.btn-circle-lg {
    width: 55px;
    height: 55px;
    line-height: 55px;
    font-size: 1.1rem;
}

.btn-circle-xl {
    width: 70px;
    height: 70px;
    line-height: 70px;
    font-size: 1.3rem;
}

/* -- CIRCLE -- */
.circle {
    width: 252px;
    height: 252px;
    position: relative;
    border-radius: 999px;
    box-shadow: inset 0 0 0 10px rgba(255, 255, 255, 0.5);
}

.l-half,
.r-half {
    float: left;
    width: 50%;
    height: 100%;
    overflow: hidden;
}

.l-half:before,
.r-half:before {
    content: '';
    display: block;
    width: 100%;
    height: 100%;
    box-sizing: border-box;
    border: 10px solid #fff;
    /*-webkit-animation-duration: 1200s;*/
    -webkit-animation-iteration-count: 1;
    -webkit-animation-timing-function: linear;
    -webkit-animation-fill-mode: forwards;
}

.l-half:before {
    border-right: none;
    border-top-left-radius: 999px;
    border-bottom-left-radius: 999px;
    -webkit-transform-origin: center right;
    -webkit-animation-name: l-rotate;
}

.r-half:before {
    border-left: none;
    border-top-right-radius: 999px;
    border-bottom-right-radius: 999px;
    -webkit-transform-origin: center left;
    -webkit-animation-name: r-rotate;
}

/* -- TIMER -- */
.count {
    position: absolute;
    width: 100%;
    line-height: 252px;
    text-align: center;
    font-weight: 800;
    font-size: 100px;
    font-family: Helvetica, serif;
    color: #fff;
    z-index: 2;
    /*-webkit-animation: fadeout 0.5s 61s 1 linear;*/
    -webkit-animation-fill-mode: forwards;
}

.limpa {
    background: #000;
}

@-webkit-keyframes l-rotate {
    0% {
        -webkit-transform: rotate(0deg);
    }
    50% {
        -webkit-transform: rotate(-180deg);
    }
    100% {
        -webkit-transform: rotate(-180deg);
    }
}

@-webkit-keyframes r-rotate {
    0% {
        -webkit-transform: rotate(0deg);
    }
    50% {
        -webkit-transform: rotate(0deg);
    }
    100% {
        -webkit-transform: rotate(-180deg);
    }
}

@-webkit-keyframes fadeout {
    0% {
        opacity: 0.5;
    }
    100% {
        opacity: 0.5;
    }
}

/* Padrão Simulado*/
circle {
    cx: 23;
    cy: 23;
    r: 20;
    stroke: #184056;
    stroke-width: 2;
    fill: rgb(255, 255, 255);
}

text {
    fill: #184056;
}

/*    Circle Progresso*/
</style>
