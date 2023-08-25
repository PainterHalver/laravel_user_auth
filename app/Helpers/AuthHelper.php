<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class AuthHelper
{
    public static function getGoogleDiscoverDocument(): array
    {
        try {
            $discovery_document_url = 'https://accounts.google.com/.well-known/openid-configuration';
            return Http::get($discovery_document_url)->json();
        } catch (\Exception $e) {
            abort(500, 'Failed to get Google discovery document.');
        }
    }
}
