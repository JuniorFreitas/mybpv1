<template>
    <div id="formParecerRh" class="mybp-modal-form mybp-filtros-compactos">
        <div v-if="!preload">
            <dados-pessoais :form="form"></dados-pessoais>

            <fieldset v-if="!cliente_servico" class="mybp-modal-secao">
                <legend>Tipo de entrevista</legend>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Tipo</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-tipo_entrevista-${hash}`"
                                    :input-id="`rh-tipo_entrevista-${hash}`"
                                    v-model="form.parecer_rh.tipo_entrevista"
                                    :options="opcoesTipoEntrevista"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-tipo_entrevista-${hash}`)"
                                    @select="onRhComboSelect(`rh-tipo_entrevista-${hash}`, 'tipo')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if="provas > 0" class="mybp-modal-secao">
                <legend>Provas</legend>
                <div class="alert alert-warning" v-show="form.simulados.length < provas">
                    <i class="fa fa-exclamation-triangle"></i> Atenção! Candidato(a) possuí prova pendente.
                </div>

                <div
                    class="pt-2"
                    style="border-bottom: 1px dashed #cccccc"
                    v-show="form.simulados.length"
                    v-for="(prova, index) in form.simulados"
                    :key="prova.id || index"
                >
                    <p>
                        Teste: <strong>{{ prova.simulado_vaga.simulado.titulo }}</strong> Acertos: <strong>{{ prova.acertos }} </strong><br />
                        Tempo executado: <strong>{{ prova.tempo_execucao }} min </strong><br />
                        Finalizado em <strong>{{ prova.data_finalizacao }}h</strong>
                    </p>
                </div>
            </fieldset>

            <fieldset v-if="cliente_servico" class="mybp-modal-secao">
                <legend>Nota teste digitação</legend>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Nota</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-nota_digitacao-${hash}`"
                                    :input-id="`rh-nota_digitacao-${hash}`"
                                    v-model="form.parecer_rh.nota_digitacao"
                                    :options="opcoesNota1a10"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-nota_digitacao-${hash}`)"
                                    @select="onRhComboSelect(`rh-nota_digitacao-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Informações complementares</legend>
                <div class="row">
                    <div class="col-12 col-md-4" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Domínio</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-destro-${hash}`"
                                    :input-id="`rh-destro-${hash}`"
                                    v-model="form.parecer_rh.destro"
                                    :options="opcoesDestro"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-destro-${hash}`)"
                                    @select="onRhComboSelect(`rh-destro-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Ex Funcionário</label>
                            <mybp-bool-combobox
                                :input-id="`rh-ex_funcionario-${hash}`"
                                v-model="form.parecer_rh.ex_funcionario"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-ex_funcionario-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-ex_funcionario-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">CNH</label>
                            <mybp-bool-combobox
                                :input-id="`rh-cnh-${hash}`"
                                v-model="form.parecer_rh.cnh"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-cnh-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-cnh-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="form.parecer_rh.cnh">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Tipo da CNH</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' } : {}
                                "
                                v-model="form.parecer_rh.cnh_tipo"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-8" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Rota/Bairro</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    form.parecer_rh.tipo_entrevista === 'Fixo' && (!visualizar || !disabledParecerRh)
                                        ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' }
                                        : {}
                                "
                                v-model="form.parecer_rh.rota_bairro"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Avaliação Dinâmica de Grupo</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-dinamicadegrupo-${hash}`"
                                    :input-id="`rh-dinamicadegrupo-${hash}`"
                                    v-model="form.parecer_rh.dinamicadegrupo"
                                    :options="opcoesDinamicaGrupo"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-dinamicadegrupo-${hash}`)"
                                    @select="onRhComboSelect(`rh-dinamicadegrupo-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-8" v-if="form.parecer_rh.dinamicadegrupo">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">OBS.:</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-model="form.parecer_rh.obs_dinamicadegrupo"
                            />
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if="!cliente_servico" class="mybp-modal-secao">
                <legend>EPI</legend>
                <div class="row">
                    <div class="col-12 col-md-3">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Camisa de Meia</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-camisa_meia-${hash}`"
                                    :input-id="`rh-camisa_meia-${hash}`"
                                    v-model="form.parecer_rh.camisa_meia"
                                    :options="opcoesCamisaMeia"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-camisa_meia-${hash}`)"
                                    @select="onRhComboSelect(`rh-camisa_meia-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Camisa Proteção</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-camisa_protecao-${hash}`"
                                    :input-id="`rh-camisa_protecao-${hash}`"
                                    v-model="form.parecer_rh.camisa_protecao"
                                    :options="opcoesCamisaProt"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-camisa_protecao-${hash}`)"
                                    @select="onRhComboSelect(`rh-camisa_protecao-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Calça</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-calca-${hash}`"
                                    :input-id="`rh-calca-${hash}`"
                                    v-model="form.parecer_rh.calca"
                                    :options="opcoesCalca"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-calca-${hash}`)"
                                    @select="onRhComboSelect(`rh-calca-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Bota</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-bota-${hash}`"
                                    :input-id="`rh-bota-${hash}`"
                                    v-model="form.parecer_rh.bota"
                                    :options="opcoesBota"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-bota-${hash}`)"
                                    @select="onRhComboSelect(`rh-bota-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Histórico familiar e social</legend>
                <div class="row">
                    <div class="col-12 col-md-8">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Mora com quem?</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico && (!visualizar || !disabledParecerRh)
                                        ? { onchange: 'valida_campo_vazio(this, 2)', onblur: 'valida_campo_vazio(this, 2)' }
                                        : {}
                                "
                                v-model="form.parecer_rh.mora_com_quem"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Casado?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-casado-${hash}`"
                                v-model="form.parecer_rh.casado"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-casado-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-casado-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="form.parecer_rh.casado">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Tempo de convivência</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' } : {}
                                "
                                v-model="form.parecer_rh.tempodeconvivencia"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Filhos?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-filhos-${hash}`"
                                v-model="form.parecer_rh.filhos"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-filhos-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-filhos-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="form.parecer_rh.filhos">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Quantos?</label>
                            <input
                                type="number"
                                min="1"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' } : {}
                                "
                                v-model="form.parecer_rh.qnt_filhos"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="form.parecer_rh.casado">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Esposa ou Marido Trabalha?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-conjuge_trabalha-${hash}`"
                                v-model="form.parecer_rh.conjuge_trabalha"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-conjuge_trabalha-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-conjuge_trabalha-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="form.parecer_rh.casado && form.parecer_rh.conjuge_trabalha">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Em quê?</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' } : {}
                                "
                                v-model="form.parecer_rh.trabalho_conjuge"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Praticante de alguma religião?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-religioso-${hash}`"
                                v-model="form.parecer_rh.religioso"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-religioso-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-religioso-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="form.parecer_rh.religioso">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Qual?</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' } : {}
                                "
                                v-model="form.parecer_rh.religiao_praticante"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Fuma?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-fuma-${hash}`"
                                v-model="form.parecer_rh.fuma"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-fuma-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-fuma-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="form.parecer_rh.fuma">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Qual frequência?</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' } : {}
                                "
                                v-model="form.parecer_rh.frequencia_fuma"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Bebe?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-bebe-${hash}`"
                                v-model="form.parecer_rh.bebe"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-bebe-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-bebe-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="form.parecer_rh.bebe">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Qual frequência?</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' } : {}
                                "
                                v-model="form.parecer_rh.frequencia_bebe"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Indicado por alguém?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-indicacao-${hash}`"
                                v-model="form.parecer_rh.indicacao"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-indicacao-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-indicacao-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="form.parecer_rh.indicacao">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Quem?</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' } : {}
                                "
                                v-model="form.parecer_rh.indicado_por"
                            />
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Outras informações</legend>
                <div class="row">
                    <div class="col-12 col-md-4" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Experiência na ALUMAR?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-alumar_experiencia-${hash}`"
                                v-model="form.parecer_rh.alumar_experiencia"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-alumar_experiencia-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-alumar_experiencia-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico && form.parecer_rh.alumar_experiencia">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Qual área?</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' } : {}
                                "
                                v-model="form.parecer_rh.alumar_experiencia_area"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Experiência na área?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-experiencia_callcenter-${hash}`"
                                v-model="form.parecer_rh.experiencia_callcenter"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-experiencia_callcenter-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-experiencia_callcenter-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-8" v-if="cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Observação</label>
                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                cols="3"
                                rows="2"
                                v-model="form.parecer_rh.obs_call"
                            ></textarea>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Grau de instrução</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico && (!visualizar || !disabledParecerRh)
                                        ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' }
                                        : {}
                                "
                                v-model="form.parecer_rh.grau_instrucao"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Disponibilidade de hora extra?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-horaextra-${hash}`"
                                v-model="form.parecer_rh.horaextra"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-horaextra-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-horaextra-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Disponibilidade para turnos 6X2?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-turnos_seis_por_dois-${hash}`"
                                v-model="form.parecer_rh.turnos_seis_por_dois"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-turnos_seis_por_dois-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-turnos_seis_por_dois-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Disponibilidade para noturno?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-noturno-${hash}`"
                                v-model="form.parecer_rh.noturno"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-noturno-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-noturno-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Acidente de trabalho anterior?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-acidente_trabalho-${hash}`"
                                v-model="form.parecer_rh.acidente_trabalho"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-acidente_trabalho-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-acidente_trabalho-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico && form.parecer_rh.acidente_trabalho">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Especifique</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh
                                        ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' }
                                        : {}
                                "
                                v-model="form.parecer_rh.acidente_trabalho_qual"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Afastamento INSS anterior?</label>
                            <mybp-bool-combobox
                                :input-id="`rh-afastamento_inss-${hash}`"
                                v-model="form.parecer_rh.afastamento_inss"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-afastamento_inss-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-afastamento_inss-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico && form.parecer_rh.afastamento_inss">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Especifique</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh
                                        ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' }
                                        : {}
                                "
                                v-model="form.parecer_rh.afastamento_inss_qual"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Disponibilidade de horários</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-disponibilidade_horarios-${hash}`"
                                    :input-id="`rh-disponibilidade_horarios-${hash}`"
                                    v-model="form.parecer_rh.disponibilidade_horarios"
                                    :options="opcoesDisponibilidadeHorarios"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-disponibilidade_horarios-${hash}`)"
                                    @select="onRhComboSelect(`rh-disponibilidade_horarios-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Disponibilidade para turnos 6X1</label>
                            <mybp-bool-combobox
                                :input-id="`rh-turnos_seis_por_um-${hash}`"
                                v-model="form.parecer_rh.turnos_seis_por_um"
                                :disabled="visualizar || disabledParecerRh"
                                @opening="fecharOutrosComboboxes(`rh-turnos_seis_por_um-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-turnos_seis_por_um-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Horário Preferencial</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-horario_preferencial-${hash}`"
                                    :input-id="`rh-horario_preferencial-${hash}`"
                                    v-model="form.parecer_rh.horario_preferencial"
                                    :options="opcoesHorarioPreferencial"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-horario_preferencial-${hash}`)"
                                    @select="onRhComboSelect(`rh-horario_preferencial-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12" v-if="cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Observação</label>
                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                cols="3"
                                rows="2"
                                v-model="form.parecer_rh.obs_horario"
                            ></textarea>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Especifique situações de saúde</label>
                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                v-model="form.parecer_rh.situacao_saude"
                                cols="3"
                                rows="2"
                                v-bind="
                                    form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico && (!visualizar || !disabledParecerRh)
                                        ? { onkeyup: 'valida_campo_vazio(this, 2)', onblur: 'valida_campo_vazio(this, 2)' }
                                        : {}
                                "
                            ></textarea>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Certificado NR 10</label>
                            <mybp-bool-combobox
                                :input-id="`rh-nr_dez-${hash}`"
                                v-model="form.parecer_rh.nr_dez"
                                :disabled="visualizar || disabledParecerRh"
                                mode="simnao"
                                @opening="fecharOutrosComboboxes(`rh-nr_dez-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-nr_dez-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Comportamento Seguro</label>
                            <mybp-bool-combobox
                                :input-id="`rh-comportamento_seguro-${hash}`"
                                v-model="form.parecer_rh.comportamento_seguro"
                                :disabled="visualizar || disabledParecerRh"
                                mode="simnao"
                                @opening="fecharOutrosComboboxes(`rh-comportamento_seguro-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-comportamento_seguro-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Energia para o trabalho</label>
                            <mybp-bool-combobox
                                :input-id="`rh-energia_para_trabalho-${hash}`"
                                v-model="form.parecer_rh.energia_para_trabalho"
                                :disabled="visualizar || disabledParecerRh"
                                mode="simnao"
                                @opening="fecharOutrosComboboxes(`rh-energia_para_trabalho-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-energia_para_trabalho-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Postura</label>
                            <mybp-bool-combobox
                                :input-id="`rh-postura-${hash}`"
                                v-model="form.parecer_rh.postura"
                                :disabled="visualizar || disabledParecerRh"
                                mode="simnao"
                                @opening="fecharOutrosComboboxes(`rh-postura-${hash}`)"
                                    @select="limparComboboxInvalido(`rh-postura-${hash}`)"
                            ></mybp-bool-combobox>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if="!cliente_servico && form.parecer_rh.nr_dez === 'sim'" class="mybp-modal-secao">
                <legend>Certificado NR10</legend>
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-sm mr-1 btn-primary mb-2" :disabled="visualizar || disabledParecerRh" @click.prevent="addLINr($event.target)">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            Adicionar Certificado NR10
                        </button>
                    </div>
                </div>

                <div
                    class="row align-items-end py-2"
                    style="border-bottom: 1px dashed #cccccc"
                    v-show="form.certificados_nr.length > 0"
                    v-for="(obj, index) in form.certificados_nr"
                    :key="obj.id || index"
                >
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Instituição</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh
                                        ? { onkeyup: 'valida_campo_vazio(this, 2)', onblur: 'valida_campo_vazio(this, 2)' }
                                        : {}
                                "
                                v-model="obj.nr_dez_instituicao"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                            <label class="mybp-label">Data de Emissão</label>
                            <datepicker label="" :disabled="visualizar || disabledParecerRh" v-model="obj.nr_dez_emissao" formsm></datepicker>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                            <label class="mybp-label">Data de Validade</label>
                            <datepicker label="" :disabled="visualizar || disabledParecerRh" v-model="obj.nr_dez_validade" formsm></datepicker>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-sm mr-1 btn-primary" :disabled="visualizar || disabledParecerRh" @click.prevent="addLINr($event.target)">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            Certificado
                        </button>
                        <button class="btn btn-sm mr-1 btn-danger" :disabled="visualizar || disabledParecerRh" @click.prevent="removerLINr(index)">
                            <i class="fa fa-times"></i> Remover
                        </button>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if="!cliente_servico" class="mybp-modal-secao">
                <legend>Cursos de formação</legend>
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-sm mr-1 btn-primary mb-2" :disabled="visualizar || disabledParecerRh" @click.prevent="addLICurso($event.target)">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            Adicionar Curso de Formação
                        </button>
                    </div>
                </div>

                <div
                    class="row align-items-end py-2"
                    style="border-bottom: 1px dashed #cccccc"
                    v-show="form.cursos_formacoes.length > 0"
                    v-for="(obj, index) in form.cursos_formacoes"
                    :key="obj.id || index"
                >
                    <div class="col-12 col-md-3">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Curso</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh
                                        ? { onkeyup: 'valida_campo_vazio(this, 2)', onblur: 'valida_campo_vazio(this, 2)' }
                                        : {}
                                "
                                v-model="obj.curso"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Instituição</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-bind="
                                    !visualizar || !disabledParecerRh
                                        ? { onkeyup: 'valida_campo_vazio(this, 2)', onblur: 'valida_campo_vazio(this, 2)' }
                                        : {}
                                "
                                v-model="obj.instituicao"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                            <label class="mybp-label">Data de Emissão</label>
                            <datepicker label="" :disabled="visualizar || disabledParecerRh" v-model="obj.emissao" formsm></datepicker>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Data de Validade</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                v-mascara:data
                                onblur="valida_data(this)"
                                v-model="obj.validade"
                            />
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-sm mr-1 btn-primary" :disabled="visualizar || disabledParecerRh" @click.prevent="addLICurso($event.target)">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            Curso de Formação
                        </button>
                        <button class="btn btn-sm mr-1 btn-danger" :disabled="visualizar || disabledParecerRh" @click.prevent="removerLICurso(index)">
                            <i class="fa fa-times"></i> Remover
                        </button>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Histórico profissional</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label"
                                >Quais as suas últimas experiências? Nome das empresas, cargos ocupados, tempo de permanência nas funções e os motivos de saída.
                            </label>
                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                v-model="form.parecer_rh.historico_profissional"
                                cols="3"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 2)', onblur: 'valida_campo_vazio(this, 2)' } : {}
                                "
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                                rows="3"
                            ></textarea>

                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                v-model="form.parecer_rh.historico_profissional"
                                cols="3"
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Histórico educacional</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Fale-me sobre sua formação educacional e cursos.</label>
                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                v-model="form.parecer_rh.historico_educacional"
                                cols="3"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 2)', onblur: 'valida_campo_vazio(this, 2)' } : {}
                                "
                                v-if="form.parecer_rh.tipo_entrevista === 'Fixo' && !cliente_servico"
                                rows="3"
                            ></textarea>

                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                v-model="form.parecer_rh.historico_educacional"
                                cols="3"
                                v-if="form.parecer_rh.tipo_entrevista === 'Parada' || cliente_servico"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Objetivos e expectativas</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label"
                                >Quais são suas expectativas em relação à essa empresa e ao cargo ao qual você se candidatou? Geralmente, que o motiva e o que
                                você detesta no trabalho? Que seria um ambiente ideal de trabalho? Quais são seus planos profissionais em curto prazo? E a
                                longo? Quais são seus planos pessoais? Porque deveríamos contratá-lo?</label
                            >
                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                v-model="form.parecer_rh.objetivos_expectativas"
                                cols="3"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Autoimagem</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label"
                                >Quais são suas maiores qualidades? Que aspectos da sua vida você precisa melhorar? Qual foi sua maior frustração? Qual é o seu
                                maior sonho?</label
                            >
                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                v-model="form.parecer_rh.auto_imagem"
                                cols="3"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Competências</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label labeltext"
                                >Conte-me, com riqueza de detalhes, um fato recente que realmente tenha ocorrido no âmbito profissional. Caso a situação
                                perguntada não faça parte do seu histórico profissional, busque-a na sua formação acadêmica e por fim na sua vida pessoal.
                                <br />
                                Ao contar o fato lembre-se de citar o contexto, a ação e o resultado, ou seja, o momento em que aconteceu, o que você fez para
                                resolvê-la e o resultado da sua ação.
                            </label>
                            <div class="clearfix"></div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.competencias"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="competenciasUm"
                                    value="1"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="competenciasUm"
                                    >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.competencias"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="competenciasDois"
                                    value="2"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="competenciasDois">Atende parcialmente</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.competencias"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="competenciasTres"
                                    value="3"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="competenciasTres">Atende ao esperado</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.competencias"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="competenciasQuatro"
                                    value="4"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="competenciasQuatro">Supera as expectativas</label>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Comportamento ético</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label labeltext"
                                >Descreva um fato em que alguém solicitou que você procedesse contra uma norma ou regra.
                                <br />
                                Conte-me um fato em que foi solicitado um procedimento fora dos padrões, em que precisaria agir inadequadamente. O que você fez?
                                Quais foram os resultados?
                                <br />
                                Conte uma situação em que lhe foi solicitado agir em desacordo com a política da empresa.
                            </label>
                            <div class="clearfix"></div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comportamento_etico"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comportamento_eticoUm"
                                    value="1"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comportamento_eticoUm"
                                    >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comportamento_etico"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comportamento_eticoDois"
                                    value="2"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comportamento_eticoDois">Atende parcialmente</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comportamento_etico"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comportamento_eticoTres"
                                    value="3"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comportamento_eticoTres">Atende ao esperado</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comportamento_etico"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comportamento_eticoQuatro"
                                    value="4"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comportamento_eticoQuatro"
                                    >Supera as expectativas</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Comprometimento</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label labeltext"
                                >Descreva uma situação na qual o seu comprometimento com a empresa foi primordial para que um problema fosse resolvido. <br />
                                Descreva um fato no qual a sua disciplina e organização foram essenciais para o sucesso de uma ação. <br />
                                Conte-me uma situação em que você demonstrou disponibilidade para a empresa na qual trabalhava
                            </label>
                            <div class="clearfix"></div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comprometimento"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comprometimentoUm"
                                    value="1"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comprometimentoUm"
                                    >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comprometimento"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comprometimentoDois"
                                    value="2"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comprometimentoDois">Atende parcialmente</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comprometimento"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comprometimentoTres"
                                    value="3"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comprometimentoTres">Atende ao esperado</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comprometimento"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comprometimentoQuatro"
                                    value="4"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comprometimentoQuatro"
                                    >Supera as expectativas</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Comunicação</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label labeltext"
                                >Todos nós já passamos por situações em que não compreendemos o que nos foi comunicado. Por exemplo: um prazo de entrega,
                                instruções complicadas, etc. Conte uma situação vivenciada onde isso aconteceu com você. Como você solucionou? <br />
                                Qual foi o pior problema de comunicação que você já enfrentou? Relate-nos essa experiência.
                            </label>
                            <div class="clearfix"></div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comunicacao"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comunicacaoUm"
                                    value="1"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comunicacaoUm"
                                    >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comunicacao"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comunicacaoDois"
                                    value="2"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comunicacaoDois">Atende parcialmente</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comunicacao"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comunicacaoTres"
                                    value="3"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comunicacaoTres">Atende ao esperado</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.comunicacao"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="comunicacaoQuatro"
                                    value="4"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="comunicacaoQuatro">Supera as expectativas</label>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Cultura da qualidade</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label labeltext"
                                >Conte-me um fato em que foi necessário ser obediente aos procedimentos e regras organizacionais, sendo preciso ajustar a ação a
                                ser tomada para que esta se adequasse à política da empresa. <br />
                                Cite uma situação que você recorreu ao sistema de gestão da qualidade para resolver um problema. <br />
                                Cite uma situação que você precisou desenvolver uma nova metodologia ou um novo procedimento para resolver ou evitar algum
                                problema na empresa.
                            </label>
                            <div class="clearfix"></div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.cultura_qualidade"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="cultura_qualidadeUm"
                                    value="1"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="cultura_qualidadeUm"
                                    >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.cultura_qualidade"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="cultura_qualidadeDois"
                                    value="2"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="cultura_qualidadeDois">Atende parcialmente</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.cultura_qualidade"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="cultura_qualidadeTres"
                                    value="3"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="cultura_qualidadeTres">Atende ao esperado</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.cultura_qualidade"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="cultura_qualidadeQuatro"
                                    value="4"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="cultura_qualidadeQuatro"
                                    >Supera as expectativas</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Foco no cliente</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label labeltext"
                                >Conte uma situação em que você direcionou seus esforços para satisfazer as necessidades do cliente (interno ou externo). <br />
                                Cite um fato no qual foi necessário agir com atenção, respeito e cortesia para com um cliente (interno ou externo), mesmo já
                                estando irritado com a situação. Você conseguiu controlar a sua irritação e resolver a questão do cliente? <br />
                                Ao tratar com um cliente, cite uma situação na qual você conseguiu identificar a necessidade do cliente, surpreendendo-o com a
                                sua iniciativa. O que você sentiu quando isso aconteceu?
                            </label>
                            <div class="clearfix"></div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.foco_cliente"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="foco_clienteUm"
                                    value="1"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="foco_clienteUm"
                                    >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.foco_cliente"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="foco_clienteDois"
                                    value="2"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="foco_clienteDois">Atende parcialmente</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.foco_cliente"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="foco_clienteTres"
                                    value="3"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="foco_clienteTres">Atende ao esperado</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.foco_cliente"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="foco_clienteQuatro"
                                    value="4"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="foco_clienteQuatro">Supera as expectativas</label>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Iniciativa</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label labeltext"
                                >Conte uma situação na qual você precisou se antecipar para resolucionar um problema. <br />
                                Fale sobre algum projeto ou idéia que foram aceitos, introduzidos ou realizados com sucesso, principalmente em decorrência de
                                sua iniciativa. <br />
                                O que você fez recentemente para tornar seu trabalho mais interessante, desafiador, motivante?
                            </label>
                            <div class="clearfix"></div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.iniciativa"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="iniciativaUm"
                                    value="1"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="iniciativaUm"
                                    >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.iniciativa"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="iniciativaDois"
                                    value="2"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="iniciativaDois">Atende parcialmente</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.iniciativa"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="iniciativaTres"
                                    value="3"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="iniciativaTres">Atende ao esperado</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.iniciativa"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="iniciativaQuatro"
                                    value="4"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="iniciativaQuatro">Supera as expectativas</label>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Orientação para resultados</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label labeltext"
                                >Descreva algumas metas desafiadoras que você planejou para si mesmo e de que forma agiu frente a elas? Conseguiu atingi-las?
                                <br />
                                Fale sobre a meta mais desafiadora que você teve que alcançar. De que forma agiu? Quais foram os resultados?<br />
                            </label>
                            <div class="clearfix"></div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.orientacao_resultados"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="orientacao_resultadosUm"
                                    value="1"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="orientacao_resultadosUm"
                                    >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.orientacao_resultados"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="orientacao_resultadosDois"
                                    value="2"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="orientacao_resultadosDois"
                                    >Atende parcialmente</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.orientacao_resultados"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="orientacao_resultadosTres"
                                    value="3"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="orientacao_resultadosTres"
                                    >Atende ao esperado</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.orientacao_resultados"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="orientacao_resultadosQuatro"
                                    value="4"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="orientacao_resultadosQuatro"
                                    >Supera as expectativas</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Trabalho em equipe</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label labeltext"
                                >Explicite um momento que você precisou se adaptar às diversas situações num determinado grupo. O que você fez? Como você reage
                                quando tem que interagir com outros colegas para a conclusão de uma tarefa? Cite uma situação real. Por que o trabalho em equipe
                                traz resultados mais eficazes? Cite uma situação vivida por você que o faz afirmar isso.
                            </label>
                            <div class="clearfix"></div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.trabalho_equipe"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="trabalho_equipeUm"
                                    value="1"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="trabalho_equipeUm"
                                    >Não atende ao desempenho esperado</label
                                >
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.trabalho_equipe"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="trabalho_equipeDois"
                                    value="2"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="trabalho_equipeDois">Atende parcialmente</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.trabalho_equipe"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="trabalho_equipeTres"
                                    value="3"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="trabalho_equipeTres">Atende ao esperado</label>
                            </div>
                            <div class="form-check form-check-inline cursor-pointer">
                                <input
                                    class="form-check-input"
                                    v-model="form.parecer_rh.trabalho_equipe"
                                    type="radio"
                                    :disabled="visualizar || disabledParecerRh"
                                    id="trabalho_equipeQuatro"
                                    value="4"
                                />
                                <label class="mybp-label form-check-label cursor-pointer" style="margin-top: 3px" for="trabalho_equipeQuatro"
                                    >Supera as expectativas</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if="cliente_servico" class="mybp-modal-secao">
                <legend>Avaliação psicológica</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <textarea
                                :disabled="
                                    visualizar ||
                                    disabledParecerRh ||
                                    (form.parecer_rh.individual_rh.parecer !== 'destaque' && form.parecer_rh.individual_rh.parecer !== 'favoravel')
                                "
                                class="form-control form-control-sm"
                                v-model="form.parecer_rh.individual_rh.avaliacao_psicologica"
                                cols="5"
                                rows="4"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Vaga definida</legend>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Vaga</label>
                            <autocomplete
                                :caminho="caminho_autocomplete"
                                :valido="form.vaga_id !== ''"
                                v-model="form.autocomplete_label_vaga_modal"
                                placeholder="Selecione uma vaga"
                                :id="`vaga_${hash}`"
                                :disabled="visualizar || disabledParecerRh"
                                :formsm="true"
                                @onblur="resetaCampoVagaModal"
                                @onselect="selecionaVagaModal"
                            ></autocomplete>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Cidade</label>
                            <autocomplete
                                :caminho="todos_municipios"
                                :valido="form.curriculo.municipio_id !== ''"
                                v-model="form.curriculo.autocomplete_label_municipio_modal"
                                placeholder="Selecione um municipio"
                                :id="`mun_${hash}`"
                                :disabled="visualizar || disabledParecerRh"
                                :formsm="true"
                                @onblur="resetaCampoMunicipioModal"
                                @onselect="selecionaMunicipioModal"
                            ></autocomplete>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="cliente_id === 1">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Empresa</label>
                            <autocomplete
                                :caminho="caminho_cliente_autocomplete"
                                :valido="form.cliente_id !== ''"
                                v-model="form.autocomplete_label_cliente_modal"
                                placeholder="Selecione um cliente"
                                :id="`cliente_${hash}`"
                                :disabled="visualizar || disabledParecerRh"
                                :formsm="true"
                                @onblur="resetaCampoClienteModal"
                                @onselect="selecionaClienteModal"
                            ></autocomplete>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend v-if="!cliente_servico">Parecer final RH</legend>
                <legend v-if="cliente_servico">Parecer individual</legend>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Parecer</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-parecer_final-${hash}`"
                                    :input-id="`rh-parecer_final-${hash}`"
                                    v-model="form.parecer_rh.parecer_final"
                                    :options="opcoesParecerFinal"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-parecer_final-${hash}`)"
                                    @select="onRhComboSelect(`rh-parecer_final-${hash}`)"
                                v-if="!cliente_servico"
                                ></combobox-auto-complete>
                            </div>

                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-individual_rh-parecer-${hash}`"
                                    :input-id="`rh-individual_rh-parecer-${hash}`"
                                    v-model="form.parecer_rh.individual_rh.parecer"
                                    :options="opcoesParecerClassificacao"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-individual_rh-parecer-${hash}`)"
                                    @select="onRhComboSelect(`rh-individual_rh-parecer-${hash}`)"
                                v-if="cliente_servico"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-if="!cliente_servico">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Resultado</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-parecer_final_um-${hash}`"
                                    :input-id="`rh-parecer_final_um-${hash}`"
                                    v-model="form.parecer_rh.parecer_final_um"
                                    :options="opcoesParecerFinalUm"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-parecer_final_um-${hash}`)"
                                    @select="onRhComboSelect(`rh-parecer_final_um-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Nota</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-nota-${hash}`"
                                    :input-id="`rh-nota-${hash}`"
                                    v-model="form.parecer_rh.nota"
                                    :options="opcoesNota0a10"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-nota-${hash}`)"
                                    @select="onRhComboSelect(`rh-nota-${hash}`)"
                                v-if="!cliente_servico"
                                ></combobox-auto-complete>
                            </div>

                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-individual_rh-nota-${hash}`"
                                    :input-id="`rh-individual_rh-nota-${hash}`"
                                    v-model="form.parecer_rh.individual_rh.nota"
                                    :options="opcoesNota0a10"
                                    :disabled="visualizar || disabledParecerRh"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-individual_rh-nota-${hash}`)"
                                    @select="onRhComboSelect(`rh-individual_rh-nota-${hash}`)"
                                v-if="cliente_servico"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-show="entrevistadoRh">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Entrevistado Por:</label>
                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                onblur="valida_campo_vazio(this, 3)"
                                v-if="!cliente_servico"
                                v-model="form.parecer_rh.quem_entrevistou"
                            />

                            <input
                                type="text"
                                :disabled="visualizar || disabledParecerRh"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                onblur="valida_campo_vazio(this, 3)"
                                v-if="cliente_servico"
                                v-model="form.parecer_rh.individual_rh.entrevistado_por"
                            />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Comentários</label>
                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                v-model="form.parecer_rh.comentarios"
                                cols="3"
                                v-if="!cliente_servico"
                                rows="3"
                            ></textarea>

                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabledParecerRh"
                                v-model="form.parecer_rh.individual_rh.comentario"
                                cols="3"
                                v-if="cliente_servico"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if="entrevistaGestor" class="mybp-modal-secao">
                <legend>Entrevista gestor</legend>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Parecer</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-gestor_rh-parecer-${hash}`"
                                    :input-id="`rh-gestor_rh-parecer-${hash}`"
                                    v-model="form.parecer_rh.gestor_rh.parecer"
                                    :options="opcoesParecerClassificacao"
                                    :disabled="visualizar || entrevistaGestorDisabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-gestor_rh-parecer-${hash}`)"
                                    @select="onRhComboSelect(`rh-gestor_rh-parecer-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Indicado para</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-gestor_rh-indicado_para-${hash}`"
                                    :input-id="`rh-gestor_rh-indicado_para-${hash}`"
                                    v-model="form.parecer_rh.gestor_rh.indicado_para"
                                    :options="opcoesIndicadoPara"
                                    :disabled="visualizar || entrevistaGestorDisabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-gestor_rh-indicado_para-${hash}`)"
                                    @select="onRhComboSelect(`rh-gestor_rh-indicado_para-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Nota</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-gestor_rh-nota-${hash}`"
                                    :input-id="`rh-gestor_rh-nota-${hash}`"
                                    v-model="form.parecer_rh.gestor_rh.nota"
                                    :options="opcoesNota0a10"
                                    :disabled="visualizar || entrevistaGestorDisabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-gestor_rh-nota-${hash}`)"
                                    @select="onRhComboSelect(`rh-gestor_rh-nota-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Entrevistado Por:</label>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                :disabled="visualizar"
                                v-bind="
                                    !visualizar || !disabledParecerRh ? { onkeyup: 'valida_campo_vazio(this, 1)', onblur: 'valida_campo_vazio(this, 1)' } : {}
                                "
                                v-if="!entrevistaGestorDisabled"
                                v-model="form.parecer_rh.gestor_rh.entrevistado_por"
                            />

                            <input
                                type="text"
                                class="form-control form-control-sm"
                                disabled="disabled"
                                v-if="entrevistaGestorDisabled"
                                v-model="form.parecer_rh.gestor_rh.entrevistado_por"
                            />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Comentários</label>
                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || entrevistaGestorDisabled"
                                v-model="form.parecer_rh.gestor_rh.comentario"
                                cols="3"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset v-if="entrevistaRh" class="mybp-modal-secao">
                <legend>Entrevista RH</legend>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Parecer</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-entrevista_rh-parecer-${hash}`"
                                    :input-id="`rh-entrevista_rh-parecer-${hash}`"
                                    v-model="form.parecer_rh.entrevista_rh.parecer"
                                    :options="opcoesParecerClassificacao"
                                    :disabled="visualizar || entrevistaRhDisabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-entrevista_rh-parecer-${hash}`)"
                                    @select="onRhComboSelect(`rh-entrevista_rh-parecer-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Indicado para</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-entrevista_rh-indicado_para-${hash}`"
                                    :input-id="`rh-entrevista_rh-indicado_para-${hash}`"
                                    v-model="form.parecer_rh.entrevista_rh.indicado_para"
                                    :options="opcoesIndicadoPara"
                                    :disabled="visualizar || entrevistaRhDisabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-entrevista_rh-indicado_para-${hash}`)"
                                    @select="onRhComboSelect(`rh-entrevista_rh-indicado_para-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Nota</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`rh-entrevista_rh-nota-${hash}`"
                                    :input-id="`rh-entrevista_rh-nota-${hash}`"
                                    v-model="form.parecer_rh.entrevista_rh.nota"
                                    :options="opcoesNota0a10"
                                    :disabled="visualizar || entrevistaRhDisabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="80"
                                    @opening="fecharOutrosComboboxes(`rh-entrevista_rh-nota-${hash}`)"
                                    @select="onRhComboSelect(`rh-entrevista_rh-nota-${hash}`)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Entrevistado Por:</label>
                            <input
                                type="text"
                                :disabled="visualizar"
                                autocomplete="off"
                                v-if="!entrevistaRhDisabled"
                                class="form-control form-control-sm"
                                onblur="valida_campo_vazio(this, 3)"
                                v-model="form.parecer_rh.entrevista_rh.entrevistado_por"
                            />

                            <input
                                type="text"
                                disabled
                                autocomplete="off"
                                v-if="entrevistaRhDisabled"
                                class="form-control form-control-sm"
                                v-model="form.parecer_rh.entrevista_rh.entrevistado_por"
                            />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label">Comentários</label>
                            <textarea
                                class="form-control form-control-sm"
                                :disabled="visualizar || entrevistaRhDisabled"
                                v-model="form.parecer_rh.entrevista_rh.comentario"
                                cols="3"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</template>

