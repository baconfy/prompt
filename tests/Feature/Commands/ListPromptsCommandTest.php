<?php

declare(strict_types=1);

beforeEach(function (): void {
    config()->set('prompt.drivers', [
        'file' => [
            'driver' => 'file',
            'folder' => __DIR__.'/../../Fixtures/prompts',
        ],
    ]);
    config()->set('prompt.default', 'file');
});

it('lists prompts from all configured drivers by default', function (): void {
    $this->artisan('prompt:list')
        ->expectsOutputToContain('welcome')
        ->expectsOutputToContain('auth.login')
        ->assertExitCode(0);
});

it('lists prompts from a specified driver via argument', function (): void {
    $this->artisan('prompt:list', ['driver' => 'file'])
        ->expectsOutputToContain('welcome')
        ->expectsOutputToContain('auth.login')
        ->assertExitCode(0);
});
