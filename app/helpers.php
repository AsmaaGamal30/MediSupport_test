<?php

if (!function_exists('responseFormat')) {
    function responseFormat($data = [],   $message,   $status)
    {
        $response = [
            "data" => $data,
            "message" => $message,
            "status" => $status
        ];

        return response()->json(
            $response,
            $status,
        );
    }
}