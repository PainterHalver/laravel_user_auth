<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Encoding\JoseEncoder;
use App\Models\User;
use App\Models\Profile;

class OAuthController extends Controller
{
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        // 1. Get token endpoint from cache or Google's discovery document
        $token_base_endpoint = cache('google_base_token_endpoint');
        if (!$token_base_endpoint) {
            $token_base_endpoint = AuthHelper::getGoogleDiscoverDocument()['token_endpoint'];
            cache(['google_base_token_endpoint' => $token_base_endpoint], now()->addDays(1));
        }

        // 2. Exchange code for token
        $code = $request['code'];
        $token_response = Http::asForm()->post($token_base_endpoint, [
            'code' => $code,
            'client_id' => env('OAUTH_GOOGLE_CLIENT_ID'),
            'client_secret' => env('OAUTH_GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => env('OAUTH_GOOGLE_REDIRECT_URL'),
            'grant_type' => 'authorization_code',
        ])->json();

        // 3. Parse the token
        try {
            $parser = new Parser(new JoseEncoder());
            $token = $parser->parse($token_response['id_token']);
        } catch (\Exception $e) {
            abort(500, 'Failed to parse token');
        }

        // 4. Create user and profile if they don't exist
        $sub = strval($token->claims()->get('sub'));
        $user = User::where('username', $sub)->first();
        if (!$user) {
            $user = new User([
                'username' => $sub,
                'password' => 'NOT_TO_BE_USED',
            ]);
            $user->save();

            $profile = new Profile([
                'name' => $token->claims()->get('name'),
                'email' => $token->claims()->get('email'),
                'picture' => $token->claims()->get('picture'),
                'provider' => 'google',
            ]);
            $user->profile()->save($profile);
        }

        // 5. Log the user in
        Auth::login($user, true);

        return redirect()->route('home');
    }

    public function handleGithubCallback(Request $request): RedirectResponse
    {
        // 1. Exchange code for token
        $code = $request['code'];
        $access_token_url = 'https://github.com/login/oauth/access_token';
        $token_response = Http::asForm()->withHeader('Accept', 'application/json')
            ->post($access_token_url, [
                'code' => $code,
                'client_id' => env('OAUTH_GITHUB_CLIENT_ID'),
                'client_secret' => env('OAUTH_GITHUB_CLIENT_SECRET'),
                'redirect_uri' => env('OAUTH_GITHUB_REDIRECT_URL'),
        ])->json();
        $access_token = $token_response['access_token'];

        // 2. Get user info
        $user_info_url = 'https://api.github.com/user';
        $user_info_response = Http::withToken($access_token)->get($user_info_url)->json();

        // 3. Create user and profile if they don't exist
        $username_to_use = 'git'.$user_info_response['id'];
        $user = User::where('username', $username_to_use)->first();
        if (!$user) {
            $user = new User([
                'username' => $username_to_use,
                'password' => 'NOT_TO_BE_USED',
            ]);
            $user->save();

            $profile = new Profile([
                'name' => $user_info_response['name'] ?? $user_info_response['login'],
                'email' => $user_info_response['email'],
                'picture' => $user_info_response['avatar_url'],
                'provider' => 'github',
            ]);
            $user->profile()->save($profile);
        }

        // 4. Log the user in
        Auth::login($user, true);

        return redirect()->route('home');
    }

    public function handleTwitterCallback(Request $request): RedirectResponse
    {
        // 1. Handle access denied
        if (isset($request['error']) && $request['error'] === 'access_denied') {
            abort(401, 'Access denied');
        }

        // 2. Exchange code for token
        $code = $request['code'];
        $access_token_url = 'https://api.twitter.com/2/oauth2/token';
        $token_response = Http::withBasicAuth(
            env('OAUTH_TWITTER_CLIENT_ID'),
            env('OAUTH_TWITTER_CLIENT_SECRET')
        )->asForm()->post($access_token_url, [
            'code' => $code,
            'redirect_uri' => env('OAUTH_TWITTER_REDIRECT_URL'),
            'grant_type' => 'authorization_code',
            'client_id' => env('OAUTH_TWITTER_CLIENT_ID'),
            'code_verifier' => env('OAUTH_TWITTER_CODE_CHALLENGE'),
        ])->json();
        $access_token = $token_response['access_token'];

        // 3. Get user info
        $user_info_url = 'https://api.twitter.com/2/users/me';
        $user_info_response = Http::withToken($access_token)->get($user_info_url, [
            'user.fields' => 'id,name,username,profile_image_url',
        ])->json();
        $user_data = $user_info_response['data'];

        // 4. Create user and profile if they don't exist
        $username_to_use = 'twitter'.$user_data['id'];
        $user = User::where('username', $username_to_use)->first();
        if (!$user) {
            $user = new User([
                'username' => $username_to_use,
                'password' => 'NOT_TO_BE_USED',
            ]);
            $user->save();

            $profile = new Profile([
                'name' => $user_data['name'],
                'email' => null,
                'picture' => str_replace('_normal', '', $user_data['profile_image_url']),
                'provider' => 'twitter',
            ]);
            $user->profile()->save($profile);
        }

        // 5. Log the user in
        Auth::login($user, true);

        return redirect()->route('home');
    }
}
