<template>
    <div>
        <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="preload">
            <i :class="preload ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
        </button>
    </div>
</template>

<script>
export default {
    props: {
        preload:{
            type: Boolean,
            default: true
        }
    }
}
</script>

<style scoped>

</style>
