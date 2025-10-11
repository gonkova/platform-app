<?php

use Illuminate\Support\Facades\Route;

// API login route 
Route::get('/login', function () {
    return response()->json([
        'message' => 'Please use POST /api/login for authentication'
    ], 401);
})->name('login');

Route::get('/', function () {
    return view('welcome');
});