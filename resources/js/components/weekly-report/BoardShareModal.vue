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
                    Pessoas adicionadas passam a ver e editar este quadro (como no Trello).
                </p>

                <div v-if="souDono" class="form-group">
                    <label class="small font-weight-bold">Convidar pessoa</label>
                    <autocomplete
                        v-model="buscaLabel"
                        :caminho="caminhoBusca"
                        placeholder="Buscar por nome..."
                        @onblur="buscaLabel = ''"
                        @onselect="onSelectConvidar"
                    />
                </div>
                <div v-else class="alert alert-light border small">
                    Apenas o dono do quadro pode convidar ou remover membros.
                </div>

                <div v-if="loading" class="text-center text-muted py-3">
                    <i class="fa fa-spinner fa-pulse"></i> Carregando...
                </div>

                <ul v-else class="list-group wr-share-list">
                    <li v-for="m in membros" :key="m.id" class="list-group-item d-flex align-items-center">
                        <span
                            class="wr-share-avatar wr-tip mr-2"
                            :data-tip="m.nome"
                            :aria-label="m.nome"
                            tabindex="0"
                        >{{ inicialNome(m.nome) }}</span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="font-weight-bold text-truncate">{{ m.nome }}</div>
                            <small class="text-muted">{{ m.papel === 'dono' ? 'Dono' : 'Membro' }}</small>
                        </div>
                        <button
                            v-if="souDono && m.papel !== 'dono'"
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            :disabled="busyId === m.id"
                            title="Remover do quadro"
                            @click="remover(m)"
                        >
                            <i v-if="busyId === m.id" class="fa fa-spinner fa-pulse"></i>
                            <i v-else class="fas fa-times"></i>
                        </button>
                        <span v-else-if="m.papel === 'dono'" class="badge badge-primary">Dono</span>
                    </li>
                    <li v-if="!membros.length" class="list-group-item text-muted small text-center">
                        Nenhum membro neste quadro.
                    </li>
                </ul>
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
    padding: 4rem 1rem 1rem;
}
.wr-share-modal {
    width: 100%;
    max-width: 420px;
    background: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 18px 48px rgba(3, 20, 30, 0.35);
    overflow: hidden;
}
.wr-share-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #eef1f4;
}
.wr-share-modal__body {
    padding: 1rem;
}
.wr-share-avatar {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: #0f4c60;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.wr-share-list {
    max-height: 280px;
    overflow: auto;
}
</style>
