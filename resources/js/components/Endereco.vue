<template>
    <div>
        <div class="row">
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
              <div class="form-group">
                  <label>CEP</label>
                  <div class="input-group">
                      <input type="text" :disabled="preload" v-show="obrigatorio" v-mascara:cep class="form-control"
                             placeholder="Informe o cep"
                             v-model="model.cep"
                             onblur="valida_cep_vazio(this)">

                      <input type="text" :disabled="preload" v-show="!obrigatorio" v-mascara:cep class="form-control"
                             placeholder="Informe o cep"
                             v-model="model.cep">
                      <div class="input-group-append">
                          <button class="btn btn-secondary" type="button" @click.prevent="onClick"><i
                              class="fa fa-search"></i></button>
                      </div>
                  </div>
              </div>
            </div>

        </div>

        <div class="row">
          <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-5">
            <div class="form-group">
                <label>Endereço</label>
              <input type="text" :disabled="preload" v-show="obrigatorio" class="form-control"
                     placeholder="Informe o Logradouro"
                     v-model="model.logradouro"
                     onblur="valida_campo_vazio(this,3)">

              <input type="text" :disabled="preload" v-show="!obrigatorio" class="form-control"
                     placeholder="Informe o Logradouro"
                     v-model="model.logradouro">
            </div>
          </div>

          <div class="col-12 col-sm-2 col-md-2 col-lg-2 col-xl-2">
            <div class="form-group">
              <label>Número</label>
              <input type="text" :disabled="preload" class="form-control"
                     v-model="model.end_numero">
            </div>
          </div>

          <div class="col-12 col-sm-10 col-md-4 col-lg-4 col-xl-4">
            <div class="form-group">
              <label>Complemento</label>
              <input type="text" :disabled="preload" class="form-control"
                     v-model="model.complemento">
            </div>
          </div>

          <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-5">
            <div class="form-group">
              <label>Bairro</label>
              <input type="text" :disabled="preload" v-show="obrigatorio" class="form-control"
                     placeholder="Informe o Bairro"
                     v-model="model.bairro"
                     onblur="valida_campo_vazio(this,3)">

              <input type="text" :disabled="preload" v-show="!obrigatorio" class="form-control"
                     placeholder="Informe o Bairro"
                     v-model="model.bairro">
            </div>
          </div>

        </div>

        <div class="row">
          <div class="col-12 col-sm-10 col-md-6 col-lg-5">
            <div class="form-group">
              <label>Município</label>
              <input type="text" :disabled="preload" v-show="obrigatorio" class="form-control"
                     placeholder="Informe a Cidade"
                     v-model="model.municipio"
                     onblur="valida_campo_vazio(this,3)">

              <input type="text" :disabled="preload" v-show="!obrigatorio" class="form-control"
                     placeholder="Informe a Cidade"
                     v-model="model.municipio">
            </div>
          </div>


          <div class="col-4 col-sm-2 col-md-2 col-lg-2 col-xl-2">
            <div class="form-group">
              <label>UF</label>
              <input type="text" :disabled="preload" v-show="obrigatorio" maxlength="2" class="form-control"
                     placeholder="UF"
                     v-model="model.uf"
                     onblur="valida_campo_vazio(this,1)">

              <input type="text" :disabled="preload" v-show="!obrigatorio" maxlength="2" class="form-control"
                     placeholder="UF"
                     v-model="model.uf">
            </div>
          </div>

        </div>

    </div>
</template>

<script>
    export default {
        props: {
            model: {
                type: Object,
                required: true,
                default: () => {
                    return {
                        cep: '',
                        logradouro: '',
                        bairro: '',
                        end_end_numero: '',
                        complemento: '',
                        bairro_id: 0,
                        municipio: '',
                      municipio_id: 0,
                      uf: 'MA',
                    }
                }
            },
          select: { // se vai usar combox ou nao
            type: Boolean,
            required: false,
            default: false
          },
          obrigatorio: {
            type: Boolean,
            required: false,
            default: true
          }

        },
        computed: {

        },
        mounted() {

        },
        data() {
            return {
                preload: false,
                load: 'buscando ...',
                listaDeBairros: [],
                listaDeMunicipios: [{nome: 'São Luís', id: 0}],
            }
        },

        methods: {
            trocaUF() {
                if(!this.select){
                    return false;
                }
                this.preload = true;
                $.post(`${URL_PUBLICO}/lista-municipios`, {UF: this.model.municipio.uf})
                    .done((data) => {

                        this.preload = false;
                        this.listaDeBairros = data.bairros;
                        this.listaDeMunicipios = data.municipios;
                        let encontrou = _.find(this.listaDeMunicipios, { 'id': this.model.municipio_id});
                        if(!encontrou){
                            this.model.municipio_id = this.listaDeMunicipios[0].id;
                        }



                    })
                    .fail((data) => {
                        this.preload = false;
                    });
            },
            trocaMunicipio() {
                if(!this.select){
                    return false;
                }
                this.preload = true;

                $.post(`${URL_PUBLICO}/lista-bairros`, {id_municipio: this.model.municipio_id})
                    .done((data) => {

                        this.preload = false;
                        this.listaDeBairros = data.bairros;
                        let encontrou = _.find(this.listaDeBairros, { 'id': this.model.bairro_id});
                        if(!encontrou){
                            this.model.bairro_id = this.listaDeBairros[0].id;
                        }


                    })
                    .fail((data) => {
                        this.preload = false;
                    });
            },
            onClick: function () {
                if (this.model.cep.length >= 9) {
                    this.model.logradouro = this.load;
                    this.model.bairro = this.load;
                    this.model.municipio = this.load;
                    this.model.uf = this.load;

                    this.preload = true;
                    $.getJSON(`https://viacep.com.br/ws/${this.model.cep}/json/?callback=?`)
                        .done((data) => {
                            this.model.logradouro = data.logradouro;
                            this.model.bairro = data.bairro;
                            this.model.municipio = data.localidade;
                            this.model.uf = data.uf;

                            this.preload = false;
                            if (data.erro) {
                                this.preload = false;
                                this.model.cep = '';
                                this.model.logradouro = '';
                                this.model.bairro = '';
                                this.model.municipio = '';
                                this.model.uf = '';
                            }
                        })
                        .fail((data) => {
                            this.preload = false;
                            this.model.cep = '';
                            this.model.logradouro = '';
                            this.model.bairro = '';
                            this.model.municipio = '';
                            this.model.uf = '';
                        });
                }
            }
        }
    }
</script>

<style scoped>

</style>
