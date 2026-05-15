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

it('lists only the latest row per prompt name', function (): void {
    Prompt::create(['name' => 'welcome', 'content' => 'first-content']);
    Prompt::create(['name' => 'welcome', 'root_id' => 1, 'content' => 'second-content']);
    Prompt::create(['name' => 'welcome', 'root_id' => 1, 'content' => 'third-content']);
    Prompt::create(['name' => 'other', 'content' => 'lonely-content']);

    $this->get('/_prompts')
        ->assertOk()
        ->assertSee('welcome')
        ->assertSee('other');

    expect(Prompt::count())->toBe(4);
});

it('shows the versions count badge for each prompt', function (): void {
    Prompt::create(['name' => 'welcome', 'content' => 'a']);
    Prompt::create(['name' => 'welcome', 'root_id' => 1, 'content' => 'b']);
    Prompt::create(['name' => 'welcome', 'root_id' => 1, 'content' => 'c']);

    $this->get('/_prompts')
        ->assertOk()
        ->assertSee('v3');
});

it('filters prompts by the search term', function (): void {
    Prompt::create(['name' => 'welcome-email', 'content' => 'a']);
    Prompt::create(['name' => 'reset-password', 'content' => 'b']);

    $this->get('/_prompts?search=welcome')
        ->assertOk()
        ->assertSee('welcome-email')
        ->assertDontSee('reset-password');
});

it('paginates results and respects the search query string', function (): void {
    foreach (range(1, 20) as $i) {
        Prompt::create(['name' => "prompt-{$i}", 'content' => 'x']);
    }

    $this->get('/_prompts?page=2')->assertOk();
    $this->get('/_prompts?search=prompt-1')->assertOk()->assertSee('prompt-1');
});

it('renders the empty state when there are no prompts', function (): void {
    $this->get('/_prompts')
        ->assertOk()
        ->assertSee('No prompts yet');
});
