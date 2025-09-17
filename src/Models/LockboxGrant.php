<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Represents an access grant for a lockbox entry.
 * Can point to either a user or a group as recipient.
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
