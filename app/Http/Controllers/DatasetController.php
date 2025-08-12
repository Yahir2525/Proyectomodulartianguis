<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class DatasetController extends Controller
{
    public function exportAndDownload()
    {
        // Ejecuta tu comando: crea/actualiza storage/app/public/mineria_dataset.csv
        Artisan::call('export:dataset-mineria');

        $path = 'mineria_dataset.csv';

        abort_unless(Storage::disk('public')->exists($path), 404, 'El CSV no se generó.');

        return Storage::disk('public')->download($path, 'mineria_dataset.csv', [
            'Content-Type'       => 'text/csv; charset=UTF-8',
            'Cache-Control'      => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'             => 'no-cache',
            'Expires'            => '0',
            'X-Accel-Buffering'  => 'no',
        ]);
    }

    public function download()
    {
        $path = 'mineria_dataset.csv';

        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path, 'mineria_dataset.csv', [
            'Content-Type'       => 'text/csv; charset=UTF-8',
            'Cache-Control'      => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'             => 'no-cache',
            'Expires'            => '0',
            'X-Accel-Buffering'  => 'no',
        ]);
    }
}
