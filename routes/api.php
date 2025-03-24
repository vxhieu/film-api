<?php

use App\Http\Controllers\Api\ApiSourceController;
use App\Http\Controllers\Api\FilmController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EpisodeController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::prefix('v1')->group(function () {
        // Films routes
        Route::get('films/featured', [FilmController::class, 'featured']);
        Route::get('films/slug/{slug}', [FilmController::class, 'showBySlug']);
        Route::apiResource('films', FilmController::class);

        // Categories routes
        Route::apiResource('categories', CategoryController::class);

        // Episodes routes
        Route::apiResource('films.episodes', EpisodeController::class)->except(['show']);

        // API Sources routes
        Route::apiResource('api-sources', ApiSourceController::class);
        Route::post('api-sources/{id}/test', [ApiSourceController::class, 'test']);
        Route::post('api-sources/{id}/sync', [ApiSourceController::class, 'sync']);
    });
});
