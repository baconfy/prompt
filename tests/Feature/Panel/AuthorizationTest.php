<?php

declare(strict_types=1);

use Baconfy\Prompt\Panel;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
});

afterEach(function (): void {
    Panel::auth(null);
});

it('returns 403 when no gate or callback is registered', function (): void {
    $this->get('/_prompts')->assertForbidden();
});

it('allows access when the configured Gate ability passes', function (): void {
    Gate::define('viewPrompts', fn ($user = null) => true);

    $this->get('/_prompts')->assertOk();
});

it('prefers the Panel::auth callback over the Gate', function (): void {
    Gate::define('viewPrompts', fn ($user = null) => false);
    Panel::auth(fn ($user = null) => true);

    $this->get('/_prompts')->assertOk();
});

it('denies access when both the callback and the Gate refuse', function (): void {
    Gate::define('viewPrompts', fn ($user = null) => false);
    Panel::auth(fn ($user = null) => false);

    $this->get('/_prompts')->assertForbidden();
});
