<template>
    <filtro-listagem
        class="mt-2 mybp-filtros-compactos"
        :mostrar-limpar-filtros="temFiltros"
        :desabilitado="carregando"
        @submit="$emit('submit')"
        @limpar="$emit('limpar')"
    >
        <template #filtros>
            <date-range-filter
                v-model:enabled="dados.filtroPeriodo"
                v-model:start-date="dados.dataInicio"
                v-model:end-date="dados.dataFim"
                :disabled="carregando"
                :id-suffix="idPrefix"
                label="Período"
                wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                @change="$emit('periodo')"
            ></date-range-filter>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" :for="idPrefix + '-nome'">Nome</label>
                    <input
                        :id="idPrefix + '-nome'"
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="Buscar por nome"
                        autocomplete="off"
                        :disabled="carregando"
                        v-model="dados.campoBusca"
                    />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" :for="idPrefix + '-cpf'">CPF</label>
                    <input
                        :id="idPrefix + '-cpf'"
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="Buscar por cpf"
                        autocomplete="off"
                        onblur="valida_cpf(this)"
                        v-mascara:cpf
                        :disabled="carregando"
                        v-model="dados.campoCPF"
                    />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label">{{ labelCargo }}</label>
                    <autocomplete
                        :caminho="dados.caminho_autocomplete"
                        :valido="dados.campoVaga !== ''"
                        v-model="dados.autocomplete_label"
                        :disabled="carregando"
                        placeholder="Por cargo"
                        @onblur="$emit('blur-vaga')"
                        @onselect="$emit('select-vaga', $event)"
                    ></autocomplete>
                </div>
            </div>

            <div
                class="col-12 mybp-filtros-avancados-shell"
                :class="{ 'is-open': abertos }"
                :aria-hidden="abertos ? 'false' : 'true'"
            >
                <div class="mybp-filtros-avancados-shell__inner">
                    <div class="mybp-filtros-avancados">
                        <div class="mybp-filtros-avancados__block">
                            <p class="mybp-filtros-avancados__title">Outros filtros</p>
                            <div class="row">
                                <slot name="avancados"></slot>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #acoes>
            <button type="submit" class="btn btn-sm btn-success" :disabled="carregando">
                <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                Atualizar
            </button>
            <button
                type="button"
                class="btn btn-sm mybp-btn-mais-filtros"
                :class="abertos ? 'btn-primary is-open' : 'btn-outline-primary'"
                :aria-expanded="abertos ? 'true' : 'false'"
                @click="$emit('update:abertos', !abertos)"
            >
                <i class="fa" :class="abertos ? 'fa-chevron-up' : 'fa-sliders-h'"></i>
                {{ abertos ? 'Menos filtros' : 'Mais filtros' }}
                <span class="badge badge-light ml-1" v-if="totalAvancados">{{ totalAvancados }}</span>
            </button>
            <slot name="acoes"></slot>
        </template>
    </filtro-listagem>
</template>

<script>
import DateRangeFilter from '../DateRangeFilter.vue'
import FiltroListagem from '../ui/FiltroListagem.vue'

export default {
    name: 'FiltroEntrevistaCompacto',
    components: { DateRangeFilter, FiltroListagem },
    props: {
        dados: { type: Object, required: true },
        carregando: { type: Boolean, default: false },
        abertos: { type: Boolean, default: false },
        temFiltros: { type: Boolean, default: false },
        totalAvancados: { type: Number, default: 0 },
        idPrefix: { type: String, required: true },
        labelCargo: { type: String, default: 'Cargo' }
    },
    emits: ['submit', 'limpar', 'periodo', 'select-vaga', 'blur-vaga', 'update:abertos']
}
</script>
