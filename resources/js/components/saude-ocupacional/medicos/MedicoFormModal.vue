<template>
    <modal id="modalMedicoSaude" ref="modal" :titulo="form.id ? 'Editar médico' : 'Novo médico'" :size="70" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos">
                <fieldset class="mybp-modal-secao">
                    <legend>Dados do médico</legend>
                    <div class="form-row">
                        <div class="col-12 col-md-6 form-group">
                            <label class="mybp-label" for="medico-nome">Nome</label>
                            <input id="medico-nome" type="text" maxlength="150" class="form-control form-control-sm" v-model.trim="form.nome" />
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="mybp-label" for="medico-crm">CRM</label>
                            <input id="medico-crm" type="text" inputmode="numeric" maxlength="10" class="form-control form-control-sm" v-model.trim="form.crm" />
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="mybp-label" for="medico-uf">UF do CRM</label>
                            <select id="medico-uf" class="form-control form-control-sm" v-model="form.crm_uf">
                                <option value="">—</option>
                                <option v-for="uf in opcoes.ufs" :key="uf" :value="uf">{{ uf }}</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="mybp-label" for="medico-rqe">RQE</label>
                            <input id="medico-rqe" type="text" maxlength="20" class="form-control form-control-sm" v-model.trim="form.rqe" />
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="mybp-label" for="medico-especialidade">Especialidade</label>
                            <input id="medico-especialidade" type="text" maxlength="100" class="form-control form-control-sm" v-model.trim="form.especialidade" placeholder="Medicina do Trabalho" />
                        </div>
                        <div class="col-12 col-md-3 form-group">
                            <label class="mybp-label" for="medico-email">E-mail</label>
                            <input id="medico-email" type="email" maxlength="150" class="form-control form-control-sm" v-model.trim="form.email" />
                        </div>
                        <div class="col-12 col-md-3 form-group">
                            <label class="mybp-label" for="medico-telefone">Telefone</label>
                            <input id="medico-telefone" type="text" maxlength="20" class="form-control form-control-sm" v-model.trim="form.telefone" />
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Vínculos com a empresa</legend>
                    <p class="mybp-modal-legenda">Só pode haver um coordenador do PCMSO por período. Deixe o fim em branco se não houver data.</p>

                    <div v-for="(v, i) in form.vinculos" :key="v.chave" class="form-row align-items-end vinculo-linha">
                        <div class="col-12 col-md-3 form-group">
                            <label class="mybp-label">Papel</label>
                            <select class="form-control form-control-sm" v-model="v.papel">
                                <option v-for="p in opcoes.papeis" :key="p.value" :value="p.value">{{ p.label }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 form-group">
                            <label class="mybp-label">Clínica</label>
                            <select class="form-control form-control-sm" v-model="v.empresa_exame_id" :disabled="v.papel !== 'examinador'">
                                <option :value="null">—</option>
                                <option v-for="c in opcoes.clinicas" :key="c.value" :value="c.value">{{ c.label }}</option>
                            </select>
                        </div>
                        <div class="col-5 col-md-2">
                            <datepicker :id="`vinculo-inicio-${v.chave}`" label="Início" formsm v-model="v.vigencia_inicio" :config="{ format: 'DD/MM/YYYY', useCurrent: false }" />
                        </div>
                        <div class="col-5 col-md-2">
                            <datepicker :id="`vinculo-fim-${v.chave}`" label="Fim" formsm v-model="v.vigencia_fim" :config="{ format: 'DD/MM/YYYY', useCurrent: false }" />
                        </div>
                        <div class="col-2 col-md-2 form-group text-right">
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Remover vínculo" @click="form.vinculos.splice(i, 1)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary" @click="adicionarVinculo">
                        <i class="fa fa-plus"></i> Adicionar vínculo
                    </button>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Cancelar</button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvar">
                <i :class="salvando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> Salvar
            </button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

const BASE = () => `${URL_ADMIN}/saude-ocupacional/medicos`
let sequencia = 0

function vinculoVazio(papel = 'examinador') {
    sequencia += 1
    return { chave: sequencia, id: null, papel, empresa_exame_id: null, vigencia_inicio: '', vigencia_fim: '' }
}

export default defineComponent({
    name: 'MedicoFormModal',

    props: {
        opcoes: { type: Object, required: true }
    },

    emits: ['salvo'],

    data() {
        return {
            form: { id: null, nome: '', crm: '', crm_uf: '', rqe: '', especialidade: '', email: '', telefone: '', vinculos: [] },
            salvando: false
        }
    },

    methods: {
        abrir(medico = null) {
            this.form = medico
                ? {
                    id: medico.id,
                    nome: medico.nome,
                    crm: medico.crm,
                    crm_uf: medico.crm_uf,
                    rqe: medico.rqe || '',
                    especialidade: medico.especialidade || '',
                    email: medico.email || '',
                    telefone: medico.telefone || '',
                    vinculos: (medico.vinculos || []).map((v) => ({
                        ...vinculoVazio(v.papel),
                        id: v.id,
                        empresa_exame_id: v.empresa_exame_id,
                        vigencia_inicio: v.vigencia_inicio_br || '',
                        vigencia_fim: v.vigencia_fim_br || ''
                    }))
                }
                : { id: null, nome: '', crm: '', crm_uf: '', rqe: '', especialidade: 'Medicina do Trabalho', email: '', telefone: '', vinculos: [vinculoVazio('coordenador')] }
            this.$refs.modal && this.$refs.modal.abrirModal()
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        adicionarVinculo() {
            this.form.vinculos.push(vinculoVazio())
        },

        async salvar() {
            const dados = {
                ...this.form,
                vinculos: this.form.vinculos.map((v) => ({
                    id: v.id,
                    papel: v.papel,
                    empresa_exame_id: v.papel === 'examinador' ? v.empresa_exame_id : null,
                    vigencia_inicio: v.vigencia_inicio,
                    vigencia_fim: v.vigencia_fim || null
                }))
            }

            this.salvando = true
            try {
                const { data } = this.form.id
                    ? await axios.put(`${BASE()}/${this.form.id}`, dados)
                    : await axios.post(BASE(), dados)
                mostraSucesso('', data.mensagem || 'Médico salvo.')
                this.fechar()
                this.$emit('salvo')
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.salvando = false
            }
        }
    }
})
</script>

<style scoped>
.vinculo-linha {
    border-bottom: 1px dashed #dee2e6;
    margin-bottom: 0.5rem;
}
</style>
