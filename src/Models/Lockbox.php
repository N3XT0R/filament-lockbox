<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;

/**
 * Eloquent model storing encrypted lockbox values.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 *
 * @property int    $user_id
 * @property string $name
 * @property string $value
 * @property string $encrypted_dek
 */
class Lockbox extends Model
{
    protected $table = 'lockbox';

    protected $fillable = [
        'user_id',
        'name',
        'value',
        'encrypted_dek',
    ];

    /**
     * Get the owning lockboxable model.
     *
     * @return MorphTo
     */
    public function lockboxable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relationship to the owning user.
     *
     * @return BelongsTo<User, static>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, static> $relation */
        $relation = $this->belongsTo(User::class);

        return $relation;
    }

    public function grants(): HasMany
    {
        return $this->hasMany(LockboxGrant::class);
    }
}
