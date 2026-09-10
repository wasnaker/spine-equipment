<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Equipment\Http\Controllers\CustomerEquipmentController;
use Modules\Equipment\Http\Controllers\EquipmentCategoryController;
use Modules\Equipment\Http\Controllers\EquipmentController;
use Modules\Equipment\Http\Controllers\EquipmentGroupController;
use Modules\Equipment\Http\Controllers\EquipmentSubgroupController;

/*
|--------------------------------------------------------------------------
| ROUTE MODUL Equipment (konvensi core: api/v1 + auth:sanctum)
|--------------------------------------------------------------------------
| Middleware permission:feature:capability (gate per aksi). Semua resource
| katalog memakai permission family equipment:* (satu permission set).
|
|   /api/v1/equipments            item katalog (Group -> Subgroup -> Item)
|   /api/v1/equipment-groups      level group
|   /api/v1/equipment-subgroups   level subgroup (filter ?group_id=)
|   /api/v1/equipment-categories  atribut item
|
|     GET    /                    equipment:view
|     POST   /                    equipment:create
|     GET    /{id}                equipment:view
|     PUT    /{id}                equipment:edit
|     DELETE /{id}                equipment:delete
*/

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('equipment-groups')->group(function () {
        Route::get('/', [EquipmentGroupController::class, 'index'])->middleware('permission:equipment:view');
        Route::post('/', [EquipmentGroupController::class, 'store'])->middleware('permission:equipment:create');
        Route::get('/{id}', [EquipmentGroupController::class, 'show'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::get('/{id}/subgroups', [EquipmentGroupController::class, 'subgroups'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::get('/{id}/activity-logs', [EquipmentGroupController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::put('/{id}', [EquipmentGroupController::class, 'update'])->whereNumber('id')->middleware('permission:equipment:edit');
        Route::delete('/{id}', [EquipmentGroupController::class, 'destroy'])->whereNumber('id')->middleware('permission:equipment:delete');
    });

    Route::prefix('equipment-subgroups')->group(function () {
        Route::get('/', [EquipmentSubgroupController::class, 'index'])->middleware('permission:equipment:view');
        Route::post('/', [EquipmentSubgroupController::class, 'store'])->middleware('permission:equipment:create');
        Route::get('/{id}', [EquipmentSubgroupController::class, 'show'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::get('/{id}/categories', [EquipmentSubgroupController::class, 'categories'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::get('/{id}/activity-logs', [EquipmentSubgroupController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::put('/{id}', [EquipmentSubgroupController::class, 'update'])->whereNumber('id')->middleware('permission:equipment:edit');
        Route::delete('/{id}', [EquipmentSubgroupController::class, 'destroy'])->whereNumber('id')->middleware('permission:equipment:delete');
    });

    Route::prefix('equipment-categories')->group(function () {
        Route::get('/', [EquipmentCategoryController::class, 'index'])->middleware('permission:equipment:view');
        Route::post('/', [EquipmentCategoryController::class, 'store'])->middleware('permission:equipment:create');
        Route::get('/{id}', [EquipmentCategoryController::class, 'show'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::get('/{id}/equipments', [EquipmentCategoryController::class, 'equipments'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::get('/{id}/activity-logs', [EquipmentCategoryController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::put('/{id}', [EquipmentCategoryController::class, 'update'])->whereNumber('id')->middleware('permission:equipment:edit');
        Route::delete('/{id}', [EquipmentCategoryController::class, 'destroy'])->whereNumber('id')->middleware('permission:equipment:delete');
    });

    Route::prefix('equipments')->group(function () {
        Route::get('/', [EquipmentController::class, 'index'])->middleware('permission:equipment:view');
        Route::post('/', [EquipmentController::class, 'store'])->middleware('permission:equipment:create');
        Route::get('/{id}', [EquipmentController::class, 'show'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::put('/{id}', [EquipmentController::class, 'update'])->whereNumber('id')->middleware('permission:equipment:edit');
        Route::get('/{id}/activity-logs', [EquipmentController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:equipment:view');
        Route::delete('/{id}', [EquipmentController::class, 'destroy'])->whereNumber('id')->middleware('permission:equipment:delete');
    });

    Route::prefix('customer-equipments')->group(function () {
        Route::get('/', [CustomerEquipmentController::class, 'index'])->middleware('permission:customer-equipment:view');
        Route::post('/', [CustomerEquipmentController::class, 'store'])->middleware('permission:customer-equipment:create');
        Route::get('/{id}', [CustomerEquipmentController::class, 'show'])->whereNumber('id')->middleware('permission:customer-equipment:view');
        Route::put('/{id}', [CustomerEquipmentController::class, 'update'])->whereNumber('id')->middleware('permission:customer-equipment:edit');
        Route::get('/{id}/activity-logs', [CustomerEquipmentController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:customer-equipment:view');
        Route::delete('/{id}', [CustomerEquipmentController::class, 'destroy'])->whereNumber('id')->middleware('permission:customer-equipment:delete');
    });
});
