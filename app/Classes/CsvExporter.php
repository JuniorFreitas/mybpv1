<?php

/**
 * Class CsvExporter
 *  Desenvolvido por: Master Tag Desenvolvimento Web Ltda
 * Autor: Júnior Freitas
 * versao: 1.0.0
 * ultima atualização: 13/10/2023 10:48:00
 * @package MasterTag
 */

namespace MasterTag;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Arquivo;
use App\Models\Exportacao;
use App\Models\User;
use Event;
use Illuminate\Support\Facades\Storage;

class CsvExporter
{
    private $headers;
    private $data;
    private $fileName;
    /**
     * @var User
     */
    private $user;
    /**
     * @var string
     */
    private $local;

    /**
     * CsvExporter constructor.
     * @param User $user
     * @param string $local
     * @param array $headers
     * @param $data
     */
    public function __construct(User $user, string $local, array $headers, $data)
    {
        $this->user = $user;
        $this->local = $local;
        $this->fileName = $this->generateFileName(\Str::slug($this->local, '_'));
        $this->headers = $headers;
        $this->data = $data;
    }

    /**
     * Gera o nome do arquivo com base no nome passado por parâmetro
     * @param string $fileName
     * @return string
     */
    private function generateFileName(string $fileName): string
    {
        return $fileName . "_{$this->user->id}_{$this->user->empresa_id}_" . date('YmdHis') . ".csv";
    }

    /**
     * Retorna o array de dados para ser inserido no arquivo
     * @param array $row
     * @return array
     */
    private function getDataRow(array $row): array
    {
        return $row;
    }

    /**
     * Armazena o arquivo no S3
     * @param string $localPath
     * @param string $fileName
     * @return void
     * @throws \Exception
     */
    private function storeFileToS3(string $localPath, string $fileName)
    {
        $storage = Storage::disk(Arquivo::DISCO_EXPORTACAO)->putFileAs('', $localPath, $fileName, 'public');
        if (!$storage) {
            throw new \Exception("Erro ao armazenar o arquivo no S3");
        }
    }

    /**
     * Deleta o arquivo local
     * @param string $localPath
     */
    private function deleteLocalFile(string $localPath): void
    {
        if (file_exists($localPath)) {
            unlink($localPath);
        }
    }

    /**
     * Cria o registro de exportação
     * @return void
     */
    private function createExportLog(): void
    {
        Exportacao::create([
            'user_id' => $this->user->id,
            'arquivo' => $this->fileName,
            'local' => $this->local,
            'removido' => false,
        ]);
    }

    /**
     * Dispara o evento de notificação
     * @return void
     */
    private function dispatchNotificationEvent():void
    {
        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->user->id,
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }

    /**
     * Exporta o arquivo
     * @return string|null
     */
    public function export()
    {
        $filename = storage_path("app/" . $this->fileName);
        $file = fopen($filename, 'w');
        fwrite($file, "\xEF\xBB\xBF"); // BOM para garantir o UTF-8

        fputcsv($file, $this->headers, ';');

        foreach ($this->data as $row) {
            $linhas = $this->getDataRow($row);
            fputcsv($file, $linhas, ';');
        }

        fclose($file);

        try {
            $this->storeFileToS3($filename, $this->fileName);
            $this->createExportLog();
            $this->deleteLocalFile($filename);
            $this->dispatchNotificationEvent();
            return $this->fileName;
        } catch (\Exception $e) {
            \Log::error("Erro ao gerar CSV" . $e->getMessage());
            return null;
        }
    }

}
