<?php

declare(strict_types=1);

use Baconfy\Prompt\Models\Prompt;

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
});

it('creates a prompt via factory with sensible defaults', function (): void {
    $prompt = Prompt::factory()->create();

    expect($prompt->name)->toBeString()->not->toBeEmpty()
        ->and($prompt->content)->toBeString()->not->toBeEmpty();
});

it('creates many prompts with unique names', function (): void {
    Prompt::factory()->count(3)->create();

    expect(Prompt::count())->toBe(3);
});

it('overrides attributes via state', function (): void {
    $prompt = Prompt::factory()->create(['name' => 'fixed-name']);

    expect($prompt->name)->toBe('fixed-name');
});
