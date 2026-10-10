<template>
  <div class="upload-anexos">
    <div class="upload-lista" v-show="lista.length" v-if="!simples">
      <draggable v-model="lista" tag="div" class="upload-lista-itens" handle=".mover" :item-key="getItemKey" :disabled="ocupadoAcao">
        <template #item="{ element: arquivo, index }">
          <div
            class="upload-item"
            :class="{
              'upload-item-falhou': arquivo.falhou,
              'upload-item-enviando': arquivo.enviando || arquivo.aguardando,
              'upload-item-apagando': estaApagando(arquivo),
            }"
          >
            <div class="upload-item-preview">
              <img
                v-if="arquivo.imagem && (!arquivo.chave || (arquivo.enviado && !arquivo.falhou))"
                :src="arquivo.urlThumb"
                alt=""
                class="upload-item-thumb"
              />
              <div v-else-if="arquivo.falhou" class="upload-item-icone upload-item-icone-erro">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
              <div v-else class="upload-item-icone">
                <span class="upload-item-ext">{{ rotuloExtensao(arquivo) }}</span>
              </div>
            </div>

            <div class="upload-item-corpo">
              <label class="upload-item-label">{{ tabledescricao }}</label>
              <div class="upload-item-linha">
                <div class="input-wrapper">
                  <input
                    type="text"
                    class="form-control-modern"
                    :class="{ 'form-control-plaintext': leitura }"
                    :readonly="leitura"
                    v-model="arquivo.nome"
                    autocomplete="off"
                    :disabled="arquivo.enviando || arquivo.aguardando || ocupadoAcao"
                  />
                  <i class="fas fa-file input-icon"></i>
                </div>

                <div class="upload-item-acoes">
                  <button
                    type="button"
                    class="btn-filter btn-filter-neutro"
                    v-show="podeVisualizar(arquivo) && (!arquivo.chave || (arquivo.enviado && !arquivo.falhou))"
                    title="Visualizar"
                    :disabled="ocupadoAcao"
                    @click="abrirPreview(arquivo)"
                  >
                    <i class="fas" :class="estaVisualizando(arquivo) ? 'fa-spinner fa-spin' : 'fa-eye'"></i>
                    <span>{{ estaVisualizando(arquivo) ? 'Abrindo...' : 'Ver' }}</span>
                  </button>
                  <button
                    type="button"
                    class="btn-filter btn-filter-neutro"
                    v-show="!arquivo.chave || (arquivo.enviado && !arquivo.falhou)"
                    title="Download"
                    :disabled="ocupadoAcao"
                    @click="baixarArquivo(arquivo)"
                  >
                    <i class="fas" :class="estaBaixando(arquivo) ? 'fa-spinner fa-spin' : 'fa-download'"></i>
                    <span>{{ estaBaixando(arquivo) ? 'Baixando...' : 'Baixar' }}</span>
                  </button>
                  <button
                    type="button"
                    class="btn-filter btn-filter-neutro mover"
                    v-show="(!arquivo.chave && ordenar) || (arquivo.enviado && !arquivo.falhou && ordenar)"
                    v-if="!leitura"
                    title="Mover"
                    :disabled="ocupadoAcao"
                  >
                    <i class="fas fa-arrows-alt-v"></i>
                    <span>Mover</span>
                  </button>
                  <button
                    type="button"
                    class="btn-filter btn-filter-neutro"
                    @click="cancelar()"
                    v-show="arquivo.enviando"
                    :disabled="ocupadoAcao"
                  >
                    <i class="fas fa-times"></i>
                    <span>Cancelar</span>
                  </button>
                  <button
                    type="button"
                    class="btn-filter btn-filter-erro"
                    @click="remover(index)"
                    v-show="!arquivo.enviando && !leitura"
                    :disabled="ocupadoAcao"
                  >
                    <i class="fas" :class="estaApagando(arquivo) ? 'fa-spinner fa-spin' : 'fa-trash'"></i>
                    <span>{{ estaApagando(arquivo) ? 'Apagando...' : 'Apagar' }}</span>
                  </button>
                </div>
              </div>

              <div class="upload-item-progresso" v-if="arquivo.enviando || arquivo.aguardando">
                <div class="upload-progresso-topo">
                  <span>{{ arquivo.aguardando ? 'Preparando envio...' : 'Enviando...' }}</span>
                  <strong v-if="arquivo.enviando">{{ arquivo.pctProgresso || 0 }}%</strong>
                </div>
                <div class="upload-progresso-barra">
                  <div class="upload-progresso-preenchimento" :style="{ width: `${arquivo.pctProgresso || 0}%` }"></div>
                </div>
              </div>

              <small class="upload-item-status upload-item-status-erro" v-if="arquivo.falhou">
                <i class="fas fa-exclamation-circle"></i>
                Falha no envio
              </small>
            </div>
          </div>
        </template>
      </draggable>
    </div>

    <template v-if="!template && !leitura && !quantidadeMaxima && somenteBotao">
      <button
        type="button"
        class="btn btn-sm btn-outline-primary"
        :class="{ 'btn-block': classBlock }"
        :disabled="emAndamento || ocupadoAcao"
        @click="selecionar()"
      >
        <i class="fas fa-upload"></i> {{ label }}
      </button>
    </template>
    <template v-else-if="!template && !leitura && !quantidadeMaxima">
      <div
        class="upload-dropzone"
        :class="{
          'upload-dropzone-arrasto': arrastando,
          'upload-dropzone-desabilitada': emAndamento || ocupadoAcao,
          'upload-dropzone-ativa': emAndamento,
        }"
        @dragenter.prevent="aoArrastarEntrar"
        @dragover.prevent="aoArrastarSobre"
        @dragleave.prevent="aoArrastarSair"
        @drop.prevent="aoSoltar"
      >
        <div class="upload-dropzone-info" @click="abrirSeletor">
          <div class="upload-dropzone-icone">
            <i class="fas" :class="apenasImagens ? 'fa-image' : 'fa-upload'"></i>
          </div>
          <div class="upload-dropzone-corpo">
            <strong class="upload-dropzone-titulo">
              {{ emAndamento ? 'Enviando arquivo...' : tituloDropzone }}
            </strong>
            <span class="upload-dropzone-ajuda">{{ ajudaDropzone }}</span>
          </div>
        </div>

        <div class="upload-dropzone-acoes">
          <button
            type="button"
            class="btn-filter btn-filter-neutro"
            :disabled="emAndamento || ocupadoAcao"
            @click.stop="selecionar()"
          >
            <i class="fas fa-folder-open"></i>
            <span>{{ labelCurto }}</span>
          </button>
        </div>
      </div>
    </template>
    <div v-else-if="template" @click="selecionar()">
      <slot name="template"></slot>
    </div>

    <input type="file" style="display: none" ref="file" @change="upload()" :disabled="emAndamento || ocupadoAcao" v-bind="multiple"
      :accept="arquivosPermitidos" v-show="false" />

    <Teleport to="body">
      <div
        v-if="previewAberto"
        class="upload-preview-overlay"
        :style="{ zIndex: previewZIndex }"
        @click.self="fecharPreview"
      >
        <div class="upload-preview-dialog" role="dialog" aria-modal="true" :aria-label="previewTitulo">
          <div class="upload-preview-header">
            <h5 class="upload-preview-titulo">
              <i class="fas" :class="previewTipo === 'pdf' ? 'fa-file-pdf' : 'fa-image'"></i>
              {{ previewTitulo }}
            </h5>
            <button type="button" class="upload-preview-fechar" title="Fechar" @click="fecharPreview">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="upload-preview-body">
            <div v-if="previewCarregando" class="upload-preview-loading">
              <i class="fas fa-spinner fa-spin"></i>
              <span>Carregando...</span>
            </div>
            <img
              v-if="previewTipo === 'imagem'"
              :src="previewUrl"
              :alt="previewTitulo"
              class="upload-preview-imagem"
              :class="{ 'upload-preview-oculto': previewCarregando }"
              @load="onPreviewCarregado"
              @error="onPreviewCarregado"
            />
            <iframe
              v-else-if="previewTipo === 'pdf'"
              :src="previewUrl"
              class="upload-preview-pdf"
              :class="{ 'upload-preview-oculto': previewCarregando }"
              title="Visualização do PDF"
              @load="onPreviewCarregado"
            ></iframe>
          </div>
          <div class="upload-preview-footer">
            <button type="button" class="btn-filter btn-filter-primary" @click="fecharPreview">
              <i class="fas fa-check"></i>
              <span>Fechar</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
