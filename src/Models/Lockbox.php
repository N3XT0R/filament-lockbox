<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;

/**
 * @property int $user_id
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

    public function lockboxable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
