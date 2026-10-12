<?php

namespace App\Services\CartaOferta;

use App\Jobs\JobEnvioCodigoAcessoCartaOferta;
use App\Models\CartaOferta;
use App\Models\Cliente;
use App\Services\AcessoPublico\CodigoAcessoService;
use RuntimeException;

class CartaOfertaAcessoPublicoService
{
    public const CONTEXTO = 'carta_oferta_acesso';

    public const CODIGO_TAMANHO = CodigoAcessoService::CODIGO_TAMANHO;
    public const CODIGO_TTL_MINUTOS = CodigoAcessoService::CODIGO_TTL_MINUTOS;
    public const CODIGO_MAX_TENTATIVAS = CodigoAcessoService::CODIGO_MAX_TENTATIVAS;
    public const CODIGO_BLOQUEIO_MINUTOS = CodigoAcessoService::CODIGO_BLOQUEIO_MINUTOS;
    public const CODIGO_REENVIO_COOLDOWN_SEGUNDOS = CodigoAcessoService::CODIGO_REENVIO_COOLDOWN_SEGUNDOS;

    public function __construct(protected CodigoAcessoService $codigos)
    {
    }

    public function encontrarPorPublicId(string $apelido, string $publicId): ?CartaOferta
    {
        $empresa = Cliente::withoutGlobalScopes()
            ->select(['id', 'apelido', 'nome_fantasia', 'razao_social'])
            ->where('apelido', $apelido)
            ->first();

        if (!$empresa) {
            return null;
        }

        $carta = CartaOferta::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id)
            ->where(function ($q) use ($publicId) {
                $q->where('public_id', $publicId)
                    ->orWhere('token', $publicId);
            })
            ->first();

        if ($carta) {
            $carta->setRelation('empresa', $empresa);
        }

