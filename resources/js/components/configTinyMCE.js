export default {
    toolbar: ['bold italic underline'],
    menubar: false,
    statusbar: true,
    schema: 'html5',
    height: 200,
    width: '100%',
    resize: true,
    language: 'pt_BR',
    language_url: `${URL_SITE}/js/tinymce/langs/pt_BR.js`,
    branding: false,
    fontsize_formats: "12pt 14pt 18pt 24pt 36pt",
    plugins: "paste",
    paste_auto_cleanup_on_paste: true,
    paste_remove_styles: true,
    paste_remove_styles_if_webkit: true,
    paste_strip_class_attributes: true,
    content_style: 'body { font-size: 12pt; font-family: Arial; }',
    setup: function (ed) {
        ed.on('init', function (e) {
            ed.execCommand("fontName", false, "Arial");
            ed.execCommand("fontSize", false, "10pt");
        });
    },
    key: '47ujbyvg8ad4y8y2a0fcqztyms96xh1fcryr63oxa97gi0yw',
}
