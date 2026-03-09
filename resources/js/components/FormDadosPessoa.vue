<template>
    <div>
        <h5 class="alert alert-warning text-center">
            ATENÇÃO! E-mail inválido ou inexistente, desclassificará o candidato. Só altere se necessário, este e-mail será usado para entrar na plataforma.
        </h5>
        <fieldset>
            <legend>Dados Pessoais</legend>
            <div class="row">
                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Nome</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.nome"
                            placeholder="Nome"
                            autocomplete="mastertag"
                            onblur="valida_campo_vazio(this, 3)"
                        />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>CPF</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.cpf"
                            placeholder="CPF"
                            :disabled="form.cpf.length > 0"
                            autocomplete="mastertag"
                            v-mascara:cpf
                            onblur="valida_cpf_vazio(this)"
                        />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>RG</label>
                        <input type="text" class="form-control" v-model="form.rg" placeholder="RG" autocomplete="mastertag" v-mascara:numero />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Orgão Expeditor (RG)</label>
                        <input type="text" class="form-control" v-model="form.orgao_expeditor" placeholder="Orgão" autocomplete="mastertag" />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>CNH</label>
                        <input type="text" class="form-control" v-model="form.cnh" placeholder="Tipo da CNH" autocomplete="mastertag" />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Nascimento</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.nascimento"
                            placeholder="Ex: 10/10/2010"
                            :disabled="possuiCadastro"
                            v-mascara:data
                            autocomplete="mastertag"
                            onblur="valida_data_vazio(this)"
                        />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Sexo</label>
                        <select class="form-control" v-model="form.sexo" onchange="valida_campo_vazio(this, 1)" onblur="valida_campo_vazio(this, 1)">
                            <option value="">Selecione</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                        </select>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Nome do pai</label>
                        <input type="text" class="form-control" v-model="form.filiacao_pai" placeholder="Nome" autocomplete="mastertag" />
                    </div>
                </div>

                <div class="col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Nome da mãe</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.filiacao_mae"
                            onblur="valida_campo_vazio(this, 3)"
                            placeholder="Nome"
                            autocomplete="mastertag"
                        />
                    </div>
                </div>

                <div class="col-12"></div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>E-mail</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.email"
                            placeholder="Informe seu melhor e-mail"
                            autocomplete="mastertag"
                            onblur="validaEmailVazio(this)"
                        />
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Endereço</legend>
            <div class="row">
                <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                    <endereco :model="form"></endereco>
                </div>
            </div>
        </fieldset>

        <fieldset class="table-warning">
            <legend>Contato</legend>
            <label class="text-danger">Adicionar no minimo DOIS contatos, pelo menos UM sendo WhatsApp</label>
            <div class="row">
                <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                    <telefone :model="form.telefones" :model-delete="form.telefonesDelete" :pais="false" :ramal="false"></telefone>
                </div>
            </div>
        </fieldset>
    </div>
</template>

<script>
import DatePicker from './DatePicker'
import endereco from './Endereco'
import telefone from './Telefones'
import autocomplete from './AutoComplete'

export default {
    components: {
        DatePicker,
        endereco,
        telefone,
        autocomplete
    },
    props: {
        form: {
            type: Object,
            required: true,
            default: {
                temqualificacao: false,
                temexperiencia: false,
                id: '',
                cpf: '',
                login: '',
                password: '',
                nome: '',
                cnh: '',
                nascimento: '',
                logradouro: '',
                complemento: '',
                bairro: '',
                municipio: '',
                uf: '',
                cep: '',
                email: '',
                filiacao_pai: '',
                filiacao_mae: '',
                formacao: '',
                formacao_instituicao: '',
                formacao_curso: '',
                formacao_status: '',
                vaga_pretendida: '',
                vaga_id: '',
                uf_vaga: '',
                municipio_id: '',
                rg: '',
                sexo: '',
                orgao_expeditor: '',
                termos: false,

                autocomplete_label_municipio_modal: '',
                autocomplete_label_municipio_modal_anterior: '',

                pcd: '',
                cid: '',
                viajar: '',

                qualificacoes: [
                    {
                        nova: '',
                        nome: '',
                        instituicao: '',
                        mes_conclusao: '01',
                        ano_conclusao: '2019'
                    }
                ],
                qualificacoesDelete: [],

                experiencias: [
                    {
                        nova: '',
                        empresa: '',
                        cargo: '',
                        principais_atv: '',
                        data_inicio: '',
                        data_fim: '',
                        referencia_nome: '',
                        referencia_telefone: ''
                    }
                ],
                experienciasDelete: [],

                telefones: [
                    {
                        detalhe: '',
                        novo: true,
                        numero: '',
                        pais: '55',
                        ramal: '',
                        tipo: 'whatsapp',
                        principal: false
                    }
                ],
                telefonesDelete: []
            }
        },
        visualizar: {
            type: Boolean,
            default: false
        }
    }
}
</script>

<style scoped></style>
<!--



