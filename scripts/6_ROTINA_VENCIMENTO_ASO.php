<?php

use App\Models\AdmissaoAso;
use App\Models\ClienteConfig;
use App\Models\TipoRecebeEmail;
use App\Models\User;
use MasterTag\DataHora;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$kernel->terminate($request, $response);

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

unset($argv[0]);

try {
    $ClientesConfigs = ClienteConfig::get();

    foreach ($ClientesConfigs as $cliente_config) {
        $periodo_vencimento = ClienteConfig::LISTA_VENCIMENTOS[$cliente_config->vencimento_aso];
        $empresa_id = $cliente_config->cliente_id;

        $periodo_vencimento = preg_replace("/[^0-9]/", "", $periodo_vencimento);
        $data = new DataHora();
        $data->addDia($periodo_vencimento);

        $usuarios = User::withoutGlobalScopes()
            ->paraNotificacaoEmail(TipoRecebeEmail::VENCIMENTO_ASO, (int) $empresa_id)
            ->select(['users.id', 'users.nome', 'users.login'])
            ->get();

        foreach ($usuarios as $usuario) {
            $AdmissoesAso = AdmissaoAso::whereAtivo(true)->whereEmpresaId($empresa_id)
                ->whereHas('Admissao', function ($q) {
                    $q->withoutGlobalScopes()->Admitidos();
                })
                ->select(['id', 'empresa_id', 'admissao_id', 'data_aso', 'data_vencimento'])
                ->where('data_vencimento', '>=', (new DataHora())->dataInsert())
                ->where('data_vencimento', '<=', $data->dataInsert())
                ->with('Admissao', function ($a) {
                    $a->select(['id', 'feedback_id'])
                        ->Admitidos()
                        ->with('Feedback', function ($F) {
                            $F->withoutGlobalScopes()->select(['id', 'curriculo_id', 'empresa_id'])
                                ->with('Curriculo', function ($C) {
                                    $C->withoutGlobalScopes()->select(['id', 'nome', 'nascimento', 'rg', 'orgao_expeditor']);
                                });
                        });
                })
                ->orderBy('data_vencimento')
                ->get();

            $vencimentos = [];
            foreach ($AdmissoesAso as $vencimento) {
                $vencimentos[] = [
                    'colaborador' => $vencimento->Admissao->Feedback->Curriculo->nome,
                    'data_vencimento' => $vencimento->data_vencimento_formatada
                ];
            }

            if (!empty($vencimentos)) {
                \App\Jobs\JobMailVencimentoAso::dispatch([
                    'usuario' => $usuario,
                    'vencimentos' => $vencimentos,
                    'empresa_id' => $empresa_id,
                ])->delay(now()->addSeconds(5));
                \Log::info("E-mail de Vencimento ASO enviado com sucesso - {$usuario['nome']} - {$empresa_id}");
            }
        }

    }
} catch (\Exception $e) {
    Log::debug("Erro ao processar Vencimento ASO - {$usuario['nome']} - {$empresa_id}");
}
