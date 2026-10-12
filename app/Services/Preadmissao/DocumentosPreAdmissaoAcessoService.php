<?php

namespace App\Services\Preadmissao;

use App\Jobs\JobEnvioCodigoAcessoCartaOferta;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\DocumentosCurriculosAdmissaoEmpresa;
use App\Models\FeedbackCurriculo;
use App\Models\ResultadoIntegrado;
use App\Models\Sistema;
use App\Services\AcessoPublico\CodigoAcessoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Acesso público do candidato ao envio de documentos de pré-admissão:
 * link por `public_id` (ou link geral da empresa) + CPF + código de uso único por e-mail.
 */
class DocumentosPreAdmissaoAcessoService
{
    public const CONTEXTO = 'documentos_pre_adm_acesso';
    public const RECURSO = 'Documentos de Pré-admissão';

    /** @var array<int, object|null> */
    protected array $candidatos = [];

    public function __construct(protected CodigoAcessoService $codigos)
    {
    }

    public function encontrarEmpresa(string $apelido): ?Cliente
    {
        return Cliente::withoutGlobalScopes()
            ->select(['id', 'apelido', 'nome_fantasia', 'razao_social'])
            ->where('apelido', $apelido)
            ->first();
    }

    public function encontrarPorPublicId(int $empresaId, string $publicId): ?ResultadoIntegrado
    {
        return $this->queryPendentes($empresaId)
            ->where('resultado_integrados.public_id', $publicId)
            ->first();
    }

    /**
     * Link geral da empresa (sem public_id): usa a solicitação de documentos mais recente do CPF.
     */
    public function encontrarPorCpf(int $empresaId, string $cpf): ?ResultadoIntegrado
    {
        $digitos = preg_replace('/\D/', '', $cpf) ?? '';

        return $this->queryPendentes($empresaId)
            ->whereIn('c.cpf', [$digitos, Sistema::transformCpfCnpj($digitos)])
            ->orderByDesc('resultado_integrados.id')
            ->first();
    }

    public function encontrarPorId(int $empresaId, int $id): ?ResultadoIntegrado
    {
        return $this->queryPendentes($empresaId)
            ->where('resultado_integrados.id', $id)
            ->first();
    }

    /**
     * Confirma o CPF e envia o código. Com `$publicId` o CPF precisa ser o do link.
     *
     * @return array{registro: ResultadoIntegrado, resposta: array}
     */
    public function solicitarCodigo(Cliente $empresa, string $cpfInformado, ?string $publicId = null): array
    {
        if (!$this->codigos->cpfValido($cpfInformado)) {
            throw new RuntimeException('CPF inválido.');
        }

        if ($publicId) {
            $registro = $this->encontrarPorPublicId((int) $empresa->id, $publicId);
            if (!$registro) {
                throw new RuntimeException('Link de documentos inválido ou não está mais disponível.');
            }
            if (!$this->codigos->cpfConfere($cpfInformado, (string) $this->candidato($registro)?->cpf)) {
                throw new RuntimeException('CPF não confere com o cadastro deste link.');
            }
        } else {
            $registro = $this->encontrarPorCpf((int) $empresa->id, $cpfInformado);
            if (!$registro) {
                throw new RuntimeException('Não encontramos solicitação de documentos para este CPF.');
            }
        }

        return ['registro' => $registro, 'resposta' => $this->enviarCodigo($registro)];
    }

    /**
     * Reenvio para quem já confirmou o CPF (o chamador garante essa confirmação).
     */
    public function reenviarCodigo(ResultadoIntegrado $registro): array
    {
        return $this->enviarCodigo($registro);
    }

    public function validarCodigo(ResultadoIntegrado $registro, string $codigoInformado): void
    {
        $this->codigos->validar($registro, self::CONTEXTO, $codigoInformado);
    }

    /**
     * @return array{codigo_segundos: int, reenvio_segundos: int, bloqueio_segundos: int, tentativas_restantes: int}
     */
    public function statusAcesso(ResultadoIntegrado $registro): array
    {
        return $this->codigos->status($registro, self::CONTEXTO);
    }

    public function emailMascarado(ResultadoIntegrado $registro): string
    {
        return $this->codigos->mascararEmail(trim((string) $this->candidato($registro)?->email));
    }

    public function curriculoId(ResultadoIntegrado $registro): ?int
    {
        $id = $this->candidato($registro)?->id;

        return $id ? (int) $id : null;
    }

    /**
     * Dados mínimos que a tela de documentos usa (somente do próprio candidato).
     */
    public function payloadAutenticado(ResultadoIntegrado $registro, int $empresaId): array
    {
        $curriculo = Curriculo::withoutGlobalScopes()
            ->select(['id', 'nome', 'filiacao_mae', 'filiacao_pai'])
            ->with('Telefones')
            ->find($this->candidato($registro)?->id);

        if (!$curriculo) {
            throw new RuntimeException('Cadastro do candidato não encontrado.');
        }

        return [
            'autenticado' => true,
            'curriculo' => [
                'id' => $curriculo->id,
                'nome' => $curriculo->nome,
                'filiacao_mae' => $curriculo->filiacao_mae,
                'filiacao_pai' => $curriculo->filiacao_pai,
                'telefones' => $curriculo->Telefones,
                'telefonesDelete' => [],
                'docs_curriculo_pre_adm' => $this->documentosDoCandidato($empresaId, (int) $curriculo->id),
            ],
        ];
    }

