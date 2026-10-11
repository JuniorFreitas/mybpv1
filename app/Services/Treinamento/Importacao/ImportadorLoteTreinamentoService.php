<?php

namespace App\Services\Treinamento\Importacao;

use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;
use RuntimeException;

/**
 * Processa importação de treinamentos em chunks (estilo CIH):
 * preload enxuto + multi-insert/update + histórico em lote.
 *
 * Paridade carteira/etiquetas (atualizarVencimento):
 * - busca Treinamento só por feedback_id
 * - cria se não existir (tipo Fixo)
 * - atualiza só o vencimento da linha (preserva demais + FAT/arquivo)
 *
 * Idempotência: chave feedback_id+vencimento_id.
 * Sem deduplicação prévia: ordem do arquivo; última linha do par prevalece no DB.
 */
class ImportadorLoteTreinamentoService
{
    public const CHUNK_SIZE = 1000;

    /**
     * @param list<array<string, mixed>> $linhas
     * @return list<array{
     *   nome: string,
     *   cpf: string,
     *   treinamento: string,
     *   data_treinamento: string,
     *   data_vencimento: string,
     *   status: string,
     *   mensagem: string,
     *   treinamento_id: int|null
     * }>
     */
    public function processarChunk(array $linhas, int $empresaId, int $userId): array
    {
        if ($empresaId < 1) {
            throw new RuntimeException('empresa_id inválido para importação em lote.');
        }

        $resultados = [];
        $normalizadas = [];

        foreach ($linhas as $index => $linha) {
            try {
                $normalizadas[] = $this->normalizarLinha($linha, $index);
            } catch (\Throwable $e) {
                $resultados[] = $this->resultadoFalha($linha, $e->getMessage());
            }
        }

        if ($normalizadas === []) {
            return $resultados;
        }

        // Última ocorrência por chave vence no DB; todas as linhas válidas entram no relatório
        $mapaFinal = [];
        foreach ($normalizadas as $item) {
            $mapaFinal[$item['chave']] = $item;
        }

        $escritas = $this->persistirLote(array_values($mapaFinal), $empresaId, $userId);

        foreach ($normalizadas as $item) {
            $chaveOriginal = $item['chave_original'] ?? $item['chave'];
            $escrita = $escritas[$chaveOriginal] ?? null;

            if ($escrita === null) {
                $resultados[] = $this->resultadoFalha(
                    $item,
                    'Feedback ou vencimento não encontrado.'
                );
                continue;
            }

            if (($escrita['status'] ?? '') === 'FALHA') {
                $resultados[] = $this->resultadoFalha($item, $escrita['mensagem'] ?? 'Falha ao persistir.');
                continue;
            }

            $resultados[] = [
                'nome' => $item['nome'],
                'cpf' => $item['cpf'],
                'treinamento' => $escrita['label'],
                'data_treinamento' => $item['data_treinamento_br'],
                'data_vencimento' => $escrita['data_vencimento_br'],
                'status' => 'SUCESSO',
                'mensagem' => $escrita['acao'] === 'criado'
                    ? 'Treinamento criado e histórico registrado'
                    : 'Treinamento atualizado e histórico registrado',
                'treinamento_id' => $escrita['treinamento_id'],
            ];
        }

        return $resultados;
    }

    /**
     * @param array<string, mixed> $linha
     * @return array<string, mixed>
     */
    private function normalizarLinha(array $linha, int $index): array
    {
        $cpf = $this->formatarCpf((string) ($linha['cpf'] ?? ''));
        $feedbackId = (int) ($linha['feedback_id'] ?? $linha['matricula'] ?? 0);
        $vencimentoId = (int) ($linha['vencimento_id'] ?? 0);
        $dataTreinamentoBr = trim((string) ($linha['data_treinamento'] ?? ''));
        $dataVencimentoBr = trim((string) ($linha['data_vencimento'] ?? ''));
        $nome = trim((string) ($linha['nome'] ?? ''));

        if ($cpf === '' || $vencimentoId < 1 || $dataTreinamentoBr === '') {
            throw new RuntimeException("Linha {$index}: cpf, vencimento_id e data_treinamento são obrigatórios.");
        }
        if (!$this->dataBrValida($dataTreinamentoBr)) {
            throw new RuntimeException("Linha {$index}: data_treinamento inválida. Use dd/mm/aaaa.");
        }
        if ($dataVencimentoBr !== '' && !$this->dataBrValida($dataVencimentoBr)) {
            throw new RuntimeException("Linha {$index}: data_vencimento inválida. Use dd/mm/aaaa.");
        }

        $chave = ($feedbackId > 0 ? (string) $feedbackId : $cpf) . '|' . $vencimentoId;

        return [
            'chave' => $chave,
            'chave_original' => $chave,
            'feedback_id' => $feedbackId,
            'cpf' => $cpf,
            'nome' => $nome !== '' ? $nome : 'Não informado',
            'vencimento_id' => $vencimentoId,
            'data_treinamento_br' => $dataTreinamentoBr,
            'data_vencimento_br' => $dataVencimentoBr,
            'tipo' => trim((string) ($linha['tipo'] ?? 'Fixo')) ?: 'Fixo',
        ];
    }

