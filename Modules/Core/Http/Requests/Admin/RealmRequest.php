<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;

final class RealmRequest extends FormRequest
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
        $realmId = $this->route('realm');

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required', 'string', 'max:100', 'alpha_dash',
                Rule::unique('realms', 'slug')->ignore($realmId),
            ],
            'core_type' => ['required', Rule::in(array_map(fn (CoreType $c) => $c->value, CoreType::cases()))],
            'gm_realm_id' => ['nullable', 'integer'],
            'enabled' => ['boolean'],

            'auth_database.host' => ['required', 'string', 'max:255'],
            'auth_database.port' => ['required', 'integer', 'min:1', 'max:65535'],
            'auth_database.database' => ['required', 'string', 'max:255'],
            'auth_database.username' => ['required', 'string', 'max:255'],
            'auth_database.password' => ['required', 'string'],

            'characters_database' => ['nullable', 'array'],
            'characters_database.host' => ['required_with:characters_database', 'string', 'max:255'],
            'characters_database.port' => ['required_with:characters_database', 'integer', 'min:1', 'max:65535'],
            'characters_database.database' => ['required_with:characters_database', 'string', 'max:255'],
            'characters_database.username' => ['required_with:characters_database', 'string', 'max:255'],
            'characters_database.password' => ['required_with:characters_database', 'string'],

            'remote_console.host' => ['required', 'string', 'max:255'],
            'remote_console.port' => ['required', 'integer', 'min:1', 'max:65535'],
            'remote_console.username' => ['required', 'string', 'max:255'],
            'remote_console.password' => ['required', 'string'],
        ];
    }
}
