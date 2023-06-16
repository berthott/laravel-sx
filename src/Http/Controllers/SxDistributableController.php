<?php

namespace berthott\SX\Http\Controllers;

use Facades\berthott\SX\Services\SxDistributableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Barryvdh\DomPDF\Facade\Pdf;

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
     * Collect the target.
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
