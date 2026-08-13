<?php

/**
 * Corrige campos da importação CST 113803 já gravados:
 * - Área (cria AreaEtiqueta a partir do centro de custo / cod_area)
 * - Prazo de experiência FIXO → 45+45 (planilha veio vazia; padrão Maxtec)
 * - Data do ASO na tela (Examesesmt.data_realizacao) a partir da planilha
 *
 * Uso:
 *   docker compose exec mybpdp php scripts/corrigir_campos_cst_113803.php
 */

use App\Imports\Admissaoimport;
use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\AreaEtiqueta;
use App\Models\CentroCusto;
use App\Models\EmpresaExame;
use App\Models\ExameFuncionario;
use App\Models\Examesesmt;
use App\Models\Formulario;
use App\Models\Pcmso;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;
use PhpOffice\PhpSpreadsheet\Shared\Date;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

$empresaId = 113803;
Auth::loginUsingId($empresaId);

$planilha = base_path('scripts/importacao_cst_2026.xlsx');
$import = new Admissaoimport;
\Excel::import($import, $planilha);

echo "Corrigindo campos CST empresa {$empresaId} | linhas={$import->dados->count()}\n";

$infra = garantirInfraAso($empresaId);
echo "Infra ASO: clinica={$infra['empresa_exame_id']} form={$infra['formulario_id']} pcmso={$infra['pcmso_id']}\n";

$ok = 0;
$erros = 0;

foreach ($import->dados as $line) {
    $cpfMascarado = Sistema::mascaraCpf($line['cpf'] ?? '');
    if ($cpfMascarado === '' || $cpfMascarado === '..-') {
        continue;
    }

    try {
        DB::beginTransaction();

        $usuario = User::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereHas('Curriculo', fn ($q) => $q->where('cpf', $cpfMascarado))
            ->first();

        if (!$usuario || !$usuario->Curriculo || !$usuario->Curriculo->Feedback) {
            throw new RuntimeException('Colaborador/feedback não encontrado');
        }

        $feedback = $usuario->Curriculo->Feedback;
        $admissao = $feedback->Admissao;
        if (!$admissao) {
            throw new RuntimeException('Admissão não encontrada');
        }

        $centroCustoLabel = trim((string) ($line['centro_custo'] ?? ''));
        $codArea = trim((string) ($line['cod_area'] ?? ''));
        $labelArea = $codArea !== '' ? $codArea : $centroCustoLabel;

        $centroCusto = null;
        if ($centroCustoLabel !== '') {
            $centroCusto = CentroCusto::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->where('label', $centroCustoLabel)
                ->first();
            if (!$centroCusto) {
                $centroCusto = CentroCusto::create([
                    'label' => $centroCustoLabel,
                    'empresa_id' => $empresaId,
                    'gestor_id' => null,
                    'ativo' => true,
                ]);
            }
        }

        $areaId = null;
        if ($labelArea !== '') {
            $area = AreaEtiqueta::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->where('label', $labelArea)
                ->first();
            if (!$area) {
                $area = AreaEtiqueta::create([
                    'label' => $labelArea,
                    'empresa_id' => $empresaId,
                    'ativo' => true,
                    'gestor_id' => null,
                    'centro_custo_id' => $centroCusto?->id,
                ]);
            } elseif ($centroCusto && !$area->centro_custo_id) {
                $area->centro_custo_id = $centroCusto->id;
                $area->save();
            }
            $areaId = $area->id;
        }

        $tipo = mb_strtoupper(trim((string) ($line['tipo_admissao'] ?? Admissao::TIPO_ADMISSAO_FIXO)));
        $prazoPlanilha = trim((string) ($line['prazo_experiencia'] ?? ''));
        if ($prazoPlanilha !== '') {
            $prazo = $prazoPlanilha;
        } elseif ($tipo === Admissao::TIPO_ADMISSAO_FIXO) {
            $prazo = Admissao::QUARENTAECINCO_MAIS_QUARENTAECINCO;
        } else {
            $prazo = $admissao->prazo_experiencia;
        }

        $dataAdmissao = parseDataCst($line['data_admissao'] ?? null) ?? $admissao->getRawOriginal('data_admissao');
        $dataAso = parseDataCst($line['data_aso'] ?? null) ?? $dataAdmissao;
        $dataEntrega = parseDataCst($line['data_entrega_area'] ?? null) ?? $dataAdmissao;

        $admissao->update([
            'centro_custo_id' => $centroCusto?->id ?? $admissao->centro_custo_id,
            'area_etiqueta_id' => $areaId,
            'prazo_experiencia' => $prazo,
            'data_entrega_area' => $dataEntrega,
        ]);

        Admissao::tipoAdmissaoAvalNoventaCriarAtualizar(
            $feedback->id,
            $tipo !== '' ? $tipo : $admissao->tipo_admissao,
            $prazo,
            $dataAdmissao,
            parseDataCst($line['admissao_encerramento'] ?? null)
        );

        // Corrige AdmissaoAso (mantém convenção do sistema: data_aso = realização+1ano via criarAtualizar)
        AdmissaoAso::withoutGlobalScopes()
            ->where('admissao_id', $admissao->id)
            ->update(['ativo' => false]);
        AdmissaoAso::criarAtualizar($admissao->id, $empresaId, $dataAso);

        // ASO visível na tela (UltimoAso = Examesesmt.data_realizacao)
        criarOuAtualizarExameAso(
            $feedback->id,
            $empresaId,
            $dataAso,
            $dataAdmissao,
            $infra
        );

        DB::commit();
        $ok++;
        echo "OK {$cpfMascarado} | area={$labelArea} prazo={$prazo} aso={$dataAso}\n";
    } catch (Throwable $e) {
        DB::rollBack();
        $erros++;
        echo "ERRO {$cpfMascarado}: {$e->getMessage()}\n";
    }
}

