<li class="menu-title d-block d-sm-none">
    <img src="{{ asset('images/logo_horizontal.svg') }}" alt="SGIBPSE logo horizontal" class=""
         height="65" style=" margin-top: 5px;">
</li>
<li class="menu-title">Menu</li>
{{--@if(\App\Models\Sistema::permitirLinks('administracao_clientes'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-user-circle"></i>
            <span>ADMINISTRAÇÃO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
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

@if(\App\Models\Sistema::permitirLinks('administracao_clientes','administracao_fornecedores','administracao_atareuniao','administracao_pesquisaclima','administracao_planejamentodiario','administracao_aniversariantes'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bxs-book-content"></i>
            <span>ADMINISTRAÇÃO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('administracao_clientes')
                <li>
                    <a href="{{route('g.administracao.clientes.clientes.index')}}" key="clientes">
                        Clientes
                    </a>
                </li>
            @endcan
            @can('administracao_fornecedores')
                <li>
                    <a href="{{route('g.administracao.fornecedor.fornecedor.index')}}" key="fornecedores">
                        Fornecedores
                    </a>
                </li>
            @endcan

            @can('administracao_atareuniao')
                <li>
                    <a href="{{route('g.administracao.atareuniao.atareuniao.index')}}" key="atareuniao">
                        Ata Reunião
                    </a>
                </li>
            @endcan
            @can('administracao_pesquisaclima')
                <li>
                    <a href="{{route('g.administracao.pesquisaclima.indexAdm')}}" key="pesquisaclima">
                        Pesquisa de Clima
                    </a>
                </li>
            @endcan
            @can('administracao_planejamentodiario')
                <li>
                    <a href="{{route('g.administracao.planejamentodiario.planejamentodiario.index')}}"
                       key="planejamentodiario">
                        Planejamento Diário
                    </a>
                </li>
            @endcan
            @can('administracao_aniversariantes')
                <li>
                    <a href="{{route('g.administracao.aniversariantes.aniversariantes.index')}}"
                       key="aniversariantes">
                        Aniversariantes
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('cadastro_instrutor','cadastro_departamento','cadastro_vagas','cadastro_vagas_abertas','cadastro_treinamento_industria','cadastro_treinamento_sgi','cadastro_empresa_treinamento','cadastro_provas','cadastro_beneficio','cadastro_areaetiqueta','cadastro_centrocusto','cadastro_empresa_temporaria'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-briefcase-alt-2"></i>
            <span>CADASTRO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('cadastro_instrutor')
                <li>
                    <a href="{{route('g.instrutor.instrutor.index')}}" key="instrutor">
                        Instrutor
                    </a>
                </li>
            @endcan
            @can('cadastro_departamento')
                <li>
                    <a href="{{route('g.departamento.departamento.index')}}" key="departamento">
                        Departamentos
                    </a>
                </li>
            @endcan
            @can('cadastro_projetos')
                <li>
                    <a href="{{route('g.projetos.projetos.index')}}" key="projetos">
                        Projetos
                    </a>
                </li>
            @endcan
            @can('cadastro_vagas')
                <li>
                    <a href="{{route('g.vagas.vagas.index')}}" key="vagas">
                        Cargos
                    </a>
                </li>
            @endcan
            @can('cadastro_vagas_abertas')
                <li>
                    <a href="{{route('g.vagas.vagas-abertas.index')}}" key="vagas-abertas">
                        Vagas Abertas
                    </a>
                </li>
            @endcan
            @can('cadastro_cadastro_treinamento_industria')
                <li>
                    <a href="{{route('g.treinamentoindustria.treinamentoindustria.index')}}" key="treinamentoindustria">
                        Treinamentos Indústria
                    </a>
                </li>
            @endcan
            @can('cadastro_treinamento_sgi')
                <li>
                    <a href="{{route('g.treinamentosgi.treinamentosgi.index')}}" key="treinamentosgi">
                        Treinamentos
                    </a>
                </li>
            @endcan
            @can('cadastro_empresa_treinamento')
                <li>
                    <a href="{{route('g.empresatreinamento.empresatreinamento.index')}}" key="empresatreinamento">
                        Empresa Treinamento
                    </a>
                </li>
            @endcan
            @can('cadastro_empresa_exame')
                <li>
                    <a href="{{route('g.empresaexame.empresa-exame.index')}}" key="empresaexame">
                        Empresa Exames
                    </a>
                </li>
            @endcan
            @can('cadastro_empresa_temporaria')
                <li>
                    <a href="{{route('g.empresatemporaria.empresa-temporaria.index')}}" key="empresatemporaria">
                        Empresa Temporaria
                    </a>
                </li>
            @endcan
            @can('cadastro_provas')
                <li>
                    <a href="{{route('g.provas.provas.index')}}" key="provas">
                        Provas
                    </a>
                </li>
            @endcan
            @can('cadastro_beneficio')
                <li>
                    <a href="{{route('g.beneficios.beneficios.index')}}" key="beneficios">
                        Benefícios
                    </a>
                </li>
            @endcan
            @can('cadastro_areaetiqueta')
                <li>
                    <a href="{{route('g.areas.areas.index')}}" key="areaetiqueta">
                        Áreas
                    </a>
                </li>
            @endcan
            @can('cadastro_centrocusto')
                <li>
                    <a href="{{route('g.centrocusto.centrocusto.index')}}" key="centrocusto">
                        Centro de Custos
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('planejamento_requisicao_vaga','planejamento_mobilizacao'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect"><i class="bx bx-add-to-queue"></i>
            <span>PLANEJAMENTO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('planejamento_requisicao_vaga')
                <li>
                    <a href="{{route('g.requisicao_vagas.requisicao-vaga.index')}}" key="requisicao_vaga">
                        Requisição de Vaga
                    </a>
                </li>
            @endcan
            <li>
                <a href="{{ route('g.movimentacao.index') }}" key="movimentacao">
                    Movimentação
                </a>
            </li>
            @can('planejamento_mobilizacao')
                <li>
                    <a href="{{ route('g.mobilizacao.index') }}" key="mobilizacao">
                        Mobilização
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('curriculos_recrutamento','curriculos_selecionados'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect"><i class="bx bx-notepad"></i>
            <span>CURRÍCULOS</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('curriculos_recrutamento')
                <li>
                    <a href="{{route('g.recrutamento.recrutamentos.index')}}" key="recrutamento">
                        Recrutamento
                    </a>
                </li>
            @endcan
            @can('curriculos_selecionados')
                <li>
                    <a href="{{route('g.curriculoselecao.curriculos-selecionados.index')}}" key="selecionados">
                        Selecionados
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('entrevista_parecer_rh', 'entrevista_parecer_rota', 'entrevista_parecer_teste_pratico', 'entrevista_parecer_entrevista', 'entrevista_resultado_integrado','entrevista_rh_cliente','entrevista_gestor_cliente'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect"><i class="mdi mdi-clipboard-list-outline"></i>
            <span>ENTREVISTAS</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('entrevista_parecer_rh')
                <li>
                    <a href="{{route('g.entrevista.parecer_rh.parecer_rh.index')}}" key="parecer_rh">
                        Parecer RH
                    </a>
                </li>
            @endcan

            @can('entrevista_parecer_rota')
                <li>
                    <a href="{{route('g.entrevista.parecer_rota_transporte.parecer-rota.index')}}" key="parecer_rota">
                        Parecer Rota - Transporte
                    </a>
                </li>
            @endcan

            @can('entrevista_parecer_entrevista')
                <li>
                    <a href="{{route('g.entrevista.parecer_entrevista_tecnica.parecer-entrevista-tecnica.index')}}"
                       key="parecer_entrevista">
                        Parecer Entrevista Técnica
                    </a>
                </li>
            @endcan

            @can('entrevista_parecer_teste_pratico')
                <li>
                    <a href="{{route('g.entrevista.parecer_teste_pratico.parecer-teste-pratico.index')}}"
                       key="parecer_teste_pratico">
                        Parecer Teste Prático
                    </a>
                </li>
            @endcan

            @can('entrevista_rh_cliente')
                <li>
                    <a href="{{route('g.entrevista.entrevista_rh_cliente.entrevista-rh.index')}}"
                       key="entrevista_rh_cliente">
                        Entrevista RH
                    </a>
                </li>
            @endcan

            @can('entrevista_gestor_cliente')
                <li>
                    <a href="{{route('g.entrevista.entrevista_gestor_cliente.entrevista-gestor.index')}}"
                       key="entrevista_gestor_cliente">
                        Entrevista Gestor
                    </a>
                </li>
            @endcan

            @can('entrevista_resultado_integrado')
                <li>
                    <a href="{{route('g.entrevista.resultado-integrado.resultado-integrado.index')}}"
                       key="resultado_integrado">
                        Resultado Integrado
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('controle_ponto_config_empresa','controle_ponto_ocorrencias_jornadas','controle_ponto_escalas'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-time"></i>
            <span>CONTROLE DE PONTO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('controle_ponto_config_empresa')
                <li>
                    <a href="{{route('g.controle-ponto.configuracoes.index')}}">
                        CONFIGURAÇÕES
                    </a>
                </li>
            @endcan
            @can('controle_ponto_ocorrencias_jornadas')
                <li>
                    <a href="{{route('g.controle-ponto.ocorrencias_jornadas.index')}}">
                        OCORRÊNCIAS
                    </a>
                </li>
            @endcan
            @can('controle_ponto_feriados')
                <li>
                    <a href="{{route('g.controle-ponto.feriados.index')}}">
                        FERIADOS
                    </a>
                </li>
            @endcan
            @can('controle_ponto_escalas')
                <li>
                    <a href="{{route('g.controle-ponto.escalas.index')}}">
                        ESCALAS
                    </a>
                </li>
            @endcan
            @can('controle_ponto_ponto-eletronico')
                <li>
                    <a href="{{route('g.controle-ponto.ponto-eletronico.index')}}">
                        PONTO ELETRÔNICO
                    </a>
                </li>
            @endcan
            @can('controle_ponto_ajustar-jornadas')
                <li>
                    <a href="{{route('g.controle-ponto.ajustar-jornadas.index')}}">
                        AJUSTAR JORNADAS
                    </a>
                </li>
            @endcan
            @can('controle_ponto_folha-ponto')
                <li>
                    <a href="{{route('g.controle-ponto.folha-ponto.index')}}">
                        FOLHA DE PONTO
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('admissao_pre_admissao', 'admissao_cih', 'admissao_processo', 'admissao_historico', 'admissao_pos_admissao'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect"><i class="bx bx-bookmark-plus"></i>
            <span>ADMISSÃO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('admissao_pre_admissao')
                <li>
                    <a href="{{route('g.admissao.preadm.index')}}" key="pre_admissao">
                        PRÉ-ADMISSÃO
                    </a>
                </li>
            @endcan
            @can('admissao_controle_exames')
                <li>
                    <a href="{{route('g.controle_exames.index')}}" key="controle_exames">
                        CONTROLE DE EXAMES
                    </a>
                </li>
            @endcan
            @can('admissao_cih')
                <li>
                    <a href="javascript://" class="has-arrow waves-effect">
                        APONTAMENTO</a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li>
                            <a href="{{ (route('g.admissao.cih.cih.index')) }}">
                                CIH
                            </a>
                        </li>
                        <li>
                            <a href="{{ (route('g.admissao.intermitente.intermitente.index')) }}">
                                INTERMITENTE
                            </a>
                        </li>

                    </ul>
                </li>
            @endcan
            @can('admissao_processo')
                <li>
                    <a href="{{route('g.admissao.admissao.index')}}">
                        PROCESSO
                    </a>
                </li>
            @endcan
            @can('admissao_historico')
                <li>
                    <a href="{{route('g.historico.index')}}">
                        HISTÓRICO
                    </a>
                </li>
            @endcan
            @can('admissao_pos_admissao')
                <li>
                    <a href="{{route('g.posadmissao.posadmissao.index', auth()->user()->cliente_id != 1 ? ['cliente_id' => auth()->user()->cliente_id] : null)}}">
                        PÓS-ADMISSÃO
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif


@if(\App\Models\Sistema::permitirLinks('ocorrencia'))
    <li>
        <a href="{{ (route('g.ocorrencia.ocorrencia.index')) }}" class="waves-effect">
            <i class="bx bx-calendar"></i>
            <span>OCORRÊNCIAS</span>
        </a>
    </li>
@endif
@if(\App\Models\Sistema::permitirLinks('weekly_report'))
    <li>
        <a href="{{ (route('g.weekly-report.index')) }}" class="waves-effect">
            <i class="fas fa-tasks"></i>
            <span>WEEKLY REPORT</span>
        </a>
    </li>
@endif
@if(\App\Models\Sistema::permitirLinks('treinamento_portaria','treinamento_carteira-etiquetas','treinamento_certificado'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-aperture"></i>
            <span>TREINAMENTO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('treinamento_portaria')
                <li>
                    <a href="{{route('g.portaria.index' )}}" key="portaria">
                        Portaria
                    </a>
                </li>
            @endcan
            @can('treinamento_carteira-etiquetas')
                <li>
                    <a href="{{route('g.treinamentos.treinamento.index' )}}" key="carteira_etiquetas">
                        Carteira/Etiquetas
                    </a>
                </li>
            @endcan
            @can('treinamento_certificado')
                <li>
                    <a href="{{route('g.certificados.certificado.index' )}}" key="emissao_certificados">
                        Emissão Certificados (NR33/NR35)
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif
@if(\App\Models\Sistema::permitirLinks('financeiro_fluxo-caixa','financeiro_classificacao-plano-conta','financeiro_formas-pagamento'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-dollar-circle"></i>
            <span>FINANCEIRO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('financeiro_classificacao-plano-conta')
                <li>
                    <a href="{{ (route('g.financeiro.classificacao-plano-conta.index')) }}" class="waves-effect">
                        <span>Classificação</span>
                    </a>
                </li>
            @endcan
            @can('financeiro_plano-conta')
                <li>
                    <a href="{{ (route('g.financeiro.plano-conta.index')) }}" class="waves-effect">
                        <span>Planos de conta</span>
                    </a>
                </li>
            @endcan
            @can('financeiro_formas-pagamento')
                <li>
                    <a href="{{ (route('g.financeiro.formas-pagamento.index')) }}" class="waves-effect">
                        <span>Formas de pagamento</span>
                    </a>
                </li>
            @endcan
            @can('financeiro_fluxo-caixa')
                <li>
                    <a href="{{ (route('g.financeiro.fluxo-caixa.index')) }}" class="waves-effect">
                        <span>Fluxo de caixa</span>
                    </a>
                </li>
            @endcan
        </ul>
    </li>

@endif


@if(\App\Models\Sistema::permitirLinks('cloud','cloud_configuracoes'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-cloud-upload"></i>
            <span>CLOUD</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">


            @foreach(auth()->user()->CloudsAtivo as $cloud)
                <li>
                    <a href="{{route('g.cloud.cloud.single', [$cloud->id, $cloud->nome])}}" key="{{$cloud->nome}}">
                        {{$cloud->nome}}
                    </a>
                </li>
            @endforeach

            {{--            @can('cloud_configuracoes')--}}
            <li>
                <a href="{{ route('g.cloud.cadastro.indexCadastro') }}" key="cloud_cadastro">
                    Cadastro
                </a>
            </li>
            {{--            @endcan--}}

            @can('cloud_configuracoes')
                <li>
                    <a href="{{ route('g.cloud.configuracoes.configuracoes.index') }}" key="cloud_configuracoes">
                        Configurações
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('site_galeria_site','site_cartela_cliente_site','site_depoimento_site'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-sitemap"></i>
            <span>SITE</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('site_galeria_site')
                <li>
                    <a href="{{route('g.site.galeria.index')}}" key="galeria_site">
                        Galeria de Fotos
                    </a>
                </li>
            @endcan
            @can('site_cartela_cliente_site')
                <li>
                    <a href="{{route('g.site.cliente.cliente-logo.index')}}" key="cartela_cliente_site">
                        Cartela Cliente
                    </a>
                </li>
            @endcan
            @can('site_depoimento_site')
                <li>
                    <a href="{{route('g.site.testemunhal.testemunhal.index')}}" key="depoimento_site">
                        Depoimento
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('relatorio_relatorios', 'relatorio_controleusuarios', 'relatorio_asos', 'relatorio_medidas_administrativas'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-chart"></i>
            <span>RELATÓRIOS</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('relatorio_controleusuarios')
                <li>
                    <a href="{{route('g.relatorios.controleusuarios.index')}}" key="controleusuarios">
                        Controle de Usuários
                    </a>
                </li>
            @endcan
        </ul>

        <ul class="sub-menu" aria-expanded="false">
            @can('relatorio_asos')
                <li>
                    <a href="{{route('g.relatorios.vencimentoasos.index')}}" key="vencimentoasos">
                        Vencimento de Asos
                    </a>
                </li>
            @endcan
        </ul>

        <ul class="sub-menu" aria-expanded="false">
            @can('relatorio_treinamento')
                <li>
                    <a href="{{route('g.relatorios.vencimentotreinamento.index')}}" key="relatorio_treinamento">
                        Treinamentos
                    </a>
                </li>
            @endcan
        </ul>

        <ul class="sub-menu" aria-expanded="false">
            @can('relatorio_medidas_administrativas')
                <li>
                    <a href="{{route('g.relatorios.medidasadministrativas.index')}}" key="medidasadministrativas">
                        Medidas Administrativas
                    </a>
                </li>
            @endcan

        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('usuario_usuarios'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-user-circle"></i>
            <span>USUÁRIOS</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('usuario_usuarios')
                <li>
                    <a href="{{route('g.usuarios.usuarios.index')}}" key="usuarios">
                        Usuários do Sistema
                    </a>
                </li>
            @endcan

        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('configuracao_habilidades','configuracao_papel'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="fa fa-cogs" style="font-size: 16px;"></i>
            <span>CONFIGURAÇÕES</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('configuracao_habilidades')
                <li>
                    <a href="{{route('g.configuracoes.habilidades.index')}}" key="habilidades">
                        Módulos do sistema
                    </a>
                </li>
            @endcan
            @can('configuracao_papel')
                <li>
                    <a href="{{route('g.configuracoes.papeis.index')}}" key="grupo-usuarios">
                        Grupos de Usuários
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

{{--Menu CLinica--}}
@if(\App\Models\Sistema::permitirLinks('controle_exame_acesso_clinica'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="fas fa-notes-medical" style="font-size: 16px;"></i>
            <span>Controle de exames</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('controle_exame_acesso_clinica')
                <li>
                    <a href="{{route('g.acesso-clinica.acesso-clinica.index')}}" key="acesso-clinica-colaboradores">
                        Colaboradores
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif
