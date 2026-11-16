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

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => Phone::normalize($this->input('phone'))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'phone' => ['required', 'regex:'.Phone::PATTERN],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'payment_method' => ['required', 'in:'.implode(',', array_keys(config('booknest.payment_methods')))],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid Vietnamese phone number: 10 digits starting with 0, or +84 followed by 9 digits.',
            'phone.unique' => 'This phone number is already used by another account.',
            'address.min' => 'Please enter a full delivery address (house number, street, ward, city).',
            'payment_method.required' => 'Please choose a payment method.',
            'payment_method.in' => 'Please choose a valid payment method.',
        ];
    }
}
