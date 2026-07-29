<?php

namespace App\Services\Dossie;

use App\Models\Admissao;
use App\Models\Cliente;
use App\Models\EmpresaTemporaria;
use App\Models\FeedbackCurriculo;
use App\Models\Sistema;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Str;

class DossiePdfService
{
    public const MODELO_CONTRATO_TRABALHO = 'contratotrabalhoassinado';

    private const TIPOS_ADMISSAO_CONTRATO_ESPECIFICO = [
        Admissao::TIPO_ADMISSAO_TEMPORARIO,
        Admissao::TIPO_ADMISSAO_INTERMITENTE,
        Admissao::TIPO_ADMISSAO_DETERMINADO,
    ];

    /**
     * Gera o PDF do modelo de dossiê (contrato, termos, etc.).
     */
    public function gerar(FeedbackCurriculo $colaborador, Cliente $cliente, string $tipoModelo, string $solicitanteNome): PDF
    {
        $dados = $this->montarDados($colaborador, $solicitanteNome);
        [$view, $viewData] = $this->resolverView($tipoModelo, $colaborador, $cliente, $dados);

        $pdf = \PDF::loadView($view, $viewData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Monta o payload padrão compartilhado entre as views do dossiê.
     */
    public function montarDados(FeedbackCurriculo $colaborador, string $solicitanteNome): array
    {
        return [
            'dados_empresa' => Sistema::getEmpresaFilialMatriz(
                $colaborador->Admissao->centro_custo_filial_id,
                $colaborador->empresa_id
            ),
            'dados_colaborador' => $colaborador,
            'solicitante' => $solicitanteNome,
        ];
    }

    /**
     * Resolve a view Blade e os dados extras conforme tipo de modelo e admissão.
     *
     * @return array{0: string, 1: array}
     */
    public function resolverView(string $tipoModelo, FeedbackCurriculo $colaborador, Cliente $cliente, array $dados): array
    {
        $viewData = compact('dados', 'cliente');

        if ($tipoModelo !== self::MODELO_CONTRATO_TRABALHO) {
            return ['pdf.historico.dossie.' . $tipoModelo, $viewData];
        }

        $tipoAdmissao = $colaborador->Admissao->tipo_admissao;

        if (in_array($tipoAdmissao, self::TIPOS_ADMISSAO_CONTRATO_ESPECIFICO, true)) {
            $viewData['temporaria'] = EmpresaTemporaria::whereEmpresaId($colaborador->empresa_id)->first();

            return [
                'pdf.historico.dossie.contratos.' . Str::slug($tipoAdmissao),
                $viewData,
            ];
        }

        $viewCustomizada = "pdf.historico.dossie.customizado.{$cliente->apelido}.contratos.{$tipoModelo}";
        if (view()->exists($viewCustomizada)) {
            return [$viewCustomizada, $viewData];
        }

        return ['pdf.historico.dossie.default.contratos.' . $tipoModelo, $viewData];
    }
}
