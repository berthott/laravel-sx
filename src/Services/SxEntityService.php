<?php

namespace berthott\SX\Services;

class SxEntityService
{
    public function __call(string $name, array $arguments): mixed
    {
        return new SxApiService($name);
    }
}
