<?php

/**
 * ROTAS PARA O SISTEMA DE APROVAÇÃO EXTRA
 *
 * Adicionar estas rotas no arquivo routes/web.php
 */

// Grupo de rotas para Aprovação Extra Config (somente autenticados)
Route::middleware(['auth'])->prefix('g/administracao')->group(function () {

    // Interface principal
    Route::get('aprovacao-extra-config', [
        \App\Http\Controllers\AprovacaoExtraConfigController::class,
        'index'
    ])->name('g.aprovacao-extra-config.index');

    // Listar todas as configurações da empresa
    Route::get('aprovacao-extra-config/listar', [
        \App\Http\Controllers\AprovacaoExtraConfigController::class,
        'listar'
    ])->name('g.aprovacao-extra-config.listar');

    // Buscar configuração por tipo de processo
    Route::post('aprovacao-extra-config/buscar-por-tipo', [
        \App\Http\Controllers\AprovacaoExtraConfigController::class,
        'buscarPorTipo'
    ])->name('g.aprovacao-extra-config.buscar-por-tipo');

    // Obter tipos de processo disponíveis
    Route::get('aprovacao-extra-config/tipos-processo', [
        \App\Http\Controllers\AprovacaoExtraConfigController::class,
        'tiposProcesso'
    ])->name('g.aprovacao-extra-config.tipos-processo');

    // Ativar/Desativar configuração
    Route::post('aprovacao-extra-config/{id}/toggle-ativo', [
        \App\Http\Controllers\AprovacaoExtraConfigController::class,
        'toggleAtivo'
    ])->name('g.aprovacao-extra-config.toggle-ativo');

    // CRUD Resource
    Route::resource('aprovacao-extra-config', \App\Http\Controllers\AprovacaoExtraConfigController::class, [
        'names' => [
            'store' => 'g.aprovacao-extra-config.store',
            'update' => 'g.aprovacao-extra-config.update',
            'destroy' => 'g.aprovacao-extra-config.destroy',
        ],
        'only' => ['store', 'update', 'destroy']
    ]);
});

/**
 * ROTAS ADICIONAIS PARA DEMISSÃO COM APROVAÇÃO EXTRA
 *
 * Adicionar no grupo de rotas de Demissão
 */
Route::middleware(['auth'])->prefix('g/planejamento/movimentacao')->group(function () {

    // Aprovar/Reprovar demissão pela aprovação extra
    Route::put('demissao-prevista/{id}/aprovar-extra', [
        \App\Http\Controllers\DemissaoPrevistaController::class,
        'aprovarExtra'
    ])->name('g.demissao-prevista.aprovar-extra');

    // Listar demissões pendentes de aprovação extra
    Route::get('demissao-prevista/pendentes-aprovacao-extra', [
        \App\Http\Controllers\DemissaoPrevistaController::class,
        'pendentesAprovacaoExtra'
    ])->name('g.demissao-prevista.pendentes-aprovacao-extra');
});

/**
 * ROTAS ADICIONAIS PARA FÉRIAS COM APROVAÇÃO EXTRA
 *
 * Adicionar no grupo de rotas de Férias
 */
Route::middleware(['auth'])->prefix('g/planejamento/movimentacao')->group(function () {

    // Aprovar/Reprovar férias pela aprovação extra
    Route::put('ferias-prevista/{id}/aprovar-extra', [
        \App\Http\Controllers\FeriasPrevistaController::class,
        'aprovarExtra'
    ])->name('g.ferias-prevista.aprovar-extra');

    // Listar férias pendentes de aprovação extra
    Route::get('ferias-prevista/pendentes-aprovacao-extra', [
        \App\Http\Controllers\FeriasPrevistaController::class,
        'pendentesAprovacaoExtra'
    ])->name('g.ferias-prevista.pendentes-aprovacao-extra');
});

/**
 * EXEMPLO DE ORGANIZAÇÃO NO WEB.PHP COMPLETO:
 */

/*
Route::middleware(['auth'])->prefix('g')->group(function () {

    // ... outras rotas ...

    // ADMINISTRAÇÃO
    Route::prefix('administracao')->group(function () {

        // Aprovação Extra Config
        Route::get('aprovacao-extra-config', [AprovacaoExtraConfigController::class, 'index'])
            ->name('g.aprovacao-extra-config.index')
            ->middleware('can:administracao_configuracoes'); // Adicionar permissão

        Route::get('aprovacao-extra-config/listar', [AprovacaoExtraConfigController::class, 'listar']);
        Route::post('aprovacao-extra-config/buscar-por-tipo', [AprovacaoExtraConfigController::class, 'buscarPorTipo']);
        Route::get('aprovacao-extra-config/tipos-processo', [AprovacaoExtraConfigController::class, 'tiposProcesso']);
        Route::post('aprovacao-extra-config/{id}/toggle-ativo', [AprovacaoExtraConfigController::class, 'toggleAtivo']);

        Route::resource('aprovacao-extra-config', AprovacaoExtraConfigController::class, [
            'only' => ['store', 'update', 'destroy']
        ]);
    });

    // PLANEJAMENTO - MOVIMENTAÇÃO
    Route::prefix('planejamento/movimentacao')->group(function () {

        // Demissão
        Route::put('demissao-prevista/{id}/aprovar-extra', [DemissaoPrevistaController::class, 'aprovarExtra']);
        Route::get('demissao-prevista/pendentes-aprovacao-extra', [DemissaoPrevistaController::class, 'pendentesAprovacaoExtra']);

        // Férias
        Route::put('ferias-prevista/{id}/aprovar-extra', [FeriasPrevistaController::class, 'aprovarExtra']);
        Route::get('ferias-prevista/pendentes-aprovacao-extra', [FeriasPrevistaController::class, 'pendentesAprovacaoExtra']);
    });
});
*/

/**
 * NOTA IMPORTANTE:
 *
 * Não esqueça de adicionar as permissões necessárias:
 * - administracao_aprovacao_extra_config (para gerenciar configurações)
 * - planejamento_movimentacao_demissao_aprovar_extra (para aprovar demissões)
 * - planejamento_movimentacao_ferias_aprovar_extra (para aprovar férias)
 */
