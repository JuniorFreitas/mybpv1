<?php

namespace App\Services\WeeklyReport;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Events\WeeklyReport\TarefaEvent;
use App\Jobs\Weekly_report\UpdateMembrosJob;
use App\Models\LogWeekly;
use App\Models\Quadro;
use App\Models\QuadroMembro;
use App\Models\Sistema;
use App\Models\Tarefa;
use App\Models\User;
use App\Support\WeeklyReportHtml;
use Illuminate\Support\Facades\Event;

/**
 * Menções @usuario: vincula à tarefa (e ao quadro se preciso).
 * Descrição: sync no save (add/remove). Comentário: só add ao publicar.
 */
class AttachMentionedMembers
{
    /**
     * Sincroniza membros da descrição: adiciona mencionados e remove
     * quem estava na descrição anterior e saiu do HTML.
     *
     * @return array{added: \Illuminate\Support\Collection, removed: \Illuminate\Support\Collection}
     */
    public function syncDescriptionMentions(
        Tarefa $tarefa,
        Quadro $quadro,
        int $empresa,
        ?string $newHtml,
        ?string $previousHtml
    ): array {
        $newIds = WeeklyReportHtml::extractMentionUserIds($newHtml);
        $prevIds = WeeklyReportHtml::extractMentionUserIds($previousHtml);
        $authId = (int) auth()->id();

        $jaMembros = $tarefa->Membros()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        // Add: mencionados no HTML atual que ainda não estão no card
        $toAdd = array_values(array_diff($newIds, $jaMembros, [$authId]));
        // Remove: saíram da descrição (estavam mencionados antes e não estão mais)
        $toRemove = array_values(array_diff($prevIds, $newIds, [$authId]));

        $added = $this->addMembers($tarefa, $quadro, $empresa, $toAdd, 'mencionou e adicionou');
        $removed = $this->removeMembers($tarefa, $quadro, $toRemove);

        return ['added' => $added, 'removed' => $removed];
    }

    /**
     * @return \Illuminate\Support\Collection<int, User> membros efetivamente adicionados
     */
    public function attachFromHtml(Tarefa $tarefa, Quadro $quadro, int $empresa, ?string $html)
    {
        $ids = WeeklyReportHtml::extractMentionUserIds($html);
        if ($ids === []) {
            return collect();
        }

        $jaMembros = $tarefa->Membros()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $candidatos = array_values(array_diff($ids, $jaMembros, [(int) auth()->id()]));

        return $this->addMembers($tarefa, $quadro, $empresa, $candidatos, 'mencionou e adicionou');
    }

    /**
     * @param list<int> $userIds
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function addMembers(
        Tarefa $tarefa,
        Quadro $quadro,
        int $empresa,
        array $userIds,
        string $logPrefix
    ) {
        if ($userIds === []) {
            return collect();
        }

        $jaMembros = $tarefa->Membros()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $candidatos = array_values(array_diff($userIds, $jaMembros));
        if ($candidatos === []) {
            return collect();
        }

        $membros = User::query()
            ->whereIn('id', $candidatos)
            ->whereEmpresaId($empresa)
            ->whereAtivo(true)
            ->whereNull('deleted_at')
            ->where('grupo_id', '>', 0)
            ->get();

        $adicionados = collect();
        foreach ($membros as $membro) {
            if (!$quadro->temMembro($membro)) {
                if (!app(GrantWeeklyReportAccess::class)->handle($membro)) {
                    continue;
                }
                QuadroMembro::query()->firstOrCreate(
                    [
                        'quadro_id' => $quadro->id,
                        'user_id' => $membro->id,
                    ],
                    [
                        'papel' => QuadroMembro::PAPEL_MEMBRO,
                    ]
                );
            }

            $tarefa->Membros()->syncWithoutDetaching([$membro->id]);

            $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_MEMBROS);
            $evento->acao = TarefaEvent::ACAO_ADD;
            Event::dispatch($evento);

            Event::dispatch(new NotificacaoEvent([
                'tarefa' => $tarefa,
                'user_id' => $membro->id,
            ], NotificacaoEvent::MEMBRO_TAREFA_ADD, NotificacaoEvent::TIPO_PADRAO));

            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'tarefa_id' => $tarefa->id,
                'descricao' => "{$logPrefix} {$membro->nome} a esta tarefa",
            ]);

            if (auth()->user()
                && Sistema::validaEmail(auth()->user()->login)
                && Sistema::validaEmail($membro->login)
            ) {
                UpdateMembrosJob::dispatch([
                    'de' => auth()->user(),
                    'para' => $membro,
                    'acao' => TarefaEvent::ACAO_ADD,
                    'modelTarefa' => $tarefa,
                    'empresa_id' => $empresa,
                ])->afterResponse();
            }

            $adicionados->push($membro);
        }

        return $adicionados;
    }

    /**
     * @param list<int> $userIds
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function removeMembers(Tarefa $tarefa, Quadro $quadro, array $userIds)
    {
        if ($userIds === []) {
            return collect();
        }

        $membros = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'nome', 'login']);

        $removidos = collect();
        foreach ($membros as $membro) {
            if (!$tarefa->Membros()->where('users.id', $membro->id)->exists()) {
                continue;
            }

            $tarefa->Membros()->detach($membro->id);

            $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_MEMBROS);
            $evento->acao = TarefaEvent::ACAO_DELETE;
            Event::dispatch($evento);

            Event::dispatch(new NotificacaoEvent([
                'tarefa' => $tarefa,
                'user_id' => $membro->id,
            ], NotificacaoEvent::MEMBRO_TAREFA_REMOVE, NotificacaoEvent::TIPO_PADRAO));

            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'tarefa_id' => $tarefa->id,
                'descricao' => "removeu a menção de {$membro->nome} desta tarefa",
            ]);

            $removidos->push($membro);
        }

        return $removidos;
    }
}