let KB = 1024;
let MB = 1024 * KB;

import draggable from "vuedraggable";

export default {
  components: {
    draggable: draggable,
  },
  props: {
    label: {
      type: String,
      required: false,
      default: () => "Selecionar anexo(s)",
    },

    tabledescricao: {
      type: String,
      required: false,
      default: () => "Descrição",
    },
    model: {
      type: Array,
      required: true,
      default: () => [],
    },
    modelDelete: {
      type: Array,
      required: false,
      default: () => [],
    },
    url: {
      type: String,
      required: false,
      default: () => "",
    },
    classBlock: {
      type: Boolean,
      required: false,
      default: false,
    },
    /*chave: {
                type: String,
                required: false,
                default: () => ''
                /!*
                   coloquei false pq geralmente esse componente esta dentro de outro. Entao isso evita de ser obrigado
                   a passar uma chave quando o componente mais externo é apenas para leitura, nao sera usado upload de fato
               *!/
            },*/
    size: {
      // quantidade de MB (5GB)
      type: Number,
      required: false,
      default: 5120,
    },
    quantidade: {
      // quantidade de arquivos maximo para enviar
      type: Number,
      required: false,
      default: null,
    },
    dadosAjax: {
      type: Object,
      required: false,
      default: () => {
        return {};
      },
    },
    tipos: {
      type: Array,
      required: false,
      default: () => [],
    },
    ordenar: {
      type: Boolean,
      required: false,
      default: false,
    },
    multi: {
      type: Boolean,
      required: false,
      default: true,
    },
    nomePost: {
      type: String,
      required: false,
      default: () => "arquivo", // nome que vai para o backend
    },
    leitura: {
      //se o componente é apenas de leitura...
      type: Boolean,
      required: false,
      default: false,
    },
    apenasImagens: {
      // se aceita apenas imagens
      type: Boolean,
      required: false,
      default: false,
    },
    apenasPdf: {
      type: Boolean,
      required: false,
      default: false,
    },
    apenasPdfImg: {
      type: Boolean,
      required: false,
      default: false,
    },
    /**
     * Aceita qualquer arquivo, bloqueando apenas executáveis/instaladores.
     * Usado pelo Cloud.
     */
    bloquearExecutaveis: {
      type: Boolean,
      required: false,
      default: false,
    },
    simples: {
      // interface simples sem tabela
      type: Boolean,
      required: false,
      default: false,
    },
    template: {
      // passar uma marcação html nova como botao
      type: Boolean,
      required: false,
      default: false,
    },
    somenteBotao: {
      // só botão (sem dropzone visual); use receberArquivos() no pai para drop externo
      type: Boolean,
      required: false,
      default: false,
    },
    ajuda: {
      type: String,
      required: false,
      default: '',
    },
    titulo: {
      type: String,
      required: false,
      default: '',
    },
  },
  computed: {
    tituloDropzone() {
      if (this.titulo) {
        return this.titulo;
      }
      return this.apenasImagens ? 'Selecione ou arraste uma imagem' : 'Selecione ou arraste um arquivo';
    },
    ajudaDropzone() {
      if (this.ajuda) {
        return this.ajuda;
      }
      if (this.apenasImagens) {
        return 'Formatos: JPG, PNG, WEBP, GIF · arraste ou clique em Escolher';
      }
      return `Até ${this.size} MB · arraste ou clique em Escolher`;
    },
    labelCurto() {
      const texto = String(this.label || 'Escolher');
      if (texto.toLowerCase().includes('enviar') || texto.toLowerCase().includes('selecionar')) {
        return 'Escolher';
      }
      return texto;
    },
    ocupadoAcao() {
      return Boolean(this.chaveApagando || this.chaveBaixando || this.chaveVisualizando);
    },
    btn() {
      return $(this.$refs.file);
    },
    atual() {
      if (this.total === 0) {
        return 1;
      }

      let index = null;

      // o primeiro da lista que esta enviando...
      index = _.findIndex(this.lista, { enviando: true });
      if (index > -1) {
        /*console.log('achou o primeiro index que esta enviando',index);*/
        return index + 1; // o primeiro que estiver enviando é o atual...
      }

      // o primeiro da lista que esta aguardando
      index = _.findIndex(this.lista, { aguardando: true });
      if (index > -1) {
        /*console.log('achou o primeiro index que esta aguardando',index);*/
        return index + 1; // ou o primeiro que estiver aguardando é o atual...
      }

      /*index = _.findLastIndex(this.lista, { enviado: true,enviando: false,aguardando: false });
                if(index > -1){
                    console.log('achou o ultimo index que já enviou',index);
                    return index +1; // ou o primeiro que estiver enviado é o atual...
                }*/

      return this.total; // se nao achar nada retorna 1 ou o ultimo item da lista
    },
    total() {
      return this.lista.length;
    },
    arquivo() {
      return this.lista[this.atual - 1];
    },
    lista: {
      get() {
        return this.model;
      },
      set(newValue) {
        newValue.forEach((obj, index) => {
          // No Vue 3, a atribuição direta funciona para arrays reativos
          this.model[index] = obj;
        });
      },
    },
    bytesLimite() {
      return this.size * MB;
    },
    arquivosPermitidos() {
      if (this.bloquearExecutaveis) {
        return '';
      }
      return this.mimeTypes.concat([
        ".csv", ".md", ".markdown", ".txt", ".webp",
        ".mp3", ".mp4", ".eps", ".ai", ".psd",
      ]).join(",");
    },
    multiple() {
      return this.multi === true ? { multiple: "" } : "";
    },
    quantidadeMaxima() {
      if (this.quantidade == null) {
        return false;
      }
      return this.total >= this.quantidade;
    },
    pctGeral() {
      let bytesCarregados = _.sumBy(this.model, "bytesCarregados");
      let bytesTotal = _.sumBy(this.model, "bytesTotal");
      let pctProgresso = Math.round((bytesCarregados / bytesTotal) * 100);
      return {
        carregados: bytesCarregados,
        total: bytesTotal,
        pct: pctProgresso,
      };
    },
  },
  mounted() {
    if (this.apenasPdf) {
      this.mimeTypes = ["application/pdf"];
    } else if (this.apenasPdfImg) {
      this.mimeTypes = ["application/pdf", "image/jpeg", "image/jpg", "image/png", "image/webp", "image/gif"];
    } else if (this.apenasImagens) {
      this.mimeTypes = ["image/jpeg", "image/jpg", "image/png", "image/webp", "image/gif"];
    } else {
      this.mimeTypes = this.mimeTypes.concat(this.tipos);
    }
    this.chave = String(Math.random()).substr(2);
  },
  data() {
    return {
      axiosSource: null,
      pediuCancelar: false,
      prefixo_id: "upload",
      emAndamento: false, // se esta enviando arquivos ou não
      arrastando: false,
      contadorArrasto: 0,
      mimeTypes: [
        "image/gif",
        "image/jpg",
        "image/jpeg",
        "image/png",
        "image/webp",
        "application/pdf",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/msword",
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.ms-powerpoint",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "application/vnd.ms-powerpoint",
        "application/vnd.openxmlformats-officedocument.presentationml.slideshow",
        "text/plain",
        "text/csv",
        "text/comma-separated-values",
        "application/csv",
        "text/markdown",
        "text/x-markdown",
        "audio/mpeg",
        "audio/mpeg3",
        "audio/x-mpeg-3",
        "audio/mp3",
        "video/mp4",
        "application/mp4",
        "video/x-m4v",
        "application/postscript",
        "application/eps",
        "image/x-eps",
        "application/x-eps",
        "application/illustrator",
        "application/vnd.adobe.illustrator",
        "image/vnd.adobe.photoshop",
        "image/x-photoshop",
        "application/x-photoshop",
        "application/photoshop",
        "application/x-rar-compressed",
        "application/x-rar",
        "application/vnd.rar",
        "application/zip",
        "application/x-zip-compressed",
        "application/octet-stream",
      ],
      extensoesExecutaveis: [
        "exe", "bat", "cmd", "com", "msi", "msp", "scr", "pif", "cpl", "msc",
        "dll", "sys", "drv", "ocx",
        "vbs", "vbe", "js", "jse", "wsf", "wsh", "ws", "ps1", "psm1", "psd1",
        "sh", "bash", "csh", "ksh", "run",
        "jar", "app", "deb", "rpm", "apk", "dmg", "pkg",
        "reg", "hta", "inf", "lnk", "scf", "action", "command", "workflow",
      ],
      mimesExecutaveis: [
        "application/x-msdownload",
        "application/x-msdos-program",
        "application/x-ms-installer",
        "application/x-msi",
        "application/vnd.microsoft.portable-executable",
        "application/x-dosexec",
        "application/x-executable",
        "application/x-sharedlib",
        "application/x-mach-binary",
        "application/x-sh",
        "application/x-shellscript",
        "application/x-csh",
        "application/java-archive",
        "application/x-java-archive",
        "application/x-debian-package",
        "application/vnd.debian.binary-package",
        "application/x-rpm",
        "application/vnd.android.package-archive",
        "application/x-apple-diskimage",
        "application/x-ms-shortcut",
        "application/hta",
      ],
      chave: null, // para identificar o upload
      previewAberto: false,
      previewUrl: '',
      previewTitulo: '',
      previewTipo: '',
      previewZIndex: 1080,
      previewCarregando: false,
      chaveApagando: null,
      chaveBaixando: null,
      chaveVisualizando: null,
    };
  },

  beforeUnmount() {
    this.fecharPreview();
  },

  methods: {
    getItemKey(item) {
      // Usar hashId se existir (gerado para novos arquivos), caso contrário usar id ou chave
      if (item.hashId) {
        return item.hashId;
      }
      if (item.id) {
        return `item-${item.id}`;
      }
      if (item.chave) {
        return item.chave;
      }
      // Fallback: gerar uma chave baseada em propriedades únicas
      return `item-${item.nome}-${item.bytes}-${item.lastModified || Date.now()}`;
    },
    rotuloExtensao(arquivo) {
      const extensao = String(arquivo.extensao || '').replace('.', '').toUpperCase();
      if (extensao) {
        return extensao.slice(0, 4);
      }
      const nome = String(arquivo.nome || '');
      const partes = nome.split('.');
      if (partes.length > 1) {
        return partes[partes.length - 1].toUpperCase().slice(0, 4);
      }
      return 'FILE';
    },
    estaApagando(arquivo) {
      return this.chaveApagando && this.chaveApagando === this.getItemKey(arquivo);
    },
    estaBaixando(arquivo) {
      return this.chaveBaixando && this.chaveBaixando === this.getItemKey(arquivo);
    },
    estaVisualizando(arquivo) {
      return this.chaveVisualizando && this.chaveVisualizando === this.getItemKey(arquivo);
    },
    tipoArquivo(arquivo) {
      const tipo = String(arquivo.type || arquivo.mime || '').toLowerCase();
      const nome = String(arquivo.nome || arquivo.name || '').toLowerCase();
      const extensao = String(arquivo.extensao || '').toLowerCase().replace('.', '');

      if (
        arquivo.imagem === true ||
        tipo.startsWith('image/') ||
        ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(extensao) ||
        /\.(jpg|jpeg|png|gif|webp|bmp|svg)$/.test(nome)
      ) {
        return 'imagem';
      }

      if (tipo === 'application/pdf' || extensao === 'pdf' || nome.endsWith('.pdf')) {
        return 'pdf';
      }

      return 'outro';
    },
    podeVisualizar(arquivo) {
      const tipo = this.tipoArquivo(arquivo);
      return (tipo === 'imagem' || tipo === 'pdf') && Boolean(arquivo.url || arquivo.urlThumb);
    },
    calcularZIndexPreview() {
      let maxZ = 1050;
      document.querySelectorAll('.modal.show, .modal').forEach(el => {
        const z = parseInt(window.getComputedStyle(el).zIndex, 10);
        if (!Number.isNaN(z) && z > maxZ) {
          maxZ = z;
        }
      });
      document.querySelectorAll('.modal-backdrop').forEach(el => {
        const z = parseInt(window.getComputedStyle(el).zIndex, 10);
        if (!Number.isNaN(z) && z > maxZ) {
          maxZ = z;
        }
      });
      return maxZ + 20;
    },
    abrirPreview(arquivo) {
      if (this.ocupadoAcao || !this.podeVisualizar(arquivo)) {
        return;
      }

      const chave = this.getItemKey(arquivo);
      this.chaveVisualizando = chave;
      this.previewCarregando = true;
      this.previewTipo = this.tipoArquivo(arquivo);
      this.previewUrl = arquivo.url || arquivo.urlThumb || '';
      this.previewTitulo = arquivo.nome || 'Visualização';
      this.previewZIndex = this.calcularZIndexPreview();
      this.previewAberto = true;

      document.addEventListener('keydown', this.onPreviewKeydown);

      // Fallback se load não disparar (ex.: cache/CORS em alguns PDFs)
      window.clearTimeout(this._previewLoadTimeout);
      this._previewLoadTimeout = window.setTimeout(() => {
        if (this.previewCarregando) {
          this.onPreviewCarregado();
        }
      }, 8000);
    },
    onPreviewCarregado() {
      this.previewCarregando = false;
      this.chaveVisualizando = null;
      window.clearTimeout(this._previewLoadTimeout);
    },
    fecharPreview() {
      this.previewAberto = false;
      this.previewUrl = '';
      this.previewTitulo = '';
      this.previewTipo = '';
      this.previewCarregando = false;
      this.chaveVisualizando = null;
      window.clearTimeout(this._previewLoadTimeout);
      document.removeEventListener('keydown', this.onPreviewKeydown);

      // Mantém o modal pai aberto (Bootstrap)
      if (document.querySelectorAll('.modal.show').length > 0) {
        document.body.classList.add('modal-open');
      }
    },
    onPreviewKeydown(evento) {
      if (evento.key === 'Escape') {
        this.fecharPreview();
      }
    },
    nomeArquivoDownload(arquivo) {
      const nome = String(arquivo.nome || '').trim();
      if (nome) {
        return nome;
      }
      const url = String(arquivo.urlDownload || arquivo.url || '');
      const trecho = url.split('?')[0].split('/').pop();
      return trecho || 'arquivo';
    },
    async baixarArquivo(arquivo) {
      const url = arquivo.urlDownload || arquivo.url;
      if (!url || this.ocupadoAcao) {
        return;
      }

      const chave = this.getItemKey(arquivo);
      const nome = this.nomeArquivoDownload(arquivo);
      this.chaveBaixando = chave;

      try {
        const resposta = await fetch(url, { mode: 'cors' });
        if (!resposta.ok) {
          throw new Error('Falha ao baixar arquivo');
        }

        const blob = await resposta.blob();
        const objectUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = objectUrl;
        link.download = nome;
        link.rel = 'noopener';
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(objectUrl);
      } catch (error) {
        const link = document.createElement('a');
        link.href = url;
        link.download = nome;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        document.body.appendChild(link);
        link.click();
        link.remove();
      } finally {
        this.chaveBaixando = null;
      }
    },
    async remover(index) {
      if (this.ocupadoAcao || !this.lista[index]) {
        return;
      }

      const arquivo = this.lista[index];
      const chave = this.getItemKey(arquivo);
      this.chaveApagando = chave;

      try {
        this.$emit("ondelete", arquivo);

        if (arquivo.id && arquivo.temporario == false) {
          this.modelDelete.push(parseInt(arquivo.id));
        }

        if (arquivo.id && arquivo.temporario == true) {
          const dados = { _method: "DELETE" };
          try {
            await axios.post(arquivo.urlDelete, dados);
          } catch (error) {
            console.error("Erro ao deletar arquivo:", error);
          }
        }

        const indiceAtual = this.lista.findIndex(item => this.getItemKey(item) === chave);
        if (indiceAtual >= 0) {
          this.lista.splice(indiceAtual, 1);
        }
      } finally {
        this.chaveApagando = null;
      }
    },
    cancelar() {
      if (this.ocupadoAcao) {
        return;
      }
      this.pediuCancelar = true;
      mostraErro("", "O envio foi cancelado");
      if (this.axiosSource) {
        this.axiosSource.cancel("Operação cancelada pelo usuário");
      }
    },
    proximo() {
      if (this.atual === this.total) {
        if (this.arquivo.enviado) {
          this.$emit("onfinalizado");
          this.$emit("onFinalizado");
          this.emAndamento = false;
        } else {
          this.enviarArquivo();
        }
      } else {
        this.enviarArquivo();
      }
    },
    selecionar() {
      if (this.emAndamento || this.quantidadeMaxima || this.leitura || this.ocupadoAcao) {
        return;
      }
      this.btn.val("");
      this.btn.trigger("click");
    },
    abrirSeletor() {
      this.selecionar();
    },
    aoArrastarEntrar() {
      if (this.emAndamento || this.quantidadeMaxima || this.leitura || this.ocupadoAcao) {
        return;
      }
      this.contadorArrasto += 1;
      this.arrastando = true;
    },
    aoArrastarSobre() {
      if (this.emAndamento || this.quantidadeMaxima || this.leitura || this.ocupadoAcao) {
        return;
      }
      this.arrastando = true;
    },
    aoArrastarSair() {
      this.contadorArrasto = Math.max(0, this.contadorArrasto - 1);
      if (this.contadorArrasto === 0) {
        this.arrastando = false;
      }
    },
    aoSoltar(evento) {
      this.contadorArrasto = 0;
      this.arrastando = false;

      if (this.emAndamento || this.quantidadeMaxima || this.leitura || this.ocupadoAcao) {
        return;
      }

      const arquivos = evento.dataTransfer && evento.dataTransfer.files ? evento.dataTransfer.files : null;
      if (arquivos && arquivos.length) {
        this.processarArquivos(arquivos);
      }
    },
    /** API pública para o pai encaminhar arquivos (ex.: drop na área do Cloud) */
    receberArquivos(listaArquivos) {
      if (this.emAndamento || this.quantidadeMaxima || this.leitura || this.ocupadoAcao) {
        return;
      }
      this.processarArquivos(listaArquivos);
    },
    extensaoArquivo(nome) {
      const partes = String(nome || "").split(".");
      return partes.length > 1 ? partes.pop().toLowerCase() : "";
    },
    ehExecutavel(nome, mimeType) {
      const ext = this.extensaoArquivo(nome);
      if (ext && this.extensoesExecutaveis.includes(ext)) {
        return true;
      }
      const mime = String(mimeType || "").toLowerCase();
      if (!mime || mime === "application/octet-stream") {
        return false;
      }
      return this.mimesExecutaveis.includes(mime);
    },
    extensaoTextoPermitida(nome) {
      // Navegadores às vezes enviam MIME vazio/errado
      const ext = this.extensaoArquivo(nome);
      const mapa = {
        csv: ["text/plain", "text/csv", "text/comma-separated-values", "application/csv"],
        md: ["text/plain", "text/markdown", "text/x-markdown"],
        markdown: ["text/plain", "text/markdown", "text/x-markdown"],
        txt: ["text/plain"],
        webp: ["image/webp"],
        mp3: ["audio/mpeg", "audio/mpeg3", "audio/x-mpeg-3", "audio/mp3"],
        mp4: ["video/mp4", "application/mp4", "video/x-m4v"],
        eps: ["application/postscript", "application/eps", "image/x-eps", "application/x-eps"],
        ai: ["application/postscript", "application/illustrator", "application/vnd.adobe.illustrator"],
        psd: ["image/vnd.adobe.photoshop", "image/x-photoshop", "application/x-photoshop", "application/photoshop"],
      };
      const mimesExt = mapa[ext];
      if (!mimesExt) {
        return false;
      }
      return mimesExt.some((mime) => this.mimeTypes.includes(mime));
    },
    upload() {
      const arquivos = this.btn[0] && this.btn[0].files ? this.btn[0].files : null;
      this.processarArquivos(arquivos);
    },

    processarArquivos(listaArquivos) {
      const totalDeArquivos = listaArquivos ? listaArquivos.length : 0;
      this.emAndamento = true;
      this.axiosSource = null;

      if (totalDeArquivos > 0) {
        let lista = listaArquivos;
        let novosArquivos = 0;
        Array.from(lista).forEach((file, index) => {
          let arquivo = {};
          arquivo.lastModified = file.lastModified;
          arquivo.lastModifiedDate = file.lastModifiedDate;
          arquivo.nome = file.name;
          arquivo.bytes = file.size;
          arquivo.bytesCarregados = 0;
          arquivo.bytesTotal = file.size;
          arquivo.type =
            file.type !== "" ? file.type : "application/octet-stream";
          arquivo.webkitRelativePath = file.webkitRelativePath;
          arquivo.hashId = this.prefixo_id + parseInt(Math.random() * 999999);
          arquivo.chave = this.chave;

          arquivo.falhou = false;
          arquivo.aguardando = true;
          arquivo.enviando = false;
          arquivo.enviado = false;

          arquivo.file = file;

          if (this.quantidade) {
            if (this.total >= this.quantidade) {
              mostraErro(
                {},
                "Limitado apenas a " + this.quantidade + " arquivo(s)"
              );
              return true;
            }
          }

          if (arquivo.bytes > this.bytesLimite) {
            mostraErro(
              {},
              'O arquivo "' +
              arquivo.nome +
              '" (' +
              arquivo.bytes +
              " bytes) deve ter um tamanho menor que " +
              this.bytesLimite +
              " bytes"
            );
            return true;
          }

          if (this.bloquearExecutaveis) {
            if (this.ehExecutavel(arquivo.nome, arquivo.type)) {
              mostraErro(
                {},
                'O arquivo "' +
                arquivo.nome +
                '" é executável e não é permitido para envio.'
              );
              return true;
            }
          } else if (this.mimeTypes.indexOf(arquivo.type) === -1 && !this.extensaoTextoPermitida(arquivo.nome)) {
            mostraErro(
              {},
              'O formato do arquivo "' +
              arquivo.nome +
              '" não é permitido para envio.'
            );
            return true;
          }

          this.lista.push(arquivo);
          novosArquivos++;
          this.$emit("onInit", arquivo);
        });

        if (novosArquivos > 0) {
          this.emAndamento = true;
          this.enviarArquivo();
        } else {
          this.emAndamento = false;
        }
      } else {
        this.emAndamento = false;
      }
    },

    enviarArquivo() {
      let dados = new FormData();
      dados.append(this.nomePost, this.arquivo.file);
      dados.append("chave", this.chave);

      Object.keys(this.dadosAjax).forEach((key) => {
        dados.append(key, this.dadosAjax[key]);
      });

      const refVue = this;
      Object.assign(this.arquivo, { enviando: true, aguardando: false });
      this.$emit("onStart", this.arquivo);

      const CancelToken = axios.CancelToken;
      const source = CancelToken.source();
      this.axiosSource = source;

      const config = {
        onUploadProgress: (e) => {
          if (!e.total) {
            return;
          }
          refVue.arquivo.pctProgresso = Math.round((e.loaded / e.total) * 100);
          refVue.arquivo.bytesCarregados = e.loaded;
          refVue.arquivo.bytesTotal = e.total;
          this.lista[refVue.atual - 1] = refVue.arquivo;
          refVue.$emit("onprogresso", refVue.arquivo);
          refVue.$emit("onProgresso", refVue.arquivo);
          refVue.$emit("onprogressogeral", refVue.pctGeral);
        },
        headers: {
          "Content-Type": "multipart/form-data",
        },
        cancelToken: source.token,
      };

      axios
        .post(this.url, dados, config)
        .then((response) => {
          Object.assign(refVue.arquivo, response.data);
          Object.assign(refVue.arquivo, { enviando: false, enviado: true });
          refVue.$emit("onComplete", response.data);
          refVue.proximo();
        })
        .catch(() => {
          if (refVue.pediuCancelar) {
            refVue.pediuCancelar = false;

            if (refVue.atual < refVue.total) {
              refVue.lista.splice(refVue.atual - 1, 1);
              refVue.proximo();
              return;
            }

            if (refVue.atual === refVue.total) {
              refVue.lista.splice(refVue.atual - 1, 1);
              refVue.$emit("onfinalizado");
              refVue.$emit("onFinalizado");
              refVue.emAndamento = false;
              return;
            }
          } else {
            Object.assign(refVue.arquivo, { enviando: false, enviado: true, falhou: true });
          }

          refVue.proximo();
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.upload-anexos {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.upload-lista {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.upload-lista-itens {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.upload-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.75rem 0.875rem;
  background: #ffffff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  transition: border-color 0.2s ease, background 0.2s ease;
}

.upload-item-enviando {
  border-color: rgba(23, 66, 87, 0.28);
  background: rgba(23, 66, 87, 0.04);
}

.upload-item-apagando {
  border-color: #f5c2c7;
  background: #fff8f8;
  opacity: 0.85;
  pointer-events: none;
}

.upload-item-falhou {
  border-color: #f5c2c7;
  background: #fff5f5;
}

.upload-item-preview {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 6px;
  border: 1px solid #e9ecef;
  background: #f8f9fa;
  overflow: hidden;
}

.upload-item-thumb {
  max-width: 100%;
  max-height: 100%;
  width: auto;
  height: auto;
  object-fit: contain;
  object-position: center;
  display: block;
}

.upload-item-icone {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 8px;
  background: rgba(23, 66, 87, 0.08);
  color: #174257;
  border: 1px solid rgba(23, 66, 87, 0.28);
}

.upload-item-icone-erro {
  background: #fde8ea;
  border-color: #f5c2c7;
  color: #dc3545;
  font-size: 1.25rem;
}

.upload-item-ext {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  line-height: 1;
}

.upload-item-corpo {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 0.3rem;
  text-align: left;
}

.upload-item-label {
  display: block;
  width: auto;
  max-width: 100%;
  margin: 0;
  align-self: flex-start;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 500;
  color: #495057;
  line-height: 1.2;
}

.upload-item-linha {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  min-width: 0;
}

.upload-item-linha .input-wrapper {
  flex: 1;
  min-width: 0;
}

.upload-item-acoes {
  display: inline-flex;
  align-items: center;
  flex-wrap: nowrap;
  gap: 0.375rem;
  flex-shrink: 0;

  .btn-filter {
    margin: 0;
    text-decoration: none;
    height: 2rem;
    min-height: 2rem;

    &:disabled,
    &[disabled] {
      opacity: 0.65;
      cursor: not-allowed;
      pointer-events: none;
    }
  }

  a.btn-filter {
    line-height: 1;
  }
}

.upload-item-progresso {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin-top: 0.15rem;
}

.upload-progresso-topo {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.7rem;
  color: #495057;
  line-height: 1;
}

.upload-progresso-barra {
  height: 6px;
  border-radius: 4px;
  background: #e9ecef;
  overflow: hidden;
}

.upload-progresso-preenchimento {
  height: 100%;
  background: linear-gradient(135deg, #174257 0%, #103240 100%);
  transition: width 0.3s ease;
}

.upload-item-status {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.7rem;
  line-height: 1.2;
}

.upload-item-status-erro {
  color: #dc3545;
}

.upload-dropzone {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  min-height: 4.25rem;
  padding: 0.75rem 0.875rem;
  text-align: left;
  background: #ffffff;
  border: 1px dashed #ced4da;
  border-radius: 8px;
  transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;

  &:hover:not(.upload-dropzone-desabilitada) {
    border-color: #174257;
    background: rgba(23, 66, 87, 0.04);
  }
}

.upload-dropzone-ativa {
  border-style: solid;
  border-color: rgba(23, 66, 87, 0.28);
  background: rgba(23, 66, 87, 0.04);
}

.upload-dropzone-arrasto {
  border-style: solid;
  border-color: #174257;
  background: rgba(23, 66, 87, 0.1);
  box-shadow: 0 0 0 3px rgba(23, 66, 87, 0.12);
}

.upload-dropzone-desabilitada {
  opacity: 0.7;
  cursor: not-allowed;
}

.upload-dropzone-info {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 0.75rem;
  min-width: 0;
  flex: 1;
  cursor: pointer;
  text-align: left;
}

.upload-dropzone-icone {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  flex-shrink: 0;
  border-radius: 8px;
  background: rgba(23, 66, 87, 0.08);
  color: #174257;
  font-size: 1rem;
}

.upload-dropzone-corpo {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  gap: 0.15rem;
  min-width: 0;
  text-align: left;
}

.upload-dropzone-titulo {
  font-size: 0.875rem;
  font-weight: 600;
  color: #212529;
  line-height: 1.25;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-align: left;
  width: 100%;
}

.upload-dropzone-ajuda {
  font-size: 0.75rem;
  color: #6c757d;
  line-height: 1.25;
  text-align: left;
  width: 100%;
}

.upload-dropzone-acoes {
  display: inline-flex;
  align-items: center;
  justify-content: flex-start;
  gap: 0.5rem;
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .upload-item {
    flex-direction: column;
    align-items: stretch;
  }

  .upload-item-linha {
    flex-direction: column;
    align-items: stretch;
  }

  .upload-item-acoes {
    width: 100%;
    flex-wrap: wrap;

    .btn-filter {
      flex: 1 1 auto;
      justify-content: center;
    }
  }

  .upload-dropzone {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
    text-align: left;
  }

  .upload-dropzone-info,
  .upload-dropzone-corpo,
  .upload-dropzone-titulo,
  .upload-dropzone-ajuda {
    text-align: left !important;
    justify-content: flex-start;
    align-items: flex-start;
  }

  .upload-dropzone-acoes {
    width: 100%;
    justify-content: flex-start;

    .btn-filter {
      flex: 0 1 auto;
      justify-content: center;
    }
  }
}

.upload-preview-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(2px);
}

.upload-preview-dialog {
  display: flex;
  flex-direction: column;
  width: min(920px, 100%);
  max-height: calc(100vh - 2.5rem);
  background: #ffffff;
  border-radius: 10px;
  border: 1px solid #dee2e6;
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.28);
  overflow: hidden;
}

.upload-preview-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e9ecef;
  background: #f8f9fa;
}

.upload-preview-titulo {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0;
  font-size: 0.9375rem;
  font-weight: 600;
  color: #212529;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;

  i {
    color: #174257;
    flex-shrink: 0;
  }
}

.upload-preview-fechar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  background: #ffffff;
  color: #495057;
  cursor: pointer;
  flex-shrink: 0;

  &:hover {
    background: #e9ecef;
    color: #212529;
  }
}

.upload-preview-body {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
  min-height: 280px;
  padding: 1rem;
  background: #f1f3f5;
  overflow: auto;
}

.upload-preview-loading {
  position: absolute;
  inset: 0;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  background: rgba(241, 243, 245, 0.92);
  color: #495057;
  font-size: 0.875rem;
  font-weight: 500;

  i {
    font-size: 1.75rem;
    color: #174257;
  }
}

.upload-preview-oculto {
  opacity: 0;
  pointer-events: none;
}

.upload-preview-imagem {
  max-width: 100%;
  max-height: calc(100vh - 12rem);
  width: auto;
  height: auto;
  object-fit: contain;
  border-radius: 6px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
  background: #ffffff;
}

.upload-preview-pdf {
  width: 100%;
  height: calc(100vh - 12rem);
  min-height: 420px;
  border: 0;
  border-radius: 6px;
  background: #ffffff;
}

.upload-preview-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-top: 1px solid #e9ecef;
  background: #ffffff;

  .btn-filter {
    margin: 0;
    text-decoration: none;
  }
}

.input-wrapper {
  position: relative;

  .input-icon {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
    font-size: 0.75rem;
  }
}

.form-control-modern {
  width: 100%;
  height: 2rem;
  min-height: 2rem;
  padding: 0.375rem 2.25rem 0.375rem 0.75rem;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  background: #ffffff;
  color: #212529;
  font-size: 0.8125rem;
  line-height: 1.2;
  transition: all 0.2s ease;

  &:focus {
    outline: none;
    border-color: #174257;
    box-shadow: 0 0 0 3px rgba(23, 66, 87, 0.1);
  }

  &:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
    opacity: 0.9;
  }
}

.btn-filter {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
  padding: 0.375rem 0.875rem;
  border-radius: 5px;
  font-weight: 500;
  font-size: 0.8125rem;
  transition: all 0.2s ease;
  border: none;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin: 0;
  height: 2rem;
  min-height: 2rem;
  line-height: 1;
  flex-shrink: 0;
  white-space: nowrap;

  i { font-size: 0.75rem; line-height: 1; }
  span { white-space: nowrap; line-height: 1; }

  &:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
  }
}

.btn-filter-primary {
  background: linear-gradient(135deg, #174257 0%, #103240 100%);
  color: #ffffff;

  &:hover:not(:disabled) {
    background: linear-gradient(135deg, #103240 0%, #0c2833 100%);
    color: #ffffff;
  }
}

.btn-filter-neutro {
  background: #f1f3f5;
  border: 1px solid #ced4da;
  color: #343a40;

  &:hover:not(:disabled) {
    background: #e9ecef;
    border-color: #adb5bd;
    color: #212529;
  }
}

.btn-filter-erro {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: #ffffff;

  &:hover:not(:disabled) {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    color: #ffffff;
  }
}
</style>
