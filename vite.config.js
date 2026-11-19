import { defineConfig, loadEnv } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { viteStaticCopy } from 'vite-plugin-static-copy'
import path from 'path'
import fs from 'fs'
import { fileURLToPath } from 'url'
import { buildFuncoesBundle } from './scripts/build-funcoes.mjs'
import { buildLegacyGlobals } from './scripts/build-legacy-globals.mjs'
import { loadFrontendEnv } from './scripts/load-frontend-env.mjs'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

const entries = JSON.parse(
    fs.readFileSync(path.resolve(__dirname, 'scripts/vite-entries.json'), 'utf8')
)

function legacyAssetsPlugin() {
    return {
        name: 'mybp-build-legacy-assets',
        buildStart() {
            buildFuncoesBundle()
        },
        async closeBundle() {
            // watch (`vite build --watch`) tambem precisa regenerar o IIFE;
            // senao registerGlobals/plugins ficam stale em public/js/legacy-globals.js
            buildFuncoesBundle()
            await buildLegacyGlobals()
        },
        async configureServer() {
            buildFuncoesBundle()
            await buildLegacyGlobals()
        }
    }
}

export default defineConfig(({ mode }) => {
    loadEnv(mode, process.cwd(), '')
    loadFrontendEnv(process.cwd())

    return {
        plugins: [
            laravel({
                input: ['resources/sass/app.scss', ...entries],
                refresh: true
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false
                    },
                    compilerOptions: {
                        whitespace: 'preserve'
                    }
                }
            }),
            viteStaticCopy({
                targets: [
                    {
                        src: 'resources/css/icons.min.css',
                        dest: '../css'
                    },
                    {
                        src: 'node_modules/pdfjs-dist/build/pdf.worker.min.mjs',
                        dest: '../js/vendor',
                        rename: 'pdf.worker.min.js'
                    },
                    {
                        src: 'resources/js/tinymce',
                        dest: '../js'
                    },
                    {
                        src: 'resources/js/g/controle-ponto/camera/adapter-latest.js',
                        dest: '../js/g/controle-ponto/camera'
                    },
                    {
                        src: 'resources/js/g/controle-ponto/camera/face-api.min.js',
                        dest: '../js/g/controle-ponto/camera'
                    },
                    {
                        src: 'resources/js/g/controle-ponto/camera/models',
                        dest: '../js/g/controle-ponto/camera'
                    },
                    {
                        src: 'resources/js/g/controle-ponto/camera/labels',
                        dest: '../js/g/controle-ponto/camera'
                    },
                    {
                        src: 'resources/js/g/controle-ponto/ponto-eletronico/webcam.min.js',
                        dest: '../js/g/controle-ponto/ponto-eletronico'
                    }
                ]
            }),
            legacyAssetsPlugin()
        ],
        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm-bundler.js',
                '@': path.resolve(__dirname, 'resources/js'),
                '~': path.resolve(__dirname, 'node_modules')
            },
            dedupe: ['jquery'],
            extensions: ['.mjs', '.js', '.jsx', '.json', '.vue']
        },
        define: {
            __VUE_OPTIONS_API__: true,
            __VUE_PROD_DEVTOOLS__: false,
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false
        },
        css: {
            preprocessorOptions: {
                scss: {
                    quietDeps: true,
                    silenceDeprecations: [
                        'abs-percent',
                        'color-functions',
                        'global-builtin',
                        'if-function',
                        'import',
                        'legacy-js-api',
                        'slash-div'
                    ],
                    loadPaths: [path.resolve(__dirname, 'node_modules')],
                    importers: [
                        {
                            findFileUrl(url) {
                                if (url.startsWith('~')) {
                                    const resolved = path.resolve(
                                        __dirname,
                                        'node_modules',
                                        url.slice(1)
                                    )
                                    return new URL(`file://${resolved}`)
                                }
                                return null
                            }
                        }
                    ]
                }
            }
        },
        optimizeDeps: {
            include: [
                'jquery',
                'bootstrap',
                'bootstrap-daterangepicker',
                'lodash',
                'axios',
                'moment',
                'toastr',
                'pusher-js',
                'laravel-echo',
                'popper.js'
            ]
        },
        build: {
            sourcemap: mode !== 'production',
            chunkSizeWarningLimit: 2000,
            commonjsOptions: {
                transformMixedEsModules: true,
                include: [
                    /bootstrap-daterangepicker/,
                    /resources\/js\/components\/Select2\/depends\/select2\.full/,
                    /node_modules/
                ]
            },
            rollupOptions: {
                treeshake: {
                    moduleSideEffects: (id) => {
                        if (id.includes('bootstrap-daterangepicker')) return true
                        if (id.includes('metismenu')) return true
                        if (id.includes('jquery.mask')) return true
                        return true
                    }
                },
                output: {
                    manualChunks(id) {
                        if (id.includes('node_modules/vue/') || id.includes('node_modules/@vue/')) {
                            return 'vendor-vue'
                        }
                        if (id.includes('node_modules/jquery') || id.includes('node_modules/bootstrap')) {
                            return 'vendor-jquery'
                        }
                        if (id.includes('node_modules/lodash') || id.includes('node_modules/axios') || id.includes('node_modules/moment')) {
                            return 'vendor-utils'
                        }
                    }
                }
            }
        },
        server: {
            watch: {
                ignored: ['**/public/js/**', '**/public/css/**', '**/public/build/**']
            }
        }
    }
})
