<?php

namespace App\Lib;

class ApiWrapper
{
    public static function sendResponse(array $result, string $message, int $code = 200)
    {
        $response = [
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }
}
