<template>
    <fieldset>
        <legend>DADOS</legend>
        <div class="row mb-3" v-if="dados.cnpj_lotacao">
            <div class="col-12">
                <strong>CNPJ:</strong>
                {{ dados.cnpj_lotacao.razao_social }}
                ({{ dados.pertence_filial ? 'Filial' : 'Matriz' }})
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-4">
                <strong>Nome:</strong> {{ dados.nome }}
            </div>
            <div class="col-12 col-lg-4">
                <strong>Matrícula:</strong> {{ dados.matricula }}
            </div>
            <div class="col-12 col-lg-4">
                <strong>Admissão:</strong> {{ dados.data_admissao }}
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12 col-lg-4">
                <strong>Cargo:</strong> {{ dados.cargo }}
            </div>
            <div class="col-12 col-lg-4">
                <strong>Centro de Custo:</strong> {{ dados.centro_custo }}
            </div>
            <div class="col-12 col-lg-4">
                <strong>Área:</strong> {{ dados.area }}
            </div>
        </div>
    </fieldset>
</template>

<script>
export default {
    name: 'FuncionarioDados',
    props: {
        dados: {
            type: Object,
            default: () => ({})
        }
    }
}
</script>
