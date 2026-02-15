<?php

namespace App\Jobs\Movimentacao\ValorExtraPrevista;

use App\Mail\Movimentacao\ValorExtraPrevista\NotificacaoAprovacaoMail;
use App\Models\User;
use App\Models\ValorExtraPrevista;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class JobNotificacaoAprovacao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $valorExtraId;
    protected $usuarioParaId;
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
    public function __construct($valorExtraId, $usuarioParaId, $usuarioDeId, $etapa, $empresaId, $tipo = null, $status = null)
    {
        $this->valorExtraId = $valorExtraId;
        $this->usuarioParaId = $usuarioParaId;
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
        // Busca valor extra
        $valorExtra = ValorExtraPrevista::withoutGlobalScopes()
            ->select('id', 'colaborador_id', 'tipo', 'periodo_dias')
            ->find($this->valorExtraId);

        if (!$valorExtra) {
            return;
        }

        // Busca usuário destinatário
        $usuarioPara = User::withoutGlobalScopes()
            ->select('id', 'nome', 'login')
            ->find($this->usuarioParaId);

        if (!$usuarioPara || !$usuarioPara->login) {
            return;
        }

        // Busca usuário que está enviando
        $usuarioDe = User::withoutGlobalScopes()
            ->select('id', 'nome')
            ->find($this->usuarioDeId);

        if (!$usuarioDe) {
            return;
        }

        // Busca currículo diretamente
        $curriculo = \App\Models\Curriculo::withoutGlobalScope(\App\Scopes\ScopeEmpresa::class)
            ->select('id', 'nome')
            ->where('id', $valorExtra->colaborador_id)
            ->first();

        if (!$curriculo) {
            return;
        }

        // Busca empresa
        $empresa = DB::table('clientes')
            ->select('nome_fantasia')
            ->where('id', $this->empresaId)
            ->first();

        // Prepara dados para o email
        $dados = [
            'nome_de' => $usuarioDe->nome,
            'nome_para' => $usuarioPara->nome,
            'email_para' => $usuarioPara->login,
            'etapa' => $this->etapa,
            'valor_extra_id' => $valorExtra->id,
            'colaborador' => $curriculo->nome,
            'tipo_valor' => $valorExtra->tipo,
            'periodo_dias' => $valorExtra->periodo_dias,
            'empresa_id' => $this->empresaId,
            'nome_empresa' => $empresa ? $empresa->nome_fantasia : 'MyBP seu negócio na sua mão',
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
