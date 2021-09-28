
const app = new Vue({
    el: '#app',
    data: {
        preloadAjax: false,
        ativo: false,
    },
    mounted: function () {
        this.preloadAjax = true;
        $.get(URL_ADMIN+'/horario-acesso/init').done( (data) => {

            this.preloadAjax = false;
            $('#abertura').val(data.abertura);
            $('#fechamento').val(data.fechamento);
            this.ativo = data.ativo;

        }).fail((data)=>{
            this.preloadAjax = false;
        });
    },
    methods: {
        alterarHorario: function () {

            $(':input').trigger('blur');
            if($(':input.is-invalid').length){
                alert('Verificar os erros');
                return false;
            }

            var dados = {};
            dados.abertura = $('#abertura').val();
            dados.fechamento = $('#fechamento').val();
            dados._method = 'PUT';
            this.preloadAjax = true;

            $.post(URL_ADMIN+'/horario-acesso', dados).done((data)=> {
                this.preloadAjax = false;
                $('#abertura').val(data.abertura);
                $('#fechamento').val(data.fechamento);
                this.ativo = data.ativo;

                mostraSucesso('Horário alterado com sucesso');
            }).fail((data)=>{
                this.preloadAjax = false;
            });
        },
        ativaDesativa: function () {

            var dados = {};
            dados._method = 'PUT';
            this.preloadAjax = true;

            $.post(URL_ADMIN+'/horario-acesso/ativa-desativa', dados).done((data) => {

                this.preloadAjax = false;
                this.ativo = data.ativo;
            }).fail((data)=>{
                this.preloadAjax = false;
            });
        }


    }
});