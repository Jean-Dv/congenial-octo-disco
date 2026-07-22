<?php

use Illuminate\Support\Facades\Route;
use Modules\Public\Http\Controllers\DownloadsController;
use Modules\Public\Http\Controllers\HomeController;
use Modules\Public\Http\Controllers\NewsController;
use Modules\Public\Http\Controllers\NewsDetailController;

Route::middleware('guest')->group(function () {
    Route::get('/', HomeController::class)->name('public.home');
    Route::get('/news', NewsController::class)->middleware('module:news')->name('public.news');
    Route::get('/news/{slug}', NewsDetailController::class)->middleware('module:news')->name('public.news.show');
    Route::get('/downloads', DownloadsController::class)->name('public.downloads');
});
