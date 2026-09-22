const currentOrigin = window.location.origin

window.URL_SITE = currentOrigin
window.URL_ADMIN = new URL('/g', currentOrigin).href
window.URL_PUBLICO = new URL('/publico', currentOrigin).href
window.AMBIENTE = process.env.MIX_AMBIENTE;
window.GOOGLE_MAPS_KEY = process.env.MIX_GOOGLE_MAPS_KEY;

const utils = require('./utils');
window.ESTADOS = utils.ESTADOS;
window.EXIBICAO = utils.EXIBICAO;
window.AUTENTICADO = utils.AUTENTICADO;
