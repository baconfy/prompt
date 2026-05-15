<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Http\Controllers;

use Baconfy\Prompt\FrontMatter\Parser;
use Baconfy\Prompt\Models\Prompt;
use Baconfy\Prompt\Renderer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Exception\ParseException;
use Throwable;

final class EditorController
{
    public function create(): View
    {
        return view('prompt::pages.editor', [
            'prompt' => null,
            'name' => old('name', ''),
            'content' => old('content', ''),
            'variables' => old('variables', '{}'),
            'previewOutput' => null,
            'previewError' => null,
        ]);
    }

    public function edit(Prompt $prompt): View
    {
        return view('prompt::pages.editor', [
            'prompt' => $prompt,
            'name' => old('name', $prompt->name),
            'content' => old('content', $prompt->content),
            'variables' => old('variables', '{}'),
            'previewOutput' => null,
            'previewError' => null,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        /** @var array{name: string, content: string} $validated */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        try {
            (new Parser)->parse($validated['content']);
        } catch (ParseException $e) {
            return back()
                ->withInput()
                ->withErrors(['content' => 'Invalid YAML front matter: '.$e->getMessage()]);
        }

        $existing = Prompt::query()
            ->where('name', $validated['name'])
            ->orderBy('id', 'desc')
            ->first();

        if ($existing !== null && $existing->content === $validated['content']) {
            return back()
                ->withInput()
                ->withErrors(['content' => 'No changes to save — content is identical to the current version.']);
        }

        $rootId = Prompt::query()
            ->where('name', $validated['name'])
            ->orderBy('id')
            ->value('id');

        Prompt::create([
            'name' => $validated['name'],
            'root_id' => $rootId,
            'content' => $validated['content'],
        ]);

        return redirect()->route('prompts.index');
    }

    public function preview(Request $request, Renderer $renderer): View
    {
        $name = $request->string('name')->toString();
        $content = $request->string('content')->toString();
        $variables = $request->string('variables', '{}')->toString();

        $previewOutput = null;
        $previewError = null;

        try {
            /** @var array<string, mixed> $data */
            $data = (array) json_decode($variables, associative: true, flags: JSON_THROW_ON_ERROR);
            $parsed = (new Parser)->parse($content);
            $previewOutput = $renderer->render($parsed, $data)->content;
        } catch (Throwable $e) {
            $previewError = $e->getMessage();
        }

        $prompt = null;
        $promptId = $request->integer('prompt_id');
        if ($promptId > 0) {
            $prompt = Prompt::query()->find($promptId);
        }

        return view('prompt::pages.editor', [
            'prompt' => $prompt,
            'name' => $name,
            'content' => $content,
            'variables' => $variables,
            'previewOutput' => $previewOutput,
            'previewError' => $previewError,
        ]);
    }
}
