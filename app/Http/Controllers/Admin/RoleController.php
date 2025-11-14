<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->latest()->paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Create role
        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'],
        ]);

        // Attach permissions
        $role->permissions()->attach($validated['permissions']);

        return redirect()->route('admin.roles.index')
            ->with('success', __('created_successfully'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $role->load('permissions');
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Update role
        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'],
        ]);

        // Sync permissions
        $role->permissions()->sync($validated['permissions']);

        return redirect()->route('admin.roles.index')
            ->with('success', __('updated_successfully'));
    }

    public function destroy(Role $role)
    {
        // Prevent deleting super_admin role
        if ($role->name === 'super_admin') {
            return back()->with('error', __('Cannot delete super admin role.'));
        }

        // Check if role is assigned to users
        if ($role->users()->count() > 0) {
            return back()->with('error', __('Cannot delete role that is assigned to users.'));
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', __('deleted_successfully'));
    }
}
