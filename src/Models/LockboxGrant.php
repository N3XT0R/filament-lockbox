<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Represents an access grant for a lockbox entry.
 * Can point to either a user or a group as recipient.
 *
 * @property int         $id
 * @property int         $lockbox_id
 * @property string      $grantee_type
 * @property int         $grantee_id
 * @property string      $wrapped_dek
 * @property Carbon|null $expires_at
 * @property-read Lockbox $lockbox
 * @property-read Model $grantee
 */
class LockboxGrant extends Model
{
    protected $table = 'lockbox_grants';

    protected $fillable = [
        'lockbox_id',
        'grantee_type',
        'grantee_id',
        'wrapped_dek',
        'expires_at',
    ];

    /**
     * The lockbox entry this grant is for.
     */
    public function lockbox(): BelongsTo
    {
        return $this->belongsTo(Lockbox::class);
    }

    /**
     * Polymorphic grantee (User or LockboxGroup).
     */
    public function grantee(): MorphTo
    {
        return $this->morphTo();
    }
}
