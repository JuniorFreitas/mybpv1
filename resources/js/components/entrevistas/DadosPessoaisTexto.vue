<template>
    <fieldset>
        <legend class="text-uppercase">Dados Pessoais</legend>
        <div class="row">
            <div class="col-12">
                <p>
                    Nome: <strong>{{ form.curriculo.nome }}</strong> <br>
                    Nascimento:
                    <strong>{{ form.curriculo.nascimento }}</strong> -
                    <strong>{{ form.curriculo.idade }} anos </strong><br>
                    Contato:
                    <strong>{{ form.tel_principal ? form.tel_principal.numero : 'não informado' }}</strong>
                    <br>
                    Vaga: <strong>{{ form.vaga_aberta_municipio }}</strong> - PCD:<strong>
                    {{ form.curriculo.pcd ? 'Sim' : 'Não' }}</strong><br>
                    Endereço: <strong>{{ form.curriculo.endereco_completo }}</strong><br>
                    Escolaridade: <strong>{{ form.curriculo.formacao.tipo }}</strong>
                    <span v-show="form.curriculo.formacao_curso">
                                    <strong> ({{ form.curriculo.formacao_curso }})</strong>
                                </span>
                    <br>
                </p>
            </div>
        </div>
    </fieldset>
</template>

<script>
export default {
    props: {
        form: {
            type: Object,
            required: true,
            default: () => {
            }
        }
    }
}
</script>

<style scoped>

</style>
