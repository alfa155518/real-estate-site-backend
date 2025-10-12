<?php

namespace App\Traits;

trait HandleResponse
{
    public function success($message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
        ], $code);
    }
    public function successData($data = null, $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], $code);
    }

    public function error($message = 'حدث خطأ ما', $code = 500)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    public function notFound($message = 'Not Found', $code = 404)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }
}
