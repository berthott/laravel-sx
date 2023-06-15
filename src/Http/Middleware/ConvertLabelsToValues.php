<?php

namespace berthott\SX\Http\Middleware;

use Facades\berthott\SX\Services\SxableService;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Illuminate\Support\Facades\DB;

/**
 * Middleware.
 */
class ConvertLabelsToValues extends TransformsRequest
{
    private string $target;

    public function __construct()
    {
        $this->target = SxableService::getTarget();
    }

    /**
     * Transform the given value.
     * 
     * If the value is a string, and it's key is of type `Single` or `Multiple`
     * the string value will be converted into it's numeric value representation.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        $splittedKey = explode('.', $key);
        $key = $splittedKey[array_key_last($splittedKey)];
        if (is_string($value) && $question = DB::table($this->target::questionsTableName())->where('variableName', $key)->first()) {
            switch ($question->subType) {
                case 'Single':
                case 'Multiple':
                    $found = DB::table($this->target::labelsTableName())
                        ->where('variableName', $key)
                        ->where('label', $value)
                        ->first();
                    $value = $found ? $found->value : $value;
            }
        }

        return $value;
    }
}
