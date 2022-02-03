<?php

namespace berthott\SX\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class SxableLabeledResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $ret = [];
        $questions = DB::table($this->resource->questionsTableName())->get()->keyBy('variableName');
        foreach ($this->resource->getAttributes() as $variableName => $value) {
            if (in_array($variableName, config('sx.excludeFromExport'))) {
                continue;
            }
            $header = $variableName;
            if (!$questions->has($variableName)) {
                $ret[$header] = $value;
                continue;
            }
            switch ($questions[$variableName]->subType) {
                case 'Multiple':
                    $header = $variableName.' - '.$questions[$variableName]->choiceText;
                    // no break
                case 'Single':
                    $labelEntry = DB::table($this->resource->labelsTableName())
                        ->where('variableName', $variableName)
                        ->where('value', $value)
                        ->first();
                    $ret[$header] = $labelEntry ? $labelEntry->label : $value;
                    break;
                default:
                    $ret[$header] = $value;
            }
        }
        return $ret;
    }
}
