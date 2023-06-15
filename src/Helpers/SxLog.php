<?php

namespace berthott\SX\Helpers;

use Illuminate\Support\Facades\Log;

/**
 * Logging helper class.
 */
class SxLog
{
    /**
     * Log a message to the `surveyxact` log.
     */
    public function log(string $message): void
    {
        Log::channel('surveyxact')->info($message);
    }
}
