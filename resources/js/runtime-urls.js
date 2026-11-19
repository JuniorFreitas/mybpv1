const currentOrigin = window.location.origin

window.URL_SITE = currentOrigin
window.URL_ADMIN = new URL('/g', currentOrigin).href
window.URL_PUBLICO = new URL('/publico', currentOrigin).href
window.AMBIENTE = import.meta.env.VITE_AMBIENTE
window.GOOGLE_MAPS_KEY = import.meta.env.VITE_GOOGLE_MAPS_KEY
