@extends('prompt::layouts.panel')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">Prompts</h1>
        <form method="GET" action="{{ route('prompts.index') }}">
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search by name…"
                class="w-72 rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-slate-400 dark:focus:ring-slate-400"
            >
        </form>
    </div>

    @if ($prompts->isEmpty())
        <p class="rounded-md border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
            No prompts yet. <a href="{{ route('prompts.create') }}" class="text-slate-900 underline dark:text-slate-100">Create the first one</a>.
        </p>
    @else
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wider text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3 text-center">Version</th>
                        <th class="px-4 py-3 text-center">Updated</th>
                        <th class="w-px whitespace-nowrap px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    @foreach ($prompts as $prompt)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $prompt->name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 font-mono text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    v{{ $prompt->versions_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-500 dark:text-slate-400">{{ $prompt->updated_at?->diffForHumans() }}</td>
                            <td class="w-px whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('prompts.edit', $prompt) }}" class="text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">Edit</a>
                                <span class="mx-2 text-slate-300 dark:text-slate-600">·</span>
                                <a href="{{ route('prompts.versions', $prompt) }}" class="text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">Versions</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $prompts->links() }}
        </div>
    @endif
@endsection
