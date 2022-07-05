@extends('layouts.sistema')
@section('title', 'Recrutamento')
@section('content_header','Recrutamento')
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90">
        <template slot="conteudo">
            <preload v-show="preloadAjax" :label="editando ? 'Salvando ...' : 'Carregando ...'"></preload>
            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">
                <fieldset>
                    <legend>Dados Pessoais</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" class="form-control" v-model="form.nome"
                                       placeholder="Nome"
                                       autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>CPF</label>
                                <input type="text" class="form-control" v-model="form.cpf"
                                       placeholder="CPF"
                                       disabled
                                       autocomplete="mastertag" v-mascara:cpf onblur="valida_cpf_vazio(this)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>RG</label>
                                <input type="text" class="form-control" v-model="form.rg"
                                       placeholder="RG"
                                       autocomplete="mastertag" v-mascara:numero
                                       onblur="valida_campo(this,1)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Orgão Expeditor (RG)</label>
                                <input type="text" class="form-control" v-model="form.orgao_expeditor"
                                       placeholder="Orgão"
                                       autocomplete="mastertag" onblur="valida_campo(this,1)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>CNH</label>
                                <input type="text" class="form-control" v-model="form.cnh"
                                       placeholder="Tipo da CNH"
                                       autocomplete="mastertag">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Nascimento</label>
                                <input type="text" class="form-control" v-model="form.nascimento"
                                       placeholder="Ex: 10/10/2010"
                                       v-mascara:data
                                       autocomplete="mastertag" onblur="valida_data_vazio(this)">
                            </div>
                        </div>

                        <div class="col-12"></div>

                        <div class="col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Nome do pai</label>
                                <input type="text" class="form-control" v-model="form.filiacao_pai"
                                       placeholder="Nome"
                                       onblur="valida_campo(this,3)"
                                       autocomplete="mastertag">
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Nome da mãe</label>
                                <input type="text" class="form-control" v-model="form.filiacao_mae"
                                       onblur="valida_campo(this,3)"
                                       placeholder="Nome"
                                       autocomplete="mastertag">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="text" class="form-control" v-model="form.email"
                                       placeholder="Ex.: email@email.com"
                                       autocomplete="mastertag" onblur="validaEmailVazio(this)">
                            </div>
                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>Endereço</legend>
                    <div class="row">
                        <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                            <endereco :model="form"></endereco>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Contatos</legend>
                    <div class="row">
                        <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                            <telefone :model="form.telefones" :model-delete="form.telefonesDelete" :qnt_min="1"
                                      :pais="false"
                                      :ramal="false"></telefone>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Formação</legend>
                    <div class="row">
                        <div class="col-12" style="font-size: 0.8rem;">
                            <p>Formação: <span v-if="editando">@{{ form.formacao.tipo }}</span></p>
                            <p>Curso: <span
                                    v-if="editando">@{{ form.formacao_curso }} ( @{{ form.formacao_status }} )</span>
                            </p>
                            <p>Instituição: <span v-if="editando">@{{ form.formacao_instituicao }}</span></p>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Experiências</legend>
                    <div class="row">
                        <div class="col-12" style="font-size: 0.8rem;">
                            <div v-for="item in form.experiencias">
                                <p>Empresa: <span v-if="editando">@{{ item.empresa }}</span></p>
                                <p>Cargo: <span v-if="editando">@{{ item.cargo }}</span></p>
                                <p>Nome Referência: <span v-if="editando">@{{ item.referencia_nome }}</span></p>
                                <p>Telefone Referência: <span v-if="editando">@{{ item.referencia_telefone }}</span></p>
                                <p>Principais Atividades: <span v-if="editando">@{{ item.principais_atv}}</span></p>
                                <p>Data Inicio: <span v-if="editando">@{{ item.data_inicio}}</span></p>
                                <p>Data Fim: <span v-if="editando">@{{ item.data_fim}}</span></p>
                                <hr>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Qualificações</legend>
                    <div class="row">
                        <div class="col-12" style="font-size: 0.8rem;">
                            <div v-for="item in form.qualificacoes">
                                <p>Curso: <span v-if="editando">@{{ item.nome }}</span></p>
                                <p>Instituição: <span v-if="editando">@{{ item.instituicao }}</span></p>
                                <p>Conclusão: <span
                                        v-if="editando">@{{ item.mes_conclusao }}/@{{ item.ano_conclusao }}</span></p>
                            </div>
                        </div>
                    </div>
                </fieldset>


                <fieldset>
                    <legend>Feedback</legend>

                    <div class="alert alert-warning" v-if="form.atualizacao">
                        <h5>Este curriculo foi atualizado em: @{{ form.atualizacao.created_at }}</h5>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-4" v-if="editando">
                            <div class="form-group">
                                <label>Vaga Pretendida</label>
                                <input type="text" disabled="disabled" class="form-control"
                                       :value="form.vaga_aberta.vaga_selecionada.nome">
                            </div>

                        </div>

                        {{--                        <div class="col-12 col-md-4" v-if="editando">--}}
                        {{--                            <div class="form-group">--}}
                        {{--                                <label for="Cidade">Município</label>--}}
                        {{--                                <input type="text" class="form-control" disabled v-model="form.municipio_vaga_format">--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}

                        <div class="col-12 col-md-4" v-if="editando">
                            <div class="form-group">
                                <label>Disponibilidade para viajar</label>
                                <select class="form-control" disabled v-model="form.viajar">
                                    <option value="">Não informado ou (adm avulsa)</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="editando">
                            <div class="form-group">
                                <label>Cota PCD (Lei nº 8.213/91)</label>
                                <input type="text" disabled="disabled" class="form-control"
                                       :value="!form.pcd ? 'Não': 'Sim'">
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="editando && form.pcd">
                            <div class="form-group">
                                <label>CID</label>
                                <input type="text" disabled="disabled" class="form-control"
                                       :value="form.cid">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Selecionado</label>
                                <select class="form-control"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.selecionado">
                                    <option value="">Selecione</option>
                                    <option value="sim">SIM</option>
                                    <option value="nao">NÃO</option>
                                    <option value="standby">STAND BY</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.selecionado !== '' && form_feedback.selecionado === 'nao'">
                            <div class="form-group">
                                <label>Enviar e-mail desclassificação</label>
                                <select class="form-control"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.envia_mail_desclassificacao">
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'">
                            <div class="form-group">
                                <label>Selecione uma vaga</label>
                                <autocomplete :formsm="false" :caminho="controle.dados.caminho_autocomplete"
                                              :valido="form_feedback.vaga_id !== ''"
                                              v-model="form_feedback.autocomplete_label_vaga_modal"
                                              placeholder="Digite o nome da cargo"
                                              :id="`vaga_modal_${hash}`"
                                              @onblur="resetaCampoVagaModal"
                                              @onselect="selecionaVagaModal"></autocomplete>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.selecionado !== '' && form_feedback.selecionado === 'sim' && form_feedback.tem_provas">
                            <div class="form-group">
                                <label>Enviar e-mail links de provas</label>
                                <select class="form-control"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.envia_mail_provas">
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.selecionado !== '' && form_feedback.selecionado === 'sim'">
                            <div class="form-group">
                                <label>Enviar e-mail de avanço de etapa</label>
                                <select class="form-control"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.envia_mail_proxima_etapa">
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'">
                            <div class="form-group">
                                <label for="">Contato Realizado</label>
                                <select class="form-control"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.contato_realizado">
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="_.find(this.form.telefones, {'principal': true})">
                            <div class="form-group">
                                <label for="">Contato Principal</label>
                                <select class="form-control">
                                    <option selected disabled="disabled" readonly="readonly">
                                        @{{ _.find(this.form.telefones, {'principal': true}).numero }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <template v-if="permite_envio_whatsapp">
                            <div class="col-12 col-md-4"
                                 v-if="form_feedback.contato_realizado">
                                <div class="form-group">
                                    <label>Enviar Notificação via whatsApp</label>
                                    <select class="form-control"
                                            onblur="valida_campo_vazio(this,1)"
                                            onchange="valida_campo_vazio(this,1)"
                                            v-model="form_feedback.envia_whatsapp">
                                        <option value="">Selecione</option>
                                        <option :value="true">SIM</option>
                                        <option :value="false">NÃO</option>
                                    </select>
                                </div>
                            </div>
                        </template>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.contato_realizado && form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'">
                            <div class="form-group">
                                <label for="">Interesse</label>
                                <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.interesse">
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.interesse && form_feedback.contato_realizado && form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'">
                            <div class="form-group">
                                {{--                                <label for="">Entrevista</label>--}}
                                <datepicker :hora="true"
                                            label="Entrevista"
                                            min="{{(new \MasterTag\DataHora())->dataCompleta()}}"
                                            posicao="up" v-model="form_feedback.data_entrevista"></datepicker>

                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.interesse && form_feedback.contato_realizado && form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'">
                            <div class="form-group">
                                <label for="">Local Entrevista</label>
                                <input type="text" class="form-control"
                                       onblur="valida_campo_vazio(this,1)"
                                       v-model="form_feedback.local_entrevista">
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.interesse && form_feedback.contato_realizado && form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'">
                            <div class="form-group">
                                <label for="">Selecione um cliente</label>
                                <autocomplete :formsm="false" :caminho="controle.dados.caminho_cliente_autocomplete"
                                              :valido="form_feedback.cliente_id !== ''"
                                              v-model="form_feedback.autocomplete_label_cliente_modal"
                                              :id="`cliente_modal_${hash}`"
                                              placeholder="Digite o nome da empresa"
                                              @onblur="resetaCampoClienteModal"
                                              @onselect="selecionaClienteModal"></autocomplete>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="form-group">
                                <label for="">Observações:</label>
                                <input type="text" class="form-control"
                                       v-model="form_feedback.obs">
                            </div>
                        </div>
                        <div class="col-12 col-sm-12" style="font-size: 0.8rem;" v-if="feedback && form.lido">
                            <p>Lido por: <span v-if="editando">@{{ form.usuario.nome }}</span></p>
                            <p>Em: <span v-if="editando">@{{ form.datalido }}</span></p>
                        </div>
                    </div>
                </fieldset>

            </form>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-primary" v-show="editando && !atualizado && !preloadAjax"
                    @click="alterar">
                Salvar
            </button>

        </template>
    </modal>

    <modal id="janelaConfirmar" titulo="Apagar Curriculo">
        <template slot="conteudo">
            <span v-show="preloadAjax"><preload></preload></span>
            <div class="alert alert-success alert-dismissible" v-show="apagado">
                <h4><i class="icon fa fa-check"></i>Curriculo apagado com sucesso!</h4>
            </div>
            <h4 v-show="!apagado">Tem certeza que deseja apagar este curriculo?</h4>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-danger" @click="apagar()" v-show="!apagado">Apagar</button>
        </template>
    </modal>

    <div class="row pb-3 pt-3">
        <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0 bg-primary">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-white  mb-0">Total Cadastrados</h5>
                            <span class="h2 font-weight-bold mb-0 text-white ">{{$curriculos}}</span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-default text-white rounded-circle shadow">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <fieldset>
        <legend>Filtrar por</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <div class="col-12 col-md-4">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" :disabled="controle.carregando" id="filtroIntervalo"
                           v-model="controle.dados.filtroPeriodo">
                    <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label="" :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                v-model="controle.dados.periodo"></datepicker>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text"
                           placeholder="Buscar por nome"
                           autocomplete="off"
                           class="form-control form-control-sm" :disabled="controle.carregando"
                           v-model="controle.dados.campoBusca">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
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
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
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
                    <label>Lido</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoLido">
                        <option value="">Geral</option>
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>PCD</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoPcd">
                        <option value="">Geral</option>
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>Exibir</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.pages">
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-9">
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
                <button type="button" class="btn btn-sm btn-primary  mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && !lista.length) ">
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                </button>
            </div>
        </form>
    </fieldset>

    <p class="text-center" v-if="controle.carregando">
        <preload></preload>
    </p>

    <div id="conteudo">

        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th>Cód</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>UF</th>
                    <th>Vaga</th>
                    <th>PCD</th>
                    <th>Data</th>
                    <th>Lido</th>
                    <th>Ação</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="curriculo in lista">
                    <td data-label="Cód">
                        @{{curriculo.id}}
                    </td>
                    <td data-label="Nome">
                        @{{curriculo.nome}}
                    </td>
                    <td data-label="CPF">
                        @{{curriculo.cpf}}
                    </td>
                    <td data-label="UF">
                        @{{curriculo.uf_vaga ? curriculo.uf_vaga : 'Não informado'}}
                    </td>
                    <td data-label="Vaga">
                        @{{curriculo.vaga_aberta.vaga_selecionada.nome}}
                    </td>
                    <td data-label="PCD">
                        @{{curriculo.pcd ? "SIM" : "NÃO"}}
                    </td>
                    <td data-label="Data">
                        @{{curriculo.created_at}}
                    </td>
                    <td data-label="Lido">
                        <span v-show="curriculo.lido">
                            <i class="fa fa-check text-success"></i> SIM
                        </span>
                        <span v-show="!curriculo.lido">
                            <i class="fa fa-ban text-warning"></i> NÃO
                        </span>
                        {{--                        @{{curriculo.lido===true ? 'SIM' : 'NÃO'}}--}}
                    </td>

                    <td data-label="Ação">
                        <a href="javascript://" class="btn btn-sm mb-2 btn-primary"
                           @click.prevent="formAlterar(curriculo.id)"
                           content="Recrutar" v-tippy
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>

                        <a :href="`recrutamentos/${curriculo.id}`" target="_blank" class="btn btn-sm mb-2 btn-primary"
                           content="Gerar PDF" v-tippy>
                            <i class="far fa-file-pdf"></i>
                        </a>

                        @can('curriculos_recrutamento_delete')
                            <a href="javascript://" class="btn btn-sm mb-2 btn-danger" content="Remover" v-tippy
                               @click.prevent="janelaConfirmar(curriculo.id)"
                               data-toggle="modal"
                               data-target="#janelaConfirmar">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </a>
                        @endcan
                    </td>

                </tr>
                </tbody>
            </table>
        </div>

        <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>


        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.recrutamento.recrutamentos.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/recrutamento/app.js')}}"></script>
@endpush
