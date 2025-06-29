<template>
    <div class="dropdown show">
        <a
            class="btn btn-secondary dropdown-toggle"
            href="#"
            role="button"
            id="dropdownMenuLink"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        >
            <i class="fas fa-ellipsis-v"></i>
        </a>

        <div
            class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
            aria-labelledby="dropdownMenuLink"
        >
            <template v-for="acao in acoes">
                <a
                    v-if="acao.tipo !== 'imprimir'"
                    :key="acao.tipo"
                    class="dropdown-item"
                    href="javascript://"
                    :title="acao.texto"
                    @click="$emit(acao.tipo, item)"
                >
                    <i :class="`fa ${acao.icone}`"></i> {{ acao.texto }}
                </a>

                <a
                    v-else
                    :key="acao.tipo"
                    class="dropdown-item"
                    :href="acao.url"
                    target="_blank"
                    :title="acao.texto"
                >
                    <i :class="`fa ${acao.icone}`"></i> {{ acao.texto }}
                </a>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AcoesDropdown',
    props: {
        item: {
            type: Object,
            required: true
        },
        acoes: {
            type: Array,
            required: true
        }
    }
}
</script>
