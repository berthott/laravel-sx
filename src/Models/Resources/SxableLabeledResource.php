<?php

namespace berthott\SX\Models\Resources;

use berthott\SX\Facades\SxHelpers;
use Illuminate\Http\Resources\Json\JsonResource;

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
        return SxHelpers::getLabeledResource($this->resource);
    }
}
