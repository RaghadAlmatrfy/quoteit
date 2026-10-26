<?php

namespace App\Http\Requests;

use App\Enums\Field;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:2', 'max:50'],
            'username' => ['required', 'min:3', 'max:30', 'alpha_dash:ascii', Rule::unique('users', 'username')->ignore(Auth::id())],
            'bio' => ['nullable', 'max:160'],
            'specialty' => ['nullable', Rule::enum(Field::class)],
            'interests' => ['nullable', 'array'],
            'interests.*' => [Rule::enum(Field::class)],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'is_private' => ['nullable', 'boolean'],
        ];
    }
}