<template>
    <div id="componenteVagasAbertas">

        <modal id="janelaBloqueiaCandidato" titulo="Candidatar a vaga" v-show="bloqueado" ref="modal_janelaBloqueiaCandidato">
            <template #conteudo>
                <div>
                    <h5>OPS... Você ja está cadastrado nesta vaga.</h5>
                </div>
            </template>
        </modal>

        <modal id="janelaConfirmar" :fechar="false" titulo="Confirmação" ref="modal_janelaConfirmar">
            <template #conteudo>
                <div>
                    <h5>Você tem certeza que deseja cadastrar seu curriculo SEM EXPERIÊNCIA?</h5>
                    <div class="text-center">
                        <button class="btn btn-danger" data-dismiss="modal" @click="addLIExperiencia">Não</button>
                        <button class="btn btn-success" @click="semexperienciaSim">SIM</button>
                    </div>
                </div>
            </template>
        </modal>

        <modal id="janelaPrimeiro" titulo="Cadastro" v-if="!autenticado" :size="90" :fechar="!autenticado" ref="modal_janelaPrimeiro">
            <template #conteudo>
                <fieldset>
                <legend>Informe</legend>
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" class="form-control" v-model="formAcesso.nome" placeholder="Nome Completo"
                           onblur="valida_campo_vazio(this,1)">
                </div>
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text" v-mascara:cpf class="form-control" v-model="formAcesso.cpf" placeholder="CPF"
                           onblur="valida_campo_vazio(this,1)">
                </div>
                <div class="form-group">
                    <label>Data Nascimento</label>
                    <input type="text" class="form-control" v-model="formAcesso.data_nascimento"
                           placeholder="Ex: 10/10/2010"
                           v-mascara:data onblur="valida_data_vazio(this)">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" v-model="formAcesso.login"
                           placeholder="Email" onblur="validaEmailVazio(this)">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input :type="inputType" class="form-control" v-model="formAcesso.password"
                           placeholder="Senha" onblur="valida_campo_vazio(this,1)">
                    <div class="switchToggle">
                        <input type="checkbox" @click="mostrandoSenha" id="mostraSenhaPrimeiro">
                        <label for="mostraSenhaPrimeiro"> Mostra Senha</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switchToggle">
                        <input type="checkbox" v-model="formAcesso.termos" id="switch">
                        <label for="switch"> Concordo com os Termos e Condições.</label>
                    </div>
                </div>
                <button class="btn btn-primary btn-block" :disabled="formAcesso.termos === false"
                        @click="cadastroPrimeiro">Cadastrar
                </button>
                </fieldset>
            </template>
        </modal>

        <modal id="janelaEntrar" titulo="Entrar" :size="90" :fechar="!autenticado" ref="modal_janelaEntrar">
            <template #conteudo>
                <fieldset>
                <legend>Informe</legend>
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" class="form-control" v-model="form.login"
                           placeholder="Email" onblur="validaEmailVazio(this)">
                </div>

                <div class="form-group">
                    <label>Senha</label>
                    <input :type="inputType" class="form-control" v-model="form.password"
                           placeholder="Senha" id="senha" onblur="valida_campo_vazio(this,1)">
                    <div class="switchToggle">
                        <input type="checkbox" @click="mostrandoSenha" id="mostraSenhaEntrar">
                        <label for="mostraSenhaEntrar"> Mostra Senha</label>
                    </div>
                </div>
                <button class="btn btn-primary btn-block" @click="autenticar">Entrar</button>
                <br>
                <div class="form-group text-left">
                    <a href="javascript://"
                       @click="formPrimeiro; $refs.modal_janelaPrimeiro && $refs.modal_janelaPrimeiro.abrirModal()">Primeiro Acesso</a>
                </div>
                </fieldset>
            </template>
        </modal>

        <modal id="janelaCadastrar" v-show="!bloqueado && id  ref="modal_janelaCadastrar"> 0" :titulo="titulo_vaga" :fechar="!preloadAjax"
               :size="90">
            <template #conteudo>

                <span v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</span>

                <div v-if="exibiFormulario || autenticado">

                    <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                        <p style="font-size: 1.2rem" class="text-center"><i class="icon fa fa-check"></i>
                            A confirmação do seu cadastro foi enviada por e-mail, verifique sua CAIXA DE ENTRADA ou LIXO
                            ELETRÔNICO/SPAM.
                            <br>
                            Caso esteja no LIXO ELETRÔNICO/SPAM marque que não é SPAM, pois todas as etapas serão
                            enviadas
                            dessa forma por e-mail.
                        </p>
                        <br>
                        <img src="https://sgibpse.com.br/imagens/bepinhas/branca_2.png" alt="bepinha"
                             class="img-fluid mx-auto d-flex">
                    </div>
                    <div class="alert alert-success alert-dismissible" v-show="atualizado">
                        <p style="font-size: 1.2rem" class="text-center"><i class="icon fa fa-check"></i>
                            A confirmação do seu cadastro foi enviada por e-mail, verifique sua CAIXA DE ENTRADA ou LIXO
                            ELETRÔNICO/SPAM.
                            <br>
                            Caso esteja no LIXO ELETRÔNICO/SPAM marque que não é SPAM, pois todas as etapas serão
                            enviadas
                            dessa forma por e-mail.
                        </p>
                        <br>
                        <img src="https://sgibpse.com.br/imagens/bepinhas/branca_2.png" alt="bepinha"
                             class="img-fluid mx-auto d-flex">
                    </div>

                    <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                        <fieldset>
                            <legend>Dados Pessoais</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Nome</label>
                                        <input type="text" class="form-control" v-model="form.nome"
                                               placeholder="Nome"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>CPF</label>
                                        <input type="text" class="form-control" v-model="form.cpf"
                                               placeholder="CPF"
                                               :disabled="possuiCadastro"
                                               autocomplete="mastertag" v-mascara:cpf onblur="valida_cpf_vazio(this)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>RG</label>
                                        <input type="text" class="form-control" v-model="form.rg"
                                               placeholder="RG"
                                               autocomplete="mastertag" v-mascara:numero>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Orgão Expeditor (RG)</label>
                                        <input type="text" class="form-control" v-model="form.orgao_expeditor"
                                               placeholder="Orgão"
                                               autocomplete="mastertag">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>CNH</label>
                                        <input type="text" class="form-control" v-model="form.cnh"
                                               placeholder="Tipo da CNH"
                                               autocomplete="mastertag">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Nascimento</label>
                                        <input type="text" class="form-control" v-model="form.nascimento"
                                               placeholder="Ex: 10/10/2010"
                                               :disabled="possuiCadastro"
                                               v-mascara:data
                                               autocomplete="mastertag" onblur="valida_data_vazio(this)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Sexo</label>
                                        <select class="form-control" v-model="form.sexo"
                                                onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)">
                                            <option :value="null">Selecione</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Feminino">Feminino</option>
                                            <option value="Indefinido">Indefinido</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Nome do pai</label>
                                        <input type="text" class="form-control" v-model="form.filiacao_pai"
                                               placeholder="Nome"
                                               autocomplete="mastertag">
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Nome da mãe</label>
                                        <input type="text" class="form-control" v-model="form.filiacao_mae"
                                               onblur="valida_campo_vazio(this,3)"
                                               placeholder="Nome"
                                               autocomplete="mastertag">
                                    </div>
                                </div>

                                <div class="col-12"></div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <p class="alert alert-danger">
                                        E-mail inválido ou inexistente, desclassificará o candidato.<br>
                                        Só altere se necessário, este email será usado para seu login.
                                    </p>
                                    <div class="form-group">
                                        <label>E-mail</label>
                                        <input type="text" class="form-control" v-model="form.email"
                                               placeholder="Informe seu melhor e-mail"
                                               autocomplete="mastertag" onblur="validaEmailVazio(this)">
                                    </div>
                                </div>

                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Endereço</legend>
                            <div class="row">
                                <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                                    <endereco :model="form"></endereco>
                                </div>
                            </div>
                        </fieldset>


                        <fieldset class="table-warning">
                            <legend>Contato</legend>
                            <label class="text-danger">Adicionar no minimo DOIS contatos, pelo menos UM sendo
                                WhatsApp</label>
                            <div class="row">
                                <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                                    <telefone :model="form.telefones" :model-delete="form.telefonesDelete" :pais="false"
                                              :ramal="false"></telefone>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Formação</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Formação</label>
                                        <select name="formacao" class="form-control" v-model="form.formacao"
                                                onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)">
                                            <option :value="null">Selecione</option>
                                            <option v-for="(item, index) in escolaridades" :value="item.id">{{item.tipo}}
                                            :key="item.id || index"
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Instituição</label>
                                        <input type="text" class="form-control" v-model="form.formacao_instituicao"
                                               placeholder="Ex: USP"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6" v-show="form.formacao >=8">
                                    <div class="form-group">
                                        <label>Curso</label>
                                        <input type="text" class="form-control" v-model="form.formacao_curso"
                                               placeholder="Ex: Administração"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control" v-model="form.formacao_status"
                                                onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)">
                                            <option :value="null">Selecione</option>
                                            <option value="Concluido">Concluido</option>
                                            <option value="Trancado">Trancado</option>
                                            <option value="Cursando">Cursando</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </fieldset>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group table-warning py-2 px-2">
                                    <label>Possuí Experiências?</label>
                                    <select class="form-control" v-model="form.temexperiencia">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <fieldset v-if="form.temexperiencia">
                            <legend>Experiências</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                    <button class="btn btn-success mb-2" @click="addLIExperiencia($event.target)">
                                        <span class="fas fa-plus" aria-hidden="true"></span>
                                        ADICIONAR EXPERIÊNCIA
                                    </button>
                                </div>
                            </div>
                            <div class="row" v-show="form.experiencias.length > 0"
                                 v-for="(obj, index) in form.experiencias" :key="obj.id" style="margin-bottom: 13px">
                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Empresa</label>
                                        <input type="text" class="form-control" v-model="obj.empresa"
                                               placeholder="Nome da Empresa"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Cargo</label>
                                        <input type="text" class="form-control" v-model="obj.cargo"
                                               placeholder="Cargo"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Nome Referência</label>
                                        <input type="text" class="form-control" v-model="obj.referencia_nome"
                                               placeholder="Ex: Ana"
                                               autocomplete="mastertag">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Telefone Referência</label>
                                        <input type="text" class="form-control" v-model="obj.referencia_telefone"
                                               placeholder="Ex: (98) 9 9999-9999"
                                               autocomplete="mastertag" v-mascara:telefone
                                        >
                                    </div>
                                </div>

                                <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                                    <div class="form-group">
                                        <label>Principais Atividades</label>
                                        <textarea class="form-control" v-model="obj.principais_atv"
                                                  placeholder="Ex: Recepcionista atendimento ao cliente"
                                                  autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                </textarea>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                    <div class="form-group">
                                        <label>Data Inicio</label>
                                        <input type="text" class="form-control" v-model="obj.data_inicio"
                                               placeholder="Ex: 02/04/2012"
                                               autocomplete="mastertag" v-mascara:data onblur="valida_data_vazio(this)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                    <div class="form-group">
                                        <label>Data Fim </label>
                                        <input type="text" class="form-control" v-model="obj.data_fim"
                                               placeholder="Se for atual deixe em branco"
                                               autocomplete="mastertag" v-mascara:data onblur="valida_data(this)">
                                    </div>
                                </div>

                                <div class="col-1">
                                    <button class="btn btn-danger" style="margin-top: 24px;"
                                            @click="removerLIExperiencia(index)"><i
                                        class="fa fa-times"></i></button>
                                </div>

                                <div class="col-12">
                                    <hr>
                                </div>

                            </div>
                        </fieldset>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group table-warning py-2 px-2">
                                    <label>Possuí Qualificações?</label>
                                    <select class="form-control" v-model="form.temqualificacao">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <fieldset v-if="form.temqualificacao">
                            <legend>Qualificações</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                    <button class="btn btn-success mb-2" @click="addLIQualificacao($event.target)">
                                        <span class="fas fa-plus" aria-hidden="true"></span>
                                        ADCIONAR QUALIFICAÇÃO
                                    </button>
                                </div>
                            </div>
                            <div class="row" v-show="form.qualificacoes.length>0"
                                 v-for="(obj, index) in form.qualificacoes" :key="obj.id" style="margin-bottom: 13px">

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Curso</label>
                                        <input type="text" class="form-control" v-model="obj.nome"
                                               placeholder="Ex: Tecnico em Contabilidade"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Instituição</label>
                                        <input type="text" class="form-control" v-model="obj.instituicao"
                                               placeholder="Ex: SEBRAE"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                    <div class="form-group">
                                        <label>Mês Conclusão</label>
                                        <select class="form-control" onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)" v-model="obj.mes_conclusao">
                                            <option value="01">01</option>
                                            <option value="02">02</option>
                                            <option value="03">03</option>
                                            <option value="04">04</option>
                                            <option value="05">05</option>
                                            <option value="06">06</option>
                                            <option value="07">07</option>
                                            <option value="08">08</option>
                                            <option value="09">09</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                    <div class="form-group">
                                        <label>Ano Conclusão</label>
                                        <input type="text" class="form-control" v-model="obj.ano_conclusao"
                                               placeholder="Ex: Concluido"
                                               autocomplete="mastertag" v-mascara:numero
                                               onblur="valida_campo_vazio(this,4)">
                                    </div>
                                </div>

                                <div class="col-1">
                                    <button class="btn btn-danger" style="margin-top: 24px;"
                                            @click="removerLIQualificacao(index)"><i
                                        class="fa fa-times"></i></button>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Vaga Pretendida</legend>
                            <div class="row">
                                <div class="col-12 col-sm-3">
                                    <div class="form-group">
                                        <label>Cota pessoa com deficiência (Lei nº 8.213/91)</label>
                                        <select class="form-control" onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)" v-model="form.pcd">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-3" v-if="form.pcd">
                                    <div class="form-group">
                                        <label>CID (Código Internacional de Doenças)</label>
                                        <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                               placeholder="Informe o CID" v-model="form.cid">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-3">
                                    <div class="form-group">
                                        <label>Disponibilidade para viajar</label>
                                        <select class="form-control" v-model="form.viajar">
                                            <option value="">Selecione</option>
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label>Vaga Escolhida</label>
                                        <select class="form-control" name="vaga_pretendida"
                                                v-model="form.vaga_pretendida"
                                                onblur="valida_campo_vazio(this,1)"
                                                onchange="valida_campo_vazio(this,1)" :disabled="true">
                                            <option value="">Selecione ...</option>
                                            <option v-for="(vaga, index) in vagas" :value="vaga.vaga_id">{{vaga.vaga.nome}}
                                            :key="vaga.id || index"
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label>Município</label>
                                        <autocomplete :caminho="todos_municipios"
                                                      :valido="form.municipio_id !== ''"
                                                      v-model="form.autocomplete_label_municipio_modal"
                                                      placeholder="Informe o Município"
                                                      :id="`mun_${hash}`"
                                                      :disabled="true"
                                                      @onblur="resetaCampoMunicipioModal"
                                                      @onSelect="selecionaMunicipioModal">
                                        </autocomplete>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </template>
            <template #rodape>
                <div v-show="autenticado && !preloadAjax">
                    <button type="button" class="btn btn-primary"
                            @click="cadastrar">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                </div>
            </template>
        </modal>

        <modal id="janelaBancoTalentos" v-show="autenticado && id  ref="modal_janelaBancoTalentos"> 0" titulo="Monte seu Currículo"
               :fechar="!preloadAjax"
               :size="90">
            <template #conteudo>

                <span v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</span>

                <div v-if="bancoTalento">

                    <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                        <p style="font-size: 1.2rem" class="text-center"><i class="icon fa fa-check"></i>
                            A confirmação do seu cadastro foi enviada por e-mail, verifique sua CAIXA DE ENTRADA ou LIXO
                            ELETRÔNICO/SPAM.
                            <br>
                            Caso esteja no LIXO ELETRÔNICO/SPAM marque que não é SPAM, pois todas as etapas serão
                            enviadas
                            dessa forma por e-mail.
                        </p>
                        <br>
                    </div>
                    <div class="alert alert-success alert-dismissible" v-show="atualizado">
                        <p style="font-size: 1.2rem" class="text-center"><i class="icon fa fa-check"></i>
                            A confirmação do seu cadastro foi enviada por e-mail, verifique sua CAIXA DE ENTRADA ou LIXO
                            ELETRÔNICO/SPAM.
                            <br>
                            Caso esteja no LIXO ELETRÔNICO/SPAM marque que não é SPAM, pois todas as etapas serão
                            enviadas
                            dessa forma por e-mail.
                        </p>
                        <br>
                    </div>

                    <form v-if="!preloadAjax && !cadastrado" id="formBancoTalentos"
                          onsubmit="return false;">

                        <fieldset>
                            <legend>Dados Pessoais</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Nome</label>
                                        <input type="text" class="form-control" v-model="form.nome"
                                               placeholder="Nome"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>CPF</label>
                                        <input type="text" class="form-control" v-model="form.cpf"
                                               placeholder="CPF"
                                               :disabled="possuiCadastro"
                                               autocomplete="mastertag" v-mascara:cpf onblur="valida_cpf_vazio(this)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>RG</label>
                                        <input type="text" class="form-control" v-model="form.rg"
                                               placeholder="RG"
                                               autocomplete="mastertag" v-mascara:numero>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Orgão Expeditor (RG)</label>
                                        <input type="text" class="form-control" v-model="form.orgao_expeditor"
                                               placeholder="Orgão"
                                               autocomplete="mastertag">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>CNH</label>
                                        <input type="text" class="form-control" v-model="form.cnh"
                                               placeholder="Tipo da CNH"
                                               autocomplete="mastertag">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Nascimento</label>
                                        <input type="text" class="form-control" v-model="form.nascimento"
                                               placeholder="Ex: 10/10/2010"
                                               :disabled="possuiCadastro"
                                               v-mascara:data
                                               autocomplete="mastertag" onblur="valida_data_vazio(this)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Sexo</label>
                                        <select class="form-control" v-model="form.sexo"
                                                onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)">
                                            <option value="">Selecione</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Feminino">Feminino</option>
                                            <option value="Indefinido">Indefinido</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Nome do pai</label>
                                        <input type="text" class="form-control" v-model="form.filiacao_pai"
                                               placeholder="Nome"
                                               autocomplete="mastertag">
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Nome da mãe</label>
                                        <input type="text" class="form-control" v-model="form.filiacao_mae"
                                               onblur="valida_campo_vazio(this,3)"
                                               placeholder="Nome"
                                               autocomplete="mastertag">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Endereço</legend>
                            <div class="row">
                                <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                                    <endereco :model="form"></endereco>
                                </div>
                            </div>
                        </fieldset>


                        <fieldset class="table-warning">
                            <legend>Contato</legend>
                            <label class="text-danger">Adicionar no minimo DOIS contatos, pelo menos UM sendo
                                WhatsApp</label>
                            <div class="row">
                                <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                                    <telefone :model="form.telefones" :model-delete="form.telefonesDelete" :pais="false"
                                              :ramal="false" :principal="false"></telefone>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Formação</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Formação</label>
                                        <select name="formacao" class="form-control" v-model="form.formacao"
                                                onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)">
                                            <option value="">Selecione</option>
                                            <option v-for="(item, index) in escolaridades" :value="item.id">{{item.tipo}}
                                            :key="item.id || index"
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Instituição</label>
                                        <input type="text" class="form-control" v-model="form.formacao_instituicao"
                                               placeholder="Ex: USP"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6" v-show="form.formacao >=8">
                                    <div class="form-group">
                                        <label>Curso</label>
                                        <input type="text" class="form-control" v-model="form.formacao_curso"
                                               placeholder="Ex: Administração"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control" v-model="form.formacao_status"
                                                onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)">
                                            <option value="">Selecione</option>
                                            <option value="Concluido">Concluido</option>
                                            <option value="Trancado">Trancado</option>
                                            <option value="Cursando">Cursando</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </fieldset>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group table-warning py-2 px-2">
                                    <label>Possuí Experiências?</label>
                                    <select class="form-control" v-model="form.temexperiencia">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <fieldset v-if="form.temexperiencia">
                            <legend>Experiências</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                    <button class="btn btn-success mb-2" @click="addLIExperiencia($event.target)">
                                        <span class="fas fa-plus" aria-hidden="true"></span>
                                        ADICIONAR EXPERIÊNCIA
                                    </button>
                                </div>
                            </div>
                            <div class="row" v-show="form.experiencias.length > 0"
                                 v-for="(obj, index) in form.experiencias" :key="obj.id" style="margin-bottom: 13px">
                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Empresa</label>
                                        <input type="text" class="form-control" v-model="obj.empresa"
                                               placeholder="Nome da Empresa"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Cargo</label>
                                        <input type="text" class="form-control" v-model="obj.cargo"
                                               placeholder="Cargo"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Nome Referência</label>
                                        <input type="text" class="form-control" v-model="obj.referencia_nome"
                                               placeholder="Ex: Ana"
                                               autocomplete="mastertag">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Telefone Referência</label>
                                        <input type="text" class="form-control" v-model="obj.referencia_telefone"
                                               placeholder="Ex: (98) 9 9999-9999"
                                               autocomplete="mastertag" v-mascara:telefone
                                        >
                                    </div>
                                </div>

                                <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                                    <div class="form-group">
                                        <label>Principais Atividades</label>
                                        <textarea class="form-control" v-model="obj.principais_atv"
                                                  placeholder="Ex: Recepcionista atendimento ao cliente"
                                                  autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                </textarea>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                    <div class="form-group">
                                        <label>Data Inicio</label>
                                        <input type="text" class="form-control" v-model="obj.data_inicio"
                                               placeholder="Ex: 02/04/2012"
                                               autocomplete="mastertag" v-mascara:data onblur="valida_data_vazio(this)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                    <div class="form-group">
                                        <label>Data Fim </label>
                                        <input type="text" class="form-control" v-model="obj.data_fim"
                                               placeholder="Se for atual deixe em branco"
                                               autocomplete="mastertag" v-mascara:data onblur="valida_data(this)">
                                    </div>
                                </div>

                                <div class="col-1">
                                    <button class="btn btn-danger" style="margin-top: 24px;"
                                            @click="removerLIExperiencia(index)"><i
                                        class="fa fa-times"></i></button>
                                </div>

                                <div class="col-12">
                                    <hr>
                                </div>

                            </div>
                        </fieldset>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group table-warning py-2 px-2">
                                    <label>Possuí Qualificações?</label>
                                    <select class="form-control" v-model="form.temqualificacao">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <fieldset v-if="form.temqualificacao">
                            <legend>Qualificações</legend>
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                    <button class="btn btn-success mb-2" @click="addLIQualificacao($event.target)">
                                        <span class="fas fa-plus" aria-hidden="true"></span>
                                        ADCIONAR QUALIFICAÇÃO
                                    </button>
                                </div>
                            </div>
                            <div class="row" v-show="form.qualificacoes.length>0"
                                 v-for="(obj, index) in form.qualificacoes" :key="obj.id" style="margin-bottom: 13px">

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Curso</label>
                                        <input type="text" class="form-control" v-model="obj.nome"
                                               placeholder="Ex: Tecnico em Contabilidade"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>Instituição</label>
                                        <input type="text" class="form-control" v-model="obj.instituicao"
                                               placeholder="Ex: SEBRAE"
                                               autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                    <div class="form-group">
                                        <label>Mês Conclusão</label>
                                        <select class="form-control" onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)" v-model="obj.mes_conclusao">
                                            <option value="01">01</option>
                                            <option value="02">02</option>
                                            <option value="03">03</option>
                                            <option value="04">04</option>
                                            <option value="05">05</option>
                                            <option value="06">06</option>
                                            <option value="07">07</option>
                                            <option value="08">08</option>
                                            <option value="09">09</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-2 col-lg-2 col-xl-2">
                                    <div class="form-group">
                                        <label>Ano Conclusão</label>
                                        <input type="text" class="form-control" v-model="obj.ano_conclusao"
                                               placeholder="Ex: Concluido"
                                               autocomplete="mastertag" v-mascara:numero
                                               onblur="valida_campo_vazio(this,4)">
                                    </div>
                                </div>

                                <div class="col-1">
                                    <button class="btn btn-danger" style="margin-top: 24px;"
                                            @click="removerLIQualificacao(index)"><i
                                        class="fa fa-times"></i></button>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Outras Informações</legend>
                            <div class="row">
                                <div class="col-12 col-sm-3">
                                    <div class="form-group">
                                        <label>Cota pessoa com deficiência (Lei nº 8.213/91)</label>
                                        <select class="form-control" onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)" v-model="form.pcd">
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-3" v-if="form.pcd">
                                    <div class="form-group">
                                        <label>CID (Código Internacional de Doenças)</label>
                                        <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                               placeholder="Informe o CID" v-model="form.cid">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-3">
                                    <div class="form-group">
                                        <label>Disponibilidade para viajar</label>
                                        <select class="form-control" v-model="form.viajar">
                                            <option :value="true">Sim</option>
                                            <option :value="false">Não</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        &lt;!&ndash;                        <fieldset>&ndash;&gt;
                        &lt;!&ndash;                            <div class="col-12 col-sm-6">&ndash;&gt;
                        &lt;!&ndash;                                <div class="switchToggle">&ndash;&gt;
                        &lt;!&ndash;                                    <input type="checkbox" v-model="form.termos" id="termos">&ndash;&gt;
                        &lt;!&ndash;                                    <label for="termos"> Concordo com os Termos e Condições.</label>&ndash;&gt;
                        &lt;!&ndash;                                </div>&ndash;&gt;
                        &lt;!&ndash;                            </div>&ndash;&gt;
                        &lt;!&ndash;                        </fieldset>&ndash;&gt;
                    </form>
                </div>
            </template>
            <template #rodape>
                <div v-show="autenticado && bancoTalento">
                    <button type="button" class="btn btn-primary"
                            @click="cadastrarBancoTalento">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                </div>
            </template>
        </modal>

        <main class="py-5 my-5">
            <div class="container">
                <header class="section-header py-5">
                    <h3 v-if="vaga_id === ''">VAGAS ABERTAS</h3>
                    <h3 v-else>VAGA ABERTA</h3>
                </header>

                <span v-show="preloadVagas"><i class="fa fa-spinner fa-pulse"></i> Carregando...</span>
                <fieldset v-for="(vaga, index) in vagas" v-show="!preloadVagas">
                :key="vaga.id || index"
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">{{ vaga.vaga.nome }}</h5>
                            <p class="card-text" v-html="vaga.descricao"></p>
                            <p class="card-subtitle mb-2 text-muted"><i class="fas fa-map-marker-alt"></i>
                                {{ vaga.municipio.nome }} - {{ vaga.municipio.uf }}
                            </p>

                            <div class="float-md-left" v-if="id !== 0">
                                <button type="button" class="btn mr-1 mb-2 btn-primary"
                                        @click="selecionaVaga(vaga); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()">CANDIDATAR-SE
                                </button>
                            </div>

                            <div class="float-md-left" v-if="id === 0">
                                <button type="button" class="btn mr-1 mb-2 btn-primary"
                                        @click="formEntrar(); $refs.modal_janelaEntrar && $refs.modal_janelaEntrar.abrirModal()">CANDIDATAR-SE
                                </button>
                            </div>
                            <div class="float-md-right">
                                <a class="btn mr-1 mb-2 btn-outline-primary"
                                   :href="`https://api.whatsapp.com/send?text=Vaga aberta no site da ${slug} para ${vaga.vaga.nome} -  ${ vaga.municipio.nome } - ${ vaga.municipio.uf } é só acessar ${uriPadrao}/vagas-abertas/${vaga.id}/${slug}`"
                                   target="_blank"><i
                                    class="fab fa-whatsapp"></i> COMPARTILHAR</a>
                                <a class="btn mr-1 mb-2 btn-outline-primary"
                                   :href="`https://www.facebook.com/sharer/sharer.php?u=${uriPadrao}/vagas-abertas/${vaga.id}/${slug}`"
                                   target="_blank"><i
                                    class="fab fa-facebook"></i> COMPARTILHAR</a>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <template v-show="!preloadVagas">
                    <div v-if="vagas.length === 0" class="alert alert-warning alert-dismissible">
                        <p style="font-size: 1.2rem" class="text-center"><i class="icon fa fa-times"></i> Nenhuma Vaga
                            Encontrada
                        </p>
                    </div>
                    <div v-if="vaga_id !== ''" class="alert alert-secondary text-center" role="alert">
                        <h2 class="text-center">QUER VER TODAS AS VAGAS ABERTAS DA EMPRESA?</h2>
                        <a class="btn btn-outline-primary py-2 px-3" style="font-size: 1.2rem;"
                           @click="redirecionaInicio">
                            CLIQUE AQUI
                        </a>
                    </div>
                </template>
                <template v-show="!preloadVagas">
                    <div v-if="!autenticado" class="alert alert-secondary text-center" role="alert">
                        <h2 class="text-center">NÃO ENCONTROU A SUA VAGA?</h2>
                        <h4 class="text-center">Então envie o seu CV para nosso Banco de Talentos!</h4>
                        <a class="btn btn-outline-primary py-2 px-3" style="font-size: 1.2rem;"
                           @click="formEntrar; $refs.modal_janelaEntrar && $refs.modal_janelaEntrar.abrirModal()">
                            ENVIAR AGORA
                        </a>
                    </div>
                    <div v-if="autenticado || id > 0" class="alert alert-secondary text-center" role="alert">
                        <h2 class="text-center">NÃO ENCONTROU A SUA VAGA?</h2>
                        <h4 class="text-center">Então envie o seu CV para nosso Banco de Talentos!</h4>
                        <a class="btn btn-outline-primary py-2 px-3" style="font-size: 1.2rem;"
                           @click="formBancoTalentos; $refs.modal_janelaBancoTalentos && $refs.modal_janelaBancoTalentos.abrirModal()">
                            ENVIAR AGORA
                        </a>
                    </div>
                </template>
            </div>
        </main>
    </div>
