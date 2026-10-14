<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Models\Admin;
use App\Services\Admin\StaffManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Staff & role management (FR-ADM-002), gated by the staff.manage permission.
 */
class AdminStaffController extends Controller
{
    public function __construct(private readonly StaffManagementService $staff) {}

    public function index(): View
    {
        return view('admin.staff.index', [
            'staffMembers' => Admin::query()->with('roles')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.create', [
            'roles' => Role::query()->where('guard_name', 'admin')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->staff->create($data, $data['roles']);

        return redirect()->route('admin.staff.index')->with('status', 'Staff member created.');
    }

    public function edit(Admin $admin): View
    {
        return view('admin.staff.edit', [
            'staffMember' => $admin->load('roles'),
            'roles' => Role::query()->where('guard_name', 'admin')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateStaffRequest $request, Admin $admin): RedirectResponse
    {
        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->staff->update($actor, $admin, $request->validated());

        return redirect()->route('admin.staff.index')->with('status', 'Staff member updated.');
    }
}
