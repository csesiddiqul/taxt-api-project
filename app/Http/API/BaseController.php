<?php

namespace App\Http\API;

class BaseController
{
    protected function sendResponse($message = null, $data = null, $status = 200)
    {
        $response = [
            'success' => true,
            'message' => $message ?? "Successful response was returned",
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    protected function sendError($message, $errors = [], $status = 404)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
