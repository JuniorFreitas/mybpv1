<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Contrato de Trabalho a Título de Experiência</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        @page {
            size: A4 portrait;
            margin: 5mm 10mm 12mm 10mm;
        }
        html, body {
            font-size: 9.5pt;
        }
        .cabecalho {
            margin: 0 0 10pt;
            padding: 0 0 6pt;
        }
        .cabecalho__logo img {
            max-height: 13mm;
            max-width: 42mm;
        }
        .cabecalho__logo--compact img {
            max-height: 10mm;
        }
        .cabecalho__razao {
            font-size: 9.5pt;
        }
        .cabecalho__meta {
            font-size: 7.5pt;
        }
        .titulo {
            font-size: 11pt;
            margin: 0 0 6pt;
            line-height: 1.3;
        }
        .titulo-sub {
            text-align: center;
            font-size: 8.5pt;
            font-weight: normal;
            text-transform: none;
            line-height: 1.3;
            margin: 0 0 12pt;
        }
        .paragrafo {
            font-size: 9.5pt;
            line-height: 1.4;
            text-indent: 8mm;
            margin: 0 0 7pt;
        }
        .paragrafo--sem-recuo {
            text-indent: 0;
        }
        .quebra-pagina {
            page-break-before: always;
        }
        .doc-fechamento__local {
            font-size: 9.5pt;
        }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $colaborador = $dados['dados_colaborador'] ?? null;
    $nomeEmpregado = $colaborador->Curriculo->nome ?? '—';
    $razaoSocial = $dados_empresa['razao_social'] ?? '';
    $dadosAdmissao = $colaborador->Admissao->DadosAdmissoes ?? null;
    $ctpsNumero = $dadosAdmissao->ctps_numero ?? 'NÃO INFORMADO';
    $ctpsSerie = $dadosAdmissao->ctps_serie ?? 'NÃO INFORMADO';
    $funcao = $colaborador->VagaAberta->VagaSelecionada->nome ?? '—';
    $salario = $colaborador->Admissao->salario ?? 0;
    $experiencia = $colaborador->Admissao->pExperiencia();
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Contrato de Trabalho a Título de Experiência</p>
    <p class="titulo-sub">(Alínea ‘c’ do § 2º do artigo 443 da CLT)</p>

    <p class="paragrafo paragrafo--sem-recuo">
        Pelo presente Instrumento Particular de Contrato de Trabalho a Título de Experiência, que de um lado celebra
        a empresa <strong>{{ $razaoSocial }}</strong>, sociedade empresária limitada, inscrita no CNPJ/MF sob n.º
        <strong>{{ $dados_empresa['cnpj'] ?? '' }}</strong>, com atividade localizada:
        {{ $dados_empresa['endereco_completo'] ?? '' }}, doravante designada simplesmente
        <strong>EMPREGADORA</strong> e de outro <strong>{{ $nomeEmpregado }}</strong> portador(a) Carteira
        Profissional n.º {{ $ctpsNumero }}, Série {{ $ctpsSerie }} a seguir chamado apenas
        <strong>EMPREGADO</strong>, é celebrado o presente <strong>CONTRATO DE EXPERIÊNCIA</strong>, que terá
        vigência a partir da data de início da prestação de serviços, de acordo com as condições a seguir
        especificadas:
    </p>

    <p class="paragrafo">
        <strong>1ª (Função/Salário)</strong> – Fica o <strong>EMPREGADO</strong> admitido no quadro de funcionários
        da <strong>EMPREGADORA</strong> para exercer as funções de
        <span style="text-transform: uppercase; font-weight: bold">{{ $funcao }}</span>
        mediante a remuneração mensal de R$ {{ $salario }}
        ({{ \App\Models\Sistema::valorPorExtenso($salario) }}). A circunstância, porém, de ser a função especificada
        não importa na intransferibilidade do <strong>EMPREGADO</strong> para outro serviço, no qual demonstre
        melhor capacidade de adaptação desde que compatível com sua condição pessoal.
    </p>

    <p class="paragrafo">
        <strong>2ª (Horário)</strong> – O horário de trabalho será no horário administrativo ou, especialmente, em
        horário de turno cumprindo jornada de 44h/semanais, se comprometendo a trabalhar em regime de compensação e
        de prorrogação de horas, quando necessário, e a eventual redução da jornada, por determinação da
        <strong>EMPREGADORA</strong>, não inovará este ajuste, permanecendo sempre íntegra a obrigação do
        <strong>EMPREGADO</strong> de cumprir o horário que lhe for determinado, observando o limite legal.
    </p>

    <p class="paragrafo">
        <strong>3ª (Horas Extras)</strong> – Obriga-se também o <strong>EMPREGADO</strong> a prestar serviços em
        horas extraordinárias, sempre que lhe for determinado pela <strong>EMPREGADORA</strong>, na forma prevista
        em Lei. Na hipótese desta faculdade pela <strong>EMPREGADORA</strong>, o <strong>EMPREGADO</strong>
        receberá as horas extraordinárias com o acréscimo legal, ou será tratado através de acordo de compensação de
        horas, com a consequente redução da jornada de trabalho em outro dia. Horas extras não autorizadas não serão
        remuneradas e estarão passíveis de medidas administrativas.
    </p>

    <p class="paragrafo">
        <strong>4ª (Localidade)</strong> – Fica ajustado nos termos que dispõe o Parágrafo 10° do Artigo 469, da
        Consolidação das Leis de Trabalho, que o <strong>EMPREGADO</strong> acatará ordem emanada da
        <strong>EMPREGADORA</strong> para prestação de serviços tanto da localidade de celebração do Contrato de
        Trabalho, como qualquer outra cidade, quer essa transferência seja transitória ou definitiva.
    </p>

    <p class="paragrafo">
        <strong>5ª (Dano)</strong> – Em caso de dano causado pelo <strong>EMPREGADO</strong>, fica a
        <strong>EMPREGADORA</strong>, autorizada a efetivar o desconto da importância correspondente ao prejuízo, o
        qual fará, com fundamento no Parágrafo único do artigo 462 da Consolidação das Leis de Trabalho, já que essa
        possibilidade fica expressamente prevista em contrato.
    </p>

    <p class="paragrafo">
        @if(!$experiencia)
            <strong>6ª (Duração)</strong> – O presente contrato vigorará por tempo inderterminado.
        @else
            <strong>6ª (Duração)</strong> – O presente contrato vigorará
            por {{ $experiencia[0] }}
            ({{ \MasterTag\GExtenso::numero($experiencia[0]) }})
            dias, podendo este ser prorrogado ou não por mais {{ $experiencia[1] }}
            ({{ \MasterTag\GExtenso::numero($experiencia[1]) }})
            dias sendo celebrado para as partes verificarem reciprocamente, a conveniência ou não de se vincularem
            em caráter definitivo a um Contrato de Trabalho. A empresa passando a conhecer as aptidões do
            <strong>EMPREGADO</strong> e suas qualidades pessoais e morais; o <strong>EMPREGADO</strong>
            verificando se o ambiente e os métodos de trabalho atendem à sua conveniência.
        @endif
    </p>

    <p class="paragrafo">
        <strong>7ª (EPIs)</strong> – A <strong>EMPREGADORA</strong> concederá ao colaborador, uniformes e EPIs
        padronizados, sendo de uso diário obrigatório, objetivando manter imagem e identidade da empresa como também
        evitar eventuais acidentes. Os mesmos deverão ser devolvidos no ato do desligamento ou quando ocorrer
        substituição por qualquer defeito decorrente do uso normal.
    </p>

    <p class="paragrafo">
        <strong>8ª</strong> As intervenções decorrentes das atribuições do Empregado, originadas de pesquisa pura e
        aplicada, bem como aquelas oriundas de estudos com a utilização das instalações e equipamentos do local de
        trabalho, são de propriedade exclusiva da <strong>EMPREGADORA</strong>.
    </p>

    <p class="paragrafo">
        <strong>9ª (Férias)</strong> – Com fundamento no Parágrafo 1° do artigo 134 CLT, concorda o
        <strong>EMPREGADO</strong> que <strong>EMPREGADORA</strong> fracione as férias anuais em 03 períodos
        concessivos, na medida em que um dos períodos de férias não poderá ser inferior a quatorze dias corridos e
        os demais não poderão ser inferior a cinco dias corridos.
    </p>

    <p class="paragrafo">
        <strong>10ª</strong> Fica ainda autorizado, de livre e espontânea vontade, para os mesmos fins, a cessão de
        direitos da veiculação das imagens em todo e qualquer material entre imagens de vídeo, fotos e documentos,
        para ser utilizada pela empresa <strong>CONTRATANTE</strong> destinado a divulgação ao público em geral não
        recebendo para tanto qualquer tipo de remuneração.
    </p>

    <p class="paragrafo">
        <strong>11ª (Rescisão)</strong> – Opera-se a rescisão do presente contrato pela decorrência do prazo supra
        ou por vontade de uma das partes; rescindindo-se por vontade do <strong>EMPREGADO</strong> ou pela
        <strong>EMPREGADORA</strong> com justa causa, nenhuma indenização é devida; rescindindo-se antes do prazo,
        pela <strong>EMPREGADORA</strong>, fica esta obrigada a pagar 50% dos salários devidos até o final (metade
        do tempo combinado restante), nos termos do Artigo 479 da CLT, sem prejuízo do disposto no Regimento do FGTS.
    </p>

    <p class="paragrafo">
        <strong>12ª (Proteção de Dados Pessoais)</strong> – O <strong>EMPREGADO</strong> está ciente de que informa
        os dados contidos na Ficha Registro de Funcionário à <strong>EMPREGADORA</strong>, os quais são necessários
        para a execução do contrato de trabalho de experiência.
    </p>

    <p class="paragrafo">
        <strong>12.1</strong> - Durante a vigência do contrato, outros dados pessoais poderão ser gerados, os quais
        também são necessários para a execução do contrato e para as finalidades mencionados na cláusula 12.3 e, na
        hipótese de ausência de previsão, por meio de formulário próprio autorizativo.
    </p>

    <p class="paragrafo">
        <strong>12.2</strong> - O <strong>EMPREGADO</strong> ao assinar o presente contrato, está ciente que a
        empresa contratante poderá solicitar dados extras, para o cumprimento de uma obrigação legal, uso de
        sistemas de controle de pessoal, pagamento de impostos e informações sociais aos órgãos públicos, bem como,
        para a execução do presente contrato.
    </p>

    <p class="paragrafo">
        <strong>12.3</strong> - Os dados fornecidos pelo <strong>EMPREGADO</strong> serão utilizados para as
        seguintes finalidades:<br>
        a. Funcionalidades de sistemas, softwares, plataformas, cadastros e normas internas;<br>
        b. Cadastro para eventuais notificações, comunicações formais entre empregado e empregadora no âmbito da
        gestão contratual por parte da <strong>EMPREGADORA</strong>;<br>
        c. Atendimento das demandas administrativas e operacionais no setor de RH;<br>
        d. Atendimento à pedidos de autoridades públicas, prestação de contas e informações sociais obrigadas em lei.
    </p>

    <p class="paragrafo">
        <strong>12.4</strong> - A coleta de dados se dá através de sua inserção pela <strong>EMPREGADORA</strong> no
        momento da contratação pelo setor de RH.
    </p>

    <p class="paragrafo">
        Parágrafo Primeiro – Os dados da cláusula 12.3 serão gerados no decorrer do relacionamento entre as partes e
        do cumprimento do presente contrato.
    </p>

    <p class="paragrafo">
        Parágrafo segundo – A qualquer tempo, a <strong>EMPREGADORA</strong> pode requerer a correção ou atualização
        dos dados pessoais coletados, tendo o <strong>EMPREGADO</strong> de pleno conhecimento e aceite deste
        parágrafo.
    </p>

    <p class="paragrafo">
        <strong>12.5</strong> - Os dados pessoais coletados podem ser acessados a qualquer momento pela
        <strong>EMPREGADORA</strong> através de solicitação no site/e-mail/plataforma, solicitação do setor de RH,
        solicitação do setor contábil e quantas outras solicitações sejam necessárias para o cumprimento do presente
        contrato, estando o empregado de pleno acordo com tais termos.
    </p>

    <p class="paragrafo">
        <strong>12.6</strong> - O tratamento dos dados será realizado enquanto o <strong>EMPREGADO</strong> estiver
        trabalhando para a empresa contratante, ou seja, enquanto durar o contrato de trabalho.
    </p>

    <p class="paragrafo">
        <strong>12.7</strong> - A <strong>EMPREGADORA</strong> armazenará os dados relativos ao
        <strong>EMPREGADO</strong> por até 5 anos, após o encerramento do contrato, prorrogáveis por igual prazo, a
        fim de atender ao prazo prescricional de discussões judiciais que estão em Ficha Registro do Empregado
        atrelada à este contrato.
    </p>

    <p class="paragrafo">
        <strong>12.8</strong> - Os dados serão tratados pela própria <strong>EMPREGADORA</strong>, o qual poderá
        realizar a transferência de dados pessoais a terceiros visando o melhor desempenho das atividades vinculadas
        ao serviço contratado, assim como para gestão, segurança, armazenamento e backup em nuvem. Poderá, ainda,
        quando solicitado, compartilhar os dados pessoais do <strong>EMPREGADO</strong> com autoridades e entidades
        governamentais em função de exigências legais ou na defesa dos seus interesses no caso de conflitos,
        judiciais ou administrativos.
    </p>

    <p class="paragrafo">
        Parágrafo primeiro – A <strong>EMPREGADORA</strong> garante, desde já, que os terceiros
        contratados/subcontratados adotam medidas de segurança adequadas aos princípios e diretrizes previstos na
        LGPD e em conformidade com as boas práticas de privacidade e segurança da informação.
    </p>

    <p class="paragrafo">
        Parágrafo segundo – A <strong>EMPREGADORA</strong> cumprirá os princípios de adequação, necessidade e
        finalidade, e limitará internamente o acesso aos dados aos colaboradores estritamente necessários ao
        atendimento da finalidade.
    </p>

    <p class="paragrafo">
        <strong>12.9</strong> - Os dados coletados são de acesso exclusivo da <strong>EMPREGADORA</strong>, e não
        serão vendidos ou cedidos a terceiros sem expresso consentimento pelo <strong>EMPREGADO</strong>. A
        <strong>EMPREGADORA</strong> se compromete em manter seguros os dados pessoais coletados, e realizará,
        quando necessário, relatório para avaliar o impacto dos tratamentos.
    </p>

    <p class="paragrafo">
        Parágrafo único – A <strong>EMPREGADORA</strong>, ao prezar pela privacidade do <strong>EMPREGADO</strong>,
        garante que os funcionários e terceiros envolvidos direta ou indiretamente nas operações de tratamento de
        dados pessoais estão cobertos por um dever de confidencialidade.
    </p>

    <p class="paragrafo">
        <strong>12.10</strong> - Se, apesar das medidas de segurança implementadas, houver vazamento de dados que
        possa acarretar risco ou dano relevante aos titulares, a <strong>EMPREGADORA</strong> irá comunicar o
        <strong>EMPREGADO</strong> e a Autoridade Nacional de Proteção de Dados, nos termos do art. 48 da Lei nº
        13.709/2018, e tomará as medidas necessárias para reduzir os danos e o risco de recorrência. Entende-se como
        vazamento a destruição, perda, alteração ou acesso não autorizados, seja de forma acidental ou deliberada, a
        dados pessoais.”
    </p>

    <p class="paragrafo">
        <strong>13ª (Vigência)</strong> – Na hipótese deste ajuste transformar-se em contrato de prazo
        indeterminado, pelo decurso do tempo, continuarão em plena vigência às clausulas de 01 (Um) a 12 (Doze),
        enquanto durarem as relações do <strong>EMPREGADO</strong> com a <strong>EMPREGADORA</strong>.
    </p>

    <p class="paragrafo">
        E por estarem de pleno acordo, as partes contratantes, assinam o presente contrato de experiência em duas
        vias, ficando a primeira em poder da <strong>EMPREGADORA</strong>, e a segunda com o
        <strong>EMPREGADO</strong> que dela dará o competente recibo.
    </p>

    @include('pdf.partials.documento_formal_fechamento', [
        'localData' => 'São Luís/MA, ' . (new \MasterTag\DataHora($colaborador->Admissao->data_admissao))->dataCompletaExt() . '.',
        'assinantes' => [
            ['nome' => $razaoSocial, 'papel' => 'EMPREGADORA'],
            ['nome' => $nomeEmpregado, 'papel' => 'EMPREGADO'],
        ],
        'testemunhas' => 2,
        'novaPagina' => true,
    ])

    <div class="doc-secao-pagina doc-secao-pagina--termo">
        <p class="titulo">Termo de Prorrogação</p>

        <p class="doc-termo__texto">
            Por mútuo acordo entre as partes, fica o presente contrato de experiência, que deveria vencer em,
            _______/_______/______________ prorrogado até _______/_______/______________.
        </p>

        @include('pdf.partials.documento_formal_fechamento', [
            'localData' => 'São Luís, ______________ de ____________________________ de ______________.',
            'assinantes' => [
                ['nome' => $razaoSocial, 'papel' => 'EMPREGADORA'],
                ['nome' => $nomeEmpregado, 'papel' => 'EMPREGADO'],
            ],
            'testemunhas' => 2,
            'novaPagina' => false,
        ])
    </div>
</div>
</body>
</html>
