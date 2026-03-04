const path = require('path')
const webpack = require('webpack')
const mix = require('laravel-mix')
// const lodash = require("lodash");
// const folder = {
//     src: "resources/", // source files
//     dist: "public/", // build files
//     dist_assets: "public/assets/" //build assets files
// };
//
// if (mix.inProduction()) {
//     jsOutputDir = 'build/js';
//     cssOutputDir = 'build/css';
// } else {
//     jsOutputDir = 'build/js/development';
//     cssOutputDir = 'build/css/development';
// }
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('resources/sass/app.scss', 'public/css', {
    sassOptions: {
        silenceDeprecations: ['abs-percent', 'color-functions', 'global-builtin', 'if-function', 'import', 'legacy-js-api', 'slash-div'],
        quietDeps: true,
        loadPaths: [path.resolve(__dirname, 'node_modules')]
    }
})

mix.copy('resources/css/icons.min.css', 'public/css')

// mix.js('resources/js/app.js', 'public/js')
//     .sass('resources/sass/app.scss', 'public/css');

// mix.sass('resources/sass/app.scss', 'public/css');
// mix.sass('resources/template/scss/app.scss', 'public/css/layout');
// mix.sass('resources/template/scss/icons.scss', 'public/css/layout');

// copy all fonts
/*var out = folder.dist_assets + "fonts";
mix.copyDirectory(folder.src + "template/fonts", out);

// copy all images
var out = folder.dist_assets + "images";
mix.copyDirectory(folder.src + "template/images", out);*/
// mix.sass('resources/template/scss/bootstrap.scss', folder.dist_assets + "css").minify(folder.dist_assets + "css/bootstrap.css");
// mix.sass('resources/template/scss/bootstrap-dark.scss', folder.dist_assets + "css").minify(folder.dist_assets + "css/bootstrap-dark.css");
// mix.sass('resources/template/scss/icons.scss', folder.dist_assets + "css").minify(folder.dist_assets + "css/icons.css");
// // mix.sass('resources/template/scss/app-rtl.scss', folder.dist_assets + "css").minify(folder.dist_assets + "css/app-rtl.css");
// mix.sass('resources/template/scss/app.scss', folder.dist_assets + "css").minify(folder.dist_assets + "css/app.css");
// mix.sass('resources/template/scss/app-dark.scss', folder.dist_assets + "css").minify(folder.dist_assets + "css/app-dark.css");
//

