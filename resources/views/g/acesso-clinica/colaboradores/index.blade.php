@extends('layouts.sistema')
@section('title', 'CONTROLE DE EXAMES - COLABORADORES')
@section('content_header', 'CONTROLE DE EXAMES - COLABORADORES')
@section('content')
    <modal id="validaSesmt" :titulo="abasesmt.tituloJanela" modal-pai='janelaParecerEntrevista' :size="80"
           :fechar="!abasesmt.preload">
        <template #conteudo>
            <preload v-if="abasesmt.preload" label="Aguarde ...."></preload>
            <fieldset v-if="!abasesmt.preload">
                <legend>Exame</legend>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">EXAME REALIZADO?</label>
                            <select class="form-control validacampo" v-model='abasesmt.form.exame_realizado'
                                    @keyup.prevent="valida_campo_vazio($event.target,1)"
                                    @blur.prevent="valida_campo_vazio($event.target,1)"
                                    @change="limpaformResultado()"
                            >
                                <option :value="null">Selecione...</option>
                                <option :value="true">Sim</option>
                                <option :value="false">Não</option>
                            </select>
                        </div>
                    </div>

                    <template v-if="abasesmt.form.exame_realizado">
                        <div class="col-12 col-md-6">
                            <label for="">DATA DO EXAME:
                            </label>
                            <input type="text" class="form-control validacampo" v-model="abasesmt.form.data_realizacao"
                                   v-mascara:data
                                   @keyup.prevent="valida_data_vazio($event.target)"
                                   @blur.prevent="valida_data_vazio($event.target)">
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">RESULTADO DO EXAME</label>
                                <select class="form-control validacampo" v-model="abasesmt.form.resultado.result"
                                        @keyup.prevent="valida_campo_vazio($event.target,1)"
                                        @blur.prevent="valida_campo_vazio($event.target,1)">
                                    <option :value="null">Selecione ...</option>
                                    <option value="Apto">Apto</option>
                                    <option value="Apto com Restrição">Apto com Restrição</option>
                                    <option value="Inapto">Inapto</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">Há pendencias</label>
                                <select class="form-control validacampo" v-model="abasesmt.form.resultado.pendencias"
                                        @keyup.prevent="valida_campo_vazio($event.target,1)"
                                        @blur.prevent="valida_campo_vazio($event.target,1)"
                                >
                                    <option :value="null">Selecione ...</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6" v-if='abasesmt.form.resultado.pendencias === "Sim" '>
                            <div class="form-group">
                                <label for="">Quais</label>
                                <input type="text" class="form-control validacampo"
                                       v-model="abasesmt.form.resultado.pendencias_quais"
                                       @keyup.prevent="valida_campo_vazio($event.target,1)"
                                       @blur.prevent="valida_campo_vazio($event.target,1)"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">Aprovado</label>
                                <select class="form-control validacampo"
                                        v-model="abasesmt.form.resultado.aprovado"
                                        @keyup.prevent="valida_campo_vazio($event.target,1)"
                                        @blur.prevent="valida_campo_vazio($event.target,1)"
                                >
                                    <option :value="null">Selecione ...</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">APTO TRABALHO EM ALTURA?</label>
                                <select class="form-control validacampo"
                                        v-model="abasesmt.form.resultado.trabalho_altura"
                                        @keyup.prevent="valida_campo_vazio($event.target,1)"
                                        @blur.prevent="valida_campo_vazio($event.target,1)"
                                >
                                    <option :value="null">Selecione ...</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                    <option value="Não se aplica">Não se aplica</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">APTO TRABALHO EM ESPACO CONFINADO?</label>
                                <select class="form-control validacampo"
                                        v-model="abasesmt.form.resultado.espacao_confinado"
                                        @keyup.prevent="valida_campo_vazio($event.target,1)"
                                        @blur.prevent="valida_campo_vazio($event.target,1)"
                                >
                                    <option :value="null">Selecione ...</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                    <option value="Não se aplica">Não se aplica</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-12">
                            <div class="form-group">
                                <label for="">Observações</label>
                                <textarea class='form-control' cols='30' rows='5'
                                          v-model='abasesmt.form.resultado.observacoes'>
                                </textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>Anexo</legend>
                                <upload :model="abasesmt.form.anexos"
                                        :model-delete="abasesmt.form.anexosDel"
                                        :url="url_anexo"
                                        label="Selecionar"
                                        @onProgresso="anexoUploadAndamento=true"
                                        @onFinalizado="anexoUploadAndamento=false"></upload>
                            </fieldset>
                        </div>

                    </template>
                </div>
            </fieldset>
        </template>
        <template #rodape>
            {{--            <div v-show="!visualizar">--}}
            <button type="button" class="btn btn-sm mr-1 btn-primary"
                    v-if="!abasesmt.preload"
                    @click.prevent="salvarResultado">
                <i class="fa fa-save"></i> Salvar
                {{--                                <span v-show='cadastrando'>Salvar</span>--}}
                {{--                                <span v-show='editando'>Editando</span>--}}
            </button>
            {{--            </div>--}}
        </template>
    </modal>

    <modal id="janelaParecerEntrevista" :titulo="tituloJanela" :size="80" :fechar="!preload">
        <template #conteudo>
            <preload v-if="preload"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''">
{{--                <fieldset>--}}
{{--                    <legend class="text-uppercase">Dados Pessoais</legend>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-12">--}}
{{--                            <p>--}}
{{--                                Nome: <strong>@{{ dados.nome }}</strong> <br>--}}
{{--                                <br>--}}
{{--                                Cargo: <strong>@{{ dados.cargo }}</strong> <br>--}}
{{--                            </p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </fieldset>--}}


                <div class="tab-content py-3 p-2">
                    <h4>Exames</h4>
                        <template>
                            <div class="alert alert-warning text-center"
                                 v-show="!preload && historico.length===0">
                                <i class="fa fa-exclamation-triangle"></i> Nenhum Encaminhamento Encontrado.
                            </div>
                            <div v-show="!preload && historico.length > 0">
                                <table class="tabela table-striped">
                                    <thead>
                                    <tr class="bg-default">
                                        <th>CÓD</th>
                                        <th>Tipo de exame</th>
                                        <th>Clinica</th>
                                        <th>Encaminhado Por</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr style="background: white !important; border-bottom: none"
                                        v-for="item in lista"
                                        :key="item.id"
                                    >
                                        <td>@{{ item.id }}</td>
                                        <td>@{{ item.tipo_exame }}</td>
                                        <td>@{{ item.feedback.curriculo.nome }}</td>
                                        <td>
                                            @{{ item.quem_encaminhou.nome }}<br>em @{{ item.created_at }}
                                        </td>
                                        <td>
                                            <button type="button" content="Resultado exame" v-tippy
                                                    class="btn btn-sm mr-1 btn-primary mb-2" data-toggle="modal"
                                                    data-target="#validaSesmt" @click='formResultado(item.id)'>
                                                <i class="fa fa-search-plus" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                </div>
            </div>
        </template>
        <template #rodape>
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="!cadastrado & nav === 'encaminhar'  && !preload"
                        @click.prevent="salvarUpdate">
                    <i class="fa fa-save"></i>
                    <span v-show='cadastrando'>Salvar</span>
                    <span v-show='editando'>Editando</span>
                </button>
            </div>
        </template>
    </modal>
    <fieldset>
        <legend>Filtro</legend>
        <form @submit.prevent="$refs.componente.buscar()">
            <div class="row">
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
                        <input type="text" placeholder="Buscar por nome" autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" placeholder="Buscar por cpf" autocomplete="mastertag"
                               onblur="valida_cpf(this)"
                               v-mascara:cpf class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoCPF">
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label for="">Exibir</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.pages">
                            <option v-for='exib in exibicao' :value="exib">@{{exib}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
        <div class="col-12">
            <div class="row">
                <button type="button" class="btn btn-sm mr-1 btn-success mr-1 mb-1" :disabled="controle.carregando"
                        @click.prevent="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
            </div>
        </div>

    </fieldset>
    <preload class="text-center" v-if="controle.carregando"></preload>
    <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length===0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>

    <div id="conteudo">
        <table class="tabela table-striped" v-show="!controle.carregando && lista.length > 0">
            <thead>
            <tr class="bg-default">
                <th>COD</th>
{{--                <th>Empresa</th>--}}
                <th>Nome</th>
                <th>Cargo</th>
                <th>

                </th>
            </tr>
            </thead>
            <tbody v-for="colaborador in lista">
            <tr style="background: white !important; border-bottom: none">
                <td>@{{ colaborador.id }}</td>
{{--                <td></td>--}}
                <td><strong>@{{ colaborador.feedback.curriculo.nome }}</strong>
                    <br> @{{ colaborador.feedback.vaga_selecionada.nome }}
                </td>
                <td></td>
                <td class="text-center">
                    <button class="btn btn-sm mr-1 btn-primary mb-2" content="Historico" v-tippy
                            v-show="!colaborador.resultado_integrado"
                            @click.prevent="formEncaminhar(colaborador)"
                            data-toggle="modal" data-target="#janelaParecerEntrevista">
                        <i class="fa fa-search-plus"></i> Abrir
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                        :url="urlPaginacao"
                        :por-pagina="controle.dados.porPagina" :dados="controle.dados" @carregou="carregou"
                        @carregando="carregando">
    </controle-paginacao>
@stop
@push('js')
    <script src="{{ mix('js/g/clinica/controle-exames/app.js') }}"></script>
@endpush
