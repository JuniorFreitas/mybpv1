import fs from 'fs'
import path from 'path'
import dotenv from 'dotenv'

/**
 * Carrega .env e resolve chaves de frontend para o build Vite/legacy.
 * Aceita VITE_*, MIX_* e fallbacks REVERB_/PUSHER_, expandindo referencias ${VAR}.
 */
export function loadFrontendEnv(root = process.cwd()) {
    const envPath = path.join(root, '.env')
    const fileVars = fs.existsSync(envPath) ? dotenv.parse(fs.readFileSync(envPath)) : {}
    const bag = { ...fileVars, ...process.env }

    const expand = (value, depth = 0) => {
        if (value == null || typeof value !== 'string' || depth > 5) {
            return value == null ? '' : String(value)
        }
        return value.replace(/\$\{([A-Z0-9_]+)\}/g, (_, name) => expand(bag[name] ?? '', depth + 1))
    }

    for (const [key, value] of Object.entries(fileVars)) {
        if (process.env[key] === undefined) {
            process.env[key] = expand(value)
            bag[key] = process.env[key]
        }
    }

    const setIfEmpty = (target, ...candidates) => {
        if (process.env[target]) {
            return process.env[target]
        }
        for (const name of candidates) {
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

    // Driver de broadcast do Laravel (pusher|reverb|log|null)
    setIfEmpty('VITE_BROADCAST_DRIVER', 'BROADCAST_DRIVER', 'BROADCAST_CONNECTION')

    const broadcastDriver = String(process.env.VITE_BROADCAST_DRIVER || '').toLowerCase()

    // Reverb: soh preenche a partir de REVERB_* se o driver for reverb.
    // Se MIX_REVERB_APP_KEY estiver vazio de proposito (caso local com Pusher), respeita.
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
