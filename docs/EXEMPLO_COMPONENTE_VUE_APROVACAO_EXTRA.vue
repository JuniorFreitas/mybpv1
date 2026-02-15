<template>
    <div class="aprovacao-extra-config">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Configuração de Aprovações Extras</h4>
                <p class="card-subtitle">Configure aprovações adicionais personalizadas para cada tipo de processo</p>
            </div>
            
            <div class="card-body">
                <!-- Botão para adicionar nova configuração -->
                <button class="btn btn-primary mb-3" @click="abrirModal()">
                    <i class="bx bx-plus"></i> Nova Configuração
                </button>

                <!-- Tabela de configurações -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Tipo de Processo</th>
                                <th>Nome da Aprovação</th>
                                <th>Status</th>
                                <th>Data Criação</th>
                                <th width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="configs.length === 0">
                                <td colspan="5" class="text-center">
                                    Nenhuma configuração cadastrada
                                </td>
                            </tr>
                            <tr v-for="config in configs" :key="config.id">
                                <td>
                                    <span class="badge badge-info">{{ tiposProcesso[config.tipo_processo] }}</span>
                                </td>
                                <td>
                                    <strong>{{ config.nome_aprovacao }}</strong>
                                </td>
                                <td>
                                    <span 
                                        class="badge" 
                                        :class="config.ativo ? 'badge-success' : 'badge-secondary'"
                                    >
                                        {{ config.ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>{{ formatarData(config.created_at) }}</td>
                                <td>
                                    <button 
                                        class="btn btn-sm btn-warning" 
                                        @click="abrirModal(config)"
                                        title="Editar"
                                    >
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button 
                                        class="btn btn-sm" 
                                        :class="config.ativo ? 'btn-secondary' : 'btn-success'"
                                        @click="toggleAtivo(config.id)"
                                        :title="config.ativo ? 'Desativar' : 'Ativar'"
                                    >
                                        <i class="bx" :class="config.ativo ? 'bx-x' : 'bx-check'"></i>
                                    </button>
                                    <button 
                                        class="btn btn-sm btn-danger" 
                                        @click="deletar(config.id)"
                                        title="Deletar"
                                    >
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal de Cadastro/Edição -->
        <div class="modal fade" id="modalConfig" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ modoEdicao ? 'Editar' : 'Nova' }} Configuração
                        </h5>
                        <button type="button" class="close" @click="fecharModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="salvar">
                            <!-- Tipo de Processo -->
                            <div class="form-group">
                                <label>Tipo de Processo *</label>
                                <select 
                                    v-model="form.tipo_processo" 
                                    class="form-control"
                                    :disabled="modoEdicao"
                                    required
                                >
                                    <option value="">Selecione...</option>
                                    <option 
                                        v-for="(nome, key) in tiposProcesso" 
                                        :key="key"
                                        :value="key"
                                    >
                                        {{ nome }}
                                    </option>
                                </select>
                                <small class="form-text text-muted">
                                    Define qual processo terá esta aprovação extra
                                </small>
                            </div>

                            <!-- Nome da Aprovação -->
                            <div class="form-group">
                                <label>Nome da Aprovação *</label>
                                <input 
                                    v-model="form.nome_aprovacao" 
                                    type="text"
                                    class="form-control"
                                    placeholder="Ex: SESMT, Supervisor, Gerente"
                                    required
                                    maxlength="255"
                                >
                                <small class="form-text text-muted">
                                    Nome que aparecerá no sistema (Ex: "Aguardando aprovação do SESMT")
                                </small>
                            </div>

                            <!-- Status Ativo -->
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input 
                                        type="checkbox" 
                                        class="custom-control-input" 
                                        id="switchAtivo"
                                        v-model="form.ativo"
                                    >
                                    <label class="custom-control-label" for="switchAtivo">
                                        Ativo
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Apenas uma configuração pode estar ativa por tipo de processo
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="fecharModal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" @click="salvar">
                            <i class="bx bx-save"></i> Salvar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AprovacaoExtraConfig',
    
    data() {
        return {
            configs: [],
            tiposProcesso: {},
            form: {
                id: null,
                tipo_processo: '',
                nome_aprovacao: '',
                ativo: true
            },
            modoEdicao: false
        }
    },

    mounted() {
        this.carregarTiposProcesso();
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

        async carregarConfigs() {
            try {
                const response = await axios.get('/g/administracao/aprovacao-extra-config/listar');
                this.configs = response.data;
            } catch (error) {
                console.error('Erro ao carregar configurações:', error);
                this.$swal('Erro', 'Não foi possível carregar as configurações', 'error');
            }
        },

        abrirModal(config = null) {
            if (config) {
                this.modoEdicao = true;
                this.form = {
                    id: config.id,
                    tipo_processo: config.tipo_processo,
                    nome_aprovacao: config.nome_aprovacao,
                    ativo: config.ativo
                };
            } else {
                this.modoEdicao = false;
                this.form = {
                    id: null,
                    tipo_processo: '',
                    nome_aprovacao: '',
                    ativo: true
                };
            }
            $('#modalConfig').modal('show');
        },

        fecharModal() {
            $('#modalConfig').modal('hide');
            this.form = {
                id: null,
                tipo_processo: '',
                nome_aprovacao: '',
                ativo: true
            };
        },

        async salvar() {
            try {
                let response;
                if (this.modoEdicao) {
                    response = await axios.put(
                        `/g/administracao/aprovacao-extra-config/${this.form.id}`,
                        this.form
                    );
                } else {
                    response = await axios.post(
                        '/g/administracao/aprovacao-extra-config',
                        this.form
                    );
                }

                this.$swal('Sucesso!', response.data.message, 'success');
                this.fecharModal();
                this.carregarConfigs();
            } catch (error) {
                console.error('Erro ao salvar:', error);
                this.$swal('Erro', error.response?.data?.message || 'Erro ao salvar configuração', 'error');
            }
        },

        async toggleAtivo(id) {
            try {
                const response = await axios.post(
                    `/g/administracao/aprovacao-extra-config/${id}/toggle-ativo`
                );
                this.$swal('Sucesso!', response.data.message, 'success');
                this.carregarConfigs();
            } catch (error) {
                console.error('Erro ao alterar status:', error);
                this.$swal('Erro', 'Não foi possível alterar o status', 'error');
            }
        },

        async deletar(id) {
            const result = await this.$swal({
                title: 'Tem certeza?',
                text: 'Esta ação não poderá ser desfeita!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, deletar!',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/g/administracao/aprovacao-extra-config/${id}`);
                    this.$swal('Deletado!', 'Configuração removida com sucesso', 'success');
                    this.carregarConfigs();
                } catch (error) {
                    console.error('Erro ao deletar:', error);
                    this.$swal('Erro', 'Não foi possível deletar a configuração', 'error');
                }
            }
        },

        formatarData(data) {
            if (!data) return '-';
            return new Date(data).toLocaleDateString('pt-BR');
        }
    }
}
</script>

<style scoped>
.aprovacao-extra-config {
    padding: 20px;
}

.card-subtitle {
    color: #6c757d;
    font-size: 14px;
}

.badge {
    font-size: 12px;
    padding: 5px 10px;
}

.btn-sm {
    margin-right: 5px;
}
</style>
