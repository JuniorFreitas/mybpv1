<template>
    <div>
        <p class=" mt-2" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>
        <div v-if="!preload" :id="`form_${hash}`">

            <button class="btn btn-sm btn-primary mb-3" @click="addLIMedida">
                <i class="fa fa-plus"></i> Adicionar Medida
            </button>

            <fieldset class=" mb-2" v-if="form.medidas_administrativas.length > 0"
                      v-for="(obj, index) in form.medidas_administrativas" :key="index">
                <legend>#{{ index + 1 }}</legend>
                <div class="row">

                    <div class="col-md-4">
                        <label>Tipo</label>
                        <select class="form-control" v-model="obj.tipo" :disabled="!obj.novo"
                                onchange="valida_campo_vazio(this,1)"
                                onblur="valida_campo_vazio(this,1)">
                            <option value="">Selecione ...</option>
                            <option v-for="item in tipos" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>

                    <!--                    <div class="col-md-4">-->
                    <!--                        <label>Definição</label>-->
                    <!--                        <select class="form-control" v-model="obj.definicao" :disabled="!obj.novo"-->
                    <!--                                onchange="valida_campo_vazio(this,1)"-->
                    <!--                                onblur="valida_campo_vazio(this,1)">-->
                    <!--                            <option value="">Selecione ...</option>-->
                    <!--                            <option v-for="item in definicao" :value="item">-->
                    <!--                                {{ item }}-->
                    <!--                            </option>-->
                    <!--                        </select>-->
                    <!--                    </div>-->

                    <div class="col-md-8">
                        <label>Causa</label>
                        <select class="form-control" v-model="obj.causa" :disabled="!obj.novo"
                                onchange="valida_campo_vazio(this,1)"
                                onblur="valida_campo_vazio(this,1)">
                            <option value="">Selecione ...</option>
                            <option v-for="item in causas" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Motivo</label>
                        <input type="text" class="form-control" v-model="obj.motivo" :disabled="!obj.novo"
                               onblur="valida_campo_vazio(this,1)">
                    </div>

                    <div class="col-md-4">
                        <label>Solicitante</label>
                        <input type="text" class="form-control" v-model="obj.solicitante" :disabled="!obj.novo"
                               onblur="valida_campo_vazio(this,1)">
                    </div>

                    <div class="col-md-2">
                        <date-picker label="Data Solicitação" v-model="obj.data_solicitacao" :max="restricao"
                                     :disabled="!obj.novo"></date-picker>
                    </div>

                    <div class="col-md-2">
                        <date-picker v-if="!naoExibiRetorno.includes(obj.tipo)"
                                     label="Data Retorno" v-model="obj.data_retorno" :min="hoje"
                                     :disabled="!obj.novo"></date-picker>
                    </div>

                    <div class="col-12">
                        <fieldset>
                            <legend>Anexo</legend>
                            <upload :model="obj.anexos"
                                    :model-delete="obj.anexosDel"
                                    :url="url_anexo"
                                    label="Selecionar"
                                    @onProgresso="anexoUploadAndamento=true"
                                    @onFinalizado="anexoUploadAndamento=false"></upload>
                        </fieldset>
                    </div>

                    <div class="col-12 mt-3" v-show="obj.novo">
                        <button class="btn btn-sm btn-danger" @click="removerLIMedida(index)"><i
                            class="fa fa-times"></i> Remover
                        </button>

                        <button class="btn btn-sm btn-primary mt" @click="addLIMedida" v-show="index >=1">
                            <i class="fa fa-plus"></i> Adicionar
                        </button>
                    </div>

                    <div class="col-12 mt-3"
                         v-show="!obj.novo && validTypes.includes(obj.tipo)">
                        <button class="btn btn-sm btn-outline-primary" @click="gerarPdf(obj)" v-show="!obj.novo"><i
                            class="fas fa-file-pdf"></i> GERAR PDF
                        </button>
                    </div>

                    <div class="col-12 mt-3" v-show="!obj.novo && privilegio_gestao_rh">
                        <button class="btn btn-sm btn-danger" @click="abrirModalRemover(obj)" 
                                data-toggle="modal" :data-target="`#janelaRemoverMedida_${hash}`">
                            <i class="fa fa-trash"></i> Remover Medida
                        </button>
                    </div>
                </div>
            </fieldset>

            <button class="btn btn-sm btn-primary mb-3" v-if="form.medidas_administrativas.length > 0" @click="salvar">
                <i class="fa fa-save"></i> Salvar
            </button>
        </div>

        <!-- Modal para Remover Medida Administrativa -->
        <modal :id="`janelaRemoverMedida_${hash}`" :titulo="tituloModalRemover" :fechar="!preloadRemover" :size="75">
            <template slot="conteudo">
                <div v-if="!preloadRemover && medidaSelecionada">
                    <fieldset>
                        <legend>Informações do Colaborador</legend>
                        <div style="text-transform: uppercase" v-if="feedbackInfo">
                            <p>Nome: <strong>{{ feedbackInfo.curriculo ? feedbackInfo.curriculo.nome : '' }}</strong><br>
                                CPF: <strong>{{ feedbackInfo.curriculo ? feedbackInfo.curriculo.cpf : '' }}</strong><br>
                                <span v-if="feedbackInfo.empresa">
                                    Empresa: <strong>{{ feedbackInfo.empresa.nome_fantasia || feedbackInfo.empresa.nome }}</strong><br>
                                </span>
                                <span v-if="feedbackInfo.vaga_aberta && feedbackInfo.vaga_aberta.vaga">
                                    Vaga: <strong>{{ feedbackInfo.vaga_aberta.vaga.nome }}</strong><br>
                                </span>
                            </p>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Informações da Medida Administrativa</legend>
                        <div>
                            <p>Tipo: <strong>{{ medidaSelecionada.tipo }}</strong><br>
                                Causa: <strong>{{ medidaSelecionada.causa }}</strong><br>
                                Motivo: <strong>{{ medidaSelecionada.motivo }}</strong><br>
                                Data Solicitação: <strong>{{ medidaSelecionada.data_solicitacao }}</strong><br>
                                <span v-if="medidaSelecionada.data_retorno">
                                    Data Retorno: <strong>{{ medidaSelecionada.data_retorno }}</strong><br>
                                </span>
                            </p>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Termo de Responsabilidade para Remover Medida Administrativa</legend>
                        <div v-html="textoTermoResponsabilidade"></div>
                        <div class="form-group">
                            <label for="">Motivo da remoção</label>
                            <textarea v-model="auditoriaForm.descricao" class="form-control" cols="5" rows="5" 
                                      placeholder="Informe o motivo da remoção da medida administrativa"></textarea>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-danger" 
                        v-if="!preloadRemover && auditoriaForm.descricao.length"
                        @click="removerMedida">
                    Remover Medida
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
import DatePicker from "../DatePicker";
import Upload from "../Upload";
import Modal from "../Modal";

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
        DatePicker,
        Upload,
        Modal
    },
    data() {
        return {
            preload: false,
            URL_ADMIN,

            url_anexo: `${URL_ADMIN}/historico/medidas-administrativas/uploadAnexos`,
            anexoUploadAndamento: false,

            hoje: '',
            restricao: '',

            validTypes: [
                'Advertência Escrita',
                'Advertência Verbal',
                'Suspensão de 1 dia',
                'Suspensão de 2 ou 3 dias',
                'Suspensão acima de 3 dias',
                'Re-orientação'
            ],
            naoExibiRetorno: [
                'Advertência Escrita',
                'Advertência Verbal',
                'Desligamento',
                'Re-orientação',
                ''
            ],

            form: {
                medidas_administrativas: [],
                medidas_administrativasDelete: [],
            },
            formDefault: null,

            causas: [],
            tipos: [],
            definicao: [],
            privilegio_gestao_rh: false,
            
            // Modal remover medida
            medidaSelecionada: null,
            feedbackInfo: null,
            tituloModalRemover: 'Remover Medida Administrativa',
            preloadRemover: false,
            auditoriaForm: {
                descricao: '',
                medida_id: null,
                feedback_id: null
            }
        }
    },
    computed: {
        textoTermoResponsabilidade() {
            const nomeColaborador = this.feedbackInfo && this.feedbackInfo.curriculo ? this.feedbackInfo.curriculo.nome : '';
            const nomeUsuario = (typeof AUTENTICADO !== 'undefined' && AUTENTICADO) ? AUTENTICADO.nome : '';
            const tipoMedida = this.medidaSelecionada ? this.medidaSelecionada.tipo : '';
            
            return `<p>
                        Ao clicar em "Remover Medida Administrativa" e remover a medida administrativa do tipo
                        <strong>${tipoMedida}</strong> do colaborador
                        <strong>${nomeColaborador}</strong>, eu,
                        <strong>${nomeUsuario}</strong>, reconheço e aceito que estou assumindo a
                        responsabilidade por esta ação.
                        <br>
                        Além disso, declaro que:
                        <br><br>
                        Estou ciente de que a remoção da medida administrativa implica em uma ação
                        irreversível no sistema.
                        <br><br>
                        Confirmo que revisei cuidadosamente todas as informações relevantes relacionadas à remoção da
                        medida administrativa.
                        <br><br>
                        Comprometo-me a fornecer um motivo válido e justificável para esta ação, conforme solicitado
                        pelo sistema.
                        <br><br>
                        Aceito total responsabilidade por quaisquer consequências decorrentes da remoção da medida administrativa.
                        <br><br>
                        Assumo que, ao clicar em "Remover Medida Administrativa" no sistema MyBP, estou ciente e concordo com as disposições deste termo de responsabilidade.
                    </p>`;
        }
    },
    mounted() {
        this.atualizar();
    },

    methods: {
        addLIMedida() {
            const obj = {};
            obj.novo = true;
            obj.feedback_id = this.feedback_id;
            obj.solicitante = '';
            obj.tipo = '';
            obj.causa = '';
            obj.definicao = '';
            obj.motivo = '';
            obj.data_solicitacao = this.hoje;
            obj.data_retorno = this.hoje;
            obj.anexos = [];
            obj.anexosDel = [];

            this.form.medidas_administrativas.unshift(obj);
        },
        removerLIMedida(index) {
            if (this.editando) {
                this.form.medidas_administrativasDelete.push(this.form.medidas_administrativas[index].id);
            }
            this.form.medidas_administrativas.splice(index, 1);
        },
        gerarPdf(obj) {
            let link = `${URL_ADMIN}/historico/medidas-administrativas/${obj.id}/${obj.feedback_id}/pdf`;
            open(link, 'blank');
        },
        salvar() {
            formReset();
            $(`#form_${this.hash} :input:visible`).trigger('blur');
            if ($(`#form_${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros')
                return false;
            }

            this.preload = true;

            if (this.form.medidas_administrativas[0].id) { //alterar
                axios.put(`${URL_ADMIN}/historico/${this.feedback_id}`, this.form)
                    .then(response => {
                        if (response.status === 201) {
                            this.preload = false;
                            // this.cadastrado = true;
                            mostraSucesso('Medida administrativa alterada com sucesso');
                            this.atualizar();
                        }
                    }).catch(error => (this.preload = false));
            } else { //criar
                axios.post(`${URL_ADMIN}/historico/${this.feedback_id}`, this.form)
                    .then(response => {
                        if (response.status === 201) {
                            this.preload = false;
                            mostraSucesso('Medida administrativa criada com sucesso');
                            // this.cadastrado = true;
                            this.atualizar();
                        }
                    }).catch(error => (this.preload = false));
            }
        },
        atualizar() {
            this.preload = true;
            this.form.medidas_administrativas = [];
            this.form.medidas_administrativasDelete = [];
            axios.get(`${URL_ADMIN}/historico/${this.feedback_id}`).then(res => {
                let data = res.data;
                this.form.medidas_administrativas = data.feedback.medidas_administrativas;
                this.feedbackInfo = data.feedback;
                this.causas = data.causas;
                this.tipos = data.tipos;
                this.definicao = data.definicao;
                this.hoje = data.hoje;
                this.restricao = data.restricao;
                this.privilegio_gestao_rh = data.privilegio_gestao_rh || false;
                this.preload = false;
            })
        },
        abrirModalRemover(obj) {
            this.medidaSelecionada = obj;
            this.auditoriaForm = {
                descricao: '',
                medida_id: obj.id,
                feedback_id: this.feedback_id
            };
            this.tituloModalRemover = `Remover Medida Administrativa: ${obj.tipo}`;
            this.preloadRemover = false;
        },
        removerMedida() {
            if (!this.auditoriaForm.descricao || this.auditoriaForm.descricao.length === 0) {
                mostraErro('', 'Informe o motivo da remoção da medida administrativa');
                return false;
            }
            
            this.preloadRemover = true;
            axios.put(`${URL_ADMIN}/historico/medidas-administrativas/remover-medida-administrativa`, {
                medida_id: this.auditoriaForm.medida_id,
                feedback_id: this.auditoriaForm.feedback_id,
                motivo: this.auditoriaForm.descricao
            }).then(response => {
                if (response.status === 201) {
                    this.preloadRemover = false;
                    $(`#janelaRemoverMedida_${this.hash}`).modal('hide');
                    mostraSucesso('Medida administrativa removida com sucesso');
                    this.medidaSelecionada = null;
                    this.auditoriaForm = {
                        descricao: '',
                        medida_id: null,
                        feedback_id: null
                    };
                    this.atualizar();
                }
            }).catch(error => {
                this.preloadRemover = false;
                const errorMsg = error.response && error.response.data && error.response.data.msg 
                    ? error.response.data.msg 
                    : 'Erro ao remover medida administrativa';
                mostraErro(errorMsg);
            });
        }
    }
}
</script>

<style scoped>

</style>
