<?php

namespace App\Services\Avaliacoes;

use App\Jobs\Avaliacoes\SendAvaliacaoPendenciaMailJob;
use App\Models\Avaliacao;
use App\Models\AvaliacaoFeedback;
use App\Models\AvaliacaoNotificacao;
use App\Models\AvaliacaoResultado;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AvaliacaoNotificacaoService
{
    public const TIPO_PROXIMA_ETAPA = 'proxima_etapa';
    public const TIPO_LEMBRETE_MANUAL = 'lembrete_manual';
    public const TIPO_LEMBRETE_PRAZO = 'lembrete_prazo';

    public function notificarProximaEtapaPorConclusao(AvaliacaoFeedback $avaliacaoFeedback): int
    {
        $feedbacks = $this->carregarFluxoDoFuncionario($avaliacaoFeedback);

        return $this->notificarPendentesAcionaveis(
            $feedbacks,
            self::TIPO_PROXIMA_ETAPA,
            [],
            true
        );
    }

    public function notificarPendenteManual(AvaliacaoFeedback $avaliacaoFeedback, User $usuario): bool
    {
        $feedbacks = $this->carregarFluxoDoFuncionario($avaliacaoFeedback);
        $pendente = $feedbacks->firstWhere('id', $avaliacaoFeedback->id);

        if (!$pendente || !$this->isFeedbackPendenteAcionavel($pendente)) {
            return false;
        }

        return $this->dispararNotificacao($pendente, self::TIPO_LEMBRETE_MANUAL, [
            'solicitante' => $usuario->nome,
            'usuario_solicitante_id' => $usuario->id,
        ], false);
    }

    public function notificarPendentesManualmente(Collection $feedbacks, User $usuario): int
    {
        $feedbacksDecorados = $this->decorarFeedbacks($feedbacks);

        return $this->notificarPendentesAcionaveis(
            $feedbacksDecorados,
            self::TIPO_LEMBRETE_MANUAL,
            ['solicitante' => $usuario->nome, 'usuario_solicitante_id' => $usuario->id],
            false
        );
    }

    public function notificarPendentesPorPrazo(int $diasRestantes, ?int $empresaId = null, ?Carbon $dataBase = null): int
    {
        $dataBase = ($dataBase ?: now())->copy()->startOfDay();

        $feedbacks = $this->baseFeedbackQuery($empresaId)
            ->whereHas('Avaliacao', function ($query) {
                $query->withoutGlobalScopes()
                    ->where('status', Avaliacao::STATUS_ABERTA)
                    ->whereAtivo(true);
            })->get();

        $feedbacksDecorados = $this->decorarFeedbacks($feedbacks)->filter(function ($item) use ($diasRestantes, $dataBase) {
            $dataFimPrazo = $this->parseDataPrazo($item->Avaliacao?->getRawOriginal('data_fim_prazo') ?: $item->Avaliacao?->data_fim_prazo);

            if (!$dataFimPrazo) {
                return false;
            }

            $dias = (int) $dataBase->diffInDays($dataFimPrazo->copy()->startOfDay(), false);

            return $dias === (int) $diasRestantes;
        });

        return $this->notificarPendentesAcionaveis(
            $feedbacksDecorados,
            self::TIPO_LEMBRETE_PRAZO,
            ['dias_restantes' => $diasRestantes],
            true
        );
    }

    public function decorarFeedbacks(Collection $feedbacks): Collection
    {
        return $feedbacks->groupBy(fn ($item) => $item->avaliacao_id . '-' . $item->funcionario_id)
            ->flatMap(function (Collection $grupo) {
                $totalAvaliacoes = $grupo->count();
                $concluidas = $grupo->filter(fn ($item) => in_array($item->status, [AvaliacaoFeedback::STATUS_CONCLUIDA, AvaliacaoFeedback::STATUS_FINAL], true))->count();
                $autoAvaliacao = $grupo->first(fn ($item) => $item->origem_feedback === AvaliacaoFeedback::ORIGEM_FUNCIONARIO && $item->avaliador_id === $item->funcionario_id);
                $fezAutoAvaliacao = $autoAvaliacao && in_array($autoAvaliacao->status, [AvaliacaoFeedback::STATUS_CONCLUIDA, AvaliacaoFeedback::STATUS_FINAL], true);
                $pares = $grupo->filter(fn ($item) => $item->origem_feedback === AvaliacaoFeedback::ORIGEM_AVALIADOR && !$item->principal);
                $pendentePar = $pares->contains(fn ($item) => $item->status === AvaliacaoFeedback::STATUS_AGUARDANDO);

                return $grupo->map(function ($item) use ($totalAvaliacoes, $concluidas, $fezAutoAvaliacao, $pendentePar) {
                    $item->total_avaliacoes = $totalAvaliacoes;
                    $item->total_avaliacoes_concluidas = $concluidas;
                    $item->fez_auto_avaliacao = $fezAutoAvaliacao;
                    $item->pendente_autoavaliacao = $item->avaliador_id == $item->funcionario_id && !$fezAutoAvaliacao;
                    $item->pendente_autoavaliacao_colaborador = $item->avaliador_id != $item->funcionario_id && $item->status === AvaliacaoFeedback::STATUS_AGUARDANDO && !$fezAutoAvaliacao;
                    $item->pendente_avaliacao_par = $pendentePar;
                    $item->pendente_avaliacao_gestor = $totalAvaliacoes - $concluidas;
                    $item->fazer_avaliacao_final = $item->principal && $concluidas === $totalAvaliacoes && $item->status !== AvaliacaoFeedback::STATUS_FINAL;

                    if ($item->Avaliacao && !$item->Avaliacao->auto_avaliacao) {
                        $item->pendente_avaliacao_gestor = $totalAvaliacoes - $concluidas;
                        $item->fazer_avaliacao_final = $item->principal && $concluidas === $totalAvaliacoes && $item->status !== AvaliacaoFeedback::STATUS_FINAL;
                    }

                    $item->pdi_cadastrado = false;
                    if ($item->principal) {
                        $item->pdi_cadastrado = AvaliacaoResultado::where('avaliacao_feedback_id', $item->id)
                            ->withoutGlobalScopes()
                            ->where('gestor_id', $item->avaliador_id)
                            ->exists();
                    }

                    return $item;
                });
            })->values();
    }

    public function isFeedbackPendenteAcionavel(AvaliacaoFeedback $item): bool
    {
        if ($item->status !== AvaliacaoFeedback::STATUS_AGUARDANDO) {
            return false;
        }

        if ($item->Avaliacao?->auto_avaliacao) {
            return (
                ($item->status === AvaliacaoFeedback::STATUS_AGUARDANDO && $item->fez_auto_avaliacao && !$item->principal) ||
                ($item->status === AvaliacaoFeedback::STATUS_AGUARDANDO && $item->fez_auto_avaliacao && $item->principal && !$item->pendente_avaliacao_par) ||
                ($item->status === AvaliacaoFeedback::STATUS_AGUARDANDO && !$item->fez_auto_avaliacao && $item->avaliador_id === $item->funcionario_id)
            );
        }

        return $item->status === AvaliacaoFeedback::STATUS_AGUARDANDO && $item->principal;
    }

    private function notificarPendentesAcionaveis(Collection $feedbacks, string $tipo, array $extra, bool $usarCache): int
    {
        $total = 0;

        foreach ($feedbacks as $feedback) {
            if (!$this->isFeedbackPendenteAcionavel($feedback)) {
                continue;
            }

            if ($this->dispararNotificacao($feedback, $tipo, $extra, $usarCache)) {
                $total++;
            }
        }

        return $total;
    }

    private function dispararNotificacao(AvaliacaoFeedback $feedback, string $tipo, array $extra, bool $usarCache): bool
    {
        $email = $feedback->Avaliador?->login;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $cacheKey = $this->getCacheKey($feedback, $tipo, $extra);
        if ($usarCache && !Cache::add($cacheKey, true, now()->addDays(30))) {
            return false;
        }

        $payload = $this->montarPayload($feedback, $tipo, $extra);
        $notificacao = AvaliacaoNotificacao::withoutGlobalScopes()->create([
            'empresa_id' => $feedback->empresa_id,
            'avaliacao_id' => $feedback->avaliacao_id,
            'avaliacao_feedback_id' => $feedback->id,
            'funcionario_id' => $feedback->funcionario_id,
            'avaliador_id' => $feedback->avaliador_id,
            'usuario_solicitante_id' => $extra['usuario_solicitante_id'] ?? null,
            'canal' => 'email',
            'modo_disparo' => in_array($tipo, [self::TIPO_PROXIMA_ETAPA, self::TIPO_LEMBRETE_PRAZO], true) ? 'automatico' : 'manual',
            'tipo' => $tipo,
            'status' => 'pendente',
            'destinatario_nome' => $feedback->Avaliador?->nome,
            'destinatario_email' => $feedback->Avaliador?->login,
            'destinatario_telefone' => null,
            'assunto' => $payload['subject'] ?? null,
            'payload' => $payload,
        ]);

        SendAvaliacaoPendenciaMailJob::dispatch($notificacao->id);

        return true;
    }

    private function montarPayload(AvaliacaoFeedback $feedback, string $tipo, array $extra): array
    {
        $etapa = $this->resolverNomeEtapa($feedback);
        $prazo = $feedback->Avaliacao?->data_fim_prazo;
        $isAutoavaliacao = $feedback->origem_feedback === AvaliacaoFeedback::ORIGEM_FUNCIONARIO && !$feedback->principal;

        $subject = match ($tipo) {
            self::TIPO_PROXIMA_ETAPA => 'Nova etapa de avaliação disponível',
            self::TIPO_LEMBRETE_MANUAL => 'Lembrete de avaliação pendente',
            self::TIPO_LEMBRETE_PRAZO => ($extra['dias_restantes'] ?? 0) > 0
                ? 'Prazo da avaliação encerrando em ' . $extra['dias_restantes'] . ' dia(s)'
                : 'Prazo da avaliação encerra hoje',
            default => 'Notificação de avaliação',
        };

        $mensagem = match ($tipo) {
            self::TIPO_PROXIMA_ETAPA => $isAutoavaliacao
                ? "Informamos que a autoavaliação de {$feedback->Funcionario?->nome} está disponível para preenchimento."
                : ($feedback->principal
                    ? "Informamos que a avaliação de {$feedback->Funcionario?->nome} avançou para a etapa final sob sua responsabilidade."
                    : "Informamos que a avaliação de {$feedback->Funcionario?->nome} avançou para a etapa {$etapa} sob sua responsabilidade."),
            self::TIPO_LEMBRETE_MANUAL => $isAutoavaliacao
                ? "Identificamos que a autoavaliação de {$feedback->Funcionario?->nome} permanece pendente de conclusão."
                : "Identificamos que a avaliação de {$feedback->Funcionario?->nome}, na etapa {$etapa}, permanece pendente de conclusão.",
            self::TIPO_LEMBRETE_PRAZO => ($extra['dias_restantes'] ?? 0) > 0
                ? ($isAutoavaliacao
                    ? "A autoavaliação de {$feedback->Funcionario?->nome} deverá ser concluída até {$prazo}."
                    : "A avaliação de {$feedback->Funcionario?->nome}, referente à etapa {$etapa}, deverá ser concluída até {$prazo}.")
                : ($isAutoavaliacao
                    ? "O prazo para conclusão da autoavaliação de {$feedback->Funcionario?->nome} encerra-se hoje."
                    : "O prazo para conclusão da avaliação de {$feedback->Funcionario?->nome}, referente à etapa {$etapa}, encerra-se hoje."),
            default => '',
        };

        $complemento = match ($tipo) {
            self::TIPO_PROXIMA_ETAPA => $isAutoavaliacao
                ? 'Solicitamos que acesse o menu Minhas Avaliações para registrar sua autoavaliação dentro do prazo estabelecido.'
                : 'Solicitamos que acesse o menu Minhas Avaliações para registrar sua avaliação dentro do prazo estabelecido.',
            self::TIPO_LEMBRETE_MANUAL => $isAutoavaliacao
                ? 'Solicitamos que acesse o menu Minhas Avaliações e conclua sua autoavaliação o quanto antes.'
                : 'Solicitamos que acesse o menu Minhas Avaliações e conclua esta etapa o quanto antes.',
            self::TIPO_LEMBRETE_PRAZO => $isAutoavaliacao
                ? 'Solicitamos que acesse o menu Minhas Avaliações e conclua sua autoavaliação dentro do prazo estabelecido.'
                : 'Solicitamos que acesse o menu Minhas Avaliações e conclua esta etapa dentro do prazo estabelecido.',
            default => '',
        };

        return [
            'tipo' => $tipo,
            'subject' => $subject,
            'mensagem' => $mensagem,
            'complemento' => $complemento,
            'nome' => $feedback->Avaliador?->nome,
            'email' => $feedback->Avaliador?->login,
            'funcionario' => $feedback->Funcionario?->nome,
            'avaliacao' => $feedback->Avaliacao?->titulo,
            'etapa' => $etapa,
            'prazo_final' => $prazo,
            'empresa_id' => $feedback->empresa_id,
        ];
    }

    private function resolverNomeEtapa(AvaliacaoFeedback $feedback): string
    {
        if ($feedback->origem_feedback === AvaliacaoFeedback::ORIGEM_FUNCIONARIO && !$feedback->principal) {
            return 'Autoavaliação';
        }

        $label = trim((string) optional($feedback->TipoAvaliador)->label);
        if ($label === '') {
            return $feedback->principal ? 'Gestor (Avaliador Final)' : 'Avaliação';
        }

        return $feedback->principal && !str_contains($label, '(Avaliador Final)') ? "{$label} (Avaliador Final)" : $label;
    }

    private function getCacheKey(AvaliacaoFeedback $feedback, string $tipo, array $extra): string
    {
        $suffix = $tipo;

        if ($tipo === self::TIPO_LEMBRETE_PRAZO) {
            $suffix .= ':' . ($extra['dias_restantes'] ?? 'x') . ':' . ($feedback->Avaliacao?->data_fim_prazo ?? 'sem-prazo');
        }

        return "avaliacoes:notificacao:{$feedback->id}:{$suffix}";
    }

    private function carregarFluxoDoFuncionario(AvaliacaoFeedback $avaliacaoFeedback): Collection
    {
        $feedbacks = $this->baseFeedbackQuery($avaliacaoFeedback->empresa_id)
            ->where('avaliacao_id', $avaliacaoFeedback->avaliacao_id)
            ->where('funcionario_id', $avaliacaoFeedback->funcionario_id)
            ->get();

        return $this->decorarFeedbacks($feedbacks);
    }

    private function baseFeedbackQuery(?int $empresaId = null)
    {
        return AvaliacaoFeedback::withoutGlobalScopes()
            ->with([
                'Avaliacao' => function ($query) {
                    $query->withoutGlobalScopes()->with([
                        'AvaliacaoTipo' => function ($subQuery) {
                            $subQuery->withoutGlobalScopes();
                        }
                    ]);
                },
                'TipoAvaliador' => function ($query) {
                    $query->withoutGlobalScopes();
                },
                'Funcionario' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->select(['id', 'nome', 'login', 'temp', 'ativo', 'deleted_at'])
                        ->where('ativo', true)
                        ->whereNull('deleted_at');
                },
                'Avaliador' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->select(['id', 'nome', 'login', 'ativo', 'deleted_at'])
                        ->where('ativo', true)
                        ->whereNull('deleted_at');
                },
            ])
            ->whereHas('Funcionario', function ($query) {
                $query->withoutGlobalScopes()
                    ->where('ativo', true)
                    ->whereNull('deleted_at');
            })
            ->whereHas('Avaliador', function ($query) {
                $query->withoutGlobalScopes()
                    ->where('ativo', true)
                    ->whereNull('deleted_at');
            })
            ->when($empresaId, function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            });
    }

    private function parseDataPrazo(?string $data): ?Carbon
    {
        if (!$data) {
            return null;
        }

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
                return Carbon::createFromFormat('d/m/Y', $data);
            }

            return Carbon::parse($data);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
