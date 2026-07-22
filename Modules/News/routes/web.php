<?php

use Illuminate\Support\Facades\Route;
use Modules\News\Http\Controllers\Admin\MarkdownPreviewController;
use Modules\News\Http\Controllers\Admin\NewsCategoryController;
use Modules\News\Http\Controllers\Admin\NewsController;

Route::middleware(['auth', 'admin', 'module:news'])->prefix('admin/news')->name('admin.news.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/create', [NewsController::class, 'create'])->name('create');
    Route::post('/', [NewsController::class, 'store'])->name('store');
    Route::post('/preview', MarkdownPreviewController::class)->name('preview');
    Route::get('/categories', [NewsCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [NewsCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [NewsCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [NewsCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/{news}/edit', [NewsController::class, 'edit'])->name('edit');
    Route::put('/{news}', [NewsController::class, 'update'])->name('update');
    Route::delete('/{news}', [NewsController::class, 'destroy'])->name('destroy');
});
