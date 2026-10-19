<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaTreinamentos;
use App\Models\Admissao;
use App\Models\AuditoriaInterna;
use App\Models\Arquivo;
use App\Models\CarteiraAssinatura;
use App\Models\CentroCusto;
use App\Models\ClienteConfig;
use App\Models\PadraoTreinamento;
use App\Models\Pivot\TreinamentoVencimento;
use App\Models\ResultadoIntegrado;
use App\Models\Sistema;
use App\Models\FeedbackCurriculo;
use App\Models\Treinamento;
use App\Models\TreinamentoVencimentoHistorico;
use App\Models\Vencimento;
use App\Services\Treinamento\CarteiraEtiquetaBloqueioLayout;
use App\Services\Treinamento\CarteiraImagemCache;
use App\Services\Treinamento\FeedbackCurriculoFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mail;
use MasterTag\DataHora;

class TreinamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.treinamentos.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['cadastrou'] = auth()->id();

        try {
            DB::beginTransaction();

            $empresaId = (int) auth()->user()->empresa_id;
            $padraoId = $this->resolverPadraoTreinamentoId($dados)
                ?? $this->resolverPadraoDefaultEmpresa($empresaId);
            $dados['padrao_treinamento_id'] = $padraoId;
            $this->atualizarPadraoAdmissao($dados, $padraoId);
            $idsEscopo = $this->getVencimentosIdsPorPadrao($padraoId);

            $clienteConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
            $treinamento_permitir_desmarcar = $clienteConfig && ($clienteConfig->treinamento_permitir_desmarcar_realizado ?? false);
            $privilegio_gestao_rh = auth()->user()->can('privilegio_gestao_rh');
            $treinamento_retirar_realizado = auth()->user()->can('treinamento_carteira-etiquetas_retirar_treinamento_realizado');
            $pode_desmarcar = $treinamento_permitir_desmarcar && ($privilegio_gestao_rh || $treinamento_retirar_realizado);

            $isUpdate = isset($dados['id']);
            $treinamento = $isUpdate ? Treinamento::find($dados['id']) : null;
            $existingPivotsForAudit = null;

            $listaVencimentos = collect($dados['listaVencimentos']);
            if ($isUpdate && $treinamento) {
                $existingPivots = $treinamento->Vencimentos()->whereIn('vencimento_id', $idsEscopo)->get()->keyBy('vencimento_id');
                $existingPivotsForAudit = $existingPivots;
                $listaVencimentos = $listaVencimentos->map(function ($item) use ($existingPivots, $pode_desmarcar) {
                    $pivot = $existingPivots->get($item['id'] ?? null);
                    $wasRealized = $pivot !== null;
                    if ($wasRealized && empty($item['fez_treinamento']) && !$pode_desmarcar) {
                        $pivotData = $pivot->pivot ?? $pivot;
                        $merged = array_merge($item, [
                            'fez_treinamento' => true,
                            'data_treinamento' => $pivotData->data_treinamento ?? $item['data_treinamento'] ?? null,
                            'data_vencimento' => $pivotData->data_vencimento ?? $item['data_vencimento'] ?? null,
                            'numero_fat' => $pivotData->numero_fat ?? $item['numero_fat'] ?? null,
                        ]);
                        if (empty($merged['arquivo'])) {
                            $anexos = $this->carregarAnexosFat(
                                (int) $treinamento->id,
                                (int) ($item['id'] ?? 0),
                                $pivotData->arquivo_id ?? null
                            );
                            $merged['arquivo'] = collect($anexos)->map(fn ($a) => [
                                'id' => $a->id,
                                'temporario' => false,
                                'chave' => '',
                                'falhou' => false,
                            ])->all();
                        }
                        return $merged;
                    }
                    return $item;
                });
            }
            $listaVencimentos = $listaVencimentos->filter(function ($item) {
                return $item['fez_treinamento'];
            });

            // Só persiste o escopo do padrão atual (padrão + genéricos).
            // Realizados de outro padrão entram na UI, mas o pivot deles não é reanexado/apagado aqui.
            $listaParaPersistir = $listaVencimentos->filter(function ($item) use ($idsEscopo) {
                return $idsEscopo->contains((int) ($item['id'] ?? 0));
            })->values();

            $treinamento_fat_obrigatorio = $clienteConfig ? $clienteConfig->getConfig('treinamento_fat_obrigatorio', false) : false;
            if ($treinamento_fat_obrigatorio && $listaParaPersistir->isNotEmpty()) {
                $semFat = $listaParaPersistir->filter(function ($item) {
                    return empty(trim((string) ($item['numero_fat'] ?? '')));
                });
                if ($semFat->isNotEmpty()) {
                    return response()->json(['msg' => 'Número FAT é obrigatório para este cliente em treinamentos realizados.'], 400);
                }
            }

            $vencimentosDesmarcadosIds = collect();
            if ($isUpdate && $treinamento && $pode_desmarcar && $existingPivotsForAudit) {
                $vencimentosDesmarcadosIds = $existingPivotsForAudit->keys()->diff($listaParaPersistir->pluck('id'))->values();
            }

            if ($isUpdate) {
                $this->authorize('treinamento_carteira-etiquetas_update');

                unset(
                    $dados['enviado_email'],
                    $dados['email_aberto'],
                    $dados['data_email_aberto'],
                    $dados['data_envio'],
                    $dados['padrao_treinamento_id'],
                    $dados['listaVencimentos'],
                    $dados['listaVencimentosNaoVinculadosCargo']
                );
                $treinamento->update($dados);

                if ($idsEscopo->isNotEmpty()) {
                    if (Schema::hasTable('treinamento_vencimento_anexos')) {
                        DB::table('treinamento_vencimento_anexos')
                            ->where('treinamento_id', $treinamento->id)
                            ->whereIn('vencimento_id', $idsEscopo->all())
                            ->delete();
                    }
                    $treinamento->Vencimentos()->detach($idsEscopo->all());
                }
            } else {
                $this->authorize('treinamento_carteira-etiquetas_insert');
                $treinamento = Treinamento::create($dados);
            }

            $this->processarVencimentos($treinamento, $listaParaPersistir);
            $this->salvarHistorico($dados['feedback_id'], $treinamento->id);

            if ($pode_desmarcar && $vencimentosDesmarcadosIds->isNotEmpty()) {
                $this->registrarAuditoriaDesmarcarTreinamentoRealizado(
                    $dados['feedback_id'],
                    $vencimentosDesmarcadosIds
                );
            }

            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollback();

            $msg = "error Treinamento: {$e->getMessage()}, {$e->getCode()}, {$e->getLine()}, USUARIO: " . auth()->user()->nome;

            \Log::debug($msg);
            return response()->json(['msg' => 'Não foi possivel realizar o cadastro dos treinamentos'], 400);
        }
    }

    /**
     * Usar referência para Collection evita cópia desnecessária
     * @param Treinamento $treinamento
     * @param Collection &$listaVencimentos - REFERÊNCIA
     * @return void
     */
    private function processarVencimentos(Treinamento $treinamento, \Illuminate\Support\Collection &$listaVencimentos): void
    {
        // Preparar dados em lote para insert mais eficiente
        $dadosParaAnexar = [];
        $anexosPorVencimento = [];

        foreach ($listaVencimentos as &$lista) { // Referência no foreach
            $dataHora = new DataHora($lista['data_treinamento']);
            $dataTreinamento = $dataHora->dataInsert();

            // Usar somente prazo_fixo para vencimento (não há mais fixo/parada)
            $dataVencimento = $lista['prazo_fixo']
                ? $dataHora->addDia($lista['prazo_fixo'])
                : $lista['data_vencimento'];

            $this->processarArquivosDeletar($lista);
            $arquivoIds = $this->persistirArquivosFat($lista);
            $anexosPorVencimento[(int) $lista['id']] = $arquivoIds;

            // Acumula dados para insert em lote (arquivo_id = primeiro anexo, compatibilidade)
            $dadosParaAnexar[$lista['id']] = [
                'data_treinamento' => $dataTreinamento,
                'data_vencimento' => (new DataHora($dataVencimento))->dataInsert(),
                'numero_fat' => $lista['numero_fat'],
                'arquivo_id' => $arquivoIds[0] ?? null,
            ];
        }

        // Insert em lote - MUITO mais eficiente
        if (!empty($dadosParaAnexar)) {
            $treinamento->Vencimentos()->attach($dadosParaAnexar);
            foreach ($anexosPorVencimento as $vencimentoId => $arquivoIds) {
                $this->sincronizarAnexosFat((int) $treinamento->id, (int) $vencimentoId, $arquivoIds);
            }
        }
    }

    /**
     * Usar referência para array evita cópia
     * @param array &$lista - REFERÊNCIA
     * @return void
     */
    private function processarArquivosDeletar(array &$lista): void
    {
        if (!isset($lista['arquivoDel'])) {
            return;
        }

        // Usar whereIn para update em lote
        $idsParaDeletar = $lista['arquivoDel'];

        if (!empty($idsParaDeletar)) {
            Arquivo::whereIn('id', $idsParaDeletar)
                ->update([
                    'temporario' => true,
                    'chave' => \Str::uuid(),
                ]);
        }
    }

    /**
     * Verifica se o item (vencimento) possui ao menos um anexo FAT válido.
     * Usado quando treinamento_fat_obrigatorio está ativo.
     */
    private function itemTemAnexoFat(array $item): bool
    {
        if (empty($item['arquivo']) || !is_array($item['arquivo'])) {
            return false;
        }

        foreach ($item['arquivo'] as $arq) {
            if (!is_array($arq)) {
                continue;
            }
            if (!empty($arq['id']) && empty($arq['temporario'])) {
                return true;
            }
            if (!empty($arq['temporario']) && !empty($arq['chave']) && empty($arq['falhou'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Persiste até 5 anexos FAT e devolve os IDs na ordem do upload.
     *
     * @return list<int>
     */
    private function persistirArquivosFat(array &$lista): array
    {
        $arquivos = collect($lista['arquivo'] ?? [])
            ->filter(fn ($arq) => is_array($arq) && !empty($arq['id']))
            ->take(5)
            ->values();

        $ids = [];
        foreach ($arquivos as $arq) {
            $id = (int) $arq['id'];
            $ehTemporarioValido = !empty($arq['temporario'])
                && !empty($arq['chave'])
                && empty($arq['falhou']);

            if ($ehTemporarioValido) {
                $arquivo = Arquivo::find($id);
                if ($arquivo) {
                    $arquivo->update([
                        'chave' => '',
                        'nome' => $arq['nome'] ?? $arquivo->nome,
                        'temporario' => false,
                    ]);
                }
            }

            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param list<int> $arquivoIds
     */
    private function sincronizarAnexosFat(int $treinamentoId, int $vencimentoId, array $arquivoIds): void
    {
        if (!Schema::hasTable('treinamento_vencimento_anexos')) {
            return;
        }

        DB::table('treinamento_vencimento_anexos')
            ->where('treinamento_id', $treinamentoId)
            ->where('vencimento_id', $vencimentoId)
            ->delete();

        $rows = [];
        foreach (array_values($arquivoIds) as $ordem => $arquivoId) {
            $rows[] = [
                'treinamento_id' => $treinamentoId,
                'vencimento_id' => $vencimentoId,
                'arquivo_id' => $arquivoId,
                'ordem' => $ordem,
            ];
        }
        if ($rows) {
            DB::table('treinamento_vencimento_anexos')->insert($rows);
        }
    }

    /**
     * Carrega anexos FAT (nova tabela) com fallback para arquivo_id legado.
     *
     * @return list<\App\Models\Arquivo>
     */
    private function carregarAnexosFat(int $treinamentoId, int $vencimentoId, $arquivoIdLegado = null): array
    {
        $ids = [];
        if (Schema::hasTable('treinamento_vencimento_anexos')) {
            $ids = DB::table('treinamento_vencimento_anexos')
                ->where('treinamento_id', $treinamentoId)
                ->where('vencimento_id', $vencimentoId)
                ->orderBy('ordem')
                ->pluck('arquivo_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if (!$ids && $arquivoIdLegado) {
            $ids = [(int) $arquivoIdLegado];
        }

        if (!$ids) {
            return [];
        }

        $porId = Arquivo::query()->whereIn('id', $ids)->get()->keyBy('id');

        $lista = [];
        foreach ($ids as $id) {
            if (isset($porId[$id])) {
                $lista[] = $porId[$id];
            }
        }

        return $lista;
    }

    /**
     * Usar eager loading para evitar N+1
     * @param int $feedbackId
     * @param int $treinamentoId
     * @return void
     */
    private function salvarHistorico(int $feedbackId, int $treinamentoId): void
    {
        // Usar with() para eager loading - evita query N+1
        $treinamento = Treinamento::with('Vencimentos')->find($treinamentoId);

        TreinamentoVencimentoHistorico::create([
            'feedback_id' => $feedbackId,
            'empresa_id' => auth()->user()->empresa_id,
            'treinamento_id' => $treinamentoId,
            'user_id' => auth()->id(),
            'treinamentos_vencimentos' => $treinamento->Vencimentos
        ]);
    }

    /**
     * Grava histórico apenas do vencimento alterado (1 a 1). Usado por atualizarVencimento.
     */
    private function salvarHistoricoVencimento(int $feedbackId, int $treinamentoId, int $vencimentoId, bool $removido): void
    {
        $payload = [
            'feedback_id' => $feedbackId,
            'empresa_id' => auth()->user()->empresa_id,
            'treinamento_id' => $treinamentoId,
            'vencimento_id' => $vencimentoId,
            'removido' => $removido,
            'user_id' => auth()->id(),
        ];

        if ($removido) {
            $payload['treinamentos_vencimentos'] = [];
        } else {
            $treinamento = Treinamento::with(['Vencimentos' => fn ($q) => $q->where('vencimento_id', $vencimentoId)])
                ->find($treinamentoId);
            $payload['treinamentos_vencimentos'] = $treinamento->Vencimentos;
        }

        TreinamentoVencimentoHistorico::create($payload);
    }

    /**
     * Atualiza apenas um vencimento do treinamento (1 a 1). Histórico registra só essa alteração.
     * Request: feedback_id, vencimento (objeto com id, fez_treinamento, data_treinamento, data_vencimento, numero_fat, arquivo, etc.)
     */
    public function atualizarVencimento(Request $request): JsonResponse
    {
        $request->validate([
            'feedback_id' => 'required|integer',
            'vencimento' => 'required|array',
            'vencimento.id' => 'required|integer',
            'padrao_treinamento_id' => 'nullable|integer',
        ]);

        $feedbackId = (int) $request->input('feedback_id');
        $item = $request->input('vencimento');
        $vencimentoId = (int) $item['id'];
        $empresaId = auth()->user()->empresa_id;

        $treinamento = Treinamento::whereFeedbackId($feedbackId)
            ->whereHas('FeedbackCurriculo', fn ($q) => $q->where('empresa_id', $empresaId))
            ->first();

        if (!$treinamento) {
            $feedback = FeedbackCurriculo::where('id', $feedbackId)->where('empresa_id', $empresaId)->first();
            if (!$feedback) {
                return response()->json(['msg' => 'Treinamento não encontrado.'], 404);
            }
            // $this->authorize('treinamento_carteira-etiquetas_insert');
            $treinamento = Treinamento::create([
                'feedback_id' => $feedbackId,
                'cadastrou' => auth()->id(),
                'tipo' => 'Fixo',
            ]);
        } else {
            $this->authorize('treinamento_carteira-etiquetas_update');
        }

        $clienteConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
        $treinamento_permitir_desmarcar = $clienteConfig && ($clienteConfig->treinamento_permitir_desmarcar_realizado ?? false);
        $privilegio_gestao_rh = auth()->user()->can('privilegio_gestao_rh');
        $treinamento_retirar_realizado = auth()->user()->can('treinamento_carteira-etiquetas_retirar_treinamento_realizado');
        $pode_desmarcar = $treinamento_permitir_desmarcar && ($privilegio_gestao_rh || $treinamento_retirar_realizado);

        $pivot = $treinamento->Vencimentos()->where('vencimento_id', $vencimentoId)->first();
        $wasRealized = $pivot !== null;
        if ($wasRealized && empty($item['fez_treinamento']) && !$pode_desmarcar) {
            $pivotData = $pivot->pivot ?? $pivot;
            $item = array_merge($item, [
                'fez_treinamento' => true,
                'data_treinamento' => $pivotData->data_treinamento ?? $item['data_treinamento'] ?? null,
                'data_vencimento' => $pivotData->data_vencimento ?? $item['data_vencimento'] ?? null,
                'numero_fat' => $pivotData->numero_fat ?? $item['numero_fat'] ?? null,
            ]);
            $anexosExistentes = $this->carregarAnexosFat(
                (int) $treinamento->id,
                $vencimentoId,
                $pivotData->arquivo_id ?? null
            );
            if ($anexosExistentes && empty($item['arquivo'])) {
                $item['arquivo'] = collect($anexosExistentes)->map(fn ($a) => [
                    'id' => $a->id,
                    'temporario' => false,
                    'chave' => '',
                    'falhou' => false,
                ])->all();
            }
        }

        $treinamento_fat_obrigatorio = $clienteConfig ? $clienteConfig->getConfig('treinamento_fat_obrigatorio', false) : false;
        if ($treinamento_fat_obrigatorio && !empty($item['fez_treinamento']) && !$this->itemTemAnexoFat($item)) {
            return response()->json(['msg' => 'Anexo da FAT é obrigatório para este cliente em treinamentos realizados.'], 400);
        }

        DB::beginTransaction();
        try {
            // Troca de padrão: persiste na admissão
            if (array_key_exists('padrao_treinamento_id', $request->all())) {
                $padraoId = $request->filled('padrao_treinamento_id')
                    ? (int) $request->input('padrao_treinamento_id')
                    : null;
                if ($padraoId) {
                    $valido = PadraoTreinamento::query()
                        ->where('id', $padraoId)
                        ->where('empresa_id', $empresaId)
                        ->exists();
                    $padraoId = $valido ? $padraoId : null;
                }
                $this->atualizarPadraoAdmissao([
                    'feedback_id' => $feedbackId,
                    'padrao_treinamento_id' => $padraoId,
                ], $padraoId);
            }

            if (Schema::hasTable('treinamento_vencimento_anexos')) {
                DB::table('treinamento_vencimento_anexos')
                    ->where('treinamento_id', $treinamento->id)
                    ->where('vencimento_id', $vencimentoId)
                    ->delete();
            }
            $treinamento->Vencimentos()->detach($vencimentoId);

            if (!empty($item['fez_treinamento'])) {
                $listaUmItem = collect([$item]);
                $this->processarVencimentos($treinamento, $listaUmItem);
            }

            $this->salvarHistoricoVencimento($feedbackId, $treinamento->id, $vencimentoId, empty($item['fez_treinamento']));
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::debug('atualizarVencimento: ' . $e->getMessage());
            return response()->json(['msg' => 'Não foi possível salvar o treinamento.'], 400);
        }
    }

    /**
     * Desmarca um treinamento realizado (salvamento individual). Exige config ativa e (privilegio_gestao_rh ou treinamento_carteira-etiquetas_retirar_treinamento_realizado).
     * Recebe motivo e registra auditoria.
     *
     * @param Request $request feedback_id, vencimento_id, motivo
     * @return JsonResponse
     */
    public function desmarcarTreinamentoRealizado(Request $request): JsonResponse
    {
        $request->validate([
            'feedback_id' => 'required|integer',
            'vencimento_id' => 'required|integer',
            'motivo' => 'required|string|max:1000',
        ]);

        $clienteConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
        $treinamento_permitir_desmarcar = $clienteConfig && ($clienteConfig->treinamento_permitir_desmarcar_realizado ?? false);
        $privilegio_gestao_rh = auth()->user()->can('privilegio_gestao_rh');
        $treinamento_retirar_realizado = auth()->user()->can('treinamento_carteira-etiquetas_retirar_treinamento_realizado');
        $pode_desmarcar = $treinamento_permitir_desmarcar && ($privilegio_gestao_rh || $treinamento_retirar_realizado);

        if (!$pode_desmarcar) {
            return response()->json(['msg' => 'Sem permissão para desmarcar treinamento realizado.'], 403);
        }

        $this->authorize('treinamento_carteira-etiquetas_update');

        $treinamento = Treinamento::whereFeedbackId($request->input('feedback_id'))
            ->whereHas('FeedbackCurriculo', function ($q) {
                $q->where('empresa_id', auth()->user()->empresa_id);
            })
            ->first();

        if (!$treinamento) {
            return response()->json(['msg' => 'Treinamento não encontrado.'], 404);
        }

        $vencimentoId = (int) $request->input('vencimento_id');
        $motivo = $request->input('motivo');

        $pivotExists = $treinamento->Vencimentos()->where('vencimento_id', $vencimentoId)->exists();
        if (!$pivotExists) {
            return response()->json(['msg' => 'Este treinamento não estava marcado como realizado.'], 400);
        }

        DB::beginTransaction();
        try {
            $treinamento->Vencimentos()->detach($vencimentoId);

            $feedbackId = (int) $request->input('feedback_id');
            $this->salvarHistoricoVencimento($feedbackId, $treinamento->id, $vencimentoId, true);

            $this->registrarAuditoriaDesmarcarTreinamentoRealizado(
                $feedbackId,
                collect([$vencimentoId]),
                $motivo
            );
            DB::commit();
            return response()->json(['msg' => 'Treinamento desmarcado com sucesso.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::debug('desmarcarTreinamentoRealizado: ' . $e->getMessage());
            return response()->json(['msg' => 'Erro ao desmarcar treinamento.'], 400);
        }
    }

    /**
     * Registra auditoria quando usuário com privilegio_gestao_rh desmarca treinamento já realizado.
     *
     * @param int $feedbackId
     * @param \Illuminate\Support\Collection $vencimentosDesmarcadosIds IDs dos vencimentos desmarcados
     * @param string|null $motivo Motivo da retirada (quando desmarcado individualmente)
     * @return void
     */
    private function registrarAuditoriaDesmarcarTreinamentoRealizado(int $feedbackId, \Illuminate\Support\Collection $vencimentosDesmarcadosIds, ?string $motivo = null): void
    {
        $feedback = FeedbackCurriculo::whereId($feedbackId)->first(['id', 'curriculo_id']);
        $colaboradorId = $feedback ? $feedback->curriculo_id : null;

        $vencimentos = Vencimento::whereIn('id', $vencimentosDesmarcadosIds->all())->get(['id', 'label']);
        $listaLabels = $vencimentos->pluck('label', 'id')->all();
        $descricao = 'Treinamento(s) alterado(s) de "Realizado" para "Não realizado" (Gestão RH): ' . implode(', ', array_values($listaLabels));
        if ($motivo !== null && $motivo !== '') {
            $descricao .= ' | Motivo: ' . $motivo;
        }

        $dados = [
            'vencimentos_desmarcados' => $listaLabels,
            'vencimento_ids' => $vencimentosDesmarcadosIds->all(),
        ];
        if ($motivo !== null && $motivo !== '') {
            $dados['motivo_retirada'] = $motivo;
        }

        AuditoriaInterna::create([
            'empresa_id' => auth()->user()->empresa_id,
            'usuario_id' => auth()->id(),
            'feedback_id' => $feedbackId,
            'colaborador_id' => $colaboradorId,
            'tipo' => 'desmarcar_treinamento_realizado',
            'descricao' => $descricao,
            'dados' => $dados,
        ]);
    }

    public function storeMassa(Request $request)
    {
        $dados = $request->input();
        $dados['cadastrou'] = auth()->id();
        $exame = $dados['exame'];

        try {
            DB::beginTransaction();

            $listaVencimentos = collect($dados['listaVencimentos'])->filter(function ($item) {
                return $item['fez_treinamento'];
            });

            foreach ($dados['selecionadosMassa'] as $feedback_id) {

                $dados['feedback_id'] = $feedback_id;
                $treinamento = Treinamento::whereFeedbackId($feedback_id)->first();

                if (isset($treinamento)) {
                    $this->authorize('treinamento_carteira-etiquetas_update');

                    unset($dados['enviado_email']);
                    unset($dados['email_aberto']);
                    unset($dados['data_email_aberto']);
                    unset($dados['data_envio']);

                    $treinamento->update($dados);
                    $treinamento->Vencimentos()->detach();

                } else {
                    $this->authorize('treinamento_carteira-etiquetas_insert');
                    $treinamento = Treinamento::create($dados);
                }


                foreach ($listaVencimentos as $lista) {
                    $dataHora = new DataHora($lista['data_treinamento']);
                    $dataTreinamento = $dataHora->dataInsert();

                    // Usar somente prazo_fixo para vencimento (não há mais fixo/parada)
                    $dataVencimento = $lista['prazo_fixo'] ? $dataHora->addDia($lista['prazo_fixo']) : $lista['data_vencimento'];

                    $treinamento->Vencimentos()->attach($lista['id'], [
                        'data_treinamento' => $dataTreinamento,
                        'data_vencimento' => (new DataHora($dataVencimento))->dataInsert(),
                        'numero_fat' => $lista['numero_fat'] ?? null
                    ]);
                }
            }

            DB::commit();

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();

            $msg = "error Treinamento:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}, USUARIO: " . auth()->user()->nome;

            \Log::debug($msg);
//            return response()->json(['msg' => 'Não foi possivel realizar o cadastro'], 400);
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Treinamento $treinamento
     * @return \Illuminate\Http\Response
     */
    public function show(Treinamento $treinamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Treinamento $treinamento
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $treinamento)
    {
        $treinamento = ResultadoIntegrado::whereFeedbackId($treinamento)
            ->whereHas('Feedback', function ($q) {
                $q->where('empresa_id', auth()->user()->empresa_id);
            })
            ->first();

        if (!$treinamento) {
            return response()->json(['msg' => 'Treinamento não encontrado'], 404);
        }

        $treinamento = $treinamento->load(
            'Treinamento',
            'Treinamento.Vencimentos',
            'Treinamento.QuemCadastrou:id,nome',
            'Curriculo:id,nome,nascimento,cpf,pcd,uf,uf_vaga,email,rg,orgao_expeditor,logradouro,complemento,end_numero,bairro,municipio,cep',
            'Admissao.PadraoTreinamento:id,nome,tem_carteira,carteira_treinamento_id,tem_etiqueta_bloqueio,etiqueta_bloqueio_id',
            'Feedback.Exame',
            'Feedback.TelPrincipal:id,tipo,numero',
            'Feedback.Curriculo:id,nome,cpf,nascimento,pcd,uf,uf_vaga,email,rg,orgao_expeditor,logradouro,complemento,end_numero,bairro,municipio,cep',
            'Feedback.VagaAberta:id,vaga_id,titulo,municipio_id',
            'Feedback.VagaAberta.Vaga:id,nome',
            'Feedback.VagaAberta.Municipio:id,nome,uf',
            'Feedback.Demissao:id,feedback_id',
            'Feedback.Cliente:id,nome_fantasia,razao_social',
        );

        if (!is_null($treinamento->Admissao)) {
            $treinamento->nr_trinta_tres = $treinamento->Admissao->nr_trinta_tres == 'NÃO SE APLICA' ? false : true;
            $treinamento->nr_trinta_cinco = $treinamento->Admissao->nr_trinta_cinco == 'NÃO SE APLICA' ? false : true;
        } else {
            $treinamento->nr_trinta_tres = true;
            $treinamento->nr_trinta_cinco = true;
        }

        // Obter todos os relacionamentos treinamento_vencimento com seus arquivos
        $treinamentoVencimentos = null;
        if ($treinamento->Treinamento) {
            $treinamentoVencimentos = TreinamentoVencimento::with('arquivo')
                ->where('treinamento_id', $treinamento->Treinamento->id)
                ->get();

            // Adicionar os arquivos FAT aos vencimentos já carregados (até 5)
            foreach ($treinamento->Treinamento->Vencimentos as $vencimento) {
                $pivotData = $treinamentoVencimentos->where('vencimento_id', $vencimento->id)->first();
                $vencimento->arquivo = [];
                $vencimento->arquivoDel = [];
                if ($pivotData) {
                    $vencimento->arquivo = $this->carregarAnexosFat(
                        (int) $treinamento->Treinamento->id,
                        (int) $vencimento->id,
                        optional($pivotData)->arquivo_id ?? optional($pivotData->arquivo)->id
                    );
                }
            }
        }

        $empresaId = (int) auth()->user()->empresa_id;
        $padraoId = $request->filled('padrao_treinamento_id')
            ? (int) $request->input('padrao_treinamento_id')
            : (optional($treinamento->Admissao)->padrao_treinamento_id
                ? (int) $treinamento->Admissao->padrao_treinamento_id
                : $this->resolverPadraoDefaultEmpresa($empresaId));

        $treinamento->padrao_treinamento_id = $padraoId;
        $dadosVinculoCargo = $this->montarListaVencimentosPorPadrao($treinamento, $treinamentoVencimentos, $padraoId);
        // setAttribute + array: garante que extras (outro padrão) saem no JSON do edit
        $treinamento->setAttribute('listaVencimentos', $dadosVinculoCargo['listaVencimentos']->values()->all());
        $treinamento->setAttribute('listaVencimentosNaoVinculadosCargo', $dadosVinculoCargo['listaVencimentosNaoVinculadosCargo']->values()->all());
        $treinamento->setAttribute('alertaSemCargoVinculo', $dadosVinculoCargo['alertaSemCargoVinculo']);

        /*  $treinamento->listaVencimentos = Vencimento::whereAtivo(true)->orderBy('ordem')->get()->transform(function ($item) use ($treinamento, $treinamentoVencimentos) {
              if ($treinamento->Treinamento) {
                  $pivo = $treinamento->Treinamento->Vencimentos()->whereId($item->id);
                  $item->data_treinamento = $pivo->count() > 0 ? $pivo->first()->pivot->data_treinamento : null;
                  $item->data_vencimento = $pivo->count() > 0 ? $pivo->first()->pivot->data_vencimento : null;
                  $item->numero_fat = $pivo->count() > 0 ? $pivo->first()->pivot->numero_fat : null;
                  $item->fez_treinamento = $pivo->count() > 0 ? true : false;
                  $item->arquivo = [];
                  $item->arquivoDel = [];


  //                $item->Treinamento->Vencimentos->transform(function ($i) use ($item) {
  //                    $i->fez_treinamento = $item->Treinamento->Vencimentos()->where('vencimento_id', $i->id)->count() > 0 ? true : false;
  //                    $i->pivot->status = $this->StatusTreinamento($i->pivot->data_vencimento);
  //                    $i->dias_vencer = DataHora::diferencaDias((new DataHora())->dataInsert() . ' 00:00:00', (new DataHora($i->pivot->data_vencimento))->dataInsert() . ' 23:59:59');
  //                    return $i;
  //                });
  //
  //                $item->treinamento->vencimentos = $item->Treinamento->Vencimentos->sortBy('dias_vencer')->values()->all();
                  // Adicionar informações do arquivo
                  if ($treinamentoVencimentos) {
                      $pivotData = $treinamentoVencimentos->where('vencimento_id', $item->id)->first();
                      if ($pivotData) {
                          $item->arquivo = $pivotData->arquivo ? [$pivotData->arquivo] : [];
                      }
                  }
              } else {
                  $item->data_treinamento = null;
                  $item->data_vencimento = null;
                  $item->fez_treinamento = false;
                  $item->numero_fat = null;
                  $item->arquivo = [];
                  $item->arquivoDel = [];
              }

              return $item;
          });*/

        $treinamento->dadosFuncionario = $this->montarDadosFuncionarioModal(
            $treinamento,
            $padraoId,
            $empresaId
        );

        $clienteConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
        $treinamento->privilegio_gestao_rh = auth()->user()->can('privilegio_gestao_rh');
        $treinamento->treinamento_permitir_desmarcar_realizado = $clienteConfig ? (bool) ($clienteConfig->treinamento_permitir_desmarcar_realizado ?? false) : false;
        $treinamento->treinamento_retirar_treinamento_realizado = auth()->user()->can('treinamento_carteira-etiquetas_retirar_treinamento_realizado');
        $treinamento->treinamento_fat_obrigatorio = $clienteConfig ? $clienteConfig->getConfig('treinamento_fat_obrigatorio', false) : false;

        return response()->json($treinamento);
    }

    /**
     * Resumo enriquecido do colaborador para o header da modal (paridade com pré-admissão).
     *
     * @return array<string, mixed>
     */
    private function montarDadosFuncionarioModal(ResultadoIntegrado $treinamento, ?int $padraoId, int $empresaId): array
    {
        $feedback = $treinamento->Feedback;
        $curriculo = $feedback?->Curriculo ?? $treinamento->Curriculo;
        $admissao = $treinamento->Admissao;
        $vagaAberta = $feedback?->VagaAberta;

        $cargo = $vagaAberta?->Vaga?->nome
            ?? $vagaAberta?->titulo
            ?? $admissao?->cargo
            ?? null;

        $municipio = $vagaAberta?->Municipio;
        $localVaga = $municipio
            ? trim(($municipio->nome ?? '') . ' - ' . ($municipio->uf ?? ''), ' -')
            : null;

        $padraoInfo = $admissao?->PadraoTreinamento;
        if (!$padraoInfo && $padraoId) {
            $padraoInfo = PadraoTreinamento::query()
                ->where('empresa_id', $empresaId)
                ->where('id', $padraoId)
                ->first(['id', 'nome', 'tem_carteira', 'carteira_treinamento_id', 'tem_etiqueta_bloqueio', 'etiqueta_bloqueio_id']);
        }

        $empCnpj = null;
        $empNomeFantasia = null;
        $empCentroCusto = null;
        $empTipo = null;
        if ($admissao?->centro_custo_id) {
            $cc = (new CentroCusto())->listaCentroCustoPorCnpj($empresaId);
            $ccColaborador = collect($cc['centros_custos'] ?? [])->collapse()
                ->where('id', $admissao->centro_custo_id)
                ->first();
            if ($ccColaborador) {
                $empCnpj = $ccColaborador['cnpj_format'] ?? null;
                $empNomeFantasia = $ccColaborador['nome_fantasia'] ?? null;
                $empCentroCusto = $ccColaborador['label'] ?? null;
                $empTipo = !empty($ccColaborador['matriz']) ? 'Matriz' : 'Filial';
            }
        }

        $tel = $feedback?->TelPrincipal;
        $cpfDigitos = preg_replace('/\D+/', '', (string) ($curriculo?->cpf ?? ''));
        $cpfFormatado = strlen($cpfDigitos) === 11 ? Sistema::mascaraCpf($cpfDigitos) : ($curriculo?->cpf ?: null);

        $situacao = 'em_processo';
        if ($feedback?->Demissao) {
            $situacao = 'demitido';
        } elseif ($admissao && $admissao->status === Admissao::STATUS_ADMISSAO_ADMITIDO) {
            $situacao = 'admitido';
        } elseif ($admissao && in_array(mb_strtoupper((string) $admissao->status), ['DEMITIDO'], true)) {
            $situacao = 'demitido';
        }

        $cliente = $feedback?->Cliente;

        return [
            'nome' => $curriculo?->nome,
            'idade' => $curriculo?->idade,
            'cpf' => $cpfFormatado,
            'email' => $curriculo?->email,
            'uf' => $curriculo?->uf,
            'endereco_completo' => $curriculo?->endereco_completo,
            'telefone' => $tel?->numero,
            'telefone_tipo' => $tel?->tipo,
            'cargo' => $cargo,
            'local_vaga' => $localVaga,
            'status_admissao' => $admissao?->status,
            'tipo_admissao' => $admissao?->tipo_admissao,
            'numero_cracha' => $admissao?->numero_cracha,
            'situacao_colaborador' => $situacao,
            'emp_cnpj' => $empCnpj,
            'emp_nome_fantasia' => $empNomeFantasia,
            'emp_centro_custo' => $empCentroCusto,
            'emp_tipo' => $empTipo,
            'padrao_treinamento_nome' => $padraoInfo?->nome,
            'padrao_tem_carteira' => $padraoInfo
                ? ((bool) $padraoInfo->tem_carteira && !empty($padraoInfo->carteira_treinamento_id))
                : null,
            'padrao_tem_etiqueta' => $padraoInfo
                ? ((bool) $padraoInfo->tem_etiqueta_bloqueio && !empty($padraoInfo->etiqueta_bloqueio_id))
                : null,
            'cliente_nome' => $cliente?->nome_fantasia ?: $cliente?->razao_social,
        ];
    }

    public function vencimentosPorPadrao(Request $request): JsonResponse
    {
        $request->validate([
            'feedback_id' => 'required|integer',
            'padrao_treinamento_id' => 'nullable|integer',
        ]);

        $treinamento = ResultadoIntegrado::whereFeedbackId($request->input('feedback_id'))
            ->whereHas('Feedback', function ($q) {
                $q->where('empresa_id', auth()->user()->empresa_id);
            })
            ->first();
        if (!$treinamento) {
            return response()->json(['msg' => 'Treinamento não encontrado'], 404);
        }

        $treinamento = $treinamento->load('Treinamento', 'Treinamento.Vencimentos');
        $treinamentoVencimentos = null;
        if ($treinamento->Treinamento) {
            $treinamentoVencimentos = TreinamentoVencimento::with('arquivo')
                ->where('treinamento_id', $treinamento->Treinamento->id)
                ->get();
        }

        // Escopo pelo padrao_treinamento_id (pivot padrao_treinamento_vencimento).
        $padraoId = array_key_exists('padrao_treinamento_id', $request->all())
            ? ($request->filled('padrao_treinamento_id') ? (int) $request->input('padrao_treinamento_id') : null)
            : (optional($treinamento->Admissao)->padrao_treinamento_id ? (int) $treinamento->Admissao->padrao_treinamento_id : null);

        $dadosVinculoCargo = $this->montarListaVencimentosPorPadrao($treinamento, $treinamentoVencimentos, $padraoId);

        $clienteConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();

        return response()->json([
            'padrao_treinamento_id' => $padraoId,
            'listaVencimentos' => $dadosVinculoCargo['listaVencimentos'],
            'listaVencimentosNaoVinculadosCargo' => $dadosVinculoCargo['listaVencimentosNaoVinculadosCargo'],
            'alertaSemCargoVinculo' => $dadosVinculoCargo['alertaSemCargoVinculo'],
            'privilegio_gestao_rh' => auth()->user()->can('privilegio_gestao_rh'),
            'treinamento_permitir_desmarcar_realizado' => $clienteConfig ? (bool) ($clienteConfig->treinamento_permitir_desmarcar_realizado ?? false) : false,
            'treinamento_retirar_treinamento_realizado' => auth()->user()->can('treinamento_carteira-etiquetas_retirar_treinamento_realizado'),
            'treinamento_fat_obrigatorio' => $clienteConfig ? $clienteConfig->getConfig('treinamento_fat_obrigatorio', false) : false,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Treinamento $treinamento
     * @return JsonResponse
     */
    public function update(Request $request, $treinamento)
    {
        $dados = $request->input();
        $treinamento = ResultadoIntegrado::whereFeedbackId($treinamento)->first();
        try {
            DB::beginTransaction();
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::debug("error TREINAMENTO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}");
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Treinamento $treinamento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Treinamento $treinamento)
    {
        //
    }

    public function vencimentos()
    {
        $vencimentos = Vencimento::whereAtivo(true)->orderBy('ordem')->get();
        return response()->json($vencimentos, 200);
    }

    /**
     * Escopo do padrão (catálogo): vinculado na pivot padrao_treinamento_vencimento.
     */
    private function aplicarFiltroEscopoPadrao($query, ?int $padraoId)
    {
        if (!$padraoId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('PadroesTreinamento', function ($qPadrao) use ($padraoId) {
            $qPadrao->where('padroes_treinamento.id', $padraoId);
        });
    }

    /** Mesma regra do filtro em memória (listagem / PDF / realizados). */
    private function vencimentoNoEscopoPadrao($vencimento, ?int $padraoId): bool
    {
        if (!$padraoId) {
            return false;
        }

        $padroes = $vencimento->relationLoaded('PadroesTreinamento')
            ? $vencimento->PadroesTreinamento
            : $vencimento->PadroesTreinamento()->get(['padroes_treinamento.id', 'padroes_treinamento.nome']);

        return $padroes->contains(function ($p) use ($padraoId) {
            return (int) $p->id === (int) $padraoId;
        });
    }

    /**
     * Marca se o vencimento é de outro padrão e qual o nome (para mensagem na UI).
     */
    private function aplicarMarcacaoOutroPadrao($vencimento, ?int $padraoId): void
    {
        $deOutro = !$this->vencimentoNoEscopoPadrao($vencimento, $padraoId);
        $vencimento->mergeCasts(['de_outro_padrao' => 'boolean']);
        $vencimento->setAttribute('de_outro_padrao', $deOutro);
        $vencimento->setAttribute('padrao_origem_nome', null);

        if (!$deOutro) {
            return;
        }

        $padroes = $vencimento->relationLoaded('PadroesTreinamento')
            ? $vencimento->PadroesTreinamento
            : $vencimento->PadroesTreinamento()->get(['padroes_treinamento.id', 'padroes_treinamento.nome']);

        $nomes = $padroes
            ->filter(function ($p) use ($padraoId) {
                return !$padraoId || (int) $p->id !== (int) $padraoId;
            })
            ->pluck('nome')
            ->filter()
            ->unique()
            ->values();

        if ($nomes->isNotEmpty()) {
            $vencimento->setAttribute('padrao_origem_nome', $nomes->implode(', '));
        }
    }

    /**
     * Lista vencimentos do escopo (padrão + genéricos) e inclui realizados de outro
     * padrão e inclui realizados de outro padrão.
     */
    private function montarListaVencimentosPorPadrao(ResultadoIntegrado $treinamento, $treinamentoVencimentos, ?int $padraoId): array
    {
        $vagaId = optional($treinamento->Feedback)->vaga_id ? (int) $treinamento->Feedback->vaga_id : null;

        $mapearItemLista = function ($item) use ($treinamento, $treinamentoVencimentos, $padraoId, $vagaId) {
            $this->aplicarMarcacaoOutroPadrao($item, $padraoId);
            $item->nao_vinculado_ao_cargo = false;
            if ($vagaId) {
                $item->nao_vinculado_ao_cargo = !$item->vinculo_todos_cargos
                    && !$item->Vagas->contains(function ($vaga) use ($vagaId) {
                        return (int) $vaga->id === (int) $vagaId;
                    });
            }

            if ($treinamento->Treinamento) {
                $pivo = $treinamento->Treinamento->Vencimentos()->whereId($item->id);
                $item->data_treinamento = $pivo->count() > 0 ? $pivo->first()->pivot->data_treinamento : null;
                $item->data_vencimento = $pivo->count() > 0 ? $pivo->first()->pivot->data_vencimento : null;
                $item->numero_fat = $pivo->count() > 0 ? $pivo->first()->pivot->numero_fat : null;
                $item->fez_treinamento = $pivo->count() > 0 ? true : false;
                $item->arquivo = [];
                $item->arquivoDel = [];

                if ($item->data_vencimento) {
                    $item->dias_vencer = DataHora::diferencaDias(
                        (new DataHora())->dataInsert() . ' 00:00:00',
                        (new DataHora($item->data_vencimento))->dataInsert() . ' 23:59:59'
                    );
                } else {
                    $item->dias_vencer = PHP_INT_MAX;
                }

                if ($treinamentoVencimentos) {
                    $pivotData = $treinamentoVencimentos->where('vencimento_id', $item->id)->first();
                    $item->arquivo = $this->carregarAnexosFat(
                        (int) $treinamento->Treinamento->id,
                        (int) $item->id,
                        optional($pivotData)->arquivo_id ?? optional(optional($pivotData)->arquivo)->id
                    );
                }
            } else {
                $item->data_treinamento = null;
                $item->data_vencimento = null;
                $item->fez_treinamento = false;
                $item->numero_fat = null;
                $item->arquivo = [];
                $item->arquivoDel = [];
                $item->dias_vencer = PHP_INT_MAX;
            }

            return $item;
        };

        $vencimentosQuery = Vencimento::whereAtivo(true)->orderBy('ordem')->with(['Vagas:id', 'PadroesTreinamento:id,nome']);
        $this->aplicarFiltroEscopoPadrao($vencimentosQuery, $padraoId);
        if ($vagaId) {
            $vencimentosQuery->where(function ($q) use ($vagaId) {
                $q->where('vinculo_todos_cargos', true)
                    ->orWhereHas('Vagas', function ($qVaga) use ($vagaId) {
                        $qVaga->where('vagas.id', $vagaId);
                    });
            });
        }

        $listaVencimentos = $vencimentosQuery->get()->transform($mapearItemLista)->values();

        $listaNaoVinculadosCargo = collect();
        if ($treinamento->Treinamento) {
            // Todos os realizados (qualquer padrão): não some ao trocar o padrão — mesma ideia do legado
            $realizados = $treinamento->Treinamento->Vencimentos()
                ->with(['Vagas:id', 'PadroesTreinamento:id,nome'])
                ->get();

            $idsListaAtual = $listaVencimentos->pluck('id')->map(fn ($id) => (int) $id)->all();
            $idsExtras = $realizados
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->reject(fn ($id) => in_array($id, $idsListaAtual, true))
                ->values()
                ->all();

            if ($vagaId) {
                $realizadosForaVinculo = $realizados->filter(function ($v) use ($vagaId) {
                    if ($v->vinculo_todos_cargos) {
                        return false;
                    }

                    return !$v->Vagas->contains(function ($vaga) use ($vagaId) {
                        return (int) $vaga->id === (int) $vagaId;
                    });
                })->values();

                $listaNaoVinculadosCargo = $realizadosForaVinculo->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'label' => $v->label,
                        'label_reduzida' => $v->label_reduzida,
                        'data_treinamento' => optional($v->pivot)->data_treinamento,
                        'data_vencimento' => optional($v->pivot)->data_vencimento,
                    ];
                });
            }

            if (!empty($idsExtras)) {
                // Mesmo shape Eloquent da lista principal (evita stdClass sumir no JSON do edit)
                $extrasMapeados = Vencimento::query()
                    ->whereIn('id', $idsExtras)
                    ->with(['Vagas:id', 'PadroesTreinamento:id,nome'])
                    ->orderBy('ordem')
                    ->get()
                    ->transform($mapearItemLista);

                $listaVencimentos = $listaVencimentos
                    ->concat($extrasMapeados)
                    ->values();
            }
        }

        $listaVencimentos = $listaVencimentos
            ->sortBy(function ($v) {
                $deOutro = !empty($v->de_outro_padrao) ? 1 : 0;
                $dias = $v->dias_vencer ?? PHP_INT_MAX;

                return $deOutro . '-' . str_pad((string) $dias, 10, '0', STR_PAD_LEFT);
            })
            ->values();

        return [
            'listaVencimentos' => $listaVencimentos,
            'listaVencimentosNaoVinculadosCargo' => $listaNaoVinculadosCargo->values(),
            'alertaSemCargoVinculo' => $vagaId === null,
        ];
    }

    private function resolverPadraoTreinamentoId(array $dados): ?int
    {
        if (array_key_exists('padrao_treinamento_id', $dados)) {
            if (empty($dados['padrao_treinamento_id'])) {
                return null;
            }

            $padraoId = (int) $dados['padrao_treinamento_id'];
            $empresaId = (int) auth()->user()->empresa_id;
            $valido = PadraoTreinamento::query()
                ->where('id', $padraoId)
                ->where('empresa_id', $empresaId)
                ->exists();

            return $valido ? $padraoId : null;
        }

        $admissao = $this->buscarAdmissaoPorFeedbackEmpresa($dados['feedback_id'] ?? null);

        return $admissao && $admissao->padrao_treinamento_id
            ? (int) $admissao->padrao_treinamento_id
            : null;
    }

    /** Default da empresa (slug alumar ou primeiro ativo). */
    private function resolverPadraoDefaultEmpresa(int $empresaId): ?int
    {
        $alumar = PadraoTreinamento::query()
            ->where('empresa_id', $empresaId)
            ->where('slug', 'alumar')
            ->value('id');
        if ($alumar) {
            return (int) $alumar;
        }

        $primeiro = PadraoTreinamento::query()
            ->where('empresa_id', $empresaId)
            ->ativos()
            ->orderBy('id')
            ->value('id');

        return $primeiro ? (int) $primeiro : null;
    }

    /** Persiste o padrão na admissão. */
    private function atualizarPadraoAdmissao(array $dados, ?int $padraoId): void
    {
        if (!array_key_exists('padrao_treinamento_id', $dados) && $padraoId === null) {
            return;
        }

        $admissao = $this->buscarAdmissaoPorFeedbackEmpresa($dados['feedback_id'] ?? null);
        if (!$admissao) {
            return;
        }

        $admissao->padrao_treinamento_id = $padraoId;
        $admissao->save();
    }

    private function getVencimentosIdsPorPadrao(?int $padraoId)
    {
        if (!$padraoId) {
            return collect();
        }

        $query = Vencimento::whereAtivo(true);
        $this->aplicarFiltroEscopoPadrao($query, $padraoId);

        return $query->pluck('id');
    }

    private function buscarAdmissaoPorFeedbackEmpresa($feedbackId): ?Admissao
    {
        if (!$feedbackId) {
            return null;
        }

        $resultado = ResultadoIntegrado::whereFeedbackId($feedbackId)
            ->whereHas('Feedback', function ($q) {
                $q->where('empresa_id', auth()->user()->empresa_id);
            })
            ->with('Admissao')
            ->first();

        return $resultado ? $resultado->Admissao : null;
    }

    public function atualizar(Request $request)
    {
//        $resultado = $this->filtro($request)->paginate();

        $filter = FeedbackCurriculoFilter::make()->apply($request);
        $query = $filter->getQuery();
        $query->setEagerLoads([
            'Curriculo' => function ($q) {
                $q->select('id', 'nome', 'cpf');
            },
            'Admissao' => function ($q) {
                $q->select('id', 'feedback_id', 'status', 'tipo_admissao', 'padrao_treinamento_id', 'centro_custo_id');
            },
            'Admissao.PadraoTreinamento' => function ($q) {
                $q->select('id', 'nome', 'tem_carteira', 'carteira_treinamento_id', 'tem_etiqueta_bloqueio', 'etiqueta_bloqueio_id');
            },
            'Treinamento' => function ($q) {
                $q->select('id', 'feedback_id');
            },
            'Treinamento.Vencimentos' => function ($q) {
                $q->select('vencimentos.id', 'label', 'label_reduzida', 'exibir_na_carteira', 'vinculo_todos_cargos');
            },
            'Treinamento.Vencimentos.Vagas' => function ($q) {
                $q->select('vagas.id');
            },
            'VagaAberta' => function ($q) {
                $q->select('id', 'vaga_id');
            },
            'VagaAberta.Vaga' => function ($q) {
                $q->select('id', 'nome');
            }
        ]);

        $resultado = $filter->paginate(50);

        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
        $empresaId = (int) auth()->user()->empresa_id;

        $itens = collect($resultado->items());
        $vencimentos = $this->vencimentosAtivosCache();

        $vencimentos->transform(function ($i) {
            $i->fez_treinamento = false;
            return $i;
        });

        $padraoIdsPagina = $itens
            ->map(fn ($item) => optional($item->admissao)->padrao_treinamento_id)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $padraoDefaultEmpresa = $this->resolverPadraoDefaultEmpresa($empresaId);

        if ($padraoDefaultEmpresa) {
            $padraoIdsPagina = $padraoIdsPagina->push((int) $padraoDefaultEmpresa)->unique()->values();
        }

        $padroesInfo = PadraoTreinamento::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $padraoIdsPagina->all() ?: [0])
            ->get(['id', 'nome', 'tem_carteira', 'carteira_treinamento_id', 'tem_etiqueta_bloqueio', 'etiqueta_bloqueio_id'])
            ->keyBy('id');

        $itens->transform(function ($item) use ($cc, $padraoDefaultEmpresa, $padroesInfo) {
            // Evita appends desnecessários (e possíveis acessos a relações não carregadas)
            $item->setAppends([]);
            $item->makeHidden(['vaga_aberta_municipio', 'fc_token']);

            $padraoId = $item->admissao && $item->admissao->padrao_treinamento_id
                ? (int) $item->admissao->padrao_treinamento_id
                : ($padraoDefaultEmpresa ? (int) $padraoDefaultEmpresa : null);

            if ($item->Treinamento) {
                $item->nr_33 = $item->Treinamento->Vencimentos()->where(function ($q) {
                    $q->where('label', 'like', '%NR33%')->orWhere('label', 'like', '%NR-33%');
                })->count() > 0 ? $item->Treinamento->Vencimentos()->where(function ($q) {
                    $q->where('label', 'like', '%NR33%')->orWhere('label', 'like', '%NR-33%');
                })->first()->pivot : null;
                $item->nr_35 = $item->Treinamento->Vencimentos()->where(function ($q) {
                    $q->where('label', 'like', '%NR35%')->orWhere('label', 'like', '%NR-35%');
                })->count() > 0 ? $item->Treinamento->Vencimentos()->where(function ($q) {
                    $q->where('label', 'like', '%NR35%')->orWhere('label', 'like', '%NR-35%');
                })->first()->pivot : null;
                $item->ebtv = $item->Treinamento->Vencimentos()->where('label', 'like', '%EBTV%')->count() > 0 ? $item->Treinamento->Vencimentos()->where('label', 'like', '%EBTV%')->first()->pivot : null;

                // Eager PadroesTreinamento (id+nome) para escopo e mensagem "outro padrão"
                $item->Treinamento->loadMissing('Vencimentos.PadroesTreinamento:id,nome');

                $item->Treinamento->Vencimentos->transform(function ($i) use ($item) {
                    $i->fez_treinamento = $item->Treinamento->Vencimentos()->where('vencimento_id', $i->id)->count() > 0 ? true : false;
                    $i->pivot->status = $this->StatusTreinamento($i->pivot->data_vencimento);
                    $i->dias_vencer = DataHora::diferencaDias((new DataHora())->dataInsert() . ' 00:00:00', (new DataHora($i->pivot->data_vencimento))->dataInsert() . ' 23:59:59');
                    $vagaId = $item->vaga_id ? (int) $item->vaga_id : null;
                    $i->nao_vinculado_ao_cargo = false;
                    if ($vagaId) {
                        $i->nao_vinculado_ao_cargo = !$i->vinculo_todos_cargos && !$i->Vagas->contains(function ($vaga) use ($vagaId) {
                            return (int) $vaga->id === (int) $vagaId;
                        });
                    }
                    return $i;
                });

                // Todos os realizados: do padrão atual e de outro padrão (com nome do padrão de origem)
                $vencimentosFiltrados = $item->Treinamento->Vencimentos
                    ->map(function ($v) use ($padraoId) {
                        $this->aplicarMarcacaoOutroPadrao($v, $padraoId);
                        return $v;
                    })
                    // Do padrão primeiro; depois os de outro padrão; dentro, por dias a vencer
                    ->sortBy(function ($v) {
                        return ($v->de_outro_padrao ? 1 : 0) . '-' . str_pad((string) $v->dias_vencer, 10, '0', STR_PAD_LEFT);
                    })
                    ->values();

                // setRelation: atributo solto seria sobrescrito pela relação na serialização JSON
                $item->Treinamento->setRelation('Vencimentos', $vencimentosFiltrados);
            } else {
                $item->nr_33 = null;
                $item->nr_35 = null;
                $item->ebtv = null;
            }

            if ($item->admissao) {
                $cc_colaborador = collect($cc['centros_custos'])->collapse()->where('id', $item->admissao->centro_custo_id)->first();
                $item->admissao->emp_cnpj = null;
                $item->admissao->emp_nome_fantasia = null;
                $item->admissao->emp_centro_custo = null;
                $item->admissao->emp_tipo = null;
                $item->admissao->padrao_treinamento_nome = null;
                $item->admissao->padrao_tem_carteira = null;
                $item->admissao->padrao_tem_etiqueta = null;

                if ($cc_colaborador) {
                    $item->admissao->emp_cnpj = $cc_colaborador['cnpj_format'];
                    $item->admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'];
                    $item->admissao->emp_centro_custo = $cc_colaborador['label'];
                    $item->admissao->emp_tipo = $cc_colaborador['matriz'] ? 'Matriz' : 'Filial';
                }

                if (!$item->admissao->padrao_treinamento_id && $padraoId) {
                    $item->admissao->padrao_treinamento_id = $padraoId;
                }

                $padraoInfo = $item->admissao->PadraoTreinamento
                    ?: ($padraoId ? $padroesInfo->get($padraoId) : null);

                if ($padraoInfo) {
                    $item->admissao->padrao_treinamento_nome = $padraoInfo->nome;
                    $item->admissao->padrao_tem_carteira = (bool) $padraoInfo->tem_carteira
                        && !empty($padraoInfo->carteira_treinamento_id);
                    $item->admissao->padrao_tem_etiqueta = (bool) $padraoInfo->tem_etiqueta_bloqueio
                        && !empty($padraoInfo->etiqueta_bloqueio_id);
                }

                // Enxuga payload da admissão para somente o essencial
                $item->admissao->setVisible([
                    'status',
                    'tipo_admissao',
                    'centro_custo_id',
                    'padrao_treinamento_id',
                    'emp_cnpj',
                    'emp_nome_fantasia',
                    'emp_centro_custo',
                    'emp_tipo',
                    'padrao_treinamento_nome',
                    'padrao_tem_carteira',
                    'padrao_tem_etiqueta',
                ]);
                $item->admissao->unsetRelation('PadraoTreinamento');
            }

            return $item;
        });


        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'vencimentos' => $vencimentos,
                'cc' => $cc
            ]
        ]);
    }

    private function vencimentosAtivosCache()
    {
        $empresaId = auth()->user()->empresa_id ?? 'global';
        $cacheKey = \App\Models\Vencimento::cacheKey($empresaId);

        return Cache::remember($cacheKey, now()->addDays(30), function () {
            return Vencimento::whereAtivo(true)
                ->select('id', 'label', 'label_reduzida', 'exibir_na_carteira', 'ordem')
                ->orderBy('ordem')
                ->get();
        });
    }

    private function StatusTreinamento($dataVencimento)
    {
        $dataVencimento = new DataHora($dataVencimento . ' 23:59:59');
        $hoje = new DataHora();

        $diffHojeVencimento = DataHora::diferencaDias($hoje->dataHoraInsert(), $dataVencimento->dataHoraInsert());

        $status = [
            'label' => 'Em dia',
            'corBorder' => 'border-left-success',
            'bg' => 'bg-success',
            'text' => 'text-white',
            'badge' => 'badge-success',
            'status' => 'Em dia'
        ];

        if ($diffHojeVencimento < 0) {
            $status = [
                'label' => 'Vencido a ' . abs($diffHojeVencimento) . ' dia(s)',
                'corBorder' => 'border-left-danger',
                'bg' => 'bg-danger',
                'text' => 'text-white',
                'badge' => 'badge-danger',
                'status' => 'Vencido'
            ];
        }
        if ($diffHojeVencimento <= 30 && $diffHojeVencimento > 0) {
            $status = [
                'label' => 'Vence em ' . abs($diffHojeVencimento) . ' dia(s)',
                'corBorder' => 'border-left-warning',
                'bg' => 'bg-warning',
                'text' => 'text-white',
                'badge' => 'badge-warning text-dark',
                'status' => 'Proximo do vencimento'
            ];
        }

        return $status;
    }

    public function filtro(Request $request)
    {
        $this->authorize('treinamento_carteira-etiquetas');

        return FeedbackCurriculoFilter::make()
            ->apply($request)
            ->getQuery();
    }


    public function export(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $requestData = $request->all();

            // Criar uma chave única baseada no usuário e parâmetros da request
            $cacheKey = 'export_treinamentos_' . $userId . '_' . md5(json_encode($requestData));

            // Verificar se já existe um export em andamento
            if (Cache::get($cacheKey)) {
                $cacheData = Cache::get($cacheKey);

                // Verifica diferentes status
                $status = $cacheData['status'] ?? 'processing';
                $attempts = $cacheData['attempt'] ?? 1;
                $maxTries = $cacheData['max_tries'] ?? 3;

                $message = match ($status) {
                    'processing' => "Exportação em andamento (tentativa {$attempts}/{$maxTries}). Aguarde a conclusão.",
                    'retrying' => "Exportação tentando novamente (tentativa {$attempts}/{$maxTries}). Aguarde.",
                    'completed' => "Exportação já foi concluído. Verifique suas notificações.",
                    'failed' => "Última Exportação falhou após {$maxTries} tentativas. Você pode tentar novamente.",
                    default => "Já existe uma Exportação em andamento. Aguarde a conclusão."
                };

                return response()->json([
                    'msg' => $message,
                    'status' => $status,
                    'initiated_at' => $cacheData['initiated_at'] ?? null,
                    'attempts' => $attempts,
                    'max_tries' => $maxTries,
                    'last_error' => $cacheData['last_error'] ?? null
                ], 200); // 409 Conflict apenas se ainda processando
            }
            $nameArquivo = "treinamentos-exportado" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
            $expiresAt = now()->addMinutes(15);

            // Armazenar no cache com tempo de expiração definido
            Cache::put($cacheKey, [
                'filename' => $nameArquivo, // Corrigido: era fileName
                'initiated_at' => now(),
                'expires_at' => $expiresAt, // Para controle de TTL
                'user_id' => $userId,
                'status' => 'queued', // Status inicial
                'attempt' => 0,
                'max_tries' => 3,
                'progress' => 0
            ], $expiresAt);

            // Dispatch do job
            JobExportaTreinamentos::dispatch(
                $userId,
                $requestData,
                $nameArquivo,
                $cacheKey
            );

            return response()->json([
                'msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.',
                'export_id' => $cacheKey,
                'estimated_time' => '5-15 minutos'
            ]);

        } catch (\Exception $e) {
            \Log::error("Erro no controller de export treinamentos: " . $e->getMessage() . " " . $e->getFile() . " on line " . $e->getLine());

            // Limpar cache em caso de erro
            if (isset($cacheKey)) {
                Cache::forget($cacheKey);
            }

            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    public function carteiraPdf(Request $request)
    {
        //ToDo: Melhoria ajustar query para trazer apenas os dados necessários
        $tipo = $request->tipo;
        // Aceita selecionados[] (form) ou selecionados (array/único) em POST
        $rawSelecionados = $request->input('selecionados', $request->query('selecionados', []));
        if (!is_array($rawSelecionados)) {
            $rawSelecionados = [$rawSelecionados];
        }
        $selecionados = collect($rawSelecionados)
            ->flatten()
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($selecionados === []) {
            return response(
                'Nenhum colaborador selecionado para gerar a carteira. Marque o checkbox do colaborador e tente novamente.',
                422
            );
        }

        $clienteConfig = ClienteConfig::select(['id', 'supervisor_etiqueta_bloqueio', 'cliente_id'])
            ->where('cliente_id', auth()->user()->empresa_id)
            ->first();
        $telefone_supervisor = (bool) optional($clienteConfig)->supervisor_etiqueta_bloqueio;

        $embedImagensSegmentoBase64 = $request->boolean('as_pdf');

        $treinamentos = Treinamento::select([
            'id',
            'feedback_id',
            'tipo',
        ])->whereIn('feedback_id', $selecionados)
            ->with(['FeedbackCurriculo:id,curriculo_id,cliente_id,empresa_id',
                'FeedbackCurriculo.Empresa:id,razao_social,nome_fantasia,cnpj',
                'FeedbackCurriculo.Empresa.Logo:id,nome,file,disco',
                'FeedbackCurriculo.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
                'FeedbackCurriculo.Curriculo.FotoTres:id,nome,file,disco',
                'FeedbackCurriculo.Admissao:id,feedback_id,numero_cracha,cargo,usa_lentes_corretivas,area_etiqueta_id,padrao_treinamento_id',
                'FeedbackCurriculo.Admissao.PadraoTreinamento:id,nome,slug,tem_carteira,carteira_treinamento_id,tem_etiqueta_bloqueio,etiqueta_bloqueio_id',
                'FeedbackCurriculo.Admissao.PadraoTreinamento.CarteiraTreinamento:id,cabecalho_img,verso_img,slug',
                'FeedbackCurriculo.Admissao.PadraoTreinamento.EtiquetaBloqueio:id,ramal_emergencia,bloqueio_texto_nao_use,bloqueio_texto_demissao,bloqueio_texto_cuidado,bloqueio_texto_homens_trabalhando,slug',
                'Vencimentos.Vagas:id',
                'Vencimentos.PadroesTreinamento:id',
            ])
            ->get()
            ->filter(fn ($item) => $item->FeedbackCurriculo !== null)
            ->transform(function ($item) use ($telefone_supervisor, $embedImagensSegmentoBase64) {
                $telefone = "";
                if ($item->FeedbackCurriculo->Admissao) {
                    $telefone = $telefone_supervisor ? Admissao::getNumeroSupervisor($item->FeedbackCurriculo->empresa_id, $item->FeedbackCurriculo->Admissao->area_etiqueta_id) : \App\Models\Curriculo::getTelPrincipal($item->FeedbackCurriculo->curriculo_id, false);
                }
                $admissao = optional($item->FeedbackCurriculo)->Admissao;
                $padrao = optional($admissao)->PadraoTreinamento;
                $padraoId = $padrao ? (int) $padrao->id : (optional($admissao)->padrao_treinamento_id ? (int) $admissao->padrao_treinamento_id : null);
                if (!$padrao && $padraoId) {
                    $padrao = PadraoTreinamento::query()
                        ->with(['CarteiraTreinamento:id,cabecalho_img,verso_img,slug', 'EtiquetaBloqueio:id,ramal_emergencia,bloqueio_texto_nao_use,bloqueio_texto_demissao,bloqueio_texto_cuidado,bloqueio_texto_homens_trabalhando,slug'])
                        ->find($padraoId);
                }

                // Templates só do Padrão (chave segmento_config mantida pela view PDF)
                $segmentoConfig = $padrao ? $padrao->toConfigCarteira() : [
                    'cabecalho_img' => '',
                    'verso_img' => '',
                    'exibir_etiqueta_bloqueio' => false,
                    'ramal_emergencia' => '1199',
                    'bloqueio_texto_nao_use' => '',
                    'bloqueio_texto_demissao' => '',
                    'bloqueio_texto_cuidado' => '',
                    'bloqueio_texto_homens_trabalhando' => '',
                ];
                $segmentoSlug = $padrao
                    ? (optional($padrao->CarteiraTreinamento)->slug ?: $padrao->slug)
                    : 'padrao';

                $pathCabecalho = !empty($segmentoConfig['cabecalho_img']) ? $segmentoConfig['cabecalho_img'] : 'images/carteira/cabecalho_carteira_alumar.webp';
                $pathVerso = !empty($segmentoConfig['verso_img']) ? $segmentoConfig['verso_img'] : 'images/carteira/verso_carteira_alumar.webp';
                if ($embedImagensSegmentoBase64) {
                    $segmentoConfig['cabecalho_img_base64'] = CarteiraImagemCache::imagemPublicaParaBase64($pathCabecalho);
                    $segmentoConfig['verso_img_base64'] = CarteiraImagemCache::imagemPublicaParaBase64($pathVerso);
                } else {
                    $segmentoConfig['cabecalho_img_base64'] = null;
                    $segmentoConfig['verso_img_base64'] = null;
                }
                $item->setAttribute('segmento_config', $segmentoConfig);
                $item->setAttribute('segmento_slug', $segmentoSlug);
                $item->setAttribute('telefone', $telefone);
                $empresaId = auth()->user()->empresa_id;
                $assinaturaSesmt = $this->resolverAssinaturaCarteira($empresaId, $padraoId, CarteiraAssinatura::TIPO_SESMT);
                $assinaturaGestorRh = $this->resolverAssinaturaCarteira($empresaId, $padraoId, CarteiraAssinatura::TIPO_GERENTE_OU_RH);
                $item->setAttribute('assinatura_sesmt', $assinaturaSesmt);
                $item->setAttribute('assinatura_gestor_rh', $assinaturaGestorRh);
                $vencimentosFiltrados = $padraoId
                    ? $item->vencimentos->filter(function ($v) use ($padraoId) {
                        return $this->vencimentoNoEscopoPadrao($v, $padraoId);
                    })
                    : collect();
                $vagaId = optional($item->FeedbackCurriculo)->vaga_id ? (int) $item->FeedbackCurriculo->vaga_id : null;
                $alertaSemCargoVinculo = $vagaId === null;

                $vencimentosNaoVinculadosCargo = collect();
                if (!$alertaSemCargoVinculo) {
                    $vencimentosNaoVinculadosCargo = $vencimentosFiltrados
                        ->filter(function ($v) use ($vagaId) {
                            if ($v->vinculo_todos_cargos) {
                                return false;
                            }

                            return !$v->Vagas->contains(function ($vaga) use ($vagaId) {
                                return (int) $vaga->id === (int) $vagaId;
                            });
                        })
                        ->values()
                        ->map(function ($v) {
                            return [
                                'id' => $v->id,
                                'label' => $v->label,
                                'label_reduzida' => $v->label_reduzida,
                                'data_treinamento' => optional($v->pivot)->data_treinamento,
                                'data_vencimento' => optional($v->pivot)->data_vencimento,
                            ];
                        });

                    $vencimentosFiltrados = $vencimentosFiltrados
                        ->filter(function ($v) use ($vagaId) {
                            if ($v->vinculo_todos_cargos) {
                                return true;
                            }

                            return $v->Vagas->contains(function ($vaga) use ($vagaId) {
                                return (int) $vaga->id === (int) $vagaId;
                            });
                        })
                        ->values();
                }

                $item->setAttribute('alerta_sem_cargo_vinculo', $alertaSemCargoVinculo);
                $item->setAttribute('vencimentos_nao_vinculados_cargo', $vencimentosNaoVinculadosCargo->values()->all());
                $item->setRelation('vencimentos', $vencimentosFiltrados);
                // Foto 3x4 em base64 (cache do disco fotocurriculo) para PDF carteira/bloqueio evitar requisições HTTP
                $curriculo = $item->FeedbackCurriculo->Curriculo ?? null;
                if ($curriculo && $curriculo->fotoTres->isNotEmpty()) {
                    $firstFoto = $curriculo->fotoTres->first();
                    $dataUrl = CarteiraImagemCache::fotoCurriculo3x4ParaDataUrl($firstFoto->file);
                    $firstFoto->setAttribute('url_base64', $dataUrl);
                }
                return $item;
            })->values()->toArray();

        $empresa = Sistema::getEmpresa(auth()->user()->empresa_id);

        // Bloqueio: apenas itens cujo padrão permite exibir etiqueta de bloqueio
        if (in_array($tipo, ['bloqueio', 'treinamento_bloqueio'], true)) {
            $treinamentos = array_values(array_filter($treinamentos, function ($item) {
                $exibir = $item['segmento_config']['exibir_etiqueta_bloqueio'] ?? true;
                return $exibir !== false;
            }));
        }

        $viewData = array_merge(
            compact('treinamentos', 'tipo', 'empresa'),
            [
                'forPdf' => $request->boolean('as_pdf'),
                'bloqueio_altura_linha_par_mm' => CarteiraEtiquetaBloqueioLayout::alturaLinhaParMm(),
            ]
        );

        if ($request->boolean('as_pdf')) {
            $pdf = app('dompdf.wrapper');
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('isJavascriptEnabled', false);
            $pdf->setOption('isPhpEnabled', false);
            $chroot = realpath(public_path());
            $pdf->setOption('chroot', $chroot !== false ? $chroot : public_path());
            $pdf->loadView('pdf.treinamento.carteira.pdf', $viewData);

            return $pdf->stream('carteira-treinamento.pdf');
        }

        return view('pdf.treinamento.carteira.pdf', $viewData);
    }

    /**
     * Resolve assinatura da carteira: padrão específico → fallback da empresa (padrao null).
     *
     * @return array|null ['nome' => string, 'tipo' => string, 'url_thumb' => string|null]
     */
    private function resolverAssinaturaCarteira(int $empresaId, ?int $padraoId, string $tipo): ?array
    {
        $queryBase = function () use ($empresaId, $tipo) {
            return CarteiraAssinatura::where('empresa_id', $empresaId)
                ->where('ativo', true)
                ->where('tipo', $tipo)
                ->with('Anexos');
        };

        $a = null;
        if ($padraoId) {
            $a = $queryBase()->where('padrao_treinamento_id', $padraoId)->first();
        }
        if (!$a) {
            $a = $queryBase()->whereNull('padrao_treinamento_id')->first();
        }
        if (!$a) {
            return null;
        }
        return CarteiraImagemCache::assinaturaParaArray($a);
    }

    public function enviarCarteiraEmail(Request $request)
    {
        $dados = $request->input();
        try {
            Mail::send('email.treinamento.carteira', $dados, function ($m) use ($dados) {
                $m->from('naoresponda@mybp.com.br', 'MyBPIN');
                $m->subject("Carteira e etiqueta de treinamentos");
                $m->to(trim(mb_strtolower($dados['email'])));
            });

            $treinamento = Treinamento::find(\Crypt::decrypt($dados['token']));

            $treinamento->update([
                'enviado_email' => true,
                'email_envio' => $dados['email'],
                'enviou_id' => auth()->id(),
                'data_envio' => (new DataHora())->dataHoraInsert()
            ]);
            return response()->json(['enviado' => true], 200);
        } catch (\Exception $e) {
            $msg = "Error ao enviar e-maill de Revisão no Cloud: {$e->getMessage()}, {$e->getFile()}, {$e->getLine()}, {$e->getCode()}, {$e->getTrace()} ";
            \Log::debug($msg);
//            return response()->json(['msg' => $msg], 400);
            return response()->json(['enviado' => false], 400);
        }
    }

    public function carteiraIndividual($curriculo)
    {
        $treinamentos = Treinamento::whereId(\Crypt::decrypt($curriculo))->get();
        $treinamentos->first()->update([
            'email_aberto' => true,
            'data_email_aberto' => (new DataHora())->dataHoraInsert()
        ]);

        return view('pdf.treinamento.carteira.pdf', compact('treinamentos'));
//        return view('pdf.treinamento.carteira.individualEmail', compact('treinamento'));
    }

    public function treinamentoProximoVencimento(Request $request)
    {
        $hoje = new DataHora();
        $trintaDias = new DataHora($hoje->addDia(60));

        $treinamentos = Treinamento::whereHas('FeedbackCurriculo', function ($q) {
            $q->whereClienteId(1);
        })->whereHas('Vencimentos', function ($q) use ($trintaDias) {
            $q->where('data_vencimento', '<=', $trintaDias->dataInsert());
        })->with('Curriculo', 'Vencimentos.PadroesTreinamento:id', 'FeedbackCurriculo.Admissao.PadraoTreinamento:id');


        if ($treinamentos->count() >= 1) {
            $data = $request->input();
            $dados = ['dados' => $treinamentos->get()->map(function ($treinamento) {
                $admissao = optional($treinamento->FeedbackCurriculo)->Admissao;
                $padraoId = optional($admissao)->padrao_treinamento_id
                    ? (int) $admissao->padrao_treinamento_id
                    : null;

                $vencimentosFiltrados = $padraoId
                    ? $treinamento->Vencimentos->filter(function ($v) use ($padraoId) {
                        return $this->vencimentoNoEscopoPadrao($v, $padraoId);
                    })
                    : collect();

                $treinamento->setRelation('Vencimentos', $vencimentosFiltrados->values());
                return $treinamento;
            })];
            try {
                Mail::send('email.treinamento.vencendo', $dados, function ($m) use ($dados, $data) {
                    $m->from('naoresponda@mybp.com.br', 'MyBPIN - E-mail Automatico');
                    $m->subject("Treinamentos Vencidos ou próximo ao vencimento");
                    $m->to(trim(mb_strtolower($data['email'])));
                });
                \Log::info("E-mail enviado com sucesso de treinamento  vencidos ou vencendo total de {$treinamentos->count()}");
                return response()->json(['enviado' => true], 200);
            } catch (\Exception $e) {
                \Log::debug("Error ao enviar e-mail de Vencimento de Servicos: {$e->getMessage()}, {$e->getFile()}, {$e->getLine()}, {$e->getCode()}, {$e->getTrace()} ");
                return response()->json(['enviado' => false], 400);
            }
        }
    }

    // Anexos FAT -------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEFAT, Arquivo::DISCO_TREINAMENTO);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        $model = $this->autorizarArquivoFat($arquivo);
        $disco = $this->resolverDiscoArquivoFat($arquivo, $model);

        return $this->respostaAnexoPrivado(
            Arquivo::anexoShow($disco, $arquivo)
        );
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        $model = $this->autorizarArquivoFat($arquivo);
        $disco = $this->resolverDiscoArquivoFat($arquivo, $model);

        return Arquivo::anexoDelete($disco, $arquivo);
    }

    public function download(Request $request, $arquivo)
    {
        $model = $this->autorizarArquivoFat($arquivo);
        $disco = $this->resolverDiscoArquivoFat($arquivo, $model);

        return $this->respostaAnexoPrivado(
            Arquivo::anexoDownload($disco, $arquivo)
        );
    }

    /**
     * Autoriza anexo FAT: autenticado, mesma empresa (vínculo) ou uploader do temporário.
     */
    protected function autorizarArquivoFat(string $arquivo): Arquivo
    {
        if (!auth()->check()) {
            abort(401, 'Não autenticado');
        }

        $user = auth()->user();

        $model = Arquivo::query()
            ->whereIn('disco', [Arquivo::DISCO_TREINAMENTO, Arquivo::DISCO_CLOUD])
            ->where(function ($query) use ($arquivo) {
                $query->where('file', $arquivo)->orWhere('thumb', $arquivo);
            })
            ->first();

        if (!$model) {
            abort(404);
        }

        // Upload ainda não vinculado: só quem enviou
        if ($model->temporario) {
            if ((int) $model->quem_enviou === (int) $user->id) {
                return $model;
            }
            abort(403, 'Sem permissão para acessar este arquivo');
        }

        $autorizado = DB::table('treinamento_vencimento as tv')
            ->join('treinamentos as t', 't.id', '=', 'tv.treinamento_id')
            ->join('feedback_curriculos as f', 'f.id', '=', 't.feedback_id')
            ->where('tv.arquivo_id', $model->id)
            ->where('f.empresa_id', $user->empresa_id)
            ->exists();

        if (!$autorizado && Schema::hasTable('treinamento_vencimento_anexos')) {
            $autorizado = DB::table('treinamento_vencimento_anexos as tva')
                ->join('treinamentos as t', 't.id', '=', 'tva.treinamento_id')
                ->join('feedback_curriculos as f', 'f.id', '=', 't.feedback_id')
                ->where('tva.arquivo_id', $model->id)
                ->where('f.empresa_id', $user->empresa_id)
                ->exists();
        }

        if (!$autorizado) {
            abort(403, 'Sem permissão para acessar este arquivo');
        }

        return $model;
    }

    /**
     * Usa o disco do registro quando o arquivo existe lá; senão fallback cloud ↔ treinamento.
     */
    protected function resolverDiscoArquivoFat(string $arquivo, ?Arquivo $model = null): string
    {
        if ($model && in_array($model->disco, [Arquivo::DISCO_TREINAMENTO, Arquivo::DISCO_CLOUD], true)
            && Storage::disk($model->disco)->exists($arquivo)) {
            return $model->disco;
        }

        if (Storage::disk(Arquivo::DISCO_TREINAMENTO)->exists($arquivo)) {
            return Arquivo::DISCO_TREINAMENTO;
        }

        if (Storage::disk(Arquivo::DISCO_CLOUD)->exists($arquivo)) {
            return Arquivo::DISCO_CLOUD;
        }

        abort(404);
    }

    /**
     * Evita cache de CDN/browser em anexos autenticados/assinados.
     */
    protected function respostaAnexoPrivado(mixed $response): mixed
    {
        if (!method_exists($response, 'headers')) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('CDN-Cache-Control', 'no-store');
        $response->headers->set('Cloudflare-CDN-Cache-Control', 'no-store');

        return $response;
    }

}
