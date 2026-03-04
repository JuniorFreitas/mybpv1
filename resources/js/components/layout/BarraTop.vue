<template>
    <header class="page-topbar shadow-sm bg-white">
        <modal id="janelaPerfil" titulo="Seu Perfil" size="g">
            <template #conteudo>
                <span v-show="preload">
                    <preload />
                </span>
                <template v-if="!preload">
                    <div class="form-group">
                        <label>Nome do usuário</label>
                        <input
                            type="text"
                            v-model="form.nome"
                            class="form-control form-control-sm"
                            placeholder="Nome do usuário"
                            autocomplete="off"
                            onblur="valida_campo_vazio(this, 3)"
                        />
                    </div>
                    <div class="form-group">
                        <label>Login</label>
                        <input
                            type="text"
                            v-model="form.login"
                            class="form-control form-control-sm"
                            placeholder="Login"
                            autocomplete="off"
                            onblur="valida_campo_vazio(this, 3)"
                        />
                    </div>
                    <fieldset>
                        <legend>Foto de Perfil</legend>
                        <!-- <p>DECLARAÇÃO ESCOLAR DO ANO EM CURSO (ORIGINAL)</p> -->
                        <upload
                            label="Selecionar anexo(s)"
                            :dados-ajax="{ usuario_id: usuario.id }"
                            :model="form.foto_perfil"
                            :apenas-imagens="true"
                            :quantidade="1"
                            :model-delete="form.foto_perfilDel"
                            :url="urlAnexoUpload"
                            @onprogresso="anexoUploadAndamento = true"
                            @onfinalizado="anexoUploadAndamento = false"
                            :multi="true"
                        >
                        </upload>
                    </fieldset>
                </template>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-primary" v-show="!preload" @click="alterarFormPerfil()">Salvar</button>
            </template>
        </modal>

        <modal id="download" titulo="Meus Downloads" size="g">
            <template #conteudo>
                <preload v-show="preloadDownload" />
                <template v-if="!preloadDownload">
                    <div class="table-responsive">
                        <table class="tabela">
                            <thead>
                                <tr class="bg-default">
                                    <th class="text-center">Local</th>
                                    <th class="text-center">Data</th>
                                    <th class="text-center" />
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="download in downloads">
                                    <td>{{ download.local }}</td>
                                    <td>{{ download.data_hora_criacao }}</td>
                                    <td>
                                        <a :href="URL_ADMIN + '/downloads/exportacao/' + download.arquivo" class="btn btn-primary" target="_blank">
                                            <i class="fa fa-download" />
                                            Download
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </template>
        </modal>

        <!-- Janela do chat -->
        <modal id="janelaChat" titulo="Chat" size="g">
            <template #conteudo>
                <chat v-if="usuario.empresa_id" :id="usuario.empresa_id" @notificar="notificacao" />
            </template>
            <template #rodape>
                <!-- Rodapé vazio -->
            </template>
        </modal>

        <modal id="janelaConfirmarSair" titulo="Sair" :centralizada="true" label-fechar="Não">
            <template #conteudo>
                <div class="text-center text-default">
                    <i class="fa fa-exclamation-triangle" style="font-size: 67px" />
                    <h6 class="text-center mt-2">Você realmente deseja Sair do Sistema?</h6>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-danger" @click="sair">SIM</button>
            </template>
        </modal>

        <div class="navbar-header fixed-top bg-primary">
            <div class="d-flex">
                <div class="navbar-brand-box">
                    <a :href="URL_SITE" class="logo logo-light">
                        <span class="logo-sm">
                            <img :src="`${URL_SITE}/images/icone.svg`" alt="Mybp logo icone" height="40" />
                        </span>
                        <span class="logo-lg">
                            <img
                                :src="`${URL_SITE}/images/logo_horizontal.svg`"
                                alt="Mybp logo horizontal"
                                height="65"
                                style="margin-left: -32px; margin-top: 5px"
                            />
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" @click="verticalMenu">
                    <i class="fa fa-fw fa-bars text-white" />
                </button>
            </div>

            <div class="d-flex">
                <!-- Comentário removido para melhor legibilidade -->

                <div class="dropdown ml-1">
                    <button
                        type="button"
                        v-tippy
                        class="btn header-item noti-icon waves-effect"
                        content="Downloads"
                        data-toggle="modal"
                        data-target="#download"
                        @click.prevent="meusDownloads()"
                    >
                        <i class="bx bx-download text-white" />
                    </button>
                </div>

                <!-- Componente notificações -->
                <notificacoes :usuario="usuario" />

                <!-- CHAT -->
                <div v-if="usuario.empresa_id" class="dropdown ml-1">
                    <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="modal" data-target="#janelaChat">
                        <i class="far fa-comments text-white" />
                        <span v-if="quantidadeMensagensNovas > 0" class="badge badge-pill badge-danger">
                            {{ quantidadeMensagensNovas }}
                        </span>
                    </button>
                </div>

                <!-- Tela cheia -->
                <div class="dropdown ml-1">
                    <button type="button" class="btn header-item noti-icon waves-effect" @click.prevent="fullscreen">
                        <i class="bx bx-fullscreen text-white" />
                    </button>
                </div>

                <!-- Usuário -->
                <div class="dropdown d-inline-block">
                    <button
                        type="button"
                        id="page-header-user-dropdown"
                        class="btn header-item waves-effect"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <img
                            class="rounded-circle header-profile-user"
                            :src="usuario.foto_perfil.length === 0 ? `${URL_SITE}/images/default_avatar.jpg` : usuario.foto_perfil[0].urlThumb"
                            alt="Header Avatar"
                        />
                        <span class="d-none d-xl-inline-block ml-1 text-left text-white">
                            {{ usuario.nome }}
                        </span>
                        <i class="mdi mdi-chevron-down text-white d-none d-xl-inline-block" />
                    </button>
                    <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right">
                        <!-- item-->
                        <span class="dropdown-item d-xl-inline-block ml-1">
                            <span class="d-block d-sm-none">{{ usuario.nome }} / </span>
                            {{ usuario.papel && usuario.papel.nome ? usuario.papel.nome : 'Nenhum Grupo' }}
                        </span>
                        <a
                            class="dropdown-item"
                            data-toggle="modal"
                            @click.prevent="alterarPerfil(usuario.id)"
                            data-target="#janelaPerfil"
                            href="javascript://"
                        >
                            <i class="bx bx-user font-size-16 align-middle mr-1" />
                            <span>Perfil</span>
                        </a>
                        <a class="dropdown-item" :href="`${URL_ADMIN}/alterar-senha`">
                            <i class="bx bx-wallet font-size-16 align-middle mr-1" />
                            <span key="t-my-wallet">Alterar senha</span>
                        </a>
                        <!-- Comentários removidos para melhor legibilidade -->
                        <div class="dropdown-divider" />
                        <a class="dropdown-item text-danger" href="javascript://" data-toggle="modal" data-target="#janelaConfirmarSair">
                            <i class="bx bx-power-off font-size-16 align-middle mr-1 text-danger" />
                            <span>Sair</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
