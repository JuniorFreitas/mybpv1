<?php

namespace App\Services\AtaReuniao;

use App\Models\AtaReuniao;
use App\Models\AtaReuniaoAcesso;
use App\Models\AtaReuniaoAprovacao;
use App\Models\AtaReuniaoEvento;
use App\Models\AtaReuniaoVersao;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AtaReuniaoAprovacaoService
{
    public function solicitar(AtaReuniao $ata, User $solicitante, array $aprovadorIds): int
    {
        return DB::transaction(function () use ($ata, $solicitante, $aprovadorIds) {
            $this->bloquearSeAprovada($ata);

            $versao = $this->registrarVersao($ata, $solicitante, 'Solicitacao de aprovacao');
            $total = 0;

            foreach (array_unique(array_filter($aprovadorIds)) as $aprovadorId) {
                $aprovador = User::where('empresa_id', $ata->empresa_id)->where('ativo', true)->find($aprovadorId);
                if (!$aprovador) {
                    continue;
                }

                AtaReuniaoAprovacao::withoutGlobalScopes()->updateOrCreate([
                    'empresa_id' => $ata->empresa_id,
                    'ata_reuniao_id' => $ata->id,
                    'versao' => $versao,
                    'aprovador_id' => $aprovador->id,
                ], [
                    'status' => 'pendente',
                    'decisao' => null,
                    'comentario' => null,
                    'respondido_em' => null,
                ]);

                AtaReuniaoAcesso::withoutGlobalScopes()->updateOrCreate([
                    'empresa_id' => $ata->empresa_id,
                    'ata_reuniao_id' => $ata->id,
                    'user_id' => $aprovador->id,
                    'papel' => AtaReuniaoAcesso::PAPEL_APROVADOR,
                ], [
                    'origem' => 'aprovacao',
                    'revogado_em' => null,
                ]);

                $total++;
            }

            $ata->withoutEvents(function () use ($ata) {
                $ata->update(['status' => AtaReuniao::STATUS_AGUARDANDO_APROVACAO]);
            });

            $this->evento($ata, $solicitante, 'aprovacao_solicitada', ['total_aprovadores' => $total]);

            return $total;
        });
    }

    public function decidir(AtaReuniao $ata, User $aprovador, string $decisao, ?string $comentario = null): AtaReuniaoAprovacao
    {
        return DB::transaction(function () use ($ata, $aprovador, $decisao, $comentario) {
            $aprovacao = AtaReuniaoAprovacao::withoutGlobalScopes()
                ->where('empresa_id', $ata->empresa_id)
                ->where('ata_reuniao_id', $ata->id)
                ->where('aprovador_id', $aprovador->id)
                ->where('versao', $ata->versao_atual ?: '0.1')
                ->where('status', 'pendente')
                ->firstOrFail();

            $status = in_array($decisao, ['aprovado', 'aprovado_com_ressalva'], true) ? 'aprovado' : $decisao;

            $aprovacao->update([
                'status' => $status,
                'decisao' => $decisao,
                'comentario' => $comentario,
                'respondido_em' => now(),
            ]);

            $this->evento($ata, $aprovador, 'aprovacao_decidida', [
                'decisao' => $decisao,
                'comentario' => $comentario,
            ]);

            $this->atualizarStatusDaAta($ata);

            return $aprovacao;
        });
    }

    public function registrarVersao(AtaReuniao $ata, User $autor, string $descricao): string
    {
        $versao = $ata->versao_atual ?: '0.1';

        AtaReuniaoVersao::withoutGlobalScopes()->firstOrCreate([
            'empresa_id' => $ata->empresa_id,
            'ata_reuniao_id' => $ata->id,
            'numero' => $versao,
        ], [
            'autor_id' => $autor->id,
            'descricao' => $descricao,
            'snapshot' => $ata->fresh(['Assuntos', 'Tipos', 'Acoes', 'Participantes'])?->toArray(),
        ]);

        return $versao;
    }

    private function atualizarStatusDaAta(AtaReuniao $ata): void
    {
        $pendentes = AtaReuniaoAprovacao::withoutGlobalScopes()
            ->where('empresa_id', $ata->empresa_id)
            ->where('ata_reuniao_id', $ata->id)
            ->where('versao', $ata->versao_atual ?: '0.1')
            ->where('status', 'pendente')
            ->exists();

        if ($pendentes) {
            return;
        }

        $rejeitada = AtaReuniaoAprovacao::withoutGlobalScopes()
            ->where('empresa_id', $ata->empresa_id)
            ->where('ata_reuniao_id', $ata->id)
            ->where('versao', $ata->versao_atual ?: '0.1')
            ->whereIn('status', ['rejeitado', 'ajustes_solicitados'])
            ->exists();

        $ata->withoutEvents(function () use ($ata, $rejeitada) {
            $ata->update($rejeitada ? [
                'status' => AtaReuniao::STATUS_AJUSTES_SOLICITADOS,
            ] : [
                'status' => AtaReuniao::STATUS_APROVADA,
                'aprovada_em' => now(),
                'bloqueada_em' => now(),
                'versao_atual' => '1.0',
            ]);
        });
    }

    private function bloquearSeAprovada(AtaReuniao $ata): void
    {
        if ($ata->bloqueada_em || in_array($ata->status, [AtaReuniao::STATUS_APROVADA, AtaReuniao::STATUS_PUBLICADA, AtaReuniao::STATUS_ENCERRADA], true)) {
            abort(422, 'Ata aprovada ou bloqueada exige nova versao ou reabertura autorizada.');
        }
    }

    private function evento(AtaReuniao $ata, User $ator, string $tipo, array $dados): void
    {
        AtaReuniaoEvento::create([
            'empresa_id' => $ata->empresa_id,
            'ata_reuniao_id' => $ata->id,
            'ator_id' => $ator->id,
            'tipo_evento' => $tipo,
            'dados' => $dados,
            'created_at' => now(),
        ]);
    }
}
