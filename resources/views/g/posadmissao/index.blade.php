@extends('layouts.sistema')
@section('title', 'PÓS-ADMISSÃO')
@section('content_header')
    <h4 class="text-default">PÓS-ADMISSÃO</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop

@section('content')
    <modal id="janelaAvaliar" :titulo="tituloJanela" :fechar="!preload" :size="75">
        <template slot="conteudo">
            <div class="alert alert-success text-center" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i> <span v-show="avaliacao">Demissão</span> <span
                        v-show="desmobilizacao">Desmobilização</span> Concluida!</h4>
            </div>

            <p class=" mt-2 text-center" v-if="preload">
                <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
            </p>

            <div class="row" v-if="!preload && cadastrando">
                <div class="col-12">
                    <fieldset>
                        <legend>Informações do Colaborador</legend>
                        <div style="text-transform: uppercase">
                            <p>Nome: <strong>@{{ form.feedback.curriculo.nome }}</strong><br>
                                CPF: <strong>@{{ form.feedback.curriculo.cpf }}</strong><br>
                                Empresa: <strong>@{{form.feedback.empresa.nome_fantasia ?
                                    form.feedback.empresa.nome_fantasia :
                                    form.feedback.empresa.nome}}</strong>
                                <br>
                                Vaga: <strong>
                                    @{{ form.feedback.vaga_selecionada.nome }}</strong>
                                <br>

                                Cargo: <strong>@{{ form.cargo }}</strong> | Função: <strong>
                                    @{{ form.funcao }}</strong><br>
                                Data de admissão: <strong>@{{ form.data_admissao }}</strong></p><br>
                        </div>
                    </fieldset>

                    <fieldset v-if="avaliacao">
                        <legend>Informações de Rescisão</legend>

                        <div class="form-group">
                            <label class="text-danger">OBS: ANTES DE FAZER O AVISO DEVE SER CONFIRMADO:</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" v-model="form.cipa" id="cipa">
                                <label class="custom-control-label" style="cursor: pointer" for="cipa">
                                    Checar Estabilidade: CIPA, Acidente Trabalho e Sindicato, Gestante, Aposentadoria
                                    (Itens CLT ou CCT)
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">Data desmobilização</label>
                            <datepicker v-model="form.data_desmobilizacao"></datepicker>
                        </div>

                        <div class="form-group">
                            <label>Motivo da rescisão contratual?</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.motivo">
                                <option value="">Selecione</option>
                                <option v-for="item in listaMotivos" :value="item.id">@{{ item.descricao }}</option>
                            </select>
                        </div>

                        <div class="form-group" v-if="form.motivo === 7">
                            <label>Motivo</label>
                            <input type="text" class="form-control" v-model="form.outromotivo"
                                   onblur="valida_campo_vazio(this,3)">
                        </div>


                        <div class="form-group">
                            <label>Tipo de Aviso:</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.aviso">
                                <option value="">Selecione</option>
                                <option v-for="item in listaAvisos" :value="item.id">@{{ item.descricao }}</option>
                            </select>
                        </div>

                        <fieldset>
                            <legend>OBSERVAÇÃO</legend>

                            <div class="form-group">
                                <label>Quem Solicitou</label>
                                <input type="text" class="form-control" v-model="form.quem_classificou"
                                       onblur="valida_campo_vazio(this,3)">
                            </div>

                            <div class="form-group">
                                <label>Preenchido por</label>
                                <input type="text" class="form-control" v-model="form.preenchido_por"
                                       onblur="valida_campo_vazio(this,3)">
                            </div>

                            <div class="form-group">
                                <label>Comentário</label>
                                <textarea class="form-control" v-model="form.observacoes" cols="3" rows="3"></textarea>
                            </div>

                        </fieldset>

                    </fieldset>

                    <fieldset v-if="desmobilizacao">
                        <legend>Desmobilização</legend>

                        <div class="form-group">
                            <label>Deu baixa em EPI'S:</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.deu_baixa_epi">
                                <option value="">Selecione</option>
                                <option :value="true">Sim</option>
                                <option :value="false">Não</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Tipo de formulário:</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.tipo_form" v-if="formulario.setores.length > 0">
                                <option value="">Selecione</option>
                                @if(\App\Models\Sistema::permitirLinks('posadmissao_form_rh'))
                                    <option :value="1">RECURSOS HUMANOS</option>
                                @endif
                                @if(\App\Models\Sistema::permitirLinks('posadmissao_form_adm'))
                                    <option :value="2">ALMOXARIFADO / ADM</option>
                                @endif
                                @if(\App\Models\Sistema::permitirLinks('posadmissao_form_ssma'))
                                    <option :value="3">SEGURANÇA DO TRABALHO / SSMA</option>
                                @endif
                            </select>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.tipo_form" v-else>
                                <option value="">Nenhum formulário encontrado, entre em contato com o desenvolvedor do sistema.</option>
                            </select>
                        </div>

                        <div v-if="form.tipo_form === setor.id" v-for="(setor, setorIndex) in formulario.setores">
                            <fieldset>
                                <legend>Checklist - @{{ setor.nome }}</legend>
                                <div class="custom-control custom-switch"
                                     v-for="(alternativa, key) in setor.alternativas">
                                    <input type="checkbox" class="custom-control-input"
                                           v-model="form.alternativas[alternativa.id]" :value="alternativa.id"
                                           :id="`alternativa_${alternativa.id}`">
                                    <label class="custom-control-label" style="cursor: pointer"
                                           :for="`alternativa_${alternativa.id}`">
                                        @{{ alternativa.nome }}
                                    </label>
                                </div>

                                <div v-if="setorIndex === 0">
                                    <div class="form-group">
                                        <label for="">Preenchido por:</label>
                                        <input type="text" class="form-control" v-model="form.preenchido_por_rh"
                                               onblur="valida_campo_vazio(this,1)">
                                    </div>
                                </div>

                                <div v-if="setorIndex === 1">
                                    <div class="form-group" v-if="form.alternativas['20']">
                                        <label for="">Quais outros?</label>
                                        <textarea v-model="form.outros" onblur="valida_campo_vazio(this,1)"
                                                  class="form-control" cols="3"
                                                  rows="3"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="">Há pendências?</label>
                                        <select class="form-control" v-model="form.pendencia"
                                                onblur="valida_campo_vazio(this,1)">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-if="form.pendencia">
                                        <label for="">Quais pendências?</label>
                                        <textarea v-model="form.pendencias_quais" onblur="valida_campo_vazio(this,1)"
                                                  class="form-control" cols="3"
                                                  rows="3"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="">Preenchido por:</label>
                                        <input type="text" class="form-control" v-model="form.preenchido_por_adm"
                                               onblur="valida_campo_vazio(this,1)">
                                    </div>
                                </div>

                                <div v-if="setorIndex === 2">
                                    <div class="form-group">
                                        <label for="">Preenchido por:</label>
                                        <input type="text" class="form-control" v-model="form.preenchido_por_ssma"
                                               onblur="valida_campo_vazio(this,1)">
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                    </fieldset>

                    <fieldset v-if="entrevista">
                        <legend>Entrevista Desligamento</legend>
                        <p>
                            A Entrevista de Desligamento será conduzida por um profissional da área de RH da empresa,
                            realizada com todos colaboradores que estão saindo da empresa, seja contratado, profissional
                            autônomo ou estagiário, que tenha pedido demissão ou foi demitido e, preferencialmente, logo
                            após a comunicação do desligamento.
                            <br>
                            <br>
                            O Objetivo da Entrevista de Desligamento é, a princípio, dar apoio ao ex-colaborador (em
                            caso de demissão) e colher o máximo de informações e/ou impressões que ele leva da empresa,
                            problemas percebidos, sentimentos, contribuindo, assim, para o processo de melhoria da
                            organização.
                            <br>
                            <br>
                            É importante que a entrevista seja mais informal, como um bate-papo, porém em caráter
                            confidencial e em local reservado.
                            <br>
                            <br>
                            Como a Entrevista de Desligamento é um documento, deverá ser preenchida obrigatoriamente, em
                            todos os campos (com exceção, é claro, no caso do ex-colaborador se recusar a responder as
                            questões), com parecer da entrevista, parecer do superior imediato e arquivada na pasta do
                            ex-colaborador.

                        </p>


                        <div class="form-group">
                            <label for="">Superior Imediato:</label>
                            <input type="text" class="form-control"
                                   v-model="form.entrevista_desligamento.superior_imediato"
                                   onblur="valida_campo_vazio(this,1)">
                        </div>

                        <div class="form-group">
                            <label for="">Motivo do Desligamento ?</label>
                            <textarea v-model="form.entrevista_desligamento.motivo" onblur="valida_campo_vazio(this,1)"
                                      class="form-control" cols="5"
                                      rows="5"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="">Você trabalharia na empresa novamente? Por quê?</label>
                            <textarea v-model="form.entrevista_desligamento.trabalharia_novamente"
                                      onblur="valida_campo_vazio(this,1)"
                                      class="form-control" cols="5"
                                      rows="5"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="">O que você deixaria como contribuição para o processo de melhoria da
                                empresa?</label>
                            <textarea v-model="form.entrevista_desligamento.contr_melhoria"
                                      onblur="valida_campo_vazio(this,1)"
                                      class="form-control" cols="5"
                                      rows="5"></textarea>
                        </div>

                        <div class="alert alert-secondary">
                            <h6>Agora faça uma avaliação da empresa em relação aos aspectos abaixo.
                            </h6>
                        </div>

                        <div class="form-group">
                            <label>Relacionamento Interpessoal</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.entrevista_desligamento.relacao_interpessoal">
                                <option value="">Selecione</option>
                                <option value="Excelente">Excelente</option>
                                <option value="Ótimo">Ótimo</option>
                                <option value="Bom">Bom</option>
                                <option value="Regular">Regular</option>
                                <option value="Ruim">Ruim</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Recursos físicos, materiais e tecnológicos (estrutura física para trabalhar):</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.entrevista_desligamento.recursos_fisicos">
                                <option value="">Selecione</option>
                                <option value="Excelente">Excelente</option>
                                <option value="Ótimo">Ótimo</option>
                                <option value="Bom">Bom</option>
                                <option value="Regular">Regular</option>
                                <option value="Ruim">Ruim</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Valores e normas da empresa</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.entrevista_desligamento.valores_normas">
                                <option value="">Selecione</option>
                                <option value="Excelente">Excelente</option>
                                <option value="Ótimo">Ótimo</option>
                                <option value="Bom">Bom</option>
                                <option value="Regular">Regular</option>
                                <option value="Ruim">Ruim</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Planejamento, organização e metas estabelecidas:</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.entrevista_desligamento.planejamento">
                                <option value="">Selecione</option>
                                <option value="Excelente">Excelente</option>
                                <option value="Ótimo">Ótimo</option>
                                <option value="Bom">Bom</option>
                                <option value="Regular">Regular</option>
                                <option value="Ruim">Ruim</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Seu superior imediato</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.entrevista_desligamento.sob_superior_imediato">
                                <option value="">Selecione</option>
                                <option value="Excelente">Excelente</option>
                                <option value="Ótimo">Ótimo</option>
                                <option value="Bom">Bom</option>
                                <option value="Regular">Regular</option>
                                <option value="Ruim">Ruim</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Da direção da empresa</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.entrevista_desligamento.direcao_empresa">
                                <option value="">Selecione</option>
                                <option value="Excelente">Excelente</option>
                                <option value="Ótimo">Ótimo</option>
                                <option value="Bom">Bom</option>
                                <option value="Regular">Regular</option>
                                <option value="Ruim">Ruim</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Oportunidades de treinamento e de crescimento</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.entrevista_desligamento.oportunidades">
                                <option value="">Selecione</option>
                                <option value="Excelente">Excelente</option>
                                <option value="Ótimo">Ótimo</option>
                                <option value="Bom">Bom</option>
                                <option value="Regular">Regular</option>
                                <option value="Ruim">Ruim</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Seu salário e Benefícios oferecidos</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.entrevista_desligamento.salario_beneficio">
                                <option value="">Selecione</option>
                                <option value="Excelente">Excelente</option>
                                <option value="Ótimo">Ótimo</option>
                                <option value="Bom">Bom</option>
                                <option value="Regular">Regular</option>
                                <option value="Ruim">Ruim</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Sua atividade</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.entrevista_desligamento.atividade">
                                <option value="">Selecione</option>
                                <option value="Excelente">Excelente</option>
                                <option value="Ótimo">Ótimo</option>
                                <option value="Bom">Bom</option>
                                <option value="Regular">Regular</option>
                                <option value="Ruim">Ruim</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Comentários</label>
                            <textarea v-model="form.entrevista_desligamento.comentarios"
                                      class="form-control" cols="5"
                                      rows="5"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="">Parecer do Entrevistador</label>
                            <textarea v-model="form.entrevista_desligamento.parecer_entrevistador"
                                      onblur="valida_campo_vazio(this,1)"
                                      class="form-control" cols="5"
                                      rows="5"></textarea>
                        </div>

                        <div class="form-group">
                            <label>O Colaborador poderá ser recontratado pela empresa em outro momento</label>
                            <select onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)"
                                    class="form-control" v-model="form.entrevista_desligamento.pode_voltar">
                                <option value="">Selecione</option>
                                <option :value="true">Sim</option>
                                <option :value="false">Não</option>
                            </select>
                        </div>

                        <div class="form-group" v-show="form.entrevista_desligamento.pode_voltar !== ''">
                            <label for="">Por quê:</label>
                            <textarea v-model="form.entrevista_desligamento.porque_pode_voltar"
                                      onblur="valida_campo_vazio(this,1)"
                                      class="form-control" cols="5"
                                      rows="5"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="">Entrevistado por:</label>
                            <input type="text" class="form-control"
                                   v-model="form.entrevista_desligamento.quem_entrevistou"
                                   onblur="valida_campo_vazio(this,1)">
                        </div>

                        <div class="form-group">
                            <label for="">Preenchido por:</label>
                            <input type="text" class="form-control"
                                   v-model="form.entrevista_desligamento.preenchido_por"
                                   onblur="valida_campo_vazio(this,1)">
                        </div>

                    </fieldset>
                </div>
            </div>
        </template>

        <template slot="rodape">
            <button class="btn btn-sm btn-primary" v-show="!preload && !atualizado" v-if="avaliacao" @click="avaliar">
                Demitir
            </button>
            <button class="btn btn-sm btn-primary" v-show="!preload && !atualizado" v-if="desmobilizacao"
                    @click="desmobilizar">Desmobilizar
            </button>
            <button class="btn btn-sm btn-primary" v-show="!preload && !atualizado" v-if="entrevista"
                    @click="entrevistar">Salvar entrevista
            </button>
        </template>
    </modal>

    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">

                <label>Buscar</label>
                <input type="text"
                       placeholder="Buscar por nome"
                       autocomplete="off"
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoBusca">

            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">

                <label>CPF</label>
                <input type="text"
                       placeholder="Buscar por cpf"
                       autocomplete="mastertag"
                       v-mascara:cpf
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoCPF">

            </div>


            <div class="col-12 col-sm-6 col-md-6 col-lg-3">

                <label>Áreas</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoArea">
                    <option value="">Sem filtro</option>
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

            <div class="col-12 col-sm-6 col-md-6 col-lg-3">

                <label>Avaliação</label>
                <select class="custom-select custom-select-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.campoFeedback">
                    <option value="">Sem filtro</option>
                    {{--                            <option value="não">Não avaliado</option>--}}
                    <option value="DESTAQUE">Destaque</option>
                    <option value="RETORNA">Retorna</option>
                    <option value="NÃO RETORNA">Não retorna</option>
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
        </form>
        <br>
        <div class="col-12">
            <div class="row">
                <button type="button" class="btn btn-sm btn-success mr-1" :disabled="controle.carregando"
                        @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
                <button class="btn btn-sm btn-danger"
                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="selecionados.length === 0" @click="selecionados = []">
                    <i class="fa fa-times"></i> Limpar seleção
                </button>
                {{--                <form target="_blank"--}}
                {{--                      action="{{ \App\Models\Sistema::UrlServidor }}/admissao/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS"--}}
                {{--                      --}}{{--                      action="{{ route('admissao.excel') }}"--}}
                {{--                      method="get">--}}
                {{--                    @csrf--}}
                {{--                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">--}}
                {{--                    <input type="hidden" name="campoVaga" :value="controle.dados.campoVaga">--}}
                {{--                    <input type="hidden" name="campoUf" :value="controle.dados.campoUf">--}}
                {{--                    <input type="hidden" name="campoRh" :value="controle.dados.campoRh">--}}
                {{--                    <input type="hidden" name="campoFinalRh" :value="controle.dados.campoFinalRh">--}}
                {{--                    <input type="hidden" name="campoRota" :value="controle.dados.campoRota">--}}
                {{--                    <input type="hidden" name="campoTecnica" :value="controle.dados.campoTecnica">--}}
                {{--                    <input type="hidden" name="campoTeste" :value="controle.dados.campoTeste">--}}
                {{--                    <input type="hidden" name="campoPcd" :value="controle.dados.campoPcd">--}}
                {{--                    <button type="submit" class="btn btn-sm btn-primary ml-1"--}}
                {{--                            :disabled="controle.carregando || (!controle.carregando && lista.length===0 && selecionados.length === 0 ) ">--}}
                {{--                        <i class="fas fa-file-excel"></i> Exportar Excel <span class="badge badge-light"--}}
                {{--                                                                               v-show="selecionados.length > 0">@{{ selecionados.length }}</span>--}}
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
        <div v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">
                        <input type="checkbox"
                               :checked="tudoMarcado"
                               :disabled="comAvaliacao.length === 0"
                               style="cursor: pointer"
                               @click="selecionaTodos">
                    </th>
                    <th class="text-center">ID</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th class="text-center">Área</th>
                    <th class="text-center">Cargo</th>
                    <th class="text-center">Data da Admissão</th>
                    <th class="text-center">Avaliação</th>
                    <th class="text-center">Ação</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in lista">
                    <td class="text-center">
                        <label :for="item.curriculo_id">
                            <input
                                type="checkbox"
                                v-model="selecionados"
                                :value="item.feedback.curriculo_id"
                                :id="item.feedback.curriculo_id"
                                :style="item.avaliacao ? 'cursor:pointer' : 'cursor: not-allowed'"
                                :title="item.avaliacao ? null : 'Não possui cadastro em Avaliação'"
                                v-if="item.avaliacao"
                            >
                            <input type="checkbox" v-else disabled="disabled"
                                   title="Sem Avaliação">

                        </label>
                    </td>
                    <td class="text-center">
                        @{{item.id}}
                    </td>
                    <td>
                        @{{item.curriculo.nome}}
                    </td>
                    <td>
                        @{{item.curriculo.cpf}}
                    </td>

                    <td class="text-center">
                        @{{item.admissao.area_etiqueta ? item.admissao.area_etiqueta.label : null}}
                    </td>
                    <td class="text-center">
                        @{{item.admissao.cargo}}
                    </td>

                    <td class="text-center">
                        @{{item.admissao.data_admissao}}
                    </td>
                    <td class="text-center">
                        @{{item.admissao.avaliacao}}
                    </td>

                    <td class="text-center">

                        <div class="dropdown show">
                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item" href="javascript://" title="Avaliar"
                                   @click.prevent="formAvaliar(item.id)"
                                   data-toggle="modal"
                                   data-target="#janelaAvaliar">
                                    Avaliar
                                </a>
                                <a class="dropdown-item" href="javascript://" title="Desmobilizar"
                                   @click.prevent="formDesmobilizar(item.id)"
                                   data-toggle="modal"
                                   data-target="#janelaAvaliar">
                                    Desmobilizar
                                </a>
                                @can('posadmissao_entrevista_desligamento')
                                    <a class="dropdown-item" href="javascript://" title="Entrevistar"
                                       @click.prevent="formEntrevistar(item.id)"
                                       data-toggle="modal"
                                       data-target="#janelaAvaliar">
                                        Entrevistar
                                    </a>
                                @endif
                            </div>
                        </div>


                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.posadmissao.posadmissao.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/posadmissao/app.js')}}"></script>
@endpush
@push('css')
    <style>
        .dropdown-menu {
            float: right !important;
        }
    </style>
@endpush
