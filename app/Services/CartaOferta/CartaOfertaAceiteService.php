<?php

namespace App\Services\CartaOferta;

use App\Jobs\JobComprovanteAceiteCartaOferta;
use App\Models\AcessoPublicoAuditoria;
use App\Models\CartaOferta;
use App\Models\CartaOfertaAceite;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\Sistema;
use App\Services\AcessoPublico\AuditoriaAcessoService;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use RuntimeException;

/**
 * Aceite/recusa eletrônico da carta oferta para empresas sem o módulo de assinatura digital.
 * Registra as mesmas evidências da assinatura (identificação, data/hora, IP, navegador, local,
 * consentimento e hash) e gera código de verificação para o QR code do comprovante.
 */
class CartaOfertaAceiteService
{
    public const TEXTO_CONSENTIMENTO = 'Li a Carta Oferta apresentada e declaro, de forma livre e inequívoca, que ACEITO a proposta nos termos do documento exibido, reconhecendo este meio eletrônico como válido para comprovar minha manifestação de vontade (MP nº 2.200-2/2001, art. 10, § 2º, e Lei nº 14.063/2020).';

    public const TEXTO_RECUSA = 'Li a Carta Oferta apresentada e declaro que NÃO ACEITO a proposta, reconhecendo este meio eletrônico como válido para comprovar minha manifestação de vontade.';

    public function __construct(
        protected CartaOfertaPdfService $pdf,
        protected AuditoriaAcessoService $auditoria,
        protected AssinaturaDigitalService $assinatura,
    ) {
    }

    /**
     * O aceite eletrônico vale para quem não usa a assinatura digital (que tem fluxo próprio).
     */
    public function habilitado(int $empresaId): bool
    {
        return !Sistema::assinaturaDigitalHabilitada($empresaId);
    }

    public function aceitar(CartaOferta $carta, Request $request): CartaOfertaAceite
    {
        return $this->registrar($carta, CartaOfertaAceite::DECISAO_ACEITO, $request, null);
    }

    public function recusar(CartaOferta $carta, Request $request, ?string $motivo): CartaOfertaAceite
    {
        return $this->registrar($carta, CartaOfertaAceite::DECISAO_RECUSADO, $request, $motivo);
    }

    protected function registrar(CartaOferta $carta, string $decisao, Request $request, ?string $motivo): CartaOfertaAceite
    {
        if (!$this->habilitado((int) $carta->empresa_id)) {
            throw new RuntimeException('Esta carta oferta deve ser assinada pelo link de assinatura digital.');
        }
        if ($carta->status !== CartaOferta::STATUS_PENDENTE_ANEXO) {
            throw new RuntimeException('Esta carta oferta não está mais disponível para resposta.');
        }
        if (CartaOfertaAceite::query()->where('carta_oferta_id', $carta->id)->exists()) {
            throw new RuntimeException('Esta carta oferta já foi respondida.');
        }

        $candidato = Curriculo::withoutGlobalScopes()
            ->select(['id', 'nome', 'email', 'cpf'])
            ->find($carta->curriculo_id);
        if (!$candidato) {
            throw new RuntimeException('Carta oferta inválida.');
        }

        // Garante que a decisão se refere a um PDF congelado (o mesmo que foi exibido).
        $proposta = $this->pdf->propostaCongelada($carta);
        $aceito = $decisao === CartaOfertaAceite::DECISAO_ACEITO;
        $ip = $request->ip();
        $userAgent = Str::limit((string) $request->userAgent(), 500, '');
        $dataUtc = now('UTC')->startOfSecond();
        $local = $this->assinatura->localPorIp($ip);
        $texto = $aceito ? self::TEXTO_CONSENTIMENTO : self::TEXTO_RECUSA;
        $motivo = $aceito ? null : (trim((string) $motivo) ?: null);

        $evidencia = [
            'carta_oferta_id' => $carta->id,
            'decisao' => $decisao,
            'motivo' => $motivo,
            'nome' => $candidato->nome,
            'email' => $candidato->email,
            'cpf' => $candidato->cpf,
            'consentimento' => true,
            'consentimento_texto' => $texto,
            'data_utc' => $dataUtc->toIso8601String(),
            'ip' => $ip,
            'user_agent' => $userAgent,
            'geolocalizacao' => $local['dados'],
            'proposta_hash' => $carta->proposta_hash,
        ];

        $aceite = DB::transaction(function () use ($carta, $decisao, $aceito, $motivo, $candidato, $ip, $userAgent, $dataUtc, $local, $texto, $proposta, $evidencia, $request) {
            $aceite = CartaOfertaAceite::create([
                'carta_oferta_id' => $carta->id,
                'empresa_id' => $carta->empresa_id,
                'curriculo_id' => $carta->curriculo_id,
                'decisao' => $decisao,
                'motivo' => $motivo,
                'nome' => $candidato->nome,
                'email' => $candidato->email,
                'cpf' => $candidato->cpf,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'geolocalizacao' => $local['dados'],
                'consentimento' => true,
                'consentimento_texto' => $texto,
                'data_decisao_utc' => $dataUtc,
                'proposta_arquivo_id' => $proposta->id,
                'proposta_hash' => $carta->proposta_hash,
                'hash_evidencia' => hash('sha256', json_encode($evidencia)),
                'codigo_verificacao' => (string) Str::uuid(),
            ]);

            $logs = is_array($carta->logs) ? $carta->logs : (json_decode((string) $carta->getRawOriginal('logs'), true) ?: []);
            array_unshift($logs, [
                'data' => (new DataHora())->dataHoraInsert(),
                'mensagem' => $aceito
                    ? 'Candidato aceitou a carta oferta eletronicamente'
                    : 'Candidato recusou a carta oferta eletronicamente' . ($motivo ? ': ' . $motivo : ''),
                'status' => $aceito ? 'Aceito pelo candidato' : CartaOferta::STATUS_RECUSADO_CANDIDATO,
                'usuario' => $candidato->nome,
            ]);

            // saveQuietly: ato do candidato (sem usuário logado) não deve entrar na auditoria de usuários.
            $carta->forceFill([
                'logs' => $logs,
                'status' => $aceito ? $carta->status : CartaOferta::STATUS_RECUSADO_CANDIDATO,
            ])->saveQuietly();

            $this->auditoria->registrar(
                CartaOfertaAcessoPublicoService::CONTEXTO,
                (int) $carta->empresa_id,
                $aceito ? AuditoriaAcessoService::EVENTO_CARTA_ACEITA : AuditoriaAcessoService::EVENTO_CARTA_RECUSADA,
                $request,
                [
                    'registro_id' => $carta->id,
                    'curriculo_id' => $carta->curriculo_id,
                    'cpf' => $candidato->cpf,
                    'protocolo' => $aceite->codigo_verificacao,
                    'dados' => [
                        'hash_evidencia' => $aceite->hash_evidencia,
                        'proposta_hash' => $carta->proposta_hash,
                        'motivo' => $motivo,
                    ],
                ]
            );

            return $aceite;
        });

        JobComprovanteAceiteCartaOferta::dispatch((int) $aceite->id);

        return $aceite;
    }

