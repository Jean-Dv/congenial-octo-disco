<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * El username/password aqui validados son los MISMOS que se usaran
 * para crear la cuenta de juego en cada reino: por eso el limite de 16
 * caracteres ASCII (impuesto por el propio cliente 3.3.5a), no es un
 * capricho de este formulario.
 */
final class RegisterUserRequest extends FormRequest
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
            'username' => [
                'required', 'string', 'min:3', 'max:16',
                'regex:/^[A-Za-z0-9]+$/',
                Rule::unique('users', 'name'),
            ],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required', 'string', 'min:6', 'max:16',
                'regex:/^[\x21-\x7E]+$/',
                'confirmed',
            ],
            'locale' => ['nullable', 'string', 'in:es,en'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => __('core::auth.validation.username_regex'),
            'username.max' => __('core::auth.validation.username_max'),
            'password.regex' => __('core::auth.validation.password_regex'),
            'password.max' => __('core::auth.validation.password_max'),
        ];
    }
}
