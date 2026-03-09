@extends('layouts.sistema')
@section('title', 'Admissão')
@section('content_header')
    <h4 class="text-default">Processo</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal ref="filtroColunas" id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template #conteudo>
            <div class="row">
                <div class="col-sm-6" v-for="item in colunasTabela">
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" @click="item.checked = !item.checked;atualizaColunaTabelas()"
                               v-model="item.checked"
                               class="custom-control-input" :id="item.id">
                        <label class="custom-control-label"
                               :for="item.id">@{{item.label}}</label>
                    </div>
                </div>
            </div>
        </template>
    </modal>

    <modal ref="janelaAdmissaoAvulsa" id="janelaAdmissaoAvulsa" titulo="Admissão Avulsa" :size="95">
        <template #conteudo>
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
                               class="btn btn-sm mr-1 btn-primary"
                               target="_blank"
                               rel="noopener noreferrer">Verificar Pós Admissão</a>
                        </span>
                    </div>
                    <fieldset>
                        <legend>Dados Pessoais</legend>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label>CPF <span class="text-danger">*</span></label>
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
                                        <label>Nome <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="formAvulsa.curriculo.nome"
                                               placeholder="Nome"
                                               autocomplete="mybp" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>E-mail <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="formAvulsa.curriculo.email"
                                               placeholder="Ex.: email@email.com"
                                               autocomplete="mybp" onblur="validaEmail(this)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Nascimento <span class="text-danger">*</span></label>
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
                                        <label>Cota PCD (Lei nº 8.213/91) <span class="text-danger">*</span></label>
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
                                        <label>CID (Código Internacional de Doenças) <span class="text-danger">*</span></label>
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
                                        <label>Mãe <span class="text-danger">*</span></label>
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
                                        <legend>Contato <span class="text-danger">*</span></legend>
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
                                                    <label>Formação </label>
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
                                        <label>Vaga <span class="text-danger">*</span></label>
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
                                        <label>Indicado <span class="text-danger">*</span></label>
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
                                        <label>Quem indicou <span class="text-danger">*</span></label>
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
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!formAvulsa.cadastrado && !formAvulsa.preload"
                    @click="CadastraAvulsa">
                <i class="fa fa-save"></i> Salvar
            </button>
        </template>
    </modal>

    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :size="95">
        <template #conteudo>
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
                                <label>Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-model="form.curriculo.nome">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>E-mail <span class="text-danger">*</span></label>
                                <input type="text" class="form-control"
                                       :disabled="visualizar"
                                       onblur="validaEmailVazio(this)"
                                       v-model="form.curriculo.email">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Nascimento <span class="text-danger">*</span></label>
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
                                <label>Cota PCD (Lei nº 8.213/91) <span class="text-danger">*</span></label>
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
                                <label>CID (Código Internacional de Doenças) <span class="text-danger">*</span></label>
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
                                <label>Mãe <span class="text-danger">*</span></label>
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
                                    <label>Formação <span class="text-danger">*</span></label>
                                    <select class="form-control"
                                            onblur="valida_campo_vazio(this,1)"
                                            onchange="valida_campo_vazio(this,1)"
                                            :disabled="visualizar"
                                            v-model="form.curriculo.formacao">
                                        <option value="">Selecione ...</option>
                                        @foreach(\App\Models\Escolaridade::get() as $item)
                                            <option :value="{{$item->id}}">{{$item->tipo}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6"
                                 v-if="form.curriculo.formacao >=8">
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
                                <label>Vaga <span class="text-danger">*</span></label>
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
                                <label>Quem indicou <span class="text-danger">*</span></label>
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
        <template #rodape>
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="!atualizado  && !preload"
                        @click.prevent="alterar">
                    <i class="fa fa-edit"></i> Salvar
                </button>
            </div>

            {{--            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !cadastrado" @click="encaminhar()">--}}
            {{--                Admitir--}}
            {{--            </button>--}}
        </template>
    </modal>

    <modal ref="janelaAdmissaoMassa" id="janelaAdmissaoMassa" titulo="Admissão em massa" :size="95">
        <template #conteudo>
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
                                <label>Padrão de treinamento</label>
                                <select class="form-control" v-model="form_massa.segmento_treinamento_id">
                                    <option :value="null">Selecione</option>
                                    <option v-for="s in segmentos_treinamento" :key="s.id" :value="s.id">@{{ s.nome }}</option>
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
        <template #rodape>
            <div>
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="!form_massa.preload"
                        @click.prevent="CadastraMassa">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </div>
        </template>
    </modal>

    <modal ref="janelaDemitir" id="janelaDemitir" titulo="Demissão Avulsa" size="g">
        <template #conteudo>
            <preload v-if="modeldemissao.preload"></preload>
            <div v-if="!modeldemissao.preload">
                <fieldset style="margin-top: 0px">
                    <legend>Informações do Colaborador</legend>
                    <div style="text-transform: uppercase">
                        <span>Nome: <strong>@{{ modeldemissao.form.nome }}</strong></span><br>
                        <span>CPF: <strong>@{{ modeldemissao.form.cpf }}</strong></span><br>
                        <span>
                            Cargo: <strong>@{{ modeldemissao.form.cargo }}</strong> | Função: <strong>
                                @{{ modeldemissao.form.funcao }}</strong></span><br>
                        <span>Data de admissão: <strong>@{{ modeldemissao.form.data_admissao }}</strong></span><br>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Dados da demissão</legend>
                    <div class="row">
                        <div class="col-12">
                            <datepicker label="Data demissão" :disabled="modeldemissao.form.status === 'DEMITIDO'"
                                        v-model="modeldemissao.form.data_desmobilizacao"></datepicker>
                        </div>
                    </div>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <div v-if="modeldemissao.form.status !== 'DEMITIDO'">
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="!modeldemissao.preload"
                        @click.prevent="demiteColaborador">
                    <i class="fa fa-save"></i> Demitir
                </button>
            </div>
        </template>
    </modal>

    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <date-range-filter
                :key="'filtro-periodo'"
                v-model:enabled="controle.dados.filtroPeriodo"
                v-model:start-date="controle.dados.dataInicio"
                v-model:end-date="controle.dados.dataFim"
                :disabled="!!controle.carregando"
                :id-suffix="'periodo-' + hash"
                label="Por período"
                wrapper-class="col-12 col-md-3">
            </date-range-filter>

            <date-range-filter
                :key="'filtro-aso'"
                v-model:enabled="controle.dados.filtroAso"
                v-model:start-date="controle.dados.dataInicioAso"
                v-model:end-date="controle.dados.dataFimAso"
                :disabled="!!controle.carregando"
                :id-suffix="'aso-' + hash"
                label="Data do ASO"
                wrapper-class="col-12 col-md-3">
            </date-range-filter>

            <date-range-filter
                :key="'filtro-admissao'"
                v-model:enabled="controle.dados.filtroDataAdmissao"
                v-model:start-date="controle.dados.dataInicioAdmissao"
                v-model:end-date="controle.dados.dataFimAdmissao"
                :disabled="!!controle.carregando"
                :id-suffix="'admissao-' + hash"
                label="Data da Admissão"
                wrapper-class="col-12 col-md-3">
            </date-range-filter>
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
                           placeholder="Buscar por nome ou ID"
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
                <button type="button" class="btn btn-sm mr-1 btn-success mr-1 mb-2" :disabled="controle.carregando"
                        @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
                @can('admissao_processo_insert')
                    <button type="button" class="btn btn-sm mr-1 btn-primary mr-1 mb-2" :disabled="controle.carregando"
                            @click="formCadastraAvulsa(); $refs.janelaAdmissaoAvulsa?.abrirModal()"
                    >
                        <i class="fas fa-plus"></i>
                        ADMISSÃO AVULSA
                    </button>


                    <button class="btn btn-sm mr-1 btn-danger mb-2 mr-1"
                            :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                            :disabled="selecionados.length === 0" @click="selecionados = []">
                        <i class="fa fa-times"></i> LIMPAR SELEÇÃO
                    </button>

                    <button type="button" class="btn btn-sm mr-1 btn-primary mb-2 mr-1"
                            @click.prevent="exportaExcel()"
                            :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && lista.length===0 && selecionados.length === 0) ">
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL <span class="badge badge-light"
                                                                               v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                    </button>


                    <button class="btn btn-sm mr-1 btn-primary mb-2 mr-1"
                            :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                            :disabled="selecionados.length === 0"
                            @click="formCadastraMassa(); $refs.janelaAdmissaoMassa?.abrirModal()">
                        <i class="fa fa-plus"></i> ATUALIZAR SELECIONADOS <span class="badge badge-light"
                                                                                v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                    </button>
                @endcan

            </div>
        </div>

    </fieldset>

    <preload v-if="controle.carregando" class="text-center"></preload>

    <div id="conteudo">
        <div class="empty-state" v-show="!controle.carregando && lista.length===0">
            <div class="empty-state-icon"><i class="fas fa-user-clock"></i></div>
            <h3 class="empty-state-title">Nenhum registro encontrado</h3>
            <p class="empty-state-text">Ajuste os filtros ou aguarde novos candidatos no processo de admissão.</p>
        </div>

        <div class="cards-toolbar" v-show="!controle.carregando && lista.length > 0">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="selTodosProcesso"
                       :checked="tudoMarcado"
                       :disabled="comAdm.length === 0"
                       :style="comAdm.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                       @click="selecionaTodos">
                <label class="custom-control-label" for="selTodosProcesso" style="width: max-content">Selecionar todos com admissão</label>
            </div>
            <!-- <span class="cards-toolbar-count">@{{ lista.length }} registro(s)</span> -->
            <button class="btn btn-sm mr-1 btn-outline-secondary ml-auto" @click="$refs.filtroColunas?.abrirModal()" v-tippy content="Mostrar e Ocultar Colunas">
                <i class="bx bxs-filter-alt"></i> Colunas
            </button>
        </div>

        <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
            <div class="solicitacao-card" v-for="item in lista" :key="item.id"
                 :class="{ 'card-status-em-processo': !item.admissao, 'card-status-admitido': item.admissao && (item.admissao.status === 'ADMITIDO' || item.admissao.status === 'Admitido'), 'card-status-demitido': item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido') }">
                <div class="card-header-row">
                    <div class="card-left">
                        <label class="checkbox-inline mb-0" v-if="item.admissao">
                            <input type="checkbox" class="custom-checkbox" v-model="selecionados" :value="item.id" :id="'chk_' + item.id">
                        </label>
                        <input type="checkbox" class="custom-checkbox" v-else disabled title="Sem cadastro em Admissão">
                        <span class="badge-id">#@{{ item.id }}</span>
                        <div class="colaborador-principal">
                            <i class="fas fa-user-circle mr-1"></i>
                            <strong>@{{ item.curriculo.nome }}</strong>
                        </div>
                        <span class="status-badge" :class="{
                            'status-admitido': item.admissao && (item.admissao.status === 'ADMITIDO' || item.admissao.status === 'Admitido'),
                            'status-demitido': item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido'),
                            'status-processo': item.admissao && item.admissao.status && item.admissao.status !== 'ADMITIDO' && item.admissao.status !== 'DEMITIDO' && item.admissao.status !== 'Admitido' && item.admissao.status !== 'Demitido',
                            'status-pendente': !item.admissao
                        }">
                            @{{ item.admissao ? item.admissao.status : 'Em processo' }}
                        </span>
                    </div>
                    <div class="card-right">
                        <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                            <a
                                class="btn-actions-compact"
                                href="#"
                                role="button"
                                :id="`dropdownProcesso_${item.id}`"
                                aria-haspopup="true"
                                :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
                                @click.prevent.stop="toggleDropdown(item.id)"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div
                                class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                :class="{ show: isDropdownOpen(item.id) }"
                                :aria-labelledby="`dropdownProcesso_${item.id}`"
                                @click="fecharDropdown"
                            >
                                <a class="dropdown-item" href="javascript://" title="Admitir" @click.prevent="formEntrevistar(item.id); visualizar = false; $refs.janelaCadastrar?.abrirModal()" v-if="!filtrarDemitidos"><i class="fas fa-user-plus mr-2 text-success"></i> Admitir</a>
                                <a class="dropdown-item" href="javascript://" title="Demitir" @click.prevent="formDemitir(item); visualizar = false; $refs.janelaDemitir?.abrirModal()" v-if="permissoes.privilegio_processo_demitir && !filtrarDemitidos"><i class="fas fa-user-minus mr-2 text-danger"></i> Demitir</a>
                                <a class="dropdown-item" href="javascript://" title="Visualizar" @click.prevent="formEntrevistar(item.id); visualizar = true; $refs.janelaCadastrar?.abrirModal()"><i class="fas fa-eye mr-2 text-info"></i> Visualizar</a>
                                <div class="dropdown-divider" v-if="item.admissao"></div>
                                <a class="dropdown-item" v-if="item.admissao" :href="`${item.fc_token}/pdf`" title="Gerar PDFs" target="_blank"><i class="fas fa-file-pdf mr-2 text-danger"></i> Gerar PDF</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-details-row card-details-main">
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'tbl_cpf')?.checked">
                        <i class="fas fa-id-card"></i>
                        <span class="detail-label">CPF</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !item.curriculo.cpf }">@{{ item.curriculo.cpf || 'Não informado' }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-building"></i>
                        <span class="detail-label">Centro</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !(item.admissao && item.admissao.emp_centro_custo) }">@{{ (item.admissao && item.admissao.emp_centro_custo) ? item.admissao.emp_centro_custo : 'Não informado' }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-briefcase"></i>
                        <span class="detail-label">Cargo</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !item.vaga_aberta_municipio }">@{{ item.vaga_aberta_municipio || 'Não informado' }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'tbl_contato')?.checked">
                        <i class="fas fa-phone"></i>
                        <span class="detail-label">Contato</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !item.curriculo.tel_principal || !item.curriculo.tel_principal.numero }">@{{ item.curriculo.tel_principal && item.curriculo.tel_principal.numero ? item.curriculo.tel_principal.numero : 'Não informado' }}</span>
                    </div>
                    <div class="detail-item" v-if="AUTENTICADO.temFilial && item.admissao && item.admissao.emp_cnpj">
                        <i class="fas fa-building"></i>
                        <span class="detail-label">Unidade</span>
                        <span class="detail-value">@{{ item.admissao.emp_nome_fantasia }} (@{{ item.admissao.emp_tipo }})</span>
                    </div>
                </div>

                <div class="card-details-row card-details-docs" v-if="colunasTabela.find(c => c.id === 'pcd').checked || colunasTabela.find(c => c.id === 'enc_documento').checked || colunasTabela.find(c => c.id === 'enc_exame').checked || colunasTabela.find(c => c.id === 'enc_treinamento').checked || colunasTabela.find(c => c.id === 'resp_encaminhamento').checked || colunasTabela.find(c => c.id === 'cracha').checked || colunasTabela.find(c => c.id === 'foto_3x4').checked">
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'pcd').checked">
                        <span class="detail-check" :class="item.curriculo.pcd ? 'detail-check-yes' : 'detail-check-no'"><i :class="item.curriculo.pcd ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">PCD</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'enc_documento').checked">
                        <span class="detail-check" :class="(item.resultado_integrado && item.resultado_integrado.documentos_entregue) ? 'detail-check-yes' : 'detail-check-no'"><i :class="(item.resultado_integrado && item.resultado_integrado.documentos_entregue) ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">Enc. Doc</span>
                        <span class="detail-value detail-value-small" v-if="item.resultado_integrado && item.resultado_integrado.documentos_entregue_data">@{{ item.resultado_integrado.documentos_entregue_data }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'enc_exame').checked">
                        <span class="detail-check" :class="(item.resultado_integrado && item.resultado_integrado.encaminhado_exame) ? 'detail-check-yes' : 'detail-check-no'"><i :class="(item.resultado_integrado && item.resultado_integrado.encaminhado_exame) ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">Enc. Exame</span>
                        <span class="detail-value detail-value-small" v-if="item.resultado_integrado && item.resultado_integrado.encaminhado_exame_data">@{{ item.resultado_integrado.encaminhado_exame_data }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'enc_treinamento').checked">
                        <span class="detail-check" :class="(item.resultado_integrado && item.resultado_integrado.encaminhado_treinamento) ? 'detail-check-yes' : 'detail-check-no'"><i :class="(item.resultado_integrado && item.resultado_integrado.encaminhado_treinamento) ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">Enc. Trein.</span>
                        <span class="detail-value detail-value-small" v-if="item.resultado_integrado && item.resultado_integrado.encaminhado_treinamento_data">@{{ item.resultado_integrado.encaminhado_treinamento_data }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'resp_encaminhamento').checked">
                        <i class="fas fa-user-tie"></i>
                        <span class="detail-label">Resp. Enc.</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !item.resultado_integrado || !item.resultado_integrado.responsavel_envio }">@{{ item.resultado_integrado && item.resultado_integrado.responsavel_envio ? item.resultado_integrado.responsavel_envio : '—' }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'cracha').checked">
                        <span class="detail-check" :class="(item.admissao && item.admissao.numero_cracha) ? 'detail-check-yes' : 'detail-check-no'"><i :class="(item.admissao && item.admissao.numero_cracha) ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">Crachá</span>
                        <span class="detail-value detail-value-small" v-if="item.admissao && item.admissao.numero_cracha">@{{ item.admissao.numero_cracha }}</span>
                    </div>
                    <div class="detail-item" v-if="colunasTabela.find(c => c.id === 'foto_3x4').checked">
                        <span class="detail-check" :class="(item.curriculo.foto_tres && item.curriculo.foto_tres.length > 0) ? 'detail-check-yes' : 'detail-check-no'"><i :class="(item.curriculo.foto_tres && item.curriculo.foto_tres.length > 0) ? 'fas fa-check' : 'fas fa-minus'"></i></span>
                        <span class="detail-label">Foto 3x4</span>
                    </div>
                </div>

                <div class="card-details-row card-details-fixas">
                    <div class="detail-item">
                        <i class="fas fa-notes-medical"></i>
                        <span class="detail-label">Data ASO</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !getDataAso(item) }">@{{ getDataAso(item) || 'Não informado' }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar-check"></i>
                        <span class="detail-label">Data Admissão</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !item.admissao || !item.admissao.data_admissao }">@{{ item.admissao && item.admissao.data_admissao ? item.admissao.data_admissao : 'Não informado' }}</span>
                    </div>
                    <div class="detail-item" v-if="item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido')">
                        <i class="fas fa-calendar-times"></i>
                        <span class="detail-label">Data Demissão</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !getDataDemissao(item) }">@{{ getDataDemissao(item) || 'Não informado' }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-file-contract"></i>
                        <span class="detail-label">Tipo Admissão</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !getTipoAdmissao(item) }">@{{ getTipoAdmissao(item) || 'Não informado' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.admissao.admissao.atualizar')}}"
                            :por-pagina="controle.dados.pages"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/admissao/processo/app.js')}}"></script>
@endpush
@push('css')
    <style>
        /* Empty state */
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

        /* Toolbar */
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
        .cards-toolbar-count {
            font-size: 0.813rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Cards list */
        .cards-lista { display: flex; flex-direction: column; gap: 1rem; }
        .solicitacao-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 0;
            transition: all 0.25s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #6c757d;
            overflow: hidden;
        }
        .solicitacao-card.card-status-em-processo { border-left-color: #17a2b8; }
        .solicitacao-card.card-status-admitido { border-left-color: #28a745; }
        .solicitacao-card.card-status-demitido { border-left-color: #dc3545; }
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
        }
        .card-left { display: flex; align-items: center; gap: 0.75rem; flex: 1; overflow: hidden; min-width: 0; }
        .card-right { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
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
        .detail-value-small { font-size: 0.75rem; color: #6c757d; }
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
