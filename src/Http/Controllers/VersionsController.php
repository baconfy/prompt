<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Http\Controllers;

use Baconfy\Prompt\Diff\LineDiff;
use Baconfy\Prompt\Models\Prompt;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;

final class VersionsController
{
    public function index(Prompt $prompt): View
    {
        return view('prompt::pages.versions', [
            'prompt' => $prompt,
            'versions' => $this->collectVersions($prompt),
        ]);
    }

    public function restore(Prompt $prompt, int $version): RedirectResponse
    {
        $source = Prompt::query()
            ->where('id', $version)
            ->where('name', $prompt->name)
            ->firstOrFail();

        $rootId = Prompt::query()
            ->where('name', $prompt->name)
            ->orderBy('id')
            ->value('id');

        Prompt::create([
            'name' => $prompt->name,
            'root_id' => $rootId,
            'content' => $source->content,
        ]);

        return redirect()->route('prompts.versions', $prompt);
    }

    public function destroy(Prompt $prompt, int $version): RedirectResponse
    {
        Prompt::query()
            ->where('id', $version)
            ->where('name', $prompt->name)
            ->delete();

        return redirect()->route('prompts.versions', $prompt);
    }

    /**
     * All rows (root + versions) sharing the prompt's name, newest first, each
     * decorated with a sequential `version_number` (v1 = root, vN = latest) and
     * a `diff` attribute relative to the current latest content.
     *
     * @return Collection<int, Prompt>
     */
    private function collectVersions(Prompt $prompt): Collection
    {
        /** @var Collection<int, Prompt> $rows */
        $rows = Prompt::query()
            ->where('name', $prompt->name)
            ->orderBy('id')
            ->get();

        $latest = $rows->last();
        $latestContent = $latest instanceof Prompt ? $latest->content : '';
        $differ = new LineDiff;

        foreach ($rows as $index => $row) {
            $row->setAttribute('version_number', $index + 1);
            $row->setAttribute('diff', $differ->diff($row->content, $latestContent));
        }

        /** @var Collection<int, Prompt> $reversed */
        $reversed = $rows->reverse()->values();

        return $reversed;
    }
}
