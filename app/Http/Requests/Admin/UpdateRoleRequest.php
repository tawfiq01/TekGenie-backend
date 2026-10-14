<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'min:2', 'max:60',
                Rule::unique(Role::class, 'name')->where('guard_name', 'admin')->ignore($this->route('role')),
            ],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => [Rule::exists(Permission::class, 'name')->where('guard_name', 'admin')],
        ];
    }
}
