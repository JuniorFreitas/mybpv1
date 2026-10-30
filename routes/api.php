<?php

use App\Classes\ZapNotificacao;
use App\Http\Controllers\Api\CboController;
use App\Http\Controllers\Api\Externa\AvaliacaoController as ApiExternaAvaliacaoController;
use App\Http\Controllers\Api\Externa\CentroCustoController as ApiExternaCentroCustoController;
use App\Http\Controllers\Api\Externa\CihController as ApiExternaCihController;
use App\Http\Controllers\Api\Externa\CloudController as ApiExternaCloudController;
use App\Http\Controllers\Api\Externa\CurriculoController as ApiExternaCurriculoController;
use App\Http\Controllers\Api\Externa\DocsController as ApiExternaDocsController;
use App\Http\Controllers\Api\Externa\FluxoController as ApiExternaFluxoController;
use App\Http\Controllers\Api\Externa\FornecedorController as ApiExternaFornecedorController;
use App\Http\Controllers\Api\Externa\HistoricoController as ApiExternaHistoricoController;
use App\Http\Controllers\Api\Externa\MedidaAdministrativaController as ApiExternaMedidaController;
use App\Http\Controllers\Api\Externa\OAuthTokenController as ApiExternaOAuthTokenController;
use App\Http\Controllers\Api\Externa\PosAdmissaoController as ApiExternaPosAdmissaoController;
use App\Http\Controllers\Api\Externa\ProcessoController as ApiExternaProcessoController;
use App\Http\Controllers\Api\Externa\RelatorioController as ApiExternaRelatorioController;
use App\Http\Controllers\Api\Externa\VagaAbertaController as ApiExternaVagaAbertaController;
use App\Http\Controllers\Api\Externa\WeeklyController as ApiExternaWeeklyController;
use App\Models\CartaOferta;
use App\Models\Sistema;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('convocacao/{resposta}/{hash}/', [\App\Http\Controllers\IntermitenteController::class, "respostaConvocacao"])->name('respostaConvocacao');

Route::middleware(['apitoken:whatsapp', 'throttle:api-integracao-spa'])->post('envia-whats', function (Request $request) {
    //     $arquivo =  Sistema::convertBase2(CartaOferta::checklistArquivo('equatorialservicos'), true);
    try {
        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => preg_replace('/[^0-9]/', '', $request->telefone),
            'mensagem' => $request->mensagem,
            'anexo' => $request->anexo
        ]);
        return response()->json([
            'msg' => __('msg.ENVIADO_FILA'),
            'status' => __('msg.SUCESSO')
        ]);
    } catch (Exception $exception) {
        Log::error("Error ao enviar o whatsapp " . $exception->getMessage());
        return response()->json(['msg' => __('msg.HOUVE_UM_ERRO')], 400);
    }

    //    'json' => [
    //        "enviado_id" => 104,
    //        "telefone" => env('APP_ENV') == 'local' ? "5598999023762" : $dados['telefone'],
    //        "mensagem" => $dados['mensagem'],
    //        "sistema" => "sgi"
    //    ]
});


Route::middleware('api')->get('/login', function (Request $request) {
    return response()->json(['erro' => 'Não autorizado'], 403);
})->name("api.login.unauthorized");

Route::middleware('api')
    ->post('/login', [\App\Http\Controllers\Api\LoginController::class, 'login'])->name("api.login");


//Route::middleware('auth:api')->group(function () {
Route::middleware(['api', 'auth:sanctum', 'usuario.ativo'])->group(function () {
    Route::get('/logout', [\App\Http\Controllers\Api\LoginController::class, 'logout'])->name("logout");
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //Route::group(['middleware' => ['auth', 'habilidades', 'horario_ativo'], 'as' => 'g.'], function () {

    //    Route::apiResource('empresas', "EmpresasController", ['parameters' => ['empresas' => 'empresa']])->middleware('can:empresa');


    Route::apiResource('habilidades', \App\Http\Controllers\Api\HabilidadeController::class)->middleware('can.sanctum:habilidades');

    Route::put('usuarios/{usuario}/ativa-desativa', [\App\Http\Controllers\Api\UsuarioController::class, "ativaDesativa"])->middleware('can.sanctum:usuario_ativar_desativar');
    Route::apiResource('usuarios', \App\Http\Controllers\Api\UsuarioController::class)->middleware('can.sanctum:usuarios');
});


