<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

Route::post('/upload', [UploadController::class, 'store']);
Route::get('/files', [UploadController::class, 'getFiles']);
Route::delete('/files/{id}', [UploadController::class, 'delete']);
