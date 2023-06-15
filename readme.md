# Laravel-SX

A SurveyXAct (SX) API Integration for Laravel. 
Easily add SX integration by adding a Trait.

## Requirements

For a connection between Laravel, it is necessary, to own a SX License. The connection is set up per survey. The user that is used to query data from this survey need to have sufficient access rights within SX. For more information see the [Consultant Documentation](//TODO)

## Installation

```
$ composer require berthott/laravel-sx
```

## Usage

* Create a model without a table migration, eg. with `php artisan make:model YourModel`
* Add the `Sxable` Trait to your newly generated model.
* The package will create and fill the following tables on the fly:
  * **yourmodels** => the entities in wide format
  * **yourmodels_long** => the entities in long format
  * **yourmodel_questions**
  * **yourmodel_labels**
* The package will register the following routes:
  * *get*     **yourmodels/** => get all entities
  * *post*    **yourmodels/** => create a new respondent inside SX
  * *get*     **yourmodels/{yourmodel}** => get respondent informations from inside SX
  * *delete*  **yourmodels/{yourmodel}** => delete a respondent inside SX and in our DB
  * *post*    **yourmodels/sync** => sync all respondent answers to our DB

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
  * `defaultUnique`: An array of unique columns inside the SX database. Can be extended per entity with {@see \berthott\SX\Models\Traits\Sxable::uniqueFields()}. Defaults to `['respondentid']`.
  * `primary`: Defines the primary column inside the SX database. Defaults to SXs internal ID `respondentid`.
  * `filters`: Defines an array of prefixes that will be filtered during SX import automatically. Defaults to `['x_']`.
  * `api`: Defines the a JSON representation of the SX API. See the [SX API Documentation](https://documenter.getpostman.com/view/1760772/S1a33ni6).
* Export options
  * `exportFormat`: Defines the export format. Possible values are: `XLSX`, `CSV`, `TSV`, `ODS`, `XLS`, `HTML`, `MPDF`, `DOMPDF`, `TCPDF`. See the [Laravel-Excel Documentation](https://docs.laravel-excel.com/3.1/exports/export-formats.html). Defaults to `XLSX`.
  * `excludeFromExport`: Defines an array of columns to be excluded from the export. Defaults to  `['created_at', 'updated_at', 'survey', 'respondentid']`.

## Remarks

### SX short variable names vs. long variable names

SX uses short variable names for export by default. While the SX API gives us the option to export also long names, when interacting with respondents, it requires short names. Therefore we export the short names and use the returned structure to guess the short and full names however we need them. See `\berthott\SX\Services\SxSurveyService`.

## Compatibility

Tested with Laravel 10.x.

## License

See [License File](license.md). Copyright © 2023 Jan Bladt.