<template>
    <div class="wf-fluxo" :class="{ 'is-readonly': somenteLeitura }">
        <div class="wf-legenda">
            <span><i class="fas fa-grip-vertical"></i> Arraste para reordenar</span>
            <span><i class="fas fa-plus-circle"></i> Clique no <b>+</b> entre etapas para inserir</span>
            <span><i class="fas fa-link"></i> Passe o mouse para ver pré-requisitos</span>
        </div>

        <div class="wf-trilha" @dragend="limparArraste">
            <div class="wf-no wf-no--marco">
                <i class="fas fa-user-check"></i>
                <span>Candidato<br />selecionado</span>
            </div>

            <template v-for="(etapa, i) in pipeline" :key="etapa.id">
                <div
                    class="wf-ligacao"
                    :class="{ 'is-alvo': arrastandoId && indiceSobre === i }"
                    @dragover.prevent="indiceSobre = i"
                    @drop.prevent="soltar(i)"
                >
                    <i class="fas fa-long-arrow-alt-right wf-ligacao__seta"></i>
                    <button
                        type="button"
                        class="wf-ligacao__add"
                        title="Inserir etapa aqui"
                        :disabled="somenteLeitura"
                        @click="$emit('nova', { ordem: ordemEntre(i), tipo: 'avanco' })"
                    >
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <div
                    class="wf-no"
                    :class="classesNo(etapa)"
                    :draggable="!somenteLeitura"
                    @dragstart="iniciarArraste($event, etapa)"
                    @dragover.prevent="sobreNo($event, i)"
                    @drop.prevent="soltar(indiceSobre)"
                    @mouseenter="hoverId = etapa.id"
                    @mouseleave="hoverId = null"
                >
                    <div class="wf-no__topo">
                        <span class="wf-no__ordem">{{ i + 1 }}</span>
                        <i class="fas fa-grip-vertical wf-no__grip" v-if="!somenteLeitura"></i>
                        <i
                            class="wf-no__tipo"
                            :class="etapa.tipo === 'sistema' ? 'fas fa-cogs' : 'fas fa-flag'"
                            :title="etapa.tipo === 'sistema' ? 'Etapa de sistema (concluída por módulo)' : 'Etapa manual'"
                        ></i>
                    </div>

                    <strong class="wf-no__nome" @click="$emit('editar', etapa)">{{ etapa.nome }}</strong>

                    <div class="wf-no__tags">
                        <span class="wf-tag wf-tag--apto" v-if="etapa.is_apto_admissao">Apto admissão</span>
                        <span class="wf-tag wf-tag--sis" v-if="etapa.tipo === 'sistema'">Sistema</span>
                        <span class="wf-tag wf-tag--off" v-if="!etapa.ativo">Inativa</span>
                    </div>

                    <div class="wf-no__dep" v-if="etapa.depende_de_id">
                        <i class="fas fa-link"></i> requer <b>{{ nomeEtapa(etapa.depende_de_id) }}</b>
                    </div>

                    <div class="wf-no__acoes">
                        <button
                            type="button"
                            class="wf-chip"
                            :class="{ 'is-on': etapa.obrigatoria }"
                            :disabled="somenteLeitura"
                            :title="etapa.obrigatoria ? 'Obrigatória — clique para tornar opcional' : 'Opcional — clique para tornar obrigatória'"
                            @click="$emit('alternar', etapa, 'obrigatoria')"
                        >
                            <i class="fas fa-star"></i>
                        </button>
                        <button
                            type="button"
                            class="wf-chip"
                            :class="{ 'is-on': etapa.ativo }"
                            :disabled="somenteLeitura"
                            :title="etapa.ativo ? 'Ativa — clique para desativar' : 'Inativa — clique para ativar'"
                            @click="$emit('alternar', etapa, 'ativo')"
                        >
                            <i class="fas fa-power-off"></i>
                        </button>
                        <button
                            type="button"
                            class="wf-chip"
                            :disabled="somenteLeitura"
                            title="Editar detalhes"
                            @click="$emit('editar', etapa)"
                        >
                            <i class="fas fa-pen"></i>
                        </button>
                    </div>
                </div>
            </template>

            <div
                class="wf-ligacao"
                :class="{ 'is-alvo': arrastandoId && indiceSobre === pipeline.length }"
                @dragover.prevent="indiceSobre = pipeline.length"
                @drop.prevent="soltar(pipeline.length)"
            >
                <i class="fas fa-long-arrow-alt-right wf-ligacao__seta"></i>
                <button
                    type="button"
                    class="wf-ligacao__add"
                    title="Adicionar etapa ao final"
                    :disabled="somenteLeitura"
                    @click="$emit('nova', { ordem: ordemEntre(pipeline.length), tipo: 'avanco' })"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <div class="wf-no wf-no--marco wf-no--fim">
                <i class="fas fa-flag-checkered"></i>
                <span>Admissão</span>
            </div>
        </div>

        <div class="wf-saida" v-if="desclassificacoes.length || !somenteLeitura">
            <div class="wf-saida__titulo">
                <i class="fas fa-sign-out-alt"></i>
                <div>
                    <strong>Saídas do funil</strong>
                    <small>Motivos de desclassificação disponíveis em qualquer etapa</small>
                </div>
            </div>
            <div class="wf-saida__lista">
                <button
                    type="button"
                    class="wf-saida__item"
                    :class="{ 'is-off': !etapa.ativo }"
                    v-for="etapa in desclassificacoes"
                    :key="etapa.id"
                    @click="$emit('editar', etapa)"
                >
                    <i class="fas fa-times-circle"></i> {{ etapa.nome }}
                </button>
                <button
                    type="button"
                    class="wf-saida__novo"
                    v-if="!somenteLeitura"
                    @click="$emit('nova', { ordem: proximaOrdemDesclassificacao, tipo: 'desclassificacao' })"
                >
                    <i class="fas fa-plus"></i> Novo motivo
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'WorkflowFluxo',
    props: {
        etapas: { type: Array, default: () => [] },
        somenteLeitura: { type: Boolean, default: false }
    },
    emits: ['editar', 'nova', 'alternar', 'reordenar'],
    data() {
        return {
            arrastandoId: null,
            indiceSobre: null,
            hoverId: null
        }
    },
    computed: {
        ordenadas() {
            return [...this.etapas].sort((a, b) => a.ordem - b.ordem || a.id - b.id)
        },
        pipeline() {
            return this.ordenadas.filter((e) => e.tipo !== 'desclassificacao')
        },
        desclassificacoes() {
            return this.ordenadas.filter((e) => e.tipo === 'desclassificacao')
        },
        proximaOrdemDesclassificacao() {
            const max = this.desclassificacoes.reduce((m, e) => Math.max(m, e.ordem), 0)
            return max + 10
        },
        hoverEtapa() {
            return this.hoverId ? this.etapas.find((e) => e.id === this.hoverId) : null
        }
    },
    methods: {
        nomeEtapa(id) {
            const e = this.etapas.find((x) => x.id === id)
            return e ? e.nome : '—'
        },
        classesNo(etapa) {
            const h = this.hoverEtapa
            return {
                'wf-no--sistema': etapa.tipo === 'sistema',
                'is-inativa': !etapa.ativo,
                'is-obrigatoria': etapa.obrigatoria,
                'is-apto': etapa.is_apto_admissao,
                'is-arrastando': this.arrastandoId === etapa.id,
                'is-prerequisito': !!h && h.depende_de_id === etapa.id,
                'is-dependente': !!h && etapa.depende_de_id === h.id
            }
        },
        ordemEntre(indice) {
            const anterior = this.pipeline[indice - 1]
            const proxima = this.pipeline[indice]
            const a = anterior ? anterior.ordem : 0
            const b = proxima ? proxima.ordem : a + 20
            return b - a >= 2 ? Math.floor((a + b) / 2) : a + 1
        },
        iniciarArraste(ev, etapa) {
            if (this.somenteLeitura) {
                ev.preventDefault()
                return
            }
            this.arrastandoId = etapa.id
            ev.dataTransfer.effectAllowed = 'move'
            ev.dataTransfer.setData('text/plain', String(etapa.id))
        },
        sobreNo(ev, i) {
            if (!this.arrastandoId) return
            const r = ev.currentTarget.getBoundingClientRect()
            this.indiceSobre = ev.clientX > r.left + r.width / 2 ? i + 1 : i
        },
        soltar(destino) {
            const id = this.arrastandoId
            this.limparArraste()
            if (!id || destino === null || destino === undefined) return
            const ids = this.pipeline.map((e) => e.id)
            const de = ids.indexOf(id)
            if (de < 0) return
            ids.splice(de, 1)
            ids.splice(destino > de ? destino - 1 : destino, 0, id)
            if (ids.join() === this.pipeline.map((e) => e.id).join()) return
            // desclassificações mantêm a ordem após o funil
            const resto = this.desclassificacoes.map((e) => e.id)
            this.$emit('reordenar', ids.concat(resto))
        },
        limparArraste() {
            this.arrastandoId = null
            this.indiceSobre = null
        }
    }
}
</script>

