<?php

namespace App\Lib;

class ApiWrapper
{
    public static function sendResponse(array $result, string $message, int $code = 200,$relay_state = null): \Illuminate\Http\JsonResponse
    {
        $response = [
            'data' => $result,
            'message' => $message,
        ];
        if(!is_null($relay_state)){
            $response['relay'] = $relay_state;
        }

        return response()->json($response, $code);
    }
}
