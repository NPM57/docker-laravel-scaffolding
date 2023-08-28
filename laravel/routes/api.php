<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('auth:sanctum')->group(function () {
    // Company API
    Route::get('/company', [App\Http\Controllers\CompanyController::class, 'index']);
    Route::post('/company/create', [App\Http\Controllers\CompanyController::class, 'store']);
    Route::post('/company/edit', [App\Http\Controllers\CompanyController::class, 'update']);
    Route::delete('/company', [App\Http\Controllers\CompanyController::class, 'destroy']);

    // Employee API
    Route::get('/employee', [App\Http\Controllers\EmployeeController::class, 'index']);
    Route::post('/employee/create', [App\Http\Controllers\EmployeeController::class, 'store']);
    Route::post('/employee/edit', [App\Http\Controllers\EmployeeController::class, 'update']);
    Route::delete('/employee', [App\Http\Controllers\EmployeeController::class, 'destroy']);
});
