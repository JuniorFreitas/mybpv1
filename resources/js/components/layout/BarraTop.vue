<template>
    <header class="page-topbar shadow-sm bg-white">

        <!--Janela ddo chat -->
        <modal id="janelaChat" titulo="Chat" size="g">
            <template slot="conteudo">

                <chat :id="usuario.empresa_id" v-if="usuario.empresa_id" @notificar="notificacao"></chat>

            </template>
            <template slot="rodape">

            </template>
        </modal>

        <modal id="janelaConfirmarSair" titulo="Sair" :centralizada="true" label-fechar="Não">
            <template slot="conteudo">
                <div class="text-center text-default">
                    <i class="fa fa-exclamation-triangle" style="font-size: 67px;"></i>
                    <h6 class="text-center mt-2">Você realmente deseja Sair do Sistema?</h6>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-danger" @click="sair">
                    SIM
                </button>
            </template>
        </modal>

        <div class="navbar-header fixed-top  bg-primary">
            <div class="d-flex">
                <div class="navbar-brand-box">
                    <a :href="URL_SITE" class="logo logo-light">
                        <span class="logo-sm">
                            <img :src="`${URL_SITE}/images/icone.svg`" alt="SGIBPSE logo icone" height="40">
                        </span>
                        <span class="logo-lg">
                            <img :src="`${URL_SITE}/images/logo_horizontal.svg`" alt="SGIBPSE logo horizontal"
                                 height="65" style="margin-left: -32px; margin-top: 5px;">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect"
                        @click="verticalMenu">
                    <i class="fa fa-fw fa-bars text-white"></i>
                </button>

            </div>

            <div class="d-flex">

                <!--                                <div class="dropdown d-none d-lg-inline-block ml-1">
                                                    <button type="button" class="btn header-item noti-icon waves-effect"
                                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="bx bx-customize"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                        <div class="px-lg-2">
                                                            <div class="row no-gutters">
                                                                <div class="col">
                                                                    <a class="dropdown-icon-item" href="#">
                                                                        <img src="assets/images/brands/github.png" alt="Github">
                                                                        <span>GitHub</span>
                                                                    </a>
                                                                </div>
                                                                <div class="col">
                                                                    <a class="dropdown-icon-item" href="#">
                                                                        <img src="assets/images/brands/bitbucket.png" alt="bitbucket">
                                                                        <span>Bitbucket</span>
                                                                    </a>
                                                                </div>
                                                                <div class="col">
                                                                    <a class="dropdown-icon-item" href="#">
                                                                        <img src="assets/images/brands/dribbble.png" alt="dribbble">
                                                                        <span>Dribbble</span>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <div class="row no-gutters">
                                                                <div class="col">
                                                                    <a class="dropdown-icon-item" href="#">
                                                                        <img src="assets/images/brands/dropbox.png" alt="dropbox">
                                                                        <span>Dropbox</span>
                                                                    </a>
                                                                </div>
                                                                <div class="col">
                                                                    <a class="dropdown-icon-item" href="#">
                                                                        <img src="assets/images/brands/mail_chimp.png" alt="mail_chimp">
                                                                        <span>Mail Chimp</span>
                                                                    </a>
                                                                </div>
                                                                <div class="col">
                                                                    <a class="dropdown-icon-item" href="#">
                                                                        <img src="assets/images/brands/slack.png" alt="slack">
                                                                        <span>Slack</span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>-->

                <!-- Componente notificações -->
                <notificacoes :usuario="usuario"></notificacoes>

                <!-- CHAT -->
                <div class="dropdown d-none d-lg-inline-block ml-1" v-if="usuario.empresa_id">
                    <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="modal"
                            data-target="#janelaChat">
                        <i class="far fa-comments text-white"></i> <span class="badge badge-pill badge-danger"
                                                                         v-if="quantidadeMensagensNovas > 0">{{
                            quantidadeMensagensNovas
                        }}</span>
                    </button>
                </div>

                <!-- Tela cheia -->
                <div class="dropdown d-none d-lg-inline-block ml-1">
                    <button type="button" class="btn header-item noti-icon waves-effect" @click.prevent="fullscreen">
                        <i class="bx bx-fullscreen text-white"></i>
                    </button>
                </div>


                <!-- Usuário -->
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="rounded-circle header-profile-user" :src="`${URL_SITE}/images/default_avatar.jpg`"
                             alt="Header Avatar">
                        <span class="d-none d-xl-inline-block ml-1 text-left text-white">{{ usuario.nome }}</span>
                        <i class="mdi mdi-chevron-down text-white d-none d-xl-inline-block"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <!-- item-->
                        <span class="dropdown-item d-xl-inline-block ml-1"><span
                            class="d-block d-sm-none">{{ usuario.nome }} / </span>{{ usuario.papel.nome }}</span>
                        <a class="dropdown-item" href="#"><i class="bx bx-user font-size-16 align-middle mr-1"></i>
                            <span>Perfil</span></a>
                        <a class="dropdown-item" :href="`${URL_ADMIN}/alterar-senha`"><i
                            class="bx bx-wallet font-size-16 align-middle mr-1"></i> <span
                            key="t-my-wallet">Alterar senha</span></a>
                        <!--                        <a class="dropdown-item d-block" href="#"><span-->
                        <!--                            class="badge badge-success float-right">11</span><i-->
                        <!--                            class="bx bx-wrench font-size-16 align-middle mr-1"></i> <span-->
                        <!--                            key="t-settings">Settings</span></a>-->
                        <!--                        <a class="dropdown-item" href="#"><i-->
                        <!--                            class="bx bx-lock-open font-size-16 align-middle mr-1"></i> <span-->
                        <!--                            key="t-lock-screen">Lock screen</span></a>-->
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript://"
                           data-toggle="modal"
                           data-target="#janelaConfirmarSair"
                        ><i
                            class="bx bx-power-off font-size-16 align-middle mr-1 text-danger"></i>
                            <span>Sair</span></a>
                    </div>
                </div>

            </div>
        </div>

    </header>
