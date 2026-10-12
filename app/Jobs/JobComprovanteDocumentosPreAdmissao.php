<?php

namespace App\Jobs;

use App\Mail\ComprovanteDocumentosPreAdmissaoMail;
use App\Models\AcessoPublicoAuditoria;
use App\Models\DocumentosCurriculosAdmissaoEmpresa;
use App\Services\AcessoPublico\AuditoriaAcessoService;
use App\Services\Preadmissao\DocumentosPreAdmissaoAcessoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * Após o candidato salvar os documentos: calcula o SHA-256 de cada arquivo, grava a evidência
 * e envia o comprovante por e-mail.
 */
class JobComprovanteDocumentosPreAdmissao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param array{empresa_id: int, registro_id: int, curriculo_id: int, protocolo: string,
     *              ip: ?string, user_agent: ?string, termo_versao: string} $envio
     */
    public function __construct(public array $envio)
    {
    }

    public function handle(AuditoriaAcessoService $auditoria): void
    {
        $candidato = DB::table('curriculos')
            ->where('id', $this->envio['curriculo_id'])
            ->whereNull('deleted_at')
            ->first(['id', 'nome', 'cpf', 'email']);

        if (!$candidato) {
            return;
        }

        // Nova tentativa do job reaproveita a evidência já gravada (não recalcula nem duplica).
        $evidencia = AcessoPublicoAuditoria::query()
            ->where('protocolo', $this->envio['protocolo'])
            ->where('evento', AuditoriaAcessoService::EVENTO_COMPROVANTE_EMITIDO)
            ->first();

        if (!$evidencia) {
            $evidencia = $auditoria->registrar(
                DocumentosPreAdmissaoAcessoService::CONTEXTO,
                (int) $this->envio['empresa_id'],
                AuditoriaAcessoService::EVENTO_COMPROVANTE_EMITIDO,
                null,
                [
                    'registro_id' => $this->envio['registro_id'],
                    'curriculo_id' => $candidato->id,
                    'protocolo' => $this->envio['protocolo'],
                    'cpf' => $candidato->cpf,
                    'ip' => $this->envio['ip'] ?? null,
                    'user_agent' => $this->envio['user_agent'] ?? null,
                    'dados' => [
                        'termo_versao' => $this->envio['termo_versao'],
                        'arquivos' => $this->arquivosComHash((int) $candidato->id),
                    ],
                ]
            );
        }

        $email = trim((string) $candidato->email);
        if ($email === '') {
            return;
        }

        Mail::send(new ComprovanteDocumentosPreAdmissaoMail([
            'nome' => $candidato->nome,
            'email' => $email,
            'empresa_id' => (int) $this->envio['empresa_id'],
            'protocolo' => $this->envio['protocolo'],
            'data_hora' => $evidencia->created_at_br,
            'ip' => $evidencia->ip,
            'cpf_mascarado' => $evidencia->cpf_mascarado,
            'termo_versao' => $this->envio['termo_versao'],
            'arquivos' => $evidencia->dados['arquivos'] ?? [],
        ]));
    }

    /**
     * @return array<int, array{arquivo_id: int, documento: string, nome: string, bytes: int, sha256: ?string}>
     */
    protected function arquivosComHash(int $curriculoId): array
    {
        $rotulos = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa((int) $this->envio['empresa_id'])
            ->pluck('label', 'tipo');

        return DB::table('documentos_curriculos as dc')
            ->join('arquivos as a', 'a.id', '=', 'dc.arquivo_id')
            ->where('dc.curriculo_id', $curriculoId)
            ->orderBy('dc.tipo')
            ->orderBy('a.id')
            ->get(['a.id', 'a.nome', 'a.file', 'a.disco', 'a.bytes', 'dc.tipo'])
            ->map(fn ($arquivo) => [
                'arquivo_id' => (int) $arquivo->id,
                'documento' => (string) ($rotulos[$arquivo->tipo] ?? $arquivo->tipo ?? 'Não informado'),
                'nome' => (string) ($arquivo->nome ?: 'Não informado'),
                'bytes' => (int) $arquivo->bytes,
                'sha256' => $this->sha256((string) $arquivo->disco, (string) $arquivo->file),
            ])
            ->all();
    }

    protected function sha256(string $disco, string $arquivo): ?string
    {
        try {
            $stream = Storage::disk($disco)->readStream($arquivo);
            if (!is_resource($stream)) {
                return null;
            }
            $contexto = hash_init('sha256');
            hash_update_stream($contexto, $stream);
            fclose($stream);

            return hash_final($contexto);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
