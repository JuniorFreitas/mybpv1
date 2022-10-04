<?php

namespace ZapMeSdk\Actions\Contacts;

use Exception;
use ZapMeSdk\Traits\PerformRequest;
use ZapMeSdk\Traits\ShareableConstructor;

class CreateContact
{
    use PerformRequest;
    use ShareableConstructor;

    /**
     * Path related with the action.
     *
     * @return string
     */
    public function path(): string
    {
        return '/contacts';
    }

    /**
     * @throws Exception
     */
    public function __invoke(string $name, string $phone, string $status = 'active', $group = null)
    {
        $data = [
            'name'   => $name,
            'phone'  => $phone,
            'status' => $status,
        ];

        if ($group !== null) {
            $data['group'] = $group;
        }

        return $this->request($this->path(), $data);
    }
}