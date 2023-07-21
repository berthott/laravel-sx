<?php

namespace berthott\SX\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class QueryCollectException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct('No query parameter provided');
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
        return response()->json(['custom_error' => 'no_query_collect_parameter'], 400);
    }
}
