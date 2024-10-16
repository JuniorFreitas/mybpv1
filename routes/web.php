<?php

use App\Models\CartaOferta;
use Illuminate\Support\Facades\Route;

Route::get('/recupera-senha/{token}', [\App\Http\Controllers\UserController::class, 'recuperaSenha'])->name('recuperaSenhanew');
Route::post('/envia-recupera-senha', [\App\Http\Controllers\UserController::class, 'recuperaSenhaPost'])->name('recuperaSenhaPost');

Route::get('download-checklist/{empresa}', function ($empresa) {
    $path = CartaOferta::checklistArquivo($empresa);
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);

    return response($data)
        ->header('Content-Type', 'application/' . $type)
        ->header('Content-Disposition', 'attachment; filename="checklist.' . $type . '"');
})->name('download-checklist');

Route::redirect('/', 'g/login');

Route::group(['prefix' => 'publico', 'as' => 'publico.'], function () {
    Route::get('controle-exames/ficha-encaminhamento/{exame}/{token}', [\App\Http\Controllers\ControleExameController::class, 'getFichaPdf'])->name('encaminhamento_exame_fichapdf');
    Route::post('cnpjbusca', [\App\Http\Controllers\PublicoController::class, 'cnpjbusca'])->name('cnpjbusca');
    Route::get('lista-vagas', [\App\Http\Controllers\PublicoController::class, 'listaVagas'])->name('lista-vagas');
    Route::get('lista-areas', [\App\Http\Controllers\PublicoController::class, 'listaAreasEtiquetas'])->name('lista-areas');
    Route::get('lista-areas/{cliente}', [\App\Http\Controllers\PublicoController::class, 'listaAreasEtiquetasCliente'])->name('lista-areas-cliente');
    Route::post('centro-custos', [\App\Http\Controllers\PublicoController::class, 'listaCentroCusto'])->name('lista-centro');
    Route::get('centro-custos-cnpj', [\App\Http\Controllers\PublicoController::class, 'listaCentroCustoPorCnpj'])->name('lista-centro');
    Route::post('upload', [\App\Http\Controllers\PublicoController::class, 'upload'])->name('upload');
    Route::get('foto/{nome}', [\App\Http\Controllers\PublicoController::class, 'download'])->name('foto-download');

    Route::get('cloud/anexo/{arquivo}', [\App\Http\Controllers\CloudController::class, 'anexoShow'])->name('cloud.anexo-show');
//    Route::get('cloud/{arquivo}', [\App\Http\Controllers\CloudController::class,'download'])->name('cloud.anexo-download')->middleware(['auth', 'habilidades', 'can:cloud']);
    Route::get('cloud/anexoDownload/{arquivo}', [\App\Http\Controllers\CloudController::class, 'download'])->name('cloud.anexo-download');
    Route::delete('cloud/anexo/{arquivo}', [\App\Http\Controllers\CloudController::class, 'anexoDelete'])->name('cloud.anexo-delete')->middleware(['auth', 'configuracao_habilidades', 'can:cloud']);


    Route::get('cloud/anexo/{arquivo}', [\App\Http\Controllers\CloudController::class, 'anexoShow'])->name('cloud.anexo-show');
//    Route::get('cloud/{arquivo}', [\App\Http\Controllers\CloudController::class,'download'])->name('cloud.anexo-download')->middleware(['auth', 'habilidades', 'can:cloud']);
    Route::get('cloud/anexoDownload/{arquivo}', [\App\Http\Controllers\CloudController::class, 'download'])->name('cloud.anexo-download');
    Route::delete('cloud/anexo/{arquivo}', [\App\Http\Controllers\CloudController::class, 'anexoDelete'])->name('cloud.anexo-delete')->middleware(['auth', 'configuracao_habilidades', 'can:cloud']);

});


