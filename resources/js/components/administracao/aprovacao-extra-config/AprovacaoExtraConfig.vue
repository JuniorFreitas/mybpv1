<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Configuração de Aprovações Extras</h4>
                            <p class="card-subtitle text-muted mb-0">
                                Configure aprovações adicionais personalizadas para cada tipo de processo
                            </p>
                        </div>
                        <button class="btn btn-primary"
                                data-toggle="modal"
                                data-target="#modalConfig"
                                @click="abrirModal()">
                            <i class="bx bx-plus"></i> Nova Configuração
                        </button>
                    </div>

                    <div class="card-body">
                        <!-- Alerta informativo -->
                        <div class="alert alert-info border-0" role="alert">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Importante:</strong> RH sempre é a última aprovação.
                            O fluxo será: Gestor → Aprovação Extra → RH (final)
                        </div>

                        <!-- Tabela de configurações -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="180">Tipo de Processo</th>
                                        <th>Nome da Aprovação</th>
                                        <th>Usuários Autorizados</th>
                                        <th width="100" class="text-center">Status</th>
                                        <th width="120" class="text-center">Data Criação</th>
                                        <th width="150" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="loading">
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Carregando...</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-else-if="configs.length === 0">
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle fs-3"></i>
                                            <p class="mb-0 mt-2">Nenhuma configuração cadastrada</p>
                                        </td>
                                    </tr>
                                    <tr v-for="config in configs" :key="config.id">
                                        <td>
                                            <span class="badge bg-info">
                                                {{ tiposProcesso[config.tipo_processo] || config.tipo_processo }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ config.nome_aprovacao }}</strong>
                                        </td>
                                        <td>
                                            <span v-if="config.usuarios_autorizados && config.usuarios_autorizados.length > 0"
                                                  class="badge bg-secondary me-1"
                                                  v-for="userId in config.usuarios_autorizados.slice(0, 3)"
                                                  :key="userId">
                                                {{ getNomeUsuario(userId) }}
                                            </span>
                                            <span v-if="config.usuarios_autorizados && config.usuarios_autorizados.length > 3"
                                                  class="badge bg-secondary">
                                                +{{ config.usuarios_autorizados.length - 3 }} mais
                                            </span>
                                            <span v-else-if="!config.usuarios_autorizados || config.usuarios_autorizados.length === 0"
                                                  class="text-muted">
                                                <small>Apenas usuários com privilegio_rh</small>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge" :class="config.ativo ? 'bg-success' : 'bg-secondary'">
                                                {{ config.ativo ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <small>{{ formatarData(config.created_at) }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-warning"
                                                        data-toggle="modal"
                                                        data-target="#modalConfig"
                                                        @click="abrirModal(config)"
                                                        title="Editar">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                                <button class="btn btn-sm"
                                                        :class="config.ativo ? 'btn-secondary' : 'btn-success'"
                                                        @click="toggleAtivo(config.id)"
                                                        :title="config.ativo ? 'Desativar' : 'Ativar'">
                                                    <i class="bx" :class="config.ativo ? 'bx-x' : 'bx-check'"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger"
                                                        @click="deletar(config.id)"
                                                        title="Deletar">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Cadastro/Edição -->
        <modal id="modalConfig"
               :fechar="!salvando"
               size="lg"
               :titulo="(modoEdicao ? 'Editar' : 'Nova') + ' Configuração de Aprovação Extra'">
            <template slot="conteudo">
                <form @submit.prevent="salvar">
                    <!-- Tipo de Processo -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Tipo de Processo <span class="text-danger">*</span>
                                </label>
                                <select v-model="form.tipo_processo"
                                        class="form-select"
                                        :disabled="modoEdicao"
                                        required>
                                    <option value="">Selecione...</option>
                                    <option v-for="(nome, key) in tiposProcesso"
                                            :key="key"
                                            :value="key">
                                        {{ nome }}
                                    </option>
                                </select>
                                <small class="form-text text-muted">
                                    Define qual processo terá esta aprovação extra
                                </small>
                            </div>

                            <!-- Nome da Aprovação -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Nome da Aprovação <span class="text-danger">*</span>
                                </label>
                                <input v-model="form.nome_aprovacao"
                                       type="text"
                                       class="form-control"
                                       placeholder="Ex: SESMT, Supervisor, Gerente"
                                       required
                                       maxlength="255">
                                <small class="form-text text-muted">
                                    Nome que aparecerá no sistema (Ex: "Aguardando aprovação do SESMT")
                                </small>
                            </div>

                            <!-- Usuários Autorizados -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Usuários Autorizados
                                </label>
                                <div class="alert alert-info">
                                    Usuários selecionados + quem tem "privilegio_rh" podem aprovar
                                </div>

                                <div class="form-group">
                                    <label>Colaborador</label>
                                    <autocomplete :caminho="`autocomplete/buscaUsuariosAtivos`"
                                                  :formsm="true"
                                                  v-model="autocomplete_colaborador"
                                                  placeholder="Selecione um(a) colaborador(a)"
                                                  @onselect="selecionaColaborador"></autocomplete>
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
                                                <a href="javascript://" class="btn btn-sm btn-danger"
                                                   @click.prevent="removerColaborador(index)">
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
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="switchAtivo"
                                           v-model="form.ativo">
                                    <label class="form-check-label" for="switchAtivo">
                                        Ativo
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Apenas uma configuração pode estar ativa por tipo de processo
                                </small>
                            </div>

                            <!-- Alerta sobre fluxo -->
                            <div class="alert alert-warning border-0" role="alert">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Lembre-se:</strong> O fluxo será
                                <strong>Gestor → {{ form.nome_aprovacao || 'Aprovação Extra' }} → RH (final)</strong>
                            </div>
                </form>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bx bx-x"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" @click="salvar" :disabled="salvando">
                    <span v-if="salvando">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                        Salvando...
                    </span>
                    <span v-else>
                        <i class="bx bx-save"></i> Salvar
                    </span>
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
            salvando: false
        }
    },

    mounted() {
        this.carregarTiposProcesso();
        this.carregarUsuarios();
        this.carregarConfigs();
    },

    methods: {
        async carregarTiposProcesso() {
            try {
                const response = await axios.get('/g/administracao/aprovacao-extra-config/tipos-processo');
                this.tiposProcesso = response.data.tipos;
            } catch (error) {
                console.error('Erro ao carregar tipos:', error);
            }
        },

        async carregarUsuarios() {
            this.loadingUsuarios = true;
            try {
                const response = await axios.get('/g/administracao/aprovacao-extra-config/listar-usuarios');
                this.todosUsuarios = response.data;
            } catch (error) {
                console.error('Erro ao carregar usuários:', error);
                toastr.error('Não foi possível carregar os usuários');
            } finally {
                this.loadingUsuarios = false;
            }
        },

        async carregarConfigs() {
            this.loading = true;
            try {
                const response = await axios.get('/g/administracao/aprovacao-extra-config/listar');
                this.configs = response.data;
            } catch (error) {
                console.error('Erro ao carregar configurações:', error);
                toastr.error('Não foi possível carregar as configurações');
            } finally {
                this.loading = false;
            }
        },

        getNomeUsuario(userId) {
            const usuario = this.todosUsuarios.find(u => u.id === userId);
            return usuario ? usuario.nome.split(' ')[0] : `ID ${userId}`;
        },

        abrirModal(config = null) {
            if (config) {
                this.modoEdicao = true;

                // Converter usuarios_autorizados (array de IDs) para objetos completos
                let usuariosSelecionados = [];
                if (config.usuarios_autorizados && config.usuarios_autorizados.length > 0) {
                    usuariosSelecionados = this.todosUsuarios.filter(u =>
                        config.usuarios_autorizados.includes(u.id)
                    );
                }

                this.form = {
                    id: config.id,
                    tipo_processo: config.tipo_processo,
                    nome_aprovacao: config.nome_aprovacao,
                    usuarios_selecionados: usuariosSelecionados,
                    ativo: config.ativo
                };
            } else {
                this.modoEdicao = false;
                this.form = {
                    id: null,
                    tipo_processo: '',
                    nome_aprovacao: '',
                    usuarios_selecionados: [],
                    ativo: true
                };
            }
            this.autocomplete_colaborador = '';
        },

        selecionaColaborador(colaborador) {
            // Verifica se o colaborador já foi adicionado
            const jaExiste = this.form.usuarios_selecionados.some(u => u.id === colaborador.id);

            if (jaExiste) {
                toastr.warning('Este colaborador já foi adicionado');
                this.autocomplete_colaborador = '';
                return;
            }

            this.form.usuarios_selecionados.push({
                id: colaborador.id,
                nome: colaborador.nome,
                login: colaborador.login
            });

            this.autocomplete_colaborador = '';
        },

        removerColaborador(index) {
            this.form.usuarios_selecionados.splice(index, 1);
        },

        async salvar() {
            if (!this.form.tipo_processo || !this.form.nome_aprovacao) {
                toastr.warning('Preencha todos os campos obrigatórios');
                return;
            }

            this.salvando = true;
            try {
                // Converter usuarios_selecionados (objetos) para array de IDs
                const usuarios_autorizados = this.form.usuarios_selecionados.map(u => u.id);

                const dados = {
                    tipo_processo: this.form.tipo_processo,
                    nome_aprovacao: this.form.nome_aprovacao,
                    usuarios_autorizados: usuarios_autorizados,
                    ativo: this.form.ativo
                };

                let response;
                if (this.modoEdicao) {
                    response = await axios.put(
                        `/g/administracao/aprovacao-extra-config/${this.form.id}`,
                        dados
                    );
                } else {
                    response = await axios.post(
                        '/g/administracao/aprovacao-extra-config',
                        dados
                    );
                }

                toastr.success(response.data.message);
                $('#modalConfig').modal('hide');
                this.carregarConfigs();
            } catch (error) {
                console.error('Erro ao salvar:', error);
                toastr.error(error.response?.data?.message || 'Erro ao salvar configuração');
            } finally {
                this.salvando = false;
            }
        },

        async toggleAtivo(id) {
            try {
                const response = await axios.post(
                    `/g/administracao/aprovacao-extra-config/${id}/toggle-ativo`
                );
                toastr.success(response.data.message);
                this.carregarConfigs();
            } catch (error) {
                console.error('Erro ao alterar status:', error);
                toastr.error('Não foi possível alterar o status');
            }
        },

        async deletar(id) {
            if (!confirm('Tem certeza que deseja deletar esta configuração? Esta ação não poderá ser desfeita!')) {
                return;
            }

            try {
                await axios.delete(`/g/administracao/aprovacao-extra-config/${id}`);
                toastr.success('Configuração removida com sucesso');
                this.carregarConfigs();
            } catch (error) {
                console.error('Erro ao deletar:', error);
                toastr.error('Não foi possível deletar a configuração');
            }
        },

        formatarData(data) {
            if (!data) return '-';
            return new Date(data).toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
    }
}
</script>

<style scoped>
.card-subtitle {
    font-size: 14px;
}

.badge {
    font-size: 11px;
    padding: 4px 8px;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.table > :not(caption) > * > * {
    padding: 0.75rem;
}
</style>
