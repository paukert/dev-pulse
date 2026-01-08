<?php

declare(strict_types=1);

namespace App\Models;

use App\DTOs\UserDTO;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

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

    /**
     * @return int id of the VcsInstanceUser model
     */
    public static function upsertFromDTO(UserDTO $userDTO, VcsInstance $vcsInstance): int
    {
        $id = Cache::get(key: "vcs-instance-user-$vcsInstance->id-$userDTO->vcsId");
        if ($id !== null) {
            return $id;
        }

        $vcsInstanceUser = VcsInstanceUser::where([
            'vcs_id' => $userDTO->vcsId,
            'vcs_instance_id' => $vcsInstance->id,
        ])->firstOrNew();

        $vcsInstanceUser->vcs_id = $userDTO->vcsId;
        $vcsInstanceUser->username = $userDTO->username;
        $vcsInstanceUser->vcs_instance_id = $vcsInstance->id;
        $vcsInstanceUser->save();

        Cache::set(key: "vcs-instance-user-$vcsInstance->id-$userDTO->vcsId", value: $vcsInstanceUser->id, ttl: 60 * 60 * 24);

        return $vcsInstanceUser->id;
    }
}
