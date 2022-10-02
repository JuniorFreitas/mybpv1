<?php

namespace ZapMeSdk;

use Exception;
use ZapMeSdk\Actions\Messages\GetMessage;
use ZapMeSdk\Actions\Contacts\GetContact;
use ZapMeSdk\Actions\Messages\SendMessage;
use ZapMeSdk\Actions\Messages\GetMessages;
use ZapMeSdk\Actions\Contacts\GetContacts;
use ZapMeSdk\Actions\Account\AccountStatus;
use ZapMeSdk\Actions\Contacts\CreateContact;
use ZapMeSdk\Actions\Contacts\DestroyContact;

class Base
{
    /**
     * The ZapMe API url.
     *
     * @var string
     */
    private string $url = 'https://api.zapme.com.br';

    /**
     * ZapMe API.
     *
     * @var string|null
     */
    private ?string $api = null;

    /**
     * ZapMe Secret Key.
     *
     * @var string|null
     */
    private ?string $secret = null;

    /**
     * Set the ZapMe URL dynamically.
     *
     * @param string $url
     * @return $this
     */
    public function toUrl(string $url): Base
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set the user API dynamically.
     *
     * @param string $api
     * @return $this
     */
    public function withApi(string $api): Base
    {
        $this->api = $api;

        return $this;
    }

    /**
     * Set the user Secret Key dynamically.
     *
     * @param string $secret
     * @return $this
     */
    public function withSecret(string $secret): Base
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Set the user credentials.
     *
     * @param  array  $data
     * @return $this
     */
    public function withCredentials(array $data): Base
    {
        $this->api    = $data['api'];
        $this->secret = $data['secret'];

        return $this;
    }

    /**
     * Get the user account data.
     *
     * @return mixed
     * @throws Exception
     */
    public function accountStatus()
    {
        return (new AccountStatus(
            $this->url,
            $this->api,
            $this->secret)
        )();
    }

    /**
     * Send a message.
     *
     * @param  string  $phone
     * @param  string  $message
     * @param  array  $attachment
     *
     * @return mixed
     * @throws Exception
     */
    public function sendMessage(string $phone, string $message, array $attachment = [])
    {
        return (new SendMessage(
            $this->url,
            $this->api,
            $this->secret)
        )($phone, $message, $attachment);
    }

    /**
     * Get messages.
     *
     * @param  bool  $paginate
     * @param  int  $page
     * @param  int  $quantity
     *
     * @return mixed
     * @throws Exception
     */
    public function getMessages(bool $paginate = false, int $page = 1, int $quantity = 10)
    {
        return (new GetMessages(
            $this->url,
            $this->api,
            $this->secret)
        )($paginate, $page, $quantity);
    }

    /**
     * Get single message.
     *
     * @param  int  $id
     * @return mixed
     * @throws Exception
     */
    public function getMessage(int $id)
    {
        return (new GetMessage(
            $this->url,
            $this->api,
            $this->secret)
        )($id);
    }

    /**
     * Create a contact.
     *
     * @param  string  $name
     * @param  string  $phone
     * @param $group
     * @return mixed
     * @throws Exception
     */
    public function createContact(string $name, string $phone, string $status = 'active', $group = null)
    {
        return (new CreateContact(
            $this->url,
            $this->api,
            $this->secret)
        )($name, $phone, $status, $group);
    }

    /**
     * Get contacts.
     *
     * @param  bool  $paginate
     * @param  int  $page
     * @param  int  $quantity
     * @return mixed
     * @throws Exception
     */
    public function getContacts(bool $paginate = false, int $page = 1, int $quantity = 10)
    {
        return (new GetContacts(
            $this->url,
            $this->api,
            $this->secret)
        )($paginate, $page, $quantity);
    }

    /**
     * Get single contact..
     *
     * @param  int  $id
     * @return mixed
     * @throws Exception
     */
    public function getContact(int $id)
    {
        return (new GetContact(
            $this->url,
            $this->api,
            $this->secret)
        )($id);
    }

    /**
     * Destroy a contact.
     *
     * @param  int  $id
     * @return mixed
     * @throws Exception
     */
    public function destroyContact(int $id)
    {
        return (new DestroyContact(
            $this->url,
            $this->api,
            $this->secret)
        )($id);
    }
}
