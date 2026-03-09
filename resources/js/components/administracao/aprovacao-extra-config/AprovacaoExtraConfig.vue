<template>
    <div class="container-fluid">
        <div id="componente">
            <fieldset>
                <legend>Filtro</legend>
                <form class="row" @submit.prevent="carregarConfigs()">
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label>Buscar</label>
                            <input
                                type="text"
                                placeholder="Buscar por nome ou tipo"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                :disabled="loading"
                                v-model="filtroBusca"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-12">
                        <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="loading" @click="carregarConfigs()">
                            <i :class="loading ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                        </button>
                        <button type="button" class="btn btn-sm mr-1 btn-secondary" @click="abrirModal()"><i class="fa fa-plus"></i> Cadastrar</button>
                    </div>
                </form>
            </fieldset>

            <div class="alert alert-info border-0 py-2" role="alert">
                <i class="fa fa-info-circle me-2"></i>
                <strong>Importante:</strong> O RH sempre é a última aprovação! <br />
                O fluxo será: <strong>Gestor → Aprovação Extra → RH (final)</strong>
            </div>

            <preload class="text-center" v-if="loading"></preload>

            <div class="alert alert-warning text-center" v-show="!loading && configsFiltrados.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <!-- Cards no padrão processo admissão -->
            <div class="cards-lista" v-show="!loading && configsFiltrados.length > 0">
                <div class="solicitacao-card" v-for="config in configsFiltrados" :key="config.id">
                    <div class="card-header-row">
                        <div class="card-left">
                            <span class="badge-id">#{{ config.id }}</span>
                            <div class="colaborador-principal">
                                <span class="badge-tipo">{{ tiposProcesso[config.tipo_processo] || config.tipo_processo }}</span>
                                <strong>{{ config.nome_aprovacao }}</strong>
                            </div>
                            <div class="data-info ml-3">
                                <i class="fas fa-calendar-plus text-muted" style="font-size: 0.75rem"></i>
                                <small class="text-muted">{{ config.created_at }}</small>
                            </div>
                        </div>
                        <div class="card-right">
                            <bt-ativo
                                :rota="`administracao/aprovacao-extra-config/${config.id}/ativa-desativa`"
                                :model="config"
                                @atualizou="carregarConfigs()"
                            ></bt-ativo>
                            <div class="dropdown" :class="{ show: isDropdownOpen(config.id) }">
                                <a
                                    class="btn-actions-compact"
                                    href="#"
                                    role="button"
                                    aria-haspopup="true"
                                    :aria-expanded="isDropdownOpen(config.id) ? 'true' : 'false'"
                                    @click.prevent.stop="toggleDropdown(config.id)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(config.id) }"
                                    @click="fecharDropdown"
                                >
                                    <a class="dropdown-item" href="javascript://" title="Editar" @click.prevent="abrirModal(config)">
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt text-muted"></i>
                            <span class="detail-label">Data criação:</span>
                            <span class="detail-value">{{ config.created_at }}</span>
                        </div>
                        <div class="detail-item" v-if="config.updated_at">
                            <i class="fas fa-calendar-check text-muted"></i>
                            <span class="detail-label">Última alteração:</span>
                            <span class="detail-value">{{ config.updated_at }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users text-muted"></i>
                            <span class="detail-label">Usuários autorizados:</span>
                            <span class="detail-value">
                                <template v-if="config.usuarios_autorizados && config.usuarios_autorizados.length > 0">
                                    <strong>{{ config.usuarios_autorizados.length }}</strong> usuário(s) —
                                    <span v-for="userId in config.usuarios_autorizados.slice(0, 3)" :key="userId" class="me-1">
                                        {{ getNomeUsuario(userId) }}
                                    </span>
                                    <span v-if="config.usuarios_autorizados.length > 3"> +{{ config.usuarios_autorizados.length - 3 }} mais </span>
                                </template>
                                <span v-else class="text-muted">Apenas privilegio_rh</span>
                            </span>
                        </div>
                    </div>
                    <div class="card-details-row card-fluxo">
                        <div class="detail-item detail-fluxo">
                            <i class="fas fa-project-diagram text-primary"></i>
                            <span class="detail-label">Fluxo:</span>
                            <span class="detail-value">
                                <span class="fluxo-texto">Gestor</span>
                                <i class="fas fa-chevron-right text-muted mx-1" style="font-size: 0.65rem"></i>
                                <span class="fluxo-texto fluxo-extra">{{ config.nome_aprovacao }}</span>
                                <i class="fas fa-chevron-right text-muted mx-1" style="font-size: 0.65rem"></i>
                                <span class="fluxo-texto">RH (final)</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Cadastro/Edição -->
        <modal
            ref="modalConfig"
            id="modalConfig"
            :fechar="!salvando"
            :mostrar-botao-fechar-no-rodape="false"
            size="lg"
            :titulo="(modoEdicao ? 'Editar' : 'Nova') + ' Configuração de Aprovação Extra'"
        >
            <template #conteudo>
                <form @submit.prevent="salvar">
                    <!-- Tipo de Processo -->
                    <div class="mb-3">
                        <label class="form-label"> Tipo de Processo <span class="text-danger">*</span> </label>
                        <select v-model="form.tipo_processo" class="form-select" :disabled="modoEdicao" required>
                            <option value="">Selecione...</option>
                            <option v-for="(nome, key) in tiposProcesso" :key="key" :value="key">
                                {{ nome }}
                            </option>
                        </select>
                        <small class="form-text text-muted"> Define qual processo terá esta aprovação extra </small>
                    </div>

                    <!-- Nome da Aprovação -->
                    <div class="mb-3">
                        <label class="form-label"> Nome da Aprovação <span class="text-danger">*</span> </label>
                        <input
                            v-model="form.nome_aprovacao"
                            type="text"
                            class="form-control"
                            placeholder="Ex: SESMT, Supervisor, Gerente"
                            required
                            maxlength="255"
                        />
                        <small class="form-text text-muted"> Nome que aparecerá no sistema (Ex: "Aguardando aprovação do SESMT") </small>
                    </div>

                    <!-- Usuários Autorizados -->
                    <div class="mb-3">
                        <label class="form-label"> Usuários Autorizados </label>
                        <div class="alert alert-info">Usuários selecionados + quem tem "privilegio_rh" podem aprovar</div>

                        <div class="form-group">
                            <label>Colaborador</label>
                            <autocomplete
                                :caminho="`autocomplete/buscaUsuariosAtivos`"
                                :formsm="true"
                                v-model="autocomplete_colaborador"
                                placeholder="Selecione um(a) colaborador(a)"
                                @onselect="selecionaColaborador"
                            ></autocomplete>
                        </div>

                        <div class="table-responsive" v-if="form.usuarios_selecionados.length > 0">
                            <table class="table table-bordered table-hover table-condensed bg-white">
                                <thead>
                                    <tr class="bg-default">
                                        <th class="text-center">Nome</th>
                                        <th class="text-center">Login</th>
                                        <th class="text-center">Remover</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(colaborador, index) in form.usuarios_selecionados" :key="colaborador.id">
                                        <td class="text-center">{{ colaborador.nome }}</td>
                                        <td class="text-center">{{ colaborador.login }}</td>
                                        <td class="text-center">
                                            <a href="javascript://" class="btn btn-sm mr-1 btn-danger" @click.prevent="removerColaborador(index)">
                                                <i class="fa fa-times" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Status Ativo -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="switchAtivo" v-model="form.ativo" />
                            <label class="form-check-label" for="switchAtivo"> Ativo </label>
                        </div>
                        <small class="form-text text-muted"> Apenas uma configuração pode estar ativa por tipo de processo </small>
                    </div>

                    <!-- Alerta sobre fluxo -->
                    <div class="alert alert-warning border-0" role="alert">
                        <i class="fa fa-info-circle me-2"></i>
                        <strong>Lembre-se:</strong> O fluxo será
                        <strong>Gestor → {{ form.nome_aprovacao || 'Aprovação Extra' }} → RH (final)</strong>
                    </div>
                </form>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-secondary" @click="fecharModal"><i class="fa fa-times"></i> Cancelar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" @click="salvar" :disabled="salvando">
                    <span v-if="salvando">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                        Salvando...
                    </span>
                    <span v-else> <i class="fa fa-save"></i> Salvar </span>
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
export default {
    name: 'AprovacaoExtraConfig',

    data() {
        return {
            configs: [],
            todosUsuarios: [],
            tiposProcesso: {},
            autocomplete_colaborador: '',
            dropdownAbertoKey: null,
            form: {
                id: null,
                tipo_processo: '',
                nome_aprovacao: '',
                usuarios_selecionados: [],
                ativo: true
            },
            modoEdicao: false,
            loading: true,
            loadingUsuarios: false,
            salvando: false,
            filtroBusca: ''
        }
    },

    computed: {
        configsFiltrados() {
            if (!this.filtroBusca || !this.filtroBusca.trim()) return this.configs
            const q = this.filtroBusca.trim().toLowerCase()
            return this.configs.filter((c) => {
                const nome = (c.nome_aprovacao || '').toLowerCase()
                const tipo = (this.tiposProcesso[c.tipo_processo] || c.tipo_processo || '').toLowerCase()
                return nome.includes(q) || tipo.includes(q)
            })
        }
    },

    mounted() {
        this.carregarTiposProcesso()
        this.carregarUsuarios()
        this.carregarConfigs()
        document.addEventListener('click', this.onClickOutside)
    },

    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },

    methods: {
        async carregarTiposProcesso() {
            try {
                const response = await axios.get('/g/administracao/aprovacao-extra-config/tipos-processo')
                this.tiposProcesso = response.data.tipos
            } catch (error) {
                console.error('Erro ao carregar tipos:', error)
            }
        },

        async carregarUsuarios() {
            this.loadingUsuarios = true
            try {
                const response = await axios.get('/g/administracao/aprovacao-extra-config/listar-usuarios')
                this.todosUsuarios = response.data
            } catch (error) {
                console.error('Erro ao carregar usuários:', error)
                toastr.error('Não foi possível carregar os usuários')
            } finally {
                this.loadingUsuarios = false
            }
        },

        async carregarConfigs() {
            this.loading = true
            try {
                const response = await axios.get('/g/administracao/aprovacao-extra-config/listar')
                this.configs = response.data
            } catch (error) {
                console.error('Erro ao carregar configurações:', error)
                toastr.error('Não foi possível carregar as configurações')
            } finally {
                this.loading = false
            }
        },

        getNomeUsuario(userId) {
            const usuario = this.todosUsuarios.find((u) => u.id === userId)
            return usuario ? usuario.nome.split(' ')[0] : `ID ${userId}`
        },

        abrirModal(config = null) {
            if (config) {
                this.modoEdicao = true

                // Converter usuarios_autorizados (array de IDs) para objetos completos
                let usuariosSelecionados = []
                if (config.usuarios_autorizados && config.usuarios_autorizados.length > 0) {
                    usuariosSelecionados = this.todosUsuarios.filter((u) => config.usuarios_autorizados.includes(u.id))
                }

                this.form = {
                    id: config.id,
                    tipo_processo: config.tipo_processo,
                    nome_aprovacao: config.nome_aprovacao,
                    usuarios_selecionados: usuariosSelecionados,
                    ativo: config.ativo
                }
            } else {
                this.modoEdicao = false
                this.form = {
                    id: null,
                    tipo_processo: '',
                    nome_aprovacao: '',
                    usuarios_selecionados: [],
                    ativo: true
                }
            }
            this.autocomplete_colaborador = ''
            if (this.$refs && this.$refs.modalConfig && typeof this.$refs.modalConfig.abrirModal === 'function') {
                this.$refs.modalConfig.abrirModal()
            }
        },

        fecharModal() {
            if (this.$refs && this.$refs.modalConfig && typeof this.$refs.modalConfig.fecharModal === 'function') {
                this.$refs.modalConfig.fecharModal()
            }
        },

        toggleDropdown(configId) {
            if (!configId) {
                return
            }
            const key = `cfg:${configId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },

        isDropdownOpen(configId) {
            return this.dropdownAbertoKey === `cfg:${configId}`
        },

        fecharDropdown() {
            this.dropdownAbertoKey = null
        },

        onClickOutside(event) {
            if (event && event.target && event.target.closest && event.target.closest('.dropdown')) {
                return
            }
            this.dropdownAbertoKey = null
        },

        selecionaColaborador(colaborador) {
            // Verifica se o colaborador já foi adicionado
            const jaExiste = this.form.usuarios_selecionados.some((u) => u.id === colaborador.id)

            if (jaExiste) {
                toastr.warning('Este colaborador já foi adicionado')
                this.autocomplete_colaborador = ''
                return
            }

            this.form.usuarios_selecionados.push({
                id: colaborador.id,
                nome: colaborador.nome,
                login: colaborador.login
            })

            this.autocomplete_colaborador = ''
        },

        removerColaborador(index) {
            this.form.usuarios_selecionados.splice(index, 1)
        },

        async salvar() {
            if (!this.form.tipo_processo || !this.form.nome_aprovacao) {
                toastr.warning('Preencha todos os campos obrigatórios')
                return
            }

            this.salvando = true
            try {
                // Converter usuarios_selecionados (objetos) para array de IDs
                const usuarios_autorizados = this.form.usuarios_selecionados.map((u) => u.id)

                const dados = {
                    tipo_processo: this.form.tipo_processo,
                    nome_aprovacao: this.form.nome_aprovacao,
                    usuarios_autorizados: usuarios_autorizados,
                    ativo: this.form.ativo
                }

                let response
                if (this.modoEdicao) {
                    response = await axios.put(`/g/administracao/aprovacao-extra-config/${this.form.id}`, dados)
                } else {
                    response = await axios.post('/g/administracao/aprovacao-extra-config', dados)
                }

                toastr.success(response.data.message)
                this.fecharModal()
                this.carregarConfigs()
            } catch (error) {
                console.error('Erro ao salvar:', error)
                toastr.error(error.response?.data?.message || 'Erro ao salvar configuração')
            } finally {
                this.salvando = false
            }
        },

        async deletar(id) {
            if (!confirm('Tem certeza que deseja deletar esta configuração? Esta ação não poderá ser desfeita!')) {
                return
            }

            try {
                await axios.delete(`/g/administracao/aprovacao-extra-config/${id}`)
                toastr.success('Configuração removida com sucesso')
                this.carregarConfigs()
            } catch (error) {
                console.error('Erro ao deletar:', error)
                toastr.error('Não foi possível deletar a configuração')
            }
        },

        formatarData(data) {
            if (!data) return '-'
            return new Date(data).toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            })
        }
    }
}
</script>

<style scoped>
/* Container de Cards - padrão processo admissão */
.cards-lista {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.solicitacao-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.solicitacao-card:hover {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    border-color: #007bff;
    transform: translateY(-2px);
}

.card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    margin-bottom: 0.75rem;
}

.card-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    overflow: hidden;
}

.card-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.badge-id {
    background: #174257;
    color: white;
    padding: 0.25rem 0.625rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.75rem;
    white-space: nowrap;
    flex-shrink: 0;
}

.badge-tipo {
    background: #6c757d;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-right: 0.5rem;
    flex-shrink: 0;
}

.colaborador-principal {
    display: flex;
    align-items: center;
    font-size: 0.938rem;
    color: #212529;
    overflow: hidden;
}

.colaborador-principal strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.data-info {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.card-details-row.card-fluxo {
    padding-top: 0.5rem;
    border-top: 1px dashed #e9ecef;
    margin-top: 0.25rem;
}

.detail-fluxo .detail-value {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

.fluxo-texto {
    font-weight: 500;
}

.fluxo-texto.fluxo-extra {
    color: #007bff;
    font-weight: 600;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.813rem;
    min-width: 0;
}

.detail-item i {
    flex-shrink: 0;
    font-size: 0.875rem;
}

.detail-label {
    font-weight: 500;
    color: #6c757d;
    white-space: nowrap;
}

.detail-value {
    color: #212529;
    font-weight: 400;
}

.btn-actions-compact {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #495057;
    transition: all 0.2s ease;
    text-decoration: none;
    flex-shrink: 0;
}

.btn-actions-compact:hover {
    background: #007bff;
    border-color: #007bff;
    color: white;
    transform: rotate(90deg);
}

.dropdown-menu-custom {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: none;
    padding: 0.5rem 0;
}

.dropdown-menu-custom .dropdown-item {
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.dropdown-menu-custom .dropdown-item:hover {
    background: #f8f9fa;
    color: #007bff;
    padding-left: 1.5rem;
}

@media (max-width: 768px) {
    .card-header-row {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .card-left {
        width: 100%;
    }

    .card-right {
        width: 100%;
        justify-content: space-between;
    }

    .card-details-row {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>
