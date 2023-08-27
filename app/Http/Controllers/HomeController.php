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
        $auth_endpoints = AuthHelper::getAuthEndpoints();

        return view('home/index')->with('auth_endpoints', $auth_endpoints);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('home');
    }
}
