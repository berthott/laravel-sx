<?php

namespace berthott\SX\Http\Controllers;

use Facades\berthott\SX\Services\SxDistributableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Barryvdh\DomPDF\Facade\Pdf;
use berthott\SX\Exceptions\QueryCollectException;
use berthott\SX\Http\Requests\QueryCollectRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * AxDistributable API endpoint implementation.
 */
class SxDistributableController
{
    private string $target;

    public function __construct()
    {
        $this->target = SxDistributableService::getTarget();
    }

    /**
     * Collect the target via the id.
     * 
     * Creates a new SX respondent and redirects to its collect URL.
     * 
     * @see \berthott\SX\Models\Traits\SxDistributable::collect()
     * @api
     */
    public function sxcollect(mixed $id)
    {
        return Redirect::to($this->target::findOrFail($id)->collect()->collecturl());
    }

    /**
     * Preview the target via the id.
     * 
     * Creates a new SX preview respondent and redirects to its collect URL.
     * 
     * @api
     */
    public function preview(mixed $id)
    {
        return $this->target::findOrFail($id)->preview();
    }

    /**
     * Collect the target via a query parameter.
     * 
     * Creates a new SX respondent and redirects to its collect URL.
     * 
     * @see \berthott\SX\Models\Traits\SxDistributable::distributableQueryCollectParams()
     * @throws QueryCollectException
     * @api
     */
    public function sxquerycollect(QueryCollectRequest $request)
    {
        $validated = $request->validated();
        $model = $this->target::where(function ($query) use ($validated) {
            foreach($validated as $param => $value) {
                $query = $query->where($param, $value);
            }
        })->first();
        if (empty($model) || empty($validated)) {
            throw new HttpException(404);
        }
        return Redirect::to($model->collect()->collecturl());
    }

    /**
     * Get the QR Code for the collect url.
     * 
     * @api
     */
    public function qrcode(mixed $id)
    {
        return ['data' => $this->target::findOrFail($id)->qrCode()];
    }

    /**
     * Get the QR Code PDF for the collect url.
     * 
     * @api
     */
    public function pdf(mixed $id)
    {
        $pdf = Pdf::setPaper('a4');
        $instance = $this->target::findOrFail($id);
        $pdf->loadView('sx::pdf.qrcode', [
            'qrcode' => $instance->qrCode(),
            'collecturl' => $instance->collectUrl(),
            ...$instance->sxPdfData(),
        ]);
        return $pdf->stream();
    }

    /**
     * Get the data for the sx survey from the target.
     * 
     * @api
     */
    public function sxdata(mixed $id, Request $request)
    {
        $query = $request->query();
        return $this->target::findOrFail($id)->sxData($query);
    }
}
