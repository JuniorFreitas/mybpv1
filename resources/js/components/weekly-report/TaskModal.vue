<template>
    <modal
        id="wrJanelaTarefa"
        :titulo="local?.titulo || 'Tarefa'"
        :size="95"
        topo
        :fechar="!busy"
        ref="modal"
        @abriu="onModalAbriu"
        @fechou="$emit('close')"
    >
        <template #topo>
            <div class="wr-modal__topo">
                <input
                    v-if="editingTitle"
                    ref="titulo"
                    v-model="local.titulo"
                    class="form-control wr-modal__title-input"
                    maxlength="200"
                    placeholder="Título do card"
                    @blur="saveTitulo"
                    @keydown.enter.prevent="saveTitulo"
                    @keydown.esc.prevent="editingTitle = false"
                />
                <button
                    v-else-if="local"
                    type="button"
                    class="wr-modal__title-btn"
                    :disabled="!canUpdate"
                    :title="canUpdate ? 'Clique para editar o título' : ''"
                    @click="canUpdate ? startEditTitle() : null"
                >
                    <h3 class="modal-title wr-modal__title mb-0">{{ local.titulo }}</h3>
                    <i v-if="canUpdate" class="fas fa-pen wr-modal__title-edit"></i>
                </button>
                <p class="wr-modal__lista mb-0">
                    <i class="fas fa-columns"></i>
                    na lista <span class="badge badge-soft">{{ lista?.titulo }}</span>
                </p>
            </div>
        </template>

        <template #conteudo>
            <div v-if="!local || loading" class="wr-modal__loading text-center py-5">
                <i class="fa fa-spinner fa-pulse fa-2x text-primary mb-2 d-block"></i>
                <span class="text-muted small">Carregando card...</span>
            </div>
            <div v-else class="row wr-modal">
                <div class="col-12 col-lg-8 wr-modal__main">
                    <section v-if="local.membros?.length" class="wr-modal__section">
                        <h5 class="wr-modal__section-title">
                            <i class="fas fa-users"></i> Membros
                        </h5>
                        <div class="wr-members">
                            <span v-for="m in local.membros" :key="m.id" class="wr-avatar" :title="m.nome">
                                {{ inicial(m.nome) }}
                                <button
                                    v-if="canUpdate"
                                    type="button"
                                    class="wr-avatar__remove"
                                    title="Remover membro"
                                    @click="removeMembro(m)"
                                >
                                    ×
                                </button>
                            </span>
                        </div>
                    </section>

                    <section class="wr-modal__section">
                        <h5 class="wr-modal__section-title">
                            <i class="fas fa-align-left"></i> Descrição
                        </h5>
                        <div v-if="canUpdate" class="wr-rich-editor">
                            <tiny-mce-editor
                                :key="'desc-' + local.id"
                                v-model="local.descricao"
                                preset="basico"
                                :init="tinyBasicoInit"
                                :mentions-url="membrosSearchUrl"
                                @update:modelValue="queueSaveDescricao"
                                @mention="onEditorMention"
                            />
                        </div>
                        <div
                            v-else
                            class="wr-modal__readonly wr-richtext"
                            v-html="local.descricao || 'Sem descrição'"
                        ></div>
                    </section>

                    <section class="wr-modal__section">
                        <div class="wr-modal__section-head">
                            <h5 class="wr-modal__section-title mb-0">
                                <i class="fas fa-check-square"></i> Checklists
                                <span v-if="(local.checklists || []).length" class="badge badge-soft ml-1">
                                    {{ local.checklists.length }}
                                </span>
                            </h5>
                            <button
                                v-if="canUpdate && !addingChecklist"
                                type="button"
                                class="btn btn-sm btn-outline-primary wr-btn-ghost"
                                @click="startAddChecklist"
                            >
                                <i class="fas fa-plus"></i> Checklist
                            </button>
                        </div>

                        <form v-if="canUpdate && addingChecklist" class="wr-inline-form mb-3" @submit.prevent="confirmAddChecklist">
                            <input
                                ref="novaChecklistInput"
                                v-model.trim="newChecklistTitulo"
                                class="form-control"
                                placeholder="Nome da checklist"
                                maxlength="120"
                                @keydown.esc.prevent="cancelAddChecklist"
                            />
                            <div class="wr-inline-form__actions">
                                <button class="btn btn-sm btn-primary" type="submit" :disabled="!newChecklistTitulo || busyChecklist">
                                    <i v-if="busyChecklist" class="fa fa-spinner fa-pulse"></i>
                                    <template v-else>Criar</template>
                                </button>
                                <button class="btn btn-sm btn-light" type="button" @click="cancelAddChecklist">Cancelar</button>
                            </div>
                        </form>

                        <div v-if="!(local.checklists || []).length && !addingChecklist" class="wr-empty">
                            <i class="fas fa-check-square"></i>
                            <p class="mb-0">Nenhuma checklist ainda. Organize subtarefas aqui.</p>
                        </div>

                        <div v-for="ck in local.checklists || []" :key="ck.id" class="wr-checklist">
                            <div class="wr-checklist__head">
                                <input
                                    v-if="canUpdate"
                                    v-model="ck.titulo"
                                    class="form-control form-control-sm wr-checklist__title"
                                    placeholder="Título da checklist"
                                    @blur="saveChecklist(ck)"
                                />
                                <strong v-else class="wr-checklist__title-text">{{ ck.titulo }}</strong>
                                <span class="wr-checklist__pct">{{ progressPct(ck) }}%</span>
                                <button
                                    v-if="canUpdate"
                                    type="button"
                                    class="btn btn-sm btn-link text-muted wr-icon-btn"
                                    :class="{ 'text-danger': ck.emAtraso }"
                                    :title="ck.datahora_entrega ? 'Alterar prazo da checklist' : 'Definir prazo da checklist'"
                                    @click="toggleDueEditor('ck', ck.id)"
                                >
                                    <i class="far fa-clock"></i>
                                </button>
                                <button
                                    v-if="canUpdate"
                                    type="button"
                                    class="btn btn-sm btn-outline-danger wr-checklist__delete"
                                    title="Excluir checklist"
                                    :disabled="deletingChecklistId === ck.id"
                                    @mousedown.prevent.stop="deleteChecklist(ck)"
                                >
                                    <i v-if="deletingChecklistId === ck.id" class="fa fa-spinner fa-pulse"></i>
                                    <template v-else>
                                        <i class="fas fa-trash"></i>
                                        <span class="wr-checklist__delete-label">Excluir</span>
                                    </template>
                                </button>
                            </div>
                            <div
                                v-if="ck.datahora_entrega || dueEditorKey === dueKey('ck', ck.id)"
                                class="wr-checklist__due"
                                :class="{ 'wr-checklist__due--late': ck.emAtraso }"
                            >
                                <template v-if="canUpdate && dueEditorKey === dueKey('ck', ck.id)">
                                    <datepicker
                                        :key="'ck-due-' + ck.id"
                                        v-model="ck.datahora_entrega"
                                        :hora="true"
                                        label=""
                                        formsm
                                        @onselect="(v) => onSelectChecklistDue(ck, v)"
                                    />
                                    <button
                                        v-if="ck.datahora_entrega"
                                        type="button"
                                        class="btn btn-sm btn-link text-danger"
                                        @click="clearChecklistDue(ck)"
                                    >
                                        Remover
                                    </button>
                                    <button type="button" class="btn btn-sm btn-link text-muted" @click="closeDueEditor">
                                        Fechar
                                    </button>
                                </template>
                                <template v-else>
                                    <button
                                        type="button"
                                        class="wr-due-chip"
                                        :class="{ 'wr-due-chip--late': ck.emAtraso }"
                                        :disabled="!canUpdate"
                                        @click="canUpdate && toggleDueEditor('ck', ck.id)"
                                    >
                                        <i class="far fa-clock"></i>
                                        {{ shortDue(ck.datahora_entrega) }}
                                    </button>
                                </template>
                            </div>
                            <div class="progress wr-checklist__progress">
                                <div class="progress-bar bg-primary" :style="{ width: progressPct(ck) + '%' }"></div>
                            </div>
                            <div v-for="item in ck.itens || []" :key="item.id" class="wr-checklist__item">
                                <div class="custom-control custom-checkbox">
                                    <input
                                        :id="'ckitem-' + item.id"
                                        type="checkbox"
                                        class="custom-control-input"
                                        :checked="!!item.concluido"
                                        :disabled="!canUpdate"
                                        @change="toggleItem(ck, item, $event.target.checked)"
                                    />
                                    <label class="custom-control-label" :for="'ckitem-' + item.id"></label>
                                </div>
                                <div class="wr-checklist__item-main">
                                    <input
                                        v-if="canUpdate"
                                        v-model="item.titulo"
                                        class="form-control form-control-sm wr-checklist__item-input"
                                        :class="{ 'texto-riscado': item.concluido }"
                                        @blur="saveItem(ck, item)"
                                        @keydown.enter.prevent="$event.target.blur()"
                                    />
                                    <span v-else class="wr-checklist__item-text" :class="{ 'texto-riscado': item.concluido }">
                                        {{ item.titulo }}
                                    </span>
                                    <div
                                        v-if="item.datahora_entrega || dueEditorKey === dueKey('item', item.id)"
                                        class="wr-checklist__item-due"
                                        :class="{ 'wr-checklist__item-due--late': item.emAtraso }"
                                    >
                                        <template v-if="canUpdate && dueEditorKey === dueKey('item', item.id)">
                                            <datepicker
                                                :key="'item-due-' + item.id"
                                                v-model="item.datahora_entrega"
                                                :hora="true"
                                                label=""
                                                formsm
                                                @onselect="(v) => onSelectItemDue(ck, item, v)"
                                            />
                                            <button
                                                v-if="item.datahora_entrega"
                                                type="button"
                                                class="btn btn-sm btn-link text-danger"
                                                @click="clearItemDue(ck, item)"
                                            >
                                                Remover
                                            </button>
                                            <button type="button" class="btn btn-sm btn-link text-muted" @click="closeDueEditor">
                                                Fechar
                                            </button>
                                        </template>
                                        <button
                                            v-else
                                            type="button"
                                            class="wr-due-chip wr-due-chip--sm"
                                            :class="{ 'wr-due-chip--late': item.emAtraso }"
                                            :disabled="!canUpdate"
                                            @click="canUpdate && toggleDueEditor('item', item.id)"
                                        >
                                            <i class="far fa-clock"></i>
                                            {{ shortDue(item.datahora_entrega) }}
                                        </button>
                                    </div>
                                    <div
                                        v-if="(item.membros || []).length || memberEditorItemId === item.id"
                                        class="wr-checklist__item-members"
                                    >
                                        <span
                                            v-for="m in item.membros || []"
                                            :key="m.id"
                                            class="wr-avatar wr-avatar--xs"
                                            :title="m.nome"
                                        >
                                            {{ inicial(m.nome) }}
                                            <button
                                                v-if="canUpdate"
                                                type="button"
                                                class="wr-avatar__remove"
                                                title="Remover membro"
                                                @click="removeItemMembro(ck, item, m)"
                                            >
                                                ×
                                            </button>
                                        </span>
                                        <div v-if="canUpdate && memberEditorItemId === item.id" class="wr-checklist__item-member-search">
                                            <autocomplete
                                                v-model="itemMembroBusca"
                                                :caminho="membrosUrl"
                                                placeholder="@ buscar membro"
                                                @onblur="itemMembroBusca = ''"
                                                @onselect="(u) => addItemMembro(ck, item, u)"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <button
                                    v-if="canUpdate"
                                    type="button"
                                    class="btn btn-sm btn-link text-muted wr-icon-btn"
                                    :class="{ 'text-primary': memberEditorItemId === item.id }"
                                    title="Membros do item"
                                    :disabled="item._pending"
                                    @click="toggleItemMemberEditor(item)"
                                >
                                    <i class="fas fa-user-plus"></i>
                                </button>
                                <button
                                    v-if="canUpdate"
                                    type="button"
                                    class="btn btn-sm btn-link text-muted wr-icon-btn"
                                    :class="{ 'text-danger': item.emAtraso }"
                                    :title="item.datahora_entrega ? 'Alterar prazo' : 'Definir prazo'"
                                    :disabled="item._pending"
                                    @click="toggleDueEditor('item', item.id)"
                                >
                                    <i class="far fa-clock"></i>
                                </button>
                                <button
                                    v-if="canUpdate"
                                    type="button"
                                    class="btn btn-sm btn-link text-muted wr-icon-btn"
                                    title="Remover item"
                                    :disabled="deletingItemId === item.id || item._pending"
                                    @mousedown.prevent.stop="deleteItem(ck, item)"
                                >
                                    <i
                                        v-if="deletingItemId === item.id"
                                        class="fa fa-spinner fa-pulse"
                                    ></i>
                                    <i v-else class="fas fa-times"></i>
                                </button>
                            </div>
                            <form v-if="canUpdate" class="wr-checklist__add" @submit.prevent="addItem(ck)">
                                <i class="fas fa-plus text-muted"></i>
                                <input
                                    :value="newItems[ck.id] || ''"
                                    class="form-control form-control-sm"
                                    placeholder="Adicionar item e pressionar Enter"
                                    :disabled="addingItemCkId === ck.id"
                                    @input="setNewItem(ck.id, $event.target.value)"
                                />
                                <button
                                    class="btn btn-sm btn-primary"
                                    type="submit"
                                    :disabled="!(newItems[ck.id] || '').trim() || addingItemCkId === ck.id"
                                >
                                    <i v-if="addingItemCkId === ck.id" class="fa fa-spinner fa-pulse"></i>
                                    <template v-else>Add</template>
                                </button>
                            </form>
                        </div>
                    </section>

                    <section class="wr-modal__section">
                        <h5 class="wr-modal__section-title">
                            <i class="fas fa-paperclip"></i> Anexos
                        </h5>
                        <div class="wr-anexos-box">
                            <upload
                                v-if="canUpdate && uploadUrl"
                                :model="local.anexos"
                                :url="uploadUrl"
                                :multi="true"
                                label="Anexar arquivo"
                                @onFinalizado="loadShow"
                            />
                            <ul class="list-unstyled wr-anexos mb-0">
                                <li v-for="anexo in local.anexos || []" :key="anexo.id" class="wr-anexos__item">
                                    <a :href="anexo.urlDownload || '#'" target="_blank" rel="noopener" class="wr-anexos__link">
                                        <i class="fas fa-file-alt"></i>
                                        <span>{{ anexo.nome }}{{ anexo.extensao }}</span>
                                    </a>
                                    <button
                                        v-if="canUpdate"
                                        type="button"
                                        class="btn btn-sm btn-link text-danger wr-icon-btn"
                                        title="Excluir anexo"
                                        @click="deleteAnexo(anexo)"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </li>
                                <li v-if="!(local.anexos || []).length" class="wr-empty wr-empty--sm">
                                    Nenhum anexo
                                </li>
                            </ul>
                        </div>
                    </section>

                    <section class="wr-modal__section wr-modal__section--last">
                        <div class="wr-feed-tabs" role="tablist">
                            <button
                                type="button"
                                class="wr-feed-tabs__btn"
                                role="tab"
                                :class="{ 'wr-feed-tabs__btn--active': feedTab === 'comentarios' }"
                                :aria-selected="feedTab === 'comentarios'"
                                @click="feedTab = 'comentarios'"
                            >
                                <i class="far fa-comment-dots"></i>
                                Comentários
                                <span v-if="(local.comentarios || []).length" class="badge badge-soft">
                                    {{ local.comentarios.length }}
                                </span>
                            </button>
                            <button
                                type="button"
                                class="wr-feed-tabs__btn"
                                role="tab"
                                :class="{ 'wr-feed-tabs__btn--active': feedTab === 'atividades' }"
                                :aria-selected="feedTab === 'atividades'"
                                @click="feedTab = 'atividades'"
                            >
                                <i class="fas fa-history"></i>
                                Atividade
                                <span v-if="logsTotal" class="badge badge-soft">
                                    {{ logsTotal }}
                                </span>
                            </button>
                        </div>

                        <div v-show="feedTab === 'comentarios'" class="wr-feed-tabs__panel" role="tabpanel">
                            <form v-if="canUpdate" class="wr-comment-form" @submit.prevent="addComentario">
                                <div class="wr-rich-editor wr-rich-editor--comment">
                                    <tiny-mce-editor
                                        ref="commentEditor"
                                        :key="'comment-' + local.id"
                                        v-model="novoComentario"
                                        preset="basico"
                                        :init="tinyComentarioInit"
                                        :mentions-url="membrosSearchUrl"
                                        @mention="onEditorMention"
                                    />
                                </div>
                                <div class="wr-comment-form__actions">
                                    <select v-model="novoComentarioTipo" class="form-control form-control-sm wr-comment-form__tipo">
                                        <option value="comentario">Comentário</option>
                                        <option value="bloqueio">Bloqueio</option>
                                        <option value="dependencia">Dependência externa</option>
                                    </select>
                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-primary"
                                        :disabled="!hasComentarioTexto || busyComentario"
                                    >
                                        <i v-if="busyComentario" class="fa fa-spinner fa-pulse"></i>
                                        <template v-else>Publicar</template>
                                    </button>
                                </div>
                            </form>

                            <ul class="wr-comments">
                                <li
                                    v-for="c in local.comentarios || []"
                                    :key="c.id"
                                    class="wr-comments__item"
                                    :class="{
                                        'wr-comments__item--bloqueio': c.tipo === 'bloqueio',
                                        'wr-comments__item--dependencia': c.tipo === 'dependencia'
                                    }"
                                >
                                    <div class="wr-comments__head">
                                        <span class="wr-avatar-badge" :title="c.usuario?.nome">
                                            {{ inicial(c.usuario?.nome) }}
                                        </span>
                                        <div class="wr-comments__meta">
                                            <strong>{{ c.usuario?.nome || 'Usuário' }}</strong>
                                            <span class="wr-comments__tipo" :class="'wr-comments__tipo--' + c.tipo">
                                                {{ c.tipo_label }}
                                            </span>
                                            <small class="text-muted">{{ c.created_at }}</small>
                                        </div>
                                        <button
                                            v-if="canManageComentario(c)"
                                            type="button"
                                            class="btn btn-sm btn-link text-muted wr-icon-btn"
                                            title="Excluir comentário"
                                            :disabled="deletingComentarioId === c.id"
                                            @click="deleteComentario(c)"
                                        >
                                            <i v-if="deletingComentarioId === c.id" class="fa fa-spinner fa-pulse"></i>
                                            <i v-else class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="wr-comments__body wr-richtext" v-html="c.comentario"></div>
                                </li>
                                <li v-if="!(local.comentarios || []).length" class="wr-empty wr-empty--sm">
                                    Sem comentários. Use para informar bloqueios ou dependências externas.
                                </li>
                            </ul>
                        </div>

                        <div v-show="feedTab === 'atividades'" class="wr-feed-tabs__panel" role="tabpanel">
                            <ul class="wr-logs">
                                <li v-for="log in local.logs || []" :key="log.id" class="wr-logs__item">
                                    <div class="wr-logs__dot"></div>
                                    <div>
                                        <strong>{{ log.usuario?.nome || 'Usuário' }}</strong>
                                        {{ log.descricao }}
                                        <small class="text-muted d-block">{{ log.created_at }}</small>
                                    </div>
                                </li>
                                <li v-if="!(local.logs || []).length" class="wr-empty wr-empty--sm">
                                    Sem atividades ainda.
                                </li>
                            </ul>
                            <div v-if="hasMoreLogs" class="wr-logs__more">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary btn-block"
                                    :disabled="busyLogs"
                                    @click="loadMoreLogs"
                                >
                                    <span v-if="busyLogs">
                                        <i class="fas fa-spinner fa-spin"></i> Carregando...
                                    </span>
                                    <span v-else>
                                        Carregar mais
                                        <small class="text-muted">
                                            ({{ (local.logs || []).length }}/{{ logsMeta.total }})
                                        </small>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="col-12 col-lg-4">
                    <div class="wr-sidebar">
                        <h6 class="wr-sidebar__title">Ações</h6>

                        <div v-if="canUpdate" class="wr-field">
                            <label class="wr-field__label">Membros</label>
                            <autocomplete
                                v-model="membroBusca"
                                :caminho="membrosUrl"
                                placeholder="Buscar e adicionar membro"
                                @onblur="membroBusca = ''"
                                @onselect="addMembro"
                            />
                        </div>

                        <div class="wr-field">
                            <label class="wr-field__label">
                                <i class="far fa-calendar-alt"></i> Início
                            </label>
                            <template v-if="canUpdate">
                                <div v-if="local.datahora_inicio" class="wr-field__date">
                                    <datepicker
                                        :key="'inicio-' + local.id"
                                        v-model="local.datahora_inicio"
                                        :hora="true"
                                        label=""
                                        formsm
                                        @onselect="onSelectInicio"
                                    />
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-link text-danger wr-field__remove"
                                        @click="setData('inicio', 'remove')"
                                    >
                                        Remover
                                    </button>
                                </div>
                                <button
                                    v-else
                                    type="button"
                                    class="btn btn-sm btn-light btn-block wr-field__add-btn"
                                    @click="definirDataInicio"
                                >
                                    <i class="far fa-calendar-plus"></i> Definir início
                                </button>
                            </template>
                            <div v-else class="wr-field__value">{{ local.datahora_inicio || '—' }}</div>
                        </div>

                        <div class="wr-field">
                            <label class="wr-field__label">
                                <i class="far fa-clock"></i> Entrega
                            </label>
                            <template v-if="canUpdate">
                                <div v-if="local.datahora_entrega" class="wr-field__date">
                                    <datepicker
                                        :key="'entrega-' + local.id"
                                        v-model="local.datahora_entrega"
                                        :hora="true"
                                        label=""
                                        formsm
                                        @onselect="onSelectEntrega"
                                    />
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-link text-danger wr-field__remove"
                                        @click="setData('entrega', 'remove')"
                                    >
                                        Remover
                                    </button>
                                </div>
                                <button
                                    v-else
                                    type="button"
                                    class="btn btn-sm btn-light btn-block wr-field__add-btn"
                                    @click="definirDataEntrega"
                                >
                                    <i class="far fa-calendar-plus"></i> Definir entrega
                                </button>
                            </template>
                            <div v-else class="wr-field__value">{{ local.datahora_entrega || '—' }}</div>
                        </div>

                        <div v-if="canUpdate && local.datahora_entrega" class="wr-field">
                            <label class="wr-field__label" for="wrLembrete">
                                <i class="far fa-bell"></i> Lembrete
                            </label>
                            <select
                                id="wrLembrete"
                                v-model="local.lembreteText"
                                class="form-control form-control-sm"
                                @change="saveLembrete"
                            >
                                <option :value="null">Sem lembrete</option>
                                <option value="5m">5 minutos antes</option>
                                <option value="10m">10 minutos antes</option>
                                <option value="15m">15 minutos antes</option>
                                <option value="1H">1 hora antes</option>
                                <option value="2H">2 horas antes</option>
                                <option value="1d">1 dia antes</option>
                                <option value="2d">2 dias antes</option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                Notifica os membros do card (alerta + e-mail) no horário escolhido.
                            </small>
                        </div>

                        <label class="wr-concluido" :class="{ 'wr-concluido--on': local.concluido }">
                            <input
                                id="wrConcluido"
                                v-model="local.concluido"
                                type="checkbox"
                                :disabled="!canUpdate"
                                @change="saveConcluido"
                            />
                            <span class="wr-concluido__box">
                                <i class="fas fa-check"></i>
                            </span>
                            <span class="wr-concluido__text">Marcar como concluído</span>
                        </label>

                        <button
                            v-if="canDelete"
                            type="button"
                            class="btn btn-sm btn-outline-danger btn-block wr-sidebar__danger"
                            @click="$emit('delete', local)"
                        >
                            <i class="fas fa-trash"></i> Excluir card
                        </button>
                    </div>
                </aside>
            </div>
        </template>
    </modal>
