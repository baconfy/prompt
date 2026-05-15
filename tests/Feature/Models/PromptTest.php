<?php

declare(strict_types=1);

use Baconfy\Prompt\Models\Prompt;

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
});

it('persists a prompt as raw markdown content', function (): void {
    Prompt::create([
        'name' => 'welcome',
        'content' => "---\nmodel: claude-opus-4-5\n---\nHello!",
    ]);

    $prompt = Prompt::where('name', 'welcome')->first();

    expect($prompt?->name)->toBe('welcome')
        ->and($prompt?->content)->toBe("---\nmodel: claude-opus-4-5\n---\nHello!");
});
