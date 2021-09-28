<template>
    <div>
        <p class=" mt-2" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>
        <div v-if="!preload" :id="`form_${hash}`">
            <fieldset>
                <legend>DOCUMENTO DE SELEÇÃO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'DocSelecao',
                            // curriculo_id: curriculo.id,
                            label: 'DOCUMENTO DE SELEÇÃO'
                        }"
                        :model="form.doc_selecao"
                        :model-delete="form.doc_selecaoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>
            <fieldset>
                <legend>DOCUMENTOS CHECK LIST ADMISSÃO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'DocChecklist',
                            // curriculo_id: curriculo.id,
                            label: 'DOCUMENTOS CHECK LIST ADMISSÃO'
                        }"
                        :model="form.doc_checklist"
                        :model-delete="form.doc_checklistDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>FICHA REGISTRO ASSINADA</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'FichaRegistrada',
                            // curriculo_id: curriculo.id,
                            label: 'FICHA REGISTRO ASSINADA'
                        }"
                        :model="form.ficha_registrada"
                        :model-delete="form.ficha_registradaDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>CONTRATO DE TRABALHO ASSINADO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ContratoTrabalhoAssinado',
                            // curriculo_id: curriculo.id,
                            label: 'CONTRATO DE TRABALHO ASSINADO'
                        }"
                        :model="form.contrato_trabalho_assinado"
                        :model-delete="form.contrato_trabalho_assinadoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>TERMO DE CONFIDENCIALIDADE ASSINADO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'TermoConfiabilidade',
                            // curriculo_id: curriculo.id,
                            label: 'TERMO DE CONFIDENCIALIDADE ASSINADO'
                        }"
                        :model="form.termo_confiabilidade"
                        :model-delete="form.termo_confiabilidadeDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>OPÇÃO VALE TRANSPORTE ASSINADO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ValeTransporteAssinado',
                            // curriculo_id: curriculo.id,
                            label: 'OPÇÃO VALE TRANSPORTE ASSINADO'
                        }"
                        :model="form.vale_transporte_assinado"
                        :model-delete="form.vale_transporte_assinadoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>ACORDO COMPENSAÇÃO DE HORAS ASSINADO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'AcordoHora',
                            // curriculo_id: curriculo.id,
                            label: 'ACORDO COMPENSAÇÃO DE HORAS ASSINADO'
                        }"
                        :model="form.acordo_hora"
                        :model-delete="form.acordo_horaDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>TERMO SALÁRIO FAMILIA ASSINADO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'SalarioFamiliaAssinado',
                            // curriculo_id: curriculo.id,
                            label: 'TERMO SALÁRIO FAMILIA ASSINADO'
                        }"
                        :model="form.salario_familia_assinado"
                        :model-delete="form.salario_familia_assinadoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>DECLARAÇÃO DEPENDENTES IMPOSTO DE RENDA ASSINADO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'DeclaracaoDependentesImposto',
                            // curriculo_id: curriculo.id,
                            label: 'DECLARAÇÃO DEPENDENTES IMPOSTO DE RENDA ASSINADO'
                        }"
                        :model="form.declaracao_dependentes_imposto"
                        :model-delete="form.declaracao_dependentes_impostoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>COMPROVANTE DEVOLUÇÃO CTPS ASSINADO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ComprovanteDevCtp',
                            // curriculo_id: curriculo.id,
                            label: 'COMPROVANTE DEVOLUÇÃO CTPS ASSINADO'
                        }"
                        :model="form.comprovante_dev_ctp"
                        :model-delete="form.comprovante_dev_ctpDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>ORDEM DE SERVIÇO ASSINADA</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'OrdemServicoAssinada',
                            // curriculo_id: curriculo.id,
                            label: 'ORDEM DE SERVIÇO ASSINADA'
                        }"
                        :model="form.ordem_servico_assinada"
                        :model-delete="form.ordem_servico_assinadaDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>CERTIFICADOS DE TREINAMENTOS SEGURANÇA</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'CertificadoTreinSeg',
                            // curriculo_id: curriculo.id,
                            label: 'CERTIFICADOS DE TREINAMENTOS SEGURANÇA'
                        }"
                        :model="form.certificado_trein_seg"
                        :model-delete="form.certificado_trein_segDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>FICHA DE ENTREGA DE EPI</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'FichaEntregaEpi',
                            // curriculo_id: curriculo.id,
                            label: 'FICHA DE ENTREGA DE EPI'
                        }"
                        :model="form.ficha_entrega_epi"
                        :model-delete="form.ficha_entrega_epiDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>CONTRACHEQUES MENSAIS</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ContraChequeMensais',
                            // curriculo_id: curriculo.id,
                            label: 'CONTRACHEQUES MENSAIS'
                        }"
                        :model="form.contra_cheque_mensais"
                        :model-delete="form.contra_cheque_mensaisDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>CARTÕES DE PONTO MENSAIS</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'CartoesPonto',
                            // curriculo_id: curriculo.id,
                            label: 'CARTÕES DE PONTO MENSAIS'
                        }"
                        :model="form.cartoes_ponto"
                        :model-delete="form.cartoes_pontoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>AVISOS DE FÉRIAS ANUAIS</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'AvisoFerias',
                            // curriculo_id: curriculo.id,
                            label: 'AVISOS DE FÉRIAS ANUAIS'
                        }"
                        :model="form.aviso_ferias"
                        :model-delete="form.aviso_feriasDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>PASTA DE CONTROLE DE ASOS: ADMISSIONAIS, PERIODICOS,MUDANÇA DE FUNÇÃO, DEMISSIONAL</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ControleAsos',
                            // curriculo_id: curriculo.id,
                            label: 'PASTA DE CONTROLE DE ASOS: ADMISSIONAIS, PERIODICOS,MUDANÇA DE FUNÇÃO, DEMISSIONAL'
                        }"
                        :model="form.controle_asos"
                        :model-delete="form.controle_asosDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>BOOK DE RESCISÃO – CHECK LIST DEMISSÃO:</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'BookRescisao',
                            // curriculo_id: curriculo.id,
                            label: 'BOOK DE RESCISÃO – CHECK LIST DEMISSÃO:'
                        }"
                        :model="form.book_rescisao"
                        :model-delete="form.book_rescisaoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>TERMO DE RESCISAO DE CONTRATO DE TRABALHO ASSINADO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'TermoRescisao',
                            // curriculo_id: curriculo.id,
                            label: 'TERMO DE RESCISAO DE CONTRATO DE TRABALHO ASSINADO'
                        }"
                        :model="form.termo_rescisao"
                        :model-delete="form.termo_rescisaoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>GUIAS DE SEGURO DESEMPREGO ASSINADAS</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'GuiaSeguroDesemprego',
                            // curriculo_id: curriculo.id,
                            label: 'GUIAS DE SEGURO DESEMPREGO ASSINADAS'
                        }"
                        :model="form.guia_seguro_desemprego"
                        :model-delete="form.guia_seguro_desempregoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>CHAVE DE FGTS ASSINADA</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ChaveFgts',
                            // curriculo_id: curriculo.id,
                            label: 'CHAVE DE FGTS ASSINADA'
                        }"
                        :model="form.chave_fgts"
                        :model-delete="form.chave_fgtsDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>COMPROVANTE DE PAGAMENTO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ComprovantePagamento',
                            // curriculo_id: curriculo.id,
                            label: 'COMPROVANTE DE PAGAMENTO'
                        }"
                        :model="form.comprovante_pagamento"
                        :model-delete="form.comprovante_pagamentoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>EXAME DEMISSIONAL</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ExameDemissional',
                            // curriculo_id: curriculo.id,
                            label: 'EXAME DEMISSIONAL'
                        }"
                        :model="form.exame_demissional"
                        :model-delete="form.exame_demissionalDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>NADA CONSTA DE BAIXA E FICHA DE ENTREGA DE EPI</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'NadaConstaFichaEpi',
                            // curriculo_id: curriculo.id,
                            label: 'NADA CONSTA DE BAIXA E FICHA DE ENTREGA DE EPI'
                        }"
                        :model="form.nada_consta_ficha_epi"
                        :model-delete="form.nada_consta_ficha_epiDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>COMPROVANTE DEVOLUÇÃO CTPS ASSINADO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ComprovanteDevolucaoCtps',
                            // curriculo_id: curriculo.id,
                            label: 'COMPROVANTE DEVOLUÇÃO CTPS ASSINADO'
                        }"
                        :model="form.comprovante_devolucao_ctps"
                        :model-delete="form.comprovante_devolucao_ctpsDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>PPP ASSINADO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'PppAssinado',
                            // curriculo_id: curriculo.id,
                            label: 'PPP ASSINADO'
                        }"
                        :model="form.ppp_assinado"
                        :model-delete="form.ppp_assinadoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>FORMA DE ARQUIVAMENTO REGISTRO ELETRÔNICO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ArquivamentoEletronico',
                            // curriculo_id: curriculo.id,
                            label: 'FORMA DE ARQUIVAMENTO REGISTRO ELETRÔNICO'
                        }"
                        :model="form.arquivamento_eletronico"
                        :model-delete="form.arquivamento_eletronicoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>

            <fieldset>
                <legend>FORMA DE ARQUIVAMENTO DOSSIÊ</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{
                            tipo:'ArquivamentoDossie',
                            // curriculo_id: curriculo.id,
                            label: 'FORMA DE ARQUIVAMENTO DOSSIÊ'
                        }"
                        :model="form.arquivamento_dossie"
                        :model-delete="form.arquivamento_dossieDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false"></upload>
            </fieldset>


            <button class="btn btn-sm btn-primary mb-3" @click="salvar">
                <i class="fa fa-save"></i> Salvar
            </button>

        </div>
    </div>
