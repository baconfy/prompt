<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Facades;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Baconfy\Prompt\RenderedPrompt;
use Baconfy\Prompt\Testing\PromptFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Driver driver(?string $name = null)
 * @method static ParsedFrontMatter|null source(string $name)
 * @method static RenderedPrompt get(string $name, array<string, mixed> $data = [])
 * @method static void assertCalled(string $name)
 * @method static void assertNotCalled(string $name)
 *
 * @see \Baconfy\Prompt\PromptManager
 */
final class Prompt extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string The name of the facade accessor.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'prompt';
    }

    /**
     * @param  array<string, RenderedPrompt|string>  $stubs  Optional prompt stubs.
     */
    public static function fake(array $stubs = []): PromptFake
    {
        Prompt::swap($fake = new PromptFake($stubs));

        return $fake;
    }
}