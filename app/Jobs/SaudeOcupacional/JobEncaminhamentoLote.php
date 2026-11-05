<?php

namespace App\Jobs\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Painel\PainelCache;
use App\Models\EmpresaExame;
use App\Models\ExameFuncionario;
use App\Models\ExameTipo;
use App\Models\FeedbackCurriculo;
use App\Models\Sistema;
use App\Services\ControleExames\ExameEncaminhamentoNotificacaoService;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cria os encaminhamentos de um lote (até 25 colaboradores) pelo mesmo caminho do encaminhamento
 * individual com PCMSO, e dispara e-mail/WhatsApp para clínica e colaborador se pedido.
 * Um colaborador com erro não derruba os outros. Log sem dados pessoais.
 */
class JobEncaminhamentoLote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    /**
     * @param  list<int>  $feedbackIds
     * @param  array{exame_tipo_id: int, empresa_exame_id: int, pcmso_id: int, formulario_id: int, data_realizacao: string, envia_email: bool, envia_whatsapp: bool}  $parametros
     */
    public function __construct(
        public int $empresaId,
        public int $usuarioId,
        public array $feedbackIds,
        public array $parametros
    ) {
    }

    public function handle(ExameEncaminhamentoNotificacaoService $notificacao): void
    {
        // ExameFuncionario preenche empresa e "quem encaminhou" pelo usuário logado.
        Auth::loginUsingId($this->usuarioId);

        $clinica = EmpresaExame::withoutGlobalScopes()->where('empresa_id', $this->empresaId)->find($this->parametros['empresa_exame_id']);
        $tipo = ExameTipo::query()->find($this->parametros['exame_tipo_id']);
        $dataRealizacaoBr = CarbonImmutable::parse($this->parametros['data_realizacao'])->format('d/m/Y');
        $criados = 0;
        $falhas = 0;

        foreach ($this->feedbackIds as $feedbackId) {
            try {
                $token = Sistema::uuid();
                $exame = DB::transaction(fn () => ExameFuncionario::query()->create([
                    'feedback_id' => $feedbackId,
                    'empresa_exame_id' => $this->parametros['empresa_exame_id'],
                    'formulario_id' => $this->parametros['formulario_id'],
                    'respostas' => (object) [],
                    'token' => $token,
                    'pcmso' => true,
                    'pcmso_id' => $this->parametros['pcmso_id'],
                    'exame_tipo_id' => $this->parametros['exame_tipo_id'],
                    'encaminhamento_data' => $this->parametros['data_realizacao'],
                ]));
                $criados++;

                if (!$clinica || (!$this->parametros['envia_email'] && !$this->parametros['envia_whatsapp'])) {
                    continue;
                }

                $colaborador = FeedbackCurriculo::withoutGlobalScopes()
                    ->with(['Curriculo', 'TelPrincipal'])
                    ->select(['curriculo_id', 'id', 'telefone_id'])
                    ->find($feedbackId);
                $dataEncaminhamento = now()->format('d/m/Y H:i:s');

                if ($this->parametros['envia_email']) {
                    $notificacao->enviarEmail($exame, $colaborador, $clinica, (string) $tipo?->label, $token, $dataEncaminhamento, $dataRealizacaoBr);
                }
                if ($this->parametros['envia_whatsapp']) {
                    $notificacao->enviarWhatsapp($colaborador, $clinica, (string) $tipo?->label, $dataEncaminhamento, $dataRealizacaoBr);
                }
            } catch (\Throwable $e) {
                $falhas++;
                Log::warning('Encaminhamento em lote: falha em um colaborador', [
                    'empresa_id' => $this->empresaId,
                    'feedback_id' => $feedbackId,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        PainelCache::invalidar($this->empresaId);
        Log::info('Encaminhamento em lote processado', [
            'empresa_id' => $this->empresaId,
            'criados' => $criados,
            'falhas' => $falhas,
        ]);
        Auth::logout();
    }
}
