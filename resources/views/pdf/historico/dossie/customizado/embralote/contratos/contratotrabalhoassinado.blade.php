@extends('layouts.pdf_filial')
@section('title','Relatorio de ponto')
@section('conteudo')
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div style="margin-left: 5.2%; width: 87%">
        <p class="f11"
           style="text-align: center; margin-top: -0.3cm; text-transform: uppercase"><br>
            <strong>CONTRATO INDIVIDUAL DE TRABALHO</strong>
        </p>

        <p class="f11 text-justify">
            Pelo presente instrumento particular, de um lado <br>
            {{$dados['dados_empresa']['razao_social']}}, razão social, inscrita no CPF/CNPJ sob o
            n.º {{$dados['dados_empresa']['cnpj']}}, com sede em
            {{$dados['dados_empresa']['endereco_completo']}}, doravante denominado (a) EMPREGADOR (A);
            <br>E
            {{$dados['dados_colaborador']->Curriculo->nome}}, {{$dados['dados_colaborador']->Curriculo->estado_civil}},
            portador do CPF inscrito sob o nº {{$dados['dados_colaborador']->Curriculo->cpf}},
            portador da Carteira de Trabalho e Previdência Social - CTPS
            {{$dados['dados_colaborador']->Admissao->DadosAdmissoes? $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_numero : 'NÃO INFORMADO'}}
            ,
            Série {{$dados['dados_colaborador']->Admissao->DadosAdmissoes? $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_serie: 'NÃO INFORMADO'}}
            ,
            residente e domiciliada em {{$dados['dados_colaborador']->Curriculo->endereco_completo}}, doravante
            denominado (a) EMPREGADO (A), resolvem, de livre e espontânea
            vontade, firmar o presente CONTRATO INDIVIDUAL DE TRABALHO, que será regido pela legislação trabalhista e
            pelas seguintes cláusulas e condições:
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA PRIMEIRA – DO OBJETO</strong>
        </p>
        <p class="f11 text-justify">
            1.1. O objeto do presente contrato é a prestação de serviços pelo (a) EMPREGADO (A) ao (à) EMPREGADOR (A).
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA SEGUNDA– DA FUNÇÃO</strong>
        </p>

        <p class="f11 text-justify">
            2.2. O (A) EMPREGADO (A) se compromete a prestar seus serviços junto ao quadro de funcionários do (a)
            EMPREGADOR (A), ocupando a função de {{ $dados['dados_colaborador']->Admissao->funcao }} obrigando-se,
            assim,
            a realizar as atividades de acordo
            com o cargo, bem como todo e qualquer serviço que lhe for repassado pelo (a) EMPREGADOR (A) por meio de
            ordens verbais ou escritas, desde que compatível com a sua condição pessoal, nos termos do art. 456 da CLT.
            <br>
            2.3. Durante a vigência deste contrato, o (a) EMPREGADO (A) poderá ser reconduzido (a) a outra função, por
            conveniência do (a) EMPREGADOR (A), desde que haja a sua anuência ou que sejam verificadas as hipóteses
            legais.
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA TERCEIRA – DA REMUNERAÇÃO</strong>
        </p>

        <p class="f11 text-justify">
            3.1. Pelo trabalho descrito na Cláusula Segunda, o (a) EMPREGADOR (A) pagará ao (à) EMPREGADO (A) o valor de
            R$ {{ $dados['dados_colaborador']->Admissao->salario }}
            ({{\App\Models\Sistema::valorPorExtenso($dados['dados_colaborador']->Admissao->salario)}}) , a título de
            salário, a ser abatido pelos descontos legais e pelos adiantamentos eventualmente
            concedidos.
            <br>
            3.2. O pagamento deverá ser realizado em espécie, em cheque ou depositado em conta corrente de titularidade
            do (a) EMPREGADO (A), diretamente ao (à) EMPREGADO (A), até o quinto dia útil do mês subsequente ao vencido
            até o 5º dia útil de cada mês.
            <br>
            3.3. O (A) EMPREGADO (A) fará jus ao recebimento de benefícios em conformidade com o instrumento coletivo
            aplicável e com o regulamento interno do (a) EMPREGADOR (A).
            <br>
            3.4. Na assinatura do presente contrato, o (a) EMPREGADO (A) informará ao (à) EMPREGADOR (A) sobre a sua
            necessidade ao benefício de vale transporte, solicitando-o por escrito, bem como na necessidade de
            cancelamento ou modificação do benefício a comunicação com o (a) EMPREGADOR (A) sempre deverá ser por
            escrito.
            <br>
            3.5 Os benefícios concedidos não integram a remuneração do empregado, nos termos do artigo 458, § 2º, da CLT
            ou cuja previsão em instrumento coletivo ou no regulamento interno da empresa assim estabeleça.
            <br>
            @if(!$dados['dados_colaborador']->Admissao->pExperiencia())
                3.6 – O presente contrato vigorará por tempo inderterminado.
            @else
                3.6 – O presente contrato vigorará
                por {{$dados['dados_colaborador']->Admissao->pExperiencia()[0]}}
                ({{ \MasterTag\GExtenso::numero($dados['dados_colaborador']->Admissao->pExperiencia()[0]) }})
                dias, podendo este ser
                prorrogado ou não por mais {{$dados['dados_colaborador']->Admissao->pExperiencia()[1]}}
                ({{ \MasterTag\GExtenso::numero($dados['dados_colaborador']->Admissao->pExperiencia()[1]) }})
                dias sendo celebrado para as partes verificarem reciprocamente, a conveniência ou não de se vincularem
                em caráter definitivo a um Contrato de Trabalho. A empresa passando a conhecer as aptidões do (a)
                <strong>EMPREGADO (A)</strong> e suas qualidades pessoais e morais; o
                <strong>EMPREGADO (A)</strong> verificando se o ambiente e os métodos de trabalho atendem à sua
                conveniência.
            @endif
            <br>
            3.7. A mudança de função, de local de trabalho ou de quaisquer outras cláusulas deste contrato não importará
            em redução salarial, salvo quando a lei o permitir.
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA QUARTA- DOS DESCONTOS</strong>
        </p>

        <p class="f11 text-justify">
            4.1. O (A) EMPREGADO (A) autoriza o (a) EMPREGADOR (A) a efetuar todos os demais descontos previstos em lei
            ou em contrato coletivo ou que por eles for determinado, do mesmo modo com relação aos valores
            correspondentes em casos de perda, desvio ou dano causados pelo (a) EMPREGADO (A) em equipamentos de
            segurança, materiais, ferramentas, máquinas, veículos, móveis, utensílios e ao estabelecimento em geral, por
            dolo ou mesmo imprudência, imperícia ou negligência nos termos do parágrafo 1º do artigo 462 da Consolidação
            das Leis do Trabalho.
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA QUINTA - DA JORNADA DE TRABALHO</strong>
        </p>

        <p class="f11 text-justify">
            5.1 A jornada semanal de trabalho totalizará 44 (quarenta e quatro) horas, que serão distribuídas confome
            escala de trabalho.
            <br>
            5.2. O (A) EMPREGADO (A) deverá ter pelo menos 24 (vinte e quatro) horas consecutivas semanais de repouso
            preferencialmente aos domingos, além dos feriados civis e religiosos conforme o artigo 67 da Consolidação
            das Leis do Trabalho, podendo ser alterada de acordo com a escala de trabalho.
            <br>
            5.3. (Horas Extras) – Obriga-se também o EMPREGADO a prestar serviços em horas extraordinárias, sempre que
            lhe for determinado pela EMPREGADORA, na forma prevista em Lei. Na hipótese desta faculdade pela
            EMPREGADORA, o EMPREGADO receberá as horas extraordinárias com o acréscimo legal, ou será tratado
            através de acordo de compensação de horas, com a consequente redução da jornada de trabalho em outro dia.
            Horas extras não autorizadas não serão remuneradas e estarão passíveis de medidas administrativas..
            <br>
            5.4. Poderá ser dispensado o acréscimo de salário se as horas extras forem compensadas por banco de horas ou
            regimes de compensação de jornada, na forma prevista em lei.
            <br>
            5.5 O (A) EMPREGADO (A) terá pelo menos 1 (uma) hora de intervalo para repouso e alimentação, podendo ser
            suprimido por acordo entre as partes, mediante pagamento suplementar do período suprimido, com acréscimo de
            50% (cinquenta por cento) sobre o valor da remuneração da hora normal de trabalho.
            <br>
            5.6. Em caso de ausência ou atraso do (a) EMPREGADO (A) ao trabalho, haverá desconto proporcional em sua
            remuneração, exceto quando justificado ou permitido por lei ou, ainda, compensado posteriormente por banco
            de horas ou regime de compensação de jornada.
            <br>
            5.7. O descanso semanal remunerado e os feriados civis e religiosos já estão inclusos no cômputo da jornada
            e não serão objeto de remuneração suplementar
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA SEXTA - DO LOCAL DE TRABALHO</strong>
        </p>

        <p class="f11 text-justify">
            6.1. O (A) EMPREGADO (A) desempenhará sua função, já estabelecidas no presente contrato, ao (à) EMPREGADOR
            (A), em qualquer uma das lotações disponibilizadas pelo EMPREGADOR.
            <br>
            6.2. Durante a vigência deste contrato, o (a) EMPREGADO (A) poderá ser transferido de forma provisória ou
            definitiva, para exercer sua função em localidade diversa daquela acima indicada, desde que haja a sua
            anuência ou que sejam verificadas as hipóteses legais tal como previsto no artigo 469 da Consolidação das
            Leis do Trabalho.
            <br>
            6.3 A alteração do regime presencial para o regime de teletrabalho apenas será autorizado por mútuo acordo
            entre as partes, registrado em aditivo contratual garantindo-se ao (a) EMPREGADO (A) o prazo mínimo de 15
            (quinze dias), contados da assinatura do termo aditivo, para a transição.
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA SÉTIMA - DAS OBRIGAÇÕES DAS PARTES</strong>
        </p>

        <p class="f11 text-justify">
            7.1. São obrigações do (a) EMPREGADOR (A):
            <br>
            a) O (A) EMPREGADOR (A) deverá pagar ao (a) EMPREGADO (A) os valores previstos na Cláusula Terceira, dentro
            do prazo e da forma previamente indicada, a título salarial;
            <br>
            b) O (A) EMPREGADOR (A) deverá fornecer todas as condições para que o (a) EMPREGADO (A) labore em ambiente
            de trabalho seguro, com boas condições sanitárias e com infraestrutura adequada à execução das atividades
            pelo (a) EMPREGADO (A);
            <br>
            c) O (A) EMPREGADOR (A), no ato de celebração deste contrato, deverá cientificar o (a) EMPREGADO (A) de
            todas as regras de conduta estabelecidas e políticas internas, devendo entregar uma cópia do regulamento
            interno, caso exista;
            <br>
            d) A EMPREGADORA concederá ao colaborador, uniformes e EPIs padronizados, sendo de uso diário
            obrigatório, objetivando manter imagem e identidade da empresa como também evitar eventuais acidentes. Os
            mesmos deverão ser devolvidos no ato do desligamento ou quando ocorrer substituição por qualquer defeito
            decorrente do uso normal.
            <br>
            7.2. São obrigações do (a) EMPREGADO (A):
            <br>
            a) O (A) EMPREGADO (A) se compromete a executar as funções objeto do presente contrato, conforme as
            exigências, regimentos internos, manuais, diretrizes e padrões exigidos pelo (a) EMPREGADOR (A), bem como
            realizá-las com empenho para o
            melhor desenvolvimento do trabalho, preservando a qualidade e os prazos pactuados;
            <br>
            b) O (A) EMPREGADO (A) se compromete a prestar ao (à) EMPREGADOR (A) as informações necessárias sobre o
            andamento das atividades desenvolvidas;
            <br>
            c) O (A) EMPREGADO (A) deverá manter durante toda vigência deste contrato, comportamento compatível com as
            normas de disciplina, da ética profissional e de segurança estabelecidas pelo legislação brasileira e pelas
            normas internas do (a) EMPREGADOR (A), declarando estar ciente dos seus termos e condições;
            <br>
            d) O (A) EMPREGADO (A) se compromete a utilizar adequadamente os equipamentos e materiais fornecidos pelo
            (a) EMPREGADOR (A), os quais devem ser utilizados apenas para os fins profissionais contratados, podendo o
            (a) EMPREGADOR (A) realizar vistorias periódicas nos equipamentos por ele fornecido, desde a verificação de
            e-mails corporativos até a delimitação do recebimento e envio de arquivos;
            <br>
            e) O (A) EMPREGADO (A) assume estar ciente de que todos os códigos e senhas fornecidos pelo EMPREGADOR para
            utilização dos equipamentos são estritamente confidenciais, devendo ele tomar todas as cautelas na sua
            guarda.
            <br>
            f) Fica ainda autorizado, de livre e espontânea vontade, para os mesmos fins, a cessão de direitos da
            veiculação
            das imagens em todo e qualquer material entre imagens de vídeo, fotos e documentos, para ser utilizada pela
            empresa CONTRATANTE destinado a divulgação ao público em geral não recebendo para tanto qualquer tipo de
            remuneração.
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA OITAVA - DA CONFIDENCIALIDADE</strong>
        </p>

        <p class="f11 text-justify">
            8.1. O (A) EMPREGADO (A) deverá manter em sigilo, durante a vigência do presente termo e mesmo após sua
            extinção, qualquer informação confidencial relativa aos negócios, políticas, segredos comerciais,
            organização, criação e outras informações relativas ao (à) EMPREGADOR (A), seus clientes, fornecedores,
            representantes ou demais empregados;
            <br>
            8.2. Para fins do presente contrato, entende-se por informação confidencial: (a) qualquer informação
            relacionada ao negócio e operações do (a) EMPREGADOR (A) que não sejam públicas, (b) informações contidas em
            pesquisas, desenhos, designs, propostas, projetos, planos de negócio, venda ou marketing, informações
            financeiras, custos, dados de precificação, parceiros de negócios, informações de fornecedores e clientes,
            segredos industriais, propriedade intelectual, especificações, expertises, técnicas, invenções e todos os
            métodos, conceitos ou ideias relacionadas ao negócio do EMPREGADOR.
            <br>
            8.3. É vedado ao (a) EMPREGADO (A) repassar a terceiros, sejam particulares ou pessoas jurídicas, quaisquer
            destas informações, exceto quando expressamente autorizado pelo EMPREGADOR
            <br>
            8.4. A confidencialidade dessas informações independente de aviso prévio do EMPREGADOR, devendo o (a)
            EMPREGADO (A) considerar toda e qualquer informação relacionada ao negócio do (a) EMPREGADOR (A) como
            confidencial.
            <br>
            8.5. Ressalta-se que o dever de confidencialidade permanece mesmo após o término deste contrato de trabalho.
            <br>
            8.6. A violação da obrigação de confidencialidade pode causar a rescisão imediata deste contrato por justa
            causa, conforme o artigo 482, alínea g da CLT.
            <br>
            8.7. Em caso de violação desta cláusula o (A) EMPREGADO (A), (poderá ser responsabilizado pelo pagamento das
            quantias equivalentes ao dano causado) ou (estará sujeito ao pagamento de multa no valor de R$ 10.000 (Dez
            mil reais), a ser devidamente atualizada e corrigidas no momento de sua aplicação,) e, ainda, estará sujeito
            a eventuais penalidades civis e criminais aplicáveis.
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA NONA - DA VEDAÇÃO AO RECRUTAMENTO</strong>
        </p>

        <p class="f11 text-justify">
            9.1. O (A) EMPREGADO (A) está vedado de recrutar qualquer empregado do EMPREGADOR se praticada na mesma
            localidade de atuação do (a) EMPREGADOR (A), mesmo após o término da vigência deste contrato.
            <br>
            9.2. A vedação ao recrutamento perdurará pelo prazo de 18 meses, contado da data de resolução deste
            contrato.
            Após esse período a presente cláusula perde sua vigência.
            <br>
            9.3. O descumprimento desta cláusula poderá gerar a rescisão contratual, devendo o (a) EMPREGADO (A) pagar a
            multa no valor de R$ 10.000 (Dez mil reais), a ser devidamente atualizada e corrigida no momento de sua
            aplicação, e, ainda, estará sujeito a eventuais penalidades civis e criminais aplicáveis.
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA DÉCIMA - DA EXCLUSIVIDADE DO VÍNCULO EMPREGATÍCIO</strong>
        </p>

        <p class="f11 text-justify">
            10.1. Durante a vigência do presente instrumento, O (A) EMPREGADO (A) se compromete a manter a exclusividade
            do vínculo empregatício com o (a) EMPREGADOR (A), sendo-lhe vedada prestar serviços ou constituir quaisquer
            outros contratos de natureza trabalhista, com particulares ou com pessoas jurídicas.
            <br>
            10.2. O descumprimento desta cláusula poderá gerar a rescisão contratual, devendo o (a) EMPREGADO (A) pagar
            a multa no valor de R$ 10.000 (Dez mil reais), a ser devidamente atualizada e corrigida no momento de sua
            aplicação, e, ainda, estará sujeito a eventuais penalidades civis e criminais aplicáveis.
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA DÉCIMA PRIMEIRA – DOS DIREITOS AUTORAIS E DA PROPRIEDADE INTELECTUAL</strong>
        </p>

        <p class="f11 text-justify">
            11.1. O (A) EMPREGADO (A) declara estar ciente de que todo e qualquer direito advindo ou relacionado ao
            trabalho por ele (a) desempenhado, direta ou indiretamente, com os serviços prestados em decorrência do
            presente contrato, pertencerão exclusivamente ao (à) EMPREGADOR (A), nos termos da legislação vigente.
            <br>
            11.2. Nesse ponto, também é objeto do presente contrato a cessão e transferência em favor do (a) EMPREGADOR
            (A), expressamente, na integralidade, a título universal e gratuito, em caráter irretratável e irrevogável,
            para fins de utilização a qualquer tempo, para fins de utilização econômica ou não, no Brasil e/ou no
            Exterior, de todos os direitos patrimoniais de autor sobre documentos de modo geral referente às Obras que
            já tenham sido ou ainda sejam criadas pelo (a) EMPREGADO (A) no âmbito da relação de trabalho com o (a)
            EMPREGADOR (A), abrangendo tal cessão a criação, aperfeiçoamento, redação, revisão, edição, tradução,
            adaptação e toda e qualquer atividade que enseje proteção de direito de autor com relação às referidas
            Obras, que decorra, direta ou indiretamente, das atividades exercidas pelo (a) EMPREGADO (A) em razão da
            relação mantida com EMPREGADOR (A).
            <br>
            11.3. O disposto na Cláusula acima tem validade por todo o tempo em que a Obra estiver protegida por
            direitos autorais.
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA DÉCIMA SEGUNDA - DA PROTEÇÃO DE DADOS PESSOAIS</strong>
        </p>

        <p class="f11 text-justify">
            12.1 O EMPREGADO está ciente de que informa os dados contidos na Ficha
            Registro de Funcionário à EMPREGADORA, os quais são necessários para a execução do contrato de trabalho de
            experiência.
            <br>
            12.2 - Durante a vigência do contrato, outros dados pessoais poderão ser gerados, os quais também são
            necessários para a execução do contrato e para as finalidades mencionados na cláusula 12.3 e, na hipótese de
            ausência de previsão, por meio de formulário próprio autorizativo.
            <br>
            12.3 - O EMPREGADO ao assinar o presente contrato, está ciente que a empresa contratante poderá solicitar
            dados
            <br>
            extras, para o cumprimento de uma obrigação legal, uso de sistemas de controle de pessoal, pagamento de
            impostos e informações sociais aos órgãos públicos, bem como, para a execução do presente contrato.
            12.4 - Os dados fornecidos pelo EMPREGADO serão utilizados para as seguintes finalidades:
            <br>
            a). Funcionalidades de sistemas, softwares, plataformas, cadastros e normas internas;
            <br>
            b). Cadastro para eventuais notificações, comunicações formais entre empregado e empregadora no âmbito da
            gestão contratual por parte da EMPREGADORA;
            <br>
            c). Atendimento das demandas administrativas e operacionais no setor de RH;
            <br>
            d). Atendimento à pedidos de autoridades públicas, prestação de contas e informações sociais obrigadas em
            lei.
            <br>
            12.5 - A coleta de dados se dá através de sua inserção pela EMPREGADORA no momento da contratação pelo
            setor de RH.
            <br>
            Parágrafo Primeiro – Os dados da cláusula 12.3 serão gerados no decorrer do relacionamento entre as partes e
            do cumprimento do presente contrato.
            <br>
            Parágrafo segundo – A qualquer tempo, a EMPREGADORA pode requerer a correção ou atualização dos dados
            pessoais coletados, tendo o EMPREGADO de pleno conhecimento e aceite deste parágrafo.
            <br>
            12.6 - Os dados pessoais coletados podem ser acessados a qualquer momento pela EMPREGADORA através de
            solicitação no site/e-mail/plataforma, solicitação do setor de RH, solicitação do setor contábil e quantas
            outras solicitações sejam necessárias para o cumprimento do presente contrato, estando o empregado de pleno
            acordo com tais termos.
            <br>
            12.7 - O tratamento dos dados será realizado enquanto o EMPREGADO estiver trabalhando para a empresa
            contratante, ou seja, enquanto durar o contrato de trabalho.
            <br>
            12.8 - A EMPREGADORA armazenará os dados relativos ao EMPREGADO por até 5 anos, após o encerramento
            do contrato, prorrogáveis por igual prazo, a fim de atender ao prazo prescricional de discussões judiciais
            que estão em Ficha Registro do Empregado atrelada à este contrato.
            <br>
            12.9 - Os dados serão tratados pela própria EMPREGADORA, o qual poderá realizar a transferência de dados
            pessoais a terceiros visando o melhor desempenho das atividades vinculadas ao serviço contratado, assim como
            para gestão, segurança, armazenamento e backup em nuvem. Poderá, ainda, quando solicitado, compartilhar os
            dados pessoais do EMPREGADO com autoridades e entidades governamentais em função de exigências legais ou
            na defesa dos seus interesses no caso de conflitos, judiciais ou administrativos.
            <br>
            Parágrafo primeiro – A EMPREGADORA garante, desde já, que os terceiros contratados/subcontratados adotam
            medidas de segurança adequadas aos princípios e diretrizes previstos na LGPD e em conformidade com as boas
            práticas de privacidade e segurança da informação.
            <br>
            Parágrafo segundo – A EMPREGADORA cumprirá os princípios de adequação, necessidade e finalidade, e limitará
            internamente o acesso aos dados aos colaboradores estritamente necessários ao atendimento da finalidade.
            <br>
            12.10 - Os dados coletados são de acesso exclusivo da EMPREGADORA, e não serão vendidos ou cedidos a
            terceiros sem expresso consentimento pelo EMPREGADO. A EMPREGADORA se compromete em manter seguros
            os dados pessoais coletados, e realizará, quando necessário, relatório para avaliar o impacto dos
            tratamentos.
            <br>
            Parágrafo único – A EMPREGADORA, ao prezar pela privacidade do EMPREGADO, garante que os funcionários e
            terceiros envolvidos direta ou indiretamente nas operações de tratamento de dados pessoais estão cobertos
            por um dever de confidencialidade.
            <br>
            12.11 - Se, apesar das medidas de segurança implementadas, houver vazamento de dados que possa acarretar
            risco ou dano relevante aos titulares, a EMPREGADORA irá comunicar o EMPREGADO e a Autoridade Nacional de
            Proteção de Dados, nos termos do art. 48 da Lei nº 13.709/2018, e tomará as medidas necessárias para reduzir
            os danos e o risco de recorrência. Entende-se como vazamento a destruição, perda, alteração ou acesso não
            autorizados, seja de forma acidental ou deliberada, a dados pessoais.”
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA DÉCIMA TERCEIRA - DA RESCISÃO</strong>
        </p>

        <p class="f11 text-justify">
            13.1. Na hipótese de rescisão contratual, independente da parte que lhe der causa, deverá o (a) EMPREGADOR
            (A) devolver ao (à) EMPREGADO todos os documentos – impressos ou em qualquer outro meio físico – que se
            encontrem em seu poder no prazo máximo de 05 (cinco) dias úteis a contar do recebimento da comunicação
            formal da rescisão ou da submissão do pedido de demissão.
            <br>
            13.1. As partes podem romper o presente contrato unilateralmente, sem justa causa desde que pagas as
            parcelas
            legalmente devidas e respeitados os prazos de aviso prévio a seguir explicitados.
            <br>
            13.2. Em havendo rescisão contratual por parte do (a) EMPREGADO (A), este (a) deverá comunicar o empregador
            com antecedência mínima de 30 dias.
            <br>
            13.3. Em havendo rescisão contratual por parte do (a) EMPREGADOR (A), este deverá comunicar o empregado com
            antecedência mínima de 30 dias.
            <br>
            13.4. Após completados mais de 12 meses de serviço, este aviso será acrescido de 3 dias por ano de serviço
            prestado para o (a) EMPREGADOR (A) até o máximo de 60 dias podendo perfazer um total de 90 dias.
            <br>
            13.5. Em havendo uma das hipóteses do artigo 483 da Consolidação das Leis do Trabalho, este contrato poderá
            ser rescindido, independente dos prazos anteriores, podendo, ainda o (a) EMPREGADO (A) demandar indenização
            pelos prejuízos provocados.
        </p>

        <p class="f11 text-justify">
            <strong>CLÁUSULA DÉCIMA QUARTA – DAS CONSIDERAÇÕES FINAIS</strong>
        </p>

        <p class="f11 text-justify">
            14.1 Este contrato é o único instrumento que regula todas as obrigações e direitos das partes contratantes.
            Eventuais inclusões, exclusões ou alterações de direitos e deveres aqui previstos serão consignadas através
            de aditivo contratual, firmado entre as partes por escrito.
            <br>
            14.2. As partes elegem o foro de São Luís/MA, com renúncia expressa a qualquer outra que tenham ou venham a
            ter, para dirimir as dúvidas e/ou omissões por ventura existentes no presente contrato.
            <br>
            E, por estarem assim, justas e contratadas, EMPREGADOR (A) E EMPREGADO (A) assinam o presente instrumento em
            2 (duas) vias de igual teor e forma, na presença das testemunhas abaixo qualificadas, para que produza todos
            os efeitos de direito.
        </p>

        <p class="f11 text-center">
            <br>
            <br>
            <br>
            São
            Luís/MA, {{ (new \MasterTag\DataHora($dados['dados_colaborador']->Admissao->data_admissao))->dataCompletaExt() }}
            <br>
            <br>
            <br>
            ___________________________________________
            <br>
            {{$dados['dados_colaborador']->Curriculo->nome}}
            <br><br><br>
            ___________________________________________
            <br>
            {{$dados['dados_empresa']['razao_social']}}

        </p>
        <p class="f11">

            <br><br><br><br>
            TESTEMUNHAS:
            <br><br>
            1) __________________________________
            <br>
            Nome:
            <br>
            CPF nº:
            <br><br>
            2) __________________________________
            <br>
            Nome:
            <br>
            CPF nº:
        </p>

    </div>

@stop

<div style="position: fixed; right: -3.8cm; top: 16cm; text-align: left;transform: rotate(90deg);">
    @include('layouts.rodapePdfFilialJob')
</div>
@push('style')
    <style type="text/css">
        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }

        .f11 {
            font-size: 10pt !important;
            line-height: 14pt;
        }

        .rodapeAssinatura {
            color: #b2acac !important;
        }

    </style>
@endpush
