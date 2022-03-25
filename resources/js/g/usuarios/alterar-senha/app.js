const app = new Vue({
    el: "#app",
    data: {
        preloadAjax: false,
        form: {
            password: "",
            password_confirmation: ""
        }
    },

    methods: {
        alterar() {
            if (this.form.password.length === 0) {
                mostraErro("", "Informe sua nova senha");
                return false;
            }
            if (this.form.password.length > 0) {
                if (this.form.password.length < 6) {
                    mostraErro("", "A senha deve ter no mínimo 6 caracteres");
                    return false;
                }
                if (this.form.password !== this.form.password_confirmation) {
                    mostraErro("", "As senhas não conferem");
                    return false;
                }
            }

            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/alterar-senha`, this.form).then((data) => {
                this.preloadAjax = false;
                mostraSucesso("", "Senha alterada com sucesso");
            }).catch((error) => {
                this.preloadAjax = false;
            });
        }


    }
});
