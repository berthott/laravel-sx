<?php

namespace berthott\SX\Services\Http;

use berthott\SX\Facades\SxApiService;

class SxEntityService
{
    public function __call(string $name, array $arguments): mixed
    {
        return SxApiService::api($name);
    }
}
