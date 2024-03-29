<?php

namespace berthott\SX\Services\Http;

use Facades\berthott\SX\Services\Http\SxApiService;

/*
 * Service to distinguish between different SX APIs.
 * 
 * @see \berthott\SX\Services\Http\SxApiService
 * @see file://config/config.php
 */
class SxHttpService
{
    public function __call(string $name, array $arguments): mixed
    {
        return SxApiService::api($name);
    }
}
