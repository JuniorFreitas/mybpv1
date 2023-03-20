@extends('layouts.sistema')
@section('title', 'CONTROLE DE EXAMES')
@section('content_header', 'CONTROLE DE EXAMES')
@section('content')
    <modal id="validaSesmt" :titulo="abasesmt.tituloJanela" modal-pai='janelaParecerEntrevista' :size="80"
           :fechar="!abasesmt.preload">
        <template slot="conteudo">
            <preload v-if="abasesmt.preload" label="Aguarde ...."></preload>
            <fieldset v-if="!abasesmt.preload">
                <legend>Exame</legend>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">EXAME REALIZADO?</label>
                            <select class="form-control validacampo" v-model='abasesmt.form.exame_realizado'
                                    @keyup.prevent="valida_campo_vazio($event.target,1)"
                                    @blur.prevent="valida_campo_vazio($event.target,1)"
                                    @change="limpaformResultado()"
                            >
                                <option :value="null">Selecione...</option>
                                <option :value="true">Sim</option>
                                <option :value="false">Não</option>
                            </select>
                        </div>
                    </div>

                    <template v-if="abasesmt.form.exame_realizado">
                        <div class="col-12 col-md-6">
                            <label for="">DATA DO EXAME:
                            </label>
                            <input type="text" class="form-control validacampo" v-model="abasesmt.form.data_realizacao"
                                   v-mascara:data
                                   @keyup.prevent="valida_data_vazio($event.target)"
                                   @blur.prevent="valida_data_vazio($event.target)">
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">RESULTADO DO EXAME</label>
                                <select class="form-control validacampo" v-model="abasesmt.form.resultado.result"
                                        @keyup.prevent="valida_campo_vazio($event.target,1)"
                                        @blur.prevent="valida_campo_vazio($event.target,1)">
                                    <option :value="null">Selecione ...</option>
                                    <option value="Apto">Apto</option>
                                    <option value="Apto com Restrição">Apto com Restrição</option>
                                    <option value="Inapto">Inapto</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">Há pendencias</label>
                                <select class="form-control validacampo" v-model="abasesmt.form.resultado.pendencias"
                                        @keyup.prevent="valida_campo_vazio($event.target,1)"
                                        @blur.prevent="valida_campo_vazio($event.target,1)"
                                >
                                    <option :value="null">Selecione ...</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6" v-if='abasesmt.form.resultado.pendencias === "Sim" '>
                            <div class="form-group">
                                <label for="">Quais</label>
                                <input type="text" class="form-control validacampo"
                                       v-model="abasesmt.form.resultado.pendencias_quais"
                                       @keyup.prevent="valida_campo_vazio($event.target,1)"
                                       @blur.prevent="valida_campo_vazio($event.target,1)"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">Aprovado</label>
                                <select class="form-control validacampo"
                                        v-model="abasesmt.form.resultado.aprovado"
                                        @keyup.prevent="valida_campo_vazio($event.target,1)"
                                        @blur.prevent="valida_campo_vazio($event.target,1)"
                                >
                                    <option :value="null">Selecione ...</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">APTO TRABALHO EM ALTURA?</label>
                                <select class="form-control validacampo"
                                        v-model="abasesmt.form.resultado.trabalho_altura"
                                        @keyup.prevent="valida_campo_vazio($event.target,1)"
                                        @blur.prevent="valida_campo_vazio($event.target,1)"
                                >
                                    <option :value="null">Selecione ...</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                    <option value="Não se aplica">Não se aplica</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">APTO TRABALHO EM ESPACO CONFINADO?</label>
                                <select class="form-control validacampo"
                                        v-model="abasesmt.form.resultado.espacao_confinado"
                                        @keyup.prevent="valida_campo_vazio($event.target,1)"
                                        @blur.prevent="valida_campo_vazio($event.target,1)"
                                >
                                    <option :value="null">Selecione ...</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                    <option value="Não se aplica">Não se aplica</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-12">
                            <div class="form-group">
                                <label for="">Observações</label>
                                <textarea class='form-control' cols='30' rows='5'
                                          v-model='abasesmt.form.resultado.observacoes'>
                                </textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>Anexo</legend>
                                <upload :model="abasesmt.form.anexos"
                                        :model-delete="abasesmt.form.anexosDel"
                                        :url="url_anexo"
                                        label="Selecionar"
                                        @onProgresso="anexoUploadAndamento=true"
                                        @onFinalizado="anexoUploadAndamento=false"></upload>
                            </fieldset>
                        </div>

                    </template>
                </div>
            </fieldset>
        </template>
        <template slot="rodape">
            {{--            <div v-show="!visualizar">--}}
            <button type="button" class="btn btn-sm btn-primary"
                    v-if="!abasesmt.preload"
                    @click.prevent="salvarResultado">
                <i class="fa fa-save"></i> Salvar
                {{--                                <span v-show='cadastrando'>Salvar</span>--}}
                {{--                                <span v-show='editando'>Editando</span>--}}
            </button>
            {{--            </div>--}}
        </template>
    </modal>

    <modal id="janelaParecerEntrevista" :titulo="tituloJanela" :size="80" :fechar="!preload">
        <template slot="conteudo">
            <preload v-if="preload"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''">
                <fieldset>
                    <legend class="text-uppercase">Dados Pessoais</legend>
                    <div class="row">
                        <div class="col-12">
                            <p>
                                Nome: <strong>@{{ dados.nome }}</strong> <br>
                                <br>
                                Cargo: <strong>@{{ dados.cargo }}</strong> <br>
                            </p>
                        </div>
                    </div>
                </fieldset>

                <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist"
                    style="border-bottom: 1px solid #653232">
                    <li class="nav-item">
                        <a class="nav-item nav-link active" id="nav-encaminhar-tab" data-toggle="tab"
                           href="#nav-encaminhar"
                           @click.prevent="nav = 'encaminhar'"
                           role="tab" aria-controls="nav-encaminhar" aria-selected="false">ENCAMINHAR</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-item nav-link" id="nav-encaminhados-tab" data-toggle="tab"
                           @click.prevent="nav = 'encaminhados'"
                           href="#nav-encaminhados"
                           role="tab" aria-controls="nav-encaminhados" aria-selected="true">ENCAMINHADOS</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-item nav-link" id="nav-sesmt-tab" data-toggle="tab"
                           href="#nav-sesmt"
                           @click.prevent="nav = 'validacao_sesmt'"
                           role="tab" aria-controls="nav-sesmt" aria-selected="true">VALIDAÇÃO SESMT</a>
                    </li>

                </ul>

                <div class="tab-content py-3 p-2">
                    <div class="tab-pane fade show active" id="nav-encaminhar" role="tabpanel"
                         aria-labelledby="nav-encaminhar-tab">
                        <fieldset>
                            <legend class="text-uppercase">Clinica</legend>
                            <div class='row'>
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <select class='form-control'
                                                onblur='valida_campo_vazio(this,1)'
                                                onchange='valida_campo_vazio(this,1)'
                                                v-model='form.empresa_exame_id'
                                        >
                                            <option value=''>Selecione</option>
                                            <option v-for='item in listaEmpresasExames' :value='item.id'>
                                                @{{ item.nome }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                                    <div class="form-group">
                                        <h5 v-if='form.empresa_exame_id' class='my-2'
                                            v-text="listaEmpresasExames.filter(item => item.id === form.empresa_exame_id)[0].dados.email"></h5>
                                    </div>
                                </div>

                                <div class="col-12" v-if="form.empresa_exame_id">
                                    <div class="switchToggle">
                                        <input type="checkbox" v-model="form.envia_email" id="envia_email_documentos">
                                        <label for="envia_email_documentos">Enviar E-mail</label>
                                    </div>
                                </div>
{{--                                <div class="col-2" v-if="form.empresa_exame_id">--}}
{{--                                    <div class="switchToggle" v-show="whatsappLiberado">--}}
{{--                                        <input type="checkbox" v-model="form.envia_whatsapp_documentos" id="envia_whatsapp_documentos">--}}
{{--                                        <label for="envia_whatsapp_documentos">Enviar Whatsapp</label>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-uppercase">DATA DO ENCAMINHAMENTO</legend>
                            <div class='row'>
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                    <datepicker
                                        label=""
                                        style="margin-top: -19px;"
                                        :disabled="visualizar || disabled"
                                        v-model="form.encaminhado_exame_data"
                                        :min="dataHoje"
                                    ></datepicker>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-uppercase">PCMSO</legend>
                            <div class='row'>
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <select class='form-control' v-model='form.pcmso_id'>
                                            <option value=''>Nenhum</option>
                                            <option v-for='pcmso in listaPcmsos' :value='pcmso.id'>
                                                @{{ pcmso.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset v-if='form.pcmso_id'>
                            <legend class="text-uppercase">Tipo de Ordem</legend>
                            <div class='row'>
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <select class='form-control' v-model='form.exame_tipo_id'
                                                onblur='valida_campo_vazio(this,1)'
                                                onchange='valida_campo_vazio(this,1)'
                                        >
                                            <option value=''>Selecione</option>
                                            <option v-for='exame_tipo in listaExameTipos' :value='exame_tipo.id'>
                                                @{{ exame_tipo.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <formulario :model='form' :formulario_id='form.formulario.id' v-if='form.formulario && form.pcmso_id.length===0'
                                    :mostra_titulo='false'></formulario>


                    </div>

                    <div class="tab-pane fade" id="nav-encaminhados" role="tabpanel"
                         aria-labelledby="nav-encaminhados-tab">

                        <div class="alert alert-warning text-center" v-show="!preload && historico.length===0">
                            <i class="fa fa-exclamation-triangle"></i> Nenhum Encaminhamento Encontrado.
                        </div>

                        <table class="tabela table-striped" v-show="!preload && historico.length > 0">
                            <thead>
                            <tr class="bg-default">
                                <th>CÓD</th>
                                <th>Tipo de exame</th>
                                <th>Clinica</th>
                                <th>PCSMO</th>
                                <th>Encaminhado Por</th>
                                <th>Data do Encaminhamento</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody v-for="item in historico">
                            <tr style="background: white !important; border-bottom: none">
                                <td>@{{ item.id }}</td>
                                <td>@{{ item.tipo_exame }}</td>
                                <td>@{{ item.empresa_exame.nome }}</td>
                                <td>@{{ item.pcmso_label }}</td>
                                <td>@{{ item.quem_encaminhou.nome }}</td>
                                <td>@{{ item.encaminhamento_data }}</td>
                                <td>
                                    <form :action="`${URL_ADMIN}/controle-exames/ficha-encaminhamento/${item.id}`"
                                          target="_blank" method="post">
                                        @csrf
                                        <input type="hidden" name="id" :value="item.id">
                                        <input type="hidden" name="tipo_exame" :value="item.tipo_exame">
                                        <button type="submit" content="Gerar PDF" v-tippy
                                                class="btn btn-sm btn-primary mb-2">
                                            <i class="fa fa-file-pdf" aria-hidden="true"></i>
                                        </button>
                                    </form>


                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="nav-sesmt" role="tabpanel"
                         aria-labelledby="nav-sesmt-tab">

                        <p v-show='!concordo'>
                            Acesso ao prontuário
                            <br><br>
                            1 – Solicitação pela própria paciente:
                            <br><br>
                            Artigo 70 do CEM: "É vedado ao médico negar ao paciente acesso a seu prontuário médico,
                            ficha clínica ou
                            similar, bem como deixar de dar explicações necessárias a sua compreensão, salvo quando
                            ocasionar riscos
                            para o paciente ou para terceiros."
                            <br>
                            Artigo 71 do CEM: "É vedado ao médico deixar de fornecer laudo médico ao paciente, quando do
                            encaminhamento ou transferência para fins de continuidade do tratamento ou na alta, se
                            solicitado."
                            <br>
                            Artigo 11 do CEM: "O médico deve manter sigilo quanto às informações confidenciais de que
                            tiver
                            conhecimento no desempenho de suas funções. O mesmo se aplica ao trabalho em empresas,
                            exceto nos casos
                            em que seu silêncio prejudique ou ponha em risco a saúde do trabalhador ou da comunidade".
                            <br>
                            <br>

                            O segredo médico é instituto milenar, cuja origem já constava no juramento de Hipócrates: "O
                            que, no
                            exercício ou fora do exercício e no comércio da vida, eu vir ou ouvir, que não seja
                            necessário revelar,
                            conservarei como segredo." <br>

                            É importante salientar que o prontuário pertence à paciente e que, por delegação desta, pode
                            ter acesso
                            ao mesmo o médico. Portanto, é um direito da paciente ter acesso, a qualquer momento, ao seu
                            prontuário,
                            recebendo por escrito o diagnóstico e o tratamento indicado, com a identificação do nome do
                            profissional
                            e o número de registro no órgão de regulamentação e controle da profissão (CRM, Coren etc.),
                            podendo,
                            inclusive, solicitar cópias do mesmo. <br><br>

                            2 – Solicitação dos familiares e/ou do responsável legal do paciente: <br><br>

                            Quando da solicitação do responsável legal pela paciente – sendo esta menor ou incapaz – o
                            acesso ao
                            prontuário deve ser-lhe permitido e, se solicitado, fornecer as cópias solicitadas ou
                            elaborar um laudo
                            que contenha o resumo das informações lá contidas.<br>

                            Caso o pedido seja feito pelos familiares da mulher, será necessária a autorização expressa
                            dela. Na
                            hipótese de que ela não tenha condições para isso ou tenha ido a óbito, as informações devem
                            ser dadas
                            sob a forma de laudo ou até mesmo cópias. No caso de óbito, o laudo deverá revelar o
                            diagnóstico, o
                            procedimento do médico e a "causa mortis".<br>

                            Entenda-se que, em qualquer caso, o prontuário original, na sua totalidade ou em partes, não
                            deve ser
                            fornecido aos solicitantes, pois é documento que, obrigatoriamente, precisa ser arquivado
                            pela entidade
                            que o elaborou. Entenda-se, também, que os laudos médicos não devem ser cobrados
                            facultando-se, porém, a
                            critérios da entidade, a cobrança das xerocópias quando fornecidas por ela.<br><br>

                            3 – Solicitação por outras entidades:<br><br>

                            Neste caso, temos constatado que os convênios médicos e as companhias de seguro são os
                            principais
                            solicitantes. Salvo com autorização expressa da paciente, é vedado ao médico fornecer tais
                            informações.<br>

                            Sem o consentimento da mulher, o médico não poderá revelar o conteúdo de prontuário ou ficha
                            médica
                            (Artigo 102 do CEM), salvo por justa causa, isto é, quando diante de um estado extremo de
                            necessidade.
                            Haverá justa causa quando a revelação for o único meio de conjurar perigo atual ou iminente
                            e injusto
                            para si e para outro.<br><br>

                            Exemplos de "Justa Causa":<br><br>

                            a) Para evitar casamento de portador de defeito físico irremediável ou moléstia grave e
                            transmissível
                            por contágio ou herança, capaz de por em risco a saúde do futuro cônjuge ou de sua
                            descendência, casos
                            suscetíveis de motivar anulação de casamento, em que o médico esgotará, primeiro, todos os
                            meios idôneos
                            para evitar a quebra do sigilo;<br>
                            b) Crimes de ação pública incondicionada quando solicitado por autoridade judicial ou
                            policial, desde
                            que estas, preliminarmente, declarem tratar-se desse tipo de crime, não dependendo de
                            representação e
                            que não exponha o paciente a procedimento criminal;<br>
                            c) Defender interesse legítimo próprio ou de terceiros.<br><br>

                            Dever legal, ou seja, aquele que deriva não vontade de quem o confia a outrem, mas de
                            condição
                            profissional, em virtude da qual ele é confiado e na natureza dos deveres que, no interesse
                            geral, são
                            impostos aos profissionais. Exemplos de "Dever Legal":<br><br>

                            a) Leis Penais – Doenças infecto-contagiosas de notificação compulsória, de declaração
                            obrigatória
                            (toxicomanias), etc.<br>
                            b) Crimes de ação pública cuja comunicação não exponha o paciente a procedimento criminal
                            (Lei da
                            Contravenções Penais, artigo 66, inciso II);<br>
                            c) Leis Extras-Penais: Médicos militares, médicos legistas, médicos sanitaristas, médicos
                            peritos,
                            médicos de juntas de saúde, médicos de companhias de seguros, médicos de empresas, atestados
                            de óbito,
                            etc.; ou autorização expressa da paciente; permanece essa proibição: a) mesmo que o fato
                            seja de
                            conhecimento público ou que a paciente tenha falecido; b) quando o médico depõe como
                            testemunha. Nesta
                            hipótese, o médico comparecerá perante a autoridade e declarará seu impedimento.<br>

                            Artigo 105 - Revelar informações confidenciais obtidas quando do exame médico de
                            trabalhadores,
                            inclusive por exigência dos dirigentes de empresas ou instituições, salvo se o silêncio
                            puser em risco a
                            saúde dos empregados ou da comunidade.<br>
                            Artigo 106 - Prestar às empresas seguradoras qualquer informação sobre as circunstâncias da
                            morte de
                            paciente seu, além daquelas contidas no próprio atestado de óbito, salvo por expressa
                            autorização do
                            responsável legal ou sucessor.<br>
                            Artigo 107 - Deixar de orientar seus familiares e de zelar para que respeitem o segredo
                            profissional a
                            que estão obrigados por lei.<br>
                            Artigo 108 - Facilitar o manuseio e conhecimento dos prontuários, papeletas e demais folhas
                            de
                            observações médicas sujeitas ao segredo profissional, por pessoas não obrigadas ao mesmo
                            compromisso.
                            <br>
                            Os diretores técnicos ou clínicos que autorizarem a saída de prontuário das suas
                            instituições violam o
                            artigo 108 do CEM.
                            <br>
                            O acesso ao prontuário pela figura do médico auditor enquadra-se no princípio do dever
                            legal, já que tem
                            ele atribuições de peritagem sobre a cobrança dos serviços prestados pela entidade, cabendo
                            ao mesmo
                            opinar pela regularidade dos procedimentos efetuados e cobrados, tendo, inclusive, o direito
                            de examinar
                            a paciente, para confrontar o descrito no prontuário. Todavia, esse acesso sempre deverá
                            ocorrer dentro
                            das dependências da instituição de assistência à saúde responsável por sua posse e guarda,
                            não podendo a
                            instituição ser obrigada, a qualquer título, a enviar os prontuários aos seus contratantes
                            públicos ou
                            privados (Resolução CFM nº 1614/01).
                            <br><br>
                            4 - Solicitação de autoridades policiais ou judiciárias:
                            <br><br>
                            Com relação ao pedido de cópia do prontuário pelas Autoridades Policiais (delegados, p.ex.)
                            e/ou
                            Judiciárias (promotores, juízes, etc.), vale tecer alguns esclarecimentos sobre segredo
                            médico.
                            <br><br>
                            O segredo médico é uma espécie do segredo profissional, ou seja, resulta das confidências
                            que são feitas
                            ao médico pelos seus clientes, em virtude da prestação de serviço que lhes é destinada. O
                            segredo médico
                            compreende, então, confidências relatadas ao profissional, bem como as percebidas no
                            decorrer do
                            tratamento e, ainda, aquelas descobertas e que o paciente não tem intenção de informar.
                            Desta forma, o
                            segredo médico é, penal (artigo 154 do Código Penal) e eticamente, protegido (artigo 102 e
                            seguintes do
                            Código de Ética Médica), na medida em que a intimidade do paciente deve ser preservada.
                            <br><br>
                            Entretanto, ocorrendo as hipóteses de "justa causa" (circunstâncias que afastam a ilicitude
                            do ato),
                            "dever legal" (dever previsto em lei, decreto, etc.) ou autorização expressa do paciente, o
                            profissional
                            estará liberado do segredo médico. Assim, com as exceções feitas acima, aquele que revelar
                            as
                            confidências recebidas em razão de seu exercício profissional deverá ser punido. É de se
                            ressaltar, que
                            o segredo médico também não deve ser revelado para autoridade judiciária ou policial. Não há
                            disposição
                            legal que respalde ordens desta natureza. É oportuno salientar que este entendimento foi
                            sufragado pelo
                            Colendo Supremo Tribunal Federal ao julgar o "Habeas Corpus" nº 39308 de São Paulo, cuja
                            ementa é a
                            seguinte:
                            <br><br>
                            "Segredo profissional. Constitui constrangimento ilegal a exigência da revelação do sigilo e
                            participação de anotações constantes das clínicas e hospitais." Conseqüentemente, a
                            requisição judicial,
                            por si só, não é "justa causa". Entretanto, a solução para que as autoridades obtenham
                            informações
                            necessárias é que o juiz nomeie um perito médico, a fim de que o mesmo manuseie os
                            documentos e elabore
                            laudo conclusivo sobre o assunto. Ou então, solicitar ao paciente a autorização para
                            fornecer o laudo
                            médico referente a seu estado.
                            <br><br>
                            Outrossim, deverão ser sempre resguardadas todas as informações contidas no prontuário
                            médico por força
                            do sigilo médico que alcança, além do médico, todos os seus auxiliares e pessoas afins que,
                            por dever de
                            ofício, tenham acesso às informações confidenciais constantes do prontuário.
                            <br><br>
                            Segredo Médico
                            <br><br>
                            A observância do sigilo médico constitui-se numa das mais tradicionais características da
                            profissão
                            médica. O segredo médico é um tipo de segredo profissional e pertence ao paciente. Sendo o
                            médico o seu
                            depositário e guardador, somente podendo revelá-lo em situações muito especiais como: dever
                            legal, justa
                            causa ou autorização expressa do paciente. Revelar o segredo sem a justa causa ou dever
                            legal, causando
                            dano ao paciente, além de antiético é crime, capitulado no artigo 154 do Código Penal
                            Brasileiro.
                            <br><br>
                            "A justa causa, abrange toda a situação que possa ser utilizada como justificativa para a
                            prática de um
                            ato excepcional, fundamentado em razões legítimas e de interesse coletivo, ou seja, uma
                            razão superior
                            relevante, a um estado de necessidade". Como exemplo de justa causa, para a revelação do
                            segredo médico,
                            temos a situação de um paciente portador de uma doença contagiosa incurável de transmissão
                            sexual e que
                            se recusa a informar e proteger seu parceiro sexual do risco de transmissão, ou ainda, que
                            deliberadamente pratica o sexo de forma a contaminar outras pessoas.
                            <br><br>
                            O dever legal se configura quando compulsoriamente o segredo médico tem de ser revelado por
                            força de
                            disposição legal expressa que assim determine. Por exemplo: atestado de óbito, notificação
                            compulsória
                            de doenças etc. Outra situação específica de revelação de segredo médico por dever legal, é
                            a
                            comunicação de crime de ação pública, especialmente os ocasionados por arma de fogo ou
                            branca, e as
                            lesões corporais que apresentam gravidade. Nesse caso, a comunicação deverá ocorrer à
                            autoridade
                            policial ou do Ministério Público da cidade onde se procedeu o atendimento, observando a
                            preservação da
                            paciente.
                        </p>
                        <button class='btn btn-primary' v-show='!concordo' @click='concordo = true'>Concordo</button>

                        <template v-show='concordo'>
                            <div class="alert alert-warning text-center"
                                 v-show="!preload && historico.length===0 && concordo">
                                <i class="fa fa-exclamation-triangle"></i> Nenhum Encaminhamento Encontrado.
                            </div>
                            <div v-show="!preload && historico.length > 0 && concordo">
                                <div class="col-12 mb-2 mt-2 pt-1 pb-1 border-bottom">
                                    <p>
                                        Legenda: <i
                                            class="fas fa-circle text-success ml-2"></i>
                                        Aprovado
                                        <i class="fas fa-circle text-danger ml-2"></i> Reprovado
                                    </p>
                                </div>
                                <table class="tabela table-striped">
                                    <thead>
                                    <tr class="bg-default">
                                        <th>CÓD</th>
                                        <th>Tipo de exame</th>
                                        <th>Clinica</th>
                                        <th>Encaminhado Por</th>
                                        <th>Aprovado</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr style="background: white !important; border-bottom: none"
                                        v-for="item in historico"
                                        :key="item.id"
                                        :class="{
                                            'table-danger': item.resultado?.resultado.aprovado === 'Não',
                                            'table-success': item.resultado?.resultado.aprovado === 'Sim'
                                        }"
                                    >
                                        <td>@{{ item.id }}</td>
                                        <td>@{{ item.tipo_exame }}</td>
                                        <td>@{{ item.empresa_exame.nome }}</td>
                                        <td>
                                            @{{ item.quem_encaminhou.nome }}<br>em @{{ item.created_at }}
                                        </td>
                                        <td>
                                            @{{ item.resultado ? item.resultado.resultado.aprovado : 'Aguardando' }}
                                        </td>
                                        <td>
                                            <button type="button" content="Resultado exame" v-tippy
                                                    class="btn btn-sm btn-primary mb-2" data-toggle="modal"
                                                    data-target="#validaSesmt" @click='formResultado(item.id)'>
                                                <i class="fa fa-search-plus" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
        <template slot="rodape">
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="!cadastrado & nav === 'encaminhar'  && !preload"
                        @click.prevent="salvarUpdate">
                    <i class="fa fa-save"></i>
                    <span v-show='cadastrando'>Salvar</span>
                    <span v-show='editando'>Editando</span>
                </button>
            </div>
        </template>
    </modal>

    <modal id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template slot="conteudo">
            <div class="custom-control custom-switch mb-2" v-if="AUTENTICADO.cliente_id === 0">
                <input type="checkbox" v-model="colunasTabela.cliente"
                       @click="colunasTabela.cliente = !colunasTabela.cliente" class="custom-control-input"
                       id="cliente">
                <label class="custom-control-label" for="cliente">EMPRESA</label>
            </div>
        </template>
    </modal>

    <preload class="text-center" v-if="controle.carregando"></preload>
    <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length===0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>
    <fieldset>
        <legend>Filtro</legend>
        <form @submit.prevent="$refs.componente.buscar()">
            <div class="row">
                <div class="col-12 col-md-3">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                               id="filtroIntervalo"
                               v-model="controle.dados.filtroPeriodo">
                        <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label=""
                                    :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                    v-model="controle.dados.periodo"></datepicker>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text" placeholder="Buscar por nome" autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" placeholder="Buscar por cpf" autocomplete="mastertag"
                               onblur="valida_cpf(this)"
                               v-mascara:cpf class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoCPF">
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label for="">Status</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.status">
                            <option value="em_processo">Em processo</option>
                            <option value="admitidos">Admitidos</option>
                            <option value="demitidos">Demitidos</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label for="">Exibir</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.pages">
                            <option v-for='exib in exibicao' :value="exib">@{{exib}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
        <div class="col-12">
            <div class="row">
                <button type="button" class="btn btn-sm btn-success mr-1 mb-1" :disabled="controle.carregando"
                        @click.prevent="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
            </div>
        </div>

    </fieldset>
    <preload class="text-center" v-if="controle.carregando"></preload>
    <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length===0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>

    <div id="conteudo">
        <table class="tabela table-striped" v-show="!controle.carregando && lista.length > 0">
            <thead>
            <tr class="bg-default">
                <th>ID</th>
                <th>Nome</th>
                <th>Cargo</th>
                <th>

                </th>
            </tr>
            </thead>
            <tbody v-for="colaborador in lista">
            <tr style="background: white !important; border-bottom: none">
                <td>@{{ colaborador.id }}</td>
                <td>@{{ colaborador.curriculo.nome }}</td>
                <td>@{{ colaborador.vaga_selecionada.nome }}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary mb-2" content="Encaminhar/historico" v-tippy
                            v-show="!colaborador.resultado_integrado"
                            @click.prevent="formEncaminhar(colaborador)"
                            data-toggle="modal" data-target="#janelaParecerEntrevista">
                        <i class="fa fa-search-plus"></i> Abrir
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                        :url="urlPaginacao"
                        :por-pagina="controle.dados.porPagina" :dados="controle.dados" @carregou="carregou"
                        @carregando="carregando">
    </controle-paginacao>
@stop
@push('js')
    <script src="{{ mix('js/g/controle-exames/app.js') }}"></script>
@endpush
