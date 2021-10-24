@extends('layouts.sistema')
@section('title', 'Entrevista Técnica')
@section('content_header','Entrevista Técnica')
@section('content')
    <modal id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template slot="conteudo">

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.pcd" @click="colunasTabela.pcd = !colunasTabela.pcd"
                       class="custom-control-input" id="pcd">
                <label class="custom-control-label"
                       for="pcd">PCD</label>
            </div>


            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.parecer_rh"
                       @click="colunasTabela.parecer_rh = !colunasTabela.parecer_rh" class="custom-control-input"
                       id="parecer_rh">
                <label class="custom-control-label"
                       for="parecer_rh">PARECER RH NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.tecnica_nota"
                       @click="colunasTabela.tecnica_nota = !colunasTabela.tecnica_nota" class="custom-control-input"
                       id="tecnica_nota">
                <label class="custom-control-label"
                       for="tecnica_nota">ENTREVISTA TÉCNICA NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.teste_pratico_nota"
                       @click="colunasTabela.teste_pratico_nota = !colunasTabela.teste_pratico_nota"
                       class="custom-control-input" id="teste_pratico_nota">
                <label class="custom-control-label"
                       for="teste_pratico_nota">TESTE PRÁTICO NOTA</label>
            </div>
        </template>
    </modal>

    <modal id="janelaParecerEntrevista" :titulo="tituloJanela" :size="80" :fechar="!preload">
        <template slot="conteudo">
            <preload v-if="preload"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''">
                <dados-pessoais :form="form"></dados-pessoais>
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <div class="form-group">
                            <label>TIPO DE ENTREVISTA</label>
                            <select class="form-control" disabled="disabled"
                                    v-model="form.parecer_tecnica.tipo_entrevista">
                                <option value="Fixo">Fixo</option>
                                <option value="Parada">Parada</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form-group">
                            <label>TIPO DE CONTRATAÇÃO</label>
                            <select class="form-control" :disabled="visualizar"
                                    onchange="valida_campo_vazio(this,1)"
                                    onblur="valida_campo_vazio(this,1)"
                                    v-model="form.parecer_tecnica.tipo_contratacao">
                                <option value="Operacional">Operacional</option>
                                <option value="Administrativa">Administrativa</option>
                            </select>
                        </div>
                    </div>
                </div>

                <fieldset>
                    <legend class="text-uppercase">Informações</legend>

                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>TEMPO NA FUNÇÃO</label>
                                <input type="text" class="form-control" :disabled="visualizar"
                                       v-model="form.parecer_tecnica.tempo_funcao">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>JÁ TRABALHOU NA ALUMAR?</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-model="form.parecer_tecnica.trabalhou_alumar">
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>TEM ROTA?</label>
                                <select class="form-control" :disabled="visualizar"
                                        v-model="form.parecer_tecnica.rota">
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>DISPONIBILIDADE PARA TURNO?</label>
                                <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)" :disabled="visualizar"
                                        v-model="form.parecer_tecnica.turno"
                                        v-if="form.parecer_tecnica.tipo_entrevista === 'Fixo'"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>


                                <select class="form-control" :disabled="visualizar"
                                        v-model="form.parecer_tecnica.turno"
                                        v-if="form.parecer_tecnica.tipo_entrevista === 'Parada'"
                                >
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <fieldset>
                        <legend>INDICAÇÃO?</legend>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <select class="form-control" :disabled="visualizar"
                                            v-model="form.parecer_tecnica.indicado">
                                        <option value="">Selecione</option>
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12" v-if="form.parecer_tecnica.indicado">
                                <div class="form-group">
                                    <label style="font-size: 13px;">INDICADOR POR?</label>
                                    <input type="text" class="form-control"
                                           v-model="form.parecer_tecnica.indicado_por" :disabled="visualizar">
                                </div>
                            </div>
                        </div>
                    </fieldset>


                    <fieldset>
                        <legend>CONHECE E PRATICA AS NORMAS DE SSMA?</legend>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <select class="form-control" :disabled="visualizar"
                                            v-model="form.parecer_tecnica.ssma">
                                        <option value="">Selecione</option>
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12" v-if="form.parecer_tecnica.ssma">
                                <div class="form-group">
                                    <label style="font-size: 13px;">ESPECIFIQUE</label>
                                    <textarea class="form-control"
                                              v-model="form.parecer_tecnica.ssma_ex" cols="3"
                                              rows="5" :disabled="visualizar"></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div v-if="form.parecer_tecnica.tipo_contratacao === 'Operacional'">
                        <fieldset>
                            <legend>JÁ USOU EPI DO TIPO ROUPA DE PVC (AMARELINHA)?</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <select class="form-control" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.roupa_pvc">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12" v-if="form.parecer_tecnica.roupa_pvc_ex">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">ESPECIFIQUE</label>
                                        <textarea class="form-control" onblur="valida_campo_vazio(this,1)"
                                                  v-model="form.parecer_tecnica.ssma_ex" cols="3"
                                                  v-if="form.parecer_tecnica.tipo_entrevista === 'Fixo'"
                                                  rows="5" :disabled="visualizar"></textarea>

                                        <textarea class="form-control"
                                                  v-if="form.parecer_tecnica.tipo_entrevista === 'Parada'"
                                                  v-model="form.parecer_tecnica.ssma_ex" cols="3"
                                                  rows="5" :disabled="visualizar"></textarea>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset v-if="form.parecer_tecnica.roupa_pvc_ex">
                            <legend>TEM ALGUMA DIFICULDADE EM USAR EPI DO TIPO ROUPA DE PVC NA ÁREA SE FOR NECESSÁRIO?
                            </legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <select class="form-control" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.roupa_pvc_dificuldade">
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>VOCÊ JÁ INVERTEU RAQUETE COM PRODUTO QUIMICO DENTRO?</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;"></label>--}}
                                        <select class="form-control" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.trabalhou_raquete_produto_quimico">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-12" v-if="form.parecer_tecnica.trabalhou_raquete_produto_quimico">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">ESPECIFIQUE</label>
                                        <textarea :disabled="visualizar" class="form-control"
                                                  v-model="form.parecer_tecnica.trabalhou_raquete_produto_quimico_ex"
                                                  cols="3" rows="5"></textarea>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>QUAIS OS TIPOS DE TALHA?</legend>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;"></label>--}}
                                        <textarea :disabled="visualizar" class="form-control"
                                                  v-model="form.parecer_tecnica.tipos_de_talha"
                                                  cols="3"
                                                  rows="5"></textarea>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>VOCÊ SABE COMO INICIAR A ABERTURA E FECHAMENTO FLANGE COM USO DE PNEUTORQUE OU CHAVE
                                DE
                                BATER?
                            </legend>
                            <div class="row">
                                <div class="col-12 col-6">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;"></label>--}}
                                        <select class="form-control" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.fechamento_flange">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-12" v-if="form.parecer_tecnica.fechamento_flange">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">ESPECIFIQUE</label>
                                        <textarea :disabled="visualizar" class="form-control"
                                                  v-model="form.parecer_tecnica.fechamento_flange_ex" cols="3"
                                                  rows="5"></textarea>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>QUANTOS MILIMETROS TEM UMA POLEGADA?</legend>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        {{--                                    <label style="font-size: 13px;"></label>--}}
                                        <input type="text" :disabled="visualizar" class="form-control"
                                               v-model="form.parecer_tecnica.milimetros_polegada">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>VOCÊ JÁ TROCOU VALVULAS OU TUBULAÇÃO COM USO DE TALHA CORRENTE OU CATRACA?</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;"></label>--}}
                                        <select class="form-control" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.trocou_valvulas">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-12" v-if="form.parecer_tecnica.trocou_valvulas">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">ESPECIFIQUE</label>
                                        <textarea :disabled="visualizar" class="form-control"
                                                  v-model="form.parecer_tecnica.trocou_valvulas_ex" cols="3"
                                                  rows="5"></textarea>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>QUAIS AS FERRAMENTAS UTILIZADAS PARA ELEVAÇÃO DE CARGA?</legend>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;"></label>--}}
                                        <textarea :disabled="visualizar" class="form-control"
                                                  v-model="form.parecer_tecnica.ferramentas_elevacao_carga" cols="3"
                                                  rows="5"></textarea>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>CAMPOS PARA SOLDADOR</legend>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">
                                            Qual a abertura correta para enraizar um tubo de 6 polegadas? <span
                                                class="text-danger">Resposta: Tubo de 6 Polegada - Abertura Correta 6 milimetro</span></label>
                                        <select class="form-control" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.abertura_tubo_seis_polegada">
                                            <option value="">Selecione</option>
                                            <option :value="true">Acertou</option>
                                            <option :value="false">Errou</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">
                                            Qual a Vareta utilizada para enraizar um tubo de 6 polegadas? <span
                                                class="text-danger">Resposta: Vai usar Vareta TIG 1,8º</span></label>
                                        <select class="form-control" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.vareta_seis_polegada">
                                            <option value="">Selecione</option>
                                            <option :value="true">Acertou</option>
                                            <option :value="false">Errou</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">
                                            Quantos filete de acabamento é necessário em um tubo de 6 polegadas? <span
                                                class="text-danger">Resposta: 3 Filetes: 1 Eletrodo | 2,5 Eletrodo | Acabamento com 3 Filete</span></label>
                                        <select class="form-control" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.filete_acabemento">
                                            <option value="">Selecione</option>
                                            <option :value="true">Acertou</option>
                                            <option :value="false">Errou</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>TEM EXPERIÊNCIA COM MANUSEIO DE MAÇARICO?</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <select class="form-control" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.manuseio_macarico"
                                                onblur="valida_campo_vazio(this,1)">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>


                                    </div>
                                </div>

                                <div class="col-12" v-if="form.parecer_tecnica.manuseio_macarico">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">ESPECIFIQUE</label>
                                        <textarea :disabled="visualizar" class="form-control"
                                                  v-model="form.parecer_tecnica.manuseio_macarico_ex"
                                                  onblur="valida_campo_vazio(this,1)"
                                                  cols="3"
                                                  rows="5"></textarea>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>VOCÊ JÁ TRABALHOU COMO MECANICO DE MANUTENÇÃO?</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;"></label>--}}
                                        <select class="form-control" :disabled="visualizar"
                                                onblur="valida_campo_vazio(this,1)"
                                                onchange="valida_campo_vazio(this,1)"
                                                v-model="form.parecer_tecnica.trabalhou_mecanico_manutencao">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-12" v-if="form.parecer_tecnica.trabalhou_mecanico_manutencao">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">ESPECIFIQUE</label>
                                        <textarea :disabled="visualizar" class="form-control"
                                                  onblur="valida_campo_vazio(this,1)"
                                                  onchange="valida_campo_vazio(this,1)"
                                                  v-model="form.parecer_tecnica.trabalhou_mecanico_manutencao_ex"
                                                  cols="3" rows="5"></textarea>

                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>OPERA PLATAFORMA MÓVEL?</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;">OPERA PLATAFORMA MÓVEL?</label>--}}
                                        <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                                onchange="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.opera_plat_movel">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-12" v-if="form.parecer_tecnica.opera_plat_movel">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">ESPECIFIQUE</label>
                                        <textarea :disabled="visualizar" class="form-control"
                                                  onblur="valida_campo_vazio(this,1)"
                                                  v-model="form.parecer_tecnica.opera_plat_movel_ex" cols="3"
                                                  rows="5"></textarea>

                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>OPERA PONTE ROLANTE?</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;">OPERA PONTE ROLANTE?</label>--}}
                                        <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                                onchange="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.opera_plat_ponte">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-12" v-if="form.parecer_tecnica.opera_plat_ponte">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">ESPECIFIQUE</label>
                                        <textarea :disabled="visualizar" class="form-control"
                                                  onblur="valida_campo_vazio(this,1)"
                                                  v-model="form.parecer_tecnica.opera_plat_onte_ex" cols="3"
                                                  rows="5"></textarea>

                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>TEM EXPERIÊNCIA COM MOVIMENTAÇÃO DE CARGAS / RIGGER?</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;"></label>--}}
                                        <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                                onchange="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.experiencia_cargas_rigger">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>


                                    </div>
                                </div>

                                <div class="col-12" v-if="form.parecer_tecnica.experiencia_cargas_rigger">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">ESPECIFIQUE</label>
                                        <textarea :disabled="visualizar" class="form-control"
                                                  onblur="valida_campo_vazio(this,1)"
                                                  v-model="form.parecer_tecnica.experiencia_cargas_rigger_ex" cols="3"
                                                  rows="5"></textarea>

                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>JÁ TRABALHOU EM OVERHAUL ANTES?</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;">JÁ TRABALHOU EM OVERHAUL ANTES?</label>--}}
                                        <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                                onchange="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                v-model="form.parecer_tecnica.trabalhou_overhaul">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>


                                    </div>
                                </div>

                                <div class="col-12" v-if="form.parecer_tecnica.trabalhou_overhaul">
                                    <div class="form-group">
                                        <label style="font-size: 13px;">QUAL ÁREA</label>
                                        <textarea :disabled="visualizar" class="form-control"
                                                  onblur="valida_campo_vazio(this,1)"
                                                  v-model="form.parecer_tecnica.trabalhou_overhaul_ex" cols="3"
                                                  rows="5"></textarea>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                    </div>

                    <div v-if="form.parecer_tecnica.tipo_contratacao === 'Administrativa'">
                        <fieldset>
                            <legend style="text-transform: uppercase">Me Fale sobre você e suas experiências
                                profissionais
                            </legend>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        {{--                                <label style="font-size: 13px;"></label>--}}
                                        <editor :api-key='config.key' v-model="form.parecer_tecnica.texto_livre"
                                                :disabled="visualizar" :init="config"></editor>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                </fieldset>

                <fieldset>
                    <legend class="text-uppercase">Parecer Final</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Resultado Final</label>
                                <select class="form-control" :disabled="visualizar"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_tecnica.resultado_final">
                                    <option value="">Selecione</option>
                                    <option value="aprovado">Aprovado</option>
                                    <option value="restrito">Restrito</option>
                                    <option value="reprovado">Reprovado</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Nota</label>
                                <select class="form-control" :disabled="visualizar"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)" v-model="form.parecer_tecnica.nota">
                                    <option value="">Selecione</option>
                                    @foreach(range(1,10) as $r)
                                        <option value="{{$r}}">{{$r}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Entrevistado Por:</label>
                                <input type="text" :disabled="visualizar" autocomplete="off" class="form-control"
                                       onblur="valida_campo_vazio(this,3)"
                                       v-model="form.parecer_tecnica.quem_entrevistou">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label>Indicado para qual área?</label>
                            <input type="text" class="form-control" :disabled="visualizar"
                                   onblur="valida_campo_vazio(this,1)" v-model="form.parecer_tecnica.indicado_area">
                        </div>


                        <div class="col-12">
                            <div class="form-group">
                                <label>Observação</label>
                                <textarea :disabled="visualizar" class="form-control"
                                          v-model="form.parecer_tecnica.observacao"
                                          cols="3"
                                          rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>

            </div>
        </template>
        <template slot="rodape">
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="editando && !atualizado  && !preload"
                        @click.prevent="alterar">
                    <i class="fa fa-edit"></i> Alterar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="!editando && !cadastrado  && !preload"
                        @click.prevent="cadastrar">
                    <i class="fa fa-save"></i> Salvar
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
                        <label>Vaga</label>
                        <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                      :valido="controle.dados.campoVaga !== ''"
                                      v-model="controle.dados.autocomplete_label"
                                      :disabled="controle.carregando"
                                      placeholder="Por vaga"
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
                        <label for="">Rota</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.campoRota">
                            <option value="">Geral</option>
                            <option :value="true">Tem Rota</option>
                            <option :value="false">Não tem Rota</option>
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
            </div>
        </form>

        <div class="col-12">
            <div class="row">
                <button type="button" class="btn btn-sm btn-success mb-1 mr-1" :disabled="controle.carregando"
                        @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
                <button class="btn btn-sm btn-danger mb-1 mr-1"
                        :checked="tudoMarcado"
                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="selecionados.length === 0" @click="selecionados = []">
                    <i class="fa fa-times"></i> Limpar seleção
                </button>
                <form target="_blank"
                      {{--                      action="{{\App\Models\Sistema::UrlServidor}}/parecer_rota_transporte/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS"--}}
                      action="{{route('parecer_entrevista_tecnica.excel')}}"
                      method="get">
                    @csrf
                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                    <input type="hidden" name="campoVaga" :value="controle.dados.campoVaga">
                    <input type="hidden" name="campoCliente" :value="controle.dados.campoCliente">
                    <input type="hidden" name="campoUf" :value="controle.dados.campoUf">
                    <input type="hidden" name="campoRota" :value="controle.dados.campoRota">
                    <input type="hidden" name="campoPcd" :value="controle.dados.campoPcd">
{{--                    <button type="submit" class="btn btn-sm btn-primary mb-1"--}}
{{--                            :disabled="(selecionados.length === 0  && controle.dados.campoCliente === '' ||  lista.length===0 ) || controle.carregando">--}}
{{--                        <i class="fas fa-file-excel"></i> Exportar Excel <span class="badge badge-light"--}}
{{--                                                                               v-show="selecionados.length > 0">@{{ selecionados.length }}</span>--}}
{{--                    </button>--}}
                </form>
            </div>
        </div>

    </fieldset>
    <preload class="text-center" v-if="controle.carregando"></preload>
    <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length===0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>

    <div id="conteudo">
        <table class="tabela" v-show="!controle.carregando && lista.length > 0">
            <thead>
            <tr class="bg-default">
                <th style="width: 1em;">
                    <input type="checkbox"
                           :checked="tudoMarcado"
                           :disabled="comTecnica.length === 0"
                           :content="comTecnica.length > 0 ? 'Selecionar todos' : 'Não possui cadastrodo no RH'"
                           v-tippy
                           style="cursor: pointer"
                           @change.prevent="selecionaTodos">
                </th>
                <th class="text-center">ID</th>
                <th>Nome</th>
{{--                <th v-if="cliente_id === 0 && colunasTabela.cliente">Empresa</th>--}}
                <th class="text-center">Vaga</th>
                <th class="text-center" v-show="colunasTabela.pcd">PCD</th>
                <th class="text-center" v-show="colunasTabela.parecer_rh">Parecer RH Nota</th>
                <th class="text-center" v-show="colunasTabela.parecer_rota">Rota Transporte</th>
                <th class="text-center">Entrevista Técnica Nota</th>
                <th class="text-center" v-show="colunasTabela.teste_pratico_nota">Teste Prático Nota</th>
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
            <tr v-for="entrevista in lista">
                <td class="text-center">
                    <label :for="entrevista.id">
                        <input
                            type="checkbox"
                            v-model="selecionados"
                            :value="entrevista.id"
                            :id="entrevista.id"
                            :style="entrevista.parecer_tecnica ? 'cursor:pointer' : 'cursor: not-allowed'"
                            :title="entrevista.parecer_tecnica ? null : 'Não possui cadastro em rotas'"
                            v-if="entrevista.parecer_tecnica"
                        >
                        <input type="checkbox" v-else disabled="disabled" title="Sem parecer Rota">

                    </label>
                </td>
                <td class="text-center">
                    @{{entrevista.id}}
                </td>
                <td>
                    @{{entrevista.curriculo.nome}}
                    <br>
                    @{{entrevista.curriculo.cpf}}
                </td>
{{--                <td class="text-center" v-if="cliente_id === 0 && colunasTabela.cliente">--}}
{{--                    @{{entrevista.cliente.razao_social}}--}}
{{--                </td>--}}
                <td class="text-center">
                    @{{entrevista.vaga_selecionada.nome}}
                </td>
                <td class="text-center" v-show="colunasTabela.pcd">
                    @{{entrevista.curriculo.pcd ? 'Sim' : 'Não'}}
                </td>
                <td class="text-center" v-show="colunasTabela.parecer_rh">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-show="colunasTabela.parecer_rota">
                    @{{ entrevista.parecer_rota ? entrevista.parecer_rota.rota_atende != null ?
                    entrevista.parecer_rota.rota_atende === true ? 'Sim': 'Não' : 'Não Informado' : 'aguardando' }}
                    {{--                        @{{entrevista.parecer_rota ? entrevista.parecer_rota.TemRotaFormat : 'aguardando'}}--}}
                </td>

                <td class="text-center">
                    @{{entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-show="colunasTabela.teste_pratico_nota">
                    @{{entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'aguardando'}}

                </td>

                <td class="text-center">
                    <form :action="`${URL_ADMIN}/entrevistas/parecer-entrevista-tecnica/ficha_pdf`" target="_blank"
                          method="post">
                        <button class="btn btn-sm btn-primary mb-2" content="Entrevistar" v-tippy
                                v-show="!entrevista.parecer_tecnica"
                                @click.prevent="formEntrevistar(entrevista.id)"
                                data-toggle="modal"
                                data-target="#janelaParecerEntrevista">
                            <i class="far fa-list-alt"></i>
                        </button>

                        @can('parecer_entrevista_update')
                            <button class="btn btn-sm btn-primary mb-2" content="Editar" v-tippy
                                    v-show="entrevista.parecer_tecnica"
                                    @click.prevent="formEntrevistar(entrevista.id); editando = true"
                                    data-toggle="modal"
                                    data-target="#janelaParecerEntrevista">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </button>
                        @endcan

                        <button class="btn btn-sm btn-primary mb-2" content="Visualizar" v-tippy
                                v-show="entrevista.parecer_tecnica"
                                @click.prevent="formEntrevistar(entrevista.id); visualizar = true"
                                data-toggle="modal"
                                data-target="#janelaParecerEntrevista">
                            <i class="fa fa-search-plus" aria-hidden="true"></i>
                        </button>

                        @csrf
                        <input type="hidden" name="id" :value="entrevista.parecer_tecnica.id"
                               v-if="entrevista.parecer_tecnica">
                        <button type="submit" content="Gerar PDF" v-tippy v-show="entrevista.parecer_tecnica"
                                class="btn btn-sm btn-primary mb-2">
                            <i class="fa fa-file-pdf" aria-hidden="true"></i>
                        </button>
                    </form>
                </td>
            </tr>
        </table>
    </div>

    <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                        url="{{route('g.entrevista.parecer_entrevista_tecnica.atualizar')}}"
                        :por-pagina="controle.dados.porPagina"
                        :dados="controle.dados"
                        v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>

@endsection
@push('js')
    <script src="{{mix('js/g/entrevistas/parecer_entrevista_tecnica/app.js')}}"></script>
@endpush
