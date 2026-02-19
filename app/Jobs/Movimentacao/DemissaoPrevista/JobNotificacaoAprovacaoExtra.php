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

class JobNotificacaoAprovacaoExtra implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected $demissaoId;
    protected $emailsExtra; // Array de emails
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
    public function __construct($demissaoId, array $emailsExtra, $usuarioDeId, $etapa, $empresaId, $tipo = null, $status = null)
    {
        $this->demissaoId = $demissaoId;
        $this->emailsExtra = $emailsExtra;
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
        if (empty($this->emailsExtra)) {
            return;
        }

        // Busca demissão
        $demissao = DemissaoPrevista::withoutGlobalScopes()
            ->select('id', 'colaborador_id')
            ->find($this->demissaoId);

        if (!$demissao) {
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
            ->where('id', $demissao->colaborador_id)
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
        // Usa o primeiro email como destinatário principal e os demais como BCC
        $emailPrincipal = array_shift($this->emailsExtra);

        $dados = [
            'nome_de' => $usuarioDe->nome,
            'nome_para' => 'Equipe ' . $this->etapa,
            'email_para' => $emailPrincipal,
            'emails_bcc' => $this->emailsExtra, // Emails restantes vão como BCC
            'etapa' => $this->etapa,
            'demissao_id' => $demissao->id,
            'colaborador' => $curriculo->nome,
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
