<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Session-Based Authentication
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

        return redirect('home');
    }

    public function login(Request $request)
    {
        validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ])->validate();

        if (auth()->attempt(
            $request->only('email', 'password'),
            $request->filled('remember')
        )) {
            return redirect('home');
        }

        return redirect()->back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('login');
    }
}
