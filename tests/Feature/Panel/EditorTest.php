<?php

declare(strict_types=1);

use Baconfy\Prompt\Livewire\Editor;
use Baconfy\Prompt\Models\Prompt;
use Baconfy\Prompt\Panel;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
    Panel::auth(fn ($user = null) => true);
});

afterEach(function (): void {
    Panel::auth(null);
});

it('creates a new root prompt with null root_id when none exists for the name', function (): void {
    Livewire::test(Editor::class)
        ->set('name', 'welcome')
        ->set('content', 'Hello world')
        ->call('save');

    $row = Prompt::where('name', 'welcome')->first();
    expect($row?->content)->toBe('Hello world')
        ->and($row?->root_id)->toBeNull();
});

it('creates a child version pointing to the root when saving over an existing name', function (): void {
    $root = Prompt::create(['name' => 'welcome', 'content' => 'v1']);

    Livewire::test(Editor::class)
        ->set('name', 'welcome')
        ->set('content', 'v2 content')
        ->call('save');

    $latest = Prompt::where('name', 'welcome')->orderByDesc('id')->first();
    expect($latest?->content)->toBe('v2 content')
        ->and($latest?->root_id)->toBe($root->id);
});

it('prefills the form when mounted with an existing prompt', function (): void {
    $prompt = Prompt::create(['name' => 'welcome', 'content' => 'hello']);

    Livewire::test(Editor::class, ['prompt' => $prompt])
        ->assertSet('name', 'welcome')
        ->assertSet('content', 'hello')
        ->assertSet('promptId', $prompt->id);
});

it('requires both name and content to save', function (): void {
    Livewire::test(Editor::class)
        ->set('name', '')
        ->set('content', '')
        ->call('save')
        ->assertHasErrors(['name', 'content']);

    expect(Prompt::count())->toBe(0);
});

it('rejects content with invalid YAML front matter', function (): void {
    Livewire::test(Editor::class)
        ->set('name', 'welcome')
        ->set('content', "---\nname: : bad\n---\nbody")
        ->call('save')
        ->assertHasErrors('content');

    expect(Prompt::count())->toBe(0);
});

it('refuses to save when content is identical to the current latest version', function (): void {
    Prompt::create(['name' => 'welcome', 'content' => 'same']);

    Livewire::test(Editor::class)
        ->set('name', 'welcome')
        ->set('content', 'same')
        ->call('save')
        ->assertHasErrors('content');

    expect(Prompt::where('name', 'welcome')->count())->toBe(1);
});

it('renders preview output through Blade with JSON variables', function (): void {
    Livewire::test(Editor::class)
        ->set('content', 'Hello {{ $name }}!')
        ->set('variables', '{"name": "World"}')
        ->call('preview')
        ->assertSet('previewOutput', 'Hello World!')
        ->assertSet('previewError', null);
});

it('captures preview errors when variables JSON is invalid', function (): void {
    Livewire::test(Editor::class)
        ->set('content', 'Hello')
        ->set('variables', 'not-json')
        ->call('preview')
        ->assertSet('previewOutput', null)
        ->assertNotSet('previewError', null);
});
