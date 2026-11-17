{{-- Storico di una conversazione nel pannello operatore. $getState(): list<{role, text, at}> --}}
@php($messages = $getState() ?? [])
<div class="space-y-3" role="log">
    @forelse ($messages as $message)
        <div @class([
            'max-w-[85%] rounded-lg px-3 py-2 text-sm whitespace-pre-wrap',
            'ms-auto bg-primary-600 text-white' => $message['role'] === 'visitor',
            'bg-gray-100 dark:bg-gray-800' => $message['role'] === 'assistant',
            'bg-amber-100 dark:bg-amber-900' => $message['role'] === 'operator',
        ])>
            <div class="mb-1 text-xs opacity-75">
                {{ __('passaparola::conversation.roles.'.$message['role']) }}
                @if ($message['at'])
                    · {{ \Illuminate\Support\Carbon::parse($message['at'])->format('d/m/Y H:i') }}
                @endif
            </div>
            {{ $message['text'] }}
        </div>
    @empty
        <p class="text-sm text-gray-500">{{ __('passaparola::conversation.empty') }}</p>
    @endforelse
</div>
