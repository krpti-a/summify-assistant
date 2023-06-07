<?php

use App\Http\Controllers\EmbeddingControllers\DocumentEmbeddingController;
use App\Http\Controllers\EmbeddingControllers\WebsiteEmbeddingController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::view("/", "welcome");
Route::prefix('embedding')->group(function () {
    Route::post('/document', [DocumentEmbeddingController::class, 'store']);
    Route::post('/website', [WebsiteEmbeddingController::class, 'store']);
});
Route::get("/chat", [MessageController::class, 'index'])->name('chat.index');
Route::post("/chat", [MessageController::class, 'store'])->name('chat.store');
Route::get("/chat/{id}", [MessageController::class, 'show'])->name('chat.show');
