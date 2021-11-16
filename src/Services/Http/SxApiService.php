<?php

namespace berthott\SX\Services\Http;

use berthott\SX\Facades\SxLog;
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

    public function api(string $api): self
    {
        $this->api = $api;
        return $this;
    }

    /**
     * Call to the API.
     * Query parameter must be wrapped in 'query' array.
     * Body parameter must be wrapped in 'form_params' array.
     * URL parameter can be on top level.
     */
    public function __call(string $name, array $arguments): mixed
    {
        $arguments = Arr::collapse($arguments);
        [$url , $method] = $this->getApi($name, $arguments);
        SxLog::log("Requesting $method $url...");
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
                SxLog::log("Error Response... retrying...");
            },
            'on_stats' => function (TransferStats $stats) {
                SxLog::log('responded with '.$stats->getResponse()->getStatusCode().' after '.$stats->getTransferTime().'s');
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
}
