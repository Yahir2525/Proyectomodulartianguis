<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DatasetMineria extends Command
{
    protected $signature = 'export:dataset-mineria';
    protected $description = 'Exporta un CSV con los datos de usuarios para minería de datos';

    public function handle()
    {
        $tmp = fopen('php://temp', 'w+');

        // Cabeceras: SIN nivel_usuario (solo nivel_regla)
        fputcsv($tmp, [
            'id_user', 'nombre_usuario',
            'nivel_regla',
            'dias_aplazo',
            'edad', 'genero',
            'total_pedidos', 'pedidos_cerrados',
            'total_creditos', 'creditos_activos', 'creditos_vencidos', 'creditos_liquidados',
            'total_abonado', 'promedio_abonos', 'ultimo_abono_fecha',
        ]);

        $usuarios = User::with(['pedido', 'creditos.abonos'])->get();

        foreach ($usuarios as $user) {
            $pedidos  = $user->pedido ?? collect();
            $creditos = $user->creditos ?? collect();

            $totalPedidos = $pedidos->count();
            $cerrados     = $pedidos->where('estado_pedido', 0)->count();

            $activos    = $creditos->where('estado', 1);
            $vencidos   = $creditos->where('estado', 1)->where('fecha_vencimiento', '<', now());
            $liquidados = $creditos->where('saldo_total', 0)->whereNotNull('fecha_liquidacion');

            // Abonos
            $totalAbonos = 0.0;
            $cantidadAbonos = 0;
            $ultimoAbono = null;

            foreach ($creditos as $c) {
                foreach ($c->abonos as $abono) {
                    $totalAbonos += (float) $abono->monto_abono;
                    $cantidadAbonos++;
                    if (!$ultimoAbono || $abono->created_at > $ultimoAbono) {
                        $ultimoAbono = $abono->created_at;
                    }
                }
            }
            $promedioAbonos = $cantidadAbonos ? $totalAbonos / $cantidadAbonos : 0.0;

            // Edad (usa entero si ya lo guardas; si no, intenta desde fecha_nacimiento)
            $edad = null;
            if (isset($user->edad) && $user->edad !== null && $user->edad !== '') {
                $edad = (int) $user->edad;
            } elseif (!empty($user->fecha_nacimiento)) {
                try { $edad = Carbon::parse($user->fecha_nacimiento)->age; } catch (\Throwable $e) { $edad = null; }
            }

            // Género (fallback a 'sexo' si aplica)
            $genero = $user->genero ?? $user->sexo ?? null;

            // Nivel por TUS reglas (sin tocar DB)
            if ($user->tienePagosAtrasadosSinAbonar()) {
                $nivelRegla = 'malo';
            } elseif ($user->pagaSiempreAdelantado()) {
                $nivelRegla = 'excelente';
            } elseif ($user->pagaTardePeroPaga()) {
                $nivelRegla = 'bueno';
            } else {
                $nivelRegla = 'bueno';
            }

            fputcsv($tmp, [
                $user->id_user,
                $user->nombre_usuario,
                $nivelRegla,
                $user->dias_aplazo,
                $edad,
                $genero,
                $totalPedidos,
                $cerrados,
                $creditos->count(),
                $activos->count(),
                $vencidos->count(),
                $liquidados->count(),
                $totalAbonos,
                $promedioAbonos,
                $ultimoAbono ? Carbon::parse($ultimoAbono)->format('Y-m-d') : null,
            ]);
        }

        rewind($tmp);
        Storage::disk('public')->put('mineria_dataset.csv', stream_get_contents($tmp));
        fclose($tmp);

        $this->info('✅ Archivo actualizado en: storage/app/public/mineria_dataset.csv');
    }
}
