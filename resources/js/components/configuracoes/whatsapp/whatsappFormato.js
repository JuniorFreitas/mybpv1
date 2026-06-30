/**
 * Converte formatação WhatsApp (* _ ~ ```) em HTML seguro para preview.
 */
export function whatsappFormatoParaHtml(texto) {
    if (!texto) return ''

    const escapeHtml = (str) => String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')

    let html = escapeHtml(texto)

    html = html.replace(/```([^`]+)```/g, '<code>$1</code>')
    html = html.replace(/\*([^*\n]+)\*/g, '<strong>$1</strong>')
    html = html.replace(/_([^_\n]+)_/g, '<em>$1</em>')
    html = html.replace(/~([^~\n]+)~/g, '<del>$1</del>')

    return html.replace(/\n/g, '<br>')
}
