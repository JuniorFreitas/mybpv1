<?php

namespace App\Jobs\Movimentacao\MudancaCargo;

use App\Mail\Movimentacao\MudancaCargo\NotificacaoAprovacaoMail;
use App\Models\MudancaCargo;
use App\Models\User;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobNotificacaoAprovacao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $mudanca_cargo_id;
    protected $user_id;
    protected $tipo;
    protected $etapa;
    protected $status;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mudanca_cargo_id, $user_id, $tipo = null, $etapa = null, $status = null)
    {
        $this->mudanca_cargo_id = $mudanca_cargo_id;
        $this->user_id = $user_id;
        $this->tipo = $tipo;
        $this->etapa = $etapa;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Busca mudança de cargo com relacionamentos eager
            $mudanca_cargo = MudancaCargo::withoutGlobalScopes()
                ->with(['Colaborador:id,nome', 'VagaAbertaAnterior.Vaga:id,nome', 'VagaAbertaNova.Vaga:id,nome'])
                ->find($this->mudanca_cargo_id);

            if (!$mudanca_cargo) {
                \Log::warning("Mudança de cargo {$this->mudanca_cargo_id} não encontrada");
                return;
            }

            $user = User::withoutGlobalScopes()
                ->where('id', $this->user_id)
                ->where('empresa_id', $mudanca_cargo->empresa_id)
                ->where('ativo', true)
                ->first();

            if (!$user) {
                \Log::warning("Usuário {$this->user_id} não encontrado ou inativo para notificação de mudança de cargo {$this->mudanca_cargo_id}");
                return;
            }

            // Busca centros de custo em uma única query
            $centro_custos = DB::table('centro_custos')
                ->whereIn('id', array_filter([$mudanca_cargo->anterior_centro_custo_id, $mudanca_cargo->novo_centro_custo_id]))
                ->select('id', 'label')
                ->get()
                ->keyBy('id');

            $centro_custo_anterior = $centro_custos->get($mudanca_cargo->anterior_centro_custo_id);
            $centro_custo_novo = $mudanca_cargo->novo_centro_custo_id ? $centro_custos->get($mudanca_cargo->novo_centro_custo_id) : null;

            $dados = [
                'mudanca_cargo_id' => $mudanca_cargo->id,
                'colaborador' => $mudanca_cargo->Colaborador ? $mudanca_cargo->Colaborador->nome : 'N/A',
                'cargo_anterior' => ($mudanca_cargo->VagaAbertaAnterior && $mudanca_cargo->VagaAbertaAnterior->Vaga) ? $mudanca_cargo->VagaAbertaAnterior->Vaga->nome : 'N/A',
                'cargo_novo' => ($mudanca_cargo->VagaAbertaNova && $mudanca_cargo->VagaAbertaNova->Vaga) ? $mudanca_cargo->VagaAbertaNova->Vaga->nome : 'Não mudou de cargo',
                'empresa_id' => $mudanca_cargo->empresa_id,
                'link' => route('g.movimentacao.index'),
                'status_aprovacao_gestor' => $mudanca_cargo->status_aprovacao_gestor,
                'status_aprovacao_extra' => $mudanca_cargo->status_aprovacao_extra,
                'status_aprovacao_rh' => $mudanca_cargo->status_aprovacao_rh,
                'data_solicitacao' => $mudanca_cargo->created_at ? $mudanca_cargo->created_at->format('d/m/Y H:i') : null,
                // Informações DE/PARA
                'mudancas' => [
                    'centro_custo' => [
                        'mudou' => !$mudanca_cargo->mantem_centro_custo,
                        'de' => $centro_custo_anterior ? $centro_custo_anterior->label : 'N/A',
                        'para' => $centro_custo_novo ? $centro_custo_novo->label : null,
                    ],
                    'cargo' => [
                        'mudou' => !$mudanca_cargo->mantem_cargo,
                        'de' => ($mudanca_cargo->VagaAbertaAnterior && $mudanca_cargo->VagaAbertaAnterior->Vaga) ? $mudanca_cargo->VagaAbertaAnterior->Vaga->nome : 'N/A',
                        'para' => ($mudanca_cargo->VagaAbertaNova && $mudanca_cargo->VagaAbertaNova->Vaga) ? $mudanca_cargo->VagaAbertaNova->Vaga->nome : null,
                    ],
                    'funcao' => [
                        'mudou' => !$mudanca_cargo->mantem_funcao,
                        'de' => $mudanca_cargo->anterior_funcao ?? 'N/A',
                        'para' => $mudanca_cargo->nova_funcao ?? null,
                    ],
                    'salario' => [
                        'mudou' => !$mudanca_cargo->mantem_salario,
                    ],
                ],
            ];

            if ($this->tipo) {
                $dados['tipo'] = $this->tipo;
            }

            if ($this->etapa) {
                $dados['etapa'] = $this->etapa;
            }

            if ($this->status) {
                $dados['status'] = $this->status;
            }

            \Mail::to($user->login, $user->nome)
                ->send(new NotificacaoAprovacaoMail($dados));
        } catch (\Exception $e) {
            \Log::error('JobNotificacaoAprovacao - Erro', ['id' => $this->mudanca_cargo_id, 'erro' => $e->getMessage()]);
            throw $e;
        }
    }
}
