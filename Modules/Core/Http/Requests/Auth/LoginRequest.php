<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function throttleKey(): string
    {
        return 'login|'.Str::transliterate(Str::lower($this->string('email')->toString())).'|'.$this->ip();
    }
}