Route::group(['prefix' => 'g'], function () {
    // Authentication Routes...
    Route::get('login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('sair', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    // Registration Routes...
    Route::get('register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('auth', 'configuracao_habilidades');
    Route::post('register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->middleware('auth', 'configuracao_habilidades');
    // Password Reset Routes...
    Route::get('password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset']);

    Route::post('/enviaSolicitacaoSenha', [\App\Http\Controllers\UserController::class, 'solicitaRecuperaSenha'])->name('solicitaRecuperaSenha');

});

Route::get('carteira/{curriculo}', [\App\Http\Controllers\TreinamentoController::class, 'carteiraIndividual'])->name('treinamento.carteira');
Route::group(['prefix' => '3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS'], function () {
    Route::get('verificaCliente', [\App\Http\Controllers\ClientesController::class, 'clientesProximoVencimento'])->name('vencimentoClientes');
    Route::get('recrutamentos/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', [\App\Http\Controllers\RecrutamentoController::class, 'export'])->name('recrutamentos.excel');
    Route::get('parecer_rh/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', [\App\Http\Controllers\ParecerRhController::class, 'export'])->name('parecerrh.excel');
    Route::get('parecer_rota_transporte/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', [\App\Http\Controllers\ParecerRotaController::class, 'export'])->name('parecer_rota_transporte.excel');
    Route::get('parecer_entrevista_tecnica/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', [\App\Http\Controllers\ParecerEntrevistaTecnicaController::class, 'export'])->name('parecer_entrevista_tecnica.excel');
    Route::get('parecer_teste_pratico/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', [\App\Http\Controllers\ParecerTestePraticoController::class, 'export'])->name('parecer_teste_pratico.excel');
    Route::post('portaria/export', [\App\Http\Controllers\PortariaController::class, 'export'])->name('portaria.excel');

    /* Route::get('resultado_integrado/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'ResultadoIntegradoController@export')->name('resultado_integrado.excel');
     Route::get('admissao/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'AdmissaoController@export')->name('admissao.excel');
     Route::get('clientes/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'ClientesController@export')->name('clientes.excel');
     Route::get('carteira-etiqueta/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'TreinamentoController@export')->name('carteira.excel');

     Route::get('parecer_entrevista_rh/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'EntrevistaRhClienteController@export')->name('parecerentrevistarh.excel');
     Route::get('parecer_gestor_rh/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'EntrevistaGestorClienteController@export')->name('gestor_rh.excel');*/
});


Route::group(['middleware' => ['auth', 'habilidades'], 'as' => 'g.', 'prefix' => 'g'], function () {

    //ROTAS DE FORMULARIO
    Route::group(['as' => 'fomulario.', 'prefix' => 'formulario'], function () {
        Route::get('/carregaResposta', [\App\Http\Controllers\FormularioController::class, 'carregaResposta'])->name('carregaResposta');
        Route::get('/{formulario}', [\App\Http\Controllers\FormularioController::class, 'carrega'])->name('carrega');
        Route::get('buscaFormulario/{tipo}', [\App\Http\Controllers\FormularioController::class, 'buscaFormulario'])->name('buscaFormulario');
//        Route::get('carregaResposta/{resposta}', [\App\Http\Controllers\FormularioController::class, 'carregaResposta'])->name('carregaResposta');

    });

    Route::get('dashboard', [\App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');
    Route::post('downloads', [\App\Http\Controllers\HomeController::class, 'downloads'])->name('downloads');
    Route::get('downloads/exportacao/{arquivo}', [\App\Http\Controllers\HomeController::class, 'downloadArquivo'])->name('downloadArquivo');
    Route::put('concordarTermos', [\App\Http\Controllers\HomeController::class, 'concordarTermos'])->name('concordarTermos');
    Route::post('busca-data-admissao', [\App\Http\Controllers\FeriasPrevistaController::class, 'buscaDataAdmissao'])->name('buscaDataAdmissao');
    Route::get('busca-projetos/{projeto_id}', [\App\Http\Controllers\ProjetoController::class, 'buscaProjeto'])->name('buscaProjeto');
    Route::get('busca-projetos', [\App\Http\Controllers\ProjetoController::class, 'buscaTodosProjeto'])->name('buscaTodosProjeto');
    Route::get('periodos-aquisitivos', [\App\Http\Controllers\FeriasPrevistaController::class, 'buscaPeriodosAquisitivos'])->name('buscaPeriodosAquisitivos');
    Route::get('get-pcmso', [\App\Http\Controllers\ResultadoIntegradoController::class, 'getPcmos'])->name('getPcmos');
    Route::get('get-empresa-exames', [\App\Http\Controllers\ResultadoIntegradoController::class, 'getEmpresaExames'])->name('getEmpresaExames');
    Route::get('get-filiais', [\App\Http\Controllers\CentroCustoController::class, 'getFiliais'])->name('getFiliais');
    Route::post('get-filiais', [\App\Http\Controllers\CentroCustoController::class, 'getFiliaisCentroDeCusto'])->name('getFiliaisCentroDeCusto');

    // AutoCompletes
    Route::group(['as' => 'autocompletes.', 'prefix' => 'autocomplete'], function () {
        //Auto completes
        Route::get('todas-vagas-ativas', [\App\Http\Controllers\AutoCompletesController::class, 'vagasAtivas'])->name('vagas-ativas');
        Route::get('todas-vagas-abertas-ativas', [\App\Http\Controllers\AutoCompletesController::class, 'vagasAbertasAtivas'])->name('vagas-abertas-ativas');
        Route::get('cargos_ativos', [\App\Http\Controllers\AutoCompletesController::class, 'cargosAtivos'])->name('cargos-ativos');
        Route::get('todos-clientes-ativos', [\App\Http\Controllers\AutoCompletesController::class, 'clientesAtivos'])->name('clientes-ativos');
        Route::get('todos-usuarios-ativos', [\App\Http\Controllers\AutoCompletesController::class, 'usuariosAtivos'])->name('usuarios-ativos');
        Route::get('todos-gestores-ativos', [\App\Http\Controllers\AutoCompletesController::class, 'gestoresAtivos'])->name('gestores-ativos');
        Route::get('todos-gestores', [\App\Http\Controllers\AutoCompletesController::class, 'todosGestores'])->name('todos-gestores');

        Route::get('todos-municipios', [\App\Http\Controllers\AutoCompletesController::class, 'municipiosAll'])->name('municipiosAll');
        Route::get('todos-estados', function () {
            return response()->json(\App\Models\Municipio::todosEstados(), 200);
        });
        Route::get('colaboradorCih', [\App\Http\Controllers\AutoCompletesController::class, 'colaboradorCih'])->name('colaboradorCih');
        Route::get('colaboradorIntermitente', [\App\Http\Controllers\AutoCompletesController::class, 'colaboradorIntermitente'])->name('colaboradorIntermitente');
        Route::get('colaboradores', [\App\Http\Controllers\AutoCompletesController::class, 'colaboradores'])->name('colaboradores');
        Route::get('colaboradores-ferias', [\App\Http\Controllers\AutoCompletesController::class, 'colaboradoresFerias'])->name('colaboradoresFerias');
        Route::get('cargosEmpresa', [\App\Http\Controllers\AutoCompletesController::class, 'cargosEmpresa'])->name('cargosEmpresa');
        Route::get('buscaUsuariosAtivos', [\App\Http\Controllers\AutoCompletesController::class, 'buscaUsuariosAtivos'])->name('buscaUsuariosAtivos');
//        Route::get('colaboradorIntermitente/{cliente_id}', [\App\Http\Controllers\AutoCompletesController::class, 'colaboradorIntermitenteCliente'])->name('colaboradorIntermitenteCliente');

        Route::get('funcionarios', [\App\Http\Controllers\AutoCompletesController::class, 'funcionarios'])->name('funcionarios');
        Route::get('documentos-contratos', [\App\Http\Controllers\AutoCompletesController::class, 'documentosLegaisContrato'])->name('documentosLegaisContrato');


        Route::post('buscaAvaliadoresAtivos', [\App\Http\Controllers\AutoCompletesController::class, 'usuariosAtivosAvaliador'])->name('usuarios-ativos-avaliador');

        //        Route::post('/treinamento/buscaCPF', 'TreinamentoEventoController@buscaCPF')->name('treinamento_sgi.buscaCPF');
    });

    Route::group(['as' => 'storage.', 'prefix' => 'storage'], function () {
        Route::post('uploadAnexos', [\App\Http\Controllers\StorageS3Controller::class, 'uploadAnexos'])->name('s3.upload-anexos');
        Route::get('anexo/{arquivo}', [\App\Http\Controllers\StorageS3Controller::class, 'anexoShow'])->name('s3.anexo-show');
        Route::get('anexoDownload/{arquivo}', [\App\Http\Controllers\StorageS3Controller::class, 'download'])->name('s3.anexo-download');
        Route::delete('anexo/{arquivo}', [\App\Http\Controllers\StorageS3Controller::class, 'anexoDelete'])->name('s3.anexo-delete');
    });

    // Administração
    Route::group(['as' => 'administracao.', 'prefix' => 'administracao'], function () {
        // Anexos
        Route::post('clientes/uploadAnexos', [\App\Http\Controllers\ClientesController::class, 'uploadAnexos'])->name('clientes.upload-anexos')->middleware('can:administracao_clientes');
        Route::get('clientes/anexo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'anexoShow'])->name('clientes.anexo-show')->middleware('can:administracao_clientes');
        Route::get('clientes/anexoDownload/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'logoDownload'])->name('clientes.anexo-download')->middleware('can:administracao_clientes');
        Route::delete('clientes/anexo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'anexoDelete'])->name('clientes.anexo-delete')->middleware('can:administracao_clientes');

        // Anexos Logo
        Route::post('clientes/uploadLogo', [\App\Http\Controllers\ClientesController::class, 'uploadLogo'])->name('clientes.upload-logo')->middleware('can:administracao_clientes');
        Route::get('clientes/logo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'logoShow'])->name('clientes.logo-show')->middleware('can:administracao_clientes');
        Route::get('clientes/logoDownload/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'download'])->name('clientes.logo-download')->middleware('can:administracao_clientes');
        Route::delete('clientes/logo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'logoDelete'])->name('clientes.logo-delete')->middleware('can:administracao_clientes');

        // Anexos Mascote
        Route::post('clientes/uploadMascote', [\App\Http\Controllers\ClientesController::class, 'uploadMascote'])->name('clientes.upload-mascote')->middleware('can:administracao_clientes');
        Route::get('clientes/mascote/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'mascoteShow'])->name('clientes.mascote-show')->middleware('can:administracao_clientes');
        Route::get('clientes/mascoteDownload/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'mascoteDownload'])->name('clientes.mascote-download')->middleware('can:administracao_clientes');
        Route::delete('clientes/mascote/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'mascoteDelete'])->name('clientes.mascote-delete')->middleware('can:administracao_clientes');

//            Route::get('clientes/export', [\App\Http\Controllers\ClientesController::class,'export'])->name('clientes.excel')->middleware('can:administracao_clientes');
        Route::get('clientes/buscar-cnpj', [\App\Http\Controllers\ClientesController::class, 'buscaCNPJ'])->name('clientes.verifica-cnpj')->middleware('can:administracao_clientes');
        Route::get('clientes/buscar-cpf', [\App\Http\Controllers\ClientesController::class, 'buscaCPF'])->name('clientes.verifica-cpf')->middleware('can:administracao_clientes');

        // Clientes
        Route::group(['as' => 'clientes.'], function () {
            // Anexos
            Route::post('clientes/uploadAnexos', [\App\Http\Controllers\ClientesController::class, 'uploadAnexos'])->name('upload-anexos')->middleware('can:administracao_clientes');
            Route::get('clientes/anexo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'anexoShow'])->name('anexo-show')->middleware('can:administracao_clientes');
            Route::get('clientes/anexoDownload/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'download'])->name('anexo-download')->middleware('can:administracao_clientes');
            Route::delete('clientes/anexo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'anexoDelete'])->name('anexo-delete')->middleware('can:administracao_clientes');

//            Route::get('clientes/export', [\App\Http\Controllers\ClientesController::class,'export'])->name('excel')->middleware('can:administracao_clientes');
            Route::get('clientes/buscar-cnpj', [\App\Http\Controllers\ClientesController::class, 'buscaCNPJ'])->name('verifica-cnpj')->middleware('can:administracao_clientes');
            Route::get('clientes/buscar-cpf', [\App\Http\Controllers\ClientesController::class, 'buscaCPF'])->name('verifica-cpf')->middleware('can:administracao_clientes');

            Route::get('clientes/{cliente}/pdf', [\App\Http\Controllers\ClientesController::class, 'getFichaPdf'])->name('getFichapdf');
            Route::put('clientes/{cliente}/ativa-desativa', [\App\Http\Controllers\ClientesController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:administracao_clientes');
            Route::post('clientes/search', [\App\Http\Controllers\ClientesController::class, 'searchCliente'])->name('search')->middleware('can:administracao_clientes');
            Route::post('clientes/atualizar', [\App\Http\Controllers\ClientesController::class, 'atualizar'])->name('atualizar')->middleware('can:administracao_clientes');
            Route::resource('clientes', \App\Http\Controllers\ClientesController::class)->middleware('can:administracao_clientes');

            Route::group(['as' => 'filial.', 'prefix' => 'clientes'], function () {
                Route::post('filial', [\App\Http\Controllers\FilialController::class, 'store'])->name('store');
                Route::put('filial/{filial}/ativa-desativa', [\App\Http\Controllers\FilialController::class, 'ativaDesativa'])->name('ativaDesativa');
                Route::get('filial/{filial}/editar', [\App\Http\Controllers\FilialController::class, 'edit'])->name('edit');
                Route::put('filial/{filial}', [\App\Http\Controllers\FilialController::class, 'update'])->name('update');
                Route::post('filial/atualizar', [\App\Http\Controllers\FilialController::class, 'atualizar'])->name('atualizar');
            });

        });


        Route::group(['as' => 'documentoslegais.', 'prefix' => 'documentoslegais'], function () {

            Route::group(['as' => 'contrato.'], function () {
                Route::post('contrato/uploadLogo', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'uploadLogo'])->name('upload-logo')->middleware('can:administracao_documentos_legais');
                Route::post('contrato/uploadAnexos', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'uploadAnexos'])->name('upload-anexos')->middleware('can:administracao_documentos_legais');
                Route::get('contrato/anexo/{arquivo}', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'anexoShow'])->name('anexo-show')->middleware('can:administracao_documentos_legais');
                Route::get('contrato/anexoDownload/{arquivo}', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'download'])->name('anexo-download')->middleware('can:administracao_documentos_legais');
                Route::delete('contrato/anexo/{arquivo}', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'anexoDelete'])->name('anexo-delete')->middleware('can:administracao_documentos_legais');
                Route::get('contrato/buscar-cnpj', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'buscaCNPJ'])->name('verifica-cnpj')->middleware('can:administracao_documentos_legais');
                Route::get('contrato/buscar-cpf', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'buscaCPF'])->name('verifica-cpf')->middleware('can:administracao_documentos_legais');

                Route::get('contrato/{contrato}/pdf', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'getContratoPdf'])->name('getContratoPdf');
                Route::put('contrato/{contrato}/ativa-desativa', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:administracao_documentos_legais');
                Route::post('contrato/search', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'searchCliente'])->name('search')->middleware('can:administracao_documentos_legais');
                Route::post('contrato/atualizar', [\App\Http\Controllers\DocumentosLegaisContratoController::class, 'atualizar'])->name('atualizar')->middleware('can:administracao_documentos_legais');
                Route::resource('contrato', \App\Http\Controllers\DocumentosLegaisContratoController::class)->middleware('can:administracao_documentos_legais');

            });

            Route::group(['as' => 'empresa.'], function () {
                Route::post('empresa/uploadAnexos', [\App\Http\Controllers\DocumentosLegaisEmpresaController::class, 'uploadAnexos'])->name('upload-anexos')->middleware('can:administracao_documentos_legais');
                Route::get('empresa/anexo/{arquivo}', [\App\Http\Controllers\DocumentosLegaisEmpresaController::class, 'anexoShow'])->name('anexo-show')->middleware('can:administracao_documentos_legais');
                Route::get('empresa/anexoDownload/{arquivo}', [\App\Http\Controllers\DocumentosLegaisEmpresaController::class, 'download'])->name('anexo-download')->middleware('can:administracao_documentos_legais');
                Route::delete('empresa/anexo/{arquivo}', [\App\Http\Controllers\DocumentosLegaisEmpresaController::class, 'anexoDelete'])->name('anexo-delete')->middleware('can:administracao_documentos_legais');

                Route::get('empresa/{documento}/pdf', [\App\Http\Controllers\DocumentosLegaisEmpresaController::class, 'getDocumentoEmpresaPdf'])->name('getDocumentoEmpresaPdf');
                Route::put('empresa/{empresa}/ativa-desativa', [\App\Http\Controllers\DocumentosLegaisEmpresaController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:administracao_documentos_legais');
                Route::post('empresa/atualizar', [\App\Http\Controllers\DocumentosLegaisEmpresaController::class, 'atualizar'])->name('atualizar')->middleware('can:administracao_documentos_legais');
                Route::resource('empresa', \App\Http\Controllers\DocumentosLegaisEmpresaController::class)->middleware('can:administracao_documentos_legais');
            });

            Route::group(['as' => 'ssma.'], function () {
                Route::post('ssma/uploadAnexos', [\App\Http\Controllers\DocumentosLegaisSsmaController::class, 'uploadAnexos'])->name('upload-anexos')->middleware('can:administracao_documentos_legais');
                Route::get('ssma/anexo/{arquivo}', [\App\Http\Controllers\DocumentosLegaisSsmaController::class, 'anexoShow'])->name('anexo-show')->middleware('can:administracao_documentos_legais');
                Route::get('ssma/anexoDownload/{arquivo}', [\App\Http\Controllers\DocumentosLegaisSsmaController::class, 'download'])->name('anexo-download')->middleware('can:administracao_documentos_legais');
                Route::delete('ssma/anexo/{arquivo}', [\App\Http\Controllers\DocumentosLegaisSsmaController::class, 'anexoDelete'])->name('anexo-delete')->middleware('can:administracao_documentos_legais');

                Route::get('ssma/{documento}/pdf', [\App\Http\Controllers\DocumentosLegaisSsmaController::class, 'getDocumentoSsmaPdf'])->name('getDocumentoSsmaPdf');
                Route::put('ssma/{ssma}/ativa-desativa', [\App\Http\Controllers\DocumentosLegaisSsmaController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:administracao_documentos_legais');
                Route::post('ssma/search', [\App\Http\Controllers\DocumentosLegaisSsmaController::class, 'searchCliente'])->name('search')->middleware('can:administracao_documentos_legais');
                Route::post('ssma/atualizar', [\App\Http\Controllers\DocumentosLegaisSsmaController::class, 'atualizar'])->name('atualizar')->middleware('can:administracao_documentos_legais');
                Route::resource('ssma', \App\Http\Controllers\DocumentosLegaisSsmaController::class)->middleware('can:administracao_documentos_legais');
            });

            Route::group(['as' => 'tipodocumento.'], function () {
                Route::put('tipodocumento/{tipodocumento}/ativa-desativa', [\App\Http\Controllers\TipoDocumentoLegalController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:administracao_documentos_legais');
                Route::post('tipodocumento/atualizar', [\App\Http\Controllers\TipoDocumentoLegalController::class, 'atualizar'])->name('atualizar')->middleware('can:administracao_documentos_legais');
                Route::resource('tipodocumento', \App\Http\Controllers\TipoDocumentoLegalController::class)->middleware('can:administracao_documentos_legais');
            });

            Route::group(['as' => 'tiposervico.'], function () {
                Route::put('tiposervico/{tiposervico}/ativa-desativa', [\App\Http\Controllers\ServicoDocumentoLegalController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:administracao_documentos_legais');
                Route::post('tiposervico/atualizar', [\App\Http\Controllers\ServicoDocumentoLegalController::class, 'atualizar'])->name('atualizar')->middleware('can:administracao_documentos_legais');
                Route::resource('tiposervico', \App\Http\Controllers\ServicoDocumentoLegalController::class)->middleware('can:administracao_documentos_legais');
            });

            Route::group(['as' => 'formacontrato.'], function () {
                Route::put('formacontrato/{formacontrato}/ativa-desativa', [\App\Http\Controllers\FormaContratoDocumentoLegalController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:administracao_documentos_legais');
                Route::post('formacontrato/atualizar', [\App\Http\Controllers\FormaContratoDocumentoLegalController::class, 'atualizar'])->name('atualizar')->middleware('can:administracao_documentos_legais');
                Route::resource('formacontrato', \App\Http\Controllers\FormaContratoDocumentoLegalController::class)->middleware('can:administracao_documentos_legais');
            });

        });

        // Fornecedores
        Route::group(['as' => 'fornecedor.'], function () {
            // Anexos Fornecedor
            Route::post('fornecedor/uploadAnexos', [\App\Http\Controllers\FornecedorController::class, 'uploadAnexos'])->name('upload-anexos')->middleware('can:administracao_fornecedores');
            Route::get('fornecedor/anexo/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'anexoShow'])->name('anexo-show')->middleware('can:administracao_fornecedores');
            Route::get('fornecedor/anexoDownload/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'download'])->name('anexo-download')->middleware('can:administracao_fornecedores');
            Route::delete('fornecedor/anexo/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'anexoDelete'])->name('anexo-delete')->middleware('can:administracao_fornecedores');

            // Anexos Fornecedor
            Route::post('fornecedor/servico/uploadAnexos', [\App\Http\Controllers\FornecedorController::class, 'uploadServicoAnexos'])->name('upload-anexos-servico')->middleware('can:administracao_fornecedores');
            Route::get('fornecedor/servico/anexo/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'anexoServicoShow'])->name('anexo-servico-show')->middleware('can:administracao_fornecedores');
            Route::get('fornecedor/servico/anexoDownload/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'downloadServico'])->name('anexo-servico-download')->middleware('can:administracao_fornecedores');
            Route::delete('fornecedor/servico/anexo/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'anexoServicoDelete'])->name('anexo-servico-delete')->middleware('can:administracao_fornecedores');
            // Anexos Serviços

            //Comuns Fornecedores
            Route::get('fornecedor/buscar-cnpj', [\App\Http\Controllers\FornecedorController::class, 'buscaCNPJ'])->name('verifica-cnpj')->middleware('can:administracao_fornecedores');
            Route::get('fornecedor/buscar-cpf', [\App\Http\Controllers\FornecedorController::class, 'buscaCPF'])->name('verifica-cpf')->middleware('can:administracao_fornecedores');
            Route::put('fornecedor/{fornecedor}/ativa-desativa', [\App\Http\Controllers\FornecedorController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:administracao_fornecedores');
            Route::post('fornecedor/search', [\App\Http\Controllers\FornecedorController::class, 'searchCliente'])->name('search')->middleware('can:administracao_fornecedores');
            Route::post('fornecedor/atualizar', [\App\Http\Controllers\FornecedorController::class, 'atualizar'])->name('atualizar')->middleware('can:administracao_fornecedores');
            Route::resource('fornecedor', \App\Http\Controllers\FornecedorController::class)->middleware('can:administracao_fornecedores');
        });

        //Ata de Reunião
        Route::group(['as' => 'atareuniao.'], function () {
            Route::post('atareuniao/atualizar', [\App\Http\Controllers\AtaReuniaoController::class, 'atualizar'])->name('atualizarAtaReuniao')->middleware('can:administracao_atareuniao');
            Route::get('atareuniao/pdf/{item}', [\App\Http\Controllers\AtaReuniaoController::class, 'pdf'])->name('pdfAtaReuniao')->middleware('can:administracao_atareuniao');
            Route::resource('atareuniao', \App\Http\Controllers\AtaReuniaoController::class)->middleware('can:administracao_atareuniao');
        });

        //Pesquisa de Clima
        Route::group(['as' => 'pesquisaclima.'], function () {
            Route::get('pesquisaclima/atualizar', [\App\Http\Controllers\PesquisaClimaController::class, 'atualizar'])->name('atualizar')->middleware('can:administracao_pesquisaclima');
            Route::get('pesquisaclima/pdf/{item}', [\App\Http\Controllers\PesquisaClimaController::class, 'pdf'])->name('pdfPesquisaClima')->middleware('can:administracao_pesquisaclima');
            Route::get('pesquisaclima', [\App\Http\Controllers\PesquisaClimaController::class, 'indexAdm'])->name('indexAdm')->middleware('can:administracao_pesquisaclima');
            Route::get('pesquisaclima/chart/{cliente_id}', [\App\Http\Controllers\PesquisaClimaController::class, 'chart'])->name('chart')->middleware('can:administracao_pesquisaclima');
            Route::get('pesquisaclima/contador', [\App\Http\Controllers\PesquisaClimaController::class, 'contador'])->name('contador')->middleware('can:administracao_pesquisaclima');
        });

        //Planejamento Diario
        Route::group(['as' => 'planejamentodiario.'], function () {
            Route::post('planejamentodiario/atualizar', [\App\Http\Controllers\PlanejamentoDiarioController::class, 'atualizar'])->name('atualizarPlanejamentoDiario')->middleware('can:administracao_planejamentodiario');
            Route::resource('planejamentodiario', \App\Http\Controllers\PlanejamentoDiarioController::class)->middleware('can:administracao_planejamentodiario');
        });

        //Aniversariantes
        Route::group(['as' => 'aniversariantes.'], function () {
            Route::post('aniversariantes/atualizar', [\App\Http\Controllers\AniversariantesController::class, 'atualizar'])->name('atualizar')->middleware('can:administracao_aniversariantes');
            Route::post('aniversariantes/enviaEmail', [\App\Http\Controllers\AniversariantesController::class, 'enviaEmail'])->name('enviaEmail')->middleware('can:administracao_aniversariantes');
            Route::resource('aniversariantes', \App\Http\Controllers\AniversariantesController::class)->middleware('can:administracao_aniversariantes');
        });
    });

    //Cadastro
    Route::group(['prefix' => 'cadastro'], function () {
        Route::group(['as' => 'instrutor.'], function () {
            Route::post('instrutor/atualizar', [\App\Http\Controllers\InstrutorController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_instrutor');
            Route::post('instrutor/uploadAnexos', [\App\Http\Controllers\InstrutorController::class, 'uploadAnexos'])->name('instrutor.upload-anexos');
            Route::get('instrutor/anexo/{arquivo}', [\App\Http\Controllers\InstrutorController::class, 'anexoShow'])->name('instrutor.anexo-show');
            Route::get('instrutor/anexoDownload/{arquivo}', [\App\Http\Controllers\InstrutorController::class, 'download'])->name('instrutor.anexo-download');
            Route::delete('instrutor/anexo/{arquivo}', [\App\Http\Controllers\InstrutorController::class, 'anexoDelete'])->name('instrutor.anexo-delete');
            Route::resource('instrutor', \App\Http\Controllers\InstrutorController::class)->middleware('can:cadastro_instrutor');
        });

        Route::group(['as' => 'departamento.'], function () {
            Route::post('departamento/atualizar', [\App\Http\Controllers\DepartamentoController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_departamento');
            Route::resource('departamento', \App\Http\Controllers\DepartamentoController::class)->middleware('can:cadastro_departamento');
        });

        Route::group(['as' => 'treinamentoindustria.'], function () {
            Route::post('treinamentoindustria/atualizar', [\App\Http\Controllers\TreinamentoIndustriaController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_treinamento_industria');
            Route::put('treinamentoindustria/{treinamentoindustria}/ativa-desativa', [\App\Http\Controllers\TreinamentoIndustriaController::class, 'ativaDesativa'])->name('treinamento_industria.ativaDesativa')->middleware('can:cadastro_treinamento_industria');
            Route::resource('treinamentoindustria', \App\Http\Controllers\TreinamentoIndustriaController::class)->middleware('can:cadastro_treinamento_industria');
        });

        Route::group(['as' => 'treinamentosgi.'], function () {
            Route::post('treinamentosgi/atualizar', [\App\Http\Controllers\TreinamentoSgiController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_treinamento_sgi');
            Route::resource('treinamentosgi', \App\Http\Controllers\TreinamentoSgiController::class)->middleware('can:cadastro_treinamento_sgi');
        });

        Route::group(['as' => 'empresatreinamento.'], function () {
            Route::post('empresatreinamento/atualizar', [\App\Http\Controllers\EmpresaTreinamentoController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_empresa_treinamento');
            Route::put('empresatreinamento/{empresatreinamento}/ativa-desativa', [\App\Http\Controllers\EmpresaTreinamentoController::class, 'ativaDesativa'])->name('empresa_treinamento.ativaDesativa')->middleware('can:cadastro_empresa_treinamento');
            Route::resource('empresatreinamento', \App\Http\Controllers\EmpresaTreinamentoController::class)->middleware('can:cadastro_empresa_treinamento');
        });

        Route::group(['as' => 'empresaexame.'], function () {
            Route::post('empresa-exame/atualizar', [\App\Http\Controllers\EmpresaExameController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_empresa_exame');
            Route::put('empresa-exame/{id}/ativa-desativa', [\App\Http\Controllers\EmpresaExameController::class, 'ativaDesativa'])->name('empresa_exame.ativaDesativa')->middleware('can:cadastro_empresa_exame');
            Route::resource('empresa-exame', \App\Http\Controllers\EmpresaExameController::class)->middleware('can:cadastro_empresa_exame');
        });

        //PCMSO
        Route::group(['as' => 'pcmsos.'], function () {
            Route::put('pcmso/{pcmso}/ativa-desativa', [\App\Http\Controllers\PcmsoController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:cadastro_empresa_pcmso_update');
            Route::post('pcmso/atualizar', [\App\Http\Controllers\PcmsoController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_empresa_pcmso');
            Route::resource('pcmso', \App\Http\Controllers\PcmsoController::class)->middleware('can:cadastro_empresa_pcmso');
        });

        Route::group(['as' => 'assinaturacarteira.'], function () {
            Route::post('assinaturacarteira/uploadAnexos', [\App\Http\Controllers\CarteiraAssinaturaController::class, 'uploadAnexos'])->name('assinaturacarteira.upload-anexos')->middleware('can:cadastro_carteira_assinatura');
            Route::get('assinaturacarteira/anexo/{arquivo}', [\App\Http\Controllers\CarteiraAssinaturaController::class, 'anexoShow'])->name('assinaturacarteira.anexo-show')->middleware('can:cadastro_carteira_assinatura');;
            Route::get('assinaturacarteira/anexoDownload/{arquivo}', [\App\Http\Controllers\CarteiraAssinaturaController::class, 'download'])->name('assinaturacarteira.anexo-download')->middleware('can:cadastro_carteira_assinatura');;
            Route::delete('assinaturacarteira/anexo/{arquivo}', [\App\Http\Controllers\CarteiraAssinaturaController::class, 'anexoDelete'])->name('assinaturacarteira.anexo-delete')->middleware('can:cadastro_carteira_assinatura');;
            Route::post('assinaturacarteira/atualizar', [\App\Http\Controllers\CarteiraAssinaturaController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_carteira_assinatura');;
            Route::resource('assinaturacarteira', \App\Http\Controllers\CarteiraAssinaturaController::class)->middleware('can:cadastro_carteira_assinatura');;
        });

        Route::group(['as' => 'empresatemporaria.'], function () {
            Route::post('empresa-temporaria/atualizar', [\App\Http\Controllers\EmpresaTemporariaController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_empresa_temporaria');
            Route::put('empresa-temporaria/{id}/ativa-desativa', [\App\Http\Controllers\EmpresaTemporariaController::class, 'ativaDesativa'])->name('empresa_temporaria.ativaDesativa')->middleware('can:cadastro_empresa_temporaria');
            Route::resource('empresa-temporaria', \App\Http\Controllers\EmpresaTemporariaController::class)->middleware('can:cadastro_empresa_temporaria');
        });

        Route::group(['as' => 'provas.'], function () {
            Route::post('provas/atualizar', [\App\Http\Controllers\SimuladoController::class, 'atualizar'])->name('provas.atualizar');
            Route::put('provas/{prova}/ativa-desativa', [\App\Http\Controllers\SimuladoController::class, 'ativaDesativa'])->name('provas.ativaDesativa');
            Route::resource('provas', \App\Http\Controllers\SimuladoController::class);
        });

        Route::group(['as' => 'beneficios.'], function () {
            Route::put('beneficios/{beneficio}/ativa-desativa', [\App\Http\Controllers\BeneficioController::class, 'ativaDesativa'])->name('beneficios.ativaDesativa')->middleware('can:cadastro_beneficio');
            Route::get('beneficios/{tipobeneficio}/editarTipo', [\App\Http\Controllers\BeneficioController::class, 'editarTipo'])->name('beneficios.editarTipo')->middleware('can:cadastro_beneficio');
            Route::put('/beneficios/updateTipos/{tipobeneficio}', [\App\Http\Controllers\BeneficioController::class, 'updateTipo'])->name('beneficios.updateTipo')->middleware('can:cadastro_beneficio');
            Route::post('beneficios/atualizar', [\App\Http\Controllers\BeneficioController::class, 'atualizar'])->name('beneficios.atualizar')->middleware('can:cadastro_beneficio'); // manter essa rota antes do resource
            Route::post('beneficios/atualizar-historico', [\App\Http\Controllers\BeneficioController::class, 'atualizarHistorico'])->name('beneficios.atualizarHistorico')->middleware('can:cadastro_beneficio'); // manter essa rota antes do resource
            Route::post('beneficios/cadastro-tipo', [\App\Http\Controllers\BeneficioController::class, 'cadastroTipo'])->name('beneficios.cadastroTipo')->middleware('can:cadastro_beneficio'); // manter essa rota antes do resource
            Route::resource('beneficios', \App\Http\Controllers\BeneficioController::class)->middleware('can:cadastro_beneficio');
        });

        // Vagas
        Route::group(['as' => 'vagas.'], function () {
            Route::put('vagas/{vaga}/ativa-desativa', [\App\Http\Controllers\VagaController::class, 'ativaDesativa'])->name('vagas.ativaDesativa')->middleware('can:cadastro_vagas_update');
            Route::post('vagas/atualizar', [\App\Http\Controllers\VagaController::class, 'atualizar'])->name('vagas.atualizar')->middleware('can:cadastro_vagas');
            Route::resource('vagas', \App\Http\Controllers\VagaController::class)->middleware('can:cadastro_vagas');

            Route::put('vagas-abertas/{vagas_aberta}/ativa-desativa', [\App\Http\Controllers\VagasAbertasController::class, 'ativaDesativa'])->name('vagas_abertas.ativaDesativa')->middleware('can:cadastro_vagas_abertas_update');
            Route::post('vagas-abertas/atualizar', [\App\Http\Controllers\VagasAbertasController::class, 'atualizar'])->name('vagas_abertas.atualizar')->middleware('can:cadastro_vagas_abertas');
            Route::get('vagas-abertas/prova/{simulado}/{vaga_aberta}', [\App\Http\Controllers\VagasAbertasController::class, 'vagaAbertaSimulado'])->name('vagas_abertas.vagaAbertaSimulado')->middleware('can:cadastro_vagas_abertas');
            Route::resource('vagas-abertas', \App\Http\Controllers\VagasAbertasController::class)->middleware('can:cadastro_vagas_abertas');
        });

        // Projeto
        Route::group(['as' => 'projetos.'], function () {
            Route::post('projetos/atualizar', [\App\Http\Controllers\ProjetoController::class, 'atualizar'])->name('projetos.atualizar')->middleware('can:cadastro_projetos');
            Route::resource('projetos', \App\Http\Controllers\ProjetoController::class)->middleware('can:cadastro_projetos');
        });

        Route::group(['as' => 'areas.'], function () {
            Route::put('areas/{area}/ativa-desativa', [\App\Http\Controllers\AreaEtiquetasController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:cadastro_areaetiqueta');
            Route::post('areas/atualizar', [\App\Http\Controllers\AreaEtiquetasController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_areaetiqueta');
            Route::resource('areas', \App\Http\Controllers\AreaEtiquetasController::class)->middleware('can:cadastro_areaetiqueta');
        });

        Route::group(['as' => 'centrocusto.'], function () {
            Route::put('centrocusto/{centrocusto}/ativa-desativa', [\App\Http\Controllers\CentroCustoController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:cadastro_centrocusto');
            Route::post('centrocusto/atualizar', [\App\Http\Controllers\CentroCustoController::class, 'atualizar'])->name('atualizar')->middleware('can:cadastro_centrocusto');
            Route::resource('centrocusto', \App\Http\Controllers\CentroCustoController::class)->middleware('can:cadastro_centrocusto');
        });

        Route::group(['as' => 'tipocih.'], function () {
            Route::put('tipocih/{tipocih}/ativa-desativa', [\App\Http\Controllers\CihController::class, 'ativaDesativa'])->name('ativaDesativa');
            Route::post('tipocih/atualizar', [\App\Http\Controllers\CihController::class, 'tipoCihAtualizar'])->name('tipocih.tipoCihAtualizar');
            Route::get('tipocih/{tipocih}', [\App\Http\Controllers\CihController::class, 'tipoCihEdit'])->name('tipoCihEdit');
            Route::put('tipocih/{tipocih}', [\App\Http\Controllers\CihController::class, 'tipoCihUpdate'])->name('tipoCihUpdate');
            Route::post('tipocih', [\App\Http\Controllers\CihController::class, 'tipoCihStore'])->name('tipoCihStore');
            Route::get('tipocih', [\App\Http\Controllers\CihController::class, 'tipoCihIndex'])->name('tipoCihIndex');
        });

        Route::group(['prefix' => 'avaliacoes'], function () {

            Route::group(['as' => 'avaliadortipo.'], function () {
                Route::put('avaliadortipo/{avaliadortipo}/ativa-desativa', [\App\Http\Controllers\AvaliadorTipoController::class, 'ativaDesativa'])->name('AvaliadorTipoAtivaDesativa')->middleware('can:cadastro_avaliador_tipo');
                Route::post('avaliadortipo/atualizar', [\App\Http\Controllers\AvaliadorTipoController::class, 'atualizar'])->name('AvaliadorTipoAtualizar')->middleware('can:cadastro_avaliador_tipo');
                Route::resource('avaliadortipo', \App\Http\Controllers\AvaliadorTipoController::class)->middleware('can:cadastro_avaliador_tipo');
            });

            Route::group(['as' => 'avaliacaotipo.'], function () {
                Route::put('avaliacaotipo/{avaliacaotipo}/ativa-desativa', [\App\Http\Controllers\AvaliacaoTipoController::class, 'ativaDesativa'])->name('AvaliacaoTipoAtivaDesativa')->middleware('can:cadastro_avaliacao_tipo');
                Route::post('avaliacaotipo/atualizar', [\App\Http\Controllers\AvaliacaoTipoController::class, 'atualizar'])->name('AvaliacaoTipoAtualizar')->middleware('can:cadastro_avaliacao_tipo');
                Route::resource('avaliacaotipo', \App\Http\Controllers\AvaliacaoTipoController::class)->middleware('can:cadastro_avaliacao_tipo');
            });

            Route::group(['as' => 'avaliacaotopico.'], function () {
                Route::post('avaliacaotopico/atualizar', [\App\Http\Controllers\AvaliacaoTopicoController::class, 'atualizar'])->name('avaliacaotopico.atualizar')->middleware('can:cadastro_avaliacao_topico');
                Route::put('avaliacaotopico/{avaliacaotopico}/ativa-desativa', [\App\Http\Controllers\AvaliacaoTopicoController::class, 'ativaDesativa'])->name('avaliacaotopico.ativaDesativa')->middleware('can:cadastro_avaliacao_topico');
                Route::resource('avaliacaotopico', \App\Http\Controllers\AvaliacaoTopicoController::class)->middleware('can:cadastro_avaliacao_topico');
            });

            Route::group(['as' => 'avaliacao.'], function () {
                Route::post('avaliacao/atualizar', [\App\Http\Controllers\AvaliacaoController::class, 'atualizar'])->name('avaliacao.atualizar')->middleware('can:cadastro_avaliacao');
                Route::put('avaliacao/{avaliacao}/ativa-desativa', [\App\Http\Controllers\AvaliacaoController::class, 'ativaDesativa'])->name('avaliacao.ativaDesativa')->middleware('can:cadastro_avaliacao');
                Route::resource('avaliacao', \App\Http\Controllers\AvaliacaoController::class)->middleware('can:cadastro_avaliacao');
            });

            //Configuracoes
            Route::group(['as' => 'avaliadores.'], function () {
                Route::post('avaliadores/atualizar', [\App\Http\Controllers\AvaliadorController::class, 'atualizarFuncionarios'])->name('atualizarFuncionarios')->middleware('can:cadastro_avaliacao_vincular_avaliadores');
                Route::post('avaliadores/avaliador-associado', [\App\Http\Controllers\AvaliadorController::class, 'AvaliadorAssociadoSingle'])->name('AvaliadorAssociadoSingle')->middleware('can:cadastro_avaliacao_vincular_avaliadores');
                Route::post('avaliadores/associar', [\App\Http\Controllers\AvaliadorController::class, 'associar'])->name('associar')->middleware('can:cadastro_avaliacao_vincular_avaliadores');
                Route::resource('avaliadores', \App\Http\Controllers\AvaliadorController::class, ['parameters' => ['avaliadores' => 'avaliador']])->middleware('can:cadastro_avaliacao_vincular_avaliadores');
            });

            Route::group(['as' => 'avaliar.'], function () {
                Route::post('avaliar/atualizar', [\App\Http\Controllers\AvaliacaoController::class, 'atualizarAvaliar'])->name('avaliarAtualizar')->middleware('can:avaliacoes_listar');
                Route::get('avaliar/{avaliacaoFeedback}/edit', [\App\Http\Controllers\AvaliacaoController::class, 'avaliarEdit'])->name('avaliarEdit')->middleware('can:avaliacoes_listar');
                Route::put('avaliar/{avaliacaoFeedback}', [\App\Http\Controllers\AvaliacaoController::class, 'avaliarUpdate'])->name('avaliarUpdate')->middleware('can:avaliacoes_listar');
                Route::get('avaliar/{avaliacaoFeedback}/final', [\App\Http\Controllers\AvaliacaoController::class, 'avaliarFinal'])->name('avaliarFinal')->middleware('can:avaliacoes_listar');
                Route::get('avaliar/impressao/{token}', [\App\Http\Controllers\AvaliacaoController::class, 'imprimir'])->name('avaliarImprimir')->middleware('can:avaliacoes_listar');
                Route::put('avaliar/{avaliacaoFeedback}/final', [\App\Http\Controllers\AvaliacaoController::class, 'salvaAvaliacao'])->name('salvarAvaliacao')->middleware('can:avaliacoes_listar');
                Route::get('avaliar', [\App\Http\Controllers\AvaliacaoController::class, 'avaliarIndex'])->name('avaliarIndex')->middleware('can:avaliacoes_listar');
            });
        });
    });

    //Planejamento
    Route::group(['prefix' => 'planejamento'], function () {
        Route::group(['as' => 'requisicao_vagas.'], function () {
            Route::put('requisicao-vaga/{requisicaoVaga}/aprovar', [\App\Http\Controllers\RequisicaoVagaController::class, 'aprovar'])->name('aprovar')->middleware('can:planejamento_requisicao_vaga');
            Route::post('requisicao-vaga/atualizar', [\App\Http\Controllers\RequisicaoVagaController::class, 'atualizar'])->name('atualizar')->middleware('can:planejamento_requisicao_vaga');
            Route::post('requisicao-vaga/export', [\App\Http\Controllers\RequisicaoVagaController::class, 'export'])->name('requisicao-vaga.excel')->middleware('can:planejamento_requisicao_vaga');
            Route::resource('requisicao-vaga', \App\Http\Controllers\RequisicaoVagaController::class)->middleware('can:planejamento_requisicao_vaga');
        });

        Route::group(['as' => 'movimentacao.', 'prefix' => 'movimentacao'], function () {

            Route::get('anexo/{arquivo}', [\App\Http\Controllers\PlanejamentoMovimentacaoController::class, 'anexoShow'])->name('anexo-show');
            Route::get('anexoDownload/{arquivo}', [\App\Http\Controllers\PlanejamentoMovimentacaoController::class, 'download'])->name('anexo-download');
            Route::delete('anexo/{arquivo}', [\App\Http\Controllers\PlanejamentoMovimentacaoController::class, 'anexoDelete'])->name('anexo-delete');
            Route::post('uploadAnexos', [\App\Http\Controllers\PlanejamentoMovimentacaoController::class, 'uploadAnexos'])->name('.upload-anexos');

            Route::group(['as' => 'solicitacao_demissao.'], function () {
                Route::post('demissao-prevista/atualizacao-status', [\App\Http\Controllers\DemissaoPrevistaController::class, 'atualizacaoStatus'])->name('demissao-prevista.atualizacaoStatus');
                Route::get('demissao-prevista/{demissaoPrevista}/pdf', [\App\Http\Controllers\DemissaoPrevistaController::class, 'pdf'])->name('pdf');
                Route::post('demissao-prevista/atualizar', [\App\Http\Controllers\DemissaoPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::put('demissao-prevista/{demissaoPrevista}/aprovar', [\App\Http\Controllers\DemissaoPrevistaController::class, 'aprovar'])->name('aprovar');
                Route::put('demissao-prevista/{demissaoPrevista}/aprovarrh', [\App\Http\Controllers\DemissaoPrevistaController::class, 'aprovarRH'])->name('aprovarRH');
                Route::post('demissao-prevista/export', [\App\Http\Controllers\DemissaoPrevistaController::class, 'export'])->name('demissao-prevista.excel');
                Route::resource('demissao-prevista', \App\Http\Controllers\DemissaoPrevistaController::class, ['parameters' => ['demissao-prevista' => 'demissao_prevista']]);
            });

            Route::group(['as' => 'solicitacao_ferias.'], function () {
                Route::put('ferias-prevista/{ferias}/aprovargestor', [\App\Http\Controllers\FeriasPrevistaController::class, 'aprovarGestor'])->name('aprovarGestor');
                Route::post('ferias-prevista/export', [\App\Http\Controllers\FeriasPrevistaController::class, 'export'])->name('ferias-prevista.excel');
                Route::post('ferias-prevista/atualizacao-status', [\App\Http\Controllers\FeriasPrevistaController::class, 'atualizacaoStatus'])->name('ferias-prevista.atualizacaoStatus');
                Route::post('ferias-prevista/atualizar', [\App\Http\Controllers\FeriasPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::put('ferias-prevista/{ferias}/aprovarrh', [\App\Http\Controllers\FeriasPrevistaController::class, 'aprovarRH'])->name('aprovarRH');
                Route::delete('ferias-prevista/{ferias}', [\App\Http\Controllers\FeriasPrevistaController::class, 'destroy'])->name('ferias-prevista-delete');
                Route::resource('ferias-prevista', \App\Http\Controllers\FeriasPrevistaController::class, ['parameters' => ['ferias-prevista' => 'ferias']]);
            });

            Route::group(['as' => 'solicitacao_admissoes.'], function () {
                Route::post('admissoes-prevista/atualizacao-status', [\App\Http\Controllers\AdmissoesPrevistaController::class, 'atualizacaoStatus'])->name('admissoes-prevista.atualizacaoStatus');
                Route::post('admissoes-prevista/atualizar', [\App\Http\Controllers\AdmissoesPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::put('admissoes-prevista/{admissoesPrevista}/aprovar', [\App\Http\Controllers\AdmissoesPrevistaController::class, 'aprovar'])->name('aprovar');
                Route::put('admissoes-prevista/{admissoesPrevista}/aprovarrh', [\App\Http\Controllers\AdmissoesPrevistaController::class, 'aprovarRH'])->name('aprovarRH');
                Route::post('admissoes-prevista/export', [\App\Http\Controllers\AdmissoesPrevistaController::class, 'export'])->name('admissoes-prevista.excel');
                Route::resource('admissoes-prevista', \App\Http\Controllers\AdmissoesPrevistaController::class, ['parameters' => ['admissoes-prevista' => 'admissoes_prevista']]);
            });

            Route::group(['as' => 'solicitacao_valor-extra.'], function () {
                Route::post('valor-extra-prevista/atualizacao-status', [\App\Http\Controllers\ValorExtraPrevistaController::class, 'atualizacaoStatus'])->name('valor-extra-prevista.atualizacaoStatus');
                Route::post('valor-extra-prevista/atualizar', [\App\Http\Controllers\ValorExtraPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::put('valor-extra-prevista/{valorExtraPrevista}/aprovar', [\App\Http\Controllers\ValorExtraPrevistaController::class, 'aprovar'])->name('aprovar');
                Route::put('valor-extra-prevista/{valorExtraPrevista}/aprovarrh', [\App\Http\Controllers\ValorExtraPrevistaController::class, 'aprovarRH'])->name('aprovarRH');
                Route::post('valor-extra-prevista/export', [\App\Http\Controllers\ValorExtraPrevistaController::class, 'export'])->name('valor-extra-prevista.excel');
                Route::resource('valor-extra-prevista', \App\Http\Controllers\ValorExtraPrevistaController::class, ['parameters' => ['valor-extra-prevista' => 'valor_extra_prevista']]);
            });

            Route::group(['as' => 'solicitacao_cargo.'], function () {
                Route::put('mudanca-cargo/{solicitacao}/aprovargestor', [\App\Http\Controllers\MudancaCargoController::class, 'aprovarGestor'])->name('mudanca-cargo.aprovarGestor');
                Route::post('mudanca-cargo/export', [\App\Http\Controllers\MudancaCargoController::class, 'export'])->name('mudanca-cargo.excel');
                Route::post('mudanca-cargo/atualizar', [\App\Http\Controllers\MudancaCargoController::class, 'atualizar'])->name('mudanca-cargo.atualizar');
                Route::put('mudanca-cargo/{solicitacao}/aprovarrh', [\App\Http\Controllers\MudancaCargoController::class, 'aprovarRH'])->name('mudanca-cargo.aprovarRH');
                Route::resource('mudanca-cargo', \App\Http\Controllers\MudancaCargoController::class, ['parameters' => ['ferias-prevista' => 'ferias']]);
            });

            Route::group(['as' => 'solicitacao_intermitente.'], function () {
                Route::post('intermitente-fixo-prevista/atualizacao-status', [\App\Http\Controllers\IntermitenteFixoPrevistaController::class, 'atualizacaoStatus'])->name('intermitente-fixo-prevista.atualizacaoStatus');
                Route::post('intermitente-fixo-prevista/atualizar', [\App\Http\Controllers\IntermitenteFixoPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::put('intermitente-fixo-prevista/{intermitenteFixoPrevista}/aprovar', [\App\Http\Controllers\IntermitenteFixoPrevistaController::class, 'aprovar'])->name('aprovar');
                Route::put('intermitente-fixo-prevista/{intermitenteFixoPrevista}/aprovarrh', [\App\Http\Controllers\IntermitenteFixoPrevistaController::class, 'aprovarRh'])->name('mudanca-cargo.aprovarRH');
                Route::post('intermitente-fixo-prevista/export', [\App\Http\Controllers\IntermitenteFixoPrevistaController::class, 'export'])->name('intermitente-fixo-prevista.excel');
                Route::resource('intermitente-fixo-prevista', \App\Http\Controllers\IntermitenteFixoPrevistaController::class, ['parameters' => ['intermitente-fixo-prevista' => 'intermitente_fixo_prevista']]);
            });

            Route::group(['as' => 'solicitacao_transferencia.'], function () {
                Route::post('transferencia-prevista/atualizacao-status', [\App\Http\Controllers\TransferenciaPrevistaController::class, 'atualizacaoStatus'])->name('transferencia-prevista.atualizacaoStatus');
                Route::post('transferencia-prevista/atualizar', [\App\Http\Controllers\TransferenciaPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::put('transferencia-prevista/{transferenciaPrevista}/aprovar', [\App\Http\Controllers\TransferenciaPrevistaController::class, 'aprovar'])->name('aprovar');
                Route::post('transferencia-prevista/export', [\App\Http\Controllers\TransferenciaPrevistaController::class, 'export'])->name('transferencia-prevista.excel');
                Route::resource('transferencia-prevista', \App\Http\Controllers\TransferenciaPrevistaController::class, ['parameters' => ['transferencia-prevista' => 'transferencia_prevista']]);
            });

            //Rota raiz
            Route::get('/', [\App\Http\Controllers\MovimentacaoController::class, 'index'])->name('index');
            Route::get('/lista-abas', [\App\Http\Controllers\MovimentacaoController::class, 'listarAbas'])->name('listarAbas');
        });

        Route::group(['as' => 'mobilizacao.'], function () {
            Route::post('mobilizacao/atualizar', [\App\Http\Controllers\MobilizacaoController::class, 'atualizar'])->name('atualizar')->middleware('can:planejamento_mobilizacao');
            Route::post('mobilizacao/export-excel', [\App\Http\Controllers\MobilizacaoController::class, 'exportExcel'])->name('exportExcel')->middleware('can:planejamento_mobilizacao');
            Route::get('mobilizacao/pdf/{projeto}', [\App\Http\Controllers\MobilizacaoController::class, 'geraPdf'])->name('geraPdf')->middleware('can:planejamento_mobilizacao');
            Route::get('mobilizacao/get-projetos', [\App\Http\Controllers\MobilizacaoController::class, 'getProjetos'])->name('getProjetos')->middleware('can:planejamento_mobilizacao');
            Route::get('mobilizacao/seleciona-projeto/{projeto}', [\App\Http\Controllers\MobilizacaoController::class, 'selecionaProjeto'])->name('seleciona-projeto')->middleware('can:planejamento_mobilizacao');
            Route::get('mobilizacao', [\App\Http\Controllers\MobilizacaoController::class, 'index'])->name('index')->middleware('can:planejamento_mobilizacao');
        });
    });

    //Curriculos
    Route::group(['prefix' => 'curriculos'], function () {
        // Recrutamento
        Route::group(['as' => 'recrutamento.'], function () {
            //Recrutamento colocar depois Middleware
            Route::post('recrutamentos/export', [\App\Http\Controllers\RecrutamentoController::class, 'export'])->name('recrutamentos.excel')->middleware('can:curriculos_recrutamento');
            //Lido
            Route::put('recrutamentos/{curriculo}/lido', [\App\Http\Controllers\RecrutamentoController::class, 'marcaLido'])->name('recrutamentos.marcaLido')->middleware('can:curriculos_recrutamento');
            Route::post('recrutamentos/search', [\App\Http\Controllers\RecrutamentoController::class, 'searchCliente'])->name('recrutamentos.search')->middleware('can:curriculos_recrutamento');
            Route::post('recrutamentos/atualizar', [\App\Http\Controllers\RecrutamentoController::class, 'atualizar'])->name('recrutamentos.atualizar')->middleware('can:curriculos_recrutamento');
            Route::resource('recrutamentos', \App\Http\Controllers\RecrutamentoController::class)->middleware('can:curriculos_recrutamento');
        });

        // Curriculo Seleção
        Route::group(['as' => 'curriculoselecao.'], function () {
            Route::post('curriculos-selecionados/atualizar', [\App\Http\Controllers\CurriculosSelecionadosController::class, 'atualizar'])->name('curriculos-selecionados.atualizar');
            Route::get('curriculos-selecionados/{feedback}/selecionado', [\App\Http\Controllers\CurriculosSelecionadosController::class, 'getCurriculo'])->name('curriculos-selecionados.getCurriculo');
//        Route::put('curriculos-selecionados/{curriculo}/desclassificar', [\App\Http\Controllers\CurriculosSelecionadosController::class,'desclassificar'])->name('curriculos-selecionados.desclassificar');
            Route::put('modificaStatus', [\App\Http\Controllers\CurriculosSelecionadosController::class, 'modificaStatus'])->name('curriculos-selecionados.modificaStatus');
            Route::get('curriculos-selecionados', [\App\Http\Controllers\CurriculosSelecionadosController::class, 'index'])->name('curriculos-selecionados.index');
        });
    });

    // Entrevistas
    Route::group(['as' => 'entrevista.', 'prefix' => 'entrevistas'], function () {
        //Parecer de RH
        Route::group(['as' => 'parecer_rh.'], function () {
            Route::post('parecer_rh/export', [\App\Http\Controllers\ParecerRhController::class, 'export'])->name('excel')->middleware('can:entrevista_parecer_rh');
            Route::post('parecer_rh/atualizar', [\App\Http\Controllers\ParecerRhController::class, 'atualizar'])->name('atualizar')->middleware('can:entrevista_parecer_rh');
            Route::post('parecer_rh/ficha_pdf', [\App\Http\Controllers\ParecerRhController::class, 'getFichaPdf'])->name('getFichaPdf')->middleware('can:entrevista_parecer_rh');
            Route::resource('parecer_rh', \App\Http\Controllers\ParecerRhController::class)->middleware('can:entrevista_parecer_rh');
        });

        //Parecer Rota Transporte
        Route::group(['as' => 'parecer_rota_transporte.'], function () {
            Route::post('parecer-rota/export', [\App\Http\Controllers\ParecerRotaController::class, 'export'])->name('parecer_rota_transporte.excel')->middleware('can:entrevista_parecer_rota');
            Route::post('parecer-rota/atualizar', [\App\Http\Controllers\ParecerRotaController::class, 'atualizar'])->name('atualizar')->middleware('can:entrevista_parecer_rota');
            Route::post('parecer-rota/ficha_pdf', [\App\Http\Controllers\ParecerRotaController::class, 'getFichaPdf'])->name('parecer_rota_transporte.getFichaPdf')->middleware('can:entrevista_parecer_rota');
            Route::resource('parecer-rota', \App\Http\Controllers\ParecerRotaController::class, ['parameters' => ['parecer-rota' => 'parecer_rota']])->middleware('can:entrevista_parecer_rota');
        });

        //Parecer Entrevista Técnica
        Route::group(['as' => 'parecer_entrevista_tecnica.'], function () {
            Route::post('parecer-entrevista-tecnica/export', [\App\Http\Controllers\ParecerEntrevistaTecnicaController::class, 'export'])->name('parecer_entrevista_tecnica.excel')->middleware('can:entrevista_parecer_entrevista');
            Route::post('parecer-entrevista-tecnica/atualizar', [\App\Http\Controllers\ParecerEntrevistaTecnicaController::class, 'atualizar'])->name('atualizar')->middleware('can:entrevista_parecer_entrevista');
            Route::post('parecer-entrevista-tecnica/ficha_pdf', [\App\Http\Controllers\ParecerEntrevistaTecnicaController::class, 'getFichaPdf'])->name('parecer_entrevista_tecnica.getFichaPdf')->middleware('can:entrevista_parecer_entrevista');
            Route::resource('parecer-entrevista-tecnica', \App\Http\Controllers\ParecerEntrevistaTecnicaController::class, ['parameters' => ['parecer-entrevista-tecnica' => 'entrevista_tecnica']])->middleware('can:entrevista_parecer_entrevista');
        });

        //Parecer Entrevista Técnica
        Route::group(['as' => 'parecer_teste_pratico.'], function () {
            Route::post('parecer-teste-pratico/export', [\App\Http\Controllers\ParecerTestePraticoController::class, 'export'])->name('parecer_teste_pratico.excel')->middleware('can:entrevista_parecer_teste_pratico');
            Route::post('parecer-teste-pratico/atualizar', [\App\Http\Controllers\ParecerTestePraticoController::class, 'atualizar'])->name('atualizar')->middleware('can:entrevista_parecer_teste_pratico');
            Route::post('parecer-teste-pratico/ficha_pdf', [\App\Http\Controllers\ParecerTestePraticoController::class, 'getFichaPdf'])->name('parecer_teste_pratico.getFichaPdf')->middleware('can:entrevista_parecer_teste_pratico');
            Route::resource('parecer-teste-pratico', \App\Http\Controllers\ParecerTestePraticoController::class)->middleware('can:entrevista_parecer_teste_pratico');
        });

        //Entrevista Rh Cliente
        Route::group(['as' => 'entrevista_rh_cliente.'], function () {
            Route::get('entrevista-rh/export', [\App\Http\Controllers\EntrevistaRhClienteController::class, 'export'])->name('excel')->middleware('can:entrevista_rh_cliente');
            Route::get('entrevista-rh/curriculo/{recrutamento}', [\App\Http\Controllers\RecrutamentoController::class, 'show'])->name('entrevista_rh_cliente.curriculo')->middleware('can:entrevista_rh_cliente');
            Route::post('entrevista-rh/atualizar', [\App\Http\Controllers\EntrevistaRhClienteController::class, 'atualizar'])->name('atualizar')->middleware('can:entrevista_rh_cliente');
            Route::resource('entrevista-rh', \App\Http\Controllers\EntrevistaRhClienteController::class, ['parameters' => ['entrevista-rh' => 'parecer_rh']])->middleware('can:entrevista_rh_cliente');
        });

        //Entrevista GESTOR
        Route::group(['as' => 'entrevista_gestor_cliente.'], function () {
            Route::get('entrevista-gestor/export', [\App\Http\Controllers\EntrevistaGestorController::class, 'export'])->name('excel')->middleware('can:entrevista_gestor_cliente');
            Route::get('entrevista-gestor/curriculo/{recrutamento}', [\App\Http\Controllers\RecrutamentoController::class, 'show'])->name('entrevista_gestor_cliente.curriculo')->middleware('can:entrevista_gestor_cliente');
            Route::post('entrevista-gestor/atualizar', [\App\Http\Controllers\EntrevistaGestorController::class, 'atualizar'])->name('atualizar')->middleware('can:entrevista_gestor_cliente');
            Route::resource('entrevista-gestor', \App\Http\Controllers\EntrevistaGestorController::class, ['parameters' => ['entrevista-rh' => 'parecer_rh']])->middleware('can:entrevista_gestor_cliente');
        });

        //Entrevista Resultado Integrado
        Route::group(['as' => 'resultado-integrado.'], function () {
            Route::get('resultado-integrado/ficha/{feedback}', [\App\Http\Controllers\ResultadoIntegradoController::class, 'getFichaPdf'])->name('resultado_integrado.getFichapdf')->middleware('can:entrevista_resultado_integrado');

            Route::post('resultado-integrado/export', [\App\Http\Controllers\ResultadoIntegradoController::class, 'export'])->name('resultado_integrado.excel')->middleware('can:entrevista_resultado_integrado');
            Route::post('resultado-integrado/atualizar', [\App\Http\Controllers\ResultadoIntegradoController::class, 'atualizar'])->name('resultado_integrado.atualizar')->middleware('can:entrevista_resultado_integrado');
//            Route::get('resultado-integrado/ficha/{resultado}', [\App\Http\Controllers\ResultadoIntegradoController::class, 'getFichaPdf'])->name('resultado_integrado.getFichapdf')->middleware('can:entrevista_resultado_integrado');
            Route::resource('resultado-integrado', \App\Http\Controllers\ResultadoIntegradoController::class)->middleware('can:entrevista_resultado_integrado');
        });

    });

    //Controle de Exames
    Route::group(['as' => 'controle_exames.'], function () {
        Route::get('controle-exames-resultado/anexo/{arquivo}', [\App\Http\Controllers\ControleExameController::class, 'anexoShow'])->name('anexo-show');
        Route::get('controle-exames-resultado/anexoDownload/{arquivo}', [\App\Http\Controllers\ControleExameController::class, 'download'])->name('anexo-download');
        Route::delete('controle-exames-resultado/anexo/{arquivo}', [\App\Http\Controllers\ControleExameController::class, 'anexoDelete'])->name('anexo-delete');
        Route::post('controle-exames-resultado/uploadAnexos', [\App\Http\Controllers\ControleExameController::class, 'uploadAnexos'])->name('.upload-anexos');

        Route::get('controle-exames/ficha-encaminhamento/{exame}/{token}', [\App\Http\Controllers\ControleExameController::class, 'getFichaPdf'])->name('pdf');
        Route::get('controle-exames/carregaResposta', [\App\Http\Controllers\ControleExameController::class, 'carregaResposta'])->name('carregaResposta');
        Route::get('controle-exames/resultado/{exame}', [\App\Http\Controllers\ControleExameController::class, 'getResultado'])->name('getResultado');
        Route::post('controle-exames/salvaResultado', [\App\Http\Controllers\ControleExameController::class, 'salvaResultado'])->name('salvaResultado');
        Route::put('controle-exames/salvaResultado/{resultado}', [\App\Http\Controllers\ControleExameController::class, 'updateResultado'])->name('updateResultado');
        Route::match(['post', 'put'], 'controle-exames/salvaUpdate', [\App\Http\Controllers\ControleExameController::class, 'salvaUpdate'])->name('salvaUpdate');
        Route::post('controle-exames/atualizar', [\App\Http\Controllers\ControleExameController::class, 'atualizar'])->name('atualizar');
        Route::get('controle-exames', [\App\Http\Controllers\ControleExameController::class, 'index'])->name('index');
    });

    //Menu Admissão
    Route::group(['as' => 'admissao.'], function () {
        Route::group(['as' => 'cih.', 'prefix' => 'apontamento'], function () {
            //anexo
            Route::post('cih/uploadAnexos', [\App\Http\Controllers\CihController::class, 'uploadAnexos'])->name('cih.upload-anexos');
            Route::get('cih/anexo/{arquivo}', [\App\Http\Controllers\CihController::class, 'anexoShow'])->name('cih.anexo-show');
            Route::get('cih/anexoDownload/{arquivo}', [\App\Http\Controllers\CihController::class, 'download'])->name('cih.anexo-download');
            Route::delete('cih/anexo/{arquivo}', [\App\Http\Controllers\CihController::class, 'anexoDelete'])->name('cih.anexo-delete');

            Route::post('cih/gerapdf', [\App\Http\Controllers\CihController::class, 'relatorioPdf'])->name('relatorioPdf'); // manter essa rota antes do resource
            Route::post('cih/export', [\App\Http\Controllers\CihController::class, 'export'])->name('export'); // manter essa rota antes do resource
            Route::post('cih/atualizar', [\App\Http\Controllers\CihController::class, 'atualizar'])->name('atualizar'); // manter essa rota antes do resource
            Route::put('cih/aprovar/{cih}', [\App\Http\Controllers\CihController::class, 'aprovar'])->name('aprovar'); // manter essa rota antes do resource
            Route::resource('cih', \App\Http\Controllers\CihController::class)->middleware('can:admissao_cih');
        });
        Route::group(['as' => 'intermitente.', 'prefix' => 'apontamento'], function () {
            //anexo
            Route::post('intermitente/uploadAnexos', [\App\Http\Controllers\IntermitenteController::class, 'uploadAnexos'])->name('intermitente.upload-anexos');
            Route::get('intermitente/anexo/{arquivo}', [\App\Http\Controllers\IntermitenteController::class, 'anexoShow'])->name('intermitente.anexo-show');
            Route::get('intermitente/anexoDownload/{arquivo}', [\App\Http\Controllers\IntermitenteController::class, 'download'])->name('intermitente.anexo-download');
            Route::delete('intermitente/anexo/{arquivo}', [\App\Http\Controllers\IntermitenteController::class, 'anexoDelete'])->name('intermitente.anexo-delete');

            Route::post('intermitente/prorrogacao', [\App\Http\Controllers\IntermitenteController::class, 'storeProrrogacao'])->name('storeProrrogacao'); // manter essa rota antes do resource
            Route::put('intermitente/encerrar-convocacao/{id}', [\App\Http\Controllers\IntermitenteController::class, 'encerrarConvocacao'])->name('storeEncerrarConvocacao'); // manter essa rota antes do resource
            Route::get('intermitente/prorrogacao/{id}/editar', [\App\Http\Controllers\IntermitenteController::class, 'editProrrogacao'])->name('editProrrogacao'); // manter essa rota antes do resource
            Route::get('intermitente/{id}/treinamentos', [\App\Http\Controllers\IntermitenteController::class, 'treinamentosColaborador'])->name('treinamentosColaborador'); // manter essa rota antes do resource
            Route::post('intermitente/gerapdf', [\App\Http\Controllers\IntermitenteController::class, 'relatorioPdf'])->name('relatorioPdf'); // manter essa rota antes do resource
            Route::post('intermitente/export', [\App\Http\Controllers\IntermitenteController::class, 'export'])->name('export'); // manter essa rota antes do resource
            Route::post('intermitente/atualizar', [\App\Http\Controllers\IntermitenteController::class, 'atualizar'])->name('atualizar'); // manter essa rota antes do resource
            Route::post('intermitente/storeTipo', [\App\Http\Controllers\IntermitenteController::class, 'storeTipo'])->name('storeTipo'); // manter essa rota antes do resource
            Route::put('intermitente/tipo/ativa-desativa/{tipo}', [\App\Http\Controllers\IntermitenteController::class, 'ativaDesativa'])->name('ativaDesativa');
            Route::get('intermitente/tipo/editar/{tipo}', [\App\Http\Controllers\IntermitenteController::class, 'editarTipo'])->name('editarTipo');
            Route::put('intermitente/aprovar/{intermitente}', [\App\Http\Controllers\IntermitenteController::class, 'aprovar'])->name('aprovar'); // manter essa rota antes do resource
            Route::resource('intermitente', \App\Http\Controllers\IntermitenteController::class)->middleware('can:admissao_intermitente');
        });

        Route::group(['as' => 'preadm.', 'prefix' => 'preadmissao'], function () {
            Route::post('atualizar', [\App\Http\Controllers\PreAdmissaoController::class, 'atualizar'])->name('atualizar'); // manter essa rota antes do resource
            Route::get('/{feedback}', [\App\Http\Controllers\PreAdmissaoController::class, 'show'])->name('show');
            Route::get('finalizar/{feedback}', [\App\Http\Controllers\PreAdmissaoController::class, 'showFinalizar'])->name('showFinalizar');
            Route::get('/editar/{feedback}', [\App\Http\Controllers\PreAdmissaoController::class, 'edit'])->name('edit');
            Route::post('/enviar-email', [\App\Http\Controllers\PreAdmissaoController::class, 'enviarEmail'])->name('enviarEmail');
            Route::post('/finalizar-encaminhar', [\App\Http\Controllers\PreAdmissaoController::class, 'finalizarEncaminhar'])->name('finalizarEncaminhar');
            Route::get('/', [\App\Http\Controllers\PreAdmissaoController::class, 'index'])->name('index');
        });

        Route::group(['prefix' => 'admissao'], function () {
            Route::post('/export', [\App\Http\Controllers\AdmissaoController::class, 'export'])->name('admissao.excel');
            Route::post('/cadastra-massa', [\App\Http\Controllers\AdmissaoController::class, 'cadastraMassa'])->name('admissao.cadastraMassa');
            Route::post('/busca-cpf', [\App\Http\Controllers\AdmissaoController::class, 'buscaCPF'])->name('admissao.buscaCPF');
            Route::get('/{fc_token}/pdf', [\App\Http\Controllers\AdmissaoController::class, 'getFichaPdf'])->name('admissao.getFichapdf');
            // Anexos
            Route::post('/uploadAnexos', [\App\Http\Controllers\AdmissaoController::class, 'uploadAnexos'])->name('admissao.upload-anexos');
            Route::get('/anexo/{arquivo}', [\App\Http\Controllers\AdmissaoController::class, 'anexoShow'])->name('admissao.anexo-show');
            Route::get('/anexoDownload/{arquivo}', [\App\Http\Controllers\AdmissaoController::class, 'download'])->name('admissao.anexo-download');
            Route::delete('/anexo/{arquivo}', [\App\Http\Controllers\AdmissaoController::class, 'anexoDelete'])->name('admissao.anexo-delete');

            Route::get('/listSelects', [\App\Http\Controllers\AdmissaoController::class, 'listSelects'])->name('admissao.listSelects')->middleware('can:admissao_processo'); // manter essa rota antes do resource
            Route::get('/tipos_dependentes', [\App\Http\Controllers\AdmissaoController::class, 'getTiposDependentes'])->name('admissao.tiposDependentes'); // get tipos de dependentes
            Route::post('/atualizar', [\App\Http\Controllers\AdmissaoController::class, 'atualizar'])->name('admissao.atualizar')->middleware('can:admissao_processo'); // manter essa rota antes do resource
            Route::get('/script', [\App\Http\Controllers\AdmissaoController::class, 'script'])->name('admissao.script')->middleware('can:admissao_processo'); // manter essa rota antes do resource

            Route::get('/import', [\App\Http\Controllers\AdmissaoController::class, 'import'])->name('admissao.import');

            Route::get('/colunas-tabela-processo', [\App\Http\Controllers\AdmissaoController::class, 'getColunasTabela'])->name('admissao.colunas-tabela-processo');
            Route::put('/colunas-tabela-processo', [\App\Http\Controllers\AdmissaoController::class, 'atualizarColunasTabela'])->name('admissao.atualizarColunasTabela');

            Route::resource('admissao', \App\Http\Controllers\AdmissaoController::class)->middleware('can:admissao_processo');

            Route::group(['as' => 'documentos.', 'prefix' => 'documentos'], function () {

                Route::group(['as' => 'cartaoferta.', 'prefix' => 'carta-oferta'], function () {
                    Route::post('atualizar', [\App\Http\Controllers\CartaOfertaGerencialController::class, 'atualizar'])->name('atualizar'); // manter essa rota antes do resource
                    Route::put('responder', [\App\Http\Controllers\CartaOfertaGerencialController::class, 'responder'])->name('responder');
                    Route::post('/enviar-email', [\App\Http\Controllers\CartaOfertaGerencialController::class, 'enviarEmail'])->name('enviarEmail');
                    Route::get('/', [\App\Http\Controllers\CartaOfertaGerencialController::class, 'index'])->name('index');
                });
            });
        });
    });

    Route::group(['as' => 'posadmissao.'], function () {
        Route::post('posadmissao/atualizar', [\App\Http\Controllers\PosAdmissaoController::class, 'atualizar'])->name('posadmissao.atualizar')->middleware('can:admissao_pos_admissao'); // manter essa rota antes do resource
        Route::put('posadmissao/desmobilizar', [\App\Http\Controllers\PosAdmissaoController::class, 'desmobilizar'])->name('posadmissao.desmobilizar'); // manter essa rota antes do resource
        Route::post('posadmissao/entrevistar', [\App\Http\Controllers\PosAdmissaoController::class, 'entrevistar'])->name('posadmissao.entrevistar'); // manter essa rota antes do resource
        Route::put('posadmissao/entrevistar/{entrevista}', [\App\Http\Controllers\PosAdmissaoController::class, 'entrevistarUpdate'])->name('posadmissao.entrevistarUpdate'); // manter essa rota antes do resource
        Route::get('posadmissao/entrevista/{curriculo}', [\App\Http\Controllers\PosAdmissaoController::class, 'entrevista'])->name('posadmissao.entrevista'); // manter essa rota antes do resource
        Route::post('posadmissao/demitir', [\App\Http\Controllers\PosAdmissaoController::class, 'demitir'])->name('posadmissao.demitir'); // manter essa rota antes do resource
        Route::get('posadmissao/demitir/pdf/{id}', [\App\Http\Controllers\PosAdmissaoController::class, 'demissaoPdf'])->name('posadmissao.demissaoPdf'); // manter essa rota antes do resource
        Route::post('posadmissao/export', [\App\Http\Controllers\PosAdmissaoController::class, 'export'])->name('posadmissao.excel');
        Route::put('posadmissao/remover-demissao', [\App\Http\Controllers\PosAdmissaoController::class, 'removerDemissao'])->middleware('can:privilegio_gestao_rh');
        Route::resource('posadmissao', \App\Http\Controllers\PosAdmissaoController::class, ['parameters' => ['posadmissao' => 'admissao']])->middleware('can:admissao_pos_admissao');
    });

    Route::group(['as' => 'historico.', 'prefix' => 'historico'], function () {
        Route::post('/atualizar', [\App\Http\Controllers\HistoricoController::class, 'atualizar'])->name('atualizar'); // manter essa rota antes do resource
        Route::get('/{feedback}', [\App\Http\Controllers\HistoricoController::class, 'show'])->name('show');
        Route::post('/{feedback}', [\App\Http\Controllers\HistoricoController::class, 'storeMedidas'])->name('storeMedidas');
        Route::put('/{feedback}', [\App\Http\Controllers\HistoricoController::class, 'updateMedidas'])->name('updateMedidas');
        Route::get('/', [\App\Http\Controllers\HistoricoController::class, 'index'])->name('index')->middleware('can:admissao_historico'); // manter essa rota antes do resource

        //Rotas Medidas Administrativas
        Route::group(['as' => 'medidas-administrativas.', 'prefix' => 'medidas-administrativas'], function () {
            Route::get('/anexo/{arquivo}', [\App\Http\Controllers\HistoricoController::class, 'anexoShow'])->name('anexo-show');
            Route::get('/anexoDownload/{arquivo}', [\App\Http\Controllers\HistoricoController::class, 'download'])->name('anexo-download');
            Route::delete('/anexo/{arquivo}', [\App\Http\Controllers\HistoricoController::class, 'anexoDelete'])->name('anexo-delete');
            Route::post('/uploadAnexos', [\App\Http\Controllers\HistoricoController::class, 'uploadAnexos'])->name('.upload-anexos');
            Route::get('/{medida}/{feedback_id}/pdf', [\App\Http\Controllers\HistoricoController::class, 'medidasAdministrativasPDF'])->name('pdfMedidasAdministrativas');
        });

        //Rotas Afastamento Historico
        Route::group(['as' => 'afastamento-historico.', 'prefix' => 'afastamento-historico'], function () {
            Route::get('/anexo/{arquivo}', [\App\Http\Controllers\AfastamentoController::class, 'anexoShow'])->name('anexo-show');
            Route::get('/anexoDownload/{arquivo}', [\App\Http\Controllers\AfastamentoController::class, 'download'])->name('anexo-download');
            Route::delete('/anexo/{arquivo}', [\App\Http\Controllers\AfastamentoController::class, 'anexoDelete'])->name('anexo-delete');
            Route::post('/uploadAnexos', [\App\Http\Controllers\AfastamentoController::class, 'uploadAnexos'])->name('.upload-anexos');
            Route::post('/', [\App\Http\Controllers\AfastamentoController::class, 'store'])->name('store-afastamento')->middleware('can:admissao_historico_aba_afastamento');
            Route::get('/{feedback}', [\App\Http\Controllers\AfastamentoController::class, 'show'])->name('show-afastamento')->middleware('can:admissao_historico_aba_afastamento');
            Route::post('/{feedback}', [\App\Http\Controllers\AfastamentoController::class, 'store'])->name('store-afastamento')->middleware('can:admissao_historico_aba_afastamento');
            Route::put('/{feedback}', [\App\Http\Controllers\AfastamentoController::class, 'update'])->name('update-afastamento')->middleware('can:admissao_historico_aba_afastamento');
        });

        //Rotas Formulario Noventa Dias
        Route::group(['as' => 'formulario-noventa-dias.', 'prefix' => 'formulario-noventa-dias'], function () {
            Route::post('/{feedback}', [\App\Http\Controllers\HistoricoController::class, 'storeFormularioNoventaDias'])->name('storeNoventaDias');
            Route::get('/{quantidade_avaliacao}/{feedback_id}/pdf', [\App\Http\Controllers\HistoricoController::class, 'formularioNoventaDiasPDF'])->name('formularioNoventaDiasPDF');
        });

        //Rotas DOSSIE
        Route::group(['as' => 'dossie.', 'prefix' => 'dossie'], function () {
            Route::post('/uploadAnexos', [\App\Http\Controllers\DossieController::class, 'uploadAnexos'])->name('upload-anexos');
            Route::get('/anexo/{arquivo}', [\App\Http\Controllers\DossieController::class, 'anexoShow'])->name('anexo-show');
            Route::get('/anexoDownload/{arquivo}', [\App\Http\Controllers\DossieController::class, 'download'])->name('anexo-download');
            Route::delete('/anexo/{arquivo}', [\App\Http\Controllers\DossieController::class, 'anexoDelete'])->name('anexo-delete');
//        Route::get('/{feedback_id}/pdf', [\App\Http\Controllers\DossieController::class,'medidasAdministrativasPDF'])->name('pdfDossie');
            Route::post('/{feedback}', [\App\Http\Controllers\DossieController::class, 'store'])->name('store');
            Route::get('/{feedback}', [\App\Http\Controllers\DossieController::class, 'show'])->name('show');
            Route::get('/{tipo_modelo}/{curriculo_id}/{feedback_id}', [\App\Http\Controllers\DossieController::class, 'downloadModelo'])->name('downloadModelo');
        });

        //Rotas Avaliacao Anual
        Route::group(['as' => 'avaliacao-anual.', 'prefix' => 'avaliacao-anual'], function () {
            Route::get('/{feedback}', [\App\Http\Controllers\AvaliacaoAnualFeedbackController::class, 'show'])->name('showAvaliacaoAnual');
            Route::post('/{feedback}', [\App\Http\Controllers\AvaliacaoAnualFeedbackController::class, 'store'])->name('storeAvaliacaoAnual');
            Route::get('/{quantidade_avaliacao}/{feedback_id}/pdf', [\App\Http\Controllers\AvaliacaoAnualFeedbackController::class, 'avaliacaoAnualPDF'])->name('avaliacaoAnualPDF');
        });

        //Rotas Ferias e listagem Afastamento
        Route::group(['as' => 'ferias.', 'prefix' => 'ferias'], function () {
            Route::get('/{feedback}', [\App\Http\Controllers\FeriasFeedbackController::class, 'show'])->name('showFeriasFeedback');
            Route::post('/{feedback}', [\App\Http\Controllers\FeriasFeedbackController::class, 'store'])->name('storeFeriasFeedback');
            Route::get('/{id}/{feedback_id}/pdf', [\App\Http\Controllers\FeriasFeedbackController::class, 'feriasPDF'])->name('feriasPDF');
        });

        //Rotas Afastamento
        Route::group(['as' => 'afastamento.', 'prefix' => 'afastamento'], function () {
            Route::post('/{feedback}', [\App\Http\Controllers\AfastamentoFeedbackController::class, 'store'])->name('storeAfastamentoFeedback');
            Route::get('/{id}/{feedback_id}/pdf', [\App\Http\Controllers\AfastamentoFeedbackController::class, 'afastamentoPDF'])->name('afastamentoPDF');
        });

        Route::group(['as' => 'beneficio.', 'prefix' => 'beneficio'], function () {
            Route::get('/{feedback}', [\App\Http\Controllers\BeneficioController::class, 'showBeneficio'])->name('showBeneficio');
            Route::post('/{feedback}', [\App\Http\Controllers\BeneficioController::class, 'storeBeneficio'])->name('storeBeneficio');
        });

        Route::get('/cih/{feedback}', [\App\Http\Controllers\CihController::class, 'atualizarHistorico'])->name('atualizarHistorico');

        Route::group(['as' => 'promocao.', 'prefix' => 'promocao'], function () {
            Route::get('/atualizar/{feedback}', [\App\Http\Controllers\PromocaoFeedbackController::class, 'atualizar'])->name('atualizarPromocao'); // manter essa rota antes do resource
            Route::post('/{feedback}', [\App\Http\Controllers\PromocaoFeedbackController::class, 'store'])->name('storePromocao');
        });

        Route::group(['as' => 'meta.', 'prefix' => 'meta'], function () {
            Route::get('/atualizar/{feedback}', [\App\Http\Controllers\MetasFeedbackController::class, 'atualizar'])->name('atualizarMeta'); // manter essa rota antes do resource
            Route::post('/{feedback}', [\App\Http\Controllers\MetasFeedbackController::class, 'store'])->name('storeMeta');
        });

        Route::group(['as' => 'feedbackhistorico.', 'prefix' => 'feedback-historico'], function () {
            Route::get('/atualizar/{feedback_id}', [\App\Http\Controllers\FeedbackHistoricoController::class, 'atualizar'])->name('atualizar'); // manter essa rota antes do resource
            Route::post('/{feedback}', [\App\Http\Controllers\FeedbackHistoricoController::class, 'store'])->name('store');
        });

        Route::group(['as' => 'logshistorico.', 'prefix' => 'log-historico'], function () {
            Route::post('/atualizar', [\App\Http\Controllers\LogsHistoricoController::class, 'atualizar'])->name('atualizarLog'); // manter essa rota antes do resource
        });


    });
    //Fim Menu Admissao

    //Componente Etapa Classifica e desclassifica
    Route::group(['as' => 'etapa.'], function () {
//        Route::get('etapa/{curriculo}/selecionado', 'CurriculosSelecionadosController@getCurriculo')->name('etapa.getCurriculo');
        Route::post('etapa/{feedback}/desclassificar', [\App\Http\Controllers\EtapasController::class, 'desclassificar'])->name('etapa.desclassificar');
        Route::post('etapa/{feedback}/classificar', [\App\Http\Controllers\EtapasController::class, 'classificar'])->name('etapa.classificar');
    });

    // TREINAMENTOS
    Route::group(['as' => 'treinamentos.'], function () {
        //anexo
        Route::post('treinamento/uploadAnexos', [\App\Http\Controllers\TreinamentoEventoController::class, 'uploadAnexos'])->name('treinamento.upload-anexos');
        Route::get('treinamento/anexo/{arquivo}', [\App\Http\Controllers\TreinamentoEventoController::class, 'anexoShow'])->name('treinamento.anexo-show');
        Route::get('treinamento/anexoDownload/{arquivo}', [\App\Http\Controllers\TreinamentoEventoController::class, 'download'])->name('treinamento.anexo-download');
        Route::delete('treinamento/anexo/{arquivo}', [\App\Http\Controllers\TreinamentoEventoController::class, 'anexoDelete'])->name('treinamento.anexo-delete');

        //Enviar para Revisao
        Route::post('treinamento/enviar-carteira', [\App\Http\Controllers\TreinamentoController::class, 'enviarCarteiraEmail']);
        Route::post('treinamento/carteiras', [\App\Http\Controllers\TreinamentoController::class, 'carteiraPdf'])->name('carteiraPdf');
        Route::post('treinamento/export', [\App\Http\Controllers\TreinamentoController::class, 'export'])->name('excel');
        Route::post('treinamento/atualizar', [\App\Http\Controllers\TreinamentoController::class, 'atualizar'])->name('atualizar');

        Route::get('treinamento/vencimentos', [\App\Http\Controllers\TreinamentoController::class, 'vencimentos'])->name('vencimentos');

        Route::post('treinamento/proximovencimento', [\App\Http\Controllers\TreinamentoController::class, 'treinamentoProximoVencimento'])->name('vencimentoTreinamento');
        Route::post('treinamento/salvar-massa', [\App\Http\Controllers\TreinamentoController::class, 'storeMassa'])->name('storeMassa');

        Route::resource('treinamento', \App\Http\Controllers\TreinamentoController::class)->middleware('can:treinamento_carteira-etiquetas');
    });

    //PORTARIA
    Route::group(['as' => 'portaria.'], function () {
        Route::post('portaria/atualizar', [\App\Http\Controllers\PortariaController::class, 'atualizar'])->name('atualizar');
        Route::post('portaria/pdf', [\App\Http\Controllers\PortariaController::class, 'pdf'])->name('pdf');
        Route::post('portaria/export', [\App\Http\Controllers\PortariaController::class, 'export'])->name('export');
        Route::get('portaria/{resultado}', [\App\Http\Controllers\PortariaController::class, 'edit'])->name('edit');
        Route::put('portaria/{resultado}', [\App\Http\Controllers\PortariaController::class, 'update'])->name('update');
        Route::get('portaria', [\App\Http\Controllers\PortariaController::class, 'index'])->name('index')->middleware('can:treinamento_portaria');
    });

    // CERTIFICADO
    Route::group(['as' => 'certificados.'], function () {
        //Enviar para Revisao
        Route::post('certificado/enviar-carteira', [\App\Http\Controllers\CertificadoController::class, 'enviarCarteiraEmail']);
        Route::post('certificado/atualizar', [\App\Http\Controllers\CertificadoController::class, 'atualizar'])->name('atualizar');

        Route::post('certificado/pdf', [\App\Http\Controllers\CertificadoController::class, 'certificadoPdf'])->name('certificadoPdf');
        Route::resource('certificado', \App\Http\Controllers\CertificadoController::class)->middleware('can:treinamento_certificado');
    });

    //Cloud
    Route::group(['as' => 'cloud.'], function () {
        Route::get('cloud/atualizar/{cloud}/{id?}', [\App\Http\Controllers\CloudController::class, 'atualizar'])->name('cloud.atualizar')->middleware('can:cloud'); // manter essa rota antes do resource
        Route::get('cloud/{id}/{titulo}', [\App\Http\Controllers\CloudController::class, 'getSingle'])->name('cloud.single'); // manter essa rota antes do resource
        Route::get('cloud/editar/pasta/{item}', [\App\Http\Controllers\CloudController::class, 'editarPasta'])->name('cloud.editarPasta'); // manter essa rota antes do resource
        Route::resource('cloud', \App\Http\Controllers\CloudController::class)->middleware('can:cloud');

        Route::group(['as' => 'cadastro.', 'prefix' => 'clouds'], function () {
            Route::get('cadastro', [\App\Http\Controllers\CloudController::class, 'indexCadastro'])->name('indexCadastro')//                ->middleware('can:cloud_cadastro')
            ;
            Route::post('cadastro/atualizar', [\App\Http\Controllers\CloudController::class, 'listarClouds'])->name('listarClouds')//                ->middleware('can:cloud_cadastro')
            ;
            Route::post('cadastro', [\App\Http\Controllers\CloudController::class, 'storeCloud'])->name('storeCloud')//                ->middleware('can:cloud_cadastro')
            ;
            Route::get('cadastro/{cloud}/editar', [\App\Http\Controllers\CloudController::class, 'edit'])->name('edit')//                ->middleware('can:cloud_cadastro')
            ;

            Route::put('cadastro/{cloud}', [\App\Http\Controllers\CloudController::class, 'updateCloud'])->name('updateCloud')//                ->middleware('can:cloud_cadastro')
            ;

            Route::put('cadastro/{cloud}/ativa-desativa', [\App\Http\Controllers\CloudController::class, 'ativaDesativa'])->name('ativaDesativa')//                ->middleware('can:cloud_cadastro')
            ;


        });

        Route::group(['as' => 'configuracoes.', 'prefix' => 'clouds'], function () {
            Route::post('configuracoes/atualizar', [\App\Http\Controllers\CloudConfiguracaoController::class, 'atualizar'])->name('atualizar')->middleware('can:cloud_configuracoes');
            Route::resource('configuracoes', \App\Http\Controllers\CloudConfiguracaoController::class, ['parameters' => ['configuracoes' => 'grupocloud']])
                ->middleware('can:cloud_configuracoes');
        });
    });

    //itens Cloud
    Route::group(['as' => 'itenscloud.'], function () {
        //Enviar para Revisao
        Route::post('itenscloud/enviar-para-revisao', [\App\Http\Controllers\ItensCloudController::class, 'enviarRevisao']);
        //Enviar para Aprovação
        Route::post('itenscloud/enviar-para-aprovacao', [\App\Http\Controllers\ItensCloudController::class, 'enviarAprovacao']);

        //ANEXO DE ITENS
        Route::post('itenscloud/uploadAnexos', [\App\Http\Controllers\ItensCloudController::class, 'uploadAnexos'])->name('upload-anexos');
        Route::post('itenscloud/uploadAtualizarAnexos', [\App\Http\Controllers\ItensCloudController::class, 'uploadAtualizarAnexos'])->name('uploadAtualizarAnexos');

        Route::post('itenscloud/mover/{item}', [\App\Http\Controllers\ItensCloudController::class, 'moverArquivo']);
        Route::get('itenscloud/estrutura-mover/{cloud}/{id?}', [\App\Http\Controllers\ItensCloudController::class, 'moverEstruturaPasta']);

        Route::put('itenscloud/{item}/revisar', [\App\Http\Controllers\ItensCloudController::class, 'revisar']);
        Route::put('itenscloud/{item}/aprovar', [\App\Http\Controllers\ItensCloudController::class, 'aprovar']);
        Route::resource('itenscloud', \App\Http\Controllers\ItensCloudController::class)->middleware('can:cloud');
    });

    // Ocorrencias
    Route::group(['as' => 'ocorrencia.'], function () {
        //Controle de Ocorrencias
        Route::post('ocorrencia/uploadAnexos', [\App\Http\Controllers\OcorrenciaController::class, 'uploadAnexos'])->name('ocorrencia.upload-anexos');
        Route::get('ocorrencia/anexo/{arquivo}', [\App\Http\Controllers\OcorrenciaController::class, 'anexoShow'])->name('ocorrencia.anexo-show');
        Route::get('ocorrencia/anexoDownload/{arquivo}', [\App\Http\Controllers\OcorrenciaController::class, 'download'])->name('ocorrencia.anexo-download');
        Route::delete('ocorrencia/anexo/{arquivo}', [\App\Http\Controllers\OcorrenciaController::class, 'anexoDelete'])->name('ocorrencia.anexo-delete');
        Route::get('ocorrencia/exibir/{id}', [\App\Http\Controllers\OcorrenciaController::class, 'Exibir'])->name('ocorrencia.exibir')->middleware('can:ocorrencia');
        Route::get('ocorrencia/listaSetoresTags', [\App\Http\Controllers\OcorrenciaController::class, 'listaSetoresTags'])->name('ocorrencia.listasetorestags')->middleware('can:ocorrencia');
        Route::post('ocorrencia/nova_mensagem', [\App\Http\Controllers\OcorrenciaController::class, 'novaMensagem'])->name('ocorrencia.nova_mensagem')->middleware('can:ocorrencia');
        Route::post('ocorrencia/mudar_setor', [\App\Http\Controllers\OcorrenciaController::class, 'mudarSetor'])->name('ocorrencia.nova_mensagem')->middleware('can:ocorrencia');
        Route::post('ocorrencia/finalizar', [\App\Http\Controllers\OcorrenciaController::class, 'finalizar'])->name('ocorrencia.finalizar')->middleware('can:ocorrencia');
        Route::post('ocorrencia/atualizar', [\App\Http\Controllers\OcorrenciaController::class, 'atualizar'])->name('ocorrencia.atualizar')->middleware('can:ocorrencia');
        Route::post('ocorrencia/cadastro-tag', [\App\Http\Controllers\OcorrenciaController::class, 'cadastroTag'])->name('ocorrencia.cadastro-tag')->middleware('can:ocorrencia');
        Route::post('ocorrencia/cadastro-setor', [\App\Http\Controllers\OcorrenciaController::class, 'cadastroSetor'])->name('ocorrencia.cadastro-setor')->middleware('can:ocorrencia');
        Route::resource('ocorrencia', \App\Http\Controllers\OcorrenciaController::class)->middleware('can:ocorrencia');
    });

    // Site
    Route::group(['as' => 'site.'], function () {
        //ANEXO DE FOTOS
        Route::post('galeria/uploadFotos', [\App\Http\Controllers\GaleriaController::class, 'uploadFotos'])->name('galeria.upload-fotos')->middleware('can:site_galeria_site');
        Route::get('galeria/fotoDownload/{arquivo}', [\App\Http\Controllers\GaleriaController::class, 'download'])->name('galeria.foto-download')->middleware('can:site_galeria_site');
        Route::put('galeria/{galeria}/ativa-desativa', [\App\Http\Controllers\GaleriaController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:site_galeria_site');

        Route::post('galeria/atualizar', [\App\Http\Controllers\GaleriaController::class, 'atualizar'])->name('galeria.atualizar')->middleware('can:site_galeria_site');
        Route::resource('galeria', \App\Http\Controllers\GaleriaController::class, ['parameters' => ['galeria' => 'galeria']])->middleware('can:site_galeria_site');

        //CLIENTES
        Route::group(['as' => 'cliente.'], function () {
            Route::post('cliente-logo/atualizar', [\App\Http\Controllers\ClienteLogoSiteController::class, 'atualizar'])->name('atualizar')->middleware('can:site_cartela_cliente_site');
            Route::post('cliente-logo/fotoUpload', [\App\Http\Controllers\ClienteLogoSiteController::class, 'fotoUpload'])->name('upload-fotos')->middleware('can:site_cartela_cliente_site_insert');
//                Route::get('cliente/fotoDownload/{arquivo}', 'ClientesController@download')->name('foto-download');
            Route::resource('cliente-logo', \App\Http\Controllers\ClienteLogoSiteController::class)->middleware('can:site_cartela_cliente_site');
        });

        Route::group(['as' => 'testemunhal.'], function () {
            // Anexos
            Route::post('testemunhal/uploadAnexos', [\App\Http\Controllers\TestemunhalController::class, 'uploadAnexos'])->name('testemunhal.upload-anexos')->middleware('can:site_depoimento_site_insert');
            Route::get('testemunhal/anexo/{arquivo}', [\App\Http\Controllers\TestemunhalController::class, 'anexoShow'])->name('testemunhal.anexo-show');
            Route::get('testemunhal/anexoDownload/{arquivo}', [\App\Http\Controllers\TestemunhalController::class, 'download'])->name('testemunhal.anexo-download');
            Route::delete('testemunhal/anexo/{arquivo}', [\App\Http\Controllers\TestemunhalController::class, 'anexoDelete'])->name('testemunhal.anexo-delete');


            Route::post('testemunhal/atualizar', [\App\Http\Controllers\TestemunhalController::class, 'atualizar'])->name('testemunhal.atualizar')->middleware('can:site_depoimento_site');
            Route::resource('testemunhal', \App\Http\Controllers\TestemunhalController::class)->middleware('can:site_depoimento_site');
        });
    });

    // Relatorios
    Route::group(['as' => 'relatorios.', 'prefix' => 'relatorios'], function () {
        Route::group(['as' => 'controleusuarios.'], function () {
            Route::get('controleusuarios', [\App\Http\Controllers\ControleUsuariosController::class, 'index'])->name('index')->middleware('can:relatorio_relatorios');
            Route::post('controleusuarios/dados', [\App\Http\Controllers\ControleUsuariosController::class, 'dadosusuarioSistema'])->name('dadosusuarioSistema')->middleware('can:relatorio_relatorios');
//            Route::get('relatorios/controleusuarios/pdf/{dados}', [\App\Http\Controllers\ControleUsuariosController::class, 'usuarioSistema'])->name('usuarioSistema')->middleware('can:relatorios');
        });
        Route::group(['as' => 'vencimentoasos.'], function () {
            Route::get('vencimentoasos', [\App\Http\Controllers\Relatorios\VencimentoAsosController::class, 'index'])->name('index')->middleware('can:relatorio_asos');
            Route::get('tipos-exames', [\App\Http\Controllers\Relatorios\VencimentoAsosController::class, 'tiposExames'])->name('tiposExames')->middleware('can:relatorio_asos');
            Route::post('vencimentoasos/export-excel', [\App\Http\Controllers\Relatorios\VencimentoAsosController::class, 'exportExcel'])->name('exportExcel')->middleware('can:relatorio_asos');
            Route::post('vencimentoasos', [\App\Http\Controllers\Relatorios\VencimentoAsosController::class, 'show'])->name('show')->middleware('can:relatorio_asos');
        });
        Route::group(['as' => 'medidasadministrativas.'], function () {
            Route::get('medidasadministrativas', [\App\Http\Controllers\Relatorios\MedidasAdministrativasController::class, 'index'])->name('index')->middleware('can:relatorio_medidas_administrativas');
            Route::post('medidasadministrativas', [\App\Http\Controllers\Relatorios\MedidasAdministrativasController::class, 'show'])->name('show')->middleware('can:relatorio_medidas_administrativas');
            Route::post('medidasadministrativas/export-excel', [\App\Http\Controllers\Relatorios\MedidasAdministrativasController::class, 'exportExcel'])->name('exportExcel')->middleware('can:relatorio_medidas_administrativas');
        });

        Route::group(['as' => 'vencimentotreinamento.'], function () {
            Route::get('vencimento-treinamento', [\App\Http\Controllers\Relatorios\TreinamentoController::class, 'index'])->name('index')->middleware('can:relatorio_treinamento');
            Route::post('vencimento-treinamento', [\App\Http\Controllers\Relatorios\TreinamentoController::class, 'show'])->name('show')->middleware('can:relatorio_treinamento');
            Route::post('vencimento-treinamento/export-excel', [\App\Http\Controllers\Relatorios\TreinamentoController::class, 'exportExcel'])->name('exportExcel')->middleware('can:relatorio_treinamento');

        });

        Route::group(['as' => 'vencimentoferias.'], function () {
            Route::get('vencimento-ferias', [\App\Http\Controllers\Relatorios\FeriasController::class, 'indexVencimentoFerias'])->name('indexVencimentoFerias')->middleware('can:relatorio_ferias');
            Route::post('vencimento-ferias', [\App\Http\Controllers\Relatorios\FeriasController::class, 'showVencimentoFerias'])->name('showVencimentoFerias')->middleware('can:relatorio_ferias');
            Route::post('vencimento-ferias/export-excel', [\App\Http\Controllers\Relatorios\FeriasController::class, 'exportExcelVencimentoFerias'])->name('exportExcelVencimentoFerias')->middleware('can:relatorio_ferias');

        });

        Route::group(['as' => 'ferias.'], function () {
            Route::get('ferias', [\App\Http\Controllers\Relatorios\FeriasController::class, 'index'])->name('index')->middleware('can:relatorio_ferias');
            Route::post('ferias', [\App\Http\Controllers\Relatorios\FeriasController::class, 'show'])->name('show')->middleware('can:relatorio_ferias');
            Route::post('ferias/listaperiodos', [\App\Http\Controllers\Relatorios\FeriasController::class, 'listaperiodos'])->name('listaperiodos')->middleware('can:relatorio_ferias');
            Route::post('ferias/export-excel', [\App\Http\Controllers\Relatorios\FeriasController::class, 'exportExcel'])->name('exportExcel')->middleware('can:relatorio_ferias');

        });

        Route::group(['as' => 'centrodecusto.'], function () {
            Route::get('centrodecusto', [\App\Http\Controllers\Relatorios\CentroDeCustoController::class, 'index'])->name('index')->middleware('can:relatorio_centro_de_custo');
            Route::post('centrodecusto/pdf', [\App\Http\Controllers\Relatorios\CentroDeCustoController::class, 'exportPdf'])->name('exportPdf')->middleware('can:relatorio_centro_de_custo');
            Route::post('centrodecusto/export-excel', [\App\Http\Controllers\Relatorios\CentroDeCustoController::class, 'exportExcel'])->name('exportExcel')->middleware('can:relatorio_centro_de_custo');
            Route::post('centrodecusto/atualizar', [\App\Http\Controllers\Relatorios\CentroDeCustoController::class, 'atualizar'])->name('atualizar')->middleware('can:relatorio_centro_de_custo');
        });

        Route::group(['as' => 'efetivo.'], function () {
            Route::get('efetivo', [\App\Http\Controllers\Relatorios\EfetivoController::class, 'index'])->name('index')->middleware('can:relatorio_efetivo');
            Route::post('efetivo/pdf', [\App\Http\Controllers\Relatorios\EfetivoController::class, 'exportPdf'])->name('exportPdf')->middleware('can:relatorio_efetivo');
            Route::post('efetivo/export-excel', [\App\Http\Controllers\Relatorios\EfetivoController::class, 'exportExcel'])->name('exportExcel')->middleware('can:relatorio_efetivo');
            Route::post('efetivo/atualizar', [\App\Http\Controllers\Relatorios\EfetivoController::class, 'atualizar'])->name('atualizar')->middleware('can:relatorio_efetivo');
        });

        //Aniversariantes
        Route::group(['as' => 'aniversariantes.'], function () {
            Route::post('aniversariantes/atualizar', [\App\Http\Controllers\AniversariantesController::class, 'relatorioAtualizar'])->name('relatNiversAtualizar')->middleware('can:relatorio_aniversariantes');
            Route::get('aniversariantes', [\App\Http\Controllers\AniversariantesController::class, 'relatorioIndex'])->name('relatorioNivers')->middleware('can:relatorio_aniversariantes');
            Route::post('aniversariantes/pdf', [\App\Http\Controllers\AniversariantesController::class, 'exporRelatorioPdf'])->name('relatNiversPdf')->middleware('can:relatorio_aniversariantes');
            Route::post('aniversariantes/export', [\App\Http\Controllers\AniversariantesController::class, 'exporRelatorioExcel'])->name('relatNiversExcel')->middleware('can:relatorio_aniversariantes');
        });
    });

    // Usuarios
    Route::group(['as' => 'usuarios.'], function () {
        //Usuários
        Route::get('usuario/autenticado', [\App\Http\Controllers\UserController::class, 'getUsuario'])->name('getUsuario');
        Route::put('usuarios/simularUsuario', [\App\Http\Controllers\UserController::class, 'simularUsuario'])->name('simularUsuario');

        Route::post('usuarios/atualizar', [\App\Http\Controllers\UserController::class, 'atualizar'])->name('usuarios.atualizar')->middleware('can:usuario_usuarios');
        Route::put('usuarios/{usuario}/ativa-desativa', [\App\Http\Controllers\UserController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:usuario_usuarios');
        //Alterar senha
        Route::get('alterar-senha', [\App\Http\Controllers\AlterarSenhaController::class, 'index'])->name('alterar-senha.index')->middleware('can:usuario_alterar-senha');
        Route::put('alterar-senha', [\App\Http\Controllers\AlterarSenhaController::class, 'update'])->name('alterar-senha.update')->middleware('can:usuario_alterar-senha');


        Route::resource('usuarios', \App\Http\Controllers\UserController::class)->middleware('can:usuario_usuarios');

        //busca os grupos da uma empresa
        Route::get('usuario/busca-grupo-empresa/{empresa_id}', [\App\Http\Controllers\UserController::class, 'buscaGrupoEmpresa'])->name('buscaGrupoEmpresa')->middleware('can:usuario_usuarios');

    });

    Route::group(['as' => 'perfil.'], function () {
        Route::get('perfil/{id}', [\App\Http\Controllers\UserController::class, 'perfilUsuario'])->name('perfilUsuario');
        Route::put('perfil/{id}', [\App\Http\Controllers\UserController::class, 'atualizaPerfilUsuario'])->name('atualizaPerfilUsuario');
        Route::post('perfil/anexo/uploadAnexos', [\App\Http\Controllers\UserController::class, 'uploadAnexos'])->name('upload-anexos-perfil');
        Route::get('perfil/anexo/{arquivo}', [\App\Http\Controllers\UserController::class, 'anexoShow'])->name('anexo-show-perfil');
        Route::get('perfil/anexoDownload/{arquivo}', [\App\Http\Controllers\UserController::class, 'download'])->name('anexo-download-perfil');
        Route::delete('perfil/anexo/{arquivo}', [\App\Http\Controllers\UserController::class, 'anexoDelete'])->name('anexo-delete-perfil');
    });

    //Financeiro
    Route::group(['as' => 'financeiro.'], function () {

        Route::group(['as' => 'fluxo-caixa.'], function () {
            Route::put('fluxo-caixa/{empresa}/lancamento/{lancamento}/mudarStatus', [\App\Http\Controllers\FluxoCaixaController::class, 'mudarStatus'])->name('mudarStatus')->middleware('can:privilegio_realizar-lancamento');
            Route::put('fluxo-caixa/{empresa}/lancamento/{lancamento}', [\App\Http\Controllers\FluxoCaixaController::class, 'alterarLancamento'])->name('alterarLancamento')->middleware('can:financeiro_fluxo-caixa');
            Route::delete('fluxo-caixa/{empresa}/lancamento/{lancamento}', [\App\Http\Controllers\FluxoCaixaController::class, 'excluirLancamento'])->name('excluirLancamento')->middleware('can:financeiro_fluxo-caixa');
            Route::post('fluxo-caixa/{empresa}/lancamento', [\App\Http\Controllers\FluxoCaixaController::class, 'cadastrarLancamento'])->name('cadastrarLancamento')->middleware('can:financeiro_fluxo-caixa_insert');
            Route::get('fluxo-caixa/{empresa}/lancamento/{lancamento}', [\App\Http\Controllers\FluxoCaixaController::class, 'carregarLancamento'])->name('carregarLancamento')->middleware('can:financeiro_fluxo-caixa');
            Route::get('fluxo-caixa/buscaNomePlanoConta', [\App\Http\Controllers\FluxoCaixaController::class, 'buscaNomePlanoConta'])->name('buscaNomePlanoConta'); //->middleware('can:financeiro_fluxo-caixa'); não tem restrição (tela de recibos usa)
            Route::post('fluxo-caixa/{empresa}/atualizaFluxoCaixa', [\App\Http\Controllers\FluxoCaixaController::class, 'atualizaFluxoCaixa'])->name('atualizaFluxoCaixa')->middleware('can:financeiro_fluxo-caixa');
            Route::get('fluxo-caixa/{empresa}', [\App\Http\Controllers\FluxoCaixaController::class, 'show'])->name('show')->middleware('can:financeiro_fluxo-caixa');
            Route::get('fluxo-caixa', [\App\Http\Controllers\FluxoCaixaController::class, 'index'])->name('index')->middleware('can:financeiro_fluxo-caixa');
        });

        //Categoria Plano Conta
        Route::group(['as' => 'classificacao-plano-conta.'], function () {
            Route::get('classificacao-plano-conta/buscar', [\App\Http\Controllers\CategoriaPlanoContaController::class, 'buscaCategoria'])->name('buscar')->middleware('can:financeiro_classificacao-plano-conta');
            Route::post('classificacao-plano-conta/atualizar', [\App\Http\Controllers\CategoriaPlanoContaController::class, 'atualizar'])->name('atualizar')->middleware('can:financeiro_classificacao-plano-conta');
        });
        Route::resource('classificacao-plano-conta', \App\Http\Controllers\CategoriaPlanoContaController::class, ['parameters' => ['classificacao-plano-conta' => 'categoria']])->middleware('can:financeiro_classificacao-plano-conta');

        //Planos de conta
        Route::group(['as' => 'plano-conta.'], function () {
            Route::get('plano-conta/buscar', [\App\Http\Controllers\PlanoContaController::class, 'busca'])->name('buscar')->middleware('can:financeiro_plano-conta');
            Route::post('plano-conta/atualizar', [\App\Http\Controllers\PlanoContaController::class, 'atualizar'])->name('atualizar')->middleware('can:financeiro_plano-conta');
        });
        Route::resource('plano-conta', \App\Http\Controllers\PlanoContaController::class, ['parameters' => ['plano-conta' => 'plano']])->middleware('can:financeiro_plano-conta');

        //Formas de pagamento
        Route::group(['as' => 'formas-pagamento.'], function () {
            Route::get('formas-pagamento/buscar', [\App\Http\Controllers\FormaPagamentoController::class, 'buscaCategoria'])->name('buscar')->middleware('can:financeiro_formas-pagamento');
            Route::post('formas-pagamento/atualizar', [\App\Http\Controllers\FormaPagamentoController::class, 'atualizar'])->name('atualizar')->middleware('can:financeiro_formas-pagamento');
        });
        Route::resource('formas-pagamento', \App\Http\Controllers\FormaPagamentoController::class, ['parameters' => ['formas-pagamento' => 'forma']])->middleware('can:financeiro_formas-pagamento');

    });

    //Controle de ponto
    Route::group(['as' => 'controle-ponto.', 'prefix' => 'controle-ponto'], function () {

        //Configuracoes
        Route::group(['as' => 'configuracoes.'], function () {
            Route::post('configuracoes/buscarPerimetros', [\App\Http\Controllers\EmpresaConfigController::class, 'atualizarFuncionarios'])->name('atualizarFuncionarios')->middleware('can:controle_ponto_config_empresa');
            Route::post('configuracoes/atualizarFuncionarios', [\App\Http\Controllers\EmpresaConfigController::class, 'atualizarFuncionarios'])->name('atualizarFuncionarios')->middleware('can:controle_ponto_config_empresa');
            Route::get('configuracoes/getPermissoes', [\App\Http\Controllers\EmpresaConfigController::class, 'getPermissoes'])->name('getPermissoes');

        });
        Route::resource('configuracoes', \App\Http\Controllers\EmpresaConfigController::class, ['parameters' => ['configuracoes' => 'config']])->middleware('can:controle_ponto_config_empresa');

        // Feriados
        Route::group(['as' => 'feriados.'], function () {
            Route::put('feriados/{feriado}/ativa-desativa', [\App\Http\Controllers\FeriadoController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:controle_ponto_feriados');
            Route::post('feriados/search', [\App\Http\Controllers\FeriadoController::class, 'searchFeriado'])->name('search')->middleware('can:controle_ponto_feriados');
            Route::post('feriados/atualizar', [\App\Http\Controllers\FeriadoController::class, 'atualizar'])->name('atualizar')->middleware('can:controle_ponto_feriados');
        });
        Route::resource('feriados', \App\Http\Controllers\FeriadoController::class)->middleware('can:controle_ponto_feriados');

        //Perimetro
        Route::group(['as' => 'perimetros.'], function () {
            Route::post('perimetros/atualizarPerimetros', [\App\Http\Controllers\PerimetroController::class, 'atualizarPerimetros'])->name('atualizarPerimetros')->middleware('can:controle_ponto_perimetros');
            Route::put('perimetros/assosicarPerimetro', [\App\Http\Controllers\PerimetroController::class, 'assosicarPerimetro'])->name('assosicarPerimetro')->middleware('can:controle_ponto_perimetros_funcionarios');
        });
        Route::resource('perimetros', \App\Http\Controllers\PerimetroController::class, ['parameters' => ['perimetros' => 'perimetro']])->middleware('can:controle_ponto_perimetros');

        //Ocorrencais jornadas
        Route::group(['as' => 'ocorrencias_jornadas.'], function () {
            Route::get('ocorrencias_jornadas/buscar', [\App\Http\Controllers\OcorrenciaJornadaController::class, 'buscaCategoria'])->name('buscar')->middleware('can:controle_ponto_ocorrencias_jornadas');
            Route::post('ocorrencias_jornadas/atualizar', [\App\Http\Controllers\OcorrenciaJornadaController::class, 'atualizar'])->name('atualizar')->middleware('can:controle_ponto_ocorrencias_jornadas');
        });
        Route::resource('ocorrencias_jornadas', \App\Http\Controllers\OcorrenciaJornadaController::class, ['parameters' => ['ocorrencias_jornadas' => 'ocorrencia_jornada']])->middleware('can:controle_ponto_ocorrencias_jornadas');

        //Escalas
        Route::group(['as' => 'escalas.'], function () {
            Route::post('escalas/atualizarEscalas', [\App\Http\Controllers\EmpresaEscalaController::class, 'atualizarEscalas'])->name('atualizarEscalas')->middleware('can:controle_ponto_escalas');
            Route::put('escalas/assosicarEscalas', [\App\Http\Controllers\EmpresaEscalaController::class, 'assosicarEscalas'])->name('assosicarEscalas')->middleware('can:controle_ponto_escalas_funcionarios');

            Route::post('escalas/atualizarFuncionarios', [\App\Http\Controllers\EmpresaEscalaController::class, 'atualizarFuncionarios'])->name('atualizarFuncionarios')->middleware('can:controle_ponto_escalas');
            Route::get('escalas/getPermissoes', [\App\Http\Controllers\EmpresaEscalaController::class, 'getPermissoes'])->name('getPermissoes');

        });
        Route::resource('escalas', \App\Http\Controllers\EmpresaEscalaController::class, ['parameters' => ['escalas' => 'escala']])->middleware('can:controle_ponto_escalas');


        //Ponto eletronico
        Route::group(['as' => 'ponto-eletronico.'], function () {
            Route::get('ponto-eletronico/fotos/{arquivo}', [\App\Http\Controllers\PontoEletronicoController::class, 'fotoShow'])->name('fotoShow')->middleware('can:controle_ponto_ponto-eletronico');
            Route::get('ponto-eletronico/init', [\App\Http\Controllers\PontoEletronicoController::class, 'init'])->name('init')->middleware('can:controle_ponto_ponto-eletronico');
            Route::post('ponto-eletronico/atualizarHistorico', [\App\Http\Controllers\PontoEletronicoController::class, 'atualizarHistorico'])->name('atualizarHistorico')->middleware('can:controle_ponto_ponto-eletronico');
            Route::get('ponto-eletronico/{ponto}/{periodo}', [\App\Http\Controllers\PontoEletronicoController::class, 'showPeriodo'])->name('showPeriodo')->middleware('can:controle_ponto_ponto-eletronico');
        });
        Route::resource('ponto-eletronico', \App\Http\Controllers\PontoEletronicoController::class, ['parameters' => ['ponto-eletronico' => 'ponto']])->middleware('can:controle_ponto_ponto-eletronico');

        //Ajustar jornada
        Route::group(['as' => 'ajustar-jornadas.'], function () {
            Route::post('ajustar-jornadas/atualizaJornadasVerificadas', [\App\Http\Controllers\AjusteJornadaController::class, 'atualizaJornadasVerificadas'])->name('atualizaJornadasVerificadas')->middleware('can:controle_ponto_ajustar-jornadas');
            Route::post('ajustar-jornadas/atualizaJornadasPendentes', [\App\Http\Controllers\AjusteJornadaController::class, 'atualizaJornadasPendentes'])->name('atualizaJornadasPendentes')->middleware('can:controle_ponto_ajustar-jornadas');
            Route::post('ajustar-jornadas/atualizaJornadasIncompletas', [\App\Http\Controllers\AjusteJornadaController::class, 'atualizaJornadasIncompletas'])->name('atualizaJornadasIncompletas')->middleware('can:controle_ponto_ajustar-jornadas');
        });
        Route::resource('ajustar-jornadas', \App\Http\Controllers\AjusteJornadaController::class, ['parameters' => ['ajustar-jornadas' => 'ponto']])->middleware('can:controle_ponto_ajustar-jornadas');

        //Folha de ponto
        Route::group(['as' => 'folha-ponto.'], function () {
            Route::post('folha-ponto/atualizarLista', [\App\Http\Controllers\FolhaDePontoController::class, 'atualizarLista'])->name('atualizarLista')->middleware('can:controle_ponto_folha-ponto');
            Route::post('folha-ponto/{user}/frequencia', [\App\Http\Controllers\FolhaDePontoController::class, 'buscarFrequencia'])->name('buscarFrequencia')->middleware('can:controle_ponto_folha-ponto');
            Route::post('folha-ponto/{user}/imprimir', [\App\Http\Controllers\FolhaDePontoController::class, 'imprimir'])->name('imprimir')->middleware('can:controle_ponto_folha-ponto');
            Route::get('folha-ponto/relatorio-sintetico', [\App\Http\Controllers\FolhaDePontoController::class, 'relatoriosintetico'])->name('relatoriosintetico')->middleware('can:controle_ponto_folha-ponto');
            Route::get('folha-ponto/relatorio-sintetico/exportacao', [\App\Http\Controllers\FolhaDePontoController::class, 'relatoriosinteticoexportacao'])->name('relatoriosinteticoexportacao')->middleware('can:controle_ponto_relatorio_sintetico');
        });
        Route::resource('folha-ponto', \App\Http\Controllers\FolhaDePontoController::class, ['parameters' => ['folha-ponto' => 'user']])->middleware('can:controle_ponto_folha-ponto');

        Route::group(['as' => 'folha-manual.', 'prefix' => 'folha-manual'], function () {
            Route::post('atualizar', [\App\Http\Controllers\FolhaManualController::class, 'atualizar'])->name('folha-manual')->middleware('can:controle_ponto_folha_ponto_manual');
            Route::post('imprimir', [\App\Http\Controllers\FolhaManualController::class, 'imprimir'])->name('folha-manual_imprimir')->middleware('can:controle_ponto_folha_ponto_manual');
            Route::get('', [\App\Http\Controllers\FolhaManualController::class, 'index'])->name('index')->middleware('can:controle_ponto_folha_ponto_manual');
        });

    });

    //Weekley report
    Route::group(['as' => 'weekly-report.'], function () {

        //Anexos s3
        Route::get('weekly-report/anexo/{arquivo}', [\App\Http\Controllers\TarefasController::class, 'anexoShow'])->name('anexo-tarefa.anexo-show');
        Route::get('weekly-report/anexoDownload/{arquivo}', [\App\Http\Controllers\TarefasController::class, 'download'])->name('anexo-tarefa.anexo-download');
        Route::delete('weekly-report/anexo/{arquivo}', [\App\Http\Controllers\TarefasController::class, 'anexoDelete'])->name('anexo-tarefa.anexo-delete');


        //Itens
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/checklist/{checklist}/item/{item}', [\App\Http\Controllers\ChecklistsTarefaItemController::class, 'update'])->name('update');
        Route::delete('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/checklist/{checklist}/item/{item}', [\App\Http\Controllers\ChecklistsTarefaItemController::class, 'destroy'])->name('destroy');
        Route::post('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/checklist/{checklist}/item', [\App\Http\Controllers\ChecklistsTarefaItemController::class, 'store'])->name('store');

        //Checklists
        Route::delete('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/checklist/{checklist}', [\App\Http\Controllers\ChecklistsTarefaController::class, 'destroy'])->name('destroy');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/checklist/{checklist}/atualizarOrdemItens', [\App\Http\Controllers\ChecklistsTarefaController::class, 'atualizarOrdemItens'])->name('atualizarOrdemItens');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/checklist/{checklist}', [\App\Http\Controllers\ChecklistsTarefaController::class, 'update'])->name('update');
        Route::post('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/checklist', [\App\Http\Controllers\ChecklistsTarefaController::class, 'store'])->name('store');

        // Anexos
        Route::post('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/uploadAnexos', [\App\Http\Controllers\TarefasController::class, 'uploadAnexos'])->name('anexo-tarefa.upload-anexos');
        Route::get('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/anexo/{arquivo}', [\App\Http\Controllers\TarefasController::class, 'anexoShow'])->name('anexo-tarefa.anexo-show');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/anexo/{arquivo}', [\App\Http\Controllers\TarefasController::class, 'anexoUpdate'])->name('anexo-tarefa.anexo-show');
        Route::get('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/anexoDownload/{arquivo}', [\App\Http\Controllers\TarefasController::class, 'download'])->name('anexo-tarefa.anexo-download');
        Route::delete('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/anexo/{arquivo}', [\App\Http\Controllers\TarefasController::class, 'anexoDelete'])->name('anexo-tarefa.anexo-delete');

        //Tarefa

        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/atualizarOrdemCheckList', [\App\Http\Controllers\TarefasController::class, 'atualizarOrdemCheckList'])->name('atualizarOrdemCheckList');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/updateDataHoraInicio', [\App\Http\Controllers\TarefasController::class, 'updateDataHoraInicio'])->name('updateDataHoraInicio');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/updateDataHoraEntrega', [\App\Http\Controllers\TarefasController::class, 'updateDataHoraEntrega'])->name('updateDataHoraEntrega');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/updateMembro', [\App\Http\Controllers\TarefasController::class, 'updateMembro'])->name('updateMembro');
        Route::get('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}/buscarMembros', [\App\Http\Controllers\AutoCompletesController::class, 'buscarMembros'])->name('buscarMembros');

        Route::delete('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}', [\App\Http\Controllers\TarefasController::class, 'destroy'])->name('delete')->middleware('can:weekly_report_quadro_tarefa_delete');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}', [\App\Http\Controllers\TarefasController::class, 'update'])->name('update')->middleware('can:weekly_report_quadro_tarefa_update');
        Route::get('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}', [\App\Http\Controllers\TarefasController::class, 'show'])->name('show');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas', [\App\Http\Controllers\TarefasController::class, 'atualizarOrdem'])->name('atualizarOrdem');
        Route::post('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas', [\App\Http\Controllers\TarefasController::class, 'store'])->name('store')->middleware('can:weekly_report_quadro_tarefa_insert');

        //Listas
        Route::delete('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}', [\App\Http\Controllers\ListaTarefaController::class, 'destroy'])->name('delete')->middleware('can:weekly_report_quadro_lista_delete');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}', [\App\Http\Controllers\ListaTarefaController::class, 'update'])->name('update')->middleware('can:weekly_report_quadro_lista_update');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas', [\App\Http\Controllers\ListaTarefaController::class, 'atualizarOrdem'])->name('atualizarOrdem');
        Route::post('weekly-report/{empresa}/quadros/{quadro}/listas', [\App\Http\Controllers\ListaTarefaController::class, 'store'])->name('store')->middleware('can:weekly_report_quadro_lista_insert');
        Route::get('weekly-report/{empresa}/quadros/{quadro}/listas', [\App\Http\Controllers\ListaTarefaController::class, 'index'])->name('index');

        //Quadros
        Route::delete('weekly-report/{empresa}/quadros/{quadro}', [\App\Http\Controllers\QuadroController::class, 'destroy'])->name('delete')->middleware('can:weekly_report_quadro_delete');
        Route::put('weekly-report/{empresa}/quadros/{quadro}', [\App\Http\Controllers\QuadroController::class, 'update'])->name('update')->middleware('can:weekly_report_quadro_update');
        Route::post('weekly-report/{empresa}/quadros', [\App\Http\Controllers\QuadroController::class, 'store'])->name('store')->middleware('can:weekly_report_quadro_insert');
        Route::get('weekly-report/{empresa}', [\App\Http\Controllers\QuadroController::class, 'show'])->name('show')->middleware('can:weekly_report');
        Route::get('weekly-report', [\App\Http\Controllers\QuadroController::class, 'index'])->name('index')->middleware('can:weekly_report');
    });

    //Chat
    Route::group(['as' => 'chat.'], function () {

        Route::post('chat/{empresa}/carregarMaisMensagens', [\App\Http\Controllers\MensagemChatController::class, 'carregarMaisMensagens'])->name('carregarMaisMensagens');
        Route::put('chat/{empresa}/visualizarMensagem', [\App\Http\Controllers\MensagemChatController::class, 'visualizarMensagem'])->name('visualizarMensagem');
        Route::post('chat/{empresa}/enviarMensagem', [\App\Http\Controllers\MensagemChatController::class, 'store'])->name('store');
        Route::get('chat/{empresa}/buscaContato', [\App\Http\Controllers\ChatController::class, 'buscarContato'])->name('buscarContato');
        Route::get('chat/{empresa}', [\App\Http\Controllers\ChatController::class, 'show'])->name('show');
        Route::get('chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('index');
    });

    //Notificaçoes
    Route::group(['as' => 'notificacoes.'], function () {

        Route::post('notificacoes/{usuario}', [\App\Http\Controllers\NotificacoesController::class, 'marcarVisto'])->name('marcarVisto');
        Route::get('notificacoes/{usuario}', [\App\Http\Controllers\NotificacoesController::class, 'getUpdate'])->name('getUpdate');
    });

    // Configurações
    Route::group(['as' => 'configuracoes.'], function () {
        // Habilidades
        Route::post('habilidades/atualizar', [\App\Http\Controllers\HabilidadesController::class, 'atualizar'])->name('habilidades.atualizar')->middleware('can:configuracao_habilidades'); // manter essa rota antes do resource
        Route::resource('habilidades', \App\Http\Controllers\HabilidadesController::class)->middleware('can:configuracao_habilidades');
        // Papeis
        Route::put('papeis/{papel}/ativa-desativa', [\App\Http\Controllers\PapeisController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:configuracao_papel');
        Route::post('papeis/atualizar', [\App\Http\Controllers\PapeisController::class, 'atualizar'])->name('papeis.atualizar')->middleware('can:configuracao_papel');
        Route::resource('papeis', \App\Http\Controllers\PapeisController::class, ['parameters' => ['papeis' => 'papel']])->middleware('can:configuracao_papel');
    });

    // Clinica Exame
    Route::group(['as' => 'acesso-clinica.'], function () {
        Route::post('acesso-clinica/atualizar', [\App\Http\Controllers\Clinica\ControleExamesController::class, 'atualizar'])->name('atualizar')->middleware('can:acesso_clinica');
        Route::resource('acesso-clinica', \App\Http\Controllers\Clinica\ControleExamesController::class)->middleware('can:acesso_clinica');
    });

});


//Rotas de provas protegidas
Route::group(['as' => 'provas.'], function () {
    //Provas
    Route::post('provas/autenticar', [\App\Http\Controllers\SimuladoCandidatoController::class, 'autenticar'])->name('prova.autenticar');
    Route::post('prova/get-simulado', [\App\Http\Controllers\SimuladoCandidatoController::class, 'getSimulado'])->name('prova.atualiza');
    Route::post('prova/grava-tempo', [\App\Http\Controllers\SimuladoCandidatoController::class, 'gravaTempo'])->name('prova.gravaTempo');
    Route::post('prova/responder', [\App\Http\Controllers\SimuladoCandidatoController::class, 'responder'])->name('prova.responder');
    Route::post('prova/finalizar', [\App\Http\Controllers\SimuladoCandidatoController::class, 'finalizar'])->name('prova.finalizar');

    Route::post('prova/salvar-vinculo', [\App\Http\Controllers\SimuladoCandidatoController::class, 'salvarVinculo'])->name('prova.salvarVinculo');


    Route::get('provas/{empresa_id}/{vagas_abertas_id}/{simulado_id}', [\App\Http\Controllers\SimuladoCandidatoController::class, 'index'])->name('prova.simulado');

});

//Rotas de pre-admissao protegidas
Route::group(['as' => 'documentospreadmissao.'], function () {
    //Provas
    Route::group(['prefix' => '{apelido}'], function () {
        Route::post('documentos-pre-admissao/autenticar', [\App\Http\Controllers\DocumentosPreAdmissaoController::class, 'autenticar'])->name('documentos.autenticar');
        Route::get('documentos', [\App\Http\Controllers\DocumentosPreAdmissaoController::class, 'index'])->name('documentos.index');
        Route::put('documentos/{curriculo_id}', [\App\Http\Controllers\DocumentosPreAdmissaoController::class, 'update'])->name('documentos.update');


        Route::get('carta-oferta/{token}', [\App\Http\Controllers\CartaOfertaController::class, 'index'])->name('carta-oferta.index');
        Route::post('carta-oferta/{token}/salvar', [\App\Http\Controllers\CartaOfertaController::class, 'salvarCartaOferta'])->name('carta-oferta.salvarCartaOferta');

    });

    Route::post('documentos/uploadAnexos', [\App\Http\Controllers\DocumentosPreAdmissaoController::class, 'uploadAnexos'])->name('documentos.upload-anexos');
    Route::get('documentos/anexo/{arquivo}', [\App\Http\Controllers\DocumentosPreAdmissaoController::class, 'anexoShow'])->name('documentos.anexo-show');
    Route::get('documentos/anexoDownload/{arquivo}', [\App\Http\Controllers\DocumentosPreAdmissaoController::class, 'download'])->name('documentos.anexo-download');
    Route::delete('documentos/anexo/{arquivo}', [\App\Http\Controllers\DocumentosPreAdmissaoController::class, 'anexoDelete'])->name('documentos.anexo-delete');
});

//Rotas de pesquisa clima protegidas
Route::group(['as' => 'pesquisaclima.'], function () {
    //Provas
    Route::post('pesquisaclima/autenticar', [\App\Http\Controllers\PesquisaClimaController::class, 'autenticar'])->name('pesquisaclima.autenticar');

    Route::get('pesquisaclima', [\App\Http\Controllers\PesquisaClimaController::class, 'index'])->name('pesquisaclima.index');
    Route::post('pesquisaclima', [\App\Http\Controllers\PesquisaClimaController::class, 'store'])->name('pesquisaclima.store');
});
