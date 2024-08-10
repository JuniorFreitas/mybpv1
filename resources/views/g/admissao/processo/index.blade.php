@extends('layouts.sistema')
@section('title', 'Admissão')
@section('content_header')
    <h4 class="text-default">Processo</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template slot="conteudo">
            <div class="row">
                <div class="col-sm-6" v-for="item in colunasTabela">
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" @click="item.checked = !item.checked"
                               v-model="item.checked"
                               class="custom-control-input" :id="item.id">
                        <label class="custom-control-label"
                               :for="item.id">@{{item.label}}</label>
                    </div>
                </div>
            </div>
        </template>
    </modal>

    <modal id="janelaAdmissaoAvulsa" titulo="Admissão Avulsa" :size="95">
        <template slot="conteudo">
            <div class="alert alert-success text-center" v-show="formAvulsa.cadastrado">
                <h4><i class="icon fa fa-check"></i> Admissão Concluida!</h4>
            </div>

            <p class=" mt-2 text-center" v-if="formAvulsa.preload">
                <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
            </p>
            <div v-if="!formAvulsa.preload && !formAvulsa.cadastrado">
                <div v-if="!formAvulsa.preload">
                    <div class="alert alert-warning" v-show="formAvulsa.ex_funcionario">
                        <i class="fa fa-exclamation-triangle"></i> Ex-Funcionário
                        <span v-if="formAvulsa.pos_admissao_verificar">
                            <a :href="`${URL_ADMIN}/posadmissao?checkcpf=${formAvulsa.curriculo.cpf}`"
                               class="btn btn-sm btn-primary"
                               target="_blank"
                               rel="noopener noreferrer">Verificar Pós Admissão</a>
                        </span>
                    </div>
                    <fieldset>
                        <legend>Dados Pessoais</legend>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label>CPF</label>
                                    <input type="text" class="form-control" v-model="formAvulsa.curriculo.cpf"
                                           placeholder="CPF"
                                           ref="cpf"
                                           :disabled="disabledInput"
                                           @blur="buscaCpf"
                                           @keypress="buscaCpf"
                                           autocomplete="mybp" v-mascara:cpf>
                                </div>
                            </div>
                            <template v-if="exibiFormulario">
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Nome</label>
                                        <input type="text" class="form-control" v-model="formAvulsa.curriculo.nome"
                                               placeholder="Nome"
                                               autocomplete="mybp" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>E-mail</label>
                                        <input type="text" class="form-control" v-model="formAvulsa.curriculo.email"
                                               placeholder="Ex.: email@email.com"
                                               autocomplete="mybp" onblur="validaEmail(this)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Nascimento</label>
                                        <input type="text" class="form-control validacampo"
                                               v-model="formAvulsa.curriculo.nascimento"
                                               placeholder="Ex: 10/10/2010"
                                               v-mascara:data
                                               autocomplete="mybp"
                                               @keyup.prevent="valida_data_vazio($event.target,true)"
                                               @blur.prevent="valida_data_vazio($event.target,true)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Naturalidade</label>
                                        <input type="text" class="form-control" onblur="valida_campo(this,2)"
                                               v-model="formAvulsa.curriculo.naturalidade" :disabled="visualizar">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Cota PCD (Lei nº 8.213/91)</label>
                                        <select class="form-control" onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)"
                                                v-model="formAvulsa.curriculo.pcd">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4" v-if="formAvulsa.curriculo.pcd">
                                    <div class="form-group">
                                        <label>CID (Código Internacional de Doenças)</label>
                                        <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                               placeholder="Informe o CID" v-model="formAvulsa.curriculo.cid">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Sexo</label>
                                        <select class="form-control"
                                                v-model="formAvulsa.curriculo.sexo"
                                        >
                                            <option value="">Selecione</option>
                                            <option v-for="item in lista_sexos" :value="item">@{{item}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Estado Civil</label>
                                        <select
                                            class="form-control"
                                            v-model="formAvulsa.curriculo.estado_civil"
                                        >
                                            <option value="">Selecione</option>
                                            <option v-for="item in lista_estados_civis" :value="item">@{{item}}</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Pai</label>
                                        <input type="text" class="form-control"
                                               v-model="formAvulsa.curriculo.filiacao_pai"
                                               placeholder="Nome do Pai"
                                               autocomplete="mybp">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Mãe</label>
                                        <input type="text" class="form-control"
                                               v-model="formAvulsa.curriculo.filiacao_mae"
                                               placeholder="Nome da Mãe"
                                               autocomplete="mybp" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <fieldset>
                                        <legend>Endereço</legend>
                                        <div class="row">
                                            <div class="col-12">
                                                <endereco :obrigatorio="false" :model="formAvulsa.curriculo"></endereco>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>

                                <div class="col-12">
                                    <fieldset>
                                        <legend>Contato</legend>
                                        <div class="row">
                                            <div class="col-12">
                                                <telefone :model="formAvulsa.curriculo.telefones" :pais="false"
                                                          :model-delete="formAvulsa.curriculo.telefonesDelete"
                                                          :qnt_min="1"
                                                          :ramal="false"></telefone>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>

                                <div class="col-12">
                                    <fieldset>
                                        <legend>DOCUMENTOS</legend>
                                        <div class="row">
                                            <div class="col-12 col-sm-6 col-lg-2">
                                                <div class="form-group">
                                                    <label>CNH</label>
                                                    <input type="text" class="form-control"
                                                           onblur="valida_campo(this,1)"
                                                           v-model="formAvulsa.curriculo.cnh" :disabled="visualizar">
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>RG</label>
                                                    <input type="text" class="form-control"
                                                           onblur="valida_campo(this,2)"
                                                           v-model="formAvulsa.curriculo.rg" :disabled="visualizar">
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>RG Data Emissão</label>
                                                    <input type="text" class="form-control validacampo"
                                                           placeholder="dd/mm/aaaa"
                                                           v-model="formAvulsa.curriculo.rg_data_emissao" v-mascara:data
                                                           @keyup.prevent="valida_data($event.target)"
                                                           @blur.prevent="valida_data($event.target)"
                                                           :disabled="visualizar">
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>

                                <div class="col-12">
                                    <fieldset>
                                        <legend>Formação</legend>
                                        <div class="row">
                                            <div class="col-12 col-sm-6">
                                                <div class="form-group">
                                                    <label>Formação</label>
                                                    <select class="form-control"
                                                            v-model="formAvulsa.curriculo.formacao">
                                                        @foreach(\App\Models\Escolaridade::get() as $item)
                                                            <option value="{{$item->id}}">{{$item->tipo}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6"
                                                 v-if="formAvulsa.curriculo.formacao >=8">
                                                <div class="form-group">
                                                    <label>Curso</label>
                                                    <input type="text" class="form-control"
                                                           v-model="formAvulsa.curriculo.formacao_curso"
                                                           placeholder="Ex: Administração"
                                                           autocomplete="mybp" onblur="valida_campo_vazio(this,1)">
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </template>

                        </div>
                    </fieldset>

                    <template v-if="exibiFormulario">
                        <fieldset>
                            <legend>SOBRE A VAGA</legend>
                            <div class="row">

                                <div class="col-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <label>Vaga</label>
                                        <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                                      :valido="formAvulsa.feedback.vaga_id !== ''"
                                                      v-model="formAvulsa.feedback.autocomplete_label_vaga_modal"
                                                      placeholder="Digite uma vaga"
                                                      :formsm="false"
                                                      :id="`vaga_${hash}`"
                                                      @onblur="resetaCampoVagaModal"
                                                      @onselect="selecionaVagaModal"></autocomplete>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-12 col-md-6"
                                     v-if="listaProjetos.length || formAvulsa.feedback.vaga_projeto_id">
                                    <div class="form-group">
                                        <label>Projeto</label>
                                        <select class="form-control"
                                                v-model="formAvulsa.feedback.vaga_projeto_id">
                                            <option value="" selected>Selecione</option>
                                            <option v-for="item in listaProjetos" :value="item.id"
                                                    :key="item.projeto_id"
                                                    :disabled="!item.tem_vaga">
                                                @{{ item.projeto.nome }} - (@{{ item.qnt_preenchida }} de @{{
                                                item.qnt_total }})
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="">Data admissão prevista</label>
                                        <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                               v-mascara:data
                                               @keyup.prevent="valida_data($event.target)"
                                               @blur.prevent="valida_data($event.target)"
                                               v-model="formAvulsa.admissao.data_adm_prevista">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label>Ex funcionário</label>
                                        <select class="form-control"
                                                v-model="formAvulsa.parecer_rh.ex_funcionario">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label>Turno 6x2</label>
                                        <select class="form-control"
                                                v-model="formAvulsa.parecer_rh.turnos_seis_por_dois">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label>Indicado</label>
                                        <select class="form-control" onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)"
                                                v-model="formAvulsa.parecer_rh.indicacao">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-3" v-show="formAvulsa.parecer_rh.indicacao">
                                    <div class="form-group">
                                        <label>Quem indicou</label>
                                        <input type="text" class="form-control"
                                               v-model="formAvulsa.parecer_rh.indicado_por"
                                               placeholder="Nome"
                                               autocomplete="mybp" onblur="valida_campo_vazio(this,1)">
                                    </div>
                                </div>

                                <div class="col-12"></div>

                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label>Indicado para qual área?</label>
                                        <input type="text" class="form-control"
                                               v-model="formAvulsa.parecer_tecnica.indicado_area"
                                               placeholder="Área"
                                               autocomplete="mybp">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <fieldset>
                                        <legend>EPI</legend>
                                        <div class="row">
                                            <div class="col-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label>Calça</label>

                                                    <select class="form-control" v-model="formAvulsa.parecer_rh.calca">
                                                        <option value="">Selecione</option>
                                                        @foreach(range(34,56) as $i)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label>Bota</label>
                                                    <select class="form-control" :disabled="visualizar"
                                                            v-model="formAvulsa.parecer_rh.bota">
                                                        <option value="">Selecione</option>
                                                        @foreach(range(33,50) as $i)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label>Camisa proteção</label>
                                                    <select class="form-control"
                                                            v-model="formAvulsa.parecer_rh.camisa_protecao">
                                                        <option value="">Selecione</option>
                                                        @foreach(range(2,6) as $i)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label>Camisa de meia</label>
                                                    <select class="form-control"
                                                            v-model="formAvulsa.parecer_rh.camisa_meia">
                                                        <option value="">Selecione</option>
                                                        <option value="P">P</option>
                                                        <option value="M">M</option>
                                                        <option value="G">G</option>
                                                        <option value="GG">GG</option>
                                                        <option value="XG">XG</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>

                                <div class="col-12">
                                    <fieldset>
                                        <legend>Rotas</legend>
                                        <div class="row">
                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>Bairro Rota</label>
                                                    <input type="text" class="form-control"
                                                           v-model="formAvulsa.parecer_rota.bairro_rota"
                                                           placeholder="Bairro"
                                                           autocomplete="mybp">
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>Ponto de referência rota</label>
                                                    <input type="text" class="form-control"
                                                           v-model="formAvulsa.parecer_rota.ponto_referencia_rota"
                                                           placeholder="Ponto de referência"
                                                           autocomplete="mybp">
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>Ponto de referência residência</label>
                                                    <input type="text" class="form-control"
                                                           v-model="formAvulsa.parecer_rota.ponto_referencia_residencia"
                                                           placeholder="Ponto de referência"
                                                           autocomplete="mybp">
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>

                                {{--                                <div class="col-12">--}}
                                {{--                                    <fieldset>--}}
                                {{--                                        <legend>Testes</legend>--}}
                                {{--                                        <div class="row">--}}
                                {{--                                            <div class="col-12 col-sm-6">--}}
                                {{--                                                <div class="form-group">--}}
                                {{--                                                    <label>Qual o teste foi aplicado</label>--}}
                                {{--                                                    <input type="text" class="form-control"--}}
                                {{--                                                           v-model="formAvulsa.parecer_teste.qual_teste"--}}
                                {{--                                                           placeholder="Teste"--}}
                                {{--                                                           autocomplete="mybp">--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <div class="col-12 col-sm-6">--}}
                                {{--                                                <div class="form-group">--}}
                                {{--                                                    <label>Resultado do Teste</label>--}}
                                {{--                                                    <select class="form-control"--}}
                                {{--                                                            v-model="formAvulsa.parecer_teste.parecer_final_teste">--}}
                                {{--                                                        <option value="">Selecione</option>--}}
                                {{--                                                        <option value="NÃO SE APLICA">NÃO SE APLICA</option>--}}
                                {{--                                                        <option value="favoravel">Favorável</option>--}}
                                {{--                                                        <option value="restricao">Restrição</option>--}}
                                {{--                                                        <option value="desfavoravel">Desfavorável</option>--}}
                                {{--                                                    </select>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                        </div>--}}
                                {{--                                    </fieldset>--}}
                                {{--                                </div>--}}

                                {{--                                <div class="col-12">--}}
                                {{--                                    <fieldset>--}}
                                {{--                                        <legend>Técnica</legend>--}}
                                {{--                                        <div class="row">--}}

                                {{--                                            <div class="col-12 col-sm-6 col-md-4">--}}
                                {{--                                                <div class="form-group">--}}
                                {{--                                                    <label>Experiência com cargas rigger</label>--}}
                                {{--                                                    <select class="form-control"--}}
                                {{--                                                            v-model="formAvulsa.parecer_tecnica.experiencia_cargas_rigger">--}}
                                {{--                                                        <option :value="null">NÃO INFORMADO</option>--}}
                                {{--                                                        <option value="NÃO SE APLICA">NÃO SE APLICA</option>--}}
                                {{--                                                        <option value="Sim">Sim</option>--}}
                                {{--                                                        <option value="Não">Não</option>--}}
                                {{--                                                    </select>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}


                                {{--                                            <div class="col-12 col-sm-6 col-md-4">--}}
                                {{--                                                <div class="form-group">--}}
                                {{--                                                    <label>Opera plataforma móvel</label>--}}
                                {{--                                                    <select class="form-control"--}}
                                {{--                                                            v-model="formAvulsa.parecer_tecnica.opera_plat_movel">--}}
                                {{--                                                        <option :value="null">NÃO INFORMADO</option>--}}
                                {{--                                                        <option value="NÃO SE APLICA">NÃO SE APLICA</option>--}}
                                {{--                                                        <option value="Sim">Sim</option>--}}
                                {{--                                                        <option value="Não">Não</option>--}}
                                {{--                                                    </select>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <div class="col-12 col-sm-6 col-md-4">--}}
                                {{--                                                <div class="form-group">--}}
                                {{--                                                    <label>Opera ponte rolante</label>--}}
                                {{--                                                    <select class="form-control"--}}
                                {{--                                                            v-model="formAvulsa.parecer_tecnica.opera_plat_ponte">--}}
                                {{--                                                        <option :value="null">NÃO INFORMADO</option>--}}
                                {{--                                                        <option value="NÃO SE APLICA">NÃO SE APLICA</option>--}}
                                {{--                                                        <option value="true">Sim</option>--}}
                                {{--                                                        <option value="false">Não</option>--}}
                                {{--                                                    </select>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                        </div>--}}
                                {{--                                    </fieldset>--}}
                                {{--                                </div>--}}

                                <div class="col-12">
                                    <fieldset>
                                        <legend>RESULTADO INTEGRADO</legend>
                                        <form-resultado-integrado
                                            :form="formAvulsa.resultado_integrado"></form-resultado-integrado>
                                    </fieldset>
                                </div>

                            </div>

                        </fieldset>

                        <fieldset>
                            <legend>Admissão</legend>
                            <form-admissao :form="form.admissao"></form-admissao>
                            <dependentes :model="formAvulsa.curriculo.dependentes"
                                         :model-delete="formAvulsa.curriculo.dependentesDelete"></dependentes>
                            <dados-bancarios :model="formAvulsa.feedback.banco_conta"></dados-bancarios>

                            <div class="col-12">
                                <fieldset>
                                    <legend>FOTO ESCANEADA</legend>
                                    <upload :model="formAvulsa.curriculo.foto_tres"
                                            :model-delete="formAvulsa.curriculo.foto_tres_delete"
                                            url="{{ route('g.admissao.admissao.upload-anexos') }}"
                                            :apenas-imagens="true"
                                            :quantidade="1"
                                            label="Selecionar Imagem"
                                            @onProgresso="anexoUploadAndamento=true"
                                            @onFinalizado="anexoUploadAndamento=false"></upload>
                                </fieldset>
                            </div>
                        </fieldset>
                    </template>
                </div>
            </div>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-primary" v-show="!formAvulsa.cadastrado && !formAvulsa.preload"
                    @click="CadastraAvulsa">
                <i class="fa fa-save"></i> Salvar
            </button>
        </template>
    </modal>

    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="95">
        <template slot="conteudo">
            <div class="alert alert-success text-center" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i> Admissão Concluida!</h4>
            </div>

            <div class="alert alert-success text-center" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i> Alteração realizada com sucesso!</h4>
            </div>

            <preload v-if="preload"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''">

                <fieldset>
                    <legend>DADOS PESSOAIS</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-model="form.curriculo.nome">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="text" class="form-control"
                                       :disabled="visualizar"
                                       onblur="validaEmailVazio(this)"
                                       v-model="form.curriculo.email">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Nascimento</label>
                                <input type="text" class="form-control validacampo"
                                       :disabled="visualizar"
                                       v-model="form.curriculo.nascimento"
                                       placeholder="Ex: 10/10/2010"
                                       v-mascara:data
                                       autocomplete="mybp" @keyup.prevent="valida_data_vazio($event.target,true)"
                                       @blur.prevent="valida_data_vazio($event.target,true)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Naturalidade</label>
                                <input type="text" class="form-control" onblur="valida_campo(this,2)"
                                       v-model="form.curriculo.naturalidade" :disabled="visualizar">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Sexo</label>
                                <select
                                    class="form-control"
                                    v-model="form.curriculo.sexo"
                                    :disabled="visualizar"
                                >
                                    <option value="">Selecione</option>
                                    <option v-for="item in lista_sexos" :value="item">@{{item}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Estado Civil</label>
                                <select
                                    class="form-control"
                                    :disabled="visualizar"
                                    v-model="form.curriculo.estado_civil"
                                >
                                    <option value="">Selecione</option>
                                    <option v-for="item in lista_estados_civis" :value="item">@{{item}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Cota PCD (Lei nº 8.213/91)</label>
                                <select class="form-control" onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)"
                                        :disabled="visualizar"
                                        v-model="form.curriculo.pcd">
                                    <option value="">Selecione</option>
                                    <option :value='true'>Sim</option>
                                    <option :value='false'>Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4" v-if="form.curriculo.pcd">
                            <div class="form-group">
                                <label>CID (Código Internacional de Doenças)</label>
                                <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                       placeholder="Informe o CID" v-model="form.curriculo.cid">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Pai</label>
                                <input type="text" class="form-control"
                                       v-model="form.curriculo.filiacao_pai" :disabled="visualizar"
                                       placeholder="Nome do Pai"
                                       autocomplete="mybp">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Mãe</label>
                                <input type="text" class="form-control"
                                       v-model="form.curriculo.filiacao_mae" :disabled="visualizar"
                                       placeholder="Nome da Mãe"
                                       autocomplete="mybp" onblur="valida_campo_vazio(this,3)">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Endereço</legend>
                    <div class="row">
                        <div class="col-12">
                            <endereco :obrigatorio="false" :disabled="visualizar"
                                      :model="form.curriculo"></endereco>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>CONTATO</legend>
                    <div class="row">
                        <div class="col-12">
                            <telefone :model="form.curriculo.telefones" :pais="false"
                                      :model-delete="form.curriculo.telefonesDelete"
                                      :qnt_min="1"
                                      :disabled="visualizar"
                                      :ramal="false"></telefone>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>DOCUMENTOS</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-2">
                            <div class="form-group">
                                <label>CNH</label>
                                <input type="text" class="form-control"
                                       :disabled="visualizar"
                                       :value="form.parecer_rh.cnh ? form.parecer_rh.cnh_tipo : 'Não possui'">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>RG</label>
                                <input type="text" class="form-control" onblur="valida_campo(this,2)"
                                       v-model="form.curriculo.rg" :disabled="visualizar">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>RG Data Emissão</label>
                                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                       v-model="form.curriculo.rg_data_emissao" v-mascara:data
                                       @keyup.prevent="valida_data($event.target)"
                                       @blur.prevent="valida_data($event.target)" :disabled="visualizar">
                            </div>
                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>EPI</legend>
                    <div class="row">
                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label>Calça</label>

                                <select :disabled="visualizar"
                                        class="form-control"
                                        v-model="form.parecer_rh.calca"
                                >
                                    <option value="">Selecione</option>
                                    @foreach(range(34,56) as $i)
                                        <option value="{{$i}}">{{$i}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label>Bota</label>
                                <select
                                    class="form-control" :disabled="visualizar"
                                    v-model="form.parecer_rh.bota"
                                >
                                    <option value="">Selecione</option>
                                    @foreach(range(33,50) as $i)
                                        <option value="{{$i}}">{{$i}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label>Camisa proteção</label>

                                <select :disabled="visualizar" class="form-control"
                                        v-model="form.parecer_rh.camisa_protecao">
                                    <option value="">Selecione</option>
                                    @foreach(range(2,6) as $i)
                                        <option value="{{$i}}">{{$i}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label>Camisa de meia</label>
                                <select :disabled="visualizar"
                                        class="form-control"
                                        v-model="form.parecer_rh.camisa_meia">
                                    <option value="">Selecione</option>
                                    <option value="P">P</option>
                                    <option value="M">M</option>
                                    <option value="G">G</option>
                                    <option value="GG">GG</option>
                                    <option value="XG">XG</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="col-12">
                    <fieldset>
                        <legend>Formação</legend>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label>Formação</label>
                                    <select class="form-control"
                                            :disabled="visualizar"
                                            v-model="form.curriculo.formacao.id">
                                        @foreach(\App\Models\Escolaridade::get() as $item)
                                            <option :value="{{$item->id}}">{{$item->tipo}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6"
                                 v-if="form.curriculo.formacao.id >=8">
                                <div class="form-group">
                                    <label>Curso</label>
                                    <input type="text" class="form-control"
                                           :disabled="visualizar"
                                           v-model="form.curriculo.formacao_curso"
                                           placeholder="Ex: Administração"
                                           autocomplete="mybp" onblur="valida_campo_vazio(this,1)">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <fieldset>
                    <legend>Técnica</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Experiência com cargas rigger</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-model="form.parecer_tecnica.experiencia_cargas_rigger">
                                    <option :value="null">NÃO INFORMADO</option>
                                    <option value="NÃO SE APLICA">NÃO SE APLICA</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Opera plataforma móvel</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-model="form.parecer_tecnica.opera_plat_movel">
                                    <option :value="null">NÃO INFORMADO</option>
                                    <option value="NÃO SE APLICA">NÃO SE APLICA</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Opera ponte rolante</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-model="form.parecer_tecnica.opera_plat_ponte">
                                    <option :value="null">NÃO INFORMADO</option>
                                    <option value="NÃO SE APLICA">NÃO SE APLICA</option>
                                    <option value="true">Sim</option>
                                    <option value="false">Não</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>SOBRE A VAGA</legend>
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label>Vaga</label>
                                <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                              :valido="form.vagas_abertas_id !== ''"
                                              v-model="form.autocomplete_label_vaga_modal"
                                              placeholder="Digite uma vaga"
                                              :disabled="visualizar"
                                              :readonly="visualizar"
                                              :formsm="false"
                                              :id="`vaga_${hash}`"
                                              @onblur="resetaCampoVagaModalEditar"
                                              @onselect="selecionaVagaModalEditar"></autocomplete>
                            </div>
                        </div>

                        <div class="col-12 col-sm-12 col-md-6" v-if="listaProjetos.length || form.vaga_projeto_id">
                            <div class="form-group">
                                <label>Projeto</label>
                                <select class="form-control"
                                        v-model="form.vaga_projeto_id" :disabled="visualizar">
                                    <option value="" selected>Selecione</option>
                                    <option v-for="item in listaProjetos" :value="item.id" :key="item.projeto_id"
                                            :disabled="!item.tem_vaga">
                                        @{{ item.projeto.nome }} - (@{{ item.qnt_preenchida }} de @{{ item.qnt_total }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">Data admissão prevista</label>
                                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                       v-mascara:data
                                       @keyup.prevent="valida_data($event.target)"
                                       @blur.prevent="valida_data($event.target)"
                                       :disabled="visualizar"
                                       v-model="form.admissao.data_adm_prevista">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-group">
                                <label>Ex funcionário</label>
                                <select class="form-control"
                                        v-model="form.parecer_rh.ex_funcionario" :disabled="visualizar">
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-group">
                                <label>Turno 6x2</label>
                                <select class="form-control"
                                        v-model="form.parecer_rh.turnos_seis_por_dois" :disabled="visualizar">
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-group">
                                <label>Indicado</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-model="form.parecer_rh.indicacao">
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3" v-show="form.parecer_rh.indicacao">
                            <div class="form-group">
                                <label>Quem indicou</label>
                                <input type="text" class="form-control"
                                       v-model="form.parecer_rh.indicado_por" :disabled="visualizar"
                                       placeholder="Nome"
                                       autocomplete="mybp" onblur="valida_campo_vazio(this,1)">
                            </div>
                        </div>

                        <div class="col-12"></div>

                        <fieldset v-if="form.parecer_rota">
                            <legend>Rota</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label>Bairro Rota</label>
                                        <input type="text" class="form-control" :disabled="visualizar"
                                               :value="form.parecer_rota.bairro_rota">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label>Ponto Referência Rota</label>
                                        <input type="text" class="form-control" :disabled="visualizar"
                                               :value="form.parecer_rota.ponto_referencia_rota">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label>Ponto Referência Bairro</label>
                                        <input type="text" class="form-control" :disabled="visualizar"
                                               :value="form.parecer_rota.ponto_referencia_residencia">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset v-if="form.parecer_teste">
                            <legend>Testes</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label>Teste aplicado</label>
                                        <input type="text" class="form-control" :disabled="visualizar"
                                               :value="form.parecer_teste.qual_teste">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label>Resultado Teste Prático</label>
                                        <input type="text" class="form-control" :disabled="visualizar"
                                               :value="form.parecer_teste.parecer_final_teste">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>RESULTADO INTEGRADO</legend>
                    <form-resultado-integrado
                        :form="form.resultado_integrado" :disabled="visualizar"
                        :visualizar="visualizar"></form-resultado-integrado>
                </fieldset>


                <fieldset>
                    <legend class="text-uppercase">ADMISSÃO</legend>

                    <form-admissao :form="form.admissao"
                                   :visualizar="visualizar"></form-admissao>

                    <div class="col-12">
                        <dependentes :model="form.curriculo.dependentes" :visualizar='visualizar'
                                     :model-delete="form.curriculo.dependentesDelete"></dependentes>
                    </div>

                    {{--                    <div class="col-12">--}}
                    {{--                        <ferias-adquiridas :model="form.admissao.ferias_adquiridas" :visualizar='visualizar'--}}
                    {{--                                     :model-delete="form.admissao.ferias_adquiridasDelete"></ferias-adquiridas>--}}
                    {{--                    </div>--}}

                    <div class="col-12">
                        <dados-bancarios :model="form.banco_conta" :visualizar='visualizar'></dados-bancarios>
                    </div>

                    <div class='col-12'>
                        <fieldset>
                            <legend>FOTO ESCANEADA</legend>
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
            </div>
        </template>
        <template slot="rodape">
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="!atualizado  && !preload"
                        @click.prevent="alterar">
                    <i class="fa fa-edit"></i> Salvar
                </button>
            </div>

            {{--            <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !cadastrado" @click="encaminhar()">--}}
            {{--                Admitir--}}
            {{--            </button>--}}
        </template>
    </modal>

    <modal id="janelaAdmissaoMassa" titulo="Admissão em massa" :size="95">
        <template slot="conteudo">
            <preload v-if="form_massa.preload"></preload>
            <div v-if="!form_massa.preload">

                <fieldset>
                    <legend class="text-uppercase">INFORMAÇÕES</legend>
                    <div class="row">

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Tipo de admissão</label>
                                <select class="form-control" onchange="valida_campo(this,1)"
                                        onblur="valida_campo(this,1)"
                                        v-model="form_massa.tipo_admissao">
                                    <option value="">Selecione</option>
                                    <option value="TEMPORARIO">TEMPORARIO</option>
                                    <option value="INTERMITENTE">INTERMITENTE</option>
                                    <option value="DETERMINADO">DETERMINADO</option>
                                    <option value="FIXO">FIXO</option>
                                    <option value="PJ">PJ</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6" v-if="form_massa.tipo_admissao === 'FIXO'">
                            <div class="form-group">
                                <label>Prazo de experiência</label>
                                <select class="form-control" onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)"
                                        v-model="form_massa.prazo_experiencia">
                                    <option :value="''">Selecione</option>
                                    <option value="Nenhum">Nenhum</option>
                                    <option value="30+30">30+30</option>
                                    <option value="45+45">45+45</option>
                                    <option value="30+60">30+60</option>
                                    <option value="60+30">60+30</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6"
                             v-if="form_massa.tipo_admissao === 'TEMPORARIO' || form_massa.tipo_admissao === 'DETERMINADO'">
                            <div class="form-group">
                                <datepicker label="Data de encerramento"
                                            v-model="form_massa.data_encerramento"></datepicker>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Documento Portaria</label>
                                <select class="form-control" onchange="valida_campo(this,1)"
                                        onblur="valida_campo(this,1)"
                                        v-model="form_massa.documento_portaria">
                                    <option value="">Selecione</option>
                                    <option value="PENDENTE">PENDENTE</option>
                                    <option value="CONCLUIDO">CONCLUIDO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Data do ASO</label>
                                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                       v-model="form_massa.ultimo_aso.data_realizacao" v-mascara:data
                                       @keyup.prevent="valida_data($event.target)"
                                       @blur.prevent="valida_data($event.target)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Status Carteira de Treinamento e Etiqueta</label>
                                <select class="form-control" onchange="valida_campo(this,1)"
                                        onblur="valida_campo(this,1)"
                                        v-model="form_massa.status_carteira_treinamento">
                                    <option value="">Selecione</option>
                                    <option value="PENDENTE">PENDENTE</option>
                                    <option value="AGUARDANDO TREINAMENTO">AGUARDANDO TREINAMENTO</option>
                                    <option value="ENTREGUE">ENTREGUE</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" onchange="valida_campo_vazio(this,1)"

                                        onblur="valida_campo_vazio(this,1)"
                                        v-model="form_massa.status">
                                    <option value="">Selecione</option>
                                    <option value="AGUARDANDO QUALIFICAÇÃO">AGUARDANDO QUALIFICAÇÃO</option>
                                    <option value="PRONTO PARA ADMISSAO">PRONTO PARA ADMISSAO</option>
                                    <option value="ADMITIDO">ADMITIDO</option>
                                    <option value="STAND BY">STAND BY</option>
                                    <option value="PENDENTE ASO">PENDENTE ASO</option>
                                    <option value="PENDENTE DOCUMENTO">PENDENTE DOCUMENTO</option>
                                    <option value="PENDENTE TREINAMENTO">PENDENTE TREINAMENTO</option>
                                    <option value="CANCELADO">CANCELADO</option>
                                    <option value="ENCAMINHADO EXAME">ENCAMINHADO EXAME</option>
                                    <option value="DESISTÊNCIA">DESISTÊNCIA</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Data da Admissão</label>
                                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                       v-model="form_massa.data_admissao" v-mascara:data
                                       @keyup.prevent="valida_data($event.target)"
                                       @blur.prevent="valida_data($event.target)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Data da Entrega na área</label>
                                <input type="text" class="form-control validacampo" placeholder="dd/mm/aaaa"
                                       v-model="form_massa.data_entrega_area" v-mascara:data
                                       @keyup.prevent="valida_data($event.target)"
                                       @blur.prevent="valida_data($event.target)">
                            </div>
                        </div>


                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Biometria</label>
                                <select class="form-control"
                                        v-model="form_massa.biometria">
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </template>
        <template slot="rodape">
            <div>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="!form_massa.preload"
                        @click.prevent="CadastraMassa">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </div>
        </template>
    </modal>

    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <div class="col-12 col-md-2">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                           id="filtroIntervalo"
                           v-model="controle.dados.filtroPeriodo">
                    <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label=""
                                :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                v-model="controle.dados.periodo"></datepicker>
                </div>
            </div>

            <div class="col-12 col-md-2">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                           id="filtroIntervaloAso"
                           v-model="controle.dados.filtroAso">
                    <label class="form-check-label cursor-pointer" for="filtroIntervaloAso">Data do ASO</label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label=""
                                :disabled="controle.carregando || !controle.dados.filtroAso"
                                v-model="controle.dados.campoAso"></datepicker>
                </div>
            </div>

            <div class="col-12 col-md-2">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                           id="filtroAdmissao"
                           v-model="controle.dados.filtroDataAdmissao">
                    <label class="form-check-label cursor-pointer" for="filtroAdmissao">Data da Admissao</label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label=""
                                :disabled="controle.carregando || !controle.dados.filtroDataAdmissao"
                                v-model="controle.dados.campoAdmisaoData"></datepicker>
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-3" v-if="lista_ccs && AUTENTICADO.temFilial">
                <div class="form-group">
                    <label for="">Por Cnpj</label>
                    <select class="form-control form-control-sm" @change="changeCnpj"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoCnpj">
                        <option value="">Todos</option>
                        <option v-for="(item, key) in lista_ccs.cnpjs" :value="key" :keys="key">
                            @{{item.nome_fantasia}} - @{{item.cnpj}}
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3" v-if="lista_ccs">
                <div class="form-group">
                    <label for="">Centro de Custo</label>
                    <select class="form-control form-control-sm" @change="atualizar"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoCentroCusto">
                        <option value="">Todos</option>
                        <option :title="item.label" v-for="(item, key) in filtroListaCentroCustoCnpj"
                                :value="item.matriz ? item.id : item.filial_id"
                                :keys="key">
                            @{{item.label}}
                        </option>
                        <option value="--naoinformado--">--- Não Informado ---</option>
                    </select>
                </div>
            </div>


            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text"
                           placeholder="Buscar por cpf"
                           autocomplete="mybp"
                           onblur="valida_cpf(this)"
                           v-mascara:cpf
                           class="form-control form-control-sm" :disabled="controle.carregando"
                           v-model="controle.dados.campoCPF">
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text"
                           placeholder="Buscar por nome"
                           autocomplete="off"
                           class="form-control form-control-sm"
                           :disabled="controle.carregando"
                           v-model="controle.dados.campoBusca">
                </div>
            </div>

            <div class="col-12 col-sm-5 col-md-5 col-lg-5">
                <div class="form-group">
                    <label>Cargo</label>
                    <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                  :valido="controle.dados.campoVaga !== ''"
                                  v-model="controle.dados.autocomplete_label"
                                  :disabled="controle.carregando"
                                  placeholder="Por cargo"
                                  @onblur="resetaCampo"
                                  @onselect="selecionaVaga"></autocomplete>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>Estado</label>
                    <select2 :settings="settings2" :options="ufs" @change="atualizar" :disabled="controle.carregando"
                             v-model="controle.dados.campoUf"></select2>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3">
                <div class="form-group">
                    <label for="">Status admissão</label>
                    <select2 :settings="settings2" :options="listaStatusAdmissao" @change="atualizar"
                             :disabled="controle.carregando" v-model="controle.dados.campoStatusAdmissao"></select2>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3">
                <div class="form-group">
                    <label for="">Tipo admissão</label>
                    <select2 :settings="settings2" :options="listaTipoAdmissao" @change="atualizar"
                             :disabled="controle.carregando" v-model="controle.dados.campoTipoAdmissao"></select2>
                </div>
            </div>

            <div class="col-12 col-sm-2" v-if="permissoes.filtrar_demitido">
                <div class="form-group">
                    <label>Por Demitido</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            @change.prevent="filtrarDemitidos=controle.dados.campoDemitido"
                            v-model="controle.dados.campoDemitido">
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label for="">Exibir</label>
                    <select class="form-control form-control-sm" @change="atualizar"
                            :disabled="controle.carregando"
                            v-model="controle.dados.pages">
                        <option v-for="item in por_pagina" :value="item">@{{ item }}</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="col-12">
            <div class="row">
                <button type="button" class="btn btn-sm btn-success mr-1 mb-2" :disabled="controle.carregando"
                        @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
                @can('admissao_pos_admissao_insert')
                    <button type="button" class="btn btn-sm btn-primary mr-1 mb-2" :disabled="controle.carregando"
                            data-toggle="modal"
                            data-target="#janelaAdmissaoAvulsa"
                            @click="formCadastraAvulsa"
                    >
                        <i class="fas fa-plus"></i>
                        ADMISSÃO AVULSA
                    </button>


                    <button class="btn btn-sm btn-danger mb-2 mr-1"
                            :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                            :disabled="selecionados.length === 0" @click="selecionados = []">
                        <i class="fa fa-times"></i> LIMPAR SELEÇÃO
                    </button>

                    <button type="button" class="btn btn-sm btn-primary mb-2 mr-1"
                            @click.prevent="exportaExcel()"
                            :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && lista.length===0 && selecionados.length === 0) ">
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL <span class="badge badge-light"
                                                                               v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                    </button>


                    <button class="btn btn-sm btn-primary mb-2 mr-1"
                            :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                            :disabled="selecionados.length === 0"
                            data-toggle="modal"
                            data-target="#janelaAdmissaoMassa"
                            @click="formCadastraMassa">
                        <i class="fa fa-plus"></i> ATUALIZAR SELECIONADOS <span class="badge badge-light"
                                                                                v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                    </button>
                @endcan

            </div>
        </div>

    </fieldset>

    <preload v-if="controle.carregando" class="text-center"></preload>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>
        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="table table-centered bg-white">
                <thead>
                <tr>
                    <th class="text-center">

                        <input type="checkbox"
                               :checked="tudoMarcado"
                               :disabled="comAdm.length === 0"
                               :style="comAdm.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                               @click="selecionaTodos">
                    </th>
                    <th class="text-center text-nowrap">Nome</th>
                    <th class="text-center text-nowrap"
                        v-if="AUTENTICADO.temFilial"
                    >CNPJ
                    </th>
                    <th class="text-center text-nowrap">Centro de Custo</th>
                    <th class="text-center text-nowrap">Cargo</th>
                    <th class="text-center text-nowrap"
                        v-if="colunasTabela.find(item => item.id === 'pcd').checked">
                        PCD
                    </th>
                    <th class="text-center text-nowrap"
                        v-if="colunasTabela.find(item => item.id === 'enc_documento').checked">
                        Enc. Doc
                    </th>
                    <th class="text-center text-nowrap"
                        v-if="colunasTabela.find(item => item.id === 'enc_exame').checked"
                    >Enc. Exame
                    </th>
                    <th class="text-center text-nowrap"
                        v-if="colunasTabela.find(item => item.id === 'enc_treinamento').checked"
                    >Enc. Treinamento
                    </th>
                    <th class="text-center text-nowrap"
                        v-if="colunasTabela.find(item => item.id === 'resp_encaminhamento').checked"
                    >Resp. Encaminhamento
                    </th>
                    <th class="text-center text-nowrap"
                        v-if="colunasTabela.find(item => item.id === 'cracha').checked"
                    >Crachá
                    </th>
                    <th class="text-center text-nowrap"
                        v-if="colunasTabela.find(item => item.id === 'foto_3x4').checked"
                    >Foto 3x4
                    </th>
                    <th class="text-center text-nowrap" v-if="controle.dados.filtroAso">Data ASO</th>
                    <th class="text-center text-nowrap" v-if="controle.dados.filtroDataAdmissao">Data da Admissao</th>
                    <th class="text-center text-nowrap">Status Admissão</th>
                    <th>
                        <button class="btn btn-sm btn-primary mb-2" content="Mostrar e Ocultar Colunas" v-tippy
                                data-toggle="modal"
                                data-target="#filtroColunas">
                            <i class="bx bxs-filter-alt" aria-hidden="true"></i>
                        </button>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in lista" :key="item.id">
                    <td class="text-center">
                        <label :for="item.id">
                            <input
                                type="checkbox"
                                v-model="selecionados"
                                :value="item.id"
                                :id="item.id"
                                :style="item.admissao ? 'cursor:pointer' : 'cursor: not-allowed'"
                                :title="item.admissao ? null : 'Não possui cadastro em Admissão'"
                                v-if="item.admissao"
                            >
                            <input type="checkbox" v-else disabled="disabled"
                                   title="Sem parecer Informação em Admissão">

                        </label>
                    </td>

                    <td class="text-center">
                        @{{item.curriculo.nome}}
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

                    <td class="text-center">
                        @{{item.vaga_aberta_municipio}}
                    </td>
                    <td class="text-center"
                        v-show="colunasTabela.find(item => item.id === 'pcd').checked"
                    >
                        @{{item.curriculo.pcd ? 'Sim' : 'Não'}}
                    </td>

                    <td class="text-center"
                        v-show="colunasTabela.find(item => item.id === 'enc_documento').checked"
                    >
                        <span v-if="item.resultado_integrado">
                           @{{item.resultado_integrado.documentos_entregue ? 'Sim' : 'Não'}} <br>
                           @{{item.resultado_integrado.documentos_entregue_data}} <br>
                        </span>
                        <span v-else>---</span>
                    </td>

                    <td class="text-center"
                        v-show="colunasTabela.find(item => item.id === 'enc_exame').checked"
                    >
                        <span v-if="item.resultado_integrado">
                           @{{item.resultado_integrado.encaminhado_exame ? 'Sim' : 'Não'}} <br>
                           @{{item.resultado_integrado.encaminhado_exame_data}} <br>
                        </span>
                        <span v-else>---</span>
                    </td>

                    <td class="text-center"
                        v-show="colunasTabela.find(item => item.id === 'enc_treinamento').checked"
                    >
                        <span v-if="item.resultado_integrado">
                           @{{item.resultado_integrado.encaminhado_treinamento ? 'Sim' : 'Não'}} <br>
                           @{{item.resultado_integrado.encaminhado_treinamento_data}} <br>
                        </span>
                        <span v-else>---</span>
                    </td>

                    <td class="text-center"
                        v-show="colunasTabela.find(item => item.id === 'resp_encaminhamento').checked"
                    >
                        <span v-if="item.resultado_integrado">
                           @{{item.resultado_integrado.responsavel_envio}}
                        </span>
                        <span v-else>---</span>
                    </td>

                    <td class="text-center"
                        v-show="colunasTabela.find(item => item.id === 'cracha').checked"
                    >
                        @{{item.admissao ? item.admissao.numero_cracha : ''}}
                    </td>

                    <td class="text-center"
                        v-if="colunasTabela.find(item => item.id === 'foto_3x4').checked"
                    >
                        @{{item.curriculo.foto_tres.length > 0 ? 'SIM' : 'NÃO' }}
                    </td>

                    <td v-if="controle.dados.filtroAso">
                        @{{item.admissao ? item.admissao.ultimo_aso.data_realizacao : '' }}
                    </td>

                    <td v-if="controle.dados.filtroDataAdmissao">
                        @{{item.admissao ? item.admissao.data_admissao : '' }}
                    </td>

                    <td class="text-center">
                        @{{item.admissao ? item.admissao.status : ''}}
                    </td>

                    <td>

                        <div class="dropdown show">
                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                               id="dropdownMenuLink"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                 aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item" href="javascript://" title="Admitir"
                                   @click.prevent="formEntrevistar(item.id); visualizar = false"
                                   v-if="!filtrarDemitidos"
                                   data-toggle="modal"
                                   data-target="#janelaCadastrar"
                                >
                                    Admitir
                                </a>

                                <a class="dropdown-item" href="javascript://" title="Visualizar"
                                   @click.prevent="formEntrevistar(item.id); visualizar = true"
                                   data-toggle="modal"
                                   data-target="#janelaCadastrar"
                                >
                                    Visualizar
                                </a>

                                <a class="dropdown-item" v-if="item.admissao" :href="`${item.fc_token}/pdf`"
                                   title="Gerar PDFs"
                                   target="_blank">
                                    Gerar PDF
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.admissao.admissao.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/admissao/processo/app.js')}}"></script>
@endpush
