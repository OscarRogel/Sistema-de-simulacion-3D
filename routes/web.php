<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\McqPortalController;

Route::get('/', [McqPortalController::class, 'index']);

Route::prefix('api/mcq')->group(function () {
    Route::get('/bootstrap', [McqPortalController::class, 'bootstrap']);
    Route::post('/login', [McqPortalController::class, 'login']);
    Route::post('/tests', [McqPortalController::class, 'saveTest']);
    Route::delete('/tests/{id}', [McqPortalController::class, 'deleteTest']);
    Route::post('/seasons', [McqPortalController::class, 'addSeason']);
    Route::post('/brands', [McqPortalController::class, 'addBrand']);
    Route::post('/boxes', [McqPortalController::class, 'addBox']);
    Route::post('/users', [McqPortalController::class, 'addUser']);
    Route::delete('/users/{username}', [McqPortalController::class, 'deleteUser']);
    Route::delete('/boxes/{id}', [McqPortalController::class, 'deleteBox']);
    Route::delete('/seasons/{code}', [McqPortalController::class, 'deleteSeason']);
    Route::delete('/brands/{id}', [McqPortalController::class, 'deleteBrand']);
    Route::get('/users', [McqPortalController::class, 'getUsers']);
    Route::post('/users', [McqPortalController::class, 'storeUser']);
    Route::delete('/users/{username}', [McqPortalController::class, 'deleteUser']);
});