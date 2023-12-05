# Laravel-SX

A SurveyXAct (SX) API Integration for Laravel. 
Easily add SX integration by adding a Trait.

## Requirements

For a connection between Laravel, it is necessary, to own a SX License. The connection is set up per survey. The user that is used to query data from this survey need to have sufficient access rights within SX. For more information see the [Consultant Documentation](//TODO)

## Installation

```sh
$ composer require berthott/laravel-sx
```

## Concept

### Sxable

SX is a surveying tool that offers a `questionnaire` to the end user who will respond which will fill out one row in a database with values corresponding to their answers. This row is referred to as `respondent`, so the whole dataset will be the `respondents`. The structure of this respondents table including information question type, as well as the `questionnaire` which is the labels for the structure, and the `labels` for all possible answers can be downloaded by SX's API.

In addition, respondents can be created and prefilled with background variables via the API.

For more information see the [SX API Documentation](https://documenter.getpostman.com/view/1760772/S1a33ni6).

This package will provide all the API implementation for you. Once you connect a survey, the respondent's data from SX will be synced with your own database. Interacting (creating, editing and deleting respondents) with this package will ensure to always maintain the latest data in both table, SX and our own.
If changes are done to the SX Database by end users a sync can be triggered via the `sync route` or by the `sx:import Artisan command`.

### Distributable

A distributable is an entity within your database that can provide background variable to pre-fill respondents data. It corresponds to a sxable and provides some useful routes for collecting respondents.

### Use cases

There's different possible use cases, that are implemented by the `ngs-core/sx-entity` package.
* Simply connecting a survey, without creating respondents on your own: Just show the data in your application.
* Create / Edit respondents from within the package: Use SX as a sophisticated input form for your application.
* Collect data for specific entities (distributable): Create respondents connected to specific entities within you application.

## Usage

### Sxable

* Create a model without a table migration, eg. with `php artisan make:model YourModel`
* Add the `Sxable` trait to your newly generated model.
* Implement the `surveyId()` method to provide the SX connection.
* The package will create and fill the following tables on the fly:
  * `yourmodels` => the entities in wide format
  * `yourmodels_long` => the entities in long format
  * `yourmodel_questions`
  * `yourmodel_labels`
* The package will register the following routes:
  * Index, *get*     `yourmodels/` => get all entities
  * Show, *get*     `yourmodels/{yourmodel}` => get respondent information from our DB
  * Show respondent, *get*     `yourmodels/{yourmodel}/show_respondent` => get respondent information from inside SX
  * Create respondent, *post*    `yourmodels/` => create a new respondent inside SX and our DB
  * Update respondent, *put*    `yourmodels/{yourmodel}` => update a respondent inside SX and our DB
  * Destroy, *delete*  `yourmodels/{yourmodel}` => delete a respondent inside SX and in our DB
  * Destroy many, *delete*  `yourmodels/destroy_many` => delete a respondent inside SX and in our DB
  * Sync, *post*    `yourmodels/sync` => sync all respondent answers from SX to our DB
  * Export, *get* *post*    `yourmodels/export` => download a XLSX export
  * Structure, *get*  `yourmodel/structure` => get all columns inside SX and their meta information
  * Labels, *get*  `yourmodel/labels` => get all labels for the survey questions
  * Report, *get*  `yourmodel/report` => get report data for the survey
  * Report PDF, *get*  `yourmodel/report_pdf` => get a PDF report from some frontend charts
  * Languages, *get*  `yourmodel/languages` => get the SX survey languages
  * Preview, *get*  `yourmodel/preview` => get a collect url for a preview survey
* For more information on how to setup certain features see `\berthott\SX\Models\Traits\Sxable`.

### SXDistributable

* Create a model without a table migration, eg. with `php artisan make:model YourModel`
* Add the `SxDistributable` trait to your newly generated model.
* Implement the `sxable()` method to provide the connection with a Sxable.
* The package will register the following routes:
  * SX collect, *get*     `yourmodels/{yourmodel}` => create a new respondent and redirect to its collect URL
  * SX QR code, *get*     `yourmodels/{yourmodel}/qrcode` => get a QR code for the collect endpoint
  * SX QR code PDF, *get*     `yourmodels/{yourmodel}/pdf` => download a PDF with the QR code
  * SX data, *get*     `yourmodels/{yourmodel}/sxdata` => get some data that can be used inside SX surveys
  * SX preview, *get*     `yourmodels/{yourmodel}/preview` => get a collect url for a preview survey
* For more information on how to setup certain features see `\berthott\SX\Models\Traits\SxDistributable`.

## Options

To change the default options use
```
$ php artisan vendor:publish --provider="berthott\SX\SxServiceProvider" --tag="config"
```
* Inherited from [laravel-targetable](https://docs.syspons-dev.com/laravel-targetable)
  * `namespace`: String or array with one ore multiple namespaces that should be monitored for the configured trait. Defaults to `App\Models`.
  * `namespace_mode`: Defines the search mode for the namespaces. `ClassFinder::STANDARD_MODE` will only find the exact matching namespace, `ClassFinder::RECURSIVE_MODE` will find all subnamespaces. Defaults to `ClassFinder::STANDARD_MODE`.
  * `prefix`: Defines the route prefix. Defaults to `api`.
* General Package Configuration
  * `middleware`: An array of all middlewares to be applied to all of the generated routes. Defaults to `[]`.
* SX interconnectivity options
  * `auth`: Defines the SX Basic Auth. Defaults to env variables `SX_USERNAME` and `SX_PASSWORD`.
  * `defaultUnique`: An array of unique columns inside the SX database. Can be extended per entity with `\berthott\SX\Models\Traits\Sxable::uniqueFields()`. Defaults to `['respondentid']`.
  * `primary`: Defines the primary column inside the SX database. Defaults to SXs internal ID `respondentid`.
  * `filters`: Defines an array of prefixes that will be filtered during SX import automatically. Defaults to `['x_']`.
  * `api`: Defines the a JSON representation of the SX API. See the [SX API Documentation](https://documenter.getpostman.com/view/1760772/S1a33ni6).
* Export options
  * `exportFormat`: Defines the export format. Possible values are: `XLSX`, `CSV`, `TSV`, `ODS`, `XLS`, `HTML`, `MPDF`, `DOMPDF`, `TCPDF`. See the [Laravel-Excel Documentation](https://docs.laravel-excel.com/3.1/exports/export-formats.html). Defaults to `XLSX`.
  * `excludeFromExport`: Defines an array of columns to be excluded from the export. Defaults to  `['created_at', 'updated_at', 'survey', 'respondentid']`.

## Remarks

### SX short variable names vs. long variable names

SX uses short variable names for export by default. While the SX API gives us the option to export also long names, when interacting with respondents, it requires short names. Therefore we export the short names and use the returned structure to guess the short and full names however we need them. See `\berthott\SX\Services\SxSurveyService`.

## Architecture

* The package relies on [laravel-targetable](https://docs.syspons-dev.com/laravel-targetable) to connect specific functionality to Laravel model entities via traits. (`Sxable`, `Distributable`).
* API
  * The SX API interaction happens inside `SxApiService` and is separated into different endpoint with `SxHttpService`. As API definition might change in the future it is configurable inside the `api config`.
  * `SxSurveyService` is an interface into the SX survey data. It caches some data. `SxRespondentService` is an interface for interacting with respondents. Both utilize `SxHttpService` for this.
* `Sxable`
  * Maintains the connection between SX and our database utilizing `SxSurveyService`
  * `SxableController`
    * provide the routes for interacting with the Sxable and the SX API
    * utilizes `SxRespondentService` for creating respondents in SX
    * Some routes will affect our own database, some won't and only transmit data from the SX API
  * Wide vs. Long Data
    * SX stores data in a wide table format, but aggregation is best solved with long data
    * An exact copy of the original wide data is maintained as a long table by the `SxableObserver`
    * During initialization and import the observer is bypassed for performance reasons (see `Sxable::doUpsert`)
  * More Tables
    * After initializing an Sxable questions + labels will be stored in an extra table. These won't change after initialization, so whenever the surveys structure changes inside SX these table need to be reinitialized
* `Distributable`
  * Maintains the connection between an arbitrary model and an Sxable.
  * `DistributableController`
    * provide the routes for interacting with the Distributable
* Requests
  * mostly used to encapsulate validation
  * `SxReportRequest` adds some functionality around filtering and aggregation
* Export
  * An export will return a labeled wide table, as well as the unlabeled wide table, the questions and the labels table
  * The wide table can be filtered by IDs
* `RespondentsImported` will be fired whenever new entities were imported. Useful for flushing the cache
* Artisan commands for initializing, importing and dropping tables and data.

  


## Compatibility

Tested with Laravel 10.x.

## License

See [License File](license.md). Copyright © 2023 Jan Bladt.