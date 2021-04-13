<?php

namespace App\Http\Controllers\Responses;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public static function sendResponse($response)
    {
        return response()->json($response, 200);
    }


    public static function sendError($error, $code = 404)
    {
        $response = [
            'error' => $error,
        ];
        return response()->json($response, $code);
    }
}
