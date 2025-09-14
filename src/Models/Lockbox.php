<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 */
class Lockbox extends Model
{
    protected $table = 'lockbox';

    protected $fillable = [
        'user_id',
        'name',
        'value',
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
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
