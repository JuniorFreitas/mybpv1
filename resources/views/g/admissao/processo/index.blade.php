@extends('layouts.sistema')
@section('title', 'Admissão')
@section('content_header')
    <h4 class="text-default">Processo</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal ref="filtroColunas" id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template #conteudo>
            <div class="row">
                <div class="col-sm-6" v-for="item in colunasTabela">
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" @click="item.checked = !item.checked;atualizaColunaTabelas()"
                               v-model="item.checked"
                               class="custom-control-input" :id="item.id">
                        <label class="custom-control-label"
                               :for="item.id">@{{item.label}}</label>
                    </div>
                </div>
            </div>
        </template>
    </modal>

    <modal ref="janelaAdmissaoAvulsa" id="janelaAdmissaoAvulsa" titulo="Admissão Avulsa" :size="95">
        <template #conteudo>
            <div class="alert alert-success text-center" v-show="formAvulsa.cadastrado">
                <h4><i class="icon fa fa-check"></i> Admissão Concluida!</h4>
            </div>

            <p class=" mt-2 text-center" v-if="formAvulsa.preload">
                <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
            </p>
            <div v-if="!formAvulsa.preload && !formAvulsa.cadastrado" class="mybp-modal-form mybp-filtros-compactos">
                <div v-if="!formAvulsa.preload">
                    <div class="alert alert-warning" v-show="formAvulsa.ex_funcionario">
                        <i class="fa fa-exclamation-triangle"></i> Ex-Funcionário
                        <span v-if="formAvulsa.pos_admissao_verificar">
                            <a :href="`${URL_ADMIN}/posadmissao?checkcpf=${formAvulsa.curriculo.cpf}`"
                               class="btn btn-sm mr-1 btn-primary"
                               target="_blank"
                               rel="noopener noreferrer">Verificar Pós Admissão</a>
                        </span>
                    </div>

                    <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>

                    <fieldset class="mybp-modal-secao">
                        <legend>Dados Pessoais</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="avulsa-cpf">CPF <span class="text-danger">*</span></label>
                                    <input id="avulsa-cpf" type="text" class="form-control form-control-sm"
                                           v-model="formAvulsa.curriculo.cpf"
                                           placeholder="000.000.000-00"
                                           ref="cpf"
                                           :disabled="disabledInput"
                                           @blur="buscaCpf"
                                           @keypress="buscaCpf"
                                           autocomplete="mybp" v-mascara:cpf>
                                </div>
                            </div>

                            <template v-if="exibiFormulario">
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-nome">Nome <span class="text-danger">*</span></label>
                                        <input id="avulsa-nome" type="text" class="form-control form-control-sm"
                                               v-model="formAvulsa.curriculo.nome"
                                               placeholder="Nome completo"
                                               autocomplete="mybp" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-email">E-mail <span class="text-danger">*</span></label>
                                        <input id="avulsa-email" type="text" class="form-control form-control-sm"
                                               v-model="formAvulsa.curriculo.email"
                                               placeholder="email@empresa.com"
                                               autocomplete="mybp" onblur="validaEmail(this)">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-nasc">Nascimento <span class="text-danger">*</span></label>
                                        <input id="avulsa-nasc" type="text" class="form-control form-control-sm validacampo"
                                               v-model="formAvulsa.curriculo.nascimento"
                                               placeholder="dd/mm/aaaa"
                                               v-mascara:data
                                               autocomplete="mybp"
                                               @keyup.prevent="valida_data_vazio($event.target,true)"
                                               @blur.prevent="valida_data_vazio($event.target,true)">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-naturalidade">Naturalidade</label>
                                        <input id="avulsa-naturalidade" type="text" class="form-control form-control-sm"
                                               onblur="valida_campo(this,2)"
                                               v-model="formAvulsa.curriculo.naturalidade">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-pcd">Cota PCD <span class="text-danger">*</span></label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-pcd"
                                                input-id="avulsa-pcd"
                                                v-model="formAvulsaPcdCombo"
                                                :options="opcoesSimNaoModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção."
                                                :max-results="5"
                                                @opening="fecharOutrosComboboxesModal('avulsa-pcd')"
                                                @select="limparComboboxInvalido('avulsa-pcd')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4" v-show="formAvulsa.curriculo.pcd">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-cid">CID <span class="text-danger">*</span></label>
                                        <input id="avulsa-cid" type="text" class="form-control form-control-sm"
                                               onblur="valida_campo_vazio(this,1)"
                                               placeholder="Informe o CID"
                                               v-model="formAvulsa.curriculo.cid">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-sexo">Sexo</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-sexo"
                                                input-id="avulsa-sexo"
                                                v-model="formAvulsa.curriculo.sexo"
                                                :options="opcoesSexoModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção."
                                                :max-results="20"
                                                @opening="fecharOutrosComboboxesModal('avulsa-sexo')"
                                                @select="limparComboboxInvalido('avulsa-sexo')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-estado-civil">Estado Civil</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-estado-civil"
                                                input-id="avulsa-estado-civil"
                                                v-model="formAvulsa.curriculo.estado_civil"
                                                :options="opcoesEstadoCivilModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção."
                                                :max-results="20"
                                                @opening="fecharOutrosComboboxesModal('avulsa-estado-civil')"
                                                @select="limparComboboxInvalido('avulsa-estado-civil')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-pai">Pai</label>
                                        <input id="avulsa-pai" type="text" class="form-control form-control-sm"
                                               v-model="formAvulsa.curriculo.filiacao_pai"
                                               placeholder="Nome do pai"
                                               autocomplete="mybp">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-mae">Mãe <span class="text-danger">*</span></label>
                                        <input id="avulsa-mae" type="text" class="form-control form-control-sm"
                                               v-model="formAvulsa.curriculo.filiacao_mae"
                                               placeholder="Nome da mãe"
                                               autocomplete="mybp" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </fieldset>

                    <template v-if="exibiFormulario">
                        <fieldset class="mybp-modal-secao">
                            <legend>Endereço</legend>
                            <endereco ref="enderecoAvulsa"
                                      :obrigatorio="false"
                                      :model="formAvulsa.curriculo"></endereco>
                        </fieldset>

                        <fieldset class="mybp-modal-secao">
                            <legend>Contato <span class="text-danger">*</span></legend>
                            <telefone ref="telefonesAvulsa"
                                      :model="formAvulsa.curriculo.telefones"
                                      :pais="false"
                                      :ramal="false"
                                      :detalhe="false"
                                      :model-delete="formAvulsa.curriculo.telefonesDelete"
                                      :qnt_min="1"></telefone>
                        </fieldset>

                        <fieldset class="mybp-modal-secao">
                            <legend>Documentos</legend>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-cnh">CNH</label>
                                        <input id="avulsa-cnh" type="text" class="form-control form-control-sm"
                                               onblur="valida_campo(this,1)"
                                               v-model="formAvulsa.curriculo.cnh">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-rg">RG</label>
                                        <input id="avulsa-rg" type="text" class="form-control form-control-sm"
                                               onblur="valida_campo(this,2)"
                                               v-model="formAvulsa.curriculo.rg">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-rg-emissao">RG — data emissão</label>
                                        <input id="avulsa-rg-emissao" type="text"
                                               class="form-control form-control-sm validacampo"
                                               placeholder="dd/mm/aaaa"
                                               v-model="formAvulsa.curriculo.rg_data_emissao" v-mascara:data
                                               @keyup.prevent="valida_data($event.target)"
                                               @blur.prevent="valida_data($event.target)">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mybp-modal-secao">
                            <legend>Formação</legend>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-formacao">Formação</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-formacao"
                                                input-id="avulsa-formacao"
                                                v-model="formAvulsa.curriculo.formacao"
                                                :options="opcoesFormacaoModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma formação."
                                                :max-results="50"
                                                @opening="fecharOutrosComboboxesModal('avulsa-formacao')"
                                                @select="limparComboboxInvalido('avulsa-formacao')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-8" v-show="formAvulsa.curriculo.formacao >= 8">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-curso">Curso <span class="text-danger">*</span></label>
                                        <input id="avulsa-curso" type="text" class="form-control form-control-sm"
                                               v-model="formAvulsa.curriculo.formacao_curso"
                                               placeholder="Ex: Administração"
                                               autocomplete="mybp" onblur="valida_campo_vazio(this,1)">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mybp-modal-secao">
                            <legend>Sobre a Vaga</legend>
                            <div class="row">
                                <div class="col-12 col-md-8">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" :for="`vaga_${hash}`">Vaga <span class="text-danger">*</span></label>
                                        <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                                      :valido="formAvulsa.feedback.vaga_id !== ''"
                                                      v-model="formAvulsa.feedback.autocomplete_label_vaga_modal"
                                                      placeholder="Digite uma vaga"
                                                      :formsm="true"
                                                      :id="`vaga_${hash}`"
                                                      @onblur="resetaCampoVagaModal"
                                                      @onselect="selecionaVagaModal"></autocomplete>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-data-prevista">Data admissão prevista</label>
                                        <input id="avulsa-data-prevista" type="text"
                                               class="form-control form-control-sm validacampo"
                                               placeholder="dd/mm/aaaa"
                                               v-mascara:data
                                               @keyup.prevent="valida_data($event.target)"
                                               @blur.prevent="valida_data($event.target)"
                                               v-model="formAvulsa.admissao.data_adm_prevista">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4" v-show="listaProjetos.length || formAvulsa.feedback.vaga_projeto_id">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-projeto">Projeto</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-projeto"
                                                input-id="avulsa-projeto"
                                                v-model="formAvulsa.feedback.vaga_projeto_id"
                                                :options="opcoesProjetoModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhum projeto."
                                                :max-results="50"
                                                @opening="fecharOutrosComboboxesModal('avulsa-projeto')"
                                                @select="limparComboboxInvalido('avulsa-projeto')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-ex-func">Ex-funcionário</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-ex-func"
                                                input-id="avulsa-ex-func"
                                                v-model="formAvulsaExFuncCombo"
                                                :options="opcoesSimNaoModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção."
                                                :max-results="5"
                                                @opening="fecharOutrosComboboxesModal('avulsa-ex-func')"
                                                @select="limparComboboxInvalido('avulsa-ex-func')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-turno">Turno 6x2</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-turno"
                                                input-id="avulsa-turno"
                                                v-model="formAvulsaTurnoCombo"
                                                :options="opcoesSimNaoModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção."
                                                :max-results="5"
                                                @opening="fecharOutrosComboboxesModal('avulsa-turno')"
                                                @select="limparComboboxInvalido('avulsa-turno')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-indicacao">Indicado <span class="text-danger">*</span></label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-indicacao"
                                                input-id="avulsa-indicacao"
                                                v-model="formAvulsaIndicacaoCombo"
                                                :options="opcoesSimNaoModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção."
                                                :max-results="5"
                                                @opening="fecharOutrosComboboxesModal('avulsa-indicacao')"
                                                @select="limparComboboxInvalido('avulsa-indicacao')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4" v-show="formAvulsa.parecer_rh.indicacao">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-indicado-por">Quem indicou <span class="text-danger">*</span></label>
                                        <input id="avulsa-indicado-por" type="text" class="form-control form-control-sm"
                                               v-model="formAvulsa.parecer_rh.indicado_por"
                                               placeholder="Nome"
                                               autocomplete="mybp" onblur="valida_campo_vazio(this,1)">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-area">Indicado para qual área?</label>
                                        <input id="avulsa-area" type="text" class="form-control form-control-sm"
                                               v-model="formAvulsa.parecer_tecnica.indicado_area"
                                               placeholder="Área"
                                               autocomplete="mybp">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mybp-modal-secao">
                            <legend>EPI</legend>
                            <div class="row">
                                <div class="col-12 col-md-3">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-calca">Calça</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-calca"
                                                input-id="avulsa-calca"
                                                v-model="formAvulsa.parecer_rh.calca"
                                                :options="opcoesCalcaModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção."
                                                :max-results="30"
                                                @opening="fecharOutrosComboboxesModal('avulsa-calca')"
                                                @select="limparComboboxInvalido('avulsa-calca')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-bota">Bota</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-bota"
                                                input-id="avulsa-bota"
                                                v-model="formAvulsa.parecer_rh.bota"
                                                :options="opcoesBotaModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção."
                                                :max-results="30"
                                                @opening="fecharOutrosComboboxesModal('avulsa-bota')"
                                                @select="limparComboboxInvalido('avulsa-bota')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-camisa-prot">Camisa proteção</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-camisa-prot"
                                                input-id="avulsa-camisa-prot"
                                                v-model="formAvulsa.parecer_rh.camisa_protecao"
                                                :options="opcoesCamisaProtModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxesModal('avulsa-camisa-prot')"
                                                @select="limparComboboxInvalido('avulsa-camisa-prot')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-camisa-meia">Camisa de meia</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="avulsa-camisa-meia"
                                                input-id="avulsa-camisa-meia"
                                                v-model="formAvulsa.parecer_rh.camisa_meia"
                                                :options="opcoesCamisaMeiaModal"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxesModal('avulsa-camisa-meia')"
                                                @select="limparComboboxInvalido('avulsa-camisa-meia')"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mybp-modal-secao">
                            <legend>Rotas</legend>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-bairro-rota">Bairro rota</label>
                                        <input id="avulsa-bairro-rota" type="text" class="form-control form-control-sm"
                                               v-model="formAvulsa.parecer_rota.bairro_rota"
                                               placeholder="Bairro"
                                               autocomplete="mybp">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-ref-rota">Ponto de referência rota</label>
                                        <input id="avulsa-ref-rota" type="text" class="form-control form-control-sm"
                                               v-model="formAvulsa.parecer_rota.ponto_referencia_rota"
                                               placeholder="Referência"
                                               autocomplete="mybp">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="avulsa-ref-res">Ponto de referência residência</label>
                                        <input id="avulsa-ref-res" type="text" class="form-control form-control-sm"
                                               v-model="formAvulsa.parecer_rota.ponto_referencia_residencia"
                                               placeholder="Referência"
                                               autocomplete="mybp">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <form-resultado-integrado
                            ref="formResultadoIntegradoAvulsa"
                            :form="formAvulsa.resultado_integrado"></form-resultado-integrado>

                        <form-admissao ref="formAdmissaoAvulsa" :form="form.admissao"></form-admissao>

                        <dependentes ref="dependentesAvulsa"
                                     :model="formAvulsa.curriculo.dependentes"
                                     :model-delete="formAvulsa.curriculo.dependentesDelete"></dependentes>

                        <dados-bancarios ref="dadosBancariosAvulsa"
                                         :model="formAvulsa.feedback.banco_conta"></dados-bancarios>

                        <fieldset class="mybp-modal-secao">
                            <legend>Foto escaneada</legend>
                            <upload :model="formAvulsa.curriculo.foto_tres"
                                    :model-delete="formAvulsa.curriculo.foto_tres_delete"
                                    url="{{ route('g.admissao.admissao.upload-anexos') }}"
                                    :apenas-imagens="true"
                                    :quantidade="1"
                                    label="Selecionar Imagem"
                                    @onProgresso="anexoUploadAndamento=true"
                                    @onFinalizado="anexoUploadAndamento=false"></upload>
                        </fieldset>
                    </template>
                </div>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!formAvulsa.cadastrado && !formAvulsa.preload"
                    @click="CadastraAvulsa">
                <i class="fa fa-save"></i> Salvar
            </button>
        </template>
    </modal>

    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :size="95">
        <template #conteudo>
            <div class="alert alert-success text-center" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i> Admissão Concluida!</h4>
            </div>

            <div class="alert alert-success text-center" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i> Alteração realizada com sucesso!</h4>
            </div>

            <preload v-if="preload"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''" class="mybp-modal-form mybp-filtros-compactos">
                <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda">
                    Campos com <span class="text-danger">*</span> são obrigatórios.
                </p>

                <fieldset class="mybp-modal-secao">
                    <legend>Dados Pessoais</legend>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-nome">Nome <span class="text-danger">*</span></label>
                                <input id="adm-nome" type="text" class="form-control form-control-sm" :disabled="visualizar"
                                       v-model="form.curriculo.nome">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-email">E-mail <span class="text-danger">*</span></label>
                                <input id="adm-email" type="text" class="form-control form-control-sm"
                                       :disabled="visualizar"
                                       onblur="validaEmailVazio(this)"
                                       v-model="form.curriculo.email">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-nasc">Nascimento <span class="text-danger">*</span></label>
                                <input id="adm-nasc" type="text" class="form-control form-control-sm validacampo"
                                       :disabled="visualizar"
                                       v-model="form.curriculo.nascimento"
                                       placeholder="dd/mm/aaaa"
                                       v-mascara:data
                                       autocomplete="mybp" @keyup.prevent="valida_data_vazio($event.target,true)"
                                       @blur.prevent="valida_data_vazio($event.target,true)">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-naturalidade">Naturalidade</label>
                                <input id="adm-naturalidade" type="text" class="form-control form-control-sm" onblur="valida_campo(this,2)"
                                       v-model="form.curriculo.naturalidade" :disabled="visualizar">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-sexo">Sexo</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-sexo"
                                        input-id="adm-sexo"
                                        v-model="form.curriculo.sexo"
                                        :options="opcoesSexoModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-sexo')"
                                        @select="limparComboboxInvalido('adm-sexo')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-estado-civil">Estado Civil</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-estado-civil"
                                        input-id="adm-estado-civil"
                                        v-model="form.curriculo.estado_civil"
                                        :options="opcoesEstadoCivilModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-estado-civil')"
                                        @select="limparComboboxInvalido('adm-estado-civil')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-pcd">Cota PCD <span class="text-danger">*</span></label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-pcd"
                                        input-id="adm-pcd"
                                        v-model="formCurriculoPcdCombo"
                                        :options="opcoesSimNaoModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-pcd')"
                                        @select="limparComboboxInvalido('adm-pcd')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-show="form.curriculo.pcd">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-cid">CID <span class="text-danger">*</span></label>
                                <input id="adm-cid" type="text" class="form-control form-control-sm" onblur="valida_campo_vazio(this,1)"
                                       placeholder="Informe o CID" v-model="form.curriculo.cid">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-pai">Pai</label>
                                <input id="adm-pai" type="text" class="form-control form-control-sm"
                                       v-model="form.curriculo.filiacao_pai" :disabled="visualizar"
                                       placeholder="Nome do pai"
                                       autocomplete="mybp">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-mae">Mãe <span class="text-danger">*</span></label>
                                <input id="adm-mae" type="text" class="form-control form-control-sm"
                                       v-model="form.curriculo.filiacao_mae" :disabled="visualizar"
                                       placeholder="Nome da mãe"
                                       autocomplete="mybp" onblur="valida_campo_vazio(this,3)">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Endereço</legend>
                    <endereco ref="enderecoModal"
                              :obrigatorio="false"
                              :disabled="visualizar"
                              :model="form.curriculo"></endereco>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Contato <span class="text-danger">*</span></legend>
                    <telefone ref="telefonesModal"
                              :model="form.curriculo.telefones"
                              :pais="false"
                              :ramal="false"
                              :detalhe="false"
                              :model-delete="form.curriculo.telefonesDelete"
                              :qnt_min="1"
                              :disabled="visualizar"></telefone>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Documentos</legend>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-cnh">CNH</label>
                                <input id="adm-cnh" type="text" class="form-control form-control-sm"
                                       :disabled="visualizar"
                                       :value="form.parecer_rh.cnh ? form.parecer_rh.cnh_tipo : 'Não possui'">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-rg">RG</label>
                                <input id="adm-rg" type="text" class="form-control form-control-sm"
                                       onblur="valida_campo(this,2)"
                                       v-model="form.curriculo.rg" :disabled="visualizar">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-rg-emissao">RG — data emissão</label>
                                <input id="adm-rg-emissao" type="text"
                                       class="form-control form-control-sm validacampo"
                                       placeholder="dd/mm/aaaa"
                                       v-model="form.curriculo.rg_data_emissao" v-mascara:data
                                       @keyup.prevent="valida_data($event.target)"
                                       @blur.prevent="valida_data($event.target)"
                                       :disabled="visualizar">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Formação</legend>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-formacao">Formação <span class="text-danger">*</span></label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-formacao"
                                        input-id="adm-formacao"
                                        v-model="form.curriculo.formacao"
                                        :options="opcoesFormacaoModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-formacao')"
                                        @select="limparComboboxInvalido('adm-formacao')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-8" v-show="form.curriculo.formacao >= 8">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-curso">Curso <span class="text-danger">*</span></label>
                                <input id="adm-curso" type="text" class="form-control form-control-sm"
                                       :disabled="visualizar"
                                       v-model="form.curriculo.formacao_curso"
                                       placeholder="Ex: Administração"
                                       autocomplete="mybp" onblur="valida_campo_vazio(this,1)">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>EPI</legend>
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-calca">Calça</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-calca"
                                        input-id="adm-calca"
                                        v-model="form.parecer_rh.calca"
                                        :options="opcoesCalcaModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-calca')"
                                        @select="limparComboboxInvalido('adm-calca')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-bota">Bota</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-bota"
                                        input-id="adm-bota"
                                        v-model="form.parecer_rh.bota"
                                        :options="opcoesBotaModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-bota')"
                                        @select="limparComboboxInvalido('adm-bota')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-camisa-prot">Camisa proteção</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-camisa-prot"
                                        input-id="adm-camisa-prot"
                                        v-model="form.parecer_rh.camisa_protecao"
                                        :options="opcoesCamisaProtModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-camisa-prot')"
                                        @select="limparComboboxInvalido('adm-camisa-prot')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-camisa-meia">Camisa de meia</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-camisa-meia"
                                        input-id="adm-camisa-meia"
                                        v-model="form.parecer_rh.camisa_meia"
                                        :options="opcoesCamisaMeiaModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-camisa-meia')"
                                        @select="limparComboboxInvalido('adm-camisa-meia')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Técnica</legend>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-exp-rigger">Experiência com cargas rigger</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-exp-rigger"
                                        input-id="adm-exp-rigger"
                                        v-model="form.parecer_tecnica.experiencia_cargas_rigger"
                                        :options="opcoesTecnicaModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-exp-rigger')"
                                        @select="limparComboboxInvalido('adm-exp-rigger')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-plat-movel">Opera plataforma móvel</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-plat-movel"
                                        input-id="adm-plat-movel"
                                        v-model="form.parecer_tecnica.opera_plat_movel"
                                        :options="opcoesTecnicaModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-plat-movel')"
                                        @select="limparComboboxInvalido('adm-plat-movel')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-ponte">Opera ponte rolante</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-ponte"
                                        input-id="adm-ponte"
                                        v-model="form.parecer_tecnica.opera_plat_ponte"
                                        :options="opcoesTecnicaPonteModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-ponte')"
                                        @select="limparComboboxInvalido('adm-ponte')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Sobre a Vaga</legend>
                    <div class="row">
                        <div class="col-12 col-md-8">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" :for="`vaga_edit_${hash}`">Vaga <span class="text-danger">*</span></label>
                                <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                              :valido="form.vagas_abertas_id !== ''"
                                              v-model="form.autocomplete_label_vaga_modal"
                                              placeholder="Digite uma vaga"
                                              :disabled="visualizar"
                                              :readonly="visualizar"
                                              :formsm="true"
                                              :id="`vaga_edit_${hash}`"
                                              @onblur="resetaCampoVagaModalEditar"
                                              @onselect="selecionaVagaModalEditar"></autocomplete>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-data-prevista">Data admissão prevista</label>
                                <input id="adm-data-prevista" type="text"
                                       class="form-control form-control-sm validacampo"
                                       placeholder="dd/mm/aaaa"
                                       v-mascara:data
                                       @keyup.prevent="valida_data($event.target)"
                                       @blur.prevent="valida_data($event.target)"
                                       :disabled="visualizar"
                                       v-model="form.admissao.data_adm_prevista">
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-show="listaProjetos.length || form.vaga_projeto_id">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-projeto">Projeto</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-projeto"
                                        input-id="adm-projeto"
                                        v-model="form.vaga_projeto_id"
                                        :options="opcoesProjetoModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-projeto')"
                                        @select="limparComboboxInvalido('adm-projeto')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-ex-func">Ex-funcionário</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-ex-func"
                                        input-id="adm-ex-func"
                                        v-model="formParecerExFuncionarioCombo"
                                        :options="opcoesSimNaoModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-ex-func')"
                                        @select="limparComboboxInvalido('adm-ex-func')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-turno">Turno 6x2</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-turno"
                                        input-id="adm-turno"
                                        v-model="formParecerTurnoCombo"
                                        :options="opcoesSimNaoModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-turno')"
                                        @select="limparComboboxInvalido('adm-turno')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-indicado">Indicado</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="adm-indicado"
                                        input-id="adm-indicado"
                                        v-model="formParecerIndicacaoCombo"
                                        :options="opcoesSimNaoModal"
                                        :disabled="visualizar"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="80"
                                        @opening="fecharOutrosComboboxesModal('adm-indicado')"
                                        @select="limparComboboxInvalido('adm-indicado')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-show="form.parecer_rh.indicacao">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-indicado-por">Quem indicou <span class="text-danger">*</span></label>
                                <input id="adm-indicado-por" type="text" class="form-control form-control-sm"
                                       v-model="form.parecer_rh.indicado_por" :disabled="visualizar"
                                       placeholder="Nome"
                                       autocomplete="mybp" onblur="valida_campo_vazio(this,1)">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao" v-if="form.parecer_rota">
                    <legend>Rota</legend>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-bairro-rota">Bairro rota</label>
                                <input id="adm-bairro-rota" type="text" class="form-control form-control-sm" :disabled="visualizar"
                                       :value="form.parecer_rota.bairro_rota">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-ref-rota">Ponto de referência rota</label>
                                <input id="adm-ref-rota" type="text" class="form-control form-control-sm" :disabled="visualizar"
                                       :value="form.parecer_rota.ponto_referencia_rota">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-ref-res">Ponto de referência residência</label>
                                <input id="adm-ref-res" type="text" class="form-control form-control-sm" :disabled="visualizar"
                                       :value="form.parecer_rota.ponto_referencia_residencia">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao" v-if="form.parecer_teste">
                    <legend>Testes</legend>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-teste">Teste aplicado</label>
                                <input id="adm-teste" type="text" class="form-control form-control-sm" :disabled="visualizar"
                                       :value="form.parecer_teste.qual_teste">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="adm-resultado-teste">Resultado teste prático</label>
                                <input id="adm-resultado-teste" type="text" class="form-control form-control-sm" :disabled="visualizar"
                                       :value="form.parecer_teste.parecer_final_teste">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <form-resultado-integrado
                    ref="formResultadoIntegradoModal"
                    :form="form.resultado_integrado" :disabled="visualizar || !editando"
                    :visualizar="visualizar"></form-resultado-integrado>

                <form-admissao ref="formAdmissaoModal"
                               :form="form.admissao"
                               :visualizar="visualizar"
                               :disabled="visualizar || !editando"></form-admissao>

                <dependentes ref="dependentesModal"
                             :model="form.curriculo.dependentes"
                             :visualizar='visualizar'
                             :model-delete="form.curriculo.dependentesDelete"></dependentes>

                <dados-bancarios ref="dadosBancariosModal"
                                 :model="form.banco_conta"
                                 :visualizar='visualizar'></dados-bancarios>

                <fieldset class="mybp-modal-secao">
                    <legend>Foto escaneada</legend>
                    <upload :model='form.curriculo.foto_tres'
                            :model-delete='form.curriculo.foto_tres_delete' :leitura='visualizar'
                            url="{{ route('g.admissao.admissao.upload-anexos') }}"
                            :apenas-imagens='true'
                            :quantidade='1'
                            :disabled="visualizar"
                            label='Selecionar Imagem'
                            @onProgresso='anexoUploadAndamento=true'
                            @onFinalizado='anexoUploadAndamento=false'></upload>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="!atualizado  && !preload"
                        @click.prevent="alterar">
                    <i class="fa fa-edit"></i> Salvar
                </button>
            </div>

            {{--            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !cadastrado" @click="encaminhar()">--}}
            {{--                Admitir--}}
            {{--            </button>--}}
        </template>
    </modal>

    <modal ref="janelaAdmissaoMassa" id="janelaAdmissaoMassa" titulo="Admissão em massa" :size="95">
        <template #conteudo>
            <preload v-if="form_massa.preload"></preload>
            <div v-if="!form_massa.preload" class="mybp-modal-form mybp-filtros-compactos">

                <fieldset class="mybp-modal-secao">
                    <legend>Informações</legend>
                    <div class="row">

                                                <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="massa-tipo">Tipo de admissão</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="massa-tipo"
                                        input-id="massa-tipo"
                                        v-model="form_massa.tipo_admissao"
                                        :options="opcoesMassaTipoAdmissao"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="40"
                                        @opening="fecharOutrosComboboxesModal('massa-tipo')"
                                        @select="limparComboboxInvalido('massa-tipo')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                                                <div class="col-12 col-md-4" v-if="form_massa.tipo_admissao === 'FIXO'">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="massa-prazo">Prazo de experiência <span class="text-danger">*</span></label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="massa-prazo"
                                        input-id="massa-prazo"
                                        v-model="form_massa.prazo_experiencia"
                                        :options="opcoesMassaPrazo"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="40"
                                        @opening="fecharOutrosComboboxesModal('massa-prazo')"
                                        @select="limparComboboxInvalido('massa-prazo')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6"
                             v-if="form_massa.tipo_admissao === 'TEMPORARIO' || form_massa.tipo_admissao === 'DETERMINADO'">
                            <div class="form-group mybp-filtro-campo">
                                <datepicker label="Data de encerramento"
                                            v-model="form_massa.data_encerramento"></datepicker>
                            </div>
                        </div>

                                                <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="massa-doc-portaria">Documento Portaria</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="massa-doc-portaria"
                                        input-id="massa-doc-portaria"
                                        v-model="form_massa.documento_portaria"
                                        :options="opcoesMassaStatusSimples"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="40"
                                        @opening="fecharOutrosComboboxesModal('massa-doc-portaria')"
                                        @select="limparComboboxInvalido('massa-doc-portaria')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Data do ASO</label>
                                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                       v-model="form_massa.ultimo_aso.data_realizacao" v-mascara:data
                                       @keyup.prevent="valida_data($event.target)"
                                       @blur.prevent="valida_data($event.target)">
                            </div>
                        </div>

                                                <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="massa-carteira">Status Carteira / Etiqueta</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="massa-carteira"
                                        input-id="massa-carteira"
                                        v-model="form_massa.status_carteira_treinamento"
                                        :options="opcoesMassaCarteira"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="40"
                                        @opening="fecharOutrosComboboxesModal('massa-carteira')"
                                        @select="limparComboboxInvalido('massa-carteira')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                                                <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="massa-segmento">Padrão de treinamento</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="massa-segmento"
                                        input-id="massa-segmento"
                                        v-model="form_massa.segmento_treinamento_id"
                                        :options="opcoesMassaSegmento"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="40"
                                        @opening="fecharOutrosComboboxesModal('massa-segmento')"
                                        @select="limparComboboxInvalido('massa-segmento')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                                                <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="massa-status">Status <span class="text-danger">*</span></label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="massa-status"
                                        input-id="massa-status"
                                        v-model="form_massa.status"
                                        :options="opcoesMassaStatus"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="40"
                                        @opening="fecharOutrosComboboxesModal('massa-status')"
                                        @select="limparComboboxInvalido('massa-status')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Data da Admissão</label>
                                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                       v-model="form_massa.data_admissao" v-mascara:data
                                       @keyup.prevent="valida_data($event.target)"
                                       @blur.prevent="valida_data($event.target)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Data da Entrega na área</label>
                                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                       v-model="form_massa.data_entrega_area" v-mascara:data
                                       @keyup.prevent="valida_data($event.target)"
                                       @blur.prevent="valida_data($event.target)">
                            </div>
                        </div>


                                                <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="massa-biometria">Biometria</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="massa-biometria"
                                        input-id="massa-biometria"
                                        v-model="formMassaBiometriaCombo"
                                        :options="opcoesSimNaoModal"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="40"
                                        @opening="fecharOutrosComboboxesModal('massa-biometria')"
                                        @select="limparComboboxInvalido('massa-biometria')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <div>
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="!form_massa.preload"
                        @click.prevent="CadastraMassa">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </div>
        </template>
    </modal>

    <modal ref="janelaDemitir" id="janelaDemitir" titulo="Demissão Avulsa" size="g">
        <template #conteudo>
            <preload v-if="modeldemissao.preload"></preload>
            <div v-if="!modeldemissao.preload" class="mybp-modal-form mybp-filtros-compactos">
                <fieldset class="mybp-modal-secao" style="margin-top: 0">
                    <legend>Colaborador</legend>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Nome</label>
                                <input type="text" class="form-control form-control-sm" :value="modeldemissao.form.nome" disabled>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">CPF</label>
                                <input type="text" class="form-control form-control-sm" :value="modeldemissao.form.cpf" disabled>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Cargo</label>
                                <input type="text" class="form-control form-control-sm" :value="modeldemissao.form.cargo" disabled>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Função</label>
                                <input type="text" class="form-control form-control-sm" :value="modeldemissao.form.funcao" disabled>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Data de admissão</label>
                                <input type="text" class="form-control form-control-sm" :value="modeldemissao.form.data_admissao" disabled>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="mybp-modal-secao">
                    <legend>Demissão</legend>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                                <label class="mybp-label">Data demissão <span class="text-danger">*</span></label>
                                <datepicker
                                    id="demitir-data"
                                    label=""
                                    class="corrigiDatepicker"
                                    formsm
                                    :disabled="modeldemissao.form.status === 'DEMITIDO'"
                                    v-model="modeldemissao.form.data_desmobilizacao"
                                    @onselect="limparCampoDataInvalido('demitir-data')"
                                ></datepicker>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <div v-if="modeldemissao.form.status !== 'DEMITIDO'">
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="!modeldemissao.preload"
                        @click.prevent="demiteColaborador">
                    <i class="fa fa-save"></i> Demitir
                </button>
            </div>
        </template>
    </modal>

    <filtro-listagem
        class="mt-2 mybp-filtros-compactos"
        :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
        :desabilitado="controle.carregando"
        @submit="atualizar"
        @limpar="limparFiltros"
    >
        <template #filtros>
            <date-range-filter
                :key="'filtro-periodo'"
                v-model:enabled="controle.dados.filtroPeriodo"
                v-model:start-date="controle.dados.dataInicio"
                v-model:end-date="controle.dados.dataFim"
                :disabled="!!controle.carregando"
                :id-suffix="'periodo-' + hash"
                label="Por período"
                wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                @change="onPeriodoChange"
            ></date-range-filter>

            <date-range-filter
                :key="'filtro-aso'"
                v-model:enabled="controle.dados.filtroAso"
                v-model:start-date="controle.dados.dataInicioAso"
                v-model:end-date="controle.dados.dataFimAso"
                :disabled="!!controle.carregando"
                :id-suffix="'aso-' + hash"
                label="Data do ASO"
                wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                @change="onPeriodoChange"
            ></date-range-filter>

            <date-range-filter
                :key="'filtro-admissao'"
                v-model:enabled="controle.dados.filtroDataAdmissao"
                v-model:start-date="controle.dados.dataInicioAdmissao"
                v-model:end-date="controle.dados.dataFimAdmissao"
                :disabled="!!controle.carregando"
                :id-suffix="'admissao-' + hash"
                label="Data da Admissão"
                wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                @change="onPeriodoChange"
            ></date-range-filter>

            <div class="col-12 col-md-4" v-if="lista_ccs && AUTENTICADO.temFilial">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="admissao-processo-cnpj">Lotação</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroCnpj"
                            instance-id="admissao-processo-cnpj"
                            input-id="admissao-processo-cnpj"
                            v-model="controle.dados.campoCnpj"
                            :options="filtroCnpjOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Todas as lotações"
                            empty-message="Nenhuma lotação encontrada."
                            :max-results="50"
                            @opening="fecharOutrosComboboxes('admissao-processo-cnpj')"
                            @select="onSelectCnpj"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4" v-if="lista_ccs">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="admissao-processo-cc">Centro de custo</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroCentroCusto"
                            instance-id="admissao-processo-cc"
                            input-id="admissao-processo-cc"
                            v-model="controle.dados.campoCentroCusto"
                            :options="filtroCentroCustoOpcoes"
                            :disabled="controle.carregando || !filtroCentroCustoOpcoes.length"
                            placeholder-blur="Todos os centros de custo"
                            empty-message="Nenhum centro de custo encontrado."
                            :max-results="200"
                            @opening="fecharOutrosComboboxes('admissao-processo-cc')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="admissao-processo-busca">
                        Colaborador / CPF
                        <span v-if="buscaUnificadaEhCpf" class="admissao-processo-filtro-hint">CPF</span>
                    </label>
                    <input
                        id="admissao-processo-busca"
                        type="text"
                        placeholder="Nome ou CPF"
                        autocomplete="off"
                        inputmode="search"
                        class="form-control form-control-sm"
                        :disabled="controle.carregando"
                        :value="campoBuscaUnificada"
                        @input="onInputBuscaUnificada"
                    />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label">Cargo</label>
                    <autocomplete
                        :caminho="controle.dados.caminho_autocomplete"
                        :valido="controle.dados.campoVaga !== ''"
                        :formsm="true"
                        v-model="controle.dados.autocomplete_label"
                        :disabled="controle.carregando"
                        placeholder="Por cargo"
                        @onblur="resetaCampo"
                        @onselect="selecionaVaga"
                    ></autocomplete>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="admissao-processo-uf">Estado</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroUf"
                            instance-id="admissao-processo-uf"
                            input-id="admissao-processo-uf"
                            v-model="controle.dados.campoUf"
                            :options="filtroUfOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Todos os estados"
                            empty-message="Nenhum estado encontrado."
                            :max-results="30"
                            @opening="fecharOutrosComboboxes('admissao-processo-uf')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="admissao-processo-status">Status admissão</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroStatusAdmissao"
                            instance-id="admissao-processo-status"
                            input-id="admissao-processo-status"
                            v-model="controle.dados.campoStatusAdmissao"
                            :options="filtroStatusAdmissaoOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Todos os status"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="20"
                            @opening="fecharOutrosComboboxes('admissao-processo-status')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="admissao-processo-tipo">Tipo admissão</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroTipoAdmissao"
                            instance-id="admissao-processo-tipo"
                            input-id="admissao-processo-tipo"
                            v-model="controle.dados.campoTipoAdmissao"
                            :options="filtroTipoAdmissaoOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Todos os tipos"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="20"
                            @opening="fecharOutrosComboboxes('admissao-processo-tipo')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4" v-if="permissoes.filtrar_demitido">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="admissao-processo-demitido">Por demitido</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroDemitido"
                            instance-id="admissao-processo-demitido"
                            input-id="admissao-processo-demitido"
                            v-model="campoDemitidoCombo"
                            :options="filtroDemitidoOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Não"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="5"
                            @opening="fecharOutrosComboboxes('admissao-processo-demitido')"
                            @select="onSelectDemitido"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="admissao-processo-pages">Exibir</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroPages"
                            instance-id="admissao-processo-pages"
                            input-id="admissao-processo-pages"
                            v-model="campoPagesCombo"
                            :options="filtroPagesOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="20"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('admissao-processo-pages')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>
        </template>

        <template #acoes>
            <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                Buscar
            </button>
            @can('admissao_processo_insert')
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    :disabled="controle.carregando"
                    @click="formCadastraAvulsa(); $refs.janelaAdmissaoAvulsa?.abrirModal()"
                >
                    <i class="fas fa-plus"></i> Admissão avulsa
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    :disabled="selecionados.length === 0"
                    @click="selecionados = []"
                >
                    <i class="fa fa-times"></i> Limpar seleção
                    <span class="badge badge-light ml-1" v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click.prevent="exportaExcel()"
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && lista.length === 0 && selecionados.length === 0)"
                >
                    <i class="fas fa-file-excel"></i> Exportar Excel
                    <span class="badge badge-light ml-1" v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                </button>
            @endcan
        </template>
    </filtro-listagem>

    <preload v-if="controle.carregando" class="text-center"></preload>

    <div id="conteudo">
        <div class="empty-state" v-show="!controle.carregando && lista.length===0">
            <div class="empty-state-icon"><i class="fas fa-user-clock"></i></div>
            <h3 class="empty-state-title">Nenhum registro encontrado</h3>
            <p class="empty-state-text">Ajuste os filtros ou aguarde novos candidatos no processo de admissão.</p>
        </div>

        <div class="cards-toolbar" v-show="!controle.carregando && lista.length > 0">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="selTodosProcesso"
                       :checked="tudoMarcado"
                       :disabled="comAdm.length === 0"
                       :style="comAdm.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                       @click="selecionaTodos">
                <label class="custom-control-label" for="selTodosProcesso" style="width: max-content">Selecionar todos com admissão</label>
            </div>
            <!-- <span class="cards-toolbar-count">@{{ lista.length }} registro(s)</span> -->
            <button class="btn btn-sm mr-1 btn-outline-secondary ml-auto" @click="$refs.filtroColunas?.abrirModal()" v-tippy content="Mostrar e Ocultar Colunas">
                <i class="bx bxs-filter-alt"></i> Colunas
            </button>
        </div>

        <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
            <div class="solicitacao-card" v-for="item in lista" :key="item.id"
                 :class="{ 'card-status-em-processo': !item.admissao, 'card-status-admitido': item.admissao && (item.admissao.status === 'ADMITIDO' || item.admissao.status === 'Admitido'), 'card-status-demitido': item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido') }">
                <div class="card-header-row">
                    <div class="card-left">
                        <label class="checkbox-inline mb-0" v-if="item.admissao">
                            <input type="checkbox" class="custom-checkbox" v-model="selecionados" :value="item.id" :id="'chk_' + item.id">
                        </label>
                        <input type="checkbox" class="custom-checkbox" v-else disabled title="Sem cadastro em Admissão">
                        <span class="badge-id">#@{{ item.id }}</span>
                        <div class="colaborador-principal">
                            <i class="fas fa-user-circle mr-1"></i>
                            <strong>@{{ item.curriculo.nome }}</strong>
                        </div>
                        <span class="status-badge" :class="{
                            'status-admitido': item.admissao && (item.admissao.status === 'ADMITIDO' || item.admissao.status === 'Admitido'),
                            'status-demitido': item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido'),
                            'status-processo': item.admissao && item.admissao.status && item.admissao.status !== 'ADMITIDO' && item.admissao.status !== 'DEMITIDO' && item.admissao.status !== 'Admitido' && item.admissao.status !== 'Demitido',
                            'status-pendente': !item.admissao
                        }">
                            @{{ item.admissao ? item.admissao.status : 'Em processo' }}
                        </span>
                    </div>
                    <div class="card-right">
                        <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                            <a
                                class="btn-actions-compact"
                                href="#"
                                role="button"
                                :id="`dropdownProcesso_${item.id}`"
                                aria-haspopup="true"
                                :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
                                @click.prevent.stop="toggleDropdown(item.id)"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div
                                class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                :class="{ show: isDropdownOpen(item.id) }"
                                :aria-labelledby="`dropdownProcesso_${item.id}`"
                                @click="fecharDropdown"
                            >
                                <a class="dropdown-item" href="javascript://" title="Admitir" @click.prevent="formEntrevistar(item.id); visualizar = false; $refs.janelaCadastrar?.abrirModal()" v-if="!filtrarDemitidos"><i class="fas fa-user-plus mr-2 text-success"></i> Admitir</a>
                                <a class="dropdown-item" href="javascript://" title="Demitir" @click.prevent="formDemitir(item); visualizar = false; $refs.janelaDemitir?.abrirModal()" v-if="permissoes.privilegio_processo_demitir && !filtrarDemitidos"><i class="fas fa-user-minus mr-2 text-danger"></i> Demitir</a>
                                <a class="dropdown-item" href="javascript://" title="Visualizar" @click.prevent="formEntrevistar(item.id); visualizar = true; $refs.janelaCadastrar?.abrirModal()"><i class="fas fa-eye mr-2 text-info"></i> Visualizar</a>
                                <div class="dropdown-divider" v-if="item.admissao"></div>
                                <a class="dropdown-item" v-if="item.admissao" :href="`${item.fc_token}/pdf`" title="Gerar PDFs" target="_blank"><i class="fas fa-file-pdf mr-2 text-danger"></i> Gerar PDF</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-details-row card-details-main">
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'tbl_cpf')?.checked">
                        <i class="fas fa-id-card"></i>
                        <span class="detail-label">CPF</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !item.curriculo.cpf }">@{{ item.curriculo.cpf || 'Não informado' }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-building"></i>
                        <span class="detail-label">Centro</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !(item.admissao && item.admissao.emp_centro_custo) }">@{{ (item.admissao && item.admissao.emp_centro_custo) ? item.admissao.emp_centro_custo : 'Não informado' }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-briefcase"></i>
                        <span class="detail-label">Cargo</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !item.vaga_aberta_municipio }">@{{ item.vaga_aberta_municipio || 'Não informado' }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'tbl_contato')?.checked">
                        <i class="fas fa-phone"></i>
                        <span class="detail-label">Contato</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !item.curriculo.tel_principal || !item.curriculo.tel_principal.numero }">@{{ item.curriculo.tel_principal && item.curriculo.tel_principal.numero ? item.curriculo.tel_principal.numero : 'Não informado' }}</span>
                    </div>
                    <div class="detail-item" v-if="AUTENTICADO.temFilial && item.admissao && item.admissao.emp_cnpj">
                        <i class="fas fa-building"></i>
                        <span class="detail-label">Unidade</span>
                        <span class="detail-value">@{{ item.admissao.emp_nome_fantasia }} (@{{ item.admissao.emp_tipo }})</span>
                    </div>
                </div>

                <div class="card-details-row card-details-docs" v-if="colunasTabela.find(c => c.id === 'pcd').checked || colunasTabela.find(c => c.id === 'enc_documento').checked || colunasTabela.find(c => c.id === 'enc_exame').checked || colunasTabela.find(c => c.id === 'enc_treinamento').checked || colunasTabela.find(c => c.id === 'resp_encaminhamento').checked || colunasTabela.find(c => c.id === 'cracha').checked || colunasTabela.find(c => c.id === 'foto_3x4').checked">
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'pcd').checked">
                        <span class="detail-check" :class="item.curriculo.pcd ? 'detail-check-yes' : 'detail-check-no'"><i :class="item.curriculo.pcd ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">PCD</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'enc_documento').checked">
                        <span class="detail-check" :class="(item.resultado_integrado && item.resultado_integrado.documentos_entregue) ? 'detail-check-yes' : 'detail-check-no'"><i :class="(item.resultado_integrado && item.resultado_integrado.documentos_entregue) ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">Enc. Doc</span>
                        <span class="detail-value detail-value-small" v-if="item.resultado_integrado && item.resultado_integrado.documentos_entregue_data">@{{ item.resultado_integrado.documentos_entregue_data }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'enc_exame').checked">
                        <span class="detail-check" :class="(item.resultado_integrado && item.resultado_integrado.encaminhado_exame) ? 'detail-check-yes' : 'detail-check-no'"><i :class="(item.resultado_integrado && item.resultado_integrado.encaminhado_exame) ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">Enc. Exame</span>
                        <span class="detail-value detail-value-small" v-if="item.resultado_integrado && item.resultado_integrado.encaminhado_exame_data">@{{ item.resultado_integrado.encaminhado_exame_data }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'enc_treinamento').checked">
                        <span class="detail-check" :class="(item.resultado_integrado && item.resultado_integrado.encaminhado_treinamento) ? 'detail-check-yes' : 'detail-check-no'"><i :class="(item.resultado_integrado && item.resultado_integrado.encaminhado_treinamento) ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">Enc. Trein.</span>
                        <span class="detail-value detail-value-small" v-if="item.resultado_integrado && item.resultado_integrado.encaminhado_treinamento_data">@{{ item.resultado_integrado.encaminhado_treinamento_data }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'resp_encaminhamento').checked">
                        <i class="fas fa-user-tie"></i>
                        <span class="detail-label">Resp. Enc.</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !item.resultado_integrado || !item.resultado_integrado.responsavel_envio }">@{{ item.resultado_integrado && item.resultado_integrado.responsavel_envio ? item.resultado_integrado.responsavel_envio : '—' }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'cracha').checked">
                        <span class="detail-check" :class="(item.admissao && item.admissao.numero_cracha) ? 'detail-check-yes' : 'detail-check-no'"><i :class="(item.admissao && item.admissao.numero_cracha) ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">Crachá</span>
                        <span class="detail-value detail-value-small" v-if="item.admissao && item.admissao.numero_cracha">@{{ item.admissao.numero_cracha }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'foto_3x4').checked">
                        <span class="detail-check" :class="(item.curriculo.foto_tres && item.curriculo.foto_tres.length > 0) ? 'detail-check-yes' : 'detail-check-no'"><i :class="(item.curriculo.foto_tres && item.curriculo.foto_tres.length > 0) ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">Foto 3x4</span>
                    </div>
                </div>

                <div class="card-details-row card-details-fixas">
                    <div class="detail-item">
                        <i class="fas fa-notes-medical"></i>
                        <span class="detail-label">Data ASO</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !getDataAso(item) }">@{{ getDataAso(item) || 'Não informado' }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar-check"></i>
                        <span class="detail-label">Data Admissão</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !item.admissao || !item.admissao.data_admissao }">@{{ item.admissao && item.admissao.data_admissao ? item.admissao.data_admissao : 'Não informado' }}</span>
                    </div>
                    <div class="detail-item" v-if="item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido')">
                        <i class="fas fa-calendar-times"></i>
                        <span class="detail-label">Data Demissão</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !getDataDemissao(item) }">@{{ getDataDemissao(item) || 'Não informado' }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-file-contract"></i>
                        <span class="detail-label">Tipo Admissão</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !getTipoAdmissao(item) }">@{{ getTipoAdmissao(item) || 'Não informado' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.admissao.admissao.atualizar')}}"
                            :por-pagina="controle.dados.pages"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    @php
        $escolaridadesModal = \App\Models\Escolaridade::query()
            ->orderBy('tipo')
            ->get(['id', 'tipo'])
            ->map(fn ($item) => ['value' => $item->id, 'label' => $item->tipo])
            ->values();
    @endphp
    <script>
        window.__MYBP_ESCOLARIDADES = @json($escolaridadesModal);
    </script>
    <script src="{{mix('js/g/admissao/processo/app.js')}}"></script>
