<?php

namespace berthott\SX\Http\Controllers;

use berthott\SX\Models\Contracts\Targetable;
use berthott\SX\Models\Respondent;
use berthott\SX\Models\Traits\Targetable as TraitsTargetable;
use berthott\SX\Services\SxRespondentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class SxableController implements Targetable
{
    use TraitsTargetable;

    /**
     * Display a listing of the resource.
     */
    public function index(): Collection
    {
        return $this->target::all();
    }

    /**
     * Display a single resource.
     */
    public function show(int $id): Respondent
    {
        return (new SxRespondentService($id))->getRespondent();
    }

    /**
     * Store a resource.
     */
    public function store(Request $request): Respondent
    {
        return (new SxRespondentService())->createNewRespondent(array_merge(
            $request->all(),
            ['survey' => $this->target::surveyId()]
        ));
    }
}
