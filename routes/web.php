<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportTemplateController;
use App\Http\Controllers\GradeExportController;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/import-template', [ImportTemplateController::class, 'importTemplate'])
        ->name('import.template');

    Route::get('/export-grades-csv', [GradeExportController::class, 'exportGradesCsv'])
        ->name('export.grades.csv');
});