</template>

<script>
import controlePaginacao from '../ControlePaginacao';
import modal from '../Modal';
import editor from '@tinymce/tinymce-vue';
import DatePicker from "../DatePicker";
import endereco from "../Endereco";
import telefone from '../Telefones'
import autocomplete from "../AutoComplete"

export default {
    components: {
        DatePicker,
        modal,
        controlePaginacao,
        editor,
        endereco,
        telefone,
        autocomplete,
    },
    props: {
        qntPag: {
            type: Number,
            required: false,
            default: 20
        },

        status: {
            type: Boolean,
            required: false,
            default: true
        },

        filtro: {
            type: Boolean,
            required: false,
            default: true
        },
        modal: { // modal Pai
            type: String,
            required: false,
            default: ''
        },
    },
    data() {
        return {
            preload: false,
            editando: false,
            cadastrado: false,
            atualizado: false,
            preloadVagas: false,
            autenticado: false,
            preloadAjax: true,
            bancoTalento: false,
            semvaga: false,
            slug: '',
            vaga_id: '',
            uriPadrao: '',
            id: '',
            inputType: 'password',
            titulo_vaga: '',

            lista: [],
            escolaridades: [],
            vagas: [],

            tituloJanela: 'Recrutamento',
            existe: false,

            possuiCadastro: false,
            cadastroNovo: false,
            bloqueado: false,

            exibiFormulario: false,

            semexperiencia: false,

            todos_municipios: `../todos-municipios`,
            hash: `mastertag_${parseInt((Math.random() * 999999))}`,

            form: {
                temqualificacao: false,
                temexperiencia: false,
                id: '',
                cpf: '',
                login: '',
                password: '',
                nome: '',
                cnh: '',
                nascimento: '',
                logradouro: '',
                complemento: '',
                bairro: '',
                municipio: '',
                uf: '',
                cep: '',
                email: '',
                filiacao_pai: '',
                filiacao_mae: '',
                formacao: '',
                formacao_instituicao: '',
                formacao_curso: '',
                formacao_status: '',
                vaga_pretendida: "",
                vaga_id: '',
                uf_vaga: '',
                municipio_id: '',
                rg: '',
                sexo: '',
                orgao_expeditor: '',
                termos: false,

                autocomplete_label_municipio_modal: '',
                autocomplete_label_municipio_modal_anterior: '',

                pcd: '',
                cid: '',
                viajar: '',

                qualificacoes: [{
                    nova: '',
                    nome: '',
                    instituicao: '',
                    mes_conclusao: '01',
                    ano_conclusao: '2019',
                }],
                qualificacoesDelete: [],

                experiencias: [{
                    nova: '',
                    empresa: '',
                    cargo: '',
                    principais_atv: '',
                    data_inicio: '',
                    data_fim: '',
                    referencia_nome: '',
                    referencia_telefone: '',
                }],
                experienciasDelete: [],

                telefones: [
                    {
                        detalhe: "",
                        novo: true,
                        numero: "",
                        pais: "55",
                        ramal: "",
                        tipo: "whatsapp",
                        principal: false
                    }
                ],
                telefonesDelete: []

            },
            formDefault: null,

            formAcesso: {
                nome: '',
                login: '',
                password: '',
                cpf: '',
                data_nascimento: '',
                termos: false,
            },

            formAcessoDefault: null,

            campoNome: null,
            list: [],

        }
    },
    computed: {
        mostraSenha() {
            return this.inputType === 'password'
        }
    },
    mounted() {
        this.preloadAjax = true;
        this.slug = $('meta[name="slug"]').attr('content');
        this.uriPadrao = $('meta[name="uri"]').attr('content');
        this.vaga_id = $('meta[name="vaga_id"]').attr('content');
        this.abriVagasAbertas();
        this.formDefault = _.cloneDeep(this.form)
        this.formAcessoDefault = _.cloneDeep(this.formAcesso)
    },
    methods: {

        mostrandoSenha() {
            this.inputType = this.mostraSenha ? 'text' : 'password';
        },

        autenticar() {
            this.autenticado = false;
            this.preloadAutenticacao = true;
            axios.post(`${URL_SITE}/autenticar`, {
                login: this.form.login,
                password: this.form.password
            }).then(response => {
                let data = response.data.curriculo;
                if (response.status === 200) {
                    Object.assign(this.form, data);
                    this.$refs.modal_janelaEntrar && this.$refs.modal_janelaEntrar.fecharModal();
                    mostraSucesso('Seja Bem Vindo(a) ' + this.form.nome);
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                    this.possuiCadastro = true;
                    this.exibiFormulario = true;
                    this.autenticado = true;
                    this.preloadAjax = false;
                }
            })
                .catch(error => {
                    this.autenticado = false;
                    this.preloadAutenticacao = false;
                })
        },

        selecionaVaga(obj) {
            this.bloqueado = false;
            axios.post(`${URL_SITE}/seleciona-vaga`, {vaga_id: obj.id, slug: this.slug})
                .then(response => {
                    if (response.data.vagas !== null) {
                        this.bloqueiaCandidato();
                    } else {
                        this.bloqueado = false;
                        this.formNovo();
                        this.form.vaga_pretendida = obj.vaga_id;
                        this.form.vaga_id = obj.id;
                        this.form.uf_vaga = obj.municipio.uf;
                        this.selecionaMunicipioModal({
                            id: obj.municipio_id,
                            label: `${obj.municipio.nome} - ${obj.municipio.uf}`
                        })
                    }
                }).catch(error => {
                mostraErro('', error);
                this.preloadAjax = false;
            })
        },

        bloqueiaCandidato() {
            this.bloqueado = true;
            this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal();
            this.$refs.modal_janelaBloqueiaCandidato && this.$refs.modal_janelaBloqueiaCandidato.abrirModal();
        },

        selecionaMunicipioModal(obj) {
            this.form.municipio_id = obj.id;
            this.form.autocomplete_label_municipio_modal = obj.label;
            this.form.autocomplete_label_municipio_modal_anterior = obj.label;
        },

        resetaCampoMunicipioModal() {
            if (this.form.autocomplete_label_municipio_modal_anterior !== this.form.autocomplete_label_municipio_modal) {
                this.form.autocomplete_label_municipio_modal_anterior = '';
                this.form.autocomplete_label_municipio_modal = '';
                this.form.municipio_id = '';

                valida_campo_vazio($('#mun_' + this.hash), 1);
                setTimeout(() => {
                    if (this.form.municipio_id === '') {
                        $('#janelaCadastrar #mun_' + this.hash).focus().trigger('blur');
                        mostraErro('', 'O Campo Município não pode ficar vazio');
                    }
                }, 100);
            }
        },

        addLIQualificacao() {
            let obj = {};
            obj.nova = true;
            obj.nome = '';
            obj.instituicao = '';
            obj.mes_conclusao = '01';
            obj.ano_conclusao = '2019';
            this.form.qualificacoes.push(obj);
        },

        removerLIQualificacao(index) {
            if (this.autenticado) {
                this.form.qualificacoesDelete.push(this.form.qualificacoes[index].id);
            }
            this.form.qualificacoes.splice(index, 1);
        },

        addLIExperiencia() {
            let obj = {};
            obj.nova = true;
            obj.empresa = '';
            obj.cargo = '';
            obj.principais_atv = '';
            obj.data_inicio = '';
            obj.data_fim = '';
            obj.referencia_nome = '';
            obj.referencia_telefone = '';
            this.form.experiencias.push(obj);
        },

        removerLIExperiencia(index) {
            if (this.autenticado) {
                this.form.experienciasDelete.push(this.form.experiencias[index].id);
            }
            this.form.experiencias.splice(index, 1);
        },

        buscaCurriculo() {
            this.preloadAjax = true;
            axios.post(`${URL_SITE}/busca-curriculo`, {id: this.id})
                .then(response => {
                    let data = response.data.curriculo;
                    if (response.status === 200) {
                        Object.assign(this.form, data);
                        this.possuiCadastro = true;
                        this.exibiFormulario = true;
                        this.autenticado = true;
                        this.preloadAjax = false;
                    }
                })
                .catch(error => {
                    mostraErro('', error);
                    this.preloadAjax = false;
                })

        },

        buscaCpf() {

            if (this.form.cpf.length === 14) {
                $.post(`${URL_SITE}/busca-cpf/`, {cpf: this.form.cpf})
                    .done((data) => {
                        if (data.length > 0) {
                            this.form.cpf = '';
                            let dataFormatada = data[0].created_at.substring(0, 10).split('-').reverse().join('/');
                            let Hora = data[0].created_at.substring(11, 16);
                            mostraErro('', `CPF ${data[0].cpf} pertencente a ${data[0].nome} já cadastrado em nossa base de dados em ${dataFormatada} às ${Hora}h`);
                        }
                    })
                    .fail((data) => {

                    });
            }
        },

        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.exibiFormulario = false;
            this.possuiCadastro = false;
            this.preloadAjax = false;
            this.semvaga = false;
            this.titulo_vaga = "Cadastrando a uma Vaga";

            formReset();
            setupCampo();
            // this.form = _.cloneDeep(this.formDefault) //copia
        },

        formBancoTalentosAutenticado() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.exibiFormulario = false;
            this.possuiCadastro = false;
            this.preloadAjax = false;
            this.semvaga = true;
            this.titulo_vaga = "Cadastrando currículo";

            formReset();
            setupCampo();
            // this.formDefault = _.cloneDeep(this.form) //copia
        },

        formEntrar() {
            // this.form = _.cloneDeep(this.formDefault) //copia
            this.autenticado = false;
            this.exibiFormulario = false;
            this.preloadAjax = false;

            formReset();
            setupCampo();
        },

        formPrimeiro() {
            this.formAcesso = _.cloneDeep(this.formAcessoDefault) //copia
            this.autenticado = false;
            this.exibiFormulario = false;
            this.preloadAjax = false;

            formReset();
            setupCampo();
        },

        formBancoTalentos() {
            this.cadastrado = false;
            this.preloadAjax = false;
            this.bancoTalento = true;

            formReset();
            setupCampo();
        },

        semexperienciaSim() {
            this.semexperiencia = true;
            this.$refs.modal_janelaConfirmar && this.$refs.modal_janelaConfirmar.fecharModal();
            this.cadastrar();
        },

        cadastrar() {
            if (this.form.municipio_id === '') {
                valida_campo_vazio($('#mun_' + this.hash), 1);
                $('#janelaCadastrar #mun_' + this.hash).focus().trigger('blur');
                mostraErro('Erro', 'O Campo Município não pode ficar vazio');
                return false;
            }

            $('#janelaCadastrar :input:visible').trigger('blur');
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            if (this.form.experiencias.length === 0 && !this.semexperiencia) {
                this.$refs.modal_janelaConfirmar && this.$refs.modal_janelaConfirmar.abrirModal();
                return false;
            }

            this.preloadAjax = true;

            this.form.slug = this.slug;
            axios.post(`${URL_SITE}/cadastro-curriculo`, this.form)
                .then(response => {
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal();
                    mostraSucesso('Você se candidatou a VAGA! Aguarde uns instantes que iremos lhe enviar um email de confirmação.');
                    this.form = _.cloneDeep(this.formDefault)
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                })
                .catch(error => {
                    mostraErro('', error);
                    this.preloadAjax = false;
                })
        },

        cadastroPrimeiro() {
            $('#janelaPrimeiro :input:visible').trigger('blur');
            if ($('#janelaPrimeiro :input:visible.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }
            this.formAcesso.slug = this.slug;
            axios.post(`${URL_SITE}/primeiro-acesso`, this.formAcesso)
                .then(response => {
                    this.$refs.modal_janelaEntrar && this.$refs.modal_janelaEntrar.fecharModal();
                    this.$refs.modal_janelaPrimeiro && this.$refs.modal_janelaPrimeiro.fecharModal();
                    mostraSucesso('Cadastro feito com sucesso! Seja Bem Vindo(a).');
                    setTimeout(function () {
                        window.location.reload();
                    }, 3000);
                })
                .catch(error => {
                    mostraErro('', error);
                    this.preloadAjax = false;
                })
        },

        cadastrarBancoTalento() {
            $('#janelaBancoTalentos :input:visible').trigger('blur');
            if ($('#janelaBancoTalentos :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            if (this.form.experiencias.length === 0 && !this.semexperiencia) {
                this.$refs.modal_janelaConfirmar && this.$refs.modal_janelaConfirmar.abrirModal();
                return false;
            }

            this.preloadAjax = true;

            if (this.bancoTalento === true) {
                this.form.slug = this.slug;
                this.form.vagas_abertas_id = '';
                axios.post(`${URL_SITE}/cadastro-banco-de-talentos`, this.form)
                    .then(response => {
                        if (response.status === 201) {
                            this.$refs.modal_janelaBancoTalentos && this.$refs.modal_janelaBancoTalentos.fecharModal();
                            mostraSucesso('Você cadastrou um currículo!');
                            setTimeout(function () {
                                window.location.reload();
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        mostraErro('', error);
                        this.preloadAjax = false;
                    })
            } else {
                mostraErro('', 'Você não marcou a opção TERMOS E CONDIÇÕES');
                return false;
            }
        },

        redirecionaInicio() {
            window.location.assign(`${this.uriPadrao}/vagas-abertas/${this.slug}`);
        },

        abriVagasAbertas() {
            this.preloadVagas = true;
            this.bloqueado = false;
            axios.post(`${URL_SITE}/vagas-abertas-busca`, {slug: this.slug, vaga_id: this.vaga_id})
                .then(response => {
                    if (response.status === 200 && response.data.curriculo !== '') {
                        this.vagas = response.data.vagas;
                        this.id = response.data.id;
                        this.escolaridades = response.data.escolaridades;
                        this.preloadVagas = false;
                        Object.assign(this.form, response.data.curriculo);
                        this.possuiCadastro = true;
                        this.exibiFormulario = true;
                        this.autenticado = true;
                        this.bancoTalento = false;
                    }
                    if (response.status === 200 && response.data.curriculo === '') {
                        this.vagas = response.data.vagas;
                        this.id = response.data.id;
                        this.escolaridades = response.data.escolaridades;
                        this.preloadVagas = false;
                        this.bancoTalento = true;

                    }
                })
                .catch(error => {
                    this.preloadVagas = false;
                    mostraErro(error)
                })
        },

        async logout() {
            try {
                const response = await axios.get(`${URL_SITE}/sair/${this.slug}`);
                if (response.status === 200) {
                    window.location.href = `${URL_SITE}/vagas-abertas-busca/` + response.data.slug;
                    mostraSucesso('Você acabou de sair do sistema!', 200);
                }
            } catch (error) {
                mostraErro('Há algo de errado!', error);
                this.abriVagasAbertas();
            } finally {
                this.preloadAjax = false;
            }
        },
    }
}
</script>

<style scoped>
.card {
    border: none;
    background: transparent;
}

ul.timeline {
    list-style-type: none;
    position: relative;
}

ul.timeline:before {
    content: ' ';
    background: #d4d9df;
    display: inline-block;
    position: absolute;
    left: 29px;
    width: 2px;
    height: 100%;
    z-index: 400;
}

ul.timeline > li {
    margin: 20px 0;
    padding-left: 20px;
}

ul.timeline > li:before {
    content: ' ';
    background: white;
    display: inline-block;
    position: absolute;
    border-radius: 50%;
    border: 3px solid #184056;
    left: 20px;
    width: 20px;
    height: 20px;
    z-index: 400;
}

.trackind {
    padding: .5rem .8rem;
    background-color: #f4f4f4;
    border-radius: .5rem;
}
</style>-->
