<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'The email or password is incorrect.']);
        }

        $request->session()->regenerate(); // keeps the guest cart, prevents session fixation

        return redirect()->intended(route('home'))
            ->with('success', 'You are logged in. Member prices are now applied to your cart.');
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        // Drop the whole session (cart, coupon) so the next visitor on a shared computer starts clean.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out.');
    }
}
