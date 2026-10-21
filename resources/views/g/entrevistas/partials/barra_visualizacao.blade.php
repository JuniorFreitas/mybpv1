<div class="d-flex justify-content-end align-items-center flex-wrap my-2" style="gap: .5rem;">
    @if(!empty($colunas))
        <button type="button" class="btn btn-sm btn-outline-secondary" content="Mostrar e Ocultar Colunas" v-tippy
                @click="$refs.filtroColunas?.abrirModal()">
            <i class="bx bxs-filter-alt" aria-hidden="true"></i> Colunas
        </button>
    @endif
    <mybp-visualizacao-listagem v-model="visualizacao" tela="{{ $tela }}"></mybp-visualizacao-listagem>
</div>
