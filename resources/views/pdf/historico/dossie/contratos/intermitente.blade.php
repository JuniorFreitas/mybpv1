@extends('layouts.pdf_filial')
@section('title','Relatorio de ponto')
@section('conteudo')
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div style="margin-left: 9px; width: 95%">
        <p class="f12"
           style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
            <strong>CONTRATO DE TRABALHO INTERMITENTE</strong>
        </p>
        <p class="f12 text-justify">
            Por este instrumento particular, de um lado <strong>{{$dados['dados_empresa']['razao_social']}}</strong>,
            pessoa jurídica de direito
            privado, inscrita no CNPJ/MF sob o nº <strong>{{$dados['dados_empresa']['cnpj']}}</strong>, estabelecida
            no(a) {{$dados['dados_empresa']['endereco_completo']}}, doravante denominada <strong>EMPREGADOR(A)</strong>,
            e,
            de outro, {{$dados['dados_colaborador']->Curriculo->nome}}, inscrito(a) no CPF sob o nº {{$dados['dados_colaborador']->Curriculo->cpf}}, doravante, ,denominado(a)
            <strong>EMPREGADO(A)</strong>, firmam o Contrato de Trabalho na Modalidade Intermitente, nos termos da Lei
            n°
            13.467/2017, com vigência, a partir
            do {{ (new \MasterTag\DataHora($dados['dados_colaborador']->Admissao->data_admissao))->dataCompletaExt() }},
            fundamentado em todo o teor da Consolidação das Leis do Trabalho, nos termos seguintes.
        </p><br>

        <p class="f12 text-justify">
            Cláusula 1ª - O(a) EMPREGADO(a) é contratado(a) na modalidade de EMPREGO INTERMITENTE,
            conforme artigo 443 e seu parágrafo 3º, e artigo 452-A e seus parágrafos, da CLT.
        </p><br>

        <p class="f12 text-justify">
            Parágrafo único: O(A) EMPREGADO(A) tem subordinação jurídica na relação de emprego, contudo, no presente
            contrato não há continuidade, pois ocorrerá a alternância dos períodos com a prestação de serviço, portanto,
            nos
            períodos inativos o(a) EMPREGADO(A) não estará à disposição do seu EMPREGADOR(A), tão pouco receberá
            qualquer
            salário ou remuneração, podendo o(a) EMPREGADO(A) prestar serviços a outros contratantes.
        </p><br>

        <p class="f12 text-justify">
            Cláusula 2ª - O(A) EMPREGADO(A) obriga-se a prestar seus serviços no quadro de funcionários do
            EMPREGADOR(A),
            para exercer as funções de {{  mb_strtoupper($dados['dados_colaborador']->Admissao->funcao) }} A com todas as
            atribuições
            que lhe são peculiares.
        </p><br>

        <p class="f12 text-justify">
            Cláusula 3ª - O(A) EMPREGADO(A) receberá o salário de {{ $dados['dados_colaborador']->Admissao->salario }}
            ({{\App\Models\Sistema::valorPorExtenso($dados['dados_colaborador']->Admissao->salario)}}) por hora
            trabalhada, nos horários estabelecidos na convocação e o pagamento dar-se-á de forma mensal, devendo ser
            pago
            até o quinto dia útil do mês seguinte ao trabalhado, de acordo com o previsto no § 1 do artigo 459 da CLT.
        </p><br>

        <p class="f12 text-justify">
            Cláusula 4ª - O EMPREGADOR(A) convocará o(a) EMPREGADO(A) por meio de comunicação eficaz, informando a
            jornada
            solicitada, com antecedência de até 3 dias úteis antes, considerando o Art. 34 da Portaria 671 como
            possibilidade de continuidade da convocação,
        </p><br>

        <p class="f12 text-justify">
            Parágrafo único: Recebida a comunicação o(a) o empregado terá o prazo de até um dia útil para responder ao
            chamado, presumindo-se, no silêncio, a recusa.
        </p><br>

        <p class="f12 text-justify">
            Cláusula 5ª - Aceita a proposta, a parte que, sem justo motivo, descumprir o ajustado, pagará à outra parte,
            no
            prazo de trinta dias, multa de 50% (cinquenta por cento) da remuneração que seria devida, permitida a
            compensação em igual prazo.
        </p><br>

        <p class="f12 text-justify">
            Cláusula 6ª - Aceita o EMPREGADO(A), expressamente, a condição de prestar serviços em qualquer dos turnos de
            trabalho, isto é, tanto durante o dia como à noite, desde que sem simultaneidade, observadas as prescrições
            legais reguladoras do assunto, quanto à remuneração.
        </p><br>

        <p class="f12 text-justify">
            Cláusula 7ª - Em caso de dano causado pelo EMPREGADO(A), de qualquer tipo, incluindo danos físicos ao
            veículo,
            arranhão, batida de veículo da empresa ou de terceiros envolvidos, fica a EMPREGADOR(A) autorizada a
            efetivar o
            desconto da importância correspondente ao prejuízo, o qual fará independente do valor já que essa
            possibilidade
            fica expressamente prevista em contrato, nos § 1º do artigo 462 da CLT.
        </p><br>

        <p class="f12 text-justify">
            Cláusula 8ª – O EMPREGADO(A) compromete-se também, a respeitar o regulamento da empresa, mantendo conduta
            irrepreensível no ambiente de trabalho, confidencialidade e postura, constituindo motivos para imediata
            dispensa
            do empregado, além dos previstos em lei, o desacato moral ou agressão física ao EMPREGADOR(A), ao
            administrador ou
            a pessoa de seus respectivos companheiros de trabalho, a embriagues ou briga em serviço. Destaca-se também
            que é
            proibido o uso de aparelhos celulares no horário de trabalho, excetos os concedidos pela empresa para
            exercício
            das atividades laborais, ciente de que a empresa não se responsabiliza por furtos e perdas desses aparelhos
            no
            local de trabalho.
        </p><br>

        <p class="f12 text-justify">
            Cláusula 9ª – A EMPREGADOR(A) concederá ao colaborador, uniformes e EPIs padronizados, sendo de uso diário
            obrigatório, objetivando manter imagem e identidade da empresa como também evitar eventuais acidentes. Os
            mesmos
            deverão ser devolvidos no ato do desligamento ou quando ocorrer substituição por qualquer defeito decorrente
            do
            uso normal.
        </p><br>

        <p class="f12 text-justify">
            Cláusula 10ª – Pelo presente instrumento, o EMPREGADO(A) autoriza o EMPREGADOR(A) a, em razão do contrato de
            trabalho,
            dispor dos dados pessoais e dados pessoais sensíveis do EMPREGADO(A), de acordo com os artigos 7° e 11 da
            Lei n°
            13.709/2018, conforme disposto neste termo:
        </p><br>

        <p class="f12 text-justify">
            Cláusula 10ª – Pelo presente instrumento, o EMPREGADO(A) autoriza o EMPREGADOR(A) a, em razão do contrato de
            trabalho,
            dispor dos dados pessoais e dados pessoais sensíveis do EMPREGADO(A), de acordo com os artigos 7° e 11 da
            Lei n°
            13.709/2018, conforme disposto neste termo:<br>
            Dados Pessoais
        </p><br>

        <p class="f12 text-justify">
            a)- O Titular autoriza a Controladora a realizar o tratamento, ou seja, a utilizar os seguintes dados
            pessoais,
            para os fins que serão relacionados na cláusula segunda:<br>
            b)- Nome completo;<br>
            c)- Data de nascimento;<br>
            d)- Número e imagem da Carteira de Identidade (RG);<br>
            e)- Número e imagem do Cadastro de Pessoas Físicas (CPF);<br>
            f)- Número e imagem do Título de Eleitor;<br>
            g)- Número e imagem do Certificado de Reservista;<br>
            h)- Número e imagem da Carteira Nacional de Habilitação (CNH) (quando necessário para a função
            contratada);<br>
            i)- Número e Imagem do cartão de vale transporte (quando utilizado pelo empregado);<br>
            j)- Número e imagem do Programa de Integração Social (PIS); <br>
            k)- CTPS física e/ou digital; <br>
            l)- Fotografia 3×4; <br>
            m)- Imagem da Certidão de Casamento ou Declaração de União Estável; <br>
            n)- Imagem do Diploma de _________ (Nível de instrução ou escolaridade); <br>
            o)- Endereço completo; <br>
            p)- Números de telefone, WhatsApp e endereços de e-mail; <br>
            q)- Banco, agência e número de contas bancárias; <br>
            r)- Nome de usuário e senha específicos para uso dos serviços da Controladora; <br>
            t)- Comunicação, verbal e escrita, mantida entre o Titular e o Controlador; <br>
            u)- Exames e atestados médicos, especialmente admissionais, periódicos, incluídos de retorno por afastamento
            superior a 30 dias em caso de doença, acidente ou parto, de mudança de função, demissionais e ainda aqueles
            que
            atestem doença ou acidente; <br>
            v)- Certidão de nascimento dos filhos menores de 14 anos, Carteira de vacinação dos menores de 7 anos, e
            atestado de matrícula e frequência escolar semestral dos maiores de 4 anos; <br>
            x)- Número e Imagem da Carteira Profissional; <br>
            y)- e outros documentos específicos para a função, por exemplo: Documento de filiação a Sindicato; Número e
            Imagem da Carteira Profissional, etc.).
        </p><br>

        <p class="f12 text-justify">
            Parágrafo Primeiro (Finalidade do Tratamento dos Dados): O EMPREGADO(A) autoriza que o EMPREGADOR(A) utilize
            seus
            dados pessoais e seus dados pessoais sensíveis listados neste termo para as seguintes finalidades:
        </p><br>

        <p class="f12 text-justify">
            a)- Permitir que o EMPREGADOR(A) identifique e entre em contato com o titular, em razão do contrato de
            trabalho;
            <br>
            b)- Para cumprimento de obrigações decorrentes da legislação, principalmente trabalhista e previdenciária,
            incluindo o disposto em Acordo ou Convenção Coletiva da categoria da Controladora; <br>
            c)- Para procedimentos de admissão e execução do contrato de trabalho, inclusive após seu término; <br>
            d)- Para cumprimento, pelo EMPREGADOR(A), de obrigações impostas por órgãos de fiscalização; <br>
            e)- Quando necessário para a executar um contrato, no qual seja parte o titular; <br>
            g)- A pedido do titular dos dados; <br>
            i)- Para o exercício regular de direitos em processo judicial, administrativo ou arbitral; <br>
            j)- Para a proteção da vida ou da incolumidade física do titular ou de terceiros; <br>
            k)- Para a tutela da saúde, exclusivamente, em procedimento realizado por profissionais de saúde, serviços
            de
            saúde ou autoridade sanitária; <br>
            l)- Quando necessário para atender aos interesses legítimos do EMPREGADOR(A) ou de terceiros, exceto no caso
            de
            prevalecerem direitos e liberdades fundamentais do titular que exijam a proteção dos dados pessoais; <br>
            m)- Para contratação de serviços de terceiros, tais como exemplificativamente, empresas de vale alimentação,
            plano de saúde, plano odontológico, previdência privada, seguro de vida, etc., de modo que somente serão
            repassados para a empresa contratada os dados pessoais de identificação do titular; <br>
            n)- Permitir que o EMPREGADOR(A) utilize esses dados para a contratação e prestação de serviços diversos dos
            inicialmente ajustados, desde que o Titular também demonstre interesse em contratar novos serviços.
        </p><br>

        <p class="f12 text-justify">
            Parágrafo Segundo: Caso seja necessário o compartilhamento de dados com terceiros que não tenham sido
            relacionados nesse termo ou qualquer alteração contratual posterior, será ajustado termo de consentimento
            para
            este fim (§ 6° do artigo 8° e § 2° do artigo 9° da Lei n° 13.709/2018).
        </p><br>

        <p class="f12 text-justify">
            Parágrafo Terceiro: Em caso de alteração na finalidade, que esteja em desacordo com o consentimento
            original, o
            EMPREGADOR(A) deverá comunicar o EMPREGADO(A), que poderá revogar o consentimento, conforme previsto no § 7º
            da
            presente Cláusula.
        </p><br>

        <p class="f12 text-justify">
            Parágrafo Quarto (Compartilhamento de Dados): A EMPREGADOR(A) fica autorizada a compartilhar os dados
            pessoais do
            EMPREGADO(A) com outros agentes de tratamento de dados, caso seja necessário para as finalidades listadas
            neste
            instrumento, desde que, sejam respeitados os princípios da boa-fé, finalidade, adequação, necessidade, livre
            acesso, qualidade dos dados, transparência, segurança, prevenção, não discriminação e responsabilização e
            prestação de contas.
        </p><br>

        <p class="f12 text-justify">
            Parágrafo Quinto (Responsabilidade pela Segurança dos Dados): A EMPREGADOR(A) se responsabiliza por manter
            medidas
            de segurança, técnicas e administrativas suficientes a proteger os dados pessoais do EMPREGADO e à
            Autoridade
            Nacional de Proteção de Dados (ANPD), comunicando ao EMPREGADO(A), acaso ocorra algum incidente de segurança
            que
            possa acarretar risco ou dano relevante, conforme artigo 48 da Lei n° 13.709/2020.
        </p><br>

        <p class="f12 text-justify">
            Parágrafo Sexto (Término do Tratamento dos Dados): À EMPREGADOR(A), é permitido manter e utilizar os dados
            pessoais do EMPREGADO(A) durante todo o período contratualmente firmado para as finalidades relacionadas
            neste
            termo e ainda após o término da contratação para cumprimento de obrigação legal ou impostas por órgãos de
            fiscalização, nos termos do artigo 16 da Lei n° 13.709/2018.
        </p><br>

        <p class="f12 text-justify">
            Parágrafo Sétimo (Direito de Revogação do Consentimento): O EMPREGADO(A) poderá revogar seu consentimento, a
            qualquer tempo, por e-mail ou por carta escrita, conforme o artigo 8°, § 5°, da Lei n° 13.709/2020. O
            Titular
            fica ciente de que a Controladora poderá permanecer utilizando os dados para as seguintes finalidades:
        </p><br>

        <p class="f12 text-justify">
            a)- Para cumprimento de obrigações decorrentes da legislação trabalhista e previdenciária, incluindo o
            disposto
            em Acordo ou Convenção Coletiva da categoria da Controladora;<br>
            b)- Para procedimentos de admissão e execução do contrato de trabalho, inclusive após seu término;<br>
            c)- Para cumprimento, pela EMPREGADOR(A), de obrigações impostas por órgãos de fiscalização;<br>
            d)- Para o exercício regular de direitos em processo judicial, administrativo ou arbitral;<br>
            e)- Para a proteção da vida ou da incolumidade física do titular ou de terceiros;<br>
            f)- Para a tutela da saúde, exclusivamente, em procedimento realizado por profissionais de saúde, serviços
            de
            saúde ou autoridade sanitária;<br>
            g)- Quando necessário para atender aos interesses legítimos do EMPREGADOR(A) ou de terceiros, exceto no caso
            de
            prevalecerem direitos e liberdades fundamentais do titular que exijam a proteção dos dados pessoais.
        </p><br>

        <p class="f12 text-justify">
            Parágrafo Oitavo (Tempo de Permanência dos Dados Recolhidos): O EMPREGADO(A) fica ciente de que a
            EMPREGADOR(A)
            deverá permanecer com os seus dados pelo período mínimo de guarda de documentos trabalhistas,
            previdenciários,
            bem como os relacionados à segurança e saúde no trabalho, mesmo após o encerramento do vínculo empregatício.
        </p><br>

        <p class="f12 text-justify">
            Parágrafo Nono (Vazamento de Dados ou Acessos Não Autorizados – Penalidades): As partes poderão entrar em
            acordo, quanto aos eventuais danos causados, caso exista o vazamento de dados pessoais ou acessos não
            autorizados, e caso não haja acordo, a EMPREGADOR(A) tem ciência que estará sujeita às penalidades previstas
            no
            artigo 52 da Lei n° 13.709/2018.
        </p><br>
        <p class="f12 text-justify">
            Cláusula 11ª – A EMPREGADOR(A) poderá divulgar o nome, imagem e voz do EMPREGADO(A), para fins
            publicitários,
            propaganda, ou eventos, cedendo, neste ato, o EMPREGADO(A), seu direito de imagem, a título gratuito.
        </p><br>
        <p class="f12 text-justify">
            Parágrafo 1º - Como cessão correlata por afinidade ou conexão, necessária à publicação, promoção e
            divulgação
            nos moldes delineados no caput, por todos os meios midiáticos, o EMPREGADO(A) cede à EMPREGADOR(A), seu
            direito de
            imagem e voz, de forma expressa, coletiva ou individualmente de quaisquer atividades que participar, seja
            audiovisual, seja via internet, redes sociais jornais, folders, periódicos diversos e demais meios de
            comunicação público ou privado ou outra maneira lícita possível, por prazo indeterminado.
        </p><br>

        <p class="f12 text-justify">
            Parágrafo 2º - Pelo presente instrumento, as partes pactuantes acordam que o EMPREGADO(A) não terá qualquer
            participação nos valores decorrentes do uso da imagem e voz ora cedida, para os quais cedem sua imagem, voz
            e
            direitos conexos, bem como não terão direito a qualquer valor pecuniário referente aos ganhos deles
            decorrentes.
        </p><br>

        <p class="f12 text-justify">
            E por estarem de pleno acordo, assinam ambas as partes este contrato, em duas vias de igual teor na presente
            das
            testemunhas abaixo relacionadas.
        </p><br>

        <br><br>
        <div class="f12" style="line-height: 26pt">
            São Luís/MA, {{ (new \MasterTag\DataHora($dados['dados_colaborador']->Admissao->data_admissao))->dataCompletaExt() }}.
            <br>
            <br>
            <br>
        </div>
        <div class="f12" style="line-height: 15pt;text-align: center">
            <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
            {{$dados['dados_empresa']['razao_social']}}
            <br>
            <br>
            <br>
            <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
            {{$dados['dados_colaborador']->Curriculo->nome}}
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
        <div style="position:fixed; bottom: 35px">
            @include('layouts.rodapePdfFilialJob')
        </div>
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
