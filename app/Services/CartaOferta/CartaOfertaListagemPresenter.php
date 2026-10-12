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
            'fluxo_candidato' => $this->montarFluxoCandidato($item),
            'fluxo_rh' => $this->montarFluxoRh($item),
            'link_publico' => ($apelido && $publicId)
                ? "{$baseUrl}/{$apelido}/carta-oferta/{$publicId}"
                : null,
            'documento_para_assinatura' => $docsByCartaId[$item->id] ?? null,
            'anexo' => $anexo,
        ];
    }

    /**
     * @return array{status: string, texto: string, nome: ?string, data: ?string}
     */
    private function montarFluxoCandidato(CartaOferta $item): array
    {
        $statusCarta = (string) $item->status;
        $candidatoNome = trim((string) ($item->curriculo?->nome ?? '')) ?: null;

        if ($statusCarta === CartaOferta::STATUS_PENDENTE_ANEXO) {
            return $this->passoFluxo('aguardando', 'Aguardando');
        }

        $log = $this->buscarLogPorStatus($item, [
            'Enviado pelo candidato',
            'Assinado digitalmente',
            'Recusado pelo candidato',
        ]);

        if ($statusCarta === CartaOferta::STATUS_EXPIRADO && !$log) {
            return $this->passoFluxo('cancelado', 'Expirado');
        }

        $recusado = ($log['status'] ?? '') === 'Recusado pelo candidato';

        return $this->passoFluxo(
            $recusado ? 'reprovado' : 'aprovado',
            $recusado ? 'Recusado' : 'Aceito',
            $this->nomeUsuarioLog($log, $candidatoNome),
            $this->formatarDataLog($log)
        );
    }

    /**
     * @return array{status: string, texto: string, nome: ?string, data: ?string}
     */
    private function montarFluxoRh(CartaOferta $item): array
    {
        $statusCarta = (string) $item->status;

        if ($statusCarta === CartaOferta::STATUS_PENDENTE_ANEXO) {
            return $this->passoFluxo('pendente', 'Pendente');
        }

        if ($statusCarta === CartaOferta::STATUS_AGUARDANDO_RH) {
            return $this->passoFluxo('aguardando', 'Aguardando');
        }

        if ($statusCarta === CartaOferta::STATUS_EXPIRADO) {
            return $this->passoFluxo('cancelado', 'Expirado');
        }

        $log = $this->buscarLogPorStatus($item, [
            CartaOferta::STATUS_ACEITO_RH,
            CartaOferta::STATUS_RECUSADO_RH,
        ]);

        $recusado = $statusCarta === CartaOferta::STATUS_RECUSADO_RH
            || ($log['status'] ?? '') === CartaOferta::STATUS_RECUSADO_RH;

        return $this->passoFluxo(
            $recusado ? 'reprovado' : 'aprovado',
            $recusado ? 'Recusado' : 'Aceito',
            $this->nomeUsuarioLog($log, null),
            $this->formatarDataLog($log) ?: (
                $item->updated_at
                    ? (new DataHora($item->updated_at))->dataHoraCompleta()
                    : null
            )
        );
    }

    /**
     * @return array{status: string, texto: string, nome: ?string, data: ?string}
     */
    private function passoFluxo(string $status, string $texto, ?string $nome = null, ?string $data = null): array
    {
        return [
            'status' => $status,
            'texto' => $texto,
            'nome' => $nome,
            'data' => $data,
        ];
    }

    /**
     * @param  array<int, string>  $statusAlvos
     * @return array<string, mixed>|null
     */
    private function buscarLogPorStatus(CartaOferta $item, array $statusAlvos): ?array
    {
        $logs = is_array($item->logs) ? $item->logs : [];
        for ($i = count($logs) - 1; $i >= 0; $i--) {
            $log = $logs[$i];
            if (!is_array($log)) {
                continue;
            }
            $status = (string) ($log['status'] ?? '');
            if (in_array($status, $statusAlvos, true)) {
                return $log;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $log
     */
    private function nomeUsuarioLog(?array $log, ?string $fallback): ?string
    {
        $usuario = trim((string) ($log['usuario'] ?? ''));
        if (
            $usuario === ''
            || str_starts_with($usuario, 'user:')
            || str_starts_with($usuario, 'Sistema')
        ) {
            $fallback = trim((string) $fallback);

            return $fallback !== '' ? $fallback : null;
        }

        return $usuario;
    }

    /**
     * @param  array<string, mixed>|null  $log
     */
    private function formatarDataLog(?array $log): ?string
    {
        $raw = $log['data'] ?? null;
        if (!$raw) {
            return null;
        }

        try {
            return (new DataHora($raw))->dataHoraCompleta();
        } catch (\Throwable) {
            return is_string($raw) ? $raw : null;
        }
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