Route::group(['as' => 'v1', 'prefix' => 'v1/{empresa_slug}', 'middleware' => ['apitoken']], function () {
    Route::get('/', [\App\Http\Controllers\Api\IntegracaoVagaAbertaController::class, 'getVagasAbertasByEmpresa']);
    Route::get('/dadosempresa', [\App\Http\Controllers\Api\IntegracaoVagaAbertaController::class, 'getDadosEmpresa']);
});

Route::middleware(['api', 'apitoken'])->group(function () {
    Route::get('cbos', [CboController::class, 'index']);
    Route::get('cbos/{codigo}', [CboController::class, 'show'])->where('codigo', '[0-9]+');
});

Route::middleware(['api'])
    ->prefix('v2/integracao')
    ->group(function () {
        Route::get('media/{apelido}/logotipo', [\App\Http\Controllers\Api\IntegracaoSpaMediaController::class, 'logotipo'])
            ->where('apelido', '[a-zA-Z0-9_-]+')
            ->name('api.integracao_spa.media.logotipo');

        $apelidoPattern = '(?!media$)[a-zA-Z0-9_-]+';

        Route::middleware(['apitoken:spa_read', 'throttle:api-integracao-spa'])->group(function () use ($apelidoPattern) {
            Route::get('/', [\App\Http\Controllers\Api\IntegracaoSpaV2Controller::class, 'empresasAtivas']);
            Route::get('/empresas-ativas', [\App\Http\Controllers\Api\IntegracaoSpaV2Controller::class, 'empresasAtivas']);
            Route::get('{apelido}/vagas-abertas/{vaga_aberta_id}/{slug_vaga_aberta}', [\App\Http\Controllers\Api\IntegracaoSpaV2Controller::class, 'vagaAbertaDetalhe'])
                ->where('apelido', $apelidoPattern)
                ->whereNumber('vaga_aberta_id')
                ->where('slug_vaga_aberta', '[a-zA-Z0-9\-]+');
            Route::get('{apelido}/vagas-abertas', [\App\Http\Controllers\Api\IntegracaoSpaV2Controller::class, 'vagasAbertasPaginadas'])
                ->where('apelido', $apelidoPattern);
            Route::get('{apelido}', [\App\Http\Controllers\Api\IntegracaoSpaV2Controller::class, 'empresaComPreview'])
                ->where('apelido', $apelidoPattern);
        });

        Route::middleware(['apitoken:spa_write', 'throttle:api-integracao-spa'])->group(function () use ($apelidoPattern) {
            Route::post('{apelido}/busca-curriculo', [\App\Http\Controllers\Api\IntegracaoSpaCurriculoController::class, 'buscaCurriculo'])
                ->where('apelido', $apelidoPattern);
            Route::post('{apelido}/busca-cpf', [\App\Http\Controllers\Api\IntegracaoSpaCurriculoController::class, 'buscaCpf'])
                ->where('apelido', $apelidoPattern);
            Route::post('{apelido}/cadastra-curriculo', [\App\Http\Controllers\Api\IntegracaoSpaCurriculoController::class, 'cadastraCurriculo'])
                ->where('apelido', $apelidoPattern);
        });
    });

