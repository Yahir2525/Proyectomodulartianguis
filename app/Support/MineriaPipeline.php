<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PrediccionController;
use Carbon\Carbon;

class MineriaPipeline
{
    public static function exportarDataset(string $disk = 'public', string $filename = 'mineria_dataset.csv'): void
    {
        $tmp = fopen('php://temp', 'w+');

        // Cabecera EXACTA esperada por el entrenamiento/inferencia (inputs_v4)
        fputcsv($tmp, [
            'id_user', 'nombre_usuario',
            'dias_aplazo',
            'total_creditos', 'creditos_activos', 'creditos_vencidos', 'creditos_liquidados',
            'saldo_credito',
        ]);

        $usuarios = User::with(['creditos'])->get();

        foreach ($usuarios as $user) {
            $creditos = $user->creditos ?? collect();

            $activosCount    = (int) $creditos->where('estado', 1)->count();
            $vencidosCount   = (int) $creditos->where('estado', 1)->where('fecha_vencimiento', '<', now())->count();
            $liquidadosCount = (int) $creditos->where('saldo_total', 0)->whereNotNull('fecha_liquidacion')->count();

            $totalCreditos = $activosCount + $vencidosCount + $liquidadosCount;

            // --- Nuevo cálculo de saldo_credito ---
            // Regla: si todos son liquidados → saldo = 0
            //        si hay activos/vencidos → tomar suma de saldo_total (respetando límite de 10000)
            $saldo = 0;
            if ($activosCount > 0 || $vencidosCount > 0) {
                $saldo = (float) $creditos->sum('saldo_total');
                if ($saldo > 10000) {
                    $saldo = 10000; // límite máximo permitido
                }
            }

            fputcsv($tmp, [
                $user->id_user,
                (string) $user->nombre_usuario,
                (int) ($user->dias_aplazo ?? 0),

                $totalCreditos,
                $activosCount,
                $vencidosCount,
                $liquidadosCount,
                $saldo,
            ]);
        }

        rewind($tmp);
        Storage::disk($disk)->put($filename, stream_get_contents($tmp));
        fclose($tmp);

        Log::info('[Mineria] Dataset exportado en storage/'.$disk.'/'.$filename);
    }

    /** Llama al controlador que lee el CSV y aplica niveles. */
    public static function aplicarPredicciones(string $disk = 'public', string $filename = 'mineria_dataset.csv'): void
    {
        try {
            app()->make(PrediccionController::class)
                ->aplicarPrediccionesDesdeStorage($disk, $filename);

            Log::info('[Mineria] Predicciones aplicadas desde storage.');
        } catch (\Throwable $e) {
            Log::error('[Mineria] Error al aplicar predicciones: '.$e->getMessage(), ['ex' => $e]);
        }
    }

    /** Flujo completo: exportar → predecir/aplicar. */
    public static function ejecutarPipeline(string $disk = 'public', string $filename = 'mineria_dataset.csv'): void
    {
        try {
            Log::info('[Mineria] Ejecutando pipeline…');
            self::exportarDataset($disk, $filename);
            self::aplicarPredicciones($disk, $filename);
            Log::info('[Mineria] Pipeline OK.');
        } catch (\Throwable $e) {
            Log::error('[Mineria] Falló pipeline: '.$e->getMessage(), ['ex' => $e]);
        }
    }
}
