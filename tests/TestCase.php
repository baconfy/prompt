<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Tests;

use Baconfy\Prompt\PromptServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Retrieves the package service providers to be registered with the application.
     *
     * @param  Application  $app  The application instance.
     * @return array An array of package service provider class names.
     */
    protected function getPackageProviders($app): array
    {
        return [
            PromptServiceProvider::class
        ];
    }
}