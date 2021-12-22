<?php

namespace berthott\SX\Http\Controllers;

use berthott\SX\Exports\SxExport;
use berthott\SX\Exports\SxExportAll;
use berthott\SX\Http\Requests\ExportRequest;
use berthott\SX\Http\Requests\ImportRequest;
use berthott\SX\Http\Requests\StoreRequest;
use berthott\SX\Models\Contracts\Targetable;
use berthott\SX\Models\Respondent;
use berthott\SX\Models\Traits\Targetable as TraitsTargetable;
use berthott\SX\Services\SxRespondentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;

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
    public function show(int $id): Model
    {
        return $this->target::where([config('sx.primary') => $id])->first();
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
            [
                config('sx.primary') => $respondent->id(),
                'survey' => $this->target::surveyId(),
                'created' => $respondent->createts(),
                'modified' => $respondent->modifyts(),
            ],
            $this->target::filterFormParams($request->form_params)
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
     * Display the sx respondent data.
     */
    public function respondent(int $id): Respondent
    {
        return (new SxRespondentService($id))->getRespondent();
    }

    /**
     * Display the sx respondent data.
     */
    public function structure(): Collection
    {
        return $this->target::structure();
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

    /**
     * Trigger import.
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        if (empty($request->table)) {
            $fileName = $this->target::entityTableName().'.'.strtolower(config('sx.exportFormat'));
            return Excel::download(new SxExportAll($this->target), $fileName);
        }
        switch ($request->table) {
            case 'wide':
                $tableName = $this->target::entityTableName();
                break;
            case 'long':
                $tableName = $this->target::entityTableName().'_'.$request->table;
                break;
            case 'questions':
            case 'labels':
                $tableName = $this->target::singleName().'_'.$request->table;
                break;
        }
        $fileName = $tableName.'.'.strtolower(config('sx.exportFormat'));
        return Excel::download(new SxExport($tableName), $fileName);
    }
}
