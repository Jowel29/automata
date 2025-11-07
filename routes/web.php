<?php

use App\Http\Controllers\ExcelController;
use App\Http\Controllers\ExtractController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UploadController::class, 'showUploadForm'])->name('pdf.upload');
Route::post('/pdf/upload', [UploadController::class, 'uploadPDFs'])->name('pdf.upload.post');
Route::get('/pdf/fields', [ExtractController::class, 'showFieldsForm'])->name('pdf.fields');
Route::post('/pdf/extract', [ExtractController::class, 'extractData'])->name('pdf.extract');
Route::get('/pdf/results', [ExtractController::class, 'showResults'])->name('pdf.results');
Route::get('/pdf/export', [ExcelController::class, 'downloadExcel'])->name('pdf.export');
Route::post('/pdf/export', [ExcelController::class, 'downloadExcel'])->name('pdf.export.post');
