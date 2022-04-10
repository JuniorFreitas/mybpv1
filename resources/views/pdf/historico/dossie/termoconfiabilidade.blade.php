@extends('layouts.pdf')
@section('title','TERMO DE SIGILO E CONFIDENCIALIDADE – ANEXO AO CONTRATO DE TRABALHO')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <p class="f12"
       style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
        <b>TERMO DE SIGILO E CONFIDENCIALIDADE – ANEXO AO CONTRATO DE TRABALHO</b><br>
    </p>
    <p class="f11">
        EMPREGADO:<br><br>

        {{$dados->nome}}, já qualificado no Contrato de Trabalho, doravante denominado simplesmente EMPREGADO.
    </p><br>
    <p class="f11">
        EMPRESA: <br><br>

        @include('layouts.dadosEmpresa')

    </p><br>
    <p class="f11" style="">
        Sempre que em conjunto referidas, doravante denominada(s) como PARTE(S).
        <br>
    </p>
    <br>
    <p class="f11" style="">
        Considerando que, em razão do contrato de trabalho celebrado entre as PARTES, doravante denominado CONTRATO, as
        mesmas terão acesso a informações confidenciais, as quais se constituem informação comercial confidencial.
        <br><br>
    </p>
    <p class="f11" style="">
        Considerando que as PARTES desejam ajustar as condições de revelação destas informações confidenciais já
        disponibilizadas e aquelas que no futuro serão disponibilizadas para a execução do CONTRATO, bem como definir as
        regras relativas ao seu uso e proteção:
        <br><br>
    </p>
    <p class="f11" style="">
        Considerando que as PARTES declaram-se conhecedoras do art.482, “c”,“g” da CLT:<br><br>

        Art. 482. Constituem justa causa para rescisão do contrato de trabalho pelo empregador:<br><br>
        ...
        c) negociação habitual por conta própria ou alheia sem permissão do empregador, e quando construir ato de
        concorrência à empresa para a qual trabalha o empregado, ou for prejudicial ao serviço;<br><br>
        ...
        g) violação de segredo da empresa;

        <br>
    </p>
    <p class="f11" style="">
        RESOLVEM AS PARTES acima qualificadas, celebrar o presente TERMO DE CONFIDENCIALIDADE (“Termo”), Acordo
        Vinculado ao Contrato, mediante as cláusulas e condições que seguem:
        <br><br>
    </p>
    <p class="f11" style="">
        1. CLÁUSULA PRIMEIRA – DO OBJETO<br><br>

        O objeto deste Termo é prover a necessária e adequada proteção das informações confidenciais fornecidas pela
        EMPRESA, ou pelos seus clientes ao EMPREGADO, em razão do CONTRATO, a fim de que as mesmas possam desenvolver as
        atividades contempladas no CONTRATO, o qual vincular-se-á expressamente a este.
        <br><br>
        1.1.As estipulações e obrigações constantes do presente instrumento serão aplicadas a toda e qualquer informação
        que seja revelada pela EMPRESA ou pelo seus Clientes.<br><br>
    </p>
    <p class="f11" style="">
        2.CLÁUSULA SEGUNDA: DAS INFORMAÇÕES CONFIDENCIAIS<br><br>

        2.1.O EMPREGADO se obriga a manter o mais absoluto sigilo com relação a toda e qualquer informação e documento,
        conforme abaixo definida, que tenha sido revelada anteriormente e também as que venham a ser, a partir desta
        data, fornecida pela EMPRESA ou pelo seus Clientes, devendo ser tratada como informação sigilosa.<br><br>

        2.2.Deverá ser considerada como informação confidencial, toda e qualquer informação escrita ou oral, revelada ao
        EMPREGADO, contendo ela ou não a expressão “Confidencial”. O termo “informação” abrangerá toda informação
        escrita, verbal, digitalizada ou de qualquer outro modo apresentada, tangível ou intangível, virtual ou física,
        podendo incluir, mas não se limitando a: “know-how”, técnicas, “designs”, especificações, diagramas,
        fluxogramas, configurações, soluções, fórmulas, modelos, desenhos, cópias, amostras, cadastro de clientes,
        preços e custos, contratos, planos de negócios, planos de segurança, processos, projetos, fotografias, programas
        de computador, discos, disquetes, fitas, conceitos de produto, instalações, infra-estrutura, especificações,
        amostras de idéias, definições e informações mercadológicas, invenções e idéias, outras informações técnicas,
        financeiras ou comerciais, dentre outros, doravante denominados “INFORMAÇÕES CONFIDENCIAIS”, a que, diretamente
        ou através de seus Diretores, Clientes, empregados e/ou prepostos, venha o EMPREGADO, ter acesso, seja por si
        criada em favor da EMPRESA, conhecimento, ou que venha lhe ser confiadas durante e em razão dos trabalhos
        realizados e do Contrato Principal celebrado entre as PARTES.<br><br>

        2.3.Compromete-se, outrossim, o EMPREGADO, a não revelar, reproduzir, utilizar ou dar conhecimento, ou alugar ou
        vender, em hipótese alguma, a terceiros as INFORMAÇÕES CONFIDENCIAIS.<br><br>

        2.4.As PARTES deverão cuidar para que as informações confidenciais fiquem restritas a sede da EMPRESA durante as
        discussões, análises, reuniões e negócios, trabalhos e projetos.<br><br>

    </p>
    <p class="f11" style="">
        3.CLÁUSULA TERCEIRA – DOS DIREITOS E OBRIGAÇÕES<br>
        O EMPREGADO se compromete e se obriga a utilizar a informação confidencial revelada pela EMPRESA exclusivamente
        para os propósitos deste Termo e da execução do Contrato Principal, mantendo sempre estrito sigilo acerca de
        tais informações.<br><br>

        3.1. O EMPREGADO se compromete a não efetuar qualquer cópia da informação confidencial sem consentimento prévio
        e expresso da EMPRESA.<br><br>

        3.3. O EMPREGADO obriga-se a tomar todas as medidas necessárias à proteção da informação confidencial da
        EMPRESA, bem como para evitar e prevenir revelação a terceiros, exceto se devidamente autorizado por escrito
        pela EMPRESA. De qualquer forma, a revelação é permitida para empresas controladoras, controladas e/ou
        coligadas, assim consideradas as empresas que direta ou indiretamente controlem ou sejam controladas pela
        EMPRESA.<br><br>
    </p>
    <p class="f11" style="">
        4. CLÁUSULA QUARTA – DA VIGÊNCIA<br><br>

        4.1 O presente Termo tem natureza irrevogável e irretratável, permanecendo em vigor desde a data da revelação
        das INFORMAÇÕES CONFIDENCIAS até 2 (dois) anos após o término do Contrato Principal, ao qual este é
        vinculado.<br><br>

    </p>
    <p class="f11" style="">
        5. CLÁUSULA QUINTA – DAS PENALIDADES<br><br>

        5.1A quebra do sigilo profissional, devidamente comprovada, sem autorização expressa da EMPRESA, possibilitará a
        imediata rescisão de qualquer contrato firmado entre as PARTES, sem qualquer ônus para a EMPRESA. Neste caso, o
        EMPREGADO estará sujeito, por ação ou omissão, ao pagamento ou recomposição de todas as perdas e danos sofridos
        pela EMPRESA, inclusive as de ordem moral ou concorrencial, bem como as de responsabilidades civil e criminal
        respectivas, as quais serão apuradas em regular processo judicial ou administrativo, mais o valor de eventuais
        lucros cessantes resultantes de INFORMAÇÕES CONFIDENCIAIS indevidamente transferidas, devidamente corrigidos
        pelo índice determinado pelo Poder Judiciário do Maranhão.<br><br>

    </p>
    <p class="f11" style="">
        6. CLÁUSULA SEXTA - DAS DISPOSIÇÕES GERAIS<br><br>

        6.1 O presente Termo constitui acordo entre as PARTES, relativamente ao tratamento das INFORMAÇÕES
        CONFIDENCIAIS, aplicando-se a todos os acordos, promessas, propostas, declarações, entendimentos e negociações
        anteriores ou posteriores, escritas ou verbais, empreendidas pelas PARTES contratantes no que diz respeito ao
        Contrato Principal, sejam estas ações feitas direta ou indiretamente pelas PARTES, em conjunto ou separadamente,
        e, será igualmente aplicado a todo e qualquer acordo ou entendimento futuro, que venha a ser firmado entre as
        PARTES.<br><br>
        6.2 Este Termo de Confidencialidade constitui termo vinculado ao Contrato Principal, parte independente e
        regulatória daquele.<br><br>

        6.3 Surgindo divergências quanto à interpretação do pactuado neste Termo ou quanto à execução das obrigações
        dele decorrentes, ou constatando-se nele a existência de lacunas, solucionarão as PARTES tais divergências, de
        acordo com os princípios de boa fé, da eqüidade, da razoabilidade, e da economicidade e, preencherão as lacunas
        com estipulações que, presumivelmente, teriam correspondido à vontade das PARTES na respectiva ocasião.<br><br>

        6.4 O disposto no presente Termo de Confidencialidade prevalecerá, sempre, em caso de dúvida, e salvo expressa
        determinação em contrário, sobre eventuais disposições constantes de outros instrumentos conexos firmados entre
        as PARTES quanto ao sigilo de informações confidenciais, tal como aqui definida.<br><br>

        6.5 A omissão ou tolerância das PARTES, em exigir o estrito cumprimento dos termos e condições deste contrato,
        não constituirá novação ou renúncia, nem afetará os seus direitos, que poderão ser exercidos a qualquer
        tempo.<br><br>
    </p>
    <p class="f11" style="">
        7. CLÁUSULA SÉTIMA - DO FORO<br><br>

        7.1 As PARTES elegem o foro central de São Luís, Estado do Maranhão, para dirimir quaisquer dúvidas originadas
        do presente Termo, com renúncia expressa a qualquer outro, por mais privilegiado que seja.<br><br>
    </p>
    <p class="f11" style="">
        E, por assim estarem justas e contratadas, as partes assinam o presente instrumento em 2 (duas) vias de igual
        teor e um só efeito, na presença de duas testemunhas.<br><br>
    </p>
    <br>
    <br>
    <div class="f11" style="line-height: 26pt">
        {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        <br>
        São Luís, MA.
        <br>
        <br>
    </div>
    <div class="f11" style="line-height: 26pt;text-align: center">
        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
        {{$dados->User->DadosEmpresa->razao_social}}
        <br>
        <br>
        <br>
        <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
        {{$dados->nome}}
    </div>

    @include('layouts.rodapePdf')
@endsection

@push('style')
    <style type="text/css">
        .footer {
            position: absolute;
            bottom: 0px;
            font-size: 8.4pt;
            /*width: 10cm;*/
        }

        .f14 {
            font-size: 14pt;
        }

        .f11 {
            font-size: 11pt;
        }

        .f12 {
            font-size: 12pt;
        }

        .f10 {
            font-size: 10pt;
        }

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }

    </style>
@endpush
