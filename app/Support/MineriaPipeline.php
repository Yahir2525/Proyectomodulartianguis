<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PrediccionController; // <-- IMPORTA EL CONTROLLER
use Carbon\Carbon;

class MineriaPipeline
{
    public static function exportarDataset(string $disk = 'public', string $filename = 'mineria_dataset.csv'): void
    {
        $tmp = fopen('php://temp', 'w+');

        // Cabecera EXACTA esperada por el entrenamiento/inferencia (inputs9)
        fputcsv($tmp, [
            'id_user', 'nombre_usuario',
            'dias_aplazo',
            'total_creditos', 'creditos_activos', 'creditos_vencidos', 'creditos_liquidados',
            'total_abonos', 'total_abonado', 'promedio_abonos', 'ultimo_abono_fecha',
        ]);

        $usuarios = User::with(['creditos.abonos'])->get();

        foreach ($usuarios as $user) {
            $creditos = $user->creditos ?? collect();

            $activosCount = (int) $creditos->where('estado', 1)->count();
            $vencidosCount = (int) $creditos->where('estado', 1)->where('fecha_vencimiento', '<', now())->count();
            $liquidadosCount = (int) $creditos->where('saldo_total', 0)->whereNotNull('fecha_liquidacion')->count();

            $totalCreditos = $activosCount + $vencidosCount + $liquidadosCount;

            $sumaAbonos = 0.0;
            $conteoAbonos = 0;
            $ultimoAbono = null;

            foreach ($creditos as $c) {
                foreach ($c->abonos as $abono) {
                    $sumaAbonos += (float) $abono->monto_abono;
                    $conteoAbonos++;
                    if (!$ultimoAbono || $abono->created_at > $ultimoAbono) {
                        $ultimoAbono = $abono->created_at;
                    }
                }
            }

            $promedioAbonos = $conteoAbonos > 0 ? ($sumaAbonos / $conteoAbonos) : 0.0;
            $ultimoAbonoStr = $ultimoAbono ? Carbon::parse($ultimoAbono)->format('d/m/Y') : null;

            fputcsv($tmp, [
                $user->id_user,
                (string) $user->nombre_usuario,
                (int) ($user->dias_aplazo ?? 0),

                $totalCreditos,
                $activosCount,
                $vencidosCount,
                $liquidadosCount,

                (int) $conteoAbonos,
                (float) $sumaAbonos,
                (float) $promedioAbonos,
                $ultimoAbonoStr,
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
            // OPCIÓN 1 (recomendada): instanciar vía contenedor y llamar método de instancia
            app()->make(PrediccionController::class)
                ->aplicarPrediccionesDesdeStorage($disk, $filename);

            // --- OPCIÓN 2 (equivalente): usar app()->call con parámetros ---
            // app()->call([PrediccionController::class, 'aplicarPrediccionesDesdeStorage'], [
            //     'disk' => $disk,
            //     'filename' => $filename,
            // ]);

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
