<?php

declare(strict_types=1);

use Baconfy\Prompt\FrontMatter\Parser;

it('parses YAML front matter and separates it from the content', function (): void {
    $raw = <<<'MD'
        ---
        model: claude-opus-4-5
        temperature: 0.7
        ---
        Hello {{ $name }}!
        MD;

    $parsed = (new Parser)->parse($raw);

    expect($parsed->metadata)->toBe(['model' => 'claude-opus-4-5', 'temperature' => 0.7])
        ->and($parsed->content)->toBe('Hello {{ $name }}!');
});

it('returns empty metadata and the original content when no front matter is present', function (): void {
    $raw = 'Hello {{ $name }}!';

    $parsed = (new Parser)->parse($raw);

    expect($parsed->metadata)->toBe([])
        ->and($parsed->content)->toBe('Hello {{ $name }}!');
});

it('parses an empty front matter block', function (): void {
    $raw = <<<'MD'
        ---
        ---
        Just content
        MD;

    $parsed = (new Parser)->parse($raw);

    expect($parsed->metadata)->toBe([])
        ->and($parsed->content)->toBe('Just content');
});

it('returns empty content when nothing follows the closing delimiter', function (): void {
    $raw = <<<'MD'
        ---
        model: claude-opus-4-5
        ---
        MD;

    $parsed = (new Parser)->parse($raw);

    expect($parsed->metadata)->toBe(['model' => 'claude-opus-4-5'])
        ->and($parsed->content)->toBe('');
});

it('preserves --- sequences inside the body content', function (): void {
    $raw = <<<'MD'
        ---
        model: claude-opus-4-5
        ---
        # Section A

        ---

        # Section B
        MD;

    $parsed = (new Parser)->parse($raw);

    expect($parsed->metadata)->toBe(['model' => 'claude-opus-4-5'])
        ->and($parsed->content)->toBe("# Section A\n\n---\n\n# Section B");
});

it('treats the input as plain content when the front matter has no closing delimiter', function (): void {
    $raw = <<<'MD'
        ---
        model: claude-opus-4-5
        Hello
        MD;

    $parsed = (new Parser)->parse($raw);

    expect($parsed->metadata)->toBe([])
        ->and($parsed->content)->toBe("---\nmodel: claude-opus-4-5\nHello");
});
