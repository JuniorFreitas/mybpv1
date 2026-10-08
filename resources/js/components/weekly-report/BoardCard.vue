<template>
    <article
        class="wr-board-tile"
        :class="{ 'wr-board-tile--editing': editing }"
        :style="{ background: tileBg }"
        role="button"
        tabindex="0"
        :aria-label="'Abrir quadro ' + quadro.titulo"
        @click="onTileClick"
        @keydown.enter.prevent="!editing && $emit('open', quadro)"
        @keydown.space.prevent="!editing && $emit('open', quadro)"
    >
        <div class="wr-board-tile__fade" aria-hidden="true"></div>

        <div class="wr-board-tile__top">
            <form v-if="editing" class="wr-board-tile__rename" @click.stop @submit.prevent="salvarRename">
                <input
                    ref="tituloInput"
                    v-model="tituloLocal"
                    class="form-control form-control-sm wr-board-tile__rename-input"
                    type="text"
                    maxlength="120"
                    :disabled="saving"
                    @keydown.esc.prevent="cancelarRename"
                    @blur="onBlurRename"
                />
            </form>
            <h3 v-else class="wr-board-tile__title">{{ quadro.titulo }}</h3>

            <div v-if="(canUpdate || canDelete) && !editing" class="wr-board-tile__menu" @click.stop>
                <button
                    v-if="canUpdate"
                    type="button"
                    class="wr-board-tile__menu-btn"
                    title="Renomear"
                    aria-label="Renomear quadro"
                    @click="iniciarRename"
                >
                    <i class="fas fa-pen"></i>
                </button>
                <button
                    v-if="canDelete"
                    type="button"
                    class="wr-board-tile__menu-btn wr-board-tile__menu-btn--danger"
                    title="Excluir"
                    aria-label="Excluir quadro"
                    @click="$emit('delete', quadro)"
                >
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>

        <div v-if="!editing" class="wr-board-tile__bottom">
            <span v-if="souDono" class="wr-board-tile__badge">Dono</span>
            <div class="wr-board-tile__members" @click.stop>
                <span
                    v-for="m in membrosPreview"
                    :key="m.id"
                    class="wr-board-tile__avatar wr-tip"
                    :data-tip="tipMembro(m)"
                    :aria-label="tipMembro(m)"
                    tabindex="0"
                >
                    {{ inicial(m.nome) }}
                </span>
                <span
                    v-if="membrosExtra > 0"
                    class="wr-board-tile__avatar wr-board-tile__avatar--more wr-tip"
                    :data-tip="'+' + membrosExtra + ' membro(s)'"
                    :aria-label="'+' + membrosExtra + ' membro(s)'"
                    tabindex="0"
                >
                    +{{ membrosExtra }}
                </span>
            </div>
        </div>
        <div v-else class="wr-board-tile__bottom wr-board-tile__bottom--edit" @click.stop>
            <button
                type="button"
                class="btn btn-sm btn-light"
                :disabled="saving || !String(tituloLocal || '').trim()"
                @mousedown.prevent="salvarRename"
            >
                <i v-if="saving" class="fa fa-spinner fa-pulse"></i>
                <template v-else>Salvar</template>
            </button>
            <button
                type="button"
                class="btn btn-sm btn-link text-white"
                :disabled="saving"
                @mousedown.prevent="cancelarRename"
            >
                Cancelar
            </button>
        </div>
    </article>
</template>

<script>
import { inicialNome } from './api'

const TILE_COLORS = [
    'linear-gradient(135deg, #0079bf 0%, #0c3953 100%)',
    'linear-gradient(135deg, #0f4c60 0%, #174257 100%)',
    'linear-gradient(135deg, #519839 0%, #216e4e 100%)',
    'linear-gradient(135deg, #d29034 0%, #a54800 100%)',
    'linear-gradient(135deg, #b04632 0%, #833414 100%)',
    'linear-gradient(135deg, #89609e 0%, #5e4db2 100%)',
    'linear-gradient(135deg, #cd5a91 0%, #943d73 100%)',
    'linear-gradient(135deg, #4bbf6b 0%, #216e4e 100%)',
    'linear-gradient(135deg, #00aecc 0%, #206a83 100%)',
    'linear-gradient(135deg, #838c91 0%, #44546f 100%)'
]

export default {
    name: 'BoardCard',
    props: {
        quadro: { type: Object, required: true },
        canUpdate: { type: Boolean, default: false },
        canDelete: { type: Boolean, default: false },
        saving: { type: Boolean, default: false }
    },
    emits: ['open', 'rename', 'delete'],
    data() {
        return {
            editing: false,
            tituloLocal: '',
            _ignoreBlur: false
        }
    },
    computed: {
        souDono() {
            return !!this.quadro?.sou_dono
        },
        tileBg() {
            const id = Number(this.quadro?.id) || 0
            return TILE_COLORS[id % TILE_COLORS.length]
        },
        membrosPreview() {
            const list = Array.isArray(this.quadro?.membros_preview) ? this.quadro.membros_preview : []
            return list.slice(0, 4)
        },
        membrosCount() {
            return Number(this.quadro?.membros_count ?? this.membrosPreview.length) || 0
        },
        membrosExtra() {
            return Math.max(0, this.membrosCount - this.membrosPreview.length)
        }
    },
    watch: {
        'quadro.titulo'(v) {
            if (!this.editing) this.tituloLocal = v || ''
        },
        saving(v, prev) {
            if (prev && !v && this.editing) {
                this.editing = false
            }
        }
    },
    methods: {
        inicial(nome) {
            return inicialNome(nome)
        },
        tipMembro(m) {
            if (!m?.nome) return 'Membro'
            return m.papel === 'dono' ? `${m.nome} (Dono)` : m.nome
        },
        onTileClick() {
            if (this.editing) return
            this.$emit('open', this.quadro)
        },
        iniciarRename() {
            this.tituloLocal = this.quadro.titulo || ''
            this.editing = true
            this.$nextTick(() => {
                const el = this.$refs.tituloInput
                if (el) {
                    el.focus()
                    el.select()
                }
            })
        },
        cancelarRename() {
            this._ignoreBlur = true
            this.editing = false
            this.tituloLocal = this.quadro.titulo || ''
            this.$nextTick(() => {
                this._ignoreBlur = false
            })
        },
        onBlurRename() {
            if (this._ignoreBlur || this.saving) return
            // Pequeno delay para permitir clique em Salvar
            setTimeout(() => {
                if (this.editing && !this.saving) this.cancelarRename()
            }, 150)
        },
        salvarRename() {
            const titulo = String(this.tituloLocal || '').replace(/\s+/g, ' ').trim()
            if (!titulo || titulo === this.quadro.titulo) {
                this.cancelarRename()
                return
            }
            this.tituloLocal = titulo
            this._ignoreBlur = true
            this.$emit('rename', { quadro: this.quadro, titulo })
            this.$nextTick(() => {
                this._ignoreBlur = false
            })
        }
    }
}
</script>
