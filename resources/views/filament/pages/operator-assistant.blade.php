<x-filament-panels::page>
    <div
        id="pp-operator-assistant"
        class="flex h-[70vh] flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"
        data-agent-url="{{ route('passaparola.operator-assistant.run') }}"
        data-thread-id="{{ $threadId }}"
        data-csrf="{{ csrf_token() }}"
        data-i18n='@json(__("passaparola::operator_assistant.js"))'
        wire:ignore
    >
        <div id="pp-oa-messages" class="flex-1 space-y-3 overflow-y-auto p-4" aria-live="polite">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('passaparola::operator_assistant.intro') }}</p>
            <div class="flex flex-wrap gap-2">
                @foreach (__('passaparola::operator_assistant.suggestions') as $suggestion)
                    <button type="button" data-suggestion="{{ $suggestion }}" class="rounded-full border border-gray-300 px-3 py-1 text-xs hover:bg-gray-50 dark:border-white/20 dark:hover:bg-white/5">{{ $suggestion }}</button>
                @endforeach
            </div>
        </div>
        <form id="pp-oa-form" class="flex gap-2 border-t border-gray-200 p-3 dark:border-white/10">
            <label for="pp-oa-input" class="sr-only">{{ __('passaparola::operator_assistant.input') }}</label>
            <input id="pp-oa-input" type="text" autocomplete="off" maxlength="2000"
                   placeholder="{{ __('passaparola::operator_assistant.placeholder') }}"
                   class="flex-1 rounded-lg border-gray-300 text-sm dark:border-white/20 dark:bg-gray-800">
            <x-filament::button type="submit">{{ __('passaparola::operator_assistant.send') }}</x-filament::button>
        </form>
    </div>

    <script type="module" src="{{ asset('assets/passaparola/js/operator-assistant.js') }}?v={{ filemtime(module_path('Passaparola', 'public/js/operator-assistant.js')) }}"></script>
</x-filament-panels::page>
