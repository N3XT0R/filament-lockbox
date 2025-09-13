@php
    use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
    $user = auth()->user();
    $supportsLockbox = $user instanceof HasLockboxKeys;
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            @if (! $supportsLockbox)
                <div class="text-warning-600 font-medium">
                    {{ __('filament-lockbox::lockbox.status.not_supported') }}
                </div>
            @elseif ($user->hasLockboxKey())
                <div class="text-success-600 font-medium">
                    {{ __('filament-lockbox::lockbox.status.initialized') }}
                </div>
            @else
                <div class="text-danger-600 font-medium">
                    {{ __('filament-lockbox::lockbox.status.missing') }}
                </div>

                <x-filament::button wire:click="generateKey">
                    {{ __('filament-lockbox::lockbox.buttons.generate_key') }}
                </x-filament::button>
            @endif

            @if ($supportsLockbox)
                <x-filament::form wire:submit="savePassword">
                    <x-filament::grid>
                        @foreach ($this->getFormSchema() as $field)
                            {{ $field }}
                        @endforeach
                    </x-filament::grid>

                    <x-filament::button type="submit" color="primary">
                        {{ __('filament-lockbox::lockbox.buttons.set_password') }}
                    </x-filament::button>
                </x-filament::form>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
