<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Prompts' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('prompts.index') }}" class="text-lg font-semibold">Prompts</a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('prompts.index') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">All</a>
                <a href="{{ route('prompts.create') }}" class="rounded-md bg-slate-900 px-3 py-1.5 text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">New prompt</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
