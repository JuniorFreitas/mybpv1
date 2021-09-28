<?php

namespace MasterTag;
/**
 * ZapMe
 * Notificações inteligentes via WhatsApp
 * Este conteúdo foi desenvolvido pela Zap Me e por isso detem os direitos sobre o mesmo.
 *
 * Copyright (c) 2020
 * GARANTIMOS A FUNCIONALIDADE DESTE ARQUIVO CASO O MESMO NÃO SOFRA ALTERAÇÕES.
 *
 * @package    ZapMe
 * @author     ZapMe
 * @copyright  2020
 * @link       https://zapme.com.br
 * @since      Version 1.4
 */
class ZapMEApi
{
    /**
     * API. Você pode definir diretamente aqui ou instanciar
     * o objeto e definir o valor no momento em que pretende enviar a mensagem, por exemplo:
     * $zapme = new ZapMEApi();
     * $zapme->api = 'API';
     */
    public $api = null;

    /**
     * Chave Secreta. Você pode definir diretamente aqui ou instanciar
     * o objeto e definir o valor no momento em que pretende enviar a mensagem, por exemplo:
     * $zapme = new ZapMEApi();
     * $zapme->secret = 'CHAVE_SECRETA';
     */
    public $secret = null;

    /**
     * Método usado para a requisição
     * addcontact, sendmessage, consultmessage
     */
    public $method;

    /**
     * Handler do telefone usado de acordo com os métodos necessários
     */
    public $phone;

    /**
     * Handler do nome usado de acordo com os métodos necessários
     */
    public $name;

    /**
     * Handler do grupo usado de acordo com os métodos necessários
     */
    public $group = null;

    /**
     * Handler da mensagem usada de acordo com os métodos necessários
     */
    public $message;

    /**
     * Handler da hash de imagem/pdf à ser enviada em conjunto com o método `sendmessage`
     */
    public $document = null;

    /**
     * Handler do tipo de arquivo à ser enviado em conjunto com o método `sendmessage`
     */
    public $filetype = null;

    /**
     * Handler da id da mensagem usada de acordo com os métodos necessários
     */
    public $messageid;

    /**
     * Handler dos parâmetros usados de forma interna
     */
    private $data;

    /**
     * Função interna apenas para garantir de lembrar ao usuário se há valores importantes nulos
     * @return bool
     */
    private function Verify(string $type)
    {
        return $this->$type === null ? true : false;
    }

    /**
     * Run Function
     *
     * Função master (principal) usada para envio da requisição até o end-point da API.
     * @return array @json
     */
    public function Run()
    {
        if ($this->Verify('api')) {
            throw new \Exception('API não pode estar nulo. Defina no ambiente da classe ou no momento de instanciar a classe');
        }

        if ($this->Verify('secret')) {
            throw new \Exception('Chave Secreta não pode estar nulo. Defina no ambiente da classe ou no momento de instanciar a classe');
        }

        if ($this->Verify('method')) {
            throw new \Exception('Metódo não pode estar nulo. Defina no ambiente da classe ou no momento de instanciar a classe');
        }

        $this->data =
            [
                'api' => $this->api,
                'secret' => $this->secret,
                'method' => $this->method,
            ];

        $this->IncrementData();

        return $this->cURL();
    }

    /**
     * Increment Data
     *
     * Esta função é interna e privada para incrementar os dados do post (on the fly) acordo com o método enviado
     */
    private function IncrementData()
    {
        switch ($this->method) {
            case 'addcontact':

                $this->data +=
                    [
                        'phone' => $this->phone,
                        'name' => $this->name,
                    ];
                if ($this->group !== null) {
                    $this->data +=
                        [
                            'group' => $this->group
                        ];
                }
                break;

            case 'sendmessage':

                $this->data +=
                    [
                        'phone' => $this->phone,
                        'message' => $this->message,
                    ];

                if ($this->document !== null) {
                    $this->data +=
                        [
                            'document' => $this->document,
                            'filetype' => $this->filetype,
                        ];
                }

            case 'consultmessage':

                $this->data +=
                    [
                        'messageid' => $this->messageid,
                    ];

                break;
        }
    }

    /**
     * cURL
     *
     * NÃO MEXA
     *
     * Função interna apenas para disparo da API até o end-point
     * @return array @json Retorna o status da requisição a api
     */
    private function cURL()
    {
        $curl = curl_init("https://api.zapme.com.br");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($result, true);
        return $json;
    }
}
