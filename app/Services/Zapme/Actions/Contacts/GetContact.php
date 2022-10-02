<?php

namespace ZapMeSdk\Actions\Contacts;

use Exception;
use ZapMeSdk\Traits\PerformRequest;
use ZapMeSdk\Traits\ShareableConstructor;

class GetContact
{
    use PerformRequest;
    use ShareableConstructor;

    /**
     * Path related with the action.
     *
     * @param  int  $id
     * @return string
     */
    public function path(int $id): string
    {
        return sprintf('/contacts/%s?api=%s&secret=%s', $id, $this->api, $this->secret);
    }

    /**
     * @throws Exception
     */
    public function __invoke(int $id)
    {
        $this->method('GET');
        
        return $this->request($this->path($id));
    }
}
