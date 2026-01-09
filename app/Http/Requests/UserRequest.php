<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'role' => ['required', 'string', 'in:' . implode(',', User::ROLES)],
        ];

        if ($isUpdate) {
            $rules['password'] = ['nullable', 'confirmed', Rules\Password::defaults()];
        } else {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'email.required' => 'The email address is required.',
            'email.unique' => 'This email address is already in use.',
            'password.required' => 'A password is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'role.required' => 'Please select a role for the user.',
            'role.in' => 'The selected role is invalid.',
        ];
    }
}
