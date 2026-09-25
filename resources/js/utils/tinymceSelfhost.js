function tinymcePublicBase() {
    const site = typeof URL_SITE !== 'undefined' && URL_SITE ? String(URL_SITE).replace(/\/$/, '') : ''
    return `${site}/tinymce`
}

function applySelfHost(config) {
    const rest = Object.assign({}, config || {})
    delete rest.key
    return Object.assign({}, rest, {
        base_url: tinymcePublicBase(),
        suffix: '.min',
        language: 'pt_BR',
        language_url: `${tinymcePublicBase()}/langs/pt-br.min.js`
    })
}

const TINYMCE_PRESETS = {
    padrao: {
        toolbar: ['undo redo fontselect fontsizeselect | bold italic underline'],
        menubar: false,
        statusbar: true,
        schema: 'html5',
        width: '100%',
        height: 650,
        resize: true,
        branding: false,
        fontsize_formats: '12pt',
        plugins: 'paste',
        paste_auto_cleanup_on_paste: true,
        paste_remove_styles: true,
        paste_remove_styles_if_webkit: true,
        paste_strip_class_attributes: true,
        content_style: 'body { font-size: 12pt; font-family: Arial; }',
        setup: function (ed) {
            ed.on('init', function () {
                ed.execCommand('fontName', false, 'Arial')
                ed.execCommand('fontSize', false, '12pt')
            })
        }
    },
    prova: {
        toolbar: ['undo redo fontselect fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | insert table | bullist numlist link table '],
        menubar: false,
        statusbar: true,
        schema: 'html5',
        width: '100%',
        height: 200,
        resize: true,
        branding: false,
        font_formats: 'Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;',
        fontsize_formats: '12pt 14pt 18pt 24pt 36pt',
        paste_auto_cleanup_on_paste: true,
        paste_remove_styles: true,
        paste_remove_styles_if_webkit: true,
        paste_strip_class_attributes: true,
        content_style: 'body { font-size: 12pt; font-family: Arial; }',
        plugins: 'image, table, paste',
        image_class_list: [{ title: 'Responsiva', value: 'img-fluid' }],
        setup: function (ed) {
            ed.on('init', function () {
                ed.execCommand('fontName', false, 'Arial')
                ed.execCommand('fontSize', false, '12pt')
            })
        }
    },
    entrevista: {
        toolbar: ['underline'],
        menubar: false,
        statusbar: true,
        schema: 'html5',
        height: 650,
        resize: true,
        branding: false,
        fontsize_formats: '12pt 14pt 18pt 24pt 36pt',
        plugins: 'paste',
        paste_auto_cleanup_on_paste: true,
        paste_remove_styles: true,
        paste_remove_styles_if_webkit: true,
        paste_strip_class_attributes: true,
        content_style: 'body { font-size: 12pt; font-family: Arial; }',
        setup: function (ed) {
            ed.on('init', function () {
                ed.execCommand('fontName', false, 'Arial')
                ed.execCommand('fontSize', false, '12pt')
            })
        }
    },
    simples: {
        toolbar: ['undo redo | bold italic underline'],
        menubar: false,
        statusbar: true,
        schema: 'html5',
        height: 250,
        resize: true,
        branding: false,
        fontsize_formats: '12pt 14pt 18pt 24pt 36pt',
        plugins: 'paste',
        paste_auto_cleanup_on_paste: true,
        paste_remove_styles: true,
        paste_remove_styles_if_webkit: true,
        paste_strip_class_attributes: true,
        content_style: 'body { font-size: 12pt; font-family: Arial; }',
        setup: function (ed) {
            ed.on('init', function () {
                ed.execCommand('fontName', false, 'Arial')
                ed.execCommand('fontSize', false, '12pt')
            })
        }
    },
    basico: {
        toolbar: ['undo redo | bold italic underline | bullist numlist | link image'],
        menubar: false,
        statusbar: false,
        schema: 'html5',
        height: 160,
        resize: true,
        branding: false,
        plugins: 'paste lists link autolink image',
        paste_data_images: true,
        automatic_uploads: true,
        images_reuse_filename: false,
        file_picker_types: 'image',
        image_description: false,
        image_dimensions: false,
        image_class_list: [{ title: 'Responsiva', value: 'img-fluid' }],
        paste_auto_cleanup_on_paste: true,
        paste_remove_styles: true,
        paste_remove_styles_if_webkit: true,
        paste_strip_class_attributes: true,
        default_link_target: '_blank',
        link_assume_external_targets: true,
        extended_valid_elements: 'span[class|contenteditable|data-user-id|data-nome]',
        content_style:
            'body { font-size: 13px; font-family: Arial, Helvetica, sans-serif; line-height: 1.45; margin: 8px; }' +
            ' img { max-width: 100%; height: auto; }' +
            ' .wr-mention { display: inline-block; background: #e8f1f6; color: #174257; border-radius: 4px;' +
            ' padding: 0 4px; font-weight: 600; white-space: nowrap; }',
        setup: function (ed) {
            ed.on('init', function () {
                ed.execCommand('fontName', false, 'Arial')
            })
        }
    }
}

function getTinyMceInit(preset, overrides) {
    const base = (preset && TINYMCE_PRESETS[preset]) || {}
    return applySelfHost(Object.assign({}, base, overrides || {}))
}

let loadingPromise = null

function loadTinyMce() {
    if (typeof window === 'undefined') {
        return Promise.reject(new Error('TinyMCE precisa do browser'))
    }
    if (window.tinymce) {
        return Promise.resolve(window.tinymce)
    }
    if (loadingPromise) {
        return loadingPromise
    }

    loadingPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script')
        script.src = `${tinymcePublicBase()}/tinymce.min.js`
        script.async = true
        script.onload = () => {
            if (window.tinymce) {
                resolve(window.tinymce)
                return
            }
            loadingPromise = null
            reject(new Error('TinyMCE self-host nao inicializou'))
        }
        script.onerror = () => {
            loadingPromise = null
            reject(new Error('Falha ao carregar TinyMCE self-host'))
        }
        document.head.appendChild(script)
    })

    return loadingPromise
}

const api = {
    tinymcePublicBase,
    applySelfHost,
    TINYMCE_PRESETS,
    getTinyMceInit,
    loadTinyMce
}

module.exports = api
module.exports.default = api
