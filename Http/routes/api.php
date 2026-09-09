<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROUTE MODUL Equipment (konvensi core: api/v1 + auth:sanctum)
|--------------------------------------------------------------------------
|   /api/v1/equipments
|     GET    /   equipment:view
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('equipments')->group(function () {
        // Fase 1: CRUD katalog equipment menyusul.
    });
});
