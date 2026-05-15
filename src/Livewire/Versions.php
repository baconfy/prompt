<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Livewire;

use Baconfy\Prompt\Diff\LineDiff;
use Baconfy\Prompt\Models\Prompt;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('prompt::layouts.panel')]
final class Versions extends Component
{
    public Prompt $prompt;

    public function mount(Prompt $prompt): void
    {
        $this->prompt = $prompt;
    }

    /**
     * All rows (root + versions) sharing the prompt's name, newest first, with a
     * sequential `version_number` attribute (v1 = root, vN = latest).
     *
     * @return Collection<int, Prompt>
     */
    #[Computed]
    public function versions(): Collection
    {
        /** @var Collection<int, Prompt> $rows */
        $rows = Prompt::query()
            ->where('name', $this->prompt->name)
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

    public function restore(int $versionId): void
    {
        $version = Prompt::query()
            ->where('id', $versionId)
            ->where('name', $this->prompt->name)
            ->firstOrFail();

        $rootId = Prompt::query()
            ->where('name', $this->prompt->name)
            ->orderBy('id')
            ->value('id');

        Prompt::create([
            'name' => $this->prompt->name,
            'root_id' => $rootId,
            'content' => $version->content,
        ]);
    }

    public function delete(int $versionId): void
    {
        Prompt::query()
            ->where('id', $versionId)
            ->where('name', $this->prompt->name)
            ->delete();
    }

    public function render(): View
    {
        return view('prompt::livewire.versions', ['versions' => $this->versions()]);
    }
}