    /**
     * Confere se as evidências guardadas ainda produzem o hash registrado.
     */
    public function integro(CartaOfertaAceite $aceite): bool
    {
        $evidencia = [
            'carta_oferta_id' => $aceite->carta_oferta_id,
            'decisao' => $aceite->decisao,
            'motivo' => $aceite->motivo,
            'nome' => $aceite->nome,
            'email' => $aceite->email,
            'cpf' => $aceite->cpf,
            'consentimento' => true,
            'consentimento_texto' => $aceite->consentimento_texto,
            'data_utc' => \Carbon\Carbon::parse($aceite->getRawOriginal('data_decisao_utc'), 'UTC')->toIso8601String(),
            'ip' => $aceite->ip,
            'user_agent' => $aceite->user_agent,
            'geolocalizacao' => $aceite->geolocalizacao ?? [],
            'proposta_hash' => $aceite->proposta_hash,
        ];

        return hash_equals((string) $aceite->hash_evidencia, hash('sha256', json_encode($evidencia)));
    }

    public function urlVerificacao(CartaOfertaAceite $aceite, ?string $apelido = null): string
    {
        $apelido = $apelido ?: (string) Cliente::withoutGlobalScopes()->where('id', $aceite->empresa_id)->value('apelido');

        return rtrim((string) config('app.url'), '/') . '/' . $apelido . '/carta-oferta/verificacao'
            . '?c=' . $aceite->codigo_verificacao . '&h=' . $aceite->hash_evidencia;
    }

    /**
     * Linha do tempo da carta (acessos, visualização e decisão) a partir da trilha de auditoria.
     *
     * @return array<int, array{data_formatada: string, hora: string, descricao: string}>
     */
    public function historico(CartaOfertaAceite $aceite, bool $ipCompleto = true): array
    {
        $descricoes = [
            AuditoriaAcessoService::EVENTO_CODIGO_SOLICITADO => 'CPF conferido e código de acesso enviado ao e-mail',
            AuditoriaAcessoService::EVENTO_CODIGO_REENVIADO => 'Código de acesso reenviado ao e-mail',
            AuditoriaAcessoService::EVENTO_CPF_CONFIRMADO => 'CPF conferido',
            AuditoriaAcessoService::EVENTO_CODIGO_VALIDADO => 'Código de acesso validado (identidade confirmada)',
            AuditoriaAcessoService::EVENTO_PROPOSTA_VISUALIZADA => 'Carta oferta visualizada',
            AuditoriaAcessoService::EVENTO_CARTA_ACEITA => 'Carta oferta ACEITA eletronicamente',
            AuditoriaAcessoService::EVENTO_CARTA_RECUSADA => 'Carta oferta RECUSADA eletronicamente',
            AuditoriaAcessoService::EVENTO_CARTA_ENVIADA => 'Carta oferta assinada anexada pelo candidato',
        ];

        return AcessoPublicoAuditoria::query()
            ->where('contexto', CartaOfertaAcessoPublicoService::CONTEXTO)
            ->where('registro_id', $aceite->carta_oferta_id)
            ->whereIn('evento', array_keys($descricoes))
            ->orderBy('id')
            ->get()
            // Visualizações repetidas poluem o histórico: fica só a primeira.
            ->unique(fn ($e) => $e->evento === AuditoriaAcessoService::EVENTO_PROPOSTA_VISUALIZADA ? $e->evento : $e->id)
            ->map(function ($evento) use ($descricoes, $ipCompleto) {
                $data = $evento->created_at->copy()->setTimezone('America/Sao_Paulo');
                $ip = (string) $evento->ip;
                if (!$ipCompleto && $ip !== '') {
                    $ip = preg_replace('/\.\d+\.\d+$/', '.***.***', $ip);
                }

                return [
                    'data_formatada' => $data->format('d/m/Y'),
                    'hora' => $data->format('H:i:s'),
                    'descricao' => $descricoes[$evento->evento] . ($ip !== '' ? ' — IP ' . $ip : ''),
                ];
            })
            ->values()
            ->all();
    }
}