    /**
     * @param list<array<string, mixed>> $itens
     * @return array<string, array<string, mixed>>
     */
    private function persistirLote(array $itens, int $empresaId, int $userId): array
    {
        $feedbackIds = [];
        $cpfs = [];
        $vencimentoIds = [];
        foreach ($itens as $item) {
            if ($item['feedback_id'] > 0) {
                $feedbackIds[] = $item['feedback_id'];
            }
            $cpfs[] = $item['cpf'];
            $vencimentoIds[] = $item['vencimento_id'];
        }

        $feedbacks = $this->preloadFeedbacks($empresaId, array_values(array_unique($feedbackIds)), array_values(array_unique($cpfs)));
        $prazoPorVencimento = $this->preloadPrazos(array_values(array_unique($vencimentoIds)));
        $labels = $this->preloadLabels(array_values(array_unique($vencimentoIds)));

        $resolvidos = [];
        $erros = [];

        foreach ($itens as $item) {
            $feedback = null;
            if ($item['feedback_id'] > 0 && isset($feedbacks['by_id'][$item['feedback_id']])) {
                $feedback = $feedbacks['by_id'][$item['feedback_id']];
            } elseif (isset($feedbacks['by_cpf'][$item['cpf']])) {
                $feedback = $feedbacks['by_cpf'][$item['cpf']];
            }

            $chaveOriginal = $item['chave_original'] ?? $item['chave'];

            if ($feedback === null) {
                $erros[$chaveOriginal] = ['status' => 'FALHA', 'mensagem' => 'Feedback não encontrado'];
                continue;
            }
            if (!isset($prazoPorVencimento[$item['vencimento_id']]) && !isset($labels[$item['vencimento_id']])) {
                $erros[$chaveOriginal] = ['status' => 'FALHA', 'mensagem' => 'Vencimento não encontrado'];
                continue;
            }

            $item['feedback_id'] = (int) $feedback->feedback_id;
            $item['chave'] = $item['feedback_id'] . '|' . $item['vencimento_id'];
            if ($item['nome'] === 'Não informado' && !empty($feedback->nome)) {
                $item['nome'] = (string) $feedback->nome;
            }
            // last-wins por chave de domínio; mantém referência à chave original do mapaFinal
            $resolvidos[$item['chave']] = $item;
        }

        if ($resolvidos === []) {
            return $erros;
        }

        $feedbackIdsOk = array_values(array_unique(array_map(fn ($i) => $i['feedback_id'], $resolvidos)));
        $treinamentos = $this->preloadTreinamentos($feedbackIdsOk);

        $agora = now();
        $feedbacksParaCriar = [];
        foreach ($resolvidos as $item) {
            $fid = $item['feedback_id'];
            if (!isset($treinamentos[$fid])) {
                $feedbacksParaCriar[$fid] = true;
            }
        }

        $criarTreinamentos = [];
        foreach (array_keys($feedbacksParaCriar) as $fid) {
            $criarTreinamentos[] = [
                'feedback_id' => $fid,
                'cadastrou' => $userId > 0 ? $userId : $empresaId,
                'tipo' => 'Fixo',
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }

        $escritas = $erros;

        DB::transaction(function () use (
            &$escritas,
            $criarTreinamentos,
            $feedbacksParaCriar,
            &$treinamentos,
            $resolvidos,
            $prazoPorVencimento,
            $labels,
            $empresaId,
            $userId,
            $agora
        ) {
            if ($criarTreinamentos !== []) {
                foreach (array_chunk($criarTreinamentos, 500) as $lote) {
                    DB::table('treinamentos')->insert($lote);
                }
                $treinamentos = $treinamentos + $this->preloadTreinamentos(array_keys($feedbacksParaCriar));
            }

            $treinamentoIds = [];
            foreach ($resolvidos as $item) {
                $treinamentoIds[] = (int) $treinamentos[$item['feedback_id']]->id;
            }
            $pivots = $this->preloadPivots(array_values(array_unique($treinamentoIds)));

            $insertsPivot = [];
            $updatesPivot = [];
            $historicos = [];

            foreach ($resolvidos as $chave => $item) {
                $treinamentoId = (int) $treinamentos[$item['feedback_id']]->id;
                $vencimentoId = (int) $item['vencimento_id'];
                $pivotKey = $treinamentoId . '|' . $vencimentoId;
                $existiaPivot = isset($pivots[$pivotKey]);
                $criouTreinamento = isset($feedbacksParaCriar[$item['feedback_id']]);
                $acao = ($criouTreinamento && !$existiaPivot) ? 'criado' : 'atualizado';

                $dataTreinamentoSql = (new DataHora($item['data_treinamento_br']))->dataInsert();
                if ($item['data_vencimento_br'] !== '') {
                    $dataVencimentoSql = (new DataHora($item['data_vencimento_br']))->dataInsert();
                    $dataVencimentoBr = $item['data_vencimento_br'];
                } else {
                    $prazo = (int) ($prazoPorVencimento[$vencimentoId] ?? 0);
                    $dataHora = new DataHora($item['data_treinamento_br']);
                    $dataVencimentoBr = $prazo > 0
                        ? $dataHora->addDia($prazo, false)
                        : $item['data_treinamento_br'];
                    $dataVencimentoSql = (new DataHora($dataVencimentoBr))->dataInsert();
                }

                $numeroFat = $existiaPivot ? ($pivots[$pivotKey]->numero_fat ?? null) : null;

                $pivotRow = [
                    'treinamento_id' => $treinamentoId,
                    'vencimento_id' => $vencimentoId,
                    'data_treinamento' => $dataTreinamentoSql,
                    'data_vencimento' => $dataVencimentoSql,
                    'numero_fat' => $numeroFat,
                ];
                if ($this->pivotTemArquivoId()) {
                    $pivotRow['arquivo_id'] = $existiaPivot ? ($pivots[$pivotKey]->arquivo_id ?? null) : null;
                }

                if ($existiaPivot) {
                    $updatesPivot[] = $pivotRow;
                } else {
                    $insertsPivot[] = $pivotRow;
                }

                $historicos[] = [
                    'feedback_id' => $item['feedback_id'],
                    'empresa_id' => $empresaId,
                    'treinamento_id' => $treinamentoId,
                    'vencimento_id' => $vencimentoId,
                    'removido' => 0,
                    'user_id' => $userId > 0 ? $userId : $empresaId,
                    'treinamentos_vencimentos' => json_encode([[
                        'id' => $vencimentoId,
                        'pivot' => $pivotRow,
                    ]], JSON_UNESCAPED_UNICODE),
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ];

                $sucesso = [
                    'treinamento_id' => $treinamentoId,
                    'acao' => $acao,
                    'label' => $labels[$vencimentoId] ?? ('Vencimento ' . $vencimentoId),
                    'data_vencimento_br' => $dataVencimentoBr,
                    'status' => 'SUCESSO',
                ];
                $escritas[$chave] = $sucesso;
                $chaveOriginal = $item['chave_original'] ?? $chave;
                if ($chaveOriginal !== $chave) {
                    $escritas[$chaveOriginal] = $sucesso;
                }
            }

            if ($insertsPivot !== []) {
                foreach (array_chunk($insertsPivot, 500) as $lote) {
                    DB::table('treinamento_vencimento')->insert($lote);
                }
            }

            if ($updatesPivot !== []) {
                $this->multiUpdatePivots($updatesPivot);
            }

            if ($historicos !== []) {
                foreach (array_chunk($historicos, 500) as $lote) {
                    DB::table('treinamento_vencimento_historicos')->insert($lote);
                }
            }
        });

        return $escritas;
    }

    /**
     * Multi-update: delete + reinsert do lote (efeito detach+attach da carteira, em batch).
     * Preserva numero_fat/arquivo_id já embutidos nas linhas.
     *
     * @param list<array<string, mixed>> $updates
     */
    private function multiUpdatePivots(array $updates): void
    {
        foreach (array_chunk($updates, 500) as $lote) {
            $pairs = [];
            foreach ($lote as $row) {
                $pairs[] = [(int) $row['treinamento_id'], (int) $row['vencimento_id']];
            }

            DB::table('treinamento_vencimento')
                ->where(function ($q) use ($pairs) {
                    foreach ($pairs as [$tid, $vid]) {
                        $q->orWhere(function ($qq) use ($tid, $vid) {
                            $qq->where('treinamento_id', $tid)->where('vencimento_id', $vid);
                        });
                    }
                })
                ->delete();

            DB::table('treinamento_vencimento')->insert($lote);
        }
    }

    /**
     * @param list<int> $feedbackIds
     * @param list<string> $cpfs
     * @return array{by_id: array<int, object>, by_cpf: array<string, object>}
     */
    private function preloadFeedbacks(int $empresaId, array $feedbackIds, array $cpfs): array
    {
        $byId = [];
        $byCpf = [];

        if ($feedbackIds === [] && $cpfs === []) {
            return ['by_id' => [], 'by_cpf' => []];
        }

        $query = DB::table('feedback_curriculos as fc')
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->where('fc.empresa_id', $empresaId)
            ->whereNull('fc.deleted_at')
            ->whereNull('c.deleted_at')
            ->select(['fc.id as feedback_id', 'c.cpf', 'c.nome']);

        if ($feedbackIds !== [] && $cpfs !== []) {
            $query->where(function ($q) use ($feedbackIds, $cpfs) {
                $q->whereIn('fc.id', $feedbackIds)->orWhereIn('c.cpf', $cpfs);
            });
        } elseif ($feedbackIds !== []) {
            $query->whereIn('fc.id', $feedbackIds);
        } else {
            $query->whereIn('c.cpf', $cpfs);
        }

        foreach ($query->get() as $row) {
            $byId[(int) $row->feedback_id] = $row;
            $byCpf[(string) $row->cpf] = $row;
        }

        return ['by_id' => $byId, 'by_cpf' => $byCpf];
    }

    /**
     * @param list<int> $feedbackIds
     * @return array<int, object>
     */
    private function preloadTreinamentos(array $feedbackIds): array
    {
        if ($feedbackIds === []) {
            return [];
        }

        $map = [];
        $rows = DB::table('treinamentos')
            ->whereIn('feedback_id', $feedbackIds)
            ->select(['id', 'feedback_id', 'tipo', 'cadastrou'])
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            if (!isset($map[(int) $row->feedback_id])) {
                $map[(int) $row->feedback_id] = $row;
            }
        }

        return $map;
    }

