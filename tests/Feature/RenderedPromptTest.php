<?php

declare(strict_types=1);

use Baconfy\Prompt\RenderedPrompt;

it('exposes the rendered content and metadata', function (): void {
    $prompt = new RenderedPrompt(
        content: 'Hello João!',
        metadata: ['model' => 'claude-opus-4-5'],
    );

    expect($prompt->content)->toBe('Hello João!')
        ->and($prompt->metadata)->toBe(['model' => 'claude-opus-4-5']);
});

it('defaults to empty metadata when none is provided', function (): void {
    $prompt = new RenderedPrompt('Hello!');

    expect($prompt->metadata)->toBe([]);
});

it('returns its content when cast to string', function (): void {
    $prompt = new RenderedPrompt('Hello João!');

    expect((string) $prompt)->toBe('Hello João!');
});