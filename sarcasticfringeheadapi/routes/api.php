<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(BookController::class)->group(function () {
    Route::get('/', 'getAllBooks');
    Route::get('/books', 'getAllBooks');
    Route::put('/books/claim/{id}','claimABook');
    Route::put('/books/return/{id}','UnclaimABook');
    Route::post('/books', 'addABook');
    Route::get('/books/{id}','getSingleBookById');
    Route::post('/reviews','createBookReview');
});
Route::get('/genres', [GenreController::class, 'getAllGenres']);

Route::fallback(function () {
    return response()->json(['message' => 'Route not found'], 404);
});
