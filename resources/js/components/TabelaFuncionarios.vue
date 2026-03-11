// TabelaFuncionarios.vue
<template>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                    <tr>
                        <th style="width: 40px;">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="checkTodos"
                                       :checked="todosItensSelecionados" @change="selecionarTodos">
                                <label class="custom-control-label" for="checkTodos"></label>
                            </div>
                        </th>
                        <!--                        <th style="width: 40px;">-->
                        <!--                            <div class="custom-control custom-checkbox">-->
                        <!--                                <input type="checkbox" class="custom-control-input" id="checkTodosMassa"-->
                        <!--                                       :checked="todosItensMassaSelecionados" @change="selecionarTodosMassa">-->
                        <!--                                <label class="custom-control-label" for="checkTodosMassa"></label>-->
                        <!--                            </div>-->
                        <!--                        </th>-->
                        <th>Nome / CPF</th>
                        <th>Cargo</th>
                        <th v-if="temFilial">CNPJ</th>
                        <th>Centro de Custo</th>
                        <th style="width: 70px;">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Loop pelos funcionários -->
                    <template v-for="(funcionario, indexFunc) in funcionarios" :key="indexFunc">
                        <!-- Linha do funcionário -->
                        <tr class="bg-light">
                            <td :colspan="temFilial ? 7 : 6" class="py-2">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-0 font-weight-bold">{{ funcionario.curriculo.nome }}</h6>
                                        <small class="text-muted">
                                            CPF: {{ funcionario.curriculo.cpf }} |
                                            {{ funcionario.vaga_aberta.vaga.nome }} |
                                            {{ funcionario.admissao ? funcionario.admissao.emp_centro_custo : '---' }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Tabela de treinamentos do funcionário -->
                        <tr :key="'tr-container-' + funcionario.id"
                            v-if="funcionario.treinamento && funcionario.treinamento.vencimentos.length">
                            <td :colspan="temFilial ? 7 : 6" class="p-0">
                                <table class="table mb-0">
                                    <thead>
                                    <tr class="bg-light">
                                        <th style="width: 40px;">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input"
                                                       :id="'checkTreinamento' + indexFunc"
                                                       @change="selecionarTodosTreinamentos(funcionario.id, $event.target.checked)"
                                                       :checked="todosTreinamentosSelecionados(funcionario.id)">
                                                <label class="custom-control-label"
                                                       :for="'checkTreinamento' + indexFunc"></label>
                                            </div>
                                        </th>
                                        <th style="width: 40px;">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input"
                                                       :id="'checkTreinamentoMassa' + indexFunc"
                                                       @change="selecionarTodosTreinamentosMassa(funcionario.id, $event.target.checked)"
                                                       :checked="todosTreinamentosMassaSelecionados(funcionario.id)">
                                                <label class="custom-control-label"
                                                       :for="'checkTreinamentoMassa' + indexFunc"></label>
                                            </div>
                                        </th>
                                        <th>Treinamento</th>
                                        <th>Data Treinamento</th>
                                        <th>Data Vencimento</th>
                                        <th>Status</th>
                                        <th style="width: 70px;">Ações</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <!-- Loop pelos treinamentos de cada funcionário -->
                                     <template v-if="isColunaTreinamentoSelecionada(vencimento)">
                                        <tr v-for="(vencimento, indexTr) in funcionario.treinamento.vencimentos"
                                            :key="'tr-' + funcionario.id + '-' + indexTr"
                                            :class="obterClasseTreinamento(vencimento)"
                                        >
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        :id="'checkTr' + funcionario.id + '-' + vencimento.id"
                                                        v-model="selecionados"
                                                        :value="funcionario.id"
                                                        :disabled="!vencimento.pivot.data_treinamento">
                                                    <label class="custom-control-label"
                                                        :for="'checkTr' + funcionario.id + '-' + vencimento.id"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        :id="'checkTrMassa' + funcionario.id + '-' + vencimento.id"
                                                        v-model="selecionadosMassa"
                                                        :value="funcionario.id">
                                                    <label class="custom-control-label"
                                                        :for="'checkTrMassa' + funcionario.id + '-' + vencimento.id"></label>
                                                </div>
                                            </td>
                                            <td>{{ vencimento.label }}</td>
                                            <td>{{ vencimento.pivot.data_treinamento || '-' }}</td>
                                            <td :class="obterClasseVencimento(vencimento)">
                                                {{ vencimento.pivot.data_vencimento || '-' }}
                                            </td>
                                            <td>
                            <span class="badge" :class="obterClasseBadge(vencimento)">
                                {{ obterTextoStatus(vencimento) }}
                            </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-secondary" title="Visualizar"
                                                            @click="visualizarTreinamento(funcionario.id)">
                                                        <i class="far fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-secondary" title="Editar"
                                                            @click="editarTreinamento(funcionario.id)">
                                                        <i class="far fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TabelaFuncionarios',
    props: {
        funcionarios: {
            type: Array,
            required: true
        },
        temFilial: {
            type: Boolean,
            default: false
        },
        listaColunasAtivas: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            selecionados: [],
            selecionadosMassa: []
        };
    },
    computed: {
        todosItensSelecionados() {
            if (this.funcionarios.length === 0) return false;

            const funcionariosComTreinamento = this.funcionarios.filter(
                funcionario => funcionario.treinamento && funcionario.treinamento.vencimentos.length > 0
            );

            if (funcionariosComTreinamento.length === 0) return false;

            return funcionariosComTreinamento.every(funcionario => this.selecionados.includes(funcionario.id));
        },

        todosItensMassaSelecionados() {
            if (this.funcionarios.length === 0) return false;
            return this.funcionarios.every(funcionario => this.selecionadosMassa.includes(funcionario.id));
        }
    },
    methods: {
        // Métodos utilitários
        obterIniciais(nome) {
            if (!nome) return '';
            return nome.split(' ')
                .map(n => n.charAt(0))
                .join('')
                .substring(0, 2)
                .toUpperCase();
        },

        // Métodos para controle de seleção
        selecionarTodos() {
            if (this.todosItensSelecionados) {
                this.selecionados = [];
            } else {
                this.selecionados = this.funcionarios
                    .filter(funcionario => funcionario.treinamento && funcionario.treinamento.vencimentos.length > 0)
                    .map(funcionario => funcionario.id);
            }
            this.$emit('selecao-atualizada', this.selecionados);
        },

        selecionarTodosMassa() {
            if (this.todosItensMassaSelecionados) {
                this.selecionadosMassa = [];
            } else {
                this.selecionadosMassa = this.funcionarios.map(funcionario => funcionario.id);
            }
            this.$emit('selecao-massa-atualizada', this.selecionadosMassa);
        },

        todosTreinamentosSelecionados(funcionarioId) {
            return this.selecionados.includes(funcionarioId);
        },

        todosTreinamentosMassaSelecionados(funcionarioId) {
            return this.selecionadosMassa.includes(funcionarioId);
        },

        selecionarTodosTreinamentos(funcionarioId, selecionado) {
            if (selecionado) {
                if (!this.selecionados.includes(funcionarioId)) {
                    this.selecionados.push(funcionarioId);
                }
            } else {
                const index = this.selecionados.indexOf(funcionarioId);
                if (index !== -1) {
                    this.selecionados.splice(index, 1);
                }
            }
            this.$emit('selecao-atualizada', this.selecionados);
        },

        selecionarTodosTreinamentosMassa(funcionarioId, selecionado) {
            if (selecionado) {
                if (!this.selecionadosMassa.includes(funcionarioId)) {
                    this.selecionadosMassa.push(funcionarioId);
                }
            } else {
                const index = this.selecionadosMassa.indexOf(funcionarioId);
                if (index !== -1) {
                    this.selecionadosMassa.splice(index, 1);
                }
            }
            this.$emit('selecao-massa-atualizada', this.selecionadosMassa);
        },

        // Métodos para status e classes visuais
        obterClasseTreinamento(vencimento) {
            if (!vencimento.pivot.data_treinamento) return 'border-left-secondary';

            const status = this.obterStatusTreinamento(vencimento);

            switch (status) {
                case 'ativo':
                    return 'border-left-success';
                case 'avencer':
                    return 'border-left-warning';
                case 'vencido':
                    return 'border-left-danger';
                default:
                    return 'border-left-secondary';
            }
        },

        obterClasseVencimento(vencimento) {
            if (!vencimento.pivot.data_vencimento) return '';

            const status = this.obterStatusTreinamento(vencimento);

            switch (status) {
                case 'ativo':
                    return 'text-success';
                case 'avencer':
                    return 'text-warning';
                case 'vencido':
                    return 'text-danger';
                default:
                    return '';
            }
        },

        obterClasseBadge(vencimento) {
            if (!vencimento.pivot.data_treinamento) return 'badge-secondary';

            const status = this.obterStatusTreinamento(vencimento);

            switch (status) {
                case 'ativo':
                    return 'badge-success';
                case 'avencer':
                    return 'badge-warning';
                case 'vencido':
                    return 'badge-danger';
                default:
                    return 'badge-secondary';
            }
        },

        obterStatusTreinamento(vencimento) {
            if (!vencimento.pivot.data_treinamento) return 'inativo';
            if (!vencimento.pivot.data_vencimento) return 'ativo';

            const hoje = new Date();

            // Converter data no formato DD/MM/YYYY para objeto Date
            let dataVencimento;
            if (vencimento.pivot.data_vencimento) {
                const partes = vencimento.pivot.data_vencimento.split('/');
                if (partes.length === 3) {
                    dataVencimento = new Date(partes[2], partes[1] - 1, partes[0]);
                } else {
                    return 'ativo'; // Formato inválido
                }
            } else {
                return 'ativo'; // Sem data de vencimento
            }

            // Verificar se já está vencido
            if (dataVencimento < hoje) {
                return 'vencido';
            }

            // Verificar se está próximo do vencimento (30 dias)
            const trintaDiasAFrente = new Date();
            trintaDiasAFrente.setDate(hoje.getDate() + 30);

            if (dataVencimento <= trintaDiasAFrente) {
                return 'avencer';
            }

            return 'ativo';
        },

        obterTextoStatus(vencimento) {
            if (!vencimento.pivot.data_treinamento) {
                return 'Não Realizado';
            }

            const status = this.obterStatusTreinamento(vencimento);

            switch (status) {
                case 'ativo':
                    return 'Válido';
                case 'avencer':
                    return 'A Vencer';
                case 'vencido':
                    return 'Vencido';
                default:
                    return 'Desconhecido';
            }
        },

        // Métodos para filtrar colunas
        isColunaTreinamentoSelecionada(vencimento) {
            return this.listaColunasAtivas.length === 0 ||
                this.listaColunasAtivas.some(col => col.id === vencimento.id && col.checked);
        },

        // Métodos de ação
        visualizarTreinamento(funcionarioId) {
            this.$emit('visualizar-treinamento', funcionarioId);
        },

        editarTreinamento(funcionarioId) {
            this.$emit('editar-treinamento', funcionarioId);
        }
    },
    watch: {
        selecionados(novoValor) {
            this.$emit('selecao-atualizada', novoValor);
        },
        selecionadosMassa(novoValor) {
            this.$emit('selecao-massa-atualizada', novoValor);
        }
    }
};
</script>

<style scoped>
/* Avatar para identificação rápida do funcionário */
.avatar-circle {
    width: 40px;
    height: 40px;
    background-color: #007bff;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-weight: bold;
}

.avatar-initials {
    font-size: 1rem;
}

/* Bordas coloridas para status */
.border-left-success {
    border-left: 4px solid #48bb78 !important;
}

.border-left-warning {
    border-left: 4px solid #f6ad55 !important;
}

.border-left-danger {
    border-left: 4px solid #f56565 !important;
}

.border-left-secondary {
    border-left: 4px solid #a0aec0 !important;
}

/* Cores de texto para status */
.text-success {
    color: #48bb78 !important;
}

.text-warning {
    color: #ed8936 !important;
}

.text-danger {
    color: #e53e3e !important;
}

/* Ajuste para tabela dentro de tabela */
table .table {
    margin-bottom: 0;
}
</style>
