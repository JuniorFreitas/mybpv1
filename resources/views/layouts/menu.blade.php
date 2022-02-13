<li class="menu-title d-block d-sm-none">
    <img src="{{ asset('images/logo_horizontal.svg') }}" alt="SGIBPSE logo horizontal" class=""
         height="65" style=" margin-top: 5px;">
</li>
<li class="menu-title">Menu</li>
{{--@if(\App\Models\Sistema::permitirLinks('clientes'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-user-circle"></i>
            <span>ADMINISTRAÇÃO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('clientes')
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

@if(\App\Models\Sistema::permitirLinks('clientes','fornecedores','atareuniao','pesquisaclima','planejamentodiario','aniversariantes'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bxs-book-content"></i>
            <span>ADMINISTRAÇÃO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('clientes')
                <li>
                    <a href="{{route('g.administracao.clientes.clientes.index')}}" key="clientes">
                        Clientes
                    </a>
                </li>
            @endcan
            @can('fornecedores')
                <li>
                    <a href="{{route('g.administracao.fornecedor.fornecedor.index')}}" key="fornecedores">
                        Fornecedores
                    </a>
                </li>
            @endcan

            @can('atareuniao')
                <li>
                    <a href="{{route('g.administracao.atareuniao.atareuniao.index')}}" key="atareuniao">
                        Ata Reunião
                    </a>
                </li>
            @endcan
            @can('pesquisaclima')
                <li>
                    <a href="{{route('g.administracao.pesquisaclima.indexAdm')}}" key="pesquisaclima">
                        Pesquisa de Clima
                    </a>
                </li>
            @endcan
            @can('planejamentodiario')
                <li>
                    <a href="{{route('g.administracao.planejamentodiario.planejamentodiario.index')}}"
                       key="planejamentodiario">
                        Planejamento Diário
                    </a>
                </li>
            @endcan
            @can('aniversariantes')
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

@if(\App\Models\Sistema::permitirLinks('cadastro_instrutor','cadastro_departamento','vagas','vagas_abertas','cadastro_treinamento_industria','cadastro_treinamento_sgi','cadastro_empresa_treinamento','cadastro_provas','beneficio','areaetiqueta','centrocusto'))
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
            @can('vagas')
                <li>
                    <a href="{{route('g.vagas.vagas.index')}}" key="vagas">
                        Cargos
                    </a>
                </li>
            @endcan
            @can('vagas_abertas')
                <li>
                    <a href="{{route('g.vagas.vagas-abertas.index')}}" key="vagas-abertas">
                        Vagas Abertas
                    </a>
                </li>
            @endcan
            @can('cadastro_treinamento_industria')
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
            @can('cadastro_provas')
                <li>
                    <a href="{{route('g.provas.provas.index')}}" key="provas">
                        Provas
                    </a>
                </li>
            @endcan
            @can('beneficio')
                <li>
                    <a href="{{route('g.beneficios.beneficios.index')}}" key="beneficios">
                        Benefícios
                    </a>
                </li>
            @endcan
            @can('areaetiqueta')
                <li>
                    <a href="{{route('g.areas.areas.index')}}" key="areaetiqueta">
                        Áreas
                    </a>
                </li>
            @endcan
            @can('centrocusto')
                <li>
                    <a href="{{route('g.centrocusto.centrocusto.index')}}" key="centrocusto">
                        Centro de Custos
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('requisicao_vaga'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect"><i class="bx bx-add-to-queue"></i>
            <span>PLANEJAMENTO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('requisicao_vaga')
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
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('curriculos'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect"><i class="bx bx-notepad"></i>
            <span>CURRÍCULOS</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('curriculos')
                <li>
                    <a href="{{route('g.recrutamento.recrutamentos.index')}}" key="recrutamento">
                        Recrutamento
                    </a>
                </li>
            @endcan
            @can('curriculos')
                <li>
                    <a href="{{route('g.curriculoselecao.curriculos-selecionados.index')}}" key="selecionados">
                        Selecionados
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('parecer_rh', 'parecer_rota', 'parecer_teste_pratico', 'parecer_entrevista', 'resultado_integrado','entrevista_rh_cliente','entrevista_gestor_cliente'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect"><i class="mdi mdi-clipboard-list-outline"></i>
            <span>ENTREVISTAS</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('parecer_rh')
                <li>
                    <a href="{{route('g.entrevista.parecer_rh.parecer_rh.index')}}" key="parecer_rh">
                        Parecer RH
                    </a>
                </li>
            @endcan

            @can('parecer_rota')
                <li>
                    <a href="{{route('g.entrevista.parecer_rota_transporte.parecer-rota.index')}}" key="parecer_rota">
                        Parecer Rota - Transporte
                    </a>
                </li>
            @endcan

            @can('parecer_entrevista')
                <li>
                    <a href="{{route('g.entrevista.parecer_entrevista_tecnica.parecer-entrevista-tecnica.index')}}"
                       key="parecer_entrevista">
                        Parecer Entrevista Técnica
                    </a>
                </li>
            @endcan

            @can('parecer_teste_pratico')
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

            @can('resultado_integrado')
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

@if(\App\Models\Sistema::permitirLinks('config_empresa','ocorrencias_jornadas','escalas'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect"><i class="fas fa-business-time"></i>
            <span>CONTROLE DE PONTO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('config_empresa')
                <li>
                    <a href="{{route('g.controle-ponto.configuracoes.index')}}">
                        CONFIGURAÇÕES
                    </a>
                </li>
            @endcan
            @can('ocorrencias_jornadas')
                <li>
                    <a href="{{route('g.controle-ponto.ocorrencias_jornadas.index')}}">
                        OCORRÊNCIAS
                    </a>
                </li>
            @endcan
            @can('feriados')
                <li>
                    <a href="{{route('g.controle-ponto.feriados.index')}}">
                        FERIADOS
                    </a>
                </li>
            @endcan
            @can('escalas')
                <li>
                    <a href="{{route('g.controle-ponto.escalas.index')}}">
                        ESCALAS
                    </a>
                </li>
            @endcan
            @can('ponto-eletronico')
                <li>
                    <a href="{{route('g.controle-ponto.ponto-eletronico.index')}}">
                        PONTO ELETRÔNICO
                    </a>
                </li>
            @endcan
            @can('ajustar-jornadas')
                <li>
                    <a href="{{route('g.controle-ponto.ajustar-jornadas.index')}}">
                        AJUSTAR JORNADAS
                    </a>
                </li>
            @endcan
            @can('folha-ponto')
                <li>
                    <a href="{{route('g.controle-ponto.folha-ponto.index')}}">
                        FOLHA DE PONTO
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('pre_admissao', 'cih', 'admissao', 'historico', 'pos_admissao'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect"><i class="bx bx-bookmark-plus"></i>
            <span>ADMISSÃO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('pre_admissao')
                <li>
                    <a href="{{route('g.admissao.preadm.index')}}" key="pre_admissao">
                        PRÉ-ADMISSÃO
                    </a>
                </li>
            @endcan
            @can('pre_admissao')
                <li>
                    <a href="{{route('g.controle_exames.index')}}" key="controle_exames">
                        CONTROLE DE EXAMES
                    </a>
                </li>
            @endcan
            @can('cih')
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
            @can('admissao')
                <li>
                    <a href="{{route('g.admissao.admissao.index')}}">
                        PROCESSO
                    </a>
                </li>
            @endcan
            @can('historico')
                <li>
                    <a href="{{route('g.historico.index')}}">
                        HISTÓRICO
                    </a>
                </li>
            @endcan
            @can('pos_admissao')
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
@if(\App\Models\Sistema::permitirLinks('weekly-report'))
    <li>
        <a href="{{ (route('g.weekly-report.index')) }}" class="waves-effect">
            <i class="fas fa-tasks"></i>
            <span>WEEKLY REPORT</span>
        </a>
    </li>
@endif
<li>
    <a href="javascript://" class="has-arrow waves-effect">
        <i class="bx bx-aperture"></i>
        <span>TREINAMENTO</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        @can('portaria')
            <li>
                <a href="{{route('g.portaria.index' )}}" key="portaria">
                    Portaria
                </a>
            </li>
        @endcan
        @can('treinamento')
            <li>
                <a href="{{route('g.treinamentos.treinamento.index' )}}" key="carteira_etiquetas">
                    Carteira/Etiquetas
                </a>
            </li>
        @endcan
        @can('certificado')
            <li>
                <a href="{{route('g.certificados.certificado.index' )}}" key="emissao_certificados">
                    Emissão Certificados (NR33/NR35)
                </a>
            </li>
        @endcan
    </ul>
</li>
@if(\App\Models\Sistema::permitirLinks('fluxo-caixa','classificacao-plano-conta','formas-pagamento'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-dollar-circle"></i>
            <span>FINANCEIRO</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('classificacao-plano-conta')
                <li>
                    <a href="{{ (route('g.financeiro.classificacao-plano-conta.index')) }}" class="waves-effect">
                        <span>Classificação</span>
                    </a>
                </li>
            @endcan
            @can('plano-conta')
                <li>
                    <a href="{{ (route('g.financeiro.plano-conta.index')) }}" class="waves-effect">
                        <span>Planos de conta</span>
                    </a>
                </li>
            @endcan
            @can('formas-pagamento')
                <li>
                    <a href="{{ (route('g.financeiro.formas-pagamento.index')) }}" class="waves-effect">
                        <span>Formas de pagamento</span>
                    </a>
                </li>
            @endcan
            @can('fluxo-caixa')
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
            @foreach(auth()->user()->Clouds as $cloud)
                <li>
                    <a href="{{route('g.cloud.cloud.single', [$cloud->id, $cloud->nome])}}" key="{{$cloud->nome}}">
                        {{$cloud->nome}}
                    </a>
                </li>
            @endforeach

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

@if(\App\Models\Sistema::permitirLinks('galeria_site','cartela_cliente_site','depoimento_site'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-sitemap"></i>
            <span>SITE</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('galeria_site')
                <li>
                    <a href="{{route('g.site.galeria.index')}}" key="galeria_site">
                        Galeria de Fotos
                    </a>
                </li>
            @endcan
            @can('cartela_cliente_site')
                <li>
                    <a href="{{route('g.site.cliente.cliente-logo.index')}}" key="cartela_cliente_site">
                        Cartela Cliente
                    </a>
                </li>
            @endcan
            @can('depoimento_site')
                <li>
                    <a href="{{route('g.site.testemunhal.testemunhal.index')}}" key="depoimento_site">
                        Depoimento
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('relatorios'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-user-circle"></i>
            <span>RELATÓRIOS</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('controleusuarios')
                <li>
                    <a href="{{route('g.relatorios.controleusuarios.index')}}" key="controleusuarios">
                        Controle de Usuários
                    </a>
                </li>
            @endcan

        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('usuarios'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="bx bx-user-circle"></i>
            <span>USUÁRIOS</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('usuarios')
                <li>
                    <a href="{{route('g.usuarios.usuarios.index')}}" key="usuarios">
                        Usuários do Sistema
                    </a>
                </li>
            @endcan

        </ul>
    </li>
@endif

@if(\App\Models\Sistema::permitirLinks('habilidades','papel'))
    <li>
        <a href="javascript://" class="has-arrow waves-effect">
            <i class="fa fa-cogs" style="font-size: 16px;"></i>
            <span>CONFIGURAÇÕES</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @can('habilidades')
                <li>
                    <a href="{{route('g.configuracoes.habilidades.index')}}" key="habilidades">
                        Módulos do sistema
                    </a>
                </li>
            @endcan
            @can('papel')
                <li>
                    <a href="{{route('g.configuracoes.papeis.index')}}" key="grupo-usuarios">
                        Grupos de Usuários
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif
