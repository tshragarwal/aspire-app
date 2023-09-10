<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

trait HttpResponses {
    protected function success($data, string $message = null, int $code = HttpFoundationResponse::HTTP_OK) : JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($data, string $message = null, int $code ) : JsonResponse {
        return response()->json([
            'status' => 'failed',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}