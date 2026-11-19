import fs from 'fs'
import path from 'path'
import dotenv from 'dotenv'

/**
 * Carrega apenas variáveis seguras para o build Vite/legacy.
 * Nunca promove secrets de servidor (DB_*, APP_KEY, *_SECRET, AWS_*, etc.) para process.env
 * a partir de arquivos .env — evita vazamento acidental no bundle.
 *
 * Aceita VITE_*, MIX_* (legado) e fallbacks públicos REVERB_/PUSHER_/APP_URL.
 */
const SAFE_EXACT = new Set([
    'APP_URL',
    'BROADCAST_DRIVER',
    'BROADCAST_CONNECTION',
    'REVERB_APP_KEY',
    'REVERB_HOST',
    'REVERB_PORT',
    'REVERB_SCHEME',
    'PUSHER_APP_KEY',
    'PUSHER_APP_CLUSTER',
    'PUSHER_APP_TLS'
])

const SAFE_PREFIXES = ['VITE_', 'MIX_']

const BLOCKED_SUBSTRINGS = [
    'SECRET',
    'PASSWORD',
    'PRIVATE_KEY',
    'AWS_ACCESS',
    'AWS_SECRET',
    'DB_',
    'DATABASE_',
    'REDIS_PASSWORD',
    'MAIL_PASSWORD',
    'APP_KEY',
    'JWT_',
    'TOKEN_SECRET',
    'X_API_TOKEN'
]

function isBlockedKey(key) {
    const upper = String(key).toUpperCase()
    return BLOCKED_SUBSTRINGS.some((part) => upper.includes(part))
}

function isSafeKey(key) {
    if (!key || isBlockedKey(key)) {
        return false
    }
    if (SAFE_EXACT.has(key)) {
        return true
    }
    return SAFE_PREFIXES.some((prefix) => key.startsWith(prefix))
}

function parseEnvFile(filePath) {
    if (!fs.existsSync(filePath)) {
        return {}
    }
    const parsed = dotenv.parse(fs.readFileSync(filePath))
    const safe = {}
    for (const [key, value] of Object.entries(parsed)) {
        if (isSafeKey(key)) {
            safe[key] = value
        }
    }
    return safe
}

export function loadFrontendEnv(root = process.cwd()) {
    const explicitFile = process.env.FRONTEND_ENV_FILE
        ? path.resolve(root, process.env.FRONTEND_ENV_FILE)
        : null

    // Preferência: arquivo explícito do CI/prod (.env.prod / .env.homol).
    // Fallback local: só chaves allowlisted do .env (nunca secrets).
    const fileVars = explicitFile
        ? parseEnvFile(explicitFile)
        : parseEnvFile(path.join(root, '.env'))

    const bag = { ...fileVars }
    for (const [key, value] of Object.entries(process.env)) {
        if (value !== undefined && isSafeKey(key)) {
            bag[key] = value
        }
    }

    const expand = (value, depth = 0) => {
        if (value == null || typeof value !== 'string' || depth > 5) {
            return value == null ? '' : String(value)
        }
        return value.replace(/\$\{([A-Z0-9_]+)\}/g, (_, name) => {
            if (!isSafeKey(name)) {
                return ''
            }
            return expand(bag[name] ?? '', depth + 1)
        })
    }

    for (const [key, value] of Object.entries(fileVars)) {
        if (!isSafeKey(key)) {
            continue
        }
        if (process.env[key] === undefined) {
            process.env[key] = expand(value)
            bag[key] = process.env[key]
        }
    }

    const setIfEmpty = (target, ...candidates) => {
        if (!isSafeKey(target)) {
            return ''
        }
        if (process.env[target]) {
            return process.env[target]
        }
        for (const name of candidates) {
            if (!isSafeKey(name)) {
                continue
            }
            const raw = bag[name]
            const value = expand(raw ?? '')
            if (value !== '') {
                process.env[target] = value
                bag[target] = value
                return value
            }
        }
        process.env[target] = process.env[target] || ''
        return process.env[target]
    }

    setIfEmpty('VITE_BROADCAST_DRIVER', 'BROADCAST_DRIVER', 'BROADCAST_CONNECTION')

    const broadcastDriver = String(process.env.VITE_BROADCAST_DRIVER || '').toLowerCase()

    setIfEmpty('VITE_REVERB_APP_KEY', 'MIX_REVERB_APP_KEY')
    setIfEmpty('VITE_REVERB_HOST', 'MIX_REVERB_HOST')
    setIfEmpty('VITE_REVERB_PORT', 'MIX_REVERB_PORT')
    setIfEmpty('VITE_REVERB_SCHEME', 'MIX_REVERB_SCHEME')
    if (broadcastDriver === 'reverb') {
        setIfEmpty('VITE_REVERB_APP_KEY', 'REVERB_APP_KEY')
        setIfEmpty('VITE_REVERB_HOST', 'REVERB_HOST')
        setIfEmpty('VITE_REVERB_PORT', 'REVERB_PORT')
        setIfEmpty('VITE_REVERB_SCHEME', 'REVERB_SCHEME')
    }

    setIfEmpty('VITE_PUSHER_APP_KEY', 'MIX_PUSHER_APP_KEY', 'PUSHER_APP_KEY')
    setIfEmpty('VITE_PUSHER_APP_CLUSTER', 'MIX_PUSHER_APP_CLUSTER', 'PUSHER_APP_CLUSTER')
    setIfEmpty('VITE_PUSHER_APP_TLS', 'MIX_PUSHER_APP_TLS', 'PUSHER_APP_TLS')
    setIfEmpty('VITE_AMBIENTE', 'MIX_AMBIENTE')
    setIfEmpty('VITE_URL_SITE', 'MIX_URL_SITE', 'APP_URL')
    setIfEmpty('VITE_URL_ADMIN', 'MIX_URL_ADMIN')
    setIfEmpty('VITE_URL_PUBLICO', 'MIX_URL_PUBLICO')
    setIfEmpty('VITE_GOOGLE_MAPS_KEY', 'MIX_GOOGLE_MAPS_KEY')
    setIfEmpty('VITE_TYNEKEY', 'MIX_TYNEKEY')

    if (!process.env.VITE_URL_ADMIN && process.env.VITE_URL_SITE) {
        process.env.VITE_URL_ADMIN = `${String(process.env.VITE_URL_SITE).replace(/\/$/, '')}/g`
    }
    if (!process.env.VITE_URL_PUBLICO && process.env.VITE_URL_SITE) {
        process.env.VITE_URL_PUBLICO = `${String(process.env.VITE_URL_SITE).replace(/\/$/, '')}/publico`
    }

    return {
        VITE_REVERB_APP_KEY: process.env.VITE_REVERB_APP_KEY || '',
        VITE_PUSHER_APP_KEY: process.env.VITE_PUSHER_APP_KEY || '',
        VITE_AMBIENTE: process.env.VITE_AMBIENTE || ''
    }
}
