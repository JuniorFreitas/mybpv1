<template>
    <span>
        <template v-if="!template">
            <button
                v-if="!leitura"
                :class="classBlock ? 'btn btn-sm btn-block btn-outline-primary' : 'btn btn-sm btn-outline-primary'"
                :disabled="emAndamento || quantidadeMaxima"
                @click="selecionar()"
            >
                <i class="fas fa-upload"></i> {{ label }}
            </button>
        </template>
        <span v-else @click="selecionar()">
            <slot v-if="template" name="template"></slot>
        </span>

        <div
            class="upload-dropzone"
            v-if="!leitura"
            @dragenter.prevent="onDragEnter"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragLeave"
            @drop.prevent="onDrop"
            :class="{ 'is-dragover': isDragover }"
        >
            <span v-if="!lista.length">Arraste arquivos para fazer upload</span>
        </div>

        <input
            type="file"
            style="display: none"
            ref="file"
            @change="upload()"
            :disabled="emAndamento"
            v-bind="multiple"
            :accept="arquivosPermitidos"
            v-show="false"
        />
        <div class="table-responsive" v-show="lista.length" v-if="!simples">
            <table class="table table-bordered table-hover table-condensed">
                <thead>
                    <tr class="bg-default">
                        <th class="text-center"></th>
                        <th class="text-center">Descrição</th>
                        <th class="text-center">Ações</th>
                        <th class="text-center" v-if="!leitura"></th>
                    </tr>
                </thead>

                <draggable
                    :model-value="lista"
                    :item-key="itemKey"
                    tag="tbody"
                    :handle="{ draggable: '.linha', handle: '.mover' }"
                    @update:model-value="lista = $event"
                    v-slot:item="{ element: arquivo, index }"
                >
                    <tr v-if="arquivo" class="linha" :class="{ 'bg-warning': arquivo.falhou }">
                        <td class="text-center">
                            <!-- o !arquivo.chave é para os casos de editar, pois nao tem chave, ja veio do servidor  -->
                            <span v-show="!arquivo.imagem && (!arquivo.chave || (arquivo.enviado && !arquivo.falhou))">
                                <svg
                                    version="1.1"
                                    id="Layer_1"
                                    xmlns="http://www.w3.org/2000/svg"
                                    xmlns:xlink="http://www.w3.org/1999/xlink"
                                    x="0px"
                                    y="0px"
                                    viewBox="0 0 512 512"
                                    style="enable-background: new 0 0 512 512; height: 45px"
                                    xml:space="preserve"
                                >
                                    <path
                                        style="fill: #e2e5e7"
                                        d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"
                                    />
                                    <path style="fill: #b0b7bd" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z" />
                                    <polygon style="fill: #cad1d8" points="480,224 384,128 480,128 " />
                                    <path
                                        style="fill: #184056"
                                        d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"
                                    />
                                    <g>
                                        <!--	<path style="fill:#FFFFFF;" d=""/>-->
                                        <text x="45" y="380" style="font-size: 130px; fill: #ffffff">
                                            {{ arquivo.extensao }}
                                        </text>
                                    </g>
                                    <path style="fill: #cad1d8" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z" />
                                </svg>
                            </span>

                            <span v-show="arquivo.imagem && (!arquivo.chave || (arquivo.enviado && !arquivo.falhou))">
                                <img :src="arquivo.urlThumb" width="100" />
                            </span>

                            <i class="fas fa-exclamation-triangle fa-4x" v-show="arquivo.falhou"></i>
                        </td>
                        <td class="text-center">
                            <div class="form-group">
                                <input
                                    type="text"
                                    :class="[leitura ? 'form-control-plaintext' : 'form-control']"
                                    :readonly="leitura"
                                    onblur="valida_campo_vazio(this, 1)"
                                    v-model="arquivo.nome"
                                    autocomplete="off"
                                    :disabled="arquivo.enviando || arquivo.aguardando"
                                />
                            </div>
                        </td>
                        <td class="text-center">
                            <a
                                :href="arquivo.url"
                                target="_blank"
                                class="btn btn-sm btn-secondary mb-1"
                                v-show="!arquivo.chave || (arquivo.enviado && !arquivo.falhou)"
                            >
                                <i class="fas fa-search"></i> Visualizar
                            </a>
                            <a
                                :href="arquivo.urlDownload"
                                class="btn btn-sm btn-secondary mb-1"
                                target="_blank"
                                v-show="!arquivo.chave || (arquivo.enviado && !arquivo.falhou)"
                            >
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button
                                class="btn btn-sm btn-secondary mover mb-1"
                                v-show="(!arquivo.chave && ordenar) || (arquivo.enviado && !arquivo.falhou && ordenar)"
                                v-if="!leitura"
                            >
                                <span> <i class="fas fa-arrows-alt-v"></i> Mover </span>
                            </button>

                            <div class="progress" v-show="arquivo.enviando || arquivo.aguardando">
                                <div
                                    class="progress-bar progress-bar-striped progress-bar-animated"
                                    role="progressbar"
                                    :style="{ width: `${arquivo.pctProgresso}%` }"
                                    :aria-valuenow="arquivo.pctProgresso"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                >
                                    <span v-if="arquivo.enviando">{{ arquivo.pctProgresso }}%</span>
                                </div>
                            </div>
                            <span v-if="arquivo.aguardando"> Preparado para envio </span>
                        </td>
                        <td class="text-center" v-if="!leitura">
                            <button class="btn btn-sm btn-secondary" @click="cancelar()" v-show="arquivo.enviando">
                                <i class="fas fa-window-close"></i> Cancelar
                            </button>
                            <button class="btn btn-sm btn-danger" @click="remover(index)" v-show="!arquivo.enviando">
                                <i class="fas fa-trash-alt"></i> Apagar
                            </button>
                        </td>
                    </tr>
                </draggable>
            </table>
        </div>
    </span>
