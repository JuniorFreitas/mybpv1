/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap'
import './Globals'
import { registerGlobals } from './registerGlobals'
import * as Vue from 'vue'
import toastr from 'toastr'
import moment from 'moment'
import './ensure-daterangepicker'

// Vue 3 exposto globalmente para blades que usam Vue.createApp() em script inline (ex.: dashboard, login)
window.Vue = Vue
window.registerGlobals = registerGlobals

window.toastr = toastr
toastr.options.closeButton = true
window.moment = moment
moment.locale('pt-BR')