</template>

<script>
import Upload from "../../Upload";

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
        Upload
    },
    data() {
        return {
            preload: false,
            URL_ADMIN,

            urlAnexoUpload: `${URL_ADMIN}/historico/dossie/uploadAnexos`,
            anexoUploadAndamento: false,

            hoje: '',


            form: {
                // foto_tres: [], //FOTO 3X4
                // foto_tresDel: [],

                doc_selecao: [],
                doc_selecaoDel: [],

                doc_checklist: [],
                doc_checklistDel: [],

                ficha_registrada: [],
                ficha_registradaDel: [],

                contrato_trabalho_assinado: [],
                contrato_trabalho_assinadoDel: [],

                termo_confiabilidade: [],
                termo_confiabilidadeDel: [],

                vale_transporte_assinado: [],
                vale_transporte_assinadoDel: [],

                acordo_hora: [],
                acordo_horaDel: [],

                salario_familia_assinado: [],
                salario_familia_assinadoDel: [],

                declaracao_dependentes_imposto: [],
                declaracao_dependentes_impostoDel: [],

                comprovante_dev_ctp: [],
                comprovante_dev_ctpDel: [],

                ordem_servico_assinada: [],
                ordem_servico_assinadaDel: [],

                certificado_trein_seg: [],
                certificado_trein_segDel: [],

                ficha_entrega_epi: [],
                ficha_entrega_epiDel: [],

                contra_cheque_mensais: [],
                contra_cheque_mensaisDel: [],

                cartoes_ponto: [],
                cartoes_pontoDel: [],

                aviso_ferias: [],
                aviso_feriasDel: [],

                controle_asos: [],
                controle_asosDel: [],

                book_rescisao: [],
                book_rescisaoDel: [],

                termo_rescisao: [],
                termo_rescisaoDel: [],

                guia_seguro_desemprego: [],
                guia_seguro_desempregoDel: [],

                chave_fgts: [],
                chave_fgtsDel: [],

                comprovante_pagamento: [],
                comprovante_pagamentoDel: [],

                exame_demissional: [],
                exame_demissionalDel: [],

                nada_consta_ficha_epi: [],
                nada_consta_ficha_epiDel: [],

                comprovante_devolucao_ctps: [],
                comprovante_devolucao_ctpsDel: [],

                ppp_assinado: [],
                ppp_assinadoDel: [],

                arquivamento_eletronico: [],
                arquivamento_eletronicoDel: [],

                arquivamento_dossie: [],
                arquivamento_dossieDel: [],

            },
            formDefault: null,

        }
    },
    mounted() {
        this.atualizar();
    },
    methods: {
        gerarPdf(obj) {
            // let link = `${URL_ADMIN}/historico/medidas-administrativas/${obj.id}/${obj.feedback_id}/pdf`;
            // open(link, 'blank');
        },
        salvar() {
            formReset();
            $(`#form_${this.hash} :input:visible`).trigger('blur');
            if ($(`#form_${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros')
                return false;
            }

            this.preload = true;

            axios.post(`${URL_ADMIN}/historico/dossie/${this.feedback_id}`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preload = false;
                        mostraSucesso('Dossiê salvo com sucesso!');
                        this.atualizar();
                    }
                }).catch(error => (this.preload = false));
        },
        atualizar() {
            this.preload = true;
            this.form.medidas_administrativas = [];
            this.form.medidas_administrativasDelete = [];
            axios.get(`${URL_ADMIN}/historico/dossie/${this.feedback_id}`).then(res => {
                let data = res.data;
                this.form = data;
                this.preload = false;
            })
        }
    }
}
</script>

<style scoped>

</style>
