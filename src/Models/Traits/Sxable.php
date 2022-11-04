<?php

namespace berthott\SX\Models\Traits;

use berthott\SX\Events\RespondentsImported;
use berthott\SX\Facades\Helpers;
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
        foreach (Helpers::getSortedColumns(self::entityTableName()) as $variableName) {
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
    public static function initTables(bool $force = false, bool $labeled = false, int $max = null): Collection | ResourceCollection
    {
        self::initStructureTable($force);
        self::initEntityTable($force, $max);
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
    public static function initEntityTable(bool $force = false, int $max = null): void
    {
        self::initLongTable($force); // before entity because it will write to long

        $tableName = self::entityTableName();
        self::initTable($tableName, function (Blueprint $table) {
            $entityStructure = self::structure();
            $t = null;
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
                        $t = in_array($column->variableName, self::uniqueFields())
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
                    in_array($column->variableName, self::uniqueFields()) ||
                    in_array($column->variableName, config('sx.defaultUnique'))
                ) {
                    $t->unique();
                } else {
                    $t->nullable();
                }
            }
            $table->timestamps();
        }, $force);

        if (self::all()->isEmpty()) {
            SxLog::log("$tableName: Filling table.");
            $entries = self::entities();
            $entries = $max ? $entries->take($max) : $entries;
            self::doUpsert($entries);
            SxLog::log("$tableName: Table filled.");
        }
    }

    /**
     * Initialize the long table.
     */
    public static function initLongTable(bool $force = false): void
    {
        self::initTable(self::longTableName(), function (Blueprint $table) {
            $table->primary(['respondent_id', 'variableName'], self::longTableName().'_primary');
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
        return self::trimMultipleChoiceLabelsForJavaScript(self::filterByFields(self::controller()->getQuestions()));
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

    public static function import(bool $labeled = false, bool $fresh = false, string $since = null): Collection | ResourceCollection
    {
        SxLog::log(self::entityTableName().': Import triggered.');
        $entries = self::entities($fresh ? [] : self::lastImport($since));
        self::doUpsert($entries);
        SxLog::log(self::entityTableName().': Import finished.');

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
        $lastRespondent = self::orderBy('modified', 'desc')->first();
        if (isset($lastRespondent)) {
            $modified = (new Carbon($lastRespondent['modified']));
            if ($since) {
                if (self::isAbsoluteTime($since)) {
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
            $ret[self::controller()->guessShortVariableName($name)] = $value;
        }
        return $ret;
    }

    /**
     * Return a unique value for that column.
     */
    public static function generateUniqueValue(string $column): string
    {
        if (!array_key_exists($column, self::generatedUniqueFields())) {
            return '';
        }
        $previousId = (int) filter_var(DB::table(self::entityTableName())->select($column)->orderBy($column)->get()->last()->$column, FILTER_SANITIZE_NUMBER_INT);
        return self::generatedUniqueFields()[$column].Str::padLeft(++$previousId, 3, '0');
    }

    /**
     * Return a an array with generated background variables for sx.
     */
    public static function generatedUniqueFieldsParams(): array
    {
        $ret = ['form_params' => []];
        foreach (self::generatedUniqueFields() as $field => $val) {
            $val = self::generateUniqueValue($field);
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
        SxLog::log(self::entityTableName().": Importing $count respondents...");
        // wide table
        $wideChunkSize = floor(MAX_SQL_PLACEHOLDERS / self::structure()->count());
        $entries->chunk($wideChunkSize)->each(function (Collection $chunk) use ($count) {
            self::upsert($chunk->all(), [config('sx.primary')]);
        });

        // long table
        $entries->chunk(MAX_MEMORY_CHUNK_SIZE)->each(function (Collection $memoryChunk) use ($count) {
            $longEntries = $memoryChunk->reduce(function (Collection $reduced, $entry) {
                return $reduced->push(...self::makeLongEntries($entry));
            }, collect());
            $longChunkSize = floor(MAX_SQL_PLACEHOLDERS / LONG_TABLE_COLUMN_COUNT);
            $longEntries->chunk($longChunkSize)->each(function (Collection $chunk) use ($count) {
                DB::table(self::longTableName())->upsert($chunk->toArray(), ['respondent_id', 'variableName']);
            });
        });

        SxLog::log(self::entityTableName().": $count respondents imported (".$entries->pluck(config('sx.primary'))->join(', ').')');
    }

    /**
     * Make long table entries for a single model.
     */
    public static function makeLongEntries(array $attributes): array
    {
        $entries = [];
        $structure = DB::table(self::structureTableName())->get()->mapWithKeys(function ($entry) {
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
}
