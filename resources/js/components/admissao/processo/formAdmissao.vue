<template>
    <div class="row">
        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Área</label>
                <select class="form-control" v-model="form.area_etiqueta_id"
                        onchange="valida_campo_vazio(this,1)"
                        onblur="valida_campo_vazio(this,1)"
                        :disabled="visualizar">
                    <option value="">Selecione</option>
                    <option :value="item.id"
                            v-for="item in areasetiquetas">
                        {{ item.label }}
                    </option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Função</label>
                <input type="text" class="form-control" onblur="valida_campo_vazio(this,2)"
                       :disabled="visualizar"
                       v-model="form.funcao">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Cargo</label>
                <input type="text" class="form-control" onblur="valida_campo_vazio(this,2)"
                       :disabled="visualizar"
                       v-model="form.cargo">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Salário R$</label>
                <input type="text" class="form-control" v-mascara:dinheiro :disabled="visualizar"
                       v-model="form.salario">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Documento</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar"
                        v-model="form.documento">
                    <option value="">Selecione</option>
                    <option value="PENDENTE">PENDENTE</option>
                    <option value="INCOMPLETO">INCOMPLETO</option>
                    <option value="CONCLUIDO">CONCLUIDO</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Documento Portaria</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar"
                        v-model="form.documento_portaria">
                    <option value="">Selecione</option>
                    <option value="PENDENTE">PENDENTE</option>
                    <option value="CONCLUIDO">CONCLUIDO</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Tipo de admissão</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar"
                        v-model="form.tipo_admissao">
                    <option value="">Selecione</option>
                    <option value="TEMPORARIO">TEMPORARIO</option>
                    <option value="INTERMITENTE">INTERMITENTE</option>
                    <option value="DETERMINADO">DETERMINADO</option>
                    <option value="FIXO">FIXO</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Treinamento</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar"
                        v-model="form.treinamento">
                    <option value="">Selecione</option>
                    <option value="AGENDAR">AGENDAR</option>
                    <option value="NÃO SE APLICA">NÃO SE APLICA</option>
                    <option value="REALIZADO">REALIZADO</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6" v-if="form.treinamento === 'REALIZADO'">
            <div class="form-group">
                <label>Tipo de Treinamento</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar"
                        v-model="form.tipo_treinamento">
                    <option value="">Selecione</option>
                    <option value="COMPLETO">COMPLETO</option>
                    <option value="PARADA">PARADA</option>
                    <option value="LARGO">LARGO</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>NR 33</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar"
                        v-model="form.nr_trinta_tres">
                    <option value="">Selecione</option>
                    <option value="AGENDAR">AGENDAR</option>
                    <option value="NÃO SE APLICA">NÃO SE APLICA</option>
                    <option value="REALIZADO">REALIZADO</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>NR 35</label>
                <select class="form-control" onchange="valida_campo(this,1)"
                        onblur="valida_campo(this,1)" :disabled="visualizar"
                        v-model="form.nr_trinta_cinco">
                    <option value="">Selecione</option>
                    <option value="AGENDAR">AGENDAR</option>
                    <option value="NÃO SE APLICA">NÃO SE APLICA</option>
                    <option value="REALIZADO">REALIZADO</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Número Crachá</label>
                <input type="text" class="form-control" onblur="valida_campo(this,2)"
                       :disabled="visualizar"
                       v-model="form.numero_cracha">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Data do ASO</label>
                <input type="text" class="form-control" placeholder="dd/mm/aaaa" :disabled="visualizar"
                       v-model="form.data_aso" v-mascara:data
                       onblur="valida_data(this)"
                       @blur="validaData">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Status Carteira de Treinamento e Etiqueta</label>
                <select class="form-control" onchange="valida_campo(this,1)" :disabled="visualizar"
                        onblur="valida_campo(this,1)"
                        v-model="form.status_carteira_treinamento">
                    <option value="">Selecione</option>
                    <option value="PENDENTE">PENDENTE</option>
                    <option value="AGUARDANDO TREINAMENTO">AGUARDANDO TREINAMENTO</option>
                    <option value="ENTREGUE">ENTREGUE</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Status</label>
                <select class="form-control" onchange="valida_campo_vazio(this,1)"
                        :disabled="visualizar"
                        onblur="valida_campo_vazio(this,1)"
                        v-model="form.status">
                    <option value="">Selecione</option>
                    <option value="AGUARDANDO QUALIFICAÇÃO">AGUARDANDO QUALIFICAÇÃO</option>
                    <option value="PRONTO PARA ADMISSAO">PRONTO PARA ADMISSAO</option>
                    <option value="ADMITIDO">ADMITIDO</option>
                    <option value="STAND BY">STAND BY</option>
                    <option value="PENDENTE ASO">PENDENTE ASO</option>
                    <option value="PENDENTE DOCUMENTO">PENDENTE DOCUMENTO</option>
                    <option value="PENDENTE TREINAMENTO">PENDENTE TREINAMENTO</option>
                    <option value="CANCELADO">CANCELADO</option>
                    <option value="ENCAMINHADO EXAME">ENCAMINHADO EXAME</option>
                    <option value="DESISTÊNCIA">DESISTÊNCIA</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Data da Admissão</label>
                <input type="text" class="form-control" placeholder="dd/mm/aaaa" :disabled="visualizar"
                       v-model="form.data_admissao" v-mascara:data
                       onblur="valida_data(this)"
                       @blur="validaDataAdmissao">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Data da Entrega na área</label>
                <input type="text" class="form-control" placeholder="dd/mm/aaaa" :disabled="visualizar"
                       v-model="form.data_entrega_area" v-mascara:data
                       onblur="valida_data(this)">
            </div>
        </div>


        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Biometria</label>
                <select class="form-control" :disabled="visualizar"
                        v-model="form.biometria">
                    <option value="">Selecione</option>
                    <option :value="true">SIM</option>
                    <option :value="false">NÃO</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6" v-if="form.biometria">
            <div class="form-group">
                <label>Data Biometria</label>
                <input type="text" class="form-control" placeholder="dd/mm/aaaa" :disabled="visualizar"
                       v-model="form.data_biometria" v-mascara:data
                       onblur="valida_data(this)">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>PIS</label>
                <input type="text" class="form-control" onblur="valida_campo(this,2)"
                       :disabled="visualizar"
                       v-model="form.pis">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Número CTPS</label>
                <input type="text" class="form-control" onblur="valida_campo(this,2)"
                       :disabled="visualizar"
                       v-model="form.tableDadosAdmissao.ctps_numero">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Série CTPS</label>
                <input type="text" class="form-control" onblur="valida_campo(this,2)"
                       :disabled="visualizar"
                       v-model="form.tableDadosAdmissao.ctps_serie">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Data Emissão CTPS</label>
                <input type="text" class="form-control" placeholder="dd/mm/aaaa" :disabled="visualizar"
                       v-model="form.tableDadosAdmissao.ctps_data_emissao" v-mascara:data
                       onblur="valida_data(this)"
                       @blur="validaData">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Titulo de Eleitor</label>
                <input type="text" class="form-control" onblur="valida_campo(this,2)"
                       :disabled="visualizar"
                       v-model="form.tableDadosAdmissao.titulo_eleitor_numero">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Titulo de Eleitor Sessão</label>
                <input type="text" class="form-control" onblur="valida_campo(this,2)"
                       :disabled="visualizar"
                       v-model="form.tableDadosAdmissao.titulo_eleitor_sessao">
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label>Titulo de Eleitor Zona</label>
                <input type="text" class="form-control" onblur="valida_campo(this,2)"
                       :disabled="visualizar"
                       v-model="form.tableDadosAdmissao.titulo_eleitor_zona">
            </div>
        </div>

    </div>
