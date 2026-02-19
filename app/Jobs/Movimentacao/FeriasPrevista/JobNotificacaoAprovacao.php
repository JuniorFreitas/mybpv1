<?php

namespace App\Jobs\Movimentacao\FeriasPrevista;

use App\Mail\Movimentacao\FeriasPrevista\NotificacaoAprovacaoMail;
use App\Models\FeriasPrevista;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobNotificacaoAprovacao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected $feriasId;
    protected $usuarioId;
    protected $usuarioDeId;
    protected $etapa;
    protected $tipo;
    protected $status;
    protected $empresaId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($feriasId, $usuarioId, $usuarioDeId, $etapa, $empresaId, $tipo = null, $status = null)
    {
        $this->feriasId = $feriasId;
        $this->usuarioId = $usuarioId;
        $this->usuarioDeId = $usuarioDeId;
        $this->etapa = $etapa;
        $this->tipo = $tipo;
        $this->status = $status;
        $this->empresaId = $empresaId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Busca férias
        $ferias = FeriasPrevista::withoutGlobalScopes()
            ->select('id', 'colaborador_id')
            ->find($this->feriasId);

        if (!$ferias) {
            return;
        }

        // Busca múltiplos usuários em uma única query
        $usuarios = User::withoutGlobalScopes()
            ->select('id', 'nome', 'login', 'empresa_id', 'ativo')
            ->whereIn('id', [$this->usuarioId, $this->usuarioDeId])
            ->where('ativo', true)
            ->get()
            ->keyBy('id');

        $usuario = $usuarios->get($this->usuarioId);
        $usuarioDe = $usuarios->get($this->usuarioDeId);

        if (!$usuario || !$usuario->login || !$usuarioDe) {
            return;
        }

        // Valida que o usuário pertence à empresa correta e está ativo
        if ($usuario->empresa_id != $this->empresaId || !$usuario->ativo) {
            return;
        }

        // Busca currículo diretamente sem query extra do colaborador
        $curriculo = \App\Models\Curriculo::withoutGlobalScopes()
            ->select('id', 'nome')
            ->where('id', $ferias->colaborador_id)
            ->first();

        if (!$curriculo) {
            return;
        }

        $dados = [
            'nome_de' => $usuarioDe->nome,
            'nome_para' => $usuario->nome,
            'email_para' => $usuario->login,
            'etapa' => $this->etapa,
            'ferias_id' => $ferias->id,
            'colaborador' => $curriculo->nome,
            'empresa_id' => $this->empresaId
        ];

        if ($this->tipo) {
            $dados['tipo'] = $this->tipo;
        }

        if ($this->status) {
            $dados['status_aprovacao'] = $this->status;
        }

        \Mail::send(new NotificacaoAprovacaoMail($dados));
    }
}