mix.js('resources/js/app.js', 'public/js')
    //Configurações
    .js('resources/js/recuperasenha/app.js', 'public/js/recuperasenha/')

    .js('resources/js/g/configuracoes/habilidades/app.js', 'public/js/g/habilidades/')
    .js('resources/js/g/configuracoes/papeis/app.js', 'public/js/g/papeis/')
    .js('resources/js/g/configuracoes/feriados/app.js', 'public/js/g/feriados/')
    .js('resources/js/g/configuracoes/encargos/app.js', 'public/js/g/encargos/')
    .js('resources/js/g/configuracoes/horario-acesso/app.js', 'public/js/g/horario-acesso/')
    .js('resources/js/g/configuracoes/municipios/app.js', 'public/js/g/municipios/')
    .js('resources/js/g/configuracoes/bairros/app.js', 'public/js/g/bairros/')
    //administracao
    .js('resources/js/g/administracao/clientes/app.js', 'public/js/g/clientes/')

    .js('resources/js/g/administracao/documentoslegais/contrato/app.js', 'public/js/g/documentoslegais/contrato/')
    .js('resources/js/g/administracao/documentoslegais/documentoempresa/app.js', 'public/js/g/documentoslegais/documentoempresa/')
    .js('resources/js/g/administracao/documentoslegais/documentossma/app.js', 'public/js/g/documentoslegais/documentossma')
    .js('resources/js/g/administracao/documentoslegais/tipodocumento/app.js', 'public/js/g/documentoslegais/tipodocumento')
    .js('resources/js/g/administracao/documentoslegais/tiposervico/app.js', 'public/js/g/documentoslegais/tiposervico')
    .js('resources/js/g/administracao/documentoslegais/formacontrato/app.js', 'public/js/g/documentoslegais/formacontrato')
    .js('resources/js/g/administracao/documento-assinatura/app.js', 'public/js/g/administracao/documento-assinatura/')
    .js('resources/js/g/administracao/fornecedores/app.js', 'public/js/g/fornecedores/')
    .js('resources/js/g/administracao/atareuniao/app.js', 'public/js/g/atareuniao/')
    .js('resources/js/g/administracao/pesquisaclima/app.js', 'public/js/g/pesquisaclima/')
    .js('resources/js/g/administracao/planejamentodiario/app.js', 'public/js/g/planejamentodiario/')
    .js('resources/js/g/administracao/aniversariantes/app.js', 'public/js/g/aniversariantes/')
    //Cadastros
    .js('resources/js/g/cadastros/treinamentoindustria/app.js', 'public/js/g/treinamentoindustria/')
    .js('resources/js/g/cadastros/segmentostreinamento/app.js', 'public/js/g/segmentostreinamento/')
    .js('resources/js/g/cadastros/treinamentosgi/app.js', 'public/js/g/treinamentosgi/')
    .js('resources/js/g/cadastros/departamento/app.js', 'public/js/g/departamento/')
    .js('resources/js/g/cadastros/instrutor/app.js', 'public/js/g/instrutor/')
    .js('resources/js/g/cadastros/empresatreinamento/app.js', 'public/js/g/empresatreinamento/')
    .js('resources/js/g/cadastros/empresa-exame/app.js', 'public/js/g/empresaexame/')
    .js('resources/js/g/cadastros/empresa-temporaria/app.js', 'public/js/g/empresatemporaria/')
    .js('resources/js/g/cadastros/provas/app.js', 'public/js/g/cadastro/provas/')
    .js('resources/js/g/cadastros/beneficio/app.js', 'public/js/g/beneficio/')
    .js('resources/js/g/cadastros/vagas/app.js', 'public/js/g/vagas/')
    .js('resources/js/g/cadastros/vagas_abertas/app.js', 'public/js/g/vagas_abertas/')
    .js('resources/js/g/cadastros/areas/app.js', 'public/js/g/areas/')
    .js('resources/js/g/cadastros/centrocusto/app.js', 'public/js/g/centrocusto/')
    .js('resources/js/g/cadastros/projeto/app.js', 'public/js/g/projeto/')
    .js('resources/js/g/cadastros/tipocih/app.js', 'public/js/g/cadastros/tipocih/')
    .js('resources/js/g/cadastros/avaliacoes/avaliacaotipo/app.js', 'public/js/g/avaliacoes/avaliacaotipo/')
    .js('resources/js/g/cadastros/avaliacoes/avaliadortipo/app.js', 'public/js/g/avaliacoes/avaliadortipo/')
    .js('resources/js/g/cadastros/avaliacoes/avaliacaotopico/app.js', 'public/js/g/avaliacoes/avaliacaotopico/')
    .js('resources/js/g/cadastros/avaliacoes/avaliacao/app.js', 'public/js/g/avaliacoes/avaliacao/')
    .js('resources/js/g/cadastros/avaliacoes/avaliador/app.js', 'public/js/g/avaliacoes/avaliador/')
    .js('resources/js/g/cadastros/avaliacoes/avaliar/app.js', 'public/js/g/avaliacoes/avaliar/')

    //Curriculos
    .js('resources/js/g/curriculos/recrutamento/app.js', 'public/js/g/recrutamento/')
    .js('resources/js/g/curriculos/selecionados/app.js', 'public/js/g/selecionados/')
    //Entrevistas
    .js('resources/js/g/entrevistas/parecer_rh/app.js', 'public/js/g/entrevistas/parecer_rh/')
    .js('resources/js/g/entrevistas/parecer_rota/app.js', 'public/js/g/entrevistas/parecer_rota/')
    .js('resources/js/g/entrevistas/parecer_entrevista_tecnica/app.js', 'public/js/g/entrevistas/parecer_entrevista_tecnica/')
    .js('resources/js/g/entrevistas/parecer_teste_pratico/app.js', 'public/js/g/entrevistas/parecer_teste_pratico/')
    .js('resources/js/g/entrevistas/entrevista_rh/app.js', 'public/js/g/entrevistas/entrevista_rh/')
    .js('resources/js/g/entrevistas/gestor_rh/app.js', 'public/js/g/entrevistas/gestor_rh/')
    .js('resources/js/g/entrevistas/resultado_integrado/app.js', 'public/js/g/entrevistas/resultado_integrado/')

    //Admissao
    .js('resources/js/g/admissao/processo/app.js', 'public/js/g/admissao/processo/')
    //admissao->pre-admissao
    .js('resources/js/g/admissao/preadmissao/app.js', 'public/js/g/admissao/preadmissao/')
    //Admissao -> documentos -> Carta oferta
    .js('resources/js/g/admissao/documentos/cartaoferta/app.js', 'public/js/g/admissao/documentos/cartaoferta/')
    //admissao->apontamento
    .js('resources/js/g/admissao/apontamento/cih/app.js', 'public/js/g/admissao/apontamento/cih/')
    .js('resources/js/g/admissao/apontamento/intermitente/app.js', 'public/js/g/admissao/apontamento/intermitente/')
    //admissao->historico
    .js('resources/js/g/admissao/historico/app.js', 'public/js/g/admissao/historico/')
    //admissao->pos-admissao
    .js('resources/js/g/posadmissao/app.js', 'public/js/g/posadmissao/')

    //portaria
    .js('resources/js/g/portaria/app.js', 'public/js/g/portaria/')
    //treinamentos
    .js('resources/js/g/treinamentos/app.js', 'public/js/g/treinamentos/')
    //treinamentos sgi
    .js('resources/js/g/treinamentos/sgi/app.js', 'public/js/g/treinamentos/sgi/')
    //certificado
    .js('resources/js/g/certificado/app.js', 'public/js/g/certificado/')

    //ocorrencia
    .js('resources/js/g/ocorrencia/app.js', 'public/js/g/ocorrencia/')

    .js('resources/js/g/weekly-report/app.js', 'public/js/g/weekly-report/')
    .js('resources/js/g/chat/app.js', 'public/js/g/chat/')

    //Usúarios
    .js('resources/js/g/usuarios/usuarios/app.js', 'public/js/g/usuarios/')
    .js('resources/js/g/usuarios/alterar-senha/app.js', 'public/js/g/alterar-senha/')

    //Financeiro
    .js('resources/js/g/financeiro/fluxo-caixa/app.js', 'public/js/g/fluxo-caixa/')
    .js('resources/js/g/financeiro/classificacao-plano-conta/app.js', 'public/js/g/classificacao-plano-conta/')
    .js('resources/js/g/financeiro/formas-pagamento/app.js', 'public/js/g/formas-pagamento/')
    .js('resources/js/g/financeiro/planos-conta/app.js', 'public/js/g/planos-conta/')

    //controle de ponto
    .js('resources/js/g/controle-ponto/configuracoes/app.js', 'public/js/g/controle-ponto/configuracoes/')
    .js('resources/js/g/controle-ponto/feriados/app.js', 'public/js/g/controle-ponto/feriados/')
    .js('resources/js/g/controle-ponto/ocorrencias_jornadas/app.js', 'public/js/g/controle-ponto/ocorrencias_jornadas/')
    .js('resources/js/g/controle-ponto/escalas/app.js', 'public/js/g/controle-ponto/escalas/')
    .js('resources/js/g/controle-ponto/ponto-eletronico/app.js', 'public/js/g/controle-ponto/ponto-eletronico/')
    .js('resources/js/g/controle-ponto/camera/app.js', 'public/js/g/controle-ponto/camera/')
    .js('resources/js/g/controle-ponto/ajuste-jornadas/app.js', 'public/js/g/controle-ponto/ajuste-jornadas/')
    .js('resources/js/g/controle-ponto/folha-ponto/app.js', 'public/js/g/controle-ponto/folha-ponto/')
    .js('resources/js/g/controle-ponto/folha-manual/app.js', 'public/js/g/controle-ponto/folha-manual/')
    .js('resources/js/g/controle-ponto/relatorio-sintetico/app.js', 'public/js/g/controle-ponto/relatorio-sintetico/')
    .copy('resources/js/g/controle-ponto/camera/adapter-latest.js', 'public/js/g/controle-ponto/camera/')
    .copy('resources/js/g/controle-ponto/camera/face-api.min.js', 'public/js/g/controle-ponto/camera/')
    .copy('resources/js/g/controle-ponto/ponto-eletronico/webcam.min.js', 'public/js/g/controle-ponto/ponto-eletronico/')

    .copyDirectory('resources/js/g/controle-ponto/camera/models', 'public/js/g/controle-ponto/camera/models')
    .copyDirectory('resources/js/g/controle-ponto/camera/labels', 'public/js/g/controle-ponto/camera/labels')

    //Cloud
    .js('resources/js/g/cloud/app.js', 'public/js/g/cloud/')
    .js('resources/js/g/cloud/configuracoes/app.js', 'public/js/g/cloud/configuracoes/')
    .js('resources/js/g/cloud/cadastro/app.js', 'public/js/g/cloud/cadastro/')

    //Relatórios
    .js('resources/js/g/relatorios/controleusuarios/app.js', 'public/js/g/relatorios/controleusuarios/')
    .js('resources/js/g/relatorios/vencimentoasos/app.js', 'public/js/g/relatorios/vencimentoasos/')
    .js('resources/js/g/relatorios/medidasadministrativas/app.js', 'public/js/g/relatorios/medidasadministrativas/')
    .js('resources/js/g/relatorios/treinamento/app.js', 'public/js/g/relatorios/treinamento/')
    .js('resources/js/g/relatorios/ferias/app.js', 'public/js/g/relatorios/ferias/')
    .js('resources/js/g/relatorios/vencimentoferias/app.js', 'public/js/g/relatorios/vencimentoferias/')
    .js('resources/js/g/relatorios/centrodecusto/app.js', 'public/js/g/relatorios/centrodecusto/')
    .js('resources/js/g/relatorios/efetivo/app.js', 'public/js/g/relatorios/efetivo/')
    .js('resources/js/g/relatorios/aniversariantes/app.js', 'public/js/g/relatorios/aniversariantes/')
    .js('resources/js/g/relatorios/nps/app.js', 'public/js/g/relatorios/nps/')
    .js('resources/js/g/relatorios/avaliacao-experiencia/app.js', 'public/js/g/relatorios/avaliacao-experiencia/')

    //Site G/
    .js('resources/js/g/site/galeria/app.js', 'public/js/g/site/galeria')
    .js('resources/js/g/site/clientes/app.js', 'public/js/g/site/clientes')
    .js('resources/js/g/site/testemunhal/app.js', 'public/js/g/site/testemunhal/')

    //Planejamento -> Requisição de vagas
    .js('resources/js/g/planejamento/requisicao-vagas/app.js', 'public/js/g/planejamento/requisicao-vagas/')
    .js('resources/js/g/planejamento/requisicao-vagas/requisicao-vaga-app.js', 'public/js/g/planejamento/requisicao-vagas/')
    .js('resources/js/g/planejamento/requisicao-vagas/campos-custom-app.js', 'public/js/g/planejamento/requisicao-vagas/')
    //Planejamento -> Movimentação
    .js('resources/js/g/planejamento/movimentacao/app.js', 'public/js/g/planejamento/movimentacao/')
    //Planejamento -> Mobilizcao
    .js('resources/js/g/planejamento/mobilizacao/app.js', 'public/js/g/planejamento/mobilizacao/')

    //Provas
    .js('resources/js/provas/app.js', 'public/js/provas/')

    //Documentos
    .js('resources/js/documentos/app.js', 'public/js/documentos/')

    //Carta Oferta
    .js('resources/js/cartaoferta/app.js', 'public/js/cartaoferta/')

    //Pesquisa Clima
    .js('resources/js/pesquisaclima/app.js', 'public/js/pesquisaclima/')

    .js('resources/js/vagas-abertas/app.js', 'public/js/vagas-abertas/')

    //controle de exames
    .js('resources/js/g/controle-exames/app.js', 'public/js/g/controle-exames/')
    .js('resources/js/g/controle-exames/clinica/app.js', 'public/js/g/clinica/controle-exames/')

    //Perfil Usuário
    .js('resources/js/g/perfil/app.js', 'public/js/g/perfil')

    //Impressao
    .js('resources/js/g/impressao/avaliacao/app.js', 'public/js/g/impressao/avaliacao')

    //Aprovação Extra Config
    .js('resources/js/g/administracao/aprovacao-extra-config/app.js', 'public/js/g/aprovacao-extra-config/')

    .copyDirectory('resources/js/tinymce', 'public/js/tinymce')
    .vue({ version: 3 })

mix.babel(
    [
        './node_modules/metismenu/dist/metisMenu.js',
        './node_modules/simplebar/dist/simplebar.js',
        './node_modules/node-waves/dist/waves.js',
        // 'resources/template/app.js',

        'resources/js/funcoes.js',
        'resources/js/jquery.mask.js',
        'resources/js/jquery.maskMoney.js'
    ],
    'public/js/funcoes.js'
)

mix.disableNotifications()

mix.webpackConfig({
    resolve: {
        extensions: ['.*', '.wasm', '.mjs', '.js', '.jsx', '.json', '.vue']
    },
    plugins: [
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: true,
            __VUE_PROD_DEVTOOLS__: false,
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false
        })
    ]
})

if (mix.inProduction()) {
    mix.version()
}
