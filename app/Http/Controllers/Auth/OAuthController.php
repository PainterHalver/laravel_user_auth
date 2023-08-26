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
        // 1. Confirm csrf token
        if (!isset($request['state']) || $request['state'] !== csrf_token()) {
            abort(401, 'Invalid state parameter');
        }

        // 2. Get token endpoint from cache or Google's discovery document
        $token_base_endpoint = cache('google_base_token_endpoint');
        if (!$token_base_endpoint) {
            $token_base_endpoint = AuthHelper::getGoogleDiscoverDocument()['token_endpoint'];
            cache(['google_base_token_endpoint' => $token_base_endpoint], now()->addDays(1));
        }

        // 3. Exchange code for token
        $code = $request['code'];
        $token_response = Http::asForm()->post($token_base_endpoint, [
            'code' => $code,
            'client_id' => env('OAUTH_GOOGLE_CLIENT_ID'),
            'client_secret' => env('OAUTH_GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => env('OAUTH_GOOGLE_REDIRECT_URL'),
            'grant_type' => 'authorization_code',
        ])->json();

        // 4. Parse the token
        try {
            $parser = new Parser(new JoseEncoder());
            $token = $parser->parse($token_response['id_token']);
        } catch (\Exception $e) {
            abort(500, 'Failed to parse token');
        }

        // 5. Create user and profile if they don't exist
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

        // 6. Log the user in
        Auth::login($user, true);

        return redirect()->route('home');
    }
}