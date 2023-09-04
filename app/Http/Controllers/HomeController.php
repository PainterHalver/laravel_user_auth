<?php

/** @noinspection ALL */

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\Auth;

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
