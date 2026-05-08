<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Commands;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\PromptManager;
use Illuminate\Console\Command;

final class ShowPromptCommand extends Command
{
    /**
     * The signature of the command defining the required and optional arguments and options.
     *
     * {name: Prompt name}
     * {--driver=: Driver to use (defaults to active)}
     */
    protected $signature = 'prompt:show {name : Prompt name} {--driver= : Driver to use (defaults to active)}';

    /**
     * The description of the command that provides an overview of its purpose.
     *
     * Show parsed metadata and content of a prompt.
     */
    protected $description = 'Show parsed metadata and content of a prompt.';

    /**
     * Handles the main execution flow for processing a prompt using the given manager.
     *
     * @param  PromptManager  $manager  The prompt manager instance used to retrieve and process the specified prompt.
     * @return int Returns a constant indicating the success or failure of the operation.
     */
    public function handle(PromptManager $manager): int
    {
        $rawName = $this->argument('name');
        if (! is_string($rawName)) {
            return self::FAILURE;
        }

        $driverOption = $this->option('driver');

        /** @var Driver $driver */
        $driver = $manager->driver(is_string($driverOption) ? $driverOption : null);

        $parsed = $driver->find($rawName);

        if ($parsed === null) {
            $this->components->error("Prompt [{$rawName}] not found.");

            return self::FAILURE;
        }

        $this->line('Metadata:');

        if (empty($parsed->metadata)) {
            $this->line('  (no metadata)');
        } else {
            foreach ($parsed->metadata as $key => $value) {
                $display = is_string($value) ? $value : (string) json_encode($value);
                $this->line("  {$key}: {$display}");
            }
        }

        $this->newLine();
        $this->line('Content:');
        $this->line($parsed->content);

        return self::SUCCESS;
    }
}
