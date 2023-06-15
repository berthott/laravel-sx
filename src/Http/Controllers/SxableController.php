<?php

namespace berthott\SX\Http\Controllers;

use berthott\SX\Exports\SxTableExport;
use berthott\SX\Exports\SxLabeledExport;
use berthott\SX\Exports\SxExportAll;
use berthott\SX\Facades\Sxable;
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

class SxableController
{
    private string $target;

    public function __construct()
    {
        $this->target = Sxable::getTarget();
    }

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
    public function create_respondent(StoreRequest $request): Respondent
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
                // only fill with sync
                /* 'created' => $respondent->createts(),
                'modified' => $respondent->modifyts(), */
            ],
            $this->target::filterFormParams($request->form_params)
        ));
        return $respondent;
    }

    /**
     * update a resource.
     */
    public function update_respondent(UpdateRequest $request, int $id): Respondent
    {
        $key = $this->show_respondent($id)->externalkey();
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
     * Display the sx show_respondent data.
     */
    public function show_respondent(int $id): Respondent
    {
        return (new SxRespondentService($id))->getRespondent();
    }

    /**
     * Display the structure.
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
     * Display the sx labels.
     */
    public function labels(LabeledRequest $request): Collection
    {
        return $request->labeled
            ? $this->target::labeledLabels($request->lang)
            : $this->target::labels($request->lang);
    }


    /**
     * Trigger sx import.
     */
    public function sync(ImportRequest $request): Collection | ResourceCollection
    {
        return $this->target::import((bool) $request->labeled, (bool) $request->fresh, $request->since);
    }

    /**
     * Trigger export.
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
     */
    public function report(LabeledRequest $request): array
    {
        return SxReportService::get($this->target);
    }

    /**
     * Get survey languages.
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
