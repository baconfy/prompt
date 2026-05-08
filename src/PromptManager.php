<?php

declare(strict_types=1);

namespace Baconfy\Prompt;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\Drivers\FileDriver;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Baconfy\Prompt\FrontMatter\Parser;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Manager;
use InvalidArgumentException;

final class PromptManager extends Manager
{
    /**
     * Retrieves the name of the default prompt driver.
     *
     * @return string The configured default prompt driver name.
     *
     * @throws InvalidArgumentException If the default driver value is not a string.
     */
    public function getDefaultDriver(): string
    {
        $default = $this->config->get('prompt.default');

        if (! is_string($default)) {
            throw new InvalidArgumentException('Default prompt driver must be configured as a string.');
        }

        return $default;
    }

    /**
     * Retrieves the parsed front matter associated with the given source name.
     *
     * @param  string  $name  The name of the source to retrieve.
     * @return ParsedFrontMatter|null The parsed front matter if found, or null if not found.
     */
    public function source(string $name): ?ParsedFrontMatter
    {
        /** @var Driver $driver */
        $driver = $this->driver();

        return $driver->find($name);
    }

    /**
     * Creates and returns a driver instance based on the provided driver type.
     *
     * @param  string  $driver  The name of the driver to be created.
     * @return Driver The created driver instance.
     *
     * @throws InvalidArgumentException If the driver is not configured or the driver type is unsupported.
     * @throws BindingResolutionException
     */
    protected function createDriver($driver): Driver
    {
        $config = $this->config->get("prompt.drivers.{$driver}");

        if (! is_array($config)) {
            throw new InvalidArgumentException("Prompt driver [{$driver}] is not configured.");
        }

        $type = $config['driver'] ?? null;

        if (! is_string($type)) {
            throw new InvalidArgumentException("Prompt driver [{$driver}] is missing a 'driver' type.");
        }

        return match ($type) {
            'file' => $this->createFileDriverFromConfig($config),
            default => throw new InvalidArgumentException("Prompt driver type [{$type}] is not supported."),
        };
    }

    /**
     * Creates and configures a new instance of FileDriver using the provided configuration.
     *
     * @param  array<mixed>  $config  An associative array containing the configuration options. Must include a "folder" key with a string value.
     * @return FileDriver  The created FileDriver instance based on the given configuration.
     *
     * @throws InvalidArgumentException  If the "folder" configuration is missing or is not a string.
     * @throws BindingResolutionException
     */
    private function createFileDriverFromConfig(array $config): FileDriver
    {
        $folder = $config['folder'] ?? null;

        if (! is_string($folder)) {
            throw new InvalidArgumentException('File driver requires a "folder" configuration string.');
        }

        return new FileDriver(
            files: $this->container->make(Filesystem::class),
            parser: $this->container->make(Parser::class),
            folder: $folder,
        );
    }
}