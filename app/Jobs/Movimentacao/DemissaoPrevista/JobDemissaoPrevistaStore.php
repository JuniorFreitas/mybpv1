<?php

namespace App\Jobs\Movimentacao\DemissaoPrevista;

use App\Mail\Movimentacao\DemissaoPrevista\NotificacaoAprovacaoMail;
use App\Models\DemissaoPrevista;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Log\Logger;

class JobDemissaoPrevistaStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected $demissaoId;
    protected $gestorId;
    protected $usuarioDeId;
    protected $empresaId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($demissaoId, $gestorId, $usuarioDeId, $empresaId)
    {
        $this->demissaoId = $demissaoId;
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
        $demissao = DemissaoPrevista::withoutGlobalScopes()
            ->select('id', 'colaborador_id')
            ->find($this->demissaoId);
        Log::info('JobDemissaoPrevistaStore - Demissão ID: ' . $this->demissaoId);

        if (!$demissao) {
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

        Log::info('JobDemissaoPrevistaStore - Gestor ID: ' . $this->gestorId);

        if (!$gestor || !$gestor->login || !$usuarioDe) {
            return;
        }

        if ($gestor->empresa_id != $this->empresaId || !$gestor->ativo) {
            return;
        }

        // Busca currículo diretamente sem query extra do colaborador
        $curriculo = \App\Models\Curriculo::withoutGlobalScope(\App\Scopes\ScopeEmpresa::class)
            ->select('id', 'nome')
            ->where('id', $demissao->colaborador_id)
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
            'demissao_id' => $demissao->id,
            'colaborador' => $curriculo->nome,
            'empresa_id' => $this->empresaId,
            'nome_empresa' => $empresa ? $empresa->nome_fantasia : 'MyBP seu negócio na sua mão',
        ];

        \Mail::send(new NotificacaoAprovacaoMail($dados));
    }
}
