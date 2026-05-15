<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ $prompt->name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Version history — {{ $versions->count() }} {{ $versions->count() === 1 ? 'version' : 'versions' }}</p>
        </div>
        <a href="{{ route('prompts.edit', $prompt) }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
            Edit current
        </a>
    </div>

    <div class="space-y-2">
        @foreach ($versions as $version)
            @php($isLatest = $loop->first)
            <div x-data="{ open: {{ $isLatest ? 'true' : 'false' }} }" class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <button type="button" @click="open = !open" class="flex flex-1 items-center gap-3 text-left">
                        <svg class="size-4 shrink-0 text-slate-400 transition-transform dark:text-slate-500" :class="open ? 'rotate-90' : ''" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                        </svg>

                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 font-mono text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            v{{ $version->version_number }}
                        </span>

                        @if ($isLatest)
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                current
                            </span>
                        @endif

                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $version->created_at?->diffForHumans() }}
                        </span>
                    </button>

                    <div class="flex shrink-0 items-center gap-2">
                        @unless ($isLatest)
                            <button
                                type="button"
                                wire:click="restore({{ $version->id }})"
                                wire:confirm="Restore v{{ $version->version_number }}? A new version with this content will be created on top."
                                class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                Restore
                            </button>
                        @endunless

                        <button
                            type="button"
                            wire:click="delete({{ $version->id }})"
                            wire:confirm="Delete v{{ $version->version_number }} permanently? This cannot be undone."
                            class="rounded-md border border-rose-200 bg-white px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50 dark:border-rose-900/50 dark:bg-slate-900 dark:text-rose-300 dark:hover:bg-rose-900/30"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div x-show="open" x-collapse>
                    @if ($isLatest)
                        <pre class="border-t border-slate-200 bg-slate-50 px-4 py-3 font-mono text-xs leading-relaxed text-slate-800 whitespace-pre-wrap dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200">{{ $version->content }}</pre>
                    @else
                        <div class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950">
                            <div class="border-b border-slate-200 bg-white px-4 py-2 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                                Diff vs current (v{{ $versions->first()->version_number }})
                            </div>
                            <div class="font-mono text-xs leading-relaxed">
                                @foreach ($version->diff as $segment)
                                    @switch($segment['type'])
                                        @case('added')
                                            <div class="bg-emerald-50 px-4 py-0.5 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-200"><span class="select-none text-emerald-500 dark:text-emerald-400">+ </span>{{ $segment['line'] }}</div>
                                            @break
                                        @case('removed')
                                            <div class="bg-rose-50 px-4 py-0.5 text-rose-900 dark:bg-rose-900/30 dark:text-rose-200"><span class="select-none text-rose-500 dark:text-rose-400">- </span>{{ $segment['line'] }}</div>
                                            @break
                                        @default
                                            <div class="px-4 py-0.5 text-slate-700 dark:text-slate-300"><span class="select-none text-slate-300 dark:text-slate-600">  </span>{{ $segment['line'] }}</div>
                                    @endswitch
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
