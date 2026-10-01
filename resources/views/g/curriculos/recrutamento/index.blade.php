@extends('layouts.sistema')
@section('title', 'Recrutamento')
@section('content_header','Recrutamento')
@section('content')

    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90">
        <template #conteudo>
            <preload v-show="preloadAjax" :label="editando ? 'Salvando ...' : 'Carregando ...'"></preload>
            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">
                <fieldset>
                    <legend>Dados Pessoais</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Nome <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" v-model="form.nome"
                                       placeholder="Nome"
                                       autocomplete="mastertag" onblur="valida_campo_vazio(this,3)"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>CPF <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" v-model="form.cpf"
                                       placeholder="CPF"
                                       disabled
                                       autocomplete="mastertag" v-mascara:cpf onblur="valida_cpf_vazio(this)"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>RG</label>
                                <input type="text" class="form-control" v-model="form.rg"
                                       placeholder="RG"
                                       autocomplete="mastertag" v-mascara:numero
                                       onblur="valida_campo(this,1)"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Orgão Expeditor (RG)</label>
                                <input type="text" class="form-control" v-model="form.orgao_expeditor"
                                       placeholder="Orgão"
                                       autocomplete="mastertag" onblur="valida_campo(this,1)"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>CNH</label>
                                <input type="text" class="form-control" v-model="form.cnh"
                                       placeholder="Tipo da CNH"
                                       autocomplete="mastertag"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Nascimento <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" v-model="form.nascimento"
                                       placeholder="Ex: 10/10/2010"
                                       v-mascara:data
                                       autocomplete="mastertag" onblur="valida_data_vazio(this)"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label>Sexo</label>
                                <select
                                    class="form-control"
                                    v-model="form.sexo"
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
                                    v-model="form.estado_civil"
                                >
                                    <option value="">Selecione</option>
                                    <option v-for="item in lista_estados_civis" :value="item">@{{item}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12"></div>

                        <div class="col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Nome do pai</label>
                                <input type="text" class="form-control" v-model="form.filiacao_pai"
                                       placeholder="Nome"
                                       onblur="valida_campo(this,3)"
                                       autocomplete="mastertag"
                                >
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Nome da mãe</label>
                                <input type="text" class="form-control" v-model="form.filiacao_mae"
                                       onblur="valida_campo(this,3)"
                                       placeholder="Nome"
                                       autocomplete="mastertag"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>E-mail <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" v-model="form.email"
                                       placeholder="Ex.: email@email.com"
                                       autocomplete="mastertag" onblur="validaEmailVazio(this)"
                                >
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
                                      :ramal="false"
                            ></telefone>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Formação</legend>
                    <div class="row">
                        <div class="col-12" style="font-size: 0.8rem;">
                            <p>Formação: <span v-if="editando">@{{ form.formacao?.tipo || 'Não informado' }}</span></p>
                            <p>Curso: <span
                                    v-if="editando"
                                >@{{ form.formacao_curso }} ( @{{ form.formacao_status }} )</span>
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
                                        v-if="editando"
                                    >@{{ item.mes_conclusao }}/@{{ item.ano_conclusao }}</span></p>
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
                                       :value="form.vaga_aberta?.vaga_selecionada?.nome || 'Não informado'"
                                >
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
                                       :value="!form.pcd ? 'Não': 'Sim'"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="editando && form.pcd">
                            <div class="form-group">
                                <label>CID</label>
                                <input type="text" disabled="disabled" class="form-control"
                                       :value="form.cid"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Selecionado <span style="color: red;">*</span></label>
                                <select class="form-control"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.selecionado"
                                >
                                    <option value="">Selecione</option>
                                    <option value="sim">SIM</option>
                                    <option value="nao">NÃO</option>
                                    <option value="standby">STAND BY</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.selecionado !== '' && form_feedback.selecionado === 'nao'"
                        >
                            <div class="form-group">
                                <label>Enviar e-mail desclassificação <span style="color: red;">*</span></label>
                                <select class="form-control"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.envia_mail_desclassificacao"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'"
                        >
                            <div class="form-group">
                                <label>Selecione uma vaga <span style="color: red;">*</span></label>
                                <autocomplete :formsm="false" :caminho="controle.dados.caminho_autocomplete"
                                              :valido="form_feedback.vagas_abertas_id !== ''"
                                              v-model="form_feedback.autocomplete_label_vaga_modal"
                                              placeholder="Digite o nome da vaga"
                                              :id="`vaga_modal_${hash}`"
                                              @onblur="resetaCampoVagaModal"
                                              @onselect="selecionaVagaModal"
                                ></autocomplete>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.selecionado !== '' && form_feedback.selecionado === 'sim' && form_feedback.tem_provas"
                        >
                            <div class="form-group">
                                <label>Enviar e-mail links de provas <span style="color: red;">*</span></label>
                                <select class="form-control"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.envia_mail_provas"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.selecionado !== '' && form_feedback.selecionado === 'sim'"
                        >
                            <div class="form-group">
                                <label>Enviar e-mail de avanço de etapa <span style="color: red;">*</span></label>
                                <select class="form-control"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.envia_mail_proxima_etapa"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'"
                        >
                            <div class="form-group">
                                <label for="">Contato Realizado <span style="color: red;">*</span></label>
                                <select class="form-control"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.contato_realizado"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="telefonePrincipal">
                            <div class="form-group">
                                <label for="">Contato Principal</label>
                                <select class="form-control">
                                    <option selected disabled="disabled" readonly="readonly">
                                        @{{ telefonePrincipalNumero }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <template v-if="permite_envio_whatsapp && telefonePrincipal && telefonePrincipal.tipo === 'whatsapp'">
                            <div class="col-12 col-md-4"
                                 v-if="form_feedback.contato_realizado"
                            >
                                <div class="form-group">
                                    <label>Enviar Notificação via whatsApp <span style="color: red;">*</span></label>
                                    <select class="form-control"
                                            onblur="valida_campo_vazio(this,1)"
                                            onchange="valida_campo_vazio(this,1)"
                                            v-model="form_feedback.envia_whatsapp"
                                    >
                                        <option value="">Selecione</option>
                                        <option :value="true">SIM</option>
                                        <option :value="false">NÃO</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="form_feedback.contato_realizado && form_feedback.envia_whatsapp">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-outline-info btn-sm btn-block"
                                            @click="previewRecrutamentoWhatsapp">
                                        <i class="fab fa-whatsapp"></i> Visualizar mensagem
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.contato_realizado && form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'"
                        >
                            <div class="form-group">
                                <label for="">Interesse <span style="color: red;">*</span></label>
                                <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form_feedback.interesse"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">SIM</option>
                                    <option :value="false">NÃO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.interesse && form_feedback.contato_realizado && form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'"
                        >
                            <div class="form-group">
                                {{--                                <label for="">Entrevista</label>--}}
                                <datepicker :hora="true"
                                            label="Entrevista"
                                            min="{{(new \MasterTag\DataHora())->dataCompleta()}}"
                                            posicao="up" v-model="form_feedback.data_entrevista"
                                ></datepicker>

                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.interesse && form_feedback.contato_realizado && form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'"
                        >
                            <div class="form-group">
                                <label for="">Local Entrevista <span style="color: red;">*</span></label>
                                <input type="text" class="form-control"
                                       onblur="valida_campo_vazio(this,1)"
                                       v-model="form_feedback.local_entrevista"
                                >
                            </div>
                        </div>

                        {{--                        <div class="col-12 col-md-4"--}}
                        {{--                             v-if="form_feedback.interesse && form_feedback.contato_realizado && form_feedback.selecionado !== '' && form_feedback.selecionado !== 'nao'">--}}
                        {{--                            <div class="form-group">--}}
                        {{--                                <label for="">Selecione um cliente</label>--}}
                        {{--                                <autocomplete :formsm="false" :caminho="controle.dados.caminho_cliente_autocomplete"--}}
                        {{--                                              :valido="form_feedback.cliente_id !== ''"--}}
                        {{--                                              v-model="form_feedback.autocomplete_label_cliente_modal"--}}
                        {{--                                              :id="`cliente_modal_${hash}`"--}}
                        {{--                                              placeholder="Digite o nome da empresa"--}}
                        {{--                                              @onblur="resetaCampoClienteModal"--}}
                        {{--                                              @onselect="selecionaClienteModal"></autocomplete>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                        <div class="col-12 col-md-12">
                            <div class="form-group">
                                <label for="">Observações:</label>
                                <input type="text" class="form-control"
                                       v-model="form_feedback.obs"
                                >
                            </div>
                        </div>
                        <div class="col-12 col-sm-12" style="font-size: 0.8rem;" v-if="feedback && form.lido">
                            <p>Lido por: <span v-if="editando">@{{ form.usuario?.nome || 'Não informado' }}</span></p>
                            <p>Em: <span v-if="editando">@{{ form.datalido }}</span></p>
                        </div>
                    </div>
                </fieldset>

            </form>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado && !preloadAjax"
                    @click="alterar"
            >
                Salvar
            </button>

        </template>
    </modal>

    <modal ref="janelaConfirmar" id="janelaConfirmar" titulo="Apagar Curriculo">
        <template #conteudo>
            <span v-show="preloadAjax"><preload></preload></span>
            <div class="alert alert-success alert-dismissible" v-show="apagado">
                <h4><i class="icon fa fa-check"></i>Curriculo apagado com sucesso!</h4>
            </div>
            <h4 v-show="!apagado">Tem certeza que deseja apagar este curriculo?</h4>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-danger" @click="apagar()" v-show="!apagado">Apagar</button>
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

    <filtro-listagem
        class="mt-2 mybp-filtros-compactos"
        :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
        :desabilitado="controle.carregando"
        @submit="atualizar"
        @limpar="limparFiltros"
    >
        <template #filtros>
            <date-range-filter
                :key="'rec-filtro-periodo'"
                v-model:enabled="controle.dados.filtroPeriodo"
                v-model:start-date="controle.dados.dataInicio"
                v-model:end-date="controle.dados.dataFim"
                :disabled="!!controle.carregando"
                :id-suffix="'periodo-' + hash"
                label="Por período"
                wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                @change="onPeriodoChange"
            ></date-range-filter>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="rec-filtro-busca">
                        Candidato / CPF
                        <span v-if="buscaUnificadaEhCpf" class="text-muted small">CPF</span>
                    </label>
                    <input
                        id="rec-filtro-busca"
                        type="text"
                        placeholder="Nome ou CPF"
                        autocomplete="off"
                        inputmode="search"
                        class="form-control form-control-sm"
                        :disabled="controle.carregando"
                        :value="campoBuscaUnificada"
                        @input="onInputBuscaUnificada"
                    />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label">Cargo</label>
                    <autocomplete
                        :caminho="controle.dados.caminho_autocomplete"
                        :valido="controle.dados.campoVaga !== ''"
                        :formsm="true"
                        v-model="controle.dados.autocomplete_label"
                        :disabled="controle.carregando"
                        placeholder="Por cargo"
                        @onblur="resetaCampo"
                        @onselect="selecionaVaga"
                    ></autocomplete>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="rec-filtro-uf">Estado</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroUf"
                            instance-id="rec-filtro-uf"
                            input-id="rec-filtro-uf"
                            v-model="controle.dados.campoUf"
                            :options="filtroUfOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Todos os estados"
                            empty-message="Nenhum estado encontrado."
                            :max-results="30"
                            @opening="fecharOutrosComboboxes('rec-filtro-uf')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="rec-filtro-lido">Lido</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroLido"
                            instance-id="rec-filtro-lido"
                            input-id="rec-filtro-lido"
                            v-model="controle.dados.campoLido"
                            :options="filtroSimNaoOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Geral"
                            empty-message="Nenhuma opção."
                            :max-results="5"
                            @opening="fecharOutrosComboboxes('rec-filtro-lido')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="rec-filtro-pcd">PCD</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroPcd"
                            instance-id="rec-filtro-pcd"
                            input-id="rec-filtro-pcd"
                            v-model="controle.dados.campoPcd"
                            :options="filtroSimNaoOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Geral"
                            empty-message="Nenhuma opção."
                            :max-results="5"
                            @opening="fecharOutrosComboboxes('rec-filtro-pcd')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="rec-filtro-pages">Exibir</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroPages"
                            instance-id="rec-filtro-pages"
                            input-id="rec-filtro-pages"
                            v-model="campoPagesCombo"
                            :options="filtroPagesOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="20"
                            empty-message="Nenhuma opção."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('rec-filtro-pages')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>
        </template>

        <template #acoes>
            <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                Buscar
            </button>
            <button
                type="button"
                class="btn btn-sm btn-outline-primary mybp-btn-acao-compact"
                @click.prevent="exportaExcel()"
                :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)"
            >
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </template>
    </filtro-listagem>

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
                    <th>Selecionado</th>
                    <th>Contato Realizado</th>
                    <th>Interesse</th>
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
                        @{{curriculo.vaga_aberta?.vaga_selecionada?.nome || 'Não informado'}}
                    </td>
                    <td data-label="PCD">
                        @{{curriculo.pcd ? "SIM" : "NÃO"}}
                    </td>
                    <td data-label="Selecionado">
                        @{{curriculo.feed_back ? curriculo.feed_back.selecionado ?
                        curriculo.feed_back.selecionado.toUpperCase() : "--" : "--"}}
                    </td>
                    <td data-label="Contato Realizado">
                        @{{curriculo.feed_back ? curriculo.feed_back.contato_realizado ? "SIM" : "NÃO" : "NÃO"}}
                    </td>
                    <td data-label="Interesse">
                        @{{curriculo.feed_back ? curriculo.feed_back.interesse ? "SIM" : "NÃO" : "--"}}
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
                        <a href="javascript://" class="btn btn-sm mr-1 mb-2 btn-primary"
                           @click.prevent="formAlterar(curriculo.id)"
                           content="Recrutar" v-tippy
                        >
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>

                        <a :href="`recrutamentos/${curriculo.ctoken}`" target="_blank"
                           class="btn btn-sm mr-1 mb-2 btn-primary"
                           content="Gerar PDF" v-tippy
                        >
                            <i class="far fa-file-pdf"></i>
                        </a>

                        @can('curriculos_recrutamento_delete')
                            <a href="javascript://" class="btn btn-sm mr-1 mb-2 btn-danger" content="Remover" v-tippy
                               @click.prevent="janelaConfirmar(curriculo.id); $refs.janelaConfirmar?.abrirModal()"
                            >
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
                            :por-pagina="controle.dados.pages"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"
        ></controle-paginacao>

        <whatsapp-preview-modal
            v-model="previewWhatsappAberto"
            :tipo-mensagem="previewWhatsappTipo"
            :contexto="previewWhatsappContexto"
        />
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/recrutamento/app.js')}}"></script>
@endpush
