@extends('layouts.sistema')
@section('title', 'Funcionários')
@section('content_header')
    <h4 class="text-default">FUNCIONÁRIOS</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal ref="janelaAnexos" id="janelaAnexos" :titulo="form.nome +' #'+ form.id" :size="90">
        <template #conteudo>
            <span v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</span>
            <fieldset v-show="!preloadAjax && form.fotos.length>0">
                <legend style="text-transform: uppercase">Foto 3x4</legend>
                <upload label="Selecionar Foto" :model="form.fotos"
                        :model-delete="form.fotosDel"
                        :apenas-imagens="true"
                        :leitura="true"
                        :quantidade="1"
                        :url="urlFotoUpload"
                        @onProgresso="fotoUploadAndamento=true"
                        @onFinalizado="fotoUploadAndamento=false"></upload>
            </fieldset>

            <div class="alert alert-warning" v-show="!preloadAjax && form.fotos.length==0"><i
                    class="fa fa-exclamation"></i> Nenhuma Foto 3x4 foi encontrada
            </div>

            <fieldset v-show="!preloadAjax && form.anexos.length>0">
                <legend style="text-transform: uppercase">Anexos de Documentos</legend>
                <upload :model="form.anexos"
                        :leitura="true"
                        :model-delete="form.anexosDel"
                        :url="urlAnexoUpload"
                        @onProgresso="anexoUploadAndamento=true"
                        @onFinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <div class="alert alert-warning" v-show="!preloadAjax && form.anexos.length==0"><i
                    class="fa fa-exclamation"></i> Nenhum Anexo foi encontrada
            </div>

        </template>
    </modal>


    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template #conteudo>
            <span v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</span>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Funcionário cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Funcionário alterado com sucesso!</h4>
            </div>
            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <fieldset>
                    <legend>DADOS DO TRABALHADOR</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-5 col-lg-5">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Nome Completo
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           onblur="valida_campo_vazio(this,3)"
                                           placeholder="Informe o Nome Completo" value=""
                                           autocomplete="off" v-model="form.nome">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">CPF
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Número"
                                           onblur="valida_cpf_vazio(this)"
                                           v-model="form.cpf" v-mascara:cpf>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">NIS (PIS/PASEP/NIT)
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Número"
                                           onblur="valida_campo_vazio(this,1)"
                                           v-model="form.nis" v-mascara:numero>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Data de Nascimento
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data"
                                           placeholder="00/00/0000" id="nascimento" onblur="valida_data_vazio(this)"
                                           autocomplete="off" v-mascara:data v-model="form.nascimento">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-2 col-lg-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Sexo
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)" v-model="form.sexo">
                                        <option value="Masculino">Masculino</option>
                                        <option value="Feminino">Feminino</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Raça e Cor
                                        </div>
                                    </div>
                                    <select class="custom-select" v-model="form.racaecor">
                                        <option value="Branco">Branco</option>
                                        <option value="Amarelo">Amarelo</option>
                                        <option value="Pardo">Pardo</option>
                                        <option value="Indígena">Indígena</option>
                                        <option value="Negro">Negro</option>
                                        <option value="Não informado">Não informado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Estado Cívil
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.estadocivil">
                                        <option value="Solteiro">Solteiro</option>
                                        <option value="Casado">Casado</option>
                                        <option value="Divorcíado">Divorcíado</option>
                                        <option value="Viúvo">Viúvo</option>
                                        <option value="Outros">Outros</option>
                                        <option value="União Estável">União Estável</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="col-12"
                             v-show="form.estadocivil=='Casado' || form.estadocivil=='União Estável'"
                             v-if="form.estadocivil=='Casado' || form.estadocivil=='União Estável'">
                            <fieldset>
                                <legend>CONJUGE</legend>
                                <div class="row">

                                    <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">Nome
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control"
                                                       placeholder="Nome" onblur="valida_campo_vazio(this,1)"
                                                       autocomplete="off" v-model="form.conjuge">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-3 col-md-3 col-lg-3">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">CPF
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control"
                                                       placeholder="Ex: 000.000.000-00" onblur="valida_cpf_vazio(this)"
                                                       autocomplete="off" v-mascara:cpf v-model="form.conjuge_cpf">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-3 col-md-3 col-lg-3">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">Nascimento
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control"
                                                       placeholder="Ex: 00/00/0000" onblur="valida_data_vazio(this)"
                                                       autocomplete="off" v-mascara:data
                                                       v-model="form.conjuge_nascimento">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-3 col-md-3 col-lg-3">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">RG
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control"
                                                       placeholder="Ex: 000000000000"
                                                       onblur="valida_campo_vazio(this,3)"
                                                       autocomplete="off" v-mascara:numero v-model="form.conjuge_rg">
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </fieldset>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Nome do Pai
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Informe o Nome do Pai" value=""
                                           autocomplete="off" v-model="form.pai">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Nome da Mãe
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           onblur="valida_campo_vazio(this,3)"
                                           placeholder="Informe o Nome da Mãe" value=""
                                           autocomplete="off" v-model="form.mae">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Nacionalidade
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Nacionalidade" onblur="valida_campo_vazio(this,3)"
                                           autocomplete="off" v-model="form.nacionalidade">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Naturalidade
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Ex.: São Luís/MA" onblur="valida_campo_vazio(this,3)"
                                           autocomplete="off" v-model="form.naturalidade">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Escolaridade
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.escolaridade_id">
                                        @foreach($escolaridades as $e)
                                            <option value="{{$e->id}}">{{$e->tipo}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6"
                             v-if="form.escolaridade_id == 9 || form.escolaridade_id == 10 || form.escolaridade_id == 11 || form.escolaridade_id == 12"
                             v-show="form.escolaridade_id == 9 || form.escolaridade_id == 10 || form.escolaridade_id == 11 || form.escolaridade_id == 12">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Graduado em:
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Ex.: Administração" onblur="valida_campo_vazio(this,1)"
                                           autocomplete="off" v-model="form.graduacao">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-2 col-md-2 col-lg-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Sapato
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Tamanho" onblur="valida_campo_vazio(this,1)"
                                           autocomplete="off" v-model="form.sapato">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-2 col-md-2 col-lg-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Camisa
                                        </div>
                                    </div>
                                    <select class="custom-select" onblur="valida_campo_vazio(this,1)"
                                            v-model="form.camisa">
                                        <option value="PP">PP</option>
                                        <option value="P">P</option>
                                        <option value="M">M</option>
                                        <option value="G">G</option>
                                        <option value="GG">GG</option>
                                        <option value="XGG">XGG</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-2 col-md-2 col-lg-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Calça
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Tamanho" onblur="valida_campo_vazio(this,1)"
                                           autocomplete="off" v-model="form.calca">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Portador de Deficiência
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.deficiente">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-12 col-md-12 col-lg-12" v-show="form.deficiente"
                             v-if="form.deficiente">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Tipo de Deficiência
                                        </div>
                                    </div>
                                    <div class="form-check-inline ml-3">
                                        <label class="form-check-label" style="cursor: pointer">
                                            <input type="checkbox" class="form-check-input"
                                                   style="cursor: pointer;"
                                                   value="fisica"
                                                   v-model="form.deficiencia">Física
                                        </label>
                                    </div>
                                    <div class="form-check-inline">
                                        <label class="form-check-label" style="cursor: pointer">
                                            <input type="checkbox" class="form-check-input"
                                                   style="cursor: pointer;"
                                                   value="auditiva"
                                                   v-model="form.deficiencia">Auditiva
                                        </label>
                                    </div>
                                    <div class="form-check-inline">
                                        <label class="form-check-label" style="cursor: pointer">
                                            <input type="checkbox" class="form-check-input"
                                                   style="cursor: pointer;"
                                                   value="visual"
                                                   v-model="form.deficiencia">Visual
                                        </label>
                                    </div>
                                    <div class="form-check-inline">
                                        <label class="form-check-label" style="cursor: pointer">
                                            <input type="checkbox" class="form-check-input"
                                                   style="cursor: pointer;"
                                                   value="intelectual"
                                                   v-model="form.deficiencia">Intelectual
                                        </label>
                                    </div>
                                    <div class="form-check-inline">
                                        <label class="form-check-label" style="cursor: pointer">
                                            <input type="checkbox" class="form-check-input"
                                                   style="cursor: pointer;"
                                                   value="mental"
                                                   v-model="form.deficiencia">Mental
                                        </label>
                                    </div>
                                    <div class="form-check-inline">
                                        <label class="form-check-label" style="cursor: pointer">
                                            <input type="checkbox" class="form-check-input"
                                                   style="cursor: pointer;"
                                                   value="reabilitado"
                                                   v-model="form.deficiencia">Reabilitado
                                        </label>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-12 col-md-12 col-lg-12" v-show="form.deficiente"
                             v-if="form.deficiente">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Obs. Deficiência
                                        </div>
                                    </div>
                                    <input type="text" v-model="form.deficiencia_obs"
                                           class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Altura
                                        </div>
                                    </div>
                                    <input type="text" class="form-control text-right" placeholder="00,00"
                                           v-mascara:altura
                                           v-model="form.altura">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Peso KG
                                        </div>
                                    </div>
                                    <input type="text" class="form-control text-right" placeholder="00.000"
                                           v-mascara:peso
                                           v-model="form.peso">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Cabelos
                                        </div>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Cabelos"
                                           v-model="form.cabelos">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Olhos
                                        </div>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Olhos"
                                           v-model="form.olhos">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Sinais
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Sinais"
                                           v-model="form.sinais">
                                </div>
                            </div>
                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>DOCUMENTOS DE IDENTIFICAÇÃO</legend>
                    <div class="row">

                        <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">CTPS Nº
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Número"
                                           onblur="valida_campo_vazio(this,1)"
                                           v-model="form.ctps_numero" v-mascara:numero>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">CTPS Série
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Número"
                                           onblur="valida_campo_vazio(this,1)"
                                           v-model="form.ctps_serie" v-mascara:numero>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-2 col-lg-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">CTPS UF
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="UF"
                                           onblur="valida_campo_vazio(this,2)"
                                           v-model="form.ctps_uf" maxlength="2">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">CTPS Emissão
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data"
                                           placeholder="00/00/0000" id="ctps_emissao"
                                           onblur="valida_data_vazio(this)"
                                           v-model="form.ctps_emissao" v-mascara:data>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Titulo Eleitoral
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           onblur="valida_campo_vazio(this,1)"
                                           placeholder="Número"
                                           v-model="form.tituloeleitoral" v-mascara:numero>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Zona
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Número"
                                           onblur="valida_campo_vazio(this,1)"
                                           v-model="form.tituloeleitoral_zona" v-mascara:numero>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Seção
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Número"
                                           onblur="valida_campo_vazio(this,1)"
                                           v-model="form.tituloeleitoral_secao" v-mascara:numero>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6" v-show="form.sexo=='Masculino'"
                             v-if="form.sexo=='Masculino'">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Certificado de Reservista
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Número"
                                           onblur="valida_campo_vazio(this,3)"
                                           v-model="form.reservista" v-mascara:numero>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6" v-show="form.sexo=='Masculino'"
                             v-if="form.sexo=='Masculino'">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Reservista Categoria
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Categoria"
                                           onblur="valida_campo_vazio(this,3)"
                                           v-model="form.reservista_categoria">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Tipo de Documento
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.doc_tipo">
                                        <option value="RIC - Registro de Identidade Único">RIC - Registro de Identidade
                                            Único
                                        </option>
                                        <option value="RNE - Registro Nacional de Estrangeiro">RNE - Registro Nacional
                                            de
                                            Estrangeiro
                                        </option>
                                        <option value="CNH - Carteira Nacional de Habilitação">CNH - Carteira Nacional
                                            de
                                            Habilitação
                                        </option>
                                        <option value="RG - Registro Geral">RG - Registro Geral</option>
                                        <option value="OC - Número de Registro em órgão de Classe">OC - Número de
                                            Registro
                                            em órgão de Classe
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Nº Documento
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="Número"
                                           onblur="valida_campo_vazio(this,1)"
                                           v-model="form.doc_numero" v-mascara:numero>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Emissor
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           onblur="valida_campo_vazio(this,1)"
                                           placeholder="Ex: SSP/MA"
                                           v-model="form.doc_emissor">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Emissão
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data"
                                           placeholder="00/00/0000" id="doc_emissao"
                                           onblur="valida_data_vazio(this)"
                                           v-model="form.doc_emissao" v-mascara:data>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3"
                             v-if="form.doc_tipo != 'RG - Registro Geral' && form.doc_tipo != 'RIC - Registro de Identidade Único'"
                             v-show="form.doc_tipo != 'RG - Registro Geral' && form.doc_tipo != 'RIC - Registro de Identidade Único'">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Validade
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data"
                                           placeholder="00/00/0000" id="doc_validade"
                                           onblur="valida_data_vazio(this)"
                                           v-model="form.doc_validade" v-mascara:data>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>DADOS COMPLEMENTARES</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Residência própria
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.residencia">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Comprada com FGTS
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.comprada_fgts">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Reside no Exterior
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.reside_exterior">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">E-mail
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           placeholder="E-mail" value=""
                                           autocomplete="off" v-model="form.email">
                                </div>
                            </div>
                        </div>

                    </div>

                </fieldset>

                <fieldset>
                    <legend>ENDEREÇO</legend>
                    <endereco :model="form"></endereco>
                </fieldset>

                <fieldset>
                    <legend>TELEFONES</legend>

                    <telefone :model="form.telefones" :model-delete="form.telefonesDel" :ramal="false"
                              :pais="false"></telefone>
                </fieldset>

                <fieldset v-show="form.nacionalidade!='Brasileira'" v-if="form.nacionalidade!='Brasileira'">
                    <legend>TRABALHADOR ESTRANGEIRO</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Chegada ao Brasil
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data"
                                           placeholder="00/00/0000" id="chegada_brasil" onblur="valida_data_vazio(this)"
                                           autocomplete="off" v-mascara:data v-model="form.chegada_brasil">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Naturalização BR
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data"
                                           placeholder="00/00/0000" id="naturalizacao_brasil"
                                           onblur="valida_data_vazio(this)"
                                           autocomplete="off" v-mascara:data v-model="form.naturalizacao_brasil">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Casado(a) com brasileiro(a)</div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.casado_brasil">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Filho com brasileiro(a)</div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.filho_brasil">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>INFORMAÇÕES BANCÁRIAS</legend>
                    <banco :model="form"></banco>
                </fieldset>

                <fieldset>
                    <legend>DEPENDENTES</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <button class="btn btn-secondary mb-2" @click="addLIDependente($event.target)">
                                <span class="fas fa-plus" aria-hidden="true"></span>
                                Adicionar Dependentes Beneficiários (Filhos abaixo de 14 anos)
                            </button>
                        </div>
                        <div class="col-12 col-sm-12" v-show="form.dependentes.length>0"
                             v-for="(obj, index) in form.dependentes" :key="obj.id">

                            <div class="row">

                                <div class="col-12 col-sm-5 col-md-5 col-lg-5">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <div class="input-group-text">Nome
                                                </div>
                                            </div>
                                            <input type="text" class="form-control"
                                                   placeholder="Nome Completo do Dependente com menos de 14 anos"
                                                   onblur="valida_campo_vazio(this,1)"
                                                   v-model="obj.nome">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-9 col-sm-4 col-md-4 col-lg-3">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <div class="input-group-text">CPF</div>
                                            </div>
                                            <input type="text" class="form-control"
                                                   v-mascara:cpf
                                                   onblur="valida_cpf(this)"
                                                   v-model="obj.cpf">
                                        </div>
                                    </div>
                                </div>


                                <div class="col-9 col-sm-4 col-md-4 col-lg-3">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <div class="input-group-text">Nascimento</div>
                                            </div>
                                            <input type="text" v-mascara:data class="form-control"
                                                   placeholder="00/00/0000"
                                                   v-model="obj.nascimento">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-1">
                                    <button class="btn btn-danger" @click="removerLIDependente(index)"><i
                                            class="fa fa-times"></i></button>
                                </div>

                            </div>

                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>BENEFÍCIOS</legend>
                    <div class="row">

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Vale Transporte</div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.vale_transporte">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3" v-if="form.vale_transporte"
                             v-show="form.vale_transporte">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Descontar VT
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.descontar_vt">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3" v-show="form.vale_transporte"
                             v-if="form.vale_transporte">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Linha/Valor1 R$
                                        </div>
                                    </div>
                                    <input type="text" class="form-control text-right"
                                           v-model="form.vale_transporte_linhaum"
                                           onblur="valida_dinheiro(this)"
                                           placeholder="0,00"
                                           autocomplete="off" v-mascara:dinheiro>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3" v-show="form.vale_transporte"
                             v-if="form.vale_transporte">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Linha/Valor2 R$
                                        </div>
                                    </div>
                                    <input type="text" class="form-control text-right"
                                           v-model="form.vale_transporte_linhadois"
                                           placeholder="0,00"
                                           autocomplete="off" v-mascara:dinheiro>
                                </div>
                            </div>
                        </div>

                        <div class="col-12"></div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Descontar VR/VA
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.descontar_vr_va">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Valor pago VR/VA R$
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           v-model="form.valor_desconto_vr"
                                           placeholder="informe"
                                           autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Desconto Plano de Saúde
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.desconto_plano_saude">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3" v-if="form.desconto_plano_saude"
                             v-show="form.desconto_plano_saude">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Desconto Plano Saude R$
                                        </div>
                                    </div>
                                    <input type="text" class="form-control text-right"
                                           v-model="form.desconto_plano_saude_valor"
                                           onblur="valida_dinheiro(this)"
                                           placeholder="0,00"
                                           autocomplete="off" v-mascara:dinheiro>
                                </div>
                            </div>
                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>DADOS GERAIS DO CONTRATO</legend>
                    <div class="row">
                        <div class="col-12 col-sm-3 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Data de admissão
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data" id="admissao" placeholder="00/00/0000"
                                           onblur="valida_data_vazio(this)"
                                           v-model="form.admissao" v-mascara:data>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Exame Admissional
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data"
                                           placeholder="00/00/0000" id="admissional"
                                           onblur="valida_data_vazio(this)"
                                           v-model="form.admissional" v-mascara:data>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">CRM
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           onblur="valida_campo_vazio(this,1)"
                                           v-model="form.admissional_crm">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Cargo
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.cargo_id">
                                        @foreach($cargos as $c)
                                            <option value="{{$c->id}}">{{$c->titulo}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Descrição do Cargo
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           v-model="form.cargo_descricao">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Departamento
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           v-model="form.departamento">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Tomador
                                        </div>
                                    </div>
                                    <select class="custom-select" onblur="valida_campo_vazio(this,1)"
                                            onchange="valida_select(this)" v-model="form.cliente_id">
                                        <option value="">Selecione...</option>
                                        @foreach($clientes as $c)
                                            <option
                                                value="{{$c->id}}">{{$c->tipo=='pessoa_juridica' ? $c->clientepj['razaosocial'].' ('.$c->clientepj['nomefantasia'].')' : $c->clientepf['nome'].' - '.$c->clientepf['cpf']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Centro de Custo
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.empresa_id">
                                        @foreach($empresas as $e)
                                            <option value="{{$e->id}}">{{$e->razao_social}} - {{$e->cnpj}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Tipo de Contrato
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.tipo_contrato">
                                        <option value="Contrato de trabalho por prazo INDETERMINADO">Contrato de
                                            trabalho por prazo INDETERMINADO
                                        </option>
                                        <option value="Contrato de trabalho por prazo DETERMINADO">Contrato de trabalho
                                            por prazo DETERMINADO
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Dias Experiência
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           v-model="form.dias_experiencia">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Dias Prorrogação
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           v-model="form.dias_prorrogacao">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Tipo de Remuneração
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.tipo_remuneracao">
                                        <option value='Mensal'>Mensal</option>
                                        <option value='Quinzenal'>Quinzenal</option>
                                        <option value='Semanal'>Semanal</option>
                                        <option value='Diário'>Diário</option>
                                        <option value='Horário'>Horário</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Forma de pagamento
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.forma_pagamento">
                                        <option value='Semanal'>Semanal</option>
                                        <option value='Mensal'>Mensal</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Remuneração
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.remuneracao">
                                        <option value='Fixa'>Fixa</option>
                                        <option value='Variável'>Variável</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Valor R$
                                        </div>
                                    </div>
                                    <input type="text" class="form-control text-right"
                                           v-model="form.remuneracao_valor"
                                           placeholder="0,00"
                                           onblur="valida_dinheiro(this)"
                                           autocomplete="off" v-mascara:dinheiro>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Primeiro Emprego
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.primeiro_emprego">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Trabalha em outra empresa
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.outra_empresa">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12" v-show="form.outra_empresa" v-if="form.outra_empresa">
                            <fieldset>
                                <legend>INFORMAÇÕES DA OUTRA EMPRESA</legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-6" v-show="form.outra_empresa"
                                         v-if="form.outra_empresa">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">Razão Social
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control"
                                                       v-model="form.outra_empresa_razaosocial"
                                                       onblur="valida_campo_vazio(this,3)"
                                                       autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-4 col-md-3 col-lg-3" v-show="form.outra_empresa"
                                         v-if="form.outra_empresa">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">CNPJ
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control"
                                                       v-model="form.outra_empresa_cnpj"
                                                       placeholder="CNPJ"
                                                       onblur="valida_cnpj_vazio(this)"
                                                       autocomplete="off" v-mascara:cnpj>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-md-3 col-lg-3" v-show="form.outra_empresa"
                                         v-if="form.outra_empresa">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">Remuneração
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control text-right"
                                                       v-model="form.outra_empresa_remuneracao"
                                                       placeholder="0,00"
                                                       onblur="valida_dinheiro(this)"
                                                       autocomplete="off" v-mascara:dinheiro>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-md-6 col-lg-6" v-show="form.outra_empresa"
                                         v-if="form.outra_empresa">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">Obs.:
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control"
                                                       v-model="form.outra_empresa_obs"
                                                       autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>


                    </div>
                </fieldset>

                <fieldset>
                    <legend>FGTS</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-2 col-lg-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">É optante
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.fgts">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3" v-show="form.fgts"
                             v-if="form.fgts">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Data de opção/Admissão
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data" id="fgts_admissao"
                                           placeholder="00/00/0000"
                                           v-model="form.fgts_admissao" v-mascara:data>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-3 col-md-3 col-lg-3" v-show="form.fgts"
                             v-if="form.fgts">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Data de Retratação
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data" id="fgts_retratacao"
                                           placeholder="00/00/0000"
                                           v-model="form.fgts_retratacao" v-mascara:data>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-4" v-show="form.fgts"
                             v-if="form.fgts">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Banco Depostário
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           v-model="form.fgts_banco">
                                </div>
                            </div>
                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>CARGA HORÁRIA</legend>
                    <div class="row">

                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <button class="btn btn-secondary mb-2" @click="addLIHorario($event.target)">
                                <span class="fas fa-plus" aria-hidden="true"></span>
                                Adicionar Horário
                            </button>
                        </div>

                        <div class="col-12 col-sm-12" v-show="form.horarios.length>0"
                             v-for="(obj, index) in form.horarios" :key="obj.id">

                            <fieldset>
                                <legend>HORÁRIO @{{ index + 1 }}</legend>
                                <div class="row">

                                    <div class="col-12 col-sm-3 col-md-3 col-lg-3">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">Entrada 1º Turno
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control" v-mascara:hora
                                                       placeholder="00:00"
                                                       onblur="valida_campo_vazio(this,5)"
                                                       v-model="obj.entrada_turnoum">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-3 col-md-3 col-lg-3">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">Saída 1º Turno
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control" v-mascara:hora
                                                       placeholder="00:00"
                                                       onblur="valida_campo_vazio(this,5)" v-model="obj.saida_turnoum">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-3 col-md-3 col-lg-3">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">Entrada 2º Turno
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control" v-mascara:hora
                                                       placeholder="00:00"
                                                       v-model="obj.entrada_turnodois">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-3 col-md-3 col-lg-3">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">Saída 2º Turno
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control" v-mascara:hora
                                                       placeholder="00:00"
                                                       v-model="obj.saida_turnodois">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="alert alert-secondary">
                                            <h6>Dias da semana a considerar</h6>
                                            <div class="form-check-inline">
                                                <label class="form-check-label" style="cursor: pointer">
                                                    <input type="checkbox" class="form-check-input"
                                                           style="cursor: pointer;"
                                                           value="dom"
                                                           v-model="obj.diasSemana">Domingo
                                                </label>
                                            </div>
                                            <div class="form-check-inline">
                                                <label class="form-check-label" style="cursor: pointer">
                                                    <input type="checkbox" class="form-check-input"
                                                           style="cursor: pointer;"
                                                           value="seg"
                                                           v-model="obj.diasSemana">Segunda
                                                </label>
                                            </div>
                                            <div class="form-check-inline">
                                                <label class="form-check-label" style="cursor: pointer">
                                                    <input type="checkbox" class="form-check-input"
                                                           style="cursor: pointer;"
                                                           value="ter"
                                                           v-model="obj.diasSemana">Terça
                                                </label>
                                            </div>
                                            <div class="form-check-inline">
                                                <label class="form-check-label" style="cursor: pointer">
                                                    <input type="checkbox" class="form-check-input"
                                                           style="cursor: pointer;"
                                                           value="qua"
                                                           v-model="obj.diasSemana">Quarta
                                                </label>
                                            </div>
                                            <div class="form-check-inline">
                                                <label class="form-check-label" style="cursor: pointer">
                                                    <input type="checkbox" class="form-check-input"
                                                           style="cursor: pointer;"
                                                           value="qui"
                                                           v-model="obj.diasSemana">Quinta
                                                </label>
                                            </div>
                                            <div class="form-check-inline">
                                                <label class="form-check-label" style="cursor: pointer">
                                                    <input type="checkbox" class="form-check-input"
                                                           style="cursor: pointer;"
                                                           value="sex"
                                                           v-model="obj.diasSemana">Sexta
                                                </label>
                                            </div>
                                            <div class="form-check-inline">
                                                <label class="form-check-label" style="cursor: pointer">
                                                    <input type="checkbox" class="form-check-input"
                                                           style="cursor: pointer;"
                                                           value="sab"
                                                           v-model="obj.diasSemana">Sábado
                                                </label>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">Jornada de Trabalho
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control"
                                                       placeholder="Ex: 40h semanais"
                                                       onblur="valida_campo_vazio(this,1)"
                                                       v-model="obj.jornada">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button class="btn btn-secondary mb-2" @click="addLIHorario($event.target)">
                                            <span class="fas fa-plus" aria-hidden="true"></span>
                                            Adicionar Horário
                                        </button>

                                        <button class="btn btn-danger mb-2" @click="removerLIHorario(index)"><i
                                                class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </fieldset>

                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend style="text-transform: uppercase">Foto 3x4</legend>
                    <upload label="Selecionar Foto" :model="form.fotos"
                            :model-delete="form.fotosDel"
                            :apenas-imagens="true"
                            :quantidade="1"
                            :url="urlFotoUpload"
                            @onProgresso="fotoUploadAndamento=true"
                            @onFinalizado="fotoUploadAndamento=false"></upload>
                </fieldset>

                <fieldset>
                    <legend style="text-transform: uppercase">Anexos de Documentos</legend>
                    <upload :model="form.anexos"
                            :model-delete="form.anexosDel"
                            :url="urlAnexoUpload"
                            @onProgresso="anexoUploadAndamento=true"
                            @onFinalizado="anexoUploadAndamento=false"></upload>
                </fieldset>

                <fieldset>
                    <legend style="text-transform: uppercase">Outras informações</legend>

                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-2 col-lg-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Demitido
                                        </div>
                                    </div>
                                    <select class="custom-select" onchange="valida_select(this)"
                                            v-model="form.demitido">
                                        <option :value=true>Sim</option>
                                        <option :value=false>Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3" v-show="form.demitido" v-if="form.demitido">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Data Demissão
                                        </div>
                                    </div>
                                    <input type="text" class="form-control data"
                                           placeholder="00/00/0000" id="datademissao" onblur="valida_data_vazio(this)"
                                           autocomplete="off" v-mascara:data v-model="form.datademissao">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3" v-show="form.demitido" v-if="form.demitido">
                            <div class="form-group">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <div class="input-group-text">CRM
                                            </div>
                                        </div>
                                        <input type="text" class="form-control"
                                               onblur="valida_campo_vazio(this,1)"
                                               v-model="form.demissional_crm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Observações
                                        </div>
                                    </div>
                                    <input type="text" class="form-control"
                                           v-model="form.observacoes"
                                           autocomplete="off">
                                </div>
                            </div>
                        </div>

                    </div>
                </fieldset>

            </form>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-primary" v-show="editando && !atualizado" @click="alterar()">
                Alterar
            </button>
            <button type="button" class="btn btn-primary" v-show="!editando && !cadastrado" @click="cadastrar()">
                Cadastrar
            </button>
        </template>
    </modal>

    <form id="formBusca">
        <div class="row">
            <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                <div class="form-group">
                    <label>Buscar:</label>
                    <div class="input-group">
                        <span class="input-group-prepend">
                            <i class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></i>
                        </span>
                        <input type="text" id="campoBusca"
                               placeholder="Pesquise por (Nome ou CPF)" autocomplete="off"
                               class="form-control">

                    </div>
                </div>
            </div>

            <div class="col-12"></div>

            <div class="col-12 col-sm-6 col-md-2 col-lg-2">
                <div class="form-group">
                    <label>Por empresa:</label>
                    <div class="input-group">
                        <select class="form-control" v-model="controle.dados.empresa_id" @change="filtroEmpresa()">
                            <option value="" selected>Todas as empresas</option>
                            @foreach($empresas as $empresa)
                                <option value="{{$empresa->id}}">{{$empresa->razao_social}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <div class="form-group">
                    <label>Por cargo:</label>
                    <div class="input-group">
                        <select class="form-control" v-model="controle.dados.cargo_id" @change="filtroCargo()">
                            <option value="" selected>Todos os Cargos</option>
                            @foreach($cargos as $cargo)
                                <option
                                    value="{{$cargo->id}}">{{$cargo->titulo}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                <div class="form-group">
                    <label>Por cliente:</label>
                    <div class="input-group">
                        <select class="form-control" v-model="controle.dados.cliente_id" @change="filtroCliente()">
                            <option value="" selected>Todos os clientes</option>
                            @foreach($clientes as $cliente)
                                <option
                                    value="{{$cliente->id}}">{{$cliente->tipo=='pessoa_juridica' ? $cliente->clientepj->razaosocial. ' - '.$cliente->clientepj->cnpj : $cliente->clientepf->nome.' '.$cliente->clientepf->cpf}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>


        </div>


    </form>

    <button type="button" class="btn btn-success" id="btnAtualizar">Atualizar</button>
    <button type="button" class="btn btn-primary"
            @click="formNovo(); $refs.janelaCadastrar?.abrirModal()">
        Cadastrar
    </button>
    <button type="button" class="btn btn-primary"
            @click="exportExcel()">
        <i class="fas fa-file-excel"></i>
        Exportar Excel
    </button>

    {{--<a href="{{route('g.administracao.funcionarios.excel',['data'=>'1'])}}" class="btn btn-primary"><i class="fas fa-file-excel"></i>--}}
    {{--Exportar Excel</a>--}}

    <p class="text-center" v-if="controle.carregando">
        <i class="fa fa-spinner fa-pulse"></i> Carregando...
    </p>

    <div id="conteudo">
        <h4 v-show="!controle.carregando && lista.length==0"></h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-condensed"
                   v-if="!controle.carregando && lista.length > 0" style="font-size: 13px;">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">ID</th>
                    <th class="text-center">Empregador</th>
                    <th class="text-center">Nome</th>
                    <th class="text-center">Cliente</th>
                    <th class="text-center">Cargo</th>
                    <th class="text-center">Demitido</th>
                    <th class="text-center" colspan="4">Ações</th>
                </tr>
                </thead>
                <tbody v-for="(funcionario, index) in lista">
                {{--<tr>--}}
                <tr>
                    <td class="text-center">
                        @{{ funcionario.id }}
                    </td>

                    <td class="text-center">
                        @{{ funcionario.empresa.razao_social }}
                    </td>

                    <td class="text-center">
                        @{{ funcionario.nome }}
                    </td>

                    <td class="text-center">
                        @{{ funcionario.cliente.tipo=="pessoa_juridica" ? funcionario.cliente.clientepj.razaosocial :
                        funcionario.cliente.clientepf.nome }}
                    </td>

                    <td class="text-center">
                        @{{ funcionario.cargo.titulo }}
                    </td>

                    <td class="text-center">
                        @{{ funcionario.demitido ? 'Sim' : 'Não' }}
                    </td>

                    <td class="text-center">
                        <a href="javascript://" class="text-danger" title="Alterar"
                           @click.prevent="formAlterar(funcionario.id); $refs.janelaCadastrar?.abrirModal()">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>

                    </td>
                    <td class="text-center">
                        <a href="javascript://" title="ficha" @click.prevent="pdf(funcionario.id)"
                           class="text-dark">
                            <i class="fa fa-file-pdf"></i>
                        </a>
                        {{--<a href="javascript://" class="btn btn-danger btnFormExcluir"--}}
                        {{--@click.prevent="janelaConfirmar(funcionario.id)"--}}
                        {{--data-toggle="modal"--}}
                        {{--data-target="#janelaConfirmar">--}}
                        {{--<i class="fa fa-trash" aria-hidden="true"></i> Excluir--}}
                        {{--</a>--}}
                    </td>
                    <td class="text-center">
                        <div v-show="funcionario.anexos.length > 0 || funcionario.fotos.length > 0">
                            <a href="javascript://" style="cursor: pointer"
                               @click="abrirJanelaAnexo(funcionario.id); $refs.janelaAnexos?.abrirModal()"><i
                                    class="fa fa-paperclip text-info"></i></a>
                        </div>
                        <div v-show="funcionario.anexos.length == 0 && funcionario.fotos.length == 0">
                          <i class="fa fa-unlink text-info"></i>
                        </div>
                    </td>
                    <td class="text-center">

                        <a href="javascript://" @click="toggle(index)" style="cursor: pointer"><i
                                :class="!opened.includes(index) ? 'fa fa-sort-down' : 'fa fa-sort-up'"></i></a>
                    </td>
                </tr>
                <tr v-if="opened.includes(index)" class="bg-light">
                    <td class="text-center" colspan="2">CPF: <strong style="font-weight: bold">@{{ funcionario.cpf
                            }}</strong></td>
                    <td class="text-center">@{{ funcionario.documentoText }}: <strong style="font-weight: bold">@{{
                            funcionario.doc_numero }}</strong></td>
                    <td class="text-center">Nascimento: <strong style="font-weight: bold">@{{ funcionario.nascimento
                            }}</strong></td>
                    <td class="text-center">NIS (PIS/PASEP/NIT): <strong style="font-weight: bold">@{{ funcionario.nis
                            }}</strong></td>
                    <td class="text-center">Contato: <strong style="font-weight: bold"
                                                             v-for="tel in funcionario.telefones">@{{ tel.tipo }}: @{{
                            tel.numero }} / </strong></td>
                    <td class="text-center" colspan="4">Admissão: <strong style="font-weight: bold">@{{
                            funcionario.admissao }}</strong></td>
                </tr>
                </tbody>

            </table>
        </div>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.administracao.funcionarios.atualizar')}}" por-pagina="100"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/funcionarios/app.js')}}"></script>
@endpush
