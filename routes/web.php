<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])
    ->name('home');

Route::get('/messages', [HomeController::class, 'messages'])
    ->name('messages');

Route::post('/message', [HomeController::class, 'message'])
    ->name('message');

Route::get('/messages/{receiver_id}', [HomeController::class, 'getMessages']);
Route::post('/message', [HomeController::class, 'store']);



Route::get('/users', [HomeController::class, 'users'])
    ->name('users');
Route::get('/users/{id}', [HomeController::class, 'getUser']);
