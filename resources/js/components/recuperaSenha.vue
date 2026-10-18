<template>
    <div class="login-card">
        <div class="login-card__brand">
            <img src="/images/mybpin-logo-fundo-claro.webp" alt="MyBPIN" />
        </div>

        <div class="login-card__body">
            <preload v-if="preload"></preload>

            <template v-else>
                <h1 class="login-card__title">Definir nova senha</h1>
                <p class="login-card__subtitle">
                    Escolha uma senha forte para acessar o MyBPIN.
                </p>

                <form @submit.prevent="validatePassword" novalidate>
                    <ul class="recupera-checklist" aria-label="Requisitos da senha">
                        <li v-for="item in requisitos" :key="item.id" :class="{ 'is-ok': item.ok }">
                            <i :class="item.ok ? 'fas fa-check-circle' : 'far fa-circle'" aria-hidden="true"></i>
                            {{ item.label }}
                        </li>
                    </ul>

                    <div class="login-field">
                        <label for="novaSenha">Nova senha</label>
                        <div class="login-password">
                            <input
                                id="novaSenha"
                                ref="novaSenhaInput"
                                :type="revealPassword ? 'text' : 'password'"
                                class="form-control"
                                :value="novaSenha"
                                autocomplete="new-password"
                                placeholder="Crie uma senha segura"
                                @input="onNovaSenhaInput"
                                @change="onNovaSenhaInput"
                                required
                            />
                            <button
                                type="button"
                                class="login-password__toggle"
                                :aria-label="revealPassword ? 'Ocultar senha' : 'Mostrar senha'"
                                @click="revealPassword = !revealPassword"
                            >
                                <i :class="revealPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>

                        <div class="recupera-meter" aria-hidden="true">
                            <div
                                v-for="n in 4"
                                :key="'bar-' + n"
                                class="recupera-meter__bar"
                                :class="{ 'is-on': passwordLevel >= n, ['is-l' + passwordLevel]: passwordLevel >= n }"
                            ></div>
                        </div>
                        <p v-if="novaSenha" class="recupera-meter__label">{{ passwordStrength }}</p>
                    </div>

                    <div class="login-field">
                        <label for="confirmarSenha">Confirmar senha</label>
                        <div class="login-password">
                            <input
                                id="confirmarSenha"
                                ref="confirmarSenhaInput"
                                :type="revealConfirm ? 'text' : 'password'"
                                class="form-control"
                                :class="{ 'is-invalid': mostrarErroConfirmacao }"
                                :value="confirmarSenha"
                                autocomplete="new-password"
                                placeholder="Repita a nova senha"
                                @input="onConfirmarSenhaInput"
                                @change="onConfirmarSenhaInput"
                                @blur="confirmTouched = true"
                                required
                            />
                            <button
                                type="button"
                                class="login-password__toggle"
                                :aria-label="revealConfirm ? 'Ocultar confirmação' : 'Mostrar confirmação'"
                                @click="revealConfirm = !revealConfirm"
                            >
                                <i :class="revealConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        <p v-if="mostrarErroConfirmacao" class="recupera-hint-erro">
                            As senhas não coincidem.
                        </p>
                    </div>

                    <div class="login-actions">
                        <button type="submit" class="btn btn-primary" :disabled="!podeEnviar">
                            Salvar nova senha
                        </button>
                    </div>
                </form>

                <a class="login-footer-link" :href="urlLogin">Voltar ao login</a>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RecuperaSenha',
    props: {
        token: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            novaSenha: '',
            confirmarSenha: '',
            passwordStrength: '',
            passwordLevel: 0,
            revealPassword: false,
            revealConfirm: false,
            confirmTouched: false,
            preload: false,
            requisitos: [
                { id: 'len', label: 'Mínimo de 8 caracteres', ok: false },
                { id: 'lower', label: '1 letra minúscula (a–z)', ok: false },
                { id: 'upper', label: '1 letra maiúscula (A–Z)', ok: false },
                { id: 'num', label: '1 número (0–9)', ok: false },
                { id: 'special', label: '1 caractere especial (@$!%*?&)', ok: false },
            ],
        }
    },
    computed: {
        tokenValido() {
            return typeof this.token === 'string' && this.token.length >= 32
        },
        senhasIguais() {
            return this.novaSenha !== '' && this.novaSenha === this.confirmarSenha
        },
        mostrarErroConfirmacao() {
            return this.confirmTouched && this.confirmarSenha !== '' && !this.senhasIguais
        },
        requisitosOk() {
            return this.requisitos.every((r) => r.ok)
        },
        podeEnviar() {
            return this.tokenValido && this.requisitosOk && this.senhasIguais && !this.preload
        },
        urlLogin() {
            return typeof URL_ADMIN !== 'undefined' ? `${URL_ADMIN}/login` : '/g/login'
        },
    },
    methods: {
        valorDoEvento(event) {
            return event?.target?.value ?? ''
        },

        onNovaSenhaInput(event) {
            this.novaSenha = this.valorDoEvento(event)
            this.atualizarRequisitos()
        },

        onConfirmarSenhaInput(event) {
            this.confirmarSenha = this.valorDoEvento(event)
        },

        syncCamposDoDom() {
            const nova = this.$refs.novaSenhaInput?.value
            const conf = this.$refs.confirmarSenhaInput?.value
            if (typeof nova === 'string' && nova !== this.novaSenha) {
                this.novaSenha = nova
                this.atualizarRequisitos()
            }
            if (typeof conf === 'string' && conf !== this.confirmarSenha) {
                this.confirmarSenha = conf
            }
        },

        atualizarRequisitos() {
            const password = this.novaSenha
            const checks = {
                len: password.length >= 8,
                lower: /[a-z]/.test(password),
                upper: /[A-Z]/.test(password),
                num: /\d/.test(password),
                special: /[@$!%*?&]/.test(password),
            }

            this.requisitos = this.requisitos.map((r) => ({ ...r, ok: !!checks[r.id] }))

            const strength = Object.values(checks).filter(Boolean).length
            if (!password) {
                this.passwordLevel = 0
                this.passwordStrength = ''
            } else if (strength <= 2) {
                this.passwordLevel = 1
                this.passwordStrength = 'Força: muito fraca'
            } else if (strength === 3) {
                this.passwordLevel = 2
                this.passwordStrength = 'Força: fraca'
            } else if (strength === 4) {
                this.passwordLevel = 3
                this.passwordStrength = 'Força: moderada'
            } else {
                this.passwordLevel = 4
                this.passwordStrength = 'Força: forte'
            }
        },

        validatePassword() {
            this.confirmTouched = true
            this.syncCamposDoDom()

            if (!this.podeEnviar) {
                return
            }

            this.preload = true
            axios
                .post(`${URL_SITE}/envia-recupera-senha`, {
                    token: this.token,
                    novaSenha: this.novaSenha,
                })
                .then((response) => {
                    const redirect = response.data?.redirect
                    if (response.data?.mfa_required && redirect) {
                        mostraSucesso('', response.data.msg || 'Senha alterada. Continue a verificação.')
                        window.location.href = redirect
                        return
                    }
                    mostraSucesso('', response.data?.msg || 'Senha alterada com sucesso!')
                    window.location.href = this.urlLogin
                })
                .catch((error) => {
                    this.preload = false
                    if (error?.response?.status === 429) {
                        window.location.href = '/erro/muitas-tentativas'
                    }
                })
        },
    },
}
</script>