    /**
     * Link que vai no e-mail/WhatsApp. Usa o `public_id` da solicitação de documentos do candidato;
     * sem solicitação ativa, cai no link geral da empresa (que pede só o CPF).
     */
    public function urlPublica(int $empresaId, int $curriculoId, ?string $apelido = null): string
    {
        $apelido = $apelido ?: (string) Cliente::withoutGlobalScopes()->where('id', $empresaId)->value('apelido');
        $base = rtrim((string) config('app.url'), '/') . '/' . $apelido . '/documentos';

        if (!Schema::hasColumn('resultado_integrados', 'public_id')) {
            return $base;
        }

        $registro = $this->queryPendentes($empresaId)
            ->where('c.id', $curriculoId)
            ->orderByDesc('resultado_integrados.id')
            ->first();

        return $registro ? $base . '/' . $this->garantirPublicId($registro) : $base;
    }

    public function garantirPublicId(ResultadoIntegrado $registro): string
    {
        if (empty($registro->public_id)) {
            $registro->forceFill(['public_id' => (string) Str::uuid()])->saveQuietly();
        }

        return (string) $registro->public_id;
    }

    /**
     * Solicitações de documentos ativas da empresa (aliases: `f` feedback, `c` currículo).
     */
    protected function queryPendentes(int $empresaId): Builder
    {
        return ResultadoIntegrado::query()
            ->select('resultado_integrados.*')
            ->join(FeedbackCurriculo::TABELA . ' as f', 'f.id', '=', 'resultado_integrados.feedback_id')
            ->join('curriculos as c', 'c.id', '=', 'f.curriculo_id')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('f.deleted_at')
            ->whereNull('c.deleted_at')
            ->where('resultado_integrados.documentos_entregue', true);
    }

    protected function enviarCodigo(ResultadoIntegrado $registro): array
    {
        $candidato = $this->candidato($registro);
        $email = trim((string) $candidato?->email);
        if ($email === '') {
            throw new RuntimeException('Não há e-mail cadastrado para envio do código. Procure o RH.');
        }

        $codigo = $this->codigos->gerar($registro, self::CONTEXTO);

        JobEnvioCodigoAcessoCartaOferta::dispatch([
            'nome' => $candidato->nome,
            'email' => $email,
            'empresa_id' => (int) $candidato->empresa_id,
            'codigo' => $codigo,
            'minutos' => CodigoAcessoService::CODIGO_TTL_MINUTOS,
            'recurso' => self::RECURSO,
        ]);

        return [
            'message' => 'Código enviado para o e-mail cadastrado.',
            'email_mascarado' => $this->codigos->mascararEmail($email),
            'expira_em_minutos' => CodigoAcessoService::CODIGO_TTL_MINUTOS,
            'acesso' => $this->statusAcesso($registro),
        ];
    }

    /**
     * Candidato (e empresa) da solicitação: `id`, `nome`, `cpf`, `email`, `empresa_id`.
     */
    protected function candidato(ResultadoIntegrado $registro): ?object
    {
        return $this->candidatos[$registro->getKey()] ??= DB::table(FeedbackCurriculo::TABELA . ' as f')
            ->join('curriculos as c', 'c.id', '=', 'f.curriculo_id')
            ->where('f.id', $registro->feedback_id)
            ->whereNull('f.deleted_at')
            ->whereNull('c.deleted_at')
            ->select(['c.id', 'c.nome', 'c.cpf', 'c.email', 'f.empresa_id'])
            ->first();
    }

    protected function documentosDoCandidato(int $empresaId, int $curriculoId)
    {
        return DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa($empresaId)
            ->map(function ($modelo) use ($curriculoId) {
                // Clona: a coleção vem do cache e não pode carregar anexos de um candidato para outro.
                $doc = clone $modelo;
                $doc->docs_curriculo_anexos = DB::table('documentos_curriculos')
                    ->where('documentos_curriculos.tipo', $doc->tipo)
                    ->where('documentos_curriculos.curriculo_id', $curriculoId)
                    ->join('arquivos', 'arquivos.id', '=', 'documentos_curriculos.arquivo_id')
                    ->get()
                    ->transform(function ($anexo) {
                        $anexo->url = '';
                        $anexo->url_download = '';
                        if (in_array($anexo->disco, Arquivo::LISTAGEM_DISCOS)) {
                            $anexo->url = config('filesystems.disks.' . $anexo->disco . '.urlShow') . "/{$anexo->file}";
                            $anexo->urlDownload = config('filesystems.disks.' . $anexo->disco . '.urlDownload') . "/{$anexo->file}";
                            $anexo->urlThumb = config('filesystems.disks.' . $anexo->disco . '.urlThumb') . "/{$anexo->file}";
                        }

                        return $anexo;
                    });
                $doc->docs_curriculo_anexosDelete = [];
                $doc->qnt_anexos = count($doc->docs_curriculo_anexos);

                return $doc;
            })
            ->values();
    }
}
