<template>
    <div class="card">
        <div class="card-header text-center">
            <img src="https://sistema.mybp.com.br/images/bpin_mybp_color.svg" class="" alt="logo_bpse" style="height: 100px" />
            <h4 class="text-center">Recuperação de Senha</h4>
        </div>
        <div class="card-body">
            <preload v-if="preload"></preload>
            <form v-if="!preload" @submit.prevent="validatePassword">
                <div class="alert alert-success small mb-3" v-if="tokenProp">
                    <i class="fa fa-shield-alt"></i> Link de segurança validado. Defina sua nova senha abaixo.
                </div>
                <div class="alert alert-info">
                    <h6><i class="fa fa-info-circle"></i> Requisitos para senha segura:</h6>
                    <ul class="mb-0 small">
                        <li>Mínimo de 8 caracteres</li>
                        <li>Pelo menos 1 letra minúscula (a-z)</li>
                        <li>Pelo menos 1 letra maiúscula (A-Z)</li>
                        <li>Pelo menos 1 número (0-9)</li>
                        <li>Pelo menos 1 caractere especial (@$!%*?&)</li>
                    </ul>
                </div>

                <div class="mb-3">
                    <label for="novaSenha" class="form-label">Nova Senha:</label>
                    <div class="input-group">
                        <input
                            :type="revealPassword ? 'text' : 'password'"
                            class="form-control"
                            v-model="novaSenha"
                            autocomplete="new-password"
                            placeholder="Digite a nova senha (min. 8 caracteres)"
                            @input="checkPasswordStrength"
                            required
                        />
                        <button type="button" class="btn btn-outline-secondary reveal-password-btn" @click="togglePasswordVisibility">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <div>
                        {{ passwordStrength }}
                        <div class="password-level-indicator" :class="'level-' + passwordLevel"></div>
                        <div class="password-hints mt-2" v-if="passwordHints.length > 0">
                            <div v-for="(hint, index) in passwordHints" :key="hint.id || index" class="text-danger small">
                                <i class="fa fa-times-circle"></i> {{ hint }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-block btn-primary" :disabled="isPasswordWeak || !tokenProp">Redefinir Senha</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RecuperaSenha',
    props: {
        token: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            tokenProp: this.token || '',
            novaSenha: '',
            confirmarSenha: '',
            passwordStrength: '',
            passwordLevel: 0,
            passwordHints: [],
            revealPassword: false,
            preload: false
        }
    },
    computed: {
        isPasswordWeak() {
            return this.passwordHints.length > 0 || !this.novaSenha
        }
    },
    mounted() {
        if (!this.tokenProp && typeof window !== 'undefined') {
            const parts = window.location.pathname.split('/')
            const last = parts[parts.length - 1] || ''
            if (last.length >= 32) {
                this.tokenProp = last
            }
        }
    },
    methods: {
        validatePassword() {
            this.preload = true
            const form = {
                token: this.tokenProp,
                novaSenha: this.novaSenha
            }

            axios
                .post(`${URL_SITE}/envia-recupera-senha`, form)
                .then(() => {
                    mostraSucesso('', 'Senha alterada com sucesso! Faça login com a nova senha.')
                    window.location.href = `${URL_ADMIN}/login`
                })
                .catch(() => {
                    this.preload = false
                })
            return true
        },

        checkPasswordStrength() {
            const password = this.novaSenha
            let strength = 0
            const hints = []

            const hasMinLength = password.length >= 8
            const hasLowerCase = /[a-z]/.test(password)
            const hasUpperCase = /[A-Z]/.test(password)
            const hasNumbers = /\d/.test(password)
            const hasSpecialChars = /[@$!%*?&]/.test(password)

            if (hasMinLength) strength++
            if (hasLowerCase) strength++
            if (hasUpperCase) strength++
            if (hasNumbers) strength++
            if (hasSpecialChars) strength++

            if (!hasMinLength) hints.push('Deve ter pelo menos 8 caracteres')
            if (!hasLowerCase) hints.push('Deve conter pelo menos 1 letra minúscula')
            if (!hasUpperCase) hints.push('Deve conter pelo menos 1 letra maiúscula')
            if (!hasNumbers) hints.push('Deve conter pelo menos 1 número')
            if (!hasSpecialChars) hints.push('Deve conter pelo menos 1 caractere especial (@$!%*?&)')

            this.passwordHints = hints

            if (strength <= 2) {
                this.updatePasswordStrength('Muito Fraca', 1)
            } else if (strength === 3) {
                this.updatePasswordStrength('Fraca', 2)
            } else if (strength === 4) {
                this.updatePasswordStrength('Moderada', 3)
            } else if (strength === 5) {
                this.updatePasswordStrength('Forte', 4)
            }
        },
        updatePasswordStrength(message, level) {
            this.passwordStrength = 'Força da Senha: ' + message
            this.passwordLevel = level
        },
        togglePasswordVisibility() {
            this.revealPassword = !this.revealPassword
        }
    }
}
</script>

<style>
.password-level-indicator {
    height: 10px;
    margin-top: 10px;
}

.level-1 {
    background-color: red;
}

.level-2 {
    background-color: orange;
}

.level-3 {
    background-color: yellow;
}

.level-4 {
    background-color: lightgreen;
}

.level-5 {
    background-color: green;
}

.password-hints {
    font-size: 14px;
    margin-top: 10px;
}

.reveal-password-btn {
    cursor: pointer;
}

.alert {
    color: red;
    margin-top: 10px;
}

.alert-success {
    color: #0f5132;
}

.alert-info {
    color: #055160;
}
</style>
