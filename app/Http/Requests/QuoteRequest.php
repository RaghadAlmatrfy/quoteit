<?php

namespace App\Http\Requests;

use App\Enums\Field;
use App\Support\QuoteStyle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QuoteRequest extends FormRequest
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
            'content' => ['required', 'min:3', 'max:155'],
            'field' => ['required', Rule::enum(Field::class)],
            // image or video, up to 20 MB
            'media' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov', 'max:20480'],
            'remove_media' => ['nullable', 'boolean'],

            // How it looks
            'font' => ['nullable', Rule::in(array_keys(QuoteStyle::Fonts))],
            'font_size' => ['nullable', Rule::in(array_keys(QuoteStyle::Sizes))],
            'is_bold' => ['nullable', 'boolean'],
            'is_italic' => ['nullable', 'boolean'],
            'text_align' => ['nullable', Rule::in(QuoteStyle::Aligns)],
            'bg_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],

            // publish now, save as a draft, or schedule for later
            'intent' => ['nullable', Rule::in(['publish', 'draft', 'schedule'])],
            'publish_at' => ['nullable', 'required_if:intent,schedule', 'date', 'after:now'],
        ];
    }

    /**
     * The style columns, ready to save.
     *
     * @return array<string, mixed>
     */
    public function styleAttributes(): array
    {
        return [
            'font' => $this->input('font', 'default'),
            'font_size' => $this->input('font_size', 'md'),
            'is_bold' => $this->boolean('is_bold'),
            'is_italic' => $this->boolean('is_italic'),
            'text_align' => $this->input('text_align', 'start'),
            'bg_color' => $this->input('bg_color') ?: null,
        ];
    }
}
