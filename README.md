```php
use N3XT0R\FilamentLockbox\Forms\Actions\UnlockLockboxAction;
use N3XT0R\FilamentLockbox\Forms\Components\EncryptedTextInput;

$form
    ->schema([
        EncryptedTextInput::make('secret_notes')
            ->label('Secret Notes'),
    ])
    ->extraActions([
        UnlockLockboxAction::make(),
    ]);
```

```php
use N3XT0R\FilamentLockbox\Forms\Components\DecryptedTextDisplay;
use N3XT0R\FilamentLockbox\Forms\Actions\UnlockLockboxAction;

$form
    ->schema([
        DecryptedTextDisplay::make('secret_notes')
            ->label('Secret Notes'),
    ])
    ->extraActions([
        UnlockLockboxAction::make(), // same modal to set lockbox_input
    ]);
```
