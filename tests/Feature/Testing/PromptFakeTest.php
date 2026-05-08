<?php

declare(strict_types=1);

use Baconfy\Prompt\Exceptions\PromptNotFoundException;
use Baconfy\Prompt\Facades\Prompt;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Baconfy\Prompt\RenderedPrompt;
use PHPUnit\Framework\AssertionFailedError;

it('wraps a string stub into a RenderedPrompt when called via prompt()', function (): void {
    Prompt::fake(['welcome' => 'Hello stub!']);

    $result = prompt('welcome');

    expect($result)->toBeInstanceOf(RenderedPrompt::class)
        ->and($result->content)->toBe('Hello stub!')
        ->and($result->metadata)->toBe([]);
});

it('round-trips a RenderedPrompt stub including metadata via prompt()', function (): void {
    $stub = new RenderedPrompt('Please log in!', ['model' => 'gpt-4']);
    Prompt::fake(['auth.login' => $stub]);

    $result = prompt('auth.login');

    expect($result)->toBeInstanceOf(RenderedPrompt::class)
        ->and($result->content)->toBe('Please log in!')
        ->and($result->metadata)->toBe(['model' => 'gpt-4']);
});

it('throws PromptNotFoundException for an unknown prompt name', function (): void {
    Prompt::fake(['welcome' => 'Hello!']);

    prompt('unknown');
})->throws(PromptNotFoundException::class, 'Prompt [unknown] not found.');

it('returns ParsedFrontMatter with stub data for a known name via Prompt::source()', function (): void {
    $stub = new RenderedPrompt('Please log in!', ['model' => 'gpt-4']);
    Prompt::fake(['auth.login' => $stub]);

    $parsed = Prompt::source('auth.login');

    expect($parsed)->toBeInstanceOf(ParsedFrontMatter::class)
        ->and($parsed->content)->toBe('Please log in!')
        ->and($parsed->metadata)->toBe(['model' => 'gpt-4']);
});

it('returns ParsedFrontMatter with empty metadata for a string stub via Prompt::source()', function (): void {
    Prompt::fake(['welcome' => 'Hello stub!']);

    $parsed = Prompt::source('welcome');

    expect($parsed)->toBeInstanceOf(ParsedFrontMatter::class)
        ->and($parsed->content)->toBe('Hello stub!')
        ->and($parsed->metadata)->toBe([]);
});

it('returns null from Prompt::source() for an unknown name', function (): void {
    Prompt::fake(['welcome' => 'Hello!']);

    expect(Prompt::source('unknown'))->toBeNull();
});

it('assertCalled passes when the prompt was called', function (): void {
    Prompt::fake(['welcome' => 'Hello!']);

    prompt('welcome');

    Prompt::assertCalled('welcome');
});

it('assertCalled fails when the prompt was not called', function (): void {
    Prompt::fake(['welcome' => 'Hello!']);

    expect(fn () => Prompt::assertCalled('welcome'))
        ->toThrow(AssertionFailedError::class);
});

it('assertNotCalled passes when the prompt was not called', function (): void {
    Prompt::fake(['welcome' => 'Hello!']);

    Prompt::assertNotCalled('welcome');
});

it('assertNotCalled fails when the prompt was called', function (): void {
    Prompt::fake(['welcome' => 'Hello!']);

    prompt('welcome');

    expect(fn () => Prompt::assertNotCalled('welcome'))
        ->toThrow(AssertionFailedError::class);
});
