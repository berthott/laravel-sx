<?php

namespace berthott\SX\Services;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\TransferStats;
use GuzzleRetry\GuzzleRetryMiddleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class SxApiService
{
    /*
    * The API to call
    */
    protected string $api;

    public function __construct(string $api)
    {
        $this->api = $api;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $arguments = Arr::collapse($arguments);
        [$url , $method] = $this->getApi($name, $arguments);
        return $this->http()->request($method, $url, $arguments);
    }
    
    /**
     * Create a configured http client.
     */
    private function http(): Client
    {
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory());
        
        return new Client([
            'handler' => $stack,
            'auth' => config('sx.auth'),

            'retry_on_status' => [500],
            'on_retry_callback' => function () {
                $this->log("Error Response... retrying...");
            },
            'on_stats' => function (TransferStats $stats) {
                $this->log('responded with '.$stats->getResponse()->getStatusCode().' after '.$stats->getTransferTime().'s');
            },
        ]);
    }

    /**
     * Log and output.
     */
    public function getApi(string $endpoint, array $values)
    {
        $api = config("sx.api.{$this->api}");
        $url = $api[$endpoint]['api'];

        foreach ($values as $name => $value) {
            if (is_string($value)) {
                $url = str_replace('{'.$name.'}', $value, $url);
            }
        }

        return [$url , $api[$endpoint]['method']];
    }

    /**
     * Log and output.
     */
    private function log(string $message)
    {
        //$this->line($message);
        Log::channel('surveyxact')->info($message);
    }
}
