<template>
    <div>
        <div class="mb-3 d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-primary" @click="formNovo" data-toggle="modal" data-target="#modalSegmento">
                <i class="fa fa-plus"></i> Novo segmento
            </button>
        </div>

        <div v-if="carregando" class="text-center py-4">
            <i class="fa fa-spinner fa-pulse fa-2x text-muted"></i>
        </div>

        <div class="empty-state segmentos-empty" v-show="!carregando && lista.length === 0">
            <div class="empty-state-icon"><i class="fas fa-layer-group"></i></div>
            <h3 class="empty-state-title">Nenhum segmento cadastrado</h3>
            <p class="empty-state-text">Cadastre padrões de treinamento (ALUMAR, VALE, etc.) para uso na carteira e etiqueta de bloqueio.</p>
        </div>

        <div class="cards-lista segmentos-lista" v-show="!carregando && lista.length > 0">
            <div class="solicitacao-card segmento-card" v-for="s in lista" :key="s.id"
                 :class="{ 'card-status-ativo': s.ativo, 'card-status-inativo': !s.ativo }">
                <div class="card-header-row">
                    <div class="card-left">
                        <span class="badge-id">#{{ s.id }}</span>
                        <div class="colaborador-principal">
                            <i class="fas fa-tag mr-1"></i>
                            <strong>{{ s.nome }}</strong>
                        </div>
                        <span class="status-badge" :class="s.ativo ? 'status-ativo' : 'status-inativo'">
                            {{ s.ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                    <div class="card-right">
                        <button type="button" class="btn btn-sm btn-outline-primary" @click="editar(s)" data-toggle="modal" data-target="#modalSegmento" title="Editar">
                            <i class="fa fa-edit"></i>
                        </button>
                    </div>
                </div>
                <div class="card-details-row card-details-main">
                    <div class="detail-item">
                        <i class="fas fa-code"></i>
                        <span class="detail-label">Slug</span>
                        <span class="detail-value">{{ s.slug }}</span>
                    </div>
                    <div class="detail-item" v-if="s.config_carteira && (s.config_carteira.cabecalho_img || s.config_carteira.verso_img)">
                        <i class="fas fa-image"></i>
                        <span class="detail-label">Carteira</span>
                        <span class="detail-value detail-value-small">{{ temConfigCarteira(s) }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-tags"></i>
                        <span class="detail-label">Etiqueta bloqueio</span>
                        <span class="detail-value">{{ (s.config_carteira && s.config_carteira.exibir_etiqueta_bloqueio !== false) ? 'Sim' : 'Não' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalSegmento" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editando ? 'Editar' : 'Novo' }} segmento</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nome</label>
                            <input v-model="form.nome" class="form-control form-control-sm" type="text" placeholder="Ex: ALUMAR">
                        </div>
                        <div class="form-group">
                            <label>Slug (código)</label>
                            <input v-model="form.slug" class="form-control form-control-sm" type="text" placeholder="Ex: alumar">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="seg_ativo">
                                <label class="custom-control-label" for="seg_ativo">Ativo</label>
                            </div>
                        </div>

                        <hr class="my-3">
                        <h6 class="font-weight-bold text-primary">Configurações da Carteira de Treinamento</h6>
                        <p class="small text-muted mb-2">Imagens usadas no PDF da carteira. Caminho relativo (ex: images/carteira/arquivo.webp).</p>
                        <div class="form-group">
                            <label>Imagem cabeçalho (caminho)</label>
                            <input v-model="form.config_carteira.cabecalho_img" class="form-control form-control-sm" type="text" placeholder="Ex: images/carteira/cabecalho_carteira_alumar.webp">
                        </div>
                        <div class="form-group">
                            <label>Imagem verso (caminho)</label>
                            <input v-model="form.config_carteira.verso_img" class="form-control form-control-sm" type="text" placeholder="Ex: images/carteira/verso_carteira_alumar.webp">
                        </div>

                        <hr class="my-3">
                        <h6 class="font-weight-bold text-primary">Configurações da Etiqueta de Bloqueio</h6>
                        <p class="small text-muted mb-2">Define se o segmento exibe etiqueta de bloqueio e os textos/ramal usados no PDF.</p>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" v-model="form.config_carteira.exibir_etiqueta_bloqueio" class="custom-control-input" id="seg_exibir_bloqueio">
                                <label class="custom-control-label" for="seg_exibir_bloqueio">Exibir etiqueta de bloqueio para este segmento</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Ramal de emergência</label>
                            <input v-model="form.config_carteira.ramal_emergencia" class="form-control form-control-sm" type="text" placeholder="Ex: 1199">
                        </div>
                        <div class="form-group">
                            <label>Texto principal (não use, mova ou opere)</label>
                            <input v-model="form.config_carteira.bloqueio_texto_nao_use" class="form-control form-control-sm" type="text" placeholder="NÃO USE, MOVA OU OPERE...">
                        </div>
                        <div class="form-group">
                            <label>Texto demissão</label>
                            <input v-model="form.config_carteira.bloqueio_texto_demissao" class="form-control form-control-sm" type="text" placeholder="QUEM OPERAR O EQUIPAMENTO...">
                        </div>
                        <div class="form-group">
                            <label>Texto "Cuidado"</label>
                            <input v-model="form.config_carteira.bloqueio_texto_cuidado" class="form-control form-control-sm" type="text" placeholder="CUIDADO!">
                        </div>
                        <div class="form-group">
                            <label>Texto "Homens trabalhando"</label>
                            <input v-model="form.config_carteira.bloqueio_texto_homens_trabalhando" class="form-control form-control-sm" type="text" placeholder="HOMENS TRABALHANDO NÃO OPERE...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="button" class="btn btn-primary" @click="salvar">
                            {{ editando ? 'Salvar' : 'Cadastrar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            lista: [],
            carregando: false,
            editando: false,
            form: {
                id: null,
                nome: '',
                slug: '',
                ativo: true,
                config_carteira: {
                    cabecalho_img: '',
                    verso_img: '',
                    exibir_etiqueta_bloqueio: true,
                    ramal_emergencia: '1199',
                    bloqueio_texto_nao_use: 'NÃO USE, MOVA OU OPERE ENQUANTO ESTA ETIQUETA ESTIVER COLOCADA',
                    bloqueio_texto_demissao: 'QUEM OPERAR O EQUIPAMENTO OU REMOVER A ETIQUETA ESTÁ SUJEITO A DEMISSÃO',
                    bloqueio_texto_cuidado: 'CUIDADO!',
                    bloqueio_texto_homens_trabalhando: 'HOMENS TRABALHANDO NÃO OPERE ESTE EQUIPAMENTO',
                },
            },
        };
    },
    mounted() {
        this.carregar();
    },
    methods: {
        temConfigCarteira(s) {
            const c = s.config_carteira || {};
            const parts = [];
            if (c.cabecalho_img) parts.push('Cabeçalho');
            if (c.verso_img) parts.push('Verso');
            return parts.length ? parts.join(' + ') : '—';
        },
        getDefaultConfigCarteira() {
            return {
                cabecalho_img: '',
                verso_img: '',
                exibir_etiqueta_bloqueio: true,
                ramal_emergencia: '1199',
                bloqueio_texto_nao_use: 'NÃO USE, MOVA OU OPERE ENQUANTO ESTA ETIQUETA ESTIVER COLOCADA',
                bloqueio_texto_demissao: 'QUEM OPERAR O EQUIPAMENTO OU REMOVER A ETIQUETA ESTÁ SUJEITO A DEMISSÃO',
                bloqueio_texto_cuidado: 'CUIDADO!',
                bloqueio_texto_homens_trabalhando: 'HOMENS TRABALHANDO NÃO OPERE ESTE EQUIPAMENTO',
            };
        },
        carregar() {
            this.carregando = true;
            axios.post(`${URL_ADMIN}/cadastro/segmentostreinamento/atualizar`, { pages: 1 })
                .then(res => {
                    this.lista = (res.data.dados && res.data.dados.items) ? res.data.dados.items : [];
                })
                .finally(() => { this.carregando = false; });
        },
        formNovo() {
            this.editando = false;
            this.form = {
                id: null,
                nome: '',
                slug: '',
                ativo: true,
                config_carteira: this.getDefaultConfigCarteira(),
            };
        },
        editar(s) {
            this.editando = true;
            const cfg = s.config_carteira && typeof s.config_carteira === 'object' ? s.config_carteira : {};
            this.form = {
                id: s.id,
                nome: s.nome,
                slug: s.slug,
                ativo: s.ativo,
                config_carteira: { ...this.getDefaultConfigCarteira(), ...cfg },
            };
        },
        salvar() {
            if (!this.form.nome || !this.form.slug) {
                if (typeof mostraErro === 'function') mostraErro('', 'Preencha nome e slug');
                return;
            }
            const cfg = this.form.config_carteira || {};
            const payload = {
                nome: this.form.nome,
                slug: this.form.slug,
                ativo: Boolean(this.form.ativo),
                config_carteira: {
                    cabecalho_img: String(cfg.cabecalho_img ?? ''),
                    verso_img: String(cfg.verso_img ?? ''),
                    exibir_etiqueta_bloqueio: Boolean(cfg.exibir_etiqueta_bloqueio !== false),
                    ramal_emergencia: String(cfg.ramal_emergencia ?? '1199'),
                    bloqueio_texto_nao_use: String(cfg.bloqueio_texto_nao_use ?? ''),
                    bloqueio_texto_demissao: String(cfg.bloqueio_texto_demissao ?? ''),
                    bloqueio_texto_cuidado: String(cfg.bloqueio_texto_cuidado ?? ''),
                    bloqueio_texto_homens_trabalhando: String(cfg.bloqueio_texto_homens_trabalhando ?? ''),
                },
            };
            if (this.editando) {
                axios.put(`${URL_ADMIN}/cadastro/segmentostreinamento/${this.form.id}`, payload)
                    .then(() => {
                        $('#modalSegmento').modal('hide');
                        if (typeof mostraSucesso === 'function') mostraSucesso('', 'Segmento atualizado');
                        this.carregar();
                    })
                    .catch(err => {
                        if (typeof mostraErro === 'function') mostraErro('', err.response && err.response.data && err.response.data.msg ? err.response.data.msg : 'Erro ao salvar');
                    });
            } else {
                axios.post(`${URL_ADMIN}/cadastro/segmentostreinamento`, payload)
                    .then(() => {
                        $('#modalSegmento').modal('hide');
                        if (typeof mostraSucesso === 'function') mostraSucesso('', 'Segmento cadastrado');
                        this.carregar();
                    })
                    .catch(err => {
                        if (typeof mostraErro === 'function') mostraErro('', err.response && err.response.data && err.response.data.msg ? err.response.data.msg : 'Erro ao cadastrar');
                    });
            }
        },
    },
};
</script>

<style scoped>
.empty-state.segmentos-empty {
    text-align: center;
    padding: 3rem 1.5rem;
    background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
    border-radius: 12px;
    border: 1px dashed #dee2e6;
}
.empty-state-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: #6c757d;
}
.empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}
.empty-state-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0;
}
.cards-lista.segmentos-lista {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.solicitacao-card.segmento-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 0;
    transition: all 0.25s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border-left: 4px solid #6c757d;
    overflow: hidden;
}
.solicitacao-card.segmento-card.card-status-ativo {
    border-left-color: #28a745;
}
.solicitacao-card.segmento-card.card-status-inativo {
    border-left-color: #6c757d;
}
.solicitacao-card.segmento-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.card-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}
.card-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    overflow: hidden;
    min-width: 0;
}
.card-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}
.badge-id {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    background: #e9ecef;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}
.colaborador-principal {
    font-size: 0.9375rem;
}
.status-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}
.status-badge.status-ativo {
    background: #d4edda;
    color: #155724;
}
.status-badge.status-inativo {
    background: #e2e3e5;
    color: #383d41;
}
.card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem 1.5rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f0f0;
}
.card-details-row:last-child {
    border-bottom: none;
}
.card-details-main {
    padding-top: 0.75rem;
}
.detail-item {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8125rem;
}
.detail-item i {
    color: #6c757d;
    width: 1rem;
}
.detail-label {
    color: #6c757d;
}
.detail-value {
    color: #212529;
    font-weight: 500;
}
.detail-value-small {
    font-size: 0.75rem;
}
</style>
