<?php

declare(strict_types=1);

namespace Baconfy\Prompt;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class PromptServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/prompt.php', 'prompt');

        $this->app->singleton('prompt', fn (Application $app) => new PromptManager($app));
        $this->app->alias('prompt', PromptManager::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/prompt.php' => config_path('prompt.php')], 'prompt-config');
        }
    }
}