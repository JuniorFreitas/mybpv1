<template>
    <div id="formParecerRh">
        <div v-if='!preload'>
            <dados-pessoais :form='form'></dados-pessoais>

            <fieldset v-if='!cliente_servico'>
                <legend>TIPO DE ENTREVISTA</legend>
                <select
                    class='form-control'
                    :disabled='visualizar || disabledParecerRh'
                    onchange='valida_campo_vazio(this,1)'
                    @change='changeTipoEntrevista'
                    v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                    v-model='form.parecer_rh.tipo_entrevista'
                >
                    <option value='Fixo'>Fixo</option>
                    <option value='Parada'>Parada</option>
                </select>
            </fieldset>

            <fieldset v-if='provas > 0'>
                <legend>Provas</legend>
                <div class='alert alert-warning' v-show='form.simulados.length < provas'>
                    <i class='fa fa-exclamation-triangle'></i> Atenção! Candidato(a) possuí prova pendente.
                </div>

                <div class='pt-2' style='border-bottom: 1px dashed #cccccc' v-show='form.simulados.length'
                     v-for='prova in form.simulados'>
                    <p>
                        Teste: <strong>{{ prova.simulado_vaga.simulado.titulo }}</strong> Acertos:
                        <strong>{{ prova.acertos }} </strong><br/>
                        Tempo executado: <strong>{{ prova.tempo_execucao }} min </strong><br/>
                        Finalizado em <strong>{{ prova.data_finalizacao }}h</strong>
                    </p>
                </div>
            </fieldset>

            <fieldset v-if='cliente_servico'>
                <legend>NOTA TESTE DIGITAÇÃO</legend>
                <select class='form-control' :disabled='visualizar || disabledParecerRh'
                        v-model='form.parecer_rh.nota_digitacao'>
                    <option value=''>Selecione</option>
                    <option v-for='qnt in 10' :value='qnt'>{{ qnt }}</option>
                </select>
            </fieldset>

            <fieldset>
                <legend>INFORMAÇÕES COMPLEMENTARES</legend>
                <div class='row'>
                    <div class='col-12 col-sm-6' v-if='!cliente_servico'>
                        <div class='form-group'>
                            <label>Domínio</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.destro'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option value='Destro'>Destro</option>
                                <option value='Canhoto'>Canhoto</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.destro'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option value='Destro'>Destro</option>
                                <option value='Canhoto'>Canhoto</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Ex Funcionário</label>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.ex_funcionario'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.ex_funcionario'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='!cliente_servico'>
                        <div class='form-group'>
                            <label>CNH</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.cnh'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.cnh'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='form.parecer_rh.cnh'>
                        <div class='form-group'>
                            <label>Tipo da CNH</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.cnh_tipo'
                            />
                        </div>
                    </div>

                    <div class='col-12' v-if='!cliente_servico'>
                        <div class='form-group'>
                            <label>Rota/Bairro</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)" } : {}'
                                v-model='form.parecer_rh.rota_bairro'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            />

                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-model='form.parecer_rh.rota_bairro'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            />
                        </div>
                    </div>

                    <div class='col-12'></div>
                    <div class='col-12 col-sm-6' v-if='cliente_servico'>
                        <div class='form-group'>
                            <label>Avaliação Dinâmica de Grupo</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.dinamicadegrupo'
                            >
                                <option value=''>Selecione</option>
                                <option value='Destaque'>Destaque</option>
                                <option value='Favorável'>Favorável</option>
                                <option value='Desfavorável'>Desfavorável</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 ' v-if='form.parecer_rh.dinamicadegrupo'>
                        <div class='form-group'>
                            <label>OBS.:</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-model='form.parecer_rh.obs_dinamicadegrupo'
                            />
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if='!cliente_servico'>
                <legend class='text-uppercase'>EPI Pontuação</legend>
                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Camisa de Meia</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.camisa_meia'
                            >
                                <option value=''>Selecione</option>
                                <option value='P'>P</option>
                                <option value='M'>M</option>
                                <option value='G'>G</option>
                                <option value='GG'>GG</option>
                                <option value='XG'>XG</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Camisa Proteção</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.camisa_protecao'
                            >
                                <option value=''>Selecione</option>
                                <option v-for='qnt in 6' v-if='qnt >= 2' :value='qnt'>{{ qnt }}</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Calça</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.calca'
                            >
                                <option value=''>Selecione</option>
                                <option v-for='qnt in 56' v-if='qnt >= 34' :value='qnt'>{{ qnt }}</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Bota</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.bota'
                            >
                                <option value=''>Selecione</option>
                                <option v-for='qnt in 50' v-if='qnt >= 34' :value='qnt'>{{ qnt }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Hístorico Familiar e Social</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label>Mora com quem?</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 2)", onblur: "valida_campo_vazio(this, 2)", } : {}'
                                v-model='form.parecer_rh.mora_com_quem'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            />

                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-model='form.parecer_rh.mora_com_quem'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            />
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Casado?</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.casado'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.casado'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='form.parecer_rh.casado'>
                        <div class='form-group'>
                            <label>Tempo de convivência</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.tempodeconvivencia'
                            />
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Filhos?</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.filhos'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.filhos'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='form.parecer_rh.filhos'>
                        <div class='form-group'>
                            <label>Quantos?</label>
                            <input
                                type='number'
                                min='1'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.qnt_filhos'
                            />
                        </div>
                    </div>
                </div>

                <div class='row' v-if='form.parecer_rh.casado'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Esposa ou Marido Trabalha?</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.conjuge_trabalha'
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='form.parecer_rh.conjuge_trabalha'>
                        <div class='form-group'>
                            <label>Em quê?</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.trabalho_conjuge'
                            />
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Praticante de alguma religião?</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.religioso'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.religioso'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='form.parecer_rh.religioso'>
                        <div class='form-group'>
                            <label>Qual?</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.religiao_praticante'
                            />
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Fuma?</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.fuma'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.fuma'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='form.parecer_rh.fuma'>
                        <div class='form-group'>
                            <label>Qual frequência?</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.frequencia_fuma'
                            />
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Bebe?</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.bebe'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.bebe'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='form.parecer_rh.bebe'>
                        <div class='form-group'>
                            <label>Qual frequência?</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.frequencia_bebe'
                            />
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Indicado por alguém?</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.indicacao'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.indicacao'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='form.parecer_rh.indicacao'>
                        <div class='form-group'>
                            <label>Quem?</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.indicado_por'
                            />
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Outras informações</legend>

                <div class='row' v-if='!cliente_servico'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Experiência na ALUMAR?</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.alumar_experiencia'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.alumar_experiencia'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='form.parecer_rh.alumar_experiencia'>
                        <div class='form-group'>
                            <label>Qual área?</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.alumar_experiencia_area'
                            />
                        </div>
                    </div>
                </div>

                <div class='row' v-if='cliente_servico'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Experiência na área?</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.experiencia_callcenter'
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12'>
                        <div class='form-group'>
                            <label>Observação</label>
                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                cols='3'
                                rows='3'
                                v-model='form.parecer_rh.obs_call'
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label>Grau de instrução</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-model='form.parecer_rh.grau_instrucao'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            />

                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                v-model='form.parecer_rh.grau_instrucao'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            />
                        </div>
                    </div>
                </div>

                <template v-if='!cliente_servico'>
                    <div class='row'>
                        <div class='col-12 col-sm-6'>
                            <div class='form-group'>
                                <label>Disponibilidade de hora extra?</label>
                                <select
                                    v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                    class='form-control'
                                    :disabled='visualizar || disabledParecerRh'
                                    v-model='form.parecer_rh.horaextra'
                                    v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                                >
                                    <option value=''>Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>

                                <select
                                    class='form-control'
                                    :disabled='visualizar || disabledParecerRh'
                                    v-model='form.parecer_rh.horaextra'
                                    v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                                >
                                    <option value=''>Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>
                            </div>
                        </div>

                        <div class='col-12 col-sm-6'>
                            <div class='form-group'>
                                <label>Disponibilidade para turnos 6X2?</label>
                                <select
                                    v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                    class='form-control'
                                    :disabled='visualizar || disabledParecerRh'
                                    v-model='form.parecer_rh.turnos_seis_por_dois'
                                    v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                                >
                                    <option value=''>Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>

                                <select
                                    class='form-control'
                                    :disabled='visualizar || disabledParecerRh'
                                    v-model='form.parecer_rh.turnos_seis_por_dois'
                                    v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                                >
                                    <option value=''>Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>
                            </div>
                        </div>

                        <div class='col-12 col-sm-6'>
                            <div class='form-group'>
                                <label>Disponibilidade para noturno?</label>
                                <select
                                    v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                    class='form-control'
                                    :disabled='visualizar || disabledParecerRh'
                                    v-model='form.parecer_rh.noturno'
                                    v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                                >
                                    <option value=''>Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>

                                <select
                                    class='form-control'
                                    :disabled='visualizar || disabledParecerRh'
                                    v-model='form.parecer_rh.noturno'
                                    v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                                >
                                    <option value=''>Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class='row'>
                        <div class='col-12 col-sm-6'>
                            <div class='form-group'>
                                <label>Acidente de trabalho anterior?</label>
                                <select
                                    v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                    class='form-control'
                                    :disabled='visualizar || disabledParecerRh'
                                    v-model='form.parecer_rh.acidente_trabalho'
                                    v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                                >
                                    <option value=''>Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>

                                <select
                                    class='form-control'
                                    :disabled='visualizar || disabledParecerRh'
                                    v-model='form.parecer_rh.acidente_trabalho'
                                    v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                                >
                                    <option value=''>Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>
                            </div>
                        </div>

                        <div class='col-12 col-sm-6' v-if='form.parecer_rh.acidente_trabalho'>
                            <div class='form-group'>
                                <label>Especifique</label>
                                <input
                                    type='text'
                                    :disabled='visualizar || disabledParecerRh'
                                    autocomplete='off'
                                    class='form-control'
                                    v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                    v-model='form.parecer_rh.acidente_trabalho_qual'
                                />
                            </div>
                        </div>
                    </div>

                    <div class='row'>
                        <div class='col-12 col-sm-6'>
                            <div class='form-group'>
                                <label>Afastamento INSS anterior?</label>
                                <select
                                    v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                    class='form-control'
                                    :disabled='visualizar || disabledParecerRh'
                                    v-model='form.parecer_rh.afastamento_inss'
                                    v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                                >
                                    <option value=''>Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>

                                <select
                                    class='form-control'
                                    :disabled='visualizar || disabledParecerRh'
                                    v-model='form.parecer_rh.afastamento_inss'
                                    v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                                >
                                    <option value=''>Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>
                            </div>
                        </div>

                        <div class='col-12 col-sm-6' v-if='form.parecer_rh.afastamento_inss'>
                            <div class='form-group'>
                                <label>Especifique</label>
                                <input
                                    type='text'
                                    :disabled='visualizar || disabledParecerRh'
                                    autocomplete='off'
                                    class='form-control'
                                    v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                    v-model='form.parecer_rh.afastamento_inss_qual'
                                />
                            </div>
                        </div>
                    </div>
                </template>

                <div class='row' v-if='cliente_servico'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Disponibilidade de horários</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.disponibilidade_horarios'
                            >
                                <option value=''>Selecione</option>
                                <option value='Manhã'>Manhã</option>
                                <option value='Manhã - tarde'>Manhã - tarde</option>
                                <option value='Manhã - noite'>Manhã - noite</option>
                                <option value='Tarde'>Tarde</option>
                                <option value='Tarde - noite'>Tarde - noite</option>
                                <option value='Noite'>Noite</option>
                                <option value='Noite - madrugada'>Noite - Madrugada</option>
                                <option value='Madrugada'>Madrugada</option>
                                <option value='Madrugada - manhã'>Madrugada - manhã</option>
                                <option value='qualquer horário'>Qualquer horário</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Disponibilidade para turnos 6X1</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.turnos_seis_por_um'
                            >
                                <option value=''>Selecione</option>
                                <option :value='true'>Sim</option>
                                <option :value='false'>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Horário Preferencial</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.horario_preferencial'
                            >
                                <option value=''>Selecione</option>
                                <option value='manha'>Manhã</option>
                                <option value='tarde'>Tarde</option>
                                <option value='noite'>Noite</option>
                                <option value='madrugada'>Madrugada</option>
                                <option value='qualquer horário'>Qualquer horário</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12'>
                        <div class='form-group'>
                            <label>Observação</label>
                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                cols='3'
                                rows='3'
                                v-model='form.parecer_rh.obs_horario'
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label>Especifique situações de saúde</label>
                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.situacao_saude'
                                cols='3'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 2)", onblur: "valida_campo_vazio(this, 2)", } : {}'
                                rows='3'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            ></textarea>

                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.situacao_saude'
                                cols='3'
                                rows='3'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class='row' v-if='!cliente_servico'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Certificado NR 10</label>
                            <select
                                v-bind='!visualizar || !disabledParecerRh ? { onchange: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.nr_dez'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option value='sim'>Sim</option>
                                <option value='nao'>Não</option>
                                <option value='nao se aplica'>Não se aplica</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.nr_dez'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option value='sim'>Sim</option>
                                <option value='nao'>Não</option>
                                <option value='nao se aplica'>Não se aplica</option>
                            </select>
                        </div>
                    </div>
                </div>

                <fieldset v-if="form.parecer_rh.nr_dez === 'sim'">
                    <legend>Certificado NR10</legend>
                    <div class='row'>
                        <div class='col-12'>
                            <button class='btn btn-sm btn-primary mb-3' :disabled='visualizar || disabledParecerRh'
                                    @click.prevent='addLINr($event.target)'>
                                <i class='fas fa-plus' aria-hidden='true'></i>
                                Adicionar Certificado NR10
                            </button>
                        </div>
                    </div>

                    <div
                        class='row py-3'
                        style='border-bottom: 1px dashed #cccccc'
                        v-show='form.certificados_nr.length > 0'
                        v-for='(obj, index) in form.certificados_nr'
                        :key='obj.id'
                    >
                        <div class='col-12 text-uppercase'><strong>Informações Certificado NR10</strong></div>

                        <div class='col-12'>
                            <div class='form-group'>
                                <label>Instituição</label>
                                <input
                                    type='text'
                                    :disabled='visualizar || disabledParecerRh'
                                    autocomplete='off'
                                    class='form-control'
                                    v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 2)", onblur: "valida_campo_vazio(this, 2)", } : {}'
                                    v-model='obj.nr_dez_instituicao'
                                />
                            </div>
                        </div>

                        <div class='col-12 col-sm-6'>
                            <div class='form-group'>
                                <label>Data de Emissão</label>
                                <datepicker label='' :disabled='visualizar || disabledParecerRh'
                                            v-model='obj.nr_dez_emissao'></datepicker>
                            </div>
                        </div>

                        <div class='col-12 col-sm-6'>
                            <div class='form-group'>
                                <label>Data de Validade</label>
                                <datepicker label='' :disabled='visualizar || disabledParecerRh'
                                            v-model='obj.nr_dez_validade'></datepicker>
                            </div>
                        </div>

                        <div class='col-12'>
                            <button class='btn btn-sm btn-primary' :disabled='visualizar || disabledParecerRh'
                                    @click.prevent='addLINr($event.target)'>
                                <i class='fas fa-plus' aria-hidden='true'></i>
                                Certificado
                            </button>
                            <button class='btn btn-sm btn-danger' :disabled='visualizar || disabledParecerRh'
                                    @click.prevent='removerLINr(index)'>
                                <i class='fa fa-times'></i> Remover
                            </button>
                        </div>
                    </div>
                </fieldset>

                <fieldset v-if='!cliente_servico'>
                    <legend>Cursos de Formação</legend>
                    <div class='row'>
                        <div class='col-12'>
                            <button class='btn btn-sm btn-primary mb-3' :disabled='visualizar || disabledParecerRh'
                                    @click.prevent='addLICurso($event.target)'>
                                <i class='fas fa-plus' aria-hidden='true'></i>
                                Adicionar Curso de Formação
                            </button>
                        </div>
                    </div>

                    <div
                        class='row py-3'
                        style='border-bottom: 1px dashed #cccccc'
                        v-show='form.cursos_formacoes.length > 0'
                        v-for='(obj, index) in form.cursos_formacoes'
                        :key='obj.id'
                    >
                        <div class='col-12 text-uppercase'><strong>Informações Certificado NR10</strong></div>
                        <div class='col-12 col-sm-6'>
                            <div class='form-group'>
                                <label>Curso</label>
                                <input
                                    type='text'
                                    :disabled='visualizar || disabledParecerRh'
                                    autocomplete='off'
                                    class='form-control'
                                    v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 2)", onblur: "valida_campo_vazio(this, 2)", } : {}'
                                    v-model='obj.curso'
                                />
                            </div>
                        </div>

                        <div class='col-12 col-sm-6'>
                            <div class='form-group'>
                                <label>Instituição</label>
                                <input
                                    type='text'
                                    :disabled='visualizar || disabledParecerRh'
                                    autocomplete='off'
                                    class='form-control'
                                    v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 2)", onblur: "valida_campo_vazio(this, 2)", } : {}'
                                    v-model='obj.instituicao'
                                />
                            </div>
                        </div>

                        <div class='col-12 col-sm-6'>
                            <datepicker label='Data de Emissão' :disabled='visualizar || disabledParecerRh'
                                        v-model='obj.emissao'></datepicker>
                        </div>

                        <div class='col-12 col-sm-6'>
                            <div class='form-group'>
                                <label>Data de Validade</label>
                                <input
                                    type='text'
                                    :disabled='visualizar || disabledParecerRh'
                                    autocomplete='off'
                                    class='form-control'
                                    v-mascara:data
                                    onblur='valida_data(this)'
                                    v-model='obj.validade'
                                />
                            </div>
                        </div>

                        <div class='col-12'>
                            <button class='btn btn-sm btn-primary' :disabled='visualizar || disabledParecerRh'
                                    @click.prevent='addLICurso($event.target)'>
                                <i class='fas fa-plus' aria-hidden='true'></i>
                                Curso de Formação
                            </button>
                            <button class='btn btn-sm btn-danger' :disabled='visualizar || disabledParecerRh'
                                    @click.prevent='removerLICurso(index)'>
                                <i class='fa fa-times'></i> Remover
                            </button>
                        </div>
                    </div>
                </fieldset>

                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Comportamento Seguro</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-model='form.parecer_rh.comportamento_seguro'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option value='sim'>Sim</option>
                                <option value='nao'>Não</option>
                                <option value='razoavel'>Razoável</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.comportamento_seguro'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option value='sim'>Sim</option>
                                <option value='nao'>Não</option>
                                <option value='razoavel'>Razoável</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Energia para o trabalho</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-model='form.parecer_rh.energia_para_trabalho'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option value='sim'>Sim</option>
                                <option value='nao'>Não</option>
                                <option value='razoavel'>Razoável</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.energia_para_trabalho'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option value='sim'>Sim</option>
                                <option value='nao'>Não</option>
                                <option value='razoavel'>Razoável</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Postura</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-model='form.parecer_rh.postura'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option value='sim'>Sim</option>
                                <option value='nao'>Não</option>
                                <option value='razoavel'>Razoável</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.postura'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                            >
                                <option value=''>Selecione</option>
                                <option value='sim'>Sim</option>
                                <option value='nao'>Não</option>
                                <option value='razoavel'>Razoável</option>
                            </select>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Histórico Profissional</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label
                            >Quais as suas últimas experiências? Nome das empresas, cargos ocupados, tempo de
                                permanência nas funções e os motivos de saída.
                            </label>
                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.historico_profissional'
                                cols='3'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 2)", onblur: "valida_campo_vazio(this, 2)", } : {}'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                                rows='3'
                            ></textarea>

                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.historico_profissional'
                                cols='3'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                                rows='3'
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Histórico Educacional</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label>Fale-me sobre sua formação educacional e cursos.</label>
                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.historico_educacional'
                                cols='3'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 2)", onblur: "valida_campo_vazio(this, 2)", } : {}'
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                                rows='3'
                            ></textarea>

                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.historico_educacional'
                                cols='3'
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                                rows='3'
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Objetivos e expectativas</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label
                            >Quais são suas expectativas em relação à essa empresa e ao cargo ao qual você se
                                candidatou? Geralmente, que o motiva e o que
                                você detesta no trabalho? Que seria um ambiente ideal de trabalho? Quais são seus planos
                                profissionais em curto prazo? E a
                                longo? Quais são seus planos pessoais? Porque deveríamos contratá-lo?</label
                            >
                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.objetivos_expectativas'
                                cols='3'
                                rows='3'
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Auto-imagem</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label
                            >Quais são suas maiores qualidades? Que aspectos da sua vida você precisa melhorar? Qual foi
                                sua maior frustração? Qual é o seu
                                maior sonho?</label
                            >
                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.auto_imagem'
                                cols='3'
                                rows='3'
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Competências</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label class='labeltext'
                            >Conte-me, com riqueza de detalhes, um fato recente que realmente tenha ocorrido no âmbito
                                profissional. Caso a situação
                                perguntada não faça parte do seu histórico profissional, busque-a na sua formação
                                acadêmica e por fim na sua vida pessoal.
                                <br/>
                                Ao contar o fato lembre-se de citar o contexto, a ação e o resultado, ou seja, o momento
                                em que aconteceu, o que você fez para
                                resolvê-la e o resultado da sua ação.
                            </label>
                            <div class='clearfix'></div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.competencias'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='competenciasUm'
                                    value='1'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='competenciasUm'
                                >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.competencias'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='competenciasDois'
                                    value='2'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='competenciasDois'>Atende parcialmente</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.competencias'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='competenciasTres'
                                    value='3'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='competenciasTres'>Atende ao esperado</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.competencias'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='competenciasQuatro'
                                    value='4'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='competenciasQuatro'>Supera as expectativas</label>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Comportamento Ético</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label class='labeltext'
                            >Descreva um fato em que alguém solicitou que você procedesse contra uma norma ou regra.
                                <br/>
                                Conte-me um fato em que foi solicitado um procedimento fora dos padrões, em que
                                precisaria agir inadequadamente. O que você fez?
                                Quais foram os resultados?
                                <br/>
                                Conte uma situação em que lhe foi solicitado agir em desacordo com a política da
                                empresa.
                            </label>
                            <div class='clearfix'></div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comportamento_etico'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comportamento_eticoUm'
                                    value='1'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comportamento_eticoUm'
                                >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comportamento_etico'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comportamento_eticoDois'
                                    value='2'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comportamento_eticoDois'
                                >Atende parcialmente</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comportamento_etico'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comportamento_eticoTres'
                                    value='3'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comportamento_eticoTres'>Atende ao esperado</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comportamento_etico'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comportamento_eticoQuatro'
                                    value='4'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comportamento_eticoQuatro'
                                >Supera as expectativas</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Comprometimento</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label class='labeltext'
                            >Descreva uma situação na qual o seu comprometimento com a empresa foi primordial para que
                                um problema fosse resolvido. <br/>
                                Descreva um fato no qual a sua disciplina e organização foram essenciais para o sucesso
                                de uma ação. <br/>
                                Conte-me uma situação em que você demonstrou disponibilidade para a empresa na qual
                                trabalhava
                            </label>
                            <div class='clearfix'></div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comprometimento'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comprometimentoUm'
                                    value='1'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comprometimentoUm'
                                >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comprometimento'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comprometimentoDois'
                                    value='2'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comprometimentoDois'>Atende parcialmente</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comprometimento'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comprometimentoTres'
                                    value='3'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comprometimentoTres'>Atende ao esperado</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comprometimento'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comprometimentoQuatro'
                                    value='4'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comprometimentoQuatro'
                                >Supera as expectativas</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Comunicação</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label class='labeltext'
                            >Todos nós já passamos por situações em que não compreendemos o que nos foi comunicado. Por
                                exemplo: um prazo de entrega,
                                instruções complicadas, etc. Conte uma situação vivenciada onde isso aconteceu com você.
                                Como você solucionou? <br/>
                                Qual foi o pior problema de comunicação que você já enfrentou? Relate-nos essa
                                experiência.
                            </label>
                            <div class='clearfix'></div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comunicacao'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comunicacaoUm'
                                    value='1'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comunicacaoUm'
                                >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comunicacao'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comunicacaoDois'
                                    value='2'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comunicacaoDois'>Atende parcialmente</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comunicacao'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comunicacaoTres'
                                    value='3'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comunicacaoTres'>Atende ao esperado</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.comunicacao'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='comunicacaoQuatro'
                                    value='4'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='comunicacaoQuatro'>Supera as expectativas</label>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Cultura da Qualidade</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label class='labeltext'
                            >Conte-me um fato em que foi necessário ser obediente aos procedimentos e regras
                                organizacionais, sendo preciso ajustar a ação a
                                ser tomada para que esta se adequasse à política da empresa. <br/>
                                Cite uma situação que você recorreu ao sistema de gestão da qualidade para resolver um
                                problema. <br/>
                                Cite uma situação que você precisou desenvolver uma nova metodologia ou um novo
                                procedimento para resolver ou evitar algum
                                problema na empresa.
                            </label>
                            <div class='clearfix'></div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.cultura_qualidade'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='cultura_qualidadeUm'
                                    value='1'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='cultura_qualidadeUm'
                                >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.cultura_qualidade'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='cultura_qualidadeDois'
                                    value='2'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='cultura_qualidadeDois'>Atende parcialmente</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.cultura_qualidade'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='cultura_qualidadeTres'
                                    value='3'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='cultura_qualidadeTres'>Atende ao esperado</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.cultura_qualidade'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='cultura_qualidadeQuatro'
                                    value='4'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='cultura_qualidadeQuatro'
                                >Supera as expectativas</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Foco no Cliente</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label class='labeltext'
                            >Conte uma situação em que você direcionou seus esforços para satisfazer as necessidades do
                                cliente (interno ou externo). <br/>
                                Cite um fato no qual foi necessário agir com atenção, respeito e cortesia para com um
                                cliente (interno ou externo), mesmo já
                                estando irritado com a situação. Você conseguiu controlar a sua irritação e resolver a
                                questão do cliente? <br/>
                                Ao tratar com um cliente, cite uma situação na qual você conseguiu identificar a
                                necessidade do cliente, surpreendendo-o com a
                                sua iniciativa. O que você sentiu quando isso aconteceu?
                            </label>
                            <div class='clearfix'></div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.foco_cliente'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='foco_clienteUm'
                                    value='1'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='foco_clienteUm'
                                >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.foco_cliente'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='foco_clienteDois'
                                    value='2'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='foco_clienteDois'>Atende parcialmente</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.foco_cliente'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='foco_clienteTres'
                                    value='3'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='foco_clienteTres'>Atende ao esperado</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.foco_cliente'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='foco_clienteQuatro'
                                    value='4'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='foco_clienteQuatro'>Supera as expectativas</label>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Iniciativa</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label class='labeltext'
                            >Conte uma situação na qual você precisou se antecipar para resolucionar um problema. <br/>
                                Fale sobre algum projeto ou idéia que foram aceitos, introduzidos ou realizados com
                                sucesso, principalmente em decorrência de
                                sua iniciativa. <br/>
                                O que você fez recentemente para tornar seu trabalho mais interessante, desafiador,
                                motivante?
                            </label>
                            <div class='clearfix'></div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.iniciativa'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='iniciativaUm'
                                    value='1'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='iniciativaUm'
                                >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.iniciativa'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='iniciativaDois'
                                    value='2'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='iniciativaDois'>Atende parcialmente</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.iniciativa'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='iniciativaTres'
                                    value='3'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='iniciativaTres'>Atende ao esperado</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.iniciativa'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='iniciativaQuatro'
                                    value='4'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='iniciativaQuatro'>Supera as expectativas</label>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Orientação para resultados</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label class='labeltext'
                            >Descreva algumas metas desafiadoras que você planejou para si mesmo e de que forma agiu
                                frente a elas? Conseguiu atingi-las?
                                <br/>
                                Fale sobre a meta mais desafiadora que você teve que alcançar. De que forma agiu? Quais
                                foram os resultados?<br/>
                            </label>
                            <div class='clearfix'></div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.orientacao_resultados'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='orientacao_resultadosUm'
                                    value='1'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='orientacao_resultadosUm'
                                >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.orientacao_resultados'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='orientacao_resultadosDois'
                                    value='2'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='orientacao_resultadosDois'
                                >Atende parcialmente</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.orientacao_resultados'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='orientacao_resultadosTres'
                                    value='3'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='orientacao_resultadosTres'
                                >Atende ao esperado</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.orientacao_resultados'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='orientacao_resultadosQuatro'
                                    value='4'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='orientacao_resultadosQuatro'
                                >Supera as expectativas</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase'>Trabalho em equipe</legend>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label class='labeltext'
                            >Explicite um momento que você precisou se adaptar às diversas situações num determinado
                                grupo. O que você fez? Como você reage
                                quando tem que interagir com outros colegas para a conclusão de uma tarefa? Cite uma
                                situação real. Por que o trabalho em equipe
                                traz resultados mais eficazes? Cite uma situação vivida por você que o faz afirmar isso.
                            </label>
                            <div class='clearfix'></div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.trabalho_equipe'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='trabalho_equipeUm'
                                    value='1'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='trabalho_equipeUm'
                                >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.trabalho_equipe'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='trabalho_equipeDois'
                                    value='2'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='trabalho_equipeDois'>Atende parcialmente</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.trabalho_equipe'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='trabalho_equipeTres'
                                    value='3'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='trabalho_equipeTres'>Atende ao esperado</label>
                            </div>
                            <div class='form-check form-check-inline cursor-pointer'>
                                <input
                                    class='form-check-input'
                                    v-model='form.parecer_rh.trabalho_equipe'
                                    type='radio'
                                    :disabled='visualizar || disabledParecerRh'
                                    id='trabalho_equipeQuatro'
                                    value='4'
                                />
                                <label class='form-check-label cursor-pointer' style='margin-top: 3px;'
                                       for='trabalho_equipeQuatro'
                                >Supera as expectativas</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if='cliente_servico'>
                <legend>Avaliação Psicológica</legend>
                <div class='form-group'>
                    <textarea
                        :disabled="visualizar || disabledParecerRh || (form.parecer_rh.individual_rh.parecer !== 'destaque' && form.parecer_rh.individual_rh.parecer !== 'favoravel')"
                        class='form-control' v-model='form.parecer_rh.individual_rh.avaliacao_psicologica' cols='5'
                        rows='5'></textarea>
                </div>
            </fieldset>

            <fieldset>
                <legend>VAGA DEFINIDA</legend>
                <div class='row'>
                    <div class='col-12 col-sm-6 col-md-4'>
                        <div class='form-group'>
                            <label>Vaga</label>
                            <autocomplete
                                :caminho='caminho_autocomplete'
                                :valido="form.vaga_id !== ''"
                                v-model='form.autocomplete_label_vaga_modal'
                                placeholder='Selecione uma vaga'
                                :id='`vaga_${hash}`'
                                :disabled='visualizar || disabledParecerRh'
                                :formsm='false'
                                @onblur='resetaCampoVagaModal'
                                @onselect='selecionaVagaModal'
                            ></autocomplete>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6 col-md-4'>
                        <div class='form-group'>
                            <label>Cidade</label>
                            <autocomplete
                                :caminho='todos_municipios'
                                :valido="form.curriculo.municipio_id !== ''"
                                v-model='form.curriculo.autocomplete_label_municipio_modal'
                                placeholder='Selecione um municipio'
                                :id='`mun_${hash}`'
                                :disabled='visualizar || disabledParecerRh'
                                :formsm='false'
                                @onblur='resetaCampoMunicipioModal'
                                @onselect='selecionaMunicipioModal'
                            ></autocomplete>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6 col-md-4' v-if='cliente_id === 1'>
                        <div class='form-group'>
                            <label>Empresa</label>
                            <autocomplete
                                :caminho='caminho_cliente_autocomplete'
                                :valido="form.cliente_id !== ''"
                                v-model='form.autocomplete_label_cliente_modal'
                                placeholder='Selecione um cliente'
                                :id='`cliente_${hash}`'
                                :disabled='visualizar || disabledParecerRh'
                                :formsm='false'
                                @onblur='resetaCampoClienteModal'
                                @onselect='selecionaClienteModal'
                            ></autocomplete>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class='text-uppercase' v-if='!cliente_servico'>Parecer Final RH</legend>
                <legend class='text-uppercase' v-if='cliente_servico'>Parecer Individual</legend>
                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Parecer</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-model='form.parecer_rh.parecer_final'
                                v-if='!cliente_servico'
                            >
                                <option value=''>Selecione</option>
                                <option value='indicacao_rh'>Indicação RH</option>
                                <option value='parada'>Parada</option>
                                <option value='fixo'>Fixo</option>
                                <option value='intermitente'>Intermitente</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-model='form.parecer_rh.individual_rh.parecer'
                                v-if='cliente_servico'
                            >
                                <option value=''>Selecione</option>
                                <option value='destaque'>Destaque</option>
                                <option value='favoravel'>Favorável</option>
                                <option value='stand_by'>Stand By</option>
                                <option value='desfavoravel'>Desfavoravel</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-if='!cliente_servico'>
                        <div class='form-group'>
                            <label>Resultado</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-model='form.parecer_rh.parecer_final_um'
                            >
                                <option value=''>Selecione</option>
                                <option value='favoravel'>Favorável RH</option>
                                <option value='restricao'>Restrição</option>
                                <option value='desfavoravel'>Desfavorável</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Nota</label>
                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-model='form.parecer_rh.nota'
                                v-if='!cliente_servico'
                            >
                                <option value=''>Selecione</option>
                                <option v-for='qnt in 10' :value='qnt'>{{ qnt }}</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-model='form.parecer_rh.individual_rh.nota'
                                v-if='cliente_servico'
                            >
                                <option value=''>Selecione</option>
                                <option v-for='qnt in 10' :value='qnt'>{{ qnt }}</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6' v-show='entrevistadoRh'>
                        <div class='form-group'>
                            <label>Entrevistado Por:</label>
                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                onblur='valida_campo_vazio(this,3)'
                                v-if='!cliente_servico'
                                v-model='form.parecer_rh.quem_entrevistou'
                            />

                            <input
                                type='text'
                                :disabled='visualizar || disabledParecerRh'
                                autocomplete='off'
                                class='form-control'
                                onblur='valida_campo_vazio(this,3)'
                                v-if='cliente_servico'
                                v-model='form.parecer_rh.individual_rh.entrevistado_por'
                            />
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label>Comentários</label>
                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.comentarios'
                                cols='3'
                                v-if='!cliente_servico'
                                rows='3'
                            ></textarea>

                            <textarea
                                class='form-control'
                                :disabled='visualizar || disabledParecerRh'
                                v-model='form.parecer_rh.individual_rh.comentario'
                                cols='3'
                                v-if='cliente_servico'
                                rows='3'
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if='entrevistaGestor'>
                <legend class='text-uppercase'>Entrevista Gestor</legend>
                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Parecer</label>
                            <select
                                class='form-control'
                                :disabled='visualizar'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-if='!entrevistaGestorDisabled'
                                v-model='form.parecer_rh.gestor_rh.parecer'
                            >
                                <option value=''>Selecione</option>
                                <option value='destaque'>Destaque</option>
                                <option value='favoravel'>Favorável</option>
                                <option value='stand_by'>Stand By</option>
                                <option value='desfavoravel'>Desfavoravel</option>
                            </select>

                            <select class='form-control' disabled='disabled' v-if='entrevistaGestorDisabled'
                                    v-model='form.parecer_rh.gestor_rh.parecer'>
                                <option value=''>Selecione</option>
                                <option value='destaque'>Destaque</option>
                                <option value='favoravel'>Favorável</option>
                                <option value='stand_by'>Stand By</option>
                                <option value='desfavoravel'>Desfavoravel</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Indicado para</label>
                            <select
                                class='form-control'
                                :disabled='visualizar'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-if='!entrevistaGestorDisabled'
                                v-model='form.parecer_rh.gestor_rh.indicado_para'
                            >
                                <option value=''>Selecione</option>
                                <option value='ATENDENTE RECEPTIVO'>ATENDENTE RECEPTIVO</option>
                                <option value='ATENDENTE ATIVO'>ATENDENTE ATIVO</option>
                                <option value='SUPERVISOR ATENDIMENTO'>SUPERVISOR ATENDIMENTO</option>
                                <option value='ASSISTENTE COMERCIAL I'>ASSISTENTE COMERCIAL I</option>
                                <option value='PROMOTOR DE VENDAS'>PROMOTOR DE VENDAS</option>
                            </select>

                            <select class='form-control' disabled='disabled' v-if='entrevistaGestorDisabled'
                                    v-model='form.parecer_rh.gestor_rh.indicado_para'>
                                <option value=''>Selecione</option>
                                <option value='ATENDENTE RECEPTIVO'>ATENDENTE RECEPTIVO</option>
                                <option value='ATENDENTE ATIVO'>ATENDENTE ATIVO</option>
                                <option value='SUPERVISOR ATENDIMENTO'>SUPERVISOR ATENDIMENTO</option>
                                <option value='ASSISTENTE COMERCIAL I'>ASSISTENTE COMERCIAL I</option>
                                <option value='PROMOTOR DE VENDAS'>PROMOTOR DE VENDAS</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Nota</label>
                            <select
                                class='form-control'
                                :disabled='visualizar'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-if='!entrevistaGestorDisabled'
                                v-model='form.parecer_rh.gestor_rh.nota'
                            >
                                <option value=''>Selecione</option>
                                <option v-for='qnt in 10' :value='qnt'>{{ qnt }}</option>
                            </select>

                            <select class='form-control' disabled='disabled' v-if='entrevistaGestorDisabled'
                                    v-model='form.parecer_rh.gestor_rh.nota'>
                                <option value=''>Selecione</option>
                                <option v-for='qnt in 10' :value='qnt'>{{ qnt }}</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Entrevistado Por:</label>
                            <input
                                type='text'
                                class='form-control'
                                :disabled='visualizar'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                v-if='!entrevistaGestorDisabled'
                                v-model='form.parecer_rh.gestor_rh.entrevistado_por'
                            />

                            <input
                                type='text'
                                class='form-control'
                                disabled='disabled'
                                v-if='entrevistaGestorDisabled'
                                v-model='form.parecer_rh.gestor_rh.entrevistado_por'
                            />
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label>Comentários</label>
                            <textarea
                                class='form-control'
                                :disabled='visualizar || entrevistaGestorDisabled'
                                v-model='form.parecer_rh.gestor_rh.comentario'
                                cols='3'
                                rows='3'
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if='entrevistaRh'>
                <legend class='text-uppercase'>Entrevista RH</legend>
                <div class='row'>
                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Parecer</label>
                            <select
                                class='form-control'
                                :disabled='visualizar'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-if='!entrevistaRhDisabled'
                                v-model='form.parecer_rh.entrevista_rh.parecer'
                            >
                                <option value=''>Selecione</option>
                                <option value='destaque'>Destaque</option>
                                <option value='favoravel'>Favorável</option>
                                <option value='stand_by'>Stand By</option>
                                <option value='desfavoravel'>Desfavoravel</option>
                            </select>

                            <select
                                class='form-control'
                                :disabled='visualizar'
                                disabled='disabled'
                                v-if='entrevistaRhDisabled'
                                v-model='form.parecer_rh.entrevista_rh.parecer'
                            >
                                <option value=''>Selecione</option>
                                <option value='destaque'>Destaque</option>
                                <option value='favoravel'>Favorável</option>
                                <option value='stand_by'>Stand By</option>
                                <option value='desfavoravel'>Desfavoravel</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Indicado para</label>
                            <select
                                class='form-control'
                                :disabled='visualizar'
                                v-if='!entrevistaRhDisabled'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-model='form.parecer_rh.entrevista_rh.indicado_para'
                            >
                                <option value=''>Selecione</option>
                                <option value='ATENDENTE RECEPTIVO'>ATENDENTE RECEPTIVO</option>
                                <option value='ATENDENTE ATIVO'>ATENDENTE ATIVO</option>
                                <option value='SUPERVISOR ATENDIMENTO'>SUPERVISOR ATENDIMENTO</option>
                                <option value='ASSISTENTE COMERCIAL I'>ASSISTENTE COMERCIAL I</option>
                                <option value='PROMOTOR DE VENDAS'>PROMOTOR DE VENDAS</option>
                            </select>

                            <select class='form-control' disabled='disabled' v-if='entrevistaRhDisabled'
                                    v-model='form.parecer_rh.entrevista_rh.indicado_para'>
                                <option value=''>Selecione</option>
                                <option value='ATENDENTE RECEPTIVO'>ATENDENTE RECEPTIVO</option>
                                <option value='ATENDENTE ATIVO'>ATENDENTE ATIVO</option>
                                <option value='SUPERVISOR ATENDIMENTO'>SUPERVISOR ATENDIMENTO</option>
                                <option value='ASSISTENTE COMERCIAL I'>ASSISTENTE COMERCIAL I</option>
                                <option value='PROMOTOR DE VENDAS'>PROMOTOR DE VENDAS</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Nota</label>
                            <select
                                class='form-control'
                                :disabled='visualizar'
                                v-if='!entrevistaRhDisabled'
                                v-bind='!visualizar || !disabledParecerRh ? { onkeyup: "valida_campo_vazio(this, 1)", onblur: "valida_campo_vazio(this, 1)", } : {}'
                                onchange='valida_campo_vazio(this,1)'
                                v-model='form.parecer_rh.entrevista_rh.nota'
                            >
                                <option value=''>Selecione</option>
                                <option v-for='qnt in 10' :value='qnt'>{{ qnt }}</option>
                            </select>

                            <select class='form-control' disabled='disabled' v-if='entrevistaRhDisabled'
                                    v-model='form.parecer_rh.entrevista_rh.nota'>
                                <option value=''>Selecione</option>
                                <option v-for='qnt in 10' :value='qnt'>{{ qnt }}</option>
                            </select>
                        </div>
                    </div>

                    <div class='col-12 col-sm-6'>
                        <div class='form-group'>
                            <label>Entrevistado Por:</label>
                            <input type='text' :disabled='visualizar' autocomplete='off' v-if='!entrevistaRhDisabled'
                                   class='form-control' onblur='valida_campo_vazio(this,3)'
                                   v-model='form.parecer_rh.entrevista_rh.entrevistado_por'/>

                            <input type='text' disabled autocomplete='off' v-if='entrevistaRhDisabled'
                                   class='form-control' v-model='form.parecer_rh.entrevista_rh.entrevistado_por'/>
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12'>
                        <div class='form-group'>
                            <label>Comentários</label>
                            <textarea class='form-control' :disabled='visualizar || entrevistaRhDisabled'
                                      v-model='form.parecer_rh.entrevista_rh.comentario' cols='3' rows='3'></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</template>

