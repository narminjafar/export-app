<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('/download', [UploadController::class, 'download'])->name('download.file');

Route::middleware(['jwt'])->group(function () {
     Route::post('/upload', [UploadController::class, 'upload']);
});
