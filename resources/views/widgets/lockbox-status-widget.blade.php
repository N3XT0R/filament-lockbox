<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            @if (auth()->user()?->hasLockboxKey())
                <div class="text-success-600 font-medium">
                    ✅ Your Lockbox is initialized.
                </div>
            @else
                <div class="text-danger-600 font-medium">
                    ❌ No Lockbox key found for your account.
                </div>

                <x-filament::button wire:click="generateKey">
                    Generate Lockbox Key
                </x-filament::button>
            @endif

            <x-filament::form wire:submit="savePassword">
                <x-filament::grid>
                    @foreach ($this->getFormSchema() as $field)
                        {{ $field }}
                    @endforeach
                </x-filament::grid>

                <x-filament::button type="submit" color="primary">
                    Set Crypto Password
                </x-filament::button>
            </x-filament::form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
