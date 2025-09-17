<x-filament::input.wrapper
    :disabled="$isDisabled()"
    :state-path="$getStatePath()"
>
    <x-filament::input
        type="text"
        :value="$getState()"
        readonly="true"
        {{ $attributes->merge(['class' => 'text-gray-900 dark:text-gray-100']) }}
    />

    @if ($isDecrypted())
        <div class="text-xs text-blue-600 dark:text-blue-400 mt-1 flex items-center gap-1">
            <x-heroicon-s-user-group class="w-4 h-4" />
            {{ __('filament-lockbox::lockbox.decryption.shared_via_grant') }}
        </div>
    @endif
</x-filament::input.wrapper>
