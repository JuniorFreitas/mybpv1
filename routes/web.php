<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', 'g/login');

Route::group(['prefix' => 'publico', 'as' => 'publico.'], function () {
    Route::get('controle-exames/ficha-encaminhamento/{exame}/{token}', [\App\Http\Controllers\ControleExameController::class, 'getFichaPdf'])->name('encaminhamento_exame_fichapdf');
    Route::post('cnpjbusca', [\App\Http\Controllers\PublicoController::class, 'cnpjbusca'])->name('cnpjbusca');
    Route::get('lista-vagas', [\App\Http\Controllers\PublicoController::class, 'listaVagas'])->name('lista-vagas');
    Route::get('lista-areas', [\App\Http\Controllers\PublicoController::class, 'listaAreasEtiquetas'])->name('lista-areas');
    Route::get('lista-areas/{cliente}', [\App\Http\Controllers\PublicoController::class, 'listaAreasEtiquetasCliente'])->name('lista-areas-cliente');
    Route::post('centro-custos', [\App\Http\Controllers\PublicoController::class, 'listaCentroCusto'])->name('lista-centro');
    Route::post('upload', [\App\Http\Controllers\PublicoController::class, 'upload'])->name('upload');
    Route::get('foto/{nome}', [\App\Http\Controllers\PublicoController::class, 'download'])->name('foto-download');

    Route::get('cloud/anexo/{arquivo}', [\App\Http\Controllers\CloudController::class, 'anexoShow'])->name('cloud.anexo-show');
//    Route::get('cloud/{arquivo}', [\App\Http\Controllers\CloudController::class,'download'])->name('cloud.anexo-download')->middleware(['auth', 'habilidades', 'can:cloud']);
    Route::get('cloud/anexoDownload/{arquivo}', [\App\Http\Controllers\CloudController::class, 'download'])->name('cloud.anexo-download');
    Route::delete('cloud/anexo/{arquivo}', [\App\Http\Controllers\CloudController::class, 'anexoDelete'])->name('cloud.anexo-delete')->middleware(['auth', 'habilidades', 'can:cloud']);


    /*Route::get('cloud/anexo/{arquivo}', [\App\Http\Controllers\CloudController::class, 'anexoShow'])->name('cloud.anexo-show');
//    Route::get('cloud/{arquivo}', [\App\Http\Controllers\CloudController::class,'download'])->name('cloud.anexo-download')->middleware(['auth', 'habilidades', 'can:cloud']);
    Route::get('cloud/anexoDownload/{arquivo}', [\App\Http\Controllers\CloudController::class, 'download'])->name('cloud.anexo-download');
    Route::delete('cloud/anexo/{arquivo}', [\App\Http\Controllers\CloudController::class, 'anexoDelete'])->name('cloud.anexo-delete')->middleware(['auth', 'habilidades', 'can:cloud']);*/

});

