# Changelog

All notable changes to `baconfy/prompt` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

First public release.

### Added

#### Core

- `Prompt` facade and `prompt()` global helper to resolve and render prompts.
- `FileDriver` reading Markdown files from `resources/prompts/*.md`.
- `DatabaseDriver` reading rows from the `prompts` table, always returning the latest version per `name`.
- `FrontMatter\Parser` parsing optional leading YAML front matter (`---` blocks) with full content fallback when absent.
- `Renderer` rendering content through Blade with variable interpolation.
- `RequiredVariablesValidator` enforcing the `required` metadata key with `MissingRequiredVariablesException`.
- `PromptNotFoundException` raised when a name cannot be resolved by the active driver.
- `PromptManager` with config-driven driver resolution and explicit `Prompt::driver('name')` switching (no implicit fallback).
- `Models\Prompt` Eloquent model with self-referencing `root_id` versioning (root, `versions()`, `latestForName` scope).
- `Database\Factories\PromptFactory` for tests.
- `RenderedPrompt` value object exposing rendered content and original metadata.

#### Admin Panel (optional)

- Auto-discovered when `livewire/livewire:^3` is installed and `prompt.panel.enabled` is true.
- Routes mounted at `/_prompts` (configurable) with `Authorize` middleware.
- `Panel::auth(Closure)` static API for closure-style authorization (Horizon-style); falls back to the configured `Gate` ability (`viewPrompts` by default).
- `Livewire\Index` — paginated list of every prompt name (latest version), live search by name, `vN` version count badge.
- `Livewire\Editor` — create/edit form with live Blade preview using JSON variables, YAML front matter validation, and a "no changes to save" guard preventing identical-content versions.
- `Livewire\Versions` — accordion of every revision with sequential `vN` numbering and an inline line-by-line diff against the current version. Each row supports **Restore** (creates a new version on top with the chosen content) and **Delete** with browser confirmation.
- `Diff\LineDiff` — pure LCS-based line-diff utility used by the Versions component.
- Dark-mode-aware Tailwind layout (standalone, Tailwind via CDN in v1).

#### Testing helpers

- `Prompt::fake([...])` with string and `RenderedPrompt` stubs.
- `Prompt::assertCalled($name)` and `Prompt::assertNotCalled($name)`.

#### CLI

- `prompt:list` — list all prompts available through the active driver.
- `prompt:make` — scaffold a new prompt file.
- `prompt:show` — render and inspect a prompt with optional JSON variables.

#### Development

- `composer dev` boots an Orchestra Testbench workbench with SQLite (`workbench/database/database.sqlite`) and serves the panel at `http://127.0.0.1:8000` with `Panel::auth()` open in dev.
- `composer test:all` chains tests, coverage (`--min=100`), and PHPStan.
- CI matrix covering PHP 8.3 / 8.4 / 8.5 against Laravel 11 / 12 / 13.

### Publishable assets

- `prompt-config` — copies `config/prompt.php` into the host app.
- `prompt-migrations` — copies the `prompts` table migration into the host app.
- `prompt-views` — copies the panel Blade views into `resources/views/vendor/prompt`.

[Unreleased]: https://github.com/baconfy/prompt/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/baconfy/prompt/releases/tag/v0.1.0
