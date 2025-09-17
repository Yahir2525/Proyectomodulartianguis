<?php

// namespace App\Http\Controllers;
// use App\Models\User;
// use App\Models\Abono;
// use App\Models\Cliente;
// use App\Models\Compra;
// use App\Models\Credito;
// use App\Models\Pedido;
// use App\Models\Producto;
// use App\Models\Vendedor;
// use Illuminate\Http\Request;

// class VendedorController extends Controller
// {
//     /**
//      * Display a listing of the resource.
//      */
//     public function index()
//     {
//         $vendedor = new Vendedor();
//         $vendedorIndex = Vendedor::all();
//         return view('vendedor/vendedorIndex', compact ('vendedorIndedx'));
//         //
//     }

//     /**
//      * Show the form for creating a new resource.
//      */
//     public function create()
//     {
//         return view('vendedor/createVendedor');
//         //
//     }

//     /**
//      * Store a newly created resource in storage.
//      */
//     public function store(Request $request)
//     {
//         $request->validate([
//             'nombre' => 'required|string|max:100',
//             'genero' => 'requiered|character|min:1',
//             'edad'=> 'requiered|integer|min:0|max:120',
//             'telefono' => 'required|integer|min:10|unique:vendedors',
//             'direccion' => 'required|string|max:255',
//             'correo' => 'required|string|email|max:30|unique:vendedors',
//             'nombre_usuario' => 'required|string|max:30|unique:vendedors',
//         ], [
//             'nombre.required' => 'El nombre es obligatorio.',
//             'nombre.string' => 'El nombre debe ser una cadena de texto.',
//             'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
//             'genero.required' => 'El genero es obligatorio.',
//             'genero.character' => 'El genero debe ser un caracter.',
//             'genero.min' => 'El genero debe ser al menos 1.',
//             'edad.required' => 'La edad es obligatoria.',
//             'edad.integer' => 'La edad debe ser un entero.',
//             'edad.min' => 'La edad debe ser minimo 0.',
//             'edad.max' => 'La edad debe ser máximo 120 años.',
//             'telefono.required' => 'El teléfono es obligatorio.',
//             'telefono.integer' => 'El teléfono debe ser un entero.',
//             'telefono.min' => 'El teléfono debe tener al menos diez digitos.',
//             'telefono.unique' => 'El teléfono ya está en uso.',
//             'direccion.required' => 'La dirección es obligatorio.',
//             'direccion.string' => 'La dirección debe ser una cadena de texto.',
//             'direccion.max' => 'La dirección no puede tener más de 255 caracteres.',
//             'correo.required' => 'El correo electrónico es obligatorio.',
//             'correo.string' => 'El correo electrónico debe ser una cadena de texto.',
//             'correo.email' => 'El correo electrónico no es válido.',
//             'correo.unique' => 'El correo electrónico ya está en uso.',
//             'correo.max' => 'El correo electrónico no puede tener más de 30 caracteres.',
//             'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
//             'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto.',
//             'nombre_usuario.max' => 'El nombre de usuario no puede tener más de 30 caracteres.',
//             'nombre_usuario.unique' => 'El nombre de usuario ya está en uso.',
//         ]);
//             $vendedor = new Cliente();
//             $vendedor->nombre = $request->nombre;
//             $vendedor->genero = $request->genero;
//             $vendedor->edad = $request->edad;
//             $vendedor->telefono = $request->telefono;
//             $vendeor->direccion = $request->direccion;
//             $vendedor->correo = $request->correo;
//             $vendedor->nombre_usuario = $request->nombre_usuario;
            
//             $vendedor->save();
//             return redirect('/vendedor')->with('success', 'Vendedor registrado correctamente.');
//         //
//     }

//     /**
//      * Display the specified resource.
//      */
//     public function show(Request $request)
//     {
//         $id = $request->input('id_vendedor');
//         $cliente = Cliente::find($id);
            
//         if (!$cliente) {
//             return redirect()->back()->with('error', 'El cliente no se encontró.');
//         }
//         return view('/abono/showCliente', ['cliente' => $cliente]);
//         //
//     }

//     /**
//      * Show the form for editing the specified resource.
//      */
//     public function edit($id)
//     {
//         // $user = Auth::user();
//         // if($user->isAdmin())
//         // {
//             $vendedor = Vendedor::find($id);

//             if (!$vendedor) {
//                 return redirect()->back()->with('error', 'El vendedor no se encontró.');
//                     // return redirect()->route('/producto/productoIndex')->with('error', 'El producto no se encontró.');
//             }
//             return view('/vendedor/editVendedor', ['vendedor' => $vendedor]);
//         // else{
//         //     return redirect()->back()->with('error', 'No puedes editar este vendedor.');
//         // }
//     }

