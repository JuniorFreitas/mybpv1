<template>
    <div class="row">
        <!-- LADO ESQUEDO     -->
        <div class="col-4">
            <div class="py-4 border-bottom">
                <div class="media">
                    <div class="avatar-xs align-self-center mr-3">
                        <span v-if="EU" class="avatar-title rounded-circle bg-soft-primary text-primary">
                            {{ formatNome(EU.nome) }}
                        </span>
                    </div>
                    <!--  <div class="align-self-center mr-3" v-if="EU">
&lt;!&ndash;                                        <img
                            src="https://themesbrand.com/skote/layouts/assets/images/users/avatar-1.jpg"
                            class="avatar-xs rounded-circle" alt="">&ndash;&gt;
                        <span
                            class="avatar-title rounded-circle bg-soft-primary text-primary" >
                                                    {{ formatNome(EU.nome) }}
                                                </span>
                    </div>-->
                    <div class="media-body">
                        <h5 class="font-size-15 mt-0 mb-1" v-if="EU">{{ EU.nome }}</h5>
                        <p class="text-muted mb-0" v-if="EU && estaOnLine(EU.id)"><i class="mdi mdi-circle text-success align-middle mr-1"></i> On-line</p>
                        <p class="text-muted mb-0" v-else><i class="mdi mdi-circle text-danger align-middle mr-1"></i> Off-line</p>
                    </div>

                    <!-- <div>
                        <div class="dropdown chat-noti-dropdown active">
                            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-bell bx-tada"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </div>
                    </div>-->
                </div>
            </div>

            <div class="chat-leftsidebar-nav" id="tabsChat">
                <ul class="nav nav-pills nav-justified">
                    <li class="nav-item">
                        <a href="#abaChat" data-toggle="tab" aria-expanded="true" class="nav-link active">
                            <i class="bx bx-chat font-size-20 d-sm-none"></i>
                            <span class="d-none d-sm-block">Conversas</span>
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a href="#abaGruposChat" data-toggle="tab" aria-expanded="false" class="nav-link">
                            <i class="bx bx-group font-size-20 d-sm-none"></i>
                            <span class="d-none d-sm-block">Grupos</span>
                        </a>
                    </li>-->
                    <li class="nav-item">
                        <a href="#abaContatosChat" data-toggle="tab" aria-expanded="false" class="nav-link">
                            <i class="bx bx-book-content font-size-20 d-sm-none"></i>
                            <span class="d-none d-sm-block">Contatos</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content py-4">
                    <div class="tab-pane show active" id="abaChat">
                        <ul class="list-unstyled chat-list" style="max-height: 410px">
                            <li v-for="(usuario, index) in contatosRecentes" :key="usuario.id || index">
                                <a href="#" @click.prevent="selecionarContato(usuario)">
                                    <div class="media">
                                        <div class="align-self-center mr-3">
                                            <i class="mdi mdi-circle font-size-10" :class="[estaOnLine(usuario.id) ? 'text-success' : 'text-danger']"></i>
                                        </div>
                                        <!--<div class="align-self-center mr-3">
                                            <img
                                                src="https://themesbrand.com/skote/layouts/assets/images/users/avatar-3.jpg"
                                                class="rounded-circle avatar-xs" alt="">
                                        </div>-->
                                        <div class="avatar-xs align-self-center mr-3">
                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                {{ formatNome(usuario.nome) }}
                                            </span>
                                        </div>

                                        <div class="media-body overflow-hidden">
                                            <h5 class="text-truncate font-size-14 mb-1">
                                                {{ usuario.nome }}
                                            </h5>
                                        </div>
                                        <div class="font-size-15" v-if="mensagensNovas(listaMensagens, usuario.id).length > 0">
                                            <span class="badge badge-pill badge-info">{{ mensagensNovas(listaMensagens, usuario.id).length }}</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!--                                    <div class="tab-pane" id="abaGruposChat">
                                                            <h5 class="font-size-14 mb-3">Grupos</h5>
                                                            <ul class="list-unstyled chat-list" data-simplebar style="max-height: 410px;">
                                                                <li>
                                                                    <a href="#">
                                                                        <div class="media align-items-center">
                                                                            <div class="avatar-xs mr-3">
                                                                                        <span
                                                                                            class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                                            G
                                                                                        </span>
                                                                            </div>

                                                                            <div class="media-body">
                                                                                <h5 class="font-size-14 mb-0">General</h5>
                                                                            </div>
                                                                        </div>
                                                                    </a>
                                                                </li>

                                                                <li>
                                                                    <a href="#">
                                                                        <div class="media align-items-center">
                                                                            <div class="avatar-xs mr-3">
                                                                                        <span
                                                                                            class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                                            R
                                                                                        </span>
                                                                            </div>

                                                                            <div class="media-body">
                                                                                <h5 class="font-size-14 mb-0">Reporting</h5>
                                                                            </div>
                                                                        </div>
                                                                    </a>
                                                                </li>

                                                                <li>
                                                                    <a href="#">
                                                                        <div class="media align-items-center">
                                                                            <div class="avatar-xs mr-3">
                                                                                        <span
                                                                                            class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                                            M
                                                                                        </span>
                                                                            </div>

                                                                            <div class="media-body">
                                                                                <h5 class="font-size-14 mb-0">Meeting</h5>
                                                                            </div>
                                                                        </div>
                                                                    </a>
                                                                </li>

                                                                <li>
                                                                    <a href="#">
                                                                        <div class="media align-items-center">
                                                                            <div class="avatar-xs mr-3">
                                                                                        <span
                                                                                            class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                                            A
                                                                                        </span>
                                                                            </div>

                                                                            <div class="media-body">
                                                                                <h5 class="font-size-14 mb-0">Project A</h5>
                                                                            </div>
                                                                        </div>
                                                                    </a>
                                                                </li>

                                                                <li>
                                                                    <a href="#">
                                                                        <div class="media align-items-center">
                                                                            <div class="avatar-xs mr-3">
                                                                                        <span
                                                                                            class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                                            B
                                                                                        </span>
                                                                            </div>

                                                                            <div class="media-body">
                                                                                <h5 class="font-size-14 mb-0">Project B</h5>
                                                                            </div>
                                                                        </div>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>-->

                    <div class="tab-pane" id="abaContatosChat">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-search"></i></div>
                            </div>

                            <input
                                autocomplete="off"
                                v-model="campoBuscaContatos"
                                type="text"
                                class="form-control"
                                id="inlineFormInputGroup"
                                placeholder="Buscar nome do contato"
                                @keydown.enter="buscarContato"
                                @keyup="campoBuscaContatos === '' ? buscarContato() : false"
                            />
                        </div>

                        <span v-show="preloadContato"> <i class="fa fa-spinner fa-pulse"></i> Buscando... </span>
                        <div class="alert alert-warning" role="alert" v-if="!preloadContato && listaContatos.length === 0">
                            Nenhum contato com o nome <strong>{{ termoBuscaContato }}</strong> foi encontrado
                        </div>
                        <div data-simplebar style="max-height: 410px; overflow-y: scroll" class="mt-3" v-if="!preloadContato && listaContatos.length > 0">
                            <div v-for="(grupo, letra) in grupoDeContatos" :key="letra">
                                <div class="avatar-xs mb-3">
                                    <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                        {{ letra }}
                                    </span>
                                </div>

                                <ul class="list-unstyled chat-list">
                                    <li v-for="(usuario, index) in grupo" :key="usuario.id || index">
                                        <a href="#" @click.prevent="selecionarContato(usuario)">
                                            <h5 class="font-size-14 mb-0">
                                                <i
                                                    class="mdi mdi-circle align-middle mr-1"
                                                    :class="{ 'text-danger': !estaOnLine(usuario.id), 'text-success': estaOnLine(usuario.id) }"
                                                ></i>
                                                {{ usuario.nome }}
                                            </h5>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--     CHAT    -->
        <div class="col-8">
            <div class="card" style="height: 600px">
                <div class="alert alert-info text-center" role="alert" v-if="!CONTATO">
                    <i class="far fa-comments fa-8x"></i>
                    <h4>Localize um contato ou uma conversa recente</h4>
                </div>

                <!-- topo -->
                <div class="p-4 border-bottom" v-if="CONTATO">
                    <div class="row">
                        <div class="col-md-4 col-9">
                            <h5 class="font-size-15 mb-1">{{ CONTATO.nome }}</h5>
                            <p class="text-muted mb-0" v-if="estaOnLine(CONTATO.id)"><i class="mdi mdi-circle text-success align-middle mr-1"></i> On-line</p>
                            <p class="text-muted mb-0" v-else><i class="mdi mdi-circle text-danger align-middle mr-1"></i> Off-line</p>
                        </div>
                        <!--                                    <div class="col-md-8 col-3">
                                                                <ul class="list-inline user-chat-nav text-right mb-0">
                                                                    <li class="list-inline-item d-none d-sm-inline-block">
                                                                        <div class="dropdown">
                                                                            <button class="btn nav-btn dropdown-toggle" type="button"
                                                                                    data-toggle="dropdown" aria-haspopup="true"
                                                                                    aria-expanded="false">
                                                                                <i class="bx bx-search-alt-2"></i>
                                                                            </button>
                                                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-md">
                                                                                <form class="p-3">
                                                                                    <div class="form-group m-0">
                                                                                        <div class="input-group">
                                                                                            <input type="text" class="form-control"
                                                                                                   placeholder="Search ..."
                                                                                                   aria-label="Recipient's username">
                                                                                            <div class="input-group-append">
                                                                                                <button class="btn btn-primary" type="submit"><i
                                                                                                    class="mdi mdi-magnify"></i></button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                    <li class="list-inline-item  d-none d-sm-inline-block">
                                                                        <div class="dropdown">
                                                                            <button class="btn nav-btn dropdown-toggle" type="button"
                                                                                    data-toggle="dropdown" aria-haspopup="true"
                                                                                    aria-expanded="false">
                                                                                <i class="bx bx-cog"></i>
                                                                            </button>
                                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                                <a class="dropdown-item" href="#">View Profile</a>
                                                                                <a class="dropdown-item" href="#">Clear chat</a>
                                                                                <a class="dropdown-item" href="#">Muted</a>
                                                                                <a class="dropdown-item" href="#">Delete</a>
                                                                            </div>
                                                                        </div>
                                                                    </li>

                                                                    <li class="list-inline-item">
                                                                        <div class="dropdown">
                                                                            <button class="btn nav-btn dropdown-toggle" type="button"
                                                                                    data-toggle="dropdown" aria-haspopup="true"
                                                                                    aria-expanded="false">
                                                                                <i class="bx bx-dots-horizontal-rounded"></i>
                                                                            </button>
                                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                                <a class="dropdown-item" href="#">Action</a>
                                                                                <a class="dropdown-item" href="#">Another action</a>
                                                                                <a class="dropdown-item" href="#">Something else</a>
                                                                            </div>
                                                                        </div>
                                                                    </li>

                                                                </ul>
                                                            </div>-->
                    </div>
                </div>

                <!-- Conversa e campo de enviar mensagem -->
                <div style="height: 600px; overflow-y: scroll">
                    <div class="chat-conversation p-3" v-if="CONTATO">
                        <div class="chat-day-title" v-if="preloadMensagensAntigas">
                            <span class="title"><i class="fas fa-spinner fa-spin"></i></span>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <template v-for="(gurpoDeMensagens, dataDasMensagens) in grupoMensagensChat">
                            :key="dataDasMensagens"
                                <li>
                                    <div class="chat-day-title">
                                        <span class="title">{{ formatInfoData(dataDasMensagens) }}</span>
                                    </div>
                                </li>

                                <!--  Fulano falando comigo-->
                                <li
                                    v-for="(mensagem, index) in gurpoDeMensagens"
                                    :key="mensagem.id || index"
                                    :class="{ 'text-right right': mensagem.de.id === EU.id }"
                                    class="caixa_mensagem mb-3"
                                    :ref="`mensagem_id_${mensagem.id}`"
                                >
                                    <div class="conversation-list">
                                        <!--  <div class="dropdown" :class="[mensagem.de.id === EU.id ? 'dropleft':'dropright']">

                                            <a class="dropdown-toggle" href="#" role="button"
                                               data-toggle="dropdown" aria-haspopup="true"
                                               aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-left">
                                                <a class="dropdown-item" href="#">Copiar</a>
                                                &lt;!&ndash;<a class="dropdown-item" href="#">Salvar</a>&ndash;&gt;
                                                <a class="dropdown-item" href="#">Encaminhar</a>
                                                <a class="dropdown-item" href="#">Apagar</a>
                                            </div>
                                        </div>-->
                                        <div class="ctext-wrap text-left">
                                            <div class="conversation-name">{{ mensagem.de.nome }}</div>
                                            <p>
                                                {{ mensagem.mensagem }}
                                            </p>
                                            <p class="chat-time mb-0">
                                                <i class="fa fa-spinner fa-pulse" v-if="!mensagem.updated_at"></i>
                                                <template v-else>
                                                    <i class="bx bx-time-five align-middle mr-1"></i>
                                                    {{ formatHorasMensagem(mensagem.created_at) }}
                                                </template>
                                                <br />
                                                <span>
                                                    <template v-if="mensagem.visto">
                                                        <i class="fas fa-check-double text-info"></i> {{ mensagem.datahora_visto }}
                                                    </template>
                                                    <template v-else> <i class="fas fa-check-double"></i> Não visualizada </template>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div class="p-3 chat-input-section" v-if="CONTATO">
                    <div class="row">
                        <div class="col">
                            <div class="position-relative">
                                <input
                                    type="text"
                                    class="form-control chat-input"
                                    v-model="textoMensagem"
                                    placeholder="Escreva uma mensagem..."
                                    @keydown.enter="enviarMensagem"
                                />
                                <div class="chat-input-links">
                                    <ul class="list-inline mb-0">
                                        <!-- <li class="list-inline-item"><a href="#" data-toggle="tooltip"
                                                                            data-placement="top"
                                                                            title="Emoji"><i
                                                class="mdi mdi-emoticon-happy-outline"></i></a></li>
                                            <li class="list-inline-item"><a href="#" data-toggle="tooltip"
                                                                            data-placement="top"
                                                                            title="Images"><i
                                                class="mdi mdi-file-image-outline"></i></a></li>
                                        <li class="list-inline-item">
                                            <a href="#" data-toggle="tooltip" data-placement="top" title="Add Files">
                                                <i class="mdi mdi-file-document-outline"></i>
                                            </a>
                                        </li>-->
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button
                                @click="enviarMensagem"
                                :disabled="textoMensagem === ''"
                                type="button"
                                class="btn btn-primary btn-rounded chat-send w-md waves-effect waves-light"
                            >
                                <span class="d-none d-sm-inline-block mr-2">Enviar</span> <i class="mdi mdi-send"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import formataNome from '../filters/formataNomeUser'
