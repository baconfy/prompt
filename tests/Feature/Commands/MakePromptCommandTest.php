<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

beforeEach(function (): void {
    $this->tempFolder = sys_get_temp_dir().'/prompt-tests-'.uniqid();
    config()->set('prompt.drivers.file.folder', $this->tempFolder);
    config()->set('prompt.default', 'file');
});

afterEach(function (): void {
    if (is_dir($this->tempFolder)) {
        (new Filesystem)->deleteDirectory($this->tempFolder);
    }
});

it('creates a new prompt file with the stub at the correct path', function (): void {
    $this->artisan('prompt:make', ['name' => 'welcome'])
        ->assertExitCode(Command::SUCCESS);

    $path = $this->tempFolder.'/welcome.md';

    expect(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))
        ->toContain('---')
        ->toContain('description:')
        ->toContain('required: []');
});

it('creates nested directories for dot notation names', function (): void {
    $this->artisan('prompt:make', ['name' => 'auth.login'])
        ->assertExitCode(Command::SUCCESS);

    expect(file_exists($this->tempFolder.'/auth/login.md'))->toBeTrue();
});

it('refuses with failure exit code when the file already exists', function (): void {
    mkdir($this->tempFolder, 0755, true);
    file_put_contents($this->tempFolder.'/welcome.md', 'existing content');

    $this->artisan('prompt:make', ['name' => 'welcome'])
        ->assertExitCode(Command::FAILURE);
});

it('returns failure when the file driver folder is not configured', function (): void {
    config()->set('prompt.drivers.file.folder', null);

    $this->artisan('prompt:make', ['name' => 'welcome'])
        ->assertExitCode(Command::FAILURE);
});