import chat from '../Chat'
import notificacoes from '../layout/Notificacoes'
import upload from '../Upload'

export default {
    components: {
        chat,
        notificacoes,
        upload
    },
    props: {
        usuario: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            preload: false,
            anexoUploadAndamento: false,
            urlAnexoUpload: `${URL_ADMIN}/perfil/anexo/uploadAnexos`,
            form: {
                id: '',
                nome: '',
                login: '',
                foto_perfil: [],
                foto_perfilDel: []
            },
            formDefault: null,
            quantidadeMensagensNovas: 0,
            preloadDownload: true,
            downloads: [],
            full: false,
            URL_SITE,
            URL_ADMIN
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form)
        this.initDropdowns()
    },
    methods: {
        initDropdowns() {
            if (typeof $ === 'undefined' || !$.fn || !$.fn.dropdown) {
                return
            }
            $(this.$el).find('[data-toggle="dropdown"]').dropdown()
        },
        notificacao(arryMensagensNovas) {
            this.quantidadeMensagensNovas = arryMensagensNovas.length
        },

        verticalMenu() {
            $('body').toggleClass('sidebar-enable')
            if ($(window).width() >= 992) {
                $('body').toggleClass('vertical-collpsed')
            } else {
                $('body').removeClass('vertical-collpsed')
            }
        },
        fullscreen() {
            this.full = !this.full
            $('body').toggleClass('fullscreen-enable')

            if (!document.fullscreenElement && !document.mozFullScreenElement && !document.webkitFullscreenElement) {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen()
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen()
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT)
                }
            } else {
                if (document.cancelFullScreen) {
                    document.cancelFullScreen()
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen()
                } else if (document.webkitCancelFullScreen) {
                    document.webkitCancelFullScreen()
                }
            }

            document.addEventListener('fullscreenchange', exitHandler)
            document.addEventListener('webkitfullscreenchange', exitHandler)
            document.addEventListener('mozfullscreenchange', exitHandler)

            function exitHandler() {
                if (!document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement) {
                    $('body').removeClass('fullscreen-enable')
                }
            }
        },
        sair() {
            window.location.href = `${URL_ADMIN}/sair`
        },

        alterarPerfil(id) {
            this.preload = true
            formReset()
            this.form = _.cloneDeep(this.formDefault)

            axios
                .get(URL_ADMIN + '/perfil/' + id)
                .then((response) => {
                    this.preload = false
                    let data = response.data
                    Object.assign(this.form, data.user)
                })
                .catch((error) => (this.preload = false))
        },
        meusDownloads() {
            this.preloadDownload = true
            axios
                .post(`${URL_ADMIN}/downloads`)
                .then((response) => {
                    this.downloads = response.data
                    this.preloadDownload = false
                })
                .catch((error) => (this.preloadDownload = false))
        },

        alterarFormPerfil() {
            formReset()
            $('#janelaPerfil :input:enabled').trigger('blur')

            if ($('#janelaPerfil :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preload = true

            axios
                .put(URL_ADMIN + '/perfil/' + this.form.id, this.form)
                .then((response) => {
                    $('#janelaPerfil').modal('hide')
                    mostraSucesso('', 'Perfil Atualizado com sucesso!')
                    this.preload = false
                    setTimeout(function () {
                        document.location.reload(true)
                    }, 1000)
                })
                .catch((error) => (this.preload = false))
        }
    }
}
</script>

<style scoped>
.dropdown-menu.show {
    top: 70px !important;
}

.personal-image {
    text-align: center;
}

.personal-image input[type='file'] {
    display: none;
}

.personal-figure {
    position: relative;
    width: 120px;
    height: 120px;
}

.personal-avatar {
    cursor: pointer;
    width: 120px;
    height: 120px;
    box-sizing: border-box;
    border-radius: 100%;
    border: 2px solid transparent;
    box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.2);
    transition: all ease-in-out 0.3s;
}

.personal-avatar:hover {
    box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.5);
}

.personal-figcaption {
    cursor: pointer;
    position: absolute;
    top: 0px;
    width: inherit;
    height: inherit;
    border-radius: 100%;
    opacity: 0;
    background-color: rgba(0, 0, 0, 0);
    transition: all ease-in-out 0.3s;
}

.personal-figcaption:hover {
    opacity: 1;
    background-color: rgba(0, 0, 0, 0.5);
}

.personal-figcaption > img {
    margin-top: 32.5px;
    width: 50px;
    height: 50px;
}
</style>
