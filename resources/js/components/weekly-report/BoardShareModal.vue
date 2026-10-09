<template>
    <div v-if="open" class="wr-share-backdrop" @click.self="$emit('close')">
        <div class="wr-share-modal" role="dialog" aria-modal="true" aria-labelledby="wr-share-title">
            <header class="wr-share-modal__header">
                <h5 id="wr-share-title" class="mb-0">
                    <i class="fas fa-user-plus mr-2 text-primary"></i>
                    Compartilhar quadro
                </h5>
                <button type="button" class="btn btn-sm btn-link text-muted" @click="$emit('close')" aria-label="Fechar">
                    <i class="fas fa-times"></i>
                </button>
            </header>

            <div class="wr-share-modal__body">
                <p class="text-muted small mb-3">
                    Pessoas adicionadas passam a ver e editar este quadro.
                </p>

                <div v-if="souDono" class="form-group wr-share-modal__busca">
                    <label class="small font-weight-bold">Convidar pessoa</label>
                    <autocomplete
                        v-model="buscaLabel"
                        :caminho="caminhoBusca"
                        placeholder="Buscar por nome..."
                        formsm
                        :rows="8"
                        @onselect="onSelectConvidar"
                    />
                </div>
                <div v-else class="alert alert-light border small">
                    Apenas o dono do quadro pode convidar ou remover membros.
                </div>

                <div v-if="loading" class="text-center text-muted py-3">
                    <i class="fa fa-spinner fa-pulse"></i> Carregando...
                </div>

                <div v-else class="wr-share-members">
                    <div class="wr-share-members__head">
                        <span class="wr-share-members__title">Membros</span>
                        <span class="wr-share-members__count">{{ membros.length }}</span>
                    </div>
                    <ul class="wr-share-list">
                        <li v-for="m in membros" :key="m.id" class="wr-share-list__item">
                            <span
                                class="wr-share-avatar wr-tip"
                                :data-tip="m.nome"
                                :aria-label="m.nome"
                                tabindex="0"
                            >{{ inicialNome(m.nome) }}</span>
                            <span class="wr-share-list__name" :title="m.nome">{{ m.nome }}</span>
                            <span
                                v-if="m.papel === 'dono'"
                                class="wr-share-list__badge"
                            >Dono</span>
                            <button
                                v-else-if="souDono"
                                type="button"
                                class="wr-share-list__remove"
                                :disabled="busyId === m.id"
                                title="Remover do quadro"
                                aria-label="Remover do quadro"
                                @click="remover(m)"
                            >
                                <i v-if="busyId === m.id" class="fa fa-spinner fa-pulse"></i>
                                <i v-else class="fas fa-times"></i>
                            </button>
                        </li>
                        <li v-if="!membros.length" class="wr-share-list__empty">
                            Nenhum membro neste quadro.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { inicialNome, quadroMembrosBuscarUrl, quadroMembrosUrl, toastErro, toastOk } from './api'

export default {
    name: 'BoardShareModal',
    props: {
        open: { type: Boolean, default: false },
        empresaId: { type: [Number, String], required: true },
        quadroId: { type: [Number, String], required: true },
        souDono: { type: Boolean, default: false }
    },
    emits: ['close', 'changed'],
    data() {
        return {
            loading: false,
            membros: [],
            buscaLabel: '',
            busyId: null
        }
    },
    computed: {
        caminhoBusca() {
            return quadroMembrosBuscarUrl(this.empresaId, this.quadroId, false)
        }
    },
    watch: {
        open: {
            immediate: true,
            handler(v) {
                if (v) this.carregar()
            }
        }
    },
    methods: {
        inicialNome,
        async carregar() {
            this.loading = true
            try {
                const { data } = await axios.get(quadroMembrosUrl(this.empresaId, this.quadroId))
                this.membros = data.membros || []
            } catch (e) {
                toastErro(e?.response?.data?.msg || 'Erro ao carregar membros')
            } finally {
                this.loading = false
            }
        },
        async onSelectConvidar(item) {
            if (!item || !item.id) return
            this.buscaLabel = ''
            try {
                const { data } = await axios.post(quadroMembrosUrl(this.empresaId, this.quadroId), {
                    user_id: item.id
                })
                if (data.membro && !this.membros.find((m) => m.id === data.membro.id)) {
                    this.membros.push(data.membro)
                }
                toastOk(data.msg || 'Membro adicionado')
                this.$emit('changed')
            } catch (e) {
                toastErro(e?.response?.data?.msg || 'Erro ao adicionar membro')
            }
        },
        async remover(membro) {
            if (!membro?.id) return
            this.busyId = membro.id
            try {
                await axios.delete(`${quadroMembrosUrl(this.empresaId, this.quadroId)}/${membro.id}`)
                this.membros = this.membros.filter((m) => m.id !== membro.id)
                toastOk('Membro removido')
                this.$emit('changed')
            } catch (e) {
                toastErro(e?.response?.data?.msg || 'Erro ao remover membro')
            } finally {
                this.busyId = null
            }
        }
    }
}
</script>

