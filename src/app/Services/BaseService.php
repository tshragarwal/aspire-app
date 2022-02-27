<?php

namespace App\Services;

use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * class Baseservice
 */

 class BaseService
 {

    /** Send back response to user
     * @param array $data
     * @param string $message
     * @param int $code
     * @param string $status
     * @return \Illuminate\Http\Response
     */
     public function sendReponse($data = [], $message = '', $code = Response::HTTP_OK, $status = 'SUCCESS'): HttpResponse
     {
         $response = [
             'status' => $status,
             'message' => $message,
             'data' => $data
         ];

         return response($response, $code);
     }
 }