<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloWorldController;

Route::apiResource('hello', HelloWorldController::class);
Route::get('/hello', [HelloWorldController::class, 'index']);
Route::post('/hello', [HelloWorldController::class, 'store']);
Route::get('/hello/{filename}', [HelloWorldController::class, 'show']);
Route::put('/hello/{filename}', [HelloWorldController::class, 'update']);
Route::delete('/hello/{filename}', [HelloWorldController::class, 'destroy']);