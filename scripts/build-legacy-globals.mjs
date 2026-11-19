import { build } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import fs from 'fs'
import { fileURLToPath } from 'url'
import { loadFrontendEnv } from './load-frontend-env.mjs'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')
const outDir = path.join(root, 'public/js')

function envDefine(name) {
    return JSON.stringify(process.env[name] || '')
}

export async function buildLegacyGlobals() {
    fs.mkdirSync(outDir, { recursive: true })
    const resolved = loadFrontendEnv(root)

    await build({
        configFile: false,
        root,
        logLevel: 'error',
        envPrefix: ['VITE_', 'MIX_'],
        plugins: [
            vue({
                template: {
                    compilerOptions: { whitespace: 'preserve' }
                }
            })
        ],
        define: {
            __VUE_OPTIONS_API__: true,
            __VUE_PROD_DEVTOOLS__: false,
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
            'process.env.NODE_ENV': JSON.stringify('production'),
            'process.env': JSON.stringify({ NODE_ENV: 'production' }),
            'import.meta.env.MODE': JSON.stringify('production'),
            'import.meta.env.PROD': 'true',
            'import.meta.env.DEV': 'false',
            'import.meta.env.SSR': 'false',
            'import.meta.env.VITE_AMBIENTE': envDefine('VITE_AMBIENTE'),
            'import.meta.env.VITE_BROADCAST_DRIVER': envDefine('VITE_BROADCAST_DRIVER'),
            'import.meta.env.VITE_GOOGLE_MAPS_KEY': envDefine('VITE_GOOGLE_MAPS_KEY'),
            'import.meta.env.VITE_URL_SITE': envDefine('VITE_URL_SITE'),
            'import.meta.env.VITE_URL_ADMIN': envDefine('VITE_URL_ADMIN'),
            'import.meta.env.VITE_URL_PUBLICO': envDefine('VITE_URL_PUBLICO'),
            'import.meta.env.VITE_REVERB_APP_KEY': envDefine('VITE_REVERB_APP_KEY'),
            'import.meta.env.VITE_REVERB_HOST': envDefine('VITE_REVERB_HOST'),
            'import.meta.env.VITE_REVERB_PORT': envDefine('VITE_REVERB_PORT'),
            'import.meta.env.VITE_REVERB_SCHEME': envDefine('VITE_REVERB_SCHEME'),
            'import.meta.env.VITE_PUSHER_APP_KEY': envDefine('VITE_PUSHER_APP_KEY'),
            'import.meta.env.VITE_PUSHER_APP_CLUSTER': envDefine('VITE_PUSHER_APP_CLUSTER'),
            'import.meta.env.VITE_PUSHER_APP_TLS': envDefine('VITE_PUSHER_APP_TLS')
        },
        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm-bundler.js',
                '@': path.join(root, 'resources/js')
            },
            dedupe: ['jquery'],
            extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue']
        },
        build: {
            outDir,
            emptyOutDir: false,
            cssCodeSplit: false,
            lib: {
                entry: path.join(root, 'resources/js/legacy-globals-entry.js'),
                name: 'MybpLegacyGlobals',
                formats: ['iife'],
                fileName: () => 'legacy-globals.js',
                cssFileName: 'legacy-globals'
            },
            rollupOptions: {
                output: {
                    inlineDynamicImports: true,
                    assetFileNames: 'legacy-globals[extname]'
                }
            }
        }
    })

    // CSS gerado pelo tippy etc. — injeta no HTML via link se existir
    const cssPath = path.join(outDir, 'legacy-globals.css')
    return {
        js: path.join(outDir, 'legacy-globals.js'),
        css: fs.existsSync(cssPath) ? cssPath : null,
        broadcasting: Boolean(resolved.VITE_REVERB_APP_KEY || resolved.VITE_PUSHER_APP_KEY)
    }
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
    buildLegacyGlobals()
        .then((out) => {
            console.log(`OK: ${path.relative(root, out.js)}`)
            if (out.css) console.log(`OK: ${path.relative(root, out.css)}`)
            console.log(out.broadcasting ? 'Broadcasting: keys embutidas' : 'Broadcasting: sem keys (warning esperado)')
        })
        .catch((err) => {
            console.error(err)
            process.exit(1)
        })
}
