<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Support\Phone;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $request->merge(['phone' => Phone::normalize($request->input('phone'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'regex:'.Phone::PATTERN, Rule::unique('users', 'phone')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.regex' => 'Please enter a valid Vietnamese phone number: 10 digits starting with 0, or +84 followed by 9 digits.',
            'phone.unique' => 'This phone number is already used by another account.',
        ]);

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'Your profile has been updated.');
    }
}
