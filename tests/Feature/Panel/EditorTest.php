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

it('creates a new root prompt with null root_id when none exists for the name', function (): void {
    $this->post('/_prompts/save', [
        'name' => 'welcome',
        'content' => 'Hello world',
    ])->assertRedirect('/_prompts');

    $row = Prompt::where('name', 'welcome')->first();
    expect($row?->content)->toBe('Hello world')
        ->and($row?->root_id)->toBeNull();
});

it('creates a child version pointing to the root when saving over an existing name', function (): void {
    $root = Prompt::create(['name' => 'welcome', 'content' => 'v1']);

    $this->post('/_prompts/save', [
        'name' => 'welcome',
        'content' => 'v2 content',
    ])->assertRedirect('/_prompts');

    $latest = Prompt::where('name', 'welcome')->orderByDesc('id')->first();
    expect($latest?->content)->toBe('v2 content')
        ->and($latest?->root_id)->toBe($root->id);
});

it('prefills the form when editing an existing prompt', function (): void {
    $prompt = Prompt::create(['name' => 'welcome', 'content' => 'hello']);

    $this->get("/_prompts/{$prompt->id}/edit")
        ->assertOk()
        ->assertSee('value="welcome"', escape: false)
        ->assertSee('hello');
});

it('renders the empty create form', function (): void {
    $this->get('/_prompts/create')
        ->assertOk()
        ->assertSee('name="name"', escape: false)
        ->assertSee('name="content"', escape: false);
});

it('keeps the prompt context on preview when prompt_id is provided', function (): void {
    $prompt = Prompt::create(['name' => 'welcome', 'content' => 'old']);

    $response = $this->post('/_prompts/preview', [
        'name' => 'welcome',
        'content' => 'Hello {{ $name }}!',
        'variables' => '{"name": "World"}',
        'prompt_id' => $prompt->id,
    ])->assertOk();

    expect($response->viewData('prompt')?->id)->toBe($prompt->id);
});

it('requires both name and content to save', function (): void {
    $this->from('/_prompts/create')
        ->post('/_prompts/save', ['name' => '', 'content' => ''])
        ->assertRedirect('/_prompts/create')
        ->assertSessionHasErrors(['name', 'content']);

    expect(Prompt::count())->toBe(0);
});

it('rejects content with invalid YAML front matter', function (): void {
    $this->from('/_prompts/create')
        ->post('/_prompts/save', [
            'name' => 'welcome',
            'content' => "---\nname: : bad\n---\nbody",
        ])
        ->assertRedirect('/_prompts/create')
        ->assertSessionHasErrors('content');

    expect(Prompt::count())->toBe(0);
});

it('refuses to save when content is identical to the current latest version', function (): void {
    Prompt::create(['name' => 'welcome', 'content' => 'same']);

    $this->from('/_prompts/create')
        ->post('/_prompts/save', [
            'name' => 'welcome',
            'content' => 'same',
        ])
        ->assertRedirect('/_prompts/create')
        ->assertSessionHasErrors('content');

    expect(Prompt::where('name', 'welcome')->count())->toBe(1);
});

it('renders preview output through Blade with JSON variables', function (): void {
    $this->post('/_prompts/preview', [
        'name' => 'welcome',
        'content' => 'Hello {{ $name }}!',
        'variables' => '{"name": "World"}',
    ])
        ->assertOk()
        ->assertSee('Hello World!');
});

it('captures preview errors when variables JSON is invalid', function (): void {
    $this->post('/_prompts/preview', [
        'name' => 'welcome',
        'content' => 'Hello',
        'variables' => 'not-json',
    ])
        ->assertOk()
        ->assertSee('Syntax error');
});
