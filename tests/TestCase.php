<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Tests;

use Baconfy\Prompt\PromptServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Retrieves the package service providers to be registered with the application.
     *
     * @param  Application  $app  The application instance.
     * @return array<int, class-string<ServiceProvider>> An array of package service provider class names.
     */
    protected function getPackageProviders($app): array
    {
        return [
            PromptServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }
}
