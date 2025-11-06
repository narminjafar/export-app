<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadXlsxRequest extends FormRequest
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
            'file' => 'required|file|mimes:xlsx,xls|max:10240'
        ];
    }

      public function messages(): array
    {
        return [
            'file.required' => 'Fayl seçmək lazımdır.',
            'file.mimes' => 'Fayl yalnız XLSX və XLS formatında ola bilər.',
            'file.max' => 'Fayl maksimal 10MB ola bilər.',
        ];
    }
}
