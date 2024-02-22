<template>
    <div class="card">
        <div class="card-header text-center">
            <img src="https://sistema.mybp.com.br/images/bpin_mybp_color.svg" class="" alt="logo_bpse"
                 style="height: 100px">
            <h4 class="text-center">Recuperação de Senha</h4>
        </div>
        <div class="card-body">
            <preload v-if="preload"></preload>
            <form v-if="!preload" @submit.prevent="validatePassword">
                <div class="mb-3">
                    <label class="form-label">CODIGO DE SEGURANÇA:</label>
                    <div class="input-group">
                        <input
                            v-for="(digit, index) in token"
                            :key="index"
                            ref="tokenInputs"
                            v-model="token[index]"
                            type="text"
                            class="form-control token-input"
                            autocomplete="off"
                            maxlength="1"
                            @input="moveToNextInput($event, index)"
                            @paste="handlePaste($event, index)"
                            required
                        />
                    </div>
                </div>
                <div class="mb-3">
                    <label for="novaSenha" class="form-label">Nova Senha:</label>
                    <div class="input-group">
                        <input
                            :type="revealPassword ? 'text' : 'password'"
                            class="form-control"
                            v-model="novaSenha"
                            autocomplete="off"
                            placeholder="Digite a nova senha"
                            @input="checkPasswordStrength"
                            required
                        />
                        <button type="button" class="btn btn-outline-secondary reveal-password-btn"
                                @click="togglePasswordVisibility">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <div>
                        {{ passwordStrength }}
                        <div class="password-level-indicator" :class="'level-' + passwordLevel"
                        ></div>
                        <!--                        <div class="password-hints" v-if="passwordHints.length > 0">-->
                        <!--                            <div v-for="(hint, index) in passwordHints" :key="index">{{ hint }}</div>-->
                        <!--                        </div>-->
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-block btn-primary"
                            :disabled="isPasswordWeak || !isTokenAllFilled">Redefinir Senha
                    </button>
                </div>
            </form>

        </div>
    </div>
</template>

<script>
export default {
    name: 'RecuperaSenha',
    data() {
        return {
            token: Array(6).fill(''),
            novaSenha: '',
            confirmarSenha: '',
            passwordStrength: '',
            passwordLevel: 0,
            passwordHints: [],
            revealPassword: false,
            preload: false
        };
    },
    computed: {
        isPasswordWeak() {
            return this.passwordLevel <= 1;
        },
        isTokenAllFilled() {
            return this.token.every((digit) => digit !== '');
        },
    },
    methods: {
        moveToNextInput(event, index) {
            if (event.inputType === 'deleteContentBackward' && index > 0) {
                this.$refs.tokenInputs[index - 1].focus();
            } else if (index < this.token.length - 1) {
                this.$refs.tokenInputs[index + 1].focus();
            }
        },
        handlePaste(event, index) {
            event.preventDefault();
            const pastedText = event.clipboardData.getData('text');
            for (let i = 0; i < pastedText.length && index + i < this.token.length; i++) {
                this.$set(this.token, index + i, pastedText[i]);
            }
            if (index + pastedText.length < this.token.length) {
                this.$refs.tokenInputs[index + pastedText.length].focus();
            }
        },
        validatePassword() {
            const password = this.novaSenha;
            this.preload = true;
            const form = {
                token: this.token.join(''),
                novaSenha: this.novaSenha,
            }

            axios.post(`${URL_SITE}/envia-recupera-senha`, form
            ).then(response => {
                mostraSucesso("", "Senha alterada com sucesso!");
                window.location.href = `${URL_ADMIN}/login`;
            }).catch(error => {
                this.preload = false;
            });
            return true;
        },

        checkPasswordStrength() {
            const password = this.novaSenha;
            let strength = 0;

            if (password.length >= 6) {
                strength++;
            } else {
                this.updatePasswordStrength('Muito Fraco', 0);
                return;
            }

            if (/\d/.test(password)) {
                strength++;
            } else {
                this.updatePasswordStrength('Fraco', 1);
            }

            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
                strength++;
            } else {
                this.updatePasswordStrength('Moderado', 2);
            }

            if (/[^a-zA-Z0-9]/.test(password)) {
                strength++;
            } else {
                this.updatePasswordStrength('Forte', 3);
            }

            if (password.length >= 10) {
                strength++;
            } else {
                this.updatePasswordStrength('Muito Forte', 4);
            }

            this.displayPasswordHints(password);
        },
        updatePasswordStrength(message, level) {
            this.passwordStrength = 'Força da Senha: ' + message;
            this.passwordLevel = level + 1;
        },
        displayPasswordHints(password) {
            const hints = [];

            if (password.length < 8) {
                hints.push('A senha deve ter pelo menos 8 caracteres.');
            }

            if (!/\d/.test(password)) {
                hints.push('Inclua pelo menos um número na senha.');
            }

            if (!/[a-z]/.test(password) || !/[A-Z]/.test(password)) {
                hints.push('Inclua pelo menos uma letra maiúscula e uma letra minúscula na senha.');
            }

            if (!/[^a-zA-Z0-9]/.test(password)) {
                hints.push('Inclua pelo menos um caractere especial na senha.');
            }

            this.passwordHints = hints;
        },
        togglePasswordVisibility() {
            this.revealPassword = !this.revealPassword;
        },
        checkPasswordMatch() {
            const password = this.novaSenha;
            const confirmPassword = this.confirmarSenha;

            if (password !== confirmPassword) {
                this.showAlert('As senhas não coincidem.');
            }
        },
    },
};
</script>

<style>
.token-input {
    width: 2rem;
    height: 4rem;
    font-size: 1.8rem;
    text-align: center;
}

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
</style>
