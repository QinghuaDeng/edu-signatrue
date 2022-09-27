<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [\App\Http\Controllers\UserController::class, 'login']);
Route::middleware('auth.signatrue')->get('/userinfo', [\App\Http\Controllers\UserController::class, 'userInfo']);
