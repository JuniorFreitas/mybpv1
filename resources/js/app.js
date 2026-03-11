/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')
require('./Globals')
require('./registerGlobals')

// Vue 3 exposto globalmente para blades que usam Vue.createApp() em script inline (ex.: dashboard, login)
import * as Vue from 'vue'
window.Vue = Vue

window.toastr = require('toastr')
toastr.options.closeButton = true
window.moment = require('moment')
moment.locale('pt-BR')

require('bootstrap-daterangepicker')
