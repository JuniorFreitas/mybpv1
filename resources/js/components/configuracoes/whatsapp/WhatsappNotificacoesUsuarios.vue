<template>
    <div>
        <fieldset>
            <legend>Gerenciar usuários</legend>
            <p class="text-muted small mb-3">
                Controle quem recebe notificações WhatsApp da empresa. O envio só ocorre se a empresa tiver WhatsApp
                habilitado, o módulo estiver ativo, o usuário aceitar receber e possuir telefone do tipo WhatsApp.
            </p>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="small mb-1">Buscar</label>
                    <input
                        v-model="filtros.busca"
                        class="form-control form-control-sm"
                        placeholder="Nome ou e-mail"
                        @keyup.enter="buscar"
                    >
                </div>
                <div class="col-md-3">
                    <label class="small mb-1">Telefone WhatsApp</label>
                    <select v-model="filtros.apto_whatsapp" class="form-control form-control-sm" @change="buscar">
                        <option value="">Todos</option>
                        <option value="sim">Com WhatsApp</option>
                        <option value="nao">Sem WhatsApp</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small mb-1">Recebe movimentação</label>
                    <select v-model="filtros.recebe_movimentacao" class="form-control form-control-sm" @change="buscar">
                        <option value="">Todos</option>
                        <option value="sim">Sim</option>
                        <option value="nao">Não</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary btn-sm btn-block" :disabled="loading" @click="buscar">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                </div>
            </div>

            <div class="alert alert-warning py-2" v-if="!whatsappLiberado">
                WhatsApp não está habilitado para esta empresa. As preferências podem ser configuradas, mas nenhum envio será realizado.
            </div>

            <preload v-if="loading" class="text-center" />

            <div v-else class="table-responsive">
                <table class="table table-sm table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Telefone</th>
                            <th class="text-center">WhatsApp</th>
                            <th
                                v-for="modulo in modulosVisiveis"
                                :key="modulo.modulo"
                                class="text-center"
                            >
                                {{ modulo.modulo }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="usuarios.length === 0">
                            <td :colspan="3 + modulosVisiveis.length" class="text-center text-muted">
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                        <tr v-for="usuario in usuarios" :key="usuario.id">
                            <td>
                                <strong>{{ usuario.nome }}</strong>
                                <div class="small text-muted">{{ usuario.login }}</div>
                            </td>
                            <td>
                                <span v-if="usuario.telefone.numero_mascarado">{{ usuario.telefone.numero_mascarado }}</span>
                                <span v-else class="text-muted">Não cadastrado</span>
                                <div class="small text-muted" v-if="usuario.telefone.tipo">
                                    Tipo: {{ usuario.telefone.tipo }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge"
                                    :class="badgeTelefone(usuario).classe"
                                    :title="badgeTelefone(usuario).titulo"
                                >
                                    {{ badgeTelefone(usuario).texto }}
                                </span>
                            </td>
                            <td
                                v-for="modulo in modulosVisiveis"
                                :key="`${usuario.id}-${modulo.modulo}`"
                                class="text-center"
                            >
                                <div class="custom-control custom-switch d-inline-block">
                                    <input
                                        :id="`usr-${usuario.id}-${slug(modulo.modulo)}`"
                                        type="checkbox"
                                        class="custom-control-input"
                                        :checked="preferenciaReceber(usuario, modulo.modulo)"
                                        :disabled="readonly || salvandoId === chaveSalvando(usuario.id, modulo.modulo) || !moduloHabilitado(modulo.modulo)"
                                        @change="alterarPreferencia(usuario, modulo.modulo, $event)"
                                    >
                                    <label
                                        class="custom-control-label"
                                        :for="`usr-${usuario.id}-${slug(modulo.modulo)}`"
                                    />
                                </div>
                                <div class="small text-muted" v-if="!podeEnviar(usuario, modulo.modulo)">
                                    {{ motivoBloqueio(usuario, modulo.modulo) }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2" v-if="meta.last_page > 1">
                <small class="text-muted">Total: {{ meta.total }}</small>
                <div>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary mr-1"
                        :disabled="meta.current_page <= 1 || loading"
                        @click="irParaPagina(meta.current_page - 1)"
                    >
                        Anterior
                    </button>
                    <span class="small mx-2">{{ meta.current_page }} / {{ meta.last_page }}</span>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        :disabled="meta.current_page >= meta.last_page || loading"
                        @click="irParaPagina(meta.current_page + 1)"
                    >
                        Próxima
                    </button>
                </div>
            </div>
        </fieldset>
    </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted, watch, toRef } from 'vue'
import axios from 'axios'

const BASE = '/g/configuracoes/whatsapp'

export default defineComponent({
    name: 'WhatsappNotificacoesUsuarios',

    props: {
        readonly: { type: Boolean, default: false },
        whatsappLiberadoProp: { type: Boolean, default: null },
        empresaId: { type: [Number, String], default: null },
    },

    setup(props) {
        const empresaId = toRef(props, 'empresaId')
        const loading = ref(false)
        const salvandoId = ref('')
        const whatsappLiberado = ref(false)
        const modulos = ref([])
        const usuarios = ref([])
        const filtros = ref({
            busca: '',
            apto_whatsapp: '',
            recebe_movimentacao: '',
            por_pagina: 25,
            page: 1,
        })
        const meta = ref({
            current_page: 1,
            last_page: 1,
            per_page: 25,
            total: 0,
        })

        const modulosVisiveis = computed(() => modulos.value.filter((modulo) => modulo.habilitado !== false))

        const empresaParams = () => (empresaId.value ? { empresa_id: Number(empresaId.value) } : {})

        const carregar = async () => {
            loading.value = true
            try {
                const { data } = await axios.get(`${BASE}/usuarios-notificacoes`, {
                    params: { ...filtros.value, ...empresaParams() },
                })
                whatsappLiberado.value = props.whatsappLiberadoProp ?? !!data.whatsapp_liberado
                modulos.value = data.modulos || []
                usuarios.value = data.data || []
                meta.value = data.meta || meta.value
            } catch (e) {
                usuarios.value = []
            } finally {
                loading.value = false
            }
        }

        const buscar = () => {
            filtros.value.page = 1
            carregar()
        }

        const irParaPagina = (page) => {
            filtros.value.page = page
            carregar()
        }

        const slug = (texto) => String(texto).toLowerCase().replace(/[^a-z0-9]+/g, '-')

        const preferenciaReceber = (usuario, modulo) => {
            return !!(usuario.preferencias?.[modulo]?.receber ?? true)
        }

        const moduloHabilitado = (modulo) => {
            const registro = modulos.value.find((item) => item.modulo === modulo)
            return registro ? !!registro.habilitado : true
        }

        const podeEnviar = (usuario, modulo) => {
            return !!(usuario.preferencias?.[modulo]?.apto_envio)
        }

        const motivoBloqueio = (usuario, modulo) => {
            if (!whatsappLiberado.value) return 'Empresa sem WhatsApp'
            if (!moduloHabilitado(modulo)) return 'Módulo desabilitado'
            if (!usuario.telefone?.tem_whatsapp) return 'Sem telefone WhatsApp'
            if (!preferenciaReceber(usuario, modulo)) return 'Usuário desativou'
            return ''
        }

        const badgeTelefone = (usuario) => {
            if (!whatsappLiberado.value) {
                return { classe: 'badge-secondary', texto: 'Empresa off', titulo: 'WhatsApp desabilitado na empresa' }
            }
            if (!usuario.telefone?.tem_telefone) {
                return { classe: 'badge-warning', texto: 'Sem telefone', titulo: 'Usuário sem telefone cadastrado' }
            }
            if (!usuario.telefone?.tem_whatsapp) {
                return { classe: 'badge-danger', texto: 'Não WhatsApp', titulo: 'Telefone não é do tipo WhatsApp' }
            }
            return {
                classe: 'badge-success',
                texto: usuario.telefone.whatsapp_principal ? 'WhatsApp principal' : 'WhatsApp',
                titulo: 'Telefone apto para envio',
            }
        }

        const chaveSalvando = (userId, modulo) => `${userId}:${modulo}`

        const alterarPreferencia = async (usuario, modulo, event) => {
            if (props.readonly) return

            const receber = event.target.checked
            const chave = chaveSalvando(usuario.id, modulo)
            salvandoId.value = chave

            try {
                const { data } = await axios.put(`${BASE}/usuarios-notificacoes/${usuario.id}`, {
                    modulo,
                    receber,
                }, { params: empresaParams() })
                const idx = usuarios.value.findIndex((item) => item.id === usuario.id)
                if (idx >= 0 && data.usuario) {
                    usuarios.value[idx] = data.usuario
                }
            } catch (e) {
                event.target.checked = !receber
                alert(e.response?.data?.message || 'Erro ao salvar preferência.')
            } finally {
                salvandoId.value = ''
            }
        }

        watch(
            () => props.whatsappLiberadoProp,
            (valor) => {
                if (valor !== null) {
                    whatsappLiberado.value = !!valor
                }
            },
        )

        watch(empresaId, () => {
            filtros.value.page = 1
            carregar()
        })

        onMounted(carregar)

        return {
            loading,
            salvandoId,
            whatsappLiberado,
            modulos,
            modulosVisiveis,
            usuarios,
            filtros,
            meta,
            buscar,
            irParaPagina,
            slug,
            preferenciaReceber,
            moduloHabilitado,
            podeEnviar,
            motivoBloqueio,
            badgeTelefone,
            chaveSalvando,
            alterarPreferencia,
        }
    },
})
</script>
