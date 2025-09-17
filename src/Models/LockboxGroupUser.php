<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * Pivot model between users and lockbox groups.
 * Stores the group key wrapped for each user.
 *
 * @property int         $group_id
 * @property int         $user_id
 * @property string      $wrapped_group_key_for_user
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LockboxGroupUser extends Pivot
{
    protected $table = 'lockbox_group_user';

    protected $fillable = [
        'group_id',
        'user_id',
        'wrapped_group_key_for_user',
    ];
}
