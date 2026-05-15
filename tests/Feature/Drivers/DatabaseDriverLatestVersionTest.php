<?php

declare(strict_types=1);

use Baconfy\Prompt\Drivers\DatabaseDriver;
use Baconfy\Prompt\FrontMatter\Parser;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');

    $this->driver = new DatabaseDriver(
        connection: DB::connection(),
        parser: new Parser,
        table: 'prompts',
    );
});

it('returns the latest version when multiple rows share the same name', function (): void {
    DB::table('prompts')->insert([
        ['name' => 'welcome', 'content' => 'v1 content', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'welcome', 'content' => 'v2 content', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'welcome', 'content' => 'v3 content', 'created_at' => now(), 'updated_at' => now()],
    ]);

    expect($this->driver->find('welcome')?->content)->toBe('v3 content');
});

it('lists each prompt name only once when versions exist', function (): void {
    DB::table('prompts')->insert([
        ['name' => 'b-second', 'content' => 'B v1', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'a-first', 'content' => 'A v1', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'a-first', 'content' => 'A v2', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'b-second', 'content' => 'B v2', 'created_at' => now(), 'updated_at' => now()],
    ]);

    expect($this->driver->all())->toBe(['a-first', 'b-second']);
});
