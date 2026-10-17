<template>
    <div class="mybp-pdfjs">
        <div class="mybp-pdfjs__toolbar">
            <div class="mybp-pdfjs__nav">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    :disabled="preload || page <= 1"
                    @click.prevent="paginaAnterior"
                    title="Página anterior"
                >
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="mybp-pdfjs__pagina" v-if="numPages">
                    {{ page }} / {{ numPages }}
                </span>
                <span class="mybp-pdfjs__pagina text-muted" v-else>—</span>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    :disabled="preload || page >= numPages"
                    @click.prevent="proximaPagina"
                    title="Próxima página"
                >
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <a
                v-if="urldownload"
                class="btn btn-sm btn-outline-primary"
                :href="urldownload"
                target="_blank"
                rel="noopener"
            >
                <i class="fas fa-download"></i> Baixar
            </a>
        </div>

        <preload v-if="preload"></preload>

        <div v-if="erro" class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            {{ erro }}
            <a v-if="urldownload" :href="urldownload" target="_blank" rel="noopener" class="ml-1">
                Fazer download
            </a>
        </div>

        <div v-show="!preload && !erro" class="mybp-pdfjs__viewport" ref="viewport">
            <canvas ref="canvas" class="mybp-pdfjs__canvas"></canvas>
        </div>
    </div>
</template>

<script>
import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs'

pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/vendor/pdf.worker.min.js'

export default {
    name: 'visualizadorPdf',
    props: {
        url: {
            type: String,
            required: true
        },
        urldownload: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            preload: true,
            erro: '',
            page: 1,
            numPages: 0,
            loadToken: 0
        }
    },
    created() {
        // PDF.js usa campos privados (#); não pode ficar em data() (Proxy do Vue).
        this._pdfDoc = null
        this._renderTask = null
    },
    watch: {
        url: {
            immediate: true,
            handler(novaUrl) {
                this.carregarPdf(novaUrl)
            }
        }
    },
    beforeUnmount() {
        this.destruirDocumento()
    },
    methods: {
        destruirDocumento() {
            if (this._renderTask) {
                try {
                    this._renderTask.cancel()
                } catch (e) {
                    // ignore
                }
                this._renderTask = null
            }
            if (this._pdfDoc) {
                try {
                    this._pdfDoc.destroy()
                } catch (e) {
                    // ignore
                }
                this._pdfDoc = null
            }
        },
        async carregarPdf(url) {
            const token = ++this.loadToken
            this.destruirDocumento()
            this.preload = true
            this.erro = ''
            this.page = 1
            this.numPages = 0

            if (!url) {
                this.preload = false
                this.erro = 'URL do PDF indisponível.'
                return
            }

            try {
                const sameOrigin = url.startsWith('/')
                    || (typeof window !== 'undefined' && url.startsWith(window.location.origin))
                const loadingTask = pdfjsLib.getDocument({
                    url,
                    withCredentials: sameOrigin,
                    disableRange: true,
                    disableStream: true
                })
                const pdfDoc = await loadingTask.promise

                if (token !== this.loadToken) {
                    pdfDoc.destroy()
                    return
                }

                this._pdfDoc = pdfDoc
                this.numPages = pdfDoc.numPages || 0
                this.preload = false
                this.$emit('carregoupdf')

                await this.$nextTick()
                if (token !== this.loadToken) {
                    return
                }
                await this.renderPagina(1, token)
            } catch (e) {
                if (token !== this.loadToken) {
                    return
                }
                this.preload = false
                this.erro = 'Não foi possível exibir o PDF neste navegador.'
                // eslint-disable-next-line no-console
                console.error('PDF.js erro ao carregar', e)
            }
        },
        async renderPagina(numero, token = this.loadToken) {
            const pdfDoc = this._pdfDoc
            if (!pdfDoc || !this.$refs.canvas) {
                return
            }
            if (token !== this.loadToken) {
                return
            }

            if (this._renderTask) {
                try {
                    this._renderTask.cancel()
                } catch (e) {
                    // ignore
                }
                this._renderTask = null
            }

            try {
                const page = await pdfDoc.getPage(numero)
                if (token !== this.loadToken || this._pdfDoc !== pdfDoc) {
                    return
                }

                const canvas = this.$refs.canvas
                const viewportEl = this.$refs.viewport
                const baseViewport = page.getViewport({ scale: 1 })
                const largura = Math.max((viewportEl?.clientWidth || 800) - 8, 320)
                const scale = largura / baseViewport.width
                const viewport = page.getViewport({ scale })

                const context = canvas.getContext('2d')
                canvas.height = viewport.height
                canvas.width = viewport.width

                this._renderTask = page.render({
                    canvasContext: context,
                    viewport
                })
                await this._renderTask.promise
                this._renderTask = null
                if (token !== this.loadToken) {
                    return
                }
                this.page = numero
            } catch (e) {
                if (e?.name === 'RenderingCancelledException') {
                    return
                }
                if (token !== this.loadToken) {
                    return
                }
                this.erro = 'Falha ao renderizar a página do PDF.'
                // eslint-disable-next-line no-console
                console.error('PDF.js erro ao renderizar', e)
            }
        },
        paginaAnterior() {
            if (this.page <= 1) return
            this.renderPagina(this.page - 1)
        },
        proximaPagina() {
            if (this.page >= this.numPages) return
            this.renderPagina(this.page + 1)
        }
    }
}
</script>

<style scoped>
.mybp-pdfjs {
    border: 1px solid #e4e8ec;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
}

.mybp-pdfjs__toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #fff;
    border-bottom: 1px solid #eef1f4;
}

.mybp-pdfjs__nav {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.mybp-pdfjs__pagina {
    min-width: 4.5rem;
    text-align: center;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #174257;
}

.mybp-pdfjs__viewport {
    width: 100%;
    max-height: 70vh;
    overflow: auto;
    background: #525659;
    padding: 0.75rem;
    text-align: center;
}

.mybp-pdfjs__canvas {
    max-width: 100%;
    height: auto;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}
</style>