<script>
import DadosPessoais from './DadosPessoaisTexto'
import ComboboxAutoComplete from '../ComboboxAutoComplete.vue'
import MybpBoolCombobox from '../ui/MybpBoolCombobox.vue'
import ComboboxValidation from '../../mixins/ComboboxValidation'

export default {
    name: 'FormParecerRh',
    mixins: [ComboboxValidation],
    props: {
        form: {
            type: Object,
            required: true,
            default: () => {}
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
        axios
            .get(`${URL_ADMIN}/entrevistas/parecer_rh/${this.form.id}/editar`)
            .then((response) => {
                let data = response.data
                this.provas = data.provas
                Object.assign(this.form, data.feedback)
                this.cliente_servico = data.feedback.cliente.area_id > 1

                //Se não tiver parecer_rh
                this.form.parecer_rh = data.feedback.parecer_rh ? data.feedback.parecer_rh : _.cloneDeep(this.formDefault.parecer_rh)
                this.$emit('finalizou', {})
                this.form.parecer_rh.gestor_rh = data.feedback.parecer_rh.gestor_rh
                    ? data.feedback.parecer_rh.gestor_rh
                    : _.cloneDeep(this.formDefault.parecer_rh.gestor_rh)
                this.form.parecer_rh.entrevista_rh = data.feedback.parecer_rh.entrevista_rh
                    ? data.feedback.parecer_rh.entrevista_rh
                    : _.cloneDeep(this.formDefault.parecer_rh.entrevista_rh)

                this.preload = false
            })
            .catch((error) => {
                this.preload = false
            })

    },

    components: {
        DadosPessoais,
        ComboboxAutoComplete,
        MybpBoolCombobox
    },

    computed: {
        opcoesTipoEntrevista() {
            return [
                { value: 'Fixo', label: 'Fixo' },
                { value: 'Parada', label: 'Parada' }
            ]
        },
        opcoesDestro() {
            return [
                { value: 'Destro', label: 'Destro' },
                { value: 'Canhoto', label: 'Canhoto' }
            ]
        },
        opcoesDinamicaGrupo() {
            return [
                { value: 'Destaque', label: 'Destaque' },
                { value: 'Favorável', label: 'Favorável' },
                { value: 'Desfavorável', label: 'Desfavorável' }
            ]
        },
        opcoesCamisaMeia() {
            return ['P', 'M', 'G', 'GG', 'XG'].map((v) => ({ value: v, label: v }))
        },
        opcoesCamisaProt() {
            return [2, 3, 4, 5, 6].map((n) => ({ value: n, label: String(n) }))
        },
        opcoesCalca() {
            return Array.from({ length: 23 }, (_, i) => {
                const n = 34 + i
                return { value: n, label: String(n) }
            })
        },
        opcoesBota() {
            return Array.from({ length: 17 }, (_, i) => {
                const n = 34 + i
                return { value: n, label: String(n) }
            })
        },
        opcoesNota1a10() {
            return Array.from({ length: 10 }, (_, i) => {
                const n = i + 1
                return { value: n, label: String(n) }
            })
        },
        opcoesNota0a10() {
            return Array.from({ length: 11 }, (_, i) => ({ value: i, label: String(i) }))
        },
        opcoesDisponibilidadeHorarios() {
            return [
                'Manhã',
                'Manhã - tarde',
                'Manhã - noite',
                'Tarde',
                'Tarde - noite',
                'Noite',
                'Madrugada',
                'Qualquer horário'
            ].map((v) => ({ value: v, label: v }))
        },
        opcoesHorarioPreferencial() {
            return ['Manhã', 'Tarde', 'Noite', 'Madrugada', 'Qualquer horário'].map((v) => ({
                value: v,
                label: v
            }))
        },
        opcoesParecerFinal() {
            return ['Indicação RH', 'Parada', 'Fixo', 'Intermitente'].map((v) => ({ value: v, label: v }))
        },
        opcoesParecerClassificacao() {
            return [
                { value: 'Destaque', label: 'Destaque' },
                { value: 'Favorável', label: 'Favorável' },
                { value: 'Stand By', label: 'Stand By' },
                { value: 'Desfavoravel', label: 'Desfavorável' }
            ]
        },
        opcoesParecerFinalUm() {
            return [
                { value: 'Favorável RH', label: 'Favorável RH' },
                { value: 'Restrição', label: 'Restrição' },
                { value: 'Desfavorável', label: 'Desfavorável' }
            ]
        },
        opcoesIndicadoPara() {
            return [
                'ATENDENTE RECEPTIVO',
                'ATENDENTE ATIVO',
                'SUPERVISOR ATENDIMENTO',
                'ASSISTENTE COMERCIAL I',
                'PROMOTOR DE VENDAS'
            ].map((v) => ({ value: v, label: v }))
        }
    },

    methods: {
        fecharOutrosComboboxes() {},
        onRhComboSelect(inputId, tipo) {
            this.limparComboboxInvalido(inputId)
            if (tipo === 'tipo') {
                this.changeTipoEntrevista()
            }
        },
        validarCampos() {
            if (this.visualizar) return true

            if (!this.disabledParecerRh && !this.cliente_servico) {
                if (
                    !this.exigirCombobox(this.form.parecer_rh.tipo_entrevista, `rh-tipo_entrevista-${this.hash}`, {
                        toastMsg: 'Selecione o tipo de entrevista'
                    })
                ) {
                    return false
                }
            }

            if (this.entrevistaGestor && !this.entrevistaGestorDisabled) {
                if (
                    !this.exigirCombobox(this.form.parecer_rh.gestor_rh.parecer, `rh-gestor_rh-parecer-${this.hash}`, {
                        toastMsg: 'Selecione o parecer do gestor'
                    })
                ) {
                    return false
                }
            }

            if (this.entrevistaRh && !this.entrevistaRhDisabled) {
                if (
                    !this.exigirCombobox(this.form.parecer_rh.entrevista_rh.parecer, `rh-entrevista_rh-parecer-${this.hash}`, {
                        toastMsg: 'Selecione o parecer da entrevista RH'
                    })
                ) {
                    return false
                }
            }

            return this.validarInputsAtivosVisiveis('formParecerRh', { preservarIds: [] })
        },
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