//     /**
//      * Update the specified resource in storage.
//      */
//     public function update(Request $request, Vendedor $vendedor)
//     {
//         $request->validate([
//             'nombre' => 'required|string|max:100',
//             'genero' => 'requiered|character|min:1',
//             'edad'=> 'requiered|integer|min:0|max:120',
//             'telefono' => 'required|integer|min:10|unique:vendedors',
//             'direccion' => 'required|string|max:255',
//             'correo' => 'required|string|email|max:30|unique:vendedors',
//             'nombre_usuario' => 'required|string|max:30|unique:vendedors',
//         ], [
//             'nombre.required' => 'El nombre es obligatorio.',
//             'nombre.string' => 'El nombre debe ser una cadena de texto.',
//             'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
//             'genero.required' => 'El genero es obligatorio.',
//             'genero.character' => 'El genero debe ser un caracter.',
//             'genero.min' => 'El genero debe ser al menos 1.',
//             'edad.required' => 'La edad es obligatoria.',
//             'edad.integer' => 'La edad debe ser un entero.',
//             'edad.min' => 'La edad debe ser minimo 0.',
//             'edad.max' => 'La edad debe ser máximo 120 años.',
//             'telefono.required' => 'El teléfono es obligatorio.',
//             'telefono.integer' => 'El teléfono debe ser un entero.',
//             'telefono.min' => 'El teléfono debe tener al menos diez digitos.',
//             'telefono.unique' => 'El teléfono ya está en uso.',
//             'direccion.required' => 'La dirección es obligatorio.',
//             'direccion.string' => 'La dirección debe ser una cadena de texto.',
//             'direccion.max' => 'La dirección no puede tener más de 255 caracteres.',
//             'correo.required' => 'El correo electrónico es obligatorio.',
//             'correo.string' => 'El correo electrónico debe ser una cadena de texto.',
//             'correo.email' => 'El correo electrónico no es válido.',
//             'correo.unique' => 'El correo electrónico ya está en uso.',
//             'correo.max' => 'El correo electrónico no puede tener más de 30 caracteres.',
//             'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
//             'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto.',
//             'nombre_usuario.max' => 'El nombre de usuario no puede tener más de 30 caracteres.',
//             'nombre_usuario.unique' => 'El nombre de usuario ya está en uso.',
//         ]);
//         $vendedor = Vendedor::find($id);
    
//         if (!$vendedor) {
//             return redirect()->route('vendedor.vendedorIndex')->with('error', 'El vendedor no se encontró.');
//         }
//         $vendedor->nombre = $request->nombre;
//         $vendedor->genero = $request->genero;
//         $vendedor->edad = $request->edad;
//         $vendedor->telefono = $request->telefono;
//         $vendeor->direccion = $request->direccion;
//         $vendedor->correo = $request->correo;
//         $vendedor->nombre_usuario = $request->nombre_usuario;
//         //dd($request);
//         // if ($request->hasFile('archivo') && $request->file('archivo')->isValid()) {
//         //     // Eliminar el archivo antiguo si existe
//         //     if ($aceite->archivo_ubicacion) {
//         //         Storage::delete($aceite->archivo_ubicacion); 
//         //     }

//         //     $aceite->archivo_nombre = $request->file('archivo')->getClientOriginalName();
//         //     $aceite->archivo_ubicacion = $request->file('archivo')->store('public/img');
//         //     //dd($aceite);
//         // }
//             /*Eliminar las imagenes antiguas
//                 Storage::delete($aceite->aceite_ubicacion);
//                 $aceite->aceite_ubicacion->delete();
            
//                 $aceite->archivo_nombre = $request->file('archivo')->getClientOriginalName();
//                 $aceite->archivo_ubicacion = $request->file('archivo')->store('public/img');*/
//         $vendedor->save();
//         return redirect()->route('vendedor.abonoIndex')->with('success', 'El vendedor se ha actualizado con éxito.');
//     }

//     /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy(Vendedor $vendedor)
//     {
//         $vendedor = Vendedor::find($id);

//         // if ($aceite->archivo_ubicacion) {
//         //     Storage::delete($aceite->archivo_ubicacion);
//         // }

//         if (!$vendedor) {
//             return redirect()->route('vendedor.vendedorIndex')->with('error', 'El vendedor no se encontró.');
//         }
        
//         // $detalleCompras = DetalleCompra::where('id_aceite', $id)->get();

//         // foreach ($detalleCompras as $detalleCompra) {
//         //     $compra = Compras::find($detalleCompra->id_compras);
//         //     if ($compra) {
//         //         $compra->delete();
//         //     }
            
//         //     // Eliminar el DetalleCompras
//         //     $detalleCompra->delete();
//         // }

//         $vendedor->delete();

//         return redirect()->route('vendedor.vendedorIndex')->with('success', 'El vendedor se ha eliminado con éxito.');
//     }
// }
