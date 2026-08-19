<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:users,employee_id'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $role = Role::where('name', $validated['role'])->first();

        $user = User::create([
            'employee_id' => $validated['employee_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $user->assignRole($role);
        AuditLogService::log('create_user', $user, "Created user account {$user->email} with role {$role->name}");

        return redirect()->route('admin.users.index')->with('success', 'User account created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:users,employee_id,' . $user->id],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $role = Role::where('name', $validated['role'])->first();

        if ($user->id === auth()->id() && ($validated['status'] !== 'active' || $role->id !== $user->role_id)) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['account' => 'You cannot deactivate or change the system role of your own account.']);
        }

        $updateData = [
            'employee_id' => $validated['employee_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
            'role_id' => $role->id,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$role]);

        AuditLogService::log('update_user', $user, "Updated user account {$user->email}");

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        $newStatus = ($user->status === 'active') ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        AuditLogService::log('toggle_user_status', $user, "User account {$user->email} status changed to {$newStatus}");

        return redirect()->back()->with('success', "User account {$user->name} is now {$newStatus}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        AuditLogService::log('delete_user', $user, "Deleted user account {$user->email}");

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
