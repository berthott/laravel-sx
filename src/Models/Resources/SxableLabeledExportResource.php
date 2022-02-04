<?php

namespace berthott\SX\Models\Resources;

use berthott\SX\Facades\Helpers;
use Illuminate\Http\Resources\Json\JsonResource;

class SxableLabeledExportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return Helpers::getLabeledResource($this->resource, excludeFromExport: true);
    }
}
