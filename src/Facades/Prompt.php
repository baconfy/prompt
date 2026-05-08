<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Facades;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Driver driver(?string $name = null)
 * @method static ParsedFrontMatter|null source(string $name)
 *
 * @see \Baconfy\Prompt\PromptManager
 */
final class Prompt extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @see \Baconfy\Prompt\PromptManager
     */
    protected static function getFacadeAccessor(): string
    {
        return 'prompt';
    }
}