    /**
     * @param list<int> $treinamentoIds
     * @return array<string, object>
     */
    private function preloadPivots(array $treinamentoIds): array
    {
        if ($treinamentoIds === []) {
            return [];
        }

        $map = [];
        $select = ['treinamento_id', 'vencimento_id', 'data_treinamento', 'data_vencimento', 'numero_fat'];
        if ($this->pivotTemArquivoId()) {
            $select[] = 'arquivo_id';
        }

        $rows = DB::table('treinamento_vencimento')
            ->whereIn('treinamento_id', $treinamentoIds)
            ->select($select)
            ->get();

        foreach ($rows as $row) {
            if (!isset($row->arquivo_id)) {
                $row->arquivo_id = null;
            }
            $map[(int) $row->treinamento_id . '|' . (int) $row->vencimento_id] = $row;
        }

        return $map;
    }

    private function pivotTemArquivoId(): bool
    {
        static $has = null;
        if ($has === null) {
            $has = \Schema::hasColumn('treinamento_vencimento', 'arquivo_id');
        }

        return $has;
    }

    /**
     * @param list<int> $vencimentoIds
     * @return array<int, int>
     */
    private function preloadPrazos(array $vencimentoIds): array
    {
        if ($vencimentoIds === []) {
            return [];
        }

        $map = [];
        foreach (DB::table('vencimentos')->whereIn('id', $vencimentoIds)->select(['id', 'prazo_fixo'])->get() as $row) {
            $map[(int) $row->id] = (int) ($row->prazo_fixo ?? 0);
        }

        return $map;
    }