        return $carta;
    }

    public function payloadPublicoMinimo(CartaOferta $carta): array
    {
        $empresa = $this->resolverEmpresa($carta);

        return [
            'public_id' => $carta->public_id ?: $carta->token,
            'status' => $carta->status,
            'empresa' => [
                'apelido' => $empresa->apelido ?? null,
                'nome' => $empresa->nome_fantasia ?? $empresa->razao_social ?? null,
            ],
            'autenticado' => false,
        ];
    }

    /**
     * Payload para quem já confirmou o CPF nesta sessão e aguarda digitar o código.
     */
    public function payloadDesafioPendente(CartaOferta $carta): array
    {
        $carta->loadMissing([
            'Curriculo' => function ($q) {
                $q->withoutGlobalScopes()->select(['id', 'nome', 'cpf', 'email']);
            },
        ]);

        return $this->payloadPublicoMinimo($carta) + [
            'desafio_pendente' => true,
            'email_mascarado' => $this->mascararEmail((string) ($carta->Curriculo->email ?? '')),
            'acesso' => $this->statusAcesso($carta),
        ];
    }

    public function payloadAutenticado(CartaOferta $carta): array
    {
        $carta->loadMissing([
            'Curriculo' => function ($q) {
                $q->withoutGlobalScopes()->select(['id', 'nome', 'email']);
            },
        ]);

        $cargo = 'Não informado';
        if ($carta->vagas_abertas_id) {
            try {
                $carta->loadMissing([
                    'vagaAberta' => function ($q) {
                        $q->withoutGlobalScopes()
                            ->select(['id', 'vaga_id', 'titulo'])
                            ->with(['Vaga' => function ($q) {
                                $q->withoutGlobalScopes()->select(['id', 'nome']);
                            }]);
                    },
                ]);
                $cargo = $carta->vagaAberta?->Vaga?->nome
                    ?? $carta->vagaAberta?->titulo
                    ?? 'Não informado';
            } catch (\Throwable $e) {
                $cargo = 'Não informado';
            }
        }

        $email = (string) ($carta->Curriculo->email ?? '');
        $empresa = $this->resolverEmpresa($carta);

        return [
            'public_id' => $carta->public_id ?: $carta->token,
            'status' => $carta->status,
            'curriculo_id' => $carta->curriculo_id,
            'candidato' => [
                'nome' => $carta->Curriculo->nome ?? 'Não informado',
                'email_mascarado' => $this->mascararEmail($email),
            ],
            'cargo' => $cargo,
            'empresa' => [
                'apelido' => $empresa->apelido ?? null,
                'nome' => $empresa->nome_fantasia ?? $empresa->razao_social ?? null,
            ],
            'autenticado' => true,
        ] + $this->payloadAceite($carta);
    }

    /**
     * Empresas sem assinatura digital: o candidato vê a proposta e aceita/recusa eletronicamente.
     */
    protected function payloadAceite(CartaOferta $carta): array
    {
        $aceite = \App\Models\CartaOfertaAceite::query()->where('carta_oferta_id', $carta->id)->first();
        $habilitado = app(CartaOfertaAceiteService::class)->habilitado((int) $carta->empresa_id);

        return [
            'aceite_eletronico' => $habilitado || $aceite !== null,
            'textos_aceite' => [
                'aceitar' => CartaOfertaAceiteService::TEXTO_CONSENTIMENTO,
                'recusar' => CartaOfertaAceiteService::TEXTO_RECUSA,
            ],
            'aceite' => $aceite ? [
                'decisao' => $aceite->decisao,
                'data' => $aceite->data_decisao_br,
                'codigo_verificacao' => $aceite->codigo_verificacao,
                'comprovante_pronto' => (bool) $aceite->comprovante_arquivo_id,
                'url_verificacao' => app(CartaOfertaAceiteService::class)->urlVerificacao($aceite, $this->resolverEmpresa($carta)?->apelido),
            ] : null,
        ];
    }

    protected function resolverEmpresa(CartaOferta $carta): ?Cliente
    {
        if ($carta->relationLoaded('empresa') && $carta->empresa) {
            return $carta->empresa;
        }

        return Cliente::withoutGlobalScopes()
            ->select(['id', 'apelido', 'nome_fantasia', 'razao_social'])
            ->find($carta->empresa_id);
    }

    public function solicitarCodigo(CartaOferta $carta, string $cpfInformado): array
    {
        $this->conferirCpf($carta, $cpfInformado);

        return $this->enviarCodigo($carta);
    }

    /**
     * "Já recebi o código": confere o CPF e devolve os prazos do código atual, sem enviar outro.
     */
    public function confirmarCpf(CartaOferta $carta, string $cpfInformado): array
    {
        $this->conferirCpf($carta, $cpfInformado);

        return [
            'message' => 'CPF confirmado.',
            'email_mascarado' => $this->mascararEmail(trim((string) $carta->Curriculo->email)),
            'acesso' => $this->statusAcesso($carta),
        ];
    }

    protected function conferirCpf(CartaOferta $carta, string $cpfInformado): void
    {
        if (!$this->codigos->cpfValido($cpfInformado)) {
            throw new RuntimeException('CPF inválido.');
        }

        $this->carregarCurriculo($carta);

        if (!$this->codigos->cpfConfere($cpfInformado, (string) $carta->Curriculo->cpf)) {
            throw new RuntimeException('CPF não confere com o cadastro desta carta oferta.');
        }
    }

    /**
     * Reenvio para quem já confirmou o CPF (o chamador garante essa confirmação).
     */
    public function reenviarCodigo(CartaOferta $carta): array
    {
        $this->carregarCurriculo($carta);

        return $this->enviarCodigo($carta);
    }

    public function validarCodigo(CartaOferta $carta, string $codigoInformado): array
    {
        $this->codigos->validar($carta, self::CONTEXTO, $codigoInformado);

        return $this->payloadAutenticado($carta);
    }

    /**
     * @return array{codigo_segundos: int, reenvio_segundos: int, bloqueio_segundos: int, tentativas_restantes: int}
     */
    public function statusAcesso(CartaOferta $carta): array
    {
        return $this->codigos->status($carta, self::CONTEXTO);
    }

    protected function carregarCurriculo(CartaOferta $carta): void
    {
        $carta->loadMissing([
            'Curriculo' => function ($q) {
                $q->withoutGlobalScopes()->select(['id', 'nome', 'cpf', 'email']);
            },
        ]);

        if (!$carta->Curriculo) {
            throw new RuntimeException('Carta oferta inválida.');
        }
    }

    protected function enviarCodigo(CartaOferta $carta): array
    {
        $email = trim((string) $carta->Curriculo->email);
        if ($email === '') {
            throw new RuntimeException('Não há e-mail cadastrado para envio do código.');
        }

        $codigo = $this->codigos->gerar($carta, self::CONTEXTO);

        JobEnvioCodigoAcessoCartaOferta::dispatch([
            'nome' => $carta->Curriculo->nome,
            'email' => $email,
            'empresa_id' => $carta->empresa_id,
            'codigo' => $codigo,
            'minutos' => self::CODIGO_TTL_MINUTOS,
        ]);

        return [
            'message' => 'Código enviado para o e-mail cadastrado.',
            'email_mascarado' => $this->mascararEmail($email),
            'expira_em_minutos' => self::CODIGO_TTL_MINUTOS,
            'acesso' => $this->statusAcesso($carta),
        ];
    }

    public function cpfConfere(string $informado, string $cadastrado): bool
    {
        return $this->codigos->cpfConfere($informado, $cadastrado);
    }

    public function mascararEmail(string $email): string
    {
        return $this->codigos->mascararEmail($email);
    }

    public function sessionKey(string $publicId): string
    {
        return 'carta_oferta_auth_' . $publicId;
    }

    /**
     * Marca que o CPF já foi confirmado nesta sessão (permite reenviar sem redigitar).
     */
    public function sessionKeyCpfConfirmado(string $publicId): string
    {
        return 'carta_oferta_cpf_ok_' . $publicId;
    }
}
