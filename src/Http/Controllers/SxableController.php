<?php

namespace berthott\SX\Http\Controllers;

use berthott\SX\Exports\SxTableExport;
use berthott\SX\Exports\SxLabeledExport;
use berthott\SX\Exports\SxExportAll;
use berthott\SX\Http\Requests\DestroyManyRequest;
use berthott\SX\Http\Requests\ExportRequest;
use berthott\SX\Http\Requests\ImportRequest;
use berthott\SX\Http\Requests\LabeledRequest;
use berthott\SX\Http\Requests\StoreRequest;
use berthott\SX\Http\Requests\UpdateRequest;
use berthott\SX\Models\Contracts\Targetable;
use berthott\SX\Models\Resources\SxableLabeledResource;
use berthott\SX\Models\Respondent;
use berthott\SX\Models\Traits\Targetable as TraitsTargetable;
use berthott\SX\Services\SxRespondentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;

class SxableController implements Targetable
{
    use TraitsTargetable;

    /**
     * Display a listing of the resource.
     */
    public function index(LabeledRequest $request): Collection | ResourceCollection
    {
        return $request->labeled
            ? SxableLabeledResource::collection($this->target::all())
            : $this->target::all();
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
        $a = $this->formParamsWithShortNames(array_merge_recursive(
            $request->all(),
            Auth::user() ? [
                'form_params' => [
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                ]
            ] : [],
            $this->target::generatedUniqueFieldsParams(),
            ['survey' => $this->target::surveyId()]
        ));
        $respondent = (new SxRespondentService())->createNewRespondent($a);
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
     * update a resource.
     */
    public function update(UpdateRequest $request, int $id): Respondent
    {
        $key = $this->respondent($id)->externalkey();
        $respondent = (new SxRespondentService($key))->updateRespondentAnswers($this->formParamsWithShortNames(array_merge_recursive(
            $request->all(),
            Auth::user() ? [
                'form_params' => [
                    'updated_by' => Auth::user()->id,
                ]
            ] : [],
        )));
        if ($model = $this->target::where([config('sx.primary') => $id])->first()) {
            $model->update(array_merge(
                [
                    'created' => $respondent->createts(),
                    'modified' => $respondent->modifyts(),
                ],
                $this->target::filterFormParams($request->form_params)
            ));
        }
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
     * Destroy a resource.
     */
    public function destroy_many(DestroyManyRequest $request): Response
    {
        $ret = [];
        foreach ($request->ids as $id) {
            $ret[$id] = $this->destroy($id)->getOriginalContent()['response'];
        }
        return response($ret);
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
    public function structure(LabeledRequest $request): Collection | ResourceCollection
    {
        $structure = $request->labeled
            ? $this->target::labeledStructure()
            : $this->target::structure();
        return $structure->filter(function ($entry) {
            return !in_array($entry->variableName, $this->target::excludeFromStructureRoute());
        })->values();
    }

    /**
     * Display the sx labels.
     */
    public function labels(LabeledRequest $request): Collection
    {
        return $request->labeled
            ? $this->target::labeledLabels()
            : $this->target::labels();
    }


    /**
     * Trigger sx import.
     */
    public function import(ImportRequest $request): Collection | ResourceCollection
    {
        return $request->fresh
            ? $this->target::initTables(force: true, labeled: (bool) $request->labeled)
            : $this->target::import((bool) $request->labeled);
    }

    /**
     * Trigger import.
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        $ids = $request->input('ids', []);
        if (empty($request->table)) {
            $fileName = $this->target::entityTableName().'.'.strtolower(config('sx.exportFormat'));
            return Excel::download(new SxExportAll($this->target, $ids), $fileName);
        }
        switch ($request->table) {
            case 'wide_labeled':
                return Excel::download(new SxLabeledExport($this->target, $ids), $this->target::entityTableName().'_labeled.'.strtolower(config('sx.exportFormat')));
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
        return Excel::download(new SxTableExport($tableName, $ids), $fileName);
    }

    /**
     * Map to params to short names
     */
    private function formParamsWithShortNames(array $all): array
    {
        $all['form_params'] = $this->target::mapToShortNames($all['form_params']);
        return $all;
    }
}
