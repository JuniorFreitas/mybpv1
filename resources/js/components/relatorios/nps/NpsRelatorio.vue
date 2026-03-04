<template>
    <div>
        <fieldset class="mt-2">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="aplicarFiltros">
                <date-range-filter
                    v-model:enabled="controle.dados.filtroPeriodo"
                    v-model:start-date="controle.dados.dataInicio"
                    v-model:end-date="controle.dados.dataFim"
                    :disabled="controle.carregando"
                    :id-suffix="hash"
                    wrapper-class="col-12 col-md-3">
                </date-range-filter>

                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label>Ciclo</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.ciclo_id"
                                :disabled="controle.carregando">
                            <option value="">Todos</option>
                            <option v-for="c in ciclos" :key="c.id" :value="c.id">{{ c.nome }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label>Empresa</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.empresa_id"
                                :disabled="controle.carregando">
                            <option value="">Todas</option>
                            <option v-for="e in empresas" :key="e.id" :value="e.id">{{ e.nome }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary ml-1" @click="limparFiltros">
                                Limpar
                            </button>
                            <button type="button" class="btn btn-sm btn-primary ml-1" @click="abrirModalCiclo">
                                <i class="fa fa-plus"></i> Novo ciclo
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success ml-1" @click="exportarExcel" :disabled="controle.exportando">
                                <i :class="controle.exportando ? 'fa fa-spinner fa-spin' : 'fa fa-file-excel-o'"></i> Exportar Excel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </fieldset>

        <Modal
            ref="modalCiclo"
            id="modalNpsCiclo"
            titulo="Novo ciclo NPS"
            :fechar="true"
            size="g"
            :mostrar-botao-fechar-no-rodape="true"
            label-fechar="Cancelar"
            @fechou="limparFormCiclo">
            <template #conteudo>
                <form @submit.prevent="salvarCiclo" id="form-nps-ciclo">
                    <div class="form-group">
                        <label for="nps-ciclo-nome">Nome do ciclo / campanha</label>
                        <input id="nps-ciclo-nome" v-model="formCiclo.nome" type="text" class="form-control form-control-sm" required maxlength="255" placeholder="Ex: Campanha Q1 2026">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nps-ciclo-inicio">Data início</label>
                                <input id="nps-ciclo-inicio" v-model="formCiclo.data_inicio" type="date" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nps-ciclo-fim">Data fim</label>
                                <input id="nps-ciclo-fim" v-model="formCiclo.data_fim" type="date" class="form-control form-control-sm" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input id="nps-ciclo-ativo" v-model="formCiclo.ativo" type="checkbox" class="custom-control-input">
                            <label class="custom-control-label" for="nps-ciclo-ativo">Ciclo ativo (modal NPS exibido neste período)</label>
                        </div>
                    </div>
                </form>
            </template>
            <template #rodape>
                <button type="submit" form="form-nps-ciclo" class="btn btn-sm btn-success" :disabled="formCiclo.salvando">
                    <i v-if="formCiclo.salvando" class="fa fa-spinner fa-spin"></i>
                    <i v-else class="fa fa-check"></i> Salvar
                </button>
            </template>
        </Modal>

        <preload v-show="preload" class="text-center"></preload>

        <div v-if="!preload">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase mb-1">Total de respostas</h6>
                            <h4 class="mb-0 font-weight-bold">{{ resumoGeral.total_respostas }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase mb-1">Perguntas ativas</h6>
                            <h4 class="mb-0 font-weight-bold">{{ resumoGeral.por_pergunta.length }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Resumo por pergunta (total, média e distribuição %)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Pergunta</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Média</th>
                                    <th class="text-center">1 (%)</th>
                                    <th class="text-center">2 (%)</th>
                                    <th class="text-center">3 (%)</th>
                                    <th class="text-center">4 (%)</th>
                                    <th class="text-center">5 (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in resumoGeral.por_pergunta" :key="p.id">
                                    <td>{{ p.texto }}</td>
                                    <td class="text-center">{{ p.total }}</td>
                                    <td class="text-center">{{ formatMedia(p.media) }}</td>
                                    <td class="text-center" v-for="n in 5" :key="n">{{ (p.por_nota && p.por_nota[n]) ? p.por_nota[n].pct : 0 }}%</td>
                                </tr>
                                <tr v-if="!resumoGeral.por_pergunta.length">
                                    <td colspan="9" class="text-center text-muted">Nenhuma pergunta ou resposta no período.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Últimas respostas (usuário, empresa, data, notas)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Usuário</th>
                                    <th>Empresa</th>
                                    <th>Ciclo</th>
                                    <th>Data</th>
                                    <th>Respostas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(r, idx) in ultimasRespostas" :key="r.id || idx">
                                    <td>{{ r.user_nome }}</td>
                                    <td>{{ r.empresa_nome }}</td>
                                    <td>{{ r.ciclo_nome || '—' }}</td>
                                    <td>{{ r.data }}</td>
                                    <td>
                                        <span v-for="(item, i) in r.itens" :key="i" class="badge badge-secondary mr-1" :title="item.texto">
                                            {{ item.nota }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!ultimasRespostas.length">
                                    <td colspan="5" class="text-center text-muted">Nenhuma resposta no período.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import DateRangeFilter from '../../DateRangeFilter.vue';
import Modal from '../../Modal.vue';

function brToIso(br) {
    if (!br || !/^\d{2}\/\d{2}\/\d{4}$/.test(String(br).trim())) return '';
    const [d, m, y] = String(br).trim().split('/');
    return `${y}-${m}-${d}`;
}

function isoToBr(iso) {
    if (!iso) return '';
    const parts = String(iso).split('-');
    if (parts.length !== 3) return '';
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

function parseQueryParams() {
    const params = new URLSearchParams(window.location.search);
    const dataInicio = params.get('data_inicio') || '';
    const dataFim = params.get('data_fim') || '';
    const empresaId = params.get('empresa_id') || '';
    const cicloId = params.get('ciclo_id') || '';
    return {
        filtroPeriodo: !!(dataInicio && dataFim),
        dataInicio: brToIso(dataInicio),
        dataFim: brToIso(dataFim),
        empresa_id: empresaId,
        ciclo_id: cicloId,
    };
}

export default {
    name: 'NpsRelatorio',
    components: {
        DateRangeFilter,
        Modal,
    },
    data() {
        const q = parseQueryParams();
        return {
            hash: `nps_${parseInt(Math.random() * 999999, 10)}`,
            preload: true,
            controle: {
                carregando: false,
                exportando: false,
                dados: {
                    filtroPeriodo: q.filtroPeriodo,
                    dataInicio: q.dataInicio,
                    dataFim: q.dataFim,
                    empresa_id: q.empresa_id,
                    ciclo_id: q.ciclo_id,
                },
            },
            empresas: [],
            ciclos: [],
            resumoGeral: { total_respostas: 0, por_pergunta: [] },
            ultimasRespostas: [],
            formCiclo: {
                nome: '',
                data_inicio: '',
                data_fim: '',
                ativo: true,
                salvando: false,
            },
        };
    },
    mounted() {
        this.buscarDados();
    },
    methods: {
        formatMedia(val) {
            if (val == null) return '—';
            return Number(val).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
        },
        buscarDados() {
            this.preload = true;
            const params = {};
            if (this.controle.dados.filtroPeriodo && this.controle.dados.dataInicio && this.controle.dados.dataFim) {
                params.data_inicio = isoToBr(this.controle.dados.dataInicio);
                params.data_fim = isoToBr(this.controle.dados.dataFim);
            }
            if (this.controle.dados.empresa_id) {
                params.empresa_id = this.controle.dados.empresa_id;
            }
            if (this.controle.dados.ciclo_id) {
                params.ciclo_id = this.controle.dados.ciclo_id;
            }
            axios.get(`${URL_ADMIN}/relatorios/nps`, {
                params,
                headers: { Accept: 'application/json' },
            }).then(res => {
                this.empresas = res.data.empresasParaSelect || [];
                this.ciclos = res.data.ciclosParaSelect || [];
                this.resumoGeral = res.data.resumoGeral || { total_respostas: 0, por_pergunta: [] };
                this.ultimasRespostas = res.data.ultimasRespostasJson || [];
                if (res.data.filtros) {
                    this.controle.dados.filtroPeriodo = !!(res.data.filtros.data_inicio && res.data.filtros.data_fim);
                    this.controle.dados.dataInicio = brToIso(res.data.filtros.data_inicio || '');
                    this.controle.dados.dataFim = brToIso(res.data.filtros.data_fim || '');
                    this.controle.dados.empresa_id = res.data.filtros.empresa_id !== undefined && res.data.filtros.empresa_id !== null ? String(res.data.filtros.empresa_id) : '';
                    this.controle.dados.ciclo_id = res.data.filtros.ciclo_id !== undefined && res.data.filtros.ciclo_id !== null ? String(res.data.filtros.ciclo_id) : '';
                }
            }).finally(() => {
                this.preload = false;
                this.controle.carregando = false;
            });
        },
        aplicarFiltros() {
            this.controle.carregando = true;
            const base = `${window.location.origin}${window.location.pathname}`;
            const params = new URLSearchParams();
            if (this.controle.dados.filtroPeriodo && this.controle.dados.dataInicio && this.controle.dados.dataFim) {
                params.set('data_inicio', isoToBr(this.controle.dados.dataInicio));
                params.set('data_fim', isoToBr(this.controle.dados.dataFim));
            }
            if (this.controle.dados.empresa_id) {
                params.set('empresa_id', this.controle.dados.empresa_id);
            }
            if (this.controle.dados.ciclo_id) {
                params.set('ciclo_id', this.controle.dados.ciclo_id);
            }
            const qs = params.toString();
            window.location = qs ? `${base}?${qs}` : base;
        },
        limparFiltros() {
            window.location = `${window.location.origin}${window.location.pathname}`;
        },
        exportarExcel() {
            this.controle.exportando = true;
            const params = {};
            if (this.controle.dados.filtroPeriodo && this.controle.dados.dataInicio && this.controle.dados.dataFim) {
                params.data_inicio = isoToBr(this.controle.dados.dataInicio);
                params.data_fim = isoToBr(this.controle.dados.dataFim);
            }
            if (this.controle.dados.empresa_id) {
                params.empresa_id = this.controle.dados.empresa_id;
            }
            if (this.controle.dados.ciclo_id) {
                params.ciclo_id = this.controle.dados.ciclo_id;
            }
            axios.post(`${URL_ADMIN}/relatorios/nps/export`, params)
                .then(res => {
                    const msg = (res.data && res.data.msg) ? res.data.msg : 'Exportação solicitada. Você será notificado quando o arquivo estiver pronto.';
                    this.$swal && this.$swal.fire({ icon: 'success', title: msg });
                })
                .catch(err => {
                    const msg = (err.response && err.response.data && err.response.data.message) ? err.response.data.message : 'Erro ao solicitar exportação.';
                    this.$swal && this.$swal.fire({ icon: 'error', title: msg });
                })
                .finally(() => {
                    this.controle.exportando = false;
                });
        },
        abrirModalCiclo() {
            this.limparFormCiclo();
            this.$nextTick(() => {
                this.$refs.modalCiclo.abrirModal();
            });
        },
        limparFormCiclo() {
            this.formCiclo.nome = '';
            this.formCiclo.data_inicio = '';
            this.formCiclo.data_fim = '';
            this.formCiclo.ativo = true;
            this.formCiclo.salvando = false;
        },
        salvarCiclo() {
            if (!this.formCiclo.nome || !this.formCiclo.data_inicio || !this.formCiclo.data_fim) {
                this.$swal && this.$swal.fire({ icon: 'warning', title: 'Preencha nome, data início e data fim.' });
                return;
            }
            if (this.formCiclo.data_fim < this.formCiclo.data_inicio) {
                this.$swal && this.$swal.fire({ icon: 'warning', title: 'Data fim deve ser igual ou posterior à data início.' });
                return;
            }
            this.formCiclo.salvando = true;
            axios.post(`${URL_ADMIN}/relatorios/nps/ciclos`, {
                nome: this.formCiclo.nome,
                data_inicio: this.formCiclo.data_inicio,
                data_fim: this.formCiclo.data_fim,
                ativo: this.formCiclo.ativo,
            }).then(res => {
                if (res.data.sucesso && res.data.ciclo) {
                    this.ciclos.push({ id: res.data.ciclo.id, nome: res.data.ciclo.nome });
                    this.$refs.modalCiclo.fecharModal();
                    this.$swal && this.$swal.fire({ icon: 'success', title: 'Ciclo criado com sucesso.' });
                }
            }).catch(err => {
                const msg = (err.response && err.response.data && err.response.data.mensagem) ? err.response.data.mensagem : 'Erro ao salvar ciclo.';
                this.$swal && this.$swal.fire({ icon: 'error', title: msg });
            }).finally(() => {
                this.formCiclo.salvando = false;
            });
        },
    },
};
</script>

<style scoped>
.table th {
    white-space: nowrap;
}
</style>
