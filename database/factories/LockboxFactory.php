<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Tests\Stubs\Auth\LockboxUser;

class LockboxFactory extends Factory
{
    protected $model = Lockbox::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'value' => $this->faker->sha256(),
            'user_id' => LockboxUser::factory(),
        ];
    }

    public function forUser(LockboxUser $user): self
    {
        return $this->for($user, 'user');
    }
}
