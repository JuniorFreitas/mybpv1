<template>
    <div>
        <fieldset>
            <legend>Informações do Colaborador</legend>
            <div style="text-transform: uppercase">
                <span>Nome: <strong>@{{ form.curriculo.nome }}</strong></span><br>
                <span>CPF: <strong>@{{ form.curriculo.cpf }}</strong></span><br>
                <span>
                            Cargo: <strong>@{{ form.admissao.cargo }}</strong> | Função: <strong>
                                @{{ form.admissao.funcao }}</strong></span><br>
                <span>Data de admissão: <strong>@{{ form.admissao.data_admissao }}</strong></span><br>
            </div>
        </fieldset>
    </div>
</template>

<script>
export default {
    name: "DemissaoAvulsa",
    props: {
        form: {
            type: Object,
            default: () => {
                return {
                    curriculo: {
                        nome: '',
                        cpf: '',
                    },
                    admissao: {
                        cargo: '',
                        funcao: '',
                        data_admissao: '',
                    }
                }
            }
        },
        visualizar: {
            type: Boolean,
            default: false
        },
    },
}
</script>


<style scoped>

</style>
