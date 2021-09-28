window.URL_SITE = process.env.MIX_URL_SITE;
window.URL_ADMIN = process.env.MIX_URL_ADMIN;
window.URL_PUBLICO = process.env.MIX_URL_PUBLICO;
window.AMBIENTE = process.env.MIX_AMBIENTE;
window.GOOGLE_MAPS_KEY = process.env.MIX_GOOGLE_MAPS_KEY;

const utils = require('./utils');
window.ESTADOS = utils.ESTADOS;
window.EXIBICAO = utils.EXIBICAO;
window.AUTENTICADO = utils.AUTENTICADO;
