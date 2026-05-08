<?php

declare(strict_types=1);

use Illuminate\Console\Command;

beforeEach(function (): void {
    config()->set('prompt.drivers', [
        'file' => [
            'driver' => 'file',
            'folder' => __DIR__.'/../../Fixtures/prompts',
        ],
    ]);
    config()->set('prompt.default', 'file');
});

it('prints metadata and content for an existing prompt', function (): void {
    $this->artisan('prompt:show', ['name' => 'welcome'])
        ->expectsOutputToContain('Metadata:')
        ->expectsOutputToContain('claude-opus-4-5')
        ->expectsOutputToContain('Content:')
        ->expectsOutputToContain('Hello')
        ->assertExitCode(Command::SUCCESS);
});

it('shows no metadata message when prompt has no front matter', function (): void {
    $this->artisan('prompt:show', ['name' => 'auth.login'])
        ->expectsOutputToContain('Metadata:')
        ->expectsOutputToContain('(no metadata)')
        ->expectsOutputToContain('Content:')
        ->expectsOutputToContain('Please log in.')
        ->assertExitCode(Command::SUCCESS);
});

it('returns failure for an unknown prompt', function (): void {
    $this->artisan('prompt:show', ['name' => 'does-not-exist'])
        ->assertExitCode(Command::FAILURE);
});
