@extends('layouts.sistema')
@section('title', 'Treinamento')
@section('content_header')
    <h4 class="text-default">Treinamento</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')

    <modal id="janelaTreinamento" titulo="Treinamentos" :size="95">
        <template slot="conteudo">
            <div class="alert alert-success text-center" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i> Treinamento atualizado com sucesso</h4>
            </div>
            <p class=" mt-2 text-center" v-if="preload">
                <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
            </p>

            <div v-if="!preload && (!cadastrado && !atualizado)">

                <fieldset>
                    <legend>Treinamentos</legend>
                    <div class="row">
                        <div class="col-12">
                            <h6 v-if="editando">Funcionário: @{{ form.nome }}</h6>
                            <fieldset>
                                <label>Tipo</label>
                                <select class="form-control" v-model="form.tipo" :disabled='editando'
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione ...</option>
                                    <option value="Fixo">Fixo</option>
                                    <option value="Parada">Parada</option>
                                </select>
                            </fieldset>
                        </div>

                        {{--                        <div class="col-12" v-if="form.tipo">--}}
                        {{--                            <fieldset>--}}
                        {{--                                <legend>Exame</legend>--}}
                        {{--                                <div class="row">--}}
                        {{--                                    <div class="col-12 col-md-6">--}}
                        {{--                                        <div class="form-group">--}}
                        {{--                                            <label for="">EXAME REALIZADO?</label>--}}
                        {{--                                            <select class="form-control" v-model="form.exame.exame_realizado">--}}
                        {{--                                                <option value="">Selecione...</option>--}}
                        {{--                                                <option :value="true">Sim</option>--}}
                        {{--                                                <option :value="false">Não</option>--}}
                        {{--                                            </select>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}

                        {{--                                    <template v-if="form.exame.exame_realizado">--}}
                        {{--                                        <div class="col-12 col-md-6">--}}
                        {{--                                            <label for="">DATA DO EXAME:--}}
                        {{--                                            </label>--}}
                        {{--                                            <datepicker v-model="form.exame.data_realizado"--}}
                        {{--                                                        max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"--}}
                        {{--                                                        onblur="valida_data_vazio(this)"></datepicker>--}}
                        {{--                                        </div>--}}

                        {{--                                        <div class="col-12 col-md-6">--}}
                        {{--                                            <div class="form-group">--}}
                        {{--                                                <label for="">TIPO DO EXAME</label>--}}
                        {{--                                                <select class="form-control" v-model="form.exame.tipo_exame">--}}
                        {{--                                                    <option value="">Selecione ...</option>--}}
                        {{--                                                    <option value="ADMISSIONAL">ADMISSIONAL</option>--}}
                        {{--                                                    <option value="PERIODICO">PERIODICO</option>--}}
                        {{--                                                    <option value="RETORNO AO TRABALHO">RETORNO AO TRABALHO</option>--}}
                        {{--                                                </select>--}}
                        {{--                                            </div>--}}
                        {{--                                        </div>--}}

                        {{--                                        <div class="col-12 col-md-6">--}}
                        {{--                                            <div class="form-group">--}}
                        {{--                                                <label for="">APTO TRABALHO EM ALTURA?</label>--}}
                        {{--                                                <select class="form-control" v-model="form.exame.trabalho_altura">--}}
                        {{--                                                    <option value="">Selecione ...</option>--}}
                        {{--                                                    <option :value="true">Sim</option>--}}
                        {{--                                                    <option :value="false">Não</option>--}}
                        {{--                                                </select>--}}
                        {{--                                            </div>--}}
                        {{--                                        </div>--}}

                        {{--                                        <div class="col-12 col-md-6">--}}
                        {{--                                            <div class="form-group">--}}
                        {{--                                                <label for="">APTO TRABALHO EM ESPACO CONFINADO?</label>--}}
                        {{--                                                <select class="form-control" v-model="form.exame.espaco_confinado">--}}
                        {{--                                                    <option value="">Selecione ...</option>--}}
                        {{--                                                    <option :value="true">Sim</option>--}}
                        {{--                                                    <option :value="false">Não</option>--}}
                        {{--                                                </select>--}}
                        {{--                                            </div>--}}
                        {{--                                        </div>--}}

                        {{--                                    </template>--}}
                        {{--                                </div>--}}
                        {{--                            </fieldset>--}}
                        {{--                        </div>--}}

                        <div class="col-12 col-md-6" v-for="(treinamento, index) in form.listaVencimentos"
                             v-if="form.tipo">
                            <fieldset>
                                <legend>@{{ treinamento.label }}</legend>

                                <div class="alert alert-warning p-2" style="font-size: 0.85rem;"
                                     v-show="treinamento.descricao">
                                    A quem se destina: @{{ treinamento.descricao }}
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="">Realizou este treinamento?</label>
                                            <select class="form-control" v-model="treinamento.fez_treinamento">
                                                <option :value="true">Sim</option>
                                                <option :value="false">Não</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6" v-if="treinamento.fez_treinamento">
                                        <div class="form-group">
                                            <template v-if="treinamento.prazo_fixo && treinamento.prazo_parada">
                                                <label for="">Data do treinamento:
                                                    <span class="text-danger" style="font-size: 0.85rem;"
                                                          v-show="treinamento.prazo_fixo || treinamento.prazo_parada">
                                                     Vencimento: @{{ treinamento.data_vencimento }}
                                                </span>
                                                </label>
                                                <datepicker v-model="treinamento.data_treinamento"
                                                            max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                            onblur="valida_data_vazio(this)"></datepicker>

                                            </template>

                                            <template v-if="!treinamento.prazo_fixo && !treinamento.prazo_parada">
                                                <label for="">Data do treinamento</label>
                                                <datepicker v-model="treinamento.data_treinamento"
                                                            max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                            onblur="valida_data_vazio(this)"></datepicker>
                                                <label for="">Data Vencimento</label>
                                                <datepicker v-model="treinamento.data_vencimento"
                                                            min="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                            onblur="valida_data_vazio(this)"></datepicker>
                                            </template>


                                            {{--                                            <span class="text-muted" style="font-size: 0.85rem;"--}}
                                            {{--                                                  v-show="treinamento.prazo_fixo && form.tipo==='Parada'">--}}
                                            {{--                                                    Informe a data do treinamento o sistema colocara o vencimento automatico--}}
                                            {{--                                            </span>--}}

                                            {{--                                            <span class="text-muted" style="font-size: 0.85rem;"--}}
                                            {{--                                                  v-show="!treinamento.prazo_fixo && !treinamento.prazo_parada">--}}
                                            {{--                                                Informe o vencimento final--}}
                                            {{--                                            </span>--}}
                                        </div>
                                        <div class="form-group">
                                            <label for="">Numero da FAT</label>
                                            <input type="text" class="form-control" v-model="treinamento.numero_fat">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </fieldset>
            </div>

        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-primary" @click="salvar"
                    v-if="!preload && (!cadastrado && !atualizado)">
                <i class="fa fa-save"></i> Salvar
            </button>
        </template>
    </modal>

    <modal id="janelaTreinamentoMassa" titulo="Treinamentos" :size="95">
        <template slot="conteudo">
            <div class="alert alert-success text-center" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i> Treinamento atualizado com sucesso</h4>
            </div>
            <p class=" mt-2 text-center" v-if="preload">
                <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
            </p>

            <div v-if="!preload && (!cadastrado && !atualizado)">

                <fieldset>
                    <legend>Treinamentos</legend>
                    <div class="row">
                        <div class="col-12">
                            <fieldset>
                                <label>Tipo</label>
                                <select class="form-control" v-model="formMassa.tipo"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione ...</option>
                                    <option value="Fixo">Fixo</option>
                                    <option value="Parada">Parada</option>
                                </select>
                            </fieldset>
                        </div>

                        <div class="col-12 col-md-6" v-for="(treinamento, index) in formMassa.listaVencimentos"
                             v-if="formMassa.tipo !== ''">
                            <fieldset>
                                <legend>@{{ treinamento.label }}</legend>

                                <div class="alert alert-warning p-2" style="font-size: 0.85rem;"
                                     v-show="treinamento.descricao">
                                    A quem se destina: @{{ treinamento.descricao }}
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="">Realizou este treinamento?</label>
                                            <select class="form-control" v-model="treinamento.fez_treinamento">
                                                <option :value="true">Sim</option>
                                                <option :value="false">Não</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6" v-if="treinamento.fez_treinamento">
                                        <div class="form-group">
                                            <template v-if="treinamento.prazo_fixo && treinamento.prazo_parada">
                                                <label for="">Data do treinamento:
                                                    <span class="text-danger" style="font-size: 0.85rem;"
                                                          v-show="treinamento.prazo_fixo || treinamento.prazo_parada">
                                                     Vencimento: @{{ treinamento.data_vencimento }}
                                                </span>
                                                </label>
                                                <datepicker v-model="treinamento.data_treinamento"
                                                            max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                            onblur="valida_data_vazio(this)"></datepicker>

                                            </template>
                                        </div>
                                        <div class="form-group">
                                            <label for="">Numero da FAT</label>
                                            <input type="text" class="form-control" v-model="treinamento.numero_fat">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </fieldset>
            </div>

        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-primary" @click="salvarMassa"
                    v-if="!preload && formMassa.tipo && (!cadastrado && !atualizado)">
                <i class="fa fa-save"></i> Salvar
            </button>
        </template>
    </modal>

    <modal id="janelaEnviar" :fechar="!formEnviar.preload" :titulo="formEnviar.titulo">
        <template slot="conteudo">
            <span v-show="formEnviar.preload">
                <i class="fa fa-spinner fa-pulse"></i> Enviando...
            </span>
            <div class="alert alert-success alert-dismissible" v-show="formEnviar.enviado">
                <h4>
                    <i class="icon fa fa-check"></i>
                    Carteira enviada com sucesso!
                </h4>
            </div>
            <fieldset v-show="!formEnviar.enviado && !formEnviar.preload">
                <legend>Informações</legend>
                <div class="row">
                    <div class="col-12">
                        <p>Nome: @{{ formEnviar.nome }}</p>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="text" onblur="validaEmailVazio(this)" class="form-control"
                                   v-model="formEnviar.email">
                        </div>
                    </div>

                </div>
            </fieldset>
        </template>
        <template slot="rodape">
            <div v-show="!formEnviar.preload">
                <button type="button" class="btn btn-sm btn-primary"
                        @click="enviar"
                        v-show="!formEnviar.enviado">
                    <i class="fa fa-envelope"></i> Enviar
                </button>
            </div>
        </template>
    </modal>

    <modal id="janelaEnviarAviso" :fechar="!formEnviarAviso.preload"
           titulo="Notificação de treinamento próximo ao vencimento">
        <template slot="conteudo">
            <span v-show="formEnviarAviso.preload">
                <i class="fa fa-spinner fa-pulse"></i> Enviando...
            </span>
            <div class="alert alert-success alert-dismissible" v-show="formEnviarAviso.enviado">
                <h4>
                    <i class="icon fa fa-check"></i>
                    Aviso de treinamento enviado com sucesso!
                </h4>
            </div>
            <fieldset v-show="!formEnviarAviso.enviado && !formEnviarAviso.preload">
                <legend>Informações</legend>
                <div class="row">
                    <div class="alert alert-secondary">Informe o e-mail para que seja enviado os treinamentos que estão
                        próximos ao vencimento.
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="text" onblur="validaEmailVazio(this)" class="form-control"
                                   v-model="formEnviarAviso.email">
                        </div>
                    </div>

                </div>
            </fieldset>
        </template>
        <template slot="rodape">
            <div v-show="!formEnviarAviso.preload">
                <button type="button" class="btn btn-sm btn-primary"
                        @click="enviarAviso"
                        v-show="!formEnviarAviso.enviado">
                    <i class="fa fa-envelope"></i> Enviar
                </button>
            </div>
        </template>
    </modal>

    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <div class="row">
            <div class="col-12 col-md-3 ">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" @change="atualizar()"
                           :disabled="controle.carregando"
                           id="filtroVencimento"
                           v-model="controle.dados.campoVencimento">
                    <label class="form-check-label cursor-pointer" for="filtroVencimento">Por período de
                        vencimento</label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label="" @onselect="atualizar()"
                                :disabled="controle.carregando"
                                v-model="controle.dados.vencimento"></datepicker>
                </div>
            </div>

            <div class="col-12 col-md-2">
                <label>CPF</label>
                <input type="text"
                       placeholder="Buscar por cpf"
                       autocomplete="mastertag"
                       v-mascara:cpf
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoCPF">
            </div>


            <div class="col-12 col-md-7">
                <label>Buscar</label>
                <input type="text"
                       placeholder="Buscar por nome"
                       autocomplete="off"
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoBusca">
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Por Vaga</label>
                    <autocomplete :disabled="controle.carregando" :caminho="controle.dados.caminho_autocomplete"
                                  :valido="controle.dados.campoVaga !== ''"
                                  v-model="controle.dados.autocomplete_label"
                                  placeholder="Por vaga"
                                  @onblur="resetaCampo"
                                  @onselect="selecionaVaga"></autocomplete>
                </div>
            </div>

            <div class="col-12 col-lg-2">
                <div class="form-group">
                    <label>Estados</label>
                    <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoUf">
                        <option value="">Todos</option>
                        <option value="AC">AC</option>
                        <option value="AL">AL</option>
                        <option value="AP">AP</option>
                        <option value="AM">AM</option>
                        <option value="BA">BA</option>
                        <option value="CE">CE</option>
                        <option value="DF">DF</option>
                        <option value="ES">ES</option>
                        <option value="GO">GO</option>
                        <option value="MA">MA</option>
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

            <div class="col-12 col-md-2">
                <label>Admitidos</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoAdmitido">
                    <option value="">Geral</option>
                    <option value="S">Sim</option>
                    <option value="N">Não</option>
                </select>
            </div>

            <div class="col-12 col-lg-2">
                <label>Nº Crachá</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoCracha">
                    <option value="">Geral</option>
                    <option value="S">Sim</option>
                    <option value="N">Não</option>
                </select>
            </div>

            <div class="col-12 col-lg-2">
                <label>Foto 3x4</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoFoto">
                    <option value="">Geral</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-2">
                <label>Treinados</label>
                <select class="custom-select custom-select-sm" @change="selecionaTreinados($event.target.value)" :disabled="controle.carregando"
                        v-model="controle.dados.campo_treinados">
                    <option value="">Sem filtro</option>
                    <option value="S">Sim</option>
                    <option value="N">Não</option>
                </select>
            </div>

            <div class="col-12 col-md-6">
                <label>Treinamentos</label>
                <select class="custom-select custom-select-sm" @change="addTreinamento($event.target.value)" :disabled="controle.carregando"
                        v-model="controle.dados.treinamentos">
                    <option value="">Selecionar ...</option>
                    <option value="todos">---- ADICIONAR TODOS ----</option>
                    <option v-for="treinamento in listaTodosTreinamentos" :value="treinamento.label">
                        @{{ treinamento.label }}
                    </option>
                    <option value="rm">---- REMOVER TODOS ----</option>
                </select>

            </div>

            <div class="col-12 mt-2">
                <div class="p-2" style="border: 1px dashed #cccbcb">
                    <h6>TREINAMENTOS SELECIONADOS:</h6>
                    <div class="row">
                        <small class="p-2 ml-2 mb-2 table-secondary text-dark rounded" v-for="(item, ind) in controle.dados.treinamentos_selecionados">
                            @{{ item }} <a href="javascript://" @click.prevent="removeTreinamento(ind)"><i class="fa fa-times ml-1"></i></a>
                        </small>
                    </div>
                </div>
            </div>

        </div>

        <div class="row" v-if="false">
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <label>Buscar</label>
                <input type="text"
                       placeholder="Buscar por nome"
                       autocomplete="off"
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoBusca">
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <label>CPF</label>
                <input type="text"
                       placeholder="Buscar por cpf"
                       autocomplete="mastertag"
                       v-mascara:cpf
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoCPF">
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="form-group">
                    <label>Por Vaga</label>
                    <autocomplete :disabled="controle.carregando" :caminho="controle.dados.caminho_autocomplete"
                                  :valido="controle.dados.campoVaga !== ''"
                                  v-model="controle.dados.autocomplete_label"
                                  placeholder="Por vaga"
                                  @onblur="resetaCampo"
                                  @onselect="selecionaVaga"></autocomplete>
                </div>
            </div>


            {{--            @if(!Request::has('cliente_id'))--}}
            {{--                <div class="col-12 col-sm-6 col-md-6 col-lg-3">--}}
            {{--                    <div class="form-group">--}}
            {{--                        <label>Por Cliente</label>--}}
            {{--                        <autocomplete :disabled="controle.carregando"--}}
            {{--                                      :caminho="controle.dados.caminho_cliente_autocomplete"--}}
            {{--                                      :valido="controle.dados.campoCliente !== ''"--}}
            {{--                                      v-model="controle.dados.autocomplete_label_cliente"--}}
            {{--                                      placeholder="Por cliente"--}}
            {{--                                      @onblur="resetaCampoCliente"--}}
            {{--                                     @onselect="selecionaCliente"></autocomplete>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            @endif--}}

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <label>Áreas</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoArea">
                    <option value="">Todas</option>
                    <option :value="item.id" v-for="item in listaAreas">@{{ item.label }}</option>
                </select>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <label>Cargo</label>
                <input type="text"
                       placeholder="Buscar por cargo"
                       autocomplete="off"
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoCargo">
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                <label>Estado</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoUf">
                    <option value="">Todos</option>
                    <option value="AC">AC</option>
                    <option value="AL">AL</option>
                    <option value="AP">AP</option>
                    <option value="AM">AM</option>
                    <option value="BA">BA</option>
                    <option value="CE">CE</option>
                    <option value="DF">DF</option>
                    <option value="ES">ES</option>
                    <option value="GO">GO</option>
                    <option value="MA">MA</option>
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

            <div class="col-12 col-sm-4 col-md-3">
                <label>Treinados</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campo_treinados">
                    <option value="">Sem filtro</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>


            <div class="col-12 col-md-3 col-sm-4 ">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" @change="atualizar()"
                           :disabled="controle.carregando"
                           id="filtroVencimento"
                           v-model="controle.dados.campoVencimento">
                    <label class="form-check-label cursor-pointer" for="filtroVencimento">
                        Por período de vencimento
                    </label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label="" @onselect="atualizar()"
                                :disabled="controle.carregando"
                                v-model="controle.dados.vencimento"></datepicker>
                </div>
            </div>


            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>NR33</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoNr_trinta_tres">
                    <option value="">Sem filtro</option>
                    <option value="Realizado">Realizado</option>
                    <option value="Não Realizado">Não Realizado</option>
                    <option value="NÃO SE APLICA">Não se aplica</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">

                <label>NR35</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoNr_trinta_cinco">
                    <option value="">Sem filtro</option>
                    <option value="Realizado">Realizado</option>
                    <option value="Não Realizado">Não Realizado</option>
                    <option value="NÃO SE APLICA">Não se aplica</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>EBTV</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoNr_ebtv">
                    <option value="">Sem filtro</option>
                    <option :value="true">Realizado</option>
                    <option :value="false">Não Realizado</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>Admitidos</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoAdmitido">
                    <option value="">Geral</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>


            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>Nº Crachá</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoCracha">
                    <option value="">Geral</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>Foto 3x4</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoFoto">
                    <option value="">Geral</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>PCD</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoPcd">
                    <option value="">Geral</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <label>Exibir</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.pages">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                </select>
            </div>
        </div>

        <div class="col-12">
            <div class="row mt-2">
                <button type="button" class="btn btn-sm btn-success mb-1 mr-1" :disabled="controle.carregando"
                        :style="controle.carregando ? 'cursor: not-allowed' : 'cursor: pointer'" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <form target="_blank" action="{{ route('g.treinamentos.carteiraPdf') }}" method="post">
                    @csrf
                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                    <button type="submit" class="btn btn-sm btn-primary mr-1"
                            :style="!selecionados.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                            :disabled="!selecionados.length">
                        Gerar Carteira <span class="badge badge-light">@{{ selecionados.length }}</span>
                    </button>
                </form>

                <button class="btn btn-sm btn-danger mb-1 mr-1"
                        :style="!selecionados.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="!selecionados.length" @click="selecionados = []">
                    <i class="fa fa-times"></i> Limpar seleção
                </button>

                <button class="btn btn-sm btn-primary mb-1 mr-1"
                        :style="!selecionadosMassa.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                        @click.pre.prevent="abrirFormMassa()"
                        data-toggle="modal"
                        data-target="#janelaTreinamentoMassa"
                        :disabled="!selecionadosMassa.length">
                    <i class="fa fa-plus"></i> Atualizar em massa <span class="badge badge-light">@{{ selecionadosMassa.length }}</span>
                </button>

                <button class="btn btn-sm btn-danger mb-1 mr-1"
                        :style="!selecionadosMassa.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="!selecionadosMassa.length" @click="selecionadosMassa = []">
                    <i class="fa fa-times"></i> Limpar seleção em massa
                </button>

                <button type="button" class="btn btn-sm btn-primary mb-1 mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando || preloadExportacao || lista.length===0 || selecionados.length === 0">
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL <span class="badge badge-light"
                                                                           v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                </button>

                @if (auth()->user()->cliente_id != \App\Models\User::BPSE)
                    {{--                    <button class="btn btn-sm btn-primary ml-1" @click.prevent="abriJanelaEnviarAviso"--}}
                    {{--                            data-toggle="modal"--}}
                    {{--                            data-target="#janelaEnviarAviso">--}}
                    {{--                        <i class="fa fa-envelope"></i> Notificação--}}
                    {{--                    </button>--}}
                @endif
            </div>
        </div>

    </fieldset>

    <p class="text-center" v-if="controle.carregando">
        <i class="fa fa-spinner fa-pulse"></i> Carregando...
    </p>

    <div id="conteudo">
        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th class="text-center" width="30px">
                        <input type="checkbox"
                               :style="!emTreinamentos.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                               :disabled="!emTreinamentos.length" :checked="tudoMarcado"
                               @click="selecionaTodos">
                    </th>
                    <th class="text-center" width="30px">
                        <input type="checkbox"
                               :checked="tudoMarcadoMassa"
                               @click="selecionaTodosMassa">
                    </th>
                    <th>Nome</th>
                    <th class="text-center">Vaga</th>
                    <th class="text-center">Cargo</th>
                    <th class="text-center">Área</th>
                    <th class="text-center">Foto 3x4</th>
                    <th class="text-center">NR-33</th>
                    <th class="text-center">NR-35</th>
                    <th class="text-center">EBTV</th>

                    <th class="text-center">Ultima Atualização</th>
                    <th class="text-center">Data Admissão</th>
                    <th class="text-center">
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="resultado in lista">
                    <td class="text-center">
                        <label :for="resultado.id">
                            <input
                                type="checkbox"
                                v-model="selecionados"
                                :value="resultado.id"
                                :id="resultado.id"
                                :style="resultado.treinamento ? 'cursor:pointer' : 'cursor: not-allowed'"
                                :title="resultado.treinamento ? null : 'Não possui treinamento'"
                                v-if="resultado.treinamento"
                            >
                            <input type="checkbox" v-else disabled="disabled" title="Candidato sem treinamento">

                        </label>
                    </td>
                    <td class="text-center">
                        <label :for="resultado.id">
                            <input
                                type="checkbox"
                                v-model="selecionadosMassa"
                                :value="resultado.id"
                                :id="resultado.id"
                                :style="resultado.id ? 'cursor:pointer' : 'cursor: not-allowed'"
                            >
                        </label>
                    </td>

                    <td>
                        @{{resultado.curriculo.nome}} <br>
                        CPF: @{{resultado.curriculo.cpf}}
                    </td>
                    <td class="text-center">
                        @{{resultado.vaga_aberta_municipio }}
                    </td>

                    <td class="text-center">
                        @{{resultado.admissao ? resultado.admissao.cargo : null}}
                    </td>

                    <td class="text-center">
                        @{{resultado.admissao ? resultado.admissao.area_etiqueta ?
                        resultado.admissao.area_etiqueta.label : null : null}}
                    </td>

                    <td class="text-center">
                        @{{resultado.curriculo.foto_tres.length > 0 ? 'SIM' : 'Não'}}
                    </td>

                    <td>
                        <template v-if="resultado.admissao && resultado.admissao.nr_trinta_tres === 'NÃO SE APLICA'">NÃO
                            SE APLICA
                        </template>
                        <template v-else>
                            <template v-if="resultado.nr_33">
                                Data Treinamento: <strong>@{{resultado.nr_33.data_treinamento}}</strong>
                                <br>
                                Data Vencimento: <strong>@{{resultado.nr_33.data_vencimento}}</strong>
                            </template>
                            <template v-else>
                                Não realizado
                            </template>
                        </template>
                    </td>

                    <td>
                        <template v-if="resultado.admissao && resultado.admissao.nr_trinta_cinco === 'NÃO SE APLICA'">
                            NÃO SE APLICA
                        </template>
                        <template v-else>
                            <template v-if="resultado.nr_35">
                                Data Treinamento: <strong>@{{resultado.nr_35.data_treinamento}}</strong>
                                <br>
                                Data Vencimento: <strong>@{{resultado.nr_35.data_vencimento}}</strong>
                            </template>
                            <template v-else>
                                Não realizado
                            </template>
                        </template>
                    </td>
                    <td>
                        <template v-if="resultado.ebtv">
                            Data Treinamento: <strong>@{{resultado.ebtv.data_treinamento}}</strong>
                            <br>
                            Data Vencimento: <strong>@{{resultado.ebtv.data_vencimento}}</strong>
                        </template>
                        <template v-else>
                            Não realizado
                        </template>
                    </td>

                    <td class="text-center">
                        @{{ resultado.treinamento ? resultado.treinamento.updated_at : null }} <br>
                        @{{ resultado.treinamento ? resultado.treinamento.quem_cadastrou.nome : null }}
                    </td>

                    <td class="text-center">
                        @{{ resultado.admissao ? resultado.admissao.data_admissao : null }}
                    </td>

                    <td class="text-center" width="30px">
                        <div class="dropdown dropleft show">
                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item" href="javascript://" title="Atualizar treinamento"
                                   @click.prevent="formAlterar(resultado.id)"
                                   data-toggle="modal"
                                   data-target="#janelaTreinamento">
                                    Atualizar
                                </a>
                                <a class="dropdown-item" href="javascript://" title="Enviar via e-mail"
                                   v-if="resultado.treinamento"
                                   @click.prevent="abriJanelaEnviar(resultado)"
                                   data-toggle="modal"
                                   data-target="#janelaAvaliar">
                                    Enviar via e-mail
                                </a>

                            </div>
                        </div>


                        {{--                        <button href="javascript://" class="btn btn-sm btn-default" title="Enviar Via e-mail"--}}
                        {{--                                v-if="resultado.carteira"--}}
                        {{--                                @click.prevent="formAlterar(resultado.curriculo_id)"--}}
                        {{--                                data-toggle="modal"--}}
                        {{--                                data-target="#janelaCadastrar">--}}
                        {{--                            <i class="fas fa-share-square"></i> Enviar--}}
                        {{--                        </button>--}}
                        {{--                        <a v-if="resultado.admissao" :href="`admissao/${resultado.curriculo.id}/pdf`"--}}
                        {{--                           class="btn btn-sm btn-default" title="Ficha"--}}
                        {{--                           target="_blank">--}}
                        {{--                            <i class="fa fa-file-pdf"></i> Ficha--}}
                        {{--                        </a>--}}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length==0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.treinamentos.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>

@stop
@push('js')
    <script src="{{mix('js/g/treinamentos/app.js')}}"></script>
@endpush
