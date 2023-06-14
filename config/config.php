<?php

use HaydenPierce\ClassFinder\ClassFinder;

$baseUrl = env('SX_BASE_URL', 'https://www.survey-xact.dk').'/rest';

return [

    /*
    |--------------------------------------------------------------------------
    | Route Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | An array of all middlewares to be applied to all of the generated routes.
    |
    */

    'middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Model Namespace Configuration
    |--------------------------------------------------------------------------
    |
    | String or array with one ore multiple namespaces that should be monitored 
    | for the configured trait.
    |
    */

    'namespace' => 'App\Models',

    /*
    |--------------------------------------------------------------------------
    | Model Namespace Search Option
    |--------------------------------------------------------------------------
    |
    | Defines the search mode for the namespaces. ClassFinder::STANDARD_MODE
    | will only find the exact matching namespace, ClassFinder::RECURSIVE_MODE
    | will find all subnamespaces.
    | 
    | Beware: ClassFinder::RECURSIVE_MODE might cause some testing issues.
    |
    */

    'namespace_mode' => ClassFinder::STANDARD_MODE,

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | Defines the route prefix.
    |
    */

    'prefix' => 'api',

    /*
    |--------------------------------------------------------------------------
    | SX Basic Auth
    |--------------------------------------------------------------------------
    |
    | Defines the SX Basic Auth.
    |
    */

    'auth' => [
        env('SX_USERNAME'),
        env('SX_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SX default unique columns
    |--------------------------------------------------------------------------
    |
    | An array of unique columns inside the SX database. Can be extended per entity with 
    | {@see \berthott\SX\Models\Traits\Sxable::uniqueFields()}.
    |
    */

    'defaultUnique' => [
        'respondentid'
    ],

    /*
    |--------------------------------------------------------------------------
    | SX default primary
    |--------------------------------------------------------------------------
    |
    | Defines the primary column inside the SX database. Defaults to SXs internal ID.
    |
    */

    'primary' => 'respondentid',

    /*
    |--------------------------------------------------------------------------
    | SX import filters
    |--------------------------------------------------------------------------
    |
    | Defines an array of prefixes that will be filtered during SX import 
    | automatically.
    |
    */

    'filters' => ['x_'],

    /*
    |--------------------------------------------------------------------------
    | API definitions
    |--------------------------------------------------------------------------
    |
    | Defines the a JSON representation of the SX API. See
    | https://documenter.getpostman.com/view/1760772/S1a33ni6
    |
    */
    
    'api' => [
        'surveys' => [
            'all' => [
                'api' => "{$baseUrl}/organizations/{organization}/surveys",
                'method' => 'get'
            ],
            'get' => [
                'api' => "{$baseUrl}/surveys/{survey}" ,
                'method' => 'get'
            ],
            'create' => [
                'api' => "{$baseUrl}/organizations/{organization}/surveys" ,
                'method' => 'post'
            ],
            'delete' => [
                'api' => "{$baseUrl}/surveys/{survey}" ,
                'method' => 'delete'
            ],

            'move' => [
                'api' => "{$baseUrl}/surveys/{survey}/move/{parent_organization}" ,
                'method' => 'put'
            ],
            'toggleSelfCreation' => [
                'api' => "{$baseUrl}/surveys/{survey}/selfcreation" ,
                'method' => 'put'
            ],

            'exportQuestionnaire' => [
                'api' => "{$baseUrl}/surveys/{survey}/questionnaire" ,
                'method' => 'get'
            ],
            'exportDataset' => [
                'api' => "{$baseUrl}/surveys/{survey}/export/dataset" ,
                'method' => 'get'
            ],
            'exportLabels' => [
                'api' => "{$baseUrl}/surveys/{survey}/export/labels" ,
                'method' => 'get'
            ],
            'exportStructure' => [
                'api' => "{$baseUrl}/surveys/{survey}/export/structure" ,
                'method' => 'get'
            ],
            'exportVariables' => [
                'api' => "{$baseUrl}/surveys/{survey}/export/variables" ,
                'method' => 'get'
            ],

            'exportReturnMails' => [
                'api' => "{$baseUrl}/surveys/{survey}/exportReturnMails" ,
                'method' => 'get'
            ],
            'deleteReturnMails' => [
                'api' => "{$baseUrl}/surveys/{survey}/deleteReturnMails" ,
                'method' => 'delete'
            ],
        ],


        'respondents' => [
            'all' => [
                'api' => "{$baseUrl}/surveys/{survey}/respondents" ,
                'method' => 'get'
            ],
            'get' => [
                'api' => "{$baseUrl}/respondents/{respondent}" ,
                'method' => 'get'
            ],
            'create' => [
                'api' => "{$baseUrl}/surveys/{survey}/respondents" ,
                'method' => 'post'
            ],
            'delete' => [
                'api' => "{$baseUrl}/respondents/{respondent}" ,
                'method' => 'delete'
            ],

            'findRespondents' => [
                'api' => "{$baseUrl}/surveys/{survey}/findRespondents" ,
                'method' => 'get'
            ],
            'findRespondentsWithin' => [
                'api' => "{$baseUrl}/surveys/{survey}/findRespondentsWithin" ,
                'method' => 'get'
            ],
            'findRespondentByCustomKey' => [
                'api' => "{$baseUrl}/surveys/{survey}/findRespondentByCustomKey" ,
                'method' => 'get'
            ],

            'getAnswers' => [
                'api' => "{$baseUrl}/respondents/{respondent}/answer" ,
                'method' => 'get'
            ],
            'updateAnswers' => [
                'api' => "{$baseUrl}/respondents/{respondent}/answer" ,
                'method' => 'put'
            ],

            'getCompletedRespondentsCount' => [
                'api' => "{$baseUrl}/analysis/{analysis}/getCompletedRespondentsCount" ,
                'method' => 'get'
            ],
            'sendMail' => [
                'api' => "{$baseUrl}/respondents/{respondent}/sendmail/{type}" ,
                'method' => 'post'
            ],
            'anonymize' => [
                'api' => "{$baseUrl}/surveys/{survey}/anonymize" ,
                'method' => 'post'
            ],
            'anonymizeOpenVariables' => [
                'api' => "{$baseUrl}/surveys/{survey}/anonymizeOpenVariables" ,
                'method' => 'post'
            ],
        ],

        'organization' => [
            'all' => [
                'api' => "{$baseUrl}/organizations/{organization}/organizations",
                'method' => 'get'
            ],
            'get' => [
                'api' => "{$baseUrl}/organizations/{organization}" ,
                'method' => 'get'
            ],
            
            'move' => [
                'api' => "{$baseUrl}/organizations/{organization}/move/{parent_organization}" ,
                'method' => 'put'
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | exportFormat
    |--------------------------------------------------------------------------
    |
    | Defines the export format. Possible values are:
    | 'XLSX', 'CSV', 'TSV', 'ODS', 'XLS', 'HTML', 'MPDF', 'DOMPDF', 'TCPDF'
    | See https://docs.laravel-excel.com/3.1/exports/export-formats.html
    |
    */
    'exportFormat' => 'XLSX',

    /*
    |--------------------------------------------------------------------------
    | Exclude from export
    |--------------------------------------------------------------------------
    |
    | Defines an array of columns to be excluded from the export.
    |
    */
    'excludeFromExport' => ['created_at', 'updated_at', 'survey', 'respondentid'],
];
