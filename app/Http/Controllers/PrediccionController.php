<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PrediccionController extends Controller
{
    public function aplicarPrediccionesDesdeStorage()
    {
        // 1) CSV de storage (ajusta la ruta si es otra)
        $csvPath = storage_path('app/public/mineria_dataset.csv');

        // 2) Llamar a la API FastAPI
        $response = Http::timeout(60)
            ->attach('file', fopen($csvPath, 'r'), 'dataset.csv')
            ->post('http://127.0.0.1:8001/predict-csv');

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
                $nivel = strtolower(trim($p['nivel_regla_predicho'] ?? ''));

                if (!$id || !in_array($nivel, ['excelente','bueno','malo'], true)) {
                    continue; // salta registros incompletos
                }

                $diasAplazo = ($nivel === 'excelente') ? 1 : 0; // tu regla

                User::where('id_user', $id)->update([
                    'nivel_usuario' => $nivel,
                    'dias_aplazo'   => $diasAplazo,
                ]);
            }
        });

        return back()->with('success', 'Niveles actualizados según predicción.');
    }
}
