<?php

declare(strict_types=1);

namespace Baconfy\Prompt;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\Drivers\DatabaseDriver;
use Baconfy\Prompt\Drivers\FileDriver;
use Baconfy\Prompt\Exceptions\PromptNotFoundException;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Baconfy\Prompt\FrontMatter\Parser;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Manager;
use InvalidArgumentException;

final class PromptManager extends Manager
{
    /**
     * Retrieves the default prompt driver name from the configuration.
     *
     * @return string The name of the default prompt driver.
     *
     * @throws InvalidArgumentException If the default driver is not configured as a string.
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
     * Retrieves the parsed front matter for the given source name.
     *
     * @param  string  $name  The name of the source to lookup.
     * @return ParsedFrontMatter|null The parsed front matter if found, or null otherwise.
     */
    public function source(string $name): ?ParsedFrontMatter
    {
        /** @var Driver $driver */
        $driver = $this->driver();

        return $driver->find($name);
    }

    /**
     * Retrieves and renders a prompt based on the given name and data.
     *
     * @param  string  $name  The name of the prompt to retrieve.
     * @param  array  $data  The contextual data to be used during rendering.
     * @return RenderedPrompt The rendered prompt object.
     *
     * @throws BindingResolutionException
     */
    /** @param array<string, mixed> $data */
    public function get(string $name, array $data = []): RenderedPrompt
    {
        $source = $this->source($name) ?? throw new PromptNotFoundException($name);

        /** @var Renderer $renderer */
        $renderer = $this->container->make(Renderer::class);

        return $renderer->render($source, $data);
    }

    /**
     * Creates a driver instance based on the specified driver configuration.
     *
     * @param  mixed  $driver  The identifier for the driver to be created.
     * @return Driver  The created driver instance.
     *
     * @throws InvalidArgumentException If the driver is not configured properly or the driver type is unsupported.
     */
    protected function createDriver($driver): Driver
    {
        if (! is_string($driver)) {
            throw new InvalidArgumentException('Driver name must be a string.');
        }

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
            'database' => $this->createDatabaseDriverFromConfig($config),
            default => throw new InvalidArgumentException("Prompt driver type [{$type}] is not supported."),
        };
    }

    /**
     * Creates an instance of FileDriver based on the provided configuration.
     *
     * @param  array  $config  The configuration array containing driver settings.
     * @return FileDriver The created FileDriver instance.
     *
     * @throws InvalidArgumentException If the 'folder' configuration is missing or not a string.
     * @throws BindingResolutionException
     */
    /** @param array<string, mixed> $config */
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

    /**
     * Creates a new instance of the DatabaseDriver using the provided configuration.
     *
     * @param  array  $config  The configuration array containing settings for the database driver.
     *                         Required keys:
     *                         - 'table': A string specifying the name of the database table.
     *                         Optional keys:
     *                         - 'connection': A string specifying the database connection name.
     * @return DatabaseDriver  The constructed DatabaseDriver instance.
     *
     * @throws InvalidArgumentException If the 'table' configuration is missing or not a string.
     * @throws BindingResolutionException
     */
    /** @param array<string, mixed> $config */
    private function createDatabaseDriverFromConfig(array $config): DatabaseDriver
    {
        $table = $config['table'] ?? null;

        if (! is_string($table)) {
            throw new InvalidArgumentException('Database driver requires a "table" configuration string.');
        }

        /** @var string|null $connection */
        $connection = $config['connection'] ?? null;

        /** @var DatabaseManager $db */
        $db = $this->container->make('db');

        return new DatabaseDriver(
            connection: $db->connection($connection),
            parser: $this->container->make(Parser::class),
            table: $table,
        );
    }
}