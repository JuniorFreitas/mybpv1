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
            <div v-if="!preload" id="formDocumentos">
                <fieldset>
                    <legend>FOTO 3X4</legend>
                    <upload label="Selecionar anexo(s)"
                            :leitura="true"
                            :model="form.foto_tres"
                            :model-delete="form.foto_tresDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>RG/CPF</legend>
                    <upload label="Selecionar anexo(s)"
                            :leitura="true"
                            :model="form.anexos_cpf_rg"
                            :model-delete="form.anexos_cpf_rgDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>COMPROVANTE DE ENDEREÇO</legend>
                    <upload label="Selecionar anexo(s)"
                            :leitura="true"
                            :model="form.comprovante_end"
                            :model-delete="form.comprovante_endDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>CTPS DIGITAL (FRENTE)</legend>
                    <upload label="Selecionar anexo(s)"
                            :leitura="true"
                            :model="form.ctps_frente"
                            :model-delete="form.ctps_frenteDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>CTPS DIGITAL (VERSO)</legend>
                    <upload label="Selecionar anexo(s)"
                            :leitura="true"
                            :model="form.ctps_verso"
                            :model-delete="form.ctps_versoDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>ANTECEDENTE CRIMINAL</legend>
                    <upload label="Selecionar anexo(s)"
                            :leitura="true"
                            :model="form.antecedentes"
                            :model-delete="form.antecedentesDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>TITULO ELEITOR</legend>
                    <upload label="Selecionar anexo(s)"
                            :leitura="true"
                            :model="form.titulo_eleitor"
                            :model-delete="form.titulo_eleitorDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>CERTIFICADO RESERVISTA (Apenas Homens)</legend>
                    <upload label="Selecionar anexo(s)"

                            :leitura="true"
                            :model="form.certificado_reservista"
                            :model-delete="form.certificado_reservistaDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>CARTÃO DO PIS OU RESCISÃO DE CONTRATO</legend>
                    <upload label="Selecionar anexo(s)"

                            :leitura="true"
                            :model="form.pis_rescisao"
                            :model-delete="form.pis_rescisaoDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>CERTIFICADO DE ESCOLARIDADE</legend>
                    <upload label="Selecionar anexo(s)"

                            :leitura="true"
                            :model="form.certificado_escolaridade"
                            :model-delete="form.certificado_escolaridadeDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>CONTA BANCO</legend>
                    <upload label="Selecionar anexo(s)"
                            :leitura="true"
                            :model="form.conta_banco"
                            :model-delete="form.conta_bancoDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>CARTA DE SINDICALIZAÇÃO EMITIDA PELO SINDICATO</legend>
                    <upload label="Selecionar anexo(s)"

                            :leitura="true"
                            :model="form.carta_sindicato"
                            :model-delete="form.carta_sindicatoDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>CÓPIA DA CARTEIRA DE VACINA; (NÃO OBRIGATÓRIO)</legend>
                    <upload label="Selecionar anexo(s)"

                            :leitura="true"
                            :model="form.carteira_vacina"
                            :model-delete="form.carteira_vacinaDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>
                <fieldset>
                    <legend>DOCUMENTAÇÃO FILHOS (PARA SALÁRIO FAMÍLIA)</legend>
                    <p>IDENTIDADE E CPF</p>
                    <upload label="Selecionar anexo(s)"
                            :leitura="true"
                            :model="form.rgcpf_filho"
                            :model-delete="form.rgcpf_filhoDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :multi="true"></upload>
                </fieldset>
                <fieldset>
                    <legend>CARTÃO VACINA (ATÉ 6 ANOS)</legend>
                    <upload label="Selecionar anexo(s)"

                            :leitura="true"
                            :model="form.cartao_vacina_filho"
                            :model-delete="form.cartao_vacina_filhoDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :multi="true"></upload>
                </fieldset>
                <fieldset>
                    <legend>DECLARAÇÃO ESCOLAR (DE 7 ANOS ATÉ 14 ANOS)</legend>
                    <p>DECLARAÇÃO ESCOLAR DO ANO EM CURSO (ORIGINAL)</p>
                    <upload label="Selecionar anexo(s)"

                            :leitura="true"
                            :model="form.declaracao_escolar_filho"
                            :model-delete="form.declaracao_escolar_filhoDel" :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :multi="true"></upload>
                </fieldset>
            </div>

        </template>
    </modal>

    <modal id="janelaEnviarEmail" :titulo="tituloJanela" size="g">
        <template slot="conteudo">
            <preload class=" mt-2 text-center" v-if="preload"></preload>
            <div v-if="!preload">
                <fieldset>
                    <legend>Envio de Email</legend>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="text" class="form-control"
                                   v-model="formEmail.email"
                                   autocomplete="mybp" onblur="valida_campo_vazio(this,3)">
                        </div>
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

                {{--                <button class="btn btn-danger"--}}
                {{--                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"--}}
                {{--                        :disabled="selecionados.length === 0" @click="selecionados = []">--}}
                {{--                    <i class="fa fa-times"></i> Limpar seleção--}}
                {{--                </button>--}}
                {{--                <form target="_blank"--}}
                {{--                      action="{{ \App\Models\Sistema::UrlServidor }}/admissao/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS"--}}
                {{--                      method="get">--}}
                {{--                    @csrf--}}
                {{--                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">--}}
                {{--                    <input type="hidden" name="campoVaga" :value="controle.dados.campoVaga">--}}
                {{--                    <input type="hidden" name="campoCliente" :value="controle.dados.campoCliente">--}}
                {{--                    <input type="hidden" name="campoUf" :value="controle.dados.campoUf">--}}
                {{--                    <input type="hidden" name="campoRh" :value="controle.dados.campoRh">--}}
                {{--                    <input type="hidden" name="campoFinalRh" :value="controle.dados.campoFinalRh">--}}
                {{--                    <input type="hidden" name="campoRota" :value="controle.dados.campoRota">--}}
                {{--                    <input type="hidden" name="campoTecnica" :value="controle.dados.campoTecnica">--}}
                {{--                    <input type="hidden" name="campoTeste" :value="controle.dados.campoTeste">--}}
                {{--                    <input type="hidden" name="campoPcd" :value="controle.dados.campoPcd">--}}
                {{--                    <button type="submit" class="btn btn-primary ml-1"--}}
                {{--                            :disabled="controle.carregando || (!controle.carregando && lista.length===0 && selecionados.length === 0) ">--}}
                {{--                        <i class="fas fa-file-excel"></i> Exportar Excel <span class="badge badge-light"--}}
                {{--                                                                               v-show="selecionados.length > 0">@{{ selecionados.length }}</span>--}}
                {{--                    </button>--}}
                {{--                </form>--}}
            </div>
        </div>

    </fieldset>
    <preload v-if="controle.carregando"></preload>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">CÓD</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th class="text-center" v-if="cliente_id === 1">Cliente</th>
                    <th class="text-center">Vaga</th>
                    <th class="text-center"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="resultado in lista">
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
                        @{{resultado.vaga_selecionada.nome}}
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary" title="Visuzalizar"
                                @click.prevent="formVisualizar(resultado.id)"
                                data-toggle="modal"
                                data-target="#janelaVisualizar"><i class="fa fa-search-plus"></i></button>
                        @can('admissao_pre_admissao_reencaminhar_email')
                            <button class="btn btn-sm btn-primary" title="Reenviar Email"
                                    @click.prevent="formEnviarEmail(resultado.id)"
                                    data-toggle="modal"
                                    data-target="#janelaEnviarEmail"><i class="fa fa-share-square"></i></button>
                        @endcan
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
