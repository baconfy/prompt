<?php

declare(strict_types=1);

use Baconfy\Prompt\Exceptions\PromptNotFoundException;
use Baconfy\Prompt\RenderedPrompt;

beforeEach(function (): void {
    config()->set('prompt.default', 'file');
    config()->set('prompt.drivers.file', ['driver' => 'file', 'folder' => __DIR__.'/../Fixtures/prompts']);
});

it('renders a prompt via the prompt() helper', function (): void {
    $rendered = prompt('welcome', ['name' => 'John']);

    expect($rendered)->toBeInstanceOf(RenderedPrompt::class)
        ->and($rendered->content)->toBe('Hello John!'."\n")
        ->and($rendered->metadata)->toBe(['model' => 'claude-opus-4-5']);
});

it('throws when the prompt does not exist', function (): void {
    prompt('does-not-exist');
})->throws(PromptNotFoundException::class, 'Prompt [does-not-exist] not found.');