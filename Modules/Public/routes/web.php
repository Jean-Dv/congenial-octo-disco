<?php

use Illuminate\Support\Facades\Route;
use Modules\Public\Http\Controllers\DownloadsController;
use Modules\Public\Http\Controllers\HomeController;
use Modules\Public\Http\Controllers\NewsController;
use Modules\Public\Http\Controllers\NewsDetailController;

Route::get('/', HomeController::class)->name('public.home');
Route::get('/news', NewsController::class)->middleware('module:news')->name('public.news');
Route::get('/news/{slug}', NewsDetailController::class)->middleware('module:news')->name('public.news.show');
Route::get('/downloads', DownloadsController::class)->name('public.downloads');

# Redirect routes normally for the public downloads
Route::permanentRedirect(
    '/client_complete',
    'https://drive.google.com/file/d/1QTRL5GJRDt1FM2bSCZPfwtkERWe7abR_/view?usp=sharing'
)->name('public.downloads.client_complete');

Route::permanentRedirect(
    '/launcher',
    'https://drive.google.com/file/d/1QTRL5GJRDt1FM2bSCZPfwtkERWe7abR_/view?usp=sharing'
)->name('public.downloads.launcher');

Route::permanentRedirect(
    '/addons',
    'https://drive.google.com/file/d/1E0rWtQ2YQfTnO4P0y4GuXEHMxKqeqb2b/view?usp=drive_link'
)->name('public.downloads.addons');

Route::permanentRedirect(
    '/discord',
    'https://discord.gg/qEMz5XUAAY'
)->name('public.discord');