    /**
     * @param list<int> $vencimentoIds
     * @return array<int, string>
     */
    private function preloadLabels(array $vencimentoIds): array
    {
        if ($vencimentoIds === []) {
            return [];
        }

        $map = [];
        foreach (DB::table('vencimentos')->whereIn('id', $vencimentoIds)->select(['id', 'label'])->get() as $row) {
            $map[(int) $row->id] = (string) ($row->label ?? ('Vencimento ' . $row->id));
        }

        return $map;
    }

    private function formatarCpf(string $cpf): string
    {
        $digitos = preg_replace('/\D+/', '', $cpf) ?? '';
        if (strlen($digitos) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digitos) ?? $cpf;
        }

        return trim($cpf);
    }

    private function dataBrValida(string $data): bool
    {
        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
            return false;
        }
        [$dia, $mes, $ano] = array_map('intval', explode('/', $data));

        return checkdate($mes, $dia, $ano);
    }

    /**
     * @param array<string, mixed> $linha
     * @return array{nome: string, cpf: string, treinamento: string, data_treinamento: string, data_vencimento: string, status: string, mensagem: string, treinamento_id: null}
     */
    private function resultadoFalha(array $linha, string $mensagem): array
    {
        return [
            'nome' => (string) ($linha['nome'] ?? 'Não informado'),
            'cpf' => (string) ($linha['cpf'] ?? 'N/A'),
            'treinamento' => 'N/A',
            'data_treinamento' => (string) ($linha['data_treinamento'] ?? $linha['data_treinamento_br'] ?? 'N/A'),
            'data_vencimento' => (string) ($linha['data_vencimento'] ?? $linha['data_vencimento_br'] ?? 'N/A'),
            'status' => 'FALHA',
            'mensagem' => $mensagem,
            'treinamento_id' => null,
        ];
    }
}
