<template>
    <div class="d-flex justify-content-center">
        <div class="form-inline py-2 d-flex justify-content-center">
            <label class="my-1 mr-2">Total
                encontrado(s): {{ total }} | Página</label>
            <select class="custom-select my-1 mr-sm-2" ref='pgAtual' style="text-align-last: center"
                    @change="irPagina">
                <option v-for="pag in ultima" :selected="pag == atual" :value="pag">{{ pag }}</option>
            </select>

            <button type='button' class='btn btn-primary rounded mr-2 ml-2' v-show="refresh" v-tippy content="Atualizar"
                    :disabled='carregando' @click='buscar'>
                <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
            </button>

            <button @click='voltar' :style='{ display: voltarAtivo }' :disabled='carregando' v-tippy content="Anterior"
                    class="btn btn-primary rounded text-white mr-2">
                <i class="fas fa-chevron-left"></i> <span class="d-md-none"></span>
            </button>

            <button class="btn btn-primary rounded text-white  mr-2" @click='avancar' v-tippy content="Próxima"
                    :style='{ display: avancarAtivo }'
                    :disabled='carregando'>
                <span class="d-md-none"></span> <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        url: {
            type: String,
            required: false,
            default: ''
        },
        porPagina: {
            type: Number | String,
            required: false,
            default: 20
        },

        refresh: {
            type: Boolean,
            required: false,
            default: true
        },

        dados: {
            type: Object,
            required: false,
            default: () => {
                return {};
            }
        },

    },
    data: function () {
        return {
            carregando: false,
            ultima: 0,
            atual: 1,
            total: 0
        }
    },
    computed: {
        avancarAtivo: function () {
            if (this.atual < this.ultima) {
                return "block";
            }
            return "none";
        },
        voltarAtivo: function () {
            if (this.atual > 1) {
                return "block";
            }
            return "none";
        }
    },
    methods: {
        voltar: function () {
            this.atual--;
            this.$emit('update:atual', this.atual);
            this.buscar();
        },
        avancar: function () {
            this.atual++;
            this.$emit('update:atual', this.atual);
            this.buscar();
        },

        irPagina: function () {
            var novoValor = this.$refs.pgAtual.value;
            if (this.atual == novoValor) {
                return false;
            }

            this.atual = this.$refs.pgAtual.value;

            if (this.atual == "" || 0) {
                this.atual = 1;
            }
            if (this.atual >= this.ultima) {
                this.atual = this.ultima;
            }
            //return false;
            //this.$emit('update:atual', this.atual);
            this.buscar();

        },

        async buscar() {
            if (this.carregando) {
                return false;
            }
            //console.log("Entrou "+this.atual);
            //this.$emit('update:atual', this.atual);
            this.$emit('carregando');
            this.carregando = true;

            let ref = this;

            //setTimeout(function () {

            //console.log("Depois: "+ref.atual);
            //ref.$emit('update:atual', ref.atual);
            let post = ref.dados;
            post.numero = ref.atual;
            post.page = ref.atual;// para o laravel
            //post._method='GET';
            post.porPagina = ref.porPagina;

            await axios.post(ref.url, post).then(response => {
                let data = response.data;
                //var data = $.parseJSON(data);
                //console.log(data);
                ref.total = data.total;
                //ref.ultima = Math.ceil(parseInt(data.total) / parseInt(ref.porPagina));
                ref.ultima = data.ultima;
                if (ref.ultima === 0) {
                    ref.ultima = 1;
                }
                if (ref.atual >= ref.ultima) {
                    ref.atual = ref.ultima;
                    //ref.$emit('update:atual', ref.atual);
                }
                ref.carregando = false;

                if (data.dados) {
                    ref.$emit('carregou', data.dados);
                    //$('#conteudo').fadeIn();

                } else {
                    ref.atual = 1;
                    //ref.$emit('update:atual', ref.atual);
                    ref.$emit('carregou', []);
                }

            });

        }
    },
}
</script>
