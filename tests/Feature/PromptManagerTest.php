<?php

declare(strict_types=1);

use Baconfy\Prompt\Drivers\DatabaseDriver;
use Baconfy\Prompt\Drivers\FileDriver;
use Baconfy\Prompt\Exceptions\PromptNotFoundException;
use Baconfy\Prompt\Facades\Prompt;
use Baconfy\Prompt\RenderedPrompt;

beforeEach(function (): void {
    config()->set('prompt.default', 'file');
    config()->set('prompt.drivers.file', [
        'driver' => 'file',
        'folder' => __DIR__.'/../Fixtures/prompts',
    ]);
});

it('resolves the default driver from config', function (): void {
    expect(Prompt::driver())->toBeInstanceOf(FileDriver::class);
});

it('resolves a named driver from config', function (): void {
    config()->set('prompt.drivers.system', [
        'driver' => 'file',
        'folder' => __DIR__.'/../Fixtures/prompts',
    ]);

    expect(Prompt::driver('system'))->toBeInstanceOf(FileDriver::class);
});

it('resolves the database driver from config', function (): void {
    config()->set('prompt.drivers.db', [
        'driver' => 'database',
        'table' => 'prompts',
    ]);

    expect(Prompt::driver('db'))->toBeInstanceOf(DatabaseDriver::class);
});

it('returns a parsed prompt source via the default driver', function (): void {
    $parsed = Prompt::source('welcome');

    expect($parsed?->metadata)->toBe(['model' => 'claude-opus-4-5'])
        ->and($parsed?->content)->toBe('Hello {{ $name }}!'."\n");
});

it('returns null when the source does not exist', function (): void {
    expect(Prompt::source('does-not-exist'))->toBeNull();
});

it('throws when the default driver is not a string', function (): void {
    config()->set('prompt.default', null);

    Prompt::driver();
})->throws(InvalidArgumentException::class, 'Default prompt driver must be configured as a string.');

it('throws when the driver name is not configured', function (): void {
    Prompt::driver('non-existent-name');
})->throws(InvalidArgumentException::class, 'Prompt driver [non-existent-name] is not configured.');

it('throws when the driver config is missing the type field', function (): void {
    config()->set('prompt.drivers.broken', [
        'folder' => '/tmp',
    ]);

    Prompt::driver('broken');
})->throws(InvalidArgumentException::class, "missing a 'driver' type");

it('throws when the driver type is not supported', function (): void {
    config()->set('prompt.drivers.broken', [
        'driver' => 'unsupported-type',
    ]);

    Prompt::driver('broken');
})->throws(InvalidArgumentException::class, 'Prompt driver type [unsupported-type] is not supported.');

it('throws when the file driver config is missing the folder field', function (): void {
    config()->set('prompt.drivers.broken', [
        'driver' => 'file',
    ]);

    Prompt::driver('broken');
})->throws(InvalidArgumentException::class, 'File driver requires a "folder" configuration string.');

it('throws when the database driver config is missing the table field', function (): void {
    config()->set('prompt.drivers.broken', [
        'driver' => 'database',
    ]);

    Prompt::driver('broken');
})->throws(InvalidArgumentException::class, 'Database driver requires a "table" configuration string.');

it('gets a rendered prompt via Prompt::get()', function (): void {
    $rendered = Prompt::get('welcome', ['name' => 'João']);

    expect($rendered)->toBeInstanceOf(RenderedPrompt::class)
        ->and($rendered->content)->toBe('Hello João!'."\n")
        ->and($rendered->metadata)->toBe(['model' => 'claude-opus-4-5']);
});

it('throws PromptNotFoundException when the prompt does not exist', function (): void {
    Prompt::get('does-not-exist');
})->throws(PromptNotFoundException::class, 'Prompt [does-not-exist] not found.');

it('exposes the prompt name on the not-found exception', function (): void {
    try {
        Prompt::get('missing');
        expect(true)->toBeFalse();
    } catch (PromptNotFoundException $e) {
        expect($e->name)->toBe('missing');
    }
});
