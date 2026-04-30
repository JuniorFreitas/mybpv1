<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaTreinamentos;
use App\Models\Admissao;
use App\Models\AuditoriaInterna;
use App\Models\Arquivo;
use App\Models\CarteiraAssinatura;
use App\Models\CentroCusto;
use App\Models\ClienteConfig;
use App\Models\Pivot\TreinamentoVencimento;
use App\Models\ResultadoIntegrado;
use App\Models\SegmentoTreinamento;
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

            $segmentoId = $this->resolverSegmentoTreinamentoId($dados);
            $this->atualizarSegmentoAdmissao($dados, $segmentoId);

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
                $idsSegmento = $this->getVencimentosIdsPorSegmento($segmentoId);
                $existingPivots = $treinamento->Vencimentos()->whereIn('vencimento_id', $idsSegmento)->get()->keyBy('vencimento_id');
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
                        if (!empty($pivotData->arquivo_id)) {
                            $merged['arquivo'] = [['id' => $pivotData->arquivo_id, 'temporario' => false, 'chave' => '', 'falhou' => false]];
                        }
                        return $merged;
                    }
                    return $item;
                });
            }
            $listaVencimentos = $listaVencimentos->filter(function ($item) {
                return $item['fez_treinamento'];
            });

            $treinamento_fat_obrigatorio = $clienteConfig ? $clienteConfig->getConfig('treinamento_fat_obrigatorio', false) : false;
            if ($treinamento_fat_obrigatorio && $listaVencimentos->isNotEmpty()) {
                $semFat = $listaVencimentos->filter(function ($item) {
                    return empty(trim((string) ($item['numero_fat'] ?? '')));
                });
                if ($semFat->isNotEmpty()) {
                    return response()->json(['msg' => 'Número FAT é obrigatório para este cliente em treinamentos realizados.'], 400);
                }
            }

            $vencimentosDesmarcadosIds = collect();
            if ($isUpdate && $treinamento && $pode_desmarcar && $existingPivotsForAudit) {
                $vencimentosDesmarcadosIds = $existingPivotsForAudit->keys()->diff($listaVencimentos->pluck('id'))->values();
            }

            if ($isUpdate) {
                $this->authorize('treinamento_carteira-etiquetas_update');

                unset($dados['enviado_email'], $dados['email_aberto'], $dados['data_email_aberto'], $dados['data_envio']);
                $treinamento->update($dados);

                $idsSegmento = $this->getVencimentosIdsPorSegmento($segmentoId);
                if ($idsSegmento->isNotEmpty()) {
                    $treinamento->Vencimentos()->detach($idsSegmento->all());
                }
            } else {
                $this->authorize('treinamento_carteira-etiquetas_insert');
                $treinamento = Treinamento::create($dados);
            }

            $this->processarVencimentos($treinamento, $listaVencimentos);
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

        foreach ($listaVencimentos as &$lista) { // Referência no foreach
            $dataHora = new DataHora($lista['data_treinamento']);
            $dataTreinamento = $dataHora->dataInsert();

            // Usar somente prazo_fixo para vencimento (não há mais fixo/parada)
            $dataVencimento = $lista['prazo_fixo']
                ? $dataHora->addDia($lista['prazo_fixo'])
                : $lista['data_vencimento'];

            $this->processarArquivosDeletar($lista);
            $arquivoId = $this->processarArquivoPrincipal($lista);

            // Acumula dados para insert em lote
            $dadosParaAnexar[$lista['id']] = [
                'data_treinamento' => $dataTreinamento,
                'data_vencimento' => (new DataHora($dataVencimento))->dataInsert(),
                'numero_fat' => $lista['numero_fat'],
                'arquivo_id' => $arquivoId
            ];
        }

        // Insert em lote - MUITO mais eficiente
        if (!empty($dadosParaAnexar)) {
            $treinamento->Vencimentos()->attach($dadosParaAnexar);
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
     * Usar referência para array
     * @param array &$lista - REFERÊNCIA
     * @return int|null
     */
    /**
     * Verifica se o item (vencimento) possui anexo FAT válido (novo upload ou já existente).
     * Usado quando treinamento_fat_obrigatorio está ativo.
     */
    private function itemTemAnexoFat(array $item): bool
    {
        if (empty($item['arquivo']) || !is_array($item['arquivo']) || !isset($item['arquivo'][0])) {
            return false;
        }
        $arq = $item['arquivo'][0];
        // Anexo existente (veio do pivot)
        if (!empty($arq['id']) && empty($arq['temporario'])) {
            return true;
        }
        // Novo upload: temporário, com chave e não falhou
        return !empty($arq['temporario'])
            && !empty($arq['chave'])
            && empty($arq['falhou']);
    }

    private function processarArquivoPrincipal(array &$lista): ?int
    {
        if (!isset($lista['arquivo'][0]) ||
            !$lista['arquivo'][0]['temporario'] ||
            strlen($lista['arquivo'][0]['chave']) === 0 ||
            $lista['arquivo'][0]['falhou']) {

            return $lista['arquivo'][0]['id'] ?? null;
        }

        $arquivo = Arquivo::find($lista['arquivo'][0]['id']);
        if ($arquivo) {
            $arquivo->update([
                'chave' => '',
                'nome' => $lista['arquivo'][0]['nome'],
                'temporario' => false,
            ]);
        }

        return $lista['arquivo'][0]['id'];
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
            if (!empty($pivotData->arquivo_id ?? null)) {
                $item['arquivo'] = [['id' => $pivotData->arquivo_id, 'temporario' => false, 'chave' => '', 'falhou' => false]];
            }
        }

        $treinamento_fat_obrigatorio = $clienteConfig ? $clienteConfig->getConfig('treinamento_fat_obrigatorio', false) : false;
        if ($treinamento_fat_obrigatorio && !empty($item['fez_treinamento']) && !$this->itemTemAnexoFat($item)) {
            return response()->json(['msg' => 'Anexo da FAT é obrigatório para este cliente em treinamentos realizados.'], 400);
        }

        DB::beginTransaction();
        try {
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
            'Curriculo:id,nome,nascimento,id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
            'Admissao',
            'Feedback.Exame',
            'Feedback.Curriculo:id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
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

            // Adicionar os arquivos aos vencimentos já carregados
            foreach ($treinamento->Treinamento->Vencimentos as $vencimento) {
                $pivotData = $treinamentoVencimentos->where('vencimento_id', $vencimento->id)->first();
                $vencimento->arquivo = [];
                $vencimento->arquivoDel = [];
                if ($pivotData) {
                    $vencimento->arquivo = [$pivotData->arquivo];
                }
            }
        }

        $segmentoId = $request->filled('segmento_treinamento_id')
            ? (int) $request->input('segmento_treinamento_id')
            : (optional($treinamento->Admissao)->segmento_treinamento_id ?? SegmentoTreinamento::getIdAlumar());

        $treinamento->segmento_treinamento_id = $segmentoId;
        $dadosVinculoCargo = $this->montarListaVencimentosPorVinculoCargo($treinamento, $treinamentoVencimentos, $segmentoId);
        $treinamento->listaVencimentos = $dadosVinculoCargo['listaVencimentos'];
        $treinamento->listaVencimentosNaoVinculadosCargo = $dadosVinculoCargo['listaVencimentosNaoVinculadosCargo'];
        $treinamento->alertaSemCargoVinculo = $dadosVinculoCargo['alertaSemCargoVinculo'];

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

//        <div class="row">
//                        <div class="col-12">
//                            <p>
//    Nome: <strong>@{{ dados.nome }}</strong> - @{{ dados.idade }} anos <br>
//                                Cargo: <strong>@{{ dados.cargo }}</strong> <br>
//    Endereço: <strong>@{{ dados.endereco_completo }}</strong><br>
//    Contato: <strong><i class="fab fa-whatsapp text-success"
//                                                    v-show="dados.tel_principal.tipo === 'whatsapp'"></i> @{{
//        dados.tel_principal.numero }}</strong> - E-mail: <strong>@{{dados.email}}</strong>
//                                <br>
//                            </p>
//                        </div>
//                    </div>

        $treinamento->dadosFuncionario = [
            'nome' => $treinamento->Feedback->Curriculo->nome,
            'cargo' => $treinamento->Admissao ? $treinamento->Admissao->cargo : null,
            'endereco_completo' => $treinamento->Feedback->Curriculo->endereco_completo,
            'tel_principal' => $treinamento->Feedback->telefonePrincipal,
            'email' => $treinamento->Feedback->Curriculo->email,
            'idade' => $treinamento->Feedback->Curriculo->idade
        ];

        $clienteConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
        $treinamento->privilegio_gestao_rh = auth()->user()->can('privilegio_gestao_rh');
        $treinamento->treinamento_permitir_desmarcar_realizado = $clienteConfig ? (bool) ($clienteConfig->treinamento_permitir_desmarcar_realizado ?? false) : false;
        $treinamento->treinamento_retirar_treinamento_realizado = auth()->user()->can('treinamento_carteira-etiquetas_retirar_treinamento_realizado');
        $treinamento->treinamento_fat_obrigatorio = $clienteConfig ? $clienteConfig->getConfig('treinamento_fat_obrigatorio', false) : false;

        return response()->json($treinamento);
    }

    public function vencimentosPorSegmento(Request $request): JsonResponse
    {
        $request->validate([
            'feedback_id' => 'required|integer',
            'segmento_treinamento_id' => 'nullable|integer',
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

        $segmentoId = $request->filled('segmento_treinamento_id')
            ? (int) $request->input('segmento_treinamento_id')
            : (optional($treinamento->Admissao)->segmento_treinamento_id ?? SegmentoTreinamento::getIdAlumar());

        $dadosVinculoCargo = $this->montarListaVencimentosPorVinculoCargo($treinamento, $treinamentoVencimentos, $segmentoId);

        $clienteConfig = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();

        return response()->json([
            'segmento_treinamento_id' => $segmentoId,
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

    private function montarListaVencimentosPorVinculoCargo(ResultadoIntegrado $treinamento, $treinamentoVencimentos, ?int $segmentoId): array
    {
        $vagaId = optional($treinamento->Feedback)->vaga_id ? (int) $treinamento->Feedback->vaga_id : null;

        $vencimentosQuery = Vencimento::whereAtivo(true)->orderBy('ordem')->with('Vagas:id');
        if ($segmentoId) {
            $vencimentosQuery->where(function ($q) use ($segmentoId) {
                $q->where('segmento_treinamento_id', $segmentoId)->orWhereNull('segmento_treinamento_id');
            });
        }
        if ($vagaId) {
            $vencimentosQuery->where(function ($q) use ($vagaId) {
                $q->where('vinculo_todos_cargos', true)
                    ->orWhereHas('Vagas', function ($qVaga) use ($vagaId) {
                        $qVaga->where('vagas.id', $vagaId);
                    });
            });
        }

        $listaVencimentos = $vencimentosQuery->get()
            ->transform(function ($item) use ($treinamento, $treinamentoVencimentos) {
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
                    $item->dias_vencer = PHP_INT_MAX;
                }

                return $item;
            })
            ->sortBy('dias_vencer')
            ->values();

        $listaNaoVinculadosCargo = collect();
        $realizadosForaVinculo = collect();
        if ($treinamento->Treinamento) {
            $realizados = $treinamento->Treinamento->Vencimentos()->with('Vagas:id')->get();
            if ($segmentoId) {
                $realizados = $realizados->filter(function ($v) use ($segmentoId) {
                    return $v->segmento_treinamento_id === null || (int) $v->segmento_treinamento_id === (int) $segmentoId;
                })->values();
            }

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

                $idsListaAtual = $listaVencimentos->pluck('id')->map(fn ($id) => (int) $id)->all();

                $extrasNaoVinculados = $realizadosForaVinculo
                    ->filter(function ($v) use ($idsListaAtual) {
                        return !in_array((int) $v->id, $idsListaAtual, true);
                    })
                    ->map(function ($v) use ($treinamentoVencimentos) {
                        $arquivo = [];
                        if ($treinamentoVencimentos) {
                            $pivotData = $treinamentoVencimentos->where('vencimento_id', $v->id)->first();
                            if ($pivotData && $pivotData->arquivo) {
                                $arquivo = [$pivotData->arquivo];
                            }
                        }

                        return (object) [
                            'id' => $v->id,
                            'label' => $v->label,
                            'label_reduzida' => $v->label_reduzida,
                            'descricao' => $v->descricao,
                            'prazo_fixo' => $v->prazo_fixo,
                            'prazo_parada' => $v->prazo_parada,
                            'exibir_na_carteira' => $v->exibir_na_carteira,
                            'vinculo_todos_cargos' => $v->vinculo_todos_cargos,
                            'nao_vinculado_ao_cargo' => true,
                            'data_treinamento' => optional($v->pivot)->data_treinamento,
                            'data_vencimento' => optional($v->pivot)->data_vencimento,
                            'numero_fat' => optional($v->pivot)->numero_fat,
                            'fez_treinamento' => true,
                            'arquivo' => $arquivo,
                            'arquivoDel' => [],
                            'dias_vencer' => optional($v->pivot)->data_vencimento
                                ? DataHora::diferencaDias(
                                    (new DataHora())->dataInsert() . ' 00:00:00',
                                    (new DataHora(optional($v->pivot)->data_vencimento))->dataInsert() . ' 23:59:59'
                                )
                                : PHP_INT_MAX,
                        ];
                    });

                if ($extrasNaoVinculados->isNotEmpty()) {
                    $listaVencimentos = $listaVencimentos
                        ->concat($extrasNaoVinculados)
                        ->sortBy('dias_vencer')
                        ->values();
                }
            }
        }

        return [
            'listaVencimentos' => $listaVencimentos,
            'listaVencimentosNaoVinculadosCargo' => $listaNaoVinculadosCargo->values(),
            'alertaSemCargoVinculo' => $vagaId === null,
        ];
    }

    private function resolverSegmentoTreinamentoId(array $dados): ?int
    {
        if (array_key_exists('segmento_treinamento_id', $dados)) {
            return $dados['segmento_treinamento_id'] ? (int) $dados['segmento_treinamento_id'] : SegmentoTreinamento::getIdAlumar();
        }

        $admissao = $this->buscarAdmissaoPorFeedbackEmpresa($dados['feedback_id'] ?? null);

        return $admissao && $admissao->segmento_treinamento_id
            ? (int) $admissao->segmento_treinamento_id
            : SegmentoTreinamento::getIdAlumar();
    }

    private function atualizarSegmentoAdmissao(array $dados, ?int $segmentoId): void
    {
        if (!array_key_exists('segmento_treinamento_id', $dados)) {
            return;
        }

        $admissao = $this->buscarAdmissaoPorFeedbackEmpresa($dados['feedback_id'] ?? null);

        if ($admissao) {
            $admissao->segmento_treinamento_id = $segmentoId;
            $admissao->save();
        }
    }

    private function getVencimentosIdsPorSegmento(?int $segmentoId)
    {
        $query = Vencimento::whereAtivo(true);
        if ($segmentoId) {
            $query->where(function ($q) use ($segmentoId) {
                $q->where('segmento_treinamento_id', $segmentoId)->orWhereNull('segmento_treinamento_id');
            });
        }

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
                $q->select('id', 'feedback_id', 'status', 'tipo_admissao', 'segmento_treinamento_id', 'centro_custo_id');
            },
            'Admissao.SegmentoTreinamento' => function ($q) {
                $q->select('id', 'nome');
            },
            'Treinamento' => function ($q) {
                $q->select('id', 'feedback_id');
            },
            'Treinamento.Vencimentos' => function ($q) {
                $q->select('vencimentos.id', 'label', 'label_reduzida', 'segmento_treinamento_id', 'exibir_na_carteira', 'vinculo_todos_cargos');
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

        $itens = collect($resultado->items());
        $vencimentos = $this->vencimentosAtivosCache();

        $vencimentos->transform(function ($i) {
            $i->fez_treinamento = false;
            return $i;
        });

        $itens->transform(function ($item) use ($cc) {
            // Evita appends desnecessários (e possíveis acessos a relações não carregadas)
            $item->setAppends([]);
            $item->makeHidden(['vaga_aberta_municipio', 'fc_token']);

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

                $segmentoId = $item->admissao && $item->admissao->segmento_treinamento_id
                    ? (int) $item->admissao->segmento_treinamento_id
                    : SegmentoTreinamento::getIdAlumar();
                $vencimentosFiltrados = $item->Treinamento->Vencimentos->filter(function ($v) use ($segmentoId) {
                    return $v->segmento_treinamento_id === null || (int) $v->segmento_treinamento_id === (int) $segmentoId;
                });
                if ($vencimentosFiltrados->isEmpty()) {
                    $alumarId = SegmentoTreinamento::getIdAlumar();
                    $vencimentosFiltrados = $item->Treinamento->Vencimentos->filter(function ($v) use ($alumarId) {
                        return $v->segmento_treinamento_id === null || (int) $v->segmento_treinamento_id === (int) $alumarId;
                    });
                }

                $item->treinamento->vencimentos = $vencimentosFiltrados->sortBy('dias_vencer')->values()->all();


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
                $item->admissao->segmento_treinamento_nome = null;

                if ($cc_colaborador) {
                    $item->admissao->emp_cnpj = $cc_colaborador['cnpj_format'];
                    $item->admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'];
                    $item->admissao->emp_centro_custo = $cc_colaborador['label'];
                    $item->admissao->emp_tipo = $cc_colaborador['matriz'] ? 'Matriz' : 'Filial';
                }

                if ($item->admissao->SegmentoTreinamento) {
                    $item->admissao->segmento_treinamento_nome = $item->admissao->SegmentoTreinamento->nome;
                }

                // Enxuga payload da admissão para somente o essencial
                $item->admissao->setVisible([
                    'status',
                    'tipo_admissao',
                    'centro_custo_id',
                    'segmento_treinamento_id',
                    'emp_cnpj',
                    'emp_nome_fantasia',
                    'emp_centro_custo',
                    'emp_tipo',
                    'segmento_treinamento_nome'
                ]);
                $item->admissao->unsetRelation('SegmentoTreinamento');
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
                ->select('id', 'label', 'label_reduzida', 'segmento_treinamento_id', 'exibir_na_carteira', 'ordem')
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
        $telefone_supervisor = ClienteConfig::select(['id', 'supervisor_etiqueta_bloqueio', 'cliente_id'])
            ->where('cliente_id', auth()->user()->empresa_id)
            ->first()
            ->supervisor_etiqueta_bloqueio;

        $embedImagensSegmentoBase64 = $request->boolean('as_pdf');

        $treinamentos = Treinamento::select([
            'id',
            'feedback_id',
            'tipo',
        ])->whereIn('feedback_id', $request->selecionados)
            ->with(['FeedbackCurriculo:id,curriculo_id,cliente_id,empresa_id',
                'FeedbackCurriculo.Empresa:id,razao_social,nome_fantasia,cnpj',
                'FeedbackCurriculo.Empresa.Logo:id,nome,file,disco',
                'FeedbackCurriculo.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
                'FeedbackCurriculo.Curriculo.FotoTres:id,nome,file,disco',
                'FeedbackCurriculo.Admissao:id,feedback_id,numero_cracha,cargo,usa_lentes_corretivas,area_etiqueta_id,segmento_treinamento_id',
                'FeedbackCurriculo.Admissao.SegmentoTreinamento:id,nome,slug,config_carteira',
                'Vencimentos.Vagas:id'
            ])
            ->get()
            ->transform(function ($item) use ($telefone_supervisor, $embedImagensSegmentoBase64) {
                $telefone = "";
                if ($item->FeedbackCurriculo->Admissao) {
                    $telefone = $telefone_supervisor ? Admissao::getNumeroSupervisor($item->FeedbackCurriculo->empresa_id, $item->FeedbackCurriculo->Admissao->area_etiqueta_id) : \App\Models\Curriculo::getTelPrincipal($item->FeedbackCurriculo->curriculo_id, false);
                }
                // Segmento da Admissão: define config da carteira e da etiqueta de bloqueio (cabecalho_img, verso_img, ramal_emergencia, exibir_etiqueta_bloqueio, etc.)
                $segmento = optional($item->FeedbackCurriculo->Admissao)->SegmentoTreinamento;
                if (!$segmento) {
                    $segmento = SegmentoTreinamento::where('slug', SegmentoTreinamento::SLUG_ALUMAR)->first(['id', 'nome', 'slug', 'config_carteira']);
                }
                $segmentoId = $segmento ? $segmento->id : null;
                $segmentoConfig = ($segmento && is_array($segmento->config_carteira ?? null)) ? $segmento->config_carteira : [];
                $segmentoSlug = $segmento ? $segmento->slug : 'alumar';
                // Imagens do segmento em base64 com cache (path + filemtime na chave = invalida ao atualizar arquivo)
                $pathCabecalho = !empty($segmentoConfig['cabecalho_img']) ? $segmentoConfig['cabecalho_img'] : 'images/carteira/cabecalho_carteira_alumar.webp';
                $pathVerso = !empty($segmentoConfig['verso_img']) ? $segmentoConfig['verso_img'] : 'images/carteira/verso_carteira_alumar.webp';
                if ($embedImagensSegmentoBase64) {
                    $segmentoConfig['cabecalho_img_base64'] = CarteiraImagemCache::imagemPublicaParaBase64($pathCabecalho);
                    $segmentoConfig['verso_img_base64'] = CarteiraImagemCache::imagemPublicaParaBase64($pathVerso);
                } else {
                    $segmentoConfig['cabecalho_img_base64'] = null;
                    $segmentoConfig['verso_img_base64'] = null;
                }
                // setAttribute para que toArray() inclua na view (carteira e bloqueio usam essas configs)
                $item->setAttribute('segmento_config', $segmentoConfig);
                $item->setAttribute('segmento_slug', $segmentoSlug);
                $item->setAttribute('telefone', $telefone);
                // Assinaturas por segmento: busca na carteira_assinaturas por empresa + tipo + segmento (ou padrão com segmento null)
                $empresaId = auth()->user()->empresa_id;
                $assinaturaSesmt = $this->resolverAssinaturaCarteira($empresaId, $segmentoId, CarteiraAssinatura::TIPO_SESMT);
                $assinaturaGestorRh = $this->resolverAssinaturaCarteira($empresaId, $segmentoId, CarteiraAssinatura::TIPO_GERENTE_OU_RH);
                $item->setAttribute('assinatura_sesmt', $assinaturaSesmt);
                $item->setAttribute('assinatura_gestor_rh', $assinaturaGestorRh);
                // Carteira: vencimentos do segmento da Admissão (ou sem segmento). Se ficar vazio, fallback para ALUMAR/null para sempre gerar carteira.
                $vencimentosFiltrados = $item->vencimentos->filter(function ($v) use ($segmentoId) {
                    return $v->segmento_treinamento_id === $segmentoId || $v->segmento_treinamento_id === null;
                });
                if ($vencimentosFiltrados->isEmpty()) {
                    $alumarId = SegmentoTreinamento::getIdAlumar();
                    $vencimentosFiltrados = $item->vencimentos->filter(function ($v) use ($alumarId) {
                        return $v->segmento_treinamento_id === $alumarId || $v->segmento_treinamento_id === null;
                    });
                }
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
            })->toArray();

        $empresa = Sistema::getEmpresa(auth()->user()->empresa_id);

        // Bloqueio: apenas itens cujo segmento permite exibir etiqueta de bloqueio
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
     * Resolve assinatura da carteira: primeiro tenta assinatura do segmento (carteira_assinaturas.segmento_treinamento_id),
     * senão usa a padrão da empresa (segmento_treinamento_id null).
     *
     * @param int $empresaId
     * @param int|null $segmentoId ID do segmento do treinamento
     * @param string $tipo CarteiraAssinatura::TIPO_SESMT ou TIPO_GERENTE_OU_RH
     * @return array|null ['nome' => string, 'tipo' => string, 'url_thumb' => string|null]
     */
    private function resolverAssinaturaCarteira(int $empresaId, ?int $segmentoId, string $tipo): ?array
    {
        // Preferir assinatura específica do segmento; senão, a padrão (segmento null)
        $a = null;
        if ($segmentoId) {
            $a = CarteiraAssinatura::where('empresa_id', $empresaId)->where('ativo', true)->where('tipo', $tipo)
                ->where('segmento_treinamento_id', $segmentoId)->with('Anexos')->first();
        }
        if (!$a) {
            $a = CarteiraAssinatura::where('empresa_id', $empresaId)->where('ativo', true)->where('tipo', $tipo)
                ->whereNull('segmento_treinamento_id')->with('Anexos')->first();
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
                $m->from('naoresponda@mybp.com.br', 'MyBP');
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
        })->with('Curriculo', 'Vencimentos', 'FeedbackCurriculo.Admissao.SegmentoTreinamento');


        if ($treinamentos->count() >= 1) {
            $data = $request->input();
            $dados = ['dados' => $treinamentos->get()->map(function ($treinamento) {
                $segmentoId = optional(optional($treinamento->FeedbackCurriculo)->Admissao)->segmento_treinamento_id
                    ?? SegmentoTreinamento::getIdAlumar();

                $vencimentosFiltrados = $treinamento->Vencimentos->filter(function ($v) use ($segmentoId) {
                    return $v->segmento_treinamento_id === null || (int) $v->segmento_treinamento_id === (int) $segmentoId;
                });

                if ($vencimentosFiltrados->isEmpty()) {
                    $alumarId = SegmentoTreinamento::getIdAlumar();
                    $vencimentosFiltrados = $treinamento->Vencimentos->filter(function ($v) use ($alumarId) {
                        return $v->segmento_treinamento_id === null || (int) $v->segmento_treinamento_id === (int) $alumarId;
                    });
                }

                $treinamento->setRelation('Vencimentos', $vencimentosFiltrados->values());
                return $treinamento;
            })];
            try {
                Mail::send('email.treinamento.vencendo', $dados, function ($m) use ($dados, $data) {
                    $m->from('naoresponda@mybp.com.br', 'MyBP - E-mail Automatico');
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

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASDOCIMGPDF, Arquivo::DISCO_CLOUD);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CLOUD, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CLOUD, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CLOUD, $arquivo);
    }

}
