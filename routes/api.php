<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::delete('/delete/user', [AuthController::class,'deleteUser']);

Route::post('/login',[AuthController::class,'login'])->name('login');
Route::post('/register',[AuthController::class,'register'])->name('register');
Route::middleware('auth:api')->get('user-details',[AuthController::class,'user'])->name('user-details');
Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->post('update/user', [AuthController::class, 'updateUser']);

Route::get('auth/{provider}', [AuthController::class,'redirect'])->middleware('web');
Route::get('auth/{provider}/callback', [AuthController::class,'handle']);




Route::get('/health-data', [DataController::class, 'index']);
Route::post('/health-data/analyze/{data_id}', [DataController::class, 'analyze']);
