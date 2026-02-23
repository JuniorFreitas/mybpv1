@extends('layouts.sistema')
@section('title', 'Treinamento')
@section('content_header')
    <h4 class="text-default">Treinamentos</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')

    <modal id="filtroColunas" size="g" titulo="Mostrar e Ocultar Treinamentos">
        <template slot="conteudo">
            <div class="row">
                <div class="col-sm-6" v-for="item in listaColunasTreinamentos">
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" @click="item.checked = !item.checked"
                               v-model="item.checked"
                               class="custom-control-input" :id="item.id"
                        >
                        <label class="custom-control-label"
                               :for="item.id"
                        >@{{item.label}}</label>
                    </div>
                </div>
            </div>
        </template>
        <template slot="rodape">
            <div v-if="listaColunasTreinamentos && listaColunasTreinamentos.length">
                <button class="btn btn-sm btn-primary"
                        :disabled="listaColunasTreinamentos.length === listaColunasTreinamentos.filter(item => item.checked).length"
                        @click.prevent="marcarDesmarcarTodosTreinamentosColuna(true)"
                >
                    Selecionar todos
                </button>
                <button class="btn btn-sm btn-primary"
                        :disabled="listaColunasTreinamentos.filter(item => item.checked).length === 0"
                        @click.prevent="marcarDesmarcarTodosTreinamentosColuna(false)"
                >
                    Desmarcar todos
                </button>
            </div>
        </template>
    </modal>

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
                    <legend>Dados do funcionário</legend>
                    {{--                    <div class="col-12">--}}
                    {{--                        <h6>Funcionário: @{{ form.nome }}</h6>--}}

                    {{--                    </div>--}}

                    <div class="row">
                        <div class="col-12">
                            <p>
                                Nome: <strong>@{{ form.dadosFuncionario.nome }}</strong> - @{{
                                form.dadosFuncionario.idade }} anos <br>
                                Cargo: <strong>@{{ form.dadosFuncionario.cargo }}</strong> <br>
                                E-mail: <strong>@{{ form.dadosFuncionario.email }}</strong>
                                <br>
                            </p>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Treinamentos</legend>
                    <div class="row">


                        {{--                        <div class="col-12" v-if="form.tipo">
                                                    <fieldset>
                                                        <legend>Exame</legend>
                                                        <div class="row">
                                                            <div class="col-12 col-md-6">
                                                                <div class="form-group">
                                                                    <label for="">EXAME REALIZADO?</label>
                                                                    <select class="form-control" v-model="form.exame.exame_realizado">
                                                                        <option value="">Selecione...</option>
                                                                        <option :value="true">Sim</option>
                                                                        <option :value="false">Não</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <template v-if="form.exame.exame_realizado">
                                                                <div class="col-12 col-md-6">
                                                                    <label for="">DATA DO EXAME:
                                                                    </label>
                                                                    <datepicker v-model="form.exame.data_realizado"
                                                                                max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                                                onblur="valida_data_vazio(this)"></datepicker>
                                                                </div>

                                                                <div class="col-12 col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="">TIPO DO EXAME</label>
                                                                        <select class="form-control" v-model="form.exame.tipo_exame">
                                                                            <option value="">Selecione ...</option>
                                                                            <option value="ADMISSIONAL">ADMISSIONAL</option>
                                                                            <option value="PERIODICO">PERIODICO</option>
                                                                            <option value="RETORNO AO TRABALHO">RETORNO AO TRABALHO</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12 col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="">APTO TRABALHO EM ALTURA?</label>
                                                                        <select class="form-control" v-model="form.exame.trabalho_altura">
                                                                            <option value="">Selecione ...</option>
                                                                            <option :value="true">Sim</option>
                                                                            <option :value="false">Não</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12 col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="">APTO TRABALHO EM ESPACO CONFINADO?</label>
                                                                        <select class="form-control" v-model="form.exame.espaco_confinado">
                                                                            <option value="">Selecione ...</option>
                                                                            <option :value="true">Sim</option>
                                                                            <option :value="false">Não</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                            </template>
                                                        </div>
                                                    </fieldset>
                                                </div>--}}
                        <div class="col-12 mb-2"
                             v-if="form.listaVencimentos && form.listaVencimentos.length > 0"
                        >
                            <div class="row">

                                <div class="col-md-2">
                                    <div class="card">
                                        <div class="card-body text-center py-2"
                                        >
                                            <h4>@{{ form.listaVencimentos.length }}</h4>
                                            <p class="mb-0">Todos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center py-2"
                                        >
                                            <h4>@{{ treinamentosNaoRealizados }}</h4>
                                            <p class="mb-0">Não realizados</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white"
                                    >
                                        <div class="card-body text-center py-2">
                                            <h4>@{{ treinamentosRealizados }}</h4>
                                            <p class="mb-0">Realizados em dias</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-warning text-white"
                                    >
                                        <div class="card-body text-center py-2">
                                            <h4>@{{ treinamentosVencendo }}</h4>
                                            <p class="mb-0">A Vencer</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-danger text-white"
                                    >
                                        <div class="card-body text-center py-2">
                                            <h4>@{{ treinamentosVencidos }}</h4>
                                            <p class="mb-0">Vencidos</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{--<div v-if="false" class="col-12" v-for="(treinamento, index) in form.listaVencimentos"
                             v-if="form.listaVencimentos && form.listaVencimentos.length > 0">
                            <fieldset>
                                <legend>@{{ treinamento.label }}</legend>

                                <div class="alert alert-warning p-2" style="font-size: 0.85rem;"
                                     v-show="treinamento.descricao">
                                    A quem se destina: @{{ treinamento.descricao }}
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="">Realizou este treinamento?</label>
                                            <select class="form-control" v-model="treinamento.fez_treinamento">
                                                <option :value="true">Sim</option>
                                                <option :value="false">Não</option>
                                            </select>
                                        </div>
                                    </div>

                                    <template v-if="treinamento.fez_treinamento">
                                        <template v-if="treinamento.prazo_fixo">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <datepicker v-model="treinamento.data_treinamento"
                                                                label="Data do treinamento"
                                                                max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                                onblur="valida_data_vazio(this)"></datepicker>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Vencimento (prazo fixo)</label>
                                                    <input class="form-control" readonly disabled
                                                           :value="treinamento.data_vencimento">
                                                </div>
                                            </div>
                                        </template>
                                        <template v-if="!treinamento.prazo_fixo">
                                            <datepicker v-model="treinamento.data_treinamento"
                                                        label="Data do treinamento"
                                                        max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                        onblur="valida_data_vazio(this)"></datepicker>
                                            <datepicker v-model="treinamento.data_vencimento"
                                                        label="Data Vencimento"
                                                        min="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                        onblur="valida_data_vazio(this)"></datepicker>
                                        </template>

                                    </template>

                                    --}}{{--                                    <div class="col-md-6" v-if="treinamento.fez_treinamento">--}}{{--
                                    --}}{{--                                        <div class="form-group">--}}{{--
                                    --}}{{--                                            <template v-if="treinamento.prazo_fixo && treinamento.prazo_parada">--}}{{--
                                    --}}{{--                                                --}}{{----}}{{--                                                <label for="">--}}{{--
                                    --}}{{--                                                --}}{{----}}{{--                                                    <span class="text-danger" style="font-size: 0.85rem;"--}}{{--
                                    --}}{{--                                                --}}{{----}}{{--                                                          v-show="treinamento.prazo_fixo || treinamento.prazo_parada">--}}{{--
                                    --}}{{--                                                --}}{{----}}{{--                                                     Vencimento: @{{ treinamento.data_vencimento }}--}}{{--
                                    --}}{{--                                                --}}{{----}}{{--                                                </span>--}}{{--
                                    --}}{{--                                                --}}{{----}}{{--                                                </label>--}}{{--
                                    --}}{{--                                                <datepicker v-model="treinamento.data_treinamento"--}}{{--
                                    --}}{{--                                                            label="Data do treinamento"--}}{{--
                                    --}}{{--                                                            max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"--}}{{--
                                    --}}{{--                                                            onblur="valida_data_vazio(this)"></datepicker>--}}{{--
                                    --}}{{--                                            </template>--}}{{--

                                    --}}{{--                                                                                <template v-if="!treinamento.prazo_fixo && !treinamento.prazo_parada">--}}{{--
                                    --}}{{--                                                                                    <datepicker v-model="treinamento.data_treinamento"--}}{{--
                                    --}}{{--                                                                                                label="Data do treinamento"--}}{{--
                                    --}}{{--                                                                                                max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"--}}{{--
                                    --}}{{--                                                                                                onblur="valida_data_vazio(this)"></datepicker>--}}{{--
                                    --}}{{--                                                                                    <datepicker v-model="treinamento.data_vencimento"--}}{{--
                                    --}}{{--                                                                                                label="Data Vencimento"--}}{{--
                                    --}}{{--                                                                                                min="{{ (new \MasterTag\DataHora())->dataCompleta() }}"--}}{{--
                                    --}}{{--                                                                                                onblur="valida_data_vazio(this)"></datepicker>--}}{{--
                                    --}}{{--                                                                                </template>--}}{{--


                                    --}}{{--                                            --}}{{----}}{{--                                            <span class="text-muted" style="font-size: 0.85rem;"--}}{{--
                                    --}}{{--                                            --}}{{----}}{{--                                                  v-show="treinamento.prazo_fixo && form.tipo==='Parada'">--}}{{--
                                    --}}{{--                                            --}}{{----}}{{--                                                    Informe a data do treinamento o sistema colocara o vencimento automatico--}}{{--
                                    --}}{{--                                            --}}{{----}}{{--                                            </span>--}}{{--

                                    --}}{{--                                            --}}{{----}}{{--                                            <span class="text-muted" style="font-size: 0.85rem;"--}}{{--
                                    --}}{{--                                            --}}{{----}}{{--                                                  v-show="!treinamento.prazo_fixo && !treinamento.prazo_parada">--}}{{--
                                    --}}{{--                                            --}}{{----}}{{--                                                Informe o vencimento final--}}{{--
                                    --}}{{--                                            --}}{{----}}{{--                                            </span>--}}{{--
                                    --}}{{--                                        </div>--}}{{--
                                    --}}{{--                                    </div>--}}{{--
                                    <div class="col-12" v-if="treinamento.fez_treinamento">
                                        <fieldset>
                                            <legend>FAT</legend>
                                            <div class="form-group">
                                                <label for="">Numero da FAT</label>
                                                <input type="text" class="form-control"
                                                       v-model="treinamento.numero_fat">
                                            </div>

                                            <upload :model="treinamento.arquivo"
                                                    :model-delete="treinamento.arquivoDel"
                                                    :url="url_anexo"
                                                    :quantidade="1"
                                                    :multi="false"
                                                    label="ANEXAR FAT"
                                                    @onProgresso="anexoUploadAndamento=true"
                                                    @onFinalizado="anexoUploadAndamento=false"></upload>
                                        </fieldset>
                                    </div>
                                </div>
                            </fieldset>
                        </div>--}}

                    </div>

                    <div class="mb-4" v-if="form.listaVencimentos && form.listaVencimentos.length > 0">
                        <div class="input-group input-group">
                            <input type="text" class="form-control" placeholder="Buscar treinamento..."
                                   v-model="trainingSearchQuery"
                            >
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion de treinamentos -->
                    <div class="accordion" id="accordionTreinamentos"
                         v-if="treinamentosFiltrados.length > 0"
                    >
                        <div v-for="(treinamento, index) in treinamentosFiltrados"
                             :key="index"
                             class="card mb-3"
                             :class="{
                             'border-left-success': getTreinamentoStatus(treinamento) === 'ativo' && treinamento.fez_treinamento,
                             'border-left-warning': getTreinamentoStatus(treinamento) === 'avencer' && treinamento.fez_treinamento,
                             'border-left-danger': getTreinamentoStatus(treinamento) === 'vencido' && treinamento.fez_treinamento,
                             'border-left-secondary': !treinamento.fez_treinamento
                         }"
                        >
                            <div class="card-header d-flex justify-content-between align-items-center"
                                 :class="{
                                 'bg-success-light': getTreinamentoStatus(treinamento) === 'ativo' && treinamento.fez_treinamento,
                                 'bg-warning-light': getTreinamentoStatus(treinamento) === 'avencer' && treinamento.fez_treinamento,
                                 'bg-danger-light': getTreinamentoStatus(treinamento) === 'vencido' && treinamento.fez_treinamento,
                                 'bg-light': !treinamento.fez_treinamento
                             }"
                            >
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-dark"
                                            type="button"
                                            @click="togglePanel(index)"
                                            style="text-decoration: none; text-align: left;"
                                    >
                                        <i class="fa"
                                           :class="openPanels.includes(index) ? 'fa-chevron-down' : 'fa-chevron-right'"
                                        ></i>
                                        @{{ treinamento.label }}
                                    </button>
                                </h5>
                                <span class="badge"
                                      :class="{
                                      'badge-success': getTreinamentoStatus(treinamento) === 'ativo' && treinamento.fez_treinamento,
                                      'badge-warning': getTreinamentoStatus(treinamento) === 'avencer' && treinamento.fez_treinamento,
                                      'badge-danger': getTreinamentoStatus(treinamento) === 'vencido' && treinamento.fez_treinamento,
                                      'badge-secondary': !treinamento.fez_treinamento
                                  }"
                                >
                                @{{ getStatusText(treinamento) }}
                            </span>
                            </div>

                            <div class="collapse" :class="{ show: openPanels.includes(index) }">
                                <div class="card-body">
                                    <div class="alert alert-warning p-2" style="font-size: 0.85rem;"
                                         v-show="treinamento.descricao"
                                    >
                                        <strong>A quem se destina:</strong> @{{ treinamento.descricao }}
                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label>Realizou este treinamento?</label>
                                                <select class="form-control" v-model="treinamento.fez_treinamento">
                                                    <option :value="true">Sim</option>
                                                    <option :value="false">Não</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" v-if="treinamento.fez_treinamento">
                                        <!-- Datas de treinamento e vencimento (somente prazo fixo) -->
                                        <template v-if="treinamento.prazo_fixo">
                                            <div class="col-md-6 mt-2">
                                                <datepicker v-model="treinamento.data_treinamento"
                                                            label="Data do treinamento"
                                                            @input="calculoDataExpiracao(treinamento)"
                                                            max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                            onblur="valida_data_vazio(this)"
                                                ></datepicker>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mt-2">
                                                    <label>Vencimento (prazo fixo)</label>
                                                    <input class="form-control" readonly disabled
                                                           :value="treinamento.data_vencimento"
                                                    >
                                                </div>
                                            </div>
                                        </template>
                                        <template v-if="!treinamento.prazo_fixo">
                                            <div class="col-md-6 mt-2">
                                                <datepicker v-model="treinamento.data_treinamento"
                                                            label="Data do treinamento"
                                                            max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                            onblur="valida_data_vazio(this)"
                                                ></datepicker>
                                            </div>
                                            <div class="col-md-6 mt-2">
                                                <datepicker v-model="treinamento.data_vencimento"
                                                            label="Data Vencimento"
                                                            min="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                            onblur="valida_data_vazio(this)"
                                                ></datepicker>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Informações da FAT -->
                                    <fieldset class="mt-3" v-if="treinamento.fez_treinamento">
                                        <legend>FAT</legend>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Número da FAT</label>
                                                    <input type="text" class="form-control"
                                                           v-model="treinamento.numero_fat"
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <upload :model="treinamento.arquivo"
                                                        :model-delete="treinamento.arquivoDel"
                                                        :url="url_anexo"
                                                        :quantidade="1"
                                                        :multi="false"
                                                        label="ANEXAR FAT"
                                                        @onProgresso="anexoUploadAndamento=true"
                                                        @onFinalizado="anexoUploadAndamento=false"
                                                ></upload>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Mensagem de nenhum treinamento encontrado -->
                <div class="alert alert-info"
                     v-if="form.listaVencimentos && form.listaVencimentos.length > 0 && (!treinamentosFiltrados || treinamentosFiltrados.length === 0)"
                >
                    <i class="fa fa-info-circle"></i> Nenhum treinamento encontrado com os filtros atuais.
                </div>
            </div>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-primary" @click="salvar"
                    v-if="!preload && (!cadastrado && !atualizado)"
            >
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
                        <div class="col-12 col-md-6" v-for="(treinamento, index) in formMassa.listaVencimentos"
                             v-if="formMassa.listaVencimentos && formMassa.listaVencimentos.length > 0"
                        >
                            <fieldset>
                                <legend>@{{ treinamento.label }}</legend>

                                <div class="alert alert-warning p-2" style="font-size: 0.85rem;"
                                     v-show="treinamento.descricao"
                                >
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
                                            <template v-if="treinamento.prazo_fixo">
                                                <label for="">Data do treinamento:
                                                    <span class="text-danger" style="font-size: 0.85rem;">
                                                     Vencimento: @{{ treinamento.data_vencimento }}
                                                </span>
                                                </label>
                                                <datepicker v-model="treinamento.data_treinamento"
                                                            max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"
                                                            onblur="valida_data_vazio(this)"
                                                ></datepicker>
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
                    v-if="!preload && formMassa.listaVencimentos && formMassa.listaVencimentos.length > 0 && (!cadastrado && !atualizado)"
            >
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
                                   v-model="formEnviar.email"
                            >
                        </div>
                    </div>

                </div>
            </fieldset>
        </template>
        <template slot="rodape">
            <div v-show="!formEnviar.preload">
                <button type="button" class="btn btn-sm btn-primary"
                        @click="enviar"
                        v-show="!formEnviar.enviado"
                >
                    <i class="fa fa-envelope"></i> Enviar
                </button>
            </div>
        </template>
    </modal>

    <modal id="janelaEnviarAviso" :fechar="!formEnviarAviso.preload"
           titulo="Notificação de treinamento próximo ao vencimento"
    >
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
                                   v-model="formEnviarAviso.email"
                            >
                        </div>
                    </div>

                </div>
            </fieldset>
        </template>
        <template slot="rodape">
            <div v-show="!formEnviarAviso.preload">
                <button type="button" class="btn btn-sm btn-primary"
                        @click="enviarAviso"
                        v-show="!formEnviarAviso.enviado"
                >
                    <i class="fa fa-envelope"></i> Enviar
                </button>
            </div>
        </template>
    </modal>

    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <div class="row">
            <div class="col-12 col-lg-3">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" @change="atualizar()"
                           :disabled="controle.carregando"
                           id="filtroVencimento"
                           v-model="controle.dados.campoVencimento"
                    >
                    <label class="form-check-label cursor-pointer" for="filtroVencimento">Por período de
                        vencimento</label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label="" @onselect="atualizar()"
                                :disabled="controle.carregando"
                                v-model="controle.dados.vencimento"
                    ></datepicker>
                </div>
            </div>

            <div class="col-12 col-lg-3 ">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" @change="atualizar()"
                           :disabled="controle.carregando"
                           id="filtroPeriodoTreinado"
                           v-model="controle.dados.campoPeriodoTreinado"
                    >
                    <label class="form-check-label cursor-pointer" for="filtroPeriodoTreinado">Por período
                        treinado</label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label="" @onselect="atualizar()"
                                :disabled="controle.carregando"
                                v-model="controle.dados.periodoTreinado"
                    ></datepicker>
                </div>
            </div>

            <div class="col-12 col-lg-3">
                <label>Admitidos</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoAdmitido"
                >
                    <option value="">Geral</option>
                    <option value="S">Sim</option>
                    <option value="N">Não</option>
                </select>
            </div>

            <div class="col-12 col-sm-2">
                <div class="form-group">
                    <label>Por Demitido</label>
                    <select class="form-control form-control-sm"
                            @change="atualizar"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoDemitido"
                    >
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-lg-3">
                <label>Treinados</label>
                <select class="custom-select custom-select-sm" @change="selecionaTreinados($event.target.value)"
                        :disabled="controle.carregando"
                        v-model="controle.dados.campo_treinados"
                >
                    <option value="">Sem filtro</option>
                    <option value="S">Sim</option>
                    <option value="N">Não</option>
                </select>
            </div>


            <div class="col-12 col-lg-4 mb-3">
                <label>Buscar</label>
                <input type="text"
                       placeholder="Buscar por nome"
                       autocomplete="off"
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoBusca"
                >
            </div>

            <div class="col-12 col-lg-3 mb-3">
                <label>CPF</label>
                <input type="text"
                       placeholder="Buscar por cpf"
                       autocomplete="mastertag"
                       v-mascara:cpf
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoCPF"
                >
            </div>

            <div class="col-12 col-lg-3 mb-3">
                <label>Foto 3x4</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoFoto"
                >
                    <option value="">Geral</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>

            <div class="col-12 col-lg-2 mb-3">
                <label>Nº Crachá</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoCracha"
                >
                    <option value="">Geral</option>
                    <option value="S">Sim</option>
                    <option value="N">Não</option>
                </select>
            </div>

            <div class="col-12 col-lg-4 mb-3"
                 v-if="lista_ccs && AUTENTICADO.temFilial"
            >
                <label for="">Por Cnpj</label>
                <select class="form-control form-control-sm" @change="changeCnpj"
                        :disabled="controle.carregando"
                        v-model="controle.dados.campoCnpj"
                >
                    <option value="">Todos</option>
                    <option v-for="(item, key) in lista_ccs.cnpjs" :value="key" :keys="key">
                        @{{item.nome_fantasia}} - @{{item.cnpj}}
                    </option>
                </select>
            </div>

            <div class="col-12 mb-3" :class="AUTENTICADO.temFilial ? 'col-lg-3' : 'col-lg-5'" v-if="lista_ccs">
                <label for="">Centro de Custo</label>
                <select class="form-control form-control-sm" @change="atualizar"
                        :disabled="controle.carregando"
                        v-model="controle.dados.campoCentroCusto"
                >
                    <option value="">Todos</option>
                    <option :title="item.label" v-for="(item, key) in filtroListaCentroCustoCnpj"
                            :value="item.matriz ? item.id : item.filial_id"
                            :keys="key"
                    >
                        @{{item.label}}
                    </option>
                    <option value="--naoinformado--">--- Não Informado ---</option>
                </select>
            </div>


            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Por Vaga</label>
                    <autocomplete :disabled="controle.carregando" :caminho="controle.dados.caminho_autocomplete"
                                  :valido="controle.dados.campoVaga !== ''"
                                  v-model="controle.dados.autocomplete_label"
                                  placeholder="Por vaga"
                                  @onblur="resetaCampo"
                                  @onselect="selecionaVaga"
                    ></autocomplete>
                </div>
            </div>

            <div class="col-12 mb-3" :class="AUTENTICADO.temFilial ? 'col-lg-3' : 'col-lg-5'">
                <label>Cargo</label>
                <input type="text"
                       placeholder="Buscar por cargo"
                       autocomplete="off"
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoCargo"
                >
            </div>

            <div class="col-12 col-lg-2 mb-3">
                <label>Estados</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoUf"
                >
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

            <div class="col-12">
                <label>Treinamentos</label>
                <select class="custom-select custom-select-sm" @change="addTreinamento($event.target.value)"
                        :disabled="controle.carregando"
                        v-model="controle.dados.treinamentos"
                >
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
                        <small class="p-2 ml-2 mb-2 table-secondary text-dark rounded"
                               v-for="(item, ind) in controle.dados.treinamentos_selecionados"
                        >
                            @{{ item }} <a href="javascript://" @click.prevent="removeTreinamento(ind)"><i
                                    class="fa fa-times ml-1"
                                ></i></a>
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
                       v-model="controle.dados.campoBusca"
                >
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <label>CPF</label>
                <input type="text"
                       placeholder="Buscar por cpf"
                       autocomplete="mastertag"
                       v-mascara:cpf
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoCPF"
                >
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="form-group">
                    <label>Por Vaga</label>
                    <autocomplete :disabled="controle.carregando" :caminho="controle.dados.caminho_autocomplete"
                                  :valido="controle.dados.campoVaga !== ''"
                                  v-model="controle.dados.autocomplete_label"
                                  placeholder="Por vaga"
                                  @onblur="resetaCampo"
                                  @onselect="selecionaVaga"
                    ></autocomplete>
                </div>
            </div>


            @if(!Request::has('cliente_id'))
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="form-group">
                        <label>Por Cliente</label>
                        <autocomplete :disabled="controle.carregando"
                                      :caminho="controle.dados.caminho_cliente_autocomplete"
                                      :valido="controle.dados.campoCliente !== ''"
                                      v-model="controle.dados.autocomplete_label_cliente"
                                      placeholder="Por cliente"
                                      @onblur="resetaCampoCliente"
                                      @onselect="selecionaCliente"
                        ></autocomplete>
                    </div>
                </div>
            @endif

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <label>Áreas</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoArea"
                >
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
                       v-model="controle.dados.campoCargo"
                >
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                <label>Estado</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoUf"
                >
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
                        v-model="controle.dados.campo_treinados"
                >
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
                           v-model="controle.dados.campoVencimento"
                    >
                    <label class="form-check-label cursor-pointer" for="filtroVencimento">
                        Por período de vencimento
                    </label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label="" @onselect="atualizar()"
                                :disabled="controle.carregando"
                                v-model="controle.dados.vencimento"
                    ></datepicker>
                </div>
            </div>


            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>NR33</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoNr_trinta_tres"
                >
                    <option value="">Sem filtro</option>
                    <option value="Realizado">Realizado</option>
                    <option value="Não Realizado">Não Realizado</option>
                    <option value="NÃO SE APLICA">Não se aplica</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">

                <label>NR35</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoNr_trinta_cinco"
                >
                    <option value="">Sem filtro</option>
                    <option value="Realizado">Realizado</option>
                    <option value="Não Realizado">Não Realizado</option>
                    <option value="NÃO SE APLICA">Não se aplica</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>EBTV</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoNr_ebtv"
                >
                    <option value="">Sem filtro</option>
                    <option :value="true">Realizado</option>
                    <option :value="false">Não Realizado</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>Admitidos</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoAdmitido"
                >
                    <option value="">Geral</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>


            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>Nº Crachá</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoCracha"
                >
                    <option value="">Geral</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>Foto 3x4</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoFoto"
                >
                    <option value="">Geral</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>PCD</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoPcd"
                >
                    <option value="">Geral</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <label>Exibir</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.pages"
                >
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
                        :style="controle.carregando ? 'cursor: not-allowed' : 'cursor: pointer'" @click="atualizar"
                >
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                {{--                <form target="_blank" action="{{ route('g.treinamentos.carteiraPdf') }}" method="post">--}}
                {{--                    @csrf--}}
                {{--                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">--}}
                {{--                    <button type="submit" class="btn btn-sm btn-primary mr-1"--}}
                {{--                            :style="!selecionados.length ? 'cursor: not-allowed' : 'cursor: pointer'"--}}
                {{--                            :disabled="!selecionados.length">--}}
                {{--                        Gerar Carteira <span class="badge badge-light">@{{ selecionados.length }}</span>--}}
                {{--                    </button>--}}
                {{--                </form>--}}

                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle mr-1"
                            type="button"
                            id="dropdownCarteira"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            :style="!selecionados.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                            :disabled="!selecionados.length"
                    >
                        Gerar Carteira <span class="badge badge-light">@{{ selecionados.length }}</span>
                    </button>

                    <div class="dropdown-menu" aria-labelledby="dropdownCarteira">
                        <form target="_blank" action="{{ route('g.treinamentos.carteiraPdf') }}" method="post"
                              style="display: inline;"
                        >
                            @csrf
                            <input type="hidden" name="tipo" value="treinamento">
                            <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                            <button type="submit"
                                    class="dropdown-item"
                                    :disabled="!selecionados.length"
                            >
                                <i class="fas fa-graduation-cap mr-2"></i>Treinamento
                            </button>
                        </form>

                        <form target="_blank" action="{{ route('g.treinamentos.carteiraPdf') }}" method="post"
                              style="display: inline;"
                        >
                            @csrf
                            <input type="hidden" name="tipo" value="bloqueio">
                            <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                            <button type="submit"
                                    class="dropdown-item"
                                    :disabled="!selecionados.length"
                            >
                                <i class="fas fa-ban mr-2"></i>Bloqueio
                            </button>
                        </form>

                        <form target="_blank" action="{{ route('g.treinamentos.carteiraPdf') }}" method="post"
                              style="display: inline;"
                        >
                            @csrf
                            <input type="hidden" name="tipo" value="treinamento_bloqueio">
                            <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                            <button type="submit"
                                    class="dropdown-item"
                                    :disabled="!selecionados.length"
                            >
                                <i class="fas fa-list mr-2"></i>Treinamento/Bloqueio
                            </button>
                        </form>
                    </div>
                </div>

                <button class="btn btn-sm btn-danger mb-1 mr-1"
                        :style="!selecionados.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="!selecionados.length" @click="selecionados = []"
                >
                    <i class="fa fa-times"></i> Limpar seleção
                </button>

                <button class="btn btn-sm btn-primary mb-1 mr-1" v-if="false"
                        :style="!selecionadosMassa.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                        @click.pre.prevent="abrirFormMassa()"
                        data-toggle="modal"
                        data-target="#janelaTreinamentoMassa"
                        :disabled="!selecionadosMassa.length"
                >
                    <i class="fa fa-plus"></i> Atualizar em massa <span class="badge badge-light">@{{ selecionadosMassa.length }}</span>
                </button>

                <button class="btn btn-sm btn-danger mb-1 mr-1" v-if="false"
                        :style="!selecionadosMassa.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="!selecionadosMassa.length" @click="selecionadosMassa = []"
                >
                    <i class="fa fa-times"></i> Limpar seleção em massa
                </button>

                <button type="button" class="btn btn-sm btn-primary mb-1 mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando || preloadExportacao || lista.length===0"
                >
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL <span class="badge badge-light"
                                                                           v-show="selecionados.length > 0"
                    >@{{ selecionados.length }}</span>
                </button>

                {{--                @if (auth()->user()->cliente_id != \App\Models\User::BPSE)--}}
                {{--                    <button class="btn btn-sm btn-primary ml-1" @click.prevent="abriJanelaEnviarAviso"--}}
                {{--                            data-toggle="modal"--}}
                {{--                            data-target="#janelaEnviarAviso">--}}
                {{--                        <i class="fa fa-envelope"></i> Notificação--}}
                {{--                    </button>--}}
                {{--                @endif--}}
            </div>
        </div>

    </fieldset>

    <p class="text-center" v-if="controle.carregando">
        <i class="fa fa-spinner fa-pulse"></i> Carregando...
    </p>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length==0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <div v-show="!controle.carregando && lista.length > 0">
            <!-- Cabeçalho com checkboxes "selecionar todos" e filtro -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="custom-control custom-checkbox mr-3">
                                <input type="checkbox" class="custom-control-input" id="checkAllMain"
                                       :style="!emTreinamentos.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                                       :disabled="!emTreinamentos.length" :checked="tudoMarcado"
                                       @click="selecionaTodos"
                                >
                                <label class="custom-control-label" for="checkAllMain">Selecionar todos</label>
                            </div>
                        </div>

                        <div class="col-auto">
                            <div class="card-body py-2">
                                <div class="d-flex flex-wrap">
                                    <div class="mr-4 mb-1">
                                        <span class="badge badge-success mr-1">●</span> Em dia
                                    </div>
                                    <div class="mr-4 mb-1">
                                        <span class="badge badge-warning mr-1">●</span> Vencendo
                                    </div>
                                    <div class="mr-4 mb-1">
                                        <span class="badge badge-danger mr-1">●</span> Vencido
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col text-right">
                            <button class="btn btn-sm btn-primary" content="Mostrar e Ocultar Treinamentos" v-tippy
                                    data-toggle="modal" data-target="#filtroColunas"
                            >
                                <i class="bx bxs-filter-alt" aria-hidden="true"></i> Filtrar treinamentos
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards dos colaboradores -->
            <div class="row">
                <div class="col-12 mb-3" v-for="(item, key) in lista" :key="item.id">
                    <!-- Card principal do colaborador -->
                    <div class="card shadow-sm">
                        <div class="card-header  py-2"
                             :class="{
                        'bg-danger': item.admissao && ['Demitido','DEMITIDO'].includes(item.admissao.status),
                        'bg-white': item.admissao &&  !['Demitido','DEMITIDO'].includes(item.admissao.status),
                        }"
                        >
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="custom-control custom-checkbox" style="padding-left: 0px">
                                        <input
                                            type="checkbox"
                                            v-model="selecionados"
                                            :value="item.id"
                                            :id="item.id"
                                            :style="item.treinamento ? 'cursor:pointer' : 'cursor: not-allowed'"
                                            :title="item.treinamento ? null : 'Não possui treinamento'"
                                            v-if="item.treinamento && item.treinamento.vencimentos.length"
                                        >
                                    </div>
                                </div>

                                <div class="col">
                                    <h5 class="mb-0">@{{item.curriculo.nome}}</h5>
                                    <small class="text-muted">CPF: @{{item.curriculo.cpf}}</small>
                                </div>
                                <div class="col"
                                     v-if="item.admissao && ['Demitido','DEMITIDO'].includes(item.admissao.status)"
                                >
                                    <h5 class="mb-0">DEMITIDO</h5>
                                </div>
                                <div class="col-auto">
                                    <div class="dropdown dropleft">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle"
                                                id="dropdownMenuLink"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        >
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-custom"
                                             aria-labelledby="dropdownMenuLink"
                                        >
                                            <a class="dropdown-item" href="javascript://" title="Atualizar treinamento"
                                               @click.prevent="formAlterar(item.id)"
                                               data-toggle="modal"
                                               data-target="#janelaTreinamento"
                                            >
                                                <i class="fas fa-edit fa-fw mr-1"></i> Atualizar treinamento
                                            </a>
                                            {{--                                            <a class="dropdown-item" href="javascript://" title="Enviar via e-mail"--}}
                                            {{--                                               v-if="item.treinamento"--}}
                                            {{--                                               @click.prevent="abriJanelaEnviar(item)"--}}
                                            {{--                                               data-toggle="modal"--}}
                                            {{--                                               data-target="#janelaAvaliar">--}}
                                            {{--                                                Enviar via e-mail--}}
                                            {{--                                            </a>--}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Cargo:</strong> @{{item.vaga_aberta.vaga.nome}}</p>
                                </div>
                                <div class="col-md-4" v-if="AUTENTICADO.temFilial">
                                    <p class="mb-1">
                                        <strong>Empresa:</strong>
                                        <span v-if="item.admissao && item.admissao.emp_cnpj">
                                    @{{item.admissao.emp_nome_fantasia}} (@{{item.admissao.emp_tipo}})
                                </span>
                                        <span v-else>---</span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1">
                                        <strong>Centro de Custo:</strong>
                                        <span v-if="item.admissao && item.admissao.emp_centro_custo">
                                    @{{item.admissao.emp_centro_custo}}
                                </span>
                                        <span v-else>---</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tabela de treinamentos -->
                        <div v-if="item.treinamento && item.treinamento.vencimentos.length" class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                <tr class="bg-light border-left-secondary">
                                    <th>Treinamento</th>
                                    <th>Data Treinamento</th>
                                    <th>Data Vencimento</th>
                                    <th>Anexo FAT</th>
                                    <th>Status</th>
                                    <th>Exibi na Carteira</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="v in item.treinamento.vencimentos" :key="v.id"
                                    :class="v.pivot.status.corBorder"
                                    v-if="isColunaTreinamentoSelecionada(v)"
                                >
                                    <td>@{{ v.label }}</td>
                                    <td>@{{ v.pivot.data_treinamento }}</td>
                                    <td>@{{ v.pivot.data_vencimento }}</td>
                                    <td>
                                        <i class="fa fa-paperclip" v-show="v.pivot.arquivo_id"></i>
                                        <i class="fa fa-minus" v-show="!v.pivot.arquivo_id"></i>
                                    </td>
                                    <td>
                                    <span class="badge" :class="v.pivot.status.badge">
                                        @{{ v.pivot.status.label }}
                                    </span>
                                    </td>
                                    <td>@{{ v.exibir_na_carteira ? 'Sim' : 'Não' }}</td>
                                </tr>
                                <tr v-if="!item.treinamento.vencimentos.some(v => isColunaTreinamentoSelecionada(v))">
                                    <td colspan="5" class="text-center py-3">
                                        <em>Nenhum treinamento visível com os filtros atuais</em>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="card-footer text-muted">
                            <em>Não há treinamentos registrados para este colaborador</em>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{--<div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="table table-centered bg-white">
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
                    <th class="text-center">Cargo</th>
                    <th class="text-center" v-if="AUTENTICADO.temFilial">CNPJ</th>
                    <th class="text-center">Centro de Custo</th>
                    <th class="text-center">
                        <button class="btn btn-sm btn-primary mb-2" content="Mostrar e Ocultar Treinamentos" v-tippy
                                data-toggle="modal"
                                data-target="#filtroColunas">
                            <i class="bx bxs-filter-alt" aria-hidden="true"></i>
                        </button>
                    </th>
                </tr>
                </thead>
                <tbody>
                <template v-for="(item, key) in lista">
                    <tr>
                        <td class="text-center">
                            <label :for="item.id">
                                <input
                                    type="checkbox"
                                    v-model="selecionados"
                                    :value="item.id"
                                    :id="item.id"
                                    :style="item.treinamento ? 'cursor:pointer' : 'cursor: not-allowed'"
                                    :title="item.treinamento ? null : 'Não possui treinamento'"
                                    v-if="item.treinamento"
                                >
                                <input type="checkbox" v-else disabled="disabled" title="Candidato sem treinamento">

                            </label>
                        </td>
                        <td class="text-center">
                            <label :for="item.id">
                                <input
                                    type="checkbox"
                                    v-model="selecionadosMassa"
                                    :value="item.id"
                                    :id="item.id"
                                    :style="item.id ? 'cursor:pointer' : 'cursor: not-allowed'"
                                >
                            </label>
                        </td>
                        <td class="text-center">
                            @{{item.curriculo.nome}} <br>
                            CPF: @{{item.curriculo.cpf}}
                        </td>
                        <td class="text-center">
                            @{{item.vaga_aberta.vaga.nome }}
                        </td>

                        <td class="text-center"
                            v-if="AUTENTICADO.temFilial"
                        >
                        <span v-if="item.admissao && item.admissao.emp_cnpj">
                            @{{item.admissao.emp_nome_fantasia}}<br>
                            (@{{item.admissao.emp_tipo}})
                        </span>
                            <span v-else>---</span>
                        </td>

                        <td class="text-center">
                        <span v-if="item.admissao && item.admissao.emp_centro_custo">
                             @{{item.admissao.emp_centro_custo}}
                        </span>
                            <span v-else>---</span>
                        </td>
                        <td>
                            <div class="dropdown dropleft show">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                   id="dropdownMenuLink"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-custom" aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="javascript://" title="Atualizar treinamento"
                                       @click.prevent="formAlterar(item.id)"
                                       data-toggle="modal"
                                       data-target="#janelaTreinamento">
                                        Atualizar
                                    </a>
                                    <a class="dropdown-item" href="javascript://" title="Enviar via e-mail"
                                       v-if="item.treinamento"
                                       @click.prevent="abriJanelaEnviar(item)"
                                       data-toggle="modal"
                                       data-target="#janelaAvaliar">
                                        Enviar via e-mail
                                    </a>

                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="item.treinamento && item.treinamento.vencimentos.length">
                        <td colspan="7">
                            <table class="table mb-0">
                                <thead>
                                <tr class="bg-light border-left-secondary">
                                    <th>Treinamento</th>
                                    <th>Data Treinamento</th>
                                    <th>Data Vencimento</th>
                                    <th>Status</th>
                                    <th> Exibi na Carteira</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Treinamento 1 (Válido) -->
                                <tr v-for="v in item.treinamento.vencimentos" :key="v.id"
                                    :class="v.pivot.status.corBorder">
                                    <template v-if="isColunaTreinamentoSelecionada(v)">
                                        <td>@{{ v.label }}</td>
                                        <td>@{{ v.pivot.data_treinamento }}</td>
                                        <td>@{{ v.pivot.data_vencimento }}</td>
                                        <td><span class="badge" :class="v.pivot.status.badge">@{{ v.pivot.status.label }}</span>
                                        </td>
                                        <td>@{{ v.exibir_na_carteira ? 'Sim' : 'Não' }}</td>

                                    </template>
                                </tr>

                                </tbody>
                            </table>
                            <table class="table table-bordered table-sm table-secondary" cellspacing="0"
                                   cellpadding="0" v-if="false">
                                <tr>
                                    <th class="text-center">Treinamento</th>
                                    <th class="text-center">Data Treinamento</th>
                                    <th class="text-center">Data Vencimento</th>
                                    <th class="text-center">Exibi na Carteira</th>
                                </tr>
                                <tr v-for="v in item.treinamento.vencimentos" :key="v.id">
                                    <template v-if="isColunaTreinamentoSelecionada(v)">
                                        <td class="text-center">@{{ v.label }}</td>
                                        <td class="text-center">@{{ v.pivot.data_treinamento }}</td>
                                        <td class="text-center">@{{ v.pivot.data_vencimento }}</td>
                                        <td class="text-center">@{{ v.exibir_na_carteira ? 'Sim' : 'Não' }}</td>
                                    </template>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>--}}

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.treinamentos.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"
        ></controle-paginacao>
    </div>

@stop
@push('css')
    <style>
        /* Estilos para status dos treinamentos */
        .border-left-success {
            border-left: 4px solid #28a745 !important;
        }

        .border-left-warning {
            border-left: 4px solid #ffc107 !important;
        }

        .border-left-danger {
            border-left: 4px solid #dc3545 !important;
        }

        .border-left-secondary {
            border-left: 4px solid #6c757d !important;
        }

        /* Backgrounds suaves para headers dos cards */
        .bg-success-light {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .bg-danger-light {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        /* Estilos para botões de accordion */
        .card-header .btn-link:focus,
        .card-header .btn-link:hover {
            text-decoration: none;
            box-shadow: none;
        }

        /* Animação para ícones de expansão */
        .fa-chevron-down, .fa-chevron-right {
            transition: transform 0.2s ease-in-out;
        }


    </style>
@endpush
@push('js')
    <script src="{{mix('js/g/treinamentos/app.js')}}"></script>
@endpush
