@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="bg-red-100 min-h-screen grow-[3] flex flex-col justify-center items-center gap-1">
            @auth
                <img src="{{ auth()->user()->profile->picture }}" alt="Profile IMG" class="max-w-min mb-3">
                <p class="text-red-500 text-xl">{{ auth()->user()->profile->name }}</p>
                <p class="text-red-500 text-xl">{{ auth()->user()->profile->email }}</p>
                <p class="text-xl">Signed in with {{ ucfirst(auth()->user()->profile->provider) }}</p>
            @endauth
        </div>
        <div class="bg-yellow-100 min-h-screen flex justify-center items-center flex-col grow">
            <p class="mb-1">
                Google's OAuth:
                <a href="https://developers.google.com/identity/openid-connect/openid-connect#server-flow"
                class="hover:underline text-blue-400 hover:text-blue-500"
                target="_blank">
                    Server flow
                </a>
            </p>
            <a href="{{$google_auth_endpoint ?? '#'}}" class="bg-red-600 hover:bg-red-700 text-white pr-1 rounded-md">
                <img src="{{ asset('images/btn_google_light_normal_ios.svg') }}" alt="Google SVG" class="inline">
                <span class="px-2">Sign in with Google</span>
            </a>
        </div>
    </div>
@endsection
