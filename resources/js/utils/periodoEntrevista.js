export function periodoBrDeIso(inicio, fim) {
    const br = (iso) => {
        const partes = String(iso || '').split('-')
        if (partes.length !== 3 || !partes[0] || !partes[1] || !partes[2]) {
            return ''
        }
        return `${partes[2]}/${partes[1]}/${partes[0]}`
    }
    const a = br(inicio)
    const b = br(fim)
    if (!a || !b) {
        return ''
    }
    return `${a} até ${b}`
}
