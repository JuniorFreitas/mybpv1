<?php

namespace App\Services\Cloud;

use App\Models\Arquivo;
use App\Models\Cloud;
use App\Models\ItensCloud;
use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CloudMultipartUploadService
{
    public const PART_SIZE = 5 * 1024 * 1024; // 5MB

    public const SESSION_TTL_SECONDS = 86400; // 24h

    public const PRESIGN_TTL_SECONDS = 900; // 15min

    public function iniciar(array $dados): array
    {
        $cloud = Cloud::encontrarAutorizadoOuAbortar($dados['cloud_id'] ?? 0);
        $pertenceId = $dados['pertence_id'] ?? null;
        $anteriorId = $dados['anterior_id'] ?? null;

        $this->validarPastaDestino($cloud, $pertenceId);

        $nomeOriginal = (string) ($dados['nome_original'] ?? '');
        $mime = (string) ($dados['mime'] ?? 'application/octet-stream');
        $bytes = (int) ($dados['bytes'] ?? 0);

        if ($nomeOriginal === '' || $bytes <= 0) {
            throw new HttpException(400, 'Metadados do arquivo inválidos');
        }

        if (!Arquivo::permitidoNoCloud($mime, $nomeOriginal)) {
            throw new HttpException(400, "O upload do arquivo \"{$nomeOriginal}\" falhou. Arquivos executáveis não são permitidos.");
        }

        $extensao = $this->extrairExtensao($nomeOriginal);
        $nome = Arquivo::pegarNomeArquivo($nomeOriginal);

        if (!$anteriorId) {
            $this->garantirNomeDisponivel($cloud->id, $pertenceId, $nome, $extensao);
        } else {
            $this->validarItemSubstituicao($anteriorId, $cloud->id);
        }

        $fileRelativo = $this->gerarNomeArquivoDisco($extensao);
        $modo = $this->usaS3() ? 's3' : 'local';
        $s3UploadId = null;
        $s3Key = $this->montarS3Key($fileRelativo);

        if ($modo === 's3') {
            $result = $this->s3Client()->createMultipartUpload([
                'Bucket' => $this->bucket(),
                'Key' => $s3Key,
                'ContentType' => $mime,
            ]);
            $s3UploadId = $result['UploadId'] ?? null;
            if (!$s3UploadId) {
                throw new RuntimeException('Falha ao iniciar multipart no S3');
            }
        }

        $sessionId = (string) Str::uuid();
        $sessao = [
            'user_id' => (int) auth()->id(),
            'empresa_id' => (int) auth()->user()->empresa_id,
            'cloud_id' => (int) $cloud->id,
            'pertence_id' => $pertenceId ? (int) $pertenceId : null,
            'anterior_id' => $anteriorId ? (int) $anteriorId : null,
            'nome_original' => $nomeOriginal,
            'nome' => $nome,
            'extensao' => $extensao,
            'mime' => $mime,
            'bytes' => $bytes,
            'file_relativo' => $fileRelativo,
            's3_key' => $s3Key,
            's3_upload_id' => $s3UploadId,
            'modo' => $modo,
        ];

        $this->salvarSessao($sessionId, $sessao);

        if ($modo === 'local') {
            Storage::disk('local')->makeDirectory($this->caminhoPartesLocais($sessionId));
        }

        return [
            'upload_session_id' => $sessionId,
            'upload_id' => $s3UploadId,
            'key' => $fileRelativo,
            'part_size' => self::PART_SIZE,
            'modo' => $modo,
            'bytes' => $bytes,
        ];
    }

    public function assinarParte(string $sessionId, int $partNumber): array
    {
        $sessao = $this->obterSessaoAutorizada($sessionId);

        if ($partNumber < 1 || $partNumber > 10000) {
            throw new HttpException(400, 'Número de parte inválido');
        }

        if ($sessao['modo'] === 's3') {
            $command = $this->s3Client()->getCommand('UploadPart', [
                'Bucket' => $this->bucket(),
                'Key' => $sessao['s3_key'],
                'UploadId' => $sessao['s3_upload_id'],
                'PartNumber' => $partNumber,
            ]);
            $request = $this->s3Client()->createPresignedRequest($command, '+' . self::PRESIGN_TTL_SECONDS . ' seconds');

            return [
                'modo' => 's3',
                'url' => (string) $request->getUri(),
                'method' => 'PUT',
                'part_number' => $partNumber,
            ];
        }

        return [
            'modo' => 'local',
            'url' => url('/g/itenscloud/multipart/parte'),
            'method' => 'POST',
            'part_number' => $partNumber,
            'upload_session_id' => $sessionId,
        ];
    }

    public function receberParteLocal(string $sessionId, int $partNumber, UploadedFile $parte): array
    {
        $sessao = $this->obterSessaoAutorizada($sessionId);

        if ($sessao['modo'] !== 'local') {
            throw new HttpException(400, 'Sessão não é local');
        }

        if ($partNumber < 1 || $partNumber > 10000) {
            throw new HttpException(400, 'Número de parte inválido');
        }

        $conteudo = file_get_contents($parte->getRealPath());
        if ($conteudo === false) {
            throw new HttpException(400, 'Falha ao ler parte');
        }

        $path = $this->caminhoPartesLocais($sessionId) . '/part_' . $partNumber;
        Storage::disk('local')->put($path, $conteudo);

        $etag = md5($conteudo);

        return [
            'part_number' => $partNumber,
            'etag' => $etag,
        ];
    }

    /**
     * @param  array<int, array{part_number:int, etag:string}>  $partes
     */
    public function completar(string $sessionId, array $partes): Arquivo
    {
        $sessao = $this->obterSessaoAutorizada($sessionId);

        if (count($partes) < 1) {
            throw new HttpException(400, 'Lista de partes vazia');
        }

        usort($partes, static fn ($a, $b) => ((int) $a['part_number']) <=> ((int) $b['part_number']));

        if ($sessao['modo'] === 's3') {
            $this->s3Client()->completeMultipartUpload([
                'Bucket' => $this->bucket(),
                'Key' => $sessao['s3_key'],
                'UploadId' => $sessao['s3_upload_id'],
                'MultipartUpload' => [
                    'Parts' => array_map(static fn ($p) => [
                        'ETag' => $p['etag'],
                        'PartNumber' => (int) $p['part_number'],
                    ], $partes),
                ],
            ]);
        } else {
            $this->montarArquivoLocalComSessao($sessionId, $sessao, $partes);
        }

        return DB::transaction(function () use ($sessao, $sessionId) {
            $arquivo = Arquivo::create([
                'quem_enviou' => auth()->id(),
                'nome' => $sessao['nome'],
                'imagem' => false,
                'layout' => null,
                'extensao' => '.' . $sessao['extensao'],
                'file' => $sessao['file_relativo'],
                'thumb' => null,
                'bytes' => $sessao['bytes'],
                'temporario' => false,
                'chave' => '',
                'disco' => Arquivo::DISCO_CLOUD,
            ]);

            if (!empty($sessao['anterior_id'])) {
                $this->substituirItemCloud($sessao, $arquivo);
            } else {
                $this->criarItemCloud($sessao, $arquivo);
            }

            $this->limparSessao($sessionId, $sessao);

            return $arquivo->fresh();
        });
    }

    public function abortar(string $sessionId): void
    {
        $sessao = $this->obterSessaoAutorizada($sessionId);

        if ($sessao['modo'] === 's3' && !empty($sessao['s3_upload_id'])) {
            try {
                $this->s3Client()->abortMultipartUpload([
                    'Bucket' => $this->bucket(),
                    'Key' => $sessao['s3_key'],
                    'UploadId' => $sessao['s3_upload_id'],
                ]);
            } catch (\Throwable) {
                // best-effort
            }
        }

        $this->limparSessao($sessionId, $sessao);
    }

    private function criarItemCloud(array $sessao, Arquivo $arquivo): void
    {
        $item = ItensCloud::create([
            'cloud_id' => $sessao['cloud_id'],
            'arquivo_id' => $arquivo->id,
            'label' => $arquivo->nome,
            'tipo' => 'arquivo',
            'pertence' => $sessao['pertence_id'],
            'quem_criou' => auth()->id(),
        ]);

        $pasta = $sessao['pertence_id']
            ? ItensCloud::query()->where('cloud_id', $sessao['cloud_id'])->whereKey($sessao['pertence_id'])->first()
            : null;
        $permissoesPasta = $pasta
            ? $pasta->Permissoes()->pluck('grupo_cloud_id')
            : collect();
        $empresaId = optional($item->Cloud()->first())->empresa_id ?? auth()->user()->empresa_id;
        $permissoes = ItensCloud::permissoesComAdministradores($permissoesPasta, $empresaId);
        $item->Permissoes()->sync($permissoes);
    }

    private function substituirItemCloud(array $sessao, Arquivo $arquivo): void
    {
        $itemAtual = ItensCloud::query()
            ->where('arquivo_id', $sessao['anterior_id'])
            ->where('cloud_id', $sessao['cloud_id'])
            ->first();

        if (!$itemAtual) {
            throw new HttpException(404, 'Arquivo não encontrado');
        }

        ItensCloud::where('id', $itemAtual->id)
            ->where('cloud_id', $itemAtual->cloud_id)
            ->update([
                'arquivo_id' => $arquivo->id,
                'quem_editou' => auth()->id(),
                'revisado' => false,
                'quem_revisou' => null,
                'data_revisao' => null,
                'aprovado' => false,
                'quem_aprovou' => null,
                'data_aprovacao' => null,
            ]);

        $anterior = Arquivo::find($sessao['anterior_id']);
        if ($anterior) {
            $anterior->excluir();
        }
    }

    /**
     * @param  array<int, array{part_number:int, etag:string}>  $partes
     */
    private function montarArquivoLocalComSessao(string $sessionId, array $sessao, array $partes): void
    {
        $dir = $this->caminhoPartesLocais($sessionId);
        $assembledRelative = $dir . '/assembled';
        $fullAssembled = storage_path('app/' . $assembledRelative);

        if (!is_dir(dirname($fullAssembled))) {
            mkdir(dirname($fullAssembled), 0755, true);
        }

        $handle = fopen($fullAssembled, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Falha ao montar arquivo local');
        }

        try {
            foreach ($partes as $parte) {
                $partPath = $dir . '/part_' . (int) $parte['part_number'];
                if (!Storage::disk('local')->exists($partPath)) {
                    throw new HttpException(400, 'Parte ausente: ' . $parte['part_number']);
                }
                $chunk = Storage::disk('local')->get($partPath);
                fwrite($handle, $chunk);
            }
        } finally {
            fclose($handle);
        }

        $stream = fopen($fullAssembled, 'rb');
        try {
            Storage::disk(Arquivo::DISCO_CLOUD)->put($sessao['file_relativo'], $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function validarPastaDestino(Cloud $cloud, $pertenceId): void
    {
        if (!$pertenceId) {
            return;
        }

        $pastaDestino = ItensCloud::query()
            ->where('cloud_id', $cloud->id)
            ->whereKey($pertenceId)
            ->first();

        if (!$pastaDestino) {
            throw new HttpException(404, 'Pasta de destino não encontrada neste Cloud');
        }
    }

    private function garantirNomeDisponivel(int $cloudId, $pertenceId, string $nome, string $extensao): void
    {
        $existe = ItensCloud::whereCloudId($cloudId)
            ->whereLabel($nome)
            ->whereTipo('arquivo')
            ->whereDeletedAt(null)
            ->wherePertence($pertenceId)
            ->whereHas('Arquivo', function ($q) use ($extensao) {
                $q->whereExtensao('.' . $extensao);
            })
            ->count();

        if ($existe > 0) {
            throw new HttpException(400, 'Arquivo ja existe!');
        }
    }

    private function validarItemSubstituicao(int $anteriorId, int $cloudId): void
    {
        $itemAtual = ItensCloud::query()
            ->where('arquivo_id', $anteriorId)
            ->where('cloud_id', $cloudId)
            ->first();

        if (!$itemAtual) {
            throw new HttpException(404, 'Arquivo não encontrado');
        }
    }

    private function extrairExtensao(string $nomeOriginal): string
    {
        $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        if ($ext === '') {
            throw new HttpException(400, 'Arquivo sem extensão');
        }

        return $ext;
    }

    private function gerarNomeArquivoDisco(string $extensao): string
    {
        return Str::random(40) . '.' . $extensao;
    }

    private function montarS3Key(string $fileRelativo): string
    {
        $root = trim((string) config('filesystems.disks.' . Arquivo::DISCO_CLOUD . '.root'), '/');

        return $root !== '' ? $root . '/' . $fileRelativo : $fileRelativo;
    }

    private function usaS3(): bool
    {
        return config('filesystems.disks.' . Arquivo::DISCO_CLOUD . '.driver') === 's3';
    }

    private function bucket(): string
    {
        return (string) config('filesystems.disks.' . Arquivo::DISCO_CLOUD . '.bucket');
    }

    private function s3Client(): S3Client
    {
        $config = [
            'version' => 'latest',
            'region' => config('filesystems.disks.' . Arquivo::DISCO_CLOUD . '.region') ?: 'us-east-1',
            'credentials' => [
                'key' => config('filesystems.disks.' . Arquivo::DISCO_CLOUD . '.key'),
                'secret' => config('filesystems.disks.' . Arquivo::DISCO_CLOUD . '.secret'),
            ],
        ];

        $endpoint = config('filesystems.disks.' . Arquivo::DISCO_CLOUD . '.endpoint');
        if (!empty($endpoint)) {
            $config['endpoint'] = $endpoint;
            $config['use_path_style_endpoint'] = true;
        }

        return new S3Client($config);
    }

    private function chaveCache(string $sessionId): string
    {
        return 'cloud_multipart:' . $sessionId;
    }

    private function salvarSessao(string $sessionId, array $sessao): void
    {
        Cache::put($this->chaveCache($sessionId), $sessao, self::SESSION_TTL_SECONDS);
    }

    private function obterSessaoAutorizada(string $sessionId): array
    {
        $sessao = Cache::get($this->chaveCache($sessionId));
        if (!$sessao || !is_array($sessao)) {
            throw new HttpException(404, 'Sessão de upload não encontrada ou expirada');
        }

        if ((int) $sessao['user_id'] !== (int) auth()->id()) {
            throw new HttpException(403, 'Sessão de upload não autorizada');
        }

        Cloud::encontrarAutorizadoOuAbortar($sessao['cloud_id']);

        return $sessao;
    }

    private function limparSessao(string $sessionId, array $sessao): void
    {
        Cache::forget($this->chaveCache($sessionId));

        if (($sessao['modo'] ?? '') === 'local') {
            Storage::disk('local')->deleteDirectory($this->caminhoPartesLocais($sessionId));
        }
    }

    private function caminhoPartesLocais(string $sessionId): string
    {
        return 'cloud-multipart/' . $sessionId;
    }
}
