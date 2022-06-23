<template>
    <div>
        <div v-if="!preload">
           <p class="py-3 mt-3">
               <i class="fas fa-circle text-danger ml-2"></i>
               Colaborador com menos de 30 dias para o vencimento.
           </p>
           <table class="mt-4 table table-bordered tabela">
                <thead>
                <tr>
                    <th style="text-align: center; width: 2% ;">#</th>
                    <th style="text-align: center">Nome</th>
                    <th style="text-align: center">Cargo</th>
                    <th style="text-align: center">Data da Admissão</th>
                    <th style="text-align: center">Data de Vencimento</th>
                    <th style="text-align: center">Dias a Vencer</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(vencimento, index) in dados" :key="index" :class="vencimento.dias_vencer <= 30 ? 'table-danger': ''">
                    <td style="text-align: center">{{ index+1 }}</td>
                    <td style="text-align: center">{{ vencimento.colaborador }}</td>
                    <td style="text-align: center">{{ vencimento.cargo }}</td>
                    <td style="text-align: center">{{ vencimento.data_admissao }}</td>
                    <td style="text-align: center">{{ vencimento.data_vencimento }}</td>
                    <td style="text-align: center">{{ vencimento.dias_vencer }}</td>
            
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>

    export default {
        data() {
            return {
                hash: String(Math.random()).substr(2),

                preload: false,
                dados: []
            }
        },
        mounted() {
            this.buscarDados();
        },
        methods: {
            buscarDados() {
                this.preload = true;
                axios.post(`${URL_ADMIN}/relatorios/vencimentoasos`).then(res => {
                    this.dados = res.data;
                    this.preload = false;
                })
            },
            gerarPdf() {
                // let link = `${URL_ADMIN}/relatorios/controleusuarios/pdf/${this.form}`;
                // open(link, '_blank');
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
</style>
