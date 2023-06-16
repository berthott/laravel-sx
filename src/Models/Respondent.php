<?php

namespace berthott\SX\Models;

use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Support\Arrayable;

/**
 * SX Respondent Model.
 * 
 * The model holds the data returned by SX.
 * 
 * * id: Is the unique number identifying the respondent.
 * * externalkey: Is the unique key identifying the respondent.
 * * collectstatus: Is the current collection status of the respondent.
 * * collecturl: Is the link to start the collection of answers for the respondent.
 * * createts: Is the date when the respondent was created.
 * * closets: Is the date when the respondent finished answering the questionnaire.
 * * starts: Is the date when the respondent started answering the questionnaire.
 * * modifyts: Is the date when the respondent was modified. Note that this is updated if the questionnaire is saved.
 * * sessioncount: Is the number of collection sessions that the respondent used when answering the questionnaire.
 * * collect: Is the link to start the collection of answers for the respondent.
 * * self: Is the endpoint to the respondent itself.
 * * survey: Is the endpoint to get the survey the respondent belongs to.
 * * answer: Is the endpoint to get the answers for the respondent.
 * * send distribution mail: Is the endpoint to send a distribution mail to the respondent.
 * * send reminder mail: Is the endpoint to send a reminder mail to the respondent.
 * 
 * @link https://documenter.getpostman.com/view/1760772/S1a33ni6#abe7266c-677f-463d-8e01-3347caa97521 SX Respondent
 * @api
 */
class Respondent implements Arrayable
{
    /** 
     * @var string[]    $attributes Holds all the properties.
     */
    public array $attributes = [];

    /**
     * The Constructor.
     * 
     * Parses a XML SX response and fills the attributes. 
     */
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

    /**
     * Make attributes callable.
     */
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
