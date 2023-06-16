<?php

namespace berthott\SX\Http\Controllers;

use berthott\SX\Exports\SxTableExport;
use berthott\SX\Exports\SxLabeledExport;
use berthott\SX\Exports\SxExportAll;
use Facades\berthott\SX\Services\SxableService;
use Facades\berthott\SX\Services\SxReportService;
use berthott\SX\Http\Requests\DestroyManyRequest;
use berthott\SX\Http\Requests\ExportRequest;
use berthott\SX\Http\Requests\ImportRequest;
use berthott\SX\Http\Requests\LabeledRequest;
use berthott\SX\Http\Requests\StoreRequest;
use berthott\SX\Http\Requests\UpdateRequest;
use berthott\SX\Models\Resources\SxableLabeledResource;
use berthott\SX\Models\Respondent;
use berthott\SX\Services\SxRespondentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use berthott\SX\Exceptions\PdfReportException;
use \Facades\berthott\SX\Services\SxReportPdfService;
use berthott\SX\Http\Requests\SxReportPdfRequest;

/**
 * Sxable API endpoint implementation.
 */
class SxableController
{
    private string $target;

    public function __construct()
    {
        $this->target = SxableService::getTarget();
    }

    /**
     * Display a listing of all entries of the resource.
     * 
     * Possible query parameters are:
     * * *boolean*   **labeled**:    should values be substituted by labels
     * * *string*    **lang**:       the language of the labels
     * 
     * @api
     */
    public function index(LabeledRequest $request): Collection | ResourceCollection
    {
        return $request->labeled
            ? SxableLabeledResource::collection($this->target::all())
            : $this->target::all();
    }

    /**
     * Display a single resource.
     * 
     * @api
     */
    public function show(int $id): Model
    {
        return $this->target::where([config('sx.primary') => $id])->first();
    }

