<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Commands;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\PromptManager;
use Illuminate\Console\Command;

final class ListPromptsCommand extends Command
{
    protected $signature = 'prompt:list {driver? : Driver name (defaults to all configured drivers)}';

    protected $description = 'List all available prompts.';

    public function handle(PromptManager $manager): int
    {
        $driverArg = $this->argument('driver');

        if (is_string($driverArg)) {
            $this->listDriverPrompts($manager, $driverArg);

            return self::SUCCESS;
        }

        /** @var array<string, mixed> $drivers */
        $drivers = config('prompt.drivers', []);

        foreach (array_keys($drivers) as $name) {
            $this->listDriverPrompts($manager, $name);
        }

        return self::SUCCESS;
    }

    private function listDriverPrompts(PromptManager $manager, string $driverName): void
    {
        /** @var Driver $driver */
        $driver = $manager->driver($driverName);
        $prompts = $driver->all();

        $this->components->twoColumnDetail($driverName, count($prompts).' prompts');

        foreach ($prompts as $name) {
            $this->line('  '.$name);
        }

        if (! empty($prompts)) {
            $this->newLine();
        }
    }
}
