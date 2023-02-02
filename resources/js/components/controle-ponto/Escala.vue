<template>
    <div>
        <button type="button" class="btn btn-primary mt-2" @click="addJornada" v-if="botao_add_jornada"><i
            class="far fa-calendar-plus"></i> Adicionar jornada
        </button>
        <div class="card text-white mt-3" :style="{ border: topo_card? '1px solid rgb(204, 204, 204)':'' }"
             :class="{'border-primary':topo_card}" v-for="(jornada,index) in jornadas"
             v-if="filtro_jornadas_id!=null ? jornada.id===filtro_jornadas_id : true">
            <div class="card-header bg-primary" v-if="topo_card">
                <div class="row">
                    <div class="col-md-4">
                        <span v-if="jornada.inicio!==jornada.fim">
                            JORNADA {{ jornada.inicio }}-{{ jornada.fim }} ({{ jornada.inicioDia }}-{{ jornada.fimDia }})
                        </span>
                        <span v-else>JORNADA {{ jornada.fim }} ({{ jornada.fimDia }})</span>
                    </div>
                    <div class="col-md-8 d-flex flex-row-reverse align-items-end flex-column">

                        <div class="form-row" v-if="exibir_controles_jornada">
                            <div class="col-md-1 d-flex flex-row-reverse align-content-end flex-wrap">
                                <button type="button" class="btn btn-primary" @click="copiarJornada(index)"><i
                                    class="fas fa-redo fa-2x"></i></button>
                            </div>
                            <div class="col-md-2 d-flex flex-row-reverse align-content-end flex-wrap">
                                <small>Repetir por (dias):</small>
                                <input type="number" class="form-control" placeholder="Dias" v-model="jornada.repetir"
                                       @change="validaCampoRepetir(jornada)" @input="validaCampoRepetir(jornada)"
                                       onblur="valida_campo_vazio(this,1)">
                            </div>
                            <div class="col-md-3 d-flex flex-row-reverse align-content-end flex-wrap">
                                <small>Ocorrência:</small>
                                <select id="inputState" class="form-control" v-model="jornada.ocorrencia_id">
                                    <option v-for="ocorrencia in ocorrencias" :value="ocorrencia.id">
                                        {{ ocorrencia.descricao }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-5 d-flex align-content-end flex-wrap">
                                <button type="button" class="btn btn-light mt-3"
                                        :disabled="!jornada.ocorrenciaSelecionada.trabalhado"
                                        @click="addPeriodo(jornada)"> Adicionar período <i class="far fa-clock"></i>
                                </button>
                            </div>
                            <div class="col-md-1 d-flex align-content-end flex-wrap" v-if="excluir_jornada">
                                <button type="button" class="btn btn-primary" @click="removerJornada(index)"><i
                                    class="fas fa-times fa-2x"></i></button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card-body" v-if="jornada.ocorrenciaSelecionada.trabalhado">
                <div class="row">
                    <div class="col-12 col-md-6" v-for="(periodo,index) in jornada.periodos">
                        <div class="card" style="border: 1px solid rgb(219,220,220);">
                            <div class="card-header  bg-light text-black-50"
                                 style="border: 1px solid rgb(219,220,220);">
                                <div class="row">
                                    <div class="col-11">
                                        PERÍODO TRABALHADO {{ index + 1 }}
                                    </div>
                                    <div class="col-1" v-if="botao_remover_periodo">
                                        <button v-if="!periodo.autenticacao_entrada" type="button" class="btn btn-light"
                                                @click="removerPeriodo(jornada.periodos,index)"><i
                                            class="fas fa-times"></i></button>
                                    </div>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-bed" v-if="index===0"></i>
                                                    <i class="fas fa-coffee" v-else></i>
                                                </div>
                                            </div>
                                            <input type="text" class="form-control" placeholder="00:00"
                                                   :value="periodo.horas_descanso" :disabled="true">
                                        </div>
                                        <small class="text-dark" v-if="index===0">Hora de descanso</small>
                                        <small class="text-dark" v-else>Descanso entre períodos</small>
                                    </div>

                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-sign-in-alt"></i>
                                                </div>
                                            </div>
                                            <input :disabled="bloqueado" type="text" class="form-control"
                                                   placeholder="hh:mm" v-model="periodo.entrada" v-mascara:hora
                                                   onblur="valida_campo_vazio(this,5)">
                                        </div>
                                        <small class="text-dark">Hora de entrada</small>
                                    </div>

                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="input-group mb-2">
                                            <input :disabled="bloqueado" type="text" class="form-control"
                                                   placeholder="hh:mm" v-model="periodo.saida" v-mascara:hora
                                                   onblur="valida_campo_vazio(this,5)">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <i class="fas fa-sign-in-alt fa-flip-horizontal"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-dark">Hora de saída</small>
                                    </div>

                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-stopwatch"></i>
                                                </div>
                                            </div>
                                            <input type="text" class="form-control" placeholder="00:00"
                                                   :value="periodo.horas_trabalhadas" :disabled="true">
                                        </div>
                                        <small class="text-dark">Horas trabalhadas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <button v-if="!exibir_controles_jornada && botao_add_periodo" type="button"
                            class="btn btn-light btn-lg btn-block" @click="addPeriodo(jornada,'inicio')"><i
                        class="fas fa-sign-out-alt"></i> Adicionar período ao início
                    </button>
                </div>
                <div class="col-6">
                    <button v-if="!exibir_controles_jornada && botao_add_periodo" type="button"
                            class="btn btn-light btn-lg btn-block" @click="addPeriodo(jornada,'fim')">Adicionar período
                        ao fim <i class="fas fa-sign-in-alt fa-flip-horizontal"></i></button>
                </div>
            </div>


        </div>
        <div class="alert alert-info" role="alert" v-if="jornadas.length > 0 && info">
            <h4 class="alert-heading">Escala circular <i class="fas fa-undo-alt"></i></h4>
            <p v-if="jornadas.length===1">Como só existe um tipo de jornada definida, ela ficará repetindo todos os
                dias.</p>
            <p v-else>Após a jornada {{ jornadas[jornadas.length - 1].fim }} ({{
                    jornadas[jornadas.length - 1].fimDia
                }}) a escala irá circular e a próxima será a jornada 1, iniciando
                {{ jornadas[jornadas.length - 1].momentInicio.format('dddd') }} e repetindo o ciclo.</p>
        </div>
    </div>
</template>

<script>


export default {
    components: {},
    props: {

        ocorrencia_padrao: {
            type: Number,
            required: true,
        },

        ocorrencias: {
            type: Array,
            required: false,
            default: () => []
        },
        filtro_jornadas_id: {
            type: Number,
            required: false,
            default: () => null
        },

        info: {
            type: Boolean,
            required: false,
            default: () => true
        },
        excluir_jornada: {
            type: Boolean,
            required: false,
            default: () => true
        },
        exibir_controles_jornada: {
            type: Boolean,
            required: false,
            default: () => true
        },
        botao_add_jornada: {
            type: Boolean,
            required: false,
            default: () => true
        },
        botao_add_periodo: {
            type: Boolean,
            required: false,
            default: () => true
        },
        topo_card: {
            type: Boolean,
            required: false,
            default: () => true
        },
        botao_remover_periodo: {
            type: Boolean,
            required: false,
            default: () => true
        },
        bloqueado: {
            type: Boolean,
            required: false,
            default: () => false
        },

        model: {
            type: Object,
            required: true,
            default: () => {
            }
        },
    },
    data() {
        return {
            moment: moment,
        }
    },
    mounted() {

    },
    computed: {
        jornadas() {
            let numero_jornada = 1;
            let data_inicio = moment(this.model.inicio, 'DD/MM/YYYY');
            let jornadas = this.model.jornadas.map((jornada) => {

                jornada.inicioDia = data_inicio.format('ddd'); // seg
                jornada.fimDia = data_inicio.format('ddd');
                jornada.inicioDiaSemana = data_inicio.format('dddd'); //segunda-feira

                jornada.inicio = numero_jornada;
                numero_jornada += jornada.repetir === '' ? 0 : parseInt(jornada.repetir) - 1;
                jornada.fim = numero_jornada;

                jornada.momentInicio = moment(data_inicio.format('DD/MM/YYYY'), 'DD/MM/YYYY');

                data_inicio.add(jornada.fim - jornada.inicio, 'days');
                jornada.fimDia = data_inicio.format('ddd');
                jornada.momentFim = moment(data_inicio.format('DD/MM/YYYY'), 'DD/MM/YYYY');
                //jornada.fimDiaSemana = data_inicio.format('dddd');

                data_inicio.add(1, 'days');
                numero_jornada++; // proxima jornada

                jornada.ocorrenciaSelecionada = _.find(this.ocorrencias, {'id': jornada.ocorrencia_id});
                return jornada;
            });

            //horas_descanso das jornadas
            jornadas.forEach((jornada, indexJornada) => {

                jornada.periodos.forEach((periodo, indexPeriodo) => {
                    // se periodo nao esta preenchido corretamente..
                    if (periodo.entrada.length < 5 || periodo.saida.length < 5) {
                        periodo.horas_descanso = '?';
                        periodo.horas_trabalhadas = '?';
                    } else {

                        //hora de descanço
                        if (indexPeriodo > 0) { // se for a segunda em diantes, sempre pegar a anterior.

                            let inicio = moment(jornada.periodos[indexPeriodo - 1].saida, 'HH:mm');
                            let fim = moment(periodo.entrada, 'HH:mm');

                            if (jornada.periodos[indexPeriodo - 1].saida.length < 5 || periodo.entrada.length < 5) {
                                jornada.periodos[indexPeriodo - 1].horas_trabalhadas = '?';
                                periodo.horas_descanso = '?';
                            } else {
                                let duration = moment.duration(fim.diff(inicio));
                                periodo.horas_descanso = `${parseInt(duration.asHours())}:${this.formataTempo(duration.minutes())}`;
                            }

                        } else {
                            // se for primeiro periodo e...
                            // ...se for a segunda jornada ...
                            if (indexJornada > 0) {
                                //let jornadaAnterior = jornadas[indexJornada-1];
                                let jornadaAnterior = _.findLast(jornadas, j => j.periodos.length > 0 && j.fim < jornada.fim);
                                if (!jornadaAnterior) {
                                    jornadaAnterior = jornada;
                                }
                                if (jornadaAnterior) {

                                    let ultimoPeriodoJornadaAnterior = jornadaAnterior.periodos[jornadaAnterior.periodos.length - 1];
                                    if (!ultimoPeriodoJornadaAnterior) {
                                        ultimoPeriodoJornadaAnterior = periodo;
                                    }
                                    if (periodo.entrada.length < 5 || ultimoPeriodoJornadaAnterior.saida.length < 5) {
                                        periodo.horas_descanso = '?';
                                    } else {
                                        let inicio = moment(`${jornadaAnterior.momentFim.format('DD/MM/YYYY')} ${ultimoPeriodoJornadaAnterior.saida}`, 'DD/MM/YYYY HH:mm');
                                        let fim = moment(`${jornada.momentInicio.format('DD/MM/YYYY')} ${periodo.entrada}`, 'DD/MM/YYYY HH:mm');

                                        let duration = moment.duration(fim.diff(inicio));
                                        periodo.horas_descanso = `${Math.abs(parseInt(duration.asHours()))}:${this.formataTempo(Math.abs(duration.minutes()))}`;
                                    }
                                } else {

                                    periodo.horas_descanso = '?';
                                }


                            } else {
                                //primeira jornada..
                                //let jornadaAnterior = jornadas.filter(jornada=>jornada.periodos.length>0);
                                let jornadaAnterior = _.findLast(jornadas, j => j.periodos.length > 0);
                                //let jornadaAnterior = _.findLast(jornadas,j=>j.periodos.length>0 && j.fim < jornada.fim);
                                //jornadaAnterior = jornadaAnterior.length >= 1 ? jornadaAnterior[jornadaAnterior.length-1]:null;
                                let ultimaJornada = jornadas[jornadas.length - 1];
                                //let primeiraJornada = jornada;
                                //let primeiraJornada = jornadas[0];
                                let primeiraJornada = _.find(jornadas, j => j.periodos.length > 0);

                                if (jornadaAnterior) {

                                    let ultimoPeriodoJornadaAnterior = jornadaAnterior.periodos.length >= 1 ? jornadaAnterior.periodos[jornadaAnterior.periodos.length - 1] : null;
                                    let primeiroPeriodo = primeiraJornada.periodos.length >= 1 ? primeiraJornada.periodos[0] : null;
                                    if (ultimoPeriodoJornadaAnterior && primeiroPeriodo) {

                                        if (primeiroPeriodo.entrada.length < 5 || ultimoPeriodoJornadaAnterior.saida.length < 5) {
                                            periodo.horas_descanso = '?';
                                        } else {
                                            let inicio = moment(`${jornadaAnterior.momentFim.format('DD/MM/YYYY')} ${ultimoPeriodoJornadaAnterior.saida}`, 'DD/MM/YYYY HH:mm');
                                            let fim = moment(`${jornadaAnterior.momentFim.format('DD/MM/YYYY')} ${primeiroPeriodo.entrada}`, 'DD/MM/YYYY HH:mm');
                                            fim.add((ultimaJornada.fim - jornadaAnterior.fim) + 1, 'days');

                                            let duration = moment.duration(fim.diff(inicio));
                                            primeiroPeriodo.horas_descanso = `${Math.abs(parseInt(duration.asHours()))}:${this.formataTempo(Math.abs(duration.minutes()))}`;
                                        }
                                    }
                                } else {
                                    periodo.horas_descanso = '?';
                                }

                            }

                        }

                        let inicio = moment(periodo.entrada, 'HH:mm');
                        let fim = moment(periodo.saida, 'HH:mm');

                        let duration = moment.duration(fim.diff(inicio));
                        periodo.horas_trabalhadas = `${Math.abs(parseInt(duration.asHours()))}:${this.formataTempo(Math.abs(duration.minutes()))}`;
                    }

                })
            });

            return jornadas;
        }

    },

    methods: {
        validaCampoRepetir(jornada) {
            let valor = parseInt(jornada.repetir);
            if (valor <= 0) {
                jornada.repetir = 1;
            }
        },

        formataTempo(value) {
            if (value < 10) {
                return '0' + value;
            }
            return value;
        },
        addJornada() {
            this.model.jornadas.push({
                id: null,
                escala_id: this.model.id,
                inicio: moment(this.model.inicio, 'DD/MM/YYYY'),
                periodos: [],
                ocorrencia_id: this.ocorrencia_padrao,
                ocorrencias: this.ocorrencias,
                repetir: 1,
            })
        },
        copiarJornada(index) {
            let clone = _.cloneDeep(this.model.jornadas[index])
            clone.id = null;
            clone.repetir = 1;
            clone.ocorrencia_id = this.ocorrencia_padrao;
            this.model.jornadas.push(clone);
        },
        removerJornada(index) {
            this.model.jornadasDelete.push(this.model.jornadas[index].id);
            this.model.jornadas.splice(index, 1);
        },
        addPeriodo(jornada, onde = 'inicio') {
            if (onde === 'inicio') {
                jornada.periodos.unshift({
                    id: null,
                    jornada_id: jornada.id,
                    entrada: '',
                    saida: '',
                });
            } else {
                jornada.periodos.push({
                    id: null,
                    jornada_id: jornada.id,
                    entrada: '',
                    saida: '',
                });
            }

        },
        removerPeriodo(array, index) {
            if (array[index].id) {
                this.model.periodosDelete.push(array[index].id);
            }
            array.splice(index, 1);
        }
    }
}
</script>

<style scoped>

</style>