echo "RESUMO: ok={$ok} erros={$erros}\n";

function garantirInfraAso(int $empresaId): array
{
    $formulario = Formulario::withoutGlobalScopes()
        ->where('empresa_id', $empresaId)
        ->where('titulo', 'Exames')
        ->first();
    if (!$formulario) {
        $formulario = Formulario::create([
            'titulo' => 'Exames',
            'descricao' => 'Formulário de exames (importação CST)',
            'empresa_id' => $empresaId,
        ]);
    }

    $pcmso = Pcmso::withoutGlobalScopes()->where('empresa_id', $empresaId)->first();
    if (!$pcmso) {
        $pcmso = Pcmso::create([
            'empresa_id' => $empresaId,
            'label' => 'Fixo',
            'ativo' => true,
        ]);
    }

    $clinica = EmpresaExame::withoutGlobalScopes()->where('empresa_id', $empresaId)->first();
    if (!$clinica) {
        $clinica = EmpresaExame::withoutEvents(function () use ($empresaId) {
            return EmpresaExame::create([
                'user_id' => $empresaId,
                'empresa_id' => $empresaId,
                'nome' => 'CLINICA IMPORTACAO CST',
                'dados' => [
                    'cnpj' => '00.000.000/0000-00',
                    'email' => 'clinica.cst.' . $empresaId . '@mybp.local',
                    'telefone' => '(00) 0000-0000',
                    'nome_fantasia' => 'CLINICA CST',
                    'endereco' => [
                        'uf' => 'MA',
                        'cep' => '65000-000',
                        'bairro' => 'CENTRO',
                        'municipio' => 'São Luís',
                        'end_numero' => 'SN',
                        'logradouro' => 'NAO INFORMADO',
                        'complemento' => null,
                    ],
                ],
                'ativo' => true,
            ]);
        });
    }

    return [
        'empresa_exame_id' => (int) $clinica->id,
        'formulario_id' => (int) $formulario->id,
        'pcmso_id' => (int) $pcmso->id,
    ];
}

function criarOuAtualizarExameAso(int $feedbackId, int $empresaId, string $dataAso, string $dataAdmissao, array $infra): void
{
    $diffDias = DataHora::diferencaDias($dataAdmissao, (new DataHora())->dataInsert());
    $exameTipoId = $diffDias <= 547 ? 1 : 2; // 1 Admissional, 2 Periodico

    $exameFuncionario = ExameFuncionario::withoutGlobalScopes()->updateOrCreate(
        [
            'feedback_id' => $feedbackId,
            'empresa_id' => $empresaId,
            'empresa_exame_id' => $infra['empresa_exame_id'],
            'formulario_id' => $infra['formulario_id'],
            'exame_tipo_id' => $exameTipoId,
        ],
        [
            'user_encaminhou_id' => $empresaId,
            'respostas' => (object) [],
            'token' => Sistema::uuid(),
            'pcmso' => true,
            'pcmso_id' => $infra['pcmso_id'],
            'encaminhamento_data' => $dataAso,
        ]
    );

    Examesesmt::withoutGlobalScopes()
        ->where('feedback_id', $feedbackId)
        ->where('empresa_id', $empresaId)
        ->update(['atual' => 0]);

    $resultado = [
        'result' => 'Apto',
        'aprovado' => 'Sim',
        'pendencias' => 'Não',
        'observacoes' => null,
        'trabalho_altura' => 'Não se aplica',
        'pendencias_quais' => null,
        'espacao_confinado' => 'Não se aplica',
    ];

    Examesesmt::withoutGlobalScopes()->updateOrCreate(
        [
            'feedback_id' => $feedbackId,
            'empresa_id' => $empresaId,
            'exame_funcionario_id' => $exameFuncionario->id,
        ],
        [
            'exame_realizado' => true,
            'resultado' => $resultado,
            'data_realizacao' => $dataAso,
            'data_vencimento' => \Carbon\Carbon::parse($dataAso)->addYear()->format('Y-m-d'),
            'vencido' => false,
            'atual' => true,
            'user_id' => $empresaId,
        ]
    );
}

function parseDataCst($date): ?string
{
    if ($date === null || $date === '') {
        return null;
    }
    try {
        if (is_numeric($date)) {
            return Date::excelToDateTimeObject($date)->format('Y-m-d');
        }
        return (new DataHora(trim((string) $date)))->dataInsert();
    } catch (Throwable $e) {
        return null;
    }
}
