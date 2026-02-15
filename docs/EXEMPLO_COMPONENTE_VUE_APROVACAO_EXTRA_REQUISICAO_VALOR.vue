<!-- 
    Exemplo de Componente Vue para Aprovação Extra
    Este é um exemplo de como implementar o botão e modal de aprovação extra
    em componentes Vue que já têm aprovação de gestor/RH
-->

<template>
    <div>
        <!-- Botão de Aprovação Extra (exibir apenas se tiver configuração ativa) -->
        <b-button 
            v-if="temAprovacaoExtra && podeAprovarExtra && item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra"
            variant="warning" 
            size="sm"
            @click="abrirModalAprovacaoExtra(item)"
        >
            <i class="fas fa-check-circle"></i>
            Aprovar - {{ nomeAprovacaoExtra }}
        </b-button>

        <!-- Badge de Status da Aprovação Extra -->
        <b-badge 
            v-if="item.status_aprovacao_extra === 'aprovado'" 
            variant="success"
        >
            <i class="fas fa-check-circle"></i>
            {{ nomeAprovacaoExtra }}: Aprovado
        </b-badge>

        <b-badge 
            v-if="item.status_aprovacao_extra === 'reprovado'" 
            variant="danger"
        >
            <i class="fas fa-times-circle"></i>
            {{ nomeAprovacaoExtra }}: Reprovado
        </b-badge>

        <!-- Modal de Aprovação Extra -->
        <b-modal 
            id="modal-aprovacao-extra" 
            ref="modalAprovacaoExtra"
            title="Aprovação Extra"
            size="lg"
            @ok="aprovarExtra"
        >
            <div v-if="itemSelecionado">
                <h5>{{ nomeAprovacaoExtra }}</h5>
                
                <!-- Informações do item -->
                <div class="mb-3">
                    <strong>Solicitação #{{ itemSelecionado.id }}</strong>
                    <p>Solicitante: {{ itemSelecionado.user_cadastrou?.nome || itemSelecionado.solicitante }}</p>
                    <p>Data: {{ itemSelecionado.created_at }}</p>
                </div>

                <!-- Histórico de Aprovações -->
                <div class="mb-3">
                    <h6>Histórico</h6>
                    <div v-if="itemSelecionado.status_aprovacao">
                        <b-badge variant="success">
                            Aprovado por Gestor: {{ itemSelecionado.user_aprovacao?.nome || 'N/A' }}
                        </b-badge>
                        <small class="d-block text-muted">
                            {{ itemSelecionado.data_aprovacao }}
                        </small>
                    </div>
                </div>

                <!-- Formulário de Aprovação -->
                <b-form-group label="Decisão">
                    <b-form-radio-group
                        v-model="formAprovacaoExtra.status_aprovacao_extra"
                        :options="[
                            { text: 'Aprovar', value: 'aprovado' },
                            { text: 'Reprovar', value: 'reprovado' }
                        ]"
                    />
                </b-form-group>

                <b-form-group label="Observações">
                    <b-form-textarea
                        v-model="formAprovacaoExtra.obs_aprovacao_extra"
                        rows="3"
                        placeholder="Digite suas observações (opcional)"
                    />
                </b-form-group>
            </div>

            <template #modal-footer="{ ok, cancel }">
                <b-button variant="secondary" @click="cancel()">
                    Cancelar
                </b-button>
                <b-button 
                    variant="primary" 
                    @click="ok()"
                    :disabled="!formAprovacaoExtra.status_aprovacao_extra"
                >
                    Confirmar
                </b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
export default {
    data() {
        return {
            // Dados da listagem
            dados: [],
            
            // Configuração de Aprovação Extra (vem do backend)
            podeAprovarExtra: false,
            temAprovacaoExtra: false,
            nomeAprovacaoExtra: '',
            
            // Item selecionado para aprovação
            itemSelecionado: null,
            
            // Formulário de aprovação extra
            formAprovacaoExtra: {
                id: null,
                status_aprovacao_extra: '',
                obs_aprovacao_extra: ''
            }
        }
    },
    
    mounted() {
        this.atualizar()
    },
    
    methods: {
        /**
         * Carrega dados da API
         */
        atualizar() {
            // Exemplo para RequisicaoVaga
            axios.post('/planejamento/requisicao-vaga/atualizar', {
                pages: this.pagina,
                // ... outros filtros
            }).then(response => {
                this.dados = response.data.dados.itens
                
                // Configuração de aprovação extra
                this.podeAprovarExtra = response.data.dados.pode_aprovar_extra || false
                this.temAprovacaoExtra = response.data.dados.tem_aprovacao_extra || false
                this.nomeAprovacaoExtra = response.data.dados.nome_aprovacao_extra || 'Aprovação Extra'
                
                this.pagina = response.data.atual
            }).catch(error => {
                this.$swal({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao carregar dados'
                })
            })
        },
        
        /**
         * Abre modal de aprovação extra
         */
        abrirModalAprovacaoExtra(item) {
            this.itemSelecionado = item
            this.formAprovacaoExtra = {
                id: item.id,
                status_aprovacao_extra: '',
                obs_aprovacao_extra: ''
            }
            this.$refs.modalAprovacaoExtra.show()
        },
        
        /**
         * Envia aprovação extra para API
         */
        aprovarExtra() {
            if (!this.formAprovacaoExtra.status_aprovacao_extra) {
                this.$swal({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Selecione aprovar ou reprovar'
                })
                return
            }
            
            // Exemplo para RequisicaoVaga
            const url = `/planejamento/requisicao-vaga/${this.formAprovacaoExtra.id}/aprovarextra`
            
            // Para ValorExtraPrevista, use:
            // const url = `/planejamento/movimentacao/valor-extra-prevista/${this.formAprovacaoExtra.id}/aprovarextra`
            
            axios.put(url, this.formAprovacaoExtra)
                .then(response => {
                    this.$swal({
                        icon: 'success',
                        title: 'Sucesso',
                        text: `Solicitação ${this.formAprovacaoExtra.status_aprovacao_extra === 'aprovado' ? 'aprovada' : 'reprovada'} com sucesso!`
                    })
                    
                    this.$refs.modalAprovacaoExtra.hide()
                    this.atualizar()
                })
                .catch(error => {
                    const mensagem = error.response?.data?.msg || 'Erro ao processar aprovação'
                    
                    this.$swal({
                        icon: 'error',
                        title: 'Erro',
                        text: mensagem
                    })
                })
        }
    }
}
</script>

<style scoped>
/* Estilos opcionais */
.badge {
    margin-left: 5px;
}
</style>
