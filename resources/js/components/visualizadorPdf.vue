<template>
    <div>
        <preload v-if="preload"></preload>
        <p v-if="abriuPdf && preload">Aguarde...</p>
        <object v-if="abriuPdf" ref="objpdf" type="application/pdf" width="100%" height="630px">
            <p>Não é possível exibir o arquivo PDF clique para fazer o <a :href="urldownload">Download</a></p>
        </object>
    </div>
</template>

<script>
export default {
    name: "visualizadorPdf",
    props: {
        url: {
            type: String,
            required: true,
        },
        urldownload: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            preload: true,
            abriuPdf: false,
        }
    },
    mounted() {
        this.abriuPdf = true;
        this.$nextTick(() => {
            const objPDF = this.$refs.objpdf;
            objPDF.setAttribute("data", this.url);
            objPDF.addEventListener("load", () => {
                this.preload = false;
                this.$emit("carregoupdf");
            });
            objPDF.addEventListener("error", () => {
                this.preload = false;
            });
        });
    },
}
</script>

<style scoped>

</style>
