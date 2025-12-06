<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attributes
 * @property int $id
 * @property string $vcs_id
 * @property string $username
 *
 * Foreign keys
 * @property int $vcs_instance_id
 * @property ?int $user_id
 *
 * Relationships
 * @property VcsInstance $vcsInstance
 * @property ?User $user
 */
class VcsInstanceUser extends Model
{
    /** @use HasFactory<\Database\Factories\VcsInstanceUserFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<VcsInstance, $this>
     */
    public function vcsInstance(): BelongsTo
    {
        return $this->belongsTo(VcsInstance::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
