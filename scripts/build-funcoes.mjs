import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')

const sources = [
    // Depende de window.$ definido por legacy-globals.js (carregado antes)
    'node_modules/metismenu/dist/metisMenu.js',
    'node_modules/simplebar/dist/simplebar.js',
    'node_modules/node-waves/dist/waves.js',
    'resources/js/funcoes.js',
    'resources/js/jquery.mask.js',
    'resources/js/jquery.maskMoney.js'
]

/**
 * Gera public/js/funcoes.js (script classico, mesmo contrato do mix.babel).
 */
export function buildFuncoesBundle() {
    const outDir = path.join(root, 'public/js')
    const outFile = path.join(outDir, 'funcoes.js')
    fs.mkdirSync(outDir, { recursive: true })

    const parts = sources.map((rel) => {
        const abs = path.join(root, rel)
        if (!fs.existsSync(abs)) {
            throw new Error(`[build-funcoes] arquivo ausente: ${rel}`)
        }
        return `/* === ${rel} === */\n${fs.readFileSync(abs, 'utf8')}`
    })

    fs.writeFileSync(outFile, parts.join('\n;\n') + '\n')
    return outFile
}

if (import.meta.url === `file://${process.argv[1]}` || process.argv[1]?.endsWith('build-funcoes.mjs')) {
    const out = buildFuncoesBundle()
    console.log(`OK: ${path.relative(root, out)}`)
}
