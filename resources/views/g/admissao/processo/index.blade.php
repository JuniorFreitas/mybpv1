@extends('layouts.sistema')
@section('title', 'Admissão')
@section('content_header')
    <h4 class="text-default">Processo</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template slot="conteudo">
            <div class="custom-control custom-switch mb-2" v-if="cliente_id === 0">
                <input type="checkbox" v-model="colunasTabela.cliente"
                       @click="colunasTabela.cliente = !colunasTabela.cliente" class="custom-control-input"
                       id="cliente">
                <label class="custom-control-label"
                       for="cliente">EMPRESA</label>
            </div>

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.pcd" @click="colunasTabela.pcd = !colunasTabela.pcd"
                       class="custom-control-input" id="pcd">
                <label class="custom-control-label"
                       for="pcd">PCD</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.rota_transporte"
                       @click="colunasTabela.rota_transporte = !colunasTabela.rota_transporte"
                       class="custom-control-input" id="rota_transporte">
                <label class="custom-control-label"
                       for="rota_transporte">ROTA TRANSPORTE</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.rh_nota"
                       @click="colunasTabela.rh_nota = !colunasTabela.rh_nota"
                       class="custom-control-input" id="rh_nota">
                <label class="custom-control-label"
                       for="rh_nota">PARECER RH NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.entrevista_tecnica"
                       @click="colunasTabela.entrevista_tecnica = !colunasTabela.entrevista_tecnica"
                       class="custom-control-input" id="entrevista_tecnica">
                <label class="custom-control-label"
                       for="entrevista_tecnica">ENTREVISTA TÉCNICA NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.teste_pratico"
                       @click="colunasTabela.teste_pratico = !colunasTabela.teste_pratico" class="custom-control-input"
                       id="teste_pratico">
                <label class="custom-control-label"
                       for="teste_pratico">TESTE PRÁTICO</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id > 1">
                <input type="checkbox" v-model="colunasTabela.parecer_individual"
                       @click="colunasTabela.parecer_individual = !colunasTabela.parecer_individual"
                       class="custom-control-input" id="parecer_individual">
                <label class="custom-control-label"
                       for="parecer_individual">PARECER INDIVIDUAL</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id > 1">
                <input type="checkbox" v-model="colunasTabela.nota_individual"
                       @click="colunasTabela.nota_individual = !colunasTabela.nota_individual"
                       class="custom-control-input" id="nota_individual">
                <label class="custom-control-label"
                       for="nota_individual">NOTA INDIVIDUAL</label>
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
                                           autocomplete="mastertag" v-mascara:cpf>
                                </div>
                            </div>
                            <template v-if="exibiFormulario">
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Nome</label>
                                        <input type="text" class="form-control" v-model="formAvulsa.curriculo.nome"
                                               placeholder="Nome"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Nascimento</label>
                                        <input type="text" class="form-control"
                                               v-model="formAvulsa.curriculo.nascimento"
                                               placeholder="Ex: 10/10/2010"
                                               v-mascara:data
                                               autocomplete="mastertag" onblur="valida_data_vazio(this)">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Pai</label>
                                        <input type="text" class="form-control"
                                               v-model="formAvulsa.curriculo.filiacao_pai"
                                               placeholder="Nome do Pai"
                                               autocomplete="mastertag">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Mãe</label>
                                        <input type="text" class="form-control"
                                               v-model="formAvulsa.curriculo.filiacao_mae"
                                               placeholder="Nome da Mãe"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>E-mail</label>
                                        <input type="text" class="form-control" v-model="formAvulsa.curriculo.email"
                                               placeholder="Ex.: email@email.com"
                                               autocomplete="mastertag" onblur="validaEmailVazio(this)">
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
                                                          :qnt_min="1"
                                                          :qnt_max="1"
                                                          :ramal="false"></telefone>
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
                                                           autocomplete="mastertag" onblur="valida_campo_vazio(this,1)">
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
                            <legend>Informações</legend>
                            <div class="row">

                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label>Vaga</label>
                                        <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                                      :valido="formAvulsa.feedback.vaga_id !== ''"
                                                      v-model="formAvulsa.feedback.autocomplete_label_vaga_modal"
                                                      placeholder="Selecione uma vaga"
                                                      :formsm="false"
                                                      :id="`vaga_${hash}`"
                                                      @onblur="resetaCampoVagaModal"
                                                      @onselect="selecionaVagaModal"></autocomplete>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label for="Cidade">Cidade</label>
                                        <autocomplete :caminho="todos_municipios"
                                                      :valido="formAvulsa.curriculo.municipio_id !== ''"
                                                      v-model="formAvulsa.curriculo.autocomplete_label_municipio_modal"
                                                      placeholder="Selecione um municipio"
                                                      :formsm="false"
                                                      :id="`mun_${hash}`"
                                                      @onblur="resetaCampoMunicipioModal"
                                                      @onselect="selecionaMunicipioModal"></autocomplete>
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
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,1)">
                                    </div>
                                </div>

                                <div class="col-12"></div>

                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label>Indicado para qual área?</label>
                                        <input type="text" class="form-control"
                                               v-model="formAvulsa.parecer_tecnica.indicado_area"
                                               placeholder="Área"
                                               autocomplete="mastertag">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <fieldset>
                                        <legend>EPI</legend>
                                        <div class="row">
                                            <div class="col-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label>Calça</label>

                                                    <select onchange="valida_campo_vazio(this,1)"
                                                            onblur="valida_campo_vazio(this,1)"
                                                            class="form-control" v-model="formAvulsa.parecer_rh.calca"
                                                    >
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
                                                    <select onchange="valida_campo_vazio(this,1)"
                                                            onblur="valida_campo_vazio(this,1)"
                                                            class="form-control" :disabled="visualizar"
                                                            v-model="formAvulsa.parecer_rh.bota"
                                                    >
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

                                                    <select onchange="valida_campo_vazio(this,1)"
                                                            onblur="valida_campo_vazio(this,1)"
                                                            class="form-control"
                                                            v-model="formAvulsa.parecer_rh.camisa_protecao"

                                                    >
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
                                                    <select onchange="valida_campo_vazio(this,1)"
                                                            onblur="valida_campo_vazio(this,1)"
                                                            class="form-control"
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
                                                           autocomplete="mastertag">
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>Ponto de referência rota</label>
                                                    <input type="text" class="form-control"
                                                           v-model="formAvulsa.parecer_rota.ponto_referencia_rota"
                                                           placeholder="Ponto de referência"
                                                           autocomplete="mastertag">
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>Ponto de referência residência</label>
                                                    <input type="text" class="form-control"
                                                           v-model="formAvulsa.parecer_rota.ponto_referencia_residencia"
                                                           placeholder="Ponto de referência"
                                                           autocomplete="mastertag">
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>

                                <div class="col-12">
                                    <fieldset>
                                        <legend>Testes</legend>
                                        <div class="row">
                                            <div class="col-12 col-sm-6">
                                                <div class="form-group">
                                                    <label>Qual o teste foi aplicado</label>
                                                    <input type="text" class="form-control"
                                                           v-model="formAvulsa.parecer_teste.ponto_referencia_residencia"
                                                           placeholder="Teste"
                                                           autocomplete="mastertag">
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6">
                                                <div class="form-group">
                                                    <label>Resultado do Teste</label>
                                                    <select class="form-control"
                                                            v-model="formAvulsa.parecer_teste.parecer_final_teste">
                                                        <option value="">Selecione</option>
                                                        <option value="favoravel">Favorável</option>
                                                        <option value="restricao">Restrição</option>
                                                        <option value="desfavoravel">Desfavorável</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                    </fieldset>
                                </div>

                                <div class="col-12">
                                    <fieldset>
                                        <legend>Técnica</legend>
                                        <div class="row">

                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>Experiencia com cargas rigger</label>
                                                    <select class="form-control"
                                                            v-model="formAvulsa.parecer_tecnica.experiencia_cargas_rigger">
                                                        <option :value="null">NÃO INFORMADO</option>
                                                        <option :value="true">Sim</option>
                                                        <option :value="false">Não</option>
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>Opera plataforma móvel</label>
                                                    <select class="form-control"
                                                            v-model="formAvulsa.parecer_tecnica.opera_plat_movel">
                                                        <option :value="null">NÃO INFORMADO</option>
                                                        <option :value="true">Sim</option>
                                                        <option :value="false">Não</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>Opera ponte rolante</label>
                                                    <select class="form-control"
                                                            v-model="formAvulsa.parecer_tecnica.opera_plat_ponte">
                                                        <option :value="null">NÃO INFORMADO</option>
                                                        <option :value="true">Sim</option>
                                                        <option :value="false">Não</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                    </fieldset>
                                </div>

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

                            <div class="col-12">
                                <dados-bancarios :model="formAvulsa.curriculo.banco_conta"></dados-bancarios>
                            </div>


                            <div class="col-12">
                                <fieldset>
                                    <legend>FOTO ESCANEADA</legend>
                                    <upload :model="form.admissao.foto_tres"
                                            :model-delete="form.admissao.foto_tresDel"
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
                    <legend class="text-uppercase">INFORMAÇÕES</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-model="form.curriculo.nome">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <div class="form-group">
                                <label>Idade</label>
                                <input type="text" class="form-control" disabled="disabled" readonly="readonly"
                                       :value="form.curriculo.idade">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <div class="form-group">
                                <label>PCD</label>
                                <input type="text" class="form-control" disabled="disabled" readonly="readonly"
                                       :value="form.curriculo.pcd ? 'Sim' : 'Não'">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <div class="form-group">
                                <label>CNH</label>
                                <input type="text" class="form-control" disabled="disabled" readonly="readonly"
                                       :value="form.parecer_rh.cnh ? form.parecer_rh.cnh_tipo : 'Não possui'">
                            </div>
                        </div>

                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label>Calça</label>

                                <select onchange="valida_campo_vazio(this,1)" :disabled="visualizar"
                                        onblur="valida_campo_vazio(this,1)"
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
                                <select onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)"
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

                                <select onchange="valida_campo_vazio(this,1)" :disabled="visualizar"
                                        onblur="valida_campo_vazio(this,1)"
                                        class="form-control"
                                        v-model="form.parecer_rh.camisa_protecao"

                                >
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
                                <select onchange="valida_campo_vazio(this,1)" :disabled="visualizar"
                                        onblur="valida_campo_vazio(this,1)"
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


                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Pai</label>
                                <input type="text" class="form-control"
                                       v-model="form.curriculo.filiacao_pai" :disabled="visualizar"
                                       placeholder="Nome do Pai"
                                       autocomplete="mastertag">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Mãe</label>
                                <input type="text" class="form-control"
                                       v-model="form.curriculo.filiacao_mae" :disabled="visualizar"
                                       placeholder="Nome da Mãe"
                                       autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Vaga</label>
                                <input type="text" class="form-control" disabled="disabled" readonly="readonly"
                                       :value="form.vaga_selecionada.nome">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Ex funcionário</label>
                                <input type="text" class="form-control" disabled="disabled" readonly="readonly"
                                       :value="form.parecer_rh.ex_funcionario ? 'Sim' : 'Não'">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Contato</label>
                                <input type="text" class="form-control" disabled="disabled" readonly="readonly"
                                       :value="form.tel_principal ? form.tel_principal.numero: 'não informado'">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="text" class="form-control" disabled="disabled" readonly="readonly"
                                       :value="form.curriculo.email">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Disponibilidade para turnos 6X2</label>
                                <input type="text" class="form-control" disabled="disabled" readonly="readonly"
                                       :value="form.parecer_rh.turnos_seis_por_dois ? 'Sim': 'Não'">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Indicado por quem</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-model="form.indicado_por">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Indicado para qual área</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-model="form.indicado_area">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Endereço</label>
                                <input type="text" class="form-control" disabled="disabled" readonly="readonly"
                                       :value="form.curriculo.logradouro+', '+form.curriculo.bairro+', '+form.curriculo.municipio+'-'+form.curriculo.uf">
                            </div>
                        </div>

                        <template v-if="form.parecer_rota">
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label>Bairro Rota</label>
                                    <input type="text" class="form-control" disabled="disabled"
                                           readonly="readonly"
                                           :value="form.parecer_rota.bairro_rota">
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label>Ponto Referência Rota</label>
                                    <input type="text" class="form-control" disabled="disabled"
                                           readonly="readonly"
                                           :value="form.parecer_rota.ponto_referencia_rota">
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label>Ponto Referência Bairro</label>
                                    <input type="text" class="form-control" disabled="disabled"
                                           readonly="readonly"
                                           :value="form.parecer_rota.ponto_referencia_residencia">
                                </div>
                            </div>
                        </template>


                        <template v-if="form.parecer_teste">
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label>Teste aplicado</label>
                                    <input type="text" class="form-control" disabled="disabled"
                                           readonly="readonly"
                                           :value="form.parecer_teste.qual_teste">
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label>Resultado Teste Prático</label>
                                    <input type="text" class="form-control" disabled="disabled"
                                           readonly="readonly"
                                           :value="form.parecer_teste.parecer_final_teste">
                                </div>
                            </div>
                        </template>

                        <template v-if="form.parecer_tecnica">
                            <div class="col-12 col-sm-6 col-lg-3">
                                <div class="form-group">
                                    <label>Rigger</label>
                                    <input type="text" class="form-control" disabled="disabled"
                                           readonly="readonly"
                                           :value="form.parecer_tecnica.experiencia_cargas_rigger ? 'Sim' : 'Não'">
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <div class="form-group">
                                    <label>Plataforma Movél</label>
                                    <input type="text" class="form-control" disabled="disabled"
                                           readonly="readonly"
                                           :value="form.parecer_tecnica.opera_plat_movel ? 'Sim' : 'Não'">
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label>Ponte Rolante</label>
                                    <input type="text" class="form-control" disabled="disabled"
                                           readonly="readonly"
                                           :value="form.parecer_tecnica.opera_plat_ponte ? 'Sim' : 'Não'">
                                </div>
                            </div>
                        </template>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-uppercase">ADMISSÃO</legend>

                    <form-admissao :form="form.admissao" :visualizar='visualizar'
                                   :cliente_id='form.cliente_id'></form-admissao>

                    <div class="col-12">
                        <dados-bancarios :model="form.curriculo.banco_conta"></dados-bancarios>
                    </div>

                    <div class='col-12'>
                        <fieldset>
                            <legend>FOTO ESCANEADA</legend>
                            <upload :model='form.curriculo.foto_tres'
                                    :model-delete='form.curriculo.foto_tresDel' :leitura='visualizar'
                                    url="{{ route('g.admissao.admissao.upload-anexos') }}"
                                    :apenas-imagens='true'
                                    :quantidade='1'
                                    label='Selecionar Imagem'
                                    @onProgresso='anexoUploadAndamento=true'
                                    @onFinalizado='anexoUploadAndamento=false'></upload>
                        </fieldset>
                    </div>
                </fieldset>
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

    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <div class="col-12 col-md-3">
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

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text"
                           placeholder="Buscar por nome"
                           autocomplete="off"
                           class="form-control form-control-sm" :disabled="controle.carregando"
                           v-model="controle.dados.campoBusca">
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text"
                           placeholder="Buscar por cpf"
                           autocomplete="mastertag"
                           onblur="valida_cpf(this)"
                           v-mascara:cpf
                           class="form-control form-control-sm" :disabled="controle.carregando"
                           v-model="controle.dados.campoCPF">
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="form-group">
                    <label>Cargo</label>
                    <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                  :valido="controle.dados.campoVaga !== ''"
                                  v-model="controle.dados.autocomplete_label"
                                  :disabled="controle.carregando"
                                  placeholder="Por vaga"
                                  @onblur="resetaCampo"
                                  @onselect="selecionaVaga"></autocomplete>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3" v-if="cliente_id === 0">
                <div class="form-group">
                    <label>Cliente</label>
                    <autocomplete :disabled="controle.carregando"
                                  :caminho="controle.dados.caminho_cliente_autocomplete"
                                  :valido="controle.dados.campoCliente !== ''"
                                  v-model="controle.dados.autocomplete_label_cliente"
                                  placeholder="Por cliente"
                                  @onblur="resetaCampoCliente"
                                  @onselect="selecionaCliente"></autocomplete>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>Estado</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoUf">
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
                    <label for="">Exibir</label>
                    <select class="form-control form-control-sm" @change="atualizar"
                            :disabled="controle.carregando"
                            v-model="controle.dados.pages">
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
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
                    <i class="fa fa-times"></i> Limpar seleção
                </button>
                <form target="_blank"
                      action="{{ \App\Models\Sistema::UrlServidor }}/admissao/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS"
                      method="get">
                    @csrf
                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                    <input type="hidden" name="campoVaga" :value="controle.dados.campoVaga">
                    <input type="hidden" name="campoCliente" :value="controle.dados.campoCliente">
                    <input type="hidden" name="campoUf" :value="controle.dados.campoUf">
                    <input type="hidden" name="campoRh" :value="controle.dados.campoRh">
                    <input type="hidden" name="campoFinalRh" :value="controle.dados.campoFinalRh">
                    <input type="hidden" name="campoRota" :value="controle.dados.campoRota">
                    <input type="hidden" name="campoTecnica" :value="controle.dados.campoTecnica">
                    <input type="hidden" name="campoTeste" :value="controle.dados.campoTeste">
                    <input type="hidden" name="campoPcd" :value="controle.dados.campoPcd">
