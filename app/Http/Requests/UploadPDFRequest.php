<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPDFRequest extends FormRequest
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
        return [
            'pdfs' => 'required',
            'pdfs.*' => 'mimes:pdf|max:20480', // 20MB
        ];
    }
    public function messages(): array
    {
        return [
            'pdfs.required' => 'You must select at least one PDF file.',
            'pdfs.*.mimes' => 'Only PDF files are allowed.',
            'pdfs.*.max' => 'Each PDF must not exceed 20 MB.',
        ];
    }
}
