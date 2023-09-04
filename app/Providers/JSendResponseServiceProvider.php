<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class JSendResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Response::macro('jsend', function ($status, $data = null, $message = null) {
            $response = [
                'status' => $status,
            ];

            if ($data) {
                $response['data'] = $data;
            }

            if ($message) {
                $response['message'] = $message;
            }

            return response()->json($response);
        });
    }
}
