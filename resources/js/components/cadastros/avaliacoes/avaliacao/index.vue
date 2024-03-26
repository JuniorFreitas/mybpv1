<template>
    <div :id="hash">
        <modal id="janelaAssociar" :titulo="janelaAssociar" :fechar="!preload" :size="90">
            <template slot="conteudo">
                <vincula-avaliador :obj="avaliacaoSelecionada" v-if="abrirAssociar"></vincula-avaliador>
            </template>
        </modal>

        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" size="g">
            <template slot="conteudo">
                <preload v-show="preload"></preload>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Título</label>
                                    <input v-model="form.titulo" class="form-control form-control-sm validacampo"
                                           type="text"
                                           placeholder="Informe o titulo da avaliação"
                                           @keyup.prevent="valida_campo_vazio($event.target, 1);"
                                           @blur.prevent="valida_campo_vazio($event.target, 1)"
                                    >
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Tipo de avaliação</label>
                                    <select class="form-control form-control-sm validacampo"
                                            v-model="form.avaliacao_tipo_id"
                                            @change.prevent="valida_campo_vazio($event.target, 1);"
                                            @blur.prevent="valida_campo_vazio($event.target, 1)"
                                    >
                                        <option value="">Selecione ...</option>
                                        <option v-for="item in lista_avaliacoes_tipos" :value="item.id" :key="item.id">
                                            {{ item.nome }}
                                        </option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <date-picker formsm label="Data Iníco" v-model="form.data_inicio_prazo"></date-picker>
                            </div>
                            <div class="col-lg-4">
                                <date-picker formsm label="Data Fim" v-model="form.data_fim_prazo"></date-picker>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control form-control-sm validacampo" v-model="form.status"
                                            @change.prevent="valida_campo_vazio($event.target, 1);"
                                            @blur.prevent="valida_campo_vazio($event.target, 1)">
                                        <option value="">Selecione ...</option>
                                        <option v-for="item in lista_status" :value="item">{{ item }}</option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Auto Avaliação</label>
                                    <select class="form-control form-control-sm" onblur="valida_campo_vazio(this,1)"
                                            onchange="valida_campo_vazio(this,1)" v-model="form.auto_avaliacao">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Ativo</label>
                                    <select class="form-control form-control-sm" onblur="valida_campo_vazio(this,1)"
                                            onchange="valida_campo_vazio(this,1)" v-model="form.ativo">
                                        <option value="">Selecione</option>
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Ano Avaliação</label>
                                    <input v-model="form.ano_avaliacao" class="form-control form-control-sm validacampo"
                                           type="number"
                                           placeholder="Ano Avaliação"
                                           v-mascara:ano
                                           @keyup.prevent="valida_campo_vazio($event.target, 4);"
                                           @blur.prevent="valida_campo_vazio($event.target, 4)"
                                    >
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Fluxo</legend>
                        <div class="alert alert-info">
                            Se Auto Avaliação estiver marcado como sim ocupara a primeira ordem do fluxo
                        </div>

                        <div class="form-group">
                            <label>Tipo de avaliador</label>
                            <select class="form-control form-control-sm validacampo"
                                    v-model="selecionatipoavaliador"
                                    @change="addTipoAvaliadorFluxo($event.target.value)"
                            >
                                <option value="">Selecione ...</option>
                                <option v-for="item in lista_tipos_avaliadores" :value="`${item.id}|${item.label}`"
                                        :key="item.id">
                                    {{ item.label }}
                                </option>

                            </select>
                        </div>

                        <div class="my-custom-list" v-if="form.auto_avaliacao">
                            <div class="sortable-item" style="cursor:default;">
                                <div class="handle">
                                    1 - Auto Avaliação
                                </div>
                            </div>
                        </div>

                        <draggable v-model="form.fluxo" v-bind="getOptions" class="my-custom-list"
                                   @change="atualizarPrincipal">
                            <div v-for="(item, key) in form.fluxo" :key="item.id"
                                 :class="{ 'sortable-item': true, 'dragging': itemIsDragging === item.id }">
                                <div class="handle">
                                    <!--                                    <i class="fas fa-grip-vertical"></i> -->
                                    {{ form.auto_avaliacao ? key + 2 : key + 1 }} - {{ item.label }}
                                    <span class="badge badge-info" v-if="key === form.fluxo.length - 1">
                                        <i class="fa fa-user"></i> Avaliador Final
                                    </span>
                                </div>
                                <div class="delete-icon">
                                    <i class="fa fa-trash" @click.prevent="rmTipoAvaliadorFluxo(item)"></i>
                                </div>
                            </div>
                        </draggable>

                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="editando && !preload"
                        @click="alterar()">
                    Alterar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-if="lista_avaliacoes_tipos.length > 0"
                        v-show="!editando && !preload"
                        @click="cadastrar()">
                    Cadastrar
                </button>
            </template>
        </modal>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input type="text"
                               placeholder="Buscar por título"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Ano Avaliação</label>
                        <select class="form-control form-control-sm"
                                @change.prevent="atualizar();controle.dados.tipo_avaliacao = '';controle.dados.status = '';"
                                v-model="controle.dados.ano_avaliacao"
                        >
                            <option v-for="(item,key) in listaKeysAvaliacaoPorAnoOrdenado" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Tipo Avaliação</label>
                        <select class="form-control form-control-sm"
                                @change.prevent="atualizar();controle.dados.status = '';"
                                v-model="controle.dados.tipo_avaliacao"
                        >
                            <option value="">Sem filtro</option>
                            <option v-for="(item,key) in groupAvaliacaoAno"
                                    :value="item.avaliacao_tipo_id">
                                {{ item.avaliacao_tipo }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm"
                                @change.prevent="atualizar()"
                                v-model="controle.dados.status"
                        >
                            <option value="">Sem filtro</option>
                            <option v-for="(item,key) in lista_status"
                                    :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button type="button" class="btn btn-sm btn-primary" :disabled="controle.carregando"
                            @click="formNovo"
                            data-toggle="modal"
                            data-target="#janelaCadastrar">
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">

            <p class=" mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div v-show="!controle.carregando && lista.length > 0" class="table-responsive">
                <table class="table table-bordered bg-white">
                    <thead>
                    <tr class="bg-default">
                        <td class="text-center">Ano</td>
                        <td class="text-center">Título</td>
                        <td class="text-center">Tipo Avaliação</td>
                        <td class="text-center">Data Início</td>
                        <td class="text-center">Data Fim</td>
                        <td class="text-center">Status</td>
                        <td class="text-center">Ativo</td>
                        <td class="text-center">Ação</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista">
                        <td class="text-center">{{ item.ano_avaliacao }}</td>
                        <td class="text-center">{{ item.titulo }}</td>
                        <td class="text-center">{{ item.avaliacao_tipo.nome }}</td>
                        <td class="text-center">{{ item.data_inicio_prazo }}</td>
                        <td class="text-center">{{ item.data_fim_prazo }}</td>
                        <td class="text-center">{{ item.status }}</td>
                        <td class="text-center">
                            <bt-ativo :rota="`cadastro/avaliacoes/avaliacao/${item.id}/ativa-desativa`"
                                      :model="item"></bt-ativo>
                        </td>
                        <td class="text-center">

                            <div class="dropdown show">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                   id="dropdownMenuLink"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                     aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="javascript://" title="Editar"
                                       data-toggle="modal" data-target="#janelaCadastrar" @click="alterarForm(item)">
                                        Editar
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Associar avaliadores"
                                       data-toggle="modal"
                                       data-target="#janelaAssociar"
                                       @click="associar(item)"
                                    >
                                        Associar avaliadores
                                    </a>
                                </div>

                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                                :url="urlPaginacao" :por-pagina="qntPag"
                                :dados="controle.dados"
                                v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
        </div>
    </div>
</template>

<script>
import controlePaginacao from "../../../ControlePaginacao";
import modal from "../../../Modal";
import DatePicker from "../../../DatePicker";
import vinculaAvaliador from "./vinculaAvaliador";
import validacoes from "../../../../mixins/Validacoes";
import vuedraggable from 'vuedraggable'


export default {
    components: {
        modal,
        controlePaginacao,
        DatePicker,
        vinculaAvaliador,
        vuedraggable
    },
    mixins: [validacoes],
    props: {
        qntPag: {
            type: Number,
            required: false,
            default: 20
        },
        filtro: {
            type: Boolean,
            required: false,
            default: true
        },
        modal: { // modal Pai
            type: String,
            required: false,
            default: ""
        }
    },
    mounted() {
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form);
        this.form.ano_avaliacao = new Date().getFullYear();
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: "",
            janelaAssociar: "",
            preload: false,
            editando: false,
            cadastrado: false,
            abrirAssociar: false,

            form: {
                titulo: "",
                ano_avaliacao: '',
                data_inicio_prazo: "",
                data_fim_prazo: "",
                status: "",
                ativo: true,
                auto_avaliacao: true,
                avaliacao_tipo_id: '',
                fluxo: []
            },

            formDefault: null,

            lista: [],
            lista_avaliacoes_tipos: [],
            lista_status: [],
            lista_avaliacoes_por_ano: [],

            lista_tipos_avaliadores: [],
            selecionatipoavaliador: '',

            avaliacaoSelecionada: null,

            itemIsDragging: null,

            urlPaginacao: `${URL_ADMIN}/cadastro/avaliacoes/avaliacao/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: "",
                    ano_avaliacao: new Date().getFullYear(),
                    tipo_avaliacao: "",
                    status: "",
                }
            }
        };
    },
    computed: {
        listaKeysAvaliacaoPorAnoOrdenado() {
            return Object.keys(this.lista_avaliacoes_por_ano).sort((a, b) => b - a);
        },
        groupAvaliacaoAno() {
            let group = _.groupBy(this.lista_avaliacoes_por_ano[this.controle.dados.ano_avaliacao], 'avaliacao_tipo_id');

            let array = [];
            for (let key in group) {
                array.push({
                    avaliacao_tipo_id: key,
                    avaliacao_tipo: group[key][0].avaliacao_tipo.nome,
                });
            }
            return array;
        },
        getOptions() {
            return {
                animation: 150, // Duração da animação de reordenação em milissegundos
                group: 'items',   // Define um grupo para permitir arrastar e soltar entre várias listas
                onStart: (event) => {
                    this.itemIsDragging = event.item.id; // Define o item atual como arrastando
                },
                onEnd: () => {
                    this.itemIsDragging = null; // Define o item arrastando como null quando o arrastar termina
                }
            };
        },
    },
    watch: {
        fluxo: {
            handler() {
                this.atualizarPrincipal();
            },
            deep: true // Isso garante que o watcher reaja a mudanças dentro dos objetos do array
        }
    },
    methods: {
        associar(obj) {
            this.abrirAssociar = false;
            this.janelaAssociar = `Associar avaliadores para avaliação - ${obj.titulo}`;
            this.avaliacaoSelecionada = obj;
            setTimeout(() => {
                this.abrirAssociar = true;
            }, 300);

        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault); //copia
            this.titulo_janela = "Montagem da Avaliação";
            this.editando = false;
            this.cadastrado = false;
            this.preload = false;
            this.form.ano_avaliacao = new Date().getFullYear();
            formReset();
            setupCampo();
        },

        cadastrar() {
            this.validaBlur();
            let countErro = document.querySelectorAll(".is-invalid").length
            if (countErro > 0) {
                toastr.error("Verifique os campos", "Atenção!")
                return false;
            }
            this.preload = true;
            axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliacao`, this.form)
                .then(res => {
                    if (res.status === 201) {
                        $("#janelaCadastrar").modal("hide");
                        mostraSucesso("", "Avaliação cadastrada com sucesso");
                        this.cadastrado = true;
                        this.preload = false;
                        this.atualizar();
                    }
                })
                .catch(error => {
                    this.cadastrado = false;
                    this.preload = false;
                });
        },

        alterarForm(avaliacao) {
            this.cadastrado = false;
            this.editando = true;
            this.titulo_janela = `Alterando Avaliação ${avaliacao.id}`;
            this.preload = true;

            this.form = _.cloneDeep(this.formDefault); //copia
            formReset();

            axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliacao/${avaliacao.id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    this.editando = true;
                    setupCampo();
                    this.preload = false;
                }).catch(
                error => (this.preloadAjax = false)
            );

        },

        alterar() {
            formReset();
            this.validaBlur();
            if (this.form.fluxo.length === 0) {
                mostraErro("", "Informe o fluxo da avaliação");
                return false;
            }
            let countErro = document.querySelectorAll(".is-invalid").length
            if (countErro > 0) {
                mostraErro("", "Verifique os campos")
                return false;
            }
            this.preload = true;

            axios.put(`${URL_ADMIN}/cadastro/avaliacoes/avaliacao/${this.form.id}`, this.form).then(response => {
                $("#janelaCadastrar").modal("hide");
                mostraSucesso("", "Avaliação alterada com sucesso");
                this.preload = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preload = false));

        },

        addTipoAvaliadorFluxo(obj) {
            const [id, label] = obj.split('|');

            if (id !== '') {
                const objFluxo = {
                    id: +id,
                    label,
                    principal: false
                };

                const existingIndex = this.form.fluxo.findIndex(item => item.id === objFluxo.id);

                if (existingIndex === -1) {
                    this.form.fluxo.push(objFluxo);
                } else {
                    mostraErro('', `Tipo avaliador ${label} já existe na lista`);
                }
            }
            this.atualizarPrincipal();
            this.selecionatipoavaliador = '';
        },

        rmTipoAvaliadorFluxo(item) {
            const index = this.form.fluxo.indexOf(item);
            if (index !== -1) {
                this.form.fluxo.splice(index, 1);
            }
            this.atualizarPrincipal();
        },

        atualizarPrincipal() {
            // Primeiro, definir todos os itens como 'principal: false'
            this.form.fluxo.forEach(item => {
                item.principal = false;
            });

            // Então, definir o último item como 'principal: true'
            if (this.form.fluxo.length > 0) {
                this.form.fluxo[this.form.fluxo.length - 1].principal = true;
            }
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.lista_avaliacoes_tipos = dados.avaliacoes_tipos;
            this.lista_tipos_avaliadores = dados.lista_tipos_avaliadores;

            this.lista_avaliacoes_por_ano = dados.lista_avaliacoes_por_ano;
            this.lista_status = dados.lista_status;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        }
    }

};
</script>

<style scoped>
.card-header {
    background-color: white;
}

.btn-link {
    font-weight: 400;
    color: white;
    text-decoration: none;
}

.btn-link:hover {
    color: #dddddd;
}

.my-custom-list {
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    padding: 10px;
}

.sortable-item {
    background-color: #ffffff;
    display: flex;
    justify-content: space-between; /* Alinha o ícone e o conteúdo à direita */
    align-items: center; /* Centraliza verticalmente */
    padding: 10px;
    border: 1px solid #ccc;
    margin-bottom: 5px;
    border-radius: 4px;
    cursor: grab;
}

.delete-icon {
    margin-left: 10px; /* Espaço entre o ícone e o conteúdo */
}

.item-content {
    flex: 1; /* O conteúdo ocupa o espaço restante na linha */
}

/* Adicione estilos ao ícone de arrastar conforme necessário */
.delete-icon i {
    font-size: 14px;
    color: #434344;
    cursor: pointer;
}

.delete-icon i:hover {
    color: #f85454;
}

.dragging {
    background-color: red; /* Cor de fundo quando o item está sendo arrastado */
}

</style>
