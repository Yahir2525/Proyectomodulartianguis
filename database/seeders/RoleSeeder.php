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
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'administrador']);
        $userRole = Role::create(['name' => 'user']);

        // Create permissions
        // $editPermission = Permission::create(['name' => 'edit articles']);
        // $viewPermission = Permission::create(['name' => 'view articles']);
        
        
        //Abonos
        $viewAbonoPermission = Permission::create(['name' => 'view abono']);
        $createAbonoPermission = Permission::create(['name' => 'create abono']);
        $editAbonoPermission = Permission::create(['name' => 'edit abono']);
        $deleteAbonoPermission = Permission::create(['name' => 'delete abono']);

        //Carro
        $viewCarroPermission = Permission::create(['name' => 'view carro']);
        $createCarroPermission = Permission::create(['name' => 'create carro']);
        $editCarroPermission = Permission::create(['name' => 'edit carro']);
        $deleteCarroPermission = Permission::create(['name' => 'delete carro']);

        //Compra
        $viewCompraPermission = Permission::create(['name' => 'view compra']);
        $createCompraPermission = Permission::create(['name' => 'create compra']);
        $editCompraPermission = Permission::create(['name' => 'edit compra']);
        $deleteCompraPermission = Permission::create(['name' => 'delete compra']);

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

        $viewRolePermission = Permission::create(['name' => 'view role']);
        $createRolePermission = Permission::create(['name' => 'create role']);
        $editRolePermission = Permission::create(['name' => 'edit role']);
        $deleteRolePermission = Permission::create(['name' => 'delete role']);

        // Assign permissions to roles
        $adminRole->givePermissionTo($viewAbonoPermission, $createAbonoPermission, $editAbonoPermission, $deleteAbonoPermission, 
        $viewCarroPermission, $createCarroPermission, $editCarroPermission, $deleteCarroPermission,
        $viewCompraPermission, $createCompraPermission, $editCompraPermission, $deleteCompraPermission,
        $viewCreditoPermission, $createCreditoPermission, $editCreditoPermission, $deleteCreditoPermission,
        $viewPedidoPermission, $createPedidoPermission, $editPedidoPermission, $deletePedidoPermission,
        $viewProductoPermission, $createProductoPermission, $editProductoPermission, $deleteProductoPermission,
        $viewUserPermission, $createUserPermission, $editUserPermission, $deleteUserPermission,
        $viewRolePermission, $createRolePermission,
        $editRolePermission, $deleteRolePermission);
        
        $userRole->givePermissionTo($viewAbonoPermission, $deleteAbonoPermission,
        $viewCarroPermission, $createCarroPermission, $editCarroPermission, $deleteCarroPermission,
        $viewCompraPermission, $createCompraPermission, $editCompraPermission, $deleteCompraPermission,
        $viewCreditoPermission, $deleteCreditoPermission,
        $viewPedidoPermission, $createPedidoPermission, $editPedidoPermission, $deletePedidoPermission,
        $viewProductoPermission, $deleteProductoPermission,
        $viewUserPermission, $editUserPermission, $deleteUserPermission,
    
    );

        // Assign role to user
        $adminPermisos = User::firstOrCreate([
            'email' => 'admin@gmail.com'
        ], [
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make ('12345678'),
            'genero' => 'M',
            'edad' => '20',
            'telefono' => '3333331111',
            'direccion' => 'juanitoaddress',
            'nombre_usuario' => 'juanitoadmin',

        ]); // Example user with ID 1
        $adminPermisos->assignRole('administrador');

        $userPermisos = User::find(1);
        $userPermisos->assignRole('user');

    }
}
