<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PostApiController;

/*
|--------------------------------------------------------------------------
| API Routes — Tripmo (untuk mobile app Flutter)
| Semua di-prefix /api. Auth memakai Bearer token (Sanctum).
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/ping', fn () => response()->json(['status' => 'ok', 'app' => 'Tripmo API']));

// ── Publik ──
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login',    [AuthApiController::class, 'login']);

Route::get('/posts',          [PostApiController::class, 'index']);
Route::get('/posts/{id}',     [PostApiController::class, 'show']);
Route::get('/users/{id}',     [PostApiController::class, 'userProfile']);

// ── Butuh token (Bearer) ──
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user',    [AuthApiController::class, 'me']);
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // --- Rute Profil User ---
    // Gunakan POST dengan _method=PUT dari Flutter untuk update profil yang mengandung gambar
    Route::post('/user/update', [AuthApiController::class, 'updateProfile']); 
    Route::delete('/user', [AuthApiController::class, 'destroyAccount']);

    // --- Rute Postingan ---
    Route::post('/posts',            [PostApiController::class, 'store']);
    Route::get('/posts/{id}',        [PostApiController::class, 'show']); // Pastikan ini bisa diakses jika butuh token
    
    // Perbaikan Error Edit Postingan: 
    // Laravel router akan menangkap request POST dari Flutter yang berisi '_method' => 'PUT'
    Route::put('/posts/{id}',        [PostApiController::class, 'update']); 
    
    Route::delete('/posts/{id}',     [PostApiController::class, 'destroy']);
    Route::post('/posts/{id}/rate',  [PostApiController::class, 'rate']);
});
