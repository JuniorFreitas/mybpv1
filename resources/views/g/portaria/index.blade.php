@extends('layouts.sistema')
@section('title', 'PORTARIA')
@section('content_header')
    <h4 class="text-default">PORTARIA</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal id="janelaPortaria" titulo="Atualizar dados" :size="80">
        <template slot="conteudo">
            <div class="alert alert-success text-center" v-show="form.atualizado">
                <h4><i class="icon fa fa-check"></i> Dados atualizados com sucesso!</h4>
            </div>

            <p class=" mt-2 text-center" v-if="form.preload">
                <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
            </p>

            <div v-if="form.editando && !form.preload">
                <fieldset>
                    <legend>Dados Pessoais</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" class="form-control" v-model="form.feedback.curriculo.nome"
                                       placeholder="Nome"
                                       autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-group">
                                <label>Nascimento</label>
                                <input type="text" class="form-control"
                                       v-model="form.feedback.curriculo.nascimento"
                                       placeholder="Ex: 10/10/2010"
                                       v-mascara:data
                                       autocomplete="mastertag" onblur="valida_data_vazio(this)">
                            </div>
                        </div>


                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-group">
                                <label>RG</label>
                                <input type="text" class="form-control"
                                       v-model="form.feedback.curriculo.rg"
                                       placeholder="Número do RG"
                                       v-mascara:numero
                                       autocomplete="mastertag">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-2">
                            <div class="form-group">
                                <label>Emitente</label>
                                <input type="text" class="form-control"
                                       v-model="form.feedback.curriculo.orgao_expeditor"
                                       placeholder="Orgão expeditor"
                                       autocomplete="mastertag">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Pai</label>
                                <input type="text" class="form-control"
                                       v-model="form.feedback.curriculo.filiacao_pai"
                                       placeholder="Nome do Pai"
                                       autocomplete="mastertag">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 ">
                            <div class="form-group">
                                <label>Mãe</label>
                                <input type="text" class="form-control"
                                       v-model="form.feedback.curriculo.filiacao_mae"
                                       placeholder="Nome da Mãe"
                                       autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                            </div>
                        </div>


                        <div class="col-12">
                            <fieldset>
                                <legend>Endereço</legend>
                                <div class="row">
                                    <div class="col-12">
                                        <endereco :model="form.feedback.curriculo"></endereco>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Cargo</label>
                                <autocomplete :formsm="false" :caminho="controle.dados.caminho_autocomplete"
                                              :valido="form.feedback.vaga_id !== ''"
                                              v-model="form.feedback.autocomplete_label_vaga_modal"
                                              placeholder="Selecione uma vaga"
                                              :disabled="true"
                                              :id="`vaga_${hash}`"
                                              @onblur="resetaCampoVagaModal"
                                             @onselect="selecionaVagaModal"></autocomplete>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="Cidade">Cidade</label>
                                <autocomplete :formsm="false" :caminho="todos_municipios"
                                              :valido="form.feedback.curriculo.municipio_id !== ''"
                                              v-model="form.feedback.curriculo.autocomplete_label_municipio_modal"
                                              placeholder="Selecione um municipio"
                                              :disabled="true"
                                              :id="`mun_${hash}`"
                                              @onblur="resetaCampoMunicipioModal"
                                             @onselect="selecionaMunicipioModal"></autocomplete>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Função</label>
                                <input type="text" class="form-control" onblur="valida_campo_vazio(this,2)"
                                       v-model="form.funcao">
                            </div>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>FOTO ESCANEADA</legend>
                                <upload :model="form.feedback.curriculo.foto_tres"
                                        :model-delete="form.feedback.curriculo.foto_tresDel"
                                        url="{{ route('g.admissao.admissao.upload-anexos') }}"
                                        :apenas-imagens="true"
                                        :quantidade="1"
                                        label="Selecionar Imagem"
                                        @onProgresso="anexoUploadAndamento=true"
                                        @onFinalizado="anexoUploadAndamento=false"></upload>
                            </fieldset>
                        </div>

                    </div>
                </fieldset>


            </div>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-primary" v-show="!form.atualizado && !form.preload"
                    @click="salvar">
                <i class="fa fa-save"></i> Salvar
            </button>
        </template>
    </modal>
    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <div class="row">
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <label>Buscar</label>
                <input type="text"
                       placeholder="Buscar por nome ou cpf"
                       autocomplete="off"
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoBusca">

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

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>Estado</label>

                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoUf">
                    <option value="">SEM FILTRO</option>
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
                <button type="button" class="btn btn-sm btn-success mr-1 mb-2" :disabled="controle.carregando"
                        :style="controle.carregando ? 'cursor: not-allowed' : 'cursor: pointer'" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <form target="_blank" :action="`{{route('g.portaria.pdf')}}`" method="post">
                    @csrf
                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                    <button type="submit" class="btn btn-sm btn-primary mr-1 mb-2"
                            :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                            :disabled="selecionados.length === 0">
                        Imprimir <span class="badge badge-light">@{{ selecionados.length }}</span>
                    </button>
                </form>

                <button class="btn btn-sm btn-danger mr-1 mb-2"
                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="selecionados.length === 0" @click="selecionados = []">
                    <i class="fa fa-times"></i> Limpar seleção
                </button>

