<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Http\Controllers;

use Baconfy\Prompt\Models\Prompt;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class IndexController
{
    public function __invoke(Request $request): View
    {
        $search = trim($request->query->getString('search'));

        /** @phpstan-var Builder<Prompt> $query */
        $query = Prompt::query()
            ->selectRaw('prompts.*, (SELECT COUNT(*) FROM prompts AS p_versions WHERE p_versions.name = prompts.name) AS versions_count')
            ->whereIn('id', DB::table('prompts')->selectRaw('MAX(id) as id')->groupBy('name'))
            ->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $prompts = $query->paginate(15)->withQueryString();

        return view('prompt::pages.index', [
            'prompts' => $prompts,
            'search' => $search,
        ]);
    }
}
