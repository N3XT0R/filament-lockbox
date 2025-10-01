<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use N3XT0R\FilamentLockbox\Tests\Stubs\Auth\LockboxUser;

class LockboxUserFactory extends Factory
{
    protected $model = LockboxUser::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),

            'encrypted_user_key' => Crypt::encryptString('server-key'),
            'crypto_password_hash' => bcrypt('secret-passphrase'),
            'lockbox_provider' => null,
        ];
    }

    public function withProvider(string $providerClass): self
    {
        return $this->state(static fn () => [
            'lockbox_provider' => $providerClass,
        ]);
    }

    public function withCryptoPassword(string $plainPassword): self
    {
        return $this->state(fn () => [
            'crypto_password_hash' => bcrypt($plainPassword),
        ]);
    }
}
