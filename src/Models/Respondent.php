<?php

namespace berthott\SX\Models;

use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Support\Arrayable;

class Respondent implements Arrayable
{
    public array $attributes = [];

    public function __construct(Response $response)
    {
        $xml = simplexml_load_string($response->getBody());
        foreach ($xml->xpath('.//*') as $node) {
            if ($node->getName() === 'link') {
                $this->attributes[str_replace(' ', '', (string) $node['rel'][0]).'url'] = (string) $node['href'][0];
            } else {
                $this->attributes[$node->getName()] = (string) $node[0];
            }
        }
    }

    public function __call(string $method, array $args): mixed
    {
        if (array_key_exists($method, $this->attributes)) {
            return is_callable($this->attributes[$method])
                ? $this->attributes[$method](...$args)
                : $this->attributes[$method];
        }
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