<style scoped>
.wr-share-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(7, 35, 51, 0.45);
    z-index: 1050;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 3rem 1rem 1.5rem;
    overflow-y: auto;
}
.wr-share-modal {
    width: 100%;
    max-width: 420px;
    max-height: calc(100vh - 4.5rem);
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 18px 48px rgba(3, 20, 30, 0.35);
    overflow: visible;
}
.wr-share-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #eef1f4;
    border-radius: 0.75rem 0.75rem 0 0;
    background: #fff;
}
.wr-share-modal__body {
    position: relative;
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    padding: 1rem;
    overflow: visible;
    border-radius: 0 0 0.75rem 0.75rem;
    background: #fff;
}
.wr-share-modal__busca {
    position: relative;
    z-index: 30;
    flex-shrink: 0;
    margin-bottom: 0.75rem;
}
.wr-share-modal__busca :deep(.autocomplete) {
    position: relative;
    z-index: 30;
}
.wr-share-modal__busca :deep(.autocomplete-results) {
    max-height: min(200px, 32vh) !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    border-radius: 0 0 6px 6px;
    box-shadow: 0 8px 20px rgba(3, 20, 30, 0.18);
    z-index: 40 !important;
}
.wr-share-members {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    border: 1px solid #eef1f4;
    border-radius: 8px;
    overflow: hidden;
    background: #fafbfc;
}
.wr-share-members__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    padding: 0.45rem 0.7rem;
    border-bottom: 1px solid #eef1f4;
    background: #fff;
}
.wr-share-members__title {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6c757d;
}
.wr-share-members__count {
    min-width: 1.35rem;
    height: 1.35rem;
    padding: 0 0.35rem;
    border-radius: 999px;
    background: #e8eef1;
    color: #174257;
    font-size: 0.7rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.wr-share-list {
    list-style: none;
    margin: 0;
    padding: 0.25rem;
    position: relative;
    z-index: 1;
    flex: 1 1 auto;
    min-height: 120px;
    max-height: min(280px, 40vh);
    overflow-y: auto;
    overflow-x: hidden;
}
.wr-share-list__item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 36px;
    padding: 0.3rem 0.45rem;
    border-radius: 6px;
    transition: background 0.12s ease;
}
.wr-share-list__item:hover {
    background: #fff;
}
.wr-share-list__name {
    flex: 1 1 auto;
    min-width: 0;
    font-size: 0.82rem;
    font-weight: 600;
    color: #174257;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.wr-share-list__badge {
    flex-shrink: 0;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #174257;
    background: #e3eef3;
    border-radius: 4px;
    padding: 0.15rem 0.4rem;
}
.wr-share-list__remove {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border: 0;
    border-radius: 6px;
    background: transparent;
    color: #a0a8b0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
}
.wr-share-list__remove:hover:not(:disabled) {
    background: #fdeceb;
    color: #c9372c;
}
.wr-share-list__remove:disabled {
    opacity: 0.6;
    cursor: wait;
}
.wr-share-list__empty {
    padding: 1rem 0.75rem;
    text-align: center;
    color: #8a97a0;
    font-size: 0.8rem;
}
.wr-share-avatar {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    background: #0f4c60;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
</style>
