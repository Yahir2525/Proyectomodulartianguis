<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DatasetMineria extends Command
{
    protected $signature = 'export:dataset-mineria';
    protected $description = 'Exporta un CSV con los datos de usuarios para minería de datos';

    public function handle()
    {
        $filename = storage_path('app/public/mineria_dataset.csv');
        File::ensureDirectoryExists(storage_path('app/public'));
        $file = fopen($filename, 'w');

        // Cabeceras
        fputcsv($file, [
            'id_user', 'nombre_usuario', 'nivel_usuario', 'dias_aplazo',
            'total_pedidos', 'pedidos_cerrados',
            'total_creditos', 'creditos_activos', 'creditos_vencidos', 'creditos_liquidados',
            'total_abonado', 'promedio_abonos', 'ultimo_abono_fecha',
            'monto_promedio_credito', 'cumple_a_tiempo'
        ]);

        $usuarios = User::with(['pedido', 'creditos.abonos'])->get();

        foreach ($usuarios as $user) {
            $pedidos = $user->pedido;
            $creditos = $user->creditos;

            $totalPedidos = $pedidos->count();
            $cerrados = $pedidos->where('estado_pedido', 0)->count();

            $activos = $creditos->where('estado', 1);
            $vencidos = $creditos->where('estado', 1)->where('fecha_vencimiento', '<', now());
            $liquidados = $creditos->where('saldo_total', 0)->whereNotNull('fecha_liquidacion');

            $totalAbonos = 0;
            $cantidadAbonos = 0;
            $ultimoAbono = null;

            foreach ($creditos as $credito) {
                foreach ($credito->abonos as $abono) {
                    $totalAbonos += $abono->monto_abono;
                    $cantidadAbonos++;
                    if (!$ultimoAbono || $abono->created_at > $ultimoAbono) {
                        $ultimoAbono = $abono->created_at;
                    }
                }
            }

            $promedioAbonos = $cantidadAbonos ? $totalAbonos / $cantidadAbonos : 0;
            $promedioCredito = $creditos->count() ? $creditos->avg('monto_original') : 0;

            // ¿Cumple a tiempo?
            $cumpleATiempo = $creditos->filter(function ($c) {
                return $c->fecha_liquidacion && $c->fecha_liquidacion <= $c->fecha_vencimiento;
            })->count() >= 3;

            fputcsv($file, [
                $user->id_user,
                $user->nombre_usuario,
                $user->nivel_usuario,
                $user->dias_aplazo,
                $totalPedidos,
                $cerrados,
                $creditos->count(),
                $activos->count(),
                $vencidos->count(),
                $liquidados->count(),
                number_format($totalAbonos, 2, '.', ''),
                number_format($promedioAbonos, 2, '.', ''),
                $ultimoAbono,
                number_format($promedioCredito, 2, '.', ''),
                $cumpleATiempo ? 1 : 0,
            ]);
        }

        fclose($file);

        $this->info('✅ Archivo generado correctamente en: storage/app/public/mineria_dataset.csv');
    }
}
