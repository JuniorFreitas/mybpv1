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

    <fieldset>
        <legend>Filtro</legend>
        <form @submit.prevent="$refs.componente.buscar()">
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
    <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length===0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>

    <div id="conteudo">
        <table class="tabela" v-show="!controle.carregando && lista.length > 0">
            <thead>
            <tr class="bg-default">
                <th style="width: 1em;">
                    <input type="checkbox"
                           :checked="tudoMarcado"
                           :disabled="comRota.length === 0"
                           :content="comRota.length > 0 ? 'Selecionar todos' : 'Não possui cadastrodo no RH'"
                           v-tippy
                           style="cursor: pointer"
                           @change.prevent="selecionaTodos"
                    >
                </th>
                <th class="text-center">CÓD</th>
                <th>Nome</th>
                <th class="text-center">Vaga</th>
                <th class="text-center" v-show="colunasTabela.pcd">PCD</th>
                <th class="text-center" v-show="colunasTabela.parecer_rh">Parecer RH Nota</th>
                <th class="text-center">Rota Transporte</th>
                <th class="text-center" v-show="colunasTabela.tecnica_nota">Entrevista Técnica Nota</th>
                <th class="text-center" v-show="colunasTabela.teste_pratico_nota">Teste Prático Nota</th>
                <th>
                    <button class="btn btn-sm mr-1 btn-primary mb-2" content="Mostrar e Ocultar Colunas" v-tippy
                            @click="$refs.filtroColunas?.abrirModal()">
                        <i class="bx bxs-filter-alt" aria-hidden="true"></i>
                    </button>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="entrevista in lista">
                <td class="text-center">
                    <label :for="entrevista.id">
                        <input
                            type="checkbox"
                            v-model="selecionados"
                            :value="entrevista.id"
                            :id="entrevista.id"
                            :style="entrevista.parecer_rota ? 'cursor:pointer' : 'cursor: not-allowed'"
                            :title="entrevista.parecer_rota ? null : 'Não possui cadastro em rotas'"
                            v-if="entrevista.parecer_rota"
                        >
                        <input type="checkbox" v-else disabled="disabled" title="Sem parecer Rota">

                    </label>
                </td>
                <td class="text-center">
                    @{{entrevista.id}}
                </td>
                <td>
                    @{{entrevista.curriculo.nome}}
                </td>
                <td class="text-center">
                    @{{entrevista.vaga_aberta_municipio}}
                </td>
                <td class="text-center" v-show="colunasTabela.pcd">
                    @{{entrevista.curriculo.pcd ? 'Sim' : 'Não'}}
                </td>
                <td class="text-center" v-show="colunasTabela.parecer_rh">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'aguardando'}}
                </td>

                <td class="text-center">
                    @{{ entrevista.parecer_rota ? entrevista.parecer_rota.rota_atende != null ?
                    entrevista.parecer_rota.rota_atende === true ? 'Sim': 'Não' : 'Não Informado' : 'aguardando' }}
                </td>

                <td class="text-center" v-show="colunasTabela.tecnica_nota">
                    @{{entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-show="colunasTabela.teste_pratico_nota">
                    @{{entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'aguardando'}}

                </td>

                <td class="text-center">
                    <form :action="`${URL_ADMIN}/entrevistas/parecer-rota/ficha_pdf`" target="_blank"
                          method="post"
                    >
                        <button class="btn btn-sm mr-1 btn-primary mb-2" content="Entrevistar" v-tippy
                                v-show="!entrevista.parecer_rota"
                                @click.prevent="formEntrevistar(entrevista.id); $refs.janelaParecerEntrevista?.abrirModal()">
                            <i class="far fa-list-alt"></i>
                        </button>

                        @can('entrevista_parecer_rota_update')
                            <button class="btn btn-sm mr-1 btn-primary mb-2" content="Editar" v-tippy
                                    v-show="entrevista.parecer_rota"
                                    @click.prevent="formEntrevistar(entrevista.id); editando = true; $refs.janelaParecerEntrevista?.abrirModal()">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </button>
                        @endcan

                        <button class="btn btn-sm mr-1 btn-primary mb-2" content="Visualizar" v-tippy
                                v-show="entrevista.parecer_rota"
                                @click.prevent="formEntrevistar(entrevista.id); visualizar = true; $refs.janelaParecerEntrevista?.abrirModal()">
                            <i class="fa fa-search-plus" aria-hidden="true"></i>
                        </button>

                        @csrf
                        <input type="hidden" name="id" :value="entrevista.parecer_rota.id"
                               v-if="entrevista.parecer_rota"
                        >
                        <button type="submit" content="Gerar PDF" v-tippy v-show="entrevista.parecer_rota"
                                class="btn btn-sm mr-1 btn-primary mb-2"
                        >
                            <i class="fa fa-file-pdf" aria-hidden="true"></i>
                        </button>
                    </form>
                </td>
            </tr>
        </table>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.entrevista.parecer_rota_transporte.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"
        ></controle-paginacao>
    </div>

@stop
@push('js')
    <script src="{{mix('js/g/entrevistas/parecer_rota/app.js')}}"></script>
@endpush