<style scoped>
.recupera-meter {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.35rem;
    margin-top: 0.65rem;
}

.recupera-meter__bar {
    height: 6px;
    border-radius: 999px;
    background: rgba(11, 27, 61, 0.1);
    transition: background 0.2s ease;
}

.recupera-meter__bar.is-on.is-l1 {
    background: #d64545;
}

.recupera-meter__bar.is-on.is-l2 {
    background: #e08a1e;
}

.recupera-meter__bar.is-on.is-l3 {
    background: #c9a227;
}

.recupera-meter__bar.is-on.is-l4 {
    background: #1b7a4a;
}

.recupera-meter__label {
    margin: 0.4rem 0 0;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--login-muted, #5b6b86);
}

.recupera-hint-erro {
    margin: 0.4rem 0 0;
    font-size: 0.8rem;
    font-weight: 600;
    color: #c0392b;
}

.recupera-checklist {
    list-style: none;
    margin: 0 0 1.15rem;
    padding: 0.85rem 1rem;
    border-radius: 0.85rem;
    background: var(--login-nevoa, #f4f6fa);
    border: 1px solid var(--login-line, rgba(11, 27, 61, 0.12));
}

.recupera-checklist li {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--login-muted, #5b6b86);
    line-height: 1.35;
}

.recupera-checklist li + li {
    margin-top: 0.4rem;
}

.recupera-checklist li i {
    width: 1rem;
    text-align: center;
}

.recupera-checklist li.is-ok {
    color: var(--login-ok, #1b7a4a);
}

.login-field .form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);
}
</style>
