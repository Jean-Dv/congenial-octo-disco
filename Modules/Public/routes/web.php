<?php

use Illuminate\Support\Facades\Route;
use Modules\Public\Http\Controllers\HomeController;

Route::middleware('guest')->group(function () {
    Route::get('/', HomeController::class)->name('public.home');
}); 