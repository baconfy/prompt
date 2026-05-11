<?php

declare(strict_types=1);

namespace Baconfy\Prompt;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class PromptServiceProvider extends ServiceProvider
{
    /**
     * Registers the service provider by merging the configuration file and binding the PromptManager to the service container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/prompt.php', 'prompt');

        $this->app->singleton('prompt', fn (Application $app) => new PromptManager($app));
        $this->app->alias('prompt', PromptManager::class);
    }

    public function boot(): void
    {
        if (empty(glob(database_path('migrations/*_create_prompts_table.php')))) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/prompt.php' => config_path('prompt.php')], 'prompt-config');

            $this->publishesMigrations([__DIR__.'/../database/migrations' => database_path('migrations')], 'prompt-migrations');

            $this->commands([
                Commands\ListPromptsCommand::class,
                Commands\MakePromptCommand::class,
                Commands\ShowPromptCommand::class,
            ]);
        }
    }
}