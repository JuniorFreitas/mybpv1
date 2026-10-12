<?php

namespace App\Services\CartaOferta;

use App\Models\CartaOferta;
use MasterTag\DataHora;

/**
 * Monta payload enxuto da listagem operacional (sem hidratar relações no JSON).
 */
class CartaOfertaListagemPresenter
{
    public function apresentar(CartaOferta $item, array $docsByCartaId, string $apelido, string $baseUrl): array
    {
        $curriculo = $item->curriculo;
        $publicId = $item->public_id ?: $item->token;
        $status = (string) $item->status;
        $precisaCpfAssinatura = $status === CartaOferta::STATUS_PENDENTE_ANEXO;

        $anexo = null;
        if ($item->anexo) {
            $anexo = [
                'url' => $item->anexo->url,
                'urlDownload' => $item->anexo->urlDownload,
            ];
        }

        return [
            'id' => (int) $item->id,
            'status' => $status,
            'local' => $item->local,
            'token' => $item->token,
            'candidato_nome' => $curriculo?->nome,
            'cpf_mascarado' => $this->mascararCpf($curriculo?->cpf),
            'cpf' => $precisaCpfAssinatura ? ($curriculo?->cpf ?: null) : null,
            'contato_email' => $curriculo?->email,
            'contato_telefone' => $curriculo?->TelPrincipal?->numero,
            'cargo_nome' => $item->vagaAberta?->Vaga?->nome
                ?? $item->vagaAberta?->titulo
                ?? null,
            'setor' => $item->setor,
            'salario' => $item->salario,
            'projeto_nome' => $this->resolverProjetoNome($item),
            'data_inicio_exibicao' => $item->data_inicio
                ? (new DataHora($item->data_inicio))->dataCompleta()
                : null,
            'atualizacao_exibicao' => $item->updated_at
                ? (new DataHora($item->updated_at))->dataHoraCompleta()
                : null,
            'criador_nome' => $this->resolverCriadorNome($item),
            'envio_em' => $item->created_at
                ? (new DataHora($item->created_at))->dataHoraCompleta()
                : null,
            'link_publico' => ($apelido && $publicId)
                ? "{$baseUrl}/{$apelido}/carta-oferta/{$publicId}"
                : null,
            'documento_para_assinatura' => $docsByCartaId[$item->id] ?? null,
            'anexo' => $anexo,
        ];
    }

    private function resolverProjetoNome(CartaOferta $item): ?string
    {
        $nome = trim((string) ($item->vagaProjeto?->Projeto?->nome ?? ''));
        if ($nome !== '') {
            return $nome;
        }

        $projetos = $item->vagaAberta?->Projetos;
        if (!$projetos || $projetos->isEmpty()) {
            return null;
        }

        $nomes = $projetos
            ->map(fn ($vp) => trim((string) ($vp->Projeto?->nome ?? '')))
            ->filter()
            ->unique()
            ->values();

        return $nomes->isEmpty() ? null : $nomes->implode(', ');
    }

    private function mascararCpf(?string $cpf): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $cpf);
        if (strlen($digitos) < 11) {
            return null;
        }

        return substr($digitos, 0, 3) . '.***.***-' . substr($digitos, -2);
    }

    private function resolverCriadorNome(CartaOferta $item): ?string
    {
        $nome = trim((string) ($item->user?->nome ?? ''));
        if ($nome !== '') {
            return $nome;
        }

        $logs = $item->logs;
        if (!is_array($logs) || $logs === []) {
            return null;
        }

        foreach ($logs as $log) {
            if (!is_array($log)) {
                continue;
            }
            $usuario = trim((string) ($log['usuario'] ?? ''));
            if ($usuario !== '' && !str_starts_with($usuario, 'user:')) {
                return $usuario;
            }
        }

        return null;
    }
}
