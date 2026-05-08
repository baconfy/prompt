<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/** File driver only — database prompts are created directly via the prompts table. */
final class MakePromptCommand extends Command
{
    /**
     * The signature for the command.
     *
     * This defines the structure of the command, including its name and
     * the description of the {name} argument.
     */
    protected $signature = 'prompt:make {name : Prompt name in dot notation}';

    /**
     * A brief description of the command functionality.
     *
     * Provides details about the purpose of the command,
     * specifically focusing on creating a new prompt file
     * using a stub when using the File driver.
     */
    protected $description = 'Create a new prompt file with a stub. (File driver only.)';

    /**
     * Handles the creation of a prompt file in a predefined folder structure based on the given name.
     *
     * @param  Filesystem  $files  The filesystem instance used to check for file existence,
     *                          ensure directories exist, and write the file.
     * @return int Returns a status code: self::SUCCESS if the file is successfully created,
     *             or self::FAILURE if an error occurs (e.g., misconfiguration or file already exists).
     */
    public function handle(Filesystem $files): int
    {
        $folder = config('prompt.drivers.file.folder');
        if (! is_string($folder)) {
            $this->components->error('File driver folder is not configured.');

            return self::FAILURE;
        }

        $rawName = $this->argument('name');
        if (! is_string($rawName)) {
            return self::FAILURE; // @codeCoverageIgnore
        }

        $relative = str_replace('.', DIRECTORY_SEPARATOR, $rawName);
        $path = $folder.DIRECTORY_SEPARATOR.$relative.'.md';

        if ($files->exists($path)) {
            $this->components->error("Prompt file already exists: {$path}");

            return self::FAILURE;
        }

        $files->ensureDirectoryExists(dirname($path));
        $files->put($path, "---\ndescription: \nrequired: []\n---\n\n");

        $this->components->info("Prompt created: {$path}");

        return self::SUCCESS;
    }
}
