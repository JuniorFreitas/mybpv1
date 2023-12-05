@extends('layouts.sistema')
@section('title', 'Pré-admissão')
@section('content_header')
    <h4 class="text-default">Pré-admissão</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal id="janelaVisualizar" :titulo="tituloJanela" size="g">
        <template slot="conteudo">
            <preload class=" mt-2 text-center" v-if="preload"></preload>
            <div v-if="!preload && form.docs_curriculo_pre_adm.length" id="formDocumentos">
                <fieldset v-for="item in form.docs_curriculo_pre_adm">
                    <legend>@{{ item.label }}</legend>
                    <p>@{{ item.descricao }}</p>
                    <div class="alert alert-info" v-if="!item.docs_curriculo_anexos.length">
                        Nenhum anexo enviado
                    </div>
                    <upload label="Selecionar anexo(s)"
                            :leitura="true"
                            :model="item.docs_curriculo_anexos"
                            :model-delete="[]" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :multi="false"></upload>
                </fieldset>
            </div>

        </template>
    </modal>

    <modal id="janelaFinalizar" :titulo="tituloJanelaFinalizar" size="g">
        <template slot="conteudo">
            <preload class=" mt-2 text-center" v-if="preload"></preload>
            <div v-if="!preloadFinalizar" id="formFinalizar">
                <fieldset v-if="dadosFinalizar.curriculo">
                    <legend class="text-uppercase">Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            <p>
                                Nome: <strong>@{{ dadosFinalizar.curriculo.nome }}</strong> - @{{
                                dadosFinalizar.curriculo.idade }} anos <br>
                                Cargo: <strong>@{{ dadosFinalizar.vaga_aberta.vaga.nome }}</strong> <br>
                                Contato: <span
                                    v-if="dadosFinalizar.tel_principal && dadosFinalizar.tel_principal.tipo === 'whatsapp'"><strong><i
                                            class="fab fa-whatsapp text-success"></i> @{{
                                    dadosFinalizar.tel_principal.numero }}</strong> - </span>E-mail: <strong>@{{dadosFinalizar.curriculo.email}}</strong>
                                <br>
                            </p>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class="text-uppercase">Encaminhamento</legend>
                    <div class='row'>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="">CLÍNICA</label>
                                <select class='form-control'
                                        onblur='valida_campo_vazio(this,1)'
                                        onchange='valida_campo_vazio(this,1)'
                                        v-model='formFinalizar.empresa_exame_id'
                                >
                                    <option value=''>Selecione</option>
                                    <option v-for='item in listaEmpresasExames' :value='item.id'>
                                        @{{ item.nome }}
                                    </option>
                                </select>
                                <small v-if='formFinalizar.empresa_exame_id' class='my-2'
                                       v-text="listaEmpresasExames.filter(item => item.id === formFinalizar.empresa_exame_id)[0].dados.email"></small>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <datepicker
                                label="DATA PARA REALIZAÇÃO"
                                v-model="formFinalizar.encaminhado_exame_data"
                                :min="dataHoje"
                            ></datepicker>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="">PCMSO</label>
                                <select class='form-control' v-model='formFinalizar.pcmso_id'
                                        onblur='valida_campo_vazio(this,1)'
                                        onchange='valida_campo_vazio(this,1)'>
                                    <option value=''>Selecione ...</option>
                                    <option v-for='pcmso in listaPcmsos' :value='pcmso.id'>
                                        @{{ pcmso.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="custom-control custom-switch float-left">
                                <input type="checkbox" v-model="formFinalizar.envia_email" class="custom-control-input"
                                       id="envia_email">
                                <label class="custom-control-label"
                                       for="envia_email">Enviar E-mail</label>
                            </div>

                            <div class="custom-control custom-switch float-left ml-3"
                                 v-if="whatsappLiberado && dadosFinalizar.tel_principal && dadosFinalizar.tel_principal.tipo ==='whatsapp'">
                                <input type="checkbox" v-model="formFinalizar.envia_whatsapp"
                                       class="custom-control-input"
                                       id="envia_whatsapp">
                                <label class="custom-control-label"
                                       for="envia_whatsapp">Enviar WhatsApp</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </template>
        <template slot="rodape">
            <button class="btn btn-primary btn-sm" v-if="!preloadFinalizar"
                    @click="finalizarEncaminhar(dadosFinalizar.id)">
                <i class="fa fa-save"></i> Finalizar e Salvar
            </button>
        </template>
    </modal>

    <modal id="janelaEnviarEmail" :titulo="tituloJanela" size="g">
        <template slot="conteudo">
            <preload class=" mt-2 text-center" v-if="preload"></preload>
            <div v-if="!preload">
                <div class="alert alert-warning">Observação: Comunicamos que a troca do e-mail, implicará também na
                    mudança do acesso ao Sistema.
                    <span v-if="whatsappLiberado && authconfiguracao.empresa_id === 65974">
                        <br> Quando o Campo mensagem for preenchido e o colaborador possuir Whatsapp será enviado junto ao e-mail uma mensagem via Whatsapp.
                       </span>
                </div>
                <fieldset>
                    <legend>Envio de E-mail</legend>
                    <div class="col-12">
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="text" class="form-control form-control-sm validacampo"
                                   v-model="formEmail.email"
                                   autocomplete="mybp" @keyup.prevent="validaEmailVazio($event.target)"
                                   @blur.prevent="validaEmailVazio($event.target)">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Mensagem Adicional</label>
                            <textarea cols="3" rows="5" class="form-control form-control-sm"
                                      v-model="formEmail.observacao"
                                      autocomplete="mybp"></textarea>
                        </div>
                    </div>

                    <div class="col-3"
                         v-if="whatsappLiberado && authconfiguracao.empresa_id === 65974 && formEmail.temwhatsapp">
                        <label for="">Envia WhatsApp</label>
                        <select class="form-control form-control-sm" v-model="formEmail.envia_whatsapp">
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>
                </fieldset>
            </div>
        </template>
        <template slot="rodape">
            <button class="btn btn-primary btn-sm" v-if="!preload" @click="enviarEmail">
                <i class="fa fa-share"></i> Enviar
            </button>
        </template>
    </modal>

    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <label>Buscar</label>
                <input type="text"
                       placeholder="Buscar por nome ou cpf"
                       autocomplete="mastertag"
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoBusca">
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <label>Por Vaga</label>
                <autocomplete :disabled="controle.carregando" :caminho="controle.dados.caminho_autocomplete"
                              :valido="controle.dados.campoVaga !== ''"
                              v-model="controle.dados.autocomplete_label"
                              placeholder="Por vaga"
                              @onblur="resetaCampo"
                              @onselect="selecionaVaga"></autocomplete>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3" v-if="cliente_id === 1">
                <label>Por Cliente</label>
                <autocomplete :disabled="controle.carregando"
                              :caminho="controle.dados.caminho_cliente_autocomplete"
                              :valido="controle.dados.campoCliente !== ''"
                              v-model="controle.dados.autocomplete_label_cliente"
                              placeholder="Por cliente"
                              @onblur="resetaCampoCliente"
                              @onselect="selecionaCliente"></autocomplete>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>Estado</label>
                <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
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

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label for="">Status</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.status">
                        <option value="em_processo">Em processo</option>
                        <option value="admitidos">Admitidos</option>
                        <option value="demitidos">Demitidos</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>Exibir</label>
                <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.pages">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </form>

        <div class="row mt-2">
            <div class="col-12">
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                        @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

            </div>
        </div>

    </fieldset>
    <preload v-if="controle.carregando"></preload>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="table table-bordered table-striped">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">CÓD</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th class="text-center" v-if="cliente_id === 1">Cliente</th>
                    <th class="text-center">Vaga</th>
                    <th class="text-center">Qnt Anexos</th>
                    <th class="text-center">Status</th>
                    <th class="text-center"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="resultado in lista"
                    :class="{
                        'bg-success text-white': resultado.finalizado === true
                    }"
                >
                    <td class="text-center">
                        @{{resultado.id}}
                    </td>
                    <td>
                        @{{resultado.curriculo.nome}}
                    </td>
                    <td>
                        @{{resultado.curriculo.cpf}}
                    </td>
                    <td class="text-center" v-if="cliente_id === 1">
                        @{{resultado.cliente.nome_fantasia ?
                        resultado.cliente.nome_fantasia :
                        resultado.cliente.nome}}
                    </td>
                    <td class="text-center">
                        @{{resultado.vaga_aberta_municipio}}
                    </td>
                    <td class="text-center">
                        @{{ resultado.qnt_anexos }} anexo(s)
                    </td>
                    <td class="text-center">
                        <span v-if="resultado.finalizado">Finalizado por @{{ resultado.quem_finalizou }}<br>em @{{ resultado.data_finalizacao }}</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary" title="Visuzalizar"
                                @click.prevent="formVisualizar(resultado.id)"
                                data-toggle="modal"
                                data-target="#janelaVisualizar"><i class="fa fa-search-plus"></i></button>
                        @can('privilegio_admissao_pre_admissao_reencaminhar_email')
                            <button class="btn btn-sm btn-primary" title="Reenviar Email"
                                    @click.prevent="formEnviarEmail(resultado.id)"
                                    data-toggle="modal"
                                    data-target="#janelaEnviarEmail"><i class="fa fa-share-square"></i></button>
                        @endcan
                        {{--                        @can('privilegio_admissao_pre_admissao_reencaminhar_email')--}}
                        <button class="btn btn-sm btn-primary" title="Finalizar"
                                @click.prevent="abrirFormFinalizar(resultado.id)"
                                data-toggle="modal"
                                v-if="!resultado.finalizado"
                                data-target="#janelaFinalizar"><i class="fa fa-check-circle"></i></button>
                        {{--                        @endcan--}}
                    </td>

                </tr>
                </tbody>
            </table>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.admissao.preadm.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/admissao/preadmissao/app.js')}}"></script>
@endpush
