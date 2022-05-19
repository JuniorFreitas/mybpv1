const Utils = {
    computed: {
        urlSite() {
            return process.env.MIX_URL_SITE;
        },
        ufs() {
            return ["AC", "AL", "AP", "AM", "BA", "CE", "DF", "ES", "GO", "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI", "RJ", "RN", "RS", "RO", "RR", "SC", "SP", "SE", "TO"];
        },
        estados() {
            return ["Acre", "Alagoas", "Amapá", "Amazonas", "Bahia", "Ceará", "Distrito Federal", "Espírito Santo", "Goiás", "Maranhão", "Mato Grosso", "Mato Grosso do Sul", "Minas Gerais", "Pará", "Paraíba", "Paraná", "Pernambuco", "Piauí", "Rio de Janeiro", "Rio Grande do Norte", "Rio Grande do Sul", "Rondônia", "Roraima", "Santa Catarina", "São Paulo", "Sergipe", "Tocantins"];
        },
        por_pagina() {
            return [20, 50, 100, 150];
        },
        tinySimples() {
            return {
                toolbar: ["undo redo | bold italic underline"],
                menubar: false,
                statusbar: true,
                schema: "html5",
                height: 250,
                resize: true,
                language: "pt_BR",
                language_url: `${this.urlSite}/js/tinymce/langs/pt_BR.js`,
                branding: false,
                fontsize_formats: "12pt 14pt 18pt 24pt 36pt",
                plugins: "paste",
                paste_auto_cleanup_on_paste: true,
                paste_remove_styles: true,
                paste_remove_styles_if_webkit: true,
                paste_strip_class_attributes: true,
                content_style: "body { font-size: 12pt; font-family: Arial; }",
                setup: function(ed) {
                    ed.on("init", function(e) {
                        ed.execCommand("fontName", false, "Arial");
                        ed.execCommand("fontSize", false, "12pt");
                    });
                },
                key: process.env.MIX_TYNEKEY
            };
        }
    }
};

export default Utils;