<!--                    <button type="submit" class="btn btn-sm btn-primary mb-1"
                            :disabled="controle.carregando || (!controle.carregando && lista.length===0 && selecionados.length === 0) ">
                        <i class="fas fa-file-excel"></i> Exportar Excel <span class="badge badge-light"
                                                                               v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                    </button>-->
                </form>
            </div>
        </div>

    </fieldset>

    <preload v-if="controle.carregando" class="text-center"></preload>

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
                               :disabled="comAdm.length === 0"
                               style="cursor: pointer"
                               @click="selecionaTodos">
                    </th>
                    <th>Nome</th>
                    <th v-if="cliente_id === 0  && colunasTabela.cliente">Cliente</th>
                    <th>Cargo</th>
                    <th v-if="colunasTabela.pcd">PCD</th>
                    <th>Enc. Doc</th>
                    <th>Enc. Exame</th>
                    <th>Enc. Treinamento</th>
                    <th>Resp. Encaminhamento</th>
                    <th>Crachá</th>
                    <th>Foto 3x4</th>
                    <th>Status Admissão</th>
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
                <tr v-for="item in lista">
                    <td>
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

                    <td>
                        @{{item.curriculo.nome}}
                    </td>

                    <td v-if="cliente_id === 0  && colunasTabela.cliente">
                        @{{item.cliente.nome_fantasia ?
                        item.cliente.nome_fantasia : item.cliente.nome}}
                    </td>
                    <td>
                        @{{item.vaga_selecionada.nome}}
                    </td>
                    <td v-show="colunasTabela.pcd">
                        @{{item.curriculo.pcd ? 'Sim' : 'Não'}}
                    </td>

                    <td>
                        <span v-if="item.resultado_integrado">
                           @{{item.resultado_integrado.documentos_entregue ? 'Sim' : 'Não'}} <br>
                           @{{item.resultado_integrado.documentos_entregue_data}} <br>
                        </span>
                        <span v-else>---</span>
                    </td>

                    <td>
                        <span v-if="item.resultado_integrado">
                           @{{item.resultado_integrado.encaminhado_exame ? 'Sim' : 'Não'}} <br>
                           @{{item.resultado_integrado.encaminhado_exame_data}} <br>
                        </span>
                        <span v-else>---</span>
                    </td>

                    <td>
                        <span v-if="item.resultado_integrado">
                           @{{item.resultado_integrado.encaminhado_treinamento ? 'Sim' : 'Não'}} <br>
                           @{{item.resultado_integrado.encaminhado_treinamento_data}} <br>
                        </span>
                        <span v-else>---</span>
                    </td>

                    <td>
                        <span v-if="item.resultado_integrado">
                           @{{item.resultado_integrado.responsavel_envio}}
                        </span>
                        <span v-else>---</span>
                    </td>

                    <td>
                        @{{item.admissao ? item.admissao.numero_cracha : ''}}
                    </td>

                    <td>
                        @{{item.curriculo.foto_tres.length > 0 ? 'SIM' : 'NÃO' }}
                    </td>

                    <td>
                        @{{item.admissao ? item.admissao.status : ''}}
                    </td>

                    <td>
                        <button class="btn btn-sm btn-primary mb-2" content="Admitir" v-tippy
                                @click.prevent="formEntrevistar(item.id)"
                                data-toggle="modal"
                                data-target="#janelaCadastrar">
                            <i class="fa fa-check"></i>
                        </button>

                        <button class="btn btn-sm btn-primary mb-2" content="Visualizar" v-tippy
                                @click.prevent="formEntrevistar(item.id); visualizar = true"
                                data-toggle="modal"
                                data-target="#janelaCadastrar">
                            <i class="fa fa-search-plus"></i>
                        </button>

                        <a v-if="item.admissao" :href="`admissao/${item.id}/pdf`"
                           class="btn btn-sm btn-primary mb-2" content="Gerar PDF" v-tippy
                           target="_blank">
                            <i class="fa fa-file-pdf"></i>
                        </a>
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
