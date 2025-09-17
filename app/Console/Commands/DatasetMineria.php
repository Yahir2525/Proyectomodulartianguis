<?php

// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use App\Models\User;
// use Illuminate\Support\Facades\Storage;
// use Carbon\Carbon;

// class DatasetMineria extends Command
// {
//     protected $signature = 'export:dataset-mineria';
//     protected $description = 'Exporta un CSV con los datos de usuarios para minería de datos';

//     public function handle()
//     {
//         $tmp = fopen('php://temp', 'w+');

//         // Cabeceras: SIN edad ni genero, y con el orden que usa tu entrenamiento
//         fputcsv($tmp, [
//             'id_user', 'nombre_usuario',
//             'nivel_regla',
//             'dias_aplazo',
//             'total_pedidos', 'pedidos_cerrados',
//             'total_creditos', 'creditos_activos', 'creditos_vencidos', 'creditos_liquidados',
//             'total_abonado', 'promedio_abonos', 'ultimo_abono_fecha',
//         ]);

//         $usuarios = User::with(['pedido', 'creditos.abonos'])->get();

//         foreach ($usuarios as $user) {
//             $pedidos  = $user->pedido ?? collect();
//             $creditos = $user->creditos ?? collect();

//             $totalPedidos = $pedidos->count();
//             $cerrados     = $pedidos->where('estado_pedido', 0)->count();

//             // Conteos de créditos por estado
//             $activosCount    = $creditos->where('estado', 1)->count();
//             $vencidosCount   = $creditos
//                 ->where('estado', 1)
//                 ->where('fecha_vencimiento', '<', now())
//                 ->count();
//             $liquidadosCount = $creditos
//                 ->where('saldo_total', 0)
//                 ->whereNotNull('fecha_liquidacion')
//                 ->count();

//             // Total de créditos = activos + vencidos + liquidados (suma simple)
//             $totalCreditos = $activosCount + $vencidosCount + $liquidadosCount;

//             // Abonos agregados
//             $totalAbonos = 0.0;
//             $cantidadAbonos = 0;
//             $ultimoAbono = null;

//             foreach ($creditos as $c) {
//                 foreach ($c->abonos as $abono) {
//                     $totalAbonos += (float) $abono->monto_abono;
//                     $cantidadAbonos++;
//                     if (!$ultimoAbono || $abono->created_at > $ultimoAbono) {
//                         $ultimoAbono = $abono->created_at;
//                     }
//                 }
//             }
//             $promedioAbonos = $cantidadAbonos ? $totalAbonos / $cantidadAbonos : 0.0;

//             // Determinar nivel_regla con tus helpers (no se toca BD)
//             if ($user->tienePagosAtrasadosSinAbonar()) {
//                 $nivelRegla = 'malo';
//             } elseif ($user->pagaSiempreAdelantado()) {
//                 $nivelRegla = 'excelente';
//             } elseif ($user->pagaTardePeroPaga()) {
//                 $nivelRegla = 'bueno';
//             } else {
//                 $nivelRegla = 'bueno';
//             }

//             fputcsv($tmp, [
//                 $user->id_user,
//                 $user->nombre_usuario,
//                 $nivelRegla,
//                 $user->dias_aplazo,
//                 $totalPedidos,
//                 $cerrados,
//                 $totalCreditos,
//                 $activosCount,
//                 $vencidosCount,
//                 $liquidadosCount,
//                 $totalAbonos,
//                 $promedioAbonos,
//                 $ultimoAbono ? Carbon::parse($ultimoAbono)->format('d/m/Y') : null, // DD/MM/YYYY
//             ]);
//         }

//         rewind($tmp);
//         Storage::disk('public')->put('mineria_dataset.csv', stream_get_contents($tmp));
//         fclose($tmp);

//         $this->info('✅ Archivo actualizado en: storage/app/public/mineria_dataset.csv');
//     }
// }
