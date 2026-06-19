@extends('layouts.sistema')
@section('title', 'Entrevista - Parecer Rota')
@section('content_header','Entrevista - Parecer Rota')
@section('content')
    <modal ref="filtroColunas" id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template #conteudo>


            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.pcd" @click="colunasTabela.pcd = !colunasTabela.pcd"
                       class="custom-control-input" id="pcd"
                >
                <label class="custom-control-label"
                       for="pcd"
                >PCD</label>
            </div>


            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.parecer_rh"
                       @click="colunasTabela.parecer_rh = !colunasTabela.parecer_rh" class="custom-control-input"
                       id="parecer_rh"
                >
                <label class="custom-control-label"
                       for="parecer_rh"
                >PARECER RH NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.tecnica_nota"
                       @click="colunasTabela.tecnica_nota = !colunasTabela.tecnica_nota" class="custom-control-input"
                       id="tecnica_nota"
                >
                <label class="custom-control-label"
                       for="tecnica_nota"
                >ENTREVISTA TÉCNICA NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.teste_pratico_nota"
                       @click="colunasTabela.teste_pratico_nota = !colunasTabela.teste_pratico_nota"
                       class="custom-control-input" id="teste_pratico_nota"
                >
                <label class="custom-control-label"
                       for="teste_pratico_nota"
                >TESTE PRÁTICO NOTA</label>
            </div>
        </template>
    </modal>

    <modal ref="janelaParecerEntrevista" id="janelaParecerEntrevista" :titulo="tituloJanela" :size="80" :fechar="!preload">
        <template #conteudo>
            <preload v-if="preload"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''">
                <dados-pessoais :form="form"></dados-pessoais>
                <fieldset>
                    <legend class="text-uppercase">Informações</legend>

                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Tipo de Contratação</label>
                                <select class="form-control" disabled="disabled"
                                        v-model="form.parecer_rota.rota_tipo"
                                >
                                    <option value="">Selecione</option>
                                    <option value="parada">Parada</option>
                                    <option value="fixo">Fixo</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12"></div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Tem Rota que atende</label>
                                <select v-if="form.parecer_rota.rota_tipo === 'fixo'" class="form-control"
                                        :disabled="visualizar"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_rota.tem_rota"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>

                                <select v-if="form.parecer_rota.rota_tipo === 'parada'" class="form-control"
                                        :disabled="visualizar"
                                        v-model="form.parecer_rota.tem_rota"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6" v-if="form.parecer_rota.tem_rota">
                            <div class="form-group">
                                <label>Qual</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       {{--                                           onblur="valida_campo_vazio(this,1)"--}}
                                       v-model="form.parecer_rota.qual"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Bairro Rota:</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       onblur="valida_campo_vazio(this,1)"
                                       v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                       v-model="form.parecer_rota.bairro_rota"
                                >

                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-if="form.parecer_rota.rota_tipo === 'parada'"
                                       v-model="form.parecer_rota.bairro_rota"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Ponto de referência Rota:</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       onblur="valida_campo_vazio(this,1)"
                                       v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                       v-model="form.parecer_rota.ponto_referencia_rota"
                                >

                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-if="form.parecer_rota.rota_tipo === 'parada'"
                                       v-model="form.parecer_rota.ponto_referencia_rota"
                                >
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Informado sobre ponto de referência</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_rota.pega_onibus"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>

                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'parada'"
                                        v-model="form.parecer_rota.pega_onibus"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6" v-if="form.parecer_rota.pega_onibus">
                            <div class="form-group">
                                <label>Qual</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       onblur="valida_campo_vazio(this,1)"
                                       v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                       v-model="form.parecer_rota.pega_onibus_qual_ponto"
                                >

                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-if="form.parecer_rota.rota_tipo === 'parada'"
                                       v-model="form.parecer_rota.pega_onibus_qual_ponto"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Bairro Residência:</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       onblur="valida_campo_vazio(this,1)"
                                       v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                       v-model="form.parecer_rota.bairro_residencia"
                                >

                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-if="form.parecer_rota.rota_tipo === 'parada'"
                                       v-model="form.parecer_rota.bairro_residencia"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Ponto de referência Residência:</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       onblur="valida_campo_vazio(this,1)"
                                       v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                       v-model="form.parecer_rota.ponto_referencia_residencia"
                                >
                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-if="form.parecer_rota.rota_tipo === 'parada'"
                                       v-model="form.parecer_rota.ponto_referencia_residencia"
                                >
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Autorizado Vale Transporte</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_rota.vale_transporte"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>

                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'parada'"
                                        v-model="form.parecer_rota.vale_transporte"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>
                    </div>


                </fieldset>

                <fieldset>
                    <legend>Rota Disponivel para qual turno</legend>
                    <div class="row">
                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label>Turno A</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_rota.rota_disponivel_turno_a"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>

                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'parada'"
                                        v-model="form.parecer_rota.rota_disponivel_turno_a"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label>Turno B</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_rota.rota_disponivel_turno_b"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>

                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'parada'"
                                        v-model="form.parecer_rota.rota_disponivel_turno_b"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label>Turno C</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_rota.rota_disponivel_turno_c"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>

                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'parada'"
                                        v-model="form.parecer_rota.rota_disponivel_turno_c"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label>Outros</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'fixo'"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_rota.rota_disponivel_turno_o"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>

                                <select class="form-control" :disabled="visualizar"
                                        v-if="form.parecer_rota.rota_tipo === 'parada'"
                                        v-model="form.parecer_rota.rota_disponivel_turno_o"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12" v-if="form.parecer_rota.rota_disponivel_turno_o">
                            <div class="form-group">
                                <label>Quais</label>
                                <input type="text" :disabled="visualizar"
                                       {{--                                                       onblur="valida_campo_vazio(this,1)" --}}
                                       class="form-control"
                                       v-model="form.parecer_rota.rota_disponivel_outros"
                                >
                            </div>
                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-uppercase">Parecer Final Transporte</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Rota Atende</label>
                                <select class="custom-select" :disabled="visualizar"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_rota.rota_atende"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Entrevistado Por:</label>
                                <input type="text" :disabled="visualizar" autocomplete="off" class="form-control"
                                       onblur="valida_campo_vazio(this,3)"
                                       v-model="form.parecer_rota.quem_entrevistou"
                                >
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Observação</legend>
                    <textarea v-model="form.parecer_rota.observacao" class="form-control" cols="3"
                              :disabled="visualizar"
                              rows="3"
                    ></textarea>
                </fieldset>

            </div>
        </template>
        <template #rodape>
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="editando && !atualizado  && !preload"
                        @click.prevent="alterar"
                >
                    <i class="fa fa-edit"></i> Alterar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="!editando && !cadastrado  && !preload"
                        @click.prevent="cadastrar"
                >
                    <i class="fa fa-save"></i> Salvar
                </button>
            </div>
        </template>
    </modal>

    <modal ref="janelaWhatsappParecerRota" id="janelaWhatsappParecerRota" :titulo="tituloWhatsappModal" size="g" :fechar="!preloadWhatsappModal">
        <template #conteudo>
            <preload v-if="preloadWhatsappModal"></preload>
            <div v-if="!preloadWhatsappModal && whatsappModalErro" class="alert alert-danger mb-0">
                @{{ whatsappModalErro }}
            </div>
            <div v-if="!preloadWhatsappModal && !whatsappModalErro">
                <dados-pessoais :form="whatsappModalForm"></dados-pessoais>

                <fieldset>
                    <legend>Telefone WhatsApp</legend>

                    <div class="alert alert-info py-2 mb-2" v-if="possui_whatsapp_cadastrado">
                        WhatsApp cadastrado: <strong>@{{ telefone_whatsapp }}</strong>
                    </div>

                    <div class="alert alert-warning py-2 mb-2"
                         v-else-if="whatsappModalForm.tel_principal && whatsappModalForm.tel_principal.numero">
                        Contato principal (@{{ telefone_contato_tipo }}): <strong>@{{ whatsappModalForm.tel_principal.numero }}</strong>.
                        Confirme o número abaixo para envio.
                    </div>

                    <div class="alert alert-warning py-2 mb-2" v-else>
                        Nenhum telefone cadastrado — informe o WhatsApp abaixo para envio.
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-4">
                            <div class="form-group mb-sm-0">
                                <label>Tipo</label>
                                <select class="form-control" v-model="telefone_whatsapp_tipo">
                                    <option value="whatsapp">WhatsApp</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-8">
                            <div class="form-group mb-0">
                                <label>Telefone WhatsApp</label>
                                <input type="text"
                                       class="form-control"
                                       v-mascara:telefone
                                       onblur="valida_telefone(this)"
                                       v-model="telefone_whatsapp"
                                >
                                <small class="form-text text-muted">
                                    O telefone será salvo como WhatsApp principal do candidato ao enviar.
                                </small>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Pré-visualização da mensagem</legend>
                    <div class="whatsapp-preview-wrap">
                        <div class="whatsapp-preview-header">
                            <i class="fab fa-whatsapp text-success"></i>
                            <span>Como o candidato receberá</span>
                        </div>
                        <div class="whatsapp-preview-bubble" v-html="mensagemWhatsappPreviewHtml"></div>
                    </div>
                </fieldset>

                <p class="text-muted mb-2" v-if="whatsappModalEnviadoEm">
                    Último envio: @{{ formatarDataWhatsapp(whatsappModalEnviadoEm) }}
                    <span v-if="whatsappModalEnviadoPor">
                        por <strong>@{{ whatsappModalEnviadoPor }}</strong>
                    </span>
                </p>

                <small class="form-text text-muted d-block">
                    O envio é registrado automaticamente no log histórico do candidato.
                </small>
            </div>
        </template>
        <template #rodape>
            <button type="button"
                    class="btn btn-sm btn-success"
                    v-if="!preloadWhatsappModal && !whatsappModalErro"
                    :disabled="!podeEnviarWhatsapp || preloadWhatsapp"
                    @click.prevent="enviarWhatsapp"
            >
                <i class="fab fa-whatsapp"></i>
                @{{ preloadWhatsapp ? 'Enviando...' : 'Enviar WhatsApp' }}
            </button>
        </template>
    </modal>

    <modal ref="janelaConfirmarWhatsapp" id="janelaConfirmarWhatsapp" titulo="Reenviar WhatsApp">
        <template #conteudo>
            <h4 class="mb-3">Este parecer já teve WhatsApp enviado. Deseja reenviar?</h4>
            <p class="text-muted mb-0" v-if="whatsappModalEnviadoEm">
                Último envio: @{{ formatarDataWhatsapp(whatsappModalEnviadoEm) }}
                <span v-if="whatsappModalEnviadoPor">
                    por <strong>@{{ whatsappModalEnviadoPor }}</strong>
                </span>
            </p>
        </template>
        <template #rodape>
            <button type="button"
                    class="btn btn-sm btn-success"
                    :disabled="preloadWhatsapp"
                    @click.prevent="executarEnvioWhatsapp"
            >
                <i class="fab fa-whatsapp"></i>
                @{{ preloadWhatsapp ? 'Enviando...' : 'Sim, reenviar' }}
            </button>
        </template>
    </modal>

    <fieldset>
        <legend>Filtro</legend>
        <form @submit.prevent="atualizar">
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                               id="filtroIntervalo"
                               v-model="controle.dados.filtroPeriodo"
                        >
                        <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label=""
                                    :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                    v-model="controle.dados.periodo"
                        ></datepicker>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text"
                               placeholder="Buscar por nome"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca"
                               @keyup.enter.prevent="atualizar"
                        >
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text"
                               placeholder="Buscar por cpf"
                               autocomplete="mastertag"
                               onblur="valida_cpf(this)"
                               v-mascara:cpf
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoCPF"
                               @keyup.enter.prevent="atualizar"
                        >
                    </div>
                </div>

                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="form-group">
                        <label>Cargo</label>
                        <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                      :valido="controle.dados.campoVaga !== ''"
                                      v-model="controle.dados.autocomplete_label"
                                      :disabled="controle.carregando"
                                      placeholder="Por cargo"
                                      @onblur="resetaCampo"
                                      @onselect="selecionaVaga"
                                      @onenter="atualizar"
                        ></autocomplete>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.campoUf"
                        >
                            <option value="">SEM FILTRO</option>
                            <option value="MA">MA</option>
                            <option value="AC">AC</option>
                            <option value="AL">AL</option>
                            <option value="AP">AP</option>
                            <option value="AM">AM</option>
                            <option value="BA">BA</option>
                            <option value="CE">CE</option>
                            <option value="DF">DF</option>
                            <option value="ES">ES</option>
                            <option value="GO">GO</option>
                            <option value="MT">MT</option>
                            <option value="MS">MS</option>
                            <option value="MG">MG</option>
                            <option value="PA">PA</option>
                            <option value="PB">PB</option>
                            <option value="PR">PR</option>
                            <option value="PE">PE</option>
                            <option value="PI">PI</option>
                            <option value="RJ">RJ</option>
                            <option value="RN">RN</option>
                            <option value="RS">RS</option>
                            <option value="RO">RO</option>
                            <option value="RR">RR</option>
                            <option value="SC">SC</option>
                            <option value="SP">SP</option>
                            <option value="SE">SE</option>
                            <option value="TO">TO</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label for="">Rota</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.campoRota"
                        >
                            <option value="">Geral</option>
                            <option value="sem_parecer">Sem parecer</option>
                            <option value="sim">Tem Rota</option>
                            <option value="não">Não tem Rota</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label for="">Exibir</label>
                        <select class="form-control form-control-sm" @change="atualizar"
                                :disabled="controle.carregando"
                                v-model="controle.dados.pages"
                        >
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>

        <div class="col-12">
            <div class="row">
                <button type="button" class="btn btn-sm mr-1 btn-success mb-1 mr-1" :disabled="controle.carregando"
                        @click="atualizar"
                ><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"
                    ></i>
                    Atualizar
                </button>
                <button class="btn btn-sm mr-1 btn-danger mb-1 mr-1"
                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="selecionados.length === 0" @click="selecionados = []"
                >
                    <i class="fa fa-times"></i> Limpar seleção
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary mb-1 mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando || preloadExportacao || lista.length===0"
                >
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL <span class="badge badge-light"
                                                                           v-show="selecionados.length > 0"
                    >@{{ selecionados.length }}</span>
                </button>
            </div>
        </div>

    </fieldset>
    <preload class="text-center" v-if="controle.carregando"></preload>

    <div id="conteudo">
        <div class="empty-state" v-show="!controle.carregando && lista.length===0">
            <div class="empty-state-icon"><i class="fas fa-route"></i></div>
            <h3 class="empty-state-title">Nenhum registro encontrado</h3>
            <p class="empty-state-text">Ajuste os filtros ou aguarde candidatos selecionados no parecer RH.</p>
        </div>

        <div class="cards-toolbar" v-show="!controle.carregando && lista.length > 0">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="selTodosParecerRota"
                       :checked="tudoMarcado"
                       :disabled="comRota.length === 0"
                       :style="comRota.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                       @click="selecionaTodos">
                <label class="custom-control-label" for="selTodosParecerRota" style="width: max-content">
                    Selecionar todos com parecer de rota
                </label>
            </div>
            <button class="btn btn-sm mr-1 btn-outline-secondary ml-auto" @click="$refs.filtroColunas?.abrirModal()" v-tippy content="Mostrar e Ocultar Colunas">
                <i class="bx bxs-filter-alt"></i> Colunas
            </button>
        </div>

        <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
            <div class="solicitacao-card" v-for="entrevista in lista" :key="entrevista.id"
                 :class="getCardStatusClass(entrevista)">
                <div class="card-header-row">
                    <div class="card-left">
                        <label class="checkbox-inline mb-0" v-if="entrevista.parecer_rota">
                            <input type="checkbox" class="custom-checkbox" v-model="selecionados" :value="entrevista.id" :id="'chk_' + entrevista.id">
                        </label>
                        <input type="checkbox" class="custom-checkbox" v-else disabled title="Sem parecer de rota">
                        <span class="badge-id">#@{{ entrevista.id }}</span>
                        <div class="colaborador-principal">
                            <i class="fas fa-user-circle mr-1"></i>
                            <strong>@{{ entrevista.curriculo.nome }}</strong>
                        </div>
                        <span class="status-badge" :class="getStatusBadgeClass(entrevista)">
                            @{{ getStatusRotaLabel(entrevista) }}
                        </span>
                    </div>
                    <div class="card-right">
                        <div class="dropdown" :class="{ show: isDropdownOpen(entrevista.id) }">
                            <a class="btn-actions-compact"
                               href="#"
                               role="button"
                               :id="`dropdownParecerRota_${entrevista.id}`"
                               aria-haspopup="true"
                               :aria-expanded="isDropdownOpen(entrevista.id) ? 'true' : 'false'"
                               @click.prevent.stop="toggleDropdown(entrevista.id)">
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                 :class="{ show: isDropdownOpen(entrevista.id) }"
                                 :aria-labelledby="`dropdownParecerRota_${entrevista.id}`"
                                 @click="fecharDropdown">
                                <a class="dropdown-item" href="javascript://"
                                   v-if="!entrevista.parecer_rota"
                                   @click.prevent="formEntrevistar(entrevista.id); $refs.janelaParecerEntrevista?.abrirModal()">
                                    <i class="far fa-list-alt mr-2 text-primary"></i> Entrevistar
                                </a>
                                @can('entrevista_parecer_rota_update')
                                <a class="dropdown-item" href="javascript://"
                                   v-if="entrevista.parecer_rota"
                                   @click.prevent="formEntrevistar(entrevista.id); editando = true; $refs.janelaParecerEntrevista?.abrirModal()">
                                    <i class="fa fa-edit mr-2 text-warning"></i> Editar
                                </a>
                                @endcan
                                <a class="dropdown-item" href="javascript://"
                                   v-if="entrevista.parecer_rota"
                                   @click.prevent="formEntrevistar(entrevista.id); visualizar = true; $refs.janelaParecerEntrevista?.abrirModal()">
                                    <i class="fas fa-eye mr-2 text-info"></i> Visualizar
                                </a>
                                <a class="dropdown-item" href="javascript://"
                                   v-if="whatsappLiberado && entrevista.parecer_rota && entrevista.parecer_rota.tem_rota"
                                   @click.prevent="abrirModalWhatsapp(entrevista)">
                                    <i class="fab fa-whatsapp mr-2 text-success"></i> Enviar WhatsApp
                                </a>
                                <div class="dropdown-divider" v-if="entrevista.parecer_rota"></div>
                                <a class="dropdown-item" href="javascript://"
                                   v-if="entrevista.parecer_rota"
                                   @click.prevent="gerarPdf(entrevista)">
                                    <i class="fas fa-file-pdf mr-2 text-danger"></i> Gerar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-details-row card-details-main">
                    <div class="detail-item">
                        <i class="fas fa-briefcase"></i>
                        <span class="detail-label">Vaga</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !entrevista.vaga_aberta_municipio }">
                            @{{ entrevista.vaga_aberta_municipio || 'Não informado' }}
                        </span>
                    </div>
                    <div class="detail-item" v-show="colunasTabela.pcd">
                        <span class="detail-check" :class="entrevista.curriculo.pcd ? 'detail-check-yes' : 'detail-check-no'">
                            <i :class="entrevista.curriculo.pcd ? 'fas fa-check' : 'fas fa-minus'"></i>
                        </span>
                        <span class="detail-label">PCD</span>
                    </div>
                    <div class="detail-item" v-show="colunasTabela.parecer_rh">
                        <i class="fas fa-clipboard-check"></i>
                        <span class="detail-label">Parecer RH</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !entrevista.parecer_rh }">
                            @{{ entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'Aguardando' }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-route"></i>
                        <span class="detail-label">Tem rota</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !entrevista.parecer_rota }">
                            @{{ entrevista.parecer_rota && entrevista.parecer_rota.tem_rota != null ? (entrevista.parecer_rota.tem_rota ? 'Sim' : 'Não') : 'Aguardando' }}
                        </span>
                    </div>
                </div>

                <div class="card-details-row card-details-docs"
                     v-show="colunasTabela.tecnica_nota || colunasTabela.teste_pratico_nota || entrevista.parecer_rota">
                    <div class="detail-item" v-show="colunasTabela.tecnica_nota">
                        <i class="fas fa-user-tie"></i>
                        <span class="detail-label">Ent. Técnica</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !entrevista.parecer_tecnica }">
                            @{{ entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'Aguardando' }}
                        </span>
                    </div>
                    <div class="detail-item" v-show="colunasTabela.teste_pratico_nota">
                        <i class="fas fa-tools"></i>
                        <span class="detail-label">Teste Prático</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !entrevista.parecer_teste }">
                            @{{ entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'Aguardando' }}
                        </span>
                    </div>
                    <div class="detail-item" v-if="entrevista.parecer_rota">
                        <i class="fab fa-whatsapp"></i>
                        <span class="detail-label">WhatsApp</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !entrevista.parecer_rota.whatsapp_enviado_em }">
                            @{{ getWhatsappEnviadoLabel(entrevista) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.entrevista.parecer_rota_transporte.atualizar')}}"
                            :por-pagina="controle.dados.pages"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"
        ></controle-paginacao>
    </div>

@stop
@push('js')
    <script src="{{mix('js/g/entrevistas/parecer_rota/app.js')}}"></script>
@endpush
@push('css')
    <style>
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
        .cards-lista { display: flex; flex-direction: column; gap: 1rem; overflow: visible; }
        #conteudo { overflow: visible; }
        .solicitacao-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 0;
            transition: all 0.25s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #6c757d;
            overflow: visible;
            position: relative;
            z-index: 1;
        }
        .solicitacao-card:has(.dropdown.show) {
            z-index: 30;
        }
        .solicitacao-card.card-status-pendente { border-left-color: #6c757d; }
        .solicitacao-card.card-status-rota-info { border-left-color: #17a2b8; }
        .solicitacao-card.card-status-rota-sim { border-left-color: #28a745; }
        .solicitacao-card.card-status-rota-nao { border-left-color: #dc3545; }
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
            overflow: visible;
            position: relative;
            z-index: 2;
            border-radius: 10px 10px 0 0;
        }
        .card-left { display: flex; align-items: center; gap: 0.75rem; flex: 1; overflow: hidden; min-width: 0; }
        .card-right { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; position: relative; overflow: visible; }
        .card-right .dropdown { position: relative; }
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
            position: absolute;
            top: calc(100% + 4px);
            right: 0;
            left: auto;
            z-index: 1060;
        }
        .whatsapp-preview-wrap {
            background: #e5ddd5;
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid #d1c7bc;
        }
        .whatsapp-preview-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.813rem;
            color: #54656f;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        .whatsapp-preview-bubble {
            background: #fff;
            border-radius: 0 12px 12px 12px;
            padding: 0.875rem 1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
            color: #111b21;
            font-size: 0.875rem;
            line-height: 1.45;
            white-space: pre-wrap;
            word-break: break-word;
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
