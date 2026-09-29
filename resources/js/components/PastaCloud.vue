<template>
    <div>
        <div v-if="preload && !movido">
            <i class="fas fa-circle-notch fa-spin mr-1"></i> Carregando...
        </div>
        <div class="alert alert-success alert-dismissible" v-show="movido">
            <h5>
                <i class="icon fa fa-check"></i>
                {{ idsParaMover.length > 1 ? 'Itens movidos com sucesso!' : 'Item movido com sucesso!' }}
            </h5>
            <p v-if="errosMove.length" class="mb-0 mt-2 small">
                Alguns itens não puderam ser movidos:
                <span v-for="(erro, i) in errosMove" :key="i">{{ erro.msg }}{{ i < errosMove.length - 1 ? '; ' : '' }}</span>
            </p>
        </div>
        <div class="table-responsive" style="max-height: 410px" v-if="!preload && !movido">
            <table class="table table-hover">
                <thead>
                <tr class="table-info">
                    <td>{{ nomePasta ? nomePasta : 'Ínicio' }}</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td v-if="!preload && model.item">
                        <button class="btn btn-sm mr-1 btn-outline-dark border-0" @click="abriPasta(pertence_id)">
                            <i class="fa fa-long-arrow-alt-left"></i> Voltar
                        </button>
                    </td>
                </tr>
                <template v-for="(item, index) in lista" :key="item.id || index">
                    <tr v-if="item.TemPermissao">
                        <td>
                            <button class="btn btn-sm mr-1 btn-outline-default text-left border-0"
                                    @click="abriPasta(item.id)"
                            >
                                <i class="fas fa-folder mr-1" style="color: #EECD6D"></i> {{item.label}}
                            </button>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>

</template>
<script>
    export default {
        props: {
            model: {
                type: Object,
                required: false,
                default: () => ({}),
            },
        },
        data() {
            return {
                preload: true,
                lista: [],
                pertence_id: null,

                inicial: null,
                nomePasta: null,
                caminho: [],
                movido: false,
                errosMove: [],
            }
        },
        computed: {
            idsParaMover() {
                if (Array.isArray(this.model.arquivos) && this.model.arquivos.length) {
                    return this.model.arquivos.map((id) => Number(id)).filter((id) => id > 0)
                }
                if (this.model.arquivo != null && this.model.arquivo !== '') {
                    return [Number(this.model.arquivo)]
                }
                return []
            },
        },
        mounted() {
            this.inicial = this.model.item;
            this.atualizar();
        },
        methods: {
            async moverArquivo() {
                this.preload = true;
                this.movido = false;
                this.errosMove = [];
                try {
                    const pasta = this.model.item === '' || this.model.item == null
                        ? null
                        : this.model.item;
                    const { data } = await axios.post(`${URL_ADMIN}/itenscloud/mover-varios`, {
                        cloud_id: this.model.cloud,
                        pasta,
                        itens: this.idsParaMover,
                    });
                    this.errosMove = (data && data.erros) ? data.erros : [];
                    this.movido = true;
                    this.$emit("moveu", { movidos: (data && data.movidos) || [], erros: this.errosMove });
                } catch (error) {
                    const msg = error.response && error.response.data && error.response.data.msg
                        ? error.response.data.msg
                        : 'Não foi possível mover os itens';
                    this.errosMove = (error.response && error.response.data && error.response.data.erros)
                        ? error.response.data.erros
                        : [{ msg }];
                    if (error.response && error.response.data && error.response.data.movidos
                        && error.response.data.movidos.length) {
                        this.movido = true;
                        this.$emit("moveu", {
                            movidos: error.response.data.movidos,
                            erros: this.errosMove,
                        });
                    }
                } finally {
                    this.preload = false;
                }
            },

            adicionaCaminho(item) {
                this.caminho.push(item);
            },

            abriPasta(id) {
                this.preload = true;
                this.model.item = id;
                setTimeout(() => {
                    this.atualizar();
                }, 50);
            },

            async atualizar() {
                this.preload = true;
                this.$emit("carregando", {});
                try {
                    const response = await axios.get(`${URL_ADMIN}/itenscloud/estrutura-mover/${this.model.cloud}/${this.model.item}`);
                    const data = response.data;
                    this.lista = data.lista;
                    this.pertence_id = data.anterior;
                    this.nomePasta = data.nomePasta;
                    this.$emit("carregou", {});
                    this.$emit("pastaAtual", {
                        atual: this.model.item,
                        inicial: this.inicial,
                        arquivo: this.model.arquivo,
                        arquivos: this.idsParaMover,
                    });
                } catch (error) {
                    this.$emit("carregou", { msg: error.response && error.response.data ? error.response.data : null });
                } finally {
                    this.preload = false;
                }
            }

        }
    }
</script>
