/**
 * Bundle classico (IIFE) para blades/scripts inline que dependem de
 * window.$, window.Vue, window.axios, URL_*, registerGlobals, etc.
 *
 * Vite carrega entries como type=module (deferred). Este IIFE restaura
 * o contrato sincrono que o Mix garantia antes de funcoes.js / Vue.createApp inline.
 */
import * as Vue from 'vue'
import { registerGlobals } from './registerGlobals'

window.Vue = Vue
window.registerGlobals = registerGlobals
