@extends('layouts.app')

@section('content')
    <div class="flex-col grow flex sm:flex-row">
        <div class="bg-red-100 grow sm:grow-[3] flex flex-col justify-center items-center gap-1">
            @auth
                <img src="{{ auth()->user()->profile->picture }}" alt="Profile IMG" class="max-w-min max-h-[96px] mb-3">
                <p class="text-red-500 text-xl">{{ auth()->user()->profile->name }}</p>
                <p class="text-red-500 text-xl">{{ auth()->user()->profile->email }}</p>
                <p class="text-xl">Signed in with {{ ucfirst(auth()->user()->profile->provider) }}</p>
            @endauth
        </div>
        <div class="bg-yellow-100 flex justify-center items-center flex-col grow">
            <p class="mb-1">
                Google's OAuth:
                <a href="https://developers.google.com/identity/openid-connect/openid-connect#server-flow"
                class="hover:underline text-blue-400 hover:text-blue-500"
                target="_blank">
                    Server flow
                </a>
            </p>
            <a href="{{$auth_endpoints['google'] ?? '#'}}" class="bg-red-600 hover:bg-red-700 text-white pr-1 rounded-md">
                <img src="{{ asset('images/btn_google_light_normal_ios.svg') }}" alt="Google SVG" class="inline">
                <span class="px-2">Sign in with Google</span>
            </a>

            <p class="mt-3 mb-1">
                Github's OAuth:
                <a href="https://docs.github.com/en/apps/oauth-apps/building-oauth-apps/authorizing-oauth-apps#web-application-flow"
                class="hover:underline text-blue-400 hover:text-blue-500"
                target="_blank">
                    Web application flow
                </a>
            </p>
            <a href="{{$auth_endpoints['github'] ?? '#'}}" class="bg-gray-600 hover:bg-gray-700 text-white pr-1 rounded-md">
                <img src="{{ asset('images/github-3-240.png') }}" alt="Github PNG" class="inline h-11 p-[3px]">
                <span class="px-2">Sign in with Github</span>
            </a>
        </div>
    </div>
@endsection
