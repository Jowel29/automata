<?php

use App\Http\Controllers\ExcelController;
use App\Http\Controllers\ExtractController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UploadController::class, 'showUploadForm']);
Route::post('/pdf/upload', [UploadController::class, 'uploadPDFs']);
Route::get('/pdf/fields', [ExtractController::class, 'showFieldsForm']);
Route::post('/pdf/extract', [ExtractController::class, 'extractData']);
Route::get('/pdf/export', [ExcelController::class, 'downloadExcel']);
