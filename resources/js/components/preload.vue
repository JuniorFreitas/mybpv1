<template>
    <div>
        <i class="fas fa-circle-notch fa-spin mr-1"></i> {{ label }}
    </div>
</template>

<script>
export default {
    props: {
        label: {
            type: String,
            default: () => 'Carregando...'
        }
    }
}
</script>

<style scoped>

</style>
