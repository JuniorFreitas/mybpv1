<?php

namespace App\Services\WeeklyReport;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Events\WeeklyReport\TarefaEvent;
use App\Jobs\Weekly_report\UpdateMembrosJob;
use App\Models\LogWeekly;
use App\Models\Quadro;
use App\Models\Sistema;
use App\Models\Tarefa;
use App\Models\User;
use App\Support\WeeklyReportHtml;
use Illuminate\Support\Facades\Event;

/**
 * Ao mencionar @membro em descrição/comentário, vincula o usuário à tarefa
 * (mesmo fluxo de updateMembro) para notificar e dar visibilidade no card.
 */
class AttachMentionedMembers
{
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
        if ($candidatos === []) {
            return collect();
        }

        $membros = User::query()
            ->whereIn('id', $candidatos)
            ->whereEmpresaId($empresa)
            ->whereAtivo(true)
            ->get();

        $adicionados = collect();
        foreach ($membros as $membro) {
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
                'descricao' => "mencionou e adicionou {$membro->nome} a esta tarefa",
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
}