</template>

<script>
export default {
    props: {
        form: {
            type: Object,
            required: true,
            default: {
                feedback_id: "",
                area_etiqueta_id: "",
                contrato: "",
                funcao: "",
                cargo: "",
                salario: "0,00",
                status: "",
                documento: "",
                documento_portaria: "",
                tipo_admissao: "",
                tipo_treinamento: "",
                treinamento: "",
                data_treinamento: "",
                carteira_treinamento: "",
                nr_trinta_tres: "",
                data_nr_trinta_tres: "",
                nr_trinta_cinco: "",
                data_nr_trinta_cinco: "",
                trinta_dois_sessenta: "",
                data_trinta_dois_sessenta: "",
                numero_cracha: "",
                data_aso: "",
                foto_escaneada: "",
                status_carteira_treinamento: "",
                data_admissao: "",

                data_entrega_area: "",
                biometria: "",
                data_biometria: "",

                indicado_por: "",
                indicado_area: "",

                filiacao_pai: "",
                filiacao_mae: "",
                nome: "",
                calca: "",
                bota: "",
                camisa_protecao: "",
                camisa_meia: "",
                pis: "",
                tableDadosAdmissao: {
                    ctps_numero: '',
                    ctps_serie: '',
                    ctps_data_emissao: '',
                    titulo_eleitor_numero: '',
                    titulo_eleitor_sessao: '',
                    titulo_eleitor_zona: '',
                },
            }
        },
        visualizar: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            hash: `mastertag_${parseInt(Math.random() * 999999)}`,
            preload: true,
            areasetiquetas: []
        };
    },
    created() {
        axios.get(`${URL_PUBLICO}/lista-areas/${AUTENTICADO.empresa_id}`)
            .then(response => {
                this.areasetiquetas = response.data;
            }).catch(e => console.log(e));
    },
    methods: {
        validaData() {
            if (this.form.data_aso.length >= 10) {
                let dataCorreta = moment(this.form.data_aso, "DD/MM/YYYY");
                if (!dataCorreta.isValid()) {
                    mostraErro("", "A data do ASO inserida é inválida");
                    this.form.data_aso = "";
                }
            }
        },

        validaDataAdmissao() {
            if (this.form.data_admissao.length >= 10) {
                let dataCorreta = moment(this.form.data_admissao, "DD/MM/YYYY");
                if (!dataCorreta.isValid()) {
                    mostraErro("", "A data da admissao inserida é inválida");
                    this.form.data_admissao = "";
                }
            }
        }
    }
};
</script>

<style scoped></style>
