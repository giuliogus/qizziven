<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::controller(ApiController::class)->group(function () {
    Route::post('/login', 'login')->name('login');
    Route::post('/logout', 'logout')->name('logout');
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', 'user')->name('user');

    });
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(GameController::class)->group(function () {
        Route::prefix('game')->group(function () {
            Route::get('/', 'status')->name('game_status');
            Route::post('/', 'create')->name('game_create');
            Route::delete('/', 'over')->name('game_over');
            Route::put('/close', 'close')->name('game_close');
        });
    });
    Route::controller(AnswerController::class)->group(function () {
        Route::prefix('answer')->group(function () {
            Route::post('/', 'create')->name('answer_create');
            Route::put('/', 'update')->name('answer_update');
            Route::put('/evaluate', 'evaluate')->name('answer_evaluate');
        });
    });
});
