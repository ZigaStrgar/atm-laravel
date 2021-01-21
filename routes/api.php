<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('users/{user}', [\App\Http\Controllers\UsersController::class, 'show']);
Route::post('users', [\App\Http\Controllers\UsersController::class, 'store']);
Route::patch('users/{user}', [\App\Http\Controllers\UsersController::class, 'update']);

Route::post('users/{user}/deposit', [\App\Http\Controllers\TransactionsController::class, 'deposit']);
Route::post('users/{user}/withdraw', [\App\Http\Controllers\TransactionsController::class, 'withdraw']);