</template>

<script>
let KB = 1024
let MB = 1024 * KB

import draggable from 'vuedraggable'
import axios from 'axios'
import _ from 'lodash'

export default {
    components: {
        draggable: draggable
    },
    props: {
        label: {
            type: String,
            required: false,
            default: () => 'Selecionar anexo(s)'
        },
        model: {
            type: Array,
            required: true,
            default: () => []
        },
        modelDelete: {
            type: Array,
            required: false,
            default: () => []
        },
        url: {
            type: String,
            required: false,
            default: () => ''
        },
        classBlock: {
            type: Boolean,
            required: false,
            default: false
        },
        size: {
            // quantidade de MB (5GB)
            type: Number,
            required: false,
            default: 5120
        },
        quantidade: {
            // quantidade de arquivos maximo para enviar
            type: Number,
            required: false,
            default: null
        },
        dadosAjax: {
            type: Object,
            required: false,
            default: () => {
                return {}
            }
        },
        tipos: {
            type: Array,
            required: false,
            default: () => []
        },
        ordenar: {
            type: Boolean,
            required: false,
            default: false
        },
        multi: {
            type: Boolean,
            required: false,
            default: true
        },
        nomePost: {
            type: String,
            required: false,
            default: () => 'arquivo' // nome que vai para o backend
        },
        leitura: {
            //se o componente é apenas de leitura...
            type: Boolean,
            required: false,
            default: false
        },
        apenasImagens: {
            // se aceita apenas imagens
            type: Boolean,
            required: false,
            default: false
        },
        apenasPdf: {
            // se aceita apenas pdf
            type: Boolean,
            required: false,
            default: false
        },
        apenasPdfImg: {
            // se aceita apenas pdf
            type: Boolean,
            required: false,
            default: false
        },
        simples: {
            // interface simples sem tabela
            type: Boolean,
            required: false,
            default: false
        },
        template: {
            // passar uma marcação html nova como botao
            type: Boolean,
            required: false,
            default: false
        }
    },
    computed: {
        btn: function () {
            return this.$refs.file
        },
        atual: function () {
            if (this.total === 0) {
                return 1
            }

            let index = null

            // o primeiro da lista que esta enviando...
            index = _.findIndex(this.lista, { enviando: true })
            if (index > -1) {
                return index + 1 // o primeiro que estiver enviando é o atual...
            }

            // o primeiro da lista que esta aguardando
            index = _.findIndex(this.lista, { aguardando: true })
            if (index > -1) {
                return index + 1 // ou o primeiro que estiver aguardando é o atual...
            }

            return this.total // se nao achar nada retorna 1 ou o ultimo item da lista
        },
        total: function () {
            return this.lista.length
        },
        arquivo: function () {
            return this.lista[this.atual - 1]
        },
        lista: {
            get: function () {
                return this.model || []
            },
            set: function (newValue) {
                newValue.forEach((obj, index) => {
                    this.model[index] = obj
                })
            }
        },
        bytesLimite: function () {
            return this.size * MB
        },
        arquivosPermitidos: function () {
            return this.mimeTypes.join(',')
        },
        multiple: function () {
            return this.multi === true ? { multiple: '' } : ''
        },
        quantidadeMaxima: function () {
            if (this.quantidade == null) {
                return false
            }
            return this.total >= this.quantidade
        },
        pctGeral() {
            let bytesCarregados = _.sumBy(this.model, 'bytesCarregados')
            let bytesTotal = _.sumBy(this.model, 'bytesTotal')
            let pctProgresso = Math.round((bytesCarregados / bytesTotal) * 100)
            return {
                carregados: bytesCarregados,
                total: bytesTotal,
                pct: pctProgresso
            }
        }
    },
    mounted() {
        if (this.apenasPdf) {
            this.mimeTypes = ['application/pdf']
        }

        if (this.apenasPdfImg) {
            this.mimeTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png']
        }

        if (this.apenasImagens) {
            this.mimeTypes = ['image/jpeg', 'image/jpg', 'image/png']
        } else {
            this.mimeTypes = this.mimeTypes.concat(this.tipos) // junta com a lista padrão
        }
        //criando a chave de upload para essa pagina
        this.chave = String(Math.random()).substr(2)
    },
    data() {
        return {
            axiosSource: null, // Para armazenar o cancelToken
            pediuCancelar: false,
            prefixo_id: 'upload',
            emAndamento: false, // se esta enviando arquivos ou não
            isDragover: false, // estado para indicar se está arrastando sobre a dropzone
            mimeTypes: [
                'image/gif',
                'image/jpg',
                'image/jpeg',
                'image/png',
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                'text/plain',
                'application/x-rar-compressed',
                'application/zip'
            ],
            chave: null // para identificar o upload
        }
    },

    methods: {
        itemKey(arquivo) {
            return arquivo.id || arquivo.chave || arquivo.url || arquivo.nome || arquivo.urlDownload || arquivo.tempId || arquivo.extensao
        },
        // Métodos para drag and drop
        onDragEnter(e) {
            this.isDragover = true
        },
        onDragOver(e) {
            this.isDragover = true
        },
        onDragLeave(e) {
            this.isDragover = false
        },
        onDrop(e) {
            this.isDragover = false
            const droppedFiles = e.dataTransfer.files
            if (droppedFiles.length > 0) {
                this.processFiles(droppedFiles)
            }
        },
        remover: function (index) {
            this.$emit('ondelete', this.lista[index])

            if (this.lista[index].id && !this.lista[index].temporario) {
                this.modelDelete.push(parseInt(this.lista[index].id))
            }
            if (this.lista[index].id && this.lista[index].temporario) {
                var dados = {}
                dados._method = 'DELETE'

                axios
                    .post(this.lista[index].urlDelete, dados)
                    .then((response) => {
                        // console.log('apagou');
                    })
                    .catch((error) => {
                        console.error('Erro ao apagar arquivo:', error)
                    })
            }
            this.lista.splice(index, 1)
        },
        cancelar: function () {
            this.pediuCancelar = true
            this.mostraErro('', 'O envio foi cancelado')

            if (this.axiosSource) {
                this.axiosSource.cancel('Operação cancelada pelo usuário')
            }
        },
        mostraErro: function (error, mensagem) {
            // Implementar conforme necessário (substitui função global mostraErro)
            mostraErro(mensagem, error)
            console.error(mensagem, error)
            // Se estiver usando um sistema de notificação, chamar aqui
            // Ex: this.$notify.error({ message: mensagem });
        },
        proximo: function () {
            if (this.atual === this.total) {
                if (this.arquivo.enviado) {
                    this.$emit('onfinalizado')
                    this.emAndamento = false
                } else {
                    this.enviarArquivo()
                }
            } else {
                this.enviarArquivo() // proximo arquivo
            }
        },
        selecionar: function () {
            if (this.btn) {
                this.btn.value = ''
                this.btn.click()
            }
        },
        processFiles: function (fileList) {
            this.emAndamento = true
            this.axiosSource = null

            if (fileList.length > 0) {
                let novosArquivos = 0

                Array.from(fileList).forEach((file, index) => {
                    let arquivo = {}
                    arquivo.lastModified = file.lastModified
                    arquivo.lastModifiedDate = file.lastModifiedDate
                    arquivo.nome = file.name
                    arquivo.bytes = file.size
                    arquivo.bytesCarregados = 0
                    arquivo.bytesTotal = file.size
                    arquivo.type = file.type !== '' ? file.type : 'application/octet-stream'
                    arquivo.webkitRelativePath = file.webkitRelativePath
                    arquivo.hashId = this.prefixo_id + parseInt(Math.random() * 999999)
                    arquivo.chave = this.chave
                    arquivo.extensao = file.name.split('.').pop().toUpperCase()

                    arquivo.falhou = false // se falhou no upload
                    arquivo.aguardando = true
                    arquivo.enviando = false
                    arquivo.enviado = false
                    arquivo.pctProgresso = 0

                    arquivo.file = file

                    // Verificar se é uma imagem para mostrar preview
                    arquivo.imagem = /^image\//.test(file.type)

                    if (this.quantidade) {
                        if (this.total >= this.quantidade) {
                            this.mostraErro({}, 'Limitado apenas a ' + this.quantidade + ' arquivo(s)')
                            return true //proximo loop
                        }
                    }

                    if (arquivo.bytes > this.bytesLimite) {
                        this.mostraErro(
                            {},
                            'O arquivo "' + arquivo.nome + '" (' + arquivo.bytes + ' bytes) deve ter um tamanho menor que ' + this.bytesLimite + ' bytes'
                        )
                        return true //proximo loop
                    }

                    if (this.mimeTypes.indexOf(arquivo.type) === -1) {
                        this.mostraErro({}, 'O formato do arquivo "' + arquivo.nome + '" não é permitido para envio.')
                        return true //proximo loop
                    }

                    this.lista.push(arquivo)
                    novosArquivos++
                    this.$emit('onInit', arquivo) // o arquivo será enviado, aguardando o envio
                })

                if (novosArquivos > 0) {
                    this.emAndamento = true
                    this.enviarArquivo()
                } else {
                    this.emAndamento = false
                }
            }
        },
        upload: function () {
            if (this.btn && this.btn.files) {
                this.processFiles(this.btn.files)
            }
        },
        enviarArquivo() {
            let dados = new FormData()
            dados.append(this.nomePost, this.arquivo.file)
            dados.append('chave', this.chave)

            // completar com outros dados se for necessario
            Object.keys(this.dadosAjax).forEach((key, index) => {
                let value = this.dadosAjax[key]
                dados.append(key, value)
            })

            let refVue = this

            Object.assign(this.arquivo, { enviando: true, aguardando: false })

            this.$emit('onStart', this.arquivo) // o arquivo iniciou o envio

            // Criar um novo token para cancelamento
            const CancelToken = axios.CancelToken
            const source = CancelToken.source()
            this.axiosSource = source

            // Configuração para rastreamento de progresso
            const config = {
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
                    refVue.arquivo.pctProgresso = percentCompleted
                    refVue.arquivo.bytesCarregados = progressEvent.loaded
                    refVue.arquivo.bytesTotal = progressEvent.total

                    this.lista[refVue.atual - 1] = refVue.arquivo //forçar atualizacao do array

                    refVue.$emit('onprogresso', refVue.arquivo)
                    refVue.$emit('onprogressogeral', refVue.pctGeral)
                },
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                cancelToken: source.token
            }

            axios
                .post(this.url, dados, config)
                .then((response) => {
                    Object.assign(refVue.arquivo, response.data) //mesclar e sobrescrever com resposta do servidor
                    Object.assign(refVue.arquivo, { enviando: false, enviado: true })
                    refVue.$emit('onComplete', response.data) //dados do servidor
                    refVue.proximo()
                })
                .catch((error) => {
                    if (axios.isCancel(error)) {
                        if (refVue.pediuCancelar) {
                            refVue.pediuCancelar = false

                            if (refVue.atual < refVue.total) {
                                refVue.lista.splice(refVue.atual - 1, 1)
                                refVue.proximo()
                                return
                            }

                            if (refVue.atual === refVue.total) {
                                refVue.lista.splice(refVue.atual - 1, 1)
                                refVue.$emit('onfinalizado')
                                refVue.emAndamento = false
                                return
                            }
                        }
                    } else {
                        // Erro não relacionado ao cancelamento
                        Object.assign(refVue.arquivo, { enviando: false, enviado: true, falhou: true })
                    }
                    refVue.proximo()
                })
        }
    }
}
</script>

<style scoped>
.upload-dropzone {
    border: 2px dashed #ccc;
    border-radius: 5px;
    padding: 20px;
    margin-bottom: 15px;
    text-align: center;
    transition: background-color 0.3s;
    cursor: pointer;
    margin-top: 10px;
}

.upload-dropzone.is-dragover {
    background-color: rgba(0, 123, 255, 0.1);
    border-color: #007bff;
}

.upload-dropzone span {
    color: #6c757d;
    font-size: 16px;
}
</style>
