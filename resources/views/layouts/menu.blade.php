<li class="menu-title d-block d-sm-none">
    <img src="{{ asset('images/logo_horizontal.svg') }}" alt="SGIBPSE logo horizontal" class=""
         height="65" style=" margin-top: 5px;">
</li>
<li class="pl-3 pr-3 mt-3">
    <div class="form-group">
        <label for="filter-menu" class="text-white">Filtrar Menu</label>
        <input type="text" class="form-control form-control-sm" name="filter-menu" id="filter-menu"
               placeholder="Buscar">
    </div>
</li>
<li class="menu-title">Menu</li>
{{--@if(\App\Models\Sistema::permitirLinks('administracao_clientes'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-user-circle"></i>
            <span>ADMINISTRAÇÃO</span>
        </a>
        <ul aria-expanded="false">
            @can('administracao_clientes')
                <li>
                    <a href="{{route('g.administracao.clientes.clientes.index')}}" key="clientes">
                        CLIENTES
                    </a>
                </li>
            @endcan
            @can('fornecedores')
                <li>
                    <a href="{{route('g.administracao.fornecedor.fornecedor.index')}}" key="fornecedores">
                        FORNECEDORES
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif--}}
<div id="system-menu">
    @if(\App\Models\Sistema::permitirLinks('administracao_clientes','administracao_fornecedores','administracao_atareuniao','administracao_pesquisaclima','administracao_planejamentodiario','administracao_aniversariantes', 'administracao_documentos_legais', 'administracao_carta_oferta_template'))
        <li id="administracao">
            <a href="javascript://" class="has-arrow waves-effect" parent="administracao">
                <i class="bx bxs-book-content"></i>
                <span>ADMINISTRAÇÃO</span>
            </a>
            <ul aria-expanded="false">
                @can('administracao_clientes')
                    <li>
                        <a href="{{route('g.administracao.clientes.clientes.index')}}" parent="administracao"
                           key="clientes">
                            Clientes
                        </a>
                    </li>
                @endcan
                @can('administracao_fornecedores')
                    <li>
                        <a href="{{route('g.administracao.fornecedor.fornecedor.index')}}" parent="administracao"
                           key="fornecedores">
                            Fornecedores
                        </a>
                    </li>
                @endcan
                @can('administracao_documentos_legais')
                    <li id="documentos_legais">
                        <a href="javascript://" class="has-arrow waves-effect" parent="administracao">
                            Documentos Legais</a>
                        <ul aria-expanded="false">
                            <li>
                                <a href="{{route('g.administracao.documentoslegais.contrato.contrato.index')}}"
                                   subparent="documentos_legais" parent="administracao" key="contrato">
                                    Contrato
                                </a>
                            </li>
                            <li>
                                <a href="{{route('g.administracao.documentoslegais.empresa.empresa.index')}}"
                                   subparent="documentos_legais" parent="administracao" key="documentoempresa">
                                    Documentos Empresa
                                </a>
                            </li>
                            <li>
                                <a href="{{route('g.administracao.documentoslegais.ssma.ssma.index')}}"
                                   subparent="documentos_legais" parent="administracao" key="documentossma">
                                    Documentos SSMA
                                </a>
                            </li>
                            <li>
                                <a href="{{route('g.administracao.documentoslegais.tipodocumento.tipodocumento.index')}}"
                                   subparent="documentos_legais" parent="administracao" key="tipodocumento">
                                    Tipos Documentos
                                </a>
                            </li>
                            <li>
                                <a href="{{route('g.administracao.documentoslegais.tiposervico.tiposervico.index')}}"
                                   subparent="documentos_legais" parent="administracao" key="tiposervico">
                                    Tipos Serviços
                                </a>
                            </li>
                            <li>
                                <a href="{{route('g.administracao.documentoslegais.formacontrato.formacontrato.index')}}"
                                   subparent="documentos_legais" parent="administracao" key="formacontrato">
                                    Formas Contratos
                                </a>
                            </li>
                            @if(\App\Models\Sistema::assinaturaDigitalHabilitada())
                                <li>
                                    <a href="{{route('g.administracao.documento-assinatura.index')}}"
                                       subparent="documentos_legais" parent="administracao" key="documento-assinatura">
                                        Documentos para Assinatura
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endcan
                @can('administracao_carta_oferta_template')
                    <li>
                        <a href="{{route('g.administracao.carta-oferta-template.index')}}" parent="administracao"
                           key="carta-oferta-template">
                            Carta Oferta - Template
                        </a>
                    </li>
                @endcan
                @can('administracao_atareuniao')
                    <li>
                        <a href="{{route('g.administracao.atareuniao.atareuniao.index')}}" parent="administracao"
                           key="atareuniao">
                            Ata Reunião
                        </a>
                    </li>
                @endcan
                @can('administracao_pesquisaclima')
                    <li>
                        <a href="{{route('g.administracao.pesquisaclima.indexAdm')}}" parent="administracao"
                           key="pesquisaclima">
                            Pesquisa de Clima
                        </a>
                    </li>
                @endcan
                @can('administracao_planejamentodiario')
                    <li>
                        <a href="{{route('g.administracao.planejamentodiario.planejamentodiario.index')}}"
                           parent="administracao" key="planejamentodiario">
                            Planejamento Diário
                        </a>
                    </li>
                @endcan
                @can('administracao_aniversariantes')
                    <li>
                        <a href="{{route('g.administracao.aniversariantes.aniversariantes.index')}}"
                           parent="administracao" key="aniversariantes">
                            Aniversariantes
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif

    @if(\App\Models\Sistema::permitirLinks('cadastro_instrutor','cadastro_departamento','cadastro_vagas','cadastro_vagas_abertas','cadastro_treinamento_industria','cadastro_treinamento_sgi','cadastro_empresa_treinamento','cadastro_provas','cadastro_beneficio','cadastro_areaetiqueta','cadastro_centrocusto','cadastro_empresa_temporaria', 'administracao_aprovacao_extra_config'))
        <li id="cadastro">
            <a href="javascript://" class="has-arrow waves-effect" parent="cadastro">
                <i class="bx bx-briefcase-alt-2"></i>
                <span>CADASTRO</span>
            </a>
            <ul aria-expanded="false">
                @can('cadastro_areaetiqueta')
                    <li>
                        <a href="{{route('g.areas.areas.index')}}" parent="cadastro" key="areaetiqueta">
                            Áreas
                        </a>
                    </li>
                @endcan
                @can('cadastro_departamento')
                    <li>
                        <a href="{{route('g.departamento.departamento.index')}}" parent="cadastro" key="departamento">
                            Departamentos
                        </a>
                    </li>
                @endcan
                @can('cadastro_centrocusto')
                    <li>
                        <a href="{{route('g.centrocusto.centrocusto.index')}}" parent="cadastro" key="centrocusto">
                            Centro de Custos
                        </a>
                    </li>
                @endcan
                @can('cadastro_beneficio')
                    <li>
                        <a href="{{route('g.beneficios.beneficios.index')}}" parent="cadastro" key="beneficios">
                            Benefícios
                        </a>
                    </li>
                @endcan
                @can('cadastro_vagas')
                    <li>
                        <a href="{{route('g.vagas.vagas.index')}}" parent="cadastro" key="vagas">
                            Cargos
                        </a>
                    </li>
                @endcan
                @can('cadastro_vagas_abertas')
                    <li>
                        <a href="{{route('g.vagas.vagas-abertas.index')}}" parent="cadastro" key="vagas-abertas">
                            Vagas Abertas
                        </a>
                    </li>
                @endcan
                @can('cadastro_projetos')
                    <li>
                        <a href="{{route('g.projetos.projetos.index')}}" parent="cadastro" key="projetos">
                            Projetos
                        </a>
                    </li>
                @endcan
                @can('cadastro_empresa_exame')
                    <li>
                        <a href="{{route('g.empresaexame.empresa-exame.index')}}" parent="cadastro" key="empresaexame">
                            Exames
                        </a>
                    </li>
                @endcan
                @can('cadastro_instrutor')
                    <li>
                        <a href="{{route('g.instrutor.instrutor.index')}}" parent="cadastro" key="instrutor">
                            Instrutor
                        </a>
                    </li>
                @endcan
                @can('cadastro_treinamento_industria')
                    <li>
                        <a href="{{route('g.treinamentoindustria.treinamentoindustria.index')}}" parent="cadastro"
                           key="treinamentoindustria">
                            Treinamentos Indústria
                        </a>
                    </li>
                    @if(Route::has('g.segmentostreinamento.segmentostreinamento.index') && (int) auth()->user()->empresa_id === \App\Http\Controllers\SegmentoTreinamentoController::EMPRESA_ID_CADASTRO_SEGMENTOS)
                    <li>
                        <a href="{{ route('g.segmentostreinamento.segmentostreinamento.index') }}" parent="cadastro"
                           key="segmentostreinamento">
                            Segmentos de Treinamento
                        </a>
                    </li>
                    @endif
                @endcan
                @can('cadastro_treinamento_sgi')
                    <li>
                        <a href="{{route('g.treinamentosgi.treinamentosgi.index')}}" parent="cadastro"
                           key="treinamentosgi">
                            Treinamentos
                        </a>
                    </li>
                @endcan
                @can('cadastro_empresa_treinamento')
                    <li>
                        <a href="{{route('g.empresatreinamento.empresatreinamento.index')}}" parent="cadastro"
                           key="empresatreinamento">
                            Empresa Treinamento
                        </a>
                    </li>
                @endcan

                @can('cadastro_empresa_temporaria')
                    <li>
                        <a href="{{route('g.empresatemporaria.empresa-temporaria.index')}}" parent="cadastro"
                           key="empresatemporaria">
                            Empresa Temporaria
                        </a>
                    </li>
                @endcan
                @can('cadastro_provas')
                    <li>
                        <a href="{{route('g.provas.provas.index')}}" parent="cadastro" key="provas">
                            Provas
                        </a>
                    </li>
                @endcan
                @can('cadastro_avaliacoes')
                    <li id="avaliacoes">
                        <a href="javascript://" class="has-arrow waves-effect" parent="cadastro">
                            Avaliações</a>
                        <ul aria-expanded="false">
                            @can('cadastro_avaliador_tipo')
                                <li>
                                    <a href="{{route('g.avaliadortipo.avaliadortipo.index')}}" subparent="avaliacoes"
                                       parent="cadastro" key="avaliadortipo">
                                        Tipos de Avaliadores
                                    </a>
                                </li>
                            @endcan
                            @can('cadastro_avaliacao_tipo')
                                <li>
                                    <a href="{{route('g.avaliacaotipo.avaliacaotipo.index')}}" subparent="avaliacoes"
                                       parent="cadastro" key="avaliacaotipo">
                                        Tipos de Avaliações
                                    </a>
                                </li>
                            @endcan
                            @can('cadastro_avaliacao_topico')
                                <li>
                                    <a href="{{route('g.avaliacaotopico.avaliacaotopico.index')}}"
                                       subparent="avaliacoes" parent="cadastro" key="avaliacaotopico">
                                        Competências
                                    </a>
                                </li>
                            @endcan
                            @can('cadastro_avaliacao')
                                <li>
                                    <a href="{{route('g.avaliacao.avaliacao.index')}}" subparent="avaliacoes"
                                       parent="cadastro" key="avaliacao">
                                        Avaliações
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @can('cadastro_avaliacoes')
                    <li id="avaliacoes">
                        <a href="javascript://" class="has-arrow waves-effect" parent="cadastro">
                            Peformance</a>
                        <ul aria-expanded="false">
                            @can('cadastro_avaliador_tipo')
                                <li>
                                    <a href="{{route('g.pj.avaliadortipo.avaliadortipo.index')}}" subparent="avaliacoes"
                                       parent="cadastro" key="avaliadortipo">
                                        Tipos de Avaliadores
                                    </a>
                                </li>
                            @endcan
                            @can('cadastro_avaliacao_tipo')
                                <li>
                                    <a href="{{route('g.pj.avaliacaotipo.avaliacaotipo.index')}}" subparent="avaliacoes"
                                       parent="cadastro" key="avaliacaotipo">
                                        Tipos de Avaliações
                                    </a>
                                </li>
                            @endcan
                            @can('cadastro_avaliacao_topico')
                                <li>
                                    <a href="{{route('g.pj.avaliacaotopico.avaliacaotopico.index')}}"
                                       subparent="avaliacoes" parent="cadastro" key="avaliacaotopico">
                                        Competências
                                    </a>
                                </li>
                            @endcan
                            @can('cadastro_avaliacao')
                                <li>
                                    <a href="{{route('g.pj.avaliacao.avaliacao.index')}}" subparent="avaliacoes"
                                       parent="cadastro" key="avaliacao">
                                        Avaliações
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @if(\App\Models\Sistema::permitirLinks('cadastro_customizacoes_requisicao_vaga','cadastro_customizacoes_aprovacao_extra'))
                <li id="customizacoes">
                        <a href="javascript://" class="has-arrow waves-effect" parent="cadastro">
                            Customizações</a>
                        <ul aria-expanded="false">
                            @can('cadastro_customizacoes_requisicao_vaga')
                            <li>
                                <a href="{{route('g.requisicao_vagas.configurar-campos')}}" parent="customizacoes"
                                key="requisicao_vaga_campos">
                                    Requisição de Vaga
                                </a>
                            </li>
                            @endcan
                            @can('cadastro_customizacoes_aprovacao_extra')
                                <li>
                                    <a href="{{route('g.administracao.aprovacao-extra-config.index')}}"
                                    parent="customizacoes" key="aprovacao-extra-config">
                                        Aprovações Extras
                                    </a>
                                </li>
                            @endcan
                        </ul>
                </li>
                @endif
            </ul>
        </li>
    @endif

    @if(\App\Models\Sistema::permitirLinks('planejamento_requisicao_vaga','planejamento_mobilizacao'))
        <li id="planejamento">
            <a href="javascript://" class="has-arrow waves-effect" parent="planejamento"><i
                    class="bx bx-add-to-queue"></i>
                <span>PLANEJAMENTO</span>
            </a>
            <ul aria-expanded="false">
                @can('planejamento_requisicao_vaga')
                    <li>
                        <a href="{{route('g.requisicao_vagas.requisicao-vaga.index')}}" parent="planejamento"
                           key="requisicao_vaga">
                            Requisição de Vaga
                        </a>
                    </li>
                @endcan
                <li>
                    <a href="{{ route('g.movimentacao.index') }}" parent="planejamento" key="movimentacao">
                        Movimentação
                    </a>
                </li>
                @can('planejamento_mobilizacao')
                    <li>
                        <a href="{{ route('g.mobilizacao.index') }}" parent="planejamento" key="mobilizacao">
                            Mobilização
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif

    @if(\App\Models\Sistema::permitirLinks('curriculos_recrutamento','curriculos_selecionados'))
        <li id="curriculos">
            <a href="javascript://" class="has-arrow waves-effect" parent="curriculos"><i class="bx bx-notepad"></i>
                <span>CURRÍCULOS</span>
            </a>
            <ul aria-expanded="false">
                @can('curriculos_recrutamento')
                    <li>
                        <a href="{{route('g.recrutamento.recrutamentos.index')}}" parent="curriculos"
                           key="recrutamento">
                            Recrutamento
                        </a>
                    </li>
                @endcan
                @can('curriculos_selecionados')
                    <li>
                        <a href="{{route('g.curriculoselecao.curriculos-selecionados.index')}}" parent="curriculos"
                           key="selecionados">
                            Selecionados
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif

    @if(\App\Models\Sistema::permitirLinks('entrevista_parecer_rh', 'entrevista_parecer_rota', 'entrevista_parecer_teste_pratico', 'entrevista_parecer_entrevista', 'entrevista_resultado_integrado','entrevista_rh_cliente','entrevista_gestor_cliente'))
        <li id="entrevistas">
            <a href="javascript://" class="has-arrow waves-effect"><i class="mdi mdi-clipboard-list-outline"
                                                                      parent="entrevistas"></i>
                <span>ENTREVISTAS</span>
            </a>
            <ul aria-expanded="false">
                @can('entrevista_parecer_rh')
                    <li>
                        <a href="{{route('g.entrevista.parecer_rh.parecer_rh.index')}}" parent="entrevistas"
                           key="parecer_rh">
                            Parecer RH
                        </a>
                    </li>
                @endcan

                @can('entrevista_parecer_rota')
                    <li>
                        <a href="{{route('g.entrevista.parecer_rota_transporte.parecer-rota.index')}}"
                           parent="entrevistas" key="parecer_rota">
                            Parecer Rota - Transporte
                        </a>
                    </li>
                @endcan

                @can('entrevista_parecer_entrevista')
                    <li>
                        <a href="{{route('g.entrevista.parecer_entrevista_tecnica.parecer-entrevista-tecnica.index')}}"
                           parent="entrevistas" key="parecer_entrevista">
                            Parecer Entrevista Técnica
                        </a>
                    </li>
                @endcan

                @can('entrevista_parecer_teste_pratico')
                    <li>
                        <a href="{{route('g.entrevista.parecer_teste_pratico.parecer-teste-pratico.index')}}"
                           parent="entrevistas" key="parecer_teste_pratico">
                            Parecer Teste Prático
                        </a>
                    </li>
                @endcan

                @can('entrevista_rh_cliente')
                    <li>
                        <a href="{{route('g.entrevista.entrevista_rh_cliente.entrevista-rh.index')}}"
                           parent="entrevistas" key="entrevista_rh_cliente">
                            Entrevista RH
                        </a>
                    </li>
                @endcan

                @can('entrevista_gestor_cliente')
                    <li>
                        <a href="{{route('g.entrevista.entrevista_gestor_cliente.entrevista-gestor.index')}}"
                           parent="entrevistas" key="entrevista_gestor_cliente">
                            Entrevista Gestor
                        </a>
                    </li>
                @endcan

                @can('entrevista_resultado_integrado')
                    <li>
                        <a href="{{route('g.entrevista.resultado-integrado.resultado-integrado.index')}}"
                           parent="entrevistas" key="resultado_integrado">
                            Resultado Integrado
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif

    @if(\App\Models\Sistema::permitirLinks('controle_ponto_config_empresa','controle_ponto_ocorrencias_jornadas','controle_ponto_escalas','controle_ponto_folha_ponto_manual','controle_ponto_relatorio_sintetico'))
        <li id="ponto">
            <a href="javascript://" class="has-arrow waves-effect" parent="ponto">
                <i class="bx bx-time"></i>
                <span>CONTROLE DE PONTO</span>
            </a>
            <ul aria-expanded="false">
                @can('controle_ponto_config_empresa')
                    <li>
                        <a href="{{route('g.controle-ponto.configuracoes.index')}}" parent="ponto">
                            CONFIGURAÇÕES
                        </a>
                    </li>
                @endcan
                @can('controle_ponto_ocorrencias_jornadas')
                    <li>
                        <a href="{{route('g.controle-ponto.ocorrencias_jornadas.index')}}" parent="ponto">
                            OCORRÊNCIAS
                        </a>
                    </li>
                @endcan
                @can('controle_ponto_feriados')
                    <li>
                        <a href="{{route('g.controle-ponto.feriados.index')}}" parent="ponto">
                            FERIADOS
                        </a>
                    </li>
                @endcan
                @can('controle_ponto_escalas')
                    <li>
                        <a href="{{route('g.controle-ponto.escalas.index')}}" parent="ponto">
                            ESCALAS
                        </a>
                    </li>
                @endcan
                @can('controle_ponto_ponto-eletronico')
                    <li>
                        <a href="{{route('g.controle-ponto.ponto-eletronico.index')}}" parent="ponto">
                            PONTO ELETRÔNICO
                        </a>
                    </li>
                @endcan
                @can('controle_ponto_ajustar-jornadas')
                    <li>
                        <a href="{{route('g.controle-ponto.ajustar-jornadas.index')}}" parent="ponto">
                            AJUSTAR JORNADAS
                        </a>
                    </li>
                @endcan
                @can('controle_ponto_folha-ponto')
                    <li>
                        <a href="{{route('g.controle-ponto.folha-ponto.index')}}" parent="ponto">
                            FOLHA DE PONTO
                        </a>
                    </li>
                @endcan
                @can('controle_ponto_folha_ponto_manual')
                    <li>
                        <a href="{{route('g.controle-ponto.folha-manual.index')}}" parent="ponto">
                            FOLHA DE PONTO MANUAL
                        </a>
                    </li>
                @endcan

                @can('controle_ponto_relatorio_sintetico')
                    <li>
                        <a href="{{route('g.controle-ponto.folha-ponto.relatoriosintetico')}}" parent="ponto">
                            RELATORIO SINTÉTICO
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif

    @if(\App\Models\Sistema::permitirLinks('admissao_pre_admissao', 'admissao_documentos_carta_oferta', 'admissao_cih', 'admissao_processo', 'admissao_importacao', 'admissao_historico', 'admissao_pos_admissao','cadastro_tipos_cih'))
        <li id="admissao">
            <a href="javascript://" class="has-arrow waves-effect" parent="admissao"><i class="bx bx-bookmark-plus"></i>
                <span>ADMISSÃO</span>
            </a>
            <ul aria-expanded="false">
                @if(\App\Models\Sistema::permitirLinks('admissao_pre_admissao', 'admissao_documentos_carta_oferta'))
                    <li id="documentos">
                        <a href="javascript://" class="has-arrow waves-effect" subparent="documentos"
                           parent="admissao">
                            DOCUMENTOS</a>
                        <ul aria-expanded="false">
                            @can('admissao_documentos_carta_oferta')
                                <li>
                                    <a href="{{route('g.admissao.documentos.cartaoferta.index')}}" subparent="admissao"
                                       parent="documentos" key="cartaoferta">
                                        CARTA OFERTA
                                    </a>
                                </li>
                            @endcan
                            @can('admissao_pre_admissao')
                                <li>
                                    <a href="{{route('g.admissao.preadm.index')}}" subparent="admissao"
                                       parent="documentos" key="pre_admissao">
                                        PRÉ-ADMISSÃO
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif
                @can('admissao_controle_exames')
                    <li>
                        <a href="{{route('g.controle_exames.index')}}" parent="admissao" key="controle_exames">
                            CONTROLE DE EXAMES
                        </a>
                    </li>
                @endcan
                @can('admissao_cih')
                    <li id="apontamento">
                        <a href="javascript://" class="has-arrow waves-effect" subparent="apontamento"
                           parent="admissao">
                            APONTAMENTO</a>
                        <ul aria-expanded="false">
                            @can('cadastro_tipos_cih')
                                <li>
                                    <a href="{{route('g.tipocih.tipoCihIndex')}}" subparent="apontamento"
                                       parent="admissao" key="tipocih">
                                        Tipos CIH
                                    </a>
                                </li>
                            @endcan

                            <li>
                                <a href="{{ (route('g.admissao.cih.cih.index')) }}" subparent="apontamento"
                                   parent="admissao">
                                    CIH
                                </a>
                            </li>
                            <li>
                                <a href="{{ (route('g.admissao.intermitente.intermitente.index')) }}"
                                   subparent="apontamento" parent="admissao">
                                    INTERMITENTE
                                </a>
                            </li>

                        </ul>
                    </li>
                @endcan
                @can('admissao_processo')
                    <li>
                        <a href="{{route('g.admissao.admissao.index')}}" parent="admissao">
                            PROCESSO
                        </a>
                    </li>
                @endcan
                @can('admissao_importacao')
                    <li>
                        <a href="{{ route('g.admissao.admissao.import') }}" parent="admissao" key="admissao_importacao">
                            IMPORTAÇÃO
                        </a>
                    </li>
                @endcan
                @can('admissao_historico')
                    <li>
                        <a href="{{route('g.historico.index')}}" parent="admissao">
                            HISTÓRICO
                        </a>
                    </li>
                @endcan
                @can('admissao_pos_admissao')
                    <li>
                        <a href="{{route('g.posadmissao.posadmissao.index', auth()->user()->cliente_id != 1 ? ['cliente_id' => auth()->user()->cliente_id] : null)}}"
                           parent="admissao">
                            PÓS-ADMISSÃO
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif
    @if(\App\Models\Sistema::permitirLinks('avaliacoes_listar'))
        <li>
            <a href="{{ (route('g.avaliar.avaliarIndex')) }}" class="waves-effect">
                <i class="fas fa-tasks"></i>
                <span>MINHAS AVALIAÇÕES</span>
            </a>
        </li>
    @endif
    @if(\App\Models\Sistema::permitirLinks('ocorrencia'))
        <li id="ocorrencias">
            <a href="{{ (route('g.ocorrencia.ocorrencia.index')) }}" class="waves-effect" parent="ocorrencias">
                <i class="bx bx-calendar"></i>
                <span>OCORRÊNCIAS</span>
            </a>
        </li>
    @endif
    @if(\App\Models\Sistema::permitirLinks('weekly_report'))
        <li>
            <a href="{{ (route('g.weekly-report.index')) }}" class="waves-effect" parent="ocorrencias">
                <i class="fas fa-tasks"></i>
                <span>WEEKLY REPORT</span>
            </a>
        </li>
    @endif
    @if(\App\Models\Sistema::permitirLinks('treinamento_portaria','treinamento_carteira-etiquetas','treinamento_certificado'))
        <li id="treinamento">
            <a href="javascript://" class="has-arrow waves-effect" parent="treinamento">
                <i class="bx bx-aperture"></i>
                <span>TREINAMENTO</span>
            </a>
            <ul aria-expanded="false">
                @can('treinamento_portaria')
                    <li>
                        <a href="{{route('g.portaria.index' )}}" parent="treinamento" key="portaria">
                            Portaria
                        </a>
                    </li>
                @endcan
                @can('treinamento_carteira-etiquetas')
                    <li>
                        <a href="{{route('g.treinamentos.treinamento.index' )}}" parent="treinamento"
                           key="carteira_etiquetas">
                            Carteira/Etiquetas
                        </a>
                    </li>
                @endcan
                @can('treinamento_certificado')
                    <li>
                        <a href="{{route('g.certificados.certificado.index' )}}" parent="treinamento"
                           key="emissao_certificados">
                            Emissão Certificados (NR33/NR35)
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif
    @if(\App\Models\Sistema::permitirLinks('financeiro_fluxo-caixa','financeiro_classificacao-plano-conta','financeiro_formas-pagamento'))
        <li id="financeiro">
            <a href="javascript://" class="has-arrow waves-effect" parent="financeiro">
                <i class="bx bx-dollar-circle"></i>
                <span>FINANCEIRO</span>
            </a>
            <ul aria-expanded="false">
                @can('financeiro_classificacao-plano-conta')
                    <li>
                        <a href="{{ (route('g.financeiro.classificacao-plano-conta.index')) }}" parent="financeiro"
                           class="waves-effect">
                            <span>Classificação</span>
                        </a>
                    </li>
                @endcan
                @can('financeiro_plano-conta')
                    <li>
                        <a href="{{ (route('g.financeiro.plano-conta.index')) }}" parent="financeiro"
                           class="waves-effect">
                            <span>Planos de conta</span>
                        </a>
                    </li>
                @endcan
                @can('financeiro_formas-pagamento')
                    <li>
                        <a href="{{ (route('g.financeiro.formas-pagamento.index')) }}" parent="financeiro"
                           class="waves-effect">
                            <span>Formas de pagamento</span>
                        </a>
                    </li>
                @endcan
                @can('financeiro_fluxo-caixa')
                    <li>
                        <a href="{{ (route('g.financeiro.fluxo-caixa.index')) }}" parent="financeiro"
                           class="waves-effect">
                            <span>Fluxo de caixa</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>

    @endif


    @if(\App\Models\Sistema::permitirLinks('cloud','cloud_configuracoes'))
        <li id="cloud">
            <a href="javascript://" class="has-arrow waves-effect" parent="cloud">
                <i class="bx bx-cloud-upload"></i>
                <span>CLOUD</span>
            </a>
            <ul aria-expanded="false">
                {{-- auth()->user()->GrupoCloud?->nome == 'Administradores'--}}
                @foreach(auth()->user()->CloudsAtivo as $cloud)
                    <li>
                        <a href="{{route('g.cloud.cloud.single', [$cloud->id, $cloud->nome])}}" parent="cloud"
                           key="{{$cloud->nome}}">
                            <i class="fas fa-folder"
                               style="color: #EECD6D; font-size: 0.85rem;"></i> {{$cloud->nome}}
                        </a>
                    </li>
                @endforeach

                {{-- @can('cloud_configuracoes')--}}
                <li>
                    <a href="{{ route('g.cloud.cadastro.indexCadastro') }}" parent="cloud" key="cloud_cadastro">
                        Cadastro
                    </a>
                </li>
                {{-- @endcan--}}

                @can('cloud_configuracoes')
                    <li>
                        <a href="{{ route('g.cloud.configuracoes.configuracoes.index') }}" parent="cloud"
                           key="cloud_configuracoes">
                            Configurações
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif

    @if(\App\Models\Sistema::permitirLinks('site_galeria_site','site_cartela_cliente_site','site_depoimento_site'))
        <li id="site">
            <a href="javascript://" class="has-arrow waves-effect" parent="site">
                <i class="bx bx-sitemap"></i>
                <span>SITE</span>
            </a>
            <ul aria-expanded="false">
                @can('site_galeria_site')
                    <li>
                        <a href="{{route('g.site.galeria.index')}}" parent="site" key="galeria_site">
                            Galeria de Fotos
                        </a>
                    </li>
                @endcan
                @can('site_cartela_cliente_site')
                    <li>
                        <a href="{{route('g.site.cliente.cliente-logo.index')}}" parent="site"
                           key="cartela_cliente_site">
                            Cartela Cliente
                        </a>
                    </li>
                @endcan
                @can('site_depoimento_site')
                    <li>
                        <a href="{{route('g.site.testemunhal.testemunhal.index')}}" parent="site" key="depoimento_site">
                            Depoimento
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif

    @if(\App\Models\Sistema::permitirLinks('relatorio_relatorios', 'relatorio_controleusuarios', 'relatorio_asos', 'relatorio_medidas_administrativas','relatorio_ferias','relatorio_centro_de_custo','relatorio_efetivo','relatorio_aniversariantes'))
        <li id="relatorios">
            <a href="javascript://" class="has-arrow waves-effect" parent="relatorios">
                <i class="bx bx-chart"></i>
                <span>RELATÓRIOS</span>
            </a>
            <ul aria-expanded="false">
                @can('relatorio_controleusuarios')
                    <li>
                        <a href="{{route('g.relatorios.controleusuarios.index')}}" parent="relatorios"
                           key="controleusuarios">
                            Controle de Usuários
                        </a>
                    </li>
                @endcan
            </ul>

            <ul aria-expanded="false">
                @can('relatorio_asos')
                    <li>
                        <a href="{{route('g.relatorios.vencimentoasos.index')}}" parent="relatorios"
                           key="vencimentoasos">
                            Vencimento de Asos
                        </a>
                    </li>
                @endcan
            </ul>

            <ul aria-expanded="false">
                @can('relatorio_treinamento')
                    <li>
                        <a href="{{route('g.relatorios.vencimentotreinamento.index')}}" parent="relatorios"
                           key="relatorio_treinamento">
                            Treinamentos
                        </a>
                    </li>
                @endcan
            </ul>

            <ul aria-expanded="false">
                @can('relatorio_ferias')
                    <li>
                        <a href="{{route('g.relatorios.ferias.index')}}" parent="relatorios"
                           key="relatorio_ferias">
                            Férias
                        </a>
                    </li>
                @endcan
            </ul>

            <ul aria-expanded="false">
                @can('relatorio_ferias')
                    <li>
                        <a href="{{route('g.relatorios.vencimentoferias.indexVencimentoFerias')}}" parent="relatorios"
                           key="relatorio_ferias">
                            Vencimento Férias
                        </a>
                    </li>
                @endcan
            </ul>

            <ul aria-expanded="false">
                @can('relatorio_medidas_administrativas')
                    <li>
                        <a href="{{route('g.relatorios.medidasadministrativas.index')}}" parent="relatorios"
                           key="medidasadministrativas">
                            Medidas Administrativas
                        </a>
                    </li>
                @endcan

            </ul>
            <ul aria-expanded="false">
                @can('relatorio_centro_de_custo')
                    <li>
                        <a href="{{route('g.relatorios.centrodecusto.index')}}" parent="relatorios" key="centrodecusto">
                            Centro de Custo
                        </a>
                    </li>
                @endcan
            </ul>

            <ul aria-expanded="false">
                @can('relatorio_efetivo')
                    <li>
                        <a href="{{route('g.relatorios.efetivo.index')}}" parent="relatorios" key="efetivo">
                            Efetivo
                        </a>
                    </li>
                @endcan
            </ul>
            <ul aria-expanded="false">
                @can('relatorio_avaliacao_90_dias')
                    <li>
                        <a href="{{ route('g.relatorios.avaliacaoExperiencia.index') }}" parent="relatorios" key="avaliacao_experiencia">
                        Avaliação de Experiência
                        </a>
                    </li>
                 @endcan
            </ul>
            <ul aria-expanded="false">
                @can('relatorio_aniversariantes')
                    <li>
                        <a href="{{route('g.relatorios.aniversariantes.relatorioNivers')}}" parent="relatorios"
                           key="aniversariantes">
                            Aniversariantes
                        </a>
                    </li>
                @endcan
            </ul>
            @if(auth()->user()->empresa_id === \App\Models\User::MYBP_EMPRESA_ID)
            <ul aria-expanded="false">
                <li>
                    <a href="{{ route('g.relatorios.nps.index') }}" parent="relatorios" key="relatorio_nps">
                        NPS (Resultados)
                    </a>
                </li>
            </ul>
            @endif
        </li>
    @endif

    @if(\App\Models\Sistema::permitirLinks('usuario_usuarios'))
        <li id="users">
            <a href="javascript://" class="has-arrow waves-effect" parent="users">
                <i class="bx bx-user-circle"></i>
                <span>USUÁRIOS</span>
            </a>
            <ul aria-expanded="false">
                @can('usuario_usuarios')
                    <li>
                        <a href="{{route('g.usuarios.usuarios.index')}}" parent="users" key="usuarios">
                            Usuários do Sistema
                        </a>
                    </li>
                @endcan

            </ul>
        </li>
    @endif

    @if(\App\Models\Sistema::permitirLinks('configuracao_habilidades','configuracao_papel'))
        <li id="config">
            <a href="javascript://" class="has-arrow waves-effect" parent="config">
                <i class="fa fa-cogs" style="font-size: 16px;"></i>
                <span>CONFIGURAÇÕES</span>
            </a>
            <ul aria-expanded="false">
                @can('configuracao_habilidades')
                    <li>
                        <a href="{{route('g.configuracoes.habilidades.index')}}" parent="config" key="habilidades">
                            Módulos do sistema
                        </a>
                    </li>
                @endcan
                @can('configuracao_papel')
                    <li>
                        <a href="{{route('g.configuracoes.papeis.index')}}" parent="config" key="grupo-usuarios">
                            Grupos de Usuários
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif

    {{--Menu CLinica--}}
    @if(\App\Models\Sistema::permitirLinks('acesso_clinica') && auth()->user()->tipo == 'ClinicaExame')
        <li id="exames">
            <a href="javascript://" class="has-arrow waves-effect" parent="exames">
                <i class="fas fa-notes-medical" style="font-size: 16px;"></i>
                <span>Controle de exames</span>
            </a>
            <ul aria-expanded="false">
                @can('acesso_clinica')
                    <li>
                        <a href="{{route('g.acesso-clinica.acesso-clinica.index')}}" parent="exames"
                           key="acesso-clinica-colaboradores">
                            Colaboradores
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif
</div>

@push('js')
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            // Verifica se o menu 'cloud' está ativo
            let cloudMenu = document.getElementById("cloud");
            if (cloudMenu && cloudMenu.classList.contains("mm-active")) {
                // Seleciona apenas os itens ativos dentro do menu
                let activeItems = cloudMenu.querySelectorAll("li.mm-active i.fas.fa-folder");

                // Troca a classe de cada ícone dentro dos itens ativos
                activeItems.forEach(icon => {
                    icon.classList.remove("fa-folder");
                    icon.classList.add("fa-folder-open");
                });
            }
        });

        $.expr[":"].contains = $.expr.createPseudo(function(arg) {
            return function(elem) {
                return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
            };
        });

        $("#filter-menu").keyup(function() {
            let stringPesquisa = $("#filter-menu").val();
            if (stringPesquisa.length == 0) {
                $('a').css("color", "");
            } else {
                $('a').css("color", "");
                $('a:contains(' + stringPesquisa + ')').each(function(index) {
                    let id_parent = $(this).attr('parent');
                    let id_sub_parent = $(this).attr('subparent');
                    let a_parent = $("#" + id_parent + "").children().first();
                    let a_sub_parent = $("#" + id_sub_parent + "").children().first();

                    if ($("#" + id_parent + "").children().is('a')) {
                        a_parent.css("color", "#FFFF00");
                    }
                    if ($("#" + id_sub_parent + "").children().is('a')) {
                        a_sub_parent.css("color", "#FFFF00");
                    }

                    $(this).css("color", "#FFFF00");
                });
            }
        });
    </script>
@endpush
