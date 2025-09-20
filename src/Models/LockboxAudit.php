<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;

/**
 * Audit trail entry for sensitive lockbox events.
 *
 * @property int        $id
 * @property int        $lockbox_id
 * @property int|null   $grant_id
 * @property int|null   $actor_id
 * @property string     $event
 * @property array|null $context
 */
class LockboxAudit extends Model
{
    protected $table = 'lockbox_audits';

    protected $fillable = [
        'lockbox_id',
        'grant_id',
        'actor_id',
        'event',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function lockbox(): BelongsTo
    {
        return $this->belongsTo(Lockbox::class);
    }

    public function grant(): BelongsTo
    {
        return $this->belongsTo(LockboxGrant::class, 'grant_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Create an audit record for a security-sensitive event.
     *
     * ⚠️ Context payload must never contain plaintext secrets.
     */
    public static function record(
        string $event,
        Lockbox $lockbox,
        ?User $actor = null,
        ?LockboxGrant $grant = null,
        array $context = [],
    ): void {
        static::create([
            'lockbox_id' => $lockbox->getKey(),
            'grant_id' => $grant?->getKey(),
            'actor_id' => $actor?->getKey(),
            'event' => $event,
            'context' => $context,
        ]);
    }
}
