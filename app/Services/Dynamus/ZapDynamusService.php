<?php

namespace App\Services\Dynamus;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ZapDynamusService
{
    protected $client;
    protected $baseUrl;
    protected $instancia;

    public function __construct()
    {
        $this->instancia = 'mybp-notificacao';
        $this->baseUrl = 'https://zapevolutionapi.dynamusti.com.br';
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'apikey' => '20c5dee2915cfa8064a635434c118a60'
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

    public function checkIsNumberWhatsapp(string $phone)
    {
        try {
            $response = $this->client->post($this->baseUrl . "/chat/whatsappNumbers/" . $this->instancia, [
                "numbers" => [$phone]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Erro na verificação de número WhatsApp: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exemplo de método específico para o ZapDynamus
     */
    public function sendMessage(string $phone, string $message, array $attachment = [])
    {
        $config = [
            'number' => $phone,
            'text' => $message,
            'delay' => rand(2000, 4000)
        ];

        return $this->post("message/sendText/" . $this->instancia, $config);
    }

    public function sendImagem(string $phone, string $message, $path)
    {
        return $this->post("message/sendMedia/" . $this->instancia, [
//            'number' => $phone,
//            'caption' => $message,
//            'imagePath' => $path

            "number" => $phone,
            "mediatype" => "image",
            "mimetype" => "image/png",
            "caption" => $message,
            "media" => $path,
            "fileName" => "image.png",
            'delay' => rand(2000, 4000),
            "linkPreview" => true,
        ]);
    }

    public function sendPdf(string $phone, string $message, $path)
    {
        return $this->post("send-pdf", [
            "number" => $phone,
            "mediatype" => "image",
            "mimetype" => "pdf",
            "caption" => $message,
            "media" => $path,
            "fileName" => "image.pdf",
            'delay' => rand(2000, 4000),
            "linkPreview" => true,
        ]);
    }
}
