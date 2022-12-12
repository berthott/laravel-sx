<?php

$baseUrl = env('SX_BASE_URL', 'https://www.survey-xact.dk').'/rest';

return [

    /*
    |--------------------------------------------------------------------------
    | Route Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Configurations for the route.
    |
    */

    'middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Model Namespace Configuration
    |--------------------------------------------------------------------------
    |
    | Defines one or multiple model namespaces.
    |
    */

    'namespace' => 'App\Models',

    /*
    |--------------------------------------------------------------------------
    | API Prefix
    |--------------------------------------------------------------------------
    |
    | Defines the api prefix.
    |
    */

    'prefix' => 'collect',
];
