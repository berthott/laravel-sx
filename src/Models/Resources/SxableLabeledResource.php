<?php

namespace berthott\SX\Models\Resources;

use berthott\SX\Facades\SxHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class SxableLabeledResource extends JsonResource
{
    /**
     * Transform the resource into a labeled array.
     *
     * If the request is an export request, use the excludeFromExportFeature
     * @see \berthott\SX\Helpers\SxHelpers::getLabeledResource()
     */
    public function toArray(Request $request): array
    {
        return SxHelpers::getLabeledResource($this->resource, Str::contains($request->url(), 'export'));
    }
}
