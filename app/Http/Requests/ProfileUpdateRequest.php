<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'country' => ['nullable', 'string', 'max:100'],
            'continent' => ['nullable', 'string', 'in:Africa,Asia,Europe,North America,South America,Australia/Oceania,Antarctica'],
            'age_group' => ['nullable', 'string', 'in:under_18,18_24,25_34,35_44,45_54,55_plus'],
        ];
    }
}
