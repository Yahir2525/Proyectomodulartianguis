<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permission = new Permission();

        $permissionIndex = Permission::all();
        return view('permission/permissionIndex', compact ('permissionIndex'));
    }

    public function create()
    {
        $permission = Permission::get();
        return view('permission/createPermission', compact('permission'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:permissions,name'
            ]
        ], [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.string'   => 'El nombre del permiso debe ser texto válido.',
            'name.unique'   => 'Este nombre de permiso ya está registrado.',
        ]);

        Permission::create([
            'name' => $request->name
        ]);

        return redirect('/permission')->with('success', 'Permiso registrado correctamente.');
    }

    public function edit($id)
    {
        $permission = Permission::find($id);

        return view('/permission/editPermission',[
            'permission' => $permission,
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:permissions,name,' . $permission->id
            ]
        ], [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.string'   => 'El nombre del permiso debe ser texto válido.',
            'name.unique'   => 'Este nombre de permiso ya está registrado.',
        ]);

        $permission = Permission::find($id);
        $permission->name = $request->input('name');

        $permission->save();

        return redirect('/permission')->with('success', 'Permiso registrado correctamente.');
    }

    public function destroy($id)
    {
        $permission = Permission::find($id);

        $permission->delete();
        
        return redirect()->route('permission.index')->with('success', 'El permiso se ha eliminado con éxito.');
    }
}