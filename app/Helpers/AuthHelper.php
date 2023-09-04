<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AuthHelper
{
    public static function getAuthEndpoints(): array
    {
        $result = [];

        // GOOGLE
        $google_base = Cache::get('google_base_authorization_endpoint');
        if (! $google_base) {
            // Cache the authorization endpoint from Google
            $google_base = AuthHelper::getGoogleDiscoverDocument()['authorization_endpoint'];
            cache(['google_base_authorization_endpoint' => $google_base], now()->addDays(1));
        }
        $google_auth_endpoint = $google_base
            .'?client_id='.env('OAUTH_GOOGLE_CLIENT_ID')
            .'&response_type=code'
            .'&scope=openid%20email%20profile'
            .'&redirect_uri='.env('OAUTH_GOOGLE_REDIRECT_URL')
            .'&state='.csrf_token();
        $result['google'] = $google_auth_endpoint;

        // GITHUB
        $github_base = 'https://github.com/login/oauth/authorize';
        $github_auth_endpoint = $github_base
            .'?client_id='.env('OAUTH_GITHUB_CLIENT_ID')
            .'&redirect_uri='.env('OAUTH_GITHUB_REDIRECT_URL')
            .'&scope=user%20read:user%20user:email'
            .'&state='.csrf_token();
        $result['github'] = $github_auth_endpoint;

        // TWITTER
        $twitter_auth_endpoint = 'https://twitter.com/i/oauth2/authorize'
            .'?response_type=code'
            .'&client_id='.env('OAUTH_TWITTER_CLIENT_ID')
            .'&redirect_uri='.env('OAUTH_TWITTER_REDIRECT_URL')
            .'&scope=tweet.read%20users.read'
            .'&state='.csrf_token()
            .'&code_challenge='.env('OAUTH_TWITTER_CODE_CHALLENGE')
            .'&code_challenge_method=plain';
        $result['twitter'] = $twitter_auth_endpoint;

        return $result;
    }

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
