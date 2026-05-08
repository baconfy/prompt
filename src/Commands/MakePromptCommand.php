<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/** File driver only — database prompts are created directly via the prompts table. */
final class MakePromptCommand extends Command
{
    protected $signature = 'prompt:make {name : Prompt name in dot notation}';

    protected $description = 'Create a new prompt file with a stub. (File driver only.)';

    public function handle(Filesystem $files): int
    {
        /** @var string $folder */
        $folder = config('prompt.drivers.file.folder');

        $name = (string) $this->argument('name');
        $relative = str_replace('.', DIRECTORY_SEPARATOR, $name);
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
