@extends('prompt::layouts.panel')

@section('content')
  <div class="flex items-start justify-between gap-6">
    <div class="basis-full">
      <form method="POST" action="{{ route('prompts.save') }}">
        @csrf

        <div class="space-y-4 lg:col-span-2">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
            <input type="text" name="name" value="{{ $name }}" class="mt-1 w-full rounded-md border-slate-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:ring-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-slate-400 dark:focus:ring-slate-400">
            @error('name') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Content</label>
            <textarea name="content" rows="18" class="mt-1 w-full rounded-md border-slate-300 px-3 py-2 font-mono text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-slate-400 dark:focus:ring-slate-400">{{ $content }}</textarea>
            @error('content') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
          </div>

          <div class="flex items-center gap-3">
            <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">
              Save
            </button>
            <a href="{{ route('prompts.index') }}" class="text-sm text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">Cancel</a>
          </div>
        </div>
      </form>
    </div>
    <div class="basis-2/3">
      <aside class="mt-6 space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Preview</h2>
        <form method="POST" action="{{ route('prompts.preview') }}" class="space-y-3">
          @csrf
          <input type="hidden" name="name" value="{{ $name }}">
          <input type="hidden" name="content" value="{{ $content }}">

          @if ($prompt)
            <input type="hidden" name="prompt_id" value="{{ $prompt->id }}">
          @endif
          <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Variables (JSON)</label>
          <textarea name="variables" rows="4" class="w-full rounded-md border-slate-300 px-3 py-2 font-mono text-xs shadow-sm focus:border-slate-500 focus:ring-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-400 dark:focus:ring-slate-400">{{ $variables }}</textarea>
          <button type="submit" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
            Render preview
          </button>
        </form>
        @if ($previewError)
          <p class="rounded-md bg-rose-50 p-3 text-xs text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">{{ $previewError }}</p>
        @elseif ($previewOutput !== null)
          <pre class="whitespace-pre-wrap rounded-md bg-slate-50 p-3 text-xs text-slate-800 dark:bg-slate-950 dark:text-slate-200">{{ $previewOutput }}</pre>
        @endif
      </aside>
    </div>
  </div>
@endsection
