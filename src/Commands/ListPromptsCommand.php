<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Commands;

use Baconfy\Prompt\PromptManager;
use Illuminate\Console\Command;

final class ListPromptsCommand extends Command
{
    protected $signature = 'prompt:list {driver? : Driver name (defaults to all configured drivers)}';

    /**
     * The description for the command to list all available prompts.
     *
     * @var string $description Describes the purpose of the command.
     */
    protected $description = 'List all available prompts.';

    /**
     * Handles the processing of driver-related prompts by using the provided PromptManager instance.
     *
     * @param  PromptManager  $manager  The instance responsible for managing and processing prompts.
     * @return int Returns a success constant indicating the operation's completion status.
     */
    public function handle(PromptManager $manager): int
    {
        $driverArg = $this->argument('driver');

        if (is_string($driverArg)) {
            $this->listDriverPrompts($manager, $driverArg);

            return self::SUCCESS;
        }

        $drivers = config('prompt.drivers', []);
        if (! is_array($drivers)) {
            return self::SUCCESS;
        }

        foreach (array_keys($drivers) as $name) {
            $this->listDriverPrompts($manager, (string) $name);
        }

        return self::SUCCESS;
    }

    /**
     * Lists all prompts associated with a specified driver and displays them in a two-column detail format.
     *
     * @param  PromptManager  $manager  An instance of the PromptManager used to retrieve the driver and its prompts.
     * @param  string  $driverName  The name of the driver whose prompts are to be listed.
     *
     * @return void
     */
    private function listDriverPrompts(PromptManager $manager, string $driverName): void
    {
        $prompts = $manager->driver($driverName)->all();

        $this->components->twoColumnDetail($driverName, count($prompts).' prompts');

        foreach ($prompts as $name) {
            $this->line('  '.$name);
        }

        if (! empty($prompts)) {
            $this->newLine();
        }
    }
}
