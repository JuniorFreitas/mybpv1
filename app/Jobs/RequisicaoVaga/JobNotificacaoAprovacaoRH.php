<?php

namespace App\Jobs\RequisicaoVaga;

use App\Mail\RequisicaoVagas\NotificacaoAprovacaoMail;
use App\Models\RequisicaoVaga;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class JobNotificacaoAprovacaoRH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $requisicaoId;
    protected $emailsRH;
    protected $usuarioDeId;
    protected $etapa;
    protected $tipo;
    protected $status;
    protected $empresaId;

    public function __construct($requisicaoId, array $emailsRH, $usuarioDeId, $etapa, $empresaId, $tipo = null, $status = null)
    {
        $this->requisicaoId = $requisicaoId;
        $this->emailsRH = $emailsRH;
        $this->usuarioDeId = $usuarioDeId;
        $this->etapa = $etapa;
        $this->tipo = $tipo;
        $this->status = $status;
        $this->empresaId = $empresaId;
    }

    public function handle()
    {
        if (empty($this->emailsRH)) {
            return;
        }

        $requisicao = RequisicaoVaga::withoutGlobalScopes()
            ->select('id', 'cargo_id', 'quantidade', 'tipo_contratacao')
            ->find($this->requisicaoId);

        if (!$requisicao) {
            return;
        }

        $usuarioDe = User::withoutGlobalScopes()
            ->select('id', 'nome')
            ->find($this->usuarioDeId);

        if (!$usuarioDe) {
            return;
        }

        $cargo = \App\Models\Vaga::select('id', 'nome')
            ->where('id', $requisicao->cargo_id)
            ->first();

        $empresa = DB::table('clientes')
            ->select('nome_fantasia')
            ->where('id', $this->empresaId)
            ->first();

        $emailPrincipal = array_shift($this->emailsRH);

        $dados = [
            'nome_de' => $usuarioDe->nome,
            'nome_para' => 'Equipe RH',
            'email_para' => $emailPrincipal,
            'emails_bcc' => $this->emailsRH,
            'etapa' => $this->etapa,
            'requisicao_id' => $requisicao->id,
            'cargo' => $cargo ? $cargo->nome : '',
            'quantidade' => $requisicao->quantidade,
            'tipo_contratacao' => $requisicao->tipo_contratacao,
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
