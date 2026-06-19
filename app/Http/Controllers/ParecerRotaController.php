<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaParecerRota;
use App\Models\FeedbackCurriculo;
use App\Models\ParecerRota;
use App\Models\TelefoneCurriculo;
use App\Services\Entrevistas\ParecerRotaFilter;
use App\Services\Entrevistas\ParecerRotaWhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;

class ParecerRotaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.entrevistas.parecer_rota.index');
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
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'feedback_id' => 'required|exists:feedback_curriculos,id',
            'tem_rota' => 'required|boolean',
            'pega_onibus' => 'nullable|boolean',
            'vale_transporte' => 'required|boolean',
            'rota_atende' => 'required|boolean',
            'rota_tipo' => 'required|string|max:100',
            'quem_entrevistou' => 'required|string|min:3|max:255'
        ], [
            'feedback_id.required' => 'ID do feedback é obrigatório',
            'feedback_id.exists' => 'Feedback não encontrado',
            'tem_rota.required' => 'Informe se tem rota',
            'tem_rota.boolean' => 'Campo tem rota deve ser verdadeiro ou falso',
            'vale_transporte.required' => 'Informe sobre vale transporte',
            'vale_transporte.boolean' => 'Campo vale transporte deve ser verdadeiro ou falso',
            'rota_atende.required' => 'Informe se a rota atende',
            'rota_atende.boolean' => 'Campo rota atende deve ser verdadeiro ou falso',
            'rota_tipo.required' => 'Tipo de rota é obrigatório',
            'rota_tipo.max' => 'Tipo de rota deve ter no máximo 100 caracteres',
            'quem_entrevistou.required' => 'Nome do entrevistador é obrigatório',
            'quem_entrevistou.min' => 'Nome do entrevistador deve ter pelo menos 3 caracteres',
            'quem_entrevistou.max' => 'Nome do entrevistador deve ter no máximo 255 caracteres'
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao salvar a entrevista',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $parecerRota = ParecerRota::create($dados);

            DB::commit();

            return response()->json([
                'msg' => 'Parecer de rota salvo com sucesso',
                'data' => $parecerRota
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Erro ao salvar parecer rota', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'data' => $dados
            ]);

            return response()->json([
                'msg' => 'Erro ao salvar o parecer de rota. Tente novamente.'
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ParecerRota $parecerRota
     * @return \Illuminate\Http\Response
     */
    public function show(ParecerRota $parecerRota)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ParecerRota $parecerRota
     * @return JsonResponse
     */
    public function edit(FeedbackCurriculo $parecerRota)
    {
        try {
            $feedback = $parecerRota->load([
                'parecerRota.QuemEnviouWhatsapp:id,nome',
                'parecerRh:feedback_id,tipo_entrevista',
                'Curriculo',
                'Curriculo.Formacao',
                'Curriculo.Telefones',
                'telCadPrincipal',
                'TelPrincipal',
                'vagaSelecionada'
            ]);

            $telefoneWhatsapp = $feedback->Curriculo?->Telefones
                ?->where('tipo', TelefoneCurriculo::TIPO_WHATS)
                ->sortByDesc(fn (TelefoneCurriculo $telefone) => $telefone->principal ? 1 : 0)
                ->first();

            $data = $feedback->toArray();
            $data['tel_principal'] = $feedback->telCadPrincipal ?? $feedback->TelPrincipal;
            $data['telefone_whatsapp_candidato'] = $telefoneWhatsapp;

            return response()->json($data, 200);

        } catch (\Exception $e) {
            \Log::error('Erro ao carregar dados do parecer rota', [
                'error' => $e->getMessage(),
                'feedback_id' => $parecerRota->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'msg' => 'Erro ao carregar dados'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ParecerRota $parecerRota
     * @return JsonResponse
     */
    public function update(Request $request, ParecerRota $parecerRota)
    {
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'tem_rota' => 'sometimes|boolean',
            'pega_onibus' => 'nullable|boolean',
            'vale_transporte' => 'sometimes|boolean',
            'rota_atende' => 'sometimes|boolean',
            'rota_tipo' => 'sometimes|string|max:100',
            'quem_entrevistou' => 'sometimes|string|min:3|max:255'
        ], [
            'tem_rota.boolean' => 'Campo tem rota deve ser verdadeiro ou falso',
            'vale_transporte.boolean' => 'Campo vale transporte deve ser verdadeiro ou falso',
            'rota_atende.boolean' => 'Campo rota atende deve ser verdadeiro ou falso',
            'rota_tipo.max' => 'Tipo de rota deve ter no máximo 100 caracteres',
            'quem_entrevistou.min' => 'Nome do entrevistador deve ter pelo menos 3 caracteres',
            'quem_entrevistou.max' => 'Nome do entrevistador deve ter no máximo 255 caracteres'
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao alterar a entrevista',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $parecerRota->update($dados);

            DB::commit();

            return response()->json([
                'msg' => 'Parecer de rota atualizado com sucesso',
                'data' => $parecerRota->fresh()
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Erro ao atualizar parecer rota', [
                'error' => $e->getMessage(),
                'parecer_id' => $parecerRota->id,
                'user_id' => auth()->id(),
                'data' => $dados
            ]);

            return response()->json([
                'msg' => 'Erro ao atualizar o parecer de rota. Tente novamente.'
            ], 400);
        }
    }

    /**
     * Pré-visualiza a mensagem de WhatsApp do parecer rota.
     */
    public function previewWhatsapp(ParecerRota $parecerRota, ParecerRotaWhatsappService $whatsappService): JsonResponse
    {
        $user = auth()->user();

        if (!$user->enviaWhatsApp()) {
            return response()->json([
                'msg' => 'Envio de WhatsApp não habilitado para esta empresa.',
            ], 400);
        }

        if (!$parecerRota->tem_rota) {
            return response()->json([
                'msg' => 'Envio permitido somente quando há rota que atende.',
            ], 400);
        }

        try {
            $parecerRota->load([
                'FeedbackCurriculo.Curriculo',
                'FeedbackCurriculo.Curriculo.Formacao',
                'FeedbackCurriculo.Curriculo.Telefones',
                'FeedbackCurriculo.telCadPrincipal',
                'FeedbackCurriculo.TelPrincipal',
                'FeedbackCurriculo.vagaSelecionada',
                'QuemEnviouWhatsapp:id,nome',
            ]);

            $feedback = $parecerRota->FeedbackCurriculo;

            if (!$feedback) {
                return response()->json([
                    'msg' => 'Feedback não encontrado para este parecer.',
                ], 404);
            }

            $telefoneWhatsapp = $feedback->Curriculo?->Telefones
                ?->where('tipo', TelefoneCurriculo::TIPO_WHATS)
                ->sortByDesc(fn (TelefoneCurriculo $telefone) => $telefone->principal ? 1 : 0)
                ->first();

            $candidato = $feedback->toArray();
            $candidato['tel_principal'] = $feedback->telCadPrincipal ?? $feedback->TelPrincipal;
            $candidato['telefone_whatsapp_candidato'] = $telefoneWhatsapp;

            return response()->json([
                'mensagem' => $whatsappService->montarMensagem($parecerRota, $user),
                'feedback_id' => $parecerRota->feedback_id,
                'candidato' => $candidato,
                'parecer_rota' => [
                    'id' => $parecerRota->id,
                    'whatsapp_enviado_em' => $parecerRota->whatsapp_enviado_em,
                    'quem_enviou_whatsapp' => $parecerRota->QuemEnviouWhatsapp,
                ],
                'telefone_whatsapp_candidato' => $telefoneWhatsapp,
                'tel_principal' => $candidato['tel_principal'],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Erro ao pré-visualizar WhatsApp parecer rota', [
                'error' => $e->getMessage(),
                'parecer_id' => $parecerRota->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'msg' => 'Erro ao carregar pré-visualização do WhatsApp.',
            ], 500);
        }
    }

    /**
     * Envia informações da rota via WhatsApp ao candidato.
     */
    public function enviarWhatsapp(Request $request, ParecerRota $parecerRota, ParecerRotaWhatsappService $whatsappService): JsonResponse
    {
        $dadosValidados = \Validator::make($request->all(), [
            'telefone' => 'required|string|min:10',
            'tipo' => 'required|in:' . TelefoneCurriculo::TIPO_WHATS,
        ], [
            'telefone.required' => 'Informe o telefone WhatsApp do candidato',
            'telefone.min' => 'Telefone inválido',
            'tipo.required' => 'Informe o tipo do telefone',
            'tipo.in' => 'O telefone deve ser do tipo WhatsApp',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao enviar WhatsApp',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            $parecerAtualizado = $whatsappService->enviar(
                $parecerRota,
                $dadosValidados->validated()['telefone'],
                $dadosValidados->validated()['tipo'],
                auth()->user()
            );

            return response()->json([
                'msg' => 'WhatsApp enfileirado com sucesso',
                'data' => $parecerAtualizado,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar WhatsApp parecer rota', [
                'error' => $e->getMessage(),
                'parecer_id' => $parecerRota->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'msg' => 'Erro ao enviar WhatsApp. Tente novamente.',
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ParecerRota $parecerRota
     * @return JsonResponse
     */
    public function destroy(ParecerRota $parecerRota)
    {
        try {
            DB::beginTransaction();

            $parecerRota->delete();

            DB::commit();

            return response()->json([
                'msg' => 'Parecer de rota removido com sucesso'
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Erro ao deletar parecer rota', [
                'error' => $e->getMessage(),
                'parecer_id' => $parecerRota->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'msg' => 'Erro ao remover parecer de rota'
            ], 400);
        }
    }

    /**
     * Atualizar com filtros usando ParecerRotaFilter
     */
    public function atualizar(Request $request)
    {
        try {
            $perPage = $request->input('pages', 15);

            // Usar ParecerRotaFilter ao invés da lógica manual
            $resultado = ParecerRotaFilter::make()
                ->apply($request)
                ->paginate($perPage);

            return response()->json([
                'atual' => $resultado->currentPage(),
                'ultima' => $resultado->lastPage(),
                'total' => $resultado->total(),
                'dados' => [
                    'itens' => $resultado->items(),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar parecer rota', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'filters' => $request->all()
            ]);

            return response()->json([
                'msg' => 'Erro ao carregar dados'
            ], 500);
        }
    }

    /**
     * Método de filtro usando ParecerRotaFilter (mantido para compatibilidade)
     */
    public function filtro(Request $request)
    {
        return ParecerRotaFilter::make()
            ->apply($request)
            ->getQuery();
    }

    /**
     * Export usando novo Job robusto
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $requestData = $request->all();

            // Criar uma chave única baseada no usuário e parâmetros da request
            $cacheKey = 'export_parecer_rota_' . $userId . '_' . md5(json_encode($requestData));

            // Verificar se já existe um export em andamento
            if (Cache::has($cacheKey)) {
                $cacheData = Cache::get($cacheKey);

                $status = $cacheData['status'] ?? 'processing';
                $attempts = $cacheData['attempt'] ?? 1;
                $maxTries = $cacheData['max_tries'] ?? 3;

                $message = match ($status) {
                    'processing' => "Exportação em andamento (tentativa {$attempts}/{$maxTries}). Aguarde a conclusão.",
                    'retrying' => "Exportação tentando novamente (tentativa {$attempts}/{$maxTries}). Aguarde.",
                    'completed' => "Exportação já foi concluída. Verifique suas notificações.",
                    'failed' => "Última exportação falhou após {$maxTries} tentativas. Você pode tentar novamente.",
                    default => "Já existe uma exportação em andamento. Aguarde a conclusão."
                };

                return response()->json([
                    'msg' => $message,
                    'status' => $status,
                    'initiated_at' => $cacheData['initiated_at'] ?? null,
                    'attempts' => $attempts,
                    'max_tries' => $maxTries,
                    'last_error' => $cacheData['last_error'] ?? null
                ], 200);
            }

            // Validar se vai encontrar registros antes de disparar o job
            $filter = ParecerRotaFilter::make()->apply($requestData);


            // Se tem selecionados, aplica o filtro de IDs
            if (!empty($requestData['selecionados'])) {
                $filter->whereIds($requestData['selecionados']);
            }

            $count = $filter->count();

            if ($count === 0) {
                return response()->json([
                    'msg' => 'Nenhum registro encontrado com os filtros aplicados'
                ], 400);
            }

            $nameArquivo = "parecer_rota_" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
            $expiresAt = now()->addMinutes(15);

            // Armazenar no cache com tempo de expiração definido
            Cache::put($cacheKey, [
                'filename' => $nameArquivo,
                'initiated_at' => now(),
                'expires_at' => $expiresAt,
                'user_id' => $userId,
                'status' => 'queued',
                'attempt' => 0,
                'max_tries' => 3,
                'progress' => 0
            ], $expiresAt);

            // Dispatch do job robusto
            JobExportaParecerRota::dispatch(
                $userId,
                $requestData,
                $nameArquivo,
                $cacheKey
            );

            return response()->json([
                'msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.',
                'export_id' => $cacheKey,
                'estimated_time' => $this->estimateExportTime($count),
                'registros_encontrados' => $count
            ]);

        } catch (\Exception $e) {
            \Log::error("Erro ao iniciar exportação de parecer rota", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);

            // Limpar cache em caso de erro
            if (isset($cacheKey)) {
                Cache::forget($cacheKey);
            }

            return response()->json([
                'msg' => 'Erro interno ao iniciar exportação'
            ], 500);
        }
    }

    /**
     * Geração de PDF
     */
    public function getFichaPdf(Request $request)
    {
        $parecer_rota = ParecerRota::find($request->id)->append('data_entrevista');
        $dados = $parecer_rota;
        $pdf = PDF::loadView('pdf.entrevista.parecer_rota.ficha', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("parecer_rota" . Str::slug($parecer_rota->FeedbackCurriculo->Curriculo->nome) . ".pdf");
    }

    /**
     * Estatísticas
     */
    public function estatisticas(Request $request): JsonResponse
    {
        try {
            $filter = ParecerRotaFilter::make()->apply($request);

            $total = $filter->count();

            // Estatísticas específicas usando o filter
            $comRota = ParecerRotaFilter::make()
                ->apply($request)
                ->whereRaw('EXISTS (SELECT 1 FROM parecer_rotas WHERE parecer_rotas.feedback_id = feedback_curriculos.id AND parecer_rotas.tem_rota = 1)')
                ->count();

            $semRota = $total - $comRota;

            $pcdTotal = ParecerRotaFilter::make()
                ->apply($request)
                ->whereRaw('EXISTS (SELECT 1 FROM curriculos WHERE curriculos.id = feedback_curriculos.curriculo_id AND curriculos.pcd = 1)')
                ->count();

            return response()->json([
                'total' => $total,
                'com_rota' => $comRota,
                'sem_rota' => $semRota,
                'pcd_total' => $pcdTotal,
                'percentual_com_rota' => $total > 0 ? round(($comRota / $total) * 100, 2) : 0,
                'percentual_pcd' => $total > 0 ? round(($pcdTotal / $total) * 100, 2) : 0,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao calcular estatísticas parecer rota', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'filters' => $request->all()
            ]);

            return response()->json([
                'msg' => 'Erro ao calcular estatísticas'
            ], 500);
        }
    }

    /**
     * Método auxiliar para estimar tempo de exportação
     */
    private function estimateExportTime(int $recordCount): string
    {
        if ($recordCount < 100) {
            return '30 segundos - 1 minuto';
        } elseif ($recordCount < 1000) {
            return '1-3 minutos';
        } elseif ($recordCount < 5000) {
            return '3-8 minutos';
        } else {
            return '8-15 minutos';
        }
    }
}
