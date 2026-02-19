<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [UploadController::class, 'index'])->name('upload');
});

require __DIR__.'/auth.php';