    /**
     * Create a resource.
     * 
     * A new respondent is created in SX and the database simultaneously.
     * For SX API options and validation see {@see \berthott\SX\Http\Requests\StoreRequest}.
     * Additionally to the passed form_params `created_by` and `updated_by`, as well
     * as generated unique fields are added.
     * 
     * The SX values `createts` and `modifyts` won't be stored in our database
     * at this point to be able to identify whether a respondent was already synced
     * from SX. For a tracking up creating and updating through out library
     * see `created_at` and `updated_at`.
     * 
     * @api
     */
    public function create_respondent(StoreRequest $request): Respondent
    {
        // SX expects short names
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
                // only fill with sync
                /* 'created' => $respondent->createts(),
                'modified' => $respondent->modifyts(), */
            ],
            $this->target::filterFormParams($request->form_params)
        ));
        return $respondent;
    }

    /**
     * Update a resource by the given ID.
     * 
     * The respondent is updated in SX and the database simultaneously.
     * For validation see {@see \berthott\SX\Http\Requests\UpdateRequest}.
     * Additionally to the passed form_params `updated_by` is updated.
     * 
     * The SX values `createts` and `modifyts` won't be stored in our database
     * at this point to be able to identify whether a respondent was already synced
     * from SX. For a tracking up creating and updating through out library
     * see `created_at` and `updated_at`.
     * 
     * @api
     */
    public function update_respondent(UpdateRequest $request, int $id): Respondent
    {
        // SX expects a key instead of an id
        $key = $this->show_respondent($id)->externalkey();
        // SX expects short names
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
                // only fill with sync
                /* [
                    'created' => $respondent->createts(),
                    'modified' => $respondent->modifyts(),
                ], */
                $this->target::filterFormParams($request->form_params)
            ));
        }
        return $respondent;
    }

    /**
     * Destroy a resource by the given ID.
     * 
     * @api
     */
    public function destroy(int $id): Response
    {
        if ($model = $this->target::where([config('sx.primary') => $id])->first()) {
            $model->delete();
        }
        return response(['response' => (new SxRespondentService($id))->deleteRespondent()]);
    }

    /**
     * Destroy resources by a given array of IDs.
     * 
     * For validation see {@see \berthott\SX\Http\Requests\DestroyManyRequest}.
     * 
     * @api
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
     * Display the SX show_respondent data.
     * 
     * @see \berthott\SX\Models\Respondent
     * @api
     */
    public function show_respondent(int $id): Respondent
    {
        return (new SxRespondentService($id))->getRespondent();
    }

    /**
     * Display the structure table contents.
     * 
     * Possible query parameters are:
     * * *boolean*   **labeled**:    should values be substituted by labels
     * * *string*    **lang**:       the language of the labels
     * 
     * @api
     */
    public function structure(LabeledRequest $request): Collection | ResourceCollection
    {
        $structure = $request->labeled
            ? $this->target::labeledStructure($request->lang)
            : $this->target::structure();
        return $structure->filter(function ($entry) {
            return !in_array($entry->variableName, $this->target::excludeFromStructureRoute());
        })->values();
    }

    /**
     * Display the labels table contents.
     * 
     * Possible query parameters are:
     * * *boolean*   **labeled**:    should values be substituted by labels
     * * *string*    **lang**:       the language of the labels
     * 
     * @api
     */
    public function labels(LabeledRequest $request): Collection
    {
        return $request->labeled
            ? $this->target::labeledLabels($request->lang)
            : $this->target::labels($request->lang);
    }


    /**
     * Trigger an import.
     * 
     * Possible query parameters are:
     * * *fresh*    **boolean**:    if true all entries will be imported, if false just the latest will be imported
     * * *since*    **string**:     a time stamp or relative time to define the time from which onwards new respondents should be imported from SX
     * * *boolean*  **labeled**:    should values be substituted by labels
     * 
     * @api
     */
    public function sync(ImportRequest $request): Collection | ResourceCollection
    {
        return $this->target::import((bool) $request->labeled, (bool) $request->fresh, $request->since);
    }

    /**
     * Download an export.
     * 
     * Exports a xlsx table.
     * 
     * Possible query parameters are:
     * * *table*    **string**:    the following tables can be exported: **questions**, **labels**, **long**, **wide**, **wide_labeled**. If non is specified all will be exported into one file.
     * * *ids*      **array**:     an array of IDs to be included in the export
     * 
     * @api
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
     * Get report data.     
     * 
     * Possible query parameters are:
     * * *boolean*   **labeled**:    should values be substituted by labels
     * * *string*    **lang**:       the language of the labels
     * 
     * @see \berthott\SX\Services\SxReportService
     * @api
     */
    public function report(LabeledRequest $request): array
    {
        return SxReportService::get($this->target);
    }

    /**
     * Get survey languages.
     * 
     * @api
     */
    public function languages(): array
    {
        return [
            'languages' => $this->target::surveyLanguages(),
            'defaultLanguage' => $this->target::defaultSurveyLanguage(),
        ];
    }

    /**
     * Map to params to short names
     */
    private function formParamsWithShortNames(array $all): array
    {
        $all['form_params'] = $this->target::mapToShortNames($all['form_params']);
        return $all;
    }

    /**
     * Download a report PDF.
     * 
     * Takes an array of pages with base64 image data and renders it into a PDF.
     * 
     * @see \berthott\SX\Http\Requests\SxReportPdfRequest
     * @see resources/reportPDF.blade.php
     * @api
     */
    public function report_pdf(SxReportPdfRequest $request)
    {
        $pages = $request->input('pages');
        $pageLimit = $request->input('pageLimit');
        if ($pageLimit && SxReportPdfService::estimatePages($pages) > $pageLimit) {
            throw new PdfReportException(['pageLimit' => $pageLimit]);
        } 

        return Pdf::setPaper('a4', 'portrait')
            ->setOption([
                'isPhpEnabled' => true,
                'dpi' => 300,
            ])
            ->loadView(
                'sx::pdf.reportPDF',
                [ 
                    'pages' => $pages, 
                ]
            )
            ->stream();
    }
}
