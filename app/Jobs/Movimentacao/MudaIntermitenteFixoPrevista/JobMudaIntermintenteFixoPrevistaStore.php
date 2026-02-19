<?php

namespace App\Jobs\Movimentacao\MudaIntermitenteFixoPrevista;

use App\Mail\Movimentacao\IntermitenteFixoPrevista\NotificacaoAprovacaoMail;
use App\Models\IntermitenteFixoPrevista;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class JobMudaIntermitenteFixoPrevistaStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected $intermitenteId;
    protected $gestorId;
    protected $usuarioDeId;
    protected $empresaId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($intermitenteId, $gestorId, $usuarioDeId, $empresaId)
    {
        $this->intermitenteId = $intermitenteId;
        $this->gestorId = $gestorId;
        $this->usuarioDeId = $usuarioDeId;
        $this->empresaId = $empresaId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $intermitente = IntermitenteFixoPrevista::withoutGlobalScopes()
            ->select('id', 'colaborador_id')
            ->find($this->intermitenteId);
        Log::info('JobMudaIntermitenteFixoPrevistaStore - Intermitente ID: ' . $this->intermitenteId);

        if (!$intermitente) {
            return;
        }

        // Busca múltiplos usuários em uma única query
        $usuarios = User::withoutGlobalScopes()
            ->select('id', 'nome', 'login', 'empresa_id', 'ativo')
            ->whereIn('id', [$this->gestorId, $this->usuarioDeId])
            ->get()
            ->keyBy('id');

        $gestor = $usuarios->get($this->gestorId);
        $usuarioDe = $usuarios->get($this->usuarioDeId);

        Log::info('JobMudaIntermitenteFixoPrevistaStore - Gestor ID: ' . $this->gestorId);

        if (!$gestor || !$gestor->login || !$usuarioDe) {
            return;
        }

        if ($gestor->empresa_id != $this->empresaId || !$gestor->ativo) {
            return;
        }

        // Busca currículo diretamente sem query extra do colaborador
        $curriculo = \App\Models\Curriculo::withoutGlobalScope(\App\Scopes\ScopeEmpresa::class)
            ->select('id', 'nome')
            ->where('id', $intermitente->colaborador_id)
            ->first();

        if (!$curriculo) {
            return;
        }

        // Usa DB::table para query simples sem hidratar modelo completo
        $empresa = DB::table('clientes')
            ->select('nome_fantasia')
            ->where('id', $this->empresaId)
            ->first();

        $dados = [
            'nome_de' => $usuarioDe->nome,
            'nome_para' => $gestor->nome,
            'email_para' => $gestor->login,
            'etapa' => 'Gestor',
            'tipo' => 'criacao',
            'intermitente_id' => $intermitente->id,
            'colaborador' => $curriculo->nome,
            'empresa_id' => $this->empresaId,
            'nome_empresa' => $empresa ? $empresa->nome_fantasia : 'MyBP seu negócio na sua mão',
        ];

        \Mail::send(new NotificacaoAprovacaoMail($dados));
    }
}
