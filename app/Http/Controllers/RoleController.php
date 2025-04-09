<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view role', ['only' => ['index', 'show']]);
        $this->middleware('permission:create role', ['only' => ['create','store']]);
        $this->middleware('permission:edit role', ['only' => ['update','edit']]);
        $this->middleware('permission:delete role', ['only' => ['destroy']]);
    }

    // public function index()
    // {
    //     $roles = Role::get();
    //     return view('role-permission.role.index', ['roles' => $roles]);
    // }

    public function index()
    {
        $role = new Role();

        $roleIndex = User::all();
        return view('role/roleIndex', compact ('roleIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permission = Permisssion::get();
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

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit($id)
    // {
    //     $user = User::find($id);

    //     if (!$user) {
    //         return redirect()->back()->with('error', 'El usuario no se encontró.');
    //             // return redirect()->route('/producto/productoIndex')->with('error', 'El producto no se encontró.');
    //     }
    //     return view('/user/editUsuario', ['user' => $user]);
    // }

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
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
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
        $role->name = $reques->input('name');

        $role->save();

        $role->syncPermissions($request->input('permission'));
        

        // $role->update([
        //     'name' => $request->name
        // ]);

        return redirect('/role')->with('success', 'Role registrado correctamente.');
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        $role->delete();
        return redirect()->route('role.index')->with('success', 'El usuario se ha eliminado con éxito.');
    }
}