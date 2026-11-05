<template>
    <modal
        id="modalRegraSaude"
        ref="modal"
        :titulo="form.id ? 'Editar regra' : 'Nova regra'"
        :size="70"
        :mostrar-botao-fechar-no-rodape="false"
    >
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos">
                <fieldset class="mybp-modal-secao">
                    <div class="form-group">
                        <label class="mybp-label" for="regra-nome">Nome da regra</label>
                        <input id="regra-nome" type="text" maxlength="120" class="form-control form-control-sm" v-model.trim="form.nome" placeholder="Ex.: Idade 40+ anual" />
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Quando</legend>
                    <p class="mybp-modal-legenda">Todas as condições marcadas precisam valer.</p>

                    <div class="form-row align-items-center mb-2">
                        <div class="col-auto">
                            <label class="mybp-label font-weight-normal mb-0">
                                <input type="checkbox" v-model="form.idadeMinima.ativo" /> Idade a partir de
                            </label>
                        </div>
                        <div class="col-3 col-md-2">
                            <input type="number" min="14" max="100" class="form-control form-control-sm" v-model.number="form.idadeMinima.valor" :disabled="!form.idadeMinima.ativo" />
                        </div>
                        <div class="col-auto small text-muted">anos</div>
                    </div>

                    <div class="form-row align-items-center mb-2">
                        <div class="col-auto">
                            <label class="mybp-label font-weight-normal mb-0">
                                <input type="checkbox" v-model="form.idadeMaxima.ativo" /> Idade até
                            </label>
                        </div>
                        <div class="col-3 col-md-2">
                            <input type="number" min="14" max="100" class="form-control form-control-sm" v-model.number="form.idadeMaxima.valor" :disabled="!form.idadeMaxima.ativo" />
                        </div>
                        <div class="col-auto small text-muted">anos</div>
                    </div>

                    <div class="mb-1">
                        <label class="mybp-label font-weight-normal mb-1">
                            <input type="checkbox" v-model="form.cargo.ativo" /> Cargo é um destes
                        </label>
                        <template v-if="form.cargo.ativo">
                            <combobox-auto-complete
                                instance-id="regra-cargo"
                                input-id="regra-cargo-busca"
                                :model-value="null"
                                :options="cargosDisponiveis"
                                placeholder-blur="Buscar cargo para adicionar"
                                empty-message="Nenhum cargo encontrado."
                                @select="adicionarCargo"
                            />
                            <div class="mt-2">
                                <span v-for="id in form.cargo.valor" :key="id" class="badge badge-secondary mr-1 mb-1 p-2">
                                    {{ nomeCargo(id) }}
                                    <a href="javascript://" class="text-white ml-1" @click="removerCargo(id)" aria-label="Remover cargo">&times;</a>
                                </span>
                                <span v-if="!form.cargo.valor.length" class="small text-muted">Nenhum cargo escolhido.</span>
                            </div>
                        </template>
                    </div>
                    <div class="mb-1 mt-2">
                        <label class="mybp-label font-weight-normal mb-1">
                            <input type="checkbox" v-model="form.expostoRisco.ativo" /> Exposto a qualquer risco do PGR
                        </label>
                    </div>

                    <div class="mb-1">
                        <label class="mybp-label font-weight-normal mb-1">
                            <input type="checkbox" v-model="form.risco.ativo" /> Exposto a um destes riscos
                        </label>
                        <template v-if="form.risco.ativo">
                            <combobox-auto-complete
                                instance-id="regra-risco"
                                input-id="regra-risco-busca"
                                :model-value="null"
                                :options="riscosDisponiveis"
                                placeholder-blur="Buscar risco para adicionar"
                                empty-message="Nenhum risco no catálogo."
                                @select="adicionarRisco"
                            />
                            <div class="mt-2">
                                <span v-for="id in form.risco.valor" :key="id" class="badge badge-warning mr-1 mb-1 p-2">
                                    {{ nomeRisco(id) }}
                                    <a href="javascript://" class="ml-1" @click="removerRisco(id)" aria-label="Remover risco">&times;</a>
                                </span>
                                <span v-if="!form.risco.valor.length" class="small text-muted">Nenhum risco escolhido.</span>
                            </div>
                        </template>
                    </div>
                    <p class="small text-warning mb-0" v-if="(form.expostoRisco.ativo || form.risco.ativo) && !opcoes.pgr_vigente">
                        Condições de risco só valem com um PGR publicado (Programas → PGR).
                    </p>

                    <p class="small text-muted mb-0 mt-2">
                        A idade é medida na data em que o ASO venceria pelo padrão: quem faz 40 anos durante o ciclo já entra na regra "40+".
                    </p>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Então a validade do ASO é</legend>
                    <div class="form-row">
                        <div class="col-12 col-md-5 mb-2">
                            <select class="form-control form-control-sm" v-model="preset" @change="aplicarPreset">
                                <option value="1a">1 ano</option>
                                <option value="2a">2 anos</option>
                                <option value="d">Personalizado (dias)</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-2" v-if="preset === 'd'">
                            <input type="number" min="1" :max="limiteDias" class="form-control form-control-sm" v-model.number="form.valor" />
                        </div>
                    </div>
                    <p class="small text-muted mb-2">
                        Vale o menor prazo entre o padrão do tipo e as regras que se aplicam. Limite da NR-7: {{ limiteDias }} dias (2 anos).
                    </p>

                    <label class="mybp-label">Para os tipos de exame</label>
                    <div class="d-flex flex-wrap">
                        <label v-for="tipo in opcoes.tipos_exame" :key="tipo.id" class="mybp-label font-weight-normal mr-3">
                            <input type="checkbox" :value="tipo.id" v-model="form.exame_tipo_ids" /> {{ tipo.label }}
                        </label>
                    </div>
                </fieldset>

                <modo-aplicacao-campos v-model="aplicacao" id-data="regra-data-aplicacao" />

                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" :disabled="carregandoPrevia || salvando" @click="calcularPrevia">
                        <i class="fa fa-chart-bar"></i> Ver impacto
                    </button>
                    <previa-impacto :previa="previa" :carregando="carregandoPrevia" />
                </div>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Cancelar</button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvar">
                <i :class="salvando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> Salvar regra
            </button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import ModoAplicacaoCampos from './ModoAplicacaoCampos.vue'
