<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Livewire;

use Baconfy\Prompt\Models\Prompt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('prompt::layouts.panel')]
final class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Build a query returning, for each distinct prompt name, only the latest (highest-id) row.
     *
     * @return Builder<Prompt>
     */
    /**
     * Build a query returning, for each distinct prompt name, only the latest (highest-id)
     * row with a `versions_count` column counting the total rows for that name.
     *
     * @return Builder<Prompt>
     */
    private function latestQuery(): Builder
    {
        /** @phpstan-var Builder<Prompt> $query */
        $query = Prompt::query()
            ->selectRaw('prompts.*, (SELECT COUNT(*) FROM prompts AS p_versions WHERE p_versions.name = prompts.name) AS versions_count')
            ->whereIn('id', DB::table('prompts')->selectRaw('MAX(id) as id')->groupBy('name'))
            ->orderBy('name');

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return $query;
    }

    public function render(): View
    {
        /** @var LengthAwarePaginator<int, Prompt> $prompts */
        $prompts = $this->latestQuery()->paginate(15);

        return view('prompt::livewire.index', ['prompts' => $prompts]);
    }
}
