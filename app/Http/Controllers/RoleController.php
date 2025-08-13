<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:view role', ['only' => ['index', 'show']]);
    //     $this->middleware('permission:create role', ['only' => ['create','store']]);
    //     $this->middleware('permission:edit role', ['only' => ['update','edit']]);
    //     $this->middleware('permission:delete role', ['only' => ['destroy']]);
    // }
    
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
            'permissions' => ['nullable','array'],
            'permissions.*' => ['integer','exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web', // asegura el guard
        ]);

        // Opción A: convertir a enteros y pasar IDs (Spatie >= v5 suele aceptarlos)
        $ids = collect($validated['permissions'] ?? [])->map(fn($v)=>(int)$v)->all();
        // $role->syncPermissions($ids);

        // Opción B (más robusta): cargar modelos por ID y sincronizar
        $perms = Permission::whereIn('id', $ids)
                ->where('guard_name', $role->guard_name) // asegura mismo guard
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
            'permissions' => ['nullable','array'],
            'permissions.*' => ['integer','exists:permissions,id'],
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

    // Elimina solo la relación entre el rol y el permiso
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