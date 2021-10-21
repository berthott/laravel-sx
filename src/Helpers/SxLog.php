<?php

namespace berthott\SX\Helpers;

use Illuminate\Support\Facades\Log;

class SxLog
{
    public function log(string $message): void
    {
        //$this->line($message);
        Log::channel('surveyxact')->info($message);
    }
}