import PreviaImpacto from './PreviaImpacto.vue'

const API = {
    criar: () => `${URL_ADMIN}/saude-ocupacional/regras`,
    atualizar: (id) => `${URL_ADMIN}/saude-ocupacional/regras/${id}`,
    previa: () => `${URL_ADMIN}/saude-ocupacional/regras/previa`
}

function formVazio() {
    return {
        id: null,
        nome: '',
        modelo: null,
        exame_tipo_ids: [],
        idadeMinima: { ativo: false, valor: 40 },
        idadeMaxima: { ativo: false, valor: 17 },
        cargo: { ativo: false, valor: [] },
        expostoRisco: { ativo: false },
        risco: { ativo: false, valor: [] },
        unidade: 'anos',
        valor: 1
    }
}

export default defineComponent({
    name: 'RegraFormModal',

    components: { ComboboxAutoComplete, ModoAplicacaoCampos, PreviaImpacto },

    props: {
        opcoes: { type: Object, required: true }
    },

    emits: ['salvo'],

    data() {
        return {
            form: formVazio(),
            preset: '1a',
            aplicacao: { modo: 'futuro', data: '' },
            previa: null,
            carregandoPrevia: false,
            salvando: false
        }
    },

    computed: {
        limiteDias() {
            return (this.opcoes.limite_legal && this.opcoes.limite_legal.dias) || 730
        },
        cargosDisponiveis() {
            return (this.opcoes.cargos || []).filter((c) => !this.form.cargo.valor.includes(c.value))
        },
        riscosDisponiveis() {
            return (this.opcoes.riscos || []).filter((r) => !this.form.risco.valor.includes(r.value))
        }
    },

    methods: {
        abrir(regra = null) {
            const form = formVazio()
            if (regra) {
                form.id = regra.id || null
                form.nome = regra.nome || ''
                form.modelo = regra.modelo || null
                form.exame_tipo_ids = [...(regra.exame_tipo_ids || [])]
                form.unidade = regra.unidade || 'anos'
                form.valor = Number(regra.valor || 1)
                ;(regra.condicoes || []).forEach((c) => {
                    if (c.tipo === 'idade_minima') form.idadeMinima = { ativo: true, valor: Number(c.valor) }
                    if (c.tipo === 'idade_maxima') form.idadeMaxima = { ativo: true, valor: Number(c.valor) }
                    if (c.tipo === 'cargo') form.cargo = { ativo: true, valor: (c.valor || []).map(Number) }
                    if (c.tipo === 'exposto_risco') form.expostoRisco = { ativo: true }
                    if (c.tipo === 'risco') form.risco = { ativo: true, valor: (c.valor || []).map(Number) }
                })
            } else {
                form.exame_tipo_ids = (this.opcoes.tipos_exame || []).map((t) => t.id)
            }

            this.form = form
            this.preset = form.unidade === 'dias' ? 'd' : `${form.valor}a`
            if (!['1a', '2a', 'd'].includes(this.preset)) this.preset = '1a'
            this.aplicacao = { modo: 'futuro', data: '' }
            this.previa = null
            this.$refs.modal && this.$refs.modal.abrirModal()
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        aplicarPreset() {
            if (this.preset === 'd') {
                this.form.unidade = 'dias'
                this.form.valor = 180
                return
            }
            this.form.unidade = 'anos'
            this.form.valor = Number(this.preset.replace('a', ''))
        },

        nomeCargo(id) {
            const cargo = (this.opcoes.cargos || []).find((c) => c.value === id)
            return cargo ? cargo.label : `#${id}`
        },

        adicionarCargo(opcao) {
            const id = Number(opcao && (opcao.value ?? opcao))
            if (id && !this.form.cargo.valor.includes(id)) {
                this.form.cargo.valor.push(id)
                this.previa = null
            }
        },

        removerCargo(id) {
            this.form.cargo.valor = this.form.cargo.valor.filter((c) => c !== id)
            this.previa = null
        },

        nomeRisco(id) {
            const risco = (this.opcoes.riscos || []).find((r) => r.value === id)
            return risco ? risco.label : `#${id}`
        },

        adicionarRisco(opcao) {
            const id = Number(opcao && (opcao.value ?? opcao))
            if (id && !this.form.risco.valor.includes(id)) {
                this.form.risco.valor.push(id)
                this.previa = null
            }
        },

        removerRisco(id) {
            this.form.risco.valor = this.form.risco.valor.filter((r) => r !== id)
            this.previa = null
        },

        payload() {
            const condicoes = []
            if (this.form.idadeMinima.ativo) condicoes.push({ tipo: 'idade_minima', valor: this.form.idadeMinima.valor })
            if (this.form.idadeMaxima.ativo) condicoes.push({ tipo: 'idade_maxima', valor: this.form.idadeMaxima.valor })
            if (this.form.cargo.ativo) condicoes.push({ tipo: 'cargo', valor: this.form.cargo.valor })
            if (this.form.expostoRisco.ativo) condicoes.push({ tipo: 'exposto_risco', valor: true })
            if (this.form.risco.ativo) condicoes.push({ tipo: 'risco', valor: this.form.risco.valor })

            return {
                regra_id: this.form.id,
                nome: this.form.nome,
                modelo: this.form.modelo,
                exame_tipo_ids: this.form.exame_tipo_ids,
                condicoes,
                unidade: this.form.unidade,
                valor: this.form.valor,
                modo_aplicacao: this.aplicacao.modo,
                data_aplicacao: this.aplicacao.data || null
            }
        },

        validarLocal() {
            const dados = this.payload()
            if (!dados.condicoes.length) {
                mostraErro('', 'Marque ao menos uma condição (idade, cargo ou risco).')
                return false
            }
            if (this.form.cargo.ativo && !this.form.cargo.valor.length) {
                mostraErro('', 'Escolha ao menos um cargo ou desmarque a condição de cargo.')
                return false
            }
            if (this.form.risco.ativo && !this.form.risco.valor.length) {
                mostraErro('', 'Escolha ao menos um risco ou desmarque a condição de riscos.')
                return false
            }
            if (!dados.exame_tipo_ids.length) {
                mostraErro('', 'Escolha ao menos um tipo de exame.')
                return false
            }
            return true
        },

        async calcularPrevia() {
            if (!this.validarLocal()) return
            this.carregandoPrevia = true
            try {
                const { data } = await axios.post(API.previa(), this.payload())
                this.previa = data
            } catch (e) {
                this.previa = null
            } finally {
                this.carregandoPrevia = false
            }
        },

        async salvar() {
            if (!this.validarLocal()) return
            if (this.aplicacao.modo === 'a_partir_de' && !this.aplicacao.data) {
                mostraErro('', 'Informe a data de aplicação.')
                return
            }

            this.salvando = true
            try {
                const dados = this.payload()
                const { data } = this.form.id
                    ? await axios.put(API.atualizar(this.form.id), dados)
                    : await axios.post(API.criar(), dados)
                mostraSucesso('', data.mensagem || 'Regra salva.')
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
