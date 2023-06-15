<?php

namespace berthott\SX\Services\Http;

use Facades\berthott\SX\Helpers\SxLog;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\TransferStats;
use GuzzleRetry\GuzzleRetryMiddleware;
use Illuminate\Support\Arr;

/*
 * Service actually calling the SX API endpoints.
 * 
 * @see \berthott\SX\Services\Http\SxHttpService
 * @see file://config/config.php
 */
class SxApiService
{
    /*
    * The API to call.
    */
    protected string $api;

    /*
    * Set the API to call.
    */
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
        [$url , $method] = $this->getUrlAndMethod($name, $arguments);
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
     * Get the URL and Method to use.
     * 
     * @return string[]
     */
    private function getUrlAndMethod(string $endpoint, array $values): array
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
