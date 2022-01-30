<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Token-based authentication using Sanctum
    public function register(Request $request)
    {
        validator($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ])->validate();

        $new_user = User::create(
            array_merge(
                $request->only('name', 'email'),
                ['password' => bcrypt($request->password)]
            )
        );

        Auth::login($new_user);

        return [
            'token' => $new_user->createToken(time())->plainTextToken
        ];
    }

    public function login(Request $request)
    {
        validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ])->validate();

        $user = User::where('email', $request->email)->first();

        if (Hash::check($request->password, $user->getAuthPassword())) {
            return [
                'token' => $user->createToken(time())->plainTextToken
            ];
        }

        return response([
            'error' => 'Invalid credentials'
        ], 401);
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();

        return response(null, 204);
    }
}
