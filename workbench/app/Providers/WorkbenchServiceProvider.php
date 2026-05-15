<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Baconfy\Prompt\Panel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningUnitTests()) {
            return;
        }

        $projectRoot = dirname(__DIR__, 3);

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $projectRoot.'/workbench/database/database.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $this->app['config']->set('prompt.default', 'database');
    }

    public function boot(): void
    {
        if ($this->app->runningUnitTests()) {
            return;
        }

        Panel::auth(fn ($user = null) => true);

        Route::get('/', function () {
            return redirect(config('prompt.panel.path', '_prompts'));
        });
    }
}
