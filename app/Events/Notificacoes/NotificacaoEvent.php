<?php

namespace App\Events\Notificacoes;

use App\Models\Notificacao;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificacaoEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const MEMBRO_TAREFA_ADD = 'membro_tarefa_add';
    const MEMBRO_TAREFA_REMOVE = 'membro_tarefa_remove';
    const EXPORTACAO_EXCEL = 'exportacao_excel';
    const EXPORTACAO_PDF = 'exportacao_pdf';
    const IMPORTACAO_ADMISSOES_CONCLUIDA = 'importacao_admissoes_concluida';

    const TIPO_PADRAO = 'padrao';

    public $dados;
    public $evento;
    public $tipo;
    public $afterCommit = true; // só dispara se for comitado

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($dados, $evento, $tipo = 'tipo')
    {
        $this->dados = $dados;
        $this->evento = $evento;
        $this->tipo = $tipo;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $usuario = $this->dados['user_id'];
        return new PresenceChannel("notificacoes.{$usuario}");

    }

    public function broadcastAs()
    {
        return $this->evento;
    }

    public function broadcastWith()
    {
        switch ($this->evento) {
            case self::MEMBRO_TAREFA_ADD:
                $tarefa = $this->dados['tarefa'];

                $payload = [
                    'icone' => 'fas fa-tasks',
                    'titulo' => "Nova tarefa para você",
                    'descricao' => "Você foi adicionado(a) na tarefa {$tarefa->titulo} da lista {$tarefa->Lista->titulo}",
                ];
                $notificacao = Notificacao::create([
                    'tipo' => $this->tipo,
                    'payload' => $payload,
                    'user_id' => $this->dados['user_id'],
                    'visto' => false
                ]);
                return $notificacao->toArray();
            case self::MEMBRO_TAREFA_REMOVE:
                $tarefa = $this->dados['tarefa'];
                $saida = [
                    'icone' => 'fas fa-tasks',
                    'titulo' => "Você foi removido(a) de uma tarefa",
                    'descricao' => "Você foi removido(a) na tarefa {$tarefa->titulo} da lista {$tarefa->Lista->titulo}",
                ];
                $notificacao = Notificacao::create([
                    'tipo' => $this->tipo,
                    'payload' => $saida,
                    'user_id' => $this->dados['user_id'],
                    'visto' => false
                ]);
                return $notificacao->toArray();

            case self::EXPORTACAO_EXCEL:
                $saida = [
                    'icone' => 'fa fa-download',
                    'titulo' => "Seu excel {$this->dados['local']} está pronto",
                    'descricao' => "Verifique na área de downloads",
                ];
                $notificacao = Notificacao::create([
                    'tipo' => $this->tipo,
                    'payload' => $saida,
                    'user_id' => $this->dados['user_id'],
                    'visto' => false
                ]);
                return $notificacao->toArray();
            case self::EXPORTACAO_PDF:
                $saida = [
                    'icone' => 'fas fa-file-pdf',
                    'titulo' => "Seu pdf {$this->dados['local']} está pronto",
                    'descricao' => "Verifique na área de downloads",
                ];
                $notificacao = Notificacao::create([
                    'tipo' => $this->tipo,
                    'payload' => $saida,
                    'user_id' => $this->dados['user_id'],
                    'visto' => false
                ]);
                return $notificacao->toArray();

            case self::IMPORTACAO_ADMISSOES_CONCLUIDA:
                $totalProcessadas = $this->dados['total_processadas'] ?? 0;
                $totalSucesso = $this->dados['total_sucesso'] ?? 0;
                $totalErros = $this->dados['total_erros'] ?? 0;
                $saida = [
                    'icone' => 'fas fa-file-import',
                    'titulo' => 'Importação de admissões concluída',
                    'descricao' => "{$totalProcessadas} processadas, {$totalSucesso} com sucesso" . ($totalErros > 0 ? ", {$totalErros} com erro. Verifique seu e-mail para o relatório." : '.'),
                ];
                $notificacao = Notificacao::create([
                    'tipo' => $this->tipo,
                    'payload' => $saida,
                    'user_id' => $this->dados['user_id'],
                    'visto' => false
                ]);
                return $notificacao->toArray();
        }
    }
}
