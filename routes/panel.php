<?php

declare(strict_types=1);

use Baconfy\Prompt\Http\Middleware\Authorize;
use Baconfy\Prompt\Livewire\Editor;
use Baconfy\Prompt\Livewire\Index;
use Baconfy\Prompt\Livewire\Versions;
use Illuminate\Support\Facades\Route;

/** @var array<int, string> $middleware */
$middleware = (array) config('prompt.panel.middleware', ['web']);

Route::prefix((string) config('prompt.panel.path', '_prompts'))
    ->middleware([...$middleware, Authorize::class])
    ->name('prompts.')
    ->group(function (): void {
        Route::get('/', Index::class)->name('index');
        Route::get('/create', Editor::class)->name('create');
        Route::get('/{prompt}/edit', Editor::class)->name('edit');
        Route::get('/{prompt}/versions', Versions::class)->name('versions');
    });
