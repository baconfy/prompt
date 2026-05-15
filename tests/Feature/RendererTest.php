<?php

declare(strict_types=1);

use Baconfy\Prompt\Exceptions\MissingRequiredVariablesException;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Baconfy\Prompt\RenderedPrompt;
use Baconfy\Prompt\Renderer;

it('renders a prompt with Blade variables and preserves metadata', function (): void {
    $renderer = app(Renderer::class);
    $source = new ParsedFrontMatter(
        metadata: ['model' => 'claude-opus-4-5'],
        content: 'Hello {{ $name }}!',
    );

    $rendered = $renderer->render($source, ['name' => 'John']);

    expect($rendered)->toBeInstanceOf(RenderedPrompt::class)
        ->and($rendered->content)->toBe('Hello John!')
        ->and($rendered->metadata)->toBe(['model' => 'claude-opus-4-5']);
});

it('renders content without front matter', function (): void {
    $renderer = app(Renderer::class);
    $source = new ParsedFrontMatter([], 'Just a static prompt.');

    $rendered = $renderer->render($source);

    expect($rendered->content)->toBe('Just a static prompt.')
        ->and($rendered->metadata)->toBe([]);
});

it('throws when required variables are missing', function (): void {
    $renderer = app(Renderer::class);
    $source = new ParsedFrontMatter(
        metadata: ['required' => ['name', 'context']],
        content: 'Hello {{ $name }} - {{ $context }}',
    );

    $renderer->render($source, ['name' => 'John']);
})->throws(MissingRequiredVariablesException::class, 'context');
