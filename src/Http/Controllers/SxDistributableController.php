<?php

namespace berthott\SX\Http\Controllers;

use berthott\SX\Facades\SxDistributable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Barryvdh\DomPDF\Facade\Pdf;

class SxDistributableController
{
    private string $target;

    public function __construct()
    {
        $this->target = SxDistributable::getTarget();
    }

    /**
     * Collect the target.
     */
    public function sxcollect(mixed $id)
    {
        return Redirect::to($this->target::findOrFail($id)->collect()->collecturl());
    }

    /**
     * Get the QR Code for the collect url.
     */
    public function qrcode(mixed $id)
    {
        return ['data' => $this->target::findOrFail($id)->qrCode()];
    }

    /**
     * Get the QR Code for the collect url.
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
     */
    public function sxdata(mixed $id, Request $request)
    {
        $query = $request->query();
        return $this->target::findOrFail($id)->sxData($query);
    }
}
