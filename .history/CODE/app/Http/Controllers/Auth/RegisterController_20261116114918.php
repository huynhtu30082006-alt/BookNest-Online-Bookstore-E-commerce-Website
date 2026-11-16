<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'phone.regex' => 'Please enter a valid Vietnamese phone number (e.g. 0901234567).',
            'email.unique' => 'This email is already registered. Please log in instead.',
        ]);

        // Members always get the "customer" role; admins are created by seeders / the admin area.
        $user = User::create($data + ['role' => 'customer']);

        Auth::login($user);
        $request->session()->regenerate(); // keeps the guest cart

        return redirect()->intended(route('home'))
            ->with('success', "Welcome to BookNest, {$user->name}! Member prices are now applied.");
    }
}
