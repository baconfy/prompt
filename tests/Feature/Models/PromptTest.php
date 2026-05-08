<?php

declare(strict_types=1);

use Baconfy\Prompt\Models\Prompt;

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
});

it('persists a prompt with metadata cast as array', function (): void {
    Prompt::create([
        'name' => 'welcome',
        'content' => 'Hello!',
        'metadata' => ['model' => 'claude-opus-4-5'],
    ]);

    $prompt = Prompt::where('name', 'welcome')->first();

    expect($prompt?->name)->toBe('welcome')
        ->and($prompt?->content)->toBe('Hello!')
        ->and($prompt?->metadata)->toBe(['model' => 'claude-opus-4-5']);
});