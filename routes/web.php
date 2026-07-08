<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BangKeController;

Route::get('/', [
    BangKeController::class,
    'index'
]);

Route::post('/upload', [
    BangKeController::class,
    'upload'
])->name('upload');

Route::post('/generate', [
    BangKeController::class,
    'generate'
])->name('generate');