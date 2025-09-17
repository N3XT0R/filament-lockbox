<div {{ $attributes->merge(['class' => 'text-sm text-gray-900 dark:text-gray-100 flex items-center gap-1']) }}>
    {!! $getState() !!}

    @if ($isShared())
        <span class="inline-flex items-center text-xs text-blue-600 dark:text-blue-400">
            <x-heroicon-s-user-group class="w-4 h-4 mr-0.5" />
            {{ __('filament-lockbox::lockbox.decryption.shared_via_grant') }}

            @if ($sharedBy())
                ({{ $sharedBy() }})
            @endif
        </span>
    @endif
</div>
