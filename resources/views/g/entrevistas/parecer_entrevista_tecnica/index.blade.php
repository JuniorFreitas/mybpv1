@extends('layouts.sistema')
@section('title', 'Entrevista Técnica')
@section('content_header','Entrevista Técnica')
@section('content')
    <modal ref="filtroColunas" id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template #conteudo>

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

    <modal ref="janelaParecerEntrevista" id="janelaParecerEntrevista" :titulo="tituloJanela" :size="80" :fechar="!preload">
        <template #conteudo>
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
                                        <tiny-mce-editor v-model="form.parecer_tecnica.texto_livre"
                                                :disabled="visualizar" preset="" :init="config"></tiny-mce-editor>
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
        <template #rodape>
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="editando && !atualizado  && !preload"
                        @click.prevent="alterar">
                    <i class="fa fa-edit"></i> Alterar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="!editando && !cadastrado  && !preload"
                        @click.prevent="cadastrar">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </div>
        </template>
    </modal>

    <filtro-entrevista-compacto
        :dados="controle.dados"
        :carregando="controle.carregando"
        :abertos="filtrosAvancadosAbertos"
        :tem-filtros="temFiltrosEntrevista"
        :total-avancados="totalFiltrosAvancadosEntrevista"
        id-prefix="tecnica"
        label-cargo="Cargo"
        @update:abertos="filtrosAvancadosAbertos = $event"
        @submit="atualizar"
        @limpar="limparFiltrosEntrevista"
        @periodo="onPeriodoEntrevista"
        @select-vaga="selecionaVaga"
        @blur-vaga="resetaCampo"
    >
        <template #avancados>

                <combo-filtro-entrevista
                    ref="comboFiltroUf"
                    input-id="tecnica-uf"
                    label="Estado"
                    placeholder="Todos os estados"
                    v-model="controle.dados.campoUf"
                    :options="opcoesUfFiltro"
                    :disabled="controle.carregando"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroRota"
                    input-id="tecnica-rota"
                    label="Rota"
                    placeholder="Geral"
                    v-model="controle.dados.campoRota"
                    :options="opcoesRotaBooleana"
                    :disabled="controle.carregando"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroPages"
                    input-id="tecnica-pages"
                    label="Exibir"
                    placeholder="20"
                    v-model="controle.dados.pages"
                    :options="opcoesPagesFiltro"
                    :disabled="controle.carregando"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>
        </template>
        <template #acoes>

            <button type="button" class="btn btn-sm btn-outline-secondary mybp-btn-acao-compact"
                    v-show="selecionados.length" @click.prevent="selecionados = []">
                <i class="fa fa-times"></i> Limpar seleção
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary mybp-btn-acao-compact"
                    @click.prevent="exportaExcel()"
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && lista.length===0 && selecionados.length === 0)">
                <i class="fas fa-file-excel"></i> Exportar Excel
                <span class="badge badge-light" v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
            </button>

        </template>
    </filtro-entrevista-compacto>
    @include('g.entrevistas.partials.barra_visualizacao', ['tela' => 'entrevista-tecnica', 'colunas' => true])

    <preload class="text-center" v-if="controle.carregando"></preload>
    <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length===0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>

    <div id="conteudo">
        
        <div class="mybp-cards-lista" v-if="!controle.carregando && lista.length > 0 && visualizacao === 'lista'">
            <cartao-entrevista-lista
                v-for="entrevista in lista"
                :key="'lista-' + entrevista.id"
                :entrevista="entrevista"
                :aberto="isDropdownOpen('lista-' + entrevista.id)"
                @toggle="toggleDropdown('lista-' + entrevista.id)"
                @fechar="fecharDropdown"
            >
                <template #selecao>
                    <label class="mb-0 mr-1" :for="'sel-lista-' + entrevista.id" v-if="entrevista.parecer_tecnica" style="cursor: pointer;">
                        <input type="checkbox" :id="'sel-lista-' + entrevista.id" v-model="selecionados" :value="entrevista.id" />
                    </label>
                    <span class="mb-0 mr-1" v-else>
                        <input type="checkbox" disabled />
                    </span>
                </template>
                <template #acoes>
                    <a class="dropdown-item" href="javascript://" v-show="!entrevista.parecer_tecnica"
                       @click.prevent="formEntrevistar(entrevista.id); $refs.janelaParecerEntrevista?.abrirModal()">
                        <i class="far fa-list-alt mr-1"></i> Entrevistar
                    </a>
                    @can('entrevista_parecer_entrevista_update')
                    <a class="dropdown-item" href="javascript://" v-show="entrevista.parecer_tecnica"
                       @click.prevent="formEntrevistar(entrevista.id); editando = true; $refs.janelaParecerEntrevista?.abrirModal()">
                        <i class="fa fa-edit mr-1"></i> Editar
                    </a>
                    @endcan
                    <a class="dropdown-item" href="javascript://" v-show="entrevista.parecer_tecnica"
                       @click.prevent="formEntrevistar(entrevista.id); visualizar = true; $refs.janelaParecerEntrevista?.abrirModal()">
                        <i class="fa fa-search-plus mr-1"></i> Visualizar
                    </a>
                    <form :action="`${URL_ADMIN}/entrevistas/parecer-entrevista-tecnica/ficha_pdf`" target="_blank" method="post" v-if="entrevista.parecer_tecnica">
                        @csrf
                        <input type="hidden" name="id" :value="entrevista.parecer_tecnica.id">
                        <button type="submit" class="dropdown-item">
                            <i class="fa fa-file-pdf mr-1"></i> Gerar PDF
                        </button>
                    </form>
                </template>
                <template #campos>
                    <mybp-card-campo icon="fas fa-briefcase" label="Vaga" :valor="entrevista.vaga_aberta_municipio" forte></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-wheelchair" label="PCD" :valor="entrevista.curriculo && entrevista.curriculo.pcd ? 'Sim' : 'Não'" v-if="colunasTabela.pcd"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-star" label="Parecer RH" :valor="entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'aguardando'" v-if="colunasTabela.parecer_rh"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-route" label="Rota" :valor="entrevista.parecer_rota ? (entrevista.parecer_rota.rota_atende != null ? (entrevista.parecer_rota.rota_atende === true ? 'Sim' : 'Não') : 'Não informado') : 'aguardando'" v-if="colunasTabela.parecer_rota"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-user-tie" label="Ent. técnica" :valor="entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'aguardando'" forte></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-tools" label="Teste prático" :valor="entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'aguardando'" v-if="colunasTabela.teste_pratico_nota"></mybp-card-campo>
                </template>
            </cartao-entrevista-lista>
        </div>

        <div class="table-responsive mybp-tabela-wrap" v-if="!controle.carregando && lista.length > 0 && visualizacao === 'tabela'">
        <table class="tabela mybp-tabela">
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
                <th>CPF</th>
{{--                <th v-if="cliente_id === 0 && colunasTabela.cliente">Empresa</th>--}}
                <th class="text-center">Vaga</th>
                <th class="text-center" v-if="colunasTabela.pcd">PCD</th>
                <th class="text-center" v-if="colunasTabela.parecer_rh">Parecer RH Nota</th>
                <th class="text-center" v-if="colunasTabela.parecer_rota">Rota Transporte</th>
                <th class="text-center">Entrevista Técnica Nota</th>
                <th class="text-center" v-if="colunasTabela.teste_pratico_nota">Teste Prático Nota</th>
                <th class="text-center">Ações</th>
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
                <td class="mybp-col-texto">@{{ entrevista.curriculo.nome }}</td>
                <td>@{{ entrevista.curriculo && entrevista.curriculo.cpf ? entrevista.curriculo.cpf : 'Não informado' }}</td>
{{--                <td class="text-center" v-if="cliente_id === 0 && colunasTabela.cliente">--}}
{{--                    @{{entrevista.cliente.razao_social}}--}}
{{--                </td>--}}
                <td class="text-center">
                    @{{entrevista.vaga_aberta_municipio}}
                </td>
                <td class="text-center" v-if="colunasTabela.pcd">
                    @{{entrevista.curriculo.pcd ? 'Sim' : 'Não'}}
                </td>
                <td class="text-center" v-if="colunasTabela.parecer_rh">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-if="colunasTabela.parecer_rota">
                    @{{ entrevista.parecer_rota ? entrevista.parecer_rota.rota_atende != null ?
                    entrevista.parecer_rota.rota_atende === true ? 'Sim': 'Não' : 'Não Informado' : 'aguardando' }}
                    {{--                        @{{entrevista.parecer_rota ? entrevista.parecer_rota.TemRotaFormat : 'aguardando'}}--}}
                </td>

                <td class="text-center">
                    @{{entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-if="colunasTabela.teste_pratico_nota">
                    @{{entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'aguardando'}}

                </td>

                <td class="text-center">
                    <form :action="`${URL_ADMIN}/entrevistas/parecer-entrevista-tecnica/ficha_pdf`" target="_blank"
                          method="post">
                        <button class="btn btn-sm mr-1 btn-primary mb-2" content="Entrevistar" v-tippy
                                v-show="!entrevista.parecer_tecnica"
                                @click.prevent="formEntrevistar(entrevista.id); $refs.janelaParecerEntrevista?.abrirModal()">
                            <i class="far fa-list-alt"></i>
                        </button>

                        @can('entrevista_parecer_entrevista_update')
                            <button class="btn btn-sm mr-1 btn-primary mb-2" content="Editar" v-tippy
                                    v-show="entrevista.parecer_tecnica"
                                    @click.prevent="formEntrevistar(entrevista.id); editando = true; $refs.janelaParecerEntrevista?.abrirModal()">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </button>
                        @endcan

                        <button class="btn btn-sm mr-1 btn-primary mb-2" content="Visualizar" v-tippy
                                v-show="entrevista.parecer_tecnica"
                                @click.prevent="formEntrevistar(entrevista.id); visualizar = true; $refs.janelaParecerEntrevista?.abrirModal()">
                            <i class="fa fa-search-plus" aria-hidden="true"></i>
                        </button>

                        @csrf
                        <input type="hidden" name="id" :value="entrevista.parecer_tecnica.id"
                               v-if="entrevista.parecer_tecnica">
                        <button type="submit" content="Gerar PDF" v-tippy v-show="entrevista.parecer_tecnica"
                                class="btn btn-sm mr-1 btn-primary mb-2">
                            <i class="fa fa-file-pdf" aria-hidden="true"></i>
                        </button>
                    </form>
                </td>
            </tr>
        </table>
        </div>
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
