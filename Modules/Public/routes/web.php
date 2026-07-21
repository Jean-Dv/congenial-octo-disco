<?php

use Illuminate\Support\Facades\Route;
use Modules\Public\Http\Controllers\HomeController;
use Modules\Public\Http\Controllers\NewsController;

Route::middleware('guest')->group(function () {
    Route::get('/', HomeController::class)->name('public.home');
    Route::get('/news', NewsController::class)->name('public.news');
});
