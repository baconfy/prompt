<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Testing;

use Baconfy\Prompt\Exceptions\PromptNotFoundException;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Baconfy\Prompt\RenderedPrompt;
use PHPUnit\Framework\Assert;

final class PromptFake
{
    /** @var list<array{name: string, data: array<string, mixed>}> */
    private array $calls = [];

    /**
     * @param  array<string, RenderedPrompt|string>  $stubs  Map of prompt name to stub content or instance.
     */
    public function __construct(private readonly array $stubs = []) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws PromptNotFoundException
     */
    public function get(string $name, array $data = []): RenderedPrompt
    {
        $this->calls[] = ['name' => $name, 'data' => $data];

        if (! array_key_exists($name, $this->stubs)) {
            throw new PromptNotFoundException($name);
        }

        $stub = $this->stubs[$name];

        return $stub instanceof RenderedPrompt ? $stub : new RenderedPrompt($stub);
    }

    /**
     * Retrieves the parsed front matter for a given source name.
     *
     * @param  string  $name  The name of the source to retrieve.
     * @return ParsedFrontMatter|null The parsed front matter if the source exists, or null if not found.
     */
    public function source(string $name): ?ParsedFrontMatter
    {
        $this->calls[] = ['name' => $name, 'data' => []];

        if (! array_key_exists($name, $this->stubs)) {
            return null;
        }

        $stub = $this->stubs[$name];

        if ($stub instanceof RenderedPrompt) {
            return new ParsedFrontMatter($stub->metadata, $stub->content);
        }

        return new ParsedFrontMatter([], $stub);
    }

    /**
     * Asserts that a specific prompt was called.
     *
     * @param  string  $name  The name of the prompt to check.
     */
    public function assertCalled(string $name): void
    {
        Assert::assertTrue($this->wasCalled($name), "Expected prompt [{$name}] to have been called, but it was not.");
    }

    /**
     * Asserts that a prompt with the given name was not called.
     *
     * @param  string  $name  The name of the prompt to verify.
     */
    public function assertNotCalled(string $name): void
    {
        Assert::assertFalse($this->wasCalled($name), "Expected prompt [{$name}] not to have been called, but it was.");
    }

    /**
     * Checks if a method with the specified name was called.
     *
     * @param  string  $name  The name of the method to check for.
     * @return bool True if the method was called, false otherwise.
     */
    private function wasCalled(string $name): bool
    {
        return array_any($this->calls, fn ($call) => $call['name'] === $name);
    }
}
