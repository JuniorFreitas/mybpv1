import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
const abrirModal = (selector) => {
    if (typeof $ === 'undefined') return
    $(selector).modal('show')
}

const fecharModal = (selector) => {
    if (typeof $ === 'undefined') return
    $(selector).modal('hide')
}



const app = createApp({
    data() {
        return {
            preloadAjax: false,
            form: {
                password: '',
                password_confirmation: ''
            },
            passwordStrength: '',
            passwordLevel: 0,
            passwordHints: [],
            showNewPassword: false,
            showConfirmPassword: false
        }
    },

    computed: {
        isPasswordWeak() {
            // A senha é considerada fraca se não atende a todos os critérios
            return this.passwordHints.length > 0
        },
        passwordsMatch() {
            return this.form.password === this.form.password_confirmation
        }
    },

    methods: {
        checkPasswordStrength() {
            const password = this.form.password
            let strength = 0
            const hints = []

            // Verificar critérios específicos
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

            // Adicionar dicas para critérios não atendidos
            if (!hasMinLength) hints.push('Deve ter pelo menos 8 caracteres')
            if (!hasLowerCase) hints.push('Deve conter pelo menos 1 letra minúscula')
            if (!hasUpperCase) hints.push('Deve conter pelo menos 1 letra maiúscula')
            if (!hasNumbers) hints.push('Deve conter pelo menos 1 número')
            if (!hasSpecialChars) hints.push('Deve conter pelo menos 1 caractere especial (@$!%*?&)')

            this.passwordHints = hints

            // Definir força da senha
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

        togglePasswordVisibility(field) {
            if (field === 'new') {
                this.showNewPassword = !this.showNewPassword
            } else if (field === 'confirm') {
                this.showConfirmPassword = !this.showConfirmPassword
            }
        },

        alterar() {
            // Validação da nova senha
            if (this.form.password.length === 0) {
                mostraErro('', 'Informe sua nova senha')
                return false
            }

            // Verificar se atende aos critérios de segurança
            if (this.isPasswordWeak) {
                mostraErro('', 'A nova senha não atende aos critérios de segurança')
                return false
            }

            // Verificar se as senhas conferem
            if (!this.passwordsMatch) {
                mostraErro('', 'As senhas não conferem')
                return false
            }

            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/alterar-senha`, this.form)
                .then((data) => {
                    this.preloadAjax = false
                    mostraSucesso('', 'Senha alterada com sucesso')

                    // Limpar o formulário
                    this.form.password = ''
                    this.form.password_confirmation = ''
                    this.passwordStrength = ''
                    this.passwordHints = []

                    // Fechar a modal (apenas se não for obrigatório)
                    fecharModal('#modalAlterarSenha')

                    // Se a senha era obrigatória, recarregar a página para atualizar o estado
                    if (window.location.href.includes('alterar-senha')) {
                        window.location.href = `${URL_ADMIN}/dashboard`
                    }
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        }
    },

    watch: {
        'form.password': function (newPassword) {
            this.checkPasswordStrength()
        }
    }
})

registerGlobals(app)
app.mount('#app')
