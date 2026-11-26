<?php

namespace App\Services\Admissao;

use App\Models\Admissao;
use App\Models\Demissao;
use App\Models\FeedbackCurriculo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Demissão avulsa via privilégio no processo de Admissão (TD-002 fatia 5).
 */
class AdmissaoDemissaoPrivilegioService
{
    /**
     * @param  array<string, mixed>  $dados
     * @return array{status: int, body: array<string, mixed>}
     */
    public function demitir(array $dados, int $empresaId, ?int $actorUserId = null): array
    {
        $admissao = Admissao::query()
            ->whereKey((int) $dados['admissao_id'])
            ->whereHas('Feedback', function ($q) use ($empresaId) {
                // empresa_id já é o tenant explícito; evita ScopeOrderFeedback→Curriculo.
                $q->withoutGlobalScopes()->where('empresa_id', $empresaId);
            })
            ->first();

        if (!$admissao) {
            return $this->erroGenerico();
        }

        $feedback = FeedbackCurriculo::withoutGlobalScopes()
            ->whereKey($admissao->feedback_id)
            ->where('empresa_id', $empresaId)
            ->first(['id', 'curriculo_id', 'empresa_id']);

        if (!$feedback || !$feedback->curriculo_id) {
            return $this->erroGenerico();
        }

        // Ignora feedback_id/curriculo_id do payload (IDOR); só aceita se coincidirem com o registro tenant-checado.
        if (
            (int) ($dados['feedback_id'] ?? 0) !== (int) $feedback->id
            || (int) ($dados['curriculo_id'] ?? 0) !== (int) $feedback->curriculo_id
        ) {
            return $this->erroGenerico();
        }

        $userIdAtor = $actorUserId ?? (auth()->id() ? (int) auth()->id() : null);
        if (!$userIdAtor) {
            return $this->erroGenerico();
        }

        try {
            DB::transaction(function () use ($dados, $admissao, $feedback, $userIdAtor) {
                $admissao->update([
                    'status' => Admissao::STATUS_DEMITIDO,
                ]);

                Demissao::create([
                    'feedback_id' => (int) $feedback->id,
                    'cipa' => (bool) ($dados['cipa'] ?? false),
                    'data_desmobilizacao' => $dados['data_desmobilizacao'],
                    'motivo_rescisao_id' => (int) $dados['motivo_rescisao_id'],
                    'tipo_aviso_id' => (int) $dados['tipo_aviso_id'],
                    'solicitado_por' => (string) $dados['solicitado_por'],
                    'comentario' => $dados['comentario'] ?? null,
                    'user_id' => $userIdAtor,
                ]);

                User::whereKey((int) $feedback->curriculo_id)->update([
                    'ativo' => false,
                ]);
            });

            return [
                'status' => 200,
                'body' => ['msg' => 'Colaborador demitido com sucesso!'],
            ];
        } catch (Throwable $e) {
            Log::error('Erro ao demitir colaborador via privilégio', [
                'admissao_id' => $dados['admissao_id'] ?? null,
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'codigo' => $e->getCode(),
                'erro' => $e->getMessage(),
            ]);

            return $this->erroGenerico();
        }
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    private function erroGenerico(): array
    {
        return [
            'status' => 400,
            'body' => ['msg' => 'Houve um erro por favor tente novamente, Caso persista entre em contato com o suporte!'],
        ];
    }
}
