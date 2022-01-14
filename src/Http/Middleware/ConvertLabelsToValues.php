<?php

namespace berthott\SX\Http\Middleware;

use berthott\SX\Facades\Sxable;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Illuminate\Support\Facades\DB;

class ConvertLabelsToValues extends TransformsRequest
{
    private string $target;

    public function __construct()
    {
        $this->target = Sxable::getTarget();
    }

    protected function transform($key, $value)
    {
        $splittedKey = explode('.', $key);
        $key = $splittedKey[array_key_last($splittedKey)];
        if (is_string($value) && $question = DB::table($this->target::questionsTableName())->where('variableName', $key)->first()) {
            switch ($question->subType) {
                case 'Single':
                case 'Multiple':
                    $value = DB::table($this->target::labelsTableName())
                        ->where('variableName', $key)
                        ->where('label', $value)
                        ->first()
                        ->value;
            }
        }

        return $value;
    }
}
