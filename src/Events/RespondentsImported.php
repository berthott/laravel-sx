<?php

namespace berthott\SX\Events;

use Illuminate\Foundation\Events\Dispatchable;

class RespondentsImported
{
    use Dispatchable;

    public string $model;

    public function __construct(string $model) {
        $this->model = $model;
    }
}