<style scoped>
.wf-fluxo {
    margin-top: 0.5rem;
}
.wf-legenda {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem 1.25rem;
    font-size: 0.78rem;
    color: #64748b;
    margin-bottom: 0.75rem;
}
.wf-legenda i {
    margin-right: 0.25rem;
    color: #94a3b8;
}
.wf-trilha {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    row-gap: 1rem;
    padding: 1.1rem 1rem;
    border: 1px solid #dbe3ef;
    border-radius: 0.6rem;
    background: radial-gradient(#d9e1ee 1px, transparent 1px) 0 0 / 16px 16px, #f8fafc;
}
.wf-no {
    position: relative;
    width: 170px;
    padding: 0.6rem 0.7rem 0.5rem;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-left: 4px solid #4c6ef5;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    transition: box-shadow 0.15s, transform 0.15s, opacity 0.15s;
}
.wf-fluxo:not(.is-readonly) .wf-no[draggable='true'] {
    cursor: grab;
}
.wf-no:hover {
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
}
.wf-no--sistema {
    border-left-color: #0ca678;
}
.wf-no.is-obrigatoria {
    border-top: 2px solid #f59f00;
}
.wf-no.is-apto {
    background: #f0fdf4;
    border-color: #86efac;
}
.wf-no.is-inativa {
    opacity: 0.55;
    border-style: dashed;
}
.wf-no.is-arrastando {
    opacity: 0.35;
    transform: scale(0.97);
}
.wf-no.is-prerequisito {
    outline: 3px solid #fab005;
    outline-offset: 2px;
}
.wf-no.is-dependente {
    outline: 3px solid #4c6ef5;
    outline-offset: 2px;
}
.wf-no__topo {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.wf-no__ordem {
    width: 1.35rem;
    height: 1.35rem;
    border-radius: 999px;
    background: #eef2ff;
    color: #3b5bdb;
    font-size: 0.72rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.wf-no--sistema .wf-no__ordem {
    background: #e6fcf5;
    color: #087f5b;
}
.wf-no__grip {
    color: #cbd5e1;
    font-size: 0.8rem;
}
.wf-no__tipo {
    margin-left: auto;
    color: #94a3b8;
    font-size: 0.8rem;
}
.wf-no__nome {
    font-size: 0.86rem;
    line-height: 1.2;
    cursor: pointer;
    min-height: 2.05em;
}
.wf-no__nome:hover {
    text-decoration: underline;
}
.wf-no__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    min-height: 0.2rem;
}
.wf-tag {
    font-size: 0.64rem;
    font-weight: 600;
    padding: 0.05rem 0.4rem;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
}
.wf-tag--apto {
    background: #dcfce7;
    color: #166534;
}
.wf-tag--sis {
    background: #e6fcf5;
    color: #087f5b;
}
.wf-tag--off {
    background: #e2e8f0;
    color: #64748b;
}
.wf-no__dep {
    font-size: 0.7rem;
    color: #92400e;
    background: #fffbeb;
    border-radius: 0.3rem;
    padding: 0.1rem 0.35rem;
}
.wf-no__acoes {
    display: flex;
    gap: 0.25rem;
    margin-top: auto;
    padding-top: 0.25rem;
    border-top: 1px solid #f1f5f9;
}
.wf-chip {
    flex: 1;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #94a3b8;
    border-radius: 0.3rem;
    padding: 0.15rem 0;
    font-size: 0.72rem;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.wf-chip:hover:not(:disabled) {
    background: #eef2ff;
    color: #3b5bdb;
}
.wf-chip.is-on {
    color: #f59f00;
    background: #fff9db;
    border-color: #ffe066;
}
.wf-chip[title^='Ativa'].is-on {
    color: #0ca678;
    background: #e6fcf5;
    border-color: #96f2d7;
}
.wf-chip:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
.wf-no--marco {
    width: 96px;
    align-items: center;
    justify-content: center;
    text-align: center;
    border-left-width: 1px;
    border-radius: 999px 999px 999px 999px;
    background: #1e293b;
    color: #fff;
    border-color: #1e293b;
    font-size: 0.74rem;
    padding: 0.6rem 0.4rem;
    gap: 0.3rem;
}
.wf-no--marco i {
    font-size: 1.1rem;
}
.wf-no--fim {
    background: #0ca678;
    border-color: #0ca678;
}
.wf-ligacao {
    position: relative;
    width: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s, width 0.15s;
    border-radius: 0.4rem;
}
.wf-ligacao__seta {
    color: #94a3b8;
    font-size: 1.1rem;
}
.wf-ligacao__add {
    position: absolute;
    width: 22px;
    height: 22px;
    border-radius: 999px;
    border: 0;
    background: #4c6ef5;
    color: #fff;
    font-size: 0.65rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.7);
    transition: opacity 0.15s, transform 0.15s;
    cursor: pointer;
}
.wf-ligacao:hover .wf-ligacao__add:not(:disabled),
.wf-ligacao__add:focus {
    opacity: 1;
    transform: scale(1);
}
.wf-ligacao.is-alvo {
    background: #dbe4ff;
    width: 64px;
    box-shadow: inset 0 0 0 2px #4c6ef5;
}
.wf-saida {
    margin-top: 1rem;
    padding: 0.8rem 1rem;
    border: 1px solid #ffc9c9;
    border-radius: 0.6rem;
    background: #fff5f5;
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 1.25rem;
    align-items: center;
}
.wf-saida__titulo {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    color: #c92a2a;
}
.wf-saida__titulo small {
    display: block;
    color: #868e96;
    font-size: 0.72rem;
}
.wf-saida__lista {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}
.wf-saida__item,
.wf-saida__novo {
    border: 1px solid #ffa8a8;
    background: #fff;
    color: #c92a2a;
    border-radius: 999px;
    padding: 0.2rem 0.7rem;
    font-size: 0.78rem;
    cursor: pointer;
}
.wf-saida__item:hover {
    background: #ffe3e3;
}
.wf-saida__item.is-off {
    opacity: 0.5;
    border-style: dashed;
}
.wf-saida__novo {
    border-style: dashed;
    background: transparent;
}
@media (max-width: 767.98px) {
    .wf-no {
        width: 100%;
    }
    .wf-trilha {
        flex-direction: column;
        align-items: stretch;
    }
    .wf-ligacao {
        width: 100%;
        height: 32px;
    }
    .wf-ligacao__seta {
        transform: rotate(90deg);
    }
    .wf-no--marco {
        width: 100%;
        border-radius: 0.5rem;
    }
}
</style>
