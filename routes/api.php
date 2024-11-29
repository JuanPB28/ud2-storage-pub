<?php

use App\Http\Controllers\CsvController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloWorldController;
use App\Http\Controllers\JsonController;

Route::apiResource('hello', HelloWorldController::class);

Route::get('/hello', [HelloWorldController::class, 'index']);
Route::post('/hello', [HelloWorldController::class, 'store']);
Route::get('/hello/{filename}', [HelloWorldController::class, 'show']);
Route::put('/hello/{filename}', [HelloWorldController::class, 'update']);
Route::delete('/hello/{filename}', [HelloWorldController::class, 'destroy']);

Route::get('/json', [JsonController::class, 'index']);
Route::post('/json', [JsonController::class, 'store']);
Route::get('/json/{id}', [JsonController::class, 'show']);
Route::put('/json/{id}', [JsonController::class, 'update']);
Route::delete('/json/{id}', [JsonController::class, 'destroy']);

Route::get('/csv', [CsvController::class, 'index']);
Route::post('/csv', [CsvController::class, 'store']);
Route::get('/csv/{id}', [CsvController::class, 'show']);
Route::put('/csv/{id}', [CsvController::class, 'update']);
Route::delete('/csv/{id}', [CsvController::class, 'destroy']);