<template>
    <div>
        <div v-if="preload && !movido">
            <i class="fas fa-circle-notch fa-spin mr-1"></i> Carregando...
        </div>
        <div class="alert alert-success alert-dismissible" v-show="movido">
            <h5>
                <i class="icon fa fa-check"></i>
                Item movido com sucesso!
            </h5>
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
                        <button class="btn btn-sm btn-outline-dark border-0" @click="abriPasta(pertence_id)">
                            <i class="fa fa-long-arrow-alt-left"></i> Voltar
                        </button>
                    </td>
                </tr>
                <tr v-for="(item, index) in lista" v-if="!preload && lista.length > 0 && item.TemPermissao">
                    <td>
                        <button class="btn btn-sm btn-outline-default text-left border-0"
                                @click="abriPasta(item.id)"
                        >
                            <i class="fas fa-folder mr-1" style="color: #EECD6D"></i> {{item.label}}
                        </button>
                    </td>
                </tr>
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
                default: () => '',
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
            }
        },
        mounted() {
            this.inicial = this.model.item;
            this.atualizar();
        },
        methods: {
            moverArquivo() {
                this.preload = true;
                this.movido = false;

                axios.post(`${URL_ADMIN}/itenscloud/mover/${this.model.arquivo}`, {
                    'pasta': this.model.item,
                    'inicial': this.inicial
                })
                    .then(response => {
                        this.preload = false;
                        this.movido = true;
                        this.$emit("moveu", {});
                    }).catch(error => {
                    this.preload = false;
                });
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

            atualizar() {
                this.preload = true;
                this.$emit("carregando", {});
                axios.get(`${URL_ADMIN}/itenscloud/estrutura-mover/${this.model.cloud}/${this.model.item}`)
                    .then(response => {
                        let data = response.data;
                        this.lista = data.lista;
                        this.pertence_id = data.anterior;
                        this.nomePasta = data.nomePasta;
                        this.preload = false;

                        this.$emit("carregou", {});
                        this.$emit("pastaAtual", {
                            atual: this.model.item,
                            inicial: this.inicial,
                            arquivo: this.model.arquivo
                        });

                    }).catch(error => {
                    this.preload = false;
                    this.$emit("carregou", {'msg': error.data});
                });
            }

        }
    }
</script>
