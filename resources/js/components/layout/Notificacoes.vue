<template>
    <div>
        <div ref="listaDeNotificoes" style="position: absolute; top: 10px; right: 10px" v-show="Z_INDEX > 0" :style="Z_INDEX > 0 ? `z-index: ${Z_INDEX}` : ''">
            <div
                class="toast bg-white"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
                v-for="(notificacao, index) in notificacoesNaoVistas"
                :id="`notificacaoID${notificacao.id}`"
                :key="notificacao.id || index"
            >
                <div class="toast-header">
                    <i :class="notificacao.payload.icone" class="mr-2"></i> <strong class="mr-auto"> {{ notificacao.payload.titulo }}</strong>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    {{ notificacao.payload.descricao }}
                </div>
            </div>
        </div>

        <div class="dropdown d-inline-block">
            <button
                type="button"
                class="btn header-item noti-icon waves-effect"
                @click="lerNotificacoes"
                id="page-header-notifications-dropdown"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
            >
                <i class="bx bx-bell text-white"></i>
                <span class="badge badge-danger badge-pill" v-if="notificacoesNovas.length > 0">{{ notificacoesNovas.length }}</span>
            </button>
            <div class="dropdown-menu dropdown-menu-custom dropdown-menu-lg dropdown-menu-right p-0" aria-labelledby="page-header-notifications-dropdown">
                <div class="p-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-0">Minhas notificações</h6>
                        </div>
                        <!--                            <div class="col-auto">
                                                        <a href="#!" class="small"> Ver todas</a>
                                                    </div>-->
                    </div>
                </div>
                <template v-if="lista.length === 0">
                    <div data-simplebar style="max-height: 230px">
                        <a href="void(0)" class="text-reset notification-item">
                            <div class="media">
                                <div class="avatar-xs mr-3">
                                    <span class="avatar-title bg-primary rounded-circle font-size-16">
                                        <i class="far fa-bell"></i>
                                    </span>
                                </div>
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1">Nenhuma notificação para você.</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </template>
                <div v-else data-simplebar style="max-height: 230px">
                    <a v-for="(notificacao, index) in lista" :key="notificacao.id || index" href="" class="text-reset notification-item">
                        <div class="media">
                            <div class="avatar-xs mr-3">
                                <span class="avatar-title bg-primary rounded-circle font-size-16">
                                    <i :class="notificacao.payload.icone"></i>
                                </span>
                            </div>
                            <div class="media-body">
                                <h6 class="mt-0 mb-1">{{ notificacao.payload.titulo }}</h6>
                                <div class="font-size-12 text-muted">
                                    <p class="mb-1">{{ notificacao.payload.descricao }}</p>
                                    <!--                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span>3 min ago</span></p>-->
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    components: {},
    props: {
        usuario: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            quantidadeMensagensNovas: 0,
            lista: [],
            URL_SITE,
            URL_ADMIN,
            Z_INDEX: 0
        }
    },
    computed: {
        notificacoesVista() {
            return this.lista.filter((not) => not.visto)
        },
        notificacoesNovas() {
            return this.lista.filter((not) => !not.visto)
        },
        notificacoesNaoVistas() {
            return this.lista.filter((notificacao) => notificacao && !notificacao.visto)
        }
    },
    mounted() {
        axios
            .get(`${URL_ADMIN}/notificacoes/${this.usuario.id}`)
            .then((res) => {
                res.data.forEach((not) => {
                    this.lista.push(not)
                })
                // this.mostrarToast();
            })
            .catch((error) => {
                mostraErro('', 'Erro ao carregar novas notificações')
            })
        //real time
        if (this.usuario.empresa_id) {
            Echo.join(`notificacoes.${this.usuario.id}`)
                .listen('.membro_tarefa_add', (e) => {
                    this.lista.push(e)
                    this.mostrarToast()
                })
                .listen('.membro_tarefa_remove', (e) => {
                    this.lista.push(e)
                    this.mostrarToast()
                })
                .listen('.exportacao_excel', (e) => {
                    this.lista.push(e)
                    this.mostrarToast()
                })
                .listen('.exportacao_pdf', (e) => {
                    // console.log(e);
                    // console.log(this.lista);
                    this.lista.push(e)
                    this.mostrarToast()
                })
                .listen('.importacao_admissoes_concluida', (e) => {
                    this.lista.push(e)
                    this.mostrarToast()
                })
        }
    },
    methods: {
        mostrarToast() {
            this.Z_INDEX = 2000
            setTimeout(() => {
                this.lista.forEach((not) => {
                    $(`#notificacaoID${not.id}`).toast({
                        delay: 10000
                    })
                    $(`#notificacaoID${not.id}`).on('hidden.bs.toast', () => {
                        /*let notificacao = _.find(this.lista, {id: not.id});
                        if (notificacao) {
                            notificacao.visto = true;
                        }*/
                    })
                    $(`#notificacaoID${not.id}`).toast('show')
                })
            }, 100)
        },
        lerNotificacoes() {
            this.Z_INDEX = 2000
            let lista = this.notificacoesNovas.map((not) => not.id)
            if (lista.length) {
                lista.forEach((id) => {
                    let notificacao = _.find(this.lista, { id: id })
                    if (notificacao) {
                        notificacao.visto = true
                    }
                })
                axios.post(`${URL_ADMIN}/notificacoes/${this.usuario.id}`, { lista: lista })
            }
        }
    }
}
</script>

<style scoped></style>
