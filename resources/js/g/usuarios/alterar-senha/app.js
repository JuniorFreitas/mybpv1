import Kanban from "../../../components/Weekly-report";

const app = new Vue({
    el: '#app',
    data: {
        preloadAjax: false,
    },
    components:{
        Kanban
    },
    methods: {
        alterar: function () {

            $(':input').trigger('blur');
            if ($(':input.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }

            var dados = {};
            dados.password = $('#password').val();
            dados.password_confirmation = $('#password_confirmation').val();
            dados._method = 'PUT';
            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/alterar-senha`, dados).
            then((data) => {
                this.preloadAjax = false;
                mostraSucesso('', 'Senha alterada com sucesso');
            }).catch((error) => {
                this.preloadAjax = false;
            });
        }


    }
});