import { v4 as uuidv4 } from 'uuid'

export default {
    components: {},
    props: {
        id: {
            // cliente_id
            type: Number,
            required: true,
            default: 0
        },
        modalPai: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            URL_ADMIN,

            EU: null,
            CONTATO: null,
            GRUPO: null,
            listaUsuarioOnline: [],

            //Aba contatos----------
            listaContatos: [],
            campoBuscaContatos: '',
            termoBuscaContato: '',
            preloadContato: false,

            //Div conversa
            preloadMensagensAntigas: false,
            textoMensagem: '',
            listaMensagens: [],

            formApagarQuadro: {
                id: null,
                preload: false,
                delete: false,
                erro: false,
                msg: ''
            }
        }
    },
    computed: {
        grupoDeContatos() {
            return _.groupBy(this.listaContatos, (usuario) => {
                return usuario.nome[0]
                    .toUpperCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
            })
        },
        contatosRecentes() {
            let grupoDe = _.uniqBy(this.listaMensagens, (mensagem) => {
                return mensagem.de_id
            }).map((mensagem) => mensagem.de)

            let grupoPara = _.uniqBy(this.listaMensagens, (mensagem) => {
                return mensagem.para_id
            }).map((mensagem) => mensagem.para)

            return _.uniqBy(
                _.concat(grupoDe, grupoPara).filter((usuario) => usuario.id !== this.EU.id),
                (usuario) => usuario.id
            )
        },
        mensagensChat() {
            let lista = []
            if (this.CONTATO && this.EU) {
                lista = this.listaMensagens.filter((mensagem) => {
                    if (
                        (mensagem.de_id === this.CONTATO.id && mensagem.para_id === this.EU.id) ||
                        (mensagem.de_id === this.EU.id && mensagem.para_id === this.CONTATO.id)
                    ) {
                        return true
                    }
                    return false
                })
            }
            return _.orderBy(lista, ['id'], ['asc'])
        },
        grupoMensagensChat() {
            let lista = this.mensagensChat
            if (lista.length > 0) {
                lista = _.groupBy(lista, (mensagem) => {
                    return moment(mensagem.created_at, 'YYYY-MM-DD HH:mm:ss').format('DD/MM/YYYY')
                })
            }
            return lista
        }
    },
    mounted() {
        this.formQuadrosDefault = _.cloneDeep(this.formQuadros)

        //Tela de quadros ------------------------------------------
        this.preload = true
        axios
            .get(`${URL_ADMIN}/chat/${this.id}`)
            .then((response) => {
                this.listaContatos = response.data.contatos
                this.EU = response.data.eu
                this.listaMensagens = response.data.mensagens
                this.conectarWebsocket()
                this.preload = false
                this.$emit('notificar', this.mensagensNovas(this.listaMensagens))

                /*if (!("Notification" in navigator)) {
                    console.log('Esse browser não suporta notificações desktop');
                    console.log(navigator);
                    setTimeout(()=>{
                        console.log(navigator);
                    },5000)
                }*/

                //pedir notifição
                /*if(Notification.permission !== 'granted'){
                     Notification.requestPermission();
                }*/
            })
            .catch((data) => {
                this.preload = false
            })
    },
    methods: {
        formatNome(valor) {
            return formataNome(valor)
        },
        formatHorasMensagem(valor) {
            if (!valor) return ''
            let hoje = moment().format('DD/MM/YYYY')
            let dataDaMensagem = moment(valor).format('DD/MM/YYYY')
            return hoje.toString() === dataDaMensagem.toString() ? moment(valor).format('HH:mm') : moment(valor).format('DD/MM/YYYY HH:mm')
        },
        formatInfoData(valor) {
            if (!valor) return ''
            let hoje = moment().format('DD/MM/YYYY')
            let dataDaMensagem = moment(valor, 'DD/MM/YYYY').format('DD/MM/YYYY')
            return hoje.toString() === dataDaMensagem.toString() ? 'Hoje' : moment(dataDaMensagem, 'DD/MM/YYYY').format('dddd, DD/MM/YYYY')
        },
        conectarWebsocket() {
            // Chat
            Echo.join(`chat.${this.id}.mensagens.contato.${this.EU.id}`)
                .listen('.insert', (e) => {
                    this.listaMensagens.push(e.mensagem)
                    this.$emit('notificar', this.mensagensNovas(this.listaMensagens))
                    this.scrollToBottom()
                    if (e.mensagem.para_id === this.EU.id) {
                        let notification = new Notification(`${e.mensagem.de.nome} diz:`, {
                            body: e.mensagem.mensagem,
                            icon: `${URL_SITE}/images/icons/apple-icon-precomposed.png`
                        })
                        notification.onclick = (e) => {
                            e.preventDefault()
                            window.focus()
                            notification.close()
                        }
                    }
                    //se já estou batendo papo com esse contato na tela, notificar que eu vi a mensagem
                    if (this.CONTATO && e.mensagem.de_id === this.CONTATO.id) {
                        this.visualizouMensagem([e.mensagem.id])
                    }
                })
                .listen('.visto', (e) => {
                    if (e.mensagens.length > 0) {
                        e.mensagens.forEach((msg) => {
                            let mensagemFind = _.find(this.listaMensagens, { id: msg.id })
                            if (mensagemFind) {
                                Object.assign(mensagemFind, msg)
                            }
                        })
                        this.$emit('notificar', this.mensagensNovas(this.listaMensagens))
                    }
                })

            Echo.join(`chat.${this.id}`)
                .here((users) => {
                    //console.log('usuarios aqui');
                    this.listaUsuarioOnline = users
                    //console.log(users);
                })
                .joining((user) => {
                    //console.log('entrou');
                    //console.log(user);
                    this.listaUsuarioOnline.push(user)
                })
                .leaving((user) => {
                    //console.log('saiu');
                    //console.log(user);
                    let userIndex = _.findIndex(this.listaUsuarioOnline, { id: user.id })
                    if (userIndex !== -1) {
                        this.listaUsuarioOnline.splice(userIndex, 1)
                    }
                })
        },
        //Aba contatos ---------------------
        buscarContato() {
            this.preloadContato = true
            this.termoBuscaContato = this.campoBuscaContatos
            axios
                .get(`${URL_ADMIN}/chat/${this.id}/buscaContato?busca=${this.campoBuscaContatos}`)
                .then((response) => {
                    this.listaContatos = response.data
                    this.preloadContato = false
                })
                .catch((data) => {
                    this.preloadContato = false
                })
        },
        selecionarContato(contato) {
            if (this.CONTATO == null) {
                this.CONTATO = contato
                this.carregarMaisMensagens()
            }
            if (this.CONTATO && contato.id !== this.CONTATO.id) {
                this.CONTATO = contato
                this.carregarMaisMensagens()
            }
            let mensagensNaoLidas = this.mensagensNovas(this.listaMensagens, this.CONTATO.id).map((mensagem) => mensagem.id)
            this.visualizouMensagem(mensagensNaoLidas)
            this.scrollToBottom()

            //$('#tabsChat a[href="#abaChat"]').tab('show');
        },
        carregarMaisMensagens() {
            let lista = this.mensagensChat
            if (lista.length > 0) {
                this.preloadMensagensAntigas = true

                axios
                    .post(`${URL_ADMIN}/chat/${this.id}/carregarMaisMensagens`, {
                        ultimo_id: lista[0].id,
                        contato_id: this.CONTATO.id
                    })
                    .then((response) => {
                        response.data.mensagens.forEach((mensagemAntiga) => {
                            let encontrar = _.find(this.listaMensagens, { id: mensagemAntiga.id })
                            if (!encontrar) {
                                this.listaMensagens.push(mensagemAntiga)
                            }
                        })
                        this.preloadMensagensAntigas = false
                        this.scrollToBottom()
                    })
                    .catch((data) => {
                        this.preloadMensagensAntigas = false
                        /*let mensagemIndex = _.findIndex(this.listaMensagens, {id: id_rash});
                        if (mensagemIndex !== -1) {
                            this.listaMensagens.splice(mensagemIndex, 1);
                        }*/
                    })
            }
        },
        //Div mensagens------------------
        enviarMensagem() {
            let id_rash = uuidv4()
            let novaMensagem = {
                id: id_rash,
                de: this.EU,
                de_id: this.EU.id,
                para: this.CONTATO ? this.CONTATO : null,
                para_id: this.CONTATO ? this.CONTATO.id : null,
                grupo_id: this.GRUPO ? this.GRUPO : null,
                mensagem: this.textoMensagem,
                created_at: moment().format('YYYY-MM-DD HH:mm:ss')
            }

            axios
                .post(`${URL_ADMIN}/chat/${this.id}/enviarMensagem`, {
                    para_id: this.CONTATO ? this.CONTATO.id : null,
                    grupo_id: this.GRUPO ? this.GRUPO.id : null,
                    mensagem: this.textoMensagem
                })
                .then((response) => {
                    let mensagem = _.find(this.listaMensagens, { id: id_rash })
                    if (mensagem) {
                        Object.assign(mensagem, response.data)
                        this.scrollToBottom()
                    }
                })
                .catch((data) => {
                    let mensagemIndex = _.findIndex(this.listaMensagens, { id: id_rash })
                    if (mensagemIndex !== -1) {
                        this.listaMensagens.splice(mensagemIndex, 1)
                    }
                })

            this.listaMensagens.push(novaMensagem)
            this.textoMensagem = ''
            this.scrollToBottom()
        },
        scrollToBottom() {
            if (this.listaMensagens.length) {
                setTimeout(() => {
                    let idMensagem = this.mensagensChat[this.mensagensChat.length - 1] ? this.mensagensChat[this.mensagensChat.length - 1].id : false
                    if (idMensagem) {
                        let elemento = this.$refs[`mensagem_id_${idMensagem}`][0]
                        if (elemento) {
                            elemento.scrollIntoView()
                        }
                    }
                }, 70)
            }
        },
        estaOnLine(user_id) {
            return this.listaUsuarioOnline.filter((usuario) => usuario.id === user_id).length > 0
        },
        mensagensNovas(array, user_id = null) {
            return array.filter((mensagem) => {
                if (user_id) {
                    return !mensagem.visto && mensagem.de_id === user_id
                } else {
                    if (this.EU) {
                        return !mensagem.visto && mensagem.para_id === this.EU.id
                    } else {
                        return !mensagem.visto
                    }
                }
            })
        },
        visualizouMensagem(array) {
            if (array.length > 0) {
                axios
                    .put(`${URL_ADMIN}/chat/${this.id}/visualizarMensagem`, {
                        lista: array
                    })
                    .then((response) => {
                        let lista = response.data.mensagens
                        if (lista.length > 0) {
                            lista.forEach((mensagem) => {
                                let mensagemFind = _.find(this.listaMensagens, { id: mensagem.id })
                                if (mensagemFind) {
                                    Object.assign(mensagemFind, mensagem)
                                    this.$emit('notificar', this.mensagensNovas(this.listaMensagens))
                                }
                            })
                        }
                    })
                    .catch((data) => {})
            }
        }
    }
}
</script>

<style>
textarea[autoresize] {
    display: block;
    overflow: hidden;
    resize: none;
}

.tab-content {
    border: none;
}
</style>
