/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')
require('./Globals')
require('./registerGlobals')

window.toastr = require('toastr')
toastr.options.closeButton = true
window.moment = require('moment')
moment.locale('pt-BR')

require('bootstrap-daterangepicker')

const Vue = require('vue')
window.Vue = Vue
