<?php

declare(strict_types=1);

use Baconfy\Prompt\Drivers\DatabaseDriver;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');

    $this->driver = new DatabaseDriver(
        connection: DB::connection(),
        table: 'prompts',
    );
});

it('finds a prompt stored in the database with metadata', function (): void {
    DB::table('prompts')->insert([
        'name' => 'welcome',
        'content' => 'Hello {{ $name }}!',
        'metadata' => json_encode(['model' => 'claude-opus-4-5']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $parsed = $this->driver->find('welcome');

    expect($parsed?->metadata)->toBe(['model' => 'claude-opus-4-5'])
        ->and($parsed?->content)->toBe('Hello {{ $name }}!');
});

it('finds a prompt without metadata', function (): void {
    DB::table('prompts')->insert([
        'name' => 'simple',
        'content' => 'Just static content.',
        'metadata' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $parsed = $this->driver->find('simple');

    expect($parsed?->metadata)->toBe([])
        ->and($parsed?->content)->toBe('Just static content.');
});

it('returns null when the prompt is not in the database', function (): void {
    expect($this->driver->find('does-not-exist'))->toBeNull();
});