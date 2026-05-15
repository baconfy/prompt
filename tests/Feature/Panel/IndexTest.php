<?php

declare(strict_types=1);

use Baconfy\Prompt\Livewire\Index;
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

it('lists only the latest row per prompt name', function (): void {
    Prompt::create(['name' => 'welcome', 'content' => 'first-content']);
    Prompt::create(['name' => 'welcome', 'root_id' => 1, 'content' => 'second-content']);
    Prompt::create(['name' => 'welcome', 'root_id' => 1, 'content' => 'third-content']);
    Prompt::create(['name' => 'other', 'content' => 'lonely-content']);

    Livewire::test(Index::class)
        ->assertSee('welcome')
        ->assertSee('other');

    expect(Prompt::count())->toBe(4);
});

it('shows the versions count badge for each prompt', function (): void {
    Prompt::create(['name' => 'welcome', 'content' => 'a']);
    Prompt::create(['name' => 'welcome', 'root_id' => 1, 'content' => 'b']);
    Prompt::create(['name' => 'welcome', 'root_id' => 1, 'content' => 'c']);

    Livewire::test(Index::class)
        ->assertSeeHtml('v3');
});

it('filters prompts by the search term', function (): void {
    Prompt::create(['name' => 'welcome-email', 'content' => 'a']);
    Prompt::create(['name' => 'reset-password', 'content' => 'b']);

    Livewire::test(Index::class)
        ->set('search', 'welcome')
        ->assertSee('welcome-email')
        ->assertDontSee('reset-password');
});

it('resets pagination when the search term changes', function (): void {
    foreach (range(1, 20) as $i) {
        Prompt::create(['name' => "prompt-{$i}", 'content' => 'x']);
    }

    Livewire::test(Index::class)
        ->call('gotoPage', 2, 'page')
        ->assertSet('paginators.page', 2)
        ->set('search', 'prompt-1')
        ->assertSet('paginators.page', 1);
});

it('renders the empty state when there are no prompts', function (): void {
    Livewire::test(Index::class)
        ->assertSee('No prompts yet');
});
