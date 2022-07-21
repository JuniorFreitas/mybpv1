/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");
//Componentes Globais
require("./Globals");

window.toastr = require("toastr");
toastr.options.closeButton = true;
window.moment = require("moment");
moment.locale("pt-BR");

require("bootstrap-daterangepicker");

window.Vue = require("vue");

import VueTippy, { TippyComponent } from "vue-tippy";
// import configuracoes from './mixins/Configuracoes';

// Vue.mixin(configuracoes);


Vue.use(VueTippy);
// or
Vue.use(VueTippy, {
    directive: "tippy", // => v-tippy
    flipDuration: 0,
    popperOptions: {
        modifiers: {
            preventOverflow: {
                enabled: false
            }
        }
    }
});

Vue.component("tippy", TippyComponent).default;
Vue.component("preload", require("./components/preload").default);
Vue.component("btn-atualiza", require("./components/btnAtualiza").default);
Vue.component("controle-paginacao", require("./components/ControlePaginacao").default);
Vue.component("datepicker", require("./components/DatePicker").default);
Vue.component("modal", require("./components/Modal").default);
Vue.component("autocomplete", require("./components/AutoComplete").default);
Vue.component("bt-ativo", require("./components/AtivoInativo").default);
Vue.component("barra-top", require("./components/layout/BarraTop").default);
//
//
// //Diretivas globais
require("./diretivas/mascaras");
require("./diretivas/popover");
