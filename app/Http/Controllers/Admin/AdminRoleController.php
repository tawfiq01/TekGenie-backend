<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\Admin\RoleManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Role & permission management (FR-ADM-002), gated by the staff.manage permission.
 */
class AdminRoleController extends Controller
{
    public function __construct(private readonly RoleManagementService $roles) {}

    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->where('guard_name', 'admin')->with('permissions')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'permissions' => Permission::query()->where('guard_name', 'admin')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->roles->create($data['name'], $data['permissions']);

        return redirect()->route('admin.roles.index')->with('status', 'Role created.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::query()->where('guard_name', 'admin')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        $this->roles->update($role, $data['name'], $data['permissions']);

        return redirect()->route('admin.roles.index')->with('status', 'Role updated.');
    }
}
