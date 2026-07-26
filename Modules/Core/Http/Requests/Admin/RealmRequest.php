<?php

declare(strict_types=1);

namespace Modules\Core\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\RealmModel;

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
        $creating = $this->isMethod('post');
        $existingCharactersDatabase = $creating
            ? false
            : RealmModel::query()->find($realmId)?->characters_database !== null;
        $existingSshTunnel = $creating
            ? false
            : RealmModel::query()->find($realmId)?->ssh_tunnel !== null;
        $usingSsh = $this->input('connection_type') === 'ssh';

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required', 'string', 'max:100', 'alpha_dash',
                Rule::unique('realms', 'slug')->ignore($realmId),
            ],
            'core_type' => ['required', Rule::in(array_map(fn (CoreType $c) => $c->value, CoreType::cases()))],
            'gm_realm_id' => ['nullable', 'integer'],
            'enabled' => ['boolean'],
            'connection_type' => ['required', Rule::in(['direct', 'ssh'])],

            'auth_database.host' => ['required', 'string', 'max:255'],
            'auth_database.port' => ['required', 'integer', 'min:1', 'max:65535'],
            'auth_database.database' => ['required', 'string', 'max:255'],
            'auth_database.username' => ['required', 'string', 'max:255'],
            'auth_database.password' => [$creating ? 'required' : 'nullable', 'string'],

            'characters_database' => ['nullable', 'array'],
            'characters_database.host' => ['required_with:characters_database', 'string', 'max:255'],
            'characters_database.port' => ['required_with:characters_database', 'integer', 'min:1', 'max:65535'],
            'characters_database.database' => ['required_with:characters_database', 'string', 'max:255'],
            'characters_database.username' => ['required_with:characters_database', 'string', 'max:255'],
            'characters_database.password' => [
                Rule::requiredIf(
                    $this->input('characters_database') !== null
                    && ($creating || ! $existingCharactersDatabase)
                ),
                'nullable',
                'string',
            ],

            'remote_console.host' => ['required', 'string', 'max:255'],
            'remote_console.port' => ['required', 'integer', 'min:1', 'max:65535'],
            'remote_console.username' => ['required', 'string', 'max:255'],
            'remote_console.password' => [$creating ? 'required' : 'nullable', 'string'],

            'ssh_tunnel' => [
                Rule::requiredIf($usingSsh),
                'nullable',
                'array',
            ],
            'ssh_tunnel.host' => ['required_if:connection_type,ssh', 'nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9._:-]+$/'],
            'ssh_tunnel.port' => ['required_if:connection_type,ssh', 'nullable', 'integer', 'min:1', 'max:65535'],
            'ssh_tunnel.username' => ['required_if:connection_type,ssh', 'nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/'],
            'ssh_tunnel.private_key' => [
                Rule::requiredIf($usingSsh && ($creating || ! $existingSshTunnel)),
                'nullable',
                'string',
                'max:65535',
            ],
            'ssh_tunnel.private_key_passphrase' => ['nullable', 'string', 'max:4096'],
        ];
    }
}
