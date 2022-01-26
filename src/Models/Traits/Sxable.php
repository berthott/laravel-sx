<?php

namespace berthott\SX\Models\Traits;

use berthott\SX\Facades\SxLog;
use berthott\SX\Models\Resources\SxableLabeledResource;
use berthott\SX\Models\SxMode;
use berthott\SX\Observers\SxableObserver;
use berthott\SX\Services\SxSurveyService;
use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait Sxable
{

    /**
     * Bootstrap services.
     */
    protected static function boot(): void
    {
        parent::boot();

        self::$unguarded = true;
        // observe Sxable
        static::observe(SxableObserver::class);
    }

    /**
     * The Survey Id that should be connected to this Model.
     */
    public static function surveyId(): string
    {
        return '';
    }

    /**
     * The format in which the data should be stored
     */
    public static function format(): string
    {
        return SxMode::Entity;
    }

    /**
     * The fields that should be excluded from being processed.
     * Will be ignored when include is set
     */
    public static function exclude(): array
    {
        return [];
    }

    /**
     * The fields that should be processed.
     */
    public static function include(): array
    {
        return [];
    }

    /**
     * The fields that should be processed.
     */
    public static function unique(): array
    {
        return config('sx.defaultUnique');
    }

    /**
     * Returns an array of route options.
     * See Route::apiResource documentation.
     */
    public static function routeOptions(): array
    {
        return [];
    }

    /**
     * Returns the structure of the current entity.
     */
    public static function structure(): Collection
    {
        return Schema::hasTable(self::structureTableName()) ? DB::table(self::structureTableName())->get() : collect();
    }

    /**
     * Returns the labeled structure.
     */
    public static function labeledStructure(): Collection
    {
        return self::structure()->map(function ($entry) {
            if ($entry->subType === 'Multiple') {
                $entry->variableName = $entry->variableName.' - '.DB::table(self::questionsTableName())->where('variableName', $entry->variableName)->first()->choiceText;
            }
            return $entry;
        });
    }

    /**
     * Returns the labeled structure + id and timestamps.
     */
    public static function labeledAttributes(): array
    {
        $questions = DB::table(self::questionsTableName())->get()->keyBy('variableName');
        $ret = [];
        foreach (Schema::getColumnListing(self::entityTableName()) as $variableName) {
            if ($questions->has($variableName) && $questions[$variableName]->subType === 'Multiple') {
                $variableName = $variableName.' - '.$questions[$variableName]->choiceText;
            }
            array_push($ret, $variableName);
        }
        return $ret;
    }

    /**
     * The fields to be processed.
     */
    private static $_fields;

    /**
     * The controller service.
     */
    private static $_sxController;

    /**
     * The survey controller
     */
    public static function controller(): SxSurveyService
    {
        return self::$_sxController ?: self::$_sxController = new SxSurveyService(self::surveyId());
    }

    /**
     * The single name of the model.
     */
    public static function singleName(): string
    {
        return Str::snake(class_basename(get_called_class()));
    }

    /**
     * The entity table name of the model.
     */
    public static function entityTableName(): string
    {
        return Str::snake(Str::pluralStudly(class_basename(get_called_class())));
    }

    /**
     * The structure table name of the model.
     */
    public static function structureTableName(): string
    {
        return self::singleName().'_structure';
    }

    /**
     * The long table name of the model.
     */
    public static function longTableName(): string
    {
        return Str::plural(self::singleName()).'_long';
    }

    /**
     * The labels table name of the model.
     */
    public static function labelsTableName(): string
    {
        return self::singleName().'_labels';
    }

    /**
     * The questions table name of the model.
     */
    public static function questionsTableName(): string
    {
        return self::singleName().'_questions';
    }

    /**
     * Initialize the sxable tables.
     */
    public static function initTables(bool $force = false, bool $labeled = false): Collection | ResourceCollection
    {
        self::initStructureTable($force);
        self::initEntityTable($force);
        self::initLabelsTable($force);
        self::initQuestionsTable($force);
        return $labeled
            ? SxableLabeledResource::collection(static::all())
            : static::all();
    }

    /**
     * Drop the sxable tables.
     */
    public static function dropTables(): void
    {
        self::dropTable(self::entityTableName());
        self::dropTable(self::longTableName());
        self::dropTable(self::structureTableName());
        self::dropTable(self::labelsTableName());
        self::dropTable(self::questionsTableName());
    }

    /**
     * Drop the sxable tables.
     */
    private static function dropTable(string $table): void
    {
        SxLog::log("$table: Table dropped.");
        Schema::dropIfExists($table);
    }

    /**
     * Initialize the a table with the given name and closure.
     */
    private static function initTable(string $name, Closure $callback, bool $force = false): void
    {
        if ($force) {
            SxLog::log("$name: Table dropped.");
            Schema::dropIfExists($name);
        }

        if (!Schema::hasTable($name)) {
            SxLog::log("$name: Creating table.");
            Schema::create($name, $callback);
            SxLog::log("$name: Table created.");
        }
    }

    /**
     * Initialize the structure table.
     */
    public static function initStructureTable(bool $force = false): void
    {
        $table = self::structureTableName();
        self::initTable($table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('variableName');
            $table->string('subType');
            $table->timestamps();
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            SxLog::log("$table: Filling table.");
            DB::table($table)->insert(self::entityStructure()->all());
            SxLog::log("$table: Table filled.");
        }
    }

    /**
     * Initialize the entity table.
     */
    public static function initEntityTable(bool $force = false): void
    {
        self::initLongTable($force); // before entity because it will write to long
        
        $table = self::entityTableName();
        self::initTable($table, function (Blueprint $table) {
            $entityStructure = self::structure();
            $t = null;
            $table->bigIncrements('id');
            foreach ($entityStructure as $column) {
                if (!in_array($column->variableName, self::fields())) {
                    continue;
                }
                switch ($column->subType) {
                    case 'Single':
                    case 'Multiple':
                        $t = $table->integer($column->variableName);
                        break;
                    case 'Double':
                        $t = $table->double($column->variableName);
                        break;
                    case 'String':
                        $t = $table->text($column->variableName);
                        break;
                    case 'Date':
                        $t = $table->dateTime($column->variableName);
                        break;
                }
                if (in_array($column->variableName, self::unique())) {
                    $t->unique();
                } else {
                    $t->nullable();
                }
            }
            $table->timestamps();
        }, $force);

        if (self::all()->isEmpty()) {
            SxLog::log("$table: Filling table.");
            //self::upsert(self::entities()->all(), [config('sx.primary')]);
            $entities = self::entities()->all();
            $count = count($entities);
            foreach ($entities as $index => $entity) {
                $index++;
                SxLog::log("[$index/$count] Creating respondent: ".$entity[config('sx.primary')]);
                self::create($entity);
            }
            SxLog::log("$table: Table filled.");
        }
    }

    /**
     * Initialize the long table.
     */
    public static function initLongTable(bool $force = false): void
    {
        $table = self::longTableName();
        self::initTable($table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('respondent_id');
            $table->string('variableName');
            $table->integer('value_single_multiple')->nullable();
            $table->text('value_string')->nullable();
            $table->double('value_double')->nullable();
            $table->dateTime('value_datetime')->nullable();
            $table->timestamps();
        }, $force);
    }

    /**
     * Initialize the labels table.
     */
    public static function initLabelsTable(bool $force = false): void
    {
        $table = self::labelsTableName();
        self::initTable($table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('variableName');
            $table->integer('value');
            $table->string('label');
            $table->timestamp('created_at')->useCurrent();
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            SxLog::log("$table: Filling table.");
            DB::table($table)->insert(self::labels()->all());
            SxLog::log("$table: Table filled.");
        }
    }

    /**
     * Initialize the questions table.
     */
    public static function initQuestionsTable(bool $force = false): void
    {
        $table = self::questionsTableName();
        self::initTable($table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('questionName');
            $table->string('variableName');
            $table->text('questionText');
            $table->string('subType');
            $table->integer('choiceValue')->nullable();
            $table->string('choiceText')->nullable();
            $table->timestamp('created_at')->useCurrent();
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            SxLog::log("$table: Filling table.");
            DB::table($table)->insert(self::questions()->all());
            SxLog::log("$table: Table filled.");
        }
    }

    /**
     * The fields to be processed.
     */
    private static function fields(): array
    {
        return isset(self::$_fields) ? self::$_fields : self::$_fields = self::buildFields();
    }

    /**
     * bUILD The fields to be processed.
     */
    private static function buildFields(): array
    {
        $allFields = self::controller()->getEntityStructure()->pluck('variableName')->all();
        $filteredFields = $allFields;
        if (!empty(self::include())) {
            $filteredFields = array_intersect($allFields, self::include());
        } elseif (!empty(self::exclude())) {
            $filteredFields = array_diff($allFields, self::exclude());
        }
        return array_filter($filteredFields, function ($field) {
            $doFiler = true;
            foreach (config('sx.filters') as $filter) {
                if (str_starts_with($field, $filter)) {
                    $doFiler = false;
                    break;
                }
            }
            return $doFiler;
        });
    }

    /**
     * The formparams filtered to the fields.
     */
    public static function filterFormParams(array $formParams): array
    {
        return array_filter($formParams, function ($param) {
            return in_array($param, self::fields());
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * The entities mapped to the fields.
     */
    private static function entities(array $query = []): Collection
    {
        return self::controller()->getEntities($query)->map(function ($entity) {
            return array_intersect_key($entity, array_fill_keys(self::fields(), ''));
        });
    }

    /**
     * The questions mapped to the fields.
     */
    private static function questions(): Collection
    {
        return self::filterByFields(self::controller()->getQuestions());
    }

    /**
     * The labels mapped to the fields.
     */
    public static function labels(): Collection
    {
        return self::filterByFields(self::controller()->getLabels());
    }

    /**
     * Returns the labeled structure + id and timestamps.
     */
    public static function labeledLabels(): Collection
    {
        $questions = DB::table(self::questionsTableName())->get()->keyBy('variableName');
        return self::labels()->map(function ($entry) use ($questions) {
            $variableName = $entry['variableName'];
            if ($questions->has($variableName) && $questions[$variableName]->subType === 'Multiple') {
                $entry['variableName'] = $variableName.' - '.$questions[$variableName]->choiceText;
            }
            return $entry;
        });
    }

    /**
     * The labels mapped to the fields.
     */
    private static function entityStructure(): Collection
    {
        return self::filterByFields(self::controller()->getEntityStructure());
    }

    private static function filterByFields(Collection $collection): Collection
    {
        return $collection->filter(function ($entry) {
            return in_array($entry['variableName'], self::fields());
        });
    }

    public static function import(bool $labeled = false): Collection | ResourceCollection
    {
        SxLog::log(self::entityTableName().': Import triggered.');
        $entries = self::entities(self::lastImport());
        //self::upsert($entries->all(), [config('sx.primary')]);
        $count = count($entries);
        foreach ($entries as $index => $entry) {
            $index++;
            if ($model = self::where(config('sx.primary'), $entry[config('sx.primary')])->first()) {
                SxLog::log("[$index/$count] Updating respondent: ".$entry[config('sx.primary')]);
                $model->update($entry);
            } else {
                SxLog::log("[$index/$count] Creating respondent: ".$entry[config('sx.primary')]);
                self::create($entry);
            }
        }
        SxLog::log(self::entityTableName().': Import finished.');
        
        // return the imported entries from our database
        $imported = static::whereIn(config('sx.primary'), $entries->pluck(config('sx.primary'))->toArray())->get();
        return $labeled
            ? SxableLabeledResource::collection($imported)
            : $imported;
    }

    /**
     * Return a last import as query array.
     */
    private static function lastImport(): array
    {
        $lastImportArray = [];
        $lastRespondent = self::latest()->first();
        if (isset($lastRespondent)) {
            $lastImport = date('Ymd_His', strtotime($lastRespondent->created_at));
            SxLog::log("The last respondent import was $lastRespondent->created_at.");
            $lastImportArray['modifiedSince'] = $lastImport;
        } else {
            SxLog::log('There were no previous respondents.');
        }
        return $lastImportArray;
    }

    /**
     * Return a last import as query array.
     */
    public static function mapToShortNames(array $fullNames): array
    {
        $ret = [];
        foreach ($fullNames as $name => $value) {
            $ret[self::controller()->guessShortVariableName($name)] = $value;
        }
        return $ret;
    }
}
