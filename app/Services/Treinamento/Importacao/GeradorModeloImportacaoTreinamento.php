<?php

namespace App\Services\Treinamento\Importacao;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Admissao;
use RuntimeException;

/**
 * Gera planilha Excel interativa de importação de treinamentos.
 * Abas: Dados (preenchimento), Listas (dropdowns) e Instrucoes (manual resumido).
 */
class GeradorModeloImportacaoTreinamento
{
    public const ABA_DADOS = 'Dados';
    public const ABA_LISTAS = 'Listas';
    public const ABA_INSTRUCOES = 'Instrucoes';
    private const LINHAS_VALIDACAO = 2000;
    public const FORMATO_DATA = 'dd/mm/aaaa';
    public const EXEMPLO_DATA = '15/03/2026';

    /**
     * @return array{binario: string, nome_arquivo: string}
     */
    public function gerar(int $empresaId): array
    {
        if ($empresaId < 1) {
            throw new RuntimeException('empresa_id inválido para geração do modelo.');
        }

        $contratoPadrao = $this->resolverContratoPadrao($empresaId);
        $colaboradores = $this->listarColaboradores($empresaId);
        $treinamentos = $this->listarTreinamentos($empresaId, $contratoPadrao);

        $spreadsheet = new Spreadsheet();
        $dados = $spreadsheet->getActiveSheet();
        $dados->setTitle(self::ABA_DADOS);
        $dados->setCellValue('A1', 'colaborador');
        $dados->setCellValue('B1', 'treinamento');
        $dados->setCellValue('C1', 'data_treinamento');
        $dados->setCellValue('D1', 'data_vencimento');

        $listas = $spreadsheet->createSheet();
        $listas->setTitle(self::ABA_LISTAS);

        foreach ($treinamentos as $index => $valor) {
            $listas->setCellValue('A' . ($index + 1), $valor);
        }
        foreach ($colaboradores as $index => $valor) {
            $listas->setCellValue('B' . ($index + 1), $valor);
        }

        $this->aplicarValidacoes($dados, count($colaboradores), count($treinamentos));
        $this->aplicarFormatoData($dados);
        $this->criarAbaInstrucoes($spreadsheet);

        $dados->getColumnDimension('A')->setWidth(55);
        $dados->getColumnDimension('B')->setWidth(55);
        $dados->getColumnDimension('C')->setWidth(18);
        $dados->getColumnDimension('D')->setWidth(18);
        $listas->getColumnDimension('A')->setWidth(55);
        $listas->getColumnDimension('B')->setWidth(55);

        $spreadsheet->setActiveSheetIndex(0);

        $tmp = tmpfile();
        if ($tmp === false) {
            throw new RuntimeException('Não foi possível criar arquivo temporário do modelo.');
        }
        $meta = stream_get_meta_data($tmp);
        $tmpPath = $meta['uri'] ?? null;
        if (!$tmpPath) {
            fclose($tmp);
            throw new RuntimeException('Caminho temporário inválido para o modelo.');
        }

        (new Xlsx($spreadsheet))->save($tmpPath);
        $binario = file_get_contents($tmpPath);
        fclose($tmp);
        $spreadsheet->disconnectWorksheets();

        if ($binario === false) {
            throw new RuntimeException('Falha ao ler o binário do modelo gerado.');
        }

        return [
            'binario' => $binario,
            'nome_arquivo' => 'modelo_importacao_treinamentos_' . $empresaId . '.xlsx',
        ];
    }

    /**
     * @return list<string>
     */
    public function listarColaboradores(int $empresaId): array
    {
        $rows = DB::table('feedback_curriculos as fc')
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->leftJoin('admissoes as a', function ($join) {
                $join->on('a.feedback_id', '=', 'fc.id')
                    ->whereNull('a.deleted_at')
                    ->where(function ($q) {
                        // NULL NOT IN (...) é desconhecido no SQL — precisa do orWhereNull
                        $q->whereNull('a.status')
                            ->orWhereNotIn('a.status', [
                                Admissao::STATUS_ADMISSAO_STANDBY,
                                Admissao::STATUS_ADMISSAO_CANCELADO,
                                Admissao::STATUS_ADMISSAO_DESISTENCIA,
                                Admissao::STATUS_DEMITIDO,
                                Admissao::STATUS_DESMOBILIZADO,
                            ]);
                    });
            })
            ->leftJoin('vagas_abertas as va', 'va.id', '=', 'fc.vagas_abertas_id')
            ->leftJoin('vagas as v', 'v.id', '=', 'va.vaga_id')
            ->where('fc.empresa_id', $empresaId)
            ->whereNull('fc.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('resultado_integrados as ri')
                    ->whereColumn('ri.feedback_id', 'fc.id')
                    ->where('ri.encaminhado_treinamento', true);
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('demissaos as d')
                    ->whereColumn('d.feedback_id', 'fc.id');
            })
            ->orderBy('c.nome')
            ->select([
                'fc.id as feedback_id',
                'c.cpf',
                'c.nome',
                DB::raw('COALESCE(a.cargo, v.nome) as cargo'),
            ])
            ->get();

