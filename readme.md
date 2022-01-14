# Laravel-SX - A SurveyXAct API Integration for Laravel

Easily add SX integration by adding a Trait.

## Installation

```
$ composer require berthott/laravel-sx
```

## Usage

* Create a model without a table migration, eg. with `php artisan make:model YourModel`
* Add the `Sxable` Trait to your newly generated model.
* The package will create and fill the following tables on the fly:
  * yourmodels => the entities in wide format
  * yourmodels_long => the entities in long format
  * yourmodel_questions
  * yourmodel_labels
* The package will register the following routes:
  * get     yourmodels/ => get all entities
  * post    yourmodels/ => create a new respondent inside SX
  * get     yourmodels/{yourmodel} => get respondent informations from inside SX
  * delete  yourmodels/{yourmodel} => delete a respondent inside SX and in our DB
  * post    yourmodels/import => import all respondent answers to our DB
## Options

To change the default options use
```
$ php artisan vendor:publish --provider="berthott\SX\SxServiceProvider" --tag="config"
```
* `middleware`: an array of middlewares that will be added to the generated routes
* `namespace`: string or array with one ore multiple namespaces that should be monitored for the Crudable-Trait. Defaults to `App\Models`.
* `prefix`: route prefix. Defaults to `api`
* `auth`: the basic auth for SX. Defaults to env variables `SX_USERNAME` and `SX_PASSWORD`.
* `defaultUnique`: an array of unique keys inside the SX database. Defaults to `['respondentid']`.
* `primary`: the primary key inside the SX database. Defaults to `respondentid`.
* `api`: a JSON representation of the SX API. 

## Compatibility

Tested with Laravel 8.x.

## License

See [License File](license.md). Copyright © 2021 Jan Bladt.