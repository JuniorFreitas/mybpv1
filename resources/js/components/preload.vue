<!--<template>-->
<!--    <div>-->
<!--        <i class="fas fa-circle-notch fa-spin mr-1"></i> {{ label }}-->
<!--    </div>-->
<!--</template>-->

<!--<script>-->
<!--export default {-->
<!--    props: {-->
<!--        label: {-->
<!--            type: String,-->
<!--            default: () => 'Carregando...'-->
<!--        }-->
<!--    }-->
<!--}-->
<!--</script>-->

<!--<style scoped>-->

<!--</style>-->

<template>
    <div class="preload-overlay">
        <div class="preload-spinner">
            <i class="fa fa-spinner fa-pulse fa-3x"></i>
            <br>
            {{ msg }}
        </div>
    </div>
</template>

<script>
export default {
    name: "Preload",
    props: {
        msg: {
            type: String,
            default: () => 'Carregando...'
        }
    }
}
</script>

<style scoped>
.preload-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.preload-spinner {
    color: white;
    text-align: center;
}
</style>