        $lista = [];
        foreach ($rows as $row) {
            $feedbackId = trim((string) ($row->feedback_id ?? ''));
            $cpf = trim((string) ($row->cpf ?? ''));
            $nome = trim((string) ($row->nome ?? ''));
            $cargo = trim((string) ($row->cargo ?? ''));
            // feedback_id vem de feedback_curriculos (fc.id), não do leftJoin de admissões
            if ($feedbackId === '' || $cpf === '' || $nome === '') {
                continue;
            }
            $lista[] = implode('|', [$feedbackId, $cpf, $nome, $cargo]);
        }

        return array_values(array_unique($lista));
    }

    /**
     * @return list<string>
     */
    public function listarTreinamentos(int $empresaId, ?string $contratoPadrao = null): array
    {
        $contrato = $contratoPadrao ?? $this->resolverContratoPadrao($empresaId);

        $rows = DB::table('vencimentos')
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->whereNotNull('label')
            ->orderBy('ordem')
            ->orderBy('label')
            ->select(['id', 'label'])
            ->get();

        $lista = [];
        foreach ($rows as $row) {
            $label = trim((string) ($row->label ?? ''));
            if ($label === '') {
                continue;
            }
            $lista[] = implode('|', [(string) $row->id, $contrato, $label]);
        }

        return $lista;
    }

    public function resolverContratoPadrao(int $empresaId): string
    {
        $contrato = DB::table('admissoes as a')
            ->join('feedback_curriculos as fc', 'fc.id', '=', 'a.feedback_id')
            ->where('fc.empresa_id', $empresaId)
            ->whereNull('a.deleted_at')
            ->whereNull('fc.deleted_at')
            ->whereNotNull('a.contrato')
            ->where('a.contrato', '!=', '')
            ->select('a.contrato', DB::raw('COUNT(*) as total'))
            ->groupBy('a.contrato')
            ->orderByDesc('total')
            ->value('a.contrato');

        $contrato = is_string($contrato) ? trim($contrato) : '';

        return $contrato !== '' ? $contrato : 'GERAL';
    }

    private function aplicarValidacoes($sheetDados, int $totalColaboradores, int $totalTreinamentos): void
    {
        $ultimaLinha = self::LINHAS_VALIDACAO;

        if ($totalColaboradores > 0) {
            $rangeColaboradores = self::ABA_LISTAS . '!$B$1:$B$' . $totalColaboradores;
            $validationColaborador = new DataValidation();
            $validationColaborador->setType(DataValidation::TYPE_LIST);
            $validationColaborador->setErrorStyle(DataValidation::STYLE_STOP);
            $validationColaborador->setAllowBlank(true);
            $validationColaborador->setShowInputMessage(true);
            $validationColaborador->setShowErrorMessage(true);
            $validationColaborador->setShowDropDown(true);
            $validationColaborador->setPromptTitle('Colaborador');
            $validationColaborador->setPrompt('Selecione um colaborador da lista.');
            $validationColaborador->setFormula1($rangeColaboradores);
            $sheetDados->setDataValidation("A2:A{$ultimaLinha}", $validationColaborador);
        }

        if ($totalTreinamentos > 0) {
            $rangeTreinamentos = self::ABA_LISTAS . '!$A$1:$A$' . $totalTreinamentos;
            $validationTreinamento = new DataValidation();
            $validationTreinamento->setType(DataValidation::TYPE_LIST);
            $validationTreinamento->setErrorStyle(DataValidation::STYLE_STOP);
            $validationTreinamento->setAllowBlank(true);
            $validationTreinamento->setShowInputMessage(true);
            $validationTreinamento->setShowErrorMessage(true);
            $validationTreinamento->setShowDropDown(true);
            $validationTreinamento->setPromptTitle('Treinamento');
            $validationTreinamento->setPrompt('Selecione um treinamento da lista.');
            $validationTreinamento->setFormula1($rangeTreinamentos);
            $sheetDados->setDataValidation("B2:B{$ultimaLinha}", $validationTreinamento);
        }

        $this->aplicarValidacaoData(
            $sheetDados,
            "C2:C{$ultimaLinha}",
            'Data do treinamento',
            'Formato obrigatório: ' . self::FORMATO_DATA . '. Exemplo: ' . self::EXEMPLO_DATA,
            'C2'
        );
        $this->aplicarValidacaoData(
            $sheetDados,
            "D2:D{$ultimaLinha}",
            'Data de vencimento (opcional)',
            'Opcional. Vazio = data_treinamento + prazo do treinamento. Preenchido: ' . self::FORMATO_DATA,
            'D2'
        );
    }

    private function aplicarValidacaoData($sheetDados, string $range, string $titulo, string $prompt, string $celulaFormula): void
    {
        $validationData = new DataValidation();
        $validationData->setType(DataValidation::TYPE_CUSTOM);
        $validationData->setErrorStyle(DataValidation::STYLE_WARNING);
        $validationData->setAllowBlank(true);
        $validationData->setShowInputMessage(true);
        $validationData->setShowErrorMessage(true);
        $validationData->setPromptTitle($titulo);
        $validationData->setPrompt($prompt);
        $validationData->setErrorTitle('Formato de data');
        $validationData->setError(
            'Use o formato ' . self::FORMATO_DATA . ' (exemplo: ' . self::EXEMPLO_DATA . ') ou deixe em branco.'
        );
        $validationData->setFormula1(
            'OR(' . $celulaFormula . '="",ISNUMBER(' . $celulaFormula . '),AND(LEN(' . $celulaFormula . ')=10,MID(' . $celulaFormula . ',3,1)="/",MID(' . $celulaFormula . ',6,1)="/"))'
        );
        $sheetDados->setDataValidation($range, $validationData);
    }

    private function aplicarFormatoData($sheetDados): void
    {
        $ultimaLinha = self::LINHAS_VALIDACAO;
        $sheetDados->getStyle("C2:C{$ultimaLinha}")
            ->getNumberFormat()
            ->setFormatCode('DD/MM/YYYY');
        $sheetDados->getStyle("D2:D{$ultimaLinha}")
            ->getNumberFormat()
            ->setFormatCode('DD/MM/YYYY');
        $sheetDados->getComment('C1')
            ->getText()
            ->createTextRun(
                'Preencha a data no formato ' . self::FORMATO_DATA . '. Exemplo: ' . self::EXEMPLO_DATA
            );
        $sheetDados->getComment('D1')
            ->getText()
            ->createTextRun(
                'Opcional. Vazio = calcula com o prazo do treinamento (igual carteira). Preenchido: ' . self::FORMATO_DATA
            );
    }

    private function criarAbaInstrucoes(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(self::ABA_INSTRUCOES);

        $linhas = [
            'Manual rápido — Importação de Treinamentos',
            '',
            '1) Use a aba Dados. Não altere os cabeçalhos da linha 1.',
            '2) Coluna colaborador: escolha no dropdown.',
            '3) Coluna treinamento: escolha no dropdown.',
            '4) Coluna data_treinamento: formato obrigatório ' . self::FORMATO_DATA . '.',
            '   Exemplo válido: ' . self::EXEMPLO_DATA,
            '   Evite: 15-03-2026, 15.03.2026, 3/15/2026 ou 15/03/26.',
            '5) Coluna data_vencimento (opcional):',
            '   - Vazia: usa data_treinamento + prazo do treinamento (igual carteira).',
            '   - Preenchida: usa o valor informado em ' . self::FORMATO_DATA . '.',
            '6) Criar e atualizar seguem o mesmo fluxo da carteira/etiquetas.',
            '7) Salve o arquivo .xlsx e envie na tela Importação.',
            '8) O resultado fica na aba Importados (CSV de sucesso e de erro), no sino e por e-mail.',
        ];

        foreach ($linhas as $index => $texto) {
            $sheet->setCellValue('A' . ($index + 1), $texto);
        }

        $sheet->getColumnDimension('A')->setWidth(110);
        $sheet->getStyle('A1')->getFont()->setBold(true);
    }
}
