<template>
    <div>
        <p class=" mt-2" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>
        <modal :fechar="!preloadSalvar" id="janelaFormulario" size="g" modal-pai="janelaHistorico"
               titulo="Formulário Noventa Dias">
            <template slot="conteudo">
                <p class=" mt-2" v-if="preloadSalvar">
                    <i class="fa fa-spinner fa-pulse"></i> Salvando aguarde ...
                </p>
                <fieldset class="mb-2" v-show="!preloadSalvar">
                    <div class="form-group" v-for="obj in form.perguntas">
                        <label>{{ obj.id }}) {{ obj.pergunta }}</label>
                        <div>
                            <select class="form-control" v-model="obj.nota"
                                    onchange="valida_campo_vazio(this,1)"
                                    onblur="valida_campo_vazio(this,1)">
                                <option value="">Selecione a nota</option>
                                <option v-for="item in 5">{{ item }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Gestor Imediato</label>
                        <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                               v-model="form.gestor_imediato">
                    </div>
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea type="text" class="form-control"
                                  v-model="form.observacao"></textarea>
                    </div>

                </fieldset>
            </template>
            <template slot="rodape">
                <button class="btn btn-primary" v-if="!preloadSalvar" @click="salvar">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <div v-if="!preload" :id="`form_${hash}`">

            <div class="table-responsive" v-if="avNoventaVencimento">
                <label><strong>Vencimentos da Avaliação 90 dias </strong></label>
                <table class="table table-bordered table-hover table-condensed">
                    <thead>
                    <tr class="bg-default">
                        <td class="text-center">Tipo</td>
                        <td class="text-center">Prazo</td>
                        <td class="text-center" v-if="avNoventaVencimento.tipo_admissao === 'FIXO'">1º Vencimento</td>
                        <td class="text-center" v-if="avNoventaVencimento.tipo_admissao === 'FIXO'">2º Vencimento</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="avNoventaVencimento.tipo_admissao === 'FIXO'">
                        <td class="text-center">{{ avNoventaVencimento.tipo_admissao }}</td>
                        <td class="text-center">
                            {{ avNoventaVencimento.prazo_experiencia }}
                        </td>
                        <td class="text-center">
                            {{ avNoventaVencimento.feedback.avaliacao_noventa_vencimento.prazo_dia_inicial }}
                        </td>
                        <td class="text-center">
                            {{ avNoventaVencimento.feedback.avaliacao_noventa_vencimento.prazo_dia_final }}
                        </td>
                    </tr>
                    <tr v-if="avNoventaVencimento.tipo_admissao === 'TEMPORARIO' || avNoventaVencimento.tipo_admissao === 'DETERMINADO'">
                        <td class="text-center">{{ avNoventaVencimento.tipo_admissao }}</td>
                        <td class="text-center">
                            {{ avNoventaVencimento.data_encerramento }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <button class="btn btn-primary mb-3"
                    data-toggle="modal"
                    data-target="#janelaFormulario"
                    @click="addFNDias">
                <i class="fa fa-plus"></i> Adicionar Avaliação
            </button>

            <div class="table-responsive" v-if="tabelaNoventa.length > 0">
                <table class="table table-bordered table-hover table-condensed">
                    <thead>
                    <tr class="bg-default">
                        <td class="text-center">Avaliação</td>
                        <td class="text-center">Avaliado em</td>
                        <td class="text-center">PDF</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in tabelaNoventa">
                        <td class="text-center">{{ item.quantidade_avaliacao }}ª</td>
                        <td class="text-center">{{ item.created_at }}</td>
                        <td class="text-center">
                            <button class="btn btn-outline-default" @click="gerarPdf(item)"><i
                                class="fas fa-file-pdf"></i> GERAR PDF
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import controlePaginacao from '../../ControlePaginacao';

export default {
    props: {
        feedback_id: {
            type: Number,
            required: true
        },
        model: {
            type: Array,
        },
        hash: {
            type: String,
            default: `mastertag_${parseInt((Math.random() * 999999))}`
        }
    },
    components: {
        controlePaginacao
    },
    data() {
        return {
            preload: false,
            preloadSalvar: false,
            URL_ADMIN,

            perguntas: [],

            tabelaNoventa: [],

            avNoventaVencimento: [],

            form: {
                gestor_imediato: '',
                feedback_id: '',
                observacao: '',
                perguntas: []
            },

        }
    },
    mounted() {
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form);
    },
    methods: {
        addFNDias() {
            this.form = _.cloneDeep(this.formDefault);
            this.form.perguntas = _.cloneDeep(this.perguntas);
            this.form.gestor_imediato = '';
            this.form.observacao = '';
            this.form.feedback_id = this.feedback_id;
            this.preloadSalvar = false;
            formReset();
            setupCampo();
        },
        salvar: function () {
            formReset();
            $(`#janelaFormulario :input:visible`).trigger('blur');
            if ($(`#janelaFormulario :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros.')
                return false;
            }

            this.preloadSalvar = true;
            //criar
            axios.post(`${URL_ADMIN}/historico/formulario-noventa-dias/${this.feedback_id}`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preloadSalvar = false;
                        mostraSucesso('Formulário de noventa dias foi criado com sucesso.');
                        $('#janelaFormulario').modal('hide');
                        // this.cadastrado = true;
                        this.atualizar();
                    }
                })
                .catch(error => (this.preloadSalvar = false));

        },
        gerarPdf(item) {
            let link = `${URL_ADMIN}/historico/formulario-noventa-dias/${item.quantidade_avaliacao}/${item.feedback_id}/pdf`;
            open(link, 'blank');
        },
        atualizar() {
            this.preload = true;
            this.perguntas = [];
            axios.get(`${URL_ADMIN}/historico/${this.feedback_id}`).then(res => {
                let data = res.data;
                this.perguntas = data.perguntas;
                this.tabelaNoventa = data.tabelaNoventa;
                this.avNoventaVencimento = data.avNoventaVencimento;
                this.form.perguntas = _.cloneDeep(this.perguntas);
                this.form.gestor_imediato = '';
                this.form.observacao = '';
                this.form.feedback_id = this.feedback_id;
                setTimeout(() => {
                    this.preload = false;
                }, 1500);

            })
        }
    }
}
</script>

<style scoped>

</style>
