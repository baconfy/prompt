<?php

declare(strict_types=1);

use Baconfy\Prompt\Models\Prompt;
use Baconfy\Prompt\Panel;

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
    Panel::auth(fn ($user = null) => true);
});

afterEach(function (): void {
    Panel::auth(null);
});

it('lists every version of a prompt with sequential numbering newest first', function (): void {
    $root = Prompt::create(['name' => 'welcome', 'content' => 'v1']);
    Prompt::create(['name' => 'welcome', 'root_id' => $root->id, 'content' => 'v2']);
    Prompt::create(['name' => 'welcome', 'root_id' => $root->id, 'content' => 'v3']);

    $response = $this->get("/_prompts/{$root->id}/versions")->assertOk();

    $versions = $response->viewData('versions');

    expect($versions)->toHaveCount(3)
        ->and($versions->pluck('version_number')->all())->toBe([3, 2, 1])
        ->and($versions->pluck('content')->all())->toBe(['v3', 'v2', 'v1']);

    $response->assertSee('v3')
        ->assertSee('v2')
        ->assertSee('v1');
});

it('attaches a diff array against the latest version for each row', function (): void {
    $root = Prompt::create(['name' => 'welcome', 'content' => "a\nb"]);
    Prompt::create(['name' => 'welcome', 'root_id' => $root->id, 'content' => "a\nb\nc"]);

    $versions = $this->get("/_prompts/{$root->id}/versions")
        ->assertOk()
        ->viewData('versions');

    /** @var Prompt $oldest */
    $oldest = $versions->last();

    expect($oldest->getAttribute('diff'))->toBe([
        ['type' => 'equal', 'line' => 'a'],
        ['type' => 'equal', 'line' => 'b'],
        ['type' => 'added', 'line' => 'c'],
    ]);
});

it('restore creates a new version copying the chosen content', function (): void {
    $root = Prompt::create(['name' => 'welcome', 'content' => 'v1 content']);
    Prompt::create(['name' => 'welcome', 'root_id' => $root->id, 'content' => 'v2 content']);

    $this->post("/_prompts/{$root->id}/versions/{$root->id}/restore")
        ->assertRedirect("/_prompts/{$root->id}/versions");

    $latest = Prompt::where('name', 'welcome')->orderByDesc('id')->first();
    expect($latest?->content)->toBe('v1 content')
        ->and($latest?->root_id)->toBe($root->id)
        ->and(Prompt::where('name', 'welcome')->count())->toBe(3);
});

it('delete removes only the targeted row', function (): void {
    $root = Prompt::create(['name' => 'welcome', 'content' => 'v1']);
    $v2 = Prompt::create(['name' => 'welcome', 'root_id' => $root->id, 'content' => 'v2']);
    Prompt::create(['name' => 'welcome', 'root_id' => $root->id, 'content' => 'v3']);

    $this->delete("/_prompts/{$root->id}/versions/{$v2->id}")
        ->assertRedirect("/_prompts/{$root->id}/versions");

    expect(Prompt::where('name', 'welcome')->pluck('content')->all())
        ->toEqualCanonicalizing(['v1', 'v3']);
});

it('delete refuses to remove a row that belongs to another prompt name', function (): void {
    $welcome = Prompt::create(['name' => 'welcome', 'content' => 'a']);
    $other = Prompt::create(['name' => 'other', 'content' => 'b']);

    $this->delete("/_prompts/{$welcome->id}/versions/{$other->id}")
        ->assertRedirect("/_prompts/{$welcome->id}/versions");

    expect(Prompt::where('name', 'other')->count())->toBe(1);
});
