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
        $showAbonoPermission = Permission::create(['name' => 'show abono']);

        //Carro
        $viewCarroPermission = Permission::create(['name' => 'view carro']);
        $createCarroPermission = Permission::create(['name' => 'create carro']);
        $editCarroPermission = Permission::create(['name' => 'edit carro']);
        $showCarroPermission = Permission::create(['name' => 'show carro']);

        //Compra
        $viewCompraPermission = Permission::create(['name' => 'view compra']);
        $createCompraPermission = Permission::create(['name' => 'create compra']);
        $editCompraPermission = Permission::create(['name' => 'edit compra']);
        $showCompraPermission = Permission::create(['name' => 'show compra']);

        //Credito
        $viewCreditoPermission = Permission::create(['name' => 'view credito']);
        $createCreditoPermission = Permission::create(['name' => 'create credito']);
        $editCreditoPermission = Permission::create(['name' => 'edit credito']);
        $showCreditoPermission = Permission::create(['name' => 'show credito']);

        //Pedido
        $viewPedidoPermission = Permission::create(['name' => 'view pedido']);
        $createPedidoPermission = Permission::create(['name' => 'create pedido']);
        $editPedidoPermission = Permission::create(['name' => 'edit pedido']);
        $showPedidoPermission = Permission::create(['name' => 'show pedido']);

        //Producto
        $viewProductoPermission = Permission::create(['name' => 'view producto']);
        $createProductoPermission = Permission::create(['name' => 'create producto']);
        $editProductoPermission = Permission::create(['name' => 'edit producto']);
        $showProductoPermission = Permission::create(['name' => 'show producto']);

         //User
        $viewUserPermission = Permission::create(['name' => 'view user']);
        $createUserPermission = Permission::create(['name' => 'create user']);
        $editUserPermission = Permission::create(['name' => 'edit user']);
        $showUserPermission = Permission::create(['name' => 'show user']);

        // Assign permissions to roles
        $adminRole->givePermissionTo($viewAbonoPermission, $createAbonoPermission, $editAbonoPermission, $showAbonoPermission, 
        $viewCarroPermission, $createCarroPermission, $editCarroPermission, $showCarroPermission,
        $viewCompraPermission, $createCompraPermission, $editCompraPermission, $showCompraPermission,
        $viewCreditoPermission, $createCreditoPermission, $editCreditoPermission, $showCreditoPermission,
        $viewPedidoPermission, $createPedidoPermission, $editPedidoPermission, $showPedidoPermission,
        $viewProductoPermission, $createProductoPermission, $editProductoPermission, $showProductoPermission,
        $viewUserPermission, $createUserPermission, $editUserPermission, $showUserPermission,);
        
        
        $userRole->givePermissionTo($viewAbonoPermission, $showAbonoPermission,
        $viewCarroPermission, $createCarroPermission, $editCarroPermission, $showCarroPermission,
        $viewCompraPermission, $createCompraPermission, $editCompraPermission, $showCompraPermission,
        $viewCreditoPermission, $showCreditoPermission,
        $viewPedidoPermission, $createPedidoPermission, $editPedidoPermission, $showPedidoPermission,
        $viewProductoPermission, $showProductoPermission,
        $viewUserPermission, $editUserPermission, $showUserPermission,
    
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
