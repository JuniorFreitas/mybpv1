import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'

const app = createApp({
    data() {
        return {
            preloadAjax: false,
            ativo: false
        }
    },
    mounted: function () {
        this.preloadAjax = true
        $.get(`${URL_ADMIN}/manutencao-programada/init`)
            .done((data) => {
                this.preloadAjax = false
                $('#data').val(data.data)
                $('#hora').val(data.hora)
                this.ativo = data.ativo
                setupCampo()
            })
            .fail((data) => {
                this.preloadAjax = false
            })
    },
    methods: {
        ativaDesativa: function () {
            $(':input').trigger('blur')
            if ($(':input.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            var dados = {}
            dados.datahora = $('#data').val() + ' ' + $('#hora').val()
            dados._method = 'PUT'
            this.preloadAjax = true

            $.post(`${URL_ADMIN}/manutencao-programada`, dados)
                .done((data) => {
                    this.preloadAjax = false
                    $('#data').val(data.data)
                    $('#hora').val(data.hora)
                    this.ativo = data.ativo
                    mostraSucesso('Alterado com sucesso')
                })
                .fail((data) => {
                    this.preloadAjax = false
                })
        }
    }
})

registerGlobals(app)
app.mount('#app')
