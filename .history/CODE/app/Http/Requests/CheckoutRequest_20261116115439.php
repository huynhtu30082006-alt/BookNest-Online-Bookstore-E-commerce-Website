<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\Phone;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // guests and members may both check out
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'phone' => ['required', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'payment_method' => ['required', 'in:'.implode(',', array_keys(config('booknest.payment_methods')))],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid Vietnamese phone number (e.g. 0901234567).',
            'address.min' => 'Please enter a full delivery address (house number, street, ward, city).',
            'payment_method.required' => 'Please choose a payment method.',
            'payment_method.in' => 'Please choose a valid payment method.',
        ];
    }
}
