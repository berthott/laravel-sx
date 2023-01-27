<?php

namespace berthott\SX\Models\Traits;

use berthott\SX\Events\RespondentsImported;
use berthott\SX\Facades\SxHelpers;
use berthott\SX\Facades\SxLog;
use berthott\SX\Models\Resources\SxableLabeledResource;
use berthott\SX\Models\SxMode;
use berthott\SX\Observers\SxableObserver;
use berthott\SX\Services\SxSurveyService;
use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;

const MAX_SQL_PLACEHOLDERS = 60000;
const MAX_MEMORY_CHUNK_SIZE = 100;
const LONG_TABLE_COLUMN_COUNT = 8;

trait Sxable
{
    public function getKeyName(): string
    {
        return config('sx.primary');
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Bootstrap services.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::$unguarded = true;
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
     * The fields that should be excluded from the structure.
     */
    public static function excludeFromStructureRoute(): array
    {
        return [];
    }

    /**
     * Defines unique fields with 'field' => 'key'.
     * The key defines a string that is appended by a number.
     */
    public static function generatedUniqueFields(): array
    {
        return [];
    }

    /**
     * The fields that should be unique.
     */
    public static function uniqueFields(): array
    {
        return config('sx.defaultUnique');
    }

    /**
     * Returns the structure of the current entity.
     */
    public static function structure(): Collection
    {
        return Schema::hasTable(static::structureTableName()) ? DB::table(static::structureTableName())->get() : collect();
    }

    /**
     * Returns the labeled structure.
     */
    public static function labeledStructure(): Collection
    {
        return static::structure()->map(function ($entry) {
            if ($entry->subType === 'Multiple') {
                $entry->variableName = $entry->variableName.' - '.DB::table(static::questionsTableName())->where('variableName', $entry->variableName)->first()->choiceText;
            }
            return $entry;
        });
    }

    /**
     * Returns the labeled structure + id and timestamps.
     */
    public static function labeledAttributes(): array
    {
        $questions = DB::table(static::questionsTableName())->get()->keyBy('variableName');
        $ret = [];
        foreach (SxHelpers::getSortedColumns(static::entityTableName()) as $variableName) {
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
        $id = static::surveyId();
        return static::$_sxController ?: static::$_sxController = new SxSurveyService(static::surveyId());
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
        return static::singleName().'_structure';
    }

    /**
     * The long table name of the model.
     */
    public static function longTableName(): string
    {
        return Str::plural(static::singleName()).'_long';
    }

    /**
     * The labels table name of the model.
     */
    public static function labelsTableName(): string
    {
        return static::singleName().'_labels';
    }

    /**
     * The questions table name of the model.
     */
    public static function questionsTableName(): string
    {
        return static::singleName().'_questions';
    }

    /**
     * Initialize the sxable tables.
     */
    public static function initTables(bool $force = false, bool $labeled = false, int $max = null): Collection | ResourceCollection
    {
        static::initStructureTable($force);
        static::initEntityTable($force, $max);
        static::initLabelsTable($force);
        static::initQuestionsTable($force);
        return $labeled
            ? SxableLabeledResource::collection(static::all())
            : static::all();
    }

    /**
     * Drop the sxable tables.
     */
    public static function dropTables(): void
    {
        static::dropTable(static::entityTableName());
        static::dropTable(static::longTableName());
        static::dropTable(static::structureTableName());
        static::dropTable(static::labelsTableName());
        static::dropTable(static::questionsTableName());
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
        $table = static::structureTableName();
        static::initTable($table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('variableName');
            $table->string('subType');
            $table->timestamps();
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            SxLog::log("$table: Filling table.");
            DB::table($table)->insert(static::entityStructure()->all());
            SxLog::log("$table: Table filled.");
        }
    }

    /**
     * Initialize the entity table.
     */
    public static function initEntityTable(bool $force = false, int $max = null): void
    {
        static::initLongTable($force); // before entity because it will write to long

        $tableName = static::entityTableName();
        static::initTable($tableName, function (Blueprint $table) {
            $entityStructure = static::structure();
            $t = null;
            foreach ($entityStructure as $column) {
                if (!in_array($column->variableName, static::fields())) {
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
                        $t = in_array($column->variableName, static::uniqueFields())
                            ? $table->string($column->variableName)
                            : $table->text($column->variableName);
                        break;
                    case 'Date':
                        $t = $table->dateTime($column->variableName);
                        break;
                }
                if ($column->variableName === config('sx.primary')) {
                    $t->primary();
                } elseif (
                    in_array($column->variableName, static::uniqueFields()) ||
                    in_array($column->variableName, config('sx.defaultUnique'))
                ) {
                    $t->unique();
                } else {
                    $t->nullable();
                }
            }
            $table->timestamps();
        }, $force);

        if (static::all()->isEmpty()) {
            SxLog::log("$tableName: Filling table.");
            $entries = static::entities();
            $entries = $max ? $entries->take($max) : $entries;
            static::doUpsert($entries);
            SxLog::log("$tableName: Table filled.");
        }
    }

    /**
     * Initialize the long table.
     */
    public static function initLongTable(bool $force = false): void
    {
        static::initTable(static::longTableName(), function (Blueprint $table) {
            $table->primary(['respondent_id', 'variableName'], static::longTableName().'_primary');
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
        $table = static::labelsTableName();
        static::initTable($table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('variableName');
            $table->integer('value');
            $table->string('label');
            $table->timestamp('created_at')->useCurrent();
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            SxLog::log("$table: Filling table.");
            DB::table($table)->insert(static::labels()->all());
            SxLog::log("$table: Table filled.");
        }
    }

    /**
     * Initialize the questions table.
     */
    public static function initQuestionsTable(bool $force = false): void
    {
        $table = static::questionsTableName();
        static::initTable($table, function (Blueprint $table) {
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
            DB::table($table)->insert(static::questions()->all());
            SxLog::log("$table: Table filled.");
        }
    }

    /**
     * The fields to be processed.
     */
    public static function fields(): array
    {
        return isset(static::$_fields) ? static::$_fields : static::$_fields = static::buildFields();
    }

    /**
     * bUILD The fields to be processed.
     */
    private static function buildFields(): array
    {
        $allFields = static::controller()->getEntityStructure()->pluck('variableName')->all();
        $filteredFields = $allFields;
        if (!empty(static::include())) {
            $filteredFields = array_intersect($allFields, static::include());
        } elseif (!empty(static::exclude())) {
            $filteredFields = array_diff($allFields, static::exclude());
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
            return in_array($param, static::fields());
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * The entities mapped to the fields.
     */
    private static function entities(array $query = []): Collection
    {
        return static::controller()->getEntities($query)->map(function ($entity) {
            return array_intersect_key($entity, array_fill_keys(static::fields(), ''));
        });
    }

    /**
     * The questions mapped to the fields.
     */
    public static function questions(): Collection
    {
        return static::trimMultipleChoiceLabelsForJavaScript(static::filterByFields(static::controller()->getQuestions()));
    }

    /**
     * All possible question names.
     */
    public static function questionNames(): array
    {
        return static::questions()->pluck('questionName')->unique()->values()->toArray();
    }
    
    private static function trimMultipleChoiceLabelsForJavaScript(Collection $collection): Collection
    {
        return $collection->map(function ($question) {
            if ($question['subType'] === 'Multiple') {
                $question['choiceText'] = rtrim($question['choiceText'], " \t\n\r\0\x0B,.:");
            }
            return $question;
        });
    }

    /**
     * The labels mapped to the fields.
     */
    public static function labels(): Collection
    {
        return static::filterByFields(static::controller()->getLabels());
    }

    /**
     * Returns the labeled structure + id and timestamps.
     */
    public static function labeledLabels(): Collection
    {
        $questions = DB::table(static::questionsTableName())->get()->keyBy('variableName');
        return static::labels()->map(function ($entry) use ($questions) {
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
        return static::filterByFields(static::controller()->getEntityStructure());
    }

    private static function filterByFields(Collection $collection): Collection
    {
        return $collection->filter(function ($entry) {
            return in_array($entry['variableName'], static::fields());
        });
    }

    public static function import(bool $labeled = false, bool $fresh = false, string $since = null): Collection | ResourceCollection
    {
        SxLog::log(static::entityTableName().': Import triggered.');
        $entries = static::entities($fresh ? [] : static::lastImport($since));
        static::doUpsert($entries);
        SxLog::log(static::entityTableName().': Import finished.');

        // return the imported entries from our database
        $imported = static::whereIn(config('sx.primary'), $entries->pluck(config('sx.primary'))->toArray())->get();

        if ($imported->count()) {
            event(new RespondentsImported(get_called_class()));
        }

        return $labeled
            ? SxableLabeledResource::collection($imported)
            : $imported;
    }

    /**
     * Return a last import as query array.
     */
    private static function lastImport(string $since = null): array
    {
        $lastImportArray = [];
        $lastRespondent = static::orderBy('modified', 'desc')->first();
        if (isset($lastRespondent)) {
            $modified = (new Carbon($lastRespondent['modified']));
            if ($since) {
                if (static::isAbsoluteTime($since)) {
                    $modified = new Carbon($since);
                } else {
                    $modified->sub($since);
                }
                SxLog::log("Import forced since $modified.");
            } else {
                SxLog::log("The last respondent import was modified at $modified.");
            }
            $lastImportArray['modifiedSince'] = $modified->format('Ymd_His');
        } else {
            SxLog::log('There were no previous respondents.');
        }
        return $lastImportArray;
    }

    private static function isAbsoluteTime($time_string)
    {
        $time_shift = time() + 60; // 1 min from now

        $time_normal = strtotime($time_string);
        $time_shifted = strtotime($time_string, $time_shift);

        return $time_normal == $time_shifted;
    }

    /**
     * Return a last import as query array.
     */
    public static function mapToShortNames(array $fullNames): array
    {
        $ret = [];
        foreach ($fullNames as $name => $value) {
            $ret[static::controller()->guessShortVariableName($name)] = $value;
        }
        return $ret;
    }

    /**
     * Return a unique value for that column.
     */
    public static function generateUniqueValue(string $column): string
    {
        if (!array_key_exists($column, static::generatedUniqueFields())) {
            return '';
        }
        $previousId = (int) filter_var(DB::table(static::entityTableName())->select($column)->orderBy($column)->get()->last()->$column, FILTER_SANITIZE_NUMBER_INT);
        return static::generatedUniqueFields()[$column].Str::padLeft(++$previousId, 3, '0');
    }

    /**
     * Return a an array with generated background variables for sx.
     */
    public static function generatedUniqueFieldsParams(): array
    {
        $ret = ['form_params' => []];
        foreach (static::generatedUniqueFields() as $field => $val) {
            $val = static::generateUniqueValue($field);
            if (!empty($val)) {
                $ret['form_params'][$field] = $val;
            }
        }
        return $ret;
    }

    /**
     * Perform an upsert in chunks. Attention: No events will be emitted + updated_at will alway be updated.
     */
    private static function doUpsert(Collection $entries)
    {
        $count = $entries->count();
        SxLog::log(static::entityTableName().": Importing $count respondents...");
        // wide table
        $wideChunkSize = floor(MAX_SQL_PLACEHOLDERS / static::structure()->count());
        $entries->chunk($wideChunkSize)->each(function (Collection $chunk) use ($count) {
            static::upsert($chunk->all(), [config('sx.primary')]);
        });

        // long table
        $entries->chunk(MAX_MEMORY_CHUNK_SIZE)->each(function (Collection $memoryChunk) use ($count) {
            $longEntries = $memoryChunk->reduce(function (Collection $reduced, $entry) {
                return $reduced->push(...static::makeLongEntries($entry));
            }, collect());
            $longChunkSize = floor(MAX_SQL_PLACEHOLDERS / LONG_TABLE_COLUMN_COUNT);
            $longEntries->chunk($longChunkSize)->each(function (Collection $chunk) use ($count) {
                DB::table(static::longTableName())->upsert($chunk->toArray(), ['respondent_id', 'variableName']);
            });
        });

        SxLog::log(static::entityTableName().": $count respondents imported (".$entries->pluck(config('sx.primary'))->join(', ').')');
    }

    /**
     * Make long table entries for a single model.
     */
    public static function makeLongEntries(array $attributes): array
    {
        $entries = [];
        $structure = DB::table(static::structureTableName())->get()->mapWithKeys(function ($entry) {
            return [$entry->variableName => $entry->subType];
        });
        foreach ($attributes as $variableName => $value) {
            if (in_array($variableName, ['id', config('sx.primary'), 'created_at', 'updated_at'])) {
                continue;
            }
            $entry = [
                'respondent_id' => $attributes[config('sx.primary')],
                'variableName' => $variableName,
                'value_single_multiple' => null,
                'value_double' => null,
                'value_string' => null,
                'value_datetime' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            switch ($structure[$variableName]) {
                case 'Single':
                case 'Multiple':
                    $entry['value_single_multiple'] = $value;
                    break;
                case 'Double':
                    $entry['value_double'] = $value;
                    break;
                case 'String':
                    $entry['value_string'] = $value;
                    break;
                case 'Date':
                    $entry['value_datetime'] = $value;
                    break;
            }
            array_push($entries, $entry);
        }
        return $entries;
    }

    /**
     * Returns an array of query builder options.
     * See https://spatie.be/docs/laravel-query-builder/v3/introduction
     * Options are: filter, fields.
     */
    public static function reportQueryOptions(): array
    {
        return [];
    }

    /**
     * Seed dummy data for the model
     */
    public static function truncateData()
    {
        $tableName = static::entityTableName();
        SxLog::log("$tableName: Truncate wide + long table.");
        DB::table($tableName)->truncate();
        DB::table(static::longTableName())->truncate();
    }

    /**
     * Seed dummy data for the model
     */
    public static function seedDummyData(int $count, array $override = [], $truncate = true)
    {
        if ($truncate) {
            static::truncateData();
        }
        $tableName = static::entityTableName();
        SxLog::log("$tableName: Filling table with $count dummy entries...");
        $faker = FakerFactory::create();
        $labels = static::labels();
        $entries = collect();
        foreach(range(1, $count) as $index) {
            $entries->push(static::generateDummyEntry($faker, $labels, $override));
        }
        static::doUpsert($entries);
        SxLog::log("$tableName: Table filled.");
    }

    /**
     * Seed dummy data for the model
     */
    private static function generateDummyEntry(Faker $faker, Collection $labels, array $override): array
    {
        $entry = [
            config('sx.primary') => $faker->randomNumber(9),
        ];
        foreach(static::questions() as $question) {
            if (array_key_exists($question['variableName'], $override)) {
                $entry[$question['variableName']] = $override[$question['variableName']];
                continue;
            }
            if ($question['variableName'] === config('sx.primary')) {
                continue;
            }
            switch ($question['subType']) {
                case 'Single':
                case 'Multiple':
                    $entry[$question['variableName']] = $faker->randomElement($labels->where('variableName', $question['variableName'])->unique()->pluck('value')->toArray());
                    break;
                case 'Double':
                    $entry[$question['variableName']] = $faker->randomNumber(2);
                    break;
                case 'Date':
                    $entry[$question['variableName']] = $faker->date();
                    break;
                case 'String':
                default:
                    $entry[$question['variableName']] = $faker->sentence();
                    break;
            }
            
        }
        return $entry;
    }
}
