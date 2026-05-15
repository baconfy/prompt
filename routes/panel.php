<?php

declare(strict_types=1);

use Baconfy\Prompt\Http\Controllers\EditorController;
use Baconfy\Prompt\Http\Controllers\IndexController;
use Baconfy\Prompt\Http\Controllers\VersionsController;
use Baconfy\Prompt\Http\Middleware\Authorize;
use Illuminate\Support\Facades\Route;

/** @var array<int, string> $middleware */
$middleware = (array) config('prompt.panel.middleware', ['web']);

Route::prefix((string) config('prompt.panel.path', '_prompts'))
    ->middleware([...$middleware, Authorize::class])
    ->name('prompts.')
    ->group(function (): void {
        Route::get('/', IndexController::class)->name('index');

        Route::get('/create', [EditorController::class, 'create'])->name('create');
        Route::get('/{prompt}/edit', [EditorController::class, 'edit'])->name('edit');
        Route::post('/save', [EditorController::class, 'save'])->name('save');
        Route::post('/preview', [EditorController::class, 'preview'])->name('preview');

        Route::get('/{prompt}/versions', [VersionsController::class, 'index'])->name('versions');
        Route::post('/{prompt}/versions/{version}/restore', [VersionsController::class, 'restore'])->name('versions.restore');
        Route::delete('/{prompt}/versions/{version}', [VersionsController::class, 'destroy'])->name('versions.destroy');
    });
