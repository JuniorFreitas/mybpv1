<?php

namespace App\Jobs;

use App\Mail\ComprovanteAceiteCartaOfertaMail;
use App\Models\CartaOferta;
use App\Models\CartaOfertaAceite;
use App\Models\Cliente;
use App\Services\AssinaturaDigital\PdfMarcaAssinaturaService;
use App\Services\CartaOferta\CartaOfertaAceiteService;
use App\Services\CartaOferta\CartaOfertaPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Depois do aceite/recusa: gera o comprovante (carta + página de evidências com QR code de
 * verificação) e envia ao candidato.
 */
class JobComprovanteAceiteCartaOferta implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $aceiteId)
    {
    }

    public function handle(
        CartaOfertaAceiteService $aceites,
        CartaOfertaPdfService $pdfs,
        PdfMarcaAssinaturaService $marca,
    ): void {
        $aceite = CartaOfertaAceite::query()->find($this->aceiteId);
        if (!$aceite) {
            return;
        }

        $empresa = Cliente::withoutGlobalScopes()
            ->select(['id', 'apelido', 'nome_fantasia', 'razao_social'])
            ->find($aceite->empresa_id);
        $urlVerificacao = $aceites->urlVerificacao($aceite, $empresa?->apelido);

        // Nova tentativa do job reaproveita o comprovante já gerado.
        if (!$aceite->comprovante_arquivo_id) {
            $carta = CartaOferta::withoutGlobalScopes()->find($aceite->carta_oferta_id);
            $proposta = $carta ? $pdfs->propostaCongelada($carta) : null;
            $conteudo = $proposta ? $pdfs->conteudo($proposta) : null;
            if (!$carta || !$conteudo) {
                Log::warning('JobComprovanteAceiteCartaOferta: proposta indisponível', ['aceite_id' => $aceite->id]);
                return;
            }

            $comprovante = $marca->adicionarMarcaAgua(
                $conteudo,
                null,
                [$this->blocoDoCandidato($aceite, $urlVerificacao)],
                [
                    'titulo' => $aceite->aceito() ? 'Comprovante de aceite' : 'Comprovante de recusa',
                    'documento_id' => $aceite->carta_oferta_id,
                    'identificador' => $aceite->codigo_verificacao,
                    'hash_sha256_pdf' => (string) $aceite->proposta_hash,
                    'url_verificacao' => $urlVerificacao,
                    'data_ultima_atualizacao' => $aceite->data_decisao_br,
                    'data_geracao_ptbr' => now('America/Sao_Paulo')->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y'),
                    'eventos' => $aceites->historico($aceite),
                ]
            );

            if ($comprovante === null) {
                // Falha na montagem do PDF: tenta de novo pela fila.
                throw new \RuntimeException('Não foi possível gerar o comprovante do aceite ' . $aceite->id);
            }

            $arquivo = $pdfs->guardar($carta, $comprovante, 'comprovante', 'Carta Oferta - comprovante.pdf');
            $aceite->update(['comprovante_arquivo_id' => $arquivo->id]);
        }

        if (trim((string) $aceite->email) === '') {
            return;
        }

        Mail::send(new ComprovanteAceiteCartaOfertaMail([
            'nome' => $aceite->nome,
            'email' => $aceite->email,
            'empresa_id' => (int) $aceite->empresa_id,
            'aceito' => $aceite->aceito(),
            'data_hora' => $aceite->data_decisao_br,
            'codigo_verificacao' => $aceite->codigo_verificacao,
            'hash_evidencia' => $aceite->hash_evidencia,
            'url_verificacao' => $urlVerificacao,
            'comprovante_arquivo_id' => $aceite->comprovante_arquivo_id,
        ]));
    }

    protected function blocoDoCandidato(CartaOfertaAceite $aceite, string $urlVerificacao): array
    {
        $data = \Carbon\Carbon::parse($aceite->getRawOriginal('data_decisao_utc'), 'UTC')->setTimezone('America/Sao_Paulo');
        // O comprovante circula (e-mail, RH, impressão): o CPF sai mascarado, como na página de
        // verificação. O número completo fica no registro do aceite e entra no hash das evidências.
        $cpf = (string) app(\App\Services\AcessoPublico\AuditoriaAcessoService::class)->mascararCpf($aceite->cpf);

        $geo = is_array($aceite->geolocalizacao) ? $aceite->geolocalizacao : [];
        $local = implode(', ', array_filter([$geo['city'] ?? null, $geo['regionName'] ?? null, $geo['country'] ?? null]));

        return [
            'id' => $aceite->id,
            'rotulo' => $aceite->aceito() ? 'Aceito eletronicamente por: ' : 'Recusado eletronicamente por: ',
            'nome' => $aceite->nome ?: '—',
            'email' => $aceite->email ?: '—',
            'cpf' => $cpf ?: '—',
            'data_formatada' => $data->format('d/m/Y H:i'),
            'hash_evidencia' => $aceite->hash_evidencia,
            'timestamp_brasilia_iso' => $data->format('Y-m-d\TH:i:sP'),
            'data_local_br' => $data->format('Y.m.d H:i:s') . " -03'00' (Brasilia)",
            'ip' => $aceite->ip ?: '—',
            'user_agent' => $aceite->user_agent ?: '—',
            'local_assinatura' => $local,
            'qr_payload' => $urlVerificacao,
        ];
    }
}
