<?php

namespace berthott\SX\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class PdfReportException extends Exception
{
    /**
     * The error.
     */
    private array $error;

    /**
     * Create a new exception instance.
     */
    public function __construct(array $error)
    {
        parent::__construct('Building the PDF Report failed');

        $this->error = $error;
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(/* Request $request */): JsonResponse
    {
        return response()->json(['custom_error' => $this->error], 400);
    }
}
