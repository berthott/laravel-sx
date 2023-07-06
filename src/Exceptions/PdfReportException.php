<?php

namespace berthott\SX\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class PdfReportException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(private array $error)
    {
        parent::__construct('Building the PDF Report failed');
    }

    /**
     * Render the exception into an HTTP response.
     * 
     * The `custom_error` will be interpreted by the frontend and shown 
     * to the user.
     * 
     * @api
     */
    public function render(/* Request $request */): JsonResponse
    {
        return response()->json(['custom_error' => $this->error], 400);
    }
}
