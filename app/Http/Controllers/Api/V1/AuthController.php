<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $params = $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        if(!Auth::attempt($params)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('username', $params['username'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->jsend('success', [
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }
}
