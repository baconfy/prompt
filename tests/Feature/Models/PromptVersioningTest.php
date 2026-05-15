<?php

declare(strict_types=1);

use Baconfy\Prompt\Models\Prompt;

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
});

it('persists root_id on a prompt row', function (): void {
    $root = Prompt::create(['name' => 'welcome', 'content' => 'v1']);
    $next = Prompt::create(['name' => 'welcome', 'content' => 'v2', 'root_id' => $root->id]);

    expect($next->fresh()?->root_id)->toBe($root->id);
});

it('resolves the root() relation back to the first version', function (): void {
    $root = Prompt::create(['name' => 'welcome', 'content' => 'v1']);
    $child = Prompt::create(['name' => 'welcome', 'content' => 'v2', 'root_id' => $root->id]);

    expect($child->root?->id)->toBe($root->id);
});

it('resolves the versions() relation back from the root', function (): void {
    $root = Prompt::create(['name' => 'welcome', 'content' => 'v1']);
    Prompt::create(['name' => 'welcome', 'content' => 'v2', 'root_id' => $root->id]);
    Prompt::create(['name' => 'welcome', 'content' => 'v3', 'root_id' => $root->id]);

    expect($root->versions)->toHaveCount(2)
        ->and($root->versions->pluck('content')->all())->toEqualCanonicalizing(['v2', 'v3']);
});

it('latestForName scope returns the highest-id row for a given name', function (): void {
    Prompt::create(['name' => 'welcome', 'content' => 'v1']);
    Prompt::create(['name' => 'welcome', 'content' => 'v2']);
    $latest = Prompt::create(['name' => 'welcome', 'content' => 'v3']);

    /** @var Prompt|null $found */
    $found = Prompt::query()->latestForName('welcome')->first();

    expect($found?->id)->toBe($latest->id)
        ->and($found?->content)->toBe('v3');
});
