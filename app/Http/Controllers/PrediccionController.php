<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PrediccionController extends Controller
{
    // Ruta fija solicitada
    private const PREDICT_URL = 'http://127.0.0.1:8001/predict-csv';

    public function aplicarPrediccionesDesdeStorage(string $disk = 'public', string $filename = 'mineria_dataset.csv')
    {
        // 1) CSV de storage (ajusta la ruta si es otra)
        $csvPath = storage_path('app/public/mineria_dataset.csv');

        if (!file_exists($csvPath)) {
            return back()->with('error', 'No existe el archivo de dataset en storage/public/mineria_dataset.csv');
        }

        // 2) Llamar a la API FastAPI (ruta fija 8001/predict-csv)
        $response = Http::timeout(60)
            ->attach('file', fopen($csvPath, 'r'), 'dataset.csv')
            ->post(self::PREDICT_URL);

        if (!$response->ok()) {
            return back()->with('error', 'Error HTTP desde FastAPI: '.$response->status());
        }

        $json = $response->json();
        if (!data_get($json, 'ok')) {
            return back()->with('error', data_get($json, 'error', 'Error de predicción'));
        }

        $predicciones = data_get($json, 'data', []);

        // 3) Actualizar usuarios: nivel y días de aplazo
        DB::transaction(function () use ($predicciones) {
            foreach ($predicciones as $p) {
                $id = $p['id_user'] ?? null;

                // Acepta clave nueva 'nivel_regla' y la legada 'nivel_regla_predicho'
                $nivel = $p['nivel_regla'] ?? ($p['nivel_regla_predicho'] ?? null);
                $nivel = is_string($nivel) ? strtolower(trim($nivel)) : null;

                if (!$id || !in_array($nivel, ['excelente','bueno','malo'], true)) {
                    continue; // salta registros incompletos
                }

                $diasAplazo = ($nivel === 'excelente') ? 1 : 0;

                // Nota: si tu columna se llama distinto, cámbiala aquí
                User::where('id_user', $id)->update([
                    'nivel_usuario' => $nivel,  // o 'nivel_usuario' si tu esquema lo usa
                    'dias_aplazo' => $diasAplazo,
                ]);
            }
        });

        return back()->with('success', 'Niveles actualizados según predicción.');
    }
}
