<?php

namespace App\Services\Dynamus;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ZapDynamusService
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://zapapi.dynamusti.com.br/api/whatsapp/mybp-notificacao';
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * Fazer uma requisição GET para o ZapDynamus
     */
    public function get(string $endpoint, array $params = [])
    {
        try {
            $response = $this->client->get($this->baseUrl . "/" . $endpoint, [
                'query' => $params
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Erro na requisição GET ZapDynamus: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fazer uma requisição POST para o ZapDynamus
     */
    public function post(string $endpoint, array $data)
    {
        try {
            $response = $this->client->post($this->baseUrl . "/" . $endpoint, [
                'json' => $data
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Erro na requisição POST ZapDynamus: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exemplo de método específico para o ZapDynamus
     */
    public function sendMessage(string $phone, string $message, array $attachment = [])
    {
        return $this->post("send", [
            'number' => $phone,
            'message' => $message,
            'attachment' => $attachment
        ]);
    }

    public function sendImagem(string $phone, string $message, $path)
    {
        return $this->post("send-image", [
            'number' => $phone,
            'caption' => $message,
            'imagePath' => $path
        ]);
    }

    public function sendPdf(string $phone, string $message, $path)
    {
        return $this->post("send-pdf", [
            'number' => $phone,
            'message' => $message,
            'pdfPath' => $path
        ]);
    }
}
