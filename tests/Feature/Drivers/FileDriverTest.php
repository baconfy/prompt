<?php

declare(strict_types=1);

use Baconfy\Prompt\Drivers\FileDriver;
use Baconfy\Prompt\FrontMatter\Parser;
use Illuminate\Filesystem\Filesystem;

beforeEach(function (): void {
    $this->driver = new FileDriver(
        files: new Filesystem,
        parser: new Parser,
        folder: __DIR__.'/../../Fixtures/prompts',
    );
});

it('finds a prompt at the root of the folder', function (): void {
    $parsed = $this->driver->find('welcome');

    expect($parsed?->metadata)->toBe(['model' => 'claude-opus-4-5'])
        ->and($parsed?->content)->toBe('Hello {{ $name }}!'."\n");
});

it('resolves dot notation to nested folders', function (): void {
    expect($this->driver->find('auth.login')?->content)
        ->toBe('Please log in.'."\n");
});

it('returns null when the prompt does not exist', function (): void {
    expect($this->driver->find('does-not-exist'))->toBeNull();
});

it('lists all prompt names from the folder', function (): void {
    expect($this->driver->all())->toBe(['auth.login', 'welcome']);
});