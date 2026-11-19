#!/usr/bin/env node
/**
 * Falha o CI/build se o bundle público contiver padrões de secret de servidor.
 * Uso: node scripts/assert-frontend-bundle-safe.mjs
 */
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')

const SCAN_DIRS = [
    path.join(root, 'public/build'),
    path.join(root, 'public/js')
]

const FORBIDDEN_PATTERNS = [
    /PUSHER_APP_SECRET/i,
    /REVERB_APP_SECRET/i,
    /AWS_SECRET_ACCESS_KEY/i,
    /BEGIN (RSA |OPENSSH |EC )?PRIVATE KEY/,
    /\bDB_PASSWORD\b/i,
    /\bREDIS_PASSWORD\b/i,
    /\bMAIL_PASSWORD\b/i,
    /\bAPP_KEY=base64:/i
]

function walk(dir, out = []) {
    if (!fs.existsSync(dir)) {
        return out
    }
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const full = path.join(dir, entry.name)
        if (entry.isDirectory()) {
            walk(full, out)
            continue
        }
        if (/\.(js|css|map|json)$/i.test(entry.name)) {
            out.push(full)
        }
    }
    return out
}

const files = SCAN_DIRS.flatMap((dir) => walk(dir))
const hits = []

for (const file of files) {
    const text = fs.readFileSync(file, 'utf8')
    for (const pattern of FORBIDDEN_PATTERNS) {
        if (pattern.test(text)) {
            hits.push(`${path.relative(root, file)} ⇐ ${pattern}`)
        }
    }

    // Se o processo de build ainda tiver secrets no env, garante que o valor não entrou no JS
    for (const [key, value] of Object.entries(process.env)) {
        if (!/SECRET|PASSWORD|PRIVATE_KEY/i.test(key)) {
            continue
        }
        if (!value || String(value).length < 8) {
            continue
        }
        if (text.includes(String(value))) {
            hits.push(`${path.relative(root, file)} ⇐ valor de ${key}`)
        }
    }
}

if (hits.length) {
    console.error('ERRO: possível secret no bundle frontend:')
    for (const hit of hits) {
        console.error(`  - ${hit}`)
    }
    process.exit(1)
}

console.log(`OK: bundle frontend sem padrões de secret (${files.length} arquivos).`)