/*
|--------------------------------------------------------------------------
| API v3 (agents / integrações por empresa)
| Registrada ANTES das rotas com {empresa_slug} para não conflitar.
|--------------------------------------------------------------------------
*/
Route::prefix('v3')->group(function () {
    Route::get('docs', [ApiExternaDocsController::class, 'ui'])->name('api.v3.docs');
    Route::get('openapi.yaml', [ApiExternaDocsController::class, 'openapi'])->name('api.v3.openapi');

    Route::middleware(['api', 'force.json', 'throttle:api-externa-token'])
        ->post('oauth/token', [ApiExternaOAuthTokenController::class, 'token'])
        ->name('api.v3.oauth.token');

    Route::middleware([
        'api',
        'force.json',
        'auth:sanctum',
        'usuario.ativo',
        'api.externa.user',
        'throttle:api-externa',
    ])->group(function () {
        $publicId = \App\Support\ApiExterna\ApiExternaId::routePattern();

        Route::middleware('api.externa.scope:' . ApiExternaScopes::CLOUD_READ)->group(function () use ($publicId) {
            Route::get('cloud', [ApiExternaCloudController::class, 'index']);
            Route::get('cloud/{publicId}', [ApiExternaCloudController::class, 'show'])->where('publicId', $publicId);

            // Rotas nomeadas ANTES de {itemPublicId}
            Route::get('cloud/{publicId}/buscar', [ApiExternaCloudController::class, 'buscar'])->where('publicId', $publicId);
            Route::get('cloud/{publicId}/pastas', [ApiExternaCloudController::class, 'pastas'])->where('publicId', $publicId);
            Route::get('cloud/{publicId}/resolver-path', [ApiExternaCloudController::class, 'resolverPath'])->where('publicId', $publicId);
            Route::get('cloud/{publicId}/itens', [ApiExternaCloudController::class, 'itens'])->where('publicId', $publicId);

            Route::get('cloud/{publicId}/itens/{itemPublicId}/download', [ApiExternaCloudController::class, 'downloadItem'])
                ->where(['publicId' => $publicId, 'itemPublicId' => $publicId]);
            Route::get('cloud/{publicId}/itens/{itemPublicId}/arquivo', [ApiExternaCloudController::class, 'arquivoItem'])
                ->where(['publicId' => $publicId, 'itemPublicId' => $publicId]);
            Route::get('cloud/{publicId}/itens/{itemPublicId}/thumb', [ApiExternaCloudController::class, 'thumbItem'])
                ->where(['publicId' => $publicId, 'itemPublicId' => $publicId]);
            Route::get('cloud/{publicId}/itens/{itemPublicId}', [ApiExternaCloudController::class, 'showItem'])
                ->where(['publicId' => $publicId, 'itemPublicId' => $publicId]);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::FORNECEDORES_READ)->group(function () use ($publicId) {
            Route::get('fornecedores', [ApiExternaFornecedorController::class, 'index']);
            Route::get('fornecedores/buscar', [ApiExternaFornecedorController::class, 'buscar']);
            Route::get('fornecedores/tipos-servico', [ApiExternaFornecedorController::class, 'tiposServico']);

            Route::get('fornecedores/{publicId}', [ApiExternaFornecedorController::class, 'show'])->where('publicId', $publicId);
            Route::get('fornecedores/{publicId}/servicos', [ApiExternaFornecedorController::class, 'servicos'])->where('publicId', $publicId);
            Route::get('fornecedores/{publicId}/servicos/{servicoPublicId}', [ApiExternaFornecedorController::class, 'showServico'])
                ->where(['publicId' => $publicId, 'servicoPublicId' => $publicId]);

            Route::get('fornecedores/{publicId}/anexos/{arquivoPublicId}/download', [ApiExternaFornecedorController::class, 'downloadAnexo'])
                ->where(['publicId' => $publicId, 'arquivoPublicId' => $publicId]);
            Route::get('fornecedores/{publicId}/anexos/{arquivoPublicId}/arquivo', [ApiExternaFornecedorController::class, 'arquivoAnexo'])
                ->where(['publicId' => $publicId, 'arquivoPublicId' => $publicId]);

            Route::get(
                'fornecedores/{publicId}/servicos/{servicoPublicId}/anexos/{arquivoPublicId}/download',
                [ApiExternaFornecedorController::class, 'downloadAnexoServico']
            )->where([
                'publicId' => $publicId,
                'servicoPublicId' => $publicId,
                'arquivoPublicId' => $publicId,
            ]);
            Route::get(
                'fornecedores/{publicId}/servicos/{servicoPublicId}/anexos/{arquivoPublicId}/arquivo',
                [ApiExternaFornecedorController::class, 'arquivoAnexoServico']
            )->where([
                'publicId' => $publicId,
                'servicoPublicId' => $publicId,
                'arquivoPublicId' => $publicId,
            ]);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::AVALIACOES_READ)->group(function () use ($publicId) {
            Route::get('avaliacoes', [ApiExternaAvaliacaoController::class, 'index']);
            Route::get('avaliacoes/tipos', [ApiExternaAvaliacaoController::class, 'tipos']);
            Route::get('avaliacoes/tipos-avaliador', [ApiExternaAvaliacaoController::class, 'tiposAvaliador']);
            Route::get('avaliacoes/tipos/{tipoPublicId}/topicos', [ApiExternaAvaliacaoController::class, 'topicos'])
                ->where('tipoPublicId', $publicId);

            Route::get('avaliacoes/{publicId}', [ApiExternaAvaliacaoController::class, 'show'])->where('publicId', $publicId);
            Route::get('avaliacoes/{publicId}/feedbacks', [ApiExternaAvaliacaoController::class, 'feedbacks'])->where('publicId', $publicId);
            Route::get('avaliacoes/{publicId}/feedbacks/{feedbackPublicId}', [ApiExternaAvaliacaoController::class, 'showFeedback'])
                ->where(['publicId' => $publicId, 'feedbackPublicId' => $publicId]);
            Route::get(
                'avaliacoes/{publicId}/feedbacks/{feedbackPublicId}/resultado',
                [ApiExternaAvaliacaoController::class, 'feedbackResultado']
            )->where(['publicId' => $publicId, 'feedbackPublicId' => $publicId]);
            Route::get(
                'avaliacoes/{publicId}/colaboradores/{funcionarioPublicId}/feedbacks',
                [ApiExternaAvaliacaoController::class, 'colaboradorFeedbacks']
            )->where(['publicId' => $publicId, 'funcionarioPublicId' => $publicId]);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::PROCESSOS_READ)->group(function () use ($publicId) {
            Route::get('processos/ativos', [ApiExternaProcessoController::class, 'ativos']);
            Route::get('processos/em-andamento', [ApiExternaProcessoController::class, 'emAndamento']);
            Route::get('processos/demitidos', [ApiExternaProcessoController::class, 'demitidos']);
            Route::get('processos/buscar', [ApiExternaProcessoController::class, 'buscar']);

            Route::get('processos/{publicId}', [ApiExternaProcessoController::class, 'show'])->where('publicId', $publicId);
            Route::get('processos/{publicId}/etapas', [ApiExternaProcessoController::class, 'etapas'])->where('publicId', $publicId);
            Route::get('processos/{publicId}/exames', [ApiExternaProcessoController::class, 'exames'])->where('publicId', $publicId);
            Route::get('processos/{publicId}/documentos', [ApiExternaProcessoController::class, 'documentos'])->where('publicId', $publicId);
            Route::get('processos/{publicId}/ferias', [ApiExternaProcessoController::class, 'ferias'])->where('publicId', $publicId);
            Route::get('processos/{publicId}/pre-admissao', [ApiExternaProcessoController::class, 'preAdmissao'])->where('publicId', $publicId);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::CURRICULOS_READ)->group(function () use ($publicId) {
            Route::get('curriculos', [ApiExternaCurriculoController::class, 'index']);
            Route::get('curriculos/buscar', [ApiExternaCurriculoController::class, 'buscar']);

            Route::get('curriculos/{publicId}', [ApiExternaCurriculoController::class, 'show'])->where('publicId', $publicId);
            Route::get('curriculos/{publicId}/feedbacks', [ApiExternaCurriculoController::class, 'feedbacks'])->where('publicId', $publicId);
            Route::get('curriculos/{publicId}/documentos', [ApiExternaCurriculoController::class, 'documentos'])->where('publicId', $publicId);
            Route::get('curriculos/{publicId}/experiencias', [ApiExternaCurriculoController::class, 'experiencias'])->where('publicId', $publicId);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::FLUXO_READ)->group(function () use ($publicId) {
            Route::get('fluxo/{publicId}', [ApiExternaFluxoController::class, 'show'])->where('publicId', $publicId);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::VAGAS_READ)->group(function () use ($publicId) {
            Route::get('vagas-abertas', [ApiExternaVagaAbertaController::class, 'index']);
            Route::get('vagas-abertas/buscar', [ApiExternaVagaAbertaController::class, 'buscar']);
            Route::get('vagas-abertas/{publicId}', [ApiExternaVagaAbertaController::class, 'show'])->where('publicId', $publicId);
            Route::get('vagas-abertas/{publicId}/simulados', [ApiExternaVagaAbertaController::class, 'simulados'])->where('publicId', $publicId);
            Route::get('vagas-abertas/{publicId}/projetos', [ApiExternaVagaAbertaController::class, 'projetos'])->where('publicId', $publicId);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::CENTROS_CUSTO_READ)->group(function () use ($publicId) {
            Route::get('centros-custo', [ApiExternaCentroCustoController::class, 'index']);
            Route::get('centros-custo/buscar', [ApiExternaCentroCustoController::class, 'buscar']);
            Route::get('centros-custo/{publicId}', [ApiExternaCentroCustoController::class, 'show'])->where('publicId', $publicId);
            Route::get('centros-custo/{publicId}/filiais', [ApiExternaCentroCustoController::class, 'filiais'])->where('publicId', $publicId);
            Route::get('centros-custo/{publicId}/gestores', [ApiExternaCentroCustoController::class, 'gestores'])->where('publicId', $publicId);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::CIH_READ)->group(function () use ($publicId) {
            Route::get('cih', [ApiExternaCihController::class, 'index']);
            Route::get('cih/tags', [ApiExternaCihController::class, 'tags']);
            Route::get('cih/{publicId}', [ApiExternaCihController::class, 'show'])->where('publicId', $publicId);
            Route::get('cih/{publicId}/colaboradores', [ApiExternaCihController::class, 'colaboradores'])->where('publicId', $publicId);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::MEDIDAS_READ)->group(function () use ($publicId) {
            Route::get('medidas-administrativas', [ApiExternaMedidaController::class, 'index']);
            Route::get('medidas-administrativas/{publicId}', [ApiExternaMedidaController::class, 'show'])->where('publicId', $publicId);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::HISTORICO_READ)->group(function () use ($publicId) {
            Route::get('historico', [ApiExternaHistoricoController::class, 'index']);
            Route::get('historico/{publicId}', [ApiExternaHistoricoController::class, 'show'])->where('publicId', $publicId);
            Route::get('historico/{publicId}/medidas', [ApiExternaHistoricoController::class, 'medidas'])->where('publicId', $publicId);
            Route::get('historico/{publicId}/afastamentos', [ApiExternaHistoricoController::class, 'afastamentos'])->where('publicId', $publicId);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::POS_ADMISSAO_READ)->group(function () use ($publicId) {
            Route::get('pos-admissao', [ApiExternaPosAdmissaoController::class, 'index']);
            Route::get('pos-admissao/{publicId}', [ApiExternaPosAdmissaoController::class, 'show'])->where('publicId', $publicId);
            Route::get('pos-admissao/{publicId}/entrevista-desligamento', [ApiExternaPosAdmissaoController::class, 'entrevistaDesligamento'])
                ->where('publicId', $publicId);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::WEEKLY_READ)->group(function () use ($publicId) {
            Route::get('weekly', [ApiExternaWeeklyController::class, 'index']);
            Route::get('weekly/{publicId}', [ApiExternaWeeklyController::class, 'show'])->where('publicId', $publicId);
            Route::get('weekly/{publicId}/listas', [ApiExternaWeeklyController::class, 'listas'])->where('publicId', $publicId);
            Route::get('weekly/{publicId}/listas/{listaPublicId}/tarefas', [ApiExternaWeeklyController::class, 'tarefas'])
                ->where(['publicId' => $publicId, 'listaPublicId' => $publicId]);
            Route::get('weekly/{publicId}/tarefas/{tarefaPublicId}', [ApiExternaWeeklyController::class, 'showTarefa'])
                ->where(['publicId' => $publicId, 'tarefaPublicId' => $publicId]);
        });

        Route::middleware('api.externa.scope:' . ApiExternaScopes::RELATORIOS_READ)->group(function () {
            Route::get('relatorios', [ApiExternaRelatorioController::class, 'index']);
            Route::get('relatorios/{slug}', [ApiExternaRelatorioController::class, 'show'])
                ->where('slug', implode('|', \App\Services\ApiExterna\RelatorioApiService::SLUGS));
        });
    });
});

// Vitrine do portal de vagas (whitelist de campos). Exige X-API-TOKEN com spa_read.
// Registrada ANTES de {empresa_slug} para não ser capturada pelo catálogo por slug.
Route::middleware(['api', 'apitoken:spa_read', 'throttle:api-integracao-spa'])
    ->get('portal-vagas/empresas', [\App\Http\Controllers\PortalVagasBffController::class, 'empresasAtivas'])
    ->name('portal-vagas.empresas');

Route::group(['as' => 'vaga'], function () {
    // Catálogo público por slug (sem PII)
    Route::get('{empresa_slug}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'getVagasAbertasByEmpresa']);
    Route::get('{empresa_slug}/{vaga_aberta_id}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'getVagaAberta']);

    // POSTs de currículo: exigem apitoken (portal candidato usa BFF /publico/portal-vagas)
    Route::middleware(['apitoken:spa_write', 'throttle:api-integracao-spa'])->group(function () {
        Route::post('{empresa_id}/vaga-aberta/{vaga_aberta_id}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'atualizar']);
        Route::post('busca-curriculo', [\App\Http\Controllers\Api\VagaAbertaController::class, 'buscaCurriculo']);
        Route::post('busca-cpf', [\App\Http\Controllers\Api\VagaAbertaController::class, 'buscaCpf']);
        Route::post('cadastra-curriculo', [\App\Http\Controllers\Api\VagaAbertaController::class, 'store']);
    });
});


Route::group(['as' => 'projetos', 'prefix' => '{empresa_slug}'], function () {
    Route::post('projetos/lista', [\App\Http\Controllers\Api\ProjetoController::class, 'index'])->middleware('apitoken');
});

//Integra SGI com MYBP
Route::post('integra-sgi-mybp-aceita-carta-oferta', [\App\Http\Controllers\Api\IntegraSgiMybpController::class, 'aceiteCartaOferta'])
    ->middleware('apitoken');

Route::fallback(function () {
    return response()->json(['msg' => 'Não encontrado', 'success' => false], 404);
});
