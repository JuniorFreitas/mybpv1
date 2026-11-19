#!/usr/bin/env node
/**
 * Substitui mix('...') por @vite([...]) / asset() nas blades,
 * usando o mapa gerado a partir do webpack.mix.js.
 */
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')
const map = JSON.parse(fs.readFileSync(path.join(__dirname, 'mix-to-vite-map.json'), 'utf8'))

const STATIC_ASSETS = new Set([
    'js/funcoes.js',
    'js/g/controle-ponto/ponto-eletronico/webcam.min.js',
    'js/g/controle-ponto/camera/adapter-latest.js',
    'js/g/controle-ponto/camera/face-api.min.js'
])

function walk(dir, out = []) {
    for (const name of fs.readdirSync(dir)) {
        const full = path.join(dir, name)
        const st = fs.statSync(full)
        if (st.isDirectory()) walk(full, out)
        else if (name.endsWith('.blade.php')) out.push(full)
    }
    return out
}

function replaceMix(content) {
    return content.replace(/\{\{\s*mix\(\s*(['"])([^'"]+)\1\s*\)\s*\}\}/g, (full, _q, assetPath) => {
        const key = assetPath.replace(/^\//, '')
        if (STATIC_ASSETS.has(key) || map[key] === null) {
            return `{{ asset('${key}') }}`
        }
        const src = map[key]
        if (!src) {
            console.warn(`SEM MAPA: ${key}`)
            return full
        }
        return `@vite(['${src}'])`
    })
}

const views = walk(path.join(root, 'resources/views'))
let changed = 0
let untouched = 0
for (const file of views) {
    const before = fs.readFileSync(file, 'utf8')
    if (!before.includes('mix(')) {
        untouched++
        continue
    }
    const after = replaceMix(before)
    if (after !== before) {
        fs.writeFileSync(file, after)
        changed++
        console.log('OK', path.relative(root, file))
    }
}

console.log(`\nAtualizados: ${changed} | sem mix: ${untouched}`)
