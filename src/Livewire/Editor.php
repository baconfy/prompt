<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Livewire;

use Baconfy\Prompt\FrontMatter\Parser;
use Baconfy\Prompt\Models\Prompt;
use Baconfy\Prompt\Renderer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Symfony\Component\Yaml\Exception\ParseException;
use Throwable;

#[Layout('prompt::layouts.panel')]
final class Editor extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string')]
    public string $content = '';

    public ?int $promptId = null;

    public string $variables = '{}';

    public ?string $previewOutput = null;

    public ?string $previewError = null;

    public function mount(?Prompt $prompt = null): void
    {
        if ($prompt !== null && $prompt->exists) {
            $this->promptId = $prompt->id;
            $this->name = $prompt->name;
            $this->content = $prompt->content;
        }
    }

    public function save(): void
    {
        $this->validate();

        try {
            (new Parser)->parse($this->content);
        } catch (ParseException $e) {
            $this->addError('content', 'Invalid YAML front matter: '.$e->getMessage());

            return;
        }

        $existing = Prompt::query()
            ->where('name', $this->name)
            ->orderBy('id', 'desc')
            ->first();

        if ($existing !== null && $existing->content === $this->content) {
            $this->addError('content', 'No changes to save — content is identical to the current version.');

            return;
        }

        $rootId = Prompt::query()
            ->where('name', $this->name)
            ->orderBy('id')
            ->value('id');

        Prompt::create([
            'name' => $this->name,
            'root_id' => $rootId,
            'content' => $this->content,
        ]);

        $this->redirectRoute('prompts.index', navigate: true);
    }

    public function preview(Renderer $renderer): void
    {
        $this->previewError = null;
        $this->previewOutput = null;

        try {
            /** @var array<string, mixed> $data */
            $data = (array) json_decode($this->variables, associative: true, flags: JSON_THROW_ON_ERROR);
            $parsed = (new Parser)->parse($this->content);
            $this->previewOutput = $renderer->render($parsed, $data)->content;
        } catch (Throwable $e) {
            $this->previewError = $e->getMessage();
        }
    }

    public function render(): View
    {
        return view('prompt::livewire.editor');
    }
}
