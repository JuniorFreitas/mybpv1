@extends('layouts.pdf_filial')
@section('title','Contrato de Trabalho')
@section('conteudo')
    @php
        $admissao = $dados['dados_colaborador']->Admissao;
        $curriculo = $dados['dados_colaborador']->Curriculo;
        $nomeColaborador = $curriculo?->nome ?? 'NÃO INFORMADO';
        $cpfColaborador = $curriculo?->cpf ?? 'NÃO INFORMADO';
        $experiencia = $admissao?->pExperiencia();
        $dataAdmissaoRaw = $admissao?->getAttributes()['data_admissao'] ?? null;
        $dataInicio = $dataAdmissaoRaw ? (new \MasterTag\DataHora($dataAdmissaoRaw))->dataCompleta() : 'NÃO INFORMADO';
        $dataTermino = null;
        $dataProrrogacao = null;
        if ($experiencia && $dataAdmissaoRaw) {
            $diasInicial = (int) $experiencia[0];
            $diasProrrogacao = (int) $experiencia[1];
            $dtTermino = new \MasterTag\DataHora($dataAdmissaoRaw);
            // Período inclusivo (ex.: 22/06 + 45 dias → 05/08)
            $dataTermino = $dtTermino->addDia(max($diasInicial - 1, 0));
            $dtProrrogacao = new \MasterTag\DataHora($dtTermino->dataInsert());
            $dataProrrogacao = $dtProrrogacao->addDia($diasProrrogacao);
        }
        $funcao = $admissao?->funcao
            ?: ($dados['dados_colaborador']->VagaAberta?->VagaSelecionada?->nome ?? 'NÃO INFORMADO');
        $municipioSede = $cliente->municipio
            ? mb_strtoupper($cliente->municipio)
            : 'SÃO LUÍS';
        $cidadeAssinatura = $cliente->municipio && $cliente->uf
            ? ucwords(mb_strtolower($cliente->municipio)) . '-' . mb_strtoupper($cliente->uf)
            : 'São Luis-Ma';
    @endphp
    <style>
        @page {
            margin: 10mm 2mm 8mm 2mm;
        }
    </style>
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div style="position: fixed; left: 20px; bottom: 0; text-align: left; width: 90%; padding-bottom: 2px;">
        @include('layouts.rodapePdfFilialJob')
    </div>
    <div style="margin-left: 5.2%; width: 87%; padding-bottom: 28px;">
        <p class="f11" style="text-align: center; margin-top: 0.4cm; margin-bottom: 0.6cm; text-transform: uppercase">
            <strong>CONTRATO DE TRABALHO DE EXPERIÊNCIA</strong>
        </p>

        <p class="f11 text-justify">
            Entre a firma <strong>{{ $dados['dados_empresa']['razao_social'] }}</strong>, com sede em
            <strong>{{ $municipioSede }}</strong>, no endereço
            <strong>{{ $dados['dados_empresa']['endereco_completo'] }}</strong> doravante designada, simplesmente
            <strong>EMPREGADOR</strong> e <strong>{{ $nomeColaborador }}</strong>, portador da Carteira de Trabalho
            nº <strong>{{ $cpfColaborador }}</strong>, a seguir denominado apenas de
            <strong>EMPREGADO</strong>, é celebrado o presente <strong>CONTRATO DE EXPERIÊNCIA</strong>, que terá
            vigência a partir da data de início da prestação de serviços, de acordo com as condições a seguir
            especificadas:
        </p>

        <p class="f11 text-justify">
            <strong>1ª</strong> – Fica o <strong>EMPREGADO</strong> admitido no quadro de funcionários da
            <strong>EMPREGADORA</strong> para exercer as funções de
            <strong style="text-transform: uppercase">{{ $funcao }}</strong> mediante a remuneração de:
            R$ {{ $admissao->salario }}
            ({{ \App\Models\Sistema::valorPorExtenso($admissao->salario) }}).
            Em circunstância, porém, de ser a função especificada, não importa na intransferibilidade do
            <strong>EMPREGADO</strong> para outro serviço no qual demonstre melhor capacidade de adaptação desde que
            compatível com a sua condição pessoal.
        </p>

        <p class="f11 text-justify">
            <strong>2ª</strong> – O horário de trabalho será anotado na sua ficha de registro e a eventual jornada de
            trabalho por determinação da <strong>EMPREGADORA</strong>, não inovará esse ajuste, permanecendo sempre
            íntegra a obrigação do <strong>EMPREGADO</strong> de cumprir o horário que lhe for determinado,
            observando o limite legal.
        </p>

        <p class="f11 text-justify">
            <strong>3ª</strong> – Obriga-se também o <strong>EMPREGADO</strong> a prestar serviços em horas
            extraordinárias, sempre que lhe for determinado pela <strong>EMPREGADORA</strong>, na forma prevista em
            Lei. Na hipótese desta faculdade pela <strong>EMPREGADORA</strong> o <strong>EMPREGADO</strong> receberá
            as horas extraordinárias com o acréscimo legal, salvo a ocorrência de compensação, com a consequente
            redução da jornada de trabalho em outro dia, o que desde já se convenciona com a validade de compensação
            no prazo de 6 meses, conforme art. 59, §5º da CLT, restando instituído o banco de horas na empresa
            aplicável ao contrato de presente trabalho, ressalvando-se hipótese de maior prazo em convenção ou acordo
            coletivo de trabalho.
        </p>

        <p class="f11 text-justify">
            <strong>4ª</strong> – Aceita o <strong>EMPREGADO</strong>, expressamente, a condição de prestar serviços
            em qualquer dos turnos de trabalho, isto é, tanto durante o dia, como a noite, desde que sem
            simultaneidade, observadas as prescrições legais reguladoras do assunto, quanto a remuneração.
        </p>

        <p class="f11 text-justify">
            <strong>5ª</strong> – Fica disposto nos termos que dispõe o parágrafo primeiro do artigo 469, da
            Consolidação das leis de trabalho, que o <strong>EMPREGADO</strong> acatará emanada da
            <strong>EMPREGADORA</strong> para a prestação de serviços tanto na localidade de celebração do
            <strong>CONTRATO DE TRABALHO</strong>, como em qualquer outra cidade, capital ou vila do território
            nacional, quer essa transferência seja transitória, quer seja definitiva.
        </p>

        <p class="f11 text-justify">
            <strong>6ª</strong> – No ato da assinatura desse contrato, o <strong>EMPREGADO</strong> recebe o
            Regulamento Interno da Empresa cujas cláusulas fazem parte do contrato de trabalho, e a violação de
            qualquer uma delas implicará em sanção, cuja graduação dependerá da gravidade da mesma, culminado com a
            rescisão do contrato.
        </p>

        <p class="f11 text-justify">
            <strong>7ª</strong> – Em caso de dano causado pelo <strong>EMPREGADO</strong>, fica a
            <strong>EMPREGADORA</strong>, autorizada a efetivar o desconto da importância correspondente ao prejuízo,
            o qual fará, com fundamento no parágrafo 1º do artigo 462 da consolidação das Leis de Trabalho, já que
            essa possibilidade fica expressamente prevista em contrato, com a limitação de até 30% do salário por
            prestação de ressarcimento até que este seja efetivamente quitado.
        </p>

        <p class="f11 text-justify">
            <strong>8ª</strong> – O <strong>EMPREGADO</strong> autoriza de forma gratuita eventual utilização da sua
            imagem e voz em sites, redes sociais e demais veículos de publicidade da <strong>EMPREGADORA</strong>.
        </p>

        <p class="f11 text-justify">
            <strong>9ª</strong> – O <strong>EMPREGADO</strong> se obriga a guardar confidencialidade e sigilo
            <strong>QUAISQUER</strong> informações (Técnicas Administrativas ou Gerenciais) e dados dos quais tenha
            ciência em razão da relação de emprego ora constituída, para gerar benefício próprio exclusivo e/ou
            unilateral, presente ou futuro, ou para o uso de terceiros de forma que a quebra da confidencialidade,
            reconhecendo, ainda, sua obrigação de: não efetuar nenhuma gravação ou cópia da documentação a que tiver
            acesso; não apropriar para mim ou para outrem de <strong>QUALQUER</strong> material técnico, gerencial
            ou administrativo que venha a ser disponível; não repassar o conhecimento das informações,
            responsabilizando-se por todas as pessoas que vierem a ter acesso às informações, por seu intermédio, e
            obrigando-se, assim, a ressarcir a ocorrência de qualquer dano e/ou prejuízo oriundo de uma eventual
            quebra de sigilo ou confidencialidade de todas as informações fornecidas; Cuidar para que as informações
            confidenciais fiquem restritas ao conhecimento tão somente das pessoas que estejam diretamente
            envolvidos nas discussões, análises, reuniões e negócios, devendo cientificá-los da existência deste
            Termo e da natureza confidencial destas informações. O <strong>EMPREGADO</strong> fica, desde já, ciente
            que a quebra da presente cláusula acarretará a rescisão do contrato de trabalho por justa causa, bem como
            se opera a responsabilização deste em reparação por perdas e danos em favor da
            <strong>EMPREGADORA</strong>.
        </p>

        <p class="f11 text-justify">
            <strong>10ª</strong> – Os Dados Pessoais e os Dados Pessoais sensíveis coletados neste Contrato de
            trabalho serão tratados conforme as hipóteses do artigo 7º, da Lei 13.709/2018 (Lei Geral de Proteção de
            Dados Pessoais), com fundamentos nos incisos:<br>
            II - para o cumprimento de obrigação legal ou regulatória pelo controlador;<br>
            V - quando necessário para a execução de contrato ou de procedimentos preliminares relacionados a
            contrato do qual seja parte o titular, a pedido do titular dos dados;<br>
            VI - para o exercício regular de direitos em processo judicial, administrativo ou arbitral, esse último
            nos termos da Lei nº 9.307, de 23 de setembro de 1996 (Lei de Arbitragem).
        </p>

        <p class="f11 text-justify">
            @if(!$experiencia)
                <strong>11ª</strong> – O presente contrato vigorará por tempo indeterminado.
            @else
                <strong>11ª</strong> – O presente contrato vigerá durante {{ $experiencia[0] }} dias, com início em
                {{ $dataInicio }} e término em {{ $dataTermino }}, podendo ser prorrogado por mais
                {{ $experiencia[1] }} dias, sendo celebrado para as partes verificarem reciprocamente, a conveniência
                ou não de se vincularem em caráter definitivo a um contrato de trabalho. A empresa passando a
                conhecer as aptidões do <strong>EMPREGADO</strong> verificando se o ambiente e os métodos de
                trabalho atendem à sua conveniência.
            @endif
        </p>

        <p class="f11 text-justify">
            Fica estabelecido que, findo o prazo acima, este contrato poderá ser prorrogado ou rescindido,
            independente de aviso prévio, o qual já se acha convencionado no presente ajuste, nada podendo ser
            reclamado fora do presente acordo e após o prazo fixado para o mesmo.
        </p>

        <p class="f11 text-justify">
            Na hipótese deste ajuste transformar-se em contrato de prazo indeterminado, pelo descurso de tempo,
            continuarão em plena vigência as cláusulas de 1 (um) a 11 (onze), enquanto durarem as relações do
            <strong>EMPREGADO</strong> com a <strong>EMPREGADORA</strong>.
        </p>

        <p class="f11 text-justify">
            E por estarem de pleno acordo, as partes contratantes, assinam o presente Contrato de Experiência em 2
            vias, ficando a primeira em poder da <strong>EMPREGADORA</strong>, e a segunda com o
            <strong>EMPREGADO</strong>, que dela dará o competente recibo.
        </p>

        <p class="f11" style="margin-top: 18pt;">
            {{ $cidadeAssinatura }}, {{ $dataInicio }}.
        </p>

        <div class="f11" style="line-height: 15pt; text-align: center; margin-top: 28pt;">
            <div style="width: 48%; float: left;">
                <hr style="width: 90%; margin-left: 5%; border: none; border-top: 1px solid #333;">
                <strong>EMPREGADO</strong><br>
                {{ $nomeColaborador }}
            </div>
            <div style="width: 48%; float: right;">
                <hr style="width: 90%; margin-left: 5%; border: none; border-top: 1px solid #333;">
                <strong>EMPREGADOR</strong><br>
                {{ $dados['dados_empresa']['razao_social'] }}
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="f11" style="line-height: 15pt; text-align: center; margin-top: 36pt;">
            <div style="width: 48%; float: left;">
                <hr style="width: 90%; margin-left: 5%; border: none; border-top: 1px solid #333;">
                Testemunha
            </div>
            <div style="width: 48%; float: right;">
                <hr style="width: 90%; margin-left: 5%; border: none; border-top: 1px solid #333;">
                Testemunha
            </div>
            <div style="clear: both;"></div>
        </div>

        @if($experiencia)
            <div style="page-break-before: always;"></div>
            <p class="f11 text-justify" style="margin-top: 1cm;">
                <strong>TERMO DE PRORROGAÇÃO</strong>
            </p>
            <p class="f11 text-justify">
                Por mútuo acordo entre as partes, fica o presente contrato de experiência, que deveria vencer em
                {{ $dataTermino }}, prorrogado até {{ $dataProrrogacao }}.
            </p>

            <div class="f11" style="line-height: 15pt; text-align: center; margin-top: 36pt;">
                <div style="width: 48%; float: left;">
                    <hr style="width: 90%; margin-left: 5%; border: none; border-top: 1px solid #333;">
                    <strong>EMPREGADO</strong><br>
                    {{ $nomeColaborador }}
                </div>
                <div style="width: 48%; float: right;">
                    <hr style="width: 90%; margin-left: 5%; border: none; border-top: 1px solid #333;">
                    <strong>EMPREGADOR</strong><br>
                    {{ $dados['dados_empresa']['razao_social'] }}
                </div>
                <div style="clear: both;"></div>
            </div>

            <div class="f11" style="line-height: 15pt; text-align: center; margin-top: 36pt;">
                <div style="width: 48%; float: left;">
                    <hr style="width: 90%; margin-left: 5%; border: none; border-top: 1px solid #333;">
                    Testemunha
                </div>
                <div style="width: 48%; float: right;">
                    <hr style="width: 90%; margin-left: 5%; border: none; border-top: 1px solid #333;">
                    Testemunha
                </div>
                <div style="clear: both;"></div>
            </div>
        @endif
    </div>
@stop
@push('style')
    <style type="text/css">
        .f11 {
            font-size: 10pt !important;
            line-height: 14pt;
        }
    </style>
@endpush
