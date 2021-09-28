<template>
    <div id="pesquisaClima">
        <fieldset>
            <legend>Clientes</legend>
            <form onsubmit="return false" class="row">
                <div class="col-12">
                    <div class="form-group">
                        <div class="input-group">
                            <select class="form-control" v-model="form.cliente_id">
                                <option class="form-control" value="">Selecione</option>
                                <option class="form-control" v-for="item in lista" :value="item.cliente_id">
                                    {{item.cliente.nome_fantasia}}
                                </option>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary" @click="buscar(form.cliente_id)">
                        <i class="fa fa-save"></i> Buscar
                    </button>
                </div>
            </form>
        </fieldset>
        <div v-if="form.cliente_id && entrevistados != null">

            <div class="col-12">
                <label>
                    <strong>Total de Entrevistados: </strong>{{entrevistados}}
                </label><br>
            </div>

            <div class="row" v-for="(perguntas, index) in dadosPesquisa">
                <div class="col-12">
                    <br><label>
                    {{index + 1}} ) <strong>{{perguntas.pergunta}}</strong>
                </label><br>
                </div>
                <div class="table-responsive col-5">
                    <table class="tabela">
                        <thead>
                        <tr class="bg-default">
                            <td class="text-center">Respostas</td>
                            <td>Quantidade de Respostas</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="respostas in perguntas">
                            <td class="text-center">{{respostas.respostadigitada ? respostas.respostadigitada :
                                respostas.resposta }}
                            </td>
                            <td>{{respostas.respostadigitada ? '-' : respostas.contagem}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    // import chart from '../../Chart'

    export default {
        components: {},
        props: {},
        data() {
            return {

                form: {
                    cliente_id: '',
                },
                lista: [],
                formDefault: null,

                dadosPesquisa: null,

                entrevistados: null,

                urlPaginacao: `${URL_ADMIN}/administracao/pesquisaclima/atualizar`,

            }
        },
        mounted() {
            this.atualizar();
            this.formDefault = _.cloneDeep(this.form);
        },
        methods: {
            buscar(item) {
                let link = `${URL_ADMIN}/administracao/pesquisaclima/chart/${item}`;
                axios.get(link).then(res => {
                    this.dadosPesquisa = res.data.respostas;
                    this.entrevistados = res.data.entrevistados;
                    this.preload = false;
                })
            },
            atualizar() {
                this.preload = true;
                axios.get(`${URL_ADMIN}/administracao/pesquisaclima/atualizar`).then(res => {
                    let data = res.data;
                    this.lista = data.items;
                    this.preload = false;
                })
            }
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
</style>
