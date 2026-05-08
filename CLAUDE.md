# CLAUDE.md

Project context for Claude Code when working on this package.

## Package: `baconfy/prompt`

A Laravel package for managing AI prompts as Markdown files (with optional YAML front matter) or database records, rendered through Blade.

Currently feature-complete. 100% test coverage. PHPStan clean. Targeting `v0.1.0` for Packagist.

## Public API

```php
// Helper
prompt(string $name, array $data = []): RenderedPrompt

// Facade
Prompt::get('welcome', ['name' => 'João']);    // RenderedPrompt
Prompt::source('welcome');                      // ParsedFrontMatter|null
Prompt::driver('database');                     // Driver instance

// Testing
Prompt::fake([
    'welcome' => 'Hello stub!',
    'auth.login' => new RenderedPrompt('...', ['model' => 'gpt-4']),
]);
Prompt::assertCalled('welcome');
Prompt::assertNotCalled('other');

// Eloquent
use Baconfy\Prompt\Models\Prompt;
Prompt::create(['name' => '...', 'content' => '...']);
Prompt::factory()->create();
```

## Architecture

- **Manager pattern** via `Illuminate\Support\Manager` with config-driven driver resolution.
- **Drivers**: `FileDriver` (default, reads `resources/prompts/*.md`) and `DatabaseDriver`. Both return `ParsedFrontMatter`.
- **Strict driver switching**: no implicit fallback. `Prompt::driver('database')` is always explicit.
- **Unified storage format**: file content and the DB `content` column hold the same raw markdown with optional inline front matter. Migration between drivers is copy/paste.
- **`FrontMatter\Parser`**: pure component (no IO, no Laravel deps). Auto-detects leading `---` YAML block.
- **`Renderer`**: orchestrates validator + Blade rendering. Returns `RenderedPrompt`.
- **Validators** (e.g. `RequiredVariablesValidator`) only run when front matter declares the relevant key.
- **Blade rendering** via `Illuminate\Support\Facades\Blade::render()`.

## Front matter convention

```md
---
model: claude-opus-4-5
temperature: 0.7
required: [user_name]
description: Greeting prompt
---
Hello {{ $user_name }}!
```

If the content does not start with `---`, it is treated as plain content (no metadata, no validation, just Blade).

## Code conventions

- PHP 8.2+. `final readonly` for value objects, `final` for services, no `final` on Eloquent Models (meant to be extended by users).
- `declare(strict_types=1);` at the top of every PHP file.
- Explicit Actions/Services. No fat controllers or models.
- `null` means true absence. Never use it for "empty" or "unset".
- Identifiers, comments, commit messages: **English only**.
- Format: Laravel Pint with default rules.
- PHPDoc descriptions on every `array<...>` type (project PHPStan rule requires it).

## Components

```
src/
├── Commands/
│   ├── ListPromptsCommand.php
│   ├── MakePromptCommand.php
│   └── ShowPromptCommand.php
├── Contracts/
│   └── Driver.php                # find(), all()
├── Database/Factories/
│   └── PromptFactory.php
├── Drivers/
│   ├── DatabaseDriver.php
│   └── FileDriver.php
├── Exceptions/
│   ├── MissingRequiredVariablesException.php
│   └── PromptNotFoundException.php
├── Facades/
│   └── Prompt.php                # exposes get, source, driver, fake, assertCalled, assertNotCalled
├── FrontMatter/
│   ├── Parser.php
│   └── ParsedFrontMatter.php
├── Models/
│   └── Prompt.php
├── Testing/
│   └── PromptFake.php
├── Validators/
│   └── RequiredVariablesValidator.php
├── helpers.php                   # global prompt() function
├── PromptManager.php
├── PromptServiceProvider.php
├── RenderedPrompt.php
└── Renderer.php

database/migrations/
└── 2024_01_01_000001_create_prompts_table.php

config/
└── prompt.php

tests/
├── Unit/                         # pure components, no Laravel boot
├── Feature/                      # uses Orchestra Testbench
├── Fixtures/prompts/             # sample .md files for FileDriver tests
├── Pest.php
└── TestCase.php
```

## Testing

- **Pest 3** + `pestphp/pest-plugin-laravel`.
- **Orchestra Testbench** for Laravel context.
- **TDD strict**: write the failing test first; bundle test + class together when adding new components.
- **100% line coverage** required (`composer test:coverage`). `src/helpers.php` is excluded via `phpunit.xml.dist` (the `function_exists` guard pattern can't reach 100% structurally).
- One test file per source file, mirroring the source layout under `tests/Unit` (pure) and `tests/Feature` (integration with Laravel app).

## Commands

```bash
composer test             # pest
composer test:coverage    # pest --coverage --min=100
composer test:types       # phpstan analyse
composer format           # pint
```

## CI

GitHub Actions matrix in `.github/workflows/tests.yml`:
- PHP 8.2/8.3/8.4/8.5 × Laravel 11/12/13 (with matching Testbench versions).
- PHPStan runs as a separate job on a single PHP version.

## Out of scope (initial release)

- LLM provider integration (Prism, OpenAI SDK). Companion package candidate.
- Cache layer. Reads happen on every call.
- Locale variants. Possible filename suffix later (e.g. `welcome.pt.md`).
- Multi-document YAML or alternative front matter formats (TOML, JSON).
- `assertCalledWith($name, $data)` and richer fake assertions. Add when there's a real need.
