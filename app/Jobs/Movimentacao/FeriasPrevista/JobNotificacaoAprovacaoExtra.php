<?php

namespace App\Jobs\Movimentacao\FeriasPrevista;

use App\Mail\Movimentacao\FeriasPrevista\NotificacaoAprovacaoMail;
use App\Models\Ferias;
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

    protected $feriasId;
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
    public function __construct($feriasId, array $emailsExtra, $usuarioDeId, $etapa, $empresaId, $tipo = null, $status = null)
    {
        $this->feriasId = $feriasId;
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

        // Busca férias
        $ferias = Ferias::withoutGlobalScopes()
            ->select('id', 'admissao_id')
            ->find($this->feriasId);

        if (!$ferias) {
            return;
        }

        // Busca usuário que está enviando
        $usuarioDe = User::withoutGlobalScopes()
            ->select('id', 'nome')
            ->find($this->usuarioDeId);

        if (!$usuarioDe) {
            return;
        }

        // Busca nome do colaborador usando query builder direto
        $nomeColaborador = DB::table('admissoes as a')
            ->join('feedback_curriculos as f', 'a.feedback_id', '=', 'f.id')
            ->join('curriculos as c', 'f.curriculo_id', '=', 'c.id')
            ->where('a.id', $ferias->admissao_id)
            ->value('c.nome');

        if (!$nomeColaborador) {
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
            'ferias_id' => $ferias->id,
            'colaborador' => $nomeColaborador,
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