</template>

<script>
import autocomplete from '../AutoComplete'
import datepicker from '../DatePicker'
import upload from '../Upload'
import { checklistUrl, comentariosUrl, formatDataHoraPicker, inicialNome, itemUrl, normalizeComentario, normalizeLog, normalizeLogsMeta, normalizeTarefa, tarefasUrl, toastErro, toastOk } from './api'

export default {
    name: 'TaskModal',
    components: { autocomplete, datepicker, upload },
    props: {
        empresaId: { type: [Number, String], required: true },
        quadroId: { type: [Number, String], required: true },
        lista: { type: Object, default: null },
        tarefa: { type: Object, default: null },
        userId: { type: [Number, String], default: null },
        canUpdate: { type: Boolean, default: false },
        canDelete: { type: Boolean, default: false }
    },
    emits: ['close', 'delete', 'refresh', 'updated', 'checklist-changed'],
    data() {
        return {
            local: null,
            editingTitle: false,
            busy: false,
            loading: false,
            loadedId: null,
            membroBusca: '',
            newItems: {},
            newChecklistTitulo: '',
            addingChecklist: false,
            busyChecklist: false,
            deletingChecklistId: null,
            addingItemCkId: null,
            deletingItemId: null,
            dueEditorKey: null,
            memberEditorItemId: null,
            itemMembroBusca: '',
            feedTab: 'comentarios',
            novoComentario: '',
            novoComentarioTipo: 'comentario',
            busyComentario: false,
            deletingComentarioId: null,
            commentEditorKey: 0,
            _descSaveTimer: null,
            _descSavedSnapshot: null,
            logsMeta: null,
            busyLogs: false
        }
    },
    beforeUnmount() {
        clearTimeout(this._justOpenedTimer)
        if (this._onFocusInScroll) {
            document.removeEventListener('focusin', this._onFocusInScroll)
            this._onFocusInScroll = null
        }
    },
    computed: {
        hasComentarioTexto() {
            return !this.isEmptyHtml(this.novoComentario)
        },
        logsTotal() {
            if (this.logsMeta?.total != null) return Number(this.logsMeta.total)
            return (this.local?.logs || []).length
        },
        hasMoreLogs() {
            if (!this.logsMeta) return false
            return Number(this.logsMeta.current_page) < Number(this.logsMeta.last_page)
        },
        editorImageUploadUrl() {
            if (!this.lista || !this.local?.id) return ''
            return `${tarefasUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id)}/uploadEditorImage`
        },
        tinyBasicoInit() {
            return this.buildTinyImageInit({
                height: 180,
                placeholder: 'Descreva o card, contexto, critérios de aceite...'
            })
        },
        tinyComentarioInit() {
            return this.buildTinyImageInit({
                height: 140,
                placeholder: 'Ex.: aguardando retorno do cliente / bloqueado por falta de documentação...'
            })
        },
        uploadUrl() {
            if (!this.lista || !this.local?.id) return ''
            return `${tarefasUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id)}/uploadAnexos`
        },
        membrosUrl() {
            if (!this.lista || !this.local?.id) return ''
            return `${tarefasUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, false)}/buscarMembros`
        },
        membrosSearchUrl() {
            if (!this.lista || !this.local?.id) return ''
            return `${tarefasUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id)}/buscarMembros`
        }
    },
    watch: {
        'tarefa.id': {
            immediate: true,
            handler(id) {
                if (!id) {
                    this.local = null
                    this.loadedId = null
                    this.loading = false
                    this.logsMeta = null
                    this.busyLogs = false
                    return
                }
                if (Number(this.loadedId) === Number(id) && this.local) {
                    return
                }
                this.loadedId = id
                this.local = normalizeTarefa(this.tarefa)
                this.loading = true
                this.feedTab = 'comentarios'
                this.novoComentario = ''
                this.novoComentarioTipo = 'comentario'
                this.memberEditorItemId = null
                this.itemMembroBusca = ''
                this.logsMeta = null
                this.busyLogs = false
                this._descSavedSnapshot = this.local?.descricao || ''
                this.$nextTick(() => {
                    setTimeout(() => {
                        if (this.$refs.modal) this.$refs.modal.abrirModal()
                        this.scrollModalTopo()
                    }, 30)
                    this.loadShow().finally(() => {
                        this.$nextTick(() => {
                            this.scrollModalTopo()
                            // TinyMCE monta depois e às vezes rouba o scroll
                            setTimeout(() => this.scrollModalTopo(), 200)
                            setTimeout(() => this.scrollModalTopo(), 500)
                        })
                    })
                })
            }
        }
    },
    methods: {
        inicial: inicialNome,
        onModalAbriu() {
            this._justOpened = true
            this.scrollModalTopo()
            clearTimeout(this._justOpenedTimer)
            this._justOpenedTimer = setTimeout(() => {
                this._justOpened = false
                this.scrollModalTopo()
            }, 700)
            this.bindScrollGuard()
        },
        bindScrollGuard() {
            if (this._onFocusInScroll) return
            this._onFocusInScroll = (e) => {
                if (!this._justOpened) return
                const modal = document.getElementById('wrJanelaTarefa')
                if (!modal || !modal.contains(e.target)) return
                this.scrollModalTopo()
            }
            document.addEventListener('focusin', this._onFocusInScroll)
        },
        scrollModalTopo() {
            const modal = document.getElementById('wrJanelaTarefa')
            if (!modal) return
            const y = modal.style.overflowY
            modal.style.overflowY = 'hidden'
            modal.scrollTop = 0
            const body = modal.querySelector('.modal-body')
            if (body) body.scrollTop = 0
            const dialog = modal.querySelector('.modal-dialog')
            if (dialog) dialog.scrollTop = 0
            const content = modal.querySelector('.modal-content')
            if (content) content.scrollTop = 0
            requestAnimationFrame(() => {
                modal.style.overflowY = y || 'auto'
                modal.scrollTop = 0
                if (body) body.scrollTop = 0
            })
        },
        isEmptyHtml(html) {
            if (!html) return true
            if (/<img\b/i.test(String(html))) return false
            const tmp = document.createElement('div')
            tmp.innerHTML = String(html)
            const text = (tmp.textContent || tmp.innerText || '').replace(/\u00a0/g, ' ').trim()
            return !text
        },
        buildTinyImageInit(extra = {}) {
            return {
                ...extra,
                paste_data_images: true,
                auto_focus: false,
                automatic_uploads: true,
                images_upload_credentials: true,
                file_picker_types: 'image',
                images_upload_handler: (blobInfo, success, failure) => {
                    this.uploadTinyImage(blobInfo.blob(), blobInfo.filename())
                        .then((url) => success(url))
                        .catch((err) => {
                            const msg = err?.response?.data?.msg || err?.message || 'Falha no upload da imagem'
                            failure(msg, { remove: true })
                            toastErro(msg)
                        })
                },
                file_picker_callback: (cb, value, meta) => {
                    if (meta.filetype !== 'image') return
                    const input = document.createElement('input')
                    input.setAttribute('type', 'file')
                    input.setAttribute('accept', 'image/png,image/jpeg,image/jpg,image/gif')
                    input.onchange = () => {
                        const file = input.files && input.files[0]
                        if (!file) return
                        this.uploadTinyImage(file, file.name)
                            .then((url) => cb(url, { title: file.name, alt: file.name }))
                            .catch((err) => toastErro(err?.response?.data?.msg || 'Falha ao anexar imagem'))
                    }
                    input.click()
                }
            }
        },
        uploadTinyImage(blob, filename = 'image.png') {
            if (!this.editorImageUploadUrl) {
                return Promise.reject(new Error('Salve/abra o card antes de enviar imagens'))
            }
            const formData = new FormData()
            formData.append('arquivo', blob, filename)
            return axios.post(this.editorImageUploadUrl, formData).then(({ data }) => {
                if (data.arquivo) {
                    if (!this.local.anexos) this.local.anexos = []
                    if (!this.local.anexos.some((a) => Number(a.id) === Number(data.arquivo.id))) {
                        this.local.anexos.push(data.arquivo)
                        this.$emit('updated', this.local)
                    }
                }
                if (!data.location) {
                    throw new Error('URL da imagem não retornada')
                }
                return data.location
            })
        },
        progressPct(ck) {
            const itens = ck.itens || []
            if (!itens.length) return 0
            return Math.round((itens.filter((i) => i.concluido).length / itens.length) * 100)
        },
        dueKey(tipo, id) {
            return `${tipo}-${id}`
        },
        shortDue(value) {
            if (!value) return ''
            const formatted = formatDataHoraPicker(value)
            if (!formatted) return ''
            return formatted.replace(/\s+às\s+/i, ' ')
        },
        isDueLate(value, concluido = false) {
            if (!value || concluido) return false
            if (typeof moment === 'undefined') return false
            const m = moment(value, ['DD/MM/YYYY [às] HH:mm', 'YYYY-MM-DD HH:mm:ss'], true)
            return m.isValid() && m.isBefore(moment())
        },
        closeDueEditor() {
            this.dueEditorKey = null
        },
        toggleDueEditor(tipo, id) {
            const key = this.dueKey(tipo, id)
            if (this.dueEditorKey === key) {
                this.dueEditorKey = null
                return
            }
            this.dueEditorKey = key
            if (tipo === 'ck') {
                const ck = (this.local.checklists || []).find((c) => Number(c.id) === Number(id))
                if (ck && !ck.datahora_entrega) this.definirChecklistDue(ck)
            } else {
                let item = null
                let ck = null
                ;(this.local.checklists || []).forEach((c) => {
                    const found = (c.itens || []).find((i) => Number(i.id) === Number(id))
                    if (found) {
                        item = found
                        ck = c
                    }
                })
                if (ck && item && !item.datahora_entrega) this.definirItemDue(ck, item)
            }
        },
        definirChecklistDue(ck) {
            const datahora = this.formatPicker(
                ck.datahora_entrega ||
                    (typeof moment !== 'undefined' ? moment().hour(18).minute(0).format('DD/MM/YYYY [às] HH:mm') : null)
            )
            if (!datahora) return
            ck.datahora_entrega = datahora
            this.saveChecklistDue(ck, 'add', datahora)
        },
        onSelectChecklistDue(ck, value) {
            const datahora = this.formatPicker(typeof value === 'string' ? value : value?.value)
            if (!datahora) return
            ck.datahora_entrega = datahora
            this.saveChecklistDue(ck, 'add', datahora)
        },
        clearChecklistDue(ck) {
            this.saveChecklistDue(ck, 'remove')
        },
        saveChecklistDue(ck, acao, datahora = null) {
            const payload = { acao }
            if (acao === 'add') {
                payload.datahora_entrega = this.formatPicker(datahora || ck.datahora_entrega)
                if (!payload.datahora_entrega) {
                    toastErro('Informe uma data válida')
                    return
                }
            } else {
                payload.datahora_entrega = null
            }
            axios
                .put(checklistUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, ck.id), payload)
                .then(({ data }) => {
                    const updated = data.checklist || data
                    ck.datahora_entrega = formatDataHoraPicker(updated.datahora_entrega ?? (acao === 'remove' ? null : ck.datahora_entrega))
                    ck.emAtraso = !!(updated.em_atraso ?? updated.emAtraso ?? this.isDueLate(ck.datahora_entrega))
                    if (acao === 'remove') {
                        ck.datahora_entrega = null
                        ck.emAtraso = false
                        this.closeDueEditor()
                    }
                    this.$emit('updated', this.local)
                    this.$emit('checklist-changed', {
                        tarefa_id: this.local.id,
                        checklist_id: ck.id,
                        checklists: this.local.checklists
                    })
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao salvar prazo da checklist'))
        },
        definirItemDue(ck, item) {
            const datahora = this.formatPicker(
                item.datahora_entrega ||
                    (typeof moment !== 'undefined' ? moment().hour(18).minute(0).format('DD/MM/YYYY [às] HH:mm') : null)
            )
            if (!datahora) return
            item.datahora_entrega = datahora
            this.saveItemDue(ck, item, 'add', datahora)
        },
        onSelectItemDue(ck, item, value) {
            const datahora = this.formatPicker(typeof value === 'string' ? value : value?.value)
            if (!datahora) return
            item.datahora_entrega = datahora
            this.saveItemDue(ck, item, 'add', datahora)
        },
        clearItemDue(ck, item) {
            this.saveItemDue(ck, item, 'remove')
        },
        saveItemDue(ck, item, acao, datahora = null) {
            if (!item?.id || item._pending) return
            const payload = { acao }
            if (acao === 'add') {
                payload.datahora_entrega = this.formatPicker(datahora || item.datahora_entrega)
                if (!payload.datahora_entrega) {
                    toastErro('Informe uma data válida')
                    return
                }
            } else {
                payload.datahora_entrega = null
            }
            axios
                .put(itemUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, ck.id, item.id), payload)
                .then(({ data }) => {
                    const updated = data.item || data
                    item.datahora_entrega = formatDataHoraPicker(
                        updated.datahora_entrega ?? (acao === 'remove' ? null : item.datahora_entrega)
                    )
                    item.emAtraso = !!(
                        updated.em_atraso ??
                        updated.emAtraso ??
                        this.isDueLate(item.datahora_entrega, item.concluido)
                    )
                    if (acao === 'remove') {
                        item.datahora_entrega = null
                        item.emAtraso = false
                        this.closeDueEditor()
                    }
                    this.$emit('updated', this.local)
                    this.$emit('checklist-changed', {
                        tarefa_id: this.local.id,
                        checklist_id: ck.id,
                        checklists: this.local.checklists
                    })
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao salvar prazo do item'))
        },
        base() {
            return tarefasUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id)
        },
        startEditTitle() {
            this.editingTitle = true
            this.$nextTick(() => this.$refs.titulo && this.$refs.titulo.focus())
        },
        async loadShow() {
            if (!this.local?.id || !this.lista?.id) {
                this.loading = false
                return
            }
            const requestId = this.local.id
            try {
                const { data } = await axios.get(this.base())
                if (Number(this.loadedId) !== Number(requestId)) return
                this.local = normalizeTarefa({ ...this.local, ...data })
                this.logsMeta = normalizeLogsMeta(data.logs_meta ?? data.logsMeta)
                this._descSavedSnapshot = this.local.descricao || ''
                // Só atualiza o card no quadro (não mexe na prop do modal)
                this.$emit('updated', this.local)
            } catch (e) {
                /* keep local */
            } finally {
                if (Number(this.loadedId) === Number(requestId)) {
                    this.loading = false
                }
            }
        },
        async loadMoreLogs() {
            if (!this.hasMoreLogs || this.busyLogs || !this.local?.id || !this.lista?.id) return
            const nextPage = Number(this.logsMeta.current_page) + 1
            const requestId = this.local.id
            this.busyLogs = true
            try {
                const { data } = await axios.get(`${this.base()}/logs`, { params: { page: nextPage } })
                if (Number(this.loadedId) !== Number(requestId)) return
                const items = (Array.isArray(data.data) ? data.data : []).map(normalizeLog).filter(Boolean)
                const existing = new Set((this.local.logs || []).map((l) => Number(l.id)))
                const novos = items.filter((l) => !existing.has(Number(l.id)))
                this.local.logs = [...(this.local.logs || []), ...novos]
                this.logsMeta = normalizeLogsMeta({
                    current_page: data.current_page,
                    last_page: data.last_page,
                    per_page: data.per_page,
                    total: data.total
                })
            } catch (e) {
                toastErro(e?.response?.data?.msg || 'Erro ao carregar atividades')
            } finally {
                this.busyLogs = false
            }
        },
        prependLog(raw) {
            if (!this.local || !raw) return
            const log = normalizeLog(raw)
            if (!log?.id) return
            if (!this.local.logs) this.local.logs = []
            if (this.local.logs.some((l) => Number(l.id) === Number(log.id))) return
            this.local.logs.unshift(log)
            if (this.logsMeta) {
                this.logsMeta = {
                    ...this.logsMeta,
                    total: Number(this.logsMeta.total || 0) + 1
                }
            }
        },
        canManageComentario(c) {
            if (!this.canUpdate || !c) return false
            if (!this.userId) return true
            return Number(c.user_id || c.usuario?.id) === Number(this.userId) || this.canUpdate
        },
        syncComentarioCounts() {
            if (!this.local) return
            const list = this.local.comentarios || []
            this.local.comentarios_count = list.length
            this.local.bloqueios_count = list.filter((c) => c.tipo === 'bloqueio').length
        },
        applyComentario(e) {
            if (!this.local || !e) return
            if (!this.local.comentarios) this.local.comentarios = []
            const comentario = e.comentario ? normalizeComentario(e.comentario) : null
            const comentarioId = e.comentario_id || comentario?.id
            if (e.evento === 'delete' || (!comentario && comentarioId)) {
                this.local.comentarios = this.local.comentarios.filter((c) => Number(c.id) !== Number(comentarioId))
            } else if (comentario) {
                const idx = this.local.comentarios.findIndex((c) => Number(c.id) === Number(comentario.id))
                if (idx >= 0) this.local.comentarios.splice(idx, 1, comentario)
                else this.local.comentarios.unshift(comentario)
            }
            this.syncComentarioCounts()
            this.$emit('updated', this.local)
        },
        addComentario() {
            const texto = this.novoComentario || ''
            const tipo = this.novoComentarioTipo || 'comentario'
            if (this.isEmptyHtml(texto) || this.busyComentario) return

            this.busyComentario = true
            // Limpa o TinyMCE sem destruir (remount sumia o editor)
            const editorRef = this.$refs.commentEditor
            if (editorRef && typeof editorRef.clearContent === 'function') {
                editorRef.clearContent()
            } else {
                this.novoComentario = ''
            }
            this.novoComentarioTipo = 'comentario'

            axios
                .post(comentariosUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id), {
                    comentario: texto,
                    tipo
                })
                .then(({ data }) => {
                    const c = normalizeComentario(data.comentario || data)
                    if (!this.local.comentarios) this.local.comentarios = []
                    if (c?.id && !this.local.comentarios.some((x) => Number(x.id) === Number(c.id))) {
                        this.local.comentarios.unshift(c)
                    }
                    this.applyMembrosFromTarefa(data.tarefa)
                    this.syncComentarioCounts()
                    this.$emit('updated', this.local)
                })
                .catch((e) => {
                    if (editorRef && typeof editorRef.setContentHtml === 'function') {
                        editorRef.setContentHtml(texto)
                    } else {
                        this.novoComentario = texto
                    }
                    this.novoComentarioTipo = tipo
                    toastErro(e?.response?.data?.msg || 'Erro ao publicar comentário')
                })
                .finally(() => {
                    this.busyComentario = false
                })
        },
        deleteComentario(c) {
            if (!c?.id || this.deletingComentarioId) return
            this.deletingComentarioId = c.id
            const snapshot = (this.local.comentarios || []).slice()
            this.local.comentarios = snapshot.filter((x) => Number(x.id) !== Number(c.id))
            this.syncComentarioCounts()
            this.$emit('updated', this.local)

            axios
                .delete(comentariosUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, c.id))
                .catch((e) => {
                    if (e?.response?.status === 404) return
                    this.local.comentarios = snapshot
                    this.syncComentarioCounts()
                    this.$emit('updated', this.local)
                    toastErro(e?.response?.data?.msg || 'Erro ao excluir comentário')
                })
                .finally(() => {
                    this.deletingComentarioId = null
                })
        },
        saveTitulo() {
            this.editingTitle = false
            if (!this.local.titulo?.trim()) return
            axios
                .put(this.base(), { titulo: this.local.titulo.trim() })
                .then(() => this.$emit('updated', this.local))
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao salvar título'))
        },
        queueSaveDescricao() {
            if (!this.canUpdate || !this.local?.id) return
            if (this._descSaveTimer) clearTimeout(this._descSaveTimer)
            this._descSaveTimer = setTimeout(() => this.saveDescricao(), 700)
        },
        saveDescricao() {
            if (!this.local?.id) return
            const atual = this.local.descricao || ''
            if (atual === this._descSavedSnapshot) return
            axios
                .put(this.base(), { descricao: atual })
                .then(({ data }) => {
                    this._descSavedSnapshot = atual
                    this.applyMembrosFromTarefa(data?.tarefa)
                    this.$emit('updated', this.local)
                })
                .catch(() => {})
        },
        applyMembrosFromTarefa(tarefaPayload) {
            if (!this.local || !tarefaPayload) return
            const membros = tarefaPayload.membros ?? tarefaPayload.Membros
            if (!Array.isArray(membros)) return
            this.local.membros = membros
        },
        applyMembros(membros) {
            if (!this.local || !Array.isArray(membros)) return
            this.local.membros = membros
        },
        onEditorMention(user) {
            if (!this.canUpdate || !user?.id) return
            if ((this.local.membros || []).some((m) => Number(m.id) === Number(user.id))) return
            axios
                .put(`${this.base()}/updateMembro`, { acao: 'add', user_id: user.id })
                .then(({ data }) => {
                    if (data.tarefa) {
                        this.local = normalizeTarefa({ ...this.local, ...data.tarefa })
                        this.$emit('updated', this.local)
                    } else {
                        const nome = user.nome || user.label || 'Membro'
                        this.local.membros = [...(this.local.membros || []), { id: user.id, nome }]
                        this.$emit('updated', this.local)
                    }
                })
                .catch(() => {})
        },
        saveConcluido() {
            axios
                .put(this.base(), { concluido: !!this.local.concluido })
                .then(() => this.$emit('updated', this.local))
                .catch(() => {})
        },
        saveLembrete() {
            const valor = this.local.lembreteText || null
            this.local.lembreteText = valor
            axios
                .put(this.base(), { lembrete: valor })
                .then(({ data }) => {
                    if (data.tarefa) {
                        this.local = normalizeTarefa({ ...this.local, ...data.tarefa })
                    }
                    this.$emit('updated', this.local)
                    toastOk(valor ? 'Lembrete salvo' : 'Lembrete removido')
                })
                .catch((e) => {
                    toastErro(e?.response?.data?.msg || 'Erro ao salvar lembrete')
                })
        },
        setDataInicio() {
            this.definirDataInicio()
        },
        setDataEntrega() {
            this.definirDataEntrega()
        },
        formatPicker(value) {
            return formatDataHoraPicker(value) || (typeof moment !== 'undefined'
                ? moment().format('DD/MM/YYYY [às] HH:mm')
                : null)
        },
        definirDataInicio() {
            const datahora = this.formatPicker(
                this.local.datahora_inicio ||
                    (typeof moment !== 'undefined' ? moment().hour(8).minute(0).format('DD/MM/YYYY [às] HH:mm') : null)
            )
            this.local.datahora_inicio = datahora
            this.setData('inicio', 'add', datahora)
        },
        definirDataEntrega() {
            const datahora = this.formatPicker(
                this.local.datahora_entrega ||
                    (typeof moment !== 'undefined' ? moment().hour(18).minute(0).format('DD/MM/YYYY [às] HH:mm') : null)
            )
            this.local.datahora_entrega = datahora
            this.setData('entrega', 'add', datahora)
        },
        onSelectInicio(value) {
            const datahora = this.formatPicker(typeof value === 'string' ? value : value?.value)
            if (!datahora) return
            this.local.datahora_inicio = datahora
            this.setData('inicio', 'add', datahora)
        },
        onSelectEntrega(value) {
            const datahora = this.formatPicker(typeof value === 'string' ? value : value?.value)
            if (!datahora) return
            this.local.datahora_entrega = datahora
            this.setData('entrega', 'add', datahora)
        },
        setData(tipo, acao, datahora = null) {
            const path = tipo === 'inicio' ? 'updateDataHoraInicio' : 'updateDataHoraEntrega'
            const payload = {
                acao,
                lembrete: this.local.lembreteText
            }
            if (acao === 'add') {
                payload.datahora = this.formatPicker(datahora)
                if (!payload.datahora) {
                    toastErro('Informe uma data válida')
                    return Promise.resolve()
                }
            }
            return axios
                .put(`${this.base()}/${path}`, payload)
                .then(({ data }) => {
                    if (data.tarefa) {
                        this.local = normalizeTarefa({ ...this.local, ...data.tarefa })
                        this.$emit('updated', this.local)
                    } else if (acao === 'remove') {
                        if (tipo === 'inicio') this.local.datahora_inicio = null
                        else {
                            this.local.datahora_entrega = null
                            this.local.lembreteText = null
                        }
                        this.$emit('updated', this.local)
                    }
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao atualizar data'))
        },
        addMembro(user) {
            if (!user?.id) return
            if ((this.local.membros || []).some((m) => m.id === user.id)) {
                toastErro('O membro já está na tarefa')
                this.membroBusca = ''
                return
            }
            axios
                .put(`${this.base()}/updateMembro`, { acao: 'add', user_id: user.id })
                .then(({ data }) => {
                    if (data.tarefa) {
                        this.local = normalizeTarefa({ ...this.local, ...data.tarefa })
                        this.$emit('updated', this.local)
                    } else {
                        this.loadShow()
                    }
                    this.membroBusca = ''
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao adicionar membro'))
        },
        removeMembro(m) {
            axios
                .put(`${this.base()}/updateMembro`, { acao: 'remove', user_id: m.id })
                .then(({ data }) => {
                    if (data.tarefa) {
                        this.local = normalizeTarefa({ ...this.local, ...data.tarefa })
                        this.$emit('updated', this.local)
                    } else {
                        this.local.membros = (this.local.membros || []).filter((x) => x.id !== m.id)
                        this.$emit('updated', this.local)
                    }
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao remover membro'))
        },
        normalizeItemPayload(item) {
            if (!item) return item
            const membros = item.membros ?? item.Membros
            return {
                ...item,
                concluido: !!item.concluido,
                datahora_entrega: formatDataHoraPicker(item.datahora_entrega ?? item.datahora_entrega_br ?? null),
                emAtraso: !!(item.emAtraso ?? item.em_atraso),
                membros: Array.isArray(membros) ? membros : []
            }
        },
        patchLocalItem(ck, itemPayload) {
            if (!ck || !itemPayload) return
            const normalized = this.normalizeItemPayload(itemPayload)
            if (!ck.itens) ck.itens = []
            const idx = ck.itens.findIndex((i) => Number(i.id) === Number(normalized.id))
            if (idx >= 0) ck.itens.splice(idx, 1, { ...ck.itens[idx], ...normalized })
            else ck.itens.push(normalized)
            this.$emit('checklist-changed', {
                acao: 'update',
                checklist_id: ck.id,
                checklists: this.local.checklists
            })
        },
        toggleItemMemberEditor(item) {
            if (!item?.id) return
            if (this.memberEditorItemId === item.id) {
                this.closeItemMemberEditor()
                return
            }
            this.memberEditorItemId = item.id
            this.itemMembroBusca = ''
            this.closeDueEditor()
        },
        closeItemMemberEditor() {
            this.memberEditorItemId = null
            this.itemMembroBusca = ''
        },
        addItemMembro(ck, item, user) {
            if (!user?.id || !ck || !item) return
            if ((item.membros || []).some((m) => Number(m.id) === Number(user.id))) {
                toastErro('O membro já está no item')
                this.itemMembroBusca = ''
                return
            }
            axios
                .put(
                    `${itemUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, ck.id, item.id)}/updateMembro`,
                    { acao: 'add', user_id: user.id }
                )
                .then(({ data }) => {
                    if (data.item) this.patchLocalItem(ck, data.item)
                    this.itemMembroBusca = ''
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao adicionar membro no item'))
        },
        removeItemMembro(ck, item, m) {
            if (!m?.id || !ck || !item) return
            axios
                .put(
                    `${itemUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, ck.id, item.id)}/updateMembro`,
                    { acao: 'remove', user_id: m.id }
                )
                .then(({ data }) => {
                    if (data.item) this.patchLocalItem(ck, data.item)
                    else {
                        item.membros = (item.membros || []).filter((x) => Number(x.id) !== Number(m.id))
                        this.$emit('checklist-changed', {
                            acao: 'update',
                            checklist_id: ck.id,
                            checklists: this.local.checklists
                        })
                    }
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao remover membro do item'))
        },
        startAddChecklist() {
            this.addingChecklist = true
            this.newChecklistTitulo = 'Checklist'
            this.$nextTick(() => {
                const el = this.$refs.novaChecklistInput
                if (el) {
                    el.focus()
                    el.select()
                }
            })
        },
        cancelAddChecklist() {
            this.addingChecklist = false
            this.newChecklistTitulo = ''
        },
        confirmAddChecklist() {
            const titulo = (this.newChecklistTitulo || '').trim()
            if (!titulo || this.busyChecklist) return
            this.busyChecklist = true
            axios
                .post(checklistUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id), { titulo })
                .then(({ data }) => {
                    if (!this.local.checklists) this.local.checklists = []
                    this.local.checklists.push({ ...data, itens: data.itens || [] })
                    this.$emit('updated', this.local)
                    this.cancelAddChecklist()
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao criar checklist'))
                .finally(() => {
                    this.busyChecklist = false
                })
        },
        addChecklist() {
            this.startAddChecklist()
        },
        saveChecklist(ck) {
            axios
                .put(checklistUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, ck.id), { titulo: ck.titulo })
                .catch(() => {})
        },
        deleteChecklist(ck) {
            if (!ck?.id || this.deletingChecklistId) return
            const id = ck.id

            this.deletingChecklistId = id
            const snapshot = (this.local.checklists || []).slice()
            // Otimista: some da UI na hora (evita “não aconteceu nada”)
            this.local.checklists = snapshot.filter((c) => Number(c.id) !== Number(id))
            this.$emit('updated', this.local)

            axios
                .delete(checklistUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, id))
                .then(() => {
                    this.$emit('checklist-changed', { acao: 'delete', checklist_id: id, tarefa_id: this.local.id })
                })
                .catch((e) => {
                    const status = e?.response?.status
                    // Já removida no servidor — mantém UI otimista
                    if (status === 404) {
                        this.$emit('checklist-changed', { acao: 'delete', checklist_id: id, tarefa_id: this.local.id })
                        return
                    }
                    this.local.checklists = snapshot
                    this.$emit('updated', this.local)
                    toastErro(e?.response?.data?.msg || 'Erro ao excluir checklist')
                })
                .finally(() => {
                    this.deletingChecklistId = null
                })
        },
        setNewItem(ckId, value) {
            this.newItems = { ...this.newItems, [ckId]: value }
        },
        addItem(ck) {
            const titulo = (this.newItems[ck.id] || '').trim()
            if (!titulo || this.addingItemCkId) return

            this.addingItemCkId = ck.id
            this.setNewItem(ck.id, '')
            if (!ck.itens) ck.itens = []

            const tempId = `tmp-${Date.now()}`
            const tempItem = { id: tempId, titulo, concluido: false, membros: [], _pending: true }
            ck.itens.push(tempItem)
            this.$emit('updated', this.local)

            axios
                .post(itemUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, ck.id), { titulo })
                .then(({ data }) => {
                    const real = data.item || data
                    const idx = ck.itens.findIndex((i) => i.id === tempId)
                    if (idx >= 0) {
                        ck.itens.splice(idx, 1, {
                            id: real.id,
                            titulo: real.titulo || titulo,
                            concluido: !!real.concluido,
                            ordem: real.ordem,
                            datahora_entrega: formatDataHoraPicker(real.datahora_entrega),
                            emAtraso: !!(real.em_atraso ?? real.emAtraso),
                            membros: Array.isArray(real.membros ?? real.Membros) ? (real.membros ?? real.Membros) : []
                        })
                    }
                    this.$emit('updated', this.local)
                    this.$emit('checklist-changed', {
                        tarefa_id: this.local.id,
                        checklist_id: ck.id,
                        checklists: this.local.checklists
                    })
                })
                .catch((e) => {
                    ck.itens = (ck.itens || []).filter((i) => i.id !== tempId)
                    this.setNewItem(ck.id, titulo)
                    this.$emit('updated', this.local)
                    toastErro(e?.response?.data?.msg || 'Erro ao adicionar item')
                })
                .finally(() => {
                    this.addingItemCkId = null
                })
        },
        saveItem(ck, item) {
            if (!item?.id || item._pending) return
            axios
                .put(itemUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, ck.id, item.id), {
                    titulo: item.titulo
                })
                .catch(() => {})
        },
        toggleItem(ck, item, concluido) {
            if (!item?.id || item._pending) return
            const prev = item.concluido
            item.concluido = concluido
            this.$emit('updated', this.local)
            axios
                .put(itemUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, ck.id, item.id), { concluido })
                .catch(() => {
                    item.concluido = prev
                    this.$emit('updated', this.local)
                })
        },
        deleteItem(ck, item) {
            if (!item?.id || this.deletingItemId || item._pending) return
            const id = item.id
            this.deletingItemId = id
            const snapshot = (ck.itens || []).slice()
            ck.itens = snapshot.filter((i) => i.id !== id)
            this.$emit('updated', this.local)

            axios
                .delete(itemUrl(this.empresaId, this.quadroId, this.lista.id, this.local.id, ck.id, id))
                .then(() => {
                    this.$emit('checklist-changed', {
                        tarefa_id: this.local.id,
                        checklist_id: ck.id,
                        checklists: this.local.checklists
                    })
                })
                .catch((e) => {
                    if (e?.response?.status === 404) return
                    ck.itens = snapshot
                    this.$emit('updated', this.local)
                    toastErro(e?.response?.data?.msg || 'Erro ao excluir item')
                })
                .finally(() => {
                    this.deletingItemId = null
                })
        },
        deleteAnexo(anexo) {
            axios
                .delete(`${this.base()}/anexo/${anexo.file}`)
                .then(() => {
                    this.local.anexos = this.local.anexos.filter((a) => a.id !== anexo.id)
                    this.$emit('updated', this.local)
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao excluir anexo'))
        }
    }
}
</script>
