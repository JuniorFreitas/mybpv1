<template>
    <div class="cloud-cadastro">
        <!-- FORMULÁRIO -->
        <div v-if="open">
            <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                <h4 class="text-default mb-0">{{ upper(tituloJanela) }}</h4>
                <button type="button" class="btn btn-sm btn-secondary" @click.prevent="voltar">
                    <i class="fa fa-arrow-left"></i> Voltar
                </button>
            </div>

            <preload class="my-2" v-show="preloadAjax"></preload>

            <div v-show="!preloadAjax">
                <fieldset>
                    <legend>Dados do Cloud</legend>
                    <form @submit.prevent="submitForm">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nome</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="form.nome"
                                        placeholder="Nome do Cloud"
                                        autocomplete="off"
                                        onblur="valida_campo_vazio(this, 2)"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ativo</label>
                                    <select class="form-control form-control-sm" v-model="form.ativo">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" v-if="editando" @click="alterar">
                            <i class="fa fa-save"></i> Salvar alterações
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" v-else @click="cadastrar">
                            <i class="fa fa-save"></i> Cadastrar
                        </button>
                    </form>
                </fieldset>

                <fieldset v-if="editando">
                    <legend>Acesso — {{ form.usuarios.length }} membro(s)</legend>

                    <div class="alert alert-info py-2">
                        Membros do grupo <strong>Administradores</strong> entram automaticamente e não podem ser removidos por aqui.
                        Ao escolher um grupo, os colaboradores ativos dele são incluídos na lista.
                    </div>

                    <div class="btn-group btn-group-sm mb-3" role="group">
                        <button
                            type="button"
                            class="btn"
                            :class="modoAdicao === 'grupo' ? 'btn-primary' : 'btn-outline-primary'"
                            @click="modoAdicao = 'grupo'"
                        >
                            <i class="fa fa-users"></i> Grupo
                        </button>
                        <button
                            type="button"
                            class="btn"
                            :class="modoAdicao === 'colaborador' ? 'btn-primary' : 'btn-outline-primary'"
                            @click="modoAdicao = 'colaborador'"
                        >
                            <i class="fa fa-user"></i> Colaborador
                        </button>
                    </div>

                    <div class="row" v-if="modoAdicao === 'grupo'">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Selecionar grupo</label>
                                <select class="form-control form-control-sm" v-model="grupoSelecionadoId">
                                    <option :value="null">Selecione um grupo...</option>
                                    <option v-for="grupo in gruposDisponiveis" :key="grupo.id" :value="grupo.id">
                                        {{ grupo.nome }}
                                        <template v-if="grupo.usuarios_count != null"> ({{ grupo.usuarios_count }})</template>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-group w-100">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-success btn-block"
                                    :disabled="!grupoSelecionadoId || preloadGrupo"
                                    @click="adicionarPorGrupo"
                                >
                                    <i :class="preloadGrupo ? 'fa fa-spinner fa-spin' : 'fa fa-plus'"></i>
                                    Incluir membros do grupo
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row" v-else>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Buscar colaborador</label>
                                <autocomplete
                                    :caminho="`autocomplete/buscaUsuariosAtivos`"
                                    :formsm="true"
                                    v-model="form.autocomplete_label_colaborador"
                                    placeholder="Digite o nome do colaborador"
                                    :id="`colaborador_${hash}`"
                                    @onselect="selecionaColaborador"
                                ></autocomplete>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                v-model="filtroMembros"
                                placeholder="Filtrar membros da lista..."
                            />
                        </div>
                    </div>

                    <div class="table-responsive" v-if="membrosFiltrados.length > 0">
                        <table class="table table-bordered table-hover table-sm bg-white mb-0">
                            <thead>
                                <tr class="bg-default">
                                    <th>Nome</th>
                                    <th class="text-center" style="width: 180px">Grupo</th>
                                    <th class="text-center" style="width: 90px">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="colaborador in membrosFiltrados" :key="colaborador.id">
                                    <td>{{ colaborador.nome }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge"
                                            :class="colaborador.administrador ? 'badge-warning' : 'badge-light text-dark'"
                                        >
                                            {{ colaborador.grupo_nome || '—' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button
                                            v-if="!colaborador.administrador"
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Remover"
                                            @click.prevent="removerLIColaboradorPorId(colaborador.id)"
                                        >
                                            <i class="fa fa-times"></i>
                                        </button>
                                        <span v-else class="text-muted">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0" v-else>
                        Nenhum membro na lista<span v-if="filtroMembros"> com esse filtro</span>.
                    </p>
                </fieldset>
            </div>
        </div>

        <!-- LISTAGEM -->
        <div v-show="!open">
            <h4 class="text-default">Clouds</h4>

            <div class="row align-items-end mb-2">
                <div class="col-md-5 mb-2">
                    <label class="mb-1">Buscar</label>
                    <input
                        type="text"
                        class="form-control form-control-sm"
                        v-model="controle.dados.campoBusca"
                        placeholder="Nome do cloud"
                        autocomplete="off"
                        @keyup.enter="atualizar"
                    />
                </div>
                <div class="col-md-7 mb-2">
                    <button type="button" class="btn btn-sm btn-success mr-1" @click.prevent="atualizar">
                        <i class="fa fa-sync"></i> Atualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" @click="formNovo">
                        <i class="fa fa-plus"></i> Novo Cloud
                    </button>
                </div>
            </div>

            <preload class="mt-2" v-if="controle.carregando"></preload>

            <div id="conteudo">
                <p class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                    <i class="fa fa-exclamation-triangle"></i> Nenhum registro encontrado!
                </p>
                <div class="table-responsive">
                    <table class="tabela" v-if="!controle.carregando && lista.length > 0">
                        <thead>
                            <tr class="bg-default">
                                <th class="text-center" style="width: 70px">ID</th>
                                <th>Nome</th>
                                <th class="text-center" style="width: 120px">Membros</th>
                                <th class="text-center" style="width: 90px">Ativo</th>
                                <th class="text-center" style="width: 100px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in lista" :key="item.id">
                                <td class="text-center">{{ item.id }}</td>
                                <td>{{ item.nome }}</td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ item.usuarios_count || 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <bt-ativo :rota="`clouds/cadastro/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                                </td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-success"
                                        title="Editar"
                                        @click.prevent="formAlterar(item.id)"
                                    >
                                        <i class="fa fa-edit"></i> Editar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="url_paginacao"
                :por-pagina="controle.dados.pages"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            ></controle-paginacao>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            tituloJanela: 'Cadastrando Cloud',
            preloadAjax: false,
            preloadGrupo: false,
            editando: false,
            open: false,
            modoAdicao: 'grupo',
            grupoSelecionadoId: null,
            filtroMembros: '',
            grupos: [],
            administradoresIds: [],

            form: {
                id: null,
                nome: '',
                usuarios: [],
                usuariosDelete: [],
                ativo: true,
                autocomplete_label_colaborador: ''
            },

            formDefault: null,
            hash: `mastertag_${parseInt(Math.random() * 999999)}`,
            url_paginacao: `${URL_ADMIN}/clouds/cadastro/atualizar`,
            lista: [],
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: ''
                }
            }
        }
    },
    computed: {
        gruposDisponiveis() {
            return (this.grupos || []).filter((grupo) => grupo.nome !== 'Administradores')
        },
        membrosFiltrados() {
            const termo = (this.filtroMembros || '').trim().toLowerCase()
            const lista = Array.isArray(this.form.usuarios) ? this.form.usuarios : []
            if (!termo) {
                return lista
            }
            return lista.filter((item) => {
                const nome = (item.nome || '').toLowerCase()
                const grupo = (item.grupo_nome || '').toLowerCase()
                return nome.includes(termo) || grupo.includes(termo)
            })
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form)
        this.atualizar()
    },
    methods: {
        upper(value) {
            if (!value) return ''
            return String(value).toUpperCase()
        },
        isAdministrador(usuario) {
            if (!usuario) return false
            if (usuario.administrador) return true
            return (this.administradoresIds || []).includes(usuario.id)
        },
        adicionarUsuarioNaLista(usuario) {
            if (!usuario || !usuario.id) return false
            const jaExiste = this.form.usuarios.some((item) => Number(item.id) === Number(usuario.id))
            if (jaExiste) {
                return false
            }
            const administrador = !!usuario.administrador || this.isAdministrador(usuario)
            this.form.usuarios.push({
                id: usuario.id,
                nome: usuario.nome,
                administrador,
                grupo_nome: administrador
                    ? 'Administradores'
                    : usuario.grupo_nome || '—',
                novo: usuario.novo !== false
            })
            return true
        },
        removerLIColaboradorPorId(id) {
            const index = this.form.usuarios.findIndex((item) => Number(item.id) === Number(id))
            if (index < 0) return
            const usuario = this.form.usuarios[index]
            if (this.isAdministrador(usuario)) {
                return
            }
            if (this.editando && !usuario.novo) {
                this.form.usuariosDelete.push(usuario.id)
            }
            this.form.usuarios.splice(index, 1)
        },
        selecionaColaborador(obj) {
            const grupoNome = (obj.GrupoCloud && obj.GrupoCloud.nome) || (obj.grupo_cloud && obj.grupo_cloud.nome) || '—'
            const adicionou = this.adicionarUsuarioNaLista({
                id: obj.id,
                nome: obj.nome,
                administrador: grupoNome === 'Administradores',
                grupo_nome: grupoNome,
                novo: true
            })
            this.form.autocomplete_label_colaborador = ''
            if (!adicionou) {
                mostraErro('', `O colaborador(a) ${obj.nome} já está na lista.`)
            }
        },
        async adicionarPorGrupo() {
            if (!this.grupoSelecionadoId) return
            this.preloadGrupo = true
            try {
                const { data } = await axios.get(`${URL_ADMIN}/clouds/cadastro/grupos/${this.grupoSelecionadoId}/usuarios`)
                const usuarios = data.usuarios || []
                const nomeGrupo = data.grupo?.nome || 'grupo'
                let incluidos = 0
                let ignorados = 0
                usuarios.forEach((usuario) => {
                    if (
                        this.adicionarUsuarioNaLista({
                            ...usuario,
                            grupo_nome: usuario.grupo_nome || nomeGrupo
                        })
                    ) {
                        incluidos++
                    } else {
                        ignorados++
                    }
                })
                if (incluidos === 0 && ignorados === 0) {
                    mostraErro('', `O grupo ${nomeGrupo} não possui colaboradores ativos.`)
                } else {
                    mostraSucesso(
                        `${incluidos} colaborador(es) incluído(s) do grupo ${nomeGrupo}` +
                            (ignorados ? ` (${ignorados} já estavam na lista)` : '')
                    )
                }
                this.grupoSelecionadoId = null
            } catch (error) {
                // erro global axios
            } finally {
                this.preloadGrupo = false
            }
        },
        voltar() {
            this.open = false
            this.filtroMembros = ''
            this.modoAdicao = 'grupo'
            this.grupoSelecionadoId = null
            this.atualizar()
        },
        formNovo() {
            this.editando = false
            this.open = true
            this.tituloJanela = 'Cadastrando Cloud'
            this.filtroMembros = ''
            this.modoAdicao = 'grupo'
            this.grupoSelecionadoId = null
            this.grupos = []
            this.administradoresIds = []
            formReset()
            this.form = _.cloneDeep(this.formDefault)
        },
        submitForm() {
            this.editando ? this.alterar() : this.cadastrar()
        },
        async cadastrar() {
            if (!(this.form.nome || '').trim() || (this.form.nome || '').trim().length < 2) {
                mostraErro('', 'Informe o nome do Cloud (mínimo 2 caracteres).')
                return false
            }

            this.preloadAjax = true
            try {
                const { data } = await axios.post(`${URL_ADMIN}/clouds/cadastro`, this.form)
                mostraSucesso('Cloud cadastrado com sucesso! Agora você pode gerenciar o acesso.')
                this.preloadAjax = false
                if (data && data.id) {
                    await this.formAlterar(data.id)
                } else {
                    this.open = false
                    this.atualizar()
                }
            } catch (error) {
                this.preloadAjax = false
            }
        },
        async formAlterar(id) {
            this.form = _.cloneDeep(this.formDefault)
            this.form.id = id
            this.open = true
            this.editando = false
            this.tituloJanela = 'Alterando Cloud'
            this.filtroMembros = ''
            this.modoAdicao = 'grupo'
            this.grupoSelecionadoId = null
            this.preloadAjax = true
            formReset()

            try {
                const { data } = await axios.get(`${URL_ADMIN}/clouds/cadastro/${id}/editar`)
                this.editando = true
                this.form.id = data.id
                this.form.nome = data.nome
                this.form.ativo = data.ativo
                this.form.usuarios = Array.isArray(data.usuarios) ? data.usuarios : []
                this.form.usuariosDelete = []
                this.grupos = Array.isArray(data.grupos) ? data.grupos : []
                this.administradoresIds = Array.isArray(data.administradores_ids) ? data.administradores_ids : []
                this.tituloJanela = `Alterando Cloud — ${data.nome}`
                this.preloadAjax = false
            } catch (error) {
                this.preloadAjax = false
                this.open = false
            }
        },
        alterar() {
            if (!(this.form.nome || '').trim() || (this.form.nome || '').trim().length < 2) {
                mostraErro('', 'Informe o nome do Cloud (mínimo 2 caracteres).')
                return false
            }

            this.preloadAjax = true
            axios
                .put(`${URL_ADMIN}/clouds/cadastro/${this.form.id}`, this.form)
                .then(() => {
                    mostraSucesso('Alteração realizada com sucesso!')
                    this.preloadAjax = false
                    this.voltar()
                })
                .catch(() => {
                    this.preloadAjax = false
                })
        },
        carregou(dados) {
            this.lista = dados.lista || []
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            if (this.$refs && this.$refs.componente) {
                this.$refs.componente.atual = 1
            }
            if (this.$refs && this.$refs.componente && typeof this.$refs.componente.buscar === 'function') {
                this.$refs.componente.buscar()
            }
        }
    }
}
</script>

<style scoped>
.cloud-cadastro .badge-warning {
    background: #f0ad4e;
    color: #212529;
}
</style>
