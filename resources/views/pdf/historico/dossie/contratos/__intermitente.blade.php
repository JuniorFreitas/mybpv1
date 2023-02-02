@extends('layouts.pdf')
@section('title','CONTRATO DE TRABALHO A TÍTULO DE EXPERIÊNCIA')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <p class="f12"
       style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
        <strong>CONTRATO DE TRABALHO INTERMITENTE</strong><br>
        (§2º o artigo 443 c/c Artigo 452-A da CLT)
    </p>
    <p class="f12 text-justify">
        Pelo presente Instrumento Particular de Contrato de Trabalho a Título de Experiência, que de um lado celebra a
        empresa <strong>{{$dados->User->DadosEmpresa->razao_social}}</strong>, sociedade empresária limitada, inscrita
        no CNPJ/MF sob n.º
        <strong>{{$dados->User->DadosEmpresa->cnpj}}</strong>, com atividade
        localizada: {{$dados->User->DadosEmpresa->endereco_completo}},
        doravante designada simplesmente <strong>EMPREGADORA</strong> e de outro <strong>{{$dados->nome}}</strong>
        portador(a) Carteira Profissional
        n.º {{$dados->FeedBack->Admissao->DadosAdmissoes? $dados->FeedBack->Admissao->DadosAdmissoes->ctps_numero : 'NÃO INFORMADO'}}
        ,
        Série {{$dados->FeedBack->Admissao->DadosAdmissoes? $dados->FeedBack->Admissao->DadosAdmissoes->ctps_serie: 'NÃO INFORMADO'}}
        a seguir chamado apenas <strong>EMPREGADO(A)</strong>, é celebrado o
        presente <strong>CONTRATO DE TRABALHO INTERMITENTE</strong>,, conforme artigo 443 e seu parágrafo 3º, e artigo
        452-A e seus parágrafos, da CLT que terá vigência a partir da data
        de {{$dados->FeedBack->Admissao->data_admissao}}, de acordo com as condições a seguir
        especificadas:

    </p><br>
    <p class="f12 text-justify">
        <strong>1ª (Função/Salário)</strong> – Fica o <strong>EMPREGADO(A)</strong> admitido no quadro de funcionários
        da
        <strong>EMPREGADORA</strong> para exercer as funções de <span
            style="text-transform: uppercase; font-weight: bold">{{ $dados->FeedBack->VagaAberta->VagaSelecionada->nome }}</span>
        mediante a remuneração como horista de R$ {{ $dados->FeedBack->Admissao->salario }}
        ({{\App\Models\Sistema::valorPorExtenso($dados->FeedBack->Admissao->salario)}}). A circunstância, porém, de ser
        a função especificada não importa na intransferibilidade do(a) <strong>EMPREGADO(A)</strong> para outro serviço,
        no qual
        demonstre melhor capacidade de adaptação desde que compatível com sua condição pessoal.
    </p><br>
    <p class="f12 text-justify">
        <strong>2ª (Horário)</strong> – A EMPREGADORA convocará o(a) EMPREGADO(a) por meio de comunicação eficaz,
        informando a jornada solicitada, com antecedência de pelo menos três dias. Recebida a comunicação o(a)
        EMPREGADO(A) terá um dia útil para comunicar a aceitação ou não da proposta, sendo que seu silêncio representará
        a recusa.
    </p><br>
    <p class="f12 text-justify">
        Parágrafo Primeiro. Não será computado como período extraordinário o que exceder a jornada normal, ainda que
        ultrapasse o limite de cinco minutos previsto no § 1º do art. 58 da CLT, quando o empregado, por escolha
        própria, permanecer nas dependências da empresa para exercer atividades particulares de práticas religiosas,
        descanso, lazer, estudo, alimentação, atividades de relacionamento social, higiene pessoal e troca de roupa ou
        uniforme.<br>
        As partes desde já convencionam a celebração do banco de horas, nos termos dos § 2º e 5º do artigo 59 da CLT
        para a compensação de jornada no período de seis meses.
    </p><br>
    <p class="f12 text-justify">
        Parágrafo Segundo: Aceita a proposta, a parte que, sem justo motivo, descumprir o ajustado, pagará à outra
        parte, no prazo de trinta dias, multa de 50% (cinquenta por cento) da remuneração que seria devida, permitida a
        compensação em igual prazo.
    </p><br>
    <p class="f12 text-justify">
        Parágrafo Terceiro: O período de inatividade não será considerado tempo à disposição da EMPREGADORA, podendo
        o(a) EMPREGADO(A) prestar serviços a outros contratantes.
    </p><br>
    <p class="f12 text-justify">
        PParágrafo Quarto: Não será computado como jornada de trabalho o tempo utilizado pelo EMPREGADO, por escolha
        própria, para permanecer nas dependências da empresa para exercer atividade particulares de práticas religiosas,
        descanso, lazer, estudo, alimentação, atividades de relacionamento social, higiente pessoal e troca de roupa ou
        uniforme.
    </p><br>
    <p class="f12 text-justify">
        <strong>3ª (Turno)</strong> – Aceita o(a) EMPREGADO(A), expressamente, a condição de prestar serviços em
        qualquer dos
        turnos de trabalho, isto é, tanto durante o dia como à noite, desde que sem simultaneidade, observadas as
        prescrições legais reguladoras do assunto, quanto à remuneração.
    </p><br>
    <p class="f12 text-justify">
        <strong>4ª (Localidade)</strong> – Fica ajustado nos termos que dispõe o § 2° e 3ª do Artigo 469, da
        Consolidação das Leis de Trabalho, que o(a) EMPREGADO(A) acatará ordem emanada da EMPREGADORA para prestação de
        serviços tanto da localidade de celebração do Contrato de Trabalho, como qualquer outra cidade, quer essa
        transferência seja transitória ou definitiva.
    </p><br>
    <p class="f12 text-justify">
        <strong>5ª (Dano)</strong> – Em caso de dano causado pelo(a) <strong>EMPREGADO(A)</strong>, de qualquer tipo,
        incluindo veiculos,
        arranhão, batida de veículo da <strong>EMPREGADORA</strong> ou de terceiros envolvidos, fica a <strong>EMPREGADORA</strong>
        autorizada a
        efetivar o desconto da importância correspondente ao prejuízo, o qual fará independente do valor já que essa
        possibilidade fica expressamente prevista em contrato, nos termos do § 1º do artigo 462 da CLT.

    </p>
    <br>
    <p class="f12 text-justify">
        <strong>Parágrafo único:</strong> O(A) <strong>EMPREGADO(A)</strong> compromete-se também, a respeitar o
        regulamento da empresa,
        mantendo conduta irrepreensível no ambiente de trabalho, confidencialidade (conforme Termo de Confidencialidade
        anexo a esse contrato) e postura, constituindo motivos para imediata dispensa do(a)
        <strong>EMPREGADO(A)</strong>, além dos previstos em lei, o desacato moral ou agressão física a <strong>EMPREGADORA</strong>,
        ao administrador ou a pessoa de seus respectivos companheiros de trabalho, a embriagues ou briga em serviço.
        Destaca-se também que é proibido o uso de aparelhos celulares no horário de trabalho, excetos os concedidos pela
        empresa para exercício das atividades laborais, ciente de que a empresa não se responsabiliza por furtos e
        perdas desses aparelhos no local de trabalho.
    </p><br>
    <p class="f12 text-justify">
        <strong>6ª (EPIs)</strong> – A <strong>EMPREGADORA</strong> concederá a(o) <strong>EMPREGADO(A)</strong>,
        uniformes e EPIs padronizados, sendo de uso diário obrigatório, objetivando manter imagem e identidade da
        empresa como também evitar eventuais acidentes. Os mesmos deverão ser devolvidos no ato do desligamento ou
        quando ocorrer substituição por qualquer defeito decorrente do uso normal.
    </p><br>
    <p class="f12 text-justify">
        <strong>7ª (Rescisão)</strong> – Opera-se a rescisão do presente contrato pela decorrência do prazo supra ou por
        vontade de uma das partes; rescindindo-se por vontade do(a) <strong>EMPREGADO(A)</strong> ou pela <strong>EMPREGADORA</strong>
        com justa causa, nenhuma indenização é devida. Sendo o término na vigência posterior ao contrato de experiência
        o aviso prévio é devido pela rescisão do devido contrato que poderá ser trabalhado, podendo haver ou não
        convocação para o trabalho, gerando ou não valores de aviso.
    </p><br>
    <p class="f12 text-justify">
        <strong>8ª (Proteção de Dados Pessoais)</strong> – O <strong>EMPREGADO</strong> está ciente de que informa os
        dados contidos na Ficha Registro de Funcionário à <strong>EMPREGADORA</strong>, os quais são necessários para a
        execução do contrato de trabalho de experiência.
    </p><br>
    <p class="f12 text-justify">
        <strong>8.1</strong> - Durante a vigência do contrato, outros dados pessoais poderão ser gerados, os quais
        também são necessários para a execução do contrato e para as finalidades mencionados na cláusula 8.3 e, na
        hipótese de ausência de previsão, por meio de formulário próprio autorizativo.
    </p><br>
    <p class="f12 text-justify">
        <strong>8.2</strong> - O <strong>EMPREGADO</strong> ao assinar o presente contrato, está ciente que a empresa
        contratante poderá solicitar dados extras, para o cumprimento de uma obrigação legal, uso de sistemas de
        controle de pessoal, pagamento de impostos e informações sociais aos órgãos públicos, bem como, para a execução
        do presente contrato.
    </p><br>
    <p class="f12 text-justify">
        <strong>8.3</strong> - Os dados fornecidos pelo <strong>EMPREGADO</strong> serão utilizados para as seguintes
        finalidades:<br>
        a. Funcionalidades de sistemas, softwares, plataformas, cadastros e normas internas;<br>
        b. Cadastro para eventuais notificações, comunicações formais entre empregado e empregadora no âmbito da gestão
        contratual por parte da <strong>EMPREGADORA</strong>;<br>
        c. Atendimento das demandas administrativas e operacionais no setor de RH;<br>
        d. Atendimento à pedidos de autoridades públicas, prestação de contas e informações sociais obrigadas em lei.
    </p><br>
    <p class="f12 text-justify">
        <strong>8.4</strong> - A coleta de dados se dá através de sua inserção pela <strong>EMPREGADORA</strong> no
        momento da contratação pelo setor de RH.
    </p><br>
    <p class="f12 text-justify">
        Parágrafo Primeiro – Os dados da cláusula 8.3 serão gerados no decorrer do relacionamento entre as partes e do
        cumprimento do presente contrato.
    </p><br>
    <p class="f12 text-justify">
        Parágrafo segundo – A qualquer tempo, a <strong>EMPREGADORA</strong> pode requerer a correção ou atualização dos
        dados pessoais coletados, tendo o <strong>EMPREGADO</strong> de pleno conhecimento e aceite deste parágrafo.
    </p>
    <p class="f12 text-justify">
        <strong>8.5</strong> - Os dados pessoais coletados podem ser acessados a qualquer momento pela <strong>EMPREGADORA</strong>
        através de solicitação no site/e-mail/plataforma, solicitação do setor de RH, solicitação do setor contábil e
        quantas outras solicitações sejam necessárias para o cumprimento do presente contrato, estando o empregado de
        pleno acordo com tais termos.
    </p><br>
    <p class="f12 text-justify">
        <strong>8.6</strong> - O tratamento dos dados será realizado enquanto o <strong>EMPREGADO</strong> estiver
        trabalhando para a
        empresa contratante, ou seja, enquanto durar o contrato de trabalho.
    </p><br>
    <p class="f12 text-justify">
        <strong>8.7</strong> - A <strong>EMPREGADORA</strong> armazenará os dados relativos ao
        <strong>EMPREGADO</strong> por até 5 anos, após o encerramento do contrato, prorrogáveis por igual prazo, a fim
        de atender ao prazo prescricional de discussões judiciais que estão em Ficha Registro do Empregado atrelada à
        este contrato.
    </p><br>
    <p class="f12 text-justify">
        <strong>8.8</strong> - Os dados serão tratados pela própria <strong>EMPREGADORA</strong>, o qual poderá
        realizar a transferência de dados pessoais a terceiros visando o melhor desempenho das atividades vinculadas ao
        serviço contratado, assim como para gestão, segurança, armazenamento e backup em nuvem. Poderá, ainda, quando
        solicitado, compartilhar os dados pessoais do <strong>EMPREGADO</strong> com autoridades e entidades
        governamentais em função de exigências legais ou na defesa dos seus interesses no caso de conflitos, judiciais
        ou administrativos.
    </p><br>
    <p class="f12 text-justify">
        Parágrafo primeiro – A <strong>EMPREGADORA</strong> garante, desde já, que os terceiros
        contratados/subcontratados adotam medidas de segurança adequadas aos princípios e diretrizes previstos na LGPD e
        em conformidade com as boas práticas de privacidade e segurança da informação.
    </p><br>
    <p class="f12 text-justify">
        Parágrafo segundo – A <strong>EMPREGADORA</strong> cumprirá os princípios de adequação, necessidade e
        finalidade, e limitará internamente o acesso aos dados aos colaboradores estritamente necessários ao atendimento
        da finalidade.
    </p><br>
    <p class="f12 text-justify">
        <strong>8.9</strong> - Os dados coletados são de acesso exclusivo da <strong>EMPREGADORA</strong>, e não serão
        vendidos ou cedidos a terceiros sem expresso consentimento pelo <strong>EMPREGADO</strong>. A <strong>EMPREGADORA</strong>
        se compromete em manter seguros os dados pessoais coletados, e realizará, quando necessário, relatório para
        avaliar o impacto dos tratamentos.
    </p><br>
    <p class="f12 text-justify">
        Parágrafo único – A <strong>EMPREGADORA</strong>, ao prezar pela privacidade do <strong>EMPREGADO</strong>,
        garante que os funcionários e terceiros envolvidos direta ou indiretamente nas operações de tratamento de dados
        pessoais estão cobertos por um dever de confidencialidade.
    </p><br>
    <p class="f12 text-justify">
        <strong>8.10</strong> - Se, apesar das medidas de segurança implementadas, houver vazamento de dados que possa
        acarretar risco ou dano relevante aos titulares, a <strong>EMPREGADORA</strong> irá comunicar o <strong>EMPREGADO</strong>
        e a Autoridade Nacional de Proteção de Dados, nos termos do art. 48 da Lei nº 13.709/2018, e tomará as medidas
        necessárias para reduzir os danos e o risco de recorrência. Entende-se como vazamento a destruição, perda,
        alteração ou acesso não autorizados, seja de forma acidental ou deliberada, a dados pessoais.”
    </p><br>
    <p class="f12 text-justify">
        <strong>9ª (Vigência)</strong> – Na hipótese deste ajuste transformar-se em contrato de prazo indeterminado,
        pelo decurso
        do tempo, continuarão em plena vigência as cláusulas de 01 (Um) a 08 (Oito), enquanto durarem as relações
        do(a) <strong>EMPREGADO(A)</strong> com a <strong>EMPREGADORA</strong>.
        <br><br>
        E por estarem de pleno acordo, as partes contratantes, assinam o presente contrato de experiência em duas
        vias, ficando a primeira em poder da <strong>EMPREGADORA</strong>, e a segunda com o(a)
        <strong>EMPREGADO(A)</strong>, que dela dará o
        competente recibo.
    </p><br>

    <br><br>
    <div class="f12" style="line-height: 26pt">
        São Luís/MA, {{ (new \MasterTag\DataHora($dados->FeedBack->Admissao->data_admissao))->dataCompletaExt() }}.
        <br>
        <br>
        <br>
    </div>
    <div class="f12" style="line-height: 15pt;text-align: center">
        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
        {{$dados->User->DadosEmpresa->razao_social}}
        <br>
        <br>
        <br>
        <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
        {{$dados->nome}}
    </div>

    <div class="f12" style="line-height: 15pt;text-align: left">
        <p class="f12" style="margin-bottom: 26pt; margin-top: 30pt">
            TESTEMUNHAS:
        </p>
        <div style="width: 48%; float: left">
            <hr style="  border:none; border-top: 1px solid #333">
        </div>
        <div style="width: 48%; float: right">
            <hr style="  border:none; border-top: 1px solid #333">
        </div>
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
