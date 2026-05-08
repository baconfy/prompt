<?php

declare(strict_types=1);

namespace Baconfy\Prompt;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\Drivers\DatabaseDriver;
use Baconfy\Prompt\Drivers\FileDriver;
use Baconfy\Prompt\Exceptions\PromptNotFoundException;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Baconfy\Prompt\FrontMatter\Parser;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Manager;
use InvalidArgumentException;

final class PromptManager extends Manager
{
    public function getDefaultDriver(): string
    {
        $default = $this->config->get('prompt.default');

        if (! is_string($default)) {
            throw new InvalidArgumentException('Default prompt driver must be configured as a string.');
        }

        return $default;
    }

    public function source(string $name): ?ParsedFrontMatter
    {
        /** @var Driver $driver */
        $driver = $this->driver();

        return $driver->find($name);
    }

    /**
     * @param  array<string, mixed>  $data  Variables provided to render the prompt.
     *
     * @throws PromptNotFoundException
     */
    public function get(string $name, array $data = []): RenderedPrompt
    {
        $source = $this->source($name) ?? throw new PromptNotFoundException($name);

        /** @var Renderer $renderer */
        $renderer = $this->container->make(Renderer::class);

        return $renderer->render($source, $data);
    }

    /**
     * @param  string  $driver  Connection name from prompt.drivers.X.
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
            'database' => $this->createDatabaseDriverFromConfig($config),
            default => throw new InvalidArgumentException("Prompt driver type [{$type}] is not supported."),
        };
    }

    /**
     * @param  array<mixed>  $config  Driver configuration block from prompt.drivers.X.
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

    /**
     * @param  array<mixed>  $config  Driver configuration block from prompt.drivers.X.
     */
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
            table: $table,
        );
    }
}