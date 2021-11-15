<?php

namespace berthott\SX\Http\Controllers;

use berthott\SX\Http\Requests\ImportRequest;
use berthott\SX\Http\Requests\StoreRequest;
use berthott\SX\Models\Contracts\Targetable;
use berthott\SX\Models\Respondent;
use berthott\SX\Models\Traits\Targetable as TraitsTargetable;
use berthott\SX\Services\SxRespondentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

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
    public function store(StoreRequest $request): Respondent
    {
        $respondent = (new SxRespondentService())->createNewRespondent(array_merge(
            $request->all(),
            ['survey' => $this->target::surveyId()]
        ));
        $this->target::create(array_merge(
            [config('sx.primary') => $respondent->id()],
            $request->form_params
        ));
        return $respondent;
    }

    /**
     * Destroy a resource.
     */
    public function destroy(int $id): Response
    {
        if ($model = $this->target::where([config('sx.primary') => $id])->first()) {
            $model->delete();
        }
        return response(['response' => (new SxRespondentService($id))->deleteRespondent()]);
    }

    /**
     * Trigger sx import.
     */
    public function import(ImportRequest $request): Collection
    {
        return $request->fresh
            ? $this->target::initTables(force: true)
            : $this->target::import();
    }
}
