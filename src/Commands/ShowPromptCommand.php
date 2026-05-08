<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Commands;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\PromptManager;
use Illuminate\Console\Command;

final class ShowPromptCommand extends Command
{
    protected $signature = 'prompt:show {name : Prompt name} {--driver= : Driver to use (defaults to active)}';

    protected $description = 'Show parsed metadata and content of a prompt.';

    public function handle(PromptManager $manager): int
    {
        $name = (string) $this->argument('name');
        $driverOption = $this->option('driver');

        /** @var Driver $driver */
        $driver = $manager->driver(is_string($driverOption) ? $driverOption : null);

        $parsed = $driver->find($name);

        if ($parsed === null) {
            $this->components->error("Prompt [{$name}] not found.");

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
