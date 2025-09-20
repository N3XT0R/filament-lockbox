<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Represents an access grant for a lockbox entry.
 * Can point to either a user or a group as recipient.
 *
 * @property int         $id
 * @property int         $lockbox_id
 * @property string      $grantee_type
 * @property int         $grantee_id
 * @property string      $wrapped_dek
 * @property int         $dek_version
 * @property Carbon|null $expires_at
 * @property-read Lockbox|null $lockbox
 * @property-read Model|null $grantee
 */
class LockboxGrant extends Model
{
    protected $table = 'lockbox_grants';

    protected $fillable = [
        'lockbox_id',
        'grantee_type',
        'grantee_id',
        'wrapped_dek',
        'dek_version',
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

    /**
     * Audit events associated with this grant.
     */
    public function audits(): HasMany
    {
        return $this->hasMany(LockboxAudit::class, 'grant_id');
    }

    protected static function booted(): void
    {
        static::deleting(static function (LockboxGrant $grant): void {
            if ($grant->getAttribute('_lockbox_audit_recorded')) {
                return;
            }

            $lockbox = $grant->lockbox;
            if ($lockbox === null) {
                return;
            }

            $actor = Auth::user();
            $userActor = $actor instanceof User ? $actor : null;

            LockboxAudit::record('revoke', $lockbox, $userActor, $grant, [
                'grantee_type' => $grant->getAttribute('grantee_type'),
                'grantee_id' => $grant->getAttribute('grantee_id'),
                'revoked_at' => now()->toIso8601String(),
            ]);
        });
    }
}