</template>

<script>

import chat from "../Chat";
import notificacoes from "../layout/Notificacoes";

export default {
    components: {
        chat,
        notificacoes,
        //datepicker,
        //upload
    },
    props: {
        usuario: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            quantidadeMensagensNovas: 0,
            full: false,
            //notificacao: true,
            URL_SITE,
            URL_ADMIN
        }
    },
    mounted() {


    },
    methods: {

        notificacao(arryMensagensNovas) {
            this.quantidadeMensagensNovas = arryMensagensNovas.length;
        },
        verticalMenu() {
            $('body').toggleClass('sidebar-enable');
            if ($(window).width() >= 992) {
                $('body').toggleClass('vertical-collpsed');
            } else {
                $('body').removeClass('vertical-collpsed');
            }
        },
        fullscreen() {
            this.full = !this.full;
            $('body').toggleClass('fullscreen-enable');
            if (!document.fullscreenElement && /* alternative standard method */ !document.mozFullScreenElement && !document.webkitFullscreenElement) {  // current working methods
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                }
            } else {
                if (document.cancelFullScreen) {
                    document.cancelFullScreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitCancelFullScreen) {
                    document.webkitCancelFullScreen();
                }
            }

            document.addEventListener('fullscreenchange', exitHandler);
            document.addEventListener("webkitfullscreenchange", exitHandler);
            document.addEventListener("mozfullscreenchange", exitHandler);

            function exitHandler() {
                if (!document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement) {

                    $('body').removeClass('fullscreen-enable');
                }
            }
        },
        sair() {
            // window.open()
            window.location.href = `${URL_ADMIN}/sair`;
        }
    }
}
</script>

<style scoped>
.dropdown-menu.show {
    top: 70px !important;
}
</style>
