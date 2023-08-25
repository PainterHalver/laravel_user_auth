<?php /** @noinspection ALL */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Helpers\AuthHelper;

class HomeController extends Controller
{
    public function index()
    {
        $google_base = Cache::get('google_base_authorization_endpoint');
        if (!$google_base) {
            // Cache the authorization endpoint from Google
            $google_base = AuthHelper::getGoogleDiscoverDocument()['authorization_endpoint'];
            cache(['google_base_authorization_endpoint' => $google_base], now()->addDays(1));
        }
        $google_auth_endpoint = $google_base
            . '?client_id=' . env('OAUTH_GOOGLE_CLIENT_ID')
            . '&response_type=code'
            . '&scope=openid%20email%20profile'
            . '&redirect_uri=' . env('OAUTH_GOOGLE_REDIRECT_URL')
            . '&state=' . csrf_token();

        return view('home/index')->with('google_auth_endpoint', $google_auth_endpoint);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('home');
    }
}
