<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $role = new Role();
        $roleIndex = Role::all();
        return view('role/roleIndex', compact ('roleIndex'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('role/createRole', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','unique:roles,name'],
            'permissions' => ['required','array'],
            'permissions.*' => ['integer','exists:permissions,id'],
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.string'   => 'El nombre del rol debe ser texto válido.',
            'name.unique'   => 'Este nombre de rol ya existe. Por favor elige otro.',

            'permissions.required' => 'Debes seleccionar al menos un permiso.',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $ids = collect($validated['permissions'] ?? [])->map(fn($v)=>(int)$v)->all();

        $perms = Permission::whereIn('id', $ids)
                ->where('guard_name', $role->guard_name)
                ->get();
        $role->syncPermissions($perms);

        return redirect()->route('role.index')->with('success','Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        $rolePermissionIds = $role->permissions()->pluck('id')->toArray();

        return view('role.editRole', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissionIds' => $rolePermissionIds,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required','string','unique:roles,name,'.$role->id],
            'permissions' => ['required','array'],
            'permissions.*' => ['integer','exists:permissions,id'],
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.string'   => 'El nombre del rol debe ser texto válido.',
            'name.unique'   => 'Este nombre de rol ya está registrado. Por favor elige otro.',

            'permissions.required' => 'Debes seleccionar al menos un permiso.',
            'permissions.array'    => 'El formato de los permisos no es válido.',
        ]);

        $role->update([
            'name' => $validated['name'],
            'guard_name' => $role->guard_name ?: 'web',
        ]);

        $ids = collect($validated['permissions'] ?? [])->map(fn($v)=>(int)$v)->all();
        $perms = Permission::whereIn('id', $ids)
                ->where('guard_name', $role->guard_name)
                ->get();
        $role->syncPermissions($perms);

        return redirect()->route('role.index')->with('success','Rol actualizado correctamente.');
    }

    public function destroyPermissionFromRole(Role $role, Permission $permission)
    {
        $role = Role::find($role->id);
        $permission = Permission::find($permission->id);

        $role->revokePermissionTo($permission);

        return redirect()->back()->with('success', 'Permiso eliminado del rol correctamente.');
    }

    public function destroy(Role $role, Permission $permission)
    {
        $role = Role::find($role->id);
        $permission = Permission::find($permission->id);
        $role->delete();
        return redirect()->route('role.index')->with('success', 'El rol se ha eliminado con éxito.');
    }
}