<?php

namespace App\Services\AtaReuniao;

use App\Models\AtaReuniao;
use App\Models\AtaReuniaoCompartilhamentoExterno;
use App\Models\AtaReuniaoEvento;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AtaReuniaoCompartilhamentoService
{
    public function criarLink(AtaReuniao $ata, User $criador, ?string $email, ?string $nome = null, string $escopo = 'leitura'): array
    {
        $token = Str::random(64);
        $expiraEm = now()->addHours(24);

        $compartilhamento = AtaReuniaoCompartilhamentoExterno::create([
            'empresa_id' => $ata->empresa_id,
            'ata_reuniao_id' => $ata->id,
            'token_hash' => hash('sha256', $token),
            'nome_externo' => $nome,
            'email_externo' => $email,
            'escopo' => $escopo,
            'criado_por' => $criador->id,
            'expira_em' => $expiraEm,
        ]);

        AtaReuniaoEvento::create([
            'empresa_id' => $ata->empresa_id,
            'ata_reuniao_id' => $ata->id,
            'ator_id' => $criador->id,
            'tipo_evento' => 'compartilhamento_externo_criado',
            'entidade_tipo' => AtaReuniaoCompartilhamentoExterno::class,
            'entidade_id' => $compartilhamento->id,
            'dados' => ['email' => $email, 'escopo' => $escopo, 'expira_em' => $expiraEm->toDateTimeString()],
            'created_at' => now(),
        ]);

        return [
            'id' => $compartilhamento->id,
            'expira_em' => $expiraEm,
            'url' => URL::temporarySignedRoute('atareuniao.externo', $expiraEm, ['token' => $token]),
        ];
    }

    public function resolver(string $token): ?AtaReuniaoCompartilhamentoExterno
    {
        return AtaReuniaoCompartilhamentoExterno::where('token_hash', hash('sha256', $token))
            ->whereNull('revogado_em')
            ->where('expira_em', '>', now())
            ->first();
    }

    public function registrarAcesso(AtaReuniaoCompartilhamentoExterno $compartilhamento): void
    {
        $compartilhamento->update(['ultimo_acesso_em' => now()]);

        AtaReuniaoEvento::create([
            'empresa_id' => $compartilhamento->empresa_id,
            'ata_reuniao_id' => $compartilhamento->ata_reuniao_id,
            'ator_tipo' => 'externo',
            'tipo_evento' => 'compartilhamento_externo_acessado',
            'entidade_tipo' => AtaReuniaoCompartilhamentoExterno::class,
            'entidade_id' => $compartilhamento->id,
            'dados' => ['email' => $compartilhamento->email_externo],
            'created_at' => now(),
        ]);
    }

    public function revogar(AtaReuniaoCompartilhamentoExterno $compartilhamento, User $usuario): void
    {
        $compartilhamento->update(['revogado_em' => now()]);

        AtaReuniaoEvento::create([
            'empresa_id' => $compartilhamento->empresa_id,
            'ata_reuniao_id' => $compartilhamento->ata_reuniao_id,
            'ator_id' => $usuario->id,
            'tipo_evento' => 'compartilhamento_externo_revogado',
            'entidade_tipo' => AtaReuniaoCompartilhamentoExterno::class,
            'entidade_id' => $compartilhamento->id,
            'created_at' => now(),
        ]);
    }
}
