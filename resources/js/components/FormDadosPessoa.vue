<template>
    <div>
        <h5 class="alert alert-warning text-center">
            ATENÇÃO! E-mail inválido ou inexistente, desclassificará o candidato. Só altere se necessário, este e-mail será usado para entrar na plataforma.
        </h5>
        <fieldset>
            <legend>Dados Pessoais</legend>
            <div class="row">
                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Nome</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.nome"
                            placeholder="Nome"
                            autocomplete="mastertag"
                            onblur="valida_campo_vazio(this, 3)"
                        />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>CPF</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.cpf"
                            placeholder="CPF"
                            :disabled="form.cpf.length > 0"
                            autocomplete="mastertag"
                            v-mascara:cpf
                            onblur="valida_cpf_vazio(this)"
                        />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>RG</label>
                        <input type="text" class="form-control" v-model="form.rg" placeholder="RG" autocomplete="mastertag" v-mascara:numero />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Orgão Expeditor (RG)</label>
                        <input type="text" class="form-control" v-model="form.orgao_expeditor" placeholder="Orgão" autocomplete="mastertag" />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>CNH</label>
                        <input type="text" class="form-control" v-model="form.cnh" placeholder="Tipo da CNH" autocomplete="mastertag" />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Nascimento</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.nascimento"
                            placeholder="Ex: 10/10/2010"
                            :disabled="possuiCadastro"
                            v-mascara:data
                            autocomplete="mastertag"
                            onblur="valida_data_vazio(this)"
                        />
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Sexo</label>
                        <select class="form-control" v-model="form.sexo" onchange="valida_campo_vazio(this, 1)" onblur="valida_campo_vazio(this, 1)">
                            <option value="">Selecione</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                        </select>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Nome do pai</label>
                        <input type="text" class="form-control" v-model="form.filiacao_pai" placeholder="Nome" autocomplete="mastertag" />
                    </div>
                </div>

                <div class="col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>Nome da mãe</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.filiacao_mae"
                            onblur="valida_campo_vazio(this, 3)"
                            placeholder="Nome"
                            autocomplete="mastertag"
                        />
                    </div>
                </div>

                <div class="col-12"></div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                    <div class="form-group">
                        <label>E-mail</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.email"
                            placeholder="Informe seu melhor e-mail"
                            autocomplete="mastertag"
                            onblur="validaEmailVazio(this)"
                        />
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Endereço</legend>
            <div class="row">
                <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                    <endereco :model="form"></endereco>
                </div>
            </div>
        </fieldset>

        <fieldset class="table-warning">
            <legend>Contato</legend>
            <label class="text-danger">Adicionar no minimo DOIS contatos, pelo menos UM sendo WhatsApp</label>
            <div class="row">
                <div class="col-12 col-sm-12 col-lg-12 col-xl-12">
                    <telefone :model="form.telefones" :model-delete="form.telefonesDelete" :pais="false" :ramal="false"></telefone>
                </div>
            </div>
        </fieldset>
    </div>
</template>

<script>
import DatePicker from './DatePicker'
import endereco from './Endereco'
import telefone from './Telefones'
import autocomplete from './AutoComplete'

export default {
    components: {
        DatePicker,
        endereco,
        telefone,
        autocomplete
    },
    props: {
        form: {
            type: Object,
            required: true,
            default: {
                temqualificacao: false,
                temexperiencia: false,
                id: '',
                cpf: '',
                login: '',
                password: '',
                nome: '',
                cnh: '',
                nascimento: '',
                logradouro: '',
                complemento: '',
                bairro: '',
                municipio: '',
                uf: '',
                cep: '',
                email: '',
                filiacao_pai: '',
                filiacao_mae: '',
                formacao: '',
                formacao_instituicao: '',
                formacao_curso: '',
                formacao_status: '',
                vaga_pretendida: '',
                vaga_id: '',
                uf_vaga: '',
                municipio_id: '',
                rg: '',
                sexo: '',
                orgao_expeditor: '',
                termos: false,

                autocomplete_label_municipio_modal: '',
                autocomplete_label_municipio_modal_anterior: '',

                pcd: '',
                cid: '',
                viajar: '',

                qualificacoes: [
                    {
                        nova: '',
                        nome: '',
                        instituicao: '',
                        mes_conclusao: '01',
                        ano_conclusao: '2019'
                    }
                ],
                qualificacoesDelete: [],

                experiencias: [
                    {
                        nova: '',
                        empresa: '',
                        cargo: '',
                        principais_atv: '',
                        data_inicio: '',
                        data_fim: '',
                        referencia_nome: '',
                        referencia_telefone: ''
                    }
                ],
                experienciasDelete: [],

                telefones: [
                    {
                        detalhe: '',
                        novo: true,
                        numero: '',
                        pais: '55',
                        ramal: '',
                        tipo: 'whatsapp',
                        principal: false
                    }
                ],
                telefonesDelete: []
            }
        },
        visualizar: {
            type: Boolean,
            default: false
        }
    }
}
</script>

<style scoped></style>
