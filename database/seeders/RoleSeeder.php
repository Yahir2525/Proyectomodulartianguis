<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'administrador']);
        $userRole = Role::create(['name' => 'user']);

        //Abono
        $viewAbonoPermission = Permission::create(['name' => 'view abono']);
        $createAbonoPermission = Permission::create(['name' => 'create abono']);
        $editAbonoPermission = Permission::create(['name' => 'edit abono']);
        $deleteAbonoPermission = Permission::create(['name' => 'delete abono']);

        //Carro
        $viewCarroPermission = Permission::create(['name' => 'view carro']);
        $createCarroPermission = Permission::create(['name' => 'create carro']);
        $editCarroPermission = Permission::create(['name' => 'edit carro']);
        $deleteCarroPermission = Permission::create(['name' => 'delete carro']);

        //Credito
        $viewCreditoPermission = Permission::create(['name' => 'view credito']);
        $createCreditoPermission = Permission::create(['name' => 'create credito']);
        $editCreditoPermission = Permission::create(['name' => 'edit credito']);
        $deleteCreditoPermission = Permission::create(['name' => 'delete credito']);

        //Pedido
        $viewPedidoPermission = Permission::create(['name' => 'view pedido']);
        $createPedidoPermission = Permission::create(['name' => 'create pedido']);
        $editPedidoPermission = Permission::create(['name' => 'edit pedido']);
        $deletePedidoPermission = Permission::create(['name' => 'delete pedido']);

        //Producto
        $viewProductoPermission = Permission::create(['name' => 'view producto']);
        $createProductoPermission = Permission::create(['name' => 'create producto']);
        $editProductoPermission = Permission::create(['name' => 'edit producto']);
        $deleteProductoPermission = Permission::create(['name' => 'delete producto']);

         //User
        $viewUserPermission = Permission::create(['name' => 'view user']);
        $createUserPermission = Permission::create(['name' => 'create user']);
        $editUserPermission = Permission::create(['name' => 'edit user']);
        $deleteUserPermission = Permission::create(['name' => 'delete user']);

        //Role
        $viewRolePermission = Permission::create(['name' => 'view role']);
        $createRolePermission = Permission::create(['name' => 'create role']);
        $editRolePermission = Permission::create(['name' => 'edit role']);
        $deleteRolePermission = Permission::create(['name' => 'delete role']);

        //Permission
        $viewPermission = Permission::create(['name' => 'view permission']);
        $createPermission = Permission::create(['name' => 'create permission']);
        $editPermission = Permission::create(['name' => 'edit permission']);
        $deletePermission = Permission::create(['name' => 'delete permission']);

        // Assign permissions to roles
        $adminRole->givePermissionTo($viewAbonoPermission, $createAbonoPermission, $editAbonoPermission, $deleteAbonoPermission, 
        $viewCarroPermission, $createCarroPermission, $editCarroPermission, $deleteCarroPermission,
        $viewCreditoPermission, $createCreditoPermission, $editCreditoPermission, $deleteCreditoPermission,
        $viewPedidoPermission, $createPedidoPermission, $editPedidoPermission, $deletePedidoPermission,
        $viewProductoPermission, $createProductoPermission, $editProductoPermission, $deleteProductoPermission,
        $viewUserPermission, $createUserPermission, $editUserPermission, $deleteUserPermission,
        $viewRolePermission, $createRolePermission, $editRolePermission, $deleteRolePermission, $viewPermission, $createPermission, $editPermission, $deletePermission);
        
        $userRole->givePermissionTo($viewAbonoPermission,
        $viewCarroPermission, $createCarroPermission, $editCarroPermission, $deleteCarroPermission,
        $viewCreditoPermission,
        $viewPedidoPermission, $createPedidoPermission, $editPedidoPermission,
        $viewProductoPermission,
        $viewUserPermission, $editUserPermission,
    
    );
        // Assign role to user
        $adminPermisos = User::firstOrCreate([
            'email' => 'admin@gmail.com'
        ], [
            'name' => 'YahirAdmin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make ('12345678'),
            'genero' => 'H',
            'edad' => '20',
            'telefono' => '3333331111',
            'direccion' => 'yahiraddress',
            'nombre_usuario' => 'yahiradmin',

        ]);
        $adminPermisos->assignRole('administrador');
    }
}