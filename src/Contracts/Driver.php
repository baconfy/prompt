<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Contracts;

use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;

interface Driver
{
    /**
     * Find a prompt by its name.
     *
     * Names use dot notation for subgrouping. For the file driver,
     * "auth.login" maps to "auth/login.md". For the database driver,
     * the dot remains part of the record key.
     *
     * Returns null when the prompt is not found.
     */
    public function find(string $name): ?ParsedFrontMatter;

    /**
     * @return list<string> Available prompt names, sorted alphabetically.
     */
    public function all(): array;
}
