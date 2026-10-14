<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * FR-ADM-002: Super Admin creates a staff account with one or more roles.
 */
class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:191', Rule::unique(Admin::class)],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::exists(Role::class, 'name')->where('guard_name', 'admin')],
        ];
    }
}
