<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\StatisticController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('authors', AuthorController::class);
Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);
Route::get('books/low-stock', [BookController::class, 'lowStock']);
Route::apiResource('books', BookController::class);
Route::get('statistics', StatisticController::class);