Route::group(['prefix' => 'g'], function () {
    // Authentication Routes...
    Route::get('login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('sair', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    // Registration Routes...
    Route::get('register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('auth', 'habilidades');
    Route::post('register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->middleware('auth', 'habilidades');
    // Password Reset Routes...
    Route::get('password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset']);
});

Route::get('carteira/{curriculo}', [\App\Http\Controllers\TreinamentoController::class, 'carteiraIndividual'])->name('treinamento.carteira');
Route::group(['prefix' => '3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS'], function () {
    Route::get('verificaCliente', [\App\Http\Controllers\ClientesController::class, 'clientesProximoVencimento'])->name('vencimentoClientes');
    Route::get('recrutamentos/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', [\App\Http\Controllers\RecrutamentoController::class, 'export'])->name('recrutamentos.excel');
    Route::get('parecer_rh/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', [\App\Http\Controllers\ParecerRhController::class, 'export'])->name('parecerrh.excel');
    Route::get('parecer_rota_transporte/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', [\App\Http\Controllers\ParecerRotaController::class, 'export'])->name('parecer_rota_transporte.excel');
    Route::get('parecer_entrevista_tecnica/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', [\App\Http\Controllers\ParecerEntrevistaTecnicaController::class, 'export'])->name('parecer_entrevista_tecnica.excel');
    Route::get('parecer_teste_pratico/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', [\App\Http\Controllers\ParecerTestePraticoController::class, 'export'])->name('parecer_teste_pratico.excel');

    /* Route::get('resultado_integrado/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'ResultadoIntegradoController@export')->name('resultado_integrado.excel');
     Route::get('admissao/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'AdmissaoController@export')->name('admissao.excel');
     Route::get('clientes/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'ClientesController@export')->name('clientes.excel');
     Route::get('portaria/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'PortariaController@export')->name('portaria.excel');
     Route::get('carteira-etiqueta/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'TreinamentoController@export')->name('carteira.excel');

     Route::get('parecer_entrevista_rh/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'EntrevistaRhClienteController@export')->name('parecerentrevistarh.excel');
     Route::get('parecer_gestor_rh/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS', 'EntrevistaGestorClienteController@export')->name('gestor_rh.excel');*/
});


Route::group(['middleware' => ['auth', 'habilidades'], 'as' => 'g.', 'prefix' => 'g'], function () {

    //ROTAS DE FORMULARIO
    Route::group(['as' => 'fomulario.', 'prefix' => 'formulario'], function () {
        Route::get('/carregaResposta', [\App\Http\Controllers\FormularioController::class, 'carregaResposta'])->name('carregaResposta');
        Route::get('/{formulario}', [\App\Http\Controllers\FormularioController::class, 'carrega'])->name('carrega');
//        Route::get('carregaResposta/{resposta}', [\App\Http\Controllers\FormularioController::class, 'carregaResposta'])->name('carregaResposta');

    });

    Route::get('dashboard', [\App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');
    Route::put('concordarTermos', [\App\Http\Controllers\HomeController::class, 'concordarTermos'])->name('concordarTermos');

    // AutoCompletes
    Route::group(['as' => 'autocompletes.', 'prefix' => 'autocomplete'], function () {
        //Auto completes
        Route::get('todas-vagas-ativas', [\App\Http\Controllers\AutoCompletesController::class, 'vagasAtivas'])->name('vagas-ativas');
        Route::get('todos-clientes-ativos', [\App\Http\Controllers\AutoCompletesController::class, 'clientesAtivos'])->name('clientes-ativos');
        Route::get('todos-usuarios-ativos', [\App\Http\Controllers\AutoCompletesController::class, 'usuariosAtivos'])->name('usuarios-ativos');
        Route::get('todos-municipios', [\App\Http\Controllers\AutoCompletesController::class, 'municipiosAll'])->name('municipiosAll');
        Route::get('todos-estados', function () {
            return response()->json(\App\Models\Municipio::todosEstados(), 200);
        });
        Route::get('colaboradorCih', [\App\Http\Controllers\AutoCompletesController::class, 'colaboradorCih'])->name('colaboradorCih');
        Route::get('colaboradorIntermitente', [\App\Http\Controllers\AutoCompletesController::class, 'colaboradorIntermitente'])->name('colaboradorIntermitente');
        Route::get('colaboradores/{cliente_id}', [\App\Http\Controllers\AutoCompletesController::class, 'colaboradores'])->name('colaboradores');
        Route::get('cargosEmpresa/{cliente}', [\App\Http\Controllers\AutoCompletesController::class, 'cargosEmpresa'])->name('cargosEmpresa');
        Route::get('colaboradorIntermitente/{cliente_id}', [\App\Http\Controllers\AutoCompletesController::class, 'colaboradorIntermitenteCliente'])->name('colaboradorIntermitenteCliente');

        Route::get('funcionarios', [\App\Http\Controllers\AutoCompletesController::class, 'funcionarios'])->name('funcionarios');

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
        Route::post('clientes/uploadAnexos', [\App\Http\Controllers\ClientesController::class, 'uploadAnexos'])->name('clientes.upload-anexos')->middleware('can:clientes');
        Route::get('clientes/anexo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'anexoShow'])->name('clientes.anexo-show')->middleware('can:clientes');
        Route::get('clientes/anexoDownload/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'logoDownload'])->name('clientes.anexo-download')->middleware('can:clientes');
        Route::delete('clientes/anexo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'anexoDelete'])->name('clientes.anexo-delete')->middleware('can:clientes');

        // Anexos Logo
        Route::post('clientes/uploadLogo', [\App\Http\Controllers\ClientesController::class, 'uploadLogo'])->name('clientes.upload-logo')->middleware('can:clientes');
        Route::get('clientes/logo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'logoShow'])->name('clientes.logo-show')->middleware('can:clientes');
        Route::get('clientes/logoDownload/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'download'])->name('clientes.logo-download')->middleware('can:clientes');
        Route::delete('clientes/logo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'logoDelete'])->name('clientes.logo-delete')->middleware('can:clientes');

//            Route::get('clientes/export', [\App\Http\Controllers\ClientesController::class,'export'])->name('clientes.excel')->middleware('can:clientes');
        Route::get('clientes/buscar-cnpj', [\App\Http\Controllers\ClientesController::class, 'buscaCNPJ'])->name('clientes.verifica-cnpj')->middleware('can:clientes');
        Route::get('clientes/buscar-cpf', [\App\Http\Controllers\ClientesController::class, 'buscaCPF'])->name('clientes.verifica-cpf')->middleware('can:clientes');

        // Clientes
        Route::group(['as' => 'clientes.'], function () {
            // Anexos
            Route::post('clientes/uploadAnexos', [\App\Http\Controllers\ClientesController::class, 'uploadAnexos'])->name('upload-anexos')->middleware('can:clientes');
            Route::get('clientes/anexo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'anexoShow'])->name('anexo-show')->middleware('can:clientes');
            Route::get('clientes/anexoDownload/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'download'])->name('anexo-download')->middleware('can:clientes');
            Route::delete('clientes/anexo/{arquivo}', [\App\Http\Controllers\ClientesController::class, 'anexoDelete'])->name('anexo-delete')->middleware('can:clientes');

//            Route::get('clientes/export', [\App\Http\Controllers\ClientesController::class,'export'])->name('excel')->middleware('can:clientes');
            Route::get('clientes/buscar-cnpj', [\App\Http\Controllers\ClientesController::class, 'buscaCNPJ'])->name('verifica-cnpj')->middleware('can:clientes');
            Route::get('clientes/buscar-cpf', [\App\Http\Controllers\ClientesController::class, 'buscaCPF'])->name('verifica-cpf')->middleware('can:clientes');

            Route::get('clientes/{cliente}/pdf', [\App\Http\Controllers\ClientesController::class, 'getFichaPdf'])->name('getFichapdf');
            Route::put('clientes/{cliente}/ativa-desativa', [\App\Http\Controllers\ClientesController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:clientes');
            Route::post('clientes/search', [\App\Http\Controllers\ClientesController::class, 'searchCliente'])->name('search')->middleware('can:clientes');
            Route::post('clientes/atualizar', [\App\Http\Controllers\ClientesController::class, 'atualizar'])->name('atualizar')->middleware('can:clientes');
            Route::resource('clientes', \App\Http\Controllers\ClientesController::class)->middleware('can:clientes');
        });

        // Fornecedores
        Route::group(['as' => 'fornecedor.'], function () {
            // Anexos Fornecedor
            Route::post('fornecedor/uploadAnexos', [\App\Http\Controllers\FornecedorController::class, 'uploadAnexos'])->name('upload-anexos')->middleware('can:fornecedores');
            Route::get('fornecedor/anexo/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'anexoShow'])->name('anexo-show')->middleware('can:fornecedores');
            Route::get('fornecedor/anexoDownload/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'download'])->name('anexo-download')->middleware('can:fornecedores');
            Route::delete('fornecedor/anexo/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'anexoDelete'])->name('anexo-delete')->middleware('can:fornecedores');

            // Anexos Fornecedor
            Route::post('fornecedor/servico/uploadAnexos', [\App\Http\Controllers\FornecedorController::class, 'uploadServicoAnexos'])->name('upload-anexos-servico')->middleware('can:fornecedores');
            Route::get('fornecedor/servico/anexo/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'anexoServicoShow'])->name('anexo-servico-show')->middleware('can:fornecedores');
            Route::get('fornecedor/servico/anexoDownload/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'downloadServico'])->name('anexo-servico-download')->middleware('can:fornecedores');
            Route::delete('fornecedor/servico/anexo/{arquivo}', [\App\Http\Controllers\FornecedorController::class, 'anexoServicoDelete'])->name('anexo-servico-delete')->middleware('can:fornecedores');
            // Anexos Serviços

            //Comuns Fornecedores
            Route::get('fornecedor/buscar-cnpj', [\App\Http\Controllers\FornecedorController::class, 'buscaCNPJ'])->name('verifica-cnpj')->middleware('can:fornecedores');
            Route::get('fornecedor/buscar-cpf', [\App\Http\Controllers\FornecedorController::class, 'buscaCPF'])->name('verifica-cpf')->middleware('can:fornecedores');
            Route::put('fornecedor/{fornecedor}/ativa-desativa', [\App\Http\Controllers\FornecedorController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:fornecedores');
            Route::post('fornecedor/search', [\App\Http\Controllers\FornecedorController::class, 'searchCliente'])->name('search')->middleware('can:fornecedores');
            Route::post('fornecedor/atualizar', [\App\Http\Controllers\FornecedorController::class, 'atualizar'])->name('atualizar')->middleware('can:fornecedores');
            Route::resource('fornecedor', \App\Http\Controllers\FornecedorController::class)->middleware('can:fornecedores');
        });

        //Ata de Reunião
        Route::group(['as' => 'atareuniao.'], function () {
            Route::post('atareuniao/atualizar', [\App\Http\Controllers\AtaReuniaoController::class, 'atualizar'])->name('atualizarAtaReuniao')->middleware('can:atareuniao');
            Route::get('atareuniao/pdf/{item}', [\App\Http\Controllers\AtaReuniaoController::class, 'pdf'])->name('pdfAtaReuniao')->middleware('can:atareuniao');
            Route::resource('atareuniao', \App\Http\Controllers\AtaReuniaoController::class)->middleware('can:atareuniao');
        });

        //Pesquisa de Clima
        Route::group(['as' => 'pesquisaclima.'], function () {
            Route::get('pesquisaclima/atualizar', [\App\Http\Controllers\PesquisaClimaController::class, 'atualizar'])->name('atualizar')->middleware('can:pesquisaclima');
            Route::get('pesquisaclima/pdf/{item}', [\App\Http\Controllers\PesquisaClimaController::class, 'pdf'])->name('pdfPesquisaClima')->middleware('can:pesquisaclima');
            Route::get('pesquisaclima', [\App\Http\Controllers\PesquisaClimaController::class, 'indexAdm'])->name('indexAdm')->middleware('can:pesquisaclima');
            Route::get('pesquisaclima/chart/{cliente_id}', [\App\Http\Controllers\PesquisaClimaController::class, 'chart'])->name('chart')->middleware('can:pesquisaclima');
            Route::get('pesquisaclima/contador', [\App\Http\Controllers\PesquisaClimaController::class, 'contador'])->name('contador')->middleware('can:pesquisaclima');
        });

        //Planejamento Diario
        Route::group(['as' => 'planejamentodiario.'], function () {
            Route::post('planejamentodiario/atualizar', [\App\Http\Controllers\PlanejamentoDiarioController::class, 'atualizar'])->name('atualizarPlanejamentoDiario')->middleware('can:planejamentodiario');
            Route::resource('planejamentodiario', \App\Http\Controllers\PlanejamentoDiarioController::class)->middleware('can:planejamentodiario');
        });

        //Aniversariantes
        Route::group(['as' => 'aniversariantes.'], function () {
            Route::post('aniversariantes/atualizar', [\App\Http\Controllers\AniversariantesController::class, 'atualizar'])->name('atualizar')->middleware('can:aniversariantes');
            Route::post('aniversariantes/enviaEmail', [\App\Http\Controllers\AniversariantesController::class, 'enviaEmail'])->name('enviaEmail')->middleware('can:aniversariantes');
            Route::resource('aniversariantes', \App\Http\Controllers\AniversariantesController::class)->middleware('can:aniversariantes');
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

        Route::group(['as' => 'beneficios.'], function () {
            Route::put('beneficios/{beneficio}/ativa-desativa', [\App\Http\Controllers\BeneficioController::class, 'ativaDesativa'])->name('beneficios.ativaDesativa')->middleware('can:beneficio');
            Route::get('beneficios/{tipobeneficio}/editarTipo', [\App\Http\Controllers\BeneficioController::class, 'editarTipo'])->name('beneficios.editarTipo')->middleware('can:beneficio');
            Route::put('/beneficios/updateTipos/{tipobeneficio}', [\App\Http\Controllers\BeneficioController::class, 'updateTipo'])->name('beneficios.updateTipo')->middleware('can:beneficio');
            Route::post('beneficios/atualizar', [\App\Http\Controllers\BeneficioController::class, 'atualizar'])->name('beneficios.atualizar')->middleware('can:beneficio'); // manter essa rota antes do resource
            Route::post('beneficios/atualizar-historico', [\App\Http\Controllers\BeneficioController::class, 'atualizarHistorico'])->name('beneficios.atualizarHistorico')->middleware('can:beneficio'); // manter essa rota antes do resource
            Route::post('beneficios/cadastro-tipo', [\App\Http\Controllers\BeneficioController::class, 'cadastroTipo'])->name('beneficios.cadastroTipo')->middleware('can:beneficio'); // manter essa rota antes do resource
            Route::resource('beneficios', \App\Http\Controllers\BeneficioController::class)->middleware('can:beneficio');
        });

        // Vagas
        Route::group(['as' => 'vagas.'], function () {
            Route::put('vagas/{vaga}/ativa-desativa', [\App\Http\Controllers\VagaController::class, 'ativaDesativa'])->name('vagas.ativaDesativa')->middleware('can:vagas_update');
            Route::post('vagas/atualizar', [\App\Http\Controllers\VagaController::class, 'atualizar'])->name('vagas.atualizar')->middleware('can:vagas');
            Route::resource('vagas', \App\Http\Controllers\VagaController::class)->middleware('can:vagas');

            Route::put('vagas-abertas/{vagas_aberta}/ativa-desativa', [\App\Http\Controllers\VagasAbertasController::class, 'ativaDesativa'])->name('vagas_abertas.ativaDesativa')->middleware('can:vagas_abertas_update');
            Route::post('vagas-abertas/atualizar', [\App\Http\Controllers\VagasAbertasController::class, 'atualizar'])->name('vagas_abertas.atualizar')->middleware('can:vagas_abertas');
            Route::resource('vagas-abertas', \App\Http\Controllers\VagasAbertasController::class)->middleware('can:vagas_abertas');
        });

        Route::group(['as' => 'areas.'], function () {
            Route::put('areas/{area}/ativa-desativa', [\App\Http\Controllers\AreaEtiquetasController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:areaetiqueta');
            Route::post('areas/atualizar', [\App\Http\Controllers\AreaEtiquetasController::class, 'atualizar'])->name('atualizar')->middleware('can:areaetiqueta');
            Route::resource('areas', \App\Http\Controllers\AreaEtiquetasController::class)->middleware('can:areaetiqueta');
        });

        Route::group(['as' => 'centrocusto.'], function () {
            Route::put('centrocusto/{centrocusto}/ativa-desativa', [\App\Http\Controllers\CentroCustoController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:centrocusto');
            Route::post('centrocusto/atualizar', [\App\Http\Controllers\CentroCustoController::class, 'atualizar'])->name('atualizar')->middleware('can:centrocusto');
            Route::resource('centrocusto', \App\Http\Controllers\CentroCustoController::class)->middleware('can:centrocusto');
        });
    });

    //Planejamento
    Route::group(['prefix' => 'planejamento'], function () {
        Route::group(['as' => 'requisicao_vagas.'], function () {
            Route::post('requisicao-vaga/atualizar', [\App\Http\Controllers\RequisicaoVagaController::class, 'atualizar'])->name('atualizar')->middleware('can:requisicao_vaga');
            Route::resource('requisicao-vaga', \App\Http\Controllers\RequisicaoVagaController::class)->middleware('can:requisicao_vaga');
        });

        Route::group(['as' => 'movimentacao.', 'prefix' => 'movimentacao'], function () {

            Route::group(['as' => 'solicitacao_demissao.'], function () {
                Route::post('demissao-prevista/atualizar', [\App\Http\Controllers\DemissaoPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::resource('demissao-prevista', \App\Http\Controllers\DemissaoPrevistaController::class, ['parameters' => ['demissao-prevista' => 'demissao_prevista']]);
            });

            Route::group(['as' => 'solicitacao_ferias.'], function () {
                Route::post('ferias-prevista/atualizar', [\App\Http\Controllers\FeriasPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::resource('ferias-prevista', \App\Http\Controllers\FeriasPrevistaController::class, ['parameters' => ['ferias-prevista' => 'ferias_prevista']]);
            });

            Route::group(['as' => 'solicitacao_admissoes.'], function () {
                Route::post('admissoes-prevista/atualizar', [\App\Http\Controllers\AdmissoesPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::resource('admissoes-prevista', \App\Http\Controllers\AdmissoesPrevistaController::class, ['parameters' => ['admissoes-prevista' => 'admissoes_prevista']]);
            });

            Route::group(['as' => 'solicitacao_valor-extra.'], function () {
                Route::post('valor-extra-prevista/atualizar', [\App\Http\Controllers\ValorExtraPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::resource('valor-extra-prevista', \App\Http\Controllers\ValorExtraPrevistaController::class, ['parameters' => ['valor-extra-prevista' => 'valor_extra_prevista']]);
            });

            Route::group(['as' => 'solicitacao_cargo.'], function () {
                Route::post('muda-cargo-prevista/atualizar', [\App\Http\Controllers\MudaCargoPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::resource('muda-cargo-prevista', \App\Http\Controllers\MudaCargoPrevistaController::class, ['parameters' => ['muda-cargo-prevista' => 'muda_cargo_prevista']]);
            });

            Route::group(['as' => 'solicitacao_intermitente.'], function () {
                Route::post('intermitente-fixo-prevista/atualizar', [\App\Http\Controllers\IntermitenteFixoPrevistaController::class, 'atualizar'])->name('atualizar');
                Route::resource('intermitente-fixo-prevista', \App\Http\Controllers\IntermitenteFixoPrevistaController::class, ['parameters' => ['intermitente-fixo-prevista' => 'intermitente_fixo_prevista']]);
            });

            //Rota raiz
            Route::get('/', [\App\Http\Controllers\MovimentacaoController::class, 'index'])->name('index');
        });
    });

    //Curriculos
    Route::group(['prefix' => 'curriculos'], function () {
        // Recrutamento
        Route::group(['as' => 'recrutamento.'], function () {
            //Recrutamento colocar depois Middleware
            Route::get('recrutamentos/export', [\App\Http\Controllers\RecrutamentoController::class, 'export'])->name('recrutamentos.excel')->middleware('can:curriculos');
            //Lido
            Route::put('recrutamentos/{curriculo}/lido', [\App\Http\Controllers\RecrutamentoController::class, 'marcaLido'])->name('recrutamentos.marcaLido')->middleware('can:curriculos');
            Route::post('recrutamentos/search', [\App\Http\Controllers\RecrutamentoController::class, 'searchCliente'])->name('recrutamentos.search')->middleware('can:curriculos');
            Route::post('recrutamentos/atualizar', [\App\Http\Controllers\RecrutamentoController::class, 'atualizar'])->name('recrutamentos.atualizar')->middleware('can:curriculos');
            Route::resource('recrutamentos', \App\Http\Controllers\RecrutamentoController::class)->middleware('can:curriculos');
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
            Route::get('parecer_rh/export', [\App\Http\Controllers\ParecerRhController::class, 'export'])->name('excel')->middleware('can:parecer_rh');
            Route::post('parecer_rh/atualizar', [\App\Http\Controllers\ParecerRhController::class, 'atualizar'])->name('atualizar')->middleware('can:parecer_rh');
            Route::post('parecer_rh/ficha_pdf', [\App\Http\Controllers\ParecerRhController::class, 'getFichaPdf'])->name('getFichaPdf')->middleware('can:parecer_rh');
            Route::resource('parecer_rh', \App\Http\Controllers\ParecerRhController::class)->middleware('can:parecer_rh');
        });

        //Parecer Rota Transporte
        Route::group(['as' => 'parecer_rota_transporte.'], function () {
            Route::get('parecer-rota/export', [\App\Http\Controllers\ParecerRotaController::class, 'export'])->name('parecer_rota_transporte.excel')->middleware('can:parecer_rota');
            Route::post('parecer-rota/atualizar', [\App\Http\Controllers\ParecerRotaController::class, 'atualizar'])->name('parecer_rota_transporte.atualizar')->middleware('can:parecer_rota');
            Route::post('parecer-rota/ficha_pdf', [\App\Http\Controllers\ParecerRotaController::class, 'getFichaPdf'])->name('parecer_rota_transporte.getFichaPdf')->middleware('can:parecer_rota');
            Route::resource('parecer-rota', \App\Http\Controllers\ParecerRotaController::class, ['parameters' => ['parecer-rota' => 'parecer_rota']])->middleware('can:parecer_rota');
        });

        //Parecer Entrevista Técnica
        Route::group(['as' => 'parecer_entrevista_tecnica.'], function () {
            Route::get('parecer-entrevista-tecnica/export', [\App\Http\Controllers\ParecerEntrevistaTecnicaController::class, 'export'])->name('parecer_entrevista_tecnica.excel')->middleware('can:parecer_entrevista');
            Route::post('parecer-entrevista-tecnica/atualizar', [\App\Http\Controllers\ParecerEntrevistaTecnicaController::class, 'atualizar'])->name('atualizar')->middleware('can:parecer_entrevista');
            Route::post('parecer-entrevista-tecnica/ficha_pdf', [\App\Http\Controllers\ParecerEntrevistaTecnicaController::class, 'getFichaPdf'])->name('parecer_entrevista_tecnica.getFichaPdf')->middleware('can:parecer_entrevista');
            Route::resource('parecer-entrevista-tecnica', \App\Http\Controllers\ParecerEntrevistaTecnicaController::class, ['parameters' => ['parecer-entrevista-tecnica' => 'entrevista_tecnica']])->middleware('can:parecer_entrevista');
        });

        //Parecer Entrevista Técnica
        Route::group(['as' => 'parecer_teste_pratico.'], function () {
            Route::get('parecer-teste-pratico/export', [\App\Http\Controllers\ParecerTestePraticoController::class, 'export'])->name('parecer_teste_pratico.excel')->middleware('can:parecer_teste_pratico');
            Route::post('parecer-teste-pratico/atualizar', [\App\Http\Controllers\ParecerTestePraticoController::class, 'atualizar'])->name('atualizar')->middleware('can:parecer_teste_pratico');
            Route::post('parecer-teste-pratico/ficha_pdf', [\App\Http\Controllers\ParecerTestePraticoController::class, 'getFichaPdf'])->name('parecer_teste_pratico.getFichaPdf')->middleware('can:parecer_teste_pratico');
            Route::resource('parecer-teste-pratico', \App\Http\Controllers\ParecerTestePraticoController::class)->middleware('can:parecer_teste_pratico');
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
            Route::get('resultado-integrado/ficha/{feedback}', [\App\Http\Controllers\ResultadoIntegradoController::class, 'getFichaPdf'])->name('resultado_integrado.getFichapdf')->middleware('can:resultado_integrado');

            Route::get('resultado-integrado/export', [\App\Http\Controllers\ResultadoIntegradoController::class, 'export'])->name('resultado_integrado.excel')->middleware('can:resultado_integrado');
            Route::post('resultado-integrado/atualizar', [\App\Http\Controllers\ResultadoIntegradoController::class, 'atualizar'])->name('resultado_integrado.atualizar')->middleware('can:resultado_integrado');
//            Route::get('resultado-integrado/ficha/{resultado}', [\App\Http\Controllers\ResultadoIntegradoController::class, 'getFichaPdf'])->name('resultado_integrado.getFichapdf')->middleware('can:resultado_integrado');
            Route::resource('resultado-integrado', \App\Http\Controllers\ResultadoIntegradoController::class)->middleware('can:resultado_integrado');
        });

    });

    //Controle de Exames
    Route::group(['as' => 'controle_exames.'], function () {
        Route::post('controle-exames/ficha-encaminhamento/{exame}', [\App\Http\Controllers\ControleExameController::class, 'getFichaPdf'])->name('pdf');
        Route::get('controle-exames/carregaResposta', [\App\Http\Controllers\ControleExameController::class, 'carregaResposta'])->name('carregaResposta');
        Route::get('controle-exames/resultado/{exame}', [\App\Http\Controllers\ControleExameController::class, 'getResultado'])->name('getResultado');
        Route::match(['post', 'put'], 'controle-exames/salvaResultado', [\App\Http\Controllers\ControleExameController::class, 'salvaResultado'])->name('salvaResultado');
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
            Route::post('cih/geraexcel', [\App\Http\Controllers\CihController::class, 'relatorioExcel'])->name('relatorioExcel'); // manter essa rota antes do resource
            Route::post('cih/atualizar', [\App\Http\Controllers\CihController::class, 'atualizar'])->name('atualizar'); // manter essa rota antes do resource
            Route::put('cih/aprovar/{cih}', [\App\Http\Controllers\CihController::class, 'aprovar'])->name('aprovar'); // manter essa rota antes do resource
            Route::resource('cih', \App\Http\Controllers\CihController::class)->middleware('can:cih');
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
            Route::post('intermitente/gerapdf', [\App\Http\Controllers\IntermitenteController::class, 'relatorioPdf'])->name('relatorioPdf'); // manter essa rota antes do resource
            Route::post('intermitente/geraexcel', [\App\Http\Controllers\IntermitenteController::class, 'relatorioExcel'])->name('relatorioExcel'); // manter essa rota antes do resource
            Route::post('intermitente/atualizar', [\App\Http\Controllers\IntermitenteController::class, 'atualizar'])->name('atualizar'); // manter essa rota antes do resource
            Route::post('intermitente/storeTipo', [\App\Http\Controllers\IntermitenteController::class, 'storeTipo'])->name('storeTipo'); // manter essa rota antes do resource
            Route::put('intermitente/aprovar/{intermitente}', [\App\Http\Controllers\IntermitenteController::class, 'aprovar'])->name('aprovar'); // manter essa rota antes do resource
            Route::resource('intermitente', \App\Http\Controllers\IntermitenteController::class)->middleware('can:intermitente');
        });

        Route::group(['as' => 'preadm.', 'prefix' => 'preadmissao'], function () {
            Route::post('atualizar', [\App\Http\Controllers\PreAdmissaoController::class, 'atualizar'])->name('atualizar'); // manter essa rota antes do resource
            Route::get('/{feedback}', [\App\Http\Controllers\PreAdmissaoController::class, 'show'])->name('show');
            Route::get('/', [\App\Http\Controllers\PreAdmissaoController::class, 'index'])->name('index');
        });

        Route::post('admissao/busca-cpf', [\App\Http\Controllers\AdmissaoController::class, 'buscaCPF'])->name('admissao.buscaCPF');
        Route::get('admissao/{curriculo_id}/pdf', [\App\Http\Controllers\AdmissaoController::class, 'getFichaPdf'])->name('admissao.getFichapdf');
        // Anexos
        Route::post('admissao/uploadAnexos', [\App\Http\Controllers\AdmissaoController::class, 'uploadAnexos'])->name('admissao.upload-anexos');
        Route::get('admissao/anexo/{arquivo}', [\App\Http\Controllers\AdmissaoController::class, 'anexoShow'])->name('admissao.anexo-show');
        Route::get('admissao/anexoDownload/{arquivo}', [\App\Http\Controllers\AdmissaoController::class, 'download'])->name('admissao.anexo-download');
        Route::delete('admissao/anexo/{arquivo}', [\App\Http\Controllers\AdmissaoController::class, 'anexoDelete'])->name('admissao.anexo-delete');

        Route::post('admissao/atualizar', [\App\Http\Controllers\AdmissaoController::class, 'atualizar'])->name('admissao.atualizar')->middleware('can:admissao'); // manter essa rota antes do resource


        Route::resource('admissao', \App\Http\Controllers\AdmissaoController::class)->middleware('can:admissao');
    });

    Route::group(['as' => 'posadmissao.'], function () {
        Route::post('posadmissao/atualizar', [\App\Http\Controllers\PosAdmissaoController::class, 'atualizar'])->name('posadmissao.atualizar')->middleware('can:admissao'); // manter essa rota antes do resource
        Route::put('posadmissao/desmobilizar', [\App\Http\Controllers\PosAdmissaoController::class, 'desmobilizar'])->name('posadmissao.desmobilizar'); // manter essa rota antes do resource
        Route::post('posadmissao/entrevistar', [\App\Http\Controllers\PosAdmissaoController::class, 'entrevistar'])->name('posadmissao.entrevistar'); // manter essa rota antes do resource
        Route::put('posadmissao/entrevistar/{entrevista}', [\App\Http\Controllers\PosAdmissaoController::class, 'entrevistarUpdate'])->name('posadmissao.entrevistarUpdate'); // manter essa rota antes do resource
        Route::get('posadmissao/entrevista/{curriculo}', [\App\Http\Controllers\PosAdmissaoController::class, 'entrevista'])->name('posadmissao.entrevista'); // manter essa rota antes do resource
        Route::resource('posadmissao', \App\Http\Controllers\PosAdmissaoController::class, ['parameters' => ['posadmissao' => 'admissao']])->middleware('can:admissao');
    });

    Route::group(['as' => 'historico.'], function () {
        //Rotas Medidas Administrativas
        Route::post('historico/medidas-administrativas/uploadAnexos', [\App\Http\Controllers\HistoricoController::class, 'uploadAnexos'])->name('medidas-administrativas.upload-anexos');
        Route::get('historico/medidas-administrativas/anexo/{arquivo}', [\App\Http\Controllers\HistoricoController::class, 'anexoShow'])->name('medidas-administrativas.anexo-show');
        Route::get('historico/medidas-administrativas/anexoDownload/{arquivo}', [\App\Http\Controllers\HistoricoController::class, 'download'])->name('medidas-administrativas.anexo-download');
        Route::delete('historico/medidas-administrativas/anexo/{arquivo}', [\App\Http\Controllers\HistoricoController::class, 'anexoDelete'])->name('medidas-administrativas.anexo-delete');
        Route::get('historico/medidas-administrativas/{medida}/{feedback_id}/pdf', [\App\Http\Controllers\HistoricoController::class, 'medidasAdministrativasPDF'])->name('pdfMedidasAdministrativas');
        Route::post('historico/atualizar', [\App\Http\Controllers\HistoricoController::class, 'atualizar'])->name('atualizar'); // manter essa rota antes do resource
        Route::get('historico/{feedback}', [\App\Http\Controllers\HistoricoController::class, 'show'])->name('show');
        Route::post('historico/{feedback}', [\App\Http\Controllers\HistoricoController::class, 'storeMedidas'])->name('storeMedidas');
        Route::put('historico/{feedback}', [\App\Http\Controllers\HistoricoController::class, 'updateMedidas'])->name('updateMedidas');

        //Rotas Formulario Noventa Dias
        Route::post('historico/formulario-noventa-dias/{feedback}', [\App\Http\Controllers\HistoricoController::class, 'storeFormularioNoventaDias'])->name('storeNoventaDias');
        Route::get('historico/formulario-noventa-dias/{quantidade_avaliacao}/{feedback_id}/pdf', [\App\Http\Controllers\HistoricoController::class, 'formularioNoventaDiasPDF'])->name('formularioNoventaDiasPDF');

        Route::get('historico', [\App\Http\Controllers\HistoricoController::class, 'index'])->name('index')->middleware('can:historico'); // manter essa rota antes do resource

        //Rotas DOSSIE
        Route::post('historico/dossie/uploadAnexos', [\App\Http\Controllers\DossieController::class, 'uploadAnexos'])->name('dossie.upload-anexos');
        Route::get('historico/dossie/anexo/{arquivo}', [\App\Http\Controllers\DossieController::class, 'anexoShow'])->name('dossie.anexo-show');
        Route::get('historico/dossie/anexoDownload/{arquivo}', [\App\Http\Controllers\DossieController::class, 'download'])->name('dossie.anexo-download');
        Route::delete('historico/dossie/anexo/{arquivo}', [\App\Http\Controllers\DossieController::class, 'anexoDelete'])->name('dossie.anexo-delete');
//        Route::get('historico/dossie/{feedback_id}/pdf', [\App\Http\Controllers\DossieController::class,'medidasAdministrativasPDF'])->name('pdfDossie');
        Route::post('historico/dossie/{feedback}', [\App\Http\Controllers\DossieController::class, 'store'])->name('dossie.store');
        Route::get('historico/dossie/{feedback}', [\App\Http\Controllers\DossieController::class, 'show'])->name('dossie.show');

        //Rotas Avaliacao Anual
        Route::get('historico/avaliacao-anual/{feedback}', [\App\Http\Controllers\AvaliacaoAnualFeedbackController::class, 'show'])->name('showAvaliacaoAnual');
        Route::post('historico/avaliacao-anual/{feedback}', [\App\Http\Controllers\AvaliacaoAnualFeedbackController::class, 'store'])->name('storeAvaliacaoAnual');
        Route::get('historico/avaliacao-anual/{quantidade_avaliacao}/{feedback_id}/pdf', [\App\Http\Controllers\AvaliacaoAnualFeedbackController::class, 'avaliacaoAnualPDF'])->name('avaliacaoAnualPDF');

        //Rotas Ferias e listagem Afastamento
        Route::get('historico/ferias/{feedback}', [\App\Http\Controllers\FeriasFeedbackController::class, 'show'])->name('showFeriasFeedback');
        Route::post('historico/ferias/{feedback}', [\App\Http\Controllers\FeriasFeedbackController::class, 'store'])->name('storeFeriasFeedback');
        Route::get('historico/ferias/{id}/{feedback_id}/pdf', [\App\Http\Controllers\FeriasFeedbackController::class, 'feriasPDF'])->name('feriasPDF');

        //Rotas Afastamento
        Route::post('historico/afastamento/{feedback}', [\App\Http\Controllers\AfastamentoFeedbackController::class, 'store'])->name('storeAfastamentoFeedback');
        Route::get('historico/afastamento/{id}/{feedback_id}/pdf', [\App\Http\Controllers\AfastamentoFeedbackController::class, 'afastamentoPDF'])->name('afastamentoPDF');

        Route::get('historico/beneficio/{feedback}', [\App\Http\Controllers\BeneficioController::class, 'showBeneficio'])->name('showBeneficio');
        Route::post('historico/beneficio/{feedback}', [\App\Http\Controllers\BeneficioController::class, 'storeBeneficio'])->name('storeBeneficio');

        Route::get('historico/cih/{feedback}', [\App\Http\Controllers\CihController::class, 'atualizarHistorico'])->name('atualizarHistorico');

        Route::get('historico/promocao/atualizar/{feedback}', [\App\Http\Controllers\PromocaoFeedbackController::class, 'atualizar'])->name('atualizarPromocao'); // manter essa rota antes do resource
        Route::post('historico/promocao/{feedback}', [\App\Http\Controllers\PromocaoFeedbackController::class, 'store'])->name('storePromocao');

        Route::get('historico/meta/atualizar/{feedback}', [\App\Http\Controllers\MetasFeedbackController::class, 'atualizar'])->name('atualizarMeta'); // manter essa rota antes do resource
        Route::post('historico/meta/{feedback}', [\App\Http\Controllers\MetasFeedbackController::class, 'store'])->name('storeMeta');

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
        Route::post('treinamento/atualizar', [\App\Http\Controllers\TreinamentoController::class, 'atualizar'])->name('atualizar');

        Route::get('treinamento/vencimentos', [\App\Http\Controllers\TreinamentoController::class, 'vencimentos'])->name('vencimentos');

        Route::post('treinamento/proximovencimento', [\App\Http\Controllers\TreinamentoController::class, 'treinamentoProximoVencimento'])->name('vencimentoTreinamento');

        Route::resource('treinamento', \App\Http\Controllers\TreinamentoController::class)->middleware('can:treinamento');
    });

    //PORTARIA
    Route::group(['as' => 'portaria.'], function () {
        Route::post('portaria/atualizar', [\App\Http\Controllers\PortariaController::class, 'atualizar'])->name('atualizar');
        Route::post('portaria/pdf', [\App\Http\Controllers\PortariaController::class, 'pdf'])->name('pdf');
        Route::get('portaria/{resultado}', [\App\Http\Controllers\PortariaController::class, 'edit'])->name('edit');
        Route::put('portaria/{resultado}', [\App\Http\Controllers\PortariaController::class, 'update'])->name('update');
        Route::get('portaria', [\App\Http\Controllers\PortariaController::class, 'index'])->name('index');
    });

    // CERTIFICADO
    Route::group(['as' => 'certificados.'], function () {
        //Enviar para Revisao
        Route::post('certificado/enviar-carteira', [\App\Http\Controllers\CertificadoController::class, 'enviarCarteiraEmail']);
        Route::post('certificado/atualizar', [\App\Http\Controllers\CertificadoController::class, 'atualizar'])->name('atualizar');

        Route::post('certificado/pdf', [\App\Http\Controllers\CertificadoController::class, 'certificadoPdf'])->name('certificadoPdf');
        Route::resource('certificado', \App\Http\Controllers\CertificadoController::class)->middleware('can:treinamento');
    });

    //Cloud
    Route::group(['as' => 'cloud.'], function () {
        Route::get('cloud/atualizar/{cloud}/{id?}', [\App\Http\Controllers\CloudController::class, 'atualizar'])->name('cloud.atualizar')->middleware('can:cloud'); // manter essa rota antes do resource
        Route::get('cloud/{id}/{titulo}', [\App\Http\Controllers\CloudController::class, 'getSingle'])->name('cloud.single'); // manter essa rota antes do resource
        Route::get('cloud/editar/pasta/{item}', [\App\Http\Controllers\CloudController::class, 'editarPasta'])->name('cloud.editarPasta'); // manter essa rota antes do resource
        Route::resource('cloud', \App\Http\Controllers\CloudController::class)->middleware('can:cloud');

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
        Route::post('galeria/uploadFotos', [\App\Http\Controllers\GaleriaController::class, 'uploadFotos'])->name('galeria.upload-fotos')->middleware('can:galeria_site');
        Route::get('galeria/fotoDownload/{arquivo}', [\App\Http\Controllers\GaleriaController::class, 'download'])->name('galeria.foto-download')->middleware('can:galeria_site');
        Route::put('galeria/{galeria}/ativa-desativa', [\App\Http\Controllers\GaleriaController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:galeria_site');

        Route::post('galeria/atualizar', [\App\Http\Controllers\GaleriaController::class, 'atualizar'])->name('galeria.atualizar')->middleware('can:galeria_site');
        Route::resource('galeria', \App\Http\Controllers\GaleriaController::class, ['parameters' => ['galeria' => 'galeria']])->middleware('can:galeria_site');

        //CLIENTES
        Route::group(['as' => 'cliente.'], function () {
            Route::post('cliente-logo/atualizar', [\App\Http\Controllers\ClienteLogoSiteController::class, 'atualizar'])->name('atualizar')->middleware('can:cartela_cliente_site');
            Route::post('cliente-logo/fotoUpload', [\App\Http\Controllers\ClienteLogoSiteController::class, 'fotoUpload'])->name('upload-fotos')->middleware('can:cartela_cliente_site_insert');
//                Route::get('cliente/fotoDownload/{arquivo}', 'ClientesController@download')->name('foto-download');
            Route::resource('cliente-logo', \App\Http\Controllers\ClienteLogoSiteController::class)->middleware('can:cartela_cliente_site');
        });

        Route::group(['as' => 'testemunhal.'], function () {
            // Anexos
            Route::post('testemunhal/uploadAnexos', [\App\Http\Controllers\TestemunhalController::class, 'uploadAnexos'])->name('testemunhal.upload-anexos')->middleware('can:depoimento_site_insert');
            Route::get('testemunhal/anexo/{arquivo}', [\App\Http\Controllers\TestemunhalController::class, 'anexoShow'])->name('testemunhal.anexo-show');
            Route::get('testemunhal/anexoDownload/{arquivo}', [\App\Http\Controllers\TestemunhalController::class, 'download'])->name('testemunhal.anexo-download');
            Route::delete('testemunhal/anexo/{arquivo}', [\App\Http\Controllers\TestemunhalController::class, 'anexoDelete'])->name('testemunhal.anexo-delete');


            Route::post('testemunhal/atualizar', [\App\Http\Controllers\TestemunhalController::class, 'atualizar'])->name('testemunhal.atualizar')->middleware('can:depoimento_site');
            Route::resource('testemunhal', \App\Http\Controllers\TestemunhalController::class)->middleware('can:depoimento_site');
        });
    });
    // Relatorios
    Route::group(['as' => 'relatorios.'], function () {
        Route::group(['as' => 'controleusuarios.'], function () {
            Route::get('relatorios/controleusuarios', [\App\Http\Controllers\ControleUsuariosController::class, 'index'])->name('index')->middleware('can:relatorios');
            Route::post('relatorios/controleusuarios/dados', [\App\Http\Controllers\ControleUsuariosController::class, 'dadosusuarioSistema'])->name('dadosusuarioSistema')->middleware('can:relatorios');
//            Route::get('relatorios/controleusuarios/pdf/{dados}', [\App\Http\Controllers\ControleUsuariosController::class, 'usuarioSistema'])->name('usuarioSistema')->middleware('can:relatorios');
        });
    });

    // Usuarios
    Route::group(['as' => 'usuarios.'], function () {
        //Usuários
        Route::get('usuario/autenticado', [\App\Http\Controllers\UserController::class, 'getUsuario'])->name('getUsuario');

        Route::post('usuarios/atualizar', [\App\Http\Controllers\UserController::class, 'atualizar'])->name('usuarios.atualizar')->middleware('can:usuarios');
        Route::put('usuarios/{usuario}/ativa-desativa', [\App\Http\Controllers\UserController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:usuarios');

        Route::resource('usuarios', \App\Http\Controllers\UserController::class)->middleware('can:usuarios');
        //Alterar senha
        Route::get('alterar-senha', [\App\Http\Controllers\AlterarSenhaController::class, 'index'])->name('alterar-senha.index')->middleware('can:alterar-senha');
        Route::put('alterar-senha', [\App\Http\Controllers\AlterarSenhaController::class, 'update'])->name('alterar-senha.update')->middleware('can:alterar-senha');
    });

    //Financeiro
    Route::group(['as' => 'financeiro.'], function () {

        Route::group(['as' => 'fluxo-caixa.'], function () {
            Route::put('fluxo-caixa/{empresa}/lancamento/{lancamento}/mudarStatus', [\App\Http\Controllers\FluxoCaixaController::class, 'mudarStatus'])->name('mudarStatus')->middleware('can:realizar-lancamento');
            Route::put('fluxo-caixa/{empresa}/lancamento/{lancamento}', [\App\Http\Controllers\FluxoCaixaController::class, 'alterarLancamento'])->name('alterarLancamento')->middleware('can:fluxo-caixa');
            Route::delete('fluxo-caixa/{empresa}/lancamento/{lancamento}', [\App\Http\Controllers\FluxoCaixaController::class, 'excluirLancamento'])->name('excluirLancamento')->middleware('can:fluxo-caixa');
            Route::post('fluxo-caixa/{empresa}/lancamento', [\App\Http\Controllers\FluxoCaixaController::class, 'cadastrarLancamento'])->name('cadastrarLancamento')->middleware('can:fluxo-caixa_insert');
            Route::get('fluxo-caixa/{empresa}/lancamento/{lancamento}', [\App\Http\Controllers\FluxoCaixaController::class, 'carregarLancamento'])->name('carregarLancamento')->middleware('can:fluxo-caixa');
            Route::get('fluxo-caixa/buscaNomePlanoConta', [\App\Http\Controllers\FluxoCaixaController::class, 'buscaNomePlanoConta'])->name('buscaNomePlanoConta'); //->middleware('can:fluxo-caixa'); não tem restrição (tela de recibos usa)
            Route::post('fluxo-caixa/{empresa}/atualizaFluxoCaixa', [\App\Http\Controllers\FluxoCaixaController::class, 'atualizaFluxoCaixa'])->name('atualizaFluxoCaixa')->middleware('can:fluxo-caixa');
            Route::get('fluxo-caixa/{empresa}', [\App\Http\Controllers\FluxoCaixaController::class, 'show'])->name('show')->middleware('can:fluxo-caixa');
            Route::get('fluxo-caixa', [\App\Http\Controllers\FluxoCaixaController::class, 'index'])->name('index')->middleware('can:fluxo-caixa');
        });

        //Categoria Plano Conta
        Route::group(['as' => 'classificacao-plano-conta.'], function () {
            Route::get('classificacao-plano-conta/buscar', [\App\Http\Controllers\CategoriaPlanoContaController::class, 'buscaCategoria'])->name('buscar')->middleware('can:classificacao-plano-conta');
            Route::post('classificacao-plano-conta/atualizar', [\App\Http\Controllers\CategoriaPlanoContaController::class, 'atualizar'])->name('atualizar')->middleware('can:classificacao-plano-conta');
        });
        Route::resource('classificacao-plano-conta', \App\Http\Controllers\CategoriaPlanoContaController::class, ['parameters' => ['classificacao-plano-conta' => 'categoria']])->middleware('can:classificacao-plano-conta');

        //Planos de conta
        Route::group(['as' => 'plano-conta.'], function () {
            Route::get('plano-conta/buscar', [\App\Http\Controllers\PlanoContaController::class, 'busca'])->name('buscar')->middleware('can:plano-conta');
            Route::post('plano-conta/atualizar', [\App\Http\Controllers\PlanoContaController::class, 'atualizar'])->name('atualizar')->middleware('can:plano-conta');
        });
        Route::resource('plano-conta', \App\Http\Controllers\PlanoContaController::class, ['parameters' => ['plano-conta' => 'plano']])->middleware('can:plano-conta');

        //Formas de pagamento
        Route::group(['as' => 'formas-pagamento.'], function () {
            Route::get('formas-pagamento/buscar', [\App\Http\Controllers\FormaPagamentoController::class, 'buscaCategoria'])->name('buscar')->middleware('can:formas-pagamento');
            Route::post('formas-pagamento/atualizar', [\App\Http\Controllers\FormaPagamentoController::class, 'atualizar'])->name('atualizar')->middleware('can:formas-pagamento');
        });
        Route::resource('formas-pagamento', \App\Http\Controllers\FormaPagamentoController::class, ['parameters' => ['formas-pagamento' => 'forma']])->middleware('can:formas-pagamento');

    });

    //Controle de ponto
    Route::group(['as' => 'controle-ponto.', 'prefix' => 'controle-ponto'], function () {

        //Configuracoes
        Route::group(['as' => 'configuracoes.'], function () {
            Route::post('configuracoes/buscarPerimetros', [\App\Http\Controllers\EmpresaConfigController::class, 'atualizarFuncionarios'])->name('atualizarFuncionarios')->middleware('can:config_empresa');
            Route::post('configuracoes/atualizarFuncionarios', [\App\Http\Controllers\EmpresaConfigController::class, 'atualizarFuncionarios'])->name('atualizarFuncionarios')->middleware('can:config_empresa');
            Route::get('configuracoes/getPermissoes', [\App\Http\Controllers\EmpresaConfigController::class, 'getPermissoes'])->name('getPermissoes');

        });
        Route::resource('configuracoes', \App\Http\Controllers\EmpresaConfigController::class, ['parameters' => ['configuracoes' => 'config']])->middleware('can:config_empresa');

        // Feriados
        Route::group(['as' => 'feriados.'], function () {
            Route::put('feriados/{feriado}/ativa-desativa',[\App\Http\Controllers\FeriadoController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:feriados');
            Route::post('feriados/search', [\App\Http\Controllers\FeriadoController::class, 'searchFeriado'])->name('search')->middleware('can:feriados');
            Route::post('feriados/atualizar', [\App\Http\Controllers\FeriadoController::class, 'atualizar'])->name('atualizar')->middleware('can:feriados');
        });
        Route::resource('feriados', \App\Http\Controllers\FeriadoController::class,)->middleware('can:feriados');

        //Perimetro
        Route::group(['as' => 'perimetros.'], function () {
            Route::post('perimetros/atualizarPerimetros', [\App\Http\Controllers\PerimetroController::class, 'atualizarPerimetros'])->name('atualizarPerimetros')->middleware('can:perimetros');
            Route::put('perimetros/assosicarPerimetro', [\App\Http\Controllers\PerimetroController::class, 'assosicarPerimetro'])->name('assosicarPerimetro')->middleware('can:perimetros_funcionarios');
        });
        Route::resource('perimetros', \App\Http\Controllers\PerimetroController::class, ['parameters' => ['perimetros' => 'perimetro']])->middleware('can:perimetros');

        //Ocorrencais jornadas
        Route::group(['as' => 'ocorrencias_jornadas.'], function () {
            Route::get('ocorrencias_jornadas/buscar', [\App\Http\Controllers\OcorrenciaJornadaController::class, 'buscaCategoria'])->name('buscar')->middleware('can:ocorrencias_jornadas');
            Route::post('ocorrencias_jornadas/atualizar', [\App\Http\Controllers\OcorrenciaJornadaController::class, 'atualizar'])->name('atualizar')->middleware('can:ocorrencias_jornadas');
        });
        Route::resource('ocorrencias_jornadas', \App\Http\Controllers\OcorrenciaJornadaController::class, ['parameters' => ['ocorrencias_jornadas' => 'ocorrencia_jornada']])->middleware('can:ocorrencias_jornadas');

        //Escalas
        Route::group(['as' => 'escalas.'], function () {
            Route::post('escalas/atualizarEscalas', [\App\Http\Controllers\EmpresaEscalaController::class, 'atualizarEscalas'])->name('atualizarEscalas')->middleware('can:escalas');
            Route::put('escalas/assosicarEscalas', [\App\Http\Controllers\EmpresaEscalaController::class, 'assosicarEscalas'])->name('assosicarEscalas')->middleware('can:escalas_funcionarios');

            Route::post('escalas/atualizarFuncionarios', [\App\Http\Controllers\EmpresaEscalaController::class, 'atualizarFuncionarios'])->name('atualizarFuncionarios')->middleware('can:escalas');
            Route::get('escalas/getPermissoes', [\App\Http\Controllers\EmpresaEscalaController::class, 'getPermissoes'])->name('getPermissoes');

        });
        Route::resource('escalas', \App\Http\Controllers\EmpresaEscalaController::class, ['parameters' => ['escalas' => 'escala']])->middleware('can:escalas');


        //Ponto eletronico
        Route::group(['as' => 'ponto-eletronico.'], function () {
            Route::get('ponto-eletronico/fotos/{arquivo}', [\App\Http\Controllers\PontoEletronicoController::class, 'fotoShow'])->name('fotoShow')->middleware('can:ponto-eletronico');
            Route::get('ponto-eletronico/init', [\App\Http\Controllers\PontoEletronicoController::class, 'init'])->name('init')->middleware('can:ponto-eletronico');
            Route::post('ponto-eletronico/atualizarHistorico', [\App\Http\Controllers\PontoEletronicoController::class, 'atualizarHistorico'])->name('atualizarHistorico')->middleware('can:ponto-eletronico');
            Route::get('ponto-eletronico/{ponto}/{periodo}', [\App\Http\Controllers\PontoEletronicoController::class, 'showPeriodo'])->name('showPeriodo')->middleware('can:ponto-eletronico');
        });
        Route::resource('ponto-eletronico', \App\Http\Controllers\PontoEletronicoController::class, ['parameters' => ['ponto-eletronico' => 'ponto']])->middleware('can:ponto-eletronico');

        //Ajustar jornada
        Route::group(['as' => 'ajustar-jornadas.'], function () {
            Route::post('ajustar-jornadas/atualizaJornadasVerificadas', [\App\Http\Controllers\AjusteJornadaController::class, 'atualizaJornadasVerificadas'])->name('atualizaJornadasVerificadas')->middleware('can:ajustar-jornadas');
            Route::post('ajustar-jornadas/atualizaJornadasPendentes', [\App\Http\Controllers\AjusteJornadaController::class, 'atualizaJornadasPendentes'])->name('atualizaJornadasPendentes')->middleware('can:ajustar-jornadas');
            Route::post('ajustar-jornadas/atualizaJornadasIncompletas', [\App\Http\Controllers\AjusteJornadaController::class, 'atualizaJornadasIncompletas'])->name('atualizaJornadasIncompletas')->middleware('can:ajustar-jornadas');
        });
        Route::resource('ajustar-jornadas', \App\Http\Controllers\AjusteJornadaController::class, ['parameters' => ['ajustar-jornadas' => 'ponto']])->middleware('can:ajustar-jornadas');

        //Folha de ponto
        Route::group(['as' => 'folha-ponto.'], function () {
            Route::post('folha-ponto/atualizarLista', [\App\Http\Controllers\FolhaDePontoController::class, 'atualizarLista'])->name('atualizarLista')->middleware('can:folha-ponto');
            Route::post('folha-ponto/{user}/frequencia', [\App\Http\Controllers\FolhaDePontoController::class, 'buscarFrequencia'])->name('buscarFrequencia')->middleware('can:folha-ponto');
            Route::post('folha-ponto/{user}/imprimir', [\App\Http\Controllers\FolhaDePontoController::class, 'imprimir'])->name('imprimir')->middleware('can:folha-ponto');
        });
        Route::resource('folha-ponto', \App\Http\Controllers\FolhaDePontoController::class, ['parameters' => ['folha-ponto' => 'user']])->middleware('can:folha-ponto');


    });

    //Weekley report
    Route::group(['as' => 'weekly-report.'], function () {

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

        Route::delete('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}', [\App\Http\Controllers\TarefasController::class, 'destroy'])->name('delete')->middleware('can:tarefa_delete');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}', [\App\Http\Controllers\TarefasController::class, 'update'])->name('update')->middleware('can:tarefa_update');
        Route::get('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas/{tarefa}', [\App\Http\Controllers\TarefasController::class, 'show'])->name('show');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas', [\App\Http\Controllers\TarefasController::class, 'atualizarOrdem'])->name('atualizarOrdem');
        Route::post('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}/tarefas', [\App\Http\Controllers\TarefasController::class, 'store'])->name('store')->middleware('can:tarefa_insert');

        //Listas
        Route::delete('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}', [\App\Http\Controllers\ListaTarefaController::class, 'destroy'])->name('delete')->middleware('can:lista_delete');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas/{lista}', [\App\Http\Controllers\ListaTarefaController::class, 'update'])->name('update')->middleware('can:lista_update');
        Route::put('weekly-report/{empresa}/quadros/{quadro}/listas', [\App\Http\Controllers\ListaTarefaController::class, 'atualizarOrdem'])->name('atualizarOrdem');
        Route::post('weekly-report/{empresa}/quadros/{quadro}/listas', [\App\Http\Controllers\ListaTarefaController::class, 'store'])->name('store')->middleware('can:lista_insert');
        Route::get('weekly-report/{empresa}/quadros/{quadro}/listas', [\App\Http\Controllers\ListaTarefaController::class, 'index'])->name('index');

        //Quadros
        Route::delete('weekly-report/{empresa}/quadros/{quadro}', [\App\Http\Controllers\QuadroController::class, 'destroy'])->name('delete')->middleware('can:quadro_delete');
        Route::put('weekly-report/{empresa}/quadros/{quadro}', [\App\Http\Controllers\QuadroController::class, 'update'])->name('update')->middleware('can:quadro_update');
        Route::post('weekly-report/{empresa}/quadros', [\App\Http\Controllers\QuadroController::class, 'store'])->name('store')->middleware('can:quadro_insert');
        Route::get('weekly-report/{empresa}', [\App\Http\Controllers\QuadroController::class, 'show'])->name('show')->middleware('can:weekly-report');
        Route::get('weekly-report', [\App\Http\Controllers\QuadroController::class, 'index'])->name('index')->middleware('can:weekly-report');
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
        Route::post('habilidades/atualizar', [\App\Http\Controllers\HabilidadesController::class, 'atualizar'])->name('habilidades.atualizar')->middleware('can:habilidades'); // manter essa rota antes do resource
        Route::resource('habilidades', \App\Http\Controllers\HabilidadesController::class)->middleware('can:habilidades');
        // Papeis
        Route::put('papeis/{papel}/ativa-desativa', [\App\Http\Controllers\PapeisController::class, 'ativaDesativa'])->name('ativaDesativa')->middleware('can:papel');
        Route::post('papeis/atualizar', [\App\Http\Controllers\PapeisController::class, 'atualizar'])->name('papeis.atualizar')->middleware('can:papel');
        Route::resource('papeis', \App\Http\Controllers\PapeisController::class, ['parameters' => ['papeis' => 'papel']])->middleware('can:papel');
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


    Route::get('provas/{vaga_id}/{simulado_id}/{slug}', [\App\Http\Controllers\SimuladoCandidatoController::class, 'index'])->name('prova.simulado');

});

//Rotas de pre-admissao protegidas
Route::group(['as' => 'documentospreadmissao.'], function () {
    //Provas
    Route::post('documentos-pre-admissao/autenticar', [\App\Http\Controllers\DocumentosPreAdmissaoController::class, 'autenticar'])->name('documentos.autenticar');

    Route::get('documentos', [\App\Http\Controllers\DocumentosPreAdmissaoController::class, 'index'])->name('documentos.index');
    Route::put('documentos/{curriculo_id}', [\App\Http\Controllers\DocumentosPreAdmissaoController::class, 'update'])->name('documentos.update');

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