@endpush
@push('css')
    <style>
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
            border-radius: 12px;
            border: 1px dashed #dee2e6;
        }
        .empty-state-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #6c757d;
        }
        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.25rem;
        }
        .empty-state-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin: 0;
        }

        .admissao-processo-filtro-hint {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            margin-left: 0.35rem;
            padding: 0.05rem 0.35rem;
            border-radius: 999px;
            background: rgba(23, 66, 87, 0.12);
            color: var(--primary, #174257);
            font-size: 0.68rem;
            font-weight: 700;
        }

        /* Toolbar */
        .cards-toolbar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .cards-toolbar .custom-control-label { font-size: 0.875rem; color: #495057; }
        .cards-toolbar-count {
            font-size: 0.813rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Cards list */
        .cards-lista { display: flex; flex-direction: column; gap: 1rem; }
        .solicitacao-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 0;
            transition: all 0.25s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #6c757d;
            overflow: hidden;
        }
        .solicitacao-card.card-status-em-processo { border-left-color: #17a2b8; }
        .solicitacao-card.card-status-admitido { border-left-color: #28a745; }
        .solicitacao-card.card-status-demitido { border-left-color: #dc3545; }
        .solicitacao-card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            border-color: #ced4da;
            transform: translateY(-1px);
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
            border-bottom: 1px solid #f1f3f5;
        }
        .card-left { display: flex; align-items: center; gap: 0.75rem; flex: 1; overflow: hidden; min-width: 0; }
        .card-right { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
        .checkbox-inline { margin: 0; cursor: pointer; display: flex; align-items: center; flex-shrink: 0; }
        .custom-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: #174257; }
        .badge-id {
            background: #174257;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .colaborador-principal {
            display: flex;
            align-items: center;
            font-size: 1rem;
            color: #212529;
            overflow: hidden;
            min-width: 0;
        }
        .colaborador-principal i { color: #174257; flex-shrink: 0; }
        .colaborador-principal strong {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            border-radius: 20px;
            font-size: 0.688rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .status-pendente { background: #e9ecef; color: #495057; }
        .status-processo { background: #17a2b8; color: white; }
        .status-admitido { background: #28a745; color: white; }
        .status-demitido { background: #dc3545; color: white; }

        .btn-actions-compact {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid #dee2e6;
            color: #6c757d;
            transition: all 0.2s ease;
            text-decoration: none;
            flex-shrink: 0;
        }
        .btn-actions-compact:hover {
            background: #174257;
            border-color: #174257;
            color: white;
            transform: rotate(90deg);
        }

        .card-details-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem 1.5rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f3f5;
        }
        .card-details-row:last-child { border-bottom: none; }
        .card-details-main { padding-top: 0.75rem; }
        .card-details-docs {
            background: #fafbfc;
            padding: 0.75rem 1.25rem;
            gap: 0.75rem 1.25rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.813rem;
            min-width: 0;
        }
        .detail-item i:first-child { flex-shrink: 0; font-size: 0.875rem; color: #6c757d; }
        .detail-label { font-weight: 500; color: #6c757d; white-space: nowrap; }
        .detail-value { color: #212529; font-weight: 400; }
        .detail-value-small { font-size: 0.75rem; color: #6c757d; }
        .detail-value-empty { color: #adb5bd; font-style: italic; }

        .detail-check {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 0.625rem;
            flex-shrink: 0;
        }
        .detail-check-yes { background: #d4edda; color: #155724; }
        .detail-check-no { background: #f8d7da; color: #721c24; }

        .dropdown-menu-custom {
            min-width: 11rem;
            padding: 0.25rem 0;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .dropdown-menu-custom .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        .dropdown-menu-custom .dropdown-item i { width: 1.25rem; text-align: center; }
        .dropdown-menu-custom .dropdown-item:hover { background-color: #f8f9fa; color: #174257; }
        .dropdown-menu-custom .dropdown-divider { margin: 0.25rem 0; }

        @media (max-width: 768px) {
            .card-header-row { flex-direction: column; align-items: flex-start; gap: 0.5rem; padding: 0.75rem 1rem; }
            .card-right { width: 100%; justify-content: flex-end; }
            .cards-toolbar { padding: 0.5rem 0.75rem; }
        }
    </style>
@endpush
