<template>
    <modal id="modalExamesCatalogo" ref="modal" titulo="Catálogo de exames complementares" :size="60" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos">
                <fieldset class="mybp-modal-secao">
                    <legend>{{ form.id ? 'Editar exame' : 'Novo exame' }}</legend>
                    <div class="form-row align-items-end">
                        <div class="col-12 col-md-6 form-group">
                            <label class="mybp-label" for="exame-label">Nome</label>
                            <input id="exame-label" type="text" maxlength="120" class="form-control form-control-sm" v-model.trim="form.label" placeholder="Ex.: Audiometria tonal" />
                        </div>
                        <div class="col-8 col-md-3 form-group">
                            <label class="mybp-label" for="exame-codigo">Código eSocial (Tab. 27)</label>
                            <input id="exame-codigo" type="text" inputmode="numeric" maxlength="4" class="form-control form-control-sm" v-model.trim="form.codigo_esocial" placeholder="Ex.: 0281" />
                        </div>
                        <div class="col-4 col-md-3 form-group text-right">
                            <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvar">
                                <i :class="salvando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> {{ form.id ? 'Salvar' : 'Adicionar' }}
                            </button>
                        </div>
                    </div>
                    <a v-if="form.id" href="javascript://" class="small" @click="limpar">Cancelar edição</a>
                </fieldset>

                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Exame</th>
                                <th>Código</th>
                                <th>Status</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="e in exames" :key="e.id" :class="{ 'text-muted': !e.ativo }">
                                <td>{{ e.label }}</td>
                                <td>{{ e.codigo_esocial || '—' }}</td>
                                <td><span class="badge" :class="e.ativo ? 'badge-success' : 'badge-secondary'">{{ e.ativo ? 'Ativo' : 'Inativo' }}</span></td>
                                <td class="text-right text-nowrap">
                                    <button type="button" class="btn btn-xs btn-outline-primary mr-1" title="Editar" @click="editar(e)"><i class="fa fa-pen"></i></button>
                                    <button type="button" class="btn btn-xs" :class="e.ativo ? 'btn-outline-warning' : 'btn-outline-success'" :title="e.ativo ? 'Desativar' : 'Ativar'" @click="alternar(e)">
                                        <i :class="e.ativo ? 'fa fa-pause' : 'fa fa-play'"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!exames.length">
                                <td colspan="4" class="text-muted text-center">Nenhum exame cadastrado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Fechar</button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

const BASE = () => `${URL_ADMIN}/saude-ocupacional/pcmso/exames`

export default defineComponent({
    name: 'ExamesCatalogoModal',

    props: {
        exames: { type: Array, default: () => [] }
    },

    emits: ['alterado'],

    data() {
        return { form: { id: null, label: '', codigo_esocial: '' }, salvando: false }
    },

    methods: {
        abrir() {
            this.limpar()
            this.$refs.modal && this.$refs.modal.abrirModal()
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        limpar() {
            this.form = { id: null, label: '', codigo_esocial: '' }
        },

        editar(exame) {
            this.form = { id: exame.id, label: exame.label, codigo_esocial: exame.codigo_esocial || '' }
        },

        async enviar(id, dados) {
            const { data } = id ? await axios.put(`${BASE()}/${id}`, dados) : await axios.post(BASE(), dados)
            mostraSucesso('', data.mensagem)
            this.$emit('alterado')
        },

        async salvar() {
            this.salvando = true
            try {
                await this.enviar(this.form.id, { label: this.form.label, codigo_esocial: this.form.codigo_esocial || null })
                this.limpar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.salvando = false
            }
        },

        async alternar(exame) {
            try {
                await this.enviar(exame.id, { label: exame.label, codigo_esocial: exame.codigo_esocial, ativo: !exame.ativo })
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        }
    }
})
</script>
