@extends('layouts.sistema')
@section('title', 'Certificado')
@section('content_header')
    <h4 class="text-default">Certificado</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <input type="hidden" id="cliente_id" value="{{Request::has('cliente_id') ? Request::query('cliente_id') : null}}">

    <modal ref="confirmacaoGeracao" id="confirmacaoGeracao" titulo="Confirmação de impressão de Certificados">
        <template #conteudo>
            <div class="form-check">
                <input type="checkbox" v-model="nr33" class="form-check-input" id="checknr33">
                <label class="form-check-label" for="checknr33">NR-33</label>
            </div>
            <div class="form-check">
                <input type="checkbox" v-model="nr35" class="form-check-input" id="checknr35">
                <label class="form-check-label" for="checknr35">NR-35</label>
            </div>
        </template>
        <template #rodape>
            <form target="_blank" action="{{ route('g.certificados.certificadoPdf') }}" method="post">
                @csrf
                <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                <input type="hidden" name="nr33" :value="nr33">
                <input type="hidden" name="nr35" :value="nr35">
                <button type="submit" :disabled="!nr33 && !nr35" class="btn btn-primary"><i class="fa fa-print"></i>
                    Imprimir
                </button>
            </form>
        </template>
    </modal>
    <modal ref="janelaTreinamento" id="janelaTreinamento" titulo="Certificado" size="g">
        <template #conteudo>
            <div class="alert alert-success text-center" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i> Dados para o certificado atualizado com sucesso</h4>
            </div>
            <p class=" mt-2 text-center" v-if="preload">
                <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
            </p>

            <div v-if="!preload && (!cadastrado && !atualizado)">

                <fieldset v-if="editando">
                    <legend>DADOS</legend>
                    <span>Funcionário: @{{ form.feedback.curriculo.nome }}</span><br>
                    <span>Empresa: @{{ form.feedback.cliente.nome_fantasia }}</span><br>
                    <span>Vaga: @{{ form.feedback.vaga_selecionada.nome }}</span>
                </fieldset>

                <fieldset v-if="form.nr_trinta_tres">
                    <legend>NR-33</legend>
                    <div class="form-group">
                        <label>Local de Treinamento</label>
                        <select class="form-control" v-model="form.certificado.empresa_treinamento_trinta_tres_id"
                                onchange="valida_campo_vazio(this,1)"
                                onblur="valida_campo_vazio(this,1)">
                            <option value="">Selecione ...</option>
                            @foreach(\App\Models\EmpresaTreinamento::get() as $linha)
                                <option value="{{ $linha->id }}">{{ $linha->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Instrutor</label>
                        <select class="form-control" v-model="form.certificado.instrutor_trinta_tres_id"
                                onchange="valida_campo_vazio(this,1)"
                                onblur="valida_campo_vazio(this,1)">
                            <option value="">Selecione ...</option>
                            @foreach(\App\Models\Instrutor::get() as $linha)
                                <option value="{{ $linha->id }}">{{ $linha->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>

                <fieldset v-if="form.nr_trinta_cinco">
                    <legend>NR-35</legend>
                    <div class="form-group">
                        <label>Local de Treinamento</label>
                        <select class="form-control" v-model="form.certificado.empresa_treinamento_trinta_cinco_id"
                                onchange="valida_campo_vazio(this,1)"
                                onblur="valida_campo_vazio(this,1)">
                            <option value="">Selecione ...</option>
                            @foreach(\App\Models\EmpresaTreinamento::get() as $linha)
                                <option value="{{ $linha->id }}">{{ $linha->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Instrutor</label>
                        <select class="form-control" v-model="form.certificado.instrutor_trinta_cinco_id"
                                onchange="valida_campo_vazio(this,1)"
                                onblur="valida_campo_vazio(this,1)">
                            <option value="">Selecione ...</option>
                            @foreach(\App\Models\Instrutor::get() as $linha)
                                <option value="{{ $linha->id }}">{{ $linha->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>

            </div>

        </template>
        <template #rodape>
            <button type="button" class="btn btn-primary" @click="salvar"
                    v-if="!preload && (!cadastrado && !atualizado)">
                <i class="fa fa-save"></i> Salvar
            </button>
        </template>
    </modal>
    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <div class="row">
            <div class="col-12 col-md-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" :disabled="controle.carregando" id="filtroIntervalo"
                           v-model="controle.dados.filtroPeriodo">
                    <label class="form-check-label" for="filtroIntervalo">Filtrar por período</label>
                </div>
                <div class="form-group">
                    <datepicker range label="Período" :disabled="controle.carregando"
                                v-model="controle.dados.intervalo"></datepicker>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <br>
                <label>Buscar</label>
                <input type="text"
                       placeholder="Buscar por nome"
                       autocomplete="off"
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoBusca">
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <br>
                <label>CPF</label>
                <input type="text"
                       placeholder="Buscar por cpf"
                       autocomplete="mastertag"
                       onblur="valida_cpf(this)"
                       v-mascara:cpf
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoCPF">

            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <br>
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
                                     @onselect="selecionaCliente"></autocomplete>
                    </div>
                </div>
            @endif

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
                    <option value="">Sem filtro</option>
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
                <label>Tem Instrutor NR33</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoInstrutor_nr_trinta_tres">
                    <option value="">Sem filtro</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3">
                <label>Tem Instrutor NR35</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoInstrutor_nr_trinta_cinco">
                    <option selected value="">Sem filtro</option>
                    <option :value="true">Sim</option>
                    <option :value="false">Não</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>NR33</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoNr_trinta_tres">
                    <option value="">Sem filtro</option>
                    <option :value="true">Realizado</option>
                    <option :value="false">Não Realizado</option>
                    <option value="NÃO SE APLICA">Não se aplica</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>NR35</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoNr_trinta_cinco">
                    <option value="">Sem filtro</option>
                    <option :value="true">Realizado</option>
                    <option :value="false">Não Realizado</option>
                    <option value="NÃO SE APLICA">Não se aplica</option>
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
            <div class="row">
                <button type="button" class="btn btn-sm mr-1 btn-success mr-1" :disabled="controle.carregando"
                        :style="controle.carregando ? 'cursor: not-allowed' : 'cursor: pointer'" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button class="btn btn-sm mr-1 btn-primary mr-1"
                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="selecionados.length === 0"
                        @click="nr33 = false; nr35=false; $refs.confirmacaoGeracao?.abrirModal()"
                >
                    <i class="fa fa-print"></i> Certificados <span
                        class="badge badge-light">@{{ selecionados.length }}</span>
                </button>

                <button class="btn btn-sm mr-1 btn-danger"
                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="selecionados.length === 0" @click="selecionados = []">
                    <i class="fa fa-times"></i> Limpar seleção
                </button>

                {{--<form target="_blank"
--}}{{--                      action="{{ \App\Models\Sistema::UrlServidor }}/carteira-etiqueta/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS"--}}{{--
    action="{{ route('carteira.excel') }}"
                      method="get">
                    @csrf
                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                    <input type="hidden" name="campoVaga" :value="controle.dados.campoVaga">
                    <input type="hidden" name="campoCliente" :value="controle.dados.campoCliente">
                    <input type="hidden" name="campoPcd" :value="controle.dados.campoPcd">
                    <input type="hidden" name="campoArea" :value="controle.dados.campoArea">
                    <input type="hidden" name="campoUf" :value="controle.dados.campoUf">
                    <input type="hidden" name="campoCargo" :value="controle.dados.campoCargo">
                    <input type="hidden" name="campoNr_trinta_tres" :value="controle.dados.campoNr_trinta_tres">
                    <input type="hidden" name="campoNr_trinta_cinco" :value="controle.dados.campoNr_trinta_cinco">
                    <input type="hidden" name="campoAdmitido" :value="controle.dados.campoAdmitido">

                    <button type="submit" class="btn btn-sm mr-1 btn-primary ml-1"
                            :disabled="controle.carregando || (!controle.carregando && lista.length===0 && selecionados.length === 0) ">
                        <i class="fas fa-file-excel"></i> Exportar Excel <span class="badge badge-light"
                                                                               v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                    </button>
                </form>--}}
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
                    <th class="text-center">
                        <input type="checkbox"
                               :style="comCertificado.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                               :disabled="comCertificado.length === 0" :checked="tudoMarcado"
                               @click="selecionaTodos">
                    </th>
                    <th class="text-center">ID</th>
                    <th>Nome</th>
                    <th>Lotação/Vaga</th>
                    <th>Cargo/Área</th>
                    <th>NR-33</th>
                    <th>Instrutor NR-33</th>
                    <th>NR-35</th>
                    <th>Instrutor NR-35</th>
                    <th class="text-center">
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="resultado in lista">
                    <td class="text-center">
                        <label :for="resultado.feedback.id">
                            <input
                                type="checkbox"
                                v-model="selecionados"
                                :value="resultado.certificado.feedback_id"
                                :id="resultado.feedback.id"
                                :style="resultado.certificado ? 'cursor:pointer' : 'cursor: not-allowed'"
                                :title="resultado.certificado ? null : 'Não possui certificado'"
                                v-if="resultado.certificado"
                            >
                            <input type="checkbox" v-else disabled="disabled" title="Candidato sem treinamento">

                        </label>
                    </td>
                    <td class="text-center">
                        @{{resultado.feedback.curriculo_id}}
                    </td>
                    <td>
                        @{{resultado.feedback.curriculo.nome}} <br>
                        CPF: @{{resultado.feedback.curriculo.cpf}}
                    </td>
                    <td>
                        Empresa - @{{resultado.feedback.cliente.nome_fantasia ?
                        resultado.feedback.cliente.nome_fantasia : resultado.feedback.cliente.nome}}
                        <br>
                        Vaga - @{{resultado.feedback.vaga_selecionada.nome}}
                    </td>

                    <td>
                        @{{resultado.admissao ? 'Cargo: '+resultado.admissao.cargo : null}} <br>
                        @{{resultado.admissao ? resultado.admissao.area_etiqueta ? 'Área: ' +
                        resultado.admissao.area_etiqueta.label : null : null}}
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
                        @{{ resultado.certificado ? resultado.certificado.instrutor_trinta_tres ?
                        resultado.certificado.instrutor_trinta_tres.nome : null : null }}<br>
                        @{{ resultado.certificado ? resultado.certificado.empresa_trinta_tres ? 'LOCAL:'+
                        resultado.certificado.empresa_trinta_tres.nome : null : null}}
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
                        @{{ resultado.certificado ? resultado.certificado.instrutor_trinta_cinco ?
                        resultado.certificado.instrutor_trinta_cinco.nome : null : null }}<br>
                        @{{ resultado.certificado ? resultado.certificado.empresa_trinta_cinco ? 'LOCAL:'+
                        resultado.certificado.empresa_trinta_cinco.nome : null : null }}
                    </td>

                    <td class="text-center">
                        <button class="btn btn-sm mr-1 btn-primary mb-1" title="Atualizar"
                                @click.prevent="formAlterar(resultado.feedback_id); $refs.janelaTreinamento?.abrirModal()">
                            <i class="fa fa-edit"></i>
                        </button>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.certificados.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>

@stop
@push('js')
    <script src="{{mix('js/g/certificado/app.js')}}"></script>
@endpush
