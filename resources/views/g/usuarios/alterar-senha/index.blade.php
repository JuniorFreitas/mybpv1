@extends('layouts.sistema')
@section('title', 'Alterar senha de acesso')
@section('content')

    <!-- Modal de Alteração de Senha -->
    <div class="modal fade" id="modalAlterarSenha" tabindex="-1" role="dialog" aria-labelledby="modalAlterarSenhaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalAlterarSenhaLabel">
                        <i class="fas fa-key mr-2"></i> Alterar Senha de Acesso
                        @if(auth()->user()->needsPasswordReset())
                            <span class="badge badge-warning ml-2">Obrigatório</span>
                        @endif
                    </h5>
                    @if(!auth()->user()->needsPasswordReset())
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    @endif
                </div>
                <div class="modal-body">
                    @if(auth()->user()->needsPasswordReset())
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle mr-1"></i> Alteração de senha obrigatória!</h6>
                            {{ auth()->user()->getPasswordResetReason() }}
                            @if(auth()->user()->password_changed_at && !auth()->user()->isFirstAccess())
                                <br><small>Última alteração: {{ auth()->user()->password_changed_at->format('d/m/Y H:i') }}</small>
                            @endif
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle mr-1"></i> Requisitos para senha segura:</h6>
                        <ul class="mb-0 small">
                            <li>Mínimo de 8 caracteres</li>
                            <li>Pelo menos 1 letra minúscula (a-z)</li>
                            <li>Pelo menos 1 letra maiúscula (A-Z)</li>
                            <li>Pelo menos 1 número (0-9)</li>
                            <li>Pelo menos 1 caractere especial (@$!%*?&)</li>
                        </ul>
                    </div>

                    <form>
                        <div class="row">
                            <div class="col-12">

                                <div class="form-group">
                                    <label>Nova Senha <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input :type="showNewPassword ? 'text' : 'password'" 
                                               class="form-control" 
                                               v-model="form.password" 
                                               placeholder="Digite sua nova senha" 
                                               autocomplete="new-password"
                                               :disabled="preloadAjax"
                                               @input="checkPasswordStrength">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" @click="togglePasswordVisibility('new')">
                                                <i :class="showNewPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Indicador de força da senha -->
                                    <div v-if="form.password.length > 0" class="mt-2">
                                        <div class="mb-1">
                                            <small :class="passwordLevel <= 2 ? 'text-danger' : passwordLevel === 3 ? 'text-warning' : 'text-success'">
                                                @{{ passwordStrength }}
                                            </small>
                                        </div>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" 
                                                 :class="passwordLevel <= 2 ? 'bg-danger' : passwordLevel === 3 ? 'bg-warning' : 'bg-success'"
                                                 :style="{width: (passwordLevel * 20) + '%'}"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Dicas de senha -->
                                    <div v-if="passwordHints.length > 0" class="mt-2">
                                        <div v-for="(hint, index) in passwordHints" :key="index" class="text-danger small">
                                            <i class="fas fa-times-circle"></i> @{{ hint }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Confirmar Nova Senha <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input :type="showConfirmPassword ? 'text' : 'password'" 
                                               class="form-control" 
                                               v-model="form.password_confirmation" 
                                               placeholder="Confirme sua nova senha"
                                               autocomplete="new-password"
                                               :disabled="preloadAjax">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" @click="togglePasswordVisibility('confirm')">
                                                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Indicador de confirmação -->
                                    <div v-if="form.password_confirmation.length > 0" class="mt-1">
                                        <small :class="passwordsMatch ? 'text-success' : 'text-danger'">
                                            <i :class="passwordsMatch ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                                            @{{ passwordsMatch ? 'Senhas conferem' : 'Senhas não conferem' }}
                                        </small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    @if(!auth()->user()->needsPasswordReset())
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </button>
                    @endif
                    <button type="button" 
                            class="btn btn-primary btn-sm" 
                            :disabled="preloadAjax || isPasswordWeak || !passwordsMatch" 
                            @click="alterar()">
                        <i class="fas fa-key mr-1"></i> 
                        <span v-if="preloadAjax">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Alterando...
                        </span>
                        <span v-else>Alterar senha</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo do Dashboard -->
    <div class="row">
        <div class="col-12">
            <!-- Política de Senhas Seguras -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title text-white mb-0">
                        <i class="fas fa-shield-alt mr-2"></i> Política de Senhas Seguras
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Para proteger sua conta e os dados da empresa, nossa política de segurança exige que as senhas atendam aos seguintes critérios de segurança:
                    </p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-light border">
                                <h5 class="text-primary mb-3"><i class="fas fa-check-circle mr-2"></i> Requisitos Obrigatórios</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-arrow-right text-muted mr-2"></i> <strong>8 ou mais caracteres</strong> - Quanto maior, mais segura</li>
                                    <li class="mb-2"><i class="fas fa-arrow-right text-muted mr-2"></i> <strong>1 letra minúscula</strong> (a, b, c, etc.)</li>
                                    <li class="mb-2"><i class="fas fa-arrow-right text-muted mr-2"></i> <strong>1 letra MAIÚSCULA</strong> (A, B, C, etc.)</li>
                                    <li class="mb-2"><i class="fas fa-arrow-right text-muted mr-2"></i> <strong>1 número</strong> (0, 1, 2, etc.)</li>
                                    <li class="mb-0"><i class="fas fa-arrow-right text-muted mr-2"></i> <strong>1 símbolo especial</strong> (@$!%*?&)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success border-success">
                                <h6 class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i> Dicas Importantes:</h6>
                                <ul class="small text-muted mb-0">
                                    <li>Não reutilize sua senha atual</li>
                                    <li>Evite informações pessoais (nome, data de nascimento)</li>
                                    <li>Não use sequências simples (123456, abcdef)</li>
                                    <li>Use uma combinação única e memorável</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h5 class="text-info mb-2"><i class="fas fa-question-circle mr-2"></i> Por que essas regras são importantes?</h5>
                        <p class="mb-2">
                            <strong>Senhas seguras protegem:</strong> Seus dados pessoais, informações da empresa e previnem acessos não autorizados. 
                            Uma senha forte com diferentes tipos de caracteres é exponencialmente mais difícil de ser descoberta por ataques automatizados.
                        </p>
                        <p class="mb-0">
                            <em><i class="fas fa-clock mr-1"></i> <strong>Fato interessante:</strong> Uma senha de 8 caracteres seguindo esses critérios levaria milhões de anos para ser quebrada por ataques automatizados!</em>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Configurações de Conta -->
            <div class="card mt-4">
                <div class="card-body">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAlterarSenha">
                        <i class="fas fa-key mr-2"></i> Alterar Minha Senha
                    </button>
                    <p class="text-muted mt-2 mb-0">
                        <small>Clique no botão acima para abrir o formulário de alteração de senha segura.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

@stop

@push('js')
    <script src="{{mix('js/g/alterar-senha/app.js')}}"></script>
    
    <!-- Script para abrir modal automaticamente se senha expirou -->
    @if(auth()->user()->needsPasswordReset())
        <script>
            $(document).ready(function() {
                $('#modalAlterarSenha').modal({
                    backdrop: 'static',  // Impede fechar clicando fora
                    keyboard: false      // Impede fechar com ESC
                }).modal('show');
            });
        </script>
    @endif
@endpush
