<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Baconfy\Prompt\Contracts\Driver driver(?string $name = null)
 * @method static \Baconfy\Prompt\FrontMatter\ParsedFrontMatter|null source(string $name)
 * @method static \Baconfy\Prompt\RenderedPrompt get(string $name, array<string, mixed> $data = [])
 *
 * @see \Baconfy\Prompt\PromptManager
 */
final class Prompt extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'prompt';
    }
}