<script>
import DadosPessoais from './DadosPessoaisTexto'

export default {
    props: {
        form: {
            type: Object,
            required: true,
            default: () => {
            }
        },
        visualizar: {
            type: Boolean,
            required: false,
            default: false
        },
        disabledParecerRh: {
            type: Boolean,
            required: false,
            default: true
        },
        entrevistadoRh: {
            type: Boolean,
            required: false,
            default: true
        },
        entrevistaRh: {
            type: Boolean,
            required: false,
            default: false
        },
        entrevistaRhDisabled: {
            type: Boolean,
            required: false,
            default: false
        },
        entrevistaGestor: {
            type: Boolean,
            required: false,
            default: false
        },
        entrevistaGestorDisabled: {
            type: Boolean,
            required: false,
            default: false
        },
        cliente_id: {
            type: Number,
            required: true,
            default: 0
        }
    },
    data() {
        return {
            provas: 0,
            hash: `mastertag_${parseInt(Math.random() * 999999)}`,
            preload: true,

            todos_municipios: `autocomplete/todos-municipios`,

            caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
            autocomplete_label_anterior: '',
            autocomplete_label: '',
            caminho_cliente_autocomplete: `autocomplete/todos-clientes-ativos`,
            autocomplete_label_cliente_anterior: '',
            autocomplete_label_cliente: '',

            cliente_servico: false,

            formDefault: {
                id: '',

                vaga_id: '',
                autocomplete_label_vaga_modal: '',
                autocomplete_label_vaga_modal_anterior: '',

                cliente_id: '',
                autocomplete_label_cliente_modal: '',
                autocomplete_label_cliente_modal_anterior: '',

                curriculo: {
                    nome: '',
                    nascimento: '',
                    municipio_id: '',
                    autocomplete_label_municipio_modal: '',
                    autocomplete_label_municipio_modal_anterior: ''
                },

                certificados_nr: [],
                certificados_nrDelete: [],
                cursos_formacoes: [],
                cursos_formacoesDelete: [],

                parecer_rh: {
                    feedback_id: '',
                    formulario_id: '',
                    tipo_entrevista: 'Fixo',
                    curriculo_id: '',
                    destro: '',
                    ex_funcionario: '',
                    cnh: '',
                    cnh_tipo: '',
                    mora_com_quem: '',
                    rota_bairro: '',
                    calca: '',
                    bota: '',
                    camisa_protecao: '',
                    camisa_meia: '',
                    casado: '',
                    tempodeconvivencia: '',
                    filhos: '',
                    qnt_filhos: '',
                    conjuge_trabalha: '',
                    trabalho_conjuge: '',
                    religioso: '',
                    religiao_praticante: '',
                    fuma: '',
                    frequencia_fuma: '',
                    bebe: '',
                    frequencia_bebe: '',
                    nr_dez: '',
                    indicacao: '',
                    indicado_por: '',
                    alumar_experiencia: '',
                    alumar_experiencia_area: '',
                    outra_industria_experiencia: '',
                    outra_industria_nome: '',
                    grau_instrucao: '',
                    horaextra: '',
                    turnos_seis_por_dois: '',
                    noturno: '',
                    acidente_trabalho: '',
                    acidente_trabalho_qual: '',
                    afastamento_inss: '',
                    afastamento_inss_qual: '',
                    situacao_saude: '',
                    comportamento_seguro: '',
                    energia_para_trabalho: '',
                    postura: '',
                    historico_profissional: '',
                    historico_educacional: '',
                    objetivos_expectativas: '',
                    auto_imagem: '',
                    competencias: '',
                    comportamento_etico: '',
                    comprometimento: '',
                    comunicacao: '',
                    cultura_qualidade: '',
                    foco_cliente: '',
                    iniciativa: '',
                    orientacao_resultados: '',
                    trabalho_equipe: '',
                    parecer_final: '',
                    parecer_final_um: '',
                    nota: '',
                    comentarios: '',
                    entrevistador: '',
                    quem_entrevistou: '',

                    nota_digitacao: '',
                    dinamicadegrupo: '',
                    obs_dinamicadegrupo: '',
                    experiencia_callcenter: '',
                    disponibilidade_horarios: '',
                    turnos_seis_por_um: '',
                    horario_preferencial: '',
                    obs_call: '',
                    obs_horario: '',

                    individual_rh: {
                        parecer: '',
                        nota: '',
                        entrevistado_por: '',
                        comentario: '',
                        avaliacao_psicologica: ''
                    },

                    gestor_rh: {
                        parecer: '',
                        indicado_para: '',
                        nota: '',
                        entrevistado_por: '',
                        comentario: ''
                    },

                    entrevista_rh: {
                        parecer: '',
                        indicado_para: '',
                        nota: '',
                        entrevistado_por: '',
                        comentario: ''
                    }
                },

                resultado_integrado: {
                    documentos_entregue: '',
                    documentos_entregue_data: '',
                    encaminhado_exame: '',
                    encaminhado_exame_data: '',
                    encaminhado_treinamento: '',
                    encaminhado_treinamento_data: '',
                    excessao: '',
                    autorizado_por: '',
                    responsavel_envio: '',
                    obs: ''
                },

                simulados: []
            }
        }
    },
    mounted() {
        this.preload = true
        axios.get(`${URL_ADMIN}/entrevistas/parecer_rh/${this.form.id}/editar`)
            .then(response => {
                let data = response.data
                this.provas = data.provas
                Object.assign(this.form, data.feedback)
                this.cliente_servico = data.feedback.cliente.area_id > 1

                //Se não tiver parecer_rh
                this.form.parecer_rh = data.feedback.parecer_rh ? data.feedback.parecer_rh : _.cloneDeep(this.formDefault.parecer_rh)
                this.$emit('finalizou', {})
                this.form.parecer_rh.gestor_rh = data.feedback.parecer_rh.gestor_rh ? data.feedback.parecer_rh.gestor_rh : _.cloneDeep(this.formDefault.parecer_rh.gestor_rh)
                this.form.parecer_rh.entrevista_rh = data.feedback.parecer_rh.entrevista_rh ? data.feedback.parecer_rh.entrevista_rh : _.cloneDeep(this.formDefault.parecer_rh.entrevista_rh)

                this.preload = false
            })
            .catch(error => {
                this.preload = false
            })

        if (this.disabledParecerRh) {
            setTimeout(() => {
                $('#formParecerRh select').removeAttr('onblur');
            }, 8000)
        }
    },

    components: {
        DadosPessoais
    },

    methods: {
        /** Adiciona linhas **/
        addLICurso() {
            let obj = {}
            obj.nova = true
            obj.curso = ''
            obj.instituicao = ''
            obj.emissao = ''
            obj.validade = ''

            this.form.cursos_formacoes.push(obj)
        },
        removerLICurso(index) {
            if (this.editando) {
                this.form.cursos_formacoesDelete.push(this.form.cursos_formacoes[index].id)
            }
            this.form.cursos_formacoes.splice(index, 1)
        },
        addLINr() {
            let obj = {}
            obj.nova = true
            obj.nr_dez = false
            obj.nr_dez_instituicao = ''
            obj.nr_dez_emissao = ''
            obj.nr_dez_validade = ''

            this.form.certificados_nr.push(obj)
        },
        removerLINr(index) {
            if (this.editando) {
                this.form.certificados_nrDelete.push(this.form.certificados_nr[index].id)
            }
            this.form.certificados_nr.splice(index, 1)
        },
        /***Campos de Modal ****/
        selecionaMunicipioModal(obj) {
            this.form.curriculo.municipio_id = obj.id
            this.form.curriculo.autocomplete_label_municipio_modal = obj.label
            this.form.curriculo.autocomplete_label_municipio_modal_anterior = obj.label
        },
        resetaCampoMunicipioModal() {
            if (this.form.curriculo.autocomplete_label_municipio_modal_anterior !== this.form.curriculo.autocomplete_label_municipio_modal) {
                this.form.curriculo.autocomplete_label_municipio_modal_anterior = ''
                this.form.curriculo.autocomplete_label_municipio_modal = ''
                this.form.curriculo.municipio_id = ''
                valida_campo_vazio($('#mun_' + this.hash), 1)
                setTimeout(() => {
                    if (this.form.curriculo.municipio_id === '') {
                        $('#janelaParecerEntrevista #mun_' + this.hash)
                            .focus()
                            .trigger('blur')
                        mostraErro('', 'O Campo Município não pode ficar vazio')
                    }
                }, 100)
            }
        },
        selecionaVagaModal(obj) {
            this.form.vaga_id = obj.id
            this.form.autocomplete_label_vaga_modal = obj.label
            this.form.autocomplete_label_vaga_modal_anterior = obj.label
        },
        resetaCampoVagaModal() {
            if (this.form.autocomplete_label_vaga_modal_anterior !== this.form.autocomplete_label_vaga_modal) {
                this.form.autocomplete_label_vaga_modal_anterior = ''
                this.form.autocomplete_label_vaga_modal = ''
                this.form.vaga_id = ''
                setTimeout(() => {
                    if (this.form.vaga_id === '') {
                        mostraErro('', 'O Campo Vaga não pode ficar vazio')
                    }
                }, 100)
            }
        },
        selecionaClienteModal(obj) {
            this.form.cliente_id = obj.id
            this.form.autocomplete_label_cliente_modal = obj.label
            this.form.autocomplete_label_cliente_modal_anterior = obj.label
        },
        resetaCampoClienteModal() {
            if (this.form.autocomplete_label_cliente_modal_anterior !== this.form.autocomplete_label_cliente_modal) {
                this.form.autocomplete_label_cliente_modal_anterior = ''
                this.form.autocomplete_label_cliente_modal = ''
                this.form.cliente_id = ''
                setTimeout(() => {
                    if (this.form.cliente_id === '') {
                        mostraErro('', 'O Campo Cliente não pode ficar vazio')
                    }
                }, 100)
            }
        },
        changeTipoEntrevista() {
            formReset()
            setTimeout(function () {
                $('#janelaParecerEntrevista :input:visible').trigger('blur')
            }, 100)
        }
    }
}
</script>

<style scoped></style>
