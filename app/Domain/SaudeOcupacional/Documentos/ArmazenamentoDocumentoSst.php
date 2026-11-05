<?php

namespace App\Domain\SaudeOcupacional\Documentos;

use App\Models\Arquivo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Grava arquivos de Documentos SST no disco privado (sem URL pública).
 * Caminho por empresa e nome aleatório: o nome original fica só no banco.
 */
class ArmazenamentoDocumentoSst
{
    public const DISCO = Arquivo::DISCO_SAUDE_DOCUMENTOS;

    public const TAMANHO_MAXIMO_KB = 20480;

    /** extensão => MIME aceito (validado pelo conteúdo do arquivo, não pelo nome). */
    public const TIPOS_ACEITOS = [
        'pdf' => [Arquivo::MIME_PDF],
        'jpg' => [Arquivo::MIME_JPEG, Arquivo::MIME_JPG],
        'jpeg' => [Arquivo::MIME_JPEG, Arquivo::MIME_JPG],
        'png' => [Arquivo::MIME_PNG],
        'docx' => [Arquivo::MIME_DOCX, 'application/zip'],
        'xlsx' => [Arquivo::MIME_XLSX, 'application/zip'],
    ];

    /**
     * @return array{arquivo: Arquivo, hash: string}
     */
    public function gravarUpload(int $empresaId, UploadedFile $arquivo, ?int $userId): array
    {
        $extensao = strtolower((string) $arquivo->getClientOriginalExtension());
        // Tipo pelo conteúdo (finfo), não pelo que o navegador declarou.
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($arquivo->getRealPath());
        if (!isset(self::TIPOS_ACEITOS[$extensao]) || !in_array($mime, self::TIPOS_ACEITOS[$extensao], true)) {
            throw ValidationException::withMessages(['arquivo' => 'Envie PDF, imagem (JPG/PNG), Word (DOCX) ou Excel (XLSX).']);
        }

        $conteudo = (string) file_get_contents($arquivo->getRealPath());

        return $this->gravarConteudo($empresaId, $conteudo, $arquivo->getClientOriginalName(), $extensao, $userId);
    }

    /**
     * @return array{arquivo: Arquivo, hash: string}
     */
    public function gravarConteudo(int $empresaId, string $conteudo, string $nomeOriginal, string $extensao, ?int $userId): array
    {
        if ($conteudo === '') {
            throw ValidationException::withMessages(['arquivo' => 'Arquivo vazio.']);
        }

        $extensao = strtolower(ltrim($extensao, '.'));
        $caminho = sprintf('empresa_%d/%s/%s.%s', $empresaId, date('Y/m'), Str::uuid()->toString(), $extensao);
        Storage::disk(self::DISCO)->put($caminho, $conteudo, ['visibility' => 'private']);

        $arquivo = Arquivo::create([
            'quem_enviou' => $userId,
            'nome' => mb_substr(pathinfo($nomeOriginal, PATHINFO_FILENAME) ?: 'documento', 0, 180),
            'imagem' => false,
            'layout' => null,
            'extensao' => '.' . $extensao,
            'file' => $caminho,
            'thumb' => null,
            'bytes' => strlen($conteudo),
            'temporario' => false,
            'chave' => null,
            'disco' => self::DISCO,
        ]);

        return ['arquivo' => $arquivo, 'hash' => hash('sha256', $conteudo)];
    }
}
