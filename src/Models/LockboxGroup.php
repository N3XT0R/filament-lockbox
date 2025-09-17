<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Represents a cryptographic group that can share lockbox items.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $encrypted_group_key
 * @property int         $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $creator
 * @property-read Collection<int, User> $members
 * @property-read Collection<int, LockboxGrant> $grants
 */
class LockboxGroup extends Model
{
    protected $table = 'lockbox_groups';

    protected $fillable = [
        'name',
        'encrypted_group_key',
        'created_by',
    ];

    /**
     * Creator of the group.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Members of this group with their wrapped group key.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lockbox_group_user')
            ->using(LockboxGroupUser::class)
            ->withPivot('wrapped_group_key_for_user')
            ->withTimestamps();
    }

    /**
     * Grants referencing this group as grantee.
     */
    public function grants(): MorphMany
    {
        return $this->morphMany(LockboxGrant::class, 'grantee');
    }
}
