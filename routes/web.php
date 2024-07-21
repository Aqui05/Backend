<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\DataController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('analyze/{id}', [DataController::class, 'analyze']);
