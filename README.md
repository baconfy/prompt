# baconfy/prompt

[![Tests](https://github.com/baconfy/prompt/actions/workflows/tests.yml/badge.svg)](https://github.com/baconfy/prompt/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/baconfy/prompt.svg)](https://packagist.org/packages/baconfy/prompt)
[![License](https://img.shields.io/packagist/l/baconfy/prompt.svg)](https://packagist.org/packages/baconfy/prompt)
[![Total Downloads](https://img.shields.io/packagist/dt/baconfy/prompt.svg)](https://packagist.org/packages/baconfy/prompt)
[![PHP Version](https://img.shields.io/packagist/php-v/baconfy/prompt.svg)](https://packagist.org/packages/baconfy/prompt)

Manage AI prompts in Laravel as Markdown files (with optional YAML front matter) or database records, rendered through Blade.

## Why

LLM prompts are configuration. They drift across the codebase, get duplicated, and end up hardcoded as long strings inside services. This package treats prompts as first-class assets:

- One file or DB record per prompt
- YAML front matter for model/temperature/required variables
- Blade rendering for variable interpolation
- Drivers for file and database storage
- Strict validation of required variables

## Requirements

- PHP 8.3+
- Laravel 11, 12 or 13

## Installation

```bash
composer require baconfy/prompt
```

The service provider auto-registers. Publish what you need:

```bash
php artisan vendor:publish --tag=prompt-config
php artisan vendor:publish --tag=prompt-migrations    # only if you plan to use the database driver
php artisan migrate
```

## Quick start

Create a prompt at `resources/prompts/welcome.md`:

```markdown
---
model: claude-opus-4-5
temperature: 0.7
required: [name]
---
You are a helpful assistant. Greet {{ $name }} warmly and ask how you can help today.
```

Render it:

```php
$prompt = prompt('welcome', ['name' => 'John']);

$prompt->content;           // rendered string
$prompt->metadata['model']; // 'claude-opus-4-5'
(string) $prompt;           // same as ->content (implements Stringable)
```

Use the metadata to drive your LLM call:

```php
$prompt = prompt('welcome', ['name' => 'John']);

$response = $anthropic->messages()->create([
    'model'       => $prompt->metadata['model'],
    'temperature' => $prompt->metadata['temperature'],
    'messages'    => [['role' => 'user', 'content' => (string) $prompt]],
]);
```

## Front matter

Front matter is an optional YAML block at the top of the prompt:

```markdown
---
model: claude-opus-4-5
temperature: 0.7
required: [user_name, context]
description: Onboarding greeting
tags: [onboarding, greeting]
---
Hello {{ $user_name }}! Considering {{ $context }}, welcome aboard.
```

Behavior:

- If the file does **not** start with `---`, it is treated as plain content (no metadata, no validation, just Blade).
- `required: [...]` is enforced. Missing variables throw `MissingRequiredVariablesException`.
- Anything else is metadata. The package does not interpret it; read it via `$prompt->metadata['anything']`.

## Drivers

### File driver

Default. Reads from `resources/prompts/*.md`. Dot notation maps to subfolders:

```php
prompt('auth.login');               // resources/prompts/auth/login.md
prompt('emails.welcome.subject');   // resources/prompts/emails/welcome/subject.md
```

### Database driver

Stores prompts in a `prompts` table with `name` and `content` columns. The content column holds raw markdown — exactly the same format as the file driver. Front matter, when present, sits inline at the top of `content`.

```php
use Baconfy\Prompt\Models\Prompt;

Prompt::create([
    'name'    => 'welcome',
    'content' => <<<'MD'
        ---
        model: claude-opus-4-5
        required: [name]
        ---
        Hello {{ $name }}!
        MD,
]);

prompt('welcome', ['name' => 'John']);   // works the same way
```

Migrating prompts between file and database drivers is a copy/paste — the storage format is identical.

Switch the default driver in `.env`:

```dotenv
PROMPTS_DRIVER=database
PROMPTS_CONNECTION=mysql       # optional, falls back to DB_CONNECTION
PROMPTS_TABLE=prompts          # optional
```

Or use both side by side:

```php
// config/prompt.php
'drivers' => [
    'system' => [
        'driver' => 'file',
        'folder' => resource_path('prompts/system'),
    ],
    'user' => [
        'driver' => 'database',
        'table'  => 'user_prompts',
    ],
],
```

```php
Prompt::driver('system')->find('welcome');
Prompt::driver('user')->find('welcome');
```

## API

### Helper

```php
prompt(string $name, array $data = []): RenderedPrompt
```

### Facade

```php
use Baconfy\Prompt\Facades\Prompt;

Prompt::get('welcome', ['name' => 'John']);   // RenderedPrompt
Prompt::source('welcome');                     // ParsedFrontMatter|null
Prompt::driver('database');                    // Driver instance (defaults to active)
```

### `RenderedPrompt`

```php
$prompt->content;    // rendered string
$prompt->metadata;   // array<string, mixed>
(string) $prompt;    // same as ->content
```

### `ParsedFrontMatter`

What `Prompt::source()` returns — pre-render. Useful when you want metadata without rendering Blade:

```php
$source = Prompt::source('welcome');
$source->metadata['model'];   // 'claude-opus-4-5'
$source->content;             // raw template, with Blade tags untouched
```

### `Prompt` model

Eloquent model on the `prompts` table. Use it to seed, update, or otherwise manage DB-backed prompts:

```php
use Baconfy\Prompt\Models\Prompt as PromptModel;

PromptModel::create([
    'name'    => 'welcome',
    'content' => <<<'MD'
        ---
        model: claude-opus-4-5
        ---
        Hello {{ $name }}!
        MD,
]);

PromptModel::where('name', 'welcome')->update(['content' => 'Hi {{ $name }}!']);
```

The driver itself does not depend on this model — it reads via Query Builder. The model is a convenience for your CRUD layer.

## Configuration

`config/prompt.php`:

```php
return [
    'default' => env('PROMPTS_DRIVER', 'file'),

    'drivers' => [
        'file' => [
            'driver' => 'file',
            'folder' => env('PROMPTS_FOLDER', resource_path('prompts')),
        ],
        'database' => [
            'driver'     => 'database',
            'connection' => env('PROMPTS_CONNECTION'),
            'table'      => env('PROMPTS_TABLE', 'prompts'),
        ],
    ],
];
```

The `drivers` array supports any number of named entries. Each one has a `driver` field (`file` or `database`) plus the keys that driver needs. The same type can appear under multiple names (e.g. two file folders for system vs. user prompts).

## Exceptions

```php
use Baconfy\Prompt\Exceptions\PromptNotFoundException;
use Baconfy\Prompt\Exceptions\MissingRequiredVariablesException;
```

- `PromptNotFoundException` — thrown by `Prompt::get()` when the name is not found by the active driver. Exposes `->name`.
- `MissingRequiredVariablesException` — thrown when the prompt declares `required` in its metadata and any variable is missing from `$data`. Exposes `->variables` (the list of missing names).

## Security

Blade compiles prompt content. **Do not load prompt content from untrusted sources.** A prompt containing `{{ system('rm -rf /') }}` would execute that PHP if rendered. Treat prompts as code, not user input.

## Testing

Run the package test suite:

```bash
composer test            # pest
composer test:coverage   # 100% required
composer test:types      # phpstan
composer format          # pint
```

## Credits

- [Renato Dehnhardt](https://github.com/rdehnhardt)
- [All contributors](https://github.com/baconfy/prompt/graphs/contributors)

## License

Licensed under the GNU General Public License v3.0 or later (GPL-3.0-or-later). See [LICENSE](LICENSE) for details.