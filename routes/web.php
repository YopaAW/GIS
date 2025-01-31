<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Redirect root ke login
Route::get('/', function () {
    return redirect('/login');
});

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/map', [LocationController::class, 'index']);
    Route::get('/map/create', [LocationController::class, 'create']);
    Route::post('/map', [LocationController::class, 'store']);
    Route::get('/map/{id}/edit', [LocationController::class, 'edit'])->name('map.edit');
    Route::put('/map/{id}', [LocationController::class, 'update'])->name('map.update');
    Route::delete('/map/{location}', [LocationController::class, 'destroy']);
});
