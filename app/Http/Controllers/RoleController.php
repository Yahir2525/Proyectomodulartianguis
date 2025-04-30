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
        $permission = Permission::get();
        return view('role/createRole', compact('permission'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:roles,name'
            ]
        ]);

        Role::create([
            'name' => $request->name
        ]);

        return redirect('/role')->with('success', 'Role registrado correctamente.');
    }

    public function edit($id)
    {
        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table('role_has_permissions')
        ->where('role_has_permissions.role_id', $role->id)
        ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
        ->all();

        return view('/role/editRole',[
            'role' => $role,
            'permission' => $permission,
            'rolePermission' => $rolePermissions
        ]);
    }


    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:roles,name,'.$role->id
            ]
        ]);

        $role = Role::find($id);
        $role->name = $request->input('name');

        $role->save();

        $role->syncPermissions($request->input('permission'));
        
        return redirect('role.index')->with('success', 'Role registrado correctamente.');
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