<template>
    <div>
        <modal ref="modalConfirmar" id="janelaConfirmar" titulo="Apagar">
            <template #conteudo>
                <preload v-show="preloadAjax"></preload>
                <div class="alert alert-success alert-dismissible" v-show="apagado">
                    <h4><i class="icon fa fa-check"></i>Registro apagado com sucesso!</h4>
                </div>
                <h4 v-show="!apagado">Tem certeza que deseja apagar este registro?</h4>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-danger" @click="apagar" v-show="!apagado">Apagar</button>
            </template>
        </modal>

        <modal ref="modalCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :size="90">
            <template #conteudo>
                <preload v-show="preloadAjax"></preload>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h4><i class="icon fa fa-check"></i>{{ form.tipo }} cadastrado com sucesso!</h4>
                </div>
                <div class="alert alert-success alert-dismissible" v-show="atualizado">
                    <h4><i class="icon fa fa-check"></i>{{ form.tipo }} alterado com sucesso!</h4>
                </div>
                <form v-if="!preloadAjax && !cadastrado && !atualizado" id="form" @submit.prevent>
                    <fieldset>
                        <legend class="text-uppercase">Tipo</legend>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <label>Selecione o Tipo</label>
                                <select
                                    class="form-control"
                                    v-model="form.tipo"
                                    :disabled="editando"
                                    @blur="valida_campo_vazio($event.target, 1)"
                                    @change="valida_campo_vazio($event.target, 1)"
                                >
                                    <option value="">Selecione ...</option>
                                    <option value="fornecedor">Fornecedor</option>
                                    <option value="parceiro">Parceiro</option>
                                    <option value="terceiro">Terceiro</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <div v-if="form.tipo !== ''">
                        <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist">
                            <li class="nav-item">
                                <a class="nav-item nav-link active" id="nav-dados-cadastrais-tab" href="#nav-dados-cadastrais" role="tab">DADOS CADASTRAIS</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-item nav-link" id="nav-servicos-tab" href="#nav-servicos" role="tab">SERVIÇOS</a>
                            </li>
                        </ul>

                        <div class="tab-content py-3 p-2">
                            <!-- Dados Cadastrais -->
                            <div class="tab-pane fade show active" id="nav-dados-cadastrais" role="tabpanel">
                                <fieldset>
                                    <legend class="text-uppercase">Dados do {{ form.tipo }}</legend>
                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group">
                                                <label>Tipo</label>
                                                <select class="form-control" v-model="form.tipo_pessoa" :disabled="editando">
                                                    <option value="pessoa_jurídica">Pessoa Jurídica</option>
                                                    <option value="pessoa_física">Pessoa Física</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group">
                                                <label for="cnpj">CNPJ</label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="cnpj"
                                                    v-model="form.cnpj"
                                                    @blur="validarCnpj"
                                                    v-if="form.tipo_pessoa === 'pessoa_jurídica'"
                                                />
                                            </div>

                                            <div class="form-group">
                                                <label for="cpf">CPF</label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="cpf"
                                                    v-model="form.cpf"
                                                    @blur="validarCpf"
                                                    v-if="form.tipo_pessoa === 'pessoa_física'"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group" v-if="form.tipo_pessoa === 'pessoa_jurídica'">
                                                <label for="nome">Nome</label>
                                                <input type="text" class="form-control" id="nome" v-model="form.nome" @blur="validarCampo('nome', 3)" />
                                            </div>

                                            <div class="form-group" v-if="form.tipo_pessoa === 'pessoa_física'">
                                                <label for="nome">Nome</label>
                                                <input type="text" class="form-control" id="nome" v-model="form.nome" @blur="validarCampo('nome', 3)" />
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6" v-if="form.tipo_pessoa === 'pessoa_jurídica'">
                                            <div class="form-group">
                                                <label>Nome Fantasia</label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    v-model="form.nome_fantasia"
                                                    placeholder="Nome Fantasia"
                                                    :disabled="preloadCnpj"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend class="text-uppercase">Endereço</legend>
                                    <endereco :model="form"></endereco>
                                </fieldset>

                                <fieldset>
                                    <legend class="text-uppercase">Contatos</legend>
                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group">
                                                <label for="telefone">Telefone</label>
                                                <input
                                                    type="text"
                                                    class="form-control telefone"
                                                    id="telefone"
                                                    v-model="form.telefones[0].numero"
                                                    @blur="validarTelefone"
                                                />
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group">
                                                <label for="email">E-mail</label>
                                                <input type="email" class="form-control" id="email" v-model="form.email" @blur="validarEmail" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <fieldset>
                                                <legend class="text-uppercase">Telefones</legend>
                                                <telefone :model="form.telefones" :ramal="false" :pais="false" :model-delete="form.telefonesDelete"></telefone>
                                            </fieldset>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend>ANEXOS</legend>
                                    <small>Anexo de </small>
                                    <br />
                                    <upload
                                        :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :url="urlAnexoUpload"
                                        label="Anexar ..."
                                        @onProgresso="anexoUploadAndamento = true"
                                        @onFinalizado="anexoUploadAndamento = false"
                                    ></upload>
                                </fieldset>

                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="ativo" />
                                    <label class="custom-control-label" for="ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                                </div>
                            </div>

                            <!-- Serviços -->
                            <div class="tab-pane fade" id="nav-servicos" role="tabpanel">
                                <fieldset>
                                    <legend class="text-uppercase">
                                        <span>Serviços Contratados</span>
                                    </legend>
                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                            <button class="btn btn-sm mr-1 btn-secondary mb-2" @click="addLIServicoFornecedor">
                                                <span class="fas fa-plus" aria-hidden="true"></span>
                                                Adicionar Serviço
                                            </button>
                                        </div>

                                        <div class="col-12" v-show="form.servicos.length > 0" v-for="(obj, index) in form.servicos" :key="obj.id">
                                            <div class="row py-3">
                                                <!-- Campos do serviço -->
                                                <div class="col-12 col-sm-4" v-if="form.tipo === 'fornecedor'">
                                                    <div class="form-group">
                                                        <label>Vencimento</label>
                                                        <select v-model="obj.vencimento" class="form-control">
                                                            <option value="">Selecione ...</option>
                                                            <option value="mensal">Mensal</option>
                                                            <option value="trimestral">Trimestral</option>
                                                            <option value="semestral">Semestral</option>
                                                            <option value="anual">Anual</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-4" v-if="form.tipo !== 'fornecedor'">
                                                    <div class="form-group">
                                                        <label>Data Início</label>
                                                        <datepicker posicao="up" v-model="obj.data_inicio"></datepicker>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-4" v-if="form.tipo !== 'fornecedor'">
                                                    <div class="form-group">
                                                        <label>Data Encerramento</label>
                                                        <datepicker posicao="up" v-model="obj.data_encerramento"></datepicker>
                                                    </div>
                                                </div>

                                                <!-- Outros campos do serviço -->
                                                <div class="col-12 col-sm-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label>Tipo de Serviço</label>
                                                        <select v-model="obj.tipo_servico_fornecedor_id" class="form-control">
                                                            <option value="">Selecione ...</option>
                                                            <option v-for="(item, index) in listaServicos" :value="item.id" :key="item.id || index">
                                                                {{ item.label }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label>Valor R$</label>
                                                        <select v-model="obj.valor" class="form-control">
                                                            <option value="">Selecione ...</option>
                                                            <option value="de_zero_a_quinhentos">De 0 a 500</option>
                                                            <option value="de_quinhentos_a_mil">De 500 a 1000</option>
                                                            <option value="acima_de_mil">Acima de 1000</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label>Tipo do Faturamento</label>
                                                        <select v-model="obj.tipo_faturamento" class="form-control">
                                                            <option value="Único">ÚNICO</option>
                                                            <option value="Por execução">POR EXECUÇÃO</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Escopo</label>
                                                        <textarea class="form-control" v-model="obj.escopo" rows="3"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label>Feedback</label>
                                                        <select v-model="obj.feedback" class="form-control">
                                                            <option value="">Selecione ...</option>
                                                            <option value="qualificado">Qualificado</option>
                                                            <option value="nao_qualificado">Não Qualificado</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select v-model="obj.status" class="form-control">
                                                            <option value="iniciado">Iniciado</option>
                                                            <option value="concluido">Concluído</option>
                                                            <option value="nao_iniciado">Não Iniciado</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label>Ativo</label>
                                                        <select v-model="obj.ativo" class="form-control">
                                                            <option :value="true">SIM</option>
                                                            <option :value="false">NÃO</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <fieldset>
                                                        <legend>ANEXOS</legend>
                                                        <small>Anexo de Notas Fiscais, Contratos...</small>
                                                        <br />
                                                        <upload
                                                            :model="obj.anexos"
                                                            :model-delete="obj.anexosDel"
                                                            :url="urlAnexoServicoUpload"
                                                            label="Anexar ..."
                                                            @onProgresso="anexoServicoUploadAndamento = true"
                                                            @onFinalizado="anexoServicoUploadAndamento = false"
                                                        ></upload>
                                                    </fieldset>
                                                </div>

                                                <div class="col-12 mb-3">
                                                    <button class="btn btn-sm mr-1 btn-danger" @click="removerLIServicoFornecedor(index)" v-show="obj.nova">
                                                        <i class="fa fa-trash"></i> Remover
                                                    </button>
                                                </div>

                                                <hr class="w-100" />
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </form>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado && !preloadAjax" @click="alterar">Alterar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !cadastrado && !preloadAjax" @click="cadastrar">Cadastrar</button>
            </template>
        </modal>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label for="">Buscar</label>
                        <input
                            type="text"
                            placeholder="Buscar por nome"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select class="form-control form-control-sm" :disabled="controle.carregando" v-model="controle.dados.campoTipo" @change="atualizar">
                            <option value="">Todos os Tipos</option>
                            <option value="fornecedor">Fornecedor</option>
                            <option value="parceiro">Parceiro</option>
                            <option value="terceiro">Terceiro</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" :disabled="controle.carregando" v-model="controle.dados.campoStatus" @change="atualizar">
                            <option value="">Todos os Status</option>
                            <option :value="true">Apenas Ativos</option>
                            <option :value="false">Apenas Inativos</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button type="button" class="btn btn-sm mr-1 btn-primary" :disabled="controle.carregando" @click="formNovo">Cadastrar</button>
                </div>
            </form>
        </fieldset>

        <!-- Lista -->
        <p class="text-center" v-if="controle.carregando">
            <preload></preload>
        </p>
        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length == 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>
            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Contato</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(fornecedor, index) in lista" :key="fornecedor.id || index">
                            <td data-label="ID">{{ fornecedor.id }}</td>
                            <td data-label="Nome">
                                {{ fornecedor.tipo_pessoa == 'pessoa_física' ? fornecedor.nome : fornecedor.razao_social }}
                            </td>
                            <td data-label="Tipo">{{ fornecedor.tipo }}</td>
                            <td data-label="Contato">
                                {{ fornecedor.contato }} -
                                <span v-for="(tel, index) in fornecedor.telefones" :key="tel.id || index">{{ tel.numero }}</span>
                            </td>
                            <td data-label="Status">
                                <bt-ativo :rota="`administracao/fornecedor/${fornecedor.id}/ativa-desativa`" :model="fornecedor"></bt-ativo>
                            </td>
                            <td data-label="Ações">
                                <a href="javascript://" class="btn btn-sm mr-1 btn-primary" title="Editar" @click.prevent="formAlterar(fornecedor.id)">
                                    <i class="fa fa-edit" aria-hidden="true"></i>
                                </a>

                                <a href="javascript://" class="btn btn-sm mr-1 btn-danger" title="Excluir" @click.prevent="janelaConfirmar(fornecedor.id)">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlAtualizar"
                por-pagina="50"
                :dados="controle.dados"
                @carregou="carregou"
                @carregando="carregando"
            ></controle-paginacao>
        </div>
    </div>
</template>

<script>
import telefone from '../../Telefones'
import endereco from '../../Endereco'
import datepicker from '../../DatePicker'
import upload from '../../Upload'
import moment from 'moment'
import validacoes from '../../../mixins/Validacoes'

// Constantes
const API_ENDPOINTS = {
    FORNECEDOR: `${URL_ADMIN}/administracao/fornecedor`,
    FORNECEDOR_EDITAR: (id) => `${URL_ADMIN}/administracao/fornecedor/${id}/editar`,
    FORNECEDOR_BUSCAR_CPF: `${URL_ADMIN}/administracao/fornecedor/buscar-cpf`,
    CNPJ_BUSCA: `${URL_PUBLICO}/cnpjbusca`
}

const TELEFONE_PADRAO = {
    tipo: 'whatsapp',
    pais: 55,
    numero: '',
    ramal: '',
    detalhe: ''
}

export default {
    name: 'FornecedoresComponent',
    mixins: [validacoes],
    components: {
        telefone,
        endereco,
        datepicker,
        upload
    },
    data() {
        return {
            tituloJanela: 'Cadastrando Fornecedor',
            preloadAjax: false,
            editando: false,
            apagado: false,
            preloadCnpj: false,
            cadastrado: false,
            atualizado: false,
            leitura: false,
            form: {
                tipo: '',
                cnpj: '',
                cpf: '',
                nome: '',
                tipo_pessoa: 'pessoa_jurídica',
                razao_social: '',
                nome_fantasia: '',
                cep: '',
                logradouro: '',
                end_numero: '',
                complemento: '',
                bairro: '',
                municipio: '',
                uf: '',
                contato: '',
                email: '',
                ativo: true,
                servicos: [],
                servicosDelete: [],
                anexos: [],
                anexosDel: [],
                telefones: [{ ...TELEFONE_PADRAO }],
                telefonesDelete: []
            },
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoTipo: '',
                    campoStatus: ''
                }
            },
            lista: [],
            listaServicos: [],
            listaAreas: [],
            urlAnexoUpload: `${URL_ADMIN}/administracao/fornecedor/uploadAnexos`,
            urlAnexoServicoUpload: `${URL_ADMIN}/administracao/fornecedor/uploadAnexos`,
            urlAtualizar: `${URL_ADMIN}/administracao/fornecedor/atualizar`,
            formDefault: null,
            erros: {
                cnpj: '',
                cpf: '',
                nome: '',
                email: '',
                telefone: '',
                cep: '',
                data_inicio: ''
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form)
        this.atualizar()
    },
    methods: {
        abrirModalCadastrar() {
            if (this.$refs && this.$refs.modalCadastrar && typeof this.$refs.modalCadastrar.abrirModal === 'function') {
                this.$refs.modalCadastrar.abrirModal()
            }
        },
        fecharModalCadastrar() {
            if (this.$refs && this.$refs.modalCadastrar && typeof this.$refs.modalCadastrar.fecharModal === 'function') {
                this.$refs.modalCadastrar.fecharModal()
            }
        },
        abrirModalConfirmar() {
            if (this.$refs && this.$refs.modalConfirmar && typeof this.$refs.modalConfirmar.abrirModal === 'function') {
                this.$refs.modalConfirmar.abrirModal()
            }
        },
        fecharModalConfirmar() {
            if (this.$refs && this.$refs.modalConfirmar && typeof this.$refs.modalConfirmar.fecharModal === 'function') {
                this.$refs.modalConfirmar.fecharModal()
            }
        },
        validarCnpj() {
            const cnpj = this.form.cnpj.replace(/[^\d]/g, '')
            if (cnpj.length !== 14) {
                this.erros.cnpj = 'CNPJ inválido'
                return false
            }
            this.erros.cnpj = ''
            return true
        },

        validarCpf() {
            const cpf = this.form.cpf.replace(/[^\d]/g, '')
            if (cpf.length !== 11) {
                this.erros.cpf = 'CPF inválido'
                return false
            }
            this.erros.cpf = ''
            return true
        },

        validarCampo(campo, minLength) {
            const valor = this.form[campo]
            if (!valor || valor.length < minLength) {
                this.erros[campo] = `Campo deve ter no mínimo ${minLength} caracteres`
                return false
            }
            this.erros[campo] = ''
            return true
        },

        validarEmail() {
            const email = this.form.email
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            if (!email || !regex.test(email)) {
                this.erros.email = 'Email inválido'
                return false
            }
            this.erros.email = ''
            return true
        },

        validarTelefone() {
            const telefone = this.form.telefones[0].numero.replace(/[^\d]/g, '')
            if (telefone.length < 10) {
                this.erros.telefone = 'Telefone inválido'
                return false
            }
            this.erros.telefone = ''
            return true
        },

        validarCep() {
            const cep = this.form.cep.replace(/[^\d]/g, '')
            if (cep.length !== 8) {
                this.erros.cep = 'CEP inválido'
                return false
            }
            this.erros.cep = ''
            return true
        },

        validarData() {
            const data = this.form.data_inicio
            if (!data || !moment(data, 'DD/MM/YYYY').isValid()) {
                this.erros.data_inicio = 'Data inválida'
                return false
            }
            this.erros.data_inicio = ''
            return true
        },

        validarFormulario() {
            let valido = true

            if (this.form.tipo_pessoa === 'pessoa_jurídica') {
                valido = this.validarCnpj() && valido
            } else {
                valido = this.validarCpf() && valido
            }

            valido = this.validarCampo('nome', 3) && valido
            valido = this.validarEmail() && valido
            valido = this.validarTelefone() && valido
            valido = this.validarCep() && valido
            valido = this.validarData() && valido

            if (!valido) {
                this.mostraErro('', 'Verificar os erros')
                return false
            }

            if (this.form.telefones.length === 0) {
                this.mostraErro('', 'Por favor insira um Telefone')
                return false
            }

            return true
        },

        async cadastrar() {
            try {
                if (!this.validarFormulario()) return

                this.preloadAjax = true
                const response = await axios.post(API_ENDPOINTS.FORNECEDOR, this.form)

                if (response.status === 201) {
                    this.cadastrado = true
                    this.atualizar()
                    this.mostraSucesso('Fornecedor cadastrado com sucesso!', 'Sucesso')
                }
            } catch (error) {
                this.tratarErro(error, 'Erro ao cadastrar fornecedor')
            } finally {
                this.preloadAjax = false
            }
        },

        async alterar() {
            try {
                if (!this.validarFormulario()) return

                this.form._method = 'PUT'
                this.preloadAjax = true

                await axios.put(`${API_ENDPOINTS.FORNECEDOR}/${this.form.id}`, this.form)

                this.atualizado = true
                this.atualizar()
                this.mostraSucesso('Fornecedor atualizado com sucesso!', 'Sucesso')
            } catch (error) {
                this.tratarErro(error, 'Erro ao alterar fornecedor')
            } finally {
                this.preloadAjax = false
            }
        },

        async apagar() {
            try {
                this.form._method = 'DELETE'
                this.preloadAjax = true

                await axios.delete(`${API_ENDPOINTS.FORNECEDOR}/${this.form.id}`, this.form)

                this.apagado = true
                this.atualizar()
                this.mostraSucesso('Fornecedor excluído com sucesso!', 'Sucesso')
                this.fecharModalConfirmar()
            } catch (error) {
                this.tratarErro(error, 'Erro ao apagar fornecedor')
            } finally {
                this.preloadAjax = false
            }
        },

        formNovo() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Cadastrando Fornecedor'
            formReset()
            setupCampo()
            this.form = _.cloneDeep(this.formDefault)
            this.leitura = false
            this.abrirModalCadastrar()
        },

        async formAlterar(id) {
            try {
                this.formNovo()
                this.preloadAjax = true

                const response = await axios.get(API_ENDPOINTS.FORNECEDOR_EDITAR(id))
                Object.assign(this.form, response.data)

                this.editando = true
                setupCampo()
            } catch (error) {
                this.tratarErro(error, 'Erro ao carregar dados do fornecedor')
            } finally {
                this.preloadAjax = false
            }
        },

        async verificaCnpj() {
            if (this.editando || this.form.cnpj.length !== 18) return

            try {
                const numsStr = this.form.cnpj.replace(/[^0-9]/g, '')
                const cnpj = parseInt(numsStr)

                this.preloadCnpj = true
                const response = await axios.post(API_ENDPOINTS.CNPJ_BUSCA, { cnpj })

                if (response.data.status === 'OK') {
                    this.preencherDadosCnpj(response.data)
                } else {
                    this.limparDadosCnpj()
                }
            } catch (error) {
                this.tratarErro(error, 'Erro ao consultar CNPJ')
            } finally {
                this.preloadCnpj = false
            }
        },

        preencherDadosCnpj(data) {
            this.form.razao_social = data.nome
            this.form.nome_fantasia = data.fantasia
            this.form.cep = replaceAll(data.cep, '.', '')
            this.form.logradouro = data.logradouro
            this.form.end_numero = data.numero
            this.form.complemento = data.complemento
            this.form.bairro = data.bairro
            this.form.municipio = data.municipio
            this.form.uf = data.uf
        },

        limparDadosCnpj() {
            const campos = ['razao_social', 'nome_fantasia', 'cep', 'logradouro', 'end_numero', 'complemento', 'bairro', 'municipio', 'uf']

            campos.forEach((campo) => (this.form[campo] = ''))
        },

        tratarErro(error, mensagemPadrao) {
            console.error(error)
            this.mostraErro(error.response?.data || { message: mensagemPadrao })
        },

        addLIServicoFornecedor() {
            const obj = {
                nova: true,
                tipo_servico_fornecedor_id: '',
                vencimento: '',
                data_inicio: moment().format('L'),
                data_encerramento: moment().add(12, 'months').format('L'),
                escopo: '',
                valor: '',
                tipo_faturamento: 'Único',
                status: 'Iniciado',
                feedback: '',
                ativo: true,
                anexos: [],
                anexosDel: []
            }
            this.form.servicos.unshift(obj)
        },

        removerLIServicoFornecedor(index) {
            if (this.editando) {
                this.form.servicosDelete.push(this.form.servicos[index].id)
            }
            this.form.servicos.splice(index, 1)
        },

        carregou(dados) {
            this.lista = dados.itens
            this.listaServicos = dados.servicos
            this.listaAreas = dados.areas
            this.controle.carregando = false
        },

        carregando() {
            this.controle.carregando = true
        },

        atualizar() {
            if (this.$refs.componente) {
                this.$refs.componente.atual = 1
                if (this.$refs.componente.buscar) {
                    this.$refs.componente.buscar()
                }
            }
        },

        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false
            this.preloadAjax = false
            this.abrirModalConfirmar()
        }
    }
}
</script>

<style scoped>
.tabela {
    width: 100%;
    border-collapse: collapse;
}

.tabela th,
.tabela td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.tabela th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.tabela tr:hover {
    background-color: #f5f5f5;
}

@media screen and (max-width: 600px) {
    .tabela td {
        display: block;
        text-align: right;
    }

    .tabela td::before {
        content: attr(data-label);
        float: left;
        font-weight: bold;
    }

    .tabela th {
        display: none;
    }
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 80%;
    margin-top: 0.25rem;
}
</style>