{{--                <form target="_blank"--}}
{{--                      action="{{ route('portaria.excel') }}"--}}
{{--                      method="post">--}}
{{--                    @csrf--}}
{{--                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">--}}
{{--                    <input type="hidden" name="vaga_id" :value="controle.dados.campoVaga">--}}
{{--                    <input type="hidden" name="cliente_id" :value="controle.dados.campoCliente">--}}
{{--                    <button type="submit" class="btn btn-sm btn-primary mb-2"--}}
{{--                            :disabled="controle.carregando || (!controle.carregando && lista.length===0 && selecionados.length === 0) ">--}}
{{--                        <i class="fas fa-file-excel"></i> Exportar Excel--}}
{{--                    </button>--}}
{{--                </form>--}}
            </div>
        </div>

    </fieldset>
    <p class="text-center" v-if="controle.carregando">
        <i class="fa fa-spinner fa-pulse"></i> Carregando...
    </p>
    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>
        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">
                        <input type="checkbox"
                               :checked="tudoMarcado"
                               style="cursor: pointer"
                               @click="selecionaTodos">
                    </th>
                    <th class="text-center">ID</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>RG/Emitente</th>
                    <th>Filiação</th>
                    <th class="text-center">Vaga</th>
                    <th class="text-center">Função</th>
                    <th class="text-center">Foto 3x4</th>
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
                                style="cursor: pointer"
                            >
                        </label>
                    </td>
                    <td class="text-center">
                        @{{resultado.curriculo_id}}
                    </td>
                    <td>
                        @{{resultado.curriculo.nome}}
                    </td>
                    <td>
                        @{{resultado.curriculo.cpf}}
                    </td>
                    <td>
                        <span v-html="resultado.curriculo.rg_format"></span>
                    </td>
                    <td>
                        Pai: @{{resultado.curriculo.filiacao_pai ?
                        resultado.curriculo.filiacao_pai : 'Não Informadao'}} <br>
                        Mãe: @{{resultado.curriculo.filiacao_mae ?
                        resultado.curriculo.filiacao_mae : 'Não Informadao'}}
                    </td>


                    <td class="text-center">
                        @{{resultado.vaga_selecionada.nome}}
                    </td>

                    <td class="text-center">
                        @{{resultado.admissao ? resultado.admissao.funcao : null}}
                    </td>

                    <td class="text-center">
                        @{{resultado.curriculo.foto_tres.length > 0 ? 'SIM' : 'NÃO'}}
                    </td>


                    <td class="text-center">
                        <button class="btn btn-sm btn-primary" title="Atualizar dados"
                                @click.prevent="formAlterar(resultado.id)"
                                data-toggle="modal"
                                data-target="#janelaPortaria">
                            <i class="fa fa-edit"></i>
                        </button>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.portaria.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/portaria/app.js')}}"></script>
@endpush
