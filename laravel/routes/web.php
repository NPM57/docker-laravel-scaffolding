<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $user = Auth::user();
    if ($user) {
        return view('components.company');
    }
    return view('welcome');
});

Auth::routes();

Route::get('/company', [App\Http\Controllers\HomeController::class, 'company'])->name('company');
Route::get('/employee', [App\Http\Controllers\HomeController::class, 'employee'])->name('employee');
