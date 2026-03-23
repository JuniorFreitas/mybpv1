@extends('layouts.pdf_filial')
@section('title','Relatorio de ponto')
@section('conteudo')
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
    <div style="margin-left: 2.5%; width: 93%; padding-bottom: 28px;">
        <p class="f11"
           style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase"><br>
            <strong>CONTRATO DE TRABALHO A TÍTULO DE EXPERIÊNCIA</strong><br>
            (Alínea ‘c’ do § 2º do artigo 443 da CLT)<br>
        </p>
        <p class="f11 text-justify">
            Pelo presente Instrumento Particular de Contrato de Trabalho a Título de Experiência, que de um lado celebra
            a
            empresa <strong>{{$dados['dados_empresa']['razao_social']}}</strong>, sociedade empresária limitada,
            inscrita
            no CNPJ/MF sob n.º
            <strong>{{$dados['dados_empresa']['cnpj']}}</strong>, com atividade
            localizada: {{$dados['dados_empresa']['endereco_completo']}},
            doravante designada simplesmente <strong>EMPREGADORA</strong> e de outro
            <strong>{{$dados['dados_colaborador']->Curriculo->nome}}</strong>
            portador(a) Carteira Profissional
            n.º {{$dados['dados_colaborador']->Admissao->DadosAdmissoes? $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_numero : 'NÃO INFORMADO'}}
            ,
            Série {{$dados['dados_colaborador']->Admissao->DadosAdmissoes? $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_serie: 'NÃO INFORMADO'}}
            a seguir chamado apenas <strong>EMPREGADO</strong>, é celebrado o
            presente <strong>CONTRATO DE EXPERIÊNCIA</strong>, que terá vigência a partir da data de início da prestação
            de
            serviços, de
            acordo com as condições a seguir especificadas:

        </p>
        <p class="f11 text-justify">
            <strong>1ª (Função/Salário)</strong> – Fica o <strong>EMPREGADO</strong> admitido no quadro de funcionários
            da
            <strong>EMPREGADORA</strong> para exercer as funções de <span
                style="text-transform: uppercase; font-weight: bold">{{ $dados['dados_colaborador']->VagaAberta->VagaSelecionada->nome }}</span>
            mediante a remuneração mensal de R$ {{ $dados['dados_colaborador']->Admissao->salario }}
            ({{\App\Models\Sistema::valorPorExtenso($dados['dados_colaborador']->Admissao->salario)}}). A
            circunstância, porém, de ser
            a função especificada não importa na intransferibilidade do <strong>EMPREGADO</strong> para outro serviço,
            no
            qual demonstre melhor capacidade de adaptação desde que compatível com sua condição pessoal.
        </p>
        <p class="f11 text-justify">
            <strong>2ª (Horário)</strong> – O horário de trabalho será no horário administrativo ou, especialmente, em
            horário de turno cumprindo jornada de 44h/semanais, se comprometendo a trabalhar em regime de compensação e
            de
            prorrogação de horas, quando necessário, e a eventual redução da jornada, por determinação da <strong>EMPREGADORA</strong>,
            não inovará este ajuste, permanecendo sempre íntegra a obrigação do <strong>EMPREGADO</strong> de cumprir o
            horário que lhe for determinado, observando o limite legal.
        </p>
        <p class="f11 text-justify">
            <strong>3ª (Horas Extras)</strong> – Obriga-se também o <strong>EMPREGADO</strong> a prestar serviços em
            horas
            extraordinárias, sempre que lhe for determinado pela <strong>EMPREGADORA</strong>, na forma prevista em Lei.
            Na hipótese desta faculdade pela <strong>EMPREGADORA</strong>, o <strong>EMPREGADO</strong> receberá as
            horas
            extraordinárias com o acréscimo legal, ou será tratado através de acordo de compensação de
            horas, com a consequente redução da jornada de trabalho em outro dia. Horas extras não autorizadas não serão
            remuneradas e estarão passíveis de medidas administrativas.
        </p>
        <p class="f11 text-justify">
            <strong>4ª (Localidade)</strong> – Fica ajustado nos termos que dispõe o Parágrafo 10° do Artigo 469, da
            Consolidação das Leis de Trabalho, que o <strong>EMPREGADO</strong> acatará ordem emanada da
            <strong>EMPREGADORA</strong> para prestação de serviços tanto da localidade de
            celebração do Contrato de Trabalho, como qualquer outra cidade, quer essa transferência seja transitória ou
            definitiva.
        </p>
        <p class="f11 text-justify">
            <strong>5ª (Dano)</strong> – Em caso de dano causado pelo <strong>EMPREGADO</strong>, fica a
            <strong>EMPREGADORA</strong>, autorizada a efetivar o desconto da importância correspondente ao prejuízo, o
            qual
            fará, com fundamento no Parágrafo único do artigo 462 da Consolidação das Leis de Trabalho, já que essa
            possibilidade fica expressamente prevista em contrato.
        </p>
        <p class="f11 text-justify">
            @if(!$dados['dados_colaborador']->Admissao->pExperiencia())
                <strong>6ª (Duração)</strong> – O presente contrato vigorará por tempo inderterminado.
            @else
                <strong>6ª (Duração)</strong> – O presente contrato vigorará
                por {{$dados['dados_colaborador']->Admissao->pExperiencia()[0]}}
                ({{ \MasterTag\GExtenso::numero($dados['dados_colaborador']->Admissao->pExperiencia()[0]) }})
                dias, podendo este ser
                prorrogado ou não por mais {{$dados['dados_colaborador']->Admissao->pExperiencia()[1]}}
                ({{ \MasterTag\GExtenso::numero($dados['dados_colaborador']->Admissao->pExperiencia()[1]) }})
                dias sendo celebrado para
                as
                partes verificarem reciprocamente,
                a conveniência ou não de se vincularem em caráter definitivo a um Contrato de Trabalho. A empresa
                passando a
                conhecer as aptidões do <strong>EMPREGADO</strong> e suas qualidades pessoais e morais; o
                <strong>EMPREGADO</strong> verificando se o ambiente e os métodos de trabalho atendem à sua
                conveniência.
            @endif
        </p>
        <p class="f11 text-justify">
            <strong>7ª (EPIs)</strong> – A <strong>EMPREGADORA</strong> concederá ao colaborador, uniformes e EPIs
            padronizados, sendo de uso diário obrigatório, objetivando manter imagem e identidade da empresa como também
            evitar eventuais acidentes. Os mesmos deverão ser devolvidos no ato do desligamento ou quando ocorrer
            substituição por qualquer defeito decorrente do uso normal.
        </p>
        <p class="f11 text-justify">
            <strong>8ª</strong> As intervenções decorrentes das atribuições do Empregado, originadas de pesquisa pura e
            aplicada, bem como aquelas oriundas de estudos com a utilização das instalações e equipamentos do local de
            trabalho, são de propriedade exclusiva da <strong>EMPREGADORA</strong>.
        </p>
        <p class="f11 text-justify">
            <strong>9ª (Férias)</strong> – Com fundamento no Parágrafo 1° do artigo 134 CLT, concorda o
            <strong>EMPREGADO</strong> que <strong>EMPREGADORA</strong> fracione as férias anuais em 03 períodos
            concessivos, na medida em que um dos períodos de férias não poderá ser inferior a quatorze dias corridos e
            os
            demais não poderão ser inferior a cinco dias corridos.
        </p>
        <p class="f11 text-justify">
            <strong>10ª</strong> Fica ainda autorizado, de livre e espontânea vontade, para os mesmos fins, a cessão de
            direitos da veiculação das imagens em todo e qualquer material entre imagens de vídeo, fotos e documentos,
            para ser utilizada pela empresa <strong>CONTRATANTE</strong> destinado a divulgação ao público em geral não
            recebendo para tanto qualquer tipo de remuneração.
        </p>
        <p class="f11 text-justify">
            <strong>11ª (Rescisão)</strong> – Opera-se a rescisão do presente contrato pela decorrência do prazo supra
            ou por vontade de uma das partes; rescindindo-se por vontade do <strong>EMPREGADO</strong> ou pela <strong>EMPREGADORA</strong>
            com justa causa, nenhuma indenização é devida; rescindindo-se antes do prazo, pela
            <strong>EMPREGADORA</strong>,
            fica esta obrigada a pagar 50% dos salários devidos até o final (metade do tempo combinado restante), nos
            termos do Artigo 479 da CLT, sem prejuízo do disposto no Regimento do FGTS.
        </p>
        <p class="f11 text-justify">
            <strong>12ª (Proteção de Dados Pessoais)</strong> – O <strong>EMPREGADO</strong> está ciente de que informa
            os dados contidos na Ficha Registro de Funcionário à <strong>EMPREGADORA</strong>, os quais são necessários
            para a execução do contrato de trabalho de experiência.
        </p>
        <p class="f11 text-justify">
            <strong>12.1</strong> - Durante a vigência do contrato, outros dados pessoais poderão ser gerados, os quais
            também são necessários para a execução do contrato e para as finalidades mencionados na cláusula 12.3 e, na
            hipótese de ausência de previsão, por meio de formulário próprio autorizativo.
        </p>
        <p class="f11 text-justify">
            <strong>12.2</strong> - O <strong>EMPREGADO</strong> ao assinar o presente contrato, está ciente que a
            empresa contratante poderá solicitar dados extras, para o cumprimento de uma obrigação legal, uso de
            sistemas de controle de pessoal, pagamento de impostos e informações sociais aos órgãos públicos, bem como,
            para a execução do presente contrato.
        </p>
        <p class="f11 text-justify">
            <strong>12.3</strong> - Os dados fornecidos pelo <strong>EMPREGADO</strong> serão utilizados para as
            seguintes
            finalidades:<br>
            a. Funcionalidades de sistemas, softwares, plataformas, cadastros e normas internas;<br>
            b. Cadastro para eventuais notificações, comunicações formais entre empregado e empregadora no âmbito da
            gestão
            contratual por parte da <strong>EMPREGADORA</strong>;<br>
            c. Atendimento das demandas administrativas e operacionais no setor de RH;<br>
            d. Atendimento à pedidos de autoridades públicas, prestação de contas e informações sociais obrigadas em
            lei.
        </p>
        <p class="f11 text-justify">
            <strong>12.4</strong> - A coleta de dados se dá através de sua inserção pela <strong>EMPREGADORA</strong> no
            momento da contratação pelo setor de RH.
        </p>
        <p class="f11 text-justify">
            Parágrafo Primeiro – Os dados da cláusula 12.3 serão gerados no decorrer do relacionamento entre as partes e
            do
            cumprimento do presente contrato.
        </p>
        <p class="f11 text-justify">
            Parágrafo segundo – A qualquer tempo, a <strong>EMPREGADORA</strong> pode requerer a correção ou atualização
            dos
            dados pessoais coletados, tendo o <strong>EMPREGADO</strong> de pleno conhecimento e aceite deste parágrafo.
        </p>
        <p class="f11 text-justify">
            <strong>12.5</strong> - Os dados pessoais coletados podem ser acessados a qualquer momento pela <strong>EMPREGADORA</strong>
            através de solicitação no site/e-mail/plataforma, solicitação do setor de RH, solicitação do setor contábil
            e
            quantas outras solicitações sejam necessárias para o cumprimento do presente contrato, estando o empregado
            de
            pleno acordo com tais termos.
        </p>
        <p class="f11 text-justify">
            <strong>12.6</strong> - O tratamento dos dados será realizado enquanto o <strong>EMPREGADO</strong> estiver
            trabalhando para a
            empresa contratante, ou seja, enquanto durar o contrato de trabalho.
        </p>
        <p class="f11 text-justify">
            <strong>12.7</strong> - A <strong>EMPREGADORA</strong> armazenará os dados relativos ao
            <strong>EMPREGADO</strong> por até 5 anos, após o encerramento do contrato, prorrogáveis por igual prazo, a
            fim
            de atender ao prazo prescricional de discussões judiciais que estão em Ficha Registro do Empregado atrelada
            à
            este contrato.
        </p>
        <p class="f11 text-justify">
            <strong>12.8</strong> - Os dados serão tratados pela própria <strong>EMPREGADORA</strong>, o qual poderá
            realizar a transferência de dados pessoais a terceiros visando o melhor desempenho das atividades vinculadas
            ao
            serviço contratado, assim como para gestão, segurança, armazenamento e backup em nuvem. Poderá, ainda,
            quando
            solicitado, compartilhar os dados pessoais do <strong>EMPREGADO</strong> com autoridades e entidades
            governamentais em função de exigências legais ou na defesa dos seus interesses no caso de conflitos,
            judiciais
            ou administrativos.
        </p>
        <p class="f11 text-justify">
            Parágrafo primeiro – A <strong>EMPREGADORA</strong> garante, desde já, que os terceiros
            contratados/subcontratados adotam medidas de segurança adequadas aos princípios e diretrizes previstos na
            LGPD e
            em conformidade com as boas práticas de privacidade e segurança da informação.
        </p>
        <p class="f11 text-justify">
            Parágrafo segundo – A <strong>EMPREGADORA</strong> cumprirá os princípios de adequação, necessidade e
            finalidade, e limitará internamente o acesso aos dados aos colaboradores estritamente necessários ao
            atendimento
            da finalidade.
        </p>
        <p class="f11 text-justify">
            <strong>12.9</strong> - Os dados coletados são de acesso exclusivo da <strong>EMPREGADORA</strong>, e não
            serão
            vendidos ou cedidos a terceiros sem expresso consentimento pelo <strong>EMPREGADO</strong>. A <strong>EMPREGADORA</strong>
            se compromete em manter seguros os dados pessoais coletados, e realizará, quando necessário, relatório para
            avaliar o impacto dos tratamentos.
        </p>
        <p class="f11 text-justify">
            Parágrafo único – A <strong>EMPREGADORA</strong>, ao prezar pela privacidade do <strong>EMPREGADO</strong>,
            garante que os funcionários e terceiros envolvidos direta ou indiretamente nas operações de tratamento de
            dados
            pessoais estão cobertos por um dever de confidencialidade.
        </p>
        <p class="f11 text-justify">
            <strong>12.10</strong> - Se, apesar das medidas de segurança implementadas, houver vazamento de dados que
            possa
            acarretar risco ou dano relevante aos titulares, a <strong>EMPREGADORA</strong> irá comunicar o <strong>EMPREGADO</strong>
            e a Autoridade Nacional de Proteção de Dados, nos termos do art. 48 da Lei nº 13.709/2018, e tomará as
            medidas
            necessárias para reduzir os danos e o risco de recorrência. Entende-se como vazamento a destruição, perda,
            alteração ou acesso não autorizados, seja de forma acidental ou deliberada, a dados pessoais.”
        </p>
        <p class="f11 text-justify">
            <strong>13ª (Vigência)</strong> – Na hipótese deste ajuste transformar-se em contrato de prazo
            indeterminado,
            pelo decurso do tempo, continuarão em plena vigência às clausulas de 01 (Um) a 12 (Doze), enquanto durarem
            as
            relações do <strong>EMPREGADO</strong> com a <strong>EMPREGADORA</strong>.
        </p>
        <p class="f11 text-justify">
            E por estarem de pleno acordo, as partes contratantes, assinam o presente contrato de experiência em duas
            vias,
            ficando a primeira em poder da <strong>EMPREGADORA</strong>, e a segunda com o <strong>EMPREGADO</strong>
            que
            dela dará o competente recibo.
        </p><br>
        <br><br>
        <div class="f11" style="line-height: 26pt">
            São
            Luís/MA, {{ (new \MasterTag\DataHora($dados['dados_colaborador']->Admissao->data_admissao))->dataCompletaExt() }}
            .
            <br>
            <br>
            <br>
        </div>
        <div class="f11" style="line-height: 15pt;text-align: center">
            <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
            {{$dados['dados_empresa']['razao_social']}}
            <br>
            <br>
            <br>
            <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
            {{$dados['dados_colaborador']->nome}}
        </div>

        <div class="f11" style="line-height: 15pt;text-align: left">
            <p class="f11" style="margin-bottom: 26pt; margin-top: 30pt">
                TESTEMUNHAS:
            </p>
            <div style="width: 48%; float: left">
                <hr style="  border:none; border-top: 1px solid #333">
            </div>
            <div style="width: 48%; float: right">
                <hr style="  border:none; border-top: 1px solid #333">
            </div>
        </div>

        <div style="page-break-before: always;"></div>
        <div class="f11" style="line-height: 15pt;text-align: left">
            <p class="f11 text-justify" style="line-height: 25pt;">
                <br><br>
                <strong>TERMO DE PRORROGAÇÃO</strong><br><br><br>
                Por mútuo acordo entre as partes, fica o presente contrato de experiência, que deveria vencer em,
                _______/_______/______________ prorrogado até _______/_______/______________.
                <br>
                <br><br><br>
                São Luís, ______________ de ____________________________ de ______________.
                <br><br><br><br>
            </p>

            <div class="f11" style="line-height: 15pt;text-align: center">
                <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
                {{$dados['dados_empresa']['razao_social']}}
                <br>
                <br>
                <br>
                <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
                {{$dados['dados_colaborador']->Curriculo->nome}}
            </div>

            <p class="f11" style="margin-bottom: 26pt; margin-top: 30pt">
                TESTEMUNHAS:
            </p>
            <div style="width: 48%; float: left">
                <hr style="  border:none; border-top: 1px solid #333">
            </div>
            <div style="width: 48%; float: right">
                <hr style="  border:none; border-top: 1px solid #333">
            </div>
        </div>

        <br><br><br><br>

    </div>
@stop
@push('style')
    <style type="text/css">

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }

    </style>
@